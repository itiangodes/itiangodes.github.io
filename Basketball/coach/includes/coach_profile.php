<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is a coach
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'coach') {
    header("Location: login.php");
    exit();
}

$coach_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current email
$stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$stmt->execute([$coach_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$coach_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($current_password, $user_data['password'])) {
        $error_message = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashed_password, $coach_id]);
            
            $success_message = "Password changed successfully!";
        } catch (PDOException $e) {
            $error_message = "Error changing password. Please try again.";
        }
    }
}

// Handle email change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_email'])) {
    $current_password = $_POST['email_password'];
    $new_email = trim($_POST['new_email']);
    $confirm_email = trim($_POST['confirm_email']);
    
    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$coach_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!password_verify($current_password, $user_data['password'])) {
        $error_message = "Password is incorrect.";
    } elseif ($new_email !== $confirm_email) {
        $error_message = "Email addresses do not match.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email address format.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$new_email, $coach_id]);
        
        if ($stmt->fetch()) {
            $error_message = "This email address is already in use.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                $stmt->execute([$new_email, $coach_id]);
                
                $user['email'] = $new_email;
                $success_message = "Email address changed successfully!";
            } catch (PDOException $e) {
                $error_message = "Error changing email. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Settings - VikingsDash</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f5f7fa;
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #6b7280;
            font-size: 0.95rem;
        }

        .card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color 0.3s ease;
        }

        .card-header:hover {
            background-color: #f9fafb;
        }

        .card-header-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #16a34a 0%, #0033a0 100%);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
        }

        .card-header-text h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .card-header-text p {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .expand-icon {
            color: #6b7280;
            font-size: 1.25rem;
            transition: transform 0.3s ease;
        }

        .expand-icon.expanded {
            transform: rotate(180deg);
        }

        .card-body {
            padding: 2rem;
            display: none;
        }

        .card-body.expanded {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            padding-right: 3rem;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            color: #1f2937;
        }

        .form-group input:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 1rem;
            padding: 0.5rem;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #16a34a;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background-color: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        }

        .password-strength.show {
            display: block;
        }

        .password-strength-bar {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .password-strength-bar.weak {
            width: 33%;
            background-color: #ef4444;
        }

        .password-strength-bar.medium {
            width: 66%;
            background-color: #f59e0b;
        }

        .password-strength-bar.strong {
            width: 100%;
            background-color: #10b981;
        }

        .password-requirements {
            margin-top: 0.75rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .password-requirements ul {
            margin-top: 0.5rem;
            padding-left: 1.5rem;
        }

        .password-requirements li {
            margin-bottom: 0.25rem;
        }

        .password-requirements li.met {
            color: #10b981;
        }

        .password-requirements li.met i {
            color: #10b981;
        }

        .submit-button {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            padding: 0.875rem 2rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .submit-button.loading {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .submit-button.loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: #dbeafe;
            color: #0033a0;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            margin-top: 1rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #0033a0;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1.5rem;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #16a34a;
        }

        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #0033a0;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .info-box p {
            font-size: 0.875rem;
            color: #1e40af;
            margin: 0;
        }

        @media (max-width: 640px) {
            .container {
                padding: 1rem 0.75rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .card-header {
                padding: 1rem 1.25rem;
            }

            .card-body {
                padding: 1.5rem 1.25rem;
            }

            .card-icon {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }

            .card-header-text h3 {
                font-size: 1rem;
            }

            .card-header-text p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="coach_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>

        <div class="page-header">
            <h1>Security Settings</h1>
            <p>Manage your password and email address</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Password Change Card -->
        <div class="card">
            <div class="card-header" onclick="toggleCard('password')">
                <div class="card-header-content">
                    <div class="card-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="card-header-text">
                        <h3>Change Password</h3>
                        <p>Update your password to keep your account secure</p>
                    </div>
                </div>
                <i class="fas fa-chevron-down expand-icon" id="password-icon"></i>
            </div>
            <div class="card-body" id="password-body">
                <div class="info-box">
                    <p><i class="fas fa-shield-alt"></i> Choose a strong password with at least 8 characters, including uppercase, lowercase, numbers, and symbols.</p>
                </div>

                <form method="POST" id="password_form">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="current_password" name="current_password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="new_password" name="new_password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('new_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="password_strength">
                            <div class="password-strength-bar" id="password_strength_bar"></div>
                        </div>
                        <div class="password-requirements">
                            <strong>Password must contain:</strong>
                            <ul id="password_requirements">
                                <li id="req_length"><i class="fas fa-circle"></i> At least 8 characters</li>
                                <li id="req_uppercase"><i class="fas fa-circle"></i> One uppercase letter</li>
                                <li id="req_lowercase"><i class="fas fa-circle"></i> One lowercase letter</li>
                                <li id="req_number"><i class="fas fa-circle"></i> One number</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="submit-button">
                        <i class="fas fa-key"></i>
                        Update Password
                    </button>

                    <div class="security-badge">
                        <i class="fas fa-shield-check"></i>
                        Your password is encrypted and secure
                    </div>
                </form>
            </div>
        </div>

        <!-- Email Change Card -->
        <div class="card">
            <div class="card-header" onclick="toggleCard('email')">
                <div class="card-header-content">
                    <div class="card-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="card-header-text">
                        <h3>Change Email Address</h3>
                        <p>Current email: <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
                <i class="fas fa-chevron-down expand-icon" id="email-icon"></i>
            </div>
            <div class="card-body" id="email-body">
                <div class="info-box">
                    <p><i class="fas fa-info-circle"></i> Your email is used for login and account recovery. Make sure you have access to the new email address.</p>
                </div>

                <form method="POST" id="email_form">
                    <input type="hidden" name="change_email" value="1">

                    <div class="form-group">
                        <label for="new_email">New Email Address</label>
                        <input type="email" id="new_email" name="new_email" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_email">Confirm New Email</label>
                        <input type="email" id="confirm_email" name="confirm_email" required>
                    </div>

                    <div class="form-group">
                        <label for="email_password">Current Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="email_password" name="email_password" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('email_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p style="margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">
                            Confirm your password to change your email
                        </p>
                    </div>

                    <button type="submit" class="submit-button">
                        <i class="fas fa-envelope"></i>
                        Update Email Address
                    </button>

                    <div class="security-badge">
                        <i class="fas fa-user-shield"></i>
                        Password verification required for security
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleCard(cardId) {
            const body = document.getElementById(cardId + '-body');
            const icon = document.getElementById(cardId + '-icon');
            
            body.classList.toggle('expanded');
            icon.classList.toggle('expanded');
        }

        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                button.classList.remove('fa-eye');
                button.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                button.classList.remove('fa-eye-slash');
                button.classList.add('fa-eye');
            }
        }

        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function(e) {
            const password = e.target.value;
            const strengthBar = document.getElementById('password_strength_bar');
            const strengthContainer = document.getElementById('password_strength');
            
            if (password.length === 0) {
                strengthContainer.classList.remove('show');
                return;
            }
            
            strengthContainer.classList.add('show');
            
            let strength = 0;
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            
            // Update requirements list
            document.getElementById('req_length').className = hasLength ? 'met' : '';
            document.getElementById('req_uppercase').className = hasUppercase ? 'met' : '';
            document.getElementById('req_lowercase').className = hasLowercase ? 'met' : '';
            document.getElementById('req_number').className = hasNumber ? 'met' : '';
            
            if (hasLength) strength++;
            if (hasUppercase) strength++;
            if (hasLowercase) strength++;
            if (hasNumber) strength++;
            
            strengthBar.className = 'password-strength-bar';
            
            if (strength <= 2) {
                strengthBar.classList.add('weak');
            } else if (strength === 3) {
                strengthBar.classList.add('medium');
            } else {
                strengthBar.classList.add('strong');
            }
        });

        // Form submission with loading state
        document.getElementById('password_form').addEventListener('submit', function(e) {
            const btn = this.querySelector('.submit-button');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Updating Password...';
            btn.disabled = true;
        });

        document.getElementById('email_form').addEventListener('submit', function(e) {
            const btn = this.querySelector('.submit-button');
            btn.classList.add('loading');
            btn.innerHTML = '<i class="fas fa-spinner"></i> Updating Email...';
            btn.disabled = true;
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>