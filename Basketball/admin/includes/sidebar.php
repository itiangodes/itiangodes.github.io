<?php $page = basename($_SERVER['PHP_SELF']); ?>
<div class="sidebar fixed left-0 top-0 h-full w-64 text-white flex flex-col z-50">
    <div class="p-6 border-b border-blue-700 bg-blue-800">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                <img src="../sk_logo.svg.png" alt="SK Logo" class="w-8 h-8">
            </div>
            <div>
                <h2 class="font-bold text-lg">League Admin</h2>
                <p class="text-blue-200 text-sm">Administrator</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-4 bg-blue-800 overflow-y-auto">
        <ul class="space-y-2">
            <!-- Dashboard Link -->
            <li>
                <a href="dashboard.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'dashboard.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>
            
            <li>
                <a href="announcements.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'announcements.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-bullhorn mr-3"></i> Announcements
                </a>
            </li>
            <li>
                <a href="teams.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'teams.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-users mr-3"></i> Team Management
                </a>
            </li>
            <li>
                <a href="tournament.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'tournament.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-trophy mr-3"></i> Tournament Creation
                </a>
            </li>
            <li>
                <a href="approvals.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'approvals.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-check-circle mr-3"></i> Team Approvals
                </a>
            </li>

            <!-- New User Approvals Link -->
            <li>
                <a href="user_approvals.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'user_approvals.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-user-check mr-3"></i> User Approvals
                </a>
            </li>

            <li>
                <a href="docu_scoresheets.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'docu_scoresheets.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-file-alt mr-3"></i> Documents of Scoresheets
                </a>
            </li>
            <li>
                <a href="statistics.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'statistics.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-chart-bar mr-3"></i> Statistics
                </a>
            </li>
            <li>
                <a href="settings.php" class="nav-item flex items-center p-3 rounded-lg transition <?php if($page == 'settings.php') echo 'bg-blue-700 text-white'; else echo 'text-blue-200 hover:bg-blue-700 hover:text-white'; ?>">
                    <i class="fas fa-cog mr-3"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <div class="p-4 border-t border-blue-700 bg-blue-800">
        <a href="../logout/logout.php" class="flex items-center p-3 text-red-300 hover:bg-red-600 rounded-lg transition">
            <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
    </div>
</div>

<!-- Main Content Wrapper - Add this to your main content area -->
<div class="ml-64 min-h-screen">
    <!-- Your main content goes here -->
</div>