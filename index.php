<?php require_once __DIR__ . '/inc/header.php'; ?>

<section class="card" aria-labelledby="welcome-title">
	<h1 id="welcome-title">Welcome to BudgetFlix</h1>
	<p class="muted">Track income, expenses, goals — with a familiar, simple interface.</p>
	<div class="row">
		<div class="col">
			<h2>Features</h2>
			<ul class="list">
				<li>Secure login and registration</li>
				<li>Dashboard analytics</li>
				<li>Family and individual modes</li>
				<li>Recurring transactions and goals</li>
				<li>Dark/light theme</li>
				<li>Mobile-friendly design</li>
			</ul>
		</div>
		<div class="col">
			<?php if (empty($_SESSION['user'])): ?>
				<a class="btn" href="/register.php"><button class="primary">Create Account</button></a>
				<a class="btn" href="/login.php"><button class="secondary">Login</button></a>
			<?php else: ?>
				<a class="btn" href="/dashboard.php"><button class="primary">Go to Dashboard</button></a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


