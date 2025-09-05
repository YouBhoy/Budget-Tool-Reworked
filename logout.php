<?php
require_once __DIR__ . '/inc/auth.php';
bf_logout_user();
header('Location: index.php');
exit;


