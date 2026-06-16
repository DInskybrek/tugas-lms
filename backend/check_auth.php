<?php
/**
 * Check authentication status
 */

require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(array(
        'authenticated' => false,
        'message' => 'User not authenticated'
    ));
    exit;
}

$user = getCurrentUser();

if (!$user) {
    session_destroy();
    echo json_encode(array(
        'authenticated' => false,
        'message' => 'User not found'
    ));
    exit;
}

echo json_encode(array(
    'authenticated' => true,
    'user' => array(
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role']
    )
));

?>
