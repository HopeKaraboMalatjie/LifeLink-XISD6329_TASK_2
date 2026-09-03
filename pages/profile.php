<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = db();
$donorId = $_SESSION['donor_id'];

$stmt = $pdo->prepare(
    "SELECT d.first_name, d.last_name, d.email, d.phone, d.address, bt.code AS blood_type,
            d.last_donation_date, d.created_at
     FROM donors d JOIN blood_types bt ON bt.blood_type_id = d.blood_type_id
     WHERE d.donor_id = ?"
);
$stmt->execute([$donorId]);
$donor = $stmt->fetch();

$eligible = true;
if (!empty($donor['last_donation_date'])) {
    $days = (strtotime('today') - strtotime($donor['last_donation_date'])) / 86400;
    $eligible = $days >= 56;
}

$pageTitle = 'My Profile';
$active = 'profile';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section" style="max-width:500px;">
    <h2>My Profile</h2>
    <div class="card">
      <p><strong>Name:</strong> <?= htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($donor['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($donor['phone'] ?: '—') ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($donor['address'] ?: '—') ?></p>
      <p><strong>Blood Type:</strong> <?= htmlspecialchars($donor['blood_type']) ?></p>
      <p><strong>Eligibility:</strong> <span class="badge <?= $eligible ? 'badge-confirmed' : 'badge-pending' ?>"><?= $eligible ? 'Eligible' : 'Not yet eligible' ?></span></p>
      <p><strong>Last Donation:</strong> <?= htmlspecialchars($donor['last_donation_date'] ?: 'No donations yet') ?></p>
      <p><strong>Member Since:</strong> <?= htmlspecialchars(date('M Y', strtotime($donor['created_at']))) ?></p>
    </div>
    <a class="btn btn-outline" href="logout.php">Logout</a>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
