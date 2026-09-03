<?php
// api/alerts.php — active emergency blood-request alerts

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'active');
$pdo = db();

if ($action === 'active') {
    $stmt = $pdo->query(
        "SELECT al.alert_id, al.hospital_id, h.hospital_name, bt.code AS full_type,
                al.units_needed, al.urgency_level, al.status, al.alerted_at
         FROM alerts al
         JOIN hospitals h ON h.hospital_id = al.hospital_id
         JOIN blood_types bt ON bt.blood_type_id = al.blood_type_id
         WHERE al.status = 'Active'
         ORDER BY FIELD(al.urgency_level, 'Critical','High','Medium','Low'), al.alerted_at DESC"
    );
    api_ok($stmt->fetchAll());

} else {
    api_error('Unknown action', 404);
}
