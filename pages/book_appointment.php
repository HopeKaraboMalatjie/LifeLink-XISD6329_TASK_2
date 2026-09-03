<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = db();
$donorId = $_SESSION['donor_id'];
$hospitals = $pdo->query("SELECT hospital_id, hospital_name FROM hospitals WHERE is_approved = 1 ORDER BY hospital_name")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospitalId = (int)($_POST['hospital_id'] ?? 0);
    $date = trim($_POST['scheduled_date'] ?? '');
    $time = trim($_POST['scheduled_time'] ?? '');
    $donationType = trim($_POST['donation_type'] ?? 'WholeBlood');
    $notes = trim($_POST['notes'] ?? '');

    if ($hospitalId < 1 || $date === '' || $time === '') {
        $error = 'Please select a centre, date and time.';
    } else {
        $insert = $pdo->prepare(
            "INSERT INTO appointments (donor_id, hospital_id, scheduled_date, scheduled_time, donation_type, status, notes)
             VALUES (?, ?, ?, ?, ?, 'Confirmed', ?)"
        );
        $insert->execute([$donorId, $hospitalId, $date, $time, $donationType ?: 'WholeBlood', $notes]);

        $hName = $pdo->prepare("SELECT hospital_name FROM hospitals WHERE hospital_id = ?");
        $hName->execute([$hospitalId]);
        $hospitalName = $hName->fetchColumn();

        $pdo->prepare("INSERT INTO notifications (donor_id, type, message) VALUES (?, 'Appointment', ?)")
            ->execute([$donorId, "Your appointment at {$hospitalName} on {$date} at {$time} has been confirmed."]);

        $_SESSION['flash'] = 'Appointment booked successfully!';
        header('Location: appointments.php');
        exit;
    }
}

$pageTitle = 'Book Appointment';
$active = 'appointments';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section" style="max-width:600px;">
    <h2>Book an Appointment</h2>
    <?php if ($error): ?>
      <div class="card" style="background:#fdecea;border-color:#b3141c;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="book_appointment.php" method="post">
      <div class="form-group">
        <label for="hospital_id">Select Donation Centre</label>
        <select id="hospital_id" name="hospital_id" required>
          <?php foreach ($hospitals as $h): ?>
            <option value="<?= $h['hospital_id'] ?>"><?= htmlspecialchars($h['hospital_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex; gap:16px;">
        <div class="form-group" style="flex:1;">
          <label for="scheduled_date">Date</label>
          <input type="date" id="scheduled_date" name="scheduled_date" min="<?= date('Y-m-d') ?>" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="scheduled_time">Time Slot</label>
          <select id="scheduled_time" name="scheduled_time">
            <option value="09:00">09:00</option><option value="10:00">10:00</option>
            <option value="11:00">11:00</option><option value="13:00">13:00</option>
            <option value="14:00">14:00</option><option value="15:00">15:00</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="donation_type">Donation Type</label>
        <select id="donation_type" name="donation_type">
          <option value="WholeBlood">Whole Blood</option>
          <option value="Plasma">Plasma</option>
        </select>
      </div>
      <div class="form-group">
        <label for="notes">Notes (optional)</label>
        <input type="text" id="notes" name="notes" placeholder="Any additional info">
      </div>
      <div class="map-placeholder">Map / Centre Location</div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Confirm Booking</button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
