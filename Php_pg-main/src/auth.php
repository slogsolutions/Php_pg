<?php
// src/auth.php
// Database-backed session auth with Admin and Employee roles

require_once __DIR__ . '/db.php'; // Assuming db.php handles the database connection

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Hardcoded Admin credentials (for initial login and setup)
const HARDCODED_ADMINS = [
    'surajslog' => 'surajslog',
    'kiranslog' => 'kiranslog',
];

function is_logged_in(): bool {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user(): ?array {
    return is_logged_in() ? $_SESSION['user'] : null;
}

function is_admin(): bool {
    $user = current_user();
    return $user && $user['role'] === 'admin';
}

function require_login(): void {
    if (!is_logged_in()) {
        // optional: remember where the user wanted to go
        $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '/index.php';
        header('Location: /login.php');
        exit;
    }
}

function try_login(string $username, string $password): bool {
    $username = trim($username);
    $password = trim($password);

    // 1. Check hardcoded admin users
    if (isset(HARDCODED_ADMINS[$username]) && HARDCODED_ADMINS[$username] === $password) {
        // Hardcoded admins are always 'admin' role
        $_SESSION['user'] = [
            'id' => 0, // Special ID for hardcoded admin, not stored in DB
            'username' => $username,
            'role' => 'admin',
        ];
        return true;
    }

    // 2. Check database users (employees and potential future admins)
    $db = db();
    $stmt = $db->prepare('SELECT id, username, password, role FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        return true;
    }

    return false;
}

function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Function to ensure the hardcoded admins exist in the database if they are to manage users
// This is a one-time setup function that can be called on first run or in a setup script.
// For this task, we will rely on the hardcoded login for the admin to create the first employee.
// The hardcoded admin (id=0) will not be able to create proposals, only manage users.
// New proposals must be created by database users (employees).
// To allow hardcoded admin to create proposals, we would need to insert them into the DB.
// Let's assume the hardcoded admin is only for user management and viewing all proposals.
// If the hardcoded admin creates a proposal, we'll assign it to a dummy user_id (e.g., 0) which is not ideal.
// The user request implies the hardcoded users are the 'ADMINS' and new users are 'employees'.
// Let's modify the try_login to use a special ID for hardcoded admins, and ensure all proposal creation requires a DB user ID.
// For simplicity and to meet the requirement, we will allow the hardcoded admin to *view* all proposals, but not *create* them.
// The hardcoded admin's ID will be 0 in the session.
