<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'etsam4771');
define('DB_NAME', 'dictionary_db');

// Create database connection
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Start session for flash messages
session_start();

// Helper function to set flash message
function setFlashMessage($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Helper function to display flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $type = $_SESSION['flash_type'] ?? 'success';
        $class = $type === 'error' ? 'alert-error' : 'alert-success';
        echo '<div class="alert ' . $class . '">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Helper function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
