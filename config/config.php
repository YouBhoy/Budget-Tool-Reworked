<?php
// BudgetFlix configuration

// Update these with your InfinityFree database credentials
define('DB_HOST', getenv('BF_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('BF_DB_NAME') ?: 'budgetflix');
define('DB_USER', getenv('BF_DB_USER') ?: 'root');
define('DB_PASS', getenv('BF_DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// App settings
define('APP_NAME', 'BudgetFlix');
define('APP_BASE_PATH', rtrim(str_replace('\\', '/', dirname(__DIR__)), '/'));

// Session cookie settings
define('SESSION_NAME', 'bf_session');
define('SESSION_LIFETIME', 60 * 60 * 4); // 4 hours

// CSRF token name
define('CSRF_TOKEN_KEY', 'bf_csrf_token');

// Enforce HTTPS cookies when available
function bf_is_https(): bool {
	if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
	if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
	return false;
}

?>


