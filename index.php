<?php
require_once 'config.php';
requireAuth();
$conn = getDB();

$user = getCurrentUser();
if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $user['username'];

$current_month = date('n');
$current_year = date('Y');
$current_day = date('j');
$month_name = date('F Y');
$first_day = date("$current_year-$current_month-01");
$last_day = date("$current_year-$current_month-t");
$first_day_of_month = date("N", strtotime($first_day));
$days_in_month = date("t", strtotime($first_day));

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

$today = date('Y-m-d');
$today_tasks = $conn->query("
    SELECT COUNT(*) as count 
    FROM tasks 
    WHERE user_id = $user_id 
    AND due_date = '$today'
    AND status != 'completed'
")->fetch_row()[0];

// Get dashboard statistics
$stats = [];
$stats['total_tasks'] = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id = $user_id")->fetch_row()[0];
$stats['completed_tasks'] = $conn->query("SELECT COUNT(*) FROM tasks WHERE user_id = $user_id AND status = 'completed'")->fetch_row()[0];
$stats['total_expenses'] = $conn->query("SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE user_id = $user_id")->fetch_row()[0];
$stats['total_notes'] = $conn->query("SELECT COUNT(*) FROM notes WHERE user_id = $user_id")->fetch_row()[0];
$stats['health_entries'] = $conn->query("SELECT COUNT(*) FROM health WHERE user_id = $user_id")->fetch_row()[0];

// Get recent data
$recent_tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
$recent_notes = $conn->query("SELECT * FROM notes WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
$recent_expenses = $conn->query("SELECT * FROM expenses WHERE user_id = $user_id ORDER BY date DESC LIMIT 5");
$recent_health = $conn->query("SELECT * FROM health WHERE user_id = $user_id ORDER BY date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="user-info">
                <span class="user-welcome">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            </div>
            <h1>Personal Dashboard</h1>
            <p>Welcome! Manage your tasks, notes, expenses, and health in one place</p>
        </header>

        <!-- Clock and Date Widget -->
        <div class="clock-widget">
            <div class="clock-container">
                <div class="time" id="currentTime">Loading...</div>
                <div class="date" id="currentDate">Loading...</div>
                <div class="today-tasks">
                    <span class="task-count"><?php echo $today_tasks; ?></span>
                    tasks due today
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total_tasks']; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed_tasks']; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">$<?php echo number_format($stats['total_expenses'], 2); ?></div>
                <div class="stat-label">Total Expenses</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['health_entries']; ?></div>
                <div class="stat-label">Health Records</div>
            </div>
        </section>

        <div class="dashboard-grid">
            
            <div class="card">
                <div class="card-header">
                    <h2>&#128197 This Month</h2>
                    <a href="calendar.php" class="btn btn-outline">Full Calendar</a>
                </div>
                <div class="mini-calendar">
                    <div class="mini-calendar-header">
                        <h3><?php echo $month_name; ?></h3>
                    </div>
                    <div class="mini-calendar-grid">

                        <div class="mini-calendar-day-header">M</div>
                        <div class="mini-calendar-day-header">T</div>
                        <div class="mini-calendar-day-header">W</div>
                        <div class="mini-calendar-day-header">T</div>
                        <div class="mini-calendar-day-header">F</div>
                        <div class="mini-calendar-day-header">S</div>
                        <div class="mini-calendar-day-header">S</div>

                        <?php for ($i = 1; $i < $first_day_of_month; $i++): ?>
                            <div class="mini-calendar-day other-month"></div>
                        <?php endfor; ?>

                        <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                            <?php
                            $current_date = date("$current_year-$current_month-" . sprintf("%02d", $day));
                            $is_today = $current_date === $today;
                            $has_tasks = isset($tasks_by_date[$current_date]);
                            $day_tasks_count = $has_tasks ? count($tasks_by_date[$current_date]) : 0;
                            ?>
                            <div class="mini-calendar-day 
                                <?php echo $is_today ? 'today' : ''; ?>
                                <?php echo $has_tasks ? 'has-tasks' : ''; ?>"
                                title="<?php echo $has_tasks ? $day_tasks_count . ' tasks' : 'No tasks'; ?>">
                                <span class="mini-day-number"><?php echo $day; ?></span>
                                <?php if ($has_tasks): ?>
                                    <div class="task-dot" data-task-count="<?php echo $day_tasks_count; ?>"></div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div class="mini-calendar-legend">
                        <div class="legend-item">
                            <div class="legend-dot today-dot"></div>
                            <span>Today</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot task-dot"></div>
                            <span>Has Tasks</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>&#128203 Recent Tasks</h2>
                    <a href="tasks.php" class="btn btn-outline">View All</a>
                </div>
                <div class="content-list">
                    <?php while($task = $recent_tasks->fetch_assoc()): ?>
                        <div class="list-item task-item status-<?php echo $task['status']; ?>">
                            <div class="item-title"><?php echo htmlspecialchars($task['title']); ?></div>
                            <div class="item-meta">
                                Due: <?php echo $task['due_date']; ?> | 
                                Status: <span class="status-badge"><?php echo ucfirst($task['status']); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if($recent_tasks->num_rows == 0): ?>
                        <div class="empty-state">No tasks yet. <a href="add_task.php">Add your first task!</a></div>
                    <?php endif; ?>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h2>&#128221 Recent Notes</h2>
                    <a href="notes.php" class="btn btn-outline">View All</a>
                </div>
                <div class="content-list">
                    <?php while($note = $recent_notes->fetch_assoc()): ?>
                        <div class="list-item note-item">
                            <div class="item-title"><?php echo htmlspecialchars($note['title']); ?></div>
                            <div class="item-preview"><?php echo substr(htmlspecialchars($note['content']), 0, 50); ?>...</div>
                            <div class="item-meta">Category: <?php echo $note['category']; ?></div>
                        </div>
                    <?php endwhile; ?>
                    <?php if($recent_notes->num_rows == 0): ?>
                        <div class="empty-state">No notes yet. <a href="add_note.php">Add your first note!</a></div>
                    <?php endif; ?>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h2>&#128176 Recent Expenses</h2>
                    <a href="expenses.php" class="btn btn-outline">View All</a>
                </div>
                <div class="content-list">
                    <?php while($expense = $recent_expenses->fetch_assoc()): ?>
                        <div class="list-item expense-item">
                            <div class="item-title">
                                <span class="amount">$<?php echo $expense['amount']; ?></span>
                                <?php echo htmlspecialchars($expense['description']); ?>
                            </div>
                            <div class="item-meta">
                                Category: <?php echo $expense['category']; ?> | 
                                Date: <?php echo $expense['date']; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if($recent_expenses->num_rows == 0): ?>
                        <div class="empty-state">No expenses yet. <a href="add_expense.php">Add your first expense!</a></div>
                    <?php endif; ?>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h2>&#127973 Health Tracking</h2>
                    <a href="health.php" class="btn btn-outline">View All</a>
                </div>
                <div class="content-list">
                    <?php while($health = $recent_health->fetch_assoc()): ?>
                        <div class="list-item health-item">
                            <div class="item-title">Date: <?php echo $health['date']; ?></div>
                            <div class="item-meta">
                                <?php if($health['weight']): ?>Weight: <?php echo $health['weight']; ?>kg | <?php endif; ?>
                                <?php if($health['steps']): ?>Steps: <?php echo $health['steps']; ?> | <?php endif; ?>
                                <?php if($health['sleep_hours']): ?>Sleep: <?php echo $health['sleep_hours']; ?>h | <?php endif; ?>
                                Mood: <?php echo ucfirst($health['mood']); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if($recent_health->num_rows == 0): ?>
                        <div class="empty-state">No health data yet. <a href="add_health.php">Add your first entry!</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <section class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-grid">
                <a href="add_task.php" class="action-card">
                    <span class="action-icon">&#43</span>
                    <span class="action-text">Add Task</span>
                </a>
                <a href="add_note.php" class="action-card">
                    <span class="action-icon">&#128221</span>
                    <span class="action-text">Add Note</span>
                </a>
                <a href="add_expense.php" class="action-card">
                    <span class="action-icon">&#128176</span>
                    <span class="action-text">Add Expense</span>
                </a>
                <a href="add_health.php" class="action-card">
                    <span class="action-icon">&#127973</span>
                    <span class="action-text">Add Health Data</span>
                </a>
                <a href="calendar.php" class="action-card">
                    <span class="action-icon">&#128197</span>
                    <span class="action-text">View Calendar</span>
                </a>
            </div>
        </section>
    </div>

    <script>

        function updateClock() {
            const now = new Date();
            
            const time = now.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit',
                hour12: true 
            });
            
            const date = now.toLocaleDateString('en-US', { 
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('currentTime').textContent = time;
            document.getElementById('currentDate').textContent = date;
        }

        updateClock();
        setInterval(updateClock, 1000);

        document.addEventListener('DOMContentLoaded', function() {
            const taskDots = document.querySelectorAll('.task-dot');
            taskDots.forEach(dot => {
                dot.addEventListener('mouseenter', function() {
                    const count = this.getAttribute('data-task-count');
                    this.setAttribute('title', count + ' task' + (count > 1 ? 's' : ''));
                });
            });
        });
    </script>
</body>
</html>