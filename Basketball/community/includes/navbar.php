<?php
// Get the current filename (e.g., "matches.php")
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-center space-x-1">
            <a href="view_teams.php"
               class="nav-link px-4 py-3 text-sm font-medium 
               <?php echo ($current_page == 'view_teams.php') ? 'text-purple-600 border-b-2 border-purple-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-b-2 border-transparent hover:border-purple-500'; ?> transition">
                <i class="fas fa-users mr-2"></i>View All Teams
            </a>

            <a href="upcoming_matches.php"
               class="nav-link px-4 py-3 text-sm font-medium 
               <?php echo ($current_page == 'upcoming_matches.php') ? 'text-purple-600 border-b-2 border-purple-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-b-2 border-transparent hover:border-purple-500'; ?> transition">
                <i class="fas fa-calendar-alt mr-2"></i>Upcoming Matches
            </a>

            <a href="announcements.php"
               class="nav-link px-4 py-3 text-sm font-medium 
               <?php echo ($current_page == 'announcements.php') ? 'text-purple-600 border-b-2 border-purple-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-b-2 border-transparent hover:border-purple-500'; ?> transition">
                <i class="fas fa-bullhorn mr-2"></i>Announcements
            </a>

            <a href="player_statistics.php"
               class="nav-link px-4 py-3 text-sm font-medium 
               <?php echo ($current_page == 'player_statistics.php') ? 'text-purple-600 border-b-2 border-purple-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-b-2 border-transparent hover:border-purple-500'; ?> transition">
                <i class="fas fa-chart-line mr-2"></i>Player Statistics
            </a>
        </div>
    </div>
</nav>
