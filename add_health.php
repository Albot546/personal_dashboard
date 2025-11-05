<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weight = $_POST['weight'] ? $conn->real_escape_string($_POST['weight']) : 'NULL';
    $steps = $_POST['steps'] ? $conn->real_escape_string($_POST['steps']) : 'NULL';
    $sleep_hours = $_POST['sleep_hours'] ? $conn->real_escape_string($_POST['sleep_hours']) : 'NULL';
    $mood = $conn->real_escape_string($_POST['mood'] ?? 'average');
    $date = $conn->real_escape_string($_POST['date'] ?? date('Y-m-d'));

    $sql = "INSERT INTO health (user_id, weight, steps, sleep_hours, mood, date) 
            VALUES ('$user_id', $weight, $steps, $sleep_hours, '$mood', '$date')";
    
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
    <title>Add Health Data ------ Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Add Health Data</h1>
            <a href="index.php" class="btn btn-outline">← Back to Dashboard</a>
        </header>

        <div class="form-container">
            <form method="POST" class="health-form">
                <div class="form-group">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="weight">Weight (kg)</label>
                        <input type="number" id="weight" name="weight" step="0.1" min="0">
                    </div>
                    <div class="form-group">
                        <label for="steps">Steps</label>
                        <input type="number" id="steps" name="steps" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sleep_hours">Sleep Hours</label>
                        <input type="number" id="sleep_hours" name="sleep_hours" step="0.1" min="0" max="24">
                    </div>
                    <div class="form-group">
                        <label for="mood">Mood</label>
                        <select id="mood" name="mood">
                            <option value="excellent">Excellent &#128512</option>
                            <option value="good" selected>Good &#128516</option>
                            <option value="average">Average &#128529</option>
                            <option value="poor">Poor &#128546</option>
                            <option value="terrible">Terrible &#128542</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Health Data</button>
                    <a href="index.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>