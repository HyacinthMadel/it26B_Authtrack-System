<?php
/**
 * AuthTrack Monitoring — Auth API
 * api/auth.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// ── LOGIN ─────────────────────────────────────────────────────
if ($action === 'login') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.password, r.name AS role
        FROM users u
        INNER JOIN roles r ON u.role_id = r.id
        WHERE u.email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role'];

    logActivity($pdo, $user['id'], 'login', 'User logged in successfully.');

    echo json_encode([
        'success' => true,
        'message' => 'Login successful.',
        'user'    => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ]
    ]);
    exit;
}

// ── LOGOUT ────────────────────────────────────────────────────
if ($action === 'logout') {
    if (isLoggedIn()) {
        logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out.');
    }
    $_SESSION = [];
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out successfully.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action.']);
