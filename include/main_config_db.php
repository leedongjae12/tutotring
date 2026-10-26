<?php
// Supply these variables through the web server/PHP-FPM environment.
$dbHost = getenv('DB_HOST');
$dbUser = getenv('DB_USER');
$dbPassword = getenv('DB_PASSWORD');
$dbName = getenv('DB_NAME');
$dbPort = getenv('DB_PORT') ?: '3306';

if (!$dbHost || !$dbUser || $dbPassword === false || !$dbName) {
    error_log('Database configuration missing: DB_HOST, DB_USER, DB_PASSWORD, DB_NAME are required.');
    http_response_code(503);
    exit('Database configuration is unavailable.');
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = mysqli_init();
    mysqli_options($db, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    mysqli_real_connect($db, $dbHost, $dbUser, $dbPassword, $dbName, (int) $dbPort);
    mysqli_set_charset($db, 'utf8');
    mysqli_autocommit($db, true);
    // Existing query code checks false and handles transactions explicitly.
    mysqli_report(MYSQLI_REPORT_OFF);
} catch (mysqli_sql_exception $exception) {
    error_log('Database connection failed (code ' . $exception->getCode() . ').');
    http_response_code(503);
    exit('Database service is unavailable.');
}
