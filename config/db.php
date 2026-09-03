<?php
// LifeLink — shared database connection (XAMPP defaults)
// Update these if your MySQL setup differs.

define('DB_HOST', 'localhost');
define('DB_NAME', 'lifelink_db');
define('DB_USER', 'root');
define('DB_PASS', '');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    // IMPORTANT: with emulated prepares (PDO's default), MySQL returns every
                    // column as a string, which breaks the Android app's Gson parsing for
                    // Int/Double fields (e.g. donor_id, latitude). Native prepares fix this.
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_STRINGIFY_FETCHES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Database connection failed. Is MySQL running and lifelink_db imported?']);
            } else {
                echo "<h2>Database connection failed</h2><p>Is MySQL running in XAMPP, and have you imported <code>database/lifelink.sql</code>?</p><p>" . htmlspecialchars($e->getMessage()) . "</p>";
            }
            exit;
        }
    }
    return $pdo;
}
