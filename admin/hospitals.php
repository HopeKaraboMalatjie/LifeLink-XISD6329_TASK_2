<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        "INSERT INTO hospitals (hospital_name, address, contact_email, contact_phone, operating_hours, is_approved)
         VALUES (?, ?, ?, ?, ?, 1)"
    );
    $stmt->execute([
        trim($_POST['hospital_name']), trim($_POST['address']), trim($_POST['contact_email']),
        trim($_POST['contact_phone']), trim($_POST['operating_hours']),
    ]);
    $_SESSION['admin_flash'] = 'Hospital added.';
    header('Location: hospitals.php');
    exit;
}

$hospitals = $pdo->query("SELECT * FROM hospitals ORDER BY hospital_name")->fetchAll();

$pageTitle = 'Manage Hospitals';
$active = 'hospitals';
include __DIR__ . '/../includes/admin_header.php';
?>

  <div class="section">
    <h2>Hospitals / Donation Centres (<?= count($hospitals) ?>)</h2>
    <table>
      <tr><th>Name</th><th>Address</th><th>Phone</th><th>Hours</th></tr>
      <?php foreach ($hospitals as $h): ?>
        <tr>
          <td><?= htmlspecialchars($h['hospital_name']) ?></td>
          <td><?= htmlspecialchars($h['address']) ?></td>
          <td><?= htmlspecialchars($h['contact_phone']) ?></td>
          <td><?= htmlspecialchars($h['operating_hours']) ?></td>
        </tr>
      <?php endforeach; ?>
    </table>

    <h2>Add Hospital</h2>
    <form action="hospitals.php" method="post" class="card">
      <div class="form-group"><label>Name</label><input type="text" name="hospital_name" required></div>
      <div class="form-group"><label>Address</label><input type="text" name="address" required></div>
      <div style="display:flex; gap:12px;">
        <div class="form-group" style="flex:1;"><label>Contact Email</label><input type="email" name="contact_email"></div>
        <div class="form-group" style="flex:1;"><label>Contact Phone</label><input type="text" name="contact_phone"></div>
      </div>
      <div class="form-group"><label>Operating Hours</label><input type="text" name="operating_hours" placeholder="e.g. 08:00 - 17:00"></div>
      <button type="submit" class="btn btn-primary">Add Hospital</button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
