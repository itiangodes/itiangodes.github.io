<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';
include 'includes/sidebar.php';

// Fetch statistics

// Total teams
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM teams");
$totalTeams = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Teams by approval status
$stmt = $pdo->query("SELECT approval_status, COUNT(*) AS cnt FROM teams GROUP BY approval_status");
$teamStatusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total announcements
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM announcements");
$totalAnnouncements = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total scoresheets - Using matches table instead since scoresheets doesn't exist
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM matches");
$totalMatches = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total brackets - Using tournament_brackets table
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM tournament_brackets");
$totalBrackets = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total tournaments
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM tournaments");
$totalTournaments = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total bracket teams (tournament participants)
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM bracket_teams");
$totalBracketTeams = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total bracket matches
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM bracket_matches");
$totalBracketMatches = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total users by role
$stmt = $pdo->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role");
$userRoleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total players and coaches
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM players");
$totalPlayers = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM coaches");
$totalCoaches = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total training programs
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM training_programs");
$totalTrainingPrograms = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total games
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM games");
$totalGames = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total training activities
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM training_activities");
$totalTrainingActivities = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total player stats records
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM player_stats");
$totalPlayerStats = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

// Total player_teams records (team memberships)
$stmt = $pdo->query("SELECT COUNT(*) AS cnt FROM player_teams");
$totalPlayerTeams = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">Statistics Dashboard</h1>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-8">
        <!-- Main Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Teams -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Teams</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalTeams ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Approved Teams -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Approved Teams</p>
                        <?php
                            $approved = 0;
                            foreach ($teamStatusCounts as $row) {
                                if ($row['approval_status'] === 'approved') {
                                    $approved = $row['cnt'];
                                }
                            }
                        ?>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $approved ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Matches -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Matches</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalMatches ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Brackets -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Tournament Brackets</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalBrackets ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-sitemap text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Tournaments -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Tournaments</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalTournaments ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-trophy text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Announcements -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Announcements</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalAnnouncements ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bullhorn text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Total Users</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= array_sum(array_column($userRoleCounts, 'cnt')) ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-friends text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Players -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Players</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalPlayers ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-running text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Coaches -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Coaches</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalCoaches ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clipboard-list text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Games -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Games Recorded</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalGames ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-basketball-ball text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Training Programs -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Training Programs</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalTrainingPrograms ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-teal-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dumbbell text-teal-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Training Activities -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Training Activities</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalTrainingActivities ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-cyan-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fourth Row Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Player Stats Records -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Player Stats</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalPlayerStats ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-bar text-pink-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tournament Teams -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Tournament Teams</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalBracketTeams ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-flag text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Tournament Matches -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Tournament Matches</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalBracketMatches ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chess text-orange-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Team Memberships -->
            <div class="stat-card bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-sm">Team Memberships</p>
                        <h3 class="text-3xl font-bold text-gray-800"><?= $totalPlayerTeams ?></h3>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-gray-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Table -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Complete Statistics Overview</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full table-auto">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Category</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Metric</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-gray-700">Count</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status/Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <!-- User Statistics -->
                        <tr class="bg-blue-50">
                            <td class="px-4 py-3 font-medium text-blue-800" rowspan="5">Users</td>
                            <td class="px-4 py-3 text-gray-600">Total Users</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= array_sum(array_column($userRoleCounts, 'cnt')) ?></td>
                            <td class="px-4 py-3 text-gray-600">All registered users</td>
                        </tr>
                        <?php foreach ($userRoleCounts as $row): ?>
                        <tr class="bg-blue-50">
                            <td class="px-4 py-3 text-gray-600 capitalize"><?= htmlspecialchars($row['role']) ?>s</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $row['cnt'] ?></td>
                            <td class="px-4 py-3 text-gray-600">
                                <?php 
                                    $statusCount = match($row['role']) {
                                        'admin' => 'System administrators',
                                        'coach' => 'Team coaches',
                                        'player' => 'Basketball players',
                                        'community' => 'Community members',
                                        default => 'Other users'
                                    };
                                    echo $statusCount;
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Team Statistics -->
                        <tr class="bg-green-50">
                            <td class="px-4 py-3 font-medium text-green-800" rowspan="4">Teams</td>
                            <td class="px-4 py-3 text-gray-600">Total Teams</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalTeams ?></td>
                            <td class="px-4 py-3 text-gray-600">All created teams</td>
                        </tr>
                        <?php 
                        $approved = 0;
                        $pending = 0;
                        $rejected = 0;
                        foreach ($teamStatusCounts as $row) {
                            switch($row['approval_status']) {
                                case 'approved': $approved = $row['cnt']; break;
                                case 'pending': $pending = $row['cnt']; break;
                                case 'rejected': $rejected = $row['cnt']; break;
                            }
                        }
                        ?>
                        <tr class="bg-green-50">
                            <td class="px-4 py-3 text-gray-600">Approved Teams</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $approved ?></td>
                            <td class="px-4 py-3"><span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Active</span></td>
                        </tr>
                        <tr class="bg-green-50">
                            <td class="px-4 py-3 text-gray-600">Pending Teams</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $pending ?></td>
                            <td class="px-4 py-3"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs">Awaiting Approval</span></td>
                        </tr>
                        <tr class="bg-green-50">
                            <td class="px-4 py-3 text-gray-600">Rejected Teams</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $rejected ?></td>
                            <td class="px-4 py-3"><span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Not Approved</span></td>
                        </tr>

                        <!-- Tournament Statistics -->
                        <tr class="bg-purple-50">
                            <td class="px-4 py-3 font-medium text-purple-800" rowspan="4">Tournaments</td>
                            <td class="px-4 py-3 text-gray-600">Total Tournaments</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalTournaments ?></td>
                            <td class="px-4 py-3 text-gray-600">All tournaments created</td>
                        </tr>
                        <tr class="bg-purple-50">
                            <td class="px-4 py-3 text-gray-600">Tournament Teams</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalBracketTeams ?></td>
                            <td class="px-4 py-3 text-gray-600">Teams participating in tournaments</td>
                        </tr>
                        <tr class="bg-purple-50">
                            <td class="px-4 py-3 text-gray-600">Tournament Matches</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalBracketMatches ?></td>
                            <td class="px-4 py-3 text-gray-600">Matches scheduled in tournaments</td>
                        </tr>
                        <tr class="bg-purple-50">
                            <td class="px-4 py-3 text-gray-600">Tournament Brackets</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalBrackets ?></td>
                            <td class="px-4 py-3 text-gray-600">Tournament bracket structures</td>
                        </tr>

                        <!-- Player & Coach Statistics -->
                        <tr class="bg-orange-50">
                            <td class="px-4 py-3 font-medium text-orange-800" rowspan="4">Players & Coaches</td>
                            <td class="px-4 py-3 text-gray-600">Total Players</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalPlayers ?></td>
                            <td class="px-4 py-3 text-gray-600">Registered basketball players</td>
                        </tr>
                        <tr class="bg-orange-50">
                            <td class="px-4 py-3 text-gray-600">Total Coaches</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalCoaches ?></td>
                            <td class="px-4 py-3 text-gray-600">Registered team coaches</td>
                        </tr>
                        <tr class="bg-orange-50">
                            <td class="px-4 py-3 text-gray-600">Player Stats Records</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalPlayerStats ?></td>
                            <td class="px-4 py-3 text-gray-600">Individual player statistics</td>
                        </tr>
                        <tr class="bg-orange-50">
                            <td class="px-4 py-3 text-gray-600">Team Memberships</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalPlayerTeams ?></td>
                            <td class="px-4 py-3 text-gray-600">Player-team associations</td>
                        </tr>

                        <!-- Training Statistics -->
                        <tr class="bg-teal-50">
                            <td class="px-4 py-3 font-medium text-teal-800" rowspan="3">Training</td>
                            <td class="px-4 py-3 text-gray-600">Training Programs</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalTrainingPrograms ?></td>
                            <td class="px-4 py-3 text-gray-600">Active training programs</td>
                        </tr>
                        <tr class="bg-teal-50">
                            <td class="px-4 py-3 text-gray-600">Training Activities</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalTrainingActivities ?></td>
                            <td class="px-4 py-3 text-gray-600">Scheduled training sessions</td>
                        </tr>
                        <tr class="bg-teal-50">
                            <td class="px-4 py-3 text-gray-600">Games Recorded</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalGames ?></td>
                            <td class="px-4 py-3 text-gray-600">Games tracked in system</td>
                        </tr>

                        <!-- Content Statistics -->
                        <tr class="bg-indigo-50">
                            <td class="px-4 py-3 font-medium text-indigo-800" rowspan="3">Content</td>
                            <td class="px-4 py-3 text-gray-600">Announcements</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalAnnouncements ?></td>
                            <td class="px-4 py-3 text-gray-600">System announcements posted</td>
                        </tr>
                        <tr class="bg-indigo-50">
                            <td class="px-4 py-3 text-gray-600">Matches</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalMatches ?></td>
                            <td class="px-4 py-3 text-gray-600">Tournament matches created</td>
                        </tr>
                        <tr class="bg-indigo-50">
                            <td class="px-4 py-3 text-gray-600">Bracket Matches</td>
                            <td class="px-4 py-3 text-right font-semibold"><?= $totalBracketMatches ?></td>
                            <td class="px-4 py-3 text-gray-600">Bracket-specific matches</td>
                        </tr>

                        <!-- Summary Row -->
                        <tr class="bg-gray-100 border-t-2 border-gray-300">
                            <td class="px-4 py-3 font-bold text-gray-800">Total Records</td>
                            <td class="px-4 py-3 font-medium text-gray-700">All System Data</td>
                            <td class="px-4 py-3 text-right font-bold text-lg text-gray-800">
                                <?= 
                                    array_sum(array_column($userRoleCounts, 'cnt')) + 
                                    $totalTeams + 
                                    $totalTournaments + 
                                    $totalBracketTeams + 
                                    $totalBracketMatches + 
                                    $totalPlayers + 
                                    $totalCoaches + 
                                    $totalTrainingPrograms + 
                                    $totalTrainingActivities + 
                                    $totalGames + 
                                    $totalPlayerStats + 
                                    $totalPlayerTeams +
                                    $totalAnnouncements + 
                                    $totalMatches +
                                    $totalBrackets
                                ?>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-700">Combined total across all categories</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Additional Statistics Sections -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tournament Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Tournament Statistics</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Total Tournaments</span>
                        <span class="font-semibold"><?= $totalTournaments ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Tournament Teams</span>
                        <span class="font-semibold"><?= $totalBracketTeams ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Tournament Matches</span>
                        <span class="font-semibold"><?= $totalBracketMatches ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b">
                        <span class="text-gray-600">Tournament Brackets</span>
                        <span class="font-semibold"><?= $totalBrackets ?></span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-600">Training Programs</span>
                        <span class="font-semibold"><?= $totalTrainingPrograms ?></span>
                    </div>
                </div>
            </div>

            <!-- Team Status Distribution -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 text-gray-800">Team Status Distribution</h2>
                <div class="space-y-3">
                    <?php foreach ($teamStatusCounts as $row): 
                        $statusClass = match($row['approval_status']) {
                            'approved' => 'bg-green-100 text-green-800',
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    ?>
                        <div class="flex justify-between items-center py-2 border-b">
                            <span class="capitalize px-3 py-1 rounded-full text-sm font-medium <?= $statusClass ?>">
                                <?= htmlspecialchars($row['approval_status']) ?>
                            </span>
                            <span class="font-semibold text-lg"><?= $row['cnt'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- User Role Distribution -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">User Role Distribution</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach ($userRoleCounts as $row): 
                    $roleClass = match($row['role']) {
                        'admin' => 'bg-red-100 text-red-800 border-red-200',
                        'coach' => 'bg-orange-100 text-orange-800 border-orange-200',
                        'player' => 'bg-green-100 text-green-800 border-green-200',
                        'community' => 'bg-blue-100 text-blue-800 border-blue-200',
                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                    };
                ?>
                    <div class="text-center p-4 rounded-lg border-2 <?= $roleClass ?>">
                        <div class="text-2xl font-bold mb-1"><?= $row['cnt'] ?></div>
                        <div class="text-sm font-medium capitalize"><?= htmlspecialchars($row['role']) ?>s</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Data Summary -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Data Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-2xl font-bold text-blue-600 mb-1"><?= $totalMatches ?></div>
                    <div class="text-sm font-medium text-blue-800">Matches</div>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-2xl font-bold text-green-600 mb-1"><?= $totalGames ?></div>
                    <div class="text-sm font-medium text-green-800">Games Recorded</div>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <div class="text-2xl font-bold text-purple-600 mb-1"><?= $totalTrainingActivities ?></div>
                    <div class="text-sm font-medium text-purple-800">Training Activities</div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>