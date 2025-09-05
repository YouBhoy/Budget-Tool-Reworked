<?php
require_once __DIR__ . '/inc/session.php';
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/csrf.php';
require_once __DIR__ . '/config/db.php';
bf_require_login();

$pdo = bf_get_pdo();
$userId = (int)($_SESSION['user']['id'] ?? 0);

// Handle create / update / delete
$notice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	if (!bf_verify_csrf()) {
		$notice = '<div class="error-message">Invalid CSRF token.</div>';
	} else if ($action === 'create' || $action === 'update') {
		$occurred_on = $_POST['occurred_on'] ?? '';
		$type = $_POST['type'] ?? '';
		$category = trim((string)($_POST['category'] ?? ''));
		$amount = (float)($_POST['amount'] ?? 0);
		$note = trim((string)($_POST['note'] ?? ''));
		$assigned_to = trim((string)($_POST['assigned_to'] ?? ''));
		$txId = isset($_POST['id']) ? (int)$_POST['id'] : 0;

		$valid = $occurred_on && in_array($type, ['income','expense'], true) && $category !== '' && $amount > 0;
		if (!$valid) {
			$notice = '<div class="error-message">Please provide date, type, category, and a positive amount.</div>';
		} else {
			if ($action === 'create') {
				$stmt = $pdo->prepare('INSERT INTO transactions (user_id, occurred_on, type, category, amount, note, assigned_to) VALUES (?, ?, ?, ?, ?, ?, ?)');
				$stmt->execute([$userId, $occurred_on, $type, $category, $amount, $note, $assigned_to]);
				$notice = '<div class="success-message">Transaction added.</div>';
			} else if ($action === 'update' && $txId > 0) {
				$stmt = $pdo->prepare('UPDATE transactions SET occurred_on = ?, type = ?, category = ?, amount = ?, note = ?, assigned_to = ? WHERE id = ? AND user_id = ?');
				$stmt->execute([$occurred_on, $type, $category, $amount, $note, $assigned_to, $txId, $userId]);
				$notice = '<div class="success-message">Transaction updated.</div>';
			}
		}
	} else if ($action === 'delete') {
		$txId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
		if ($txId > 0) {
			$stmt = $pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
			$stmt->execute([$txId, $userId]);
			$notice = '<div class="success-message">Transaction deleted.</div>';
		}
	} else if ($action === 'create_goal') {
		$name = trim((string)($_POST['goal_name'] ?? ''));
		$target_amount = (float)($_POST['target_amount'] ?? 0);
		$due_on = $_POST['due_on'] ?? '';
		
		if ($name !== '' && $target_amount > 0) {
			$stmt = $pdo->prepare('INSERT INTO goals (user_id, name, target_amount, due_on) VALUES (?, ?, ?, ?)');
			$stmt->execute([$userId, $name, $target_amount, $due_on ?: null]);
			$notice = '<div class="success-message">Goal created.</div>';
		} else {
			$notice = '<div class="error-message">Please provide goal name and target amount.</div>';
		}
	} else if ($action === 'update_goal') {
		$goalId = isset($_POST['goal_id']) ? (int)$_POST['goal_id'] : 0;
		$name = trim((string)($_POST['goal_name'] ?? ''));
		$target_amount = (float)($_POST['target_amount'] ?? 0);
		$current_amount = (float)($_POST['current_amount'] ?? 0);
		$due_on = $_POST['due_on'] ?? '';
		
		if ($goalId > 0 && $name !== '' && $target_amount > 0) {
			$stmt = $pdo->prepare('UPDATE goals SET name = ?, target_amount = ?, current_amount = ?, due_on = ? WHERE id = ? AND user_id = ?');
			$stmt->execute([$name, $target_amount, $current_amount, $due_on ?: null, $goalId, $userId]);
			$notice = '<div class="success-message">Goal updated.</div>';
		}
	} else if ($action === 'delete_goal') {
		$goalId = isset($_POST['goal_id']) ? (int)$_POST['goal_id'] : 0;
		if ($goalId > 0) {
			$stmt = $pdo->prepare('DELETE FROM goals WHERE id = ? AND user_id = ?');
			$stmt->execute([$goalId, $userId]);
			$notice = '<div class="success-message">Goal deleted.</div>';
		}
	} else if ($action === 'add_money') {
		$goalId = isset($_POST['goal_id']) ? (int)$_POST['goal_id'] : 0;
		$amount = (float)($_POST['amount'] ?? 0);
		
		if ($goalId > 0 && $amount > 0) {
			$stmt = $pdo->prepare('UPDATE goals SET current_amount = current_amount + ? WHERE id = ? AND user_id = ?');
			$stmt->execute([$amount, $goalId, $userId]);
			$notice = '<div class="success-message">Added $' . number_format($amount, 2) . ' to your goal!</div>';
		} else {
			$notice = '<div class="error-message">Please enter a valid amount.</div>';
		}
	} else if ($action === 'deduct_money') {
		$goalId = isset($_POST['goal_id']) ? (int)$_POST['goal_id'] : 0;
		$amount = (float)($_POST['amount'] ?? 0);
		
		if ($goalId > 0 && $amount > 0) {
			// Check if deducting would make current_amount negative
			$stmt = $pdo->prepare('SELECT current_amount FROM goals WHERE id = ? AND user_id = ?');
			$stmt->execute([$goalId, $userId]);
			$current = $stmt->fetchColumn();
			
			if ($current !== false && $amount <= $current) {
				$stmt = $pdo->prepare('UPDATE goals SET current_amount = current_amount - ? WHERE id = ? AND user_id = ?');
				$stmt->execute([$amount, $goalId, $userId]);
				$notice = '<div class="success-message">Deducted $' . number_format($amount, 2) . ' from your goal.</div>';
			} else {
				$notice = '<div class="error-message">Cannot deduct more than current amount ($' . number_format($current, 2) . ').</div>';
			}
		} else {
			$notice = '<div class="error-message">Please enter a valid amount.</div>';
		}
	}
}

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

// Load goals
$goalsStmt = $pdo->prepare('SELECT id, name, target_amount, current_amount, due_on FROM goals WHERE user_id = ? ORDER BY created_at DESC');
$goalsStmt->execute([$userId]);
$goals = $goalsStmt->fetchAll();

?>

<?php echo $notice; ?>

<section class="card" aria-labelledby="tabs-title">
	<h2 id="tabs-title">Manage Your Finances</h2>
	<div class="row" style="margin-bottom: 20px; border-bottom: 1px solid var(--border);">
		<button id="tab-transactions" class="tab-button active" onclick="switchTab('transactions')" style="background: none; border: none; padding: 12px 16px; border-bottom: 2px solid var(--accent); color: var(--accent); font-weight: 600; cursor: pointer;">Transactions</button>
		<button id="tab-goals" class="tab-button" onclick="switchTab('goals')" style="background: none; border: none; padding: 12px 16px; border-bottom: 2px solid transparent; color: var(--muted); font-weight: 600; cursor: pointer;">Goals</button>
	</div>
	
	<div id="transactions-tab" class="tab-content">
		<div class="card" aria-labelledby="add-title">
			<h3 id="add-title">Add transaction</h3>
			<form method="post" action="dashboard.php" class="row">
				<?php echo bf_csrf_field(); ?>
				<input type="hidden" name="action" value="create">
				<div class="col" style="max-width:150px;">
					<label for="occurred_on">Date</label>
					<input id="occurred_on" name="occurred_on" type="date" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" required>
				</div>
				<div class="col" style="max-width:150px;">
					<label for="type">Type</label>
					<select id="type" name="type" required>
						<option value="income">Income</option>
						<option value="expense">Expense</option>
					</select>
				</div>
				<div class="col">
					<label for="category">Category</label>
					<input id="category" name="category" type="text" placeholder="e.g., Salary, Groceries" required>
				</div>
				<div class="col" style="max-width:180px;">
					<label for="amount">Amount</label>
					<input id="amount" name="amount" type="number" step="0.01" min="0.01" placeholder="0.00" required>
				</div>
				<div class="col">
					<label for="assigned_to">Assigned to</label>
					<input id="assigned_to" name="assigned_to" type="text" placeholder="Self / Family member">
				</div>
				<div class="col" style="min-width:100%;">
					<label for="note">Note</label>
					<input id="note" name="note" type="text" placeholder="Optional note">
				</div>
				<div class="col" style="max-width:160px; align-self:flex-end;">
					<button class="primary" type="submit">Add</button>
				</div>
			</form>
		</div>
	</div>
	
	<div id="goals-tab" class="tab-content" style="display: none;">
		<div class="card" aria-labelledby="goals-title">
			<h3 id="goals-title">Savings Goals</h3>
			<div class="row" style="margin-bottom: 20px;">
				<div class="col">
					<button class="secondary" type="button" onclick="document.getElementById('add-goal-form').style.display='block'">+ Add Goal</button>
				</div>
			</div>
			
			<div id="add-goal-form" class="card" style="display:none; margin-bottom: 16px;">
				<h4 style="margin-top:0;">Create New Goal</h4>
				<form method="post" action="dashboard.php" class="row">
					<?php echo bf_csrf_field(); ?>
					<input type="hidden" name="action" value="create_goal">
					<div class="col">
						<label for="goal_name">Goal Name</label>
						<input id="goal_name" name="goal_name" type="text" placeholder="e.g., Emergency Fund, Vacation" required>
					</div>
					<div class="col" style="max-width:180px;">
						<label for="target_amount">Target Amount</label>
						<input id="target_amount" name="target_amount" type="number" step="0.01" min="0.01" placeholder="0.00" required>
					</div>
					<div class="col" style="max-width:150px;">
						<label for="due_on">Due Date (optional)</label>
						<input id="due_on" name="due_on" type="date">
					</div>
					<div class="col" style="max-width:160px; align-self:flex-end;">
						<button class="primary" type="submit">Create Goal</button>
					</div>
				</form>
			</div>
			
			<?php if (empty($goals)): ?>
				<p class="muted">No goals yet. Create your first savings goal above!</p>
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
								<h4 style="margin: 0; font-size: 1.1rem;"><?php echo htmlspecialchars($goal['name']); ?></h4>
								<div style="display: flex; gap: 4px; flex-wrap: wrap;">
									<button class="secondary" type="button" onclick="document.getElementById('add-money-<?php echo (int)$goal['id']; ?>').style.display='block'" style="padding: 4px 8px; font-size: 0.8rem; background: var(--income); color: #052e1a; border: 1px solid var(--income);">+ Add Money</button>
									<button class="secondary" type="button" onclick="document.getElementById('deduct-money-<?php echo (int)$goal['id']; ?>').style.display='block'" style="padding: 4px 8px; font-size: 0.8rem; background: var(--expense); color: #fff; border: 1px solid var(--expense);">- Deduct Money</button>
									<button class="secondary" type="button" onclick="document.getElementById('edit-goal-<?php echo (int)$goal['id']; ?>').style.display='block'" style="padding: 4px 8px; font-size: 0.8rem;">Edit</button>
									<form method="post" action="dashboard.php" style="display:inline;">
										<?php echo bf_csrf_field(); ?>
										<input type="hidden" name="action" value="delete_goal">
										<input type="hidden" name="goal_id" value="<?php echo (int)$goal['id']; ?>">
										<button class="secondary" type="submit" onclick="return confirm('Delete this goal?');" style="padding: 4px 8px; font-size: 0.8rem;">Delete</button>
									</form>
								</div>
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
							
							<div id="add-money-<?php echo (int)$goal['id']; ?>" class="card" style="display:none; margin-top:12px; background: var(--bg); border: 1px solid var(--income);">
								<h5 style="margin-top:0; color: var(--income);">Add Money to Goal</h5>
								<form method="post" action="dashboard.php" class="row">
									<?php echo bf_csrf_field(); ?>
									<input type="hidden" name="action" value="add_money">
									<input type="hidden" name="goal_id" value="<?php echo (int)$goal['id']; ?>">
									<div class="col" style="max-width:180px;">
										<label>Amount to Add</label>
										<input name="amount" type="number" step="0.01" min="0.01" placeholder="0.00" required style="border-color: var(--income);">
									</div>
									<div class="col" style="max-width:160px; align-self:flex-end;">
										<button class="primary" type="submit" style="background: var(--income); color: #052e1a; border: 1px solid var(--income);">Add Money</button>
									</div>
									<div class="col" style="max-width:100px; align-self:flex-end;">
										<button class="secondary" type="button" onclick="document.getElementById('add-money-<?php echo (int)$goal['id']; ?>').style.display='none'" style="padding: 8px 12px;">Cancel</button>
									</div>
								</form>
							</div>
							
							<div id="deduct-money-<?php echo (int)$goal['id']; ?>" class="card" style="display:none; margin-top:12px; background: var(--bg); border: 1px solid var(--expense);">
								<h5 style="margin-top:0; color: var(--expense);">Deduct Money from Goal</h5>
								<form method="post" action="dashboard.php" class="row">
									<?php echo bf_csrf_field(); ?>
									<input type="hidden" name="action" value="deduct_money">
									<input type="hidden" name="goal_id" value="<?php echo (int)$goal['id']; ?>">
									<div class="col" style="max-width:180px;">
										<label>Amount to Deduct</label>
										<input name="amount" type="number" step="0.01" min="0.01" max="<?php echo $goal['current_amount']; ?>" placeholder="0.00" required style="border-color: var(--expense);">
										<small class="muted">Max: $<?php echo number_format($goal['current_amount'], 2); ?></small>
									</div>
									<div class="col" style="max-width:160px; align-self:flex-end;">
										<button class="primary" type="submit" style="background: var(--expense); color: #fff; border: 1px solid var(--expense);">Deduct Money</button>
									</div>
									<div class="col" style="max-width:100px; align-self:flex-end;">
										<button class="secondary" type="button" onclick="document.getElementById('deduct-money-<?php echo (int)$goal['id']; ?>').style.display='none'" style="padding: 8px 12px;">Cancel</button>
									</div>
								</form>
							</div>
							
							<div id="edit-goal-<?php echo (int)$goal['id']; ?>" class="card" style="display:none; margin-top:12px; background: var(--bg);">
								<h5 style="margin-top:0;">Edit Goal</h5>
								<form method="post" action="dashboard.php" class="row">
									<?php echo bf_csrf_field(); ?>
									<input type="hidden" name="action" value="update_goal">
									<input type="hidden" name="goal_id" value="<?php echo (int)$goal['id']; ?>">
									<div class="col">
										<label>Goal Name</label>
										<input name="goal_name" type="text" value="<?php echo htmlspecialchars($goal['name']); ?>" required>
									</div>
									<div class="col" style="max-width:180px;">
										<label>Target Amount</label>
										<input name="target_amount" type="number" step="0.01" min="0.01" value="<?php echo htmlspecialchars($goal['target_amount']); ?>" required>
									</div>
									<div class="col" style="max-width:180px;">
										<label>Current Amount</label>
										<input name="current_amount" type="number" step="0.01" min="0" value="<?php echo htmlspecialchars($goal['current_amount']); ?>">
									</div>
									<div class="col" style="max-width:150px;">
										<label>Due Date</label>
										<input name="due_on" type="date" value="<?php echo htmlspecialchars($goal['due_on'] ?: ''); ?>">
									</div>
									<div class="col" style="max-width:160px; align-self:flex-end;">
										<button class="primary" type="submit">Save</button>
									</div>
								</form>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="grid" aria-label="Summary stats">
	<div class="card">
		<div class="muted">Income</div>
		<div class="stat text-income">$<?php echo number_format($income, 2); ?></div>
	</div>
	<div class="card">
		<div class="muted">Expenses</div>
		<div class="stat text-expense">$<?php echo number_format($expense, 2); ?></div>
	</div>
	<div class="card">
		<div class="muted">Savings</div>
		<div class="stat <?php echo $savings >= 0 ? 'text-income' : 'text-expense'; ?>">$<?php echo number_format($savings, 2); ?></div>
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
						<span class="pill <?php echo $t['type'] === 'income' ? 'badge-income' : 'badge-expense'; ?>">
							<?php echo htmlspecialchars($t['type']); ?>
						</span>
					</div>
					<div class="col">
						<?php echo htmlspecialchars($t['category']); ?>
					</div>
					<div class="col" style="max-width:140px; text-align:right;">
						<span class="<?php echo $t['type'] === 'income' ? 'text-income' : 'text-expense'; ?>">$<?php echo number_format((float)$t['amount'], 2); ?></span>
					</div>
					<div class="col">
						<span class="muted"><?php echo htmlspecialchars((string)$t['assigned_to']); ?></span>
					</div>
				</div>
				<?php if (!empty($t['note'])): ?>
					<div class="muted">Note: <?php echo htmlspecialchars($t['note']); ?></div>
				<?php endif; ?>
				<div class="row" style="gap:8px; margin-top:8px;">
					<form method="post" action="dashboard.php" style="display:inline;">
						<?php echo bf_csrf_field(); ?>
						<input type="hidden" name="action" value="delete">
						<input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
						<button class="secondary" type="submit" onclick="return confirm('Delete this transaction?');">Delete</button>
					</form>
					<button class="secondary" type="button" onclick="document.getElementById('edit-<?php echo (int)$t['id']; ?>').style.display='block'">Edit</button>
				</div>
				<div id="edit-<?php echo (int)$t['id']; ?>" class="card" style="display:none; margin-top:12px;">
					<h3 style="margin-top:0;">Edit transaction</h3>
					<form method="post" action="dashboard.php" class="row">
						<?php echo bf_csrf_field(); ?>
						<input type="hidden" name="action" value="update">
						<input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
						<div class="col" style="max-width:150px;">
							<label>Date</label>
							<input name="occurred_on" type="date" value="<?php echo htmlspecialchars($t['occurred_on']); ?>" required>
						</div>
						<div class="col" style="max-width:150px;">
							<label>Type</label>
							<select name="type" required>
								<option value="income" <?php echo $t['type']==='income'?'selected':''; ?>>Income</option>
								<option value="expense" <?php echo $t['type']==='expense'?'selected':''; ?>>Expense</option>
							</select>
						</div>
						<div class="col">
							<label>Category</label>
							<input name="category" type="text" value="<?php echo htmlspecialchars($t['category']); ?>" required>
						</div>
						<div class="col" style="max-width:180px;">
							<label>Amount</label>
							<input name="amount" type="number" step="0.01" min="0.01" value="<?php echo htmlspecialchars($t['amount']); ?>" required>
						</div>
						<div class="col">
							<label>Assigned to</label>
							<input name="assigned_to" type="text" value="<?php echo htmlspecialchars((string)$t['assigned_to']); ?>">
						</div>
						<div class="col" style="min-width:100%;">
							<label>Note</label>
							<input name="note" type="text" value="<?php echo htmlspecialchars((string)$t['note']); ?>">
						</div>
						<div class="col" style="max-width:160px; align-self:flex-end;">
							<button class="primary" type="submit">Save</button>
						</div>
					</form>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


