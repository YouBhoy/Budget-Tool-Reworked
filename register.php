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
			$success = 'Registration successful! You can now log in.';
		} else {
			$error = $msg;
		}
	}
}
?>

<div class="auth-container">
	<div class="auth-card" aria-labelledby="register-title">
		<h1 id="register-title" class="auth-title">Create Account</h1>
		<p class="auth-subtitle">Join BudgetFlix and start managing your finances</p>
		
		<?php if ($error): ?>
			<div class="error-message" role="alert">
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>
		
		<?php if ($success): ?>
			<div class="success-message" role="status">
				<?php echo htmlspecialchars($success); ?>
			</div>
		<?php endif; ?>
		
		<form method="post" action="register.php" id="registerForm">
			<?php echo bf_csrf_field(); ?>
			
			<div class="form-group">
				<label for="name">Full Name</label>
				<input id="name" name="name" type="text" required 
					   placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
			</div>
			
			<div class="form-group">
				<label for="email">Email Address</label>
				<input id="email" name="email" type="email" required autocomplete="username" 
					   placeholder="Enter your email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
			</div>
			
			<div class="form-group">
				<label for="password">Password</label>
				<input id="password" name="password" type="password" required autocomplete="new-password" 
					   placeholder="Create a strong password" minlength="8">
				<div class="password-requirements">
					Password must be at least 8 characters long
				</div>
				<div class="password-strength">
					<div class="password-strength-bar" id="passwordStrength"></div>
				</div>
			</div>
			
			<button class="auth-button" type="submit">Create Account</button>
		</form>
		
		<div class="auth-link">
			Already have an account? <a href="login.php">Sign in here</a>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const passwordInput = document.getElementById('password');
	const strengthBar = document.getElementById('passwordStrength');
	
	function checkPasswordStrength(password) {
		let strength = 0;
		if (password.length >= 8) strength++;
		if (password.match(/[a-z]/)) strength++;
		if (password.match(/[A-Z]/)) strength++;
		if (password.match(/[0-9]/)) strength++;
		if (password.match(/[^a-zA-Z0-9]/)) strength++;
		
		strengthBar.className = 'password-strength-bar';
		if (strength <= 1) {
			strengthBar.classList.add('password-strength-weak');
		} else if (strength <= 2) {
			strengthBar.classList.add('password-strength-fair');
		} else if (strength <= 3) {
			strengthBar.classList.add('password-strength-good');
		} else {
			strengthBar.classList.add('password-strength-strong');
		}
	}
	
	passwordInput.addEventListener('input', function() {
		checkPasswordStrength(this.value);
	});
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


