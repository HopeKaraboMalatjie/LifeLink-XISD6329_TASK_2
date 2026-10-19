<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Home';
$active = 'home';
$assetPath = '';

$pdo = db();
$donorCount = (int)$pdo->query("SELECT COUNT(*) FROM donors WHERE is_active = 1")->fetchColumn();
$unitsDonated = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'Completed'")->fetchColumn();
$hospitalCount = (int)$pdo->query("SELECT COUNT(*) FROM hospitals WHERE is_approved = 1")->fetchColumn();
$activeAlerts = (int)$pdo->query("SELECT COUNT(*) FROM alerts WHERE status = 'Active'")->fetchColumn();

include __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <h1>Give Blood. Save Lives.</h1>
    <p>Register as a donor, book appointments, and help hospitals respond to emergency blood requests via our Web Portal & Android Mobile App.</p>
    <a class="btn btn-primary" href="pages/register.php">Register as Donor</a>
    <a class="btn btn-outline" href="pages/hospitals.php">Find a Donation Centre</a>
  </section>

  <section class="section">
    <h2>Our Impact</h2>
    <div class="stats">
      <div class="stat-card"><div class="num"><?= $donorCount ?></div><div class="label">Registered Donors</div></div>
      <div class="stat-card"><div class="num"><?= $unitsDonated ?></div><div class="label">Units Donated</div></div>
      <div class="stat-card"><div class="num"><?= $hospitalCount ?></div><div class="label">Partner Hospitals</div></div>
      <div class="stat-card"><div class="num"><?= $activeAlerts ?></div><div class="label">Active Emergency Alerts</div></div>
    </div>
  </section>

  <section class="section">
    <h2>📱 LifeLink Mobile App & Web Platform</h2>
    <div class="card" style="background:#f9fafb;border-left:4px solid var(--red);">
      <p><strong>Companion Android App Available:</strong> Access single sign-on (SSO), real-time emergency alert notifications, appointment management, and interactive user support directly on your Android phone.</p>
    </div>
  </section>

  <section class="section">
    <h2>Upcoming Blood Drives</h2>
    <div class="card"><strong>Pretoria Central Hospital</strong> — Sat, 20 Sept 2026, 08:00–14:00</div>
    <div class="card"><strong>Sunninghill Community Hall</strong> — Sat, 27 Sept 2026, 09:00–15:00</div>
    <div class="card"><strong>Steve Biko Academic Hospital</strong> — Everyday 24/7 Centre</div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
