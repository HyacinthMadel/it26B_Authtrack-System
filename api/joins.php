<?php
/**
 * AuthTrack Monitoring — SQL Joins Demo API
 * api/joins.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$type = $_GET['type'] ?? 'inner';

switch ($type) {

    case 'inner':
        $sql = "SELECT u.id, u.name, u.email, r.name AS role,
                COUNT(l.id) AS log_count, MAX(l.created_at) AS last_activity
                FROM users u
                INNER JOIN roles r ON u.role_id = r.id
                INNER JOIN activity_logs l ON l.user_id = u.id
                GROUP BY u.id, u.name, u.email, r.name
                ORDER BY log_count DESC";
        $description = 'INNER JOIN: Returns only users that have activity logs AND belong to a role. Users with zero logs are excluded.';
        break;

    case 'left':
        $sql = "SELECT u.id, u.name, u.email, r.name AS role,
                COUNT(l.id) AS log_count, MAX(l.created_at) AS last_activity
                FROM users u
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN activity_logs l ON l.user_id = u.id
                GROUP BY u.id, u.name, u.email, r.name
                ORDER BY u.name ASC";
        $description = 'LEFT JOIN: Returns ALL users, including those with zero activity logs. NULL values appear where no log exists.';
        break;

    case 'right':
        $sql = "SELECT r.id AS role_id, r.name AS role,
                COUNT(u.id) AS user_count,
                GROUP_CONCAT(u.name ORDER BY u.name SEPARATOR ', ') AS members
                FROM users u
                RIGHT JOIN roles r ON u.role_id = r.id
                GROUP BY r.id, r.name ORDER BY r.id ASC";
        $description = 'RIGHT JOIN: Returns ALL roles, even roles that have no users assigned. Useful to see empty/unused roles.';
        break;

    case 'full':
        $sql = "SELECT u.id AS user_id, u.name AS user_name, u.email,
                r.id AS role_id, r.name AS role_name, 'has_user' AS source
                FROM users u LEFT JOIN roles r ON u.role_id = r.id
                UNION
                SELECT u.id AS user_id, u.name AS user_name, u.email,
                r.id AS role_id, r.name AS role_name, 'has_role' AS source
                FROM users u RIGHT JOIN roles r ON u.role_id = r.id
                WHERE u.id IS NULL
                ORDER BY role_name, user_name";
        $description = 'FULL OUTER JOIN (simulated via UNION): MySQL does not natively support FULL OUTER JOIN. Combines LEFT + RIGHT JOIN to return ALL users and ALL roles, matching where possible.';
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid join type.']);
        exit;
}

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll();

echo json_encode([
    'success'     => true,
    'type'        => $type,
    'description' => $description,
    'sql'         => trim(preg_replace('/\s+/', ' ', $sql)),
    'data'        => $rows,
    'count'       => count($rows),
]);
