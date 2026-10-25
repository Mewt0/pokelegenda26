<?php
declare(strict_types=1);
namespace Pokemon8\Service;

use Pokemon8\Repository\AdminGrantReceiptRepository;
use Pokemon8\Repository\AdminRepository;
use Pokemon8\Security\Session;

final class AdminItemGrantV2Service
{
    private const SESSION_KEY = 'admin_item_grant_v2_tickets';

    public function __construct(private AdminRepository $admin, private AdminGrantReceiptRepository $receipts,
        private Session $session, private int $ttl = 600, private ?\Closure $clock = null)
    {
        if (($ttl !== 600 || $clock !== null) && !AdminGrantReceiptRepository::localQa()) {
            throw new \LogicException('Clock overrides require local QA.');
        }
    }

    public function preview(int $adminId, array $input): array
    {
        $this->guard($adminId);
        $payload = $this->normalize($input);
        $data = $this->receipts->preview($payload);
        $tickets = $this->tickets();
        $token = bin2hex(random_bytes(32));
        $expiry = $this->now() + $this->ttl;
        $correlation = bin2hex(random_bytes(16));
        $tickets[hash('sha256', $token)] = ['admin_id' => $adminId, 'payload_hash' => $this->hash($payload),
            'before_qty' => $data['before_qty'], 'issued_at' => $this->now(), 'expires_at' => $expiry,
            'correlation_id' => $correlation, 'consumed' => false];
        // Bounded session state, including consumed tickets; receipts remain durable in DB.
        $this->session->put(self::SESSION_KEY, array_slice($tickets, -20, null, true));
        return ['ok' => true, 'data' => $data + ['ticket' => $token, 'expires_at' => $expiry, 'correlation_id' => $correlation]];
    }

    public function confirm(int $adminId, array $input): array
    {
        $this->guard($adminId);
        $this->knownFields($input, ['ticket', 'payload', 'reason', 'confirm', 'idempotency_key']);
        $payloadInput = $input['payload'] ?? null;
        if (is_string($payloadInput)) {
            $payloadInput = json_decode($payloadInput, true);
        }
        if (!is_array($payloadInput)) {
            throw new AdminGrantException('invalid_payload', 422);
        }
        $payload = $this->normalize($payloadInput);
        $reason = is_string($input['reason'] ?? null) ? trim($input['reason']) : '';
        if (mb_strlen($reason) < 8 || mb_strlen($reason) > 500 || preg_match('/password|парол|csrf|cookie|session|api[_ -]?key|token|токен|bearer\s|sk-[a-z0-9]{12}|gh[pousr]_[a-z0-9]{12}/iu', $reason)) {
            throw new AdminGrantException('invalid_reason', 422);
        }
        if (!in_array($input['confirm'] ?? null, [true, 'true'], true)) {
            throw new AdminGrantException('confirmation_required', 422);
        }
        $key = $input['idempotency_key'] ?? '';
        if (!is_string($key) || !preg_match('/\A[A-Za-z0-9_-]{16,128}\z/D', $key)) {
            throw new AdminGrantException('invalid_idempotency_key', 422);
        }
        $hash = $this->hash(['payload' => $payload, 'reason' => $reason]);
        $replay = $this->receipts->replay($adminId, $key, $hash);
        if ($replay !== null) {
            return $replay;
        }
        $token = $input['ticket'] ?? '';
        if (!is_string($token) || !preg_match('/\A[a-f0-9]{64}\z/D', $token)) {
            throw new AdminGrantException('invalid_ticket', 409);
        }
        $tickets = $this->tickets();
        $ticketHash = hash('sha256', $token);
        $ticket = $tickets[$ticketHash] ?? null;
        if (!is_array($ticket) || $ticket['admin_id'] !== $adminId || $ticket['consumed'] || $ticket['expires_at'] <= $this->now()) {
            throw new AdminGrantException('expired_or_consumed_ticket', 409);
        }
        // Consume on the first validly identified attempt, including payload mismatch/failure.
        $tickets[$ticketHash]['consumed'] = true;
        $this->session->put(self::SESSION_KEY, $tickets);
        if (!hash_equals($ticket['payload_hash'], $this->hash($payload))) {
            throw new AdminGrantException('preview_changed', 409);
        }
        return $this->receipts->confirm($adminId, $key, $hash, $payload, $reason, $ticket, $ticketHash);
    }

    private function normalize(array $payload): array
    {
        $this->knownFields($payload, ['target_user_id', 'item_id', 'quantity']);
        $normalized = [];
        foreach (['target_user_id', 'item_id', 'quantity'] as $field) {
            $value = $payload[$field] ?? null;
            if ((!is_int($value) && !is_string($value)) || !preg_match('/\A[1-9][0-9]{0,9}\z/D', (string) $value)) {
                throw new AdminGrantException('invalid_' . $field, 422);
            }
            $normalized[$field] = (int) $value;
            if ($normalized[$field] > ($field === 'quantity' ? 1000000 : 2147483647)) {
                throw new AdminGrantException('invalid_' . $field, 422);
            }
        }
        return $normalized;
    }

    private function guard(int $adminId): void
    {
        if (!$this->admin->canAccess($adminId)) {
            throw new AdminGrantException('forbidden', 403);
        }
        if ($this->admin->setting('admin.grant.v2', '0') !== '1') {
            throw new AdminGrantException('not_found', 404);
        }
    }

    private function knownFields(array $input, array $allowed): void
    {
        if (array_diff(array_keys($input), $allowed)) {
            throw new AdminGrantException('unknown_fields', 422);
        }
    }

    private function tickets(): array
    {
        $tickets = $this->session->get(self::SESSION_KEY, []);
        return is_array($tickets) ? array_filter($tickets, fn ($ticket) => is_array($ticket) && ($ticket['expires_at'] ?? 0) > $this->now()) : [];
    }

    private function hash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private function now(): int
    {
        return $this->clock !== null ? ($this->clock)() : time();
    }
}
