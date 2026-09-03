<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/admin_auth_check.php';

$pdo = db();
$hospitals = $pdo->query("SELECT hospital_id, hospital_name FROM hospitals ORDER BY hospital_name")->fetchAll();
$bloodTypes = $pdo->query("SELECT blood_type_id, code FROM blood_types ORDER BY blood_type_id")->fetchAll();

if (isset($_GET['resolve'])) {
    $pdo->prepare("UPDATE alerts SET status = 'Resolved' WHERE alert_id = ?")->execute([(int)$_GET['resolve']]);
    header('Location: alerts.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        "INSERT INTO alerts (hospital_id, blood_type_id, units_needed, urgency_level, status)
         VALUES (?, ?, ?, ?, 'Active')"
    );
    $stmt->execute([
        (int)$_POST['hospital_id'], (int)$_POST['blood_type_id'], (int)$_POST['units_needed'], $_POST['urgency_level'],
    ]);
    $_SESSION['admin_flash'] = 'Emergency alert created.';
    header('Location: alerts.php');
    exit;
}

$alerts = $pdo->query(
    "SELECT al.alert_id, h.hospital_name, bt.code, al.units_needed, al.urgency_level, al.status, al.alerted_at
     FROM alerts al JOIN hospitals h ON h.hospital_id = al.hospital_id JOIN blood_types bt ON bt.blood_type_id = al.blood_type_id
     ORDER BY al.status = 'Active' DESC, al.alerted_at DESC"
)->fetchAll();

$pageTitle = 'Manage Alerts';
$active = 'alerts';
include __DIR__ . '/../includes/admin_header.php';
?>

  <div class="section">
    <h2>Emergency Alerts</h2>
    <table>
      <tr><th>Centre</th><th>Type</th><th>Units</th><th>Urgency</th><th>Status</th><th></th></tr>
      <?php foreach ($alerts as $a): ?>
        <tr>
          <td><?= htmlspecialchars($a['hospital_name']) ?></td>
          <td><?= htmlspecialchars($a['code']) ?></td>
          <td><?= (int)$a['units_needed'] ?></td>
          <td><?= htmlspecialchars($a['urgency_level']) ?></td>
          <td><span class="badge <?= $a['status'] === 'Active' ? '' : 'badge-completed' ?>" style="<?= $a['status'] === 'Active' ? 'background:#fdecea;color:#b3141c;' : '' ?>"><?= htmlspecialchars($a['status']) ?></span></td>
          <td><?php if ($a['status'] === 'Active'): ?><a href="alerts.php?resolve=<?= $a['alert_id'] ?>">Resolve</a><?php else: ?>—<?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
    </table>

    <h2>Raise New Alert</h2>
    <form action="alerts.php" method="post" class="card">
      <div style="display:flex; gap:12px;">
        <div class="form-group" style="flex:1;">
          <label>Hospital</label>
          <select name="hospital_id" required>
            <?php foreach ($hospitals as $h): ?><option value="<?= $h['hospital_id'] ?>"><?= htmlspecialchars($h['hospital_name']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="flex:1;">
          <label>Blood Type</label>
          <select name="blood_type_id" required>
            <?php foreach ($bloodTypes as $bt): ?><option value="<?= $bt['blood_type_id'] ?>"><?= htmlspecialchars($bt['code']) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div style="display:flex; gap:12px;">
        <div class="form-group" style="flex:1;">
          <label>Units Needed</label>
          <input type="number" name="units_needed" min="1" value="1" required>
        </div>
        <div class="form-group" style="flex:1;">
          <label>Urgency</label>
          <select name="urgency_level">
            <option>Low</option><option>Medium</option><option>High</option><option>Critical</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Raise Alert</button>
    </form>
  </div>

<?php include __DIR__ . '/../includes/admin_footer.php'; ?>
