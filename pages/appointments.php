<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = db();
$donorId = $_SESSION['donor_id'];

if (isset($_GET['cancel'])) {
    $appointmentId = (int)$_GET['cancel'];
    $stmt = $pdo->prepare(
        "UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ? AND donor_id = ? AND status IN ('Confirmed','Pending')"
    );
    $stmt->execute([$appointmentId, $donorId]);
    $_SESSION['flash'] = $stmt->rowCount() ? 'Appointment cancelled.' : 'That appointment could not be cancelled.';
    header('Location: appointments.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT a.appointment_id, a.scheduled_date, a.scheduled_time, h.hospital_name, a.status
     FROM appointments a JOIN hospitals h ON h.hospital_id = a.hospital_id
     WHERE a.donor_id = ?
     ORDER BY a.scheduled_date DESC, a.scheduled_time DESC"
);
$stmt->execute([$donorId]);
$appointments = $stmt->fetchAll();

$badgeClass = ['Confirmed' => 'badge-confirmed', 'Pending' => 'badge-pending', 'Completed' => 'badge-completed', 'Cancelled' => 'badge-completed'];

$pageTitle = 'My Appointments';
$active = 'appointments';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section">
    <h2>My Appointments</h2>
    <?php if (empty($appointments)): ?>
      <p style="color:var(--grey);">You have no appointments yet.</p>
    <?php else: ?>
      <table>
        <tr><th>Date</th><th>Time</th><th>Centre</th><th>Status</th><th></th></tr>
        <?php foreach ($appointments as $a): ?>
          <tr>
            <td><?= htmlspecialchars($a['scheduled_date']) ?></td>
            <td><?= htmlspecialchars(substr($a['scheduled_time'], 0, 5)) ?></td>
            <td><?= htmlspecialchars($a['hospital_name']) ?></td>
            <td><span class="badge <?= $badgeClass[$a['status']] ?? 'badge-completed' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
            <td>
              <?php if (in_array($a['status'], ['Confirmed', 'Pending'])): ?>
                <a href="appointments.php?cancel=<?= $a['appointment_id'] ?>" onclick="return confirm('Cancel this appointment?');">Cancel</a>
              <?php else: ?>—<?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p style="margin-top:16px;"><a class="btn btn-primary" href="book_appointment.php">+ Book New Appointment</a></p>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
