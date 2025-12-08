<?php
define('POINT_PER_KG', 10);
define('SITE_NAME', 'ZeroWaste');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    define('DB_HOST', $dbParts['host'] ?? 'localhost');
    define('DB_NAME', ltrim($dbParts['path'] ?? '', '/'));
    define('DB_USER', $dbParts['user'] ?? '');
    define('DB_PASS', $dbParts['pass'] ?? '');
    define('DB_PORT', $dbParts['port'] ?? 5432);
    define('DB_TYPE', 'pgsql');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'zerowaste');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_PORT', 3306);
    define('DB_TYPE', 'mysql');
}

try {
    if (DB_TYPE === 'pgsql') {
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    } else {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }
    
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function getDB() {
    global $pdo;
    return $pdo;
}

function isPostgres() {
    return DB_TYPE === 'pgsql';
}

function dateInterval($days) {
    if (isPostgres()) {
        return "CURRENT_DATE - INTERVAL '{$days} days'";
    }
    return "DATE_SUB(CURDATE(), INTERVAL {$days} DAY)";
}

function toDate($column) {
    if (isPostgres()) {
        return "{$column}::date";
    }
    return "DATE({$column})";
}

function toYearMonth($column) {
    if (isPostgres()) {
        return "TO_CHAR({$column}, 'YYYY-MM')";
    }
    return "DATE_FORMAT({$column}, '%Y-%m')";
}
?>
