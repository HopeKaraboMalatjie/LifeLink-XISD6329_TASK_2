<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$loggedIn = isset($_SESSION['donor_id']);
$active = $active ?? '';
function navclass($name, $active) { return $name === $active ? 'style="text-decoration:underline;"' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — LifeLink' : 'LifeLink' ?></title>
  <link rel="stylesheet" href="<?= $assetPath ?? '' ?>assets/css/style.css">
</head>
<body>
  <header class="navbar">
    <div class="brand">🩸 <a href="<?= $assetPath ?? '' ?>index.php" style="color:#fff;text-decoration:none;">LifeLink</a></div>
    <nav>
      <?php if ($loggedIn): ?>
        <a href="dashboard.php" <?= navclass('dashboard', $active) ?>>Dashboard</a>
        <a href="appointments.php" <?= navclass('appointments', $active) ?>>Appointments</a>
        <a href="hospitals.php" <?= navclass('hospitals', $active) ?>>Donation Centres</a>
        <a href="profile.php" <?= navclass('profile', $active) ?>>Profile</a>
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= $assetPath ?? '' ?>index.php" <?= navclass('home', $active) ?>>Home</a>
        <a href="<?= $assetPath ?? '' ?>pages/hospitals.php" <?= navclass('hospitals', $active) ?>>Donation Centres</a>
        <a href="<?= $assetPath ?? '' ?>pages/login.php" <?= navclass('login', $active) ?>>Login</a>
        <a href="<?= $assetPath ?? '' ?>pages/register.php" <?= navclass('register', $active) ?>>Register</a>
      <?php endif; ?>
    </nav>
  </header>
  <?php if (!empty($_SESSION['flash'])): ?>
    <div style="max-width:1100px;margin:16px auto 0;padding:0 32px;">
      <div class="card" style="background:#eef7f0;border-color:#2f7a3d;">
        <?= htmlspecialchars($_SESSION['flash']) ?>
      </div>
    </div>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div style="max-width:1100px;margin:16px auto 0;padding:0 32px;">
      <div class="card" style="background:#fdecea;border-color:#b3141c;">
        <?= htmlspecialchars($_SESSION['flash_error']) ?>
      </div>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>
