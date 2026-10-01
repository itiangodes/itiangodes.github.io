<?php
// review_tournament.php
include 'includes/auth.php';
include 'includes/db.php';

// Check if we're in setup mode (existing tournament) or creation mode (new tournament)
$setup_mode = false;
$tournament_id = null;

if (isset($_SESSION['tournament_data']['id'])) {
    // Setup mode - existing tournament
    $setup_mode = true;
    $tournament_id = $_SESSION['tournament_data']['id'];
    
    // Verify tournament exists and get current data
    $stmt = $pdo->prepare("SELECT * FROM tournaments WHERE id = ? AND created_by = ?");
    $stmt->execute([$tournament_id, $_SESSION['user_id']]);
    $existing_tournament = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing_tournament) {
        $_SESSION['error'] = "Tournament not found or access denied.";
        header("Location: tournament.php");
        exit();
    }
    
    // Update session data with actual tournament name and start_date from database
    $_SESSION['tournament_data']['name'] = $existing_tournament['name'];
    $_SESSION['tournament_data']['start_date'] = $existing_tournament['start_date'];
    $_SESSION['tournament_data']['location'] = $existing_tournament['location'];
    
    // Ensure we have the required data
    if (empty($_SESSION['tournament_data']['teams']) || empty($_SESSION['tournament_data']['format'])) {
        header("Location: bracket_setup.php");
        exit();
    }
} else {
    // Creation mode - new tournament
    if (empty($_SESSION['tournament_data']) || empty($_SESSION['tournament_data']['format'])) {
        header("Location: bracket_setup.php");
        exit();
    }
}

$tournament_data = $_SESSION['tournament_data'];

// Ensure matches table exists with correct schema
ensureMatchesTableExists($pdo);

// Ensure tournaments table exists with correct schema
ensureTournamentsTableExists($pdo);

// Ensure teams table has tournament_id column
ensureTeamsTableHasTournamentId($pdo);

// Handle form submission and save tournament
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_tournament'])) {
    
    try {
        $pdo->beginTransaction();
        
        if ($setup_mode) {
            // UPDATE EXISTING TOURNAMENT
            $tournament_id = $tournament_data['id'];
            
            // Update tournament format and team count
            $stmt = $pdo->prepare("
                UPDATE tournaments 
                SET format = ?, team_count = ?, status = 'active', updated_at = NOW()
                WHERE id = ? AND created_by = ?
            ");
            
            $stmt->execute([
                $tournament_data['format'],
                $tournament_data['team_count'],
                $tournament_id,
                $_SESSION['user_id']
            ]);
            
            // Link selected teams to tournament - UPDATED VERSION
            if (isset($tournament_data['selected_team_ids']) && !empty($tournament_data['selected_team_ids'])) {
                $placeholders = str_repeat('?,', count($tournament_data['selected_team_ids']) - 1) . '?';
                
                // DEBUG: Log the team data
                error_log("Tournament setup - Linking " . count($tournament_data['selected_team_ids']) . " teams to tournament $tournament_id");
                
                // Simply update all selected teams
                $updateStmt = $pdo->prepare("
                    UPDATE teams 
                    SET tournament_id = ? 
                    WHERE id IN ($placeholders)
                ");
                
                $params = array_merge([$tournament_id], $tournament_data['selected_team_ids']);
                $updateStmt->execute($params);
                
                $affected_rows = $updateStmt->rowCount();
                
                error_log("Linked $affected_rows out of " . count($tournament_data['selected_team_ids']) . " teams to tournament $tournament_id");
                
                // Only throw error if NO teams were linked at all
                if ($affected_rows === 0) {
                    throw new Exception("Failed to link any teams to tournament. Please check if team IDs are valid.");
                }
            }
            
            // Create bracket matches
            $bracket_created = createTournamentBracket($pdo, $tournament_id, $tournament_data['teams'], $tournament_data['format']);
            
            if (!$bracket_created) {
                throw new Exception("Failed to create tournament bracket.");
            }
            
            $success_message = "Tournament '{$existing_tournament['name']}' setup completed successfully with {$tournament_data['team_count']} teams and {$tournament_data['format']} bracket!";
            
        } else {
            // CREATE NEW TOURNAMENT
            $stmt = $pdo->prepare("
                INSERT INTO tournaments (name, start_date, location, format, team_count, created_by, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            $stmt->execute([
                $tournament_data['name'],
                $tournament_data['start_date'],
                $tournament_data['location'],
                $tournament_data['format'],
                $tournament_data['team_count'],
                $_SESSION['user_id']
            ]);
            
            $tournament_id = $pdo->lastInsertId();
            
            // Handle teams for new tournament - UPDATED VERSION
            if (isset($tournament_data['selected_team_ids']) && !empty($tournament_data['selected_team_ids'])) {
                $placeholders = str_repeat('?,', count($tournament_data['selected_team_ids']) - 1) . '?';
                
                // DEBUG: Log the team data
                error_log("Tournament creation - Linking " . count($tournament_data['selected_team_ids']) . " teams to tournament $tournament_id");
                
                // Simply update all selected teams
                $updateStmt = $pdo->prepare("
                    UPDATE teams 
                    SET tournament_id = ? 
                    WHERE id IN ($placeholders)
                ");
                
                $params = array_merge([$tournament_id], $tournament_data['selected_team_ids']);
                $updateStmt->execute($params);
                
                $affected_rows = $updateStmt->rowCount();
                
                error_log("Linked $affected_rows out of " . count($tournament_data['selected_team_ids']) . " teams to tournament $tournament_id");
                
                // Only throw error if NO teams were linked at all
                if ($affected_rows === 0) {
                    throw new Exception("Failed to link any teams to tournament. Please check if team IDs are valid.");
                }
            }
            
            // Create bracket matches
            $bracket_created = createTournamentBracket($pdo, $tournament_id, $tournament_data['teams'], $tournament_data['format']);
            
            if (!$bracket_created) {
                throw new Exception("Failed to create tournament bracket.");
            }
            
            $success_message = "Tournament '{$tournament_data['name']}' created successfully with {$tournament_data['team_count']} teams and {$tournament_data['format']} bracket!";
        }
        
        $pdo->commit();
        
        // Clear session data
        unset($_SESSION['tournament_data']);
        
        // Redirect to success page
        $_SESSION['success_tournament_id'] = $tournament_id;
        $_SESSION['success_message'] = $success_message;
        
        header("Location: tournament.php?success=1");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error " . ($setup_mode ? "setting up" : "creating") . " tournament: " . $e->getMessage();
        error_log("Tournament " . ($setup_mode ? "setup" : "creation") . " error: " . $e->getMessage());
        error_log("Tournament data: " . print_r($tournament_data, true));
    }
}

// Function to ensure matches table exists with correct schema
function ensureMatchesTableExists($pdo) {
    try {
        $pdo->query("SELECT 1 FROM matches LIMIT 1");
        error_log("Matches table exists");
        
        // Check and add missing columns if needed
        $columns_to_check = ['status', 'bracket_type', 'team1_score', 'team2_score', 'winner_id'];
        
        foreach ($columns_to_check as $column) {
            $checkStmt = $pdo->query("SHOW COLUMNS FROM matches LIKE '$column'");
            if (!$checkStmt->fetch()) {
                switch($column) {
                    case 'status':
                        $pdo->exec("ALTER TABLE matches ADD COLUMN status VARCHAR(20) DEFAULT 'scheduled'");
                        break;
                    case 'bracket_type':
                        $pdo->exec("ALTER TABLE matches ADD COLUMN bracket_type VARCHAR(20) DEFAULT 'winners'");
                        break;
                    case 'team1_score':
                        $pdo->exec("ALTER TABLE matches ADD COLUMN team1_score INT NULL");
                        break;
                    case 'team2_score':
                        $pdo->exec("ALTER TABLE matches ADD COLUMN team2_score INT NULL");
                        break;
                    case 'winner_id':
                        $pdo->exec("ALTER TABLE matches ADD COLUMN winner_id INT NULL");
                        break;
                }
                error_log("Added $column column to matches table");
            }
        }
        
    } catch (Exception $e) {
        // Table doesn't exist, create it with correct schema
        $createTableSQL = "
        CREATE TABLE matches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tournament_id INT NOT NULL,
            round_number INT NOT NULL,
            match_number INT NOT NULL,
            team1_id INT NULL,
            team2_id INT NULL,
            team1_score INT NULL,
            team2_score INT NULL,
            winner_id INT NULL,
            status VARCHAR(20) DEFAULT 'scheduled',
            bracket_type VARCHAR(20) DEFAULT 'winners',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX tournament_id_index (tournament_id),
            INDEX round_number_index (round_number)
        )";
        $pdo->exec($createTableSQL);
        error_log("Created matches table with correct schema");
    }
}

// Function to ensure tournaments table exists with correct schema
function ensureTournamentsTableExists($pdo) {
    try {
        $pdo->query("SELECT 1 FROM tournaments LIMIT 1");
        error_log("Tournaments table exists");
        
        // Check and add missing columns if needed
        $columns_to_check = ['updated_at', 'start_date'];
        
        foreach ($columns_to_check as $column) {
            $checkStmt = $pdo->query("SHOW COLUMNS FROM tournaments LIKE '$column'");
            if (!$checkStmt->fetch()) {
                switch($column) {
                    case 'updated_at':
                        $pdo->exec("ALTER TABLE tournaments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                        break;
                    case 'start_date':
                        $pdo->exec("ALTER TABLE tournaments ADD COLUMN start_date DATE NULL AFTER date");
                        $pdo->exec("UPDATE tournaments SET start_date = date WHERE start_date IS NULL");
                        $pdo->exec("ALTER TABLE tournaments MODIFY start_date DATE NOT NULL");
                        break;
                }
                error_log("Added $column column to tournaments table");
            }
        }
        
    } catch (Exception $e) {
        // Table doesn't exist, we should create it (but it should exist already)
        error_log("Tournaments table might not exist: " . $e->getMessage());
    }
}

// Function to ensure teams table has tournament_id column
function ensureTeamsTableHasTournamentId($pdo) {
    try {
        $checkStmt = $pdo->query("SHOW COLUMNS FROM teams LIKE 'tournament_id'");
        if (!$checkStmt->fetch()) {
            // Add tournament_id column
            $pdo->exec("ALTER TABLE teams ADD COLUMN tournament_id INT NULL");
            $pdo->exec("ALTER TABLE teams ADD INDEX tournament_id_index (tournament_id)");
            error_log("Added tournament_id column to teams table");
        }
    } catch (Exception $e) {
        error_log("Error checking teams table structure: " . $e->getMessage());
        throw new Exception("Database configuration error: teams table missing required columns");
    }
}

// Function to create tournament bracket matches
function createTournamentBracket($pdo, $tournament_id, $teams, $format) {
    if (empty($teams)) {
        throw new Exception("No teams provided to create bracket");
    }
    
    try {
        // Clear any existing matches for this tournament
        $deleteStmt = $pdo->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $deleteStmt->execute([$tournament_id]);
        
        if ($format === 'single') {
            return createSingleEliminationBracket($pdo, $tournament_id, $teams);
        } elseif ($format === 'double') {
            return createDoubleEliminationBracket($pdo, $tournament_id, $teams);
        } elseif ($format === 'roundrobin') {
            return createRoundRobinBracket($pdo, $tournament_id, $teams);
        } else {
            return createSingleEliminationBracket($pdo, $tournament_id, $teams);
        }
        
    } catch (Exception $e) {
        error_log("Error creating bracket for tournament $tournament_id: " . $e->getMessage());
        throw new Exception("Failed to create tournament bracket: " . $e->getMessage());
    }
}

function createSingleEliminationBracket($pdo, $tournament_id, $teams) {
    $team_count = count($teams);
    $rounds = ceil(log($team_count, 2));
    $bracket_size = pow(2, $rounds);
    
    shuffle($teams);
    $match_number = 1;
    
    // First round
    $first_round_matches = $bracket_size / 2;
    
    for ($i = 0; $i < $first_round_matches; $i++) {
        $team1_index = $i * 2;
        $team2_index = $i * 2 + 1;
        
        $team1_id = ($team1_index < $team_count) ? $teams[$team1_index]['id'] : null;
        $team2_id = ($team2_index < $team_count) ? $teams[$team2_index]['id'] : null;
        
        $status = ($team1_id && $team2_id) ? 'scheduled' : 'pending';
        
        $stmt = $pdo->prepare("
            INSERT INTO matches (tournament_id, round_number, match_number, team1_id, team2_id, status) 
            VALUES (?, 1, ?, ?, ?, ?)
        ");
        $stmt->execute([$tournament_id, $match_number, $team1_id, $team2_id, $status]);
        $match_number++;
    }
    
    // Subsequent rounds
    for ($round = 2; $round <= $rounds; $round++) {
        $matches_in_round = $bracket_size / pow(2, $round);
        
        for ($match = 1; $match <= $matches_in_round; $match++) {
            $stmt = $pdo->prepare("
                INSERT INTO matches (tournament_id, round_number, match_number, team1_id, team2_id, status) 
                VALUES (?, ?, ?, NULL, NULL, 'pending')
            ");
            $stmt->execute([$tournament_id, $round, $match_number]);
            $match_number++;
        }
    }
    
    error_log("Successfully created single elimination bracket for tournament $tournament_id");
    return true;
}

function createDoubleEliminationBracket($pdo, $tournament_id, $teams) {
    return createSingleEliminationBracket($pdo, $tournament_id, $teams);
}

function createRoundRobinBracket($pdo, $tournament_id, $teams) {
    $team_count = count($teams);
    $match_number = 1;
    
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

include 'includes/header.php';
include 'includes/sidebar.php';

// Calculate bracket statistics
$team_count = $tournament_data['team_count'];
$format = $tournament_data['format'];

// Calculate expected matches based on format
if ($format === 'single') {
    $expected_matches = $team_count - 1;
    $expected_rounds = ceil(log($team_count, 2));
    $bracket_type = "Single Elimination";
} elseif ($format === 'double') {
    $expected_matches = ($team_count - 1) * 2;
    $expected_rounds = ceil(log($team_count, 2)) * 2;
    $bracket_type = "Double Elimination";
} elseif ($format === 'roundrobin') {
    $expected_matches = ($team_count * ($team_count - 1)) / 2;
    $expected_rounds = $team_count - 1;
    $bracket_type = "Round Robin";
} else {
    $expected_matches = $team_count - 1;
    $expected_rounds = ceil(log($team_count, 2));
    $bracket_type = "Single Elimination";
}
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                <?php echo $setup_mode ? 'Setup Tournament' : 'Create Tournament'; ?> - Step 4: Review & Save
            </h1>
            <div class="flex items-center space-x-4">
                <a href="tournament.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Tournaments
                </a>
                <span class="text-sm text-gray-500">Welcome, <?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Progress Steps -->
        <div class="max-w-6xl mx-auto mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="progress-step completed">
                        <div class="step-number">1</div>
                        <div class="step-label">Tournament Details</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step completed">
                        <div class="step-number">2</div>
                        <div class="step-label">Select Teams</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step completed">
                        <div class="step-number">3</div>
                        <div class="step-label">Bracket Setup</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step active">
                        <div class="step-number">4</div>
                        <div class="step-label">Review & Save</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto">
            <!-- Error Message -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6">
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="save_tournament" value="1">
                
                <div class="bg-white rounded-xl shadow-md p-8">
                    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                        <i class="fas fa-file-alt text-green-500"></i> 
                        <?php echo $setup_mode ? 'Review Tournament Setup' : 'Review Tournament Creation'; ?>
                    </h2>

                    <!-- Mode Indicator -->
                    <?php if ($setup_mode): ?>
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-cogs text-blue-600 mr-3"></i>
                                <div>
                                    <h4 class="text-base font-medium text-blue-800">Setup Mode</h4>
                                    <p class="text-sm text-blue-700 mt-1">
                                        You are setting up an existing tournament. This will update the tournament format, teams, and generate a new bracket.
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Tournament Summary -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="summary-card">
                            <div class="summary-icon bg-blue-100 text-blue-600">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-value"><?php echo htmlspecialchars($tournament_data['name']); ?></div>
                                <div class="summary-label">Tournament Name</div>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon bg-green-100 text-green-600">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-value"><?php echo $team_count; ?></div>
                                <div class="summary-label">Total Teams</div>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon bg-purple-100 text-purple-600">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="summary-content">
                                <?php
                                $format_names = [
                                    'single' => 'Single Elimination',
                                    'double' => 'Double Elimination',
                                    'roundrobin' => 'Round Robin',
                                    'groupstage' => 'Group Stage',
                                    'marchmadness' => 'March Madness'
                                ];
                                ?>
                                <div class="summary-value"><?php echo $format_names[$format] ?? ucfirst($format); ?></div>
                                <div class="summary-label">Bracket Format</div>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon bg-orange-100 text-orange-600">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="summary-content">
                                <div class="summary-value"><?php echo date('M j, Y', strtotime($tournament_data['start_date'])); ?></div>
                                <div class="summary-label">Start Date</div>
                            </div>
                        </div>
                    </div>

                    <!-- Bracket Generation Details -->
                    <div class="mb-8 bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <i class="fas fa-project-diagram text-green-600 mr-4 text-2xl mt-1"></i>
                            <div class="flex-1">
                                <h4 class="text-lg font-semibold text-green-800 mb-3">
                                    🏆 Bracket Configuration
                                </h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600"><?php echo $expected_rounds; ?></div>
                                        <div class="text-sm text-green-700">Total Rounds</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600"><?php echo $expected_matches; ?></div>
                                        <div class="text-sm text-green-700">Total Matches</div>
                                    </div>
                                    <div class="text-center">
                                        <div class="text-2xl font-bold text-green-600"><?php echo $team_count; ?></div>
                                        <div class="text-sm text-green-700">Participating Teams</div>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-green-700 mb-3">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    A <strong><?php echo $bracket_type; ?></strong> bracket will be generated with <strong><?php echo $expected_matches; ?> matches</strong> across <strong><?php echo $expected_rounds; ?> rounds</strong>.
                                </p>
                                
                                <?php if ($format === 'single'): ?>
                                    <p class="text-sm text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Pure single elimination format - No 3rd/4th place matches
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Teams Preview -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <i class="fas fa-list-ol text-blue-500"></i>
                            Selected Teams (<?php echo $team_count; ?>)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <?php foreach ($tournament_data['teams'] as $index => $team): ?>
                                <div class="bg-white border border-gray-200 rounded-lg p-4 text-center">
                                    <div class="text-lg font-bold text-blue-600 mb-2">#<?php echo $index + 1; ?></div>
                                    <div class="font-semibold text-gray-800 text-base mb-2"><?php echo htmlspecialchars($team['barangay']); ?></div>
                                    <?php if (!empty($team['coach_username'])): ?>
                                        <div class="text-xs text-gray-600 bg-gray-100 rounded px-2 py-1 inline-block">
                                            <i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($team['coach_username']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-between pt-6 border-t border-gray-200">
                        <div class="flex gap-3">
                            <a href="bracket_setup.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i>
                                Back to Bracket Setup
                            </a>
                            <a href="team_setup.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-edit"></i>
                                Edit Teams
                            </a>
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $setup_mode ? 'Complete Setup & Generate Bracket' : 'Save Tournament & Generate Bracket'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e5e7eb;
    border: 3px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #6b7280;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.3s ease;
}

.progress-connector {
    height: 3px;
    background-color: #e5e7eb;
    flex: 1;
    min-width: 60px;
    margin: 0 10px;
    position: relative;
    top: -25px;
    z-index: 1;
}

.progress-step.active .step-number {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.progress-step.active .step-label {
    color: #3b82f6;
    font-weight: 600;
}

.progress-step.completed .step-number {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.progress-step.completed .step-label {
    color: #10b981;
    font-weight: 600;
}

.progress-connector.completed {
    background-color: #10b981;
}

.summary-card {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    gap: 12px;
}

.summary-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.summary-content {
    flex: 1;
}

.summary-value {
    font-size: 1.25rem;
    font-weight: bold;
    color: #1f2937;
}

.summary-label {
    font-size: 0.875rem;
    color: #6b7280;
}
</style>

<?php include 'includes/footer.php'; ?>