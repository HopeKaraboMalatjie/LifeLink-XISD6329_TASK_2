<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$pdo = db();

if (isset($_POST['appointment_id'], $_POST['status'])) {
    $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?")
        ->execute([$_POST['status'], (int)$_POST['appointment_id']]);
    $_SESSION['admin_flash'] = 'Appointment updated.';
    header('Location: appointments.php');
    exit;
}

$appointments = $pdo->query(
    "SELECT a.appointment_id, d.first_name, d.last_name, h.hospital_name, a.scheduled_date, a.scheduled_time, a.status
     FROM appointments a
     JOIN donors d ON d.donor_id = a.donor_id
     JOIN hospitals h ON h.hospital_id = a.hospital_id
     ORDER BY a.scheduled_date DESC, a.scheduled_time DESC"
)->fetchAll();

$statuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];

$pageTitle = 'Manage Appointments';
$active = 'appointments';
include __DIR__ . '/../includes/admin_header.php';
?>

  <div class="section">
    <h2>All Appointments (<?= count($appointments) ?>)</h2>
    <table>
      <tr><th>Donor</th><th>Centre</th><th>Date</th><th>Time</th><th>Status</th></tr>
      <?php foreach ($appointments as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['first_name'] . ' ' . $a['last_name']) ?></td>
          <td><?= htmlspecialchars($a['hospital_name']) ?></td>
          <td><?= htmlspecialchars($a['scheduled_date']) ?></td>
          <td><?= htmlspecialchars(substr($a['scheduled_time'], 0, 5)) ?></td>
          <td>
            <form action="appointments.php" method="post" style="display:flex; gap:6px;">
              <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
              <select name="status" style="padding:4px;">
                <?php foreach ($statuses as $s): ?>
                  <option value="<?= $s ?>" <?= $s === $a['status'] ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-outline" style="padding:4px 10px; font-size:12px;">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
