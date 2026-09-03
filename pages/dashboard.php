<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = db();
$donorId = $_SESSION['donor_id'];

$stmt = $pdo->prepare(
    "SELECT d.first_name, bt.code AS blood_type, d.last_donation_date
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

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE donor_id = ? AND status = 'Completed'");
$countStmt->execute([$donorId]);
$donationCount = (int)$countStmt->fetchColumn();

$upcomingStmt = $pdo->prepare(
    "SELECT a.appointment_id, a.scheduled_date, a.scheduled_time, h.hospital_name, a.status
     FROM appointments a JOIN hospitals h ON h.hospital_id = a.hospital_id
     WHERE a.donor_id = ? AND a.status = 'Confirmed' AND a.scheduled_date >= CURDATE()
     ORDER BY a.scheduled_date ASC, a.scheduled_time ASC"
);
$upcomingStmt->execute([$donorId]);
$upcoming = $upcomingStmt->fetchAll();

$alertsStmt = $pdo->query(
    "SELECT al.units_needed, al.urgency_level, h.hospital_name, bt.code AS full_type
     FROM alerts al JOIN hospitals h ON h.hospital_id = al.hospital_id JOIN blood_types bt ON bt.blood_type_id = al.blood_type_id
     WHERE al.status = 'Active'
     ORDER BY FIELD(al.urgency_level,'Critical','High','Medium','Low') LIMIT 3"
);
$alerts = $alertsStmt->fetchAll();

$pageTitle = 'Dashboard';
$active = 'dashboard';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section">
    <div class="dash-welcome">
      <strong>Welcome, <?= htmlspecialchars($donor['first_name']) ?></strong> —
      Blood Type: <?= htmlspecialchars($donor['blood_type']) ?> |
      <?= $eligible ? 'Eligible to donate' : 'Not yet eligible (56-day rule)' ?>
    </div>

    <div class="stats">
      <div class="stat-card"><div class="num"><?= $donationCount ?></div><div class="label">Donations</div></div>
      <div class="stat-card"><div class="num"><?= count($upcoming) ?></div><div class="label">Upcoming</div></div>
      <div class="stat-card"><div class="num"><?= count($alerts) ?></div><div class="label">Active Alerts</div></div>
    </div>

    <h2>Emergency Alerts</h2>
    <?php if (empty($alerts)): ?>
      <p style="color:var(--grey);">No active emergency alerts right now.</p>
    <?php else: foreach ($alerts as $a): ?>
      <div class="card" style="border-color:#e0605a;">
        <span class="badge" style="background:#fdecea;color:#b3141c;"><?= strtoupper($a['urgency_level']) ?></span>
        <?= htmlspecialchars($a['full_type']) ?> blood needed at <?= htmlspecialchars($a['hospital_name']) ?> —
        <?= (int)$a['units_needed'] ?> units required
      </div>
    <?php endforeach; endif; ?>

    <h2>Upcoming Appointments</h2>
    <?php if (empty($upcoming)): ?>
      <p style="color:var(--grey);">No upcoming appointments.</p>
    <?php else: ?>
      <table>
        <tr><th>Date</th><th>Time</th><th>Centre</th><th>Status</th></tr>
        <?php foreach ($upcoming as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['scheduled_date']) ?></td>
            <td><?= htmlspecialchars(substr($u['scheduled_time'], 0, 5)) ?></td>
            <td><?= htmlspecialchars($u['hospital_name']) ?></td>
            <td><span class="badge badge-confirmed"><?= htmlspecialchars($u['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
    <p style="margin-top:16px;"><a class="btn btn-primary" href="book_appointment.php">Book New Appointment</a></p>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
