<?php
// simulate_bracket.php
include 'includes/auth.php';
include 'includes/db.php';

// Check if tournament ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "No tournament specified";
    header("Location: tournament.php");
    exit();
}

$tournament_id = $_GET['id'];

// Fetch tournament details
try {
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ? AND created_by = ?");
    $stmt->execute([$tournament_id, $_SESSION['user_id']]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$tournament) {
        $_SESSION['error'] = "Tournament not found or access denied";
        header("Location: tournament.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching tournament: " . $e->getMessage();
    header("Location: tournament.php");
    exit();
}

// Fetch teams for this tournament
try {
    $teams_stmt = $pdo->prepare("SELECT * FROM teams WHERE tournament_id = ? ORDER BY id");
    $teams_stmt->execute([$tournament_id]);
    $teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $teams = [];
}

// Fetch existing matches (use the bracket that was generated during setup)
$matches = getTournamentMatches($pdo, $tournament_id);

// DEBUG: Check if we need to generate matches
if (empty($matches) && !empty($teams)) {
    error_log("No matches found for tournament $tournament_id, but teams exist. Attempting to generate bracket...");
    $bracket_generated = generateBracketIfMissing($pdo, $tournament_id, $teams, $tournament['format']);
    if ($bracket_generated) {
        $matches = getTournamentMatches($pdo, $tournament_id);
        error_log("Bracket generated successfully. Now found " . count($matches) . " matches.");
    }
}

// Handle simulation actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (empty($teams)) {
        $_SESSION['error'] = "Cannot simulate - no teams available";
    } else {
        if ($action === 'simulate_round') {
            simulateNextRound($pdo, $tournament_id, $teams);
        } elseif ($action === 'reset_simulation') {
            resetSimulation($pdo, $tournament_id);
        } elseif ($action === 'simulate_all') {
            simulateEntireTournament($pdo, $tournament_id, $teams);
        } elseif ($action === 'update_score') {
            updateMatchScore($pdo, $_POST['match_id'], $_POST['team1_score'], $_POST['team2_score']);
        } elseif ($action === 'generate_bracket') {
            // Manual bracket generation
            if (generateBracketIfMissing($pdo, $tournament_id, $teams, $tournament['format'])) {
                $_SESSION['success'] = "Bracket generated successfully!";
            } else {
                $_SESSION['error'] = "Failed to generate bracket. Check error logs.";
            }
        } elseif ($action === 'schedule_match') {
            scheduleMatch($pdo, $_POST['match_id'], $_POST['match_date'], $_POST['match_time'], $_POST['match_location']);
        }
    }
    
    header("Location: simulate_bracket.php?id=" . $tournament_id);
    exit();
}

// Refresh matches after actions
$matches = getTournamentMatches($pdo, $tournament_id);

// Sync tournament status to ensure it's current
syncTournamentStatus($pdo, $tournament_id);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tournament Bracket</h1>
                <p class="text-gray-600"><?php echo htmlspecialchars($tournament['name']); ?> - Interactive Bracket</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-red-800">Error</h4>
                            <p class="text-sm text-red-700 mt-1"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-green-800">Success</h4>
                            <p class="text-sm text-green-700 mt-1"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tournament Status Banner -->
            <?php if ($tournament['status'] === 'completed'): ?>
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-flag-checkered text-green-600 mr-3 text-xl"></i>
                        <div>
                            <h4 class="text-lg font-bold text-green-800">Tournament Completed! 🏆</h4>
                            <p class="text-green-700 mt-1">All matches have been completed. The tournament is finished.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tournament Info Card -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    Tournament Information
                </h2>
                
                <?php if (empty($teams)): ?>
                    <!-- No Teams State -->
                    <div class="text-center py-8">
                        <i class="fas fa-users text-6xl text-gray-400 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Teams Available</h3>
                        <p class="text-gray-500 mb-4">This tournament doesn't have any teams assigned yet.</p>
                        <div class="flex gap-3 justify-center">
                            <a href="tournament_manage.php?id=<?php echo $tournament_id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center gap-2">
                                <i class="fas fa-cog"></i> Manage Tournament
                            </a>
                            <a href="team_setup.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center gap-2">
                                <i class="fas fa-users"></i> Assign Teams
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Tournament Progress -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-semibold text-gray-800">Tournament Progress</h3>
                            <span class="text-sm font-medium <?php echo $tournament['status'] === 'completed' ? 'text-green-600' : 'text-blue-600'; ?>">
                                Status: <?php echo ucfirst($tournament['status']); ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo calculateTournamentProgress($matches); ?>%"></div>
                            </div>
                            <span class="text-sm text-gray-600 ml-4"><?php echo calculateTournamentProgress($matches); ?>% Complete</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span><?php echo count($teams); ?> Teams</span>
                            <span><?php echo count($matches); ?> Matches</span>
                            <span><?php echo getCompletedMatchesCount($matches); ?> Completed</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Interactive Visual Bracket -->
            <?php if (!empty($teams) && !empty($matches)): ?>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <i class="fas fa-trophy text-yellow-500"></i>
                            Tournament Bracket - <?php echo $tournament['team_count']; ?> Teams
                        </h2>
                        <div class="text-sm text-gray-500">
                            <?php echo getCompletedMatchesCount($matches); ?> of <?php echo count($matches); ?> matches completed
                        </div>
                    </div>
                    
                    <!-- Interactive Bracket Container -->
                    <div class="bracket-container">
                        <?php echo renderTrueBracket($matches, $teams, $tournament_id, $tournament['format']); ?>
                    </div>
                </div>

                <!-- MATCH SCHEDULER SECTION -->
                <div class="bg-white rounded-xl shadow-md p-6 mt-6">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-purple-500"></i>
                        Match Scheduler
                    </h2>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Unscheduled Matches -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center gap-2">
                                <i class="fas fa-clock text-orange-500"></i>
                                Unscheduled Matches
                            </h3>
                            
                            <?php
                            $unscheduled_matches = array_filter($matches, function($match) {
                                return empty($match['scheduled_date']) && empty($match['scheduled_time']) && 
                                       $match['team1_id'] && $match['team2_id'] && 
                                       $match['status'] !== 'completed';
                            });
                            
                            if (empty($unscheduled_matches)): ?>
                                <div class="text-center py-4 text-gray-500">
                                    <i class="fas fa-check-circle text-green-500 text-xl mb-2"></i>
                                    <p>All matches are scheduled!</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <?php foreach ($unscheduled_matches as $match): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-800">Match <?php echo $match['match_number']; ?> - Round <?php echo $match['round_number']; ?></div>
                                                    <div class="text-sm text-gray-600">
                                                        <?php echo $match['team1_name'] ?? 'TBD'; ?> vs <?php echo $match['team2_name'] ?? 'TBD'; ?>
                                                    </div>
                                                </div>
                                                <button type="button" 
                                                        onclick="openSchedulerModal(<?php echo $match['id']; ?>, 'Match <?php echo $match['match_number']; ?>', '<?php echo $match['team1_name'] ?? 'TBD'; ?>', '<?php echo $match['team2_name'] ?? 'TBD'; ?>')"
                                                        class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200 flex items-center gap-1">
                                                    <i class="fas fa-calendar-plus"></i> Schedule
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Scheduled Matches -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 flex items-center gap-2">
                                <i class="fas fa-calendar-check text-green-500"></i>
                                Scheduled Matches
                            </h3>
                            
                            <?php
                            $scheduled_matches = array_filter($matches, function($match) {
                                return (!empty($match['scheduled_date']) || !empty($match['scheduled_time'])) && 
                                       $match['team1_id'] && $match['team2_id'];
                            });
                            
                            // Sort by date and time
                            usort($scheduled_matches, function($a, $b) {
                                $dateA = $a['scheduled_date'] . ' ' . $a['scheduled_time'];
                                $dateB = $b['scheduled_date'] . ' ' . $b['scheduled_time'];
                                return strtotime($dateA) - strtotime($dateB);
                            });
                            
                            if (empty($scheduled_matches)): ?>
                                <div class="text-center py-4 text-gray-500">
                                    <i class="fas fa-calendar-times text-gray-400 text-xl mb-2"></i>
                                    <p>No matches scheduled yet</p>
                                </div>
                            <?php else: ?>
                                <div class="space-y-3 max-h-96 overflow-y-auto">
                                    <?php foreach ($scheduled_matches as $match): ?>
                                        <div class="bg-white border border-green-200 rounded-lg p-3">
                                            <div class="flex justify-between items-start mb-2">
                                                <div class="flex-1">
                                                    <div class="font-medium text-gray-800">Match <?php echo $match['match_number']; ?> - Round <?php echo $match['round_number']; ?></div>
                                                    <div class="text-sm text-gray-600 mb-1">
                                                        <?php echo $match['team1_name'] ?? 'TBD'; ?> vs <?php echo $match['team2_name'] ?? 'TBD'; ?>
                                                    </div>
                                                    <div class="text-xs text-green-600 font-medium">
                                                        <i class="fas fa-calendar-day mr-1"></i>
                                                        <?php 
                                                        if (!empty($match['scheduled_date'])) {
                                                            echo date('M j, Y', strtotime($match['scheduled_date']));
                                                            if (!empty($match['scheduled_time'])) {
                                                                echo ' at ' . date('g:i A', strtotime($match['scheduled_time']));
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php if (!empty($match['location'])): ?>
                                                        <div class="text-xs text-blue-600 mt-1">
                                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                                            <?php echo htmlspecialchars($match['location']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex flex-col gap-2">
                                                    <button type="button" 
                                                            onclick="openSchedulerModal(<?php echo $match['id']; ?>, 'Match <?php echo $match['match_number']; ?>', '<?php echo $match['team1_name'] ?? 'TBD'; ?>', '<?php echo $match['team2_name'] ?? 'TBD'; ?>', '<?php echo $match['scheduled_date']; ?>', '<?php echo $match['scheduled_time']; ?>', '<?php echo $match['location']; ?>')"
                                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200 flex items-center gap-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <!-- GENERATE SCORESHEET BUTTON -->
                                                    <button type="button" 
                                                            onclick="generateScoresheet(<?php echo $match['id']; ?>, '<?php echo $match['team1_name'] ?? 'TBD'; ?>', '<?php echo $match['team2_name'] ?? 'TBD'; ?>')"
                                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition duration-200 flex items-center gap-1">
                                                        <i class="fas fa-file-alt"></i> Scoresheet
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif (!empty($teams) && empty($matches)): ?>
                <!-- Teams exist but no bracket found -->
                <div class="bg-white rounded-xl shadow-md p-6 text-center">
                    <i class="fas fa-project-diagram text-6xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Bracket Not Generated</h3>
                    <p class="text-gray-500 mb-4">The tournament bracket was not generated during setup.</p>
                    <div class="flex gap-3 justify-center">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="generate_bracket">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center gap-2">
                                <i class="fas fa-magic"></i> Generate Bracket Now
                            </button>
                        </form>
                        <a href="tournament_manage.php?id=<?php echo $tournament_id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center gap-2">
                            <i class="fas fa-cog"></i> Manage Tournament
                        </a>
                    </div>
                    
                    <!-- Debug Information -->
                    <div class="mt-6 p-4 bg-gray-100 rounded-lg text-left max-w-2xl mx-auto">
                        <h4 class="font-bold text-gray-800 mb-2">Debug Information:</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <strong>Tournament ID:</strong> <?php echo $tournament_id; ?>
                            </div>
                            <div>
                                <strong>Teams Found:</strong> <?php echo count($teams); ?>
                            </div>
                            <div>
                                <strong>Matches Found:</strong> <?php echo count($matches); ?>
                            </div>
                            <div>
                                <strong>Format:</strong> <?php echo $tournament['format']; ?>
                            </div>
                        </div>
                        <?php if (count($teams) > 0): ?>
                            <div class="mt-3">
                                <strong>Teams:</strong>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    <?php foreach ($teams as $index => $team): ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">#<?php echo $index + 1; ?> <?php echo $team['barangay']; ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Scheduler Modal -->
<div id="schedulerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Schedule Match</h3>
            <button type="button" onclick="closeSchedulerModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="scheduleForm" method="POST">
            <input type="hidden" name="action" value="schedule_match">
            <input type="hidden" name="match_id" id="matchId">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Match Details</label>
                <div id="matchDetails" class="bg-gray-50 p-3 rounded-lg text-sm text-gray-600"></div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="match_date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                    <input type="date" id="match_date" name="match_date" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <p class="text-xs text-gray-500 mt-1">Today or future dates only</p>
                </div>
                <div>
                    <label for="match_time" class="block text-sm font-medium text-gray-700 mb-1">Time</label>
                    <input type="time" id="match_time" name="match_time" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
            </div>
            
            <div class="mb-6">
                <label for="match_location" class="block text-sm font-medium text-gray-700 mb-1">Location/Venue</label>
                <input type="text" id="match_location" name="match_location" placeholder="e.g., Main Court, Gymnasium" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeSchedulerModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                    Cancel
                </button>
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-calendar-check"></i> Schedule Match
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scoresheet Modal -->
<div id="scoresheetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-7xl h-[90vh] mx-4 flex flex-col">
        <div class="flex justify-between items-center border-b p-4">
            <h3 class="text-xl font-bold text-gray-800" id="scoresheetModalTitle">FIBA Basketball Scoresheet</h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full" id="scoresheetMatchId"></span>
                <button type="button" onclick="closeScoresheetModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>
        
        <div class="flex-1 overflow-hidden">
            <iframe id="scoresheetFrame" src="" class="w-full h-full border-0" frameborder="0"></iframe>
        </div>
        
        <div class="border-t p-4 bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="closeScoresheetModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                Close
            </button>
            <button type="button" onclick="printScoresheetFromModal()" class="bg-yellow-400 text-blue-900 px-4 py-2 rounded font-semibold hover:bg-yellow-500">
                <i class="fas fa-print mr-2"></i> Print Scoresheet
            </button>
        </div>
    </div>
</div>

<style>
.bracket-container {
    @apply overflow-x-auto py-6;
}

.bracket-preview-container {
    transform: scale(0.9);
    transform-origin: top center;
}

.round {
    min-height: 500px;
}

.match {
    transition: all 0.2s ease;
}

.match:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.connector {
    min-height: 500px;
}

.champion-trophy {
    position: relative;
    overflow: hidden;
}

.champion-trophy::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: rotate(45deg) translateX(-100%); }
    100% { transform: rotate(45deg) translateX(100%); }
}

.champion-section {
    min-height: 500px;
}

.match-info {
    @apply flex justify-between items-center mb-3 text-xs text-gray-500;
}

.match-number {
    @apply font-mono font-bold;
}

.match-status {
    @apply px-2 py-1 rounded-full text-xs font-medium;
}

.status-scheduled {
    @apply bg-blue-100 text-blue-800;
}

.status-completed {
    @apply bg-green-100 text-green-800;
}

.status-pending {
    @apply bg-yellow-100 text-yellow-800;
}

.edit-score-btn {
    @apply bg-gray-600 hover:bg-gray-700 text-white py-1 px-3 rounded text-sm font-medium transition duration-200 flex items-center gap-1;
}

.waiting-text {
    @apply text-xs text-gray-500 italic;
}

/* Seed badge styling */
.seed-badge {
    font-size: 0.7rem;
    font-weight: bold;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border-radius: 4px;
    padding: 2px 6px;
    min-width: 24px;
    text-align: center;
    margin-right: 8px;
}

.team-preview-item .seed-badge {
    margin-right: 8px;
}

/* Custom scrollbar */
.bracket-container::-webkit-scrollbar {
    height: 8px;
}

.bracket-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.bracket-container::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.bracket-container::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Team card hover effects */
.bg-white.border-2.border-blue-200:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Scheduler styles */
.max-h-96 {
    max-height: 24rem;
}

/* Scoresheet Modal Styles */
#scoresheetModal {
    z-index: 60;
}

#scoresheetFrame {
    min-height: 500px;
}

/* Ensure modal content is scrollable */
.modal-content {
    max-height: calc(100vh - 100px);
}
</style>

<script>
// Bracket interaction functions
function enableMatchEditing(matchId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';
    
    const actionInput = document.createElement('input');
    actionInput.name = 'action';
    actionInput.value = 'update_score';
    form.appendChild(actionInput);
    
    const matchInput = document.createElement('input');
    matchInput.name = 'match_id';
    matchInput.value = matchId;
    form.appendChild(matchInput);
    
    const team1Score = prompt('Enter score for Team 1:');
    const team2Score = prompt('Enter score for Team 2:');
    
    if (team1Score !== null && team2Score !== null) {
        const score1Input = document.createElement('input');
        score1Input.name = 'team1_score';
        score1Input.value = team1Score;
        form.appendChild(score1Input);
        
        const score2Input = document.createElement('input');
        score2Input.name = 'team2_score';
        score2Input.value = team2Score;
        form.appendChild(score2Input);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Scheduler Modal Functions
function openSchedulerModal(matchId, matchTitle, team1, team2, date = '', time = '', location = '') {
    document.getElementById('matchId').value = matchId;
    document.getElementById('modalTitle').textContent = `Schedule ${matchTitle}`;
    document.getElementById('matchDetails').innerHTML = `
        <strong>${team1}</strong> vs <strong>${team2}</strong>
    `;
    
    // Set current values if editing
    document.getElementById('match_date').value = date;
    document.getElementById('match_time').value = time;
    document.getElementById('match_location').value = location || '';
    
    // Set minimum date to today to prevent past dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('match_date').min = today;
    
    // Show modal
    document.getElementById('schedulerModal').classList.remove('hidden');
}

function closeSchedulerModal() {
    document.getElementById('schedulerModal').classList.add('hidden');
    // Reset form
    document.getElementById('scheduleForm').reset();
}

// Generate Scoresheet Function - UPDATED FOR MODAL
function generateScoresheet(matchId, team1Name, team2Name) {
    // Encode team names for URL
    const encodedTeam1 = encodeURIComponent(team1Name);
    const encodedTeam2 = encodeURIComponent(team2Name);
    
    // Set modal title and match ID
    document.getElementById('scoresheetModalTitle').textContent = `FIBA Basketball Scoresheet - ${team1Name} vs ${team2Name}`;
    document.getElementById('scoresheetMatchId').textContent = `Match ID: ${matchId}`;
    
    // Load scoresheet in iframe
    const url = `scoresheets.php?match_id=${matchId}&team1=${encodedTeam1}&team2=${encodedTeam2}&modal=true`;
    document.getElementById('scoresheetFrame').src = url;
    
    // Show modal
    document.getElementById('scoresheetModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    console.log('Opening scoresheet for Match ' + matchId + ': ' + team1Name + ' vs ' + team2Name);
}

function closeScoresheetModal() {
    document.getElementById('scoresheetModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    // Clear iframe source
    document.getElementById('scoresheetFrame').src = '';
}

function printScoresheetFromModal() {
    const iframe = document.getElementById('scoresheetFrame');
    if (iframe.contentWindow) {
        iframe.contentWindow.print();
    }
}

// Close modals when clicking outside
document.getElementById('schedulerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSchedulerModal();
    }
});

document.getElementById('scoresheetModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeScoresheetModal();
    }
});

// Form validation to prevent past dates
document.getElementById('scheduleForm').addEventListener('submit', function(e) {
    const dateInput = document.getElementById('match_date');
    const selectedDate = new Date(dateInput.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Reset time part for accurate comparison
    
    if (dateInput.value && selectedDate < today) {
        e.preventDefault();
        alert('Cannot schedule matches in the past. Please select today or a future date.');
        dateInput.focus();
        return false;
    }
    
    // Validate that if time is provided, date must also be provided
    const timeInput = document.getElementById('match_time');
    if (timeInput.value && !dateInput.value) {
        e.preventDefault();
        alert('Please select a date when specifying a time.');
        dateInput.focus();
        return false;
    }
    
    // Validate required date field
    if (!dateInput.value) {
        e.preventDefault();
        alert('Please select a date for the match.');
        dateInput.focus();
        return false;
    }
    
    return true;
});

// Initialize bracket interactions
document.addEventListener('DOMContentLoaded', function() {
    const matches = document.querySelectorAll('.match');
    matches.forEach(match => {
        match.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        match.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Set minimum date on page load for any existing date inputs
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.min = today;
    });
});
</script>

<?php 
include 'includes/footer.php';

// Helper Functions

// Add function to get team seeds
function getTeamSeeds($teams) {
    $seeds = [];
    foreach ($teams as $index => $team) {
        $seeds[$team['id']] = $index + 1;
    }
    return $seeds;
}

// Function to automatically update tournament status when all matches are complete
function updateTournamentStatus($pdo, $tournament_id) {
    try {
        // Get total matches and completed matches
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_matches,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_matches
            FROM matches 
            WHERE tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        $match_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if all matches are completed
        if ($match_stats['total_matches'] > 0 && 
            $match_stats['completed_matches'] == $match_stats['total_matches']) {
            
            // Update tournament status to completed
            $update_stmt = $pdo->prepare("
                UPDATE tournaments 
                SET status = 'completed', updated_at = NOW() 
                WHERE id = ? AND status != 'completed'
            ");
            $update_stmt->execute([$tournament_id]);
            
            error_log("Tournament $tournament_id automatically marked as completed - all matches finished");
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error updating tournament status: " . $e->getMessage());
        return false;
    }
}

// Function to ensure tournament status is up-to-date
function syncTournamentStatus($pdo, $tournament_id) {
    try {
        // Check current tournament status
        $stmt = $pdo->prepare("SELECT status FROM tournaments WHERE id = ?");
        $stmt->execute([$tournament_id]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If tournament is active, check if it should be completed
        if ($tournament && $tournament['status'] === 'active') {
            updateTournamentStatus($pdo, $tournament_id);
        }
    } catch (Exception $e) {
        error_log("Error syncing tournament status: " . $e->getMessage());
    }
}

function getTournamentMatches($pdo, $tournament_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   t1.barangay as team1_name, 
                   t2.barangay as team2_name,
                   w.barangay as winner_name
            FROM matches m
            LEFT JOIN teams t1 ON m.team1_id = t1.id
            LEFT JOIN teams t2 ON m.team2_id = t2.id
            LEFT JOIN teams w ON m.winner_id = w.id
            WHERE m.tournament_id = ?
            ORDER BY m.round_number, m.match_number
        ");
        $stmt->execute([$tournament_id]);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found " . count($matches) . " matches for tournament $tournament_id");
        
        if (empty($matches)) {
            error_log("No matches found for tournament $tournament_id. Checking if tournament exists...");
            
            // Check if tournament exists
            $checkTournament = $pdo->prepare("SELECT id FROM tournaments WHERE id = ?");
            $checkTournament->execute([$tournament_id]);
            $tournament_exists = $checkTournament->fetch();
            
            if (!$tournament_exists) {
                error_log("Tournament $tournament_id does not exist");
            } else {
                error_log("Tournament $tournament_id exists but has no matches");
            }
        }
        
        return $matches;
    } catch (Exception $e) {
        error_log("Error fetching matches for tournament $tournament_id: " . $e->getMessage());
        return [];
    }
}

function generateBracketIfMissing($pdo, $tournament_id, $teams, $format) {
    try {
        error_log("Attempting to generate bracket for tournament $tournament_id with format $format");
        
        // Check if matches table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'matches'");
        if (!$tableCheck->fetch()) {
            error_log("Matches table does not exist");
            return false;
        }
        
        // Use the bracket creation functions
        if ($format === 'single') {
            return createSingleEliminationBracket($pdo, $tournament_id, $teams);
        } elseif ($format === 'double') {
            return createDoubleEliminationBracket($pdo, $tournament_id, $teams);
        } elseif ($format === 'roundrobin') {
            return createRoundRobinBracket($pdo, $tournament_id, $teams);
        } else {
            // Default to single elimination
            return createSingleEliminationBracket($pdo, $tournament_id, $teams);
        }
        
    } catch (Exception $e) {
        error_log("Error generating bracket: " . $e->getMessage());
        return false;
    }
}

function createSingleEliminationBracket($pdo, $tournament_id, $teams) {
    $team_count = count($teams);
    
    // Calculate number of rounds and bracket size
    $rounds = ceil(log($team_count, 2));
    $bracket_size = pow(2, $rounds);
    
    // Shuffle teams for random seeding
    shuffle($teams);
    
    $match_number = 1;
    
    // First round - place teams in matches
    $first_round_matches = $bracket_size / 2;
    
    // Debug logging
    error_log("Creating bracket for tournament $tournament_id: $team_count teams, $rounds rounds, $first_round_matches first-round matches");
    
    for ($i = 0; $i < $first_round_matches; $i++) {
        $team1_index = $i * 2;
        $team2_index = $i * 2 + 1;
        
        $team1_id = ($team1_index < $team_count) ? $teams[$team1_index]['id'] : null;
        $team2_id = ($team2_index < $team_count) ? $teams[$team2_index]['id'] : null;
        
        // Determine status - if one team is null, it's a bye match
        $status = ($team1_id && $team2_id) ? 'scheduled' : 'pending';
        
        $stmt = $pdo->prepare("
            INSERT INTO matches (tournament_id, round_number, match_number, team1_id, team2_id, status) 
            VALUES (?, 1, ?, ?, ?, ?)
        ");
        $stmt->execute([$tournament_id, $match_number, $team1_id, $team2_id, $status]);
        
        error_log("Created match $match_number: Team1=$team1_id, Team2=$team2_id, Status=$status");
        $match_number++;
    }
    
    // Subsequent rounds - create empty matches (NO 3rd/4th place)
    for ($round = 2; $round <= $rounds; $round++) {
        $matches_in_round = $bracket_size / pow(2, $round);
        
        for ($match = 1; $match <= $matches_in_round; $match++) {
            $stmt = $pdo->prepare("
                INSERT INTO matches (tournament_id, round_number, match_number, team1_id, team2_id, status) 
                VALUES (?, ?, ?, NULL, NULL, 'pending')
            ");
            $stmt->execute([$tournament_id, $round, $match_number]);
            
            error_log("Created placeholder match $match_number for round $round");
            $match_number++;
        }
    }
    
    // Count the created matches
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE tournament_id = ?");
    $countStmt->execute([$tournament_id]);
    $match_count = $countStmt->fetchColumn();
    
    error_log("Successfully created $match_count matches for tournament $tournament_id");
    return true;
}

function createDoubleEliminationBracket($pdo, $tournament_id, $teams) {
    // For now, use single elimination as base
    return createSingleEliminationBracket($pdo, $tournament_id, $teams);
}

function createRoundRobinBracket($pdo, $tournament_id, $teams) {
    $team_count = count($teams);
    $match_number = 1;
    
    // Create matches where every team plays every other team
    for ($i = 0; $i < $team_count; $i++) {
        for ($j = $i + 1; $j < $team_count; $j++) {
            $stmt = $pdo->prepare("
                INSERT INTO matches (tournament_id, round_number, match_number, team1_id, team2_id, status) 
                VALUES (?, 1, ?, ?, ?, 'scheduled')
            ");
            $stmt->execute([$tournament_id, 1, $match_number, $teams[$i]['id'], $teams[$j]['id']]);
            $match_number++;
        }
    }
    
    error_log("Round robin bracket created for $team_count teams: " . ($match_number - 1) . " matches");
    return true;
}

function simulateNextRound($pdo, $tournament_id, $teams) {
    try {
        // Get current matches
        $matches = getTournamentMatches($pdo, $tournament_id);
        
        // Find the next round with incomplete matches
        $current_round = 0;
        foreach ($matches as $match) {
            if ($match['status'] !== 'completed' && $match['team1_id'] && $match['team2_id']) {
                $current_round = $match['round_number'];
                break;
            }
        }
        
        if ($current_round === 0) {
            $_SESSION['error'] = "All matches are already completed or waiting for teams";
            return false;
        }
        
        // Simulate all matches in the current round
        $round_matches = array_filter($matches, function($match) use ($current_round) {
            return $match['round_number'] == $current_round && 
                   $match['status'] !== 'completed' && 
                   $match['team1_id'] && 
                   $match['team2_id'];
        });
        
        if (empty($round_matches)) {
            $_SESSION['error'] = "No playable matches in current round";
            return false;
        }
        
        foreach ($round_matches as $match) {
            quickSimulateMatch($pdo, $match['id']);
        }
        
        // Check if tournament should be marked as completed
        updateTournamentStatus($pdo, $tournament_id);
        
        $_SESSION['success'] = "Round $current_round simulated successfully";
        return true;
    } catch (Exception $e) {
        error_log("Error simulating next round: " . $e->getMessage());
        $_SESSION['error'] = "Error simulating round: " . $e->getMessage();
        return false;
    }
}

function simulateEntireTournament($pdo, $tournament_id, $teams) {
    try {
        $matches = getTournamentMatches($pdo, $tournament_id);
        $total_rounds = max(array_column($matches, 'round_number'));
        
        $simulated_count = 0;
        for ($round = 1; $round <= $total_rounds; $round++) {
            $round_matches = array_filter($matches, function($match) use ($round) {
                return $match['round_number'] == $round && 
                       $match['status'] !== 'completed' && 
                       $match['team1_id'] && 
                       $match['team2_id'];
            });
            
            foreach ($round_matches as $match) {
                quickSimulateMatch($pdo, $match['id']);
                $simulated_count++;
            }
            
            // Refresh matches for next round
            $matches = getTournamentMatches($pdo, $tournament_id);
        }
        
        // Check if tournament should be marked as completed
        updateTournamentStatus($pdo, $tournament_id);
        
        if ($simulated_count > 0) {
            $_SESSION['success'] = "Entire tournament simulated successfully ($simulated_count matches)";
        } else {
            $_SESSION['success'] = "No matches needed simulation - tournament already complete";
        }
        return true;
    } catch (Exception $e) {
        error_log("Error simulating tournament: " . $e->getMessage());
        $_SESSION['error'] = "Error simulating tournament: " . $e->getMessage();
        return false;
    }
}

function resetSimulation($pdo, $tournament_id) {
    try {
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET team1_score = NULL, team2_score = NULL, winner_id = NULL, status = 'scheduled'
            WHERE tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        
        // Also reset tournament status to active if it was completed
        $update_stmt = $pdo->prepare("
            UPDATE tournaments 
            SET status = 'active', updated_at = NOW()
            WHERE id = ? AND status = 'completed'
        ");
        $update_stmt->execute([$tournament_id]);
        
        $_SESSION['success'] = "Tournament simulation reset successfully";
        return true;
    } catch (Exception $e) {
        error_log("Error resetting simulation: " . $e->getMessage());
        $_SESSION['error'] = "Error resetting simulation: " . $e->getMessage();
        return false;
    }
}

function updateMatchScore($pdo, $match_id, $team1_score, $team2_score) {
    try {
        if ($team1_score == $team2_score) {
            $_SESSION['error'] = "Scores cannot be equal. There must be a winner.";
            return false;
        }
        
        // Get match details to determine winner
        $match = getMatchById($pdo, $match_id);
        if (!$match) {
            $_SESSION['error'] = "Match not found";
            return false;
        }
        
        $winner_id = ($team1_score > $team2_score) ? $match['team1_id'] : $match['team2_id'];
        
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
            WHERE id = ?
        ");
        $stmt->execute([$team1_score, $team2_score, $winner_id, $match_id]);
        
        // Update next round matches
        updateNextRoundMatches($pdo, $match['tournament_id'], $match['round_number']);
        
        // Check if tournament should be marked as completed
        updateTournamentStatus($pdo, $match['tournament_id']);
        
        $_SESSION['success'] = "Match score updated successfully";
        return true;
    } catch (Exception $e) {
        error_log("Error updating match score: " . $e->getMessage());
        $_SESSION['error'] = "Error updating score: " . $e->getMessage();
        return false;
    }
}

// NEW FUNCTION: Schedule match with date validation
function scheduleMatch($pdo, $match_id, $match_date, $match_time, $match_location) {
    try {
        // Validate date is not in the past
        if (!empty($match_date)) {
            $selected_date = new DateTime($match_date);
            $today = new DateTime();
            $today->setTime(0, 0, 0); // Reset time part for accurate comparison
            
            if ($selected_date < $today) {
                $_SESSION['error'] = "Cannot schedule matches in the past. Please select today or a future date.";
                return false;
            }
        }
        
        // Validate that if time is provided, date must also be provided
        if (!empty($match_time) && empty($match_date)) {
            $_SESSION['error'] = "Please select a date when specifying a time.";
            return false;
        }
        
        // Validate required date field
        if (empty($match_date)) {
            $_SESSION['error'] = "Please select a date for the match.";
            return false;
        }
        
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET scheduled_date = ?, scheduled_time = ?, location = ?
            WHERE id = ?
        ");
        $stmt->execute([$match_date, $match_time, $match_location, $match_id]);
        
        $_SESSION['success'] = "Match scheduled successfully";
        return true;
    } catch (Exception $e) {
        error_log("Error scheduling match: " . $e->getMessage());
        $_SESSION['error'] = "Error scheduling match: " . $e->getMessage();
        return false;
    }
}

function getMatchById($pdo, $match_id) {
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$match_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateNextRoundMatches($pdo, $tournament_id, $current_round) {
    try {
        $matches = getTournamentMatches($pdo, $tournament_id);
        $current_round_matches = array_filter($matches, function($match) use ($current_round) {
            return $match['round_number'] == $current_round;
        });
        
        $next_round = $current_round + 1;
        $next_round_matches = array_filter($matches, function($match) use ($next_round) {
            return $match['round_number'] == $next_round;
        });
        
        if (empty($next_round_matches)) {
            return; // No next round
        }
        
        // Update next round matches with winners
        $match_index = 0;
        foreach ($current_round_matches as $match) {
            if ($match['status'] === 'completed' && $match['winner_id']) {
                $next_match_index = floor($match_index / 2);
                $team_slot = ($match_index % 2 == 0) ? 'team1_id' : 'team2_id';
                
                if (isset(array_values($next_round_matches)[$next_match_index])) {
                    $next_match = array_values($next_round_matches)[$next_match_index];
                    $update_stmt = $pdo->prepare("
                        UPDATE matches SET $team_slot = ? WHERE id = ?
                    ");
                    $update_stmt->execute([$match['winner_id'], $next_match['id']]);
                }
                $match_index++;
            }
        }
    } catch (Exception $e) {
        error_log("Error updating next round matches: " . $e->getMessage());
    }
}

function calculateTournamentProgress($matches) {
    if (empty($matches)) return 0;
    
    $completed = 0;
    foreach ($matches as $match) {
        if ($match['status'] === 'completed') {
            $completed++;
        }
    }
    
    return round(($completed / count($matches)) * 100);
}

function getCompletedMatchesCount($matches) {
    if (empty($matches)) return 0;
    
    $completed = 0;
    foreach ($matches as $match) {
        if ($match['status'] === 'completed') {
            $completed++;
        }
    }
    
    return $completed;
}

// Quick match simulation function
function quickSimulateMatch($pdo, $match_id) {
    try {
        // Get match details
        $stmt = $pdo->prepare("SELECT * FROM matches WHERE id = ?");
        $stmt->execute([$match_id]);
        $match = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($match && $match['team1_id'] && $match['team2_id']) {
            // Generate random scores
            $team1_score = rand(0, 5);
            $team2_score = rand(0, 5);
            
            // Ensure there's a winner
            if ($team1_score == $team2_score) {
                $team1_score += rand(0, 1);
                $team2_score += rand(0, 1);
            }
            
            $winner_id = $team1_score > $team2_score ? $match['team1_id'] : $match['team2_id'];
            
            $update_stmt = $pdo->prepare("
                UPDATE matches 
                SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
                WHERE id = ?
            ");
            $update_stmt->execute([$team1_score, $team2_score, $winner_id, $match_id]);
            
            // Update next round matches if this match is completed
            updateNextRoundMatches($pdo, $match['tournament_id'], $match['round_number']);
            
            // Check if tournament should be marked as completed
            updateTournamentStatus($pdo, $match['tournament_id']);
        }
    } catch (Exception $e) {
        error_log("Error in quickSimulateMatch: " . $e->getMessage());
    }
}

// Bracket rendering function - UPDATED WITH SEED NUMBERS
function renderTrueBracket($matches, $teams, $tournament_id, $format) {
    if (empty($matches)) {
        return '<div class="text-center py-8 text-gray-500">No matches scheduled yet.</div>';
    }
    
    // Get team seeds
    $teamSeeds = getTeamSeeds($teams);
    
    // Group matches by round and ensure proper ordering - EXCLUDE 3rd/4th place matches
    $matches_by_round = [];
    foreach ($matches as $match) {
        $round = (float)$match['round_number'];
        // Only include whole number rounds (exclude 3rd/4th place which would be decimal rounds)
        if ($round == (int)$round && $round > 0) {
            if (!isset($matches_by_round[$round])) {
                $matches_by_round[$round] = [];
            }
            $matches_by_round[$round][] = $match;
        }
    }
    
    // Sort rounds numerically
    ksort($matches_by_round);
    
    $total_rounds = count($matches_by_round);
    
    ob_start();
    ?>
    <div class="text-center mb-6">
        <i class="fas fa-brackets-curly text-4xl text-blue-500 mb-2"></i>
        <h3 class="text-xl font-bold text-gray-900 mb-1">Single Elimination Bracket</h3>
        <p class="text-gray-600"><?php echo count($teams); ?> teams | <?php echo $total_rounds; ?> rounds | <?php echo getCompletedMatchesCount($matches); ?> matches completed</p>
    </div>
    
    <div class="overflow-x-auto">
        <div class="bracket-preview-container min-w-max mx-auto py-4">
            <div class="flex space-x-8 justify-center items-start">
                <?php 
                $round_keys = array_keys($matches_by_round);
                $final_round = end($round_keys);
                reset($round_keys);
                
                foreach ($matches_by_round as $round => $round_matches): 
                    $matchesInRound = count($round_matches);
                    $roundName = getProperRoundName($round, $total_rounds, $matchesInRound);
                ?>
                    
                    <div class="round flex flex-col space-y-8">
                        <div class="text-center font-semibold text-blue-700 mb-2 bg-blue-50 py-2 rounded-lg"><?php echo $roundName; ?></div>
                        
                        <?php foreach ($round_matches as $match_index => $match): ?>
                            <?php
                            $team1_seed = $match['team1_id'] ? ($teamSeeds[$match['team1_id']] ?? 'N/A') : null;
                            $team2_seed = $match['team2_id'] ? ($teamSeeds[$match['team2_id']] ?? 'N/A') : null;
                            
                            $team1 = [
                                'name' => $match['team1_name'] ?: 'TBD',
                                'score' => $match['team1_score'],
                                'is_winner' => $match['winner_id'] == $match['team1_id'],
                                'is_bye' => !$match['team1_id'],
                                'seed' => $team1_seed
                            ];
                            
                            $team2 = [
                                'name' => $match['team2_name'] ?: 'TBD', 
                                'score' => $match['team2_score'],
                                'is_winner' => $match['winner_id'] == $match['team2_id'],
                                'is_bye' => !$match['team2_id'],
                                'seed' => $team2_seed
                            ];
                            
                            $is_bye_match = $team1['is_bye'] || $team2['is_bye'];
                            $is_completed = $match['status'] === 'completed';
                            ?>
                            
                            <div class="match bg-white border-2 <?php echo $is_bye_match ? 'border-yellow-300' : ($is_completed ? 'border-green-300' : 'border-blue-300'); ?> rounded-lg shadow-sm p-4 min-w-[220px] transition-all duration-200 hover:shadow-md"
                                 data-match-id="<?php echo $match['id']; ?>">
                                
                                <div class="text-xs text-gray-500 text-center mb-2 font-medium">Match <?php echo $match['match_number']; ?></div>
                                
                                <!-- Team 1 -->
                                <div class="team <?php echo getTeamClassPreview($team1, $is_completed); ?> border-b <?php echo getBorderClassPreview($team1); ?> px-3 py-2 text-sm font-medium rounded-t">
                                    <?php echo getTeamDisplayPreview($team1, $match, 1, $match_index); ?>
                                </div>
                                
                                <!-- Team 2 -->
                                <div class="team <?php echo getTeamClassPreview($team2, $is_completed); ?> px-3 py-2 text-sm font-medium rounded-b">
                                    <?php echo getTeamDisplayPreview($team2, $match, 2, $match_index); ?>
                                </div>
                                
                                <?php if ($is_bye_match): ?>
                                    <div class="text-xs text-yellow-600 text-center mt-2">
                                        <i class="fas fa-bolt mr-1"></i>Auto-advance
                                    </div>
                                <?php elseif ($is_completed): ?>
                                    <div class="text-xs text-green-600 text-center mt-2 font-semibold">
                                        <i class="fas fa-trophy mr-1"></i>Winner: <?php echo $match['winner_name'] ?: 'Unknown'; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Match Schedule Info -->
                                <?php if (!empty($match['scheduled_date']) || !empty($match['location'])): ?>
                                    <div class="text-xs text-purple-600 text-center mt-2 border-t border-gray-200 pt-2">
                                        <?php if (!empty($match['scheduled_date'])): ?>
                                            <div><i class="fas fa-calendar-day mr-1"></i><?php echo date('M j', strtotime($match['scheduled_date'])); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($match['location'])): ?>
                                            <div><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($match['location']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Match Actions -->
                                <div class="match-actions mt-3 pt-3 border-t border-gray-200 flex justify-center">
                                    <?php if ($match['team1_id'] && $match['team2_id']): ?>
                                        <button type="button" 
                                                onclick="enableMatchEditing(<?php echo $match['id']; ?>)"
                                                class="edit-score-btn text-xs">
                                            <i class="fas fa-edit"></i> Edit Scores
                                        </button>
                                    <?php else: ?>
                                        <span class="waiting-text text-xs">Waiting for teams</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Add connector arrows between rounds (except last round) -->
                    <?php if ($round < $final_round): ?>
                        <div class="connector flex flex-col space-y-8">
                            <div class="text-center font-semibold text-blue-700 mb-2 py-2">&nbsp;</div>
                            <?php for ($i = 0; $i < count($round_matches); $i++): ?>
                                <div class="flex items-center justify-center h-24">
                                    <div class="w-12 border-t-2 border-blue-400 relative">
                                        <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-blue-400 border-y-4 border-y-transparent"></div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- Champion section - ONLY AFTER THE FINAL ROUND -->
                <?php if (!empty($matches_by_round) && $final_round > 0): ?>
                    <div class="champion-section flex flex-col justify-center items-center space-y-8 ml-8">
                        <div class="text-center font-semibold text-green-700 mb-2 bg-green-50 py-2 px-4 rounded-lg">Champion</div>
                        <div class="champion-trophy bg-gradient-to-br from-yellow-400 to-yellow-600 border-2 border-yellow-500 rounded-lg p-6 min-w-[240px] text-center shadow-lg transform hover:scale-105 transition-transform duration-200">
                            <i class="fas fa-trophy text-white text-3xl mb-3"></i>
                            <div class="team bg-white border border-yellow-300 rounded px-3 py-4 text-sm font-bold text-gray-800">
                                <?php
                                $champion = 'TBD';
                                $final_matches = $matches_by_round[$final_round];
                                // Get the actual final match (should be only one in final round)
                                $final_match = end($final_matches);
                                if ($final_match['status'] === 'completed' && $final_match['winner_name']) {
                                    $champion = $final_match['winner_name'];
                                }
                                echo $champion === 'TBD' ? '🏆 Tournament Winner 🏆' : $champion;
                                ?>
                            </div>
                            <div class="text-xs text-white font-medium mt-3">CHAMPION</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Teams Seeding Section -->
    <div class="mt-12 border-t pt-8">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list-ol text-purple-500"></i>
                Tournament Teams with Seed Numbers
            </h4>
            <span class="text-sm text-gray-500 bg-blue-100 px-3 py-1 rounded-full">
                <i class="fas fa-users mr-1"></i><?php echo count($teams); ?> Teams
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <?php foreach ($teams as $index => $team): ?>
                <div class="bg-white border-2 border-blue-200 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="text-lg font-bold text-blue-600 mb-2">Seed #<?php echo $index + 1; ?></div>
                    <div class="font-semibold text-gray-800 text-base mb-2"><?php echo $team['barangay']; ?></div>
                    <?php if (!empty($team['coach_username'])): ?>
                        <div class="text-xs text-gray-600 bg-gray-100 rounded px-2 py-1 inline-block">
                            <i class="fas fa-user mr-1"></i><?php echo $team['coach_username']; ?>
                        </div>
                    <?php endif; ?>
                    <div class="mt-3 text-xs text-blue-500 font-medium">
                        <i class="fas fa-seedling mr-1"></i>Seed Position
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Improved function for proper round naming - FIXED DUPLICATE FINALS
function getProperRoundName($round, $totalRounds, $matchesInRound) {
    // If this is the last round and there's only 1 match, it's the FINALS
    if ($round == $totalRounds && $matchesInRound == 1) {
        return 'FINALS';
    }
    // For tournaments with 2 teams, the only match is the FINALS
    if ($totalRounds == 1 && $matchesInRound == 1) {
        return 'FINALS';
    }
    if ($matchesInRound == 2) return 'SEMI-FINALS';
    if ($matchesInRound == 4) return 'QUARTER-FINALS';
    if ($round == 1) return "ROUND OF " . ($matchesInRound * 2);
    return "ROUND " . $round;
}

// Helper functions for the new bracket design
function getRoundNamePreview($round, $totalRounds, $matchesInRound) {
    // Use the same logic as getProperRoundName
    return getProperRoundName($round, $totalRounds, $matchesInRound);
}

function getTeamClassPreview($team, $is_completed) {
    if ($team['is_bye']) return 'bg-yellow-50 text-yellow-700';
    if ($team['is_winner'] && $is_completed) return 'bg-green-50 text-green-700';
    if ($is_completed && !$team['is_winner']) return 'bg-red-50 text-red-700';
    return 'bg-blue-50 text-blue-800';
}

function getBorderClassPreview($team) {
    if ($team['is_bye']) return 'border-yellow-200';
    if ($team['is_winner']) return 'border-green-200';
    return 'border-blue-200';
}

// UPDATED function to show seed numbers
function getTeamDisplayPreview($team, $match, $teamNumber, $matchIndex) {
    if ($team['is_bye']) {
        return '<span class="text-yellow-600"><i class="fas fa-walking mr-1"></i>BYE</span>';
    }
    
    $display = '<div class="flex justify-between items-center">';
    $display .= '<div class="flex items-center gap-2">';
    
    // Add seed number if available
    if ($team['seed']) {
        $display .= '<span class="seed-badge">#' . $team['seed'] . '</span>';
    }
    
    $display .= '<span>' . htmlspecialchars($team['name']) . '</span>';
    $display .= '</div>';
    
    if ($team['score'] !== null) {
        $display .= '<span class="font-mono font-bold ' . ($team['is_winner'] ? 'text-green-600' : 'text-red-600') . '">' . $team['score'] . '</span>';
    } else {
        $display .= '<span class="text-gray-400 text-xs">-</span>';
    }
    
    $display .= '</div>';
    return $display;
}
?>