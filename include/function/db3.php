<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$db = false;
$last_sql_log = '';

if (!defined('MYSQL_NOW')) {
    define('MYSQL_NOW', 'asd67kjk*(&86123');
}
if (!defined('MYSQL_INC')) {
    define('MYSQL_INC', 'asd6asd7kjk*(&86123');
}

function db($config = false): PDO
{
    global $db;

    if ($db instanceof PDO) {
        return $db;
    }

    if (!$config) {
        global $config;
    }

    $charset = $config['charset'] ?? 'utf8mb4';
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['server'],
        (int) ($config['port'] ?? 3306),
        $config['db'],
        $charset
    );

    try {
        $db = new PDO($dsn, (string) $config['user'], (string) $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        error_log('[DB] Connection failed: ' . $e->getMessage());
        exit('Not connect to SQL database');
    }

    return $db;
}

function db_quote(mixed $value): string
{
    global $config;
    return substr(db($config)->quote((string) $value), 1, -1);
}

function escapeArr(array $arr): array
{
    for ($i = 1, $n = count($arr); $i < $n; $i++) {
        if (is_string($arr[$i])) {
            $arr[$i] = db_quote($arr[$i]);
        }
    }

    return $arr;
}

function query($s)
{
    global $last_sql_log, $config;

    if (func_num_args() > 1) {
        $args = func_get_args();
        $s = call_user_func_array('sprintf', escapeArr($args));
    } elseif (is_array($s)) {
        $s = call_user_func_array('sprintf', escapeArr($s));
    }

    try {
        return db($config)->query((string) $s);
    } catch (PDOException $e) {
        $last_sql_log = '<div>Query: ' . app_e($s) . '</div><div style="color:red">Error: ' . app_e($e->getMessage()) . '</div>';
        error_log('[DB] Query failed: ' . (string) $s . ' :: ' . $e->getMessage());
        print $last_sql_log;
        exit;
    }
}

function select()
{
    $args = func_get_args();
    $res = call_user_func_array('query', $args);
    return $res instanceof PDOStatement ? $res->fetchAll(PDO::FETCH_ASSOC) : [];
}

function select_key($key, $items): array
{
    $list = [];
    foreach ($items as $item) {
        if (isset($item[$key])) {
            $list[$item[$key]] = $item;
        }
    }

    return $list;
}

function first()
{
    $args = func_get_args();
    $res = call_user_func_array('query', $args);
    $row = $res instanceof PDOStatement ? $res->fetch(PDO::FETCH_ASSOC) : false;
    return $row ?: false;
}

function db_identifier(string $identifier): string
{
    return '`' . str_replace('`', '', $identifier) . '`';
}

function insert($table, $query)
{
    global $config;

    $columns = [];
    $values = [];

    foreach ($query as $column => $value) {
        $columns[] = db_identifier((string) $column);
        $values[] = $value === MYSQL_NOW ? 'NOW()' : sprintf('"%s"', db_quote(trim((string) $value)));
    }

    $sql = 'INSERT INTO ' . db_identifier((string) $table)
        . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';

    if (query($sql)) {
        return db($config)->lastInsertId();
    }

    return false;
}

function update($table, $query, $where)
{
    $parts = [];

    foreach ($query as $column => $value) {
        $parts[] = sprintf('%s="%s"', db_identifier((string) $column), db_quote(trim((string) $value)));
    }

    return query('UPDATE ' . db_identifier((string) $table) . ' SET ' . implode(',', $parts) . ' WHERE ' . $where);
}

function delete($table, $where)
{
    return query('DELETE FROM ' . db_identifier((string) $table) . ' WHERE ' . $where);
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($string, $link_identifier = null): string
    {
        return db_quote($string);
    }
}

if (!function_exists('mysql_escape_string')) {
    function mysql_escape_string($string): string
    {
        return db_quote($string);
    }
}

if (!function_exists('mysql_connect')) {
    function mysql_connect($server = null, $username = null, $password = null)
    {
        global $config, $db;

        $db = false;
        if ($server !== null) {
            $config['server'] = (string) $server;
        }
        if ($username !== null) {
            $config['user'] = (string) $username;
        }
        if ($password !== null) {
            $config['pass'] = (string) $password;
        }

        return db($config);
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db($database_name, $link_identifier = null): bool
    {
        global $config, $db;

        $db = false;
        $config['db'] = (string) $database_name;
        db($config);

        return true;
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query, $link_identifier = null)
    {
        return query($query);
    }
}

if (!function_exists('mysql_fetch_assoc')) {
    function mysql_fetch_assoc($result)
    {
        return $result instanceof PDOStatement ? $result->fetch(PDO::FETCH_ASSOC) : false;
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result)
    {
        return $result instanceof PDOStatement ? $result->fetch(PDO::FETCH_BOTH) : false;
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link_identifier = null)
    {
        global $config;
        return db($config)->lastInsertId();
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error($link_identifier = null): string
    {
        global $db;
        if ($db instanceof PDO) {
            $error = $db->errorInfo();
            return $error[2] ?? '';
        }

        return '';
    }
}
