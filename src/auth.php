<?php
// src/auth.php
// Simple session auth using two fixed users (from your React login)

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Fixed credentials
const ALLOWED_USERS = [
    ['username' => 'surajslog', 'password' => 'surajslog'],
    ['username' => 'kiranslog', 'password' => 'kiranslog'],
];

function is_logged_in(): bool {
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user(): ?array {
    return is_logged_in() ? $_SESSION['user'] : null;
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
    foreach (ALLOWED_USERS as $u) {
        if (strcasecmp(trim($u['username']), trim($username)) === 0
            && $u['password'] === $password) {
            $_SESSION['user'] = ['username' => $u['username']];
            return true;
        }
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
