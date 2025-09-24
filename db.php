<?php
// Database configuration
$servername = "localhost";  // Change if needed (e.g., from hosting panel)
$username = "um4u5gpwc3dwc";
$password = "neqhgxo10ioe";
$dbname = "dbaraqjlkmfshb";
 
// Create connection with error handling
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    error_log("DB Connection failed: " . $conn->connect_error);
    die("Database connection failed. Please check credentials.");  // For debugging; remove in production
}
 
// Set charset to UTF-8
$conn->set_charset("utf8mb4");
 
// Function to hash password
if (!function_exists('hashPassword')) {
    function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}
 
// Function to verify password
if (!function_exists('verifyPassword')) {
    function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
 
// Function to get user session
if (!function_exists('startSession')) {
    function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}
 
// Function to sanitize input
if (!function_exists('sanitize')) {
    function sanitize($data) {
        global $conn;
        if ($conn) {
            return htmlspecialchars(stripslashes(trim($conn->real_escape_string($data))));
        }
        return htmlspecialchars(stripslashes(trim($data)));
    }
}
?>
