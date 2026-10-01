<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../Login.html");
    exit();
}

// Restrict access to coaches only
if (strtolower($_SESSION['role']) !== 'coach') {
    header("Location: ../unauthorized.php");
    exit();
}

$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];

require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - Basketball League</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styleC.css">
</head>
<body class="bg-gray-50">

    <!-- Header -->
    <div class="logo-container text-white py-4 px-6 flex items-center justify-between sticky top-0 z-40 shadow-lg">
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center border-2 border-white shadow-lg overflow-hidden">
                <img src="sk_logo.svg.png" alt="SK Logo" class="w-full h-full object-cover">
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-bold">Inter Barangay Basketball League</h1>
                <p class="text-xs md:text-sm opacity-80">Coach Dashboard</p>
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
                                <p>Coach</p>
                            </div>
                        </div>
                    </div>

                    <!-- Menu Items -->
                    <div class="dropdown-items">
                        <a href="coach_profile.php" class="dropdown-item">
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

    <!-- JavaScript for Dropdown -->
    <script>
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
                
                // Change icon
                if (isDarkMode) {
                    darkModeIcon.classList.remove('fa-moon');
                    darkModeIcon.classList.add('fa-sun');
                } else {
                    darkModeIcon.classList.remove('fa-sun');
                    darkModeIcon.classList.add('fa-moon');
                }
                
                // Add your dark mode logic here
                // document.body.classList.toggle('dark-mode');
            });
        }
    });
    </script>