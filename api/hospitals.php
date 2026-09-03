<?php
// api/hospitals.php — list donation centres with which blood types they currently stock

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'list');
$pdo = db();

if ($action === 'list') {
    $stmt = $pdo->query(
        "SELECT h.hospital_id, h.hospital_name, h.address, h.latitude, h.longitude,
                h.contact_email, h.contact_phone, h.operating_hours, h.is_approved,
                (SELECT GROUP_CONCAT(bt.code ORDER BY bt.blood_type_id SEPARATOR ', ')
                   FROM blood_inventory bi JOIN blood_types bt ON bt.blood_type_id = bi.blood_type_id
                   WHERE bi.hospital_id = h.hospital_id AND bi.units_available > 0) AS available_types
         FROM hospitals h
         WHERE h.is_approved = 1
         ORDER BY h.hospital_name"
    );
    $hospitals = $stmt->fetchAll();
    // DECIMAL columns (latitude/longitude) always come back from PDO as strings, even with
    // native prepares — cast explicitly so Gson can parse them into the app's Double fields.
    foreach ($hospitals as &$h) {
        $h['latitude'] = $h['latitude'] !== null ? (float)$h['latitude'] : null;
        $h['longitude'] = $h['longitude'] !== null ? (float)$h['longitude'] : null;
    }
    unset($h);
    api_ok($hospitals);

} elseif ($action === 'inventory') {
    $hospitalId = (int)param('hospital_id', 0);
    if ($hospitalId < 1) api_error('hospital_id is required');

    $stmt = $pdo->prepare(
        "SELECT bi.inventory_id, h.hospital_name, bt.code AS full_type, bi.units_available,
                bi.units_reserved, bi.min_threshold,
                CASE WHEN bi.units_available <= bi.min_threshold THEN 'Low' ELSE 'Healthy' END AS stock_status
         FROM blood_inventory bi
         JOIN hospitals h ON h.hospital_id = bi.hospital_id
         JOIN blood_types bt ON bt.blood_type_id = bi.blood_type_id
         WHERE bi.hospital_id = ?
         ORDER BY bt.blood_type_id"
    );
    $stmt->execute([$hospitalId]);
    api_ok($stmt->fetchAll());

} else {
    api_error('Unknown action', 404);
}
