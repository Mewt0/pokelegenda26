<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(2); }
require dirname(__DIR__) . '/src/Support/Autoload.php';
\Pokemon8\Support\Env::load(dirname(__DIR__) . '/.env');
$db = \Pokemon8\Database\Connection::make(require dirname(__DIR__) . '/config/database.php');
$admin = new \Pokemon8\Repository\AdminRepository($db);
$tables = [];
foreach (['items_users', 'admin_audit_log', 'admin_grant_receipts'] as $table) {
    $stmt = $db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :name');
    $stmt->execute(['name' => $table]);
    $engine = $stmt->fetchColumn();
    $tables[$table] = ['engine' => $engine ?: null, 'rows' => $engine ? (int) $db->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn() : null];
}
echo json_encode(['mode' => 'read_only', 'enabled' => $admin->setting('admin.grant.v2', '0') === '1',
    'tables' => $tables, 'transactional' => count(array_filter($tables, static fn ($row) => $row['engine'] === 'InnoDB')) === count($tables)], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
