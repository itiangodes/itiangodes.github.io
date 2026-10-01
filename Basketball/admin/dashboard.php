<?php
include 'includes/auth.php';
include 'includes/db.php';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Admin Dashboard Section -->
<main class="w-full transition-all duration-300"> <!-- Changed from ml-0 lg:ml-64 to w-full -->
    <section id="admin-dashboard" class="bg-gray-50 min-h-screen">
        <div class="w-full px-4 py-6">
            <!-- Welcome Header -->
            <div class="mb-8">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-lg p-6 md:p-8 text-white w-full">
                    <div class="flex flex-col md:flex-row items-center justify-between">
                        <div class="mb-4 md:mb-0 text-center md:text-left w-full">
                            <h1 class="text-2xl md:text-3xl font-bold mb-2">Welcome back, Admin! 👋</h1>
                            <p class="text-blue-100 text-base md:text-lg">Here's what's happening with your system today.</p>
                            <div class="flex flex-wrap justify-center md:justify-start items-center mt-4 space-x-0 md:space-x-4 space-y-2 md:space-y-0 text-sm">
                                <span class="flex items-center bg-blue-500 bg-opacity-50 px-3 py-1 rounded-full mr-2">
                                    <i class="fas fa-calendar-day mr-2"></i>
                                    <?= date('l, F j, Y') ?>
                                </span>
                                <span class="flex items-center bg-blue-500 bg-opacity-50 px-3 py-1 rounded-full">
                                    <i class="fas fa-clock mr-2"></i>
                                    <?= date('g:i A') ?>
                                </span>
                            </div>
                        </div>
                        <div class="text-5xl md:text-6xl opacity-80">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 w-full">
                <!-- Total Teams -->
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-l-4 border-blue-500 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Teams</p>
                            <p class="text-xl md:text-2xl font-bold text-gray-900">24</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i> 12% from last week
                            </p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <i class="fas fa-users text-blue-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-l-4 border-yellow-500 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Pending Approvals</p>
                            <p class="text-xl md:text-2xl font-bold text-gray-900">8</p>
                            <p class="text-xs text-yellow-600 mt-1">
                                <i class="fas fa-clock mr-1"></i> Needs attention
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Players -->
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-l-4 border-green-500 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Players</p>
                            <p class="text-xl md:text-2xl font-bold text-gray-900">156</p>
                            <p class="text-xs text-green-600 mt-1">
                                <i class="fas fa-arrow-up mr-1"></i> 5% from last week
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-lg">
                            <i class="fas fa-user-friends text-green-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Matches -->
                <div class="bg-white rounded-xl shadow-sm p-4 md:p-6 border-l-4 border-red-500 w-full">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Upcoming Matches</p>
                            <p class="text-xl md:text-2xl font-bold text-gray-900">6</p>
                            <p class="text-xs text-red-600 mt-1">
                                <i class="fas fa-calendar mr-1"></i> This week
                            </p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-lg">
                            <i class="fas fa-trophy text-red-600 text-lg md:text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 w-full">
                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6 w-full">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4 md:mb-6 flex items-center border-b pb-2">
                        <i class="fas fa-bell text-purple-600 mr-3"></i>
                        Recent Activity
                    </h2>
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex items-center space-x-3 md:space-x-4 p-2 md:p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-plus text-green-600 text-sm md:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm md:text-base">New team registration</p>
                                <p class="text-xs md:text-sm text-gray-500 truncate">Punta I Warriors registered 2 minutes ago</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">Just now</span>
                        </div>
                        
                        <div class="flex items-center space-x-3 md:space-x-4 p-2 md:p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-blue-600 text-sm md:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm md:text-base">Team approved</p>
                                <p class="text-xs md:text-sm text-gray-500 truncate">Amaya Titans approved 1 hour ago</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">1h ago</span>
                        </div>
                        
                        <div class="flex items-center space-x-3 md:space-x-4 p-2 md:p-3 hover:bg-gray-50 rounded-lg transition">
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-sm md:text-base"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 text-sm md:text-base">Pending approval</p>
                                <p class="text-xs md:text-sm text-gray-500 truncate">Bunga Bulls waiting for review</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">2h ago</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6 w-full">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4 md:mb-6 flex items-center border-b pb-2">
                        <i class="fas fa-bolt text-orange-600 mr-3"></i>
                        Quick Actions
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                        <a href="teams.php" class="p-3 md:p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition group w-full">
                            <div class="flex items-center space-x-2 md:space-x-3">
                                <div class="p-2 bg-blue-100 rounded-lg group-hover:scale-110 transition">
                                    <i class="fas fa-users text-blue-600"></i>
                                </div>
                                <span class="font-medium text-gray-900 text-sm md:text-base">Manage Teams</span>
                            </div>
                        </a>
                        
                        <a href="announcements.php" class="p-3 md:p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition group w-full">
                            <div class="flex items-center space-x-2 md:space-x-3">
                                <div class="p-2 bg-green-100 rounded-lg group-hover:scale-110 transition">
                                    <i class="fas fa-bullhorn text-green-600"></i>
                                </div>
                                <span class="font-medium text-gray-900 text-sm md:text-base">Announcements</span>
                            </div>
                        </a>
                        
                        <a href="matches.php" class="p-3 md:p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition group w-full">
                            <div class="flex items-center space-x-2 md:space-x-3">
                                <div class="p-2 bg-purple-100 rounded-lg group-hover:scale-110 transition">
                                    <i class="fas fa-trophy text-purple-600"></i>
                                </div>
                                <span class="font-medium text-gray-900 text-sm md:text-base">Schedule Matches</span>
                            </div>
                        </a>
                        
                        <a href="users.php" class="p-3 md:p-4 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 transition group w-full">
                            <div class="flex items-center space-x-2 md:space-x-3">
                                <div class="p-2 bg-red-100 rounded-lg group-hover:scale-110 transition">
                                    <i class="fas fa-user-cog text-red-600"></i>
                                </div>
                                <span class="font-medium text-gray-900 text-sm md:text-base">User Management</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>