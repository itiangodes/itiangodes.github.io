<?php $current = basename($_SERVER['PHP_SELF']); ?>

<!-- Navigation -->
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-center space-x-1 overflow-x-auto">
            <a href="join_team.php"
               class="nav-link px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2 
               <?= $current === 'join_team.php' ? 'text-blue-600 border-blue-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-transparent hover:border-blue-500' ?>">
                <i class="fas fa-user-plus mr-2"></i>Join Team
            </a>
            <a href="upcoming_matches.php"
               class="nav-link px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2 
               <?= $current === 'upcoming_matches.php' ? 'text-blue-600 border-blue-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-transparent hover:border-blue-500' ?>">
                <i class="fas fa-calendar-alt mr-2"></i>Upcoming Matches
            </a>
            <a href="announcements.php"
               class="nav-link px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2 
               <?= $current === 'announcements.php' ? 'text-blue-600 border-blue-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-transparent hover:border-blue-500' ?>">
                <i class="fas fa-bullhorn mr-2"></i>Announcements
            </a>
            <a href="training_program.php"
               class="nav-link px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2 
               <?= $current === 'training_program.php' ? 'text-blue-600 border-blue-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-transparent hover:border-blue-500' ?>">
                <i class="fas fa-running mr-2"></i>Training Program
            </a>
            <a href="player_statistics.php"
               class="nav-link px-4 py-3 text-sm font-medium whitespace-nowrap transition border-b-2 
               <?= $current === 'player_statistics.php' ? 'text-blue-600 border-blue-500 bg-gray-100' : 'text-gray-700 hover:bg-gray-100 border-transparent hover:border-blue-500' ?>">
                <i class="fas fa-chart-line mr-2"></i>My Statistics
            </a>
        </div>
    </div>
</nav>
