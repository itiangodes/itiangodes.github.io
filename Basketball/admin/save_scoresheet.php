<?php
// save_scoresheet.php
include 'includes/auth.php';
include 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON data received');
    }

    // Extract data from the scoresheet
    $match_id = isset($input['match_id']) ? intval($input['match_id']) : null;
    $team_a_name = $input['teamA'] ?? '';
    $team_b_name = $input['teamB'] ?? '';
    $referee = $input['referee'] ?? '';
    $umpire1 = $input['umpire1'] ?? '';
    $umpire2 = $input['umpire2'] ?? '';
    $scorer = $input['scorer'] ?? '';
    $game_date = $input['gameDate'] ?? '';
    $game_time = $input['gameTime'] ?? '';
    $venue = $input['venue'] ?? '';
    $coach_a = $input['coachA'] ?? '';
    $coach_b = $input['coachB'] ?? '';
    
    // Extract scores
    $scores = $input['scores'] ?? [];
    $q1_team_a = intval($scores['q1A'] ?? 0);
    $q1_team_b = intval($scores['q1B'] ?? 0);
    $q2_team_a = intval($scores['q2A'] ?? 0);
    $q2_team_b = intval($scores['q2B'] ?? 0);
    $q3_team_a = intval($scores['q3A'] ?? 0);
    $q3_team_b = intval($scores['q3B'] ?? 0);
    $q4_team_a = intval($scores['q4A'] ?? 0);
    $q4_team_b = intval($scores['q4B'] ?? 0);
    $ot_team_a = intval($scores['otA'] ?? 0);
    $ot_team_b = intval($scores['otB'] ?? 0);
    $final_team_a = intval($scores['finalA'] ?? 0);
    $final_team_b = intval($scores['finalB'] ?? 0);
    
    $winning_team = $input['winningTeam'] ?? '';
    $mvp = $input['mvp'] ?? '';
    $player_stats = isset($input['playerStats']) ? json_encode($input['playerStats']) : '{}';
    $running_score = isset($input['runningScore']) ? json_encode($input['runningScore']) : '[]';
    
    // Validate required fields
    if (empty($match_id)) {
        throw new Exception('Match ID is required');
    }
    
    if (empty($coach_a) || empty($coach_b)) {
        throw new Exception('Both coach names are required');
    }
    
    if (empty($winning_team)) {
        throw new Exception('Winning team is required');
    }

    // Get match details to determine which team won
    $match_stmt = $pdo->prepare("
        SELECT m.*, t1.barangay as team1_name, t2.barangay as team2_name, 
               t1.id as team1_id, t2.id as team2_id, m.tournament_id 
        FROM matches m 
        LEFT JOIN teams t1 ON m.team1_id = t1.id 
        LEFT JOIN teams t2 ON m.team2_id = t2.id 
        WHERE m.id = ?
    ");
    $match_stmt->execute([$match_id]);
    $match = $match_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        throw new Exception("Match with ID $match_id not found");
    }

    // DEBUG: Log match details
    error_log("Match details - Team1: {$match['team1_name']} (ID: {$match['team1_id']}), Team2: {$match['team2_name']} (ID: {$match['team2_id']})");

    // Determine winner team ID based on winning team name - SIMPLIFIED VERSION
    $winner_id = null;

    // Simple logic: Use scores to determine winner (same as edit score functionality)
    if ($final_team_a > $final_team_b) {
        $winner_id = $match['team1_id'];
        error_log("Winner determined by score: Team A wins (Score: $final_team_a vs $final_team_b)");
    } elseif ($final_team_b > $final_team_a) {
        $winner_id = $match['team2_id'];
        error_log("Winner determined by score: Team B wins (Score: $final_team_a vs $final_team_b)");
    } else {
        error_log("Game ended in TIE - no winner to advance");
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Save scoresheet data
    $stmt = $pdo->prepare("
        INSERT INTO scoresheets (
            match_id, team_a_name, team_b_name, referee, umpire1, umpire2, scorer,
            game_date, game_time, venue, coach_a, coach_b,
            q1_team_a, q1_team_b, q2_team_a, q2_team_b, q3_team_a, q3_team_b,
            q4_team_a, q4_team_b, ot_team_a, ot_team_b, final_team_a, final_team_b,
            winning_team, mvp_player, player_stats, running_score_data, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $stmt->execute([
        $match_id, $team_a_name, $team_b_name, $referee, $umpire1, $umpire2, $scorer,
        $game_date ?: null, $game_time ?: null, $venue, $coach_a, $coach_b,
        $q1_team_a, $q1_team_b, $q2_team_a, $q2_team_b, $q3_team_a, $q3_team_b,
        $q4_team_a, $q4_team_b, $ot_team_a, $ot_team_b, $final_team_a, $final_team_b,
        $winning_team, $mvp, $player_stats, $running_score, $_SESSION['user_id']
    ]);

    if (!$result) {
        throw new Exception('Failed to save scoresheet data');
    }

    // Update match with scores and winner
    $update_match_stmt = $pdo->prepare("
        UPDATE matches 
        SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
        WHERE id = ?
    ");
    $update_result = $update_match_stmt->execute([
        $final_team_a, 
        $final_team_b, 
        $winner_id, 
        $match_id
    ]);

    if (!$update_result) {
        throw new Exception('Failed to update match data');
    }

    // Automatically advance winner to next round - USING WORKING LOGIC FROM EDIT SCORE
    $winner_advanced = false;
    if ($winner_id) {
        error_log("Attempting to advance winner team $winner_id to next round...");
        
        // Use the same logic that works in edit score functionality
        $winner_advanced = updateNextRoundMatches($pdo, $match['tournament_id'], $match['round_number']);
        
        if ($winner_advanced) {
            error_log("Successfully advanced winner to next round using updateNextRoundMatches");
        } else {
            error_log("Failed to advance winner to next round");
        }
    } else {
        error_log("No winner_id available, skipping bracket advancement");
    }

    // Update tournament status
    updateTournamentStatus($pdo, $match['tournament_id']);

    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Scoresheet saved and bracket updated successfully',
        'match_updated' => true,
        'winner_advanced' => $winner_advanced,
        'debug_info' => [
            'winner_id' => $winner_id,
            'winning_team' => $winning_team,
            'final_score_a' => $final_team_a,
            'final_score_b' => $final_team_b,
            'db_team1' => $match['team1_name'],
            'db_team2' => $match['team2_name']
        ]
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error saving scoresheet: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving scoresheet: ' . $e->getMessage()
    ]);
}

// COPY FROM simulate_bracket.php - This is the working bracket advancement function
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
            error_log("No next round matches found for round $next_round");
            return false; // No next round
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
                    $update_result = $update_stmt->execute([$match['winner_id'], $next_match['id']]);
                    
                    if ($update_result) {
                        error_log("Advanced winner {$match['winner_id']} from match {$match['id']} to match {$next_match['id']} as $team_slot");
                    } else {
                        error_log("Failed to advance winner to next match");
                    }
                }
                $match_index++;
            }
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating next round matches: " . $e->getMessage());
        return false;
    }
}

// COPY FROM simulate_bracket.php - Helper function to get tournament matches
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
        
        return $matches;
    } catch (Exception $e) {
        error_log("Error fetching matches for tournament $tournament_id: " . $e->getMessage());
        return [];
    }
}

// Function to update tournament status
function updateTournamentStatus($pdo, $tournament_id) {
    try {
        // Check if all matches are completed
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_matches,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_matches
            FROM matches 
            WHERE tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($stats['total_matches'] > 0 && $stats['completed_matches'] == $stats['total_matches']) {
            $update_stmt = $pdo->prepare("
                UPDATE tournaments SET status = 'completed', updated_at = NOW() WHERE id = ?
            ");
            $update_stmt->execute([$tournament_id]);
            error_log("Tournament $tournament_id marked as completed");
        }
    } catch (Exception $e) {
        error_log("Error updating tournament status: " . $e->getMessage());
    }
}
?>