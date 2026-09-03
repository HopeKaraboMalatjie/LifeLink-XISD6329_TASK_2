<?php
require_once __DIR__ . '/../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare("SELECT admin_id, name, password FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = (int)$admin['admin_id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — LifeLink</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <header class="navbar">
    <div class="brand">🩸 LifeLink Admin</div>
    <nav><a href="../index.php">Public Site</a></nav>
  </header>

  <div class="form-wrap">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
      <div class="card" style="background:#fdecea;border-color:#b3141c;margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form action="login.php" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="admin@lifelink.co.za" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="form-footer" style="font-size:11px;">Demo account: admin@lifelink.co.za / Admin@123</div>
  </div>

  <footer>LifeLink Blood Donation &middot; XISD6329</footer>
  <script src="../assets/js/app.js"></script>
</body>
</html>
