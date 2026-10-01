<?php
// manage_tournament.php
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

// Fetch teams
try {
    $teams_stmt = $pdo->prepare("SELECT * FROM teams WHERE tournament_id = ? ORDER BY id");
    $teams_stmt->execute([$tournament_id]);
    $teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $teams = [];
}

// Fetch all matches for this tournament
try {
    $matches_stmt = $pdo->prepare("
        SELECT m.*, 
               t1.barangay as team1_name, 
               t2.barangay as team2_name,
               winner.barangay as winner_name
        FROM matches m 
        LEFT JOIN teams t1 ON m.team1_id = t1.id 
        LEFT JOIN teams t2 ON m.team2_id = t2.id 
        LEFT JOIN teams winner ON m.winner_id = winner.id 
        WHERE m.tournament_id = ? 
        ORDER BY m.round_number, m.match_number
    ");
    $matches_stmt->execute([$tournament_id]);
    $matches = $matches_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $matches = [];
}

// Handle match updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_match'])) {
    try {
        $match_id = $_POST['match_id'];
        $team1_score = $_POST['team1_score'] ?? null;
        $team2_score = $_POST['team2_score'] ?? null;
        $winner_id = $_POST['winner_id'] ?? null;
        $status = $_POST['status'] ?? 'scheduled';
        
        // Verify match belongs to user's tournament
        $verify_stmt = $pdo->prepare("SELECT id FROM matches WHERE id = ? AND tournament_id = ?");
        $verify_stmt->execute([$match_id, $tournament_id]);
        
        if ($verify_stmt->fetch()) {
            $update_stmt = $pdo->prepare("
                UPDATE matches 
                SET team1_score = ?, team2_score = ?, winner_id = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $update_stmt->execute([$team1_score, $team2_score, $winner_id, $status, $match_id]);
            
            $_SESSION['success'] = "Match updated successfully!";
        } else {
            $_SESSION['error'] = "Match not found or access denied";
        }
        
        header("Location: manage_tournament.php?id=" . $tournament_id);
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating match: " . $e->getMessage();
        header("Location: manage_tournament.php?id=" . $tournament_id);
        exit();
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Tournament Management</h1>
                <p class="text-gray-600"><?php echo htmlspecialchars($tournament['name']); ?></p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="tournament_brackets.php?id=<?php echo $tournament_id; ?>" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-sitemap"></i>
                    View Brackets
                </a>
                <a href="tournament.php" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Back to Tournaments
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <!-- Success/Error Messages -->
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

            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-tasks text-indigo-500"></i> 
                    Tournament Management Dashboard
                </h2>
                
                <!-- Tournament Overview -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php echo count($teams); ?></div>
                        <div class="text-sm text-blue-700">Total Teams</div>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo count($matches); ?></div>
                        <div class="text-sm text-green-700">Total Matches</div>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-purple-600">
                            <?php echo count(array_filter($matches, function($m) { return $m['status'] === 'completed'; })); ?>
                        </div>
                        <div class="text-sm text-purple-700">Completed</div>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-orange-600">
                            <?php echo count(array_filter($matches, function($m) { return $m['status'] === 'scheduled'; })); ?>
                        </div>
                        <div class="text-sm text-orange-700">Scheduled</div>
                    </div>
                </div>

                <!-- Bracket Display -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-sitemap text-purple-500"></i>
                        Tournament Bracket
                    </h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <?php if (empty($matches)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-sitemap text-gray-400 text-4xl mb-4"></i>
                                <h4 class="text-lg font-medium text-gray-600 mb-2">No Bracket Created</h4>
                                <p class="text-gray-500">This tournament doesn't have a bracket setup yet.</p>
                                <a href="bracket_setup.php" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                    Setup Bracket
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Include the bracket rendering function from tournament_brackets.php -->
                            <div class="single-elimination-bracket">
                                <?php 
                                // Reuse the bracket rendering logic from tournament_brackets.php
                                function renderManageBracket($matches, $teams, $tournament_id, $format) {
                                    if (empty($matches)) {
                                        return '<div class="text-center py-8 text-gray-500">No matches scheduled yet.</div>';
                                    }
                                    
                                    $team_count = count($teams);
                                    $is_41_team = ($team_count === 41);
                                    
                                    if ($is_41_team) {
                                        return render41TeamManageBracket($matches, $teams, $tournament_id);
                                    } else {
                                        return renderStandardManageBracket($matches, $teams, $tournament_id);
                                    }
                                }

                                function render41TeamManageBracket($matches, $teams, $tournament_id) {
                                    // Group matches by round
                                    $matches_by_round = [];
                                    foreach ($matches as $match) {
                                        $round = (int)$match['round_number'];
                                        if (!isset($matches_by_round[$round])) {
                                            $matches_by_round[$round] = [];
                                        }
                                        $matches_by_round[$round][] = $match;
                                    }
                                    ksort($matches_by_round);
                                    
                                    ob_start();
                                    ?>
                                    <div class="text-center mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 mb-1">41-Team Single Elimination Bracket</h3>
                                        <p class="text-gray-600">Manage matches and update scores</p>
                                    </div>

                                    <?php foreach ($matches_by_round as $round => $round_matches): ?>
                                        <div class="mb-6">
                                            <div class="text-center font-bold text-lg bg-blue-100 text-blue-800 py-3 rounded-lg mb-4">
                                                <?php
                                                $round_names = [
                                                    1 => 'ROUND 1 - PRELIMINARY MATCHES',
                                                    2 => 'ROUND OF 32',
                                                    3 => 'ROUND OF 16',
                                                    4 => 'QUARTER-FINALS',
                                                    5 => 'SEMI-FINALS',
                                                    6 => 'FINALS'
                                                ];
                                                echo $round_names[$round] ?? "ROUND $round";
                                                ?>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                <?php foreach ($round_matches as $match): ?>
                                                    <div class="match-card bg-white border-2 border-blue-300 rounded-lg shadow-sm p-4">
                                                        <div class="flex justify-between items-center mb-3">
                                                            <div class="text-sm font-medium text-gray-700">M<?php echo $match['match_number']; ?></div>
                                                            <div class="text-xs px-2 py-1 rounded-full <?php echo $match['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($match['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                                <?php echo ucfirst($match['status']); ?>
                                                            </div>
                                                        </div>
                                                        
                                                        <form method="POST" action="" class="space-y-3">
                                                            <input type="hidden" name="match_id" value="<?php echo $match['id']; ?>">
                                                            
                                                            <!-- Team 1 -->
                                                            <div class="team-section">
                                                                <div class="flex justify-between items-center mb-1">
                                                                    <span class="text-sm font-medium"><?php echo $match['team1_name'] ?: 'TBD'; ?></span>
                                                                    <input type="number" name="team1_score" value="<?php echo $match['team1_score'] ?? ''; ?>" 
                                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" 
                                                                           min="0" placeholder="Score">
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Team 2 -->
                                                            <div class="team-section">
                                                                <div class="flex justify-between items-center mb-1">
                                                                    <span class="text-sm font-medium">
                                                                        <?php 
                                                                        if ($match['team2_name'] && $match['team2_name'] !== 'TBD') {
                                                                            echo $match['team2_name'];
                                                                        } else {
                                                                            $notes = $match['notes'] ?? '';
                                                                            if (preg_match('/Winner ([A-I])/', $notes, $winner_match)) {
                                                                                echo "Winner " . $winner_match[1];
                                                                            } else {
                                                                                echo 'TBD';
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </span>
                                                                    <input type="number" name="team2_score" value="<?php echo $match['team2_score'] ?? ''; ?>" 
                                                                           class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" 
                                                                           min="0" placeholder="Score">
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Winner Selection -->
                                                            <div class="mt-3">
                                                                <label class="block text-xs font-medium text-gray-700 mb-1">Winner:</label>
                                                                <select name="winner_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                                    <option value="">Select Winner</option>
                                                                    <?php if ($match['team1_id']): ?>
                                                                        <option value="<?php echo $match['team1_id']; ?>" <?php echo $match['winner_id'] == $match['team1_id'] ? 'selected' : ''; ?>>
                                                                            <?php echo $match['team1_name']; ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                    <?php if ($match['team2_id']): ?>
                                                                        <option value="<?php echo $match['team2_id']; ?>" <?php echo $match['winner_id'] == $match['team2_id'] ? 'selected' : ''; ?>>
                                                                            <?php echo $match['team2_name'] ?: 'Winner'; ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                </select>
                                                            </div>
                                                            
                                                            <!-- Status -->
                                                            <div class="mt-2">
                                                                <label class="block text-xs font-medium text-gray-700 mb-1">Status:</label>
                                                                <select name="status" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                                    <option value="scheduled" <?php echo $match['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                                                    <option value="in_progress" <?php echo $match['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                                    <option value="completed" <?php echo $match['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <button type="submit" name="update_match" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-sm font-medium transition duration-200">
                                                                Update Match
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                    return ob_get_clean();
                                }

                                function renderStandardManageBracket($matches, $teams, $tournament_id) {
                                    // Group matches by round
                                    $matches_by_round = [];
                                    foreach ($matches as $match) {
                                        $round = (int)$match['round_number'];
                                        if (!isset($matches_by_round[$round])) {
                                            $matches_by_round[$round] = [];
                                        }
                                        $matches_by_round[$round][] = $match;
                                    }
                                    ksort($matches_by_round);
                                    
                                    ob_start();
                                    ?>
                                    <div class="text-center mb-6">
                                        <h3 class="text-xl font-bold text-gray-900 mb-1">Tournament Bracket</h3>
                                        <p class="text-gray-600">Manage matches and update scores</p>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <div class="flex space-x-8 justify-start items-start min-w-max">
                                            <?php foreach ($matches_by_round as $round => $round_matches): ?>
                                                <div class="round-section">
                                                    <div class="text-center font-semibold text-blue-700 mb-4 bg-blue-50 py-2 px-4 rounded-lg">
                                                        <?php
                                                        $round_names = [
                                                            1 => 'ROUND 1',
                                                            2 => 'ROUND 2', 
                                                            3 => 'QUARTER-FINALS',
                                                            4 => 'SEMI-FINALS',
                                                            5 => 'FINALS'
                                                        ];
                                                        echo $round_names[$round] ?? "ROUND $round";
                                                        ?>
                                                    </div>
                                                    <div class="space-y-4">
                                                        <?php foreach ($round_matches as $match): ?>
                                                            <div class="match-card bg-white border-2 border-blue-300 rounded-lg shadow-sm p-4 min-w-[280px]">
                                                                <div class="flex justify-between items-center mb-3">
                                                                    <div class="text-sm font-medium text-gray-700">M<?php echo $match['match_number']; ?></div>
                                                                    <div class="text-xs px-2 py-1 rounded-full <?php echo $match['status'] === 'completed' ? 'bg-green-100 text-green-800' : ($match['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'); ?>">
                                                                        <?php echo ucfirst($match['status']); ?>
                                                                    </div>
                                                                </div>
                                                                
                                                                <form method="POST" action="" class="space-y-3">
                                                                    <input type="hidden" name="match_id" value="<?php echo $match['id']; ?>">
                                                                    
                                                                    <!-- Team 1 -->
                                                                    <div class="team-section">
                                                                        <div class="flex justify-between items-center mb-1">
                                                                            <span class="text-sm font-medium"><?php echo $match['team1_name'] ?: 'TBD'; ?></span>
                                                                            <input type="number" name="team1_score" value="<?php echo $match['team1_score'] ?? ''; ?>" 
                                                                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" 
                                                                                   min="0" placeholder="Score">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Team 2 -->
                                                                    <div class="team-section">
                                                                        <div class="flex justify-between items-center mb-1">
                                                                            <span class="text-sm font-medium"><?php echo $match['team2_name'] ?: 'TBD'; ?></span>
                                                                            <input type="number" name="team2_score" value="<?php echo $match['team2_score'] ?? ''; ?>" 
                                                                                   class="w-16 px-2 py-1 border border-gray-300 rounded text-sm" 
                                                                                   min="0" placeholder="Score">
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <!-- Winner Selection -->
                                                                    <div class="mt-3">
                                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Winner:</label>
                                                                        <select name="winner_id" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                                            <option value="">Select Winner</option>
                                                                            <?php if ($match['team1_id']): ?>
                                                                                <option value="<?php echo $match['team1_id']; ?>" <?php echo $match['winner_id'] == $match['team1_id'] ? 'selected' : ''; ?>>
                                                                                    <?php echo $match['team1_name']; ?>
                                                                                </option>
                                                                            <?php endif; ?>
                                                                            <?php if ($match['team2_id']): ?>
                                                                                <option value="<?php echo $match['team2_id']; ?>" <?php echo $match['winner_id'] == $match['team2_id'] ? 'selected' : ''; ?>>
                                                                                    <?php echo $match['team2_name']; ?>
                                                                                </option>
                                                                            <?php endif; ?>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <!-- Status -->
                                                                    <div class="mt-2">
                                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Status:</label>
                                                                        <select name="status" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                                                                            <option value="scheduled" <?php echo $match['status'] === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                                                            <option value="in_progress" <?php echo $match['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                                            <option value="completed" <?php echo $match['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <button type="submit" name="update_match" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-3 rounded text-sm font-medium transition duration-200">
                                                                        Update Match
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php
                                    return ob_get_clean();
                                }

                                echo renderManageBracket($matches, $teams, $tournament_id, $tournament['format']);
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-500"></i>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="tournament_brackets.php?id=<?php echo $tournament_id; ?>" class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg text-center transition duration-200">
                            <i class="fas fa-sitemap text-2xl mb-2"></i>
                            <div class="font-medium">View Full Brackets</div>
                        </a>
                        <a href="bracket_setup.php" class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg text-center transition duration-200">
                            <i class="fas fa-edit text-2xl mb-2"></i>
                            <div class="font-medium">Edit Bracket</div>
                        </a>
                        <a href="tournament.php" class="bg-gray-600 hover:bg-gray-700 text-white p-4 rounded-lg text-center transition duration-200">
                            <i class="fas fa-arrow-left text-2xl mb-2"></i>
                            <div class="font-medium">Back to Tournaments</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.match-card {
    transition: all 0.2s ease;
}

.match-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.team-section {
    padding: 8px;
    background-color: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
}

.round-section {
    min-width: 300px;
}

.single-elimination-bracket {
    @apply overflow-x-auto;
}
</style>

<?php include 'includes/footer.php'; ?>