<?php
session_start();

// Database connection
$host = '127.0.0.1';
$db = 'earnings_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Create tables if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS monthly_targets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    month_year DATE UNIQUE,
    target_amount DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS actual_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    month_year DATE,
    actual_amount DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (month_year) REFERENCES monthly_targets(month_year)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Handle user login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Handle user logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);

// Handle target updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_target']) && $is_logged_in) {
    $month_year = $_POST['month_year'];
    $target_amount = $_POST['target_amount'];

    $stmt = $pdo->prepare("INSERT INTO monthly_targets (month_year, target_amount) 
                          VALUES (?, ?)
                          ON DUPLICATE KEY UPDATE target_amount = ?");
    $stmt->execute([$month_year, $target_amount, $target_amount]);
}

// Handle actual data entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_actual']) && $is_logged_in) {
    $month_year = $_POST['month_year'];
    $actual_amount = $_POST['actual_amount'];

    $stmt = $pdo->prepare("INSERT INTO actual_data (month_year, actual_amount) VALUES (?, ?)");
    $stmt->execute([$month_year, $actual_amount]);
}

// Get current month or selected month
$current_month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$start_date = date('Y-m-01', strtotime($current_month));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Monthly Targets Dashboard</title>
    <style>
        .sidebar {
            background: #f8f9fa;
            height: 100vh;
            padding: 20px;
        }
        .progress {
            height: 30px;
            margin: 20px 0;
        }
        .month-link {
            display: block;
            padding: 10px;
            margin: 5px 0;
            background: #e9ecef;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }
        .month-link:hover {
            background: #dee2e6;
        }
        .active-month {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-3 sidebar">
                <h4>Monthly Targets</h4>
                <?php if ($is_logged_in): ?>
                    <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
                    <a href="?logout" class="btn btn-danger mb-3">Logout</a>
                <?php else: ?>
                    <form method="POST" class="mb-3">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?= $login_error ?></div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php
                // Generate 12 months navigation
                $currentYear = date('Y');
                for ($i = 1; $i <= 12; $i++) {
                    $monthDate = date("$currentYear-$i-01");
                    $monthName = date("F Y", strtotime($monthDate));
                    $activeClass = ($current_month === date('Y-m', strtotime($monthDate))) ? 'active-month' : '';
                    echo "<a href='?month=" . date('Y-m', strtotime($monthDate)) . "' 
                         class='month-link $activeClass'>$monthName</a>";
                }
                ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="container py-4">
                    <?php if ($is_logged_in): ?>
                        <!-- Target Setting Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h2>Monthly Target: <?= date("F Y", strtotime($start_date)) ?></h2>
                            </div>
                            <div class="card-body">
                                <?php
                                // Get current target
                                $targetStmt = $pdo->prepare("SELECT * FROM monthly_targets WHERE month_year = ?");
                                $targetStmt->execute([$start_date]);
                                $target = $targetStmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Target Amount</label>
                                        <input type="number" class="form-control" name="target_amount" 
                                               value="<?= $target['target_amount'] ?? '' ?>" step="0.01" required>
                                        <input type="hidden" name="month_year" value="<?= $start_date ?>">
                                    </div>
                                    <button type="submit" name="update_target" class="btn btn-primary">
                                        <?= isset($target) ? 'Update Target' : 'Set Target' ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Progress Visualization -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3>Progress</h3>
                            </div>
                            <div class="card-body">
                                <?php
                                // Calculate progress
                                $actualStmt = $pdo->prepare("SELECT SUM(actual_amount) AS total FROM actual_data 
                                                            WHERE month_year = ?");
                                $actualStmt->execute([$start_date]);
                                $actual = $actualStmt->fetch(PDO::FETCH_ASSOC);
                                $totalActual = $actual['total'] ?? 0;
                                $targetAmount = $target['target_amount'] ?? 1;
                                $progress = ($totalActual / $targetAmount) * 100;
                                ?>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: <?= min($progress, 100) ?>%" 
                                         aria-valuenow="<?= $progress ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?= number_format($progress, 2) ?>%
                                    </div>
                                </div>
                                <p>Actual: <?= number_format($totalActual, 2) ?> / 
                                   Target: <?= number_format($targetAmount, 2) ?></p>
                            </div>
                        </div>

                        <!-- Actual Data Entry -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Add Actual Data</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Amount</label>
                                        <input type="number" class="form-control" name="actual_amount" 
                                               step="0.01" required>
                                        <input type="hidden" name="month_year" value="<?= $start_date ?>">
                                    </div>
                                    <button type="submit" name="add_actual" class="btn btn-success">
                                        Add Entry
                                    </button>
                                </form>

                                <!-- Actual Data List -->
                                <h4 class="mt-4">Recent Entries</h4>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $entriesStmt = $pdo->prepare("SELECT * FROM actual_data 
                                                                     WHERE month_year = ?
                                                                     ORDER BY created_at DESC");
                                        $entriesStmt->execute([$start_date]);
                                        while ($entry = $entriesStmt->fetch(PDO::FETCH_ASSOC)) {
                                            echo "<tr>
                                                    <td>" . date('Y-m-d H:i', strtotime($entry['created_at'])) . "</td>
                                                    <td>" . number_format($entry['actual_amount'], 2) . "</td>
                                                  </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">Please login to access the dashboard.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>