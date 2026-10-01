<?php
// match_schedule.php
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

// Handle schedule actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_schedule') {
        updateMatchSchedule($pdo, $_POST);
    } elseif ($action === 'generate_schedule') {
        generateAutomaticSchedule($pdo, $tournament_id);
    } elseif ($action === 'clear_schedule') {
        clearMatchSchedule($pdo, $tournament_id);
    }
    
    header("Location: match_schedule.php?id=" . $tournament_id);
    exit();
}

// Fetch matches with schedule information
$matches = getMatchesWithSchedule($pdo, $tournament_id);
$teams = getTournamentTeams($pdo, $tournament_id);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Match Schedule</h1>
                <p class="text-gray-600"><?php echo htmlspecialchars($tournament['name']); ?> - Game Schedule & Timing</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="tournament_manage.php?id=<?php echo $tournament_id; ?>" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-cog"></i> Manage
                </a>
                <a href="tournament.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Tournaments
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <!-- Success/Error Messages -->
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

            <!-- Schedule Controls -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-blue-500"></i>
                        Schedule Management
                    </h2>
                    <div class="flex gap-3">
                        <form method="POST" class="inline">
                            <input type="hidden" name="action" value="generate_schedule">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-magic"></i> Auto-Generate Schedule
                            </button>
                        </form>
                        <form method="POST" class="inline" onsubmit="return confirm('Clear all schedule times? This cannot be undone.')">
                            <input type="hidden" name="action" value="clear_schedule">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-trash"></i> Clear Schedule
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Schedule Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo count($matches); ?></div>
                        <div class="text-sm text-blue-700">Total Matches</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo count(array_filter($matches, function($m) { return !empty($m['match_date']); })); ?></div>
                        <div class="text-sm text-green-700">Scheduled</div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600"><?php echo count(array_filter($matches, function($m) { return empty($m['match_date']); })); ?></div>
                        <div class="text-sm text-yellow-700">Unscheduled</div>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600"><?php echo count(array_filter($matches, function($m) { return $m['status'] === 'completed'; })); ?></div>
                        <div class="text-sm text-purple-700">Completed</div>
                    </div>
                </div>

                <!-- Tournament Date Info -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div>
                                <div class="text-sm text-gray-600">Tournament Date</div>
                                <div class="font-semibold text-gray-800"><?php echo date('F j, Y', strtotime($tournament['date'])); ?></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-600">Location</div>
                                <div class="font-semibold text-gray-800"><?php echo htmlspecialchars($tournament['location']); ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Format</div>
                            <div class="font-semibold text-gray-800"><?php echo ucfirst($tournament['format']); ?> Elimination</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Match Schedule Table -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-list-ol text-purple-500"></i>
                    Match Schedule
                </h3>

                <?php if (empty($matches)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-calendar-times text-6xl text-gray-400 mb-4"></i>
                        <h4 class="text-xl font-semibold text-gray-700 mb-2">No Matches Found</h4>
                        <p class="text-gray-500 mb-4">This tournament doesn't have any matches scheduled yet.</p>
                        <a href="simulate_bracket.php?id=<?php echo $tournament_id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center gap-2">
                            <i class="fas fa-play-circle"></i> Generate Bracket First
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" id="scheduleForm">
                        <input type="hidden" name="action" value="update_schedule">
                        
                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Match</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Round</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Teams</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Court/Venue</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($matches as $match): ?>
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#<?php echo $match['match_number']; ?></div>
                                                <input type="hidden" name="match_ids[]" value="<?php echo $match['id']; ?>">
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Round <?php echo $match['round_number']; ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-4">
                                                <div class="text-sm text-gray-900">
                                                    <div class="font-medium <?php echo $match['winner_id'] == $match['team1_id'] ? 'text-green-600' : ''; ?>">
                                                        <?php echo $match['team1_name'] ?: 'TBD'; ?>
                                                        <?php if ($match['team1_score'] !== null): ?>
                                                            <span class="font-bold ml-1">(<?php echo $match['team1_score']; ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-xs text-gray-500">vs</div>
                                                    <div class="font-medium <?php echo $match['winner_id'] == $match['team2_id'] ? 'text-green-600' : ''; ?>">
                                                        <?php echo $match['team2_name'] ?: 'TBD'; ?>
                                                        <?php if ($match['team2_score'] !== null): ?>
                                                            <span class="font-bold ml-1">(<?php echo $match['team2_score']; ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <input type="date" 
                                                       name="match_dates[]" 
                                                       value="<?php echo $match['match_date'] ?: $tournament['date']; ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <input type="time" 
                                                       name="match_times[]" 
                                                       value="<?php echo $match['match_time'] ?: '14:00'; ?>"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <input type="text" 
                                                       name="match_venues[]" 
                                                       value="<?php echo htmlspecialchars($match['venue'] ?? 'Main Court'); ?>"
                                                       placeholder="Court/Venue"
                                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    <?php echo $match['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                          ($match['status'] === 'scheduled' ? 'bg-blue-100 text-blue-800' : 
                                                          'bg-yellow-100 text-yellow-800'); ?>">
                                                    <?php echo ucfirst($match['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="simulate_bracket.php?id=<?php echo $tournament_id; ?>" 
                                                   class="text-blue-600 hover:text-blue-900 mr-3">View Bracket</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-save"></i> Save Schedule
                            </button>
                        </div>
                    </form>

                    <!-- Schedule Summary -->
                    <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Upcoming Matches -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-clock"></i> Next 5 Matches
                            </h4>
                            <div class="space-y-2">
                                <?php
                                $upcoming_matches = array_filter($matches, function($m) {
                                    return $m['status'] === 'scheduled' && !empty($m['match_date']);
                                });
                                usort($upcoming_matches, function($a, $b) {
                                    return strcmp($a['match_date'] . $a['match_time'], $b['match_date'] . $b['match_time']);
                                });
                                $upcoming_matches = array_slice($upcoming_matches, 0, 5);
                                ?>
                                <?php foreach ($upcoming_matches as $match): ?>
                                    <div class="bg-white rounded p-3 border border-green-200">
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm font-medium">Match #<?php echo $match['match_number']; ?></div>
                                            <div class="text-xs text-green-600 font-semibold">
                                                <?php echo date('M j', strtotime($match['match_date'])); ?> at <?php echo date('g:i A', strtotime($match['match_time'])); ?>
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            <?php echo $match['team1_name'] ?: 'TBD'; ?> vs <?php echo $match['team2_name'] ?: 'TBD'; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($upcoming_matches)): ?>
                                    <p class="text-sm text-green-700 text-center py-2">No upcoming matches scheduled</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Recent Results -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center gap-2">
                                <i class="fas fa-trophy"></i> Recent Results
                            </h4>
                            <div class="space-y-2">
                                <?php
                                $completed_matches = array_filter($matches, function($m) {
                                    return $m['status'] === 'completed';
                                });
                                usort($completed_matches, function($a, $b) {
                                    return strcmp($b['match_date'] . $b['match_time'], $a['match_date'] . $a['match_time']);
                                });
                                $completed_matches = array_slice($completed_matches, 0, 5);
                                ?>
                                <?php foreach ($completed_matches as $match): ?>
                                    <div class="bg-white rounded p-3 border border-blue-200">
                                        <div class="flex justify-between items-center">
                                            <div class="text-sm font-medium">Match #<?php echo $match['match_number']; ?></div>
                                            <div class="text-xs text-blue-600 font-semibold">
                                                <?php echo $match['winner_name']; ?> won
                                            </div>
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1">
                                            <?php echo $match['team1_name']; ?> <?php echo $match['team1_score']; ?> - 
                                            <?php echo $match['team2_score']; ?> <?php echo $match['team2_name']; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($completed_matches)): ?>
                                    <p class="text-sm text-blue-700 text-center py-2">No matches completed yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<style>
.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}

.match-row:hover {
    background-color: #f9fafb;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add real-time validation for date inputs
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const tournamentDate = '<?php echo $tournament['date']; ?>';
    
    dateInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.value < tournamentDate) {
                alert('Match date cannot be before tournament date: ' + new Date(tournamentDate).toLocaleDateString());
                this.value = tournamentDate;
            }
        });
    });

    // Auto-save functionality
    let saveTimeout;
    const form = document.getElementById('scheduleForm');
    
    form.addEventListener('change', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            // Optional: Add auto-save here if desired
        }, 2000);
    });
});
</script>

<?php 
include 'includes/footer.php';

// Helper Functions

function getMatchesWithSchedule($pdo, $tournament_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   t1.barangay as team1_name, 
                   t2.barangay as team2_name,
                   w.barangay as winner_name,
                   ms.match_date, 
                   ms.match_time, 
                   ms.venue
            FROM matches m
            LEFT JOIN teams t1 ON m.team1_id = t1.id
            LEFT JOIN teams t2 ON m.team2_id = t2.id
            LEFT JOIN teams w ON m.winner_id = w.id
            LEFT JOIN match_schedule ms ON m.id = ms.match_id
            WHERE m.tournament_id = ?
            ORDER BY 
                COALESCE(ms.match_date, '9999-12-31') ASC,
                COALESCE(ms.match_time, '23:59:59') ASC,
                m.round_number DESC, 
                m.match_number ASC
        ");
        $stmt->execute([$tournament_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching matches with schedule: " . $e->getMessage());
        return [];
    }
}

function getTournamentTeams($pdo, $tournament_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM teams WHERE tournament_id = ? ORDER BY barangay");
        $stmt->execute([$tournament_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching tournament teams: " . $e->getMessage());
        return [];
    }
}

function updateMatchSchedule($pdo, $post_data) {
    try {
        ensureMatchScheduleTableExists($pdo);
        
        $match_ids = $post_data['match_ids'] ?? [];
        $match_dates = $post_data['match_dates'] ?? [];
        $match_times = $post_data['match_times'] ?? [];
        $match_venues = $post_data['match_venues'] ?? [];
        
        $updated_count = 0;
        
        for ($i = 0; $i < count($match_ids); $i++) {
            $match_id = $match_ids[$i];
            $match_date = !empty($match_dates[$i]) ? $match_dates[$i] : null;
            $match_time = !empty($match_times[$i]) ? $match_times[$i] : null;
            $venue = !empty($match_venues[$i]) ? $match_venues[$i] : null;
            
            // Check if schedule entry exists
            $check_stmt = $pdo->prepare("SELECT id FROM match_schedule WHERE match_id = ?");
            $check_stmt->execute([$match_id]);
            $existing = $check_stmt->fetch();
            
            if ($existing) {
                // Update existing
                $update_stmt = $pdo->prepare("
                    UPDATE match_schedule 
                    SET match_date = ?, match_time = ?, venue = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE match_id = ?
                ");
                $update_stmt->execute([$match_date, $match_time, $venue, $match_id]);
            } else {
                // Insert new
                $insert_stmt = $pdo->prepare("
                    INSERT INTO match_schedule (match_id, match_date, match_time, venue) 
                    VALUES (?, ?, ?, ?)
                ");
                $insert_stmt->execute([$match_id, $match_date, $match_time, $venue]);
            }
            
            $updated_count++;
        }
        
        $_SESSION['success'] = "Schedule updated for $updated_count matches";
        return true;
        
    } catch (Exception $e) {
        error_log("Error updating match schedule: " . $e->getMessage());
        $_SESSION['error'] = "Error updating schedule: " . $e->getMessage();
        return false;
    }
}

function generateAutomaticSchedule($pdo, $tournament_id) {
    try {
        ensureMatchScheduleTableExists($pdo);
        
        // Get tournament date
        $tournament_stmt = $pdo->prepare("SELECT date FROM tournaments WHERE id = ?");
        $tournament_stmt->execute([$tournament_id]);
        $tournament = $tournament_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tournament) {
            throw new Exception("Tournament not found");
        }
        
        $tournament_date = $tournament['date'];
        
        // Get all matches for this tournament
        $matches_stmt = $pdo->prepare("
            SELECT id, round_number, match_number 
            FROM matches 
            WHERE tournament_id = ? 
            ORDER BY round_number, match_number
        ");
        $matches_stmt->execute([$tournament_id]);
        $matches = $matches_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($matches)) {
            throw new Exception("No matches found for this tournament");
        }
        
        // Generate schedule
        $start_time = "09:00"; // Tournament starts at 9 AM
        $match_duration = 30; // 30 minutes per match
        $current_time = $start_time;
        $court_number = 1;
        $max_courts = 4; // Maximum number of concurrent matches
        
        $updated_count = 0;
        
        foreach ($matches as $match) {
            // Check if schedule already exists
            $check_stmt = $pdo->prepare("SELECT id FROM match_schedule WHERE match_id = ?");
            $check_stmt->execute([$match['id']]);
            $existing = $check_stmt->fetch();
            
            if (!$existing) {
                // Insert new schedule
                $insert_stmt = $pdo->prepare("
                    INSERT INTO match_schedule (match_id, match_date, match_time, venue) 
                    VALUES (?, ?, ?, ?)
                ");
                $venue = "Court " . $court_number;
                $insert_stmt->execute([$match['id'], $tournament_date, $current_time, $venue]);
                $updated_count++;
            }
            
            // Update time and court for next match
            $current_time = addMinutesToTime($current_time, $match_duration);
            $court_number = ($court_number % $max_courts) + 1;
            
            // If we've reached late evening, reset to next day (but same tournament date for simplicity)
            if ($current_time >= "18:00") { // 6 PM
                $current_time = $start_time;
            }
        }
        
        $_SESSION['success'] = "Automatic schedule generated for $updated_count matches";
        return true;
        
    } catch (Exception $e) {
        error_log("Error generating automatic schedule: " . $e->getMessage());
        $_SESSION['error'] = "Error generating schedule: " . $e->getMessage();
        return false;
    }
}

function clearMatchSchedule($pdo, $tournament_id) {
    try {
        ensureMatchScheduleTableExists($pdo);
        
        $stmt = $pdo->prepare("
            DELETE ms FROM match_schedule ms
            JOIN matches m ON ms.match_id = m.id
            WHERE m.tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        
        $cleared_count = $stmt->rowCount();
        $_SESSION['success'] = "Schedule cleared for $cleared_count matches";
        return true;
        
    } catch (Exception $e) {
        error_log("Error clearing match schedule: " . $e->getMessage());
        $_SESSION['error'] = "Error clearing schedule: " . $e->getMessage();
        return false;
    }
}

function ensureMatchScheduleTableExists($pdo) {
    try {
        $pdo->query("SELECT 1 FROM match_schedule LIMIT 1");
    } catch (Exception $e) {
        // Table doesn't exist, create it
        $createTableSQL = "
        CREATE TABLE match_schedule (
            id INT AUTO_INCREMENT PRIMARY KEY,
            match_id INT NOT NULL,
            match_date DATE NULL,
            match_time TIME NULL,
            venue VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY unique_match (match_id),
            FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
            INDEX match_date_index (match_date),
            INDEX match_time_index (match_time)
        )";
        $pdo->exec($createTableSQL);
        error_log("Created match_schedule table");
    }
}

function addMinutesToTime($time, $minutes) {
    $time = DateTime::createFromFormat('H:i', $time);
    $time->add(new DateInterval('PT' . $minutes . 'M'));
    return $time->format('H:i');
}
?>