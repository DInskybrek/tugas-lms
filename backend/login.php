<?php
/**
 * Login handler untuk Tugas LMS
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$username = isset($input['username']) ? trim($input['username']) : '';
$password = isset($input['password']) ? trim($input['password']) : '';
$remember = isset($input['remember']) ? $input['remember'] : false;

// Validation
if (empty($username) || empty($password)) {
    json_response(false, 'Username dan password tidak boleh kosong');
}

try {
    // Get user from database
    $user = getUserByEmailOrUsername($username);
    
    if (!$user) {
        json_response(false, 'Username atau email tidak ditemukan');
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        json_response(false, 'Password salah');
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    
    // Remember me (set cookie untuk 30 hari)
    if ($remember) {
        setcookie('user_id', $user['id'], time() + (30 * 24 * 60 * 60), '/tugas-lms');
        setcookie('username', $user['username'], time() + (30 * 24 * 60 * 60), '/tugas-lms');
    }
    
    // Log login
    execute(
        'INSERT INTO activity_logs (user_id, action, created_at) VALUES (' . $user['id'] . ', "login", NOW())'
    );
    
    json_response(true, 'Login berhasil', array(
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'name' => $user['name']
    ));
    
} catch (Exception $e) {
    json_response(false, 'Error: ' . $e->getMessage());
}

?>
