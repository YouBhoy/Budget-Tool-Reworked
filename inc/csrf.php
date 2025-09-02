<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../config/config.php';

function bf_csrf_token(): string {
	bf_start_session();
	if (empty($_SESSION[CSRF_TOKEN_KEY])) {
		$_SESSION[CSRF_TOKEN_KEY] = bin2hex(random_bytes(32));
	}
	return $_SESSION[CSRF_TOKEN_KEY];
}

function bf_csrf_field(): string {
	$token = bf_csrf_token();
	return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function bf_verify_csrf(): bool {
	bf_start_session();
	$token = $_POST['csrf_token'] ?? '';
	return is_string($token) && hash_equals($_SESSION[CSRF_TOKEN_KEY] ?? '', $token);
}

?>


