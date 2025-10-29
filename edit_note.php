<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

$note_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$note = null;
if ($note_id > 0) {
    $result = $conn->query("SELECT * FROM notes WHERE id = $note_id AND user_id = $user_id");
    if ($result && $result->num_rows > 0) {
        $note = $result->fetch_assoc();
    }
}

if (!$note) {
    header("Location: notes.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content'] ?? '');
    $category = $conn->real_escape_string($_POST['category'] ?? 'general');

    $sql = "UPDATE notes SET 
            title = '$title', 
            content = '$content', 
            category = '$category',
            updated_at = NOW()
            WHERE id = $note_id AND user_id = $user_id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: notes.php");
        exit();
    } else {
        $error = "Error updating note: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Note</h1>
            <a href="notes.php" class="btn btn-outline">← Back to Notes</a>
        </header>

        <div class="form-container">
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="note-form">
                <div class="form-group">
                    <label for="title">Note Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($note['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="8" placeholder="Write your note here..."><?php echo htmlspecialchars($note['content']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="general" <?php echo $note['category'] === 'general' ? 'selected' : ''; ?>>General</option>
                        <option value="work" <?php echo $note['category'] === 'work' ? 'selected' : ''; ?>>Work</option>
                        <option value="personal" <?php echo $note['category'] === 'personal' ? 'selected' : ''; ?>>Personal</option>
                        <option value="ideas" <?php echo $note['category'] === 'ideas' ? 'selected' : ''; ?>>Ideas</option>
                        <option value="shopping" <?php echo $note['category'] === 'shopping' ? 'selected' : ''; ?>>Shopping</option>
                        <option value="recipes" <?php echo $note['category'] === 'recipes' ? 'selected' : ''; ?>>Recipes</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Note</button>
                    <a href="notes.php" class="btn btn-outline">Cancel</a>
                    <a href="delete_note.php?id=<?php echo $note['id']; ?>" class="btn btn-logout" onclick="return confirm('Are you sure you want to delete this note?')">
                        Delete Note</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>