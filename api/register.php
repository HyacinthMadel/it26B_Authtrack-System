<?php
/**
 * AuthTrack Monitoring — Register API
 * api/register.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST method required.']);
    exit;
}

$name     = trim($_POST['name']     ?? '');
$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$confirm  = trim($_POST['confirm']  ?? '');

$errors = [];
if (empty($name))                               $errors[] = 'Name is required.';
if (strlen($name) > 100)                        $errors[] = 'Name must be under 100 characters.';
if (empty($email))                              $errors[] = 'Email is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
if (empty($password))                           $errors[] = 'Password is required.';
if (strlen($password) < 6)                      $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirm)                     $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, 3)");
$stmt->execute([':name' => $name, ':email' => $email, ':password' => $hashedPassword]);
$newUserId = (int) $pdo->lastInsertId();

logActivity($pdo, $newUserId, 'register', "New user registered: {$email}");

echo json_encode(['success' => true, 'message' => 'Account created successfully! You can now log in.']);
