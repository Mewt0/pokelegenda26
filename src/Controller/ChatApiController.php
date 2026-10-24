<?php
declare(strict_types=1);

namespace Pokemon8\Controller;

use Pokemon8\Game\ChatService;
use Pokemon8\Http\Request;
use Pokemon8\Http\Response;
use Pokemon8\Repository\LocationRepository;
use Pokemon8\Security\Csrf;
use Pokemon8\Security\Session;
use Throwable;

final class ChatApiController
{
    public function __construct(
        private Session $session,
        private Csrf $csrf,
        private ChatService $chatService,
        private LocationRepository $locationRepository
    ) {
    }

    public function messages(Request $request): Response
    {
        try {
            $userId = (int) $this->session->get('id', 0);
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            $afterId = max(0, (int) $request->input('after_id', '0'));
            $userState = $this->locationRepository->findUserState($userId);
            if ($userState === null) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            $roomId = (int) ($userState['buildmy'] ?? 1);
            if ($request->input('sync_only', '0') === '1') {
                return $this->json([
                    'ok' => true,
                    'messages' => [],
                    'lastId' => $this->chatService->latestVisibleMessageId($userId, $roomId),
                ]);
            }

            $messages = $this->chatService->getMessages($userId, $roomId, $afterId);

            // Упоминания приходят маркерами [[user:ID]]: id — источник правды, имя
            // подставляем из состояния игрока, чтобы упоминание переживало
            // переименование. Неизвестный id отдаёт пустое имя — фронт покажет
            // безопасную заглушку вместо сырого маркера.
            $userNames = [];
            foreach ($messages as &$message) {
                $text = (string) ($message['text'] ?? '');
                preg_match_all('/\[\[user:([1-9][0-9]*)\]\]/', $text, $matches);
                $message['mentioned_me'] = (int) ($message['author_id'] ?? 0) !== $userId
                    && in_array((string) $userId, $matches[1], true);
                $message['user_mentions'] = [];
                foreach (array_slice(array_unique($matches[1]), 0, 12) as $idText) {
                    $id = (int) $idText;
                    if ($id <= 0) {
                        continue;
                    }
                    if (!array_key_exists($id, $userNames)) {
                        $state = $this->locationRepository->findUserState($id);
                        $userNames[$id] = $state !== null ? (string) ($state['login'] ?? '') : '';
                    }
                    if ($userNames[$id] !== '') {
                        $message['user_mentions'][(string) $id] = $userNames[$id];
                    }
                }
            }
            unset($message);

            return $this->json([
                'ok' => true,
                'messages' => $messages,
                'lastId' => !empty($messages) ? (int) end($messages)['id'] : $afterId,
            ]);
        } catch (Throwable $e) {
            error_log('[ChatApiController::messages] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Ошибка сервера при получении сообщений.'], 500);
        }
    }

    public function teamPokemon(Request $request): Response
    {
        try {
            $userId = (int) $this->session->get('id', 0);
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            return $this->json([
                'ok' => true,
                'pokemon' => $this->chatService->teamPokemonForMention($userId),
            ]);
        } catch (Throwable $e) {
            error_log('[ChatApiController::teamPokemon] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Server error while loading team pokemon.'], 500);
        }
    }

    public function send(Request $request): Response
    {
        try {
            $userId = (int) $this->session->get('id', 0);
            if ($userId <= 0) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            if (!$this->csrf->validate($request->input('_csrf'))) {
                return $this->json(['ok' => false, 'error' => 'csrf', 'message' => 'Сессия устарела.'], 419);
            }

            $userState = $this->locationRepository->findUserState($userId);
            if ($userState === null) {
                return $this->json(['ok' => false, 'error' => 'auth'], 401);
            }

            // Владелец может показать своего покемона из команды или питомника.
            // И raw #ID, и маркеры проверяются на сервере, чужие ID не раскрываем.
            $text = ChatService::normalizePokemonMentions((string) $request->input('text', ''));
            preg_match_all('/\[\[poke:([1-9][0-9]*)\]\]/', $text, $matches);
            if (count(array_unique($matches[1])) > 6) {
                return $this->json(['ok' => false, 'message' => 'В одном сообщении можно показать до шести покемонов.'], 422);
            }
            if ($matches[1] !== []) {
                foreach ($matches[1] as $mentionedId) {
                    if (!$this->chatService->canMentionPokemon($userId, (int) $mentionedId)) {
                        return $this->json(['ok' => false, 'message' => 'Можно упоминать только своих покемонов.'], 422);
                    }
                }
            }

            $result = $this->chatService->sendMessage(
                $userId,
                (string) ($userState['login'] ?? 'Unknown'),
                (int) ($userState['buildmy'] ?? 1),
                $text,
                [
                    'tipe' => (int) $request->input('tipe', '1'),
                    'userto' => (int) $request->input('userto', '0'),
                    'private' => (int) $request->input('private', '0'),
                ]
            );

            return $this->json($result);
        } catch (Throwable $e) {
            error_log('[ChatApiController::send] ' . $e::class . ': ' . $e->getMessage());
            return $this->json(['ok' => false, 'message' => 'Ошибка сервера при отправке сообщения.'], 500);
        }
    }

    private function json(array $payload, int $status = 200): Response
    {
        return new Response(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE) ?: '{}',
            $status,
            ['Content-Type' => 'application/json; charset=UTF-8']
        );
    }
}
