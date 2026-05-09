<?php
/**
 * AuthTrack Monitoring — Database Connection
 * includes/db.php
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'authtrack_monitoring');  // ← your database name
define('DB_USER',    'root');                  // ← change if needed
define('DB_PASS',    '');                      // ← change if needed
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Check your DB credentials in includes/db.php.'
    ]));
}
