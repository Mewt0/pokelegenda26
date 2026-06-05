<?php
declare(strict_types=1);

namespace Pokemon8\Support;

use PDO;

final class Mailer
{
    public function __construct(private array $config, private ?PDO $db = null)
    {
    }

    public function send(string $to, string $subject, string $text): bool
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !($this->config['enabled'] ?? true)) {
            $this->logDelivery($to, $subject, 'skipped', 'disabled_or_invalid');
            return false;
        }

        $transport = strtolower((string) ($this->config['transport'] ?? 'mail'));
        $fromAddress = (string) ($this->config['from_address'] ?? 'no-reply@pokemonchic.local');
        $fromName = (string) ($this->config['from_name'] ?? 'Pokemon 8.0');
        $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
        $encodedFromName = mb_encode_mimeheader($fromName, 'UTF-8', 'B');
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'From: ' . $encodedFromName . ' <' . $fromAddress . '>',
            'Reply-To: ' . $fromAddress,
            'X-Mailer: Pokemon 8.0',
        ];

        try {
            $sent = match ($transport) {
                'smtp' => $this->sendSmtp($to, $subject, $text, $fromAddress, $fromName),
                'log', 'array' => true,
                default => mail($to, $encodedSubject, $text, implode("\r\n", $headers)),
            };
            $this->logDelivery($to, $subject, $sent ? 'success' : 'failed', '', $transport);
            return $sent;
        } catch (\Throwable $e) {
            $this->logDelivery($to, $subject, 'failed', $e->getMessage(), $transport);
            return false;
        }
    }

    private function sendSmtp(string $to, string $subject, string $text, string $fromAddress, string $fromName): bool
    {
        $host = trim((string) ($this->config['smtp_host'] ?? '127.0.0.1'));
        $port = max(1, (int) ($this->config['smtp_port'] ?? 25));
        $timeout = max(1, (int) ($this->config['smtp_timeout'] ?? 10));
        $encryption = strtolower((string) ($this->config['smtp_encryption'] ?? ''));
        $username = (string) ($this->config['smtp_username'] ?? '');
        $password = (string) ($this->config['smtp_password'] ?? '');
        if ($host === '') {
            throw new \RuntimeException('SMTP host is empty.');
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            throw new \RuntimeException('SMTP connect failed: ' . ($errstr ?: (string) $errno));
        }

        stream_set_timeout($socket, $timeout);
        try {
            $this->expect($socket, [220]);
            $this->command($socket, 'EHLO pokemonchic.com', [250]);

            if ($encryption === 'tls' || $encryption === 'starttls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException('SMTP STARTTLS failed.');
                }
                $this->command($socket, 'EHLO pokemonchic.com', [250]);
            }

            if ($username !== '') {
                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode($username), [334]);
                $this->command($socket, base64_encode($password), [235]);
            }

            $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $message = $this->smtpMessage($to, $subject, $text, $fromAddress, $fromName);
            fwrite($socket, $this->dotStuff($message) . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }

        return true;
    }

    /**
     * @param resource $socket
     * @param list<int> $expected
     */
    private function command($socket, string $command, array $expected): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->expect($socket, $expected);
    }

    /**
     * @param resource $socket
     * @param list<int> $expected
     */
    private function expect($socket, array $expected): string
    {
        $response = '';
        while (($line = fgets($socket, 1024)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expected, true)) {
            throw new \RuntimeException('SMTP unexpected response: ' . trim($response));
        }

        return $response;
    }

    private function smtpMessage(string $to, string $subject, string $text, string $fromAddress, string $fromName): string
    {
        $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B');
        $encodedFromName = mb_encode_mimeheader($fromName, 'UTF-8', 'B');

        return implode("\r\n", [
            'Date: ' . date(DATE_RFC2822),
            'To: <' . $to . '>',
            'From: ' . $encodedFromName . ' <' . $fromAddress . '>',
            'Reply-To: ' . $fromAddress,
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: Pokemon 8.0',
            '',
            str_replace(["\r\n", "\r"], "\n", $text),
        ]);
    }

    private function dotStuff(string $message): string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $message));
        foreach ($lines as &$line) {
            if (str_starts_with($line, '.')) {
                $line = '.' . $line;
            }
        }
        unset($line);

        return implode("\r\n", $lines);
    }

    private function logDelivery(string $to, string $subject, string $status, string $error = '', string $transport = ''): void
    {
        if (!$this->db || !$this->tableExists('mail_delivery_logs')) {
            return;
        }
        if (!$this->settingBool('mail.delivery_log_enabled', true)) {
            return;
        }

        $now = time();
        $stmt = $this->db->prepare(
            'INSERT INTO mail_delivery_logs (recipient, subject, transport, status, error_message, meta_json, created_at, sent_at)
             VALUES (:recipient, :subject, :transport, :status, :error, :meta, :created_at, :sent_at)'
        );
        $stmt->execute([
            'recipient' => mb_substr($to, 0, 190),
            'subject' => mb_substr($subject, 0, 190),
            'transport' => mb_substr($transport !== '' ? $transport : (string) ($this->config['transport'] ?? 'mail'), 0, 24),
            'status' => mb_substr($status, 0, 24),
            'error' => mb_substr($error, 0, 255),
            'meta' => json_encode(['from' => $this->config['from_address'] ?? ''], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_at' => $now,
            'sent_at' => $status === 'success' ? $now : 0,
        ]);
    }

    private function tableExists(string $table): bool
    {
        static $cache = [];
        if (array_key_exists($table, $cache)) {
            return $cache[$table];
        }

        $stmt = $this->db?->query('SHOW TABLES LIKE ' . $this->db->quote($table));
        $cache[$table] = (bool) ($stmt ? $stmt->fetchColumn() : false);
        return $cache[$table];
    }

    private function settingBool(string $name, bool $default): bool
    {
        if (!$this->tableExists('site_settings')) {
            return $default;
        }

        $stmt = $this->db?->prepare('SELECT value FROM site_settings WHERE name = :name LIMIT 1');
        $stmt?->execute(['name' => $name]);
        $value = $stmt ? $stmt->fetchColumn() : false;
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return filter_var((string) $value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
