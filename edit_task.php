<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch task data
$task = null;
if ($task_id > 0) {
    $result = $conn->query("SELECT * FROM tasks WHERE id = $task_id AND user_id = $user_id");
    if ($result && $result->num_rows > 0) {
        $task = $result->fetch_assoc();
    }
}

// If task not found or doesn't belong to user, redirect
if (!$task) {
    header("Location: tasks.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $status = $conn->real_escape_string($_POST['status'] ?? 'pending');
    $priority = $conn->real_escape_string($_POST['priority'] ?? 'medium');
    $due_date = $conn->real_escape_string($_POST['due_date'] ?? null);

    $sql = "UPDATE tasks SET 
            title = '$title', 
            description = '$description', 
            status = '$status', 
            priority = '$priority', 
            due_date = '$due_date',
            updated_at = NOW()
            WHERE id = $task_id AND user_id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: tasks.php");
        exit();
    } else {
        $error = "Error updating task: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Task</h1>
            <a href="tasks.php" class="btn btn-outline">← Back to Tasks</a>
        </header>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="task-form">
                <div class="form-group">
                    <label for="title">Task Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($task['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="in progress" <?php echo $task['status'] === 'in progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="low" <?php echo $task['priority'] === 'low' ? 'selected' : ''; ?>>Low</option>
                            <option value="medium" <?php echo $task['priority'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="high" <?php echo $task['priority'] === 'high' ? 'selected' : ''; ?>>High</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="date" id="due_date" name="due_date" value="<?php echo $task['due_date']; ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                    <a href="tasks.php" class="btn btn-outline">Cancel</a>
                    <a href="delete_task.php?id=<?php echo $task['id']; ?>" class="btn btn-logout" onclick="return confirm('Are you sure you want to delete this task?')">
                        Delete Task</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>