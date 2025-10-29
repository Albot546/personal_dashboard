<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

$health_data = $conn->query("SELECT * FROM health WHERE user_id = $user_id ORDER BY date DESC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Data - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Health Data</h1>
            <div class="header-actions">
                <a href="add_health.php" class="btn btn-primary">+ Add Health Data</a>
                <a href="index.php" class="btn btn-outline">← Dashboard</a>
            </div>
        </header>

        <div class="content-list">
            <?php foreach($health_data as $health): ?>
                <div class="list-item health-item">
                    <div class="item-main">
                        <div class="item-title">Date: <?php echo $health['date']; ?></div>
                        <div class="health-stats">
                            <?php if($health['weight']): ?>
                                <div class="health-stat">
                                    <strong>Weight:</strong> <?php echo $health['weight']; ?> kg
                                </div>
                            <?php endif; ?>
                            <?php if($health['steps']): ?>
                                <div class="health-stat">
                                    <strong>Steps:</strong> <?php echo number_format($health['steps']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if($health['sleep_hours']): ?>
                                <div class="health-stat">
                                    <strong>Sleep:</strong> <?php echo $health['sleep_hours']; ?> hours
                                </div>
                            <?php endif; ?>
                            <div class="health-stat">
                                <strong>Mood:</strong> 
                                <span class="mood-badge mood-<?php echo $health['mood']; ?>">
                                    <?php echo ucfirst($health['mood']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($health_data)): ?>
                <div class="empty-state">
                    <h3>No health data found</h3>
                    <p>You haven't recorded any health data yet.</p>
                    <a href="add_health.php" class="btn btn-primary">Record Your First Health Data</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>