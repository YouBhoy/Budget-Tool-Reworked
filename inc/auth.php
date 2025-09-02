<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/session.php';

function bf_register_user(string $name, string $email, string $password): array {
	$email = strtolower(trim($email));
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		return [false, 'Invalid email'];
	}
	if (strlen($password) < 8) {
		return [false, 'Password must be at least 8 characters'];
	}
	$pdo = bf_get_pdo();
	$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
	$stmt->execute([$email]);
	if ($stmt->fetch()) {
		return [false, 'Email already registered'];
	}
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
	$stmt->execute([$name, $email, $hash]);
	return [true, 'Registration successful'];
}

function bf_login_user(string $email, string $password): array {
	$email = strtolower(trim($email));
	$pdo = bf_get_pdo();
	$stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
	$stmt->execute([$email]);
	$user = $stmt->fetch();
	if (!$user || !password_verify($password, $user['password_hash'])) {
		return [false, 'Invalid credentials'];
	}
	bf_start_session();
	$_SESSION['user'] = [
		'id' => (int)$user['id'],
		'name' => $user['name'],
		'email' => $user['email'],
	];
	session_regenerate_id(true);
	return [true, 'Login successful'];
}

function bf_logout_user(): void {
	bf_start_session();
	$_SESSION = [];
	if (ini_get('session.use_cookies')) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
	}
	session_destroy();
}

?>


