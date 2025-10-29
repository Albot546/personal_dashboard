<?php
session_start();


$host = 'localhost';
$dbname = 'personal_dashboard';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function getDB() {
    global $conn;
    return $conn;
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function requireAuth() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// Get current user data safely
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDB();
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT username, email FROM users WHERE id = $user_id");
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>