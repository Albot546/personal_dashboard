<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id > 0) {
    // Delete the task if it belongs to the current user
    $conn->query("DELETE FROM tasks WHERE id = $task_id AND user_id = $user_id");
}

// Redirect back to tasks page
header("Location: tasks.php");
exit();
?>