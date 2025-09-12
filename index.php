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


<!-- Show user goals (read-only, no add/deduct/edit/delete) -->
<section class="card" aria-labelledby="goals-title" style="margin-top: 32px;">
	<h2 id="goals-title">Your Savings Goals</h2>
	<?php
		   require_once __DIR__ . '/config/db.php';
		   $pdo = bf_get_pdo();
		   $userId = $user['id'] ?? null;
		   if ($userId) {
			   $goalsStmt = $pdo->prepare('SELECT name, target_amount, current_amount, due_on FROM goals WHERE user_id = ? ORDER BY created_at DESC');
			   $goalsStmt->execute([$userId]);
			   $goals = $goalsStmt->fetchAll();
		   } else {
			   $goals = [];
		   }
	?>
	<?php if (empty($goals)): ?>
		<p class="muted">No goals yet. Create your first savings goal in your dashboard!</p>
	<?php else: ?>
		<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
			<?php foreach ($goals as $goal): ?>
				<?php 
					$progress = $goal['target_amount'] > 0 ? ($goal['current_amount'] / $goal['target_amount']) * 100 : 0;
					$progress = min(100, max(0, $progress));
					$isOverdue = $goal['due_on'] && $goal['due_on'] < date('Y-m-d') && $progress < 100;
				?>
				<div class="card" style="position: relative;">
					<div class="row" style="justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
						<h4 style="margin: 0; font-size: 1.1rem;">
							<?php echo htmlspecialchars($goal['name']); ?>
						</h4>
					</div>
					<div style="margin-bottom: 8px;">
						<div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 4px;">
							<span class="muted">Progress</span>
							<span class="<?php echo $progress >= 100 ? 'text-income' : 'text-expense'; ?>"><?php echo number_format($progress, 1); ?>%</span>
						</div>
						<div style="background: var(--border); height: 8px; border-radius: 4px; overflow: hidden;">
							<div style="background: <?php echo $progress >= 100 ? 'var(--income)' : 'var(--accent)'; ?>; height: 100%; width: <?php echo $progress; ?>%; transition: width 0.3s ease;"></div>
						</div>
					</div>
					<div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 8px;">
						<span class="muted">$<?php echo number_format($goal['current_amount'], 2); ?> of $<?php echo number_format($goal['target_amount'], 2); ?></span>
						<span class="muted">$<?php echo number_format($goal['target_amount'] - $goal['current_amount'], 2); ?> left</span>
					</div>
					<?php if ($goal['due_on']): ?>
						<div class="muted" style="font-size: 0.8rem;">
							Due: <?php echo date('M j, Y', strtotime($goal['due_on'])); ?>
							<?php if ($isOverdue): ?>
								<span class="text-expense">(Overdue)</span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


