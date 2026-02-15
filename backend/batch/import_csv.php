<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__, 2);

$localDbConstPath = $rootDir . '/backend/conf/DBConst.local.php';
if (file_exists($localDbConstPath)) {
    require_once $localDbConstPath;
} else {
    require_once $rootDir . '/backend/conf/DBConst.php';
}

$csvPath = $rootDir . '/data/utf_ken_all.csv';
if (isset($argv[1]) && is_string($argv[1]) && $argv[1] !== '') {
    $csvPath = $argv[1];
}

$cliOptions = getopt('', ['host::', 'port::', 'db::', 'user::', 'pass::']);

if (!file_exists($csvPath)) {
    fwrite(STDERR, "CSV file not found: {$csvPath}\n");
    exit(1);
}

$host = isset($cliOptions['host']) ? (string)$cliOptions['host'] : ((string)(getenv('DB_HOST') ?: DBConst::$db_setting['db_host']));
$dbName = isset($cliOptions['db']) ? (string)$cliOptions['db'] : ((string)(getenv('DB_NAME') ?: DBConst::$db_setting['db_name']));
$user = isset($cliOptions['user']) ? (string)$cliOptions['user'] : ((string)(getenv('DB_USER') ?: DBConst::$db_setting['db_user']));
$password = isset($cliOptions['pass']) ? (string)$cliOptions['pass'] : ((string)(getenv('DB_PASS') ?: DBConst::$db_setting['db_pass']));
$port = isset($cliOptions['port']) ? (string)$cliOptions['port'] : ((string)(getenv('DB_PORT') ?: DBConst::$db_setting['db_port']));

$connectionCandidates = [
    ['host' => $host, 'port' => $port],
];

if ('db' === $host) {
    $connectionCandidates[] = ['host' => 'localhost', 'port' => '3307'];
    $connectionCandidates[] = ['host' => '127.0.0.1', 'port' => '3307'];
    $connectionCandidates[] = ['host' => 'localhost', 'port' => '3306'];
    $connectionCandidates[] = ['host' => '127.0.0.1', 'port' => '3306'];
}

$connectionCandidates = array_values(array_unique(array_map(static function (array $candidate): string {
    return $candidate['host'] . ':' . $candidate['port'];
}, $connectionCandidates)));

$pdo = null;
$lastConnectionError = null;
foreach ($connectionCandidates as $candidate) {
    [$candidateHost, $candidatePort] = explode(':', $candidate, 2);
    $dsn = "mysql:host={$candidateHost};dbname={$dbName};port={$candidatePort};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        break;
    } catch (Throwable $connectionError) {
        $lastConnectionError = $connectionError;
    }
}

if (!$pdo instanceof PDO) {
    $message = $lastConnectionError ? $lastConnectionError->getMessage() : 'Unknown DB connection error';
    fwrite(STDERR, "Import failed: {$message}\n");
    fwrite(STDERR, "Hint: php backend/batch/import_csv.php data/utf_ken_all.csv --host=localhost --port=3307\n");
    exit(1);
}

try {
    $pdo->beginTransaction();

    $createSql = <<<SQL
CREATE TABLE IF NOT EXISTS `postal_codes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `zip_code` CHAR(7) NOT NULL,
    `prefecture` VARCHAR(64) NOT NULL,
    `city` VARCHAR(128) NOT NULL,
    `town` VARCHAR(255) NOT NULL,
  `create_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `update_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_postal_codes_zip_code` (`zip_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;

    $pdo->exec($createSql);
        $pdo->exec('ALTER TABLE `postal_codes` MODIFY `prefecture` VARCHAR(64) NOT NULL, MODIFY `city` VARCHAR(128) NOT NULL, MODIFY `town` VARCHAR(255) NOT NULL');
    $pdo->exec('TRUNCATE TABLE `postal_codes`');

    $insertSql = 'INSERT INTO `postal_codes` (`zip_code`, `prefecture`, `city`, `town`) VALUES (:zip_code, :prefecture, :city, :town)';
    $insertStmt = $pdo->prepare($insertSql);

    $file = new SplFileObject($csvPath);
    $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

    $importCount = 0;

    foreach ($file as $row) {
        if (!is_array($row) || count($row) < 9) {
            continue;
        }

        $zipCode = preg_replace('/\D/u', '', (string)$row[2]);
        if ($zipCode === '' || strlen($zipCode) !== 7) {
            continue;
        }

        $prefecture = trim((string)$row[6]);
        $city = trim((string)$row[7]);
        $town = trim((string)$row[8]);

        $insertStmt->execute([
            ':zip_code' => $zipCode,
            ':prefecture' => $prefecture,
            ':city' => $city,
            ':town' => $town,
        ]);

        $importCount++;
    }

    $pdo->commit();

    fwrite(STDOUT, "Imported {$importCount} rows from {$csvPath}\n");
    exit(0);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    fwrite(STDERR, "Import failed: {$error->getMessage()}\n");
    exit(1);
}
