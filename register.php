<?php
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/inc/auth.php';

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!bf_verify_csrf()) {
		$error = 'Invalid CSRF token.';
	} else {
		$name = trim($_POST['name'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$password = $_POST['password'] ?? '';
		list($ok, $msg) = bf_register_user($name, $email, $password);
		if ($ok) {
			$success = 'Registration successful. You can now log in.';
		} else {
			$error = $msg;
		}
	}
}
?>

<section class="card" aria-labelledby="register-title">
	<h1 id="register-title">Create Account</h1>
	<?php if ($error): ?><p role="alert" style="color:#ff6b6b;">
		<?php echo htmlspecialchars($error); ?>
	</p><?php endif; ?>
	<?php if ($success): ?><p role="status" style="color:#32d296;">
		<?php echo htmlspecialchars($success); ?>
	</p><?php endif; ?>
	<form method="post" action="/register.php">
		<?php echo bf_csrf_field(); ?>
		<label for="name">Full Name</label>
		<input id="name" name="name" type="text" required>
		<label for="email">Email</label>
		<input id="email" name="email" type="email" required autocomplete="username">
		<label for="password">Password</label>
		<input id="password" name="password" type="password" required autocomplete="new-password">
		<div style="margin-top:12px;">
			<button class="primary" type="submit">Create Account</button>
			<a href="/login.php" class="muted" style="margin-left:8px;">Already have an account?</a>
		</div>
	</form>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


