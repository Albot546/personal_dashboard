<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];


$notes = $conn->query("SELECT * FROM notes WHERE user_id = $user_id ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Notes - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>All Notes</h1>
            <div class="header-actions">
                <a href="add_note.php" class="btn btn-primary">+ Add Note</a>
                <a href="index.php" class="btn btn-outline">← Dashboard</a>
            </div>
        </header>

        <div class="content-list">
            <?php foreach($notes as $note): ?>
                <div class="list-item note-item">
                    <div class="item-main">
                        <div class="item-title"><?php echo htmlspecialchars($note['title']); ?></div>
                        <div class="item-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></div>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <strong>Category:</strong> <?php echo ucfirst($note['category']); ?>
                        </div>
                        <div class="meta-item">
                            <strong>Created:</strong> <?php echo date('M j, Y', strtotime($note['created_at'])); ?>
                        </div>
                        <?php if ($note['updated_at'] && $note['updated_at'] !== $note['created_at']): ?>
                        <div class="meta-item">
                            <strong>Updated:</strong> <?php echo date('M j, Y', strtotime($note['updated_at'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="item-actions">
                        <a href="edit_note.php?id=<?php echo $note['id']; ?>" class="btn btn-outline btn-small">Edit</a>
                        <a href="delete_note.php?id=<?php echo $note['id']; ?>" class="btn btn-logout btn-small" onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($notes)): ?>
                <div class="empty-state">
                    <h3>No notes found</h3>
                    <p>You haven't created any notes yet.</p>
                    <a href="add_note.php" class="btn btn-primary">Create Your First Note</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>