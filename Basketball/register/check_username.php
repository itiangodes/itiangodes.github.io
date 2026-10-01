<?php
// Database configuration
$host = 'localhost';
$dbname = 'basketball_league';
$username = 'root';
$password = '';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['available' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if username is provided
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $username = trim($_GET['username']);
    
    // Basic validation
    if (strlen($username) < 3) {
        echo json_encode(['available' => false, 'message' => 'Username must be at least 3 characters']);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode(['available' => false, 'message' => 'Username can only contain letters, numbers, and underscores']);
        exit;
    }
    
    // Check if username exists in database
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            echo json_encode(['available' => false, 'message' => 'Username is already taken']);
        } else {
            echo json_encode(['available' => true, 'message' => 'Username is available']);
        }
    } catch (Exception $e) {
        echo json_encode(['available' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['available' => false, 'error' => 'No username provided']);
}
?>