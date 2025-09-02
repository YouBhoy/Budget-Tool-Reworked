<?php
require_once __DIR__ . '/inc/session.php';
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/config/db.php';
bf_require_login();

$pdo = bf_get_pdo();
$userId = (int)($_SESSION['user']['id'] ?? 0);

// Compute summary numbers
$stmt = $pdo->prepare('SELECT 
    SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) AS total_expense
  FROM transactions WHERE user_id = ?');
$stmt->execute([$userId]);
$sums = $stmt->fetch() ?: ['total_income' => 0, 'total_expense' => 0];
$income = (float)($sums['total_income'] ?? 0);
$expense = (float)($sums['total_expense'] ?? 0);
$savings = $income - $expense;

// Recent transactions
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
	$like = '%' . $q . '%';
	$txStmt = $pdo->prepare('SELECT id, occurred_on, type, category, amount, note, assigned_to 
		FROM transactions WHERE user_id = ? AND (category LIKE ? OR note LIKE ?) 
		ORDER BY occurred_on DESC, id DESC LIMIT 50');
	$txStmt->execute([$userId, $like, $like]);
} else {
	$txStmt = $pdo->prepare('SELECT id, occurred_on, type, category, amount, note, assigned_to 
		FROM transactions WHERE user_id = ? ORDER BY occurred_on DESC, id DESC LIMIT 50');
	$txStmt->execute([$userId]);
}
$transactions = $txStmt->fetchAll();

?>

<section class="grid" aria-label="Summary stats">
	<div class="card">
		<div class="muted">Income</div>
		<div class="stat">$<?php echo number_format($income, 2); ?></div>
	</div>
	<div class="card">
		<div class="muted">Expenses</div>
		<div class="stat">$<?php echo number_format($expense, 2); ?></div>
	</div>
	<div class="card">
		<div class="muted">Savings</div>
		<div class="stat">$<?php echo number_format($savings, 2); ?></div>
	</div>
</section>

<section class="card" aria-labelledby="search-title">
	<h2 id="search-title">Search transactions</h2>
	<form method="get" action="/dashboard.php" class="row">
		<input name="q" type="text" placeholder="Search category or note" value="<?php echo htmlspecialchars($q); ?>">
		<button class="secondary" type="submit">Search</button>
	</form>
</section>

<section class="card" aria-labelledby="recent-title">
	<h2 id="recent-title">Recent transactions</h2>
	<ul class="list">
		<?php foreach ($transactions as $t): ?>
			<li>
				<div class="row" style="align-items:center;">
					<div class="col" style="max-width:140px;">
						<?php echo htmlspecialchars($t['occurred_on']); ?>
					</div>
					<div class="col" style="max-width:120px;">
						<?php echo htmlspecialchars($t['type']); ?>
					</div>
					<div class="col">
						<?php echo htmlspecialchars($t['category']); ?>
					</div>
					<div class="col" style="max-width:140px; text-align:right;">
						$<?php echo number_format((float)$t['amount'], 2); ?>
					</div>
					<div class="col">
						<span class="muted"><?php echo htmlspecialchars((string)$t['assigned_to']); ?></span>
					</div>
				</div>
				<?php if (!empty($t['note'])): ?>
					<div class="muted">Note: <?php echo htmlspecialchars($t['note']); ?></div>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


