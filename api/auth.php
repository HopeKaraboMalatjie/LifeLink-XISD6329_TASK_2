<?php
// api/auth.php — donor login & registration for the Android app
// Matches Kotlin LoginResponse: {success, message, donor_id, first_name, blood_type, error}

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'login');
$pdo = db();

if ($action === 'login') {
    $body = json_input();
    $email = trim($body['email'] ?? '');
    $password = (string)($body['password'] ?? '');

    if ($email === '' || $password === '') {
        respond(['success' => false, 'message' => 'Email and password are required', 'error' => 'Email and password are required']);
    }

    $stmt = $pdo->prepare(
        "SELECT d.donor_id, d.first_name, d.password, d.is_active, bt.code AS blood_type
         FROM donors d JOIN blood_types bt ON bt.blood_type_id = d.blood_type_id
         WHERE d.email = ? LIMIT 1"
    );
    $stmt->execute([$email]);
    $donor = $stmt->fetch();

    if (!$donor || !password_verify($password, $donor['password'])) {
        respond(['success' => false, 'message' => 'Invalid email or password', 'error' => 'Invalid email or password']);
    }
    if ((int)$donor['is_active'] !== 1) {
        respond(['success' => false, 'message' => 'This account has been deactivated', 'error' => 'Account deactivated']);
    }

    respond([
        'success' => true,
        'message' => 'Login successful',
        'donor_id' => (int)$donor['donor_id'],
        'first_name' => $donor['first_name'],
        'blood_type' => $donor['blood_type'],
        'error' => null,
    ]);

} elseif ($action === 'register') {
    $body = json_input();
    $firstName = trim($body['first_name'] ?? '');
    $lastName  = trim($body['last_name'] ?? '');
    $email     = trim($body['email'] ?? '');
    $password  = (string)($body['password'] ?? '');
    $phone     = trim($body['phone'] ?? '');
    $dob       = trim($body['date_of_birth'] ?? '');
    $gender    = trim($body['gender'] ?? '');
    $address   = trim($body['address'] ?? '');
    $bloodTypeId = (int)($body['blood_type_id'] ?? 0);

    if ($firstName === '' || $lastName === '' || $email === '' || strlen($password) < 6 || $bloodTypeId < 1) {
        respond(['success' => false, 'message' => 'Please fill in all required fields (password min 6 characters)', 'error' => 'Validation failed']);
    }

    // Check for existing email
    $check = $pdo->prepare("SELECT donor_id FROM donors WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        respond(['success' => false, 'message' => 'An account with that email already exists', 'error' => 'Email already registered']);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare(
        "INSERT INTO donors (first_name, last_name, email, password, phone, date_of_birth, gender, address, blood_type_id, is_active)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
    );
    $insert->execute([$firstName, $lastName, $email, $hash, $phone, $dob ?: null, $gender, $address, $bloodTypeId]);
    $donorId = (int)$pdo->lastInsertId();

    $bt = $pdo->prepare("SELECT code FROM blood_types WHERE blood_type_id = ?");
    $bt->execute([$bloodTypeId]);
    $bloodType = $bt->fetchColumn() ?: '';

    // Welcome notification
    $pdo->prepare("INSERT INTO notifications (donor_id, type, message) VALUES (?, 'Welcome', 'Welcome to LifeLink! Thank you for registering as a donor.')")
        ->execute([$donorId]);

    respond([
        'success' => true,
        'message' => 'Registration successful',
        'donor_id' => $donorId,
        'first_name' => $firstName,
        'blood_type' => $bloodType,
        'error' => null,
    ]);

} else {
    api_error('Unknown action', 404);
}
