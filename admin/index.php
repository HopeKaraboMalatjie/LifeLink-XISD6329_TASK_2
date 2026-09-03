<?php
// admin/index.php — entry point for /lifelink/admin/
// Sends logged-in admins to the dashboard, everyone else to the login page.
if (session_status() === PHP_SESSION_NONE) session_start();
header('Location: ' . (isset($_SESSION['admin_id']) ? 'dashboard.php' : 'login.php'));
exit;
