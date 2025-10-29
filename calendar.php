<?php
require_once 'config.php';
requireAuth();
$conn = getDB();
$user_id = $_SESSION['user_id'];


$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');


if ($month < 1 || $month > 12) $month = date('n');
if ($year < 2020 || $year > 2030) $year = date('Y');


$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year = $year - 1;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year = $year + 1;
}


$first_day = date("$year-$month-01");
$last_day = date("$year-$month-t");
$tasks = $conn->query("
    SELECT id, title, due_date, status, priority 
    FROM tasks 
    WHERE user_id = $user_id 
    AND due_date BETWEEN '$first_day' AND '$last_day'
    ORDER BY due_date, priority DESC
")->fetch_all(MYSQLI_ASSOC);


$tasks_by_date = [];
foreach ($tasks as $task) {
    $date = $task['due_date'];
    $tasks_by_date[$date][] = $task;
}


$first_day_of_month = date("N", strtotime($first_day)); 
$days_in_month = date("t", strtotime($first_day));
$month_name = date("F Y", strtotime($first_day));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="user-info">
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
            <h1>&#128197 Task Calendar</h1>
            <p>View your tasks by their due dates</p>
        </header>

        <div class="calendar-container">
            <div class="calendar-header">
                <div class="calendar-nav">
                    <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-outline">← Previous</a>
                    <h2 class="month-year"><?php echo $month_name; ?></h2>
                    <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-outline">Next →</a>
                </div>
                <div>
                    <a href="index.php" class="btn btn-outline">← Dashboard</a>
                    <a href="add_task.php" class="btn btn-primary">+ Add Task</a>
                </div>
            </div>

            <div class="calendar-grid">
                <!-- Day headers -->
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
                <div class="calendar-day-header">Sun</div>

                <!-- Empty days for the first week -->
                <?php for ($i = 1; $i < $first_day_of_month; $i++): ?>
                    <div class="calendar-day other-month"></div>
                <?php endfor; ?>

                <!-- Days of the month -->
                <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                    <?php
                    $current_date = date("$year-$month-" . sprintf("%02d", $day));
                    $is_today = $current_date === date('Y-m-d');
                    $day_tasks = $tasks_by_date[$current_date] ?? [];
                    ?>
                    <div class="calendar-day <?php echo $is_today ? 'today' : ''; ?>">
                        <div class="day-number"><?php echo $day; ?></div>
                        <div class="day-tasks">
                            <?php if (!empty($day_tasks)): ?>
                                <?php foreach ($day_tasks as $task): ?>
                                    <div class="task-item-small 
                                        task-priority-<?php echo $task['priority']; ?>
                                        <?php echo $task['status'] === 'completed' ? 'task-status-completed' : ''; ?>"
                                        title="<?php echo htmlspecialchars($task['title']); ?> - <?php echo ucfirst($task['priority']); ?> priority">
                                        <?php echo htmlspecialchars($task['title']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-tasks">No tasks</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-color legend-high"></div>
                    <span>High Priority</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-medium"></div>
                    <span>Medium Priority</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-low"></div>
                    <span>Low Priority</span>
                </div>
                <div class="legend-item">
                    <div style="text-decoration: line-through; color: #666;">Completed Task</div>
                </div>
            </div>
        </div>

        <!-- Upcoming Tasks Section -->
        <div class="card">
            <div class="card-header">
                <h2>&#128221 Upcoming Tasks (Next 7 Days)</h2>
            </div>
            <div class="content-list">
                <?php
                $next_week = date('Y-m-d', strtotime('+7 days'));
                $upcoming_tasks = $conn->query("
                    SELECT * FROM tasks 
                    WHERE user_id = $user_id 
                    AND due_date BETWEEN CURDATE() AND '$next_week'
                    AND status != 'completed'
                    ORDER BY due_date, priority DESC
                ");
                
                if ($upcoming_tasks->num_rows > 0): 
                    while($task = $upcoming_tasks->fetch_assoc()): 
                ?>
                    <div class="list-item task-item status-<?php echo $task['status']; ?>">
                        <div class="item-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="item-meta">
                            Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?> | 
                            Priority: <span class="priority-badge priority-<?php echo $task['priority']; ?>">
                                <?php echo ucfirst($task['priority']); ?>
                            </span> |
                            Status: <span class="status-badge"><?php echo ucfirst($task['status']); ?></span>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else: 
                ?>
                    <div class="empty-state">
                        <p>No upcoming tasks in the next 7 days. <a href="add_task.php">Add a new task!</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>