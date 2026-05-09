<?php
/**
 * AuthTrack Monitoring — Auth Helper Functions
 * includes/auth.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: /pangit/login.php');
        exit;
    }
}

function requireRole(string $role): void {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        http_response_code(403);
        die('Access denied.');
    }
}

function hasMinRole(string $minRole): bool {
    $hierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
    $userLevel = $hierarchy[$_SESSION['role'] ?? 'viewer'] ?? 0;
    $minLevel  = $hierarchy[$minRole] ?? 99;
    return $userLevel >= $minLevel;
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentRole(): string {
    return $_SESSION['role'] ?? 'viewer';
}

function logActivity(PDO $pdo, ?int $userId, string $action, string $details = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $pdo->prepare("
        INSERT INTO activity_logs (user_id, action, details, ip_address)
        VALUES (:user_id, :action, :details, :ip)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':action'  => $action,
        ':details' => $details,
        ':ip'      => $ip,
    ]);
}

function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
