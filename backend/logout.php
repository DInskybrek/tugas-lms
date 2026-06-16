<?php
/**
 * Logout handler
 */

require_once 'config.php';

if (!isLoggedIn()) {
    redirect('../index.html');
}

// Log logout
try {
    execute(
        'INSERT INTO activity_logs (user_id, action, created_at) VALUES (' . $_SESSION['user_id'] . ', "logout", NOW())'
    );
} catch (Exception $e) {
    // Ignore error
}

// Destroy session
session_destroy();

// Clear cookies
setcookie('user_id', '', time() - 3600, '/tugas-lms');
setcookie('username', '', time() - 3600, '/tugas-lms');

header('Content-Type: application/json');
echo json_encode(array('success' => true, 'message' => 'Logout berhasil'));
exit;

?>
