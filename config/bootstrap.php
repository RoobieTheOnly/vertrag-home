<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

date_default_timezone_set(
    getenv('APP_TIMEZONE') ?: 'Europe/Berlin'
);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

$secureCookie = filter_var(
    getenv('SESSION_SECURE_COOKIE') ?: 'false',
    FILTER_VALIDATE_BOOL
);

session_name(
    getenv('SESSION_NAME') ?: 'vertrag_home_session'
);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secureCookie,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Helpers/http.php';
require_once BASE_PATH . '/app/Helpers/csrf.php';
require_once BASE_PATH . '/app/Helpers/auth.php';
require_once BASE_PATH . '/app/Helpers/view.php';
require_once BASE_PATH . '/app/Helpers/contracts.php';
require_once BASE_PATH . '/app/Helpers/documents.php';
require_once BASE_PATH . '/app/Helpers/admin.php';
