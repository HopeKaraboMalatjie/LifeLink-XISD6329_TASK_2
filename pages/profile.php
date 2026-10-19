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

$pageTitle = 'My Profile & Settings';
$active = 'profile';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="section" style="max-width:550px;">
    <h2>My Profile</h2>
    <div class="card">
      <p><strong>Name:</strong> <?= htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($donor['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($donor['phone'] ?: '—') ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($donor['address'] ?: '—') ?></p>
      <p><strong>Blood Type:</strong> <span class="badge" style="background:#fdecea;color:#b3141c;font-size:13px;"><?= htmlspecialchars($donor['blood_type']) ?></span></p>
      <p><strong>Eligibility:</strong> <span class="badge <?= $eligible ? 'badge-confirmed' : 'badge-pending' ?>"><?= $eligible ? 'Eligible to donate' : 'Not yet eligible (56-day rule)' ?></span></p>
      <p><strong>Last Donation:</strong> <?= htmlspecialchars($donor['last_donation_date'] ?: 'No donations yet') ?></p>
      <p><strong>Member Since:</strong> <?= htmlspecialchars(date('M Y', strtotime($donor['created_at']))) ?></p>
    </div>

    <h2>⚙️ Preferences & Settings</h2>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span><strong>Push Notifications</strong></span>
        <input type="checkbox" checked style="width:20px;height:20px;cursor:pointer;">
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;">
        <span><strong>Dark Theme Mode</strong></span>
        <input type="checkbox" onclick="document.body.classList.toggle('dark-theme')" style="width:20px;height:20px;cursor:pointer;">
      </div>
    </div>

    <h2>❓ User Support & Help Center</h2>
    <div class="card">
      <details style="margin-bottom:10px;cursor:pointer;">
        <summary><strong>How do I book a blood donation appointment?</strong></summary>
        <p style="font-size:13px;color:var(--grey);margin-top:6px;">Go to the Appointments page, click "Book Appointment", choose your nearest hospital, date, and preferred time slot.</p>
      </details>
      <details style="margin-bottom:10px;cursor:pointer;">
        <summary><strong>Who is eligible to donate blood?</strong></summary>
        <p style="font-size:13px;color:var(--grey);margin-top:6px;">Donors must be aged 16–75, weigh over 50kg, be in good general health, and have waited at least 56 days since their last donation.</p>
      </details>
      <details style="cursor:pointer;">
        <summary><strong>Emergency Helpline & Support</strong></summary>
        <p style="font-size:13px;color:var(--grey);margin-top:6px;">LifeLink Support: 📞 0800 11 9031 | 📧 support@lifelink.co.za (24/7)</p>
      </details>
    </div>

    <a class="btn btn-outline" href="logout.php" style="width:100%;text-align:center;margin-top:10px;">🚪 Logout</a>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
