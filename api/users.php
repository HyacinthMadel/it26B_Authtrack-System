<?php
/**
 * AuthTrack Monitoring — Users CRUD API
 * api/users.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$role   = currentRole();

// ── LIST ──────────────────────────────────────────────────────
if ($action === 'list') {
    $search  = trim($_GET['search'] ?? '');
    $sortBy  = in_array($_GET['sort'] ?? '', ['name','email','role','created_at']) ? $_GET['sort'] : 'created_at';
    $sortDir = 'DESC';
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $limit   = 10;
    $offset  = ($page - 1) * $limit;

    $where  = '';
    $params = [];
    if ($search !== '') {
        $where  = "WHERE u.name LIKE :s OR u.email LIKE :s2";
        $params = [':s' => "%{$search}%", ':s2' => "%{$search}%"];
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u LEFT JOIN roles r ON u.role_id = r.id {$where}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, r.name AS role, u.role_id, u.created_at
        FROM users u
        LEFT JOIN roles r ON u.role_id = r.id
        {$where}
        ORDER BY {$sortBy} {$sortDir}
        LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'data'    => $stmt->fetchAll(),
        'total'   => $total,
        'page'    => $page,
        'pages'   => (int)ceil($total / $limit),
    ]);
    exit;
}

// ── GET single ────────────────────────────────────────────────
if ($action === 'get') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.role_id, r.name AS role, u.created_at
        FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();
    echo json_encode($user
        ? ['success' => true,  'data'    => $user]
        : ['success' => false, 'message' => 'User not found.']);
    exit;
}

// ── CREATE ────────────────────────────────────────────────────
if ($action === 'create') {
    if ($role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admin access required.']);
        exit;
    }
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleId   = (int)($_POST['role_id'] ?? 3);

    if (empty($name) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Name, email, and password are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }
    $chk = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $chk->execute([':email' => $email]);
    if ($chk->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already in use.']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)");
    $stmt->execute([':name' => $name, ':email' => $email, ':password' => $hash, ':role_id' => $roleId]);
    $newId = (int) $pdo->lastInsertId();
    logActivity($pdo, $_SESSION['user_id'], 'create_user', "Created user: {$email} (ID:{$newId})");

    echo json_encode(['success' => true, 'message' => 'User created successfully.', 'id' => $newId]);
    exit;
}

// ── UPDATE ────────────────────────────────────────────────────
if ($action === 'update') {
    if (!hasMinRole('editor')) {
        echo json_encode(['success' => false, 'message' => 'Editor access required.']);
        exit;
    }
    $id    = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    $chk = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $chk->execute([':email' => $email, ':id' => $id]);
    if ($chk->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already used by another account.']);
        exit;
    }

    $roleId = (int)($_POST['role_id'] ?? 0);
    if ($role === 'admin' && $roleId > 0) {
        $stmt = $pdo->prepare("UPDATE users SET name=:name, email=:email, role_id=:role_id WHERE id=:id");
        $stmt->execute([':name'=>$name,':email'=>$email,':role_id'=>$roleId,':id'=>$id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=:name, email=:email WHERE id=:id");
        $stmt->execute([':name'=>$name,':email'=>$email,':id'=>$id]);
    }

    $newPass = trim($_POST['password'] ?? '');
    if (!empty($newPass) && strlen($newPass) >= 6) {
        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare("UPDATE users SET password=:p WHERE id=:id")->execute([':p'=>$hash,':id'=>$id]);
    }

    logActivity($pdo, $_SESSION['user_id'], 'update_user', "Updated user ID:{$id}");
    echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    exit;
}

// ── DELETE ────────────────────────────────────────────────────
if ($action === 'delete') {
    if ($role !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Admin access required.']);
        exit;
    }
    $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
    if ($id === (int)$_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
        exit;
    }
    $emailStmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
    $emailStmt->execute([':id' => $id]);
    $target = $emailStmt->fetch();
    if (!$target) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    $pdo->prepare("DELETE FROM users WHERE id = :id")->execute([':id' => $id]);
    logActivity($pdo, $_SESSION['user_id'], 'delete_user', "Deleted user: {$target['email']} (ID:{$id})");
    echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
