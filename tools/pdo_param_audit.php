<?php
declare(strict_types=1);

/**
 * Finds named PDO placeholders reused in a single statically-known prepare() query.
 *
 * Usage: php tools/pdo_param_audit.php [--json] [--path=src] [--include=<regex>] [--self-test]
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run from CLI.\n");
    exit(2);
}

try {
    $options = auditOptions($argv);
    if (isset($options['self-test'])) {
        runSelfTest();
        exit(0);
    }
    $root = resolveAuditPath((string) ($options['path'] ?? 'src'));
    $include = isset($options['include']) ? (string) $options['include'] : null;
    if ($include !== null && @preg_match($include, '') === false) {
        throw new InvalidArgumentException('Invalid --include regex. Include delimiters, for example --include="~Repository\\.php$~".');
    }

    $findings = [];
    $prepareChecked = 0;
    foreach (phpFiles($root, $include) as $file) {
        $source = file_get_contents($file);
        if ($source === false) {
            throw new RuntimeException('Unable to read: ' . $file);
        }
        [$checked, $fileFindings] = auditSource($source, displayPath($file));
        $prepareChecked += $checked;
        array_push($findings, ...$fileFindings);
    }

    $duplicateCount = count($findings);
    if (isset($options['json'])) {
        echo json_encode([
            'findings' => $findings,
            'prepare_checked' => $prepareChecked,
            'duplicates' => $duplicateCount,
            'summary' => "prepare_checked={$prepareChecked}, duplicates={$duplicateCount}",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    } else {
        foreach ($findings as $finding) {
            echo sprintf(
                "%s:%d parameter=:%s occurrences=%d\n",
                $finding['file'],
                $finding['line'],
                $finding['parameter'],
                $finding['occurrences']
            );
        }
        echo "prepare_checked={$prepareChecked}, duplicates={$duplicateCount}\n";
    }
    exit($duplicateCount === 0 ? 0 : 1);
} catch (Throwable $error) {
    fwrite(STDERR, 'PDO param audit error: ' . $error->getMessage() . PHP_EOL);
    fwrite(STDERR, "prepare_checked=0, duplicates=0\n");
    exit(2);
}

/** @return array<string,string|bool> */
function auditOptions(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--json') {
            $options['json'] = true;
            continue;
        }
        if ($argument === '--self-test') {
            $options['self-test'] = true;
            continue;
        }
        if (str_starts_with($argument, '--path=')) {
            $options['path'] = substr($argument, 7);
            continue;
        }
        if (str_starts_with($argument, '--include=')) {
            $options['include'] = substr($argument, 10);
            continue;
        }
        throw new InvalidArgumentException('Unknown argument: ' . $argument);
    }
    return $options;
}

function resolveAuditPath(string $path): string
{
    if ($path === '') {
        throw new InvalidArgumentException('--path must not be empty.');
    }
    $candidate = preg_match('~^(?:[A-Za-z]:[\\\\/]|/)~', $path) === 1
        ? $path
        : dirname(__DIR__) . DIRECTORY_SEPARATOR . $path;
    $resolved = realpath($candidate);
    if ($resolved === false || !is_dir($resolved)) {
        throw new InvalidArgumentException('Directory does not exist: ' . $path);
    }
    return $resolved;
}

/** @return Generator<int,string> */
function phpFiles(string $root, ?string $include): Generator
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );
    $files = [];
    foreach ($iterator as $entry) {
        if (!$entry->isFile() || strtolower($entry->getExtension()) !== 'php') {
            continue;
        }
        $path = $entry->getPathname();
        $matchPath = str_replace('\\', '/', $path);
        if ($include !== null && preg_match($include, $matchPath) !== 1) {
            continue;
        }
        $files[] = $path;
    }
    sort($files, SORT_STRING);
    yield from $files;
}

/**
 * @return array{0:int,1:list<array{file:string,line:int,parameter:string,occurrences:int}>}
 */
function auditSource(string $source, string $file): array
{
    $tokens = token_get_all($source);
    $checked = 0;
    $findings = [];
    $count = count($tokens);
    $variables = [];

    for ($i = 0; $i < $count; $i++) {
        if (is_array($tokens[$i]) && $tokens[$i][0] === T_VARIABLE) {
            $assign = nextSignificantToken($tokens, $i + 1);
            if ($assign !== null && $tokens[$assign] === '=') {
                [$assignmentTokens, $end] = expressionTokensUntil($tokens, $assign + 1, ';');
                $resolved = staticSqlExpression($assignmentTokens, $variables);
                if ($resolved === null) {
                    unset($variables[$tokens[$i][1]]);
                } else {
                    $variables[$tokens[$i][1]] = $resolved;
                }
                $i = $end;
            }
            continue;
        }

        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_STRING || strcasecmp($tokens[$i][1], 'prepare') !== 0) {
            continue;
        }
        $open = nextSignificantToken($tokens, $i + 1);
        if ($open === null || $tokens[$open] !== '(') {
            continue;
        }
        $prepareLine = $tokens[$i][2];
        [$argumentTokens, $end] = firstArgumentTokens($tokens, $open);
        $i = $end;
        $sql = staticSqlExpression($argumentTokens, $variables);
        if ($sql === null) {
            continue;
        }

        $checked++;
        $parameters = sqlParameters($sql);
        foreach (array_count_values($parameters) as $name => $occurrences) {
            if ($occurrences > 1) {
                $findings[] = [
                    'file' => $file,
                    'line' => $prepareLine,
                    'parameter' => (string) $name,
                    'occurrences' => $occurrences,
                ];
            }
        }
    }
    return [$checked, $findings];
}

/** @return array{0:list<array|string>,1:int} */
function expressionTokensUntil(array $tokens, int $start, string $terminator): array
{
    $expression = [];
    $depth = 0;
    $count = count($tokens);
    for ($i = $start; $i < $count; $i++) {
        $token = $tokens[$i];
        if (in_array($token, ['(', '[', '{'], true)) {
            $depth++;
        } elseif (in_array($token, [')', ']', '}'], true)) {
            $depth--;
        } elseif ($token === $terminator && $depth === 0) {
            return [$expression, $i];
        }
        $expression[] = $token;
    }
    return [$expression, $count - 1];
}

/** @return array{0:list<array|string>,1:int} */
function firstArgumentTokens(array $tokens, int $open): array
{
    $argument = [];
    $depth = 1;
    $count = count($tokens);
    for ($i = $open + 1; $i < $count; $i++) {
        $token = $tokens[$i];
        if ($token === '(') {
            $depth++;
        } elseif ($token === ')') {
            $depth--;
            if ($depth === 0) {
                return [$argument, $i];
            }
        } elseif ($token === ',' && $depth === 1) {
            return [$argument, $i];
        }
        $argument[] = $token;
    }
    return [$argument, $count - 1];
}

/** @param array<string,string> $variables */
function staticSqlExpression(array $tokens, array $variables): ?string
{
    $sql = '';
    $expectLiteral = true;
    for ($i = 0, $count = count($tokens); $i < $count; $i++) {
        $token = $tokens[$i];
        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        if ($expectLiteral && is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $sql .= decodePhpString($token[1]);
            $expectLiteral = false;
            continue;
        }
        if ($expectLiteral && is_array($token) && $token[0] === T_START_HEREDOC) {
            $heredoc = '';
            $closed = false;
            while (++$i < $count) {
                $part = $tokens[$i];
                if (is_array($part) && $part[0] === T_END_HEREDOC) {
                    $closed = true;
                    break;
                }
                if (!is_array($part) || $part[0] !== T_ENCAPSED_AND_WHITESPACE) {
                    return null; // Interpolated heredoc is not a static literal.
                }
                $heredoc .= $part[1];
            }
            if (!$closed) {
                return null;
            }
            $sql .= $heredoc;
            $expectLiteral = false;
            continue;
        }
        if ($expectLiteral && is_array($token) && $token[0] === T_VARIABLE && array_key_exists($token[1], $variables)) {
            $sql .= $variables[$token[1]];
            $expectLiteral = false;
            continue;
        }
        if (!$expectLiteral && $token === '.') {
            $expectLiteral = true;
            continue;
        }
        return null;
    }
    return $expectLiteral ? null : $sql;
}

function runSelfTest(): void
{
    $source = <<<'PHP'
<?php
$where = 'WHERE user_id = :user';
$suffix = ' AND owner_id = :user';
$db->prepare('SELECT * FROM table_a ' . $where . $suffix);
$db->prepare('SELECT * FROM table_b WHERE id = :id');
$db->prepare('SELECT * FROM table_c WHERE owner_id = :owner');
$dynamic = $_GET['sql'];
$db->prepare($dynamic . ' WHERE id = :id AND other_id = :id');
PHP;
    [$checked, $findings] = auditSource($source, 'self-test.php');
    $expected = [
        ['line' => 4, 'parameter' => 'user', 'occurrences' => 2],
    ];
    $actual = array_map(
        static fn (array $finding): array => [
            'line' => $finding['line'],
            'parameter' => $finding['parameter'],
            'occurrences' => $finding['occurrences'],
        ],
        $findings
    );
    if ($checked !== 3 || $actual !== $expected) {
        fwrite(STDERR, "Self-test failed.\n");
        fwrite(STDERR, json_encode(['checked' => $checked, 'findings' => $findings], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        exit(1);
    }
    echo "Self-test passed: variable concatenation duplicate detected; adjacent prepare() calls handled; dynamic SQL skipped.\n";
    echo "prepare_checked={$checked}, duplicates=" . count($findings) . "\n";
}

function decodePhpString(string $literal): string
{
    $quote = $literal[0];
    $body = substr($literal, 1, -1);
    if ($quote === "'") {
        return str_replace(["\\\\", "\\'"], ["\\", "'"], $body);
    }
    return stripcslashes($body);
}

/** @return list<string> */
function sqlParameters(string $sql): array
{
    $withoutStrings = preg_replace_callback(
        '~(?:\'(?:\'\'|\\\\.|[^\'])*\'|"(?:""|\\\\.|[^"])*")~s',
        static fn (array $match): string => str_repeat(' ', strlen($match[0])),
        $sql
    ) ?? $sql;
    preg_match_all('~(?<!:):([A-Za-z_][A-Za-z0-9_]*)~', $withoutStrings, $matches);
    return $matches[1];
}

function nextSignificantToken(array $tokens, int $start): ?int
{
    for ($i = $start, $count = count($tokens); $i < $count; $i++) {
        if (!is_array($tokens[$i]) || !in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            return $i;
        }
    }
    return null;
}

function tokenLine(array $tokens, int $index): int
{
    for ($i = $index; $i >= 0; $i--) {
        if (is_array($tokens[$i])) {
            return $tokens[$i][2];
        }
    }
    return 1;
}

function displayPath(string $file): string
{
    $project = str_replace('\\', '/', dirname(__DIR__)) . '/';
    $normalized = str_replace('\\', '/', $file);
    return str_starts_with($normalized, $project) ? substr($normalized, strlen($project)) : $normalized;
}
