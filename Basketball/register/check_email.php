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
    error_log("Database connection failed: " . $e->getMessage());
    echo json_encode(['available' => false, 'error' => 'Database connection failed']);
    exit;
}

// Check if email is provided
if (isset($_GET['email']) && !empty($_GET['email'])) {
    $email = trim($_GET['email']);
    
    // Basic email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['available' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email exists in database
    try {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing_user = $stmt->fetch();
        
        if ($existing_user) {
            echo json_encode(['available' => false, 'message' => 'Email is already registered']);
        } else {
            echo json_encode(['available' => true, 'message' => 'Email is available']);
        }
    } catch (Exception $e) {
        error_log("Database error in check_email.php: " . $e->getMessage());
        echo json_encode(['available' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['available' => false, 'error' => 'No email provided']);
}
?>