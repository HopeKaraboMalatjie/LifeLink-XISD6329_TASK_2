<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['donor_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'sso') {
        $email = trim($_POST['email'] ?? 'donor@lifelink.co.za');
        $googleId = trim($_POST['google_id'] ?? 'google_sso_web_123');
        $firstName = trim($_POST['first_name'] ?? 'Thabo');
        $lastName = trim($_POST['last_name'] ?? 'Molefe');

        $stmt = $pdo->prepare("SELECT donor_id, first_name FROM donors WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $donor = $stmt->fetch();

        if (!$donor) {
            $hash = password_hash(bin2hex(random_bytes(8)), PASSWORD_BCRYPT);
            $insert = $pdo->prepare("INSERT INTO donors (first_name, last_name, email, password, google_id, blood_type_id, is_active) VALUES (?, ?, ?, ?, ?, 2, 1)");
            $insert->execute([$firstName, $lastName, $email, $hash, $googleId]);
            $donorId = (int)$pdo->lastInsertId();
            $donorName = $firstName;
        } else {
            $donorId = (int)$donor['donor_id'];
            $donorName = $donor['first_name'];
        }

        $_SESSION['donor_id'] = $donorId;
        $_SESSION['donor_name'] = $donorName;
        $_SESSION['flash'] = 'Welcome, ' . $donorName . '! Logged in via Google SSO.';
        header('Location: dashboard.php');
        exit;
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $stmt = $pdo->prepare("SELECT donor_id, first_name, password, is_active FROM donors WHERE email = ?");
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
      <input type="hidden" name="action" value="login">
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

    <div style="text-align:center;margin:16px 0;color:var(--grey);font-size:12px;">— OR —</div>

    <form action="login.php" method="post">
      <input type="hidden" name="action" value="sso">
      <input type="hidden" name="email" value="donor@lifelink.co.za">
      <input type="hidden" name="google_id" value="google_sso_web_101">
      <input type="hidden" name="first_name" value="Thabo">
      <input type="hidden" name="last_name" value="Molefe">
      <button type="submit" class="btn" style="width:100%;background:#fff;border:1px solid #ccc;color:#333;display:flex;align-items:center;justify-content:center;gap:8px;">
        <span>🌐 Continue with Google</span>
      </button>
    </form>

    <div class="form-footer">
      Don't have an account? <a href="register.php">Register</a><br>
      <span style="font-size:11px;">Demo account: donor@lifelink.co.za / Donor@123</span>
    </div>
  </div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
