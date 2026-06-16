<?php
/**
 * Configuration file for Tugas LMS
 * Database dan Moodle connection settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tugas_lms');
define('DB_PORT', 3306);

// App Configuration
define('APP_NAME', 'Tugas LMS');
define('APP_URL', 'http://localhost/tugas-lms');
define('APP_DEBUG', true);

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
session_start();

// Moodle Configuration (untuk integrasi)
define('MOODLE_URL', 'http://localhost/moodle');
define('MOODLE_DB_HOST', 'localhost');
define('MOODLE_DB_USER', 'root');
define('MOODLE_DB_PASS', '');
define('MOODLE_DB_NAME', 'moodle');

// Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset('utf8');
    
} catch (Exception $e) {
    if (APP_DEBUG) {
        die('Error: ' . $e->getMessage());
    } else {
        die('Database connection error. Please contact administrator.');
    }
}

// Helper function untuk escape input
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

// Helper function untuk query
function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    return $result;
}

// Helper function untuk single row
function getRow($sql) {
    $result = query($sql);
    return $result->fetch_assoc();
}

// Helper function untuk multiple rows
function getRows($sql) {
    $result = query($sql);
    $rows = array();
    
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    
    return $rows;
}

// Helper function untuk execute (INSERT, UPDATE, DELETE)
function execute($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception('Query error: ' . $conn->error);
    }
    
    return $result;
}

// Helper function untuk get last insert id
function getLastId() {
    global $conn;
    return $conn->insert_id;
}

// API Response helper
function json_response($success, $message = '', $data = array()) {
    header('Content-Type: application/json');
    echo json_encode(array(
        'success' => $success,
        'message' => $message,
        'data' => $data
    ));
    exit;
}

// Hashing password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, array('cost' => 10));
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Check if user logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return getRow('SELECT * FROM users WHERE id = ' . $_SESSION['user_id']);
}

// Redirect function
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Get user by email or username
function getUserByEmailOrUsername($email_or_username) {
    return getRow(
        'SELECT * FROM users WHERE email = "' . escape($email_or_username) . '" OR username = "' . escape($email_or_username) . '"'
    );
}

?>
