<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../Login.html");
    exit();
}

// Restrict access to players only
if (strtolower($_SESSION['role']) !== 'player') {
    header("Location: ../unauthorized.php");
    exit();
}

// Store user info from session with proper checks
$fullname = $_SESSION['fullname'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';
$email = $_SESSION['email'] ?? '';

// Database configuration
$host = 'localhost';
$dbname = 'basketball_league';
$username_db = 'root';
$password_db = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// If email is not in session, fetch it from database
if (empty($email) && !empty($user_id)) {
    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            $email = $user['email'];
            $_SESSION['email'] = $email; // Store in session for future use
        }
    } catch (Exception $e) {
        // Continue without email, will handle in form
    }
}

// Handle form submissions
$success_message = '';
$error_message = '';
$active_section = $_GET['section'] ?? 'landing'; // Default to landing page

// Change Password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            
            $success_message = "Password changed successfully!";
            $active_section = 'landing'; // Return to landing after success
        } else {
            $error_message = "Current password is incorrect.";
            $active_section = 'password';
        }
    }
}

// Change Email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_email'])) {
    $new_email = trim($_POST['new_email']);
    $confirm_password = $_POST['confirm_password_email'];
    
    // Validate email and password
    if (empty($new_email) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$new_email, $user_id]);
        if ($stmt->fetch()) {
            $error_message = "Email is already registered by another user.";
        } else {
            // Verify password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($confirm_password, $user['password'])) {
                // Update email
                $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                $stmt->execute([$new_email, $user_id]);
                
                // Update session
                $_SESSION['email'] = $new_email;
                $email = $new_email;
                
                $success_message = "Email changed successfully!";
                $active_section = 'landing'; // Return to landing after success
            } else {
                $error_message = "Password is incorrect.";
                $active_section = 'email';
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
    <title>Settings - Basketball League</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styleP.css">
    <style>
        /* Additional styles for settings page */
        .settings-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
        }
        
        .settings-header {
            background: linear-gradient(135deg, #0033a0 0%, #0047cc 100%);
            color: white;
            padding: 24px;
        }
        
        .settings-content {
            padding: 32px;
        }
        
        .form-group {
            margin-bottom: 24px;
            position: relative;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Montserrat', sans-serif;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #0033a0;
            box-shadow: 0 0 0 3px rgba(0, 51, 160, 0.1);
        }
        
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #0033a0 0%, #0047cc 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 51, 160, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(100, 116, 139, 0.3);
        }
        
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 500;
            font-family: 'Montserrat', sans-serif;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #0033a0;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
        }

        .back-link:hover {
            color: #d9272d;
        }

        .option-card {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-card:hover .option-icon {
            transform: scale(1.1);
        }

        .option-icon {
            transition: all 0.3s ease;
        }

        .section {
            display: none;
        }

        .section.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <div class="logo-container text-white py-4 px-6 flex items-center justify-between sticky top-0 z-40 shadow-lg">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center border-2 border-white shadow-lg overflow-hidden">
                <img src="../sk_logo.svg.png" alt="SK Logo" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold">Inter Barangay Basketball League</h1>
                <p class="text-xs md:text-sm opacity-80">Settings</p>
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            <!-- User Dropdown -->
            <div class="user-dropdown-container">
                <button class="user-dropdown-trigger" id="userDropdownBtn" type="button">
                    <span class="user-name"><?php echo htmlspecialchars($fullname); ?></span>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($fullname, 0, 1)) . strtoupper(substr(strstr($fullname, ' '), 1, 1)); ?>
                    </div>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <!-- User Info Header -->
                    <div class="dropdown-header">
                        <div class="dropdown-header-content">
                            <div class="dropdown-avatar">
                                <?php echo strtoupper(substr($fullname, 0, 1)) . strtoupper(substr(strstr($fullname, ' '), 1, 1)); ?>
                            </div>
                            <div class="dropdown-user-info">
                                <h3><?php echo htmlspecialchars($fullname); ?></h3>
                                <p>Player</p>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="dropdown-items">
                        <a href="player_profile.php" class="dropdown-item">
                            <div class="dropdown-item-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="dropdown-item-label">My Profile</span>
                        </a>

                        <a href="settings.php" class="dropdown-item">
                            <div class="dropdown-item-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span class="dropdown-item-label">Settings</span>
                        </a>

                        <a href="notifications.php" class="dropdown-item">
                            <div class="dropdown-item-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span class="dropdown-item-label">Notifications</span>
                            <span class="notification-badge">3</span>
                        </a>

                        <button class="dropdown-item" id="darkModeToggle" type="button">
                            <div class="dropdown-item-icon">
                                <i class="fas fa-moon" id="darkModeIcon"></i>
                            </div>
                            <span class="dropdown-item-label">Dark Mode</span>
                            <div class="dark-mode-toggle" id="darkModeSwitch">
                                <div class="dark-mode-toggle-circle"></div>
                            </div>
                        </button>

                        <div class="dropdown-divider"></div>

                        <form action="../logout/logout.php" method="POST" style="margin: 0;">
                            <button type="submit" class="dropdown-item logout">
                                <div class="dropdown-item-icon">
                                    <i class="fas fa-sign-out-alt"></i>
                                </div>
                                <span class="dropdown-item-label">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Content -->
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <a href="player_dashboard.php" class="back-link mb-6">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Dashboard
            </a>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle mr-3"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Landing Section -->
            <div id="landingSection" class="section <?php echo $active_section === 'landing' ? 'active' : ''; ?>">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-4">Account Settings</h2>
                    <p class="text-gray-600">Choose what you'd like to update</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Change Password Option -->
                    <div class="settings-card option-card" onclick="showSection('password')">
                        <div class="settings-header">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-lock text-white text-2xl option-icon"></i>
                                </div>
                            </div>
                            <h2 class="text-xl font-bold text-center">Change Password</h2>
                        </div>
                        <div class="settings-content text-center">
                            <p class="text-gray-600 mb-6">Update your account password for enhanced security</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </button>
                        </div>
                    </div>

                    <!-- Change Email Option -->
                    <div class="settings-card option-card" onclick="showSection('email')">
                        <div class="settings-header">
                            <div class="flex items-center justify-center mb-4">
                                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-envelope text-white text-2xl option-icon"></i>
                                </div>
                            </div>
                            <h2 class="text-xl font-bold text-center">Change Email</h2>
                        </div>
                        <div class="settings-content text-center">
                            <p class="text-gray-600 mb-6">Update your email address for account communications</p>
                            <button class="btn btn-primary">
                                <i class="fas fa-sync-alt mr-2"></i>Change Email
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Change Password Section -->
            <div id="passwordSection" class="section <?php echo $active_section === 'password' ? 'active' : ''; ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold flex items-center">
                                <i class="fas fa-lock mr-3"></i>
                                Change Password
                            </h2>
                            <button onclick="showSection('landing')" class="text-white hover:text-gray-200">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                        </div>
                    </div>
                    <div class="settings-content">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label" for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-input" required minlength="8">
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Must be at least 8 characters long
                                </p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input" required>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="button" onclick="showSection('landing')" class="btn btn-secondary flex-1">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                                <button type="submit" name="change_password" class="btn btn-primary flex-1">
                                    <i class="fas fa-key mr-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Email Section -->
            <div id="emailSection" class="section <?php echo $active_section === 'email' ? 'active' : ''; ?>">
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold flex items-center">
                                <i class="fas fa-envelope mr-3"></i>
                                Change Email
                            </h2>
                            <button onclick="showSection('landing')" class="text-white hover:text-gray-200">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                        </div>
                    </div>
                    <div class="settings-content">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label class="form-label">Current Email</label>
                                <input type="text" class="form-input bg-gray-50" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    This is your current email address
                                </p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="new_email">New Email Address</label>
                                <input type="email" id="new_email" name="new_email" class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="confirm_password_email">Confirm Password</label>
                                <input type="password" id="confirm_password_email" name="confirm_password_email" class="form-input" required>
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Enter your password to confirm email change
                                </p>
                            </div>
                            
                            <div class="flex space-x-4">
                                <button type="button" onclick="showSection('landing')" class="btn btn-secondary flex-1">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                                <button type="submit" name="change_email" class="btn btn-primary flex-1">
                                    <i class="fas fa-sync-alt mr-2"></i>Change Email
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    function showSection(section) {
        // Hide all sections
        document.querySelectorAll('.section').forEach(sec => {
            sec.classList.remove('active');
        });
        
        // Show selected section
        document.getElementById(section + 'Section').classList.add('active');
        
        // Update URL without reloading page
        const url = new URL(window.location);
        url.searchParams.set('section', section);
        window.history.pushState({}, '', url);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('userDropdownBtn');
        const dropdownMenu = document.getElementById('userDropdownMenu');
        const chevronIcon = document.querySelector('.chevron-icon');
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        const darkModeIcon = document.getElementById('darkModeIcon');
        
        let isDarkMode = false;

        // Toggle dropdown
        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdownMenu.classList.toggle('active');
                if (chevronIcon) {
                    chevronIcon.classList.toggle('rotate');
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (dropdownMenu && !dropdownMenu.contains(e.target) && !dropdownBtn.contains(e.target)) {
                dropdownMenu.classList.remove('active');
                if (chevronIcon) {
                    chevronIcon.classList.remove('rotate');
                }
            }
        });

        // Dark mode toggle
        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                isDarkMode = !isDarkMode;
                darkModeSwitch.classList.toggle('active');
                
                if (isDarkMode) {
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                } else {
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
            });
        }

        // Password confirmation validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePasswords() {
            if (newPassword && confirmPassword && newPassword.value && confirmPassword.value) {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#d9272d';
                } else {
                    confirmPassword.style.borderColor = '#10b981';
                }
            }
        }
        
        if (newPassword && confirmPassword) {
            newPassword.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
        }
    });
    </script>
</body>
</html>