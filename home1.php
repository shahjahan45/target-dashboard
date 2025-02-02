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

// Create default admin if no users exist
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
if ($stmt->fetchColumn() == 0) {
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)")
        ->execute([$username, $password]);
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    } else {
        $login_error = "Invalid username or password.";
    }
}

// Check authentication
$logged_in = isset($_SESSION['user_id']);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// Show login form if not authenticated
if (!$logged_in) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <title>Admin Login</title>
        <style>
            body { background: #f8f9fa; }
            .login-container { max-width: 400px; margin: 100px auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="login-container card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4">Admin Login</h2>
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?= $login_error ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Handle CRUD operations (only accessible if logged in)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_target'])) {
        // ... existing target update code ...
    }
    elseif (isset($_POST['add_actual'])) {
        // ... existing actual data add code ...
    }
    elseif (isset($_POST['update_actual'])) {
        // ... existing actual data update code ...
    }
    elseif (isset($_POST['delete_actual'])) {
        // ... existing actual data delete code ...
    }
}

// ... rest of the original dashboard HTML code ...

// Add logout button in the sidebar (inside the existing sidebar div)
<div class="col-md-2 sidebar">
    <h5 class="mb-3">Monthly Targets</h5>
    <div class="mb-3">
        <a href="?logout=1" class="btn btn-danger w-100">Logout</a>
    </div>
    <?php
    // ... existing month links code ...
    ?>
</div>

// ... rest of the original HTML and JavaScript ...