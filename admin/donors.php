<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$pdo = db();

if (isset($_GET['toggle'])) {
    $donorId = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE donors SET is_active = 1 - is_active WHERE donor_id = ?")->execute([$donorId]);
    header('Location: donors.php');
    exit;
}

$donors = $pdo->query(
    "SELECT d.donor_id, d.first_name, d.last_name, d.email, bt.code, d.last_donation_date, d.is_active,
            (SELECT COUNT(*) FROM appointments a WHERE a.donor_id = d.donor_id AND a.status='Completed') AS donations
     FROM donors d JOIN blood_types bt ON bt.blood_type_id = d.blood_type_id
     ORDER BY d.created_at DESC"
)->fetchAll();

$pageTitle = 'Manage Donors';
$active = 'donors';
include __DIR__ . '/../includes/admin_header.php';
?>

  <div class="section">
    <h2>Registered Donors (<?= count($donors) ?>)</h2>
    <table>
      <tr><th>Name</th><th>Email</th><th>Type</th><th>Last Donation</th><th>Donations</th><th>Status</th><th></th></tr>
      <?php foreach ($donors as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['first_name'] . ' ' . $d['last_name']) ?></td>
          <td><?= htmlspecialchars($d['email']) ?></td>
          <td><?= htmlspecialchars($d['code']) ?></td>
          <td><?= htmlspecialchars($d['last_donation_date'] ?: '—') ?></td>
          <td><?= (int)$d['donations'] ?></td>
          <td><span class="badge <?= $d['is_active'] ? 'badge-confirmed' : 'badge-completed' ?>"><?= $d['is_active'] ? 'Active' : 'Deactivated' ?></span></td>
          <td><a href="donors.php?toggle=<?= $d['donor_id'] ?>" onclick="return confirm('Toggle this donor\'s active status?');"><?= $d['is_active'] ? 'Deactivate' : 'Reactivate' ?></a></td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
