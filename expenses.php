<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];

$expenses = $conn->query("SELECT * FROM expenses WHERE user_id = $user_id ORDER BY date DESC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$total_expenses = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id = $user_id")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Expenses - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>All Expenses</h1>
            <div class="header-actions">
                <a href="add_expense.php" class="btn btn-primary">+ Add Expense</a>
                <a href="index.php" class="btn btn-outline">← Dashboard</a>
            </div>
        </header>

        <div class="summary-card">
            <h3>Total Expenses: $<?php echo number_format($total_expenses, 2); ?></h3>
        </div>

        <div class="content-list">
            <?php foreach($expenses as $expense): ?>
                <div class="list-item expense-item">
                    <div class="item-main">
                        <div class="item-title">
                            <span class="amount">$<?php echo number_format($expense['amount'], 2); ?></span>
                            <?php echo htmlspecialchars($expense['description']); ?>
                        </div>
                    </div>
                    <div class="item-meta">
                        <div class="meta-item">
                            <strong>Category:</strong> 
                            <span class="category-badge"><?php echo ucfirst($expense['category']); ?></span>
                        </div>
                        <div class="meta-item">
                            <strong>Date:</strong> <?php echo $expense['date']; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(empty($expenses)): ?>
                <div class="empty-state">
                    <h3>No expenses found</h3>
                    <p>You haven't recorded any expenses yet.</p>
                    <a href="add_expense.php" class="btn btn-primary">Record Your First Expense</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>