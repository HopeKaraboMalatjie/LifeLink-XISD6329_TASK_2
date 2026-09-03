<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();
$hospitals = $pdo->query(
    "SELECT h.hospital_id, h.hospital_name, h.address, h.contact_phone, h.operating_hours,
            (SELECT GROUP_CONCAT(bt.code ORDER BY bt.blood_type_id SEPARATOR ', ')
               FROM blood_inventory bi JOIN blood_types bt ON bt.blood_type_id = bi.blood_type_id
               WHERE bi.hospital_id = h.hospital_id AND bi.units_available > 0) AS in_stock
     FROM hospitals h WHERE h.is_approved = 1 ORDER BY h.hospital_name"
)->fetchAll();

$pageTitle = 'Donation Centres';
$active = 'hospitals';
$assetPath = isset($_SESSION['donor_id']) ? '../' : '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section">
    <h2>Donation Centres</h2>
    <?php foreach ($hospitals as $h): ?>
      <div class="card">
        <strong><?= htmlspecialchars($h['hospital_name']) ?></strong><br>
        <?= htmlspecialchars($h['address']) ?> &middot; <?= htmlspecialchars($h['operating_hours']) ?> &middot; ☎ <?= htmlspecialchars($h['contact_phone']) ?><br>
        In stock: <?= $h['in_stock'] ? htmlspecialchars($h['in_stock']) : 'No current stock data' ?>
      </div>
    <?php endforeach; ?>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
