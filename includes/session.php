<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Check if user is admin
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] === 'admin';
}

// Check if user is organizer
function isOrganizer() {
    return isLoggedIn() && $_SESSION['user_type'] === 'participant' && 
           isset($_SESSION['role']) && $_SESSION['role'] === 'organizer';
}

// Check if user is regular user
function isUser() {
    return isLoggedIn() && $_SESSION['user_type'] === 'participant';
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Require admin
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Require organizer
function requireOrganizer() {
    if (!isOrganizer() && !isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get user type
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Set user session
function setUserSession($account_id, $user_type, $additional_data = []) {
    $_SESSION['user_id'] = $account_id;
    $_SESSION['user_type'] = $user_type;
    
    foreach ($additional_data as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

// Destroy user session
function destroyUserSession() {
    session_unset();
    session_destroy();
}
?>
