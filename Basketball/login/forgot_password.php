<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// ========================== TIMEZONE CONFIGURATION ==========================
// Set the correct timezone for Philippines
date_default_timezone_set('Asia/Manila');

// ========================== DATABASE CONNECTION ==========================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basketball_league";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

// ========================== FORGOT PASSWORD PROCESS ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter your email address.']);
        exit();
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT user_id, username, email, CONCAT(firstname, ' ', lastname) AS fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $username, $user_email, $fullname);
        $stmt->fetch();
        
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Debug: Log the times for verification
        error_log("Current time: " . date('Y-m-d H:i:s'));
        error_log("Expiry time: " . $expiry_time);
        
        // Store token in database
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?");
        $update_stmt->bind_param("ssi", $reset_token, $expiry_time, $user_id);
        
        if ($update_stmt->execute()) {
            // CORRECT PATH FOR YOUR STRUCTURE
            $reset_link = "http://localhost/Basketball/login/reset_password.php?token=" . $reset_token;
            
            // Send email using PHPMailer
            $email_result = sendPasswordResetEmail($user_email, $fullname, $reset_link);
            
            if ($email_result['success']) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Password reset link has been sent to your email! Please check your inbox and spam folder.',
                    'email_sent' => true
                ]);
            } else {
                // If email fails, provide the reset link directly
                echo json_encode([
                    'success' => true, 
                    'message' => 'Email could not be sent. Here is your reset link:',
                    'reset_link' => $reset_link,
                    'email_sent' => false,
                    'email_error' => $email_result['error']
                ]);
            }
            
        } else {
            echo json_encode(['success' => false, 'message' => 'Error generating reset token. Please try again.']);
        }
        
        $update_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'No account found with this email address.']);
    }
    
    $stmt->close();
}

$conn->close();

// ========================== PHPMailer FUNCTION ==========================
function sendPasswordResetEmail($to_email, $fullname, $reset_link) {
    try {
        // Load PHPMailer - adjust path based on your structure
        // Since PHPMailer is outside login folder, go up one level from Basketball/login
        require '../PHPMailer/src/PHPMailer.php';
        require '../PHPMailer/src/SMTP.php';
        require '../PHPMailer/src/Exception.php';
        
        // Use fully qualified class names
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'interbarangay6@gmail.com'; // REPLACE WITH YOUR GMAIL
        $mail->Password   = 'memu igfl jfbb oaje';    // REPLACE WITH APP PASSWORD
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply.basketball.league@gmail.com', 'Basketball League');
        $mail->addAddress($to_email, $fullname);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request - Inter Barangay Basketball League';
        
        // HTML Email Body
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { 
                    font-family: 'Arial', sans-serif; 
                    line-height: 1.6; 
                    color: #333; 
                    margin: 0; 
                    padding: 0; 
                    background-color: #f4f4f4;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    background: white; 
                    border-radius: 10px; 
                    overflow: hidden;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                }
                .header { 
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                    color: white; 
                    padding: 30px 20px; 
                    text-align: center; 
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 28px; 
                    font-weight: bold;
                }
                .content { 
                    padding: 30px; 
                }
                .button { 
                    display: inline-block; 
                    padding: 14px 28px; 
                    background: linear-gradient(135deg, #0033a0, #00267a); 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 8px; 
                    font-weight: bold;
                    margin: 20px 0;
                }
                .footer { 
                    text-align: center; 
                    padding: 20px; 
                    color: #666; 
                    font-size: 12px; 
                    background: #f8f9fa;
                    border-top: 1px solid #e9ecef;
                }
                .warning {
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    padding: 12px;
                    border-radius: 5px;
                    margin: 15px 0;
                    color: #856404;
                }
                .link-box {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 15px 0;
                    word-break: break-all;
                    border: 1px solid #e9ecef;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🏀 Basketball League</h1>
                    <h2>Password Reset Request</h2>
                </div>
                <div class='content'>
                    <p>Hello <strong>$fullname</strong>,</p>
                    <p>You have requested to reset your password for the <strong>Inter Barangay Basketball League</strong> account.</p>
                    <p>Click the button below to reset your password:</p>
                    <p style='text-align: center;'>
                        <a href='$reset_link' class='button'>Reset My Password</a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <div class='link-box'>
                        $reset_link
                    </div>
                    <div class='warning'>
                        <strong>⚠️ Important:</strong> This link will expire in 1 hour for security reasons.
                    </div>
                    <p>If you didn't request this password reset, please ignore this email. Your account remains secure.</p>
                </div>
                <div class='footer'>
                    <p><strong>Inter Barangay Basketball League</strong></p>
                    <p>Municipality of Tanza, Cavite • Capstone Project</p>
                    <p>© 2025 All rights reserved</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Plain text version
        $mail->AltBody = "Password Reset Request - Basketball League\n\nHello $fullname,\n\nYou requested a password reset for your Inter Barangay Basketball League account.\n\nReset your password here:\n$reset_link\n\nThis link expires in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\n--\nBasketball League\nMunicipality of Tanza, Cavite\n© 2025 Capstone Project";
        
        $mail->send();
        return ['success' => true, 'error' => ''];
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
?>