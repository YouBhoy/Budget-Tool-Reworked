<?php
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../config/config.php';
bf_start_session();
$user = $_SESSION['user'] ?? null;
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo htmlspecialchars(APP_NAME); ?></title>
	<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
	<header class="bf-header" role="banner">
		<div class="container">
			<div class="brand"><?php echo htmlspecialchars(APP_NAME); ?></div>
			<nav class="nav" role="navigation" aria-label="Primary">
				<a href="/index.php">Home</a>
				<?php if ($user): ?>
					<a href="/dashboard.php">Dashboard</a>
					<a href="/backup.php">Backup</a>
					<a href="/help.php">Help</a>
					<a href="/logout.php">Logout</a>
				<?php else: ?>
					<a href="/login.php">Login</a>
					<a href="/register.php" class="btn">Register</a>
				<?php endif; ?>
			</nav>
			<button id="themeToggle" class="toggle" aria-label="Toggle dark and light theme">🌓</button>
		</div>
	</header>
	<main class="bf-main container" role="main">

