<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get note ID from URL
$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($note_id > 0) {
    // Delete the note if it belongs to the current user
    $conn->query("DELETE FROM notes WHERE id = $note_id AND user_id = $user_id");
}

// Redirect back to notes page
header("Location: notes.php");
exit();
?>