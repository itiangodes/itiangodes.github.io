<!-- Navigation -->
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-center space-x-1 overflow-x-auto">
            <a href="create_team.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'create_team.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-plus-circle mr-2"></i>Create Team
            </a>
            <a href="team_formation.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'team_formation.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-users mr-2"></i>Team Formation
            </a>
            <a href="upcoming_matches.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'upcoming_matches.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-calendar-alt mr-2"></i>Upcoming Matches
            </a>
            <a href="announcements.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-bullhorn mr-2"></i>Announcements
            </a>
            <a href="training_program.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'training_program.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-running mr-2"></i>Training Program
            </a>
            <a href="player_statistics.php" class="nav-link px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'player_statistics.php' ? 'border-b-2 border-green-500' : 'border-b-2 border-transparent hover:border-green-500' ?> transition whitespace-nowrap">
                <i class="fas fa-chart-line mr-2"></i>Player Statistics
            </a>
        </div>
    </div>
</nav>
