<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inventory_id'])) {
    $stmt = $pdo->prepare("UPDATE blood_inventory SET units_available = ? WHERE inventory_id = ?");
    $stmt->execute([(int)$_POST['units_available'], (int)$_POST['inventory_id']]);
    $_SESSION['admin_flash'] = 'Inventory updated.';
    header('Location: dashboard.php');
    exit;
}

$donorCount = (int)$pdo->query("SELECT COUNT(*) FROM donors WHERE is_active = 1")->fetchColumn();
$hospitalCount = (int)$pdo->query("SELECT COUNT(*) FROM hospitals WHERE is_approved = 1")->fetchColumn();
$apptToday = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE scheduled_date = CURDATE()")->fetchColumn();
$activeAlerts = (int)$pdo->query("SELECT COUNT(*) FROM alerts WHERE status = 'Active'")->fetchColumn();

$inventory = $pdo->query(
    "SELECT bi.inventory_id, h.hospital_name, bt.code, bi.units_available, bi.min_threshold
     FROM blood_inventory bi
     JOIN hospitals h ON h.hospital_id = bi.hospital_id
     JOIN blood_types bt ON bt.blood_type_id = bi.blood_type_id
     ORDER BY h.hospital_name, bt.blood_type_id"
)->fetchAll();

$pageTitle = 'Admin Dashboard';
$active = 'dashboard';
include __DIR__ . '/../includes/admin_header.php';
?>

  <div class="section">
    <h2>Overview</h2>
    <div class="stats">
      <div class="stat-card"><div class="num"><?= $donorCount ?></div><div class="label">Registered Donors</div></div>
      <div class="stat-card"><div class="num"><?= $hospitalCount ?></div><div class="label">Hospitals</div></div>
      <div class="stat-card"><div class="num"><?= $apptToday ?></div><div class="label">Appointments Today</div></div>
      <div class="stat-card"><div class="num"><?= $activeAlerts ?></div><div class="label">Active Alerts</div></div>
    </div>

    <h2>Blood Inventory</h2>
    <table>
      <tr><th>Centre</th><th>Type</th><th>Units Available</th><th>Status</th><th>Update</th></tr>
      <?php foreach ($inventory as $inv): $low = $inv['units_available'] <= $inv['min_threshold']; ?>
        <tr>
          <td><?= htmlspecialchars($inv['hospital_name']) ?></td>
          <td><?= htmlspecialchars($inv['code']) ?></td>
          <td><?= (int)$inv['units_available'] ?></td>
          <td><span class="badge <?= $low ? '' : 'badge-confirmed' ?>" style="<?= $low ? 'background:#fdecea;color:#b3141c;' : '' ?>"><?= $low ? 'Low' : 'Healthy' ?></span></td>
          <td>
            <form action="dashboard.php" method="post" style="display:flex; gap:6px;">
              <input type="hidden" name="inventory_id" value="<?= $inv['inventory_id'] ?>">
              <input type="number" name="units_available" value="<?= (int)$inv['units_available'] ?>" min="0" style="width:70px; padding:4px;">
              <button type="submit" class="btn btn-outline" style="padding:4px 10px; font-size:12px;">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
