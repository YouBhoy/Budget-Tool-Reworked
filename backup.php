<?php
require_once __DIR__ . '/inc/session.php';
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/config/db.php';
bf_require_login();

$pdo = bf_get_pdo();
$userId = (int)($_SESSION['user']['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Manual export of the current user's data to a simple SQL INSERT dump
	try {
		$tables = ['transactions', 'goals', 'recurring'];
		$dump = "-- BudgetFlix user export\n";
		$dump .= "-- User ID: {$userId}\n";
		$dump .= "-- Exported at: " . date('c') . "\n\n";
		foreach ($tables as $tbl) {
			$stmt = $pdo->prepare("SELECT * FROM {$tbl} WHERE user_id = ?");
			$stmt->execute([$userId]);
			$rows = $stmt->fetchAll();
			if (!$rows) continue;
			$cols = array_keys($rows[0]);
			$dump .= "-- Table: {$tbl}\n";
			foreach ($rows as $r) {
				$values = [];
				foreach ($cols as $c) {
					$values[] = isset($r[$c]) ? ($r[$c] === null ? 'NULL' : "'" . str_replace("'", "''", (string)$r[$c]) . "'") : 'NULL';
				}
				$dump .= "INSERT INTO `{$tbl}` (`" . implode('`,`', $cols) . "`) VALUES (" . implode(',', $values) . ");\n";
			}
			$dump .= "\n";
		}
		header('Content-Type: application/sql');
		header('Content-Disposition: attachment; filename="budgetflix_backup_'.date('Ymd_His').'_.sql"');
		echo $dump;
		exit;
	} catch (Throwable $e) {
		$error = 'Failed to export: ' . $e->getMessage();
	}
}
?>

<section class="card" aria-labelledby="backup-title">
	<h1 id="backup-title">Manual Backup</h1>
	<p>Click the button to download a SQL file containing your data (transactions, goals, recurring) for this account. You can import it later using phpMyAdmin.</p>
	<?php if ($error): ?><p role="alert" style="color:#ff6b6b;">
		<?php echo htmlspecialchars($error); ?>
	</p><?php endif; ?>
	<form method="post" action="/backup.php">
		<button class="primary" type="submit">Download Backup (.sql)</button>
	</form>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


