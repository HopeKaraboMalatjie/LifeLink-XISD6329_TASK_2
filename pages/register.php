<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pdo = db();
$bloodTypes = $pdo->query("SELECT blood_type_id, code FROM blood_types ORDER BY blood_type_id")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = (string)($_POST['password'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $dob       = trim($_POST['date_of_birth'] ?? '');
    $gender    = trim($_POST['gender'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $bloodTypeId = (int)($_POST['blood_type_id'] ?? 0);

    if ($firstName === '' || $lastName === '' || $email === '' || strlen($password) < 6 || $bloodTypeId < 1) {
        $error = 'Please fill in all required fields (password must be at least 6 characters).';
    } else {
        $check = $pdo->prepare("SELECT donor_id FROM donors WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $insert = $pdo->prepare(
                "INSERT INTO donors (first_name, last_name, email, password, phone, date_of_birth, gender, address, blood_type_id, is_active)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
            );
            $insert->execute([$firstName, $lastName, $email, $hash, $phone, $dob ?: null, $gender, $address, $bloodTypeId]);
            $donorId = (int)$pdo->lastInsertId();
            $pdo->prepare("INSERT INTO notifications (donor_id, type, message) VALUES (?, 'Welcome', 'Welcome to LifeLink! Thank you for registering as a donor.')")
                ->execute([$donorId]);

            $_SESSION['flash'] = 'Registration successful! Please log in.';
            header('Location: login.php');
            exit;
        }
    }
}

$pageTitle = 'Register';
$active = 'register';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="form-wrap" style="max-width:480px;">
    <h2>Become a Donor</h2>
    <?php if ($error): ?>
      <div class="card" style="background:#fdecea;border-color:#b3141c;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="register.php" method="post">
      <div style="display:flex;gap:12px;">
        <div class="form-group" style="flex:1;">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label for="password">Password (min 6 characters)</label>
        <input type="password" id="password" name="password" minlength="6" required>
      </div>
      <div style="display:flex;gap:12px;">
        <div class="form-group" style="flex:1;">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="form-group" style="flex:1;">
          <label for="date_of_birth">Date of Birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
        </div>
      </div>
      <div style="display:flex;gap:12px;">
        <div class="form-group" style="flex:1;">
          <label for="gender">Gender</label>
          <select id="gender" name="gender">
            <option>Male</option><option>Female</option><option>Other</option><option>Prefer not to say</option>
          </select>
        </div>
        <div class="form-group" style="flex:1;">
          <label for="blood_type_id">Blood Type</label>
          <select id="blood_type_id" name="blood_type_id" required>
            <option value="">Select…</option>
            <?php foreach ($bloodTypes as $bt): ?>
              <option value="<?= $bt['blood_type_id'] ?>"><?= htmlspecialchars($bt['code']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="address">Address</label>
        <input type="text" id="address" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
    </form>
    <div class="form-footer">Already registered? <a href="login.php">Login</a></div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
