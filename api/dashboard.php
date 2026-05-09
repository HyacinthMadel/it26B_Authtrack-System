<?php
/**
 * AuthTrack Monitoring — Dashboard Stats API
 * api/dashboard.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLogs  = (int) $pdo->query("SELECT COUNT(*) FROM activity_logs")->fetchColumn();

$roleBreakdown = $pdo->query("
    SELECT r.name AS role, COUNT(u.id) AS count
    FROM roles r
    INNER JOIN users u ON u.role_id = r.id
    GROUP BY r.name ORDER BY count DESC
")->fetchAll();

$activityByDay = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS count
    FROM activity_logs
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at) ORDER BY day ASC
")->fetchAll();

$actionBreakdown = $pdo->query("
    SELECT action, COUNT(*) AS count
    FROM activity_logs
    GROUP BY action ORDER BY count DESC LIMIT 8
")->fetchAll();

$recentLogs = $pdo->query("
    SELECT l.id, l.action, l.details, l.ip_address, l.created_at,
           COALESCE(u.name,  'Deleted User') AS user_name,
           COALESCE(u.email, 'N/A')          AS user_email,
           COALESCE(r.name,  'unknown')      AS role
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    ORDER BY l.created_at DESC LIMIT 10
")->fetchAll();

$newestUser = $pdo->query("SELECT name, email, created_at FROM users ORDER BY created_at DESC LIMIT 1")->fetch();

echo json_encode([
    'success'         => true,
    'totalUsers'      => $totalUsers,
    'totalLogs'       => $totalLogs,
    'roleBreakdown'   => $roleBreakdown,
    'activityByDay'   => $activityByDay,
    'actionBreakdown' => $actionBreakdown,
    'recentLogs'      => $recentLogs,
    'newestUser'      => $newestUser,
]);
