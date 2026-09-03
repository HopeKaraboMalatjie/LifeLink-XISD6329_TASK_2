<?php
// api/notifications.php — donor notifications list & mark-all-read

require_once __DIR__ . '/_helpers.php';

$action = param('action', 'list');
$donorId = (int)param('donor_id', 0);
$pdo = db();

if ($donorId < 1) {
    api_error('donor_id is required');
}

if ($action === 'list') {
    $stmt = $pdo->prepare(
        "SELECT notification_id, donor_id, type, message, sent_at, is_read
         FROM notifications WHERE donor_id = ? ORDER BY sent_at DESC"
    );
    $stmt->execute([$donorId]);
    api_ok($stmt->fetchAll());

} elseif ($action === 'mark_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE donor_id = ?");
    $stmt->execute([$donorId]);
    api_ok(null, 'All notifications marked as read');

} else {
    api_error('Unknown action', 404);
}
