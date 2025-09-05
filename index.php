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
				<div style="display: flex; gap: 12px; flex-wrap: wrap;">
					<a href="register.php" style="text-decoration: none;">
						<button class="primary" style="padding: 12px 24px; font-size: 16px; font-weight: 600;">
							Get Started Free
						</button>
					</a>
					<a href="login.php" style="text-decoration: none;">
						<button class="secondary" style="padding: 12px 24px; font-size: 16px; font-weight: 600;">
							Sign In
						</button>
					</a>
				</div>
			<?php else: ?>
				<a href="dashboard.php" style="text-decoration: none;">
					<button class="primary" style="padding: 12px 24px; font-size: 16px; font-weight: 600;">
						Go to Dashboard
					</button>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


