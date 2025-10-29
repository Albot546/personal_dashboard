<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get all tasks for the user
$tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date ASC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Tasks - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>All Tasks</h1>
            <div class="header-actions">
                <a href="add_task.php" class="btn btn-primary">+ Add Task</a>
                <a href="index.php" class="btn btn-outline">← Dashboard</a>
            </div>
        </header>

        <div class="content-list">
            <?php foreach($tasks as $task): ?>
                <div class="list-item task-item status-<?php echo $task['status']; ?>">
                    <div class="item-main">
                        <div class="item-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <?php if($task['description']): ?>
                            <div class="item-description"><?php echo htmlspecialchars($task['description']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <strong>Due:</strong> <?php echo $task['due_date'] ?: 'No due date'; ?>
                        </div>
                        <div class="meta-item">
                            <strong>Priority:</strong> 
                            <span class="priority-badge priority-<?php echo $task['priority']; ?>">
                                <?php echo ucfirst($task['priority']); ?>
                            </span>
                        </div>
                        <div class="meta-item">
                            <strong>Status:</strong> 
                            <span class="status-badge"><?php echo ucfirst($task['status']); ?></span>
                        </div>
                    </div>
                    <div class="item-actions">
                        <a href="edit_task.php?id=<?php echo $task['id']; ?>" class="btn btn-outline btn-small">Edit</a>
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-logout btn-small" onclick="return confirm('Are you sure you want to delete this task?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($tasks)): ?>
                <div class="empty-state">
                    <h3>No tasks found</h3>
                    <p>You haven't created any tasks yet.</p>
                    <a href="add_task.php" class="btn btn-primary">Create Your First Task</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>