<?php
// api/appointments.php — list, book, and cancel donor appointments

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'list');
$pdo = db();

if ($action === 'list') {
    $donorId = (int)param('donor_id', 0);
    if ($donorId < 1) api_error('donor_id is required');

    $status = param('status');
    $sql = "SELECT a.appointment_id, a.donor_id, a.hospital_id, h.hospital_name, a.scheduled_date,
                   a.scheduled_time, a.donation_type, a.status, a.notes, a.created_at
            FROM appointments a JOIN hospitals h ON h.hospital_id = a.hospital_id
            WHERE a.donor_id = ?";
    $params = [$donorId];
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY a.scheduled_date DESC, a.scheduled_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    api_ok($stmt->fetchAll());

} elseif ($action === 'book') {
    $body = json_input();
    $donorId = (int)($body['donor_id'] ?? 0);
    $hospitalId = (int)($body['hospital_id'] ?? 0);
    $date = trim($body['scheduled_date'] ?? '');
    $time = trim($body['scheduled_time'] ?? '');
    $donationType = trim($body['donation_type'] ?? 'WholeBlood');
    $notes = trim($body['notes'] ?? '');

    if ($donorId < 1 || $hospitalId < 1 || $date === '' || $time === '') {
        api_error('donor_id, hospital_id, scheduled_date and scheduled_time are required');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO appointments (donor_id, hospital_id, scheduled_date, scheduled_time, donation_type, status, notes)
         VALUES (?, ?, ?, ?, ?, 'Confirmed', ?)"
    );
    $stmt->execute([$donorId, $hospitalId, $date, $time, $donationType ?: 'WholeBlood', $notes]);
    $appointmentId = (int)$pdo->lastInsertId();

    $hName = $pdo->prepare("SELECT hospital_name FROM hospitals WHERE hospital_id = ?");
    $hName->execute([$hospitalId]);
    $hospitalName = $hName->fetchColumn() ?: 'the selected centre';

    $pdo->prepare("INSERT INTO notifications (donor_id, type, message) VALUES (?, 'Appointment', ?)")
        ->execute([$donorId, "Your appointment at {$hospitalName} on {$date} at {$time} has been confirmed."]);

    api_ok(['appointment_id' => $appointmentId], 'Appointment booked successfully');

} elseif ($action === 'cancel') {
    $appointmentId = (int)param('appointment_id', 0);
    $donorId = (int)param('donor_id', 0);
    if ($appointmentId < 1 || $donorId < 1) {
        api_error('appointment_id and donor_id are required');
    }

    $stmt = $pdo->prepare(
        "UPDATE appointments SET status = 'Cancelled'
         WHERE appointment_id = ? AND donor_id = ? AND status IN ('Confirmed','Pending')"
    );
    $stmt->execute([$appointmentId, $donorId]);

    if ($stmt->rowCount() < 1) {
        api_error('Appointment not found or cannot be cancelled', 404);
    }
    api_ok(null, 'Appointment cancelled');

} else {
    api_error('Unknown action', 404);
}
