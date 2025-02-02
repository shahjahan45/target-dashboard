<?php
// create_user.php
$host = '127.0.0.1';
$db = 'earnings_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    
    // New user credentials
    $username = 'new_admin';
    $password = 'strong_password_123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    
    echo "User created successfully!<br>";
    echo "Username: $username<br>";
    echo "Password: $password";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}