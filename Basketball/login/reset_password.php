<?php
session_start();

// Set timezone to match forgot_password.php
date_default_timezone_set('Asia/Manila');

// ========================== DATABASE CONNECTION ==========================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basketball_league";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$message_type = '';
$valid_token = false;
$token = '';
$show_login_button = false;

// ========================== VERIFY TOKEN ==========================
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $conn->prepare("SELECT user_id, reset_token_expiry FROM users WHERE reset_token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id, $expiry_time);
            $stmt->fetch();
            
            // Check if token is expired using same timezone
            $current_time = time();
            $expiry_timestamp = strtotime($expiry_time);
            
            if ($expiry_timestamp > $current_time) {
                $valid_token = true;
            } else {
                $message = 'Password reset link has expired. Please request a new one.';
                $message_type = 'error';
                $valid_token = false;
            }
        } else {
            $message = 'Invalid reset token. Please request a new password reset link.';
            $message_type = 'error';
            $valid_token = false;
        }
        
        $stmt->close();
    } else {
        $message = 'Database error. Please try again.';
        $message_type = 'error';
    }
} else {
    $valid_token = false;
    $message = 'No reset token provided.';
    $message_type = 'error';
}

// ========================== PROCESS PASSWORD RESET ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $message_type = 'error';
    } else {
        // Verify token again
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($user_id);
                $stmt->fetch();
                
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
                
                if ($update_stmt) {
                    $update_stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $message = 'Password has been reset successfully! You can now login with your new password.';
                        $message_type = 'success';
                        $valid_token = false;
                        $show_login_button = true; // Show login button after success
                    } else {
                        $message = 'Error resetting password. Please try again.';
                        $message_type = 'error';
                    }
                    
                    $update_stmt->close();
                } else {
                    $message = 'Database error. Please try again.';
                    $message_type = 'error';
                }
            } else {
                $message = 'Invalid or expired reset token. Please request a new password reset link.';
                $message_type = 'error';
            }
            
            $stmt->close();
        } else {
            $message = 'Database error. Please try again.';
            $message_type = 'error';
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Basketball League — Reset Password</title>
    <style>
        :root{
            --bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card: rgba(255, 255, 255, 0.95);
            --primary: #0033a0;
            --secondary: #d9272d;
            --accent: #ffc800;
            --muted: #64748b;
            --radius: 20px;
            font-family: 'Montserrat', 'Inter', ui-sans-serif, system-ui;
        }
        *{box-sizing:border-box; margin:0; padding:0;}

        html,body{
            min-height: 100vh;
            background: var(--bg);
            color:#0f172a;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .wrap{
            min-height: 100vh;
            display:grid;
            place-items:center;
            padding:20px;
            position: relative;
        }

        .basketball {
            position: absolute;
            width: 80px;
            height: 80px;
            background: var(--accent);
            border-radius: 50%;
            opacity: 0.1;
            animation: float 8s ease-in-out infinite;
        }

        .basketball:nth-child(1) { top: 10%; left: 10%; animation-delay: 0s; }
        .basketball:nth-child(2) { top: 60%; right: 15%; animation-delay: 2s; width: 60px; height: 60px; }
        .basketball:nth-child(3) { bottom: 20%; left: 20%; animation-delay: 4s; width: 40px; height: 40px; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .card {
            background: var(--card);
            padding: 40px 35px;
            border-radius: var(--radius);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            width:100%;
            max-width:420px;
            position: relative;
            z-index: 10;
            transition: transform 0.3s ease;
            margin: 20px 0;
        }

        .card:hover { transform: translateY(-5px); }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 8px 20px rgba(0,51,160,0.3);
            padding: 8px;
        }

        .logo-icon img { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
        }

        h2 {
            color: var(--primary);
            text-align: center;
            font-weight: 700;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .subtitle {
            color: var(--muted);
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        form { display: flex; flex-direction: column; gap: 20px; }

        label {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 6px;
            display: block;
            font-weight: 500;
        }

        input[type="password"] {
            width: 100%;
            padding: 16px 45px 16px 16px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(255,200,0,0.2);
            transform: translateY(-2px);
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 16px;
        }

        .btn {
            background: linear-gradient(135deg, var(--primary), #00267a);
            color: white;
            padding: 16px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0,51,160,0.3);
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            letter-spacing: 0.5px;
            margin-top: 10px;
            width: 100%;
        }

        .btn:hover {
            background: linear-gradient(135deg, var(--secondary), #c02127);
            box-shadow: 0 12px 25px rgba(217,39,45,0.4);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 8px 20px rgba(16,185,129,0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 12px 25px rgba(5,150,105,0.4);
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #f5c6cb;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }

        .copyright {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(226,232,240,0.5);
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 480px) {
            .card { padding: 30px 25px; }
        }
    </style>
</head>
<body>
  <div class="basketball"></div>
  <div class="basketball"></div>
  <div class="basketball"></div>

  <div class="wrap">
    <div class="card" role="region" aria-label="Reset password form">
      <div class="logo">
        <div class="logo-icon">
          <img src="sk_logo.svg.png" alt="SK Logo" onerror="this.style.display='none'">
        </div>
        <h2>Reset Your Password</h2>
        <p class="subtitle">Enter your new password below</p>
      </div>

      <?php if ($message): ?>
        <div class="<?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <?php if ($valid_token): ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        
        <div class="input-group">
          <label for="new_password">New Password</label>
          <input id="new_password" name="new_password" type="password" placeholder="Enter new password (min. 6 characters)" required minlength="6" />
          <div class="input-icon">🔒</div>
        </div>

        <div class="input-group">
          <label for="confirm_password">Confirm Password</label>
          <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm new password" required minlength="6" />
          <div class="input-icon">🔒</div>
        </div>

        <button class="btn" type="submit">
          Reset Password
        </button>
      </form>
      <?php elseif ($show_login_button): ?>
        <div class="button-group">
          <button class="btn btn-success" onclick="window.location.href='login.html'">
            Go to Login
          </button>
        </div>
      <?php elseif (!isset($_POST['token'])): ?>
        <div style="text-align: center;">
          <p class="subtitle"><?php echo $message ?: 'Invalid or expired reset link.'; ?></p>
          <button class="btn" onclick="window.location.href='login.html'" style="margin-top: 20px;">
            Back to Login
          </button>
        </div>
      <?php endif; ?>

      <div class="copyright">
        © 2025 Municipality of Tanza, Cavite • Capstone Project
      </div>
    </div>
  </div>
</body>
</html>