<?php
session_start();
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    header('Location: login.php');
    exit;
}
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

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_target'])) {
        $month_year = $_POST['month_year'];
        $target_amount = $_POST['target_amount'];

        $stmt = $pdo->prepare("INSERT INTO monthly_targets (month_year, target_amount) 
                              VALUES (?, ?)
                              ON DUPLICATE KEY UPDATE target_amount = ?");
        $stmt->execute([$month_year, $target_amount, $target_amount]);
    }
    elseif (isset($_POST['add_actual'])) {
        $month_year = $_POST['month_year'];
        $actual_amount = $_POST['actual_amount'];

        $stmt = $pdo->prepare("INSERT INTO actual_data (month_year, actual_amount) VALUES (?, ?)");
        $stmt->execute([$month_year, $actual_amount]);
    }
    elseif (isset($_POST['update_actual'])) {
        $id = $_POST['entry_id'];
        $actual_amount = $_POST['actual_amount'];

        $stmt = $pdo->prepare("UPDATE actual_data SET actual_amount = ? WHERE id = ?");
        $stmt->execute([$actual_amount, $id]);
    }
    elseif (isset($_POST['delete_actual'])) {
        $id = $_POST['entry_id'];

        $stmt = $pdo->prepare("DELETE FROM actual_data WHERE id = ?");
        $stmt->execute([$id]);
    }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Monthly Targets Dashboard</title>
    <style>
        .sidebar {
            background: #f8f9fa;
            height: 100vh;
            padding: 15px;
        }
        .progress {
            height: 30px;
            margin: 20px 0;
        }
        .month-link {
            display: block;
            padding: 8px;
            margin: 3px 0;
            background: #e9ecef;
            border-radius: 4px;
            text-decoration: none;
            color: #333;
            font-size: 0.9rem;
        }
        .month-link:hover {
            background: #dee2e6;
        }
        .active-month {
            background: #007bff;
            color: white;
        }
        .chart-container {
            margin: 20px 0;
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-md-2 sidebar">
                <h5 class="mb-3">Monthly Targets</h5>
                <?php
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
            <div class="col-md-10">
                <div class="container py-4">
                    <!-- Target Setting Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h2>Monthly Target: <?= date("F Y", strtotime($start_date)) ?></h2>
                        </div>
                        <div class="mb-3">
    <p class="text-muted small">Logged in as: <?= $_SESSION['username'] ?></p>
    <a href="logout.php" class="btn btn-danger btn-sm w-100">Logout</a>
</div>
                        <div class="card-body">
                            <?php
                            $targetStmt = $pdo->prepare("SELECT * FROM monthly_targets WHERE month_year = ?");
                            $targetStmt->execute([$start_date]);
                            $target = $targetStmt->fetch(PDO::FETCH_ASSOC);
                            ?>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Target Amount</label>
                                        <input type="number" class="form-control" name="target_amount" 
                                               value="<?= $target['target_amount'] ?? '' ?>" step="0.01" required>
                                        <input type="hidden" name="month_year" value="<?= $start_date ?>">
                                    </div>
                                    <div class="col-md-6 d-flex align-items-end">
                                        <button type="submit" name="update_target" class="btn btn-primary">
                                            <?= isset($target) ? 'Update Target' : 'Set Target' ?>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Progress Visualization -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3>Progress Visualization</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="progressPie"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="progressColumn"></canvas>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $actualStmt = $pdo->prepare("SELECT SUM(actual_amount) AS total FROM actual_data 
                                                        WHERE month_year = ?");
                            $actualStmt->execute([$start_date]);
                            $actual = $actualStmt->fetch(PDO::FETCH_ASSOC);
                            $totalActual = $actual['total'] ?? 0;
                            $targetAmount = $target['target_amount'] ?? 1;
                            $remaining = max($targetAmount - $totalActual, 0);
                            ?>
                        </div>
                    </div>

                    <!-- Actual Data Entry -->
                    <div class="card">
                        <div class="card-header">
                            <h3>Actual Data Management</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="mb-4">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Amount</label>
                                        <input type="number" class="form-control" name="actual_amount" 
                                               step="0.01" required>
                                        <input type="hidden" name="month_year" value="<?= $start_date ?>">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="submit" name="add_actual" class="btn btn-success">
                                            Add Entry
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Actual Data List -->
                            <h4>Recent Entries</h4>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
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
                                                <td>
                                                    <button class='btn btn-sm btn-warning edit-btn' 
                                                            data-id='{$entry['id']}' 
                                                            data-amount='{$entry['actual_amount']}'>
                                                        Edit
                                                    </button>
                                                    <form method='POST' style='display:inline'>
                                                        <input type='hidden' name='entry_id' value='{$entry['id']}'>
                                                        <button type='submit' name='delete_actual' 
                                                                class='btn btn-sm btn-danger'
                                                                onclick='return confirm(\"Are you sure?\")'>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="entry_id" id="editEntryId">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" class="form-control" name="actual_amount" 
                                   id="editAmount" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_actual" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit button handler
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = new bootstrap.Modal('#editModal');
                document.getElementById('editEntryId').value = btn.dataset.id;
                document.getElementById('editAmount').value = btn.dataset.amount;
                modal.show();
            });
        });

        // Chart initialization
        const pieCtx = document.getElementById('progressPie').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Achieved', 'Remaining'],
                datasets: [{
                    data: [<?= $totalActual ?>, <?= $remaining ?>],
                    backgroundColor: ['#4CAF50', '#e0e0e0']
                }]
            }
        });

        const columnCtx = document.getElementById('progressColumn').getContext('2d');
        new Chart(columnCtx, {
            type: 'bar',
            data: {
                labels: ['Target', 'Actual'],
                datasets: [{
                    label: 'Amount',
                    data: [<?= $targetAmount ?>, <?= $totalActual ?>],
                    backgroundColor: ['#2196F3', '#4CAF50']
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>