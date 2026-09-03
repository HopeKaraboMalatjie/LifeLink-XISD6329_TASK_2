<?php
// api/donor.php — donor profile & donation history
// Matches Kotlin Donor model field names exactly.

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'profile');
$donorId = (int)param('donor_id', 0);
$pdo = db();

if ($donorId < 1) {
    api_error('donor_id is required');
}

if ($action === 'profile') {
    $stmt = $pdo->prepare(
        "SELECT d.donor_id, d.first_name, d.last_name, d.email, d.phone, d.date_of_birth, d.gender,
                d.address, bt.code AS full_type, d.last_donation_date, d.is_active, d.created_at
         FROM donors d JOIN blood_types bt ON bt.blood_type_id = d.blood_type_id
         WHERE d.donor_id = ? LIMIT 1"
    );
    $stmt->execute([$donorId]);
    $donor = $stmt->fetch();

    if (!$donor) {
        api_error('Donor not found', 404);
    }

    // 56-day eligibility rule
    $isEligible = 1;
    if (!empty($donor['last_donation_date'])) {
        $days = (strtotime('today') - strtotime($donor['last_donation_date'])) / 86400;
        $isEligible = $days >= 56 ? 1 : 0;
    }

    api_ok([
        'donor_id' => (int)$donor['donor_id'],
        'first_name' => $donor['first_name'],
        'last_name' => $donor['last_name'],
        'email' => $donor['email'],
        'phone' => $donor['phone'],
        'date_of_birth' => $donor['date_of_birth'],
        'gender' => $donor['gender'],
        'address' => $donor['address'],
        'full_type' => $donor['full_type'],
        'last_donation_date' => $donor['last_donation_date'],
        'is_eligible' => $isEligible,
        'is_active' => (int)$donor['is_active'],
        'created_at' => $donor['created_at'],
    ]);

} elseif ($action === 'history') {
    $stmt = $pdo->prepare(
        "SELECT a.appointment_id, a.donor_id, a.hospital_id, h.hospital_name, a.scheduled_date,
                a.scheduled_time, a.donation_type, a.status, a.notes, a.created_at
         FROM appointments a JOIN hospitals h ON h.hospital_id = a.hospital_id
         WHERE a.donor_id = ? AND a.status = 'Completed'
         ORDER BY a.scheduled_date DESC"
    );
    $stmt->execute([$donorId]);
    api_ok($stmt->fetchAll());

} else {
    api_error('Unknown action', 404);
}
