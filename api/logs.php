<?php
/**
 * AuthTrack Monitoring — Activity Logs API
 * api/logs.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$search = trim($_GET['search'] ?? '');
$filter = trim($_GET['filter'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$conditions = [];
$params     = [];

if ($search !== '') {
    $conditions[] = "(u.name LIKE :s OR u.email LIKE :s2 OR l.details LIKE :s3)";
    $params[':s']  = "%{$search}%";
    $params[':s2'] = "%{$search}%";
    $params[':s3'] = "%{$search}%";
}
if ($filter !== '') {
    $conditions[] = "l.action = :filter";
    $params[':filter'] = $filter;
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id {$where}");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT l.id, l.action, l.details, l.ip_address, l.created_at,
           COALESCE(u.name,  'Deleted User') AS user_name,
           COALESCE(u.email, 'N/A')          AS user_email,
           COALESCE(r.name,  'unknown')      AS user_role
    FROM activity_logs l
    LEFT JOIN users u ON l.user_id = u.id
    LEFT JOIN roles r ON u.role_id = r.id
    {$where}
    ORDER BY l.created_at DESC
    LIMIT :limit OFFSET :offset
");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$actionsStmt = $pdo->query("SELECT DISTINCT action FROM activity_logs ORDER BY action");
$actions = $actionsStmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'success' => true,
    'data'    => $logs,
    'total'   => $total,
    'page'    => $page,
    'pages'   => (int)ceil($total / $limit),
    'actions' => $actions,
]);
