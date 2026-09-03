<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$active = $active ?? '';
function anav($name, $active) { return $name === $active ? 'style="text-decoration:underline;"' : ''; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — LifeLink Admin' : 'LifeLink Admin' ?></title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header class="navbar">
    <div class="brand">🩸 LifeLink Admin</div>
    <nav>
      <a href="dashboard.php" <?= anav('dashboard', $active) ?>>Dashboard</a>
      <a href="donors.php" <?= anav('donors', $active) ?>>Donors</a>
      <a href="hospitals.php" <?= anav('hospitals', $active) ?>>Hospitals</a>
      <a href="appointments.php" <?= anav('appointments', $active) ?>>Appointments</a>
      <a href="alerts.php" <?= anav('alerts', $active) ?>>Alerts</a>
      <a href="../index.php">Public Site</a>
      <a href="logout.php">Logout</a>
    </nav>
  </header>
  <?php if (!empty($_SESSION['admin_flash'])): ?>
    <div style="max-width:1100px;margin:16px auto 0;padding:0 32px;">
      <div class="card" style="background:#eef7f0;border-color:#2f7a3d;"><?= htmlspecialchars($_SESSION['admin_flash']) ?></div>
    </div>
    <?php unset($_SESSION['admin_flash']); ?>
  <?php endif; ?>
