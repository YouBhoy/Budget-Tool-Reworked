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
			header('Location: dashboard.php');
			exit;
		} else {
			$error = $msg;
		}
	}
}
?>

<div class="auth-container">
	<div class="auth-card" aria-labelledby="login-title">
		<h1 id="login-title" class="auth-title">Welcome Back</h1>
		<p class="auth-subtitle">Sign in to your BudgetFlix account</p>
		
		<?php if ($error): ?>
			<div class="error-message" role="alert">
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>
		
		<form method="post" action="login.php" id="loginForm">
			<?php echo bf_csrf_field(); ?>
			
			<div class="form-group">
				<label for="email">Email Address</label>
				<input id="email" name="email" type="email" required autocomplete="username" 
					   placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
			</div>
			
			<div class="form-group">
				<label for="password">Password</label>
				<input id="password" name="password" type="password" required autocomplete="current-password" 
					   placeholder="Enter your password">
			</div>
			
			<button class="auth-button" type="submit">Sign In</button>
		</form>
		
		<div class="auth-link">
			Don't have an account? <a href="register.php">Create one here</a>
		</div>
	</div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


