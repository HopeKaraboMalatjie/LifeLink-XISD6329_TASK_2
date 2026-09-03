<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['donor_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare("SELECT donor_id, first_name, password, is_active FROM donors WHERE email = ?");
    $stmt->execute([$email]);
    $donor = $stmt->fetch();

    if ($donor && password_verify($password, $donor['password']) && (int)$donor['is_active'] === 1) {
        $_SESSION['donor_id'] = (int)$donor['donor_id'];
        $_SESSION['donor_name'] = $donor['first_name'];
        $_SESSION['flash'] = 'Welcome back, ' . $donor['first_name'] . '!';
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid email or password.';
}

$pageTitle = 'Login';
$active = 'login';
$assetPath = '../';
include __DIR__ . '/../includes/header.php';
?>

  <div class="form-wrap">
    <h2>Donor Login</h2>
    <?php if ($error): ?>
      <div class="card" style="background:#fdecea;border-color:#b3141c;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="form-footer">
      Don't have an account? <a href="register.php">Register</a><br>
      <span style="font-size:11px;">Demo account: donor@lifelink.co.za / Donor@123</span>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
