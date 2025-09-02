<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!bf_verify_csrf()) {
		$error = 'Invalid CSRF token.';
	} else {
		list($ok, $msg) = bf_login_user($_POST['email'] ?? '', $_POST['password'] ?? '');
		if ($ok) {
			header('Location: /dashboard.php');
			exit;
		} else {
			$error = $msg;
		}
	}
}
?>

<section class="card" aria-labelledby="login-title">
	<h1 id="login-title">Login</h1>
	<?php if ($error): ?><p role="alert" style="color:#ff6b6b;">
		<?php echo htmlspecialchars($error); ?>
	</p><?php endif; ?>
	<form method="post" action="/login.php">
		<?php echo bf_csrf_field(); ?>
		<label for="email">Email</label>
		<input id="email" name="email" type="email" required autocomplete="username">
		<label for="password">Password</label>
		<input id="password" name="password" type="password" required autocomplete="current-password">
		<div style="margin-top:12px;">
			<button class="primary" type="submit">Login</button>
			<a href="/register.php" class="muted" style="margin-left:8px;">Create account</a>
		</div>
	</form>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


