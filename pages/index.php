<?php
// pages/index.php — entry point for /lifelink/pages/
// Sends logged-in donors to the dashboard, everyone else to the login page.
if (session_status() === PHP_SESSION_NONE) session_start();
header('Location: ' . (isset($_SESSION['donor_id']) ? 'dashboard.php' : 'login.php'));
exit;
