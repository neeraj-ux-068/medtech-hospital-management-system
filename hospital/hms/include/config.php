<?php
require_once __DIR__ . '/security.php';

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
date_default_timezone_set(getenv('HMS_TIMEZONE') ?: 'Asia/Kolkata');
set_exception_handler(static function (Throwable $exception): void {
    error_log('HMS unhandled exception: ' . $exception);
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'The application could not complete this request. Check the PHP error log for details.';
});

mysqli_report(MYSQLI_REPORT_OFF);
if (!defined('DB_SERVER')) {
    $hmsEnvironment = strtolower((string) (getenv('HMS_ENV') ?: 'development'));
    $isLocalDevelopment = in_array($hmsEnvironment, ['development', 'local', 'test'], true);
    $dbHost = getenv('HMS_DB_HOST');
    $dbUser = getenv('HMS_DB_USER');
    $dbPassword = getenv('HMS_DB_PASSWORD');
    $dbName = getenv('HMS_DB_NAME');

    // The XAMPP defaults are deliberately limited to local development. A
    // production environment must provide every database setting explicitly.
    if (!$isLocalDevelopment && (!$dbHost || !$dbUser || $dbPassword === false || !$dbName)) {
        error_log('HMS production database configuration is incomplete.');
        http_response_code(500);
        exit('The application is temporarily unavailable.');
    }

    define('DB_SERVER', $dbHost ?: 'localhost');
    define('DB_USER', $dbUser ?: 'root');
    define('DB_PASS', $dbPassword !== false ? $dbPassword : '');
    define('DB_NAME', $dbName ?: 'hms');
}

$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    error_log('HMS database connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    exit('The application is temporarily unavailable.');
}
if (!mysqli_set_charset($con, 'utf8mb4')) {
    error_log('HMS could not set the database connection character set.');
    http_response_code(500);
    exit('The application is temporarily unavailable.');
}
