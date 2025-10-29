<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $conn->real_escape_string($_POST['amount']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category'] ?? 'other');
    $date = $conn->real_escape_string($_POST['date'] ?? date('Y-m-d'));

    $sql = "INSERT INTO expenses (user_id, amount, description, category, date) 
            VALUES ('$user_id', '$amount', '$description', '$category', '$date')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Add New Expense</h1>
            <a href="index.php" class="btn btn-outline">← Back to Dashboard</a>
        </header>

        <div class="form-container">
            <form method="POST" class="expense-form">
                <div class="form-group">
                    <label for="amount">Amount *</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label for="description">Description *</label>
                    <input type="text" id="description" name="description" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="food">Food & Dining</option>
                        <option value="transport">Transport</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="bills">Bills & Utilities</option>
                        <option value="shopping">Shopping</option>
                        <option value="health">Health</option>
                        <option value="other" selected>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Expense</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>