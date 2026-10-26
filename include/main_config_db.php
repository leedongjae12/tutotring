<?php
// Local PHP configuration avoids requiring changes to PHP-FPM's environment.
// Explicit environment variables take precedence over the local file.
$dbConfigFile = __DIR__ . '/main_config_db.local.php';
$dbConfig = is_file($dbConfigFile) ? require $dbConfigFile : [];
if (!is_array($dbConfig)) {
    $dbConfig = [];
}
$dbSetting = static function ($name, $default = false) use ($dbConfig) {
    $value = getenv($name);
    return $value !== false ? $value : ($dbConfig[$name] ?? $default);
};
$dbHost = $dbSetting('DB_HOST');
$dbUser = $dbSetting('DB_USER');
$dbPassword = $dbSetting('DB_PASSWORD');
$dbName = $dbSetting('DB_NAME');
$dbPort = $dbSetting('DB_PORT', '3306');

if (!$dbHost || !$dbUser || $dbPassword === false || !$dbName) {
    error_log('Database configuration missing: configure include/main_config_db.local.php or DB_HOST, DB_USER, DB_PASSWORD, DB_NAME.');
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
