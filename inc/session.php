<?php
require_once __DIR__ . '/../config/config.php';

function bf_start_session(): void {
	if (session_status() === PHP_SESSION_ACTIVE) return;
	$secure = bf_is_https();
	$cookieParams = [
		'lifetime' => SESSION_LIFETIME,
		'path' => '/',
		'domain' => '',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Strict',
	];
	if (PHP_VERSION_ID >= 70300) {
		session_set_cookie_params($cookieParams);
	} else {
		session_set_cookie_params(
			$cookieParams['lifetime'],
			$cookieParams['path'].'; samesite='.$cookieParams['samesite'],
			$cookieParams['domain'],
			$cookieParams['secure'],
			$cookieParams['httponly']
		);
	}
	session_name(SESSION_NAME);
	@session_start();
	if (!isset($_SESSION['initiated'])) {
		session_regenerate_id(true);
		$_SESSION['initiated'] = time();
	}
}

function bf_require_login(): void {
	bf_start_session();
	if (empty($_SESSION['user'])) {
		header('Location: login.php');
		exit;
	}
}

?>


