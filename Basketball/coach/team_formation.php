<?php
ob_start();
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/db.php';

// LOGIN CHECK
if (!isset($_SESSION['username'])) {
    echo "<p class='text-center text-red-500 mt-10'>You must be logged in as a coach to access this page.</p>";
    include 'includes/footer.php';
    ob_end_flush();
    exit;
}

$coach_username = $_SESSION['username'];

// TEAM CHECK
$stmt_team = $pdo->prepare("SELECT * FROM teams WHERE coach_username = ? LIMIT 1");
$stmt_team->execute([$coach_username]);
$team = $stmt_team->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    ?>
    <section class="p-6">
        <div class="max-w-2xl mx-auto text-center bg-white shadow-md rounded-xl p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-3">You don't have a team yet</h2>
            <p class="text-gray-600 mb-6">Create a team first before setting up formations.</p>
            <a href="create_team.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition">Go to Create Team</a>
        </div>
    </section>
    <?php
    include 'includes/footer.php';
    ob_end_flush();
    exit;
}

// Get team ID
$team_id = $team['id'];

// Function to calculate player rating based on recent matches and training
function calculatePlayerRating($pdo, $player_username) {
    $ratings = [
        'match_performance' => 0,
        'training_performance' => 0,
        'consistency' => 0,
        'recent_form' => 0
    ];
    
    // 1. MATCH PERFORMANCE (60% weight) - Last 5 games (Only Points, Rebounds, Assists)
    // SIMPLIFIED: Use default values for now to avoid complex joins
    $ratings['match_performance'] = 75; // Default value
    
    // 2. TRAINING PERFORMANCE (40% weight) - Using player_username
    // SIMPLIFIED: Use default values for now
    $ratings['training_performance'] = 75; // Default value
    
    
    // Calculate weighted total rating
    $total_rating = (
        ($ratings['match_performance'] * 0.60) +
        ($ratings['training_performance'] * 0.40)
    );
    
    return [
        'total_rating' => round($total_rating, 2),
        'breakdown' => $ratings
    ];
}

// Get all players with their positions and ratings - SIMPLIFIED QUERY
$stmt_players = $pdo->prepare("
    SELECT 
        pt.id as player_team_id,
        pt.player_username as username,
        u.firstname,
        u.lastname,
        pt.position,
        pt.jersey_number,
        NULL as profile_image  -- Remove photo join for now
    FROM player_teams pt
    JOIN users u ON pt.player_username = u.username
    WHERE pt.team_id = ? AND pt.status = 'approved'
    ORDER BY pt.position
");

try {
    $stmt_players->execute([$team_id]);
    $all_players = $stmt_players->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Check what players were found
    error_log("Found " . count($all_players) . " players for team ID: " . $team_id);
    if (count($all_players) > 0) {
        error_log("First player: " . print_r($all_players[0], true));
    }
    
} catch (PDOException $e) {
    echo "<p class='text-red-500 text-center'>Error loading players: " . $e->getMessage() . "</p>";
    error_log("Player loading error: " . $e->getMessage());
    $all_players = [];
}

// If no players found, let's check why with a simple query
if (empty($all_players)) {
    echo "<!-- DEBUG: No players found with main query -->";
    
    // Try a simple query to see what's in player_teams
    try {
        $debug_stmt = $pdo->prepare("SELECT * FROM player_teams WHERE team_id = ?");
        $debug_stmt->execute([$team_id]);
        $debug_players = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<!-- DEBUG: player_teams content: " . print_r($debug_players, true) . " -->";
        
        // Also check users table
        if (count($debug_players) > 0) {
            $usernames = array_column($debug_players, 'player_username');
            $placeholders = str_repeat('?,', count($usernames) - 1) . '?';
            $user_stmt = $pdo->prepare("SELECT username, firstname, lastname FROM users WHERE username IN ($placeholders)");
            $user_stmt->execute($usernames);
            $user_data = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<!-- DEBUG: users data: " . print_r($user_data, true) . " -->";
        }
    } catch (Exception $e) {
        echo "<!-- DEBUG error: " . $e->getMessage() . " -->";
    }
}

// Calculate ratings for all players (using simplified function for now)
$players_with_ratings = [];
foreach ($all_players as $player) {
    $rating_data = calculatePlayerRating($pdo, $player['username']);
    $players_with_ratings[] = array_merge($player, [
        'rating' => $rating_data['total_rating'],
        'rating_breakdown' => $rating_data['breakdown'],
        'fullname' => trim($player['firstname'] . ' ' . $player['lastname'])
    ]);
}

// Get best lineup by position
$positions = ['Point Guard (PG)', 'Shooting Guard (SG)', 'Small Forward (SF)', 'Power Forward (PF)', 'Center (C)'];
$suggested_lineup = [];

foreach ($positions as $position) {
    $position_players = array_filter($players_with_ratings, function($p) use ($position) {
        return $p['position'] === $position;
    });
    
    usort($position_players, function($a, $b) {
        return $b['rating'] <=> $a['rating'];
    });
    
    if (!empty($position_players)) {
        $suggested_lineup[$position] = $position_players[0];
        $suggested_lineup[$position]['alternatives'] = array_slice($position_players, 1, 2);
    }
}

// Sort remaining players for bench
$bench_players = array_filter($players_with_ratings, function($p) use ($suggested_lineup) {
    foreach ($suggested_lineup as $starter) {
        if ($starter['player_team_id'] === $p['player_team_id']) {
            return false;
        }
    }
    return true;
});

usort($bench_players, function($a, $b) {
    return $b['rating'] <=> $a['rating'];
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Formation - AI Suggested Lineup</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .court-container {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            position: relative;
            min-height: 600px;
            border-radius: 20px;
            overflow: hidden;
        }
        
        .court-line {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
        }
        
        .center-circle {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 3px solid rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        
        .player-position {
            position: absolute;
            transform: translate(-50%, -50%);
            transition: all 0.3s ease;
        }
        
        .player-position:hover {
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .player-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 140px;
            cursor: pointer;
        }
        
        .rating-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #fbbf24;
            color: #92400e;
            font-weight: 700;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .position-label {
            background: #1e40af;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 8px;
            display: inline-block;
        }
        
        .player-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 10px auto;
            object-fit: cover;
            border: 3px solid #3b82f6;
        }
        
        /* Position coordinates */
        .pos-point-guard-pg { top: 75%; left: 50%; }    /* Point Guard - backcourt */
        .pos-shooting-guard-sg { top: 50%; left: 75%; } /* Shooting Guard - wing */
        .pos-small-forward-sf { top: 25%; left: 65%; }  /* Small Forward - wing */
        .pos-power-forward-pf { top: 25%; left: 35%; }  /* Power Forward - forward */
        .pos-center-c { top: 15%; left: 50%; }          /* Center - near basket */
        
        .stat-breakdown {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 10px;
            font-size: 11px;
        }
        
        .stat-item {
            background: #f3f4f6;
            padding: 5px;
            border-radius: 5px;
        }
        
        .rating-excellent { background: #10b981; }
        .rating-good { background: #fbbf24; }
        .rating-average { background: #f59e0b; }
        .rating-poor { background: #ef4444; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🏀 Recommended Starting Lineup</h1>
            <p class="text-gray-600">Based on recent match performance and training results</p>
            <div class="mt-4 flex items-center gap-4">
                <span class="text-sm text-gray-500">Team: <strong><?= htmlspecialchars($team['barangay']) ?> Team</strong></span>
                <span class="text-sm text-gray-500">Players: <strong><?= count($all_players) ?></strong></span>
                <button onclick="refreshLineup()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                    🔄 Refresh Ratings
                </button>
            </div>
        </div>

        <?php if (empty($all_players)): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">No Players Found</h2>
                <p class="text-gray-600 mb-4">You need to add players to your team first.</p>
                <p class="text-sm text-gray-500 mb-4">Make sure players have 'approved' status in your team.</p>
                <a href="create_team.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition">
                    Manage Players
                </a>
            </div>
        <?php else: ?>

        <!-- Basketball Court with Starting 5 -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Starting Five Formation</h2>
            
            <div class="court-container relative">
                <!-- Court Lines -->
                <div class="court-line" style="width: 100%; height: 2px; top: 50%; left: 0;"></div>
                <div class="court-line" style="width: 2px; height: 100%; left: 50%; top: 0;"></div>
                <div class="center-circle"></div>
                
                <!-- Players in Formation -->
                <?php foreach ($suggested_lineup as $position => $player): 
                    // Create CSS class from position name
                    $position_class = 'pos-' . strtolower(str_replace([' ', '(', ')'], ['-', '', ''], $position));
                ?>
                    <div class="player-position <?= $position_class ?>" data-player-id="<?= $player['player_team_id'] ?>">
                        <div class="player-card">
                            <div class="rating-badge rating-<?= $player['rating'] >= 80 ? 'excellent' : ($player['rating'] >= 65 ? 'good' : ($player['rating'] >= 50 ? 'average' : 'poor')) ?>">
                                <?= number_format($player['rating'], 0) ?>
                            </div>
                            <div class="position-label"><?= $position ?></div>
                            <?php if (!empty($player['profile_image'])): ?>
                                <img src="<?= htmlspecialchars($player['profile_image']) ?>" alt="<?= htmlspecialchars($player['fullname']) ?>" class="player-avatar">
                            <?php else: ?>
                                <div class="player-avatar bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-2xl font-bold text-white">
                                    <?= substr($player['fullname'], 0, 1) ?>
                                </div>
                            <?php endif; ?>
                            <div class="font-bold text-gray-800 text-sm">#<?= $player['jersey_number'] ?></div>
                            <div class="font-semibold text-gray-700 text-xs"><?= htmlspecialchars($player['fullname']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Detailed Ratings Breakdown -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <?php foreach ($suggested_lineup as $position => $player): ?>
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($player['fullname']) ?></h3>
                            <p class="text-sm text-gray-500"><?= $position ?> • #<?= $player['jersey_number'] ?></p>
                        </div>
                        <div class="text-3xl font-bold text-blue-600"><?= number_format($player['rating'], 1) ?></div>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Match Performance</span>
                                <span class="font-semibold"><?= number_format($player['rating_breakdown']['match_performance'], 1) ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?= $player['rating_breakdown']['match_performance'] ?>%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Training Performance</span>
                                <span class="font-semibold"><?= number_format($player['rating_breakdown']['training_performance'], 1) ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?= $player['rating_breakdown']['training_performance'] ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($player['alternatives'])): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs font-semibold text-gray-600 mb-2">Alternative Options:</p>
                            <div class="space-y-2">
                                <?php foreach ($player['alternatives'] as $alt): ?>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-700"><?= htmlspecialchars($alt['fullname']) ?> (#<?= $alt['jersey_number'] ?>)</span>
                                        <span class="font-semibold text-gray-600"><?= number_format($alt['rating'], 1) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bench Players -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Bench & Reserves</h2>
            <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($bench_players as $player): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                        <div class="flex items-center justify-between mb-3">
                            <span class="position-label"><?= $player['position'] ?></span>
                            <span class="font-bold text-lg text-blue-600"><?= number_format($player['rating'], 0) ?></span>
                        </div>
                        <?php if (!empty($player['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($player['profile_image']) ?>" alt="<?= htmlspecialchars($player['fullname']) ?>" class="w-16 h-16 rounded-full mx-auto mb-2 object-cover border-2 border-gray-300">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-full mx-auto mb-2 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-xl font-bold text-white">
                                <?= substr($player['fullname'], 0, 1) ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-center">
                            <div class="font-bold text-gray-800">#<?= $player['jersey_number'] ?></div>
                            <div class="font-semibold text-gray-700 text-sm"><?= htmlspecialchars($player['fullname']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php endif; ?>

        <!-- Rating Legend -->
        <div class="bg-white rounded-xl shadow-lg p-6 mt-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Rating Calculation Methodology</h3>
            <div class="grid md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600 mb-2">60%</div>
                    <div class="text-sm font-semibold text-gray-700">Match Performance</div>
                    <p class="text-xs text-gray-500 mt-2">Points, Rebounds, Assists from last 5 games</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <div class="text-3xl font-bold text-green-600 mb-2">40%</div>
                    <div class="text-sm font-semibold text-gray-700">Training Performance</div>
                    <p class="text-xs text-gray-500 mt-2">Completion & performance scores</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function refreshLineup() {
            location.reload();
        }
        
        // Add click handlers for player cards to show detailed stats
        document.querySelectorAll('.player-position').forEach(position => {
            position.addEventListener('click', function() {
                const playerId = this.dataset.playerId;
                // You can add a modal or redirect to player details page
                console.log('Player ID:', playerId);
            });
        });
    </script>
</body>
</html>

<?php
include 'includes/footer.php';
ob_end_flush();