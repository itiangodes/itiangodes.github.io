<?php
// includes/bracket_functions.php

function generateBracketIfMissing($pdo, $tournament_id, $teams, $format = 'single_elimination') {
    try {
        // Check if matches already exist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);
        $existing_matches = $stmt->fetchColumn();
        
        if ($existing_matches > 0) {
            error_log("Bracket already exists for tournament $tournament_id with $existing_matches matches");
            return true;
        }
        
        // Generate new bracket
        error_log("Generating new bracket for tournament $tournament_id with " . count($teams) . " teams");
        return generateTournamentBrackets($pdo, $tournament_id, $teams, $format);
        
    } catch (Exception $e) {
        error_log("Error in generateBracketIfMissing: " . $e->getMessage());
        return false;
    }
}

function generateTournamentBrackets($pdo, $tournament_id, $teams, $format = 'single_elimination') {
    try {
        $pdo->beginTransaction();
        
        $team_ids = array_column($teams, 'id');
        $team_count = count($team_ids);
        
        if ($team_count < 2) {
            throw new Exception("Need at least 2 teams to generate bracket");
        }
        
        // For single elimination
        $rounds = ceil(log($team_count, 2));
        $total_slots = pow(2, $rounds);
        
        // Add bye teams if needed
        while (count($team_ids) < $total_slots) {
            $team_ids[] = null; // null represents a bye
        }
        
        // Shuffle teams for random seeding
        shuffle($team_ids);
        
        $round = 1;
        $match_number = 1;
        
        // Generate first round matches
        for ($i = 0; $i < count($team_ids); $i += 2) {
            $team1_id = $team_ids[$i];
            $team2_id = $team_ids[$i + 1];
            
            // Only create match if not a bye (at least one real team)
            if ($team1_id !== null || $team2_id !== null) {
                $stmt = $pdo->prepare("
                    INSERT INTO matches (tournament_id, round, match_number, team1_id, team2_id, match_date, status) 
                    VALUES (?, ?, ?, ?, ?, NOW(), 'scheduled')
                ");
                $stmt->execute([$tournament_id, $round, $match_number, $team1_id, $team2_id]);
            }
            
            $match_number++;
        }
        
        $pdo->commit();
        error_log("Successfully generated bracket for tournament $tournament_id with $match_number matches");
        return true;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Bracket generation error for tournament $tournament_id: " . $e->getMessage());
        return false;
    }
}

function getTournamentMatches($pdo, $tournament_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, 
                   t1.name as team1_name, 
                   t2.name as team2_name,
                   t1.seed as team1_seed,
                   t2.seed as team2_seed
            FROM matches m
            LEFT JOIN teams t1 ON m.team1_id = t1.id
            LEFT JOIN teams t2 ON m.team2_id = t2.id
            WHERE m.tournament_id = ?
            ORDER BY m.round, m.match_number
        ");
        $stmt->execute([$tournament_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching matches: " . $e->getMessage());
        return [];
    }
}

function simulateNextRound($pdo, $tournament_id, $teams) {
    try {
        // Get current round with incomplete matches
        $stmt = $pdo->prepare("
            SELECT MIN(round) as current_round 
            FROM matches 
            WHERE tournament_id = ? AND winner_id IS NULL
        ");
        $stmt->execute([$tournament_id]);
        $current_round = $stmt->fetchColumn();
        
        if (!$current_round) {
            $_SESSION['error'] = "All matches are already completed!";
            return;
        }
        
        // Get matches for current round
        $stmt = $pdo->prepare("
            SELECT * FROM matches 
            WHERE tournament_id = ? AND round = ? AND winner_id IS NULL
        ");
        $stmt->execute([$tournament_id, $current_round]);
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $pdo->beginTransaction();
        
        foreach ($matches as $match) {
            // Skip if it's a bye match
            if ($match['team1_id'] === null || $match['team2_id'] === null) {
                $winner_id = $match['team1_id'] ?? $match['team2_id'];
                $stmt = $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?");
                $stmt->execute([$winner_id, $match['id']]);
                continue;
            }
            
            // Simulate match - random winner for now
            $winner_id = (rand(0, 1) === 0) ? $match['team1_id'] : $match['team2_id'];
            $team1_score = rand(60, 120);
            $team2_score = rand(60, 120);
            
            // Ensure scores are different and winner has higher score
            while ($team1_score === $team2_score) {
                $team2_score = rand(60, 120);
            }
            
            if ($winner_id === $match['team1_id'] && $team1_score < $team2_score) {
                $team1_score = $team2_score + 1;
            } elseif ($winner_id === $match['team2_id'] && $team2_score < $team1_score) {
                $team2_score = $team1_score + 1;
            }
            
            $stmt = $pdo->prepare("
                UPDATE matches 
                SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
                WHERE id = ?
            ");
            $stmt->execute([$team1_score, $team2_score, $winner_id, $match['id']]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Round $current_round simulated successfully!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error simulating round: " . $e->getMessage();
    }
}

function simulateEntireTournament($pdo, $tournament_id, $teams) {
    try {
        $pdo->beginTransaction();
        
        // Reset all matches first
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET team1_score = NULL, team2_score = NULL, winner_id = NULL, status = 'scheduled'
            WHERE tournament_id = ?
        ");
        $stmt->execute([$tournament_id]);
        
        // Get all matches ordered by round
        $stmt = $pdo->prepare("
            SELECT * FROM matches 
            WHERE tournament_id = ? 
            ORDER BY round, match_number
        ");
        $stmt->execute([$tournament_id]);
        $all_matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($all_matches as $match) {
            // Skip if it's a bye match
            if ($match['team1_id'] === null || $match['team2_id'] === null) {
                $winner_id = $match['team1_id'] ?? $match['team2_id'];
                $stmt = $pdo->prepare("UPDATE matches SET winner_id = ? WHERE id = ?");
                $stmt->execute([$winner_id, $match['id']]);
                continue;
            }
            
            // Simulate match
            $winner_id = (rand(0, 1) === 0) ? $match['team1_id'] : $match['team2_id'];
            $team1_score = rand(60, 120);
            $team2_score = rand(60, 120);
            
            while ($team1_score === $team2_score) {
                $team2_score = rand(60, 120);
            }
            
            if ($winner_id === $match['team1_id'] && $team1_score < $team2_score) {
                $team1_score = $team2_score + 1;
            } elseif ($winner_id === $match['team2_id'] && $team2_score < $team1_score) {
                $team2_score = $team1_score + 1;
            }
            
            $stmt = $pdo->prepare("
                UPDATE matches 
                SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
                WHERE id = ?
            ");
            $stmt->execute([$team1_score, $team2_score, $winner_id, $match['id']]);
        }
        
        $pdo->commit();
        $_SESSION['success'] = "Entire tournament simulated successfully!";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error simulating tournament: " . $e->getMessage();
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
        
        $_SESSION['success'] = "Tournament simulation reset successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error resetting simulation: " . $e->getMessage();
    }
}

function updateMatchScore($pdo, $match_id, $team1_score, $team2_score) {
    try {
        if ($team1_score == $team2_score) {
            $_SESSION['error'] = "Scores cannot be equal. There must be a winner.";
            return;
        }
        
        $winner_id = ($team1_score > $team2_score) ? 
            (function() use ($pdo, $match_id) {
                $stmt = $pdo->prepare("SELECT team1_id FROM matches WHERE id = ?");
                $stmt->execute([$match_id]);
                return $stmt->fetchColumn();
            })() : 
            (function() use ($pdo, $match_id) {
                $stmt = $pdo->prepare("SELECT team2_id FROM matches WHERE id = ?");
                $stmt->execute([$match_id]);
                return $stmt->fetchColumn();
            })();
        
        $stmt = $pdo->prepare("
            UPDATE matches 
            SET team1_score = ?, team2_score = ?, winner_id = ?, status = 'completed'
            WHERE id = ?
        ");
        $stmt->execute([$team1_score, $team2_score, $winner_id, $match_id]);
        
        $_SESSION['success'] = "Match score updated successfully!";
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating match score: " . $e->getMessage();
    }
}

function forceRegenerateBracket($pdo, $tournament_id, $teams, $format) {
    try {
        $pdo->beginTransaction();
        
        // Delete existing matches
        $stmt = $pdo->prepare("DELETE FROM matches WHERE tournament_id = ?");
        $stmt->execute([$tournament_id]);
        
        // Generate new bracket
        $result = generateTournamentBrackets($pdo, $tournament_id, $teams, $format);
        
        $pdo->commit();
        return $result;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Force regenerate error: " . $e->getMessage());
        return false;
    }
}

function calculateTournamentProgress($matches) {
    if (empty($matches)) return 0;
    
    $completed = 0;
    foreach ($matches as $match) {
        if ($match['winner_id'] !== null) {
            $completed++;
        }
    }
    
    return round(($completed / count($matches)) * 100);
}

function getCompletedMatchesCount($matches) {
    $completed = 0;
    foreach ($matches as $match) {
        if ($match['winner_id'] !== null) {
            $completed++;
        }
    }
    return $completed;
}

function debugBracketStatus($pdo, $tournament_id) {
    error_log("=== BRACKET DEBUG TOURNAMENT $tournament_id ===");
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as team_count FROM teams WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    $team_count = $stmt->fetchColumn();
    error_log("Teams in tournament: $team_count");
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as match_count FROM matches WHERE tournament_id = ?");
    $stmt->execute([$tournament_id]);
    $match_count = $stmt->fetchColumn();
    error_log("Matches in tournament: $match_count");
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as completed_matches FROM matches WHERE tournament_id = ? AND winner_id IS NOT NULL");
    $stmt->execute([$tournament_id]);
    $completed_matches = $stmt->fetchColumn();
    error_log("Completed matches: $completed_matches");
    
    error_log("=== END DEBUG ===");
}

function syncTournamentStatus($pdo, $tournament_id) {
    try {
        // Check if all matches are completed
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as incomplete_matches 
            FROM matches 
            WHERE tournament_id = ? AND winner_id IS NULL
        ");
        $stmt->execute([$tournament_id]);
        $incomplete_matches = $stmt->fetchColumn();
        
        $new_status = ($incomplete_matches == 0) ? 'completed' : 'active';
        
        $stmt = $pdo->prepare("UPDATE tournaments SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $tournament_id]);
        
    } catch (Exception $e) {
        error_log("Error syncing tournament status: " . $e->getMessage());
    }
}

// Placeholder function - you'll need to implement this based on your bracket rendering
function renderTrueBracket($matches, $teams, $tournament_id, $format) {
    // This is a simplified placeholder - implement your actual bracket rendering logic here
    $html = '<div class="text-center p-8 text-gray-500">';
    $html .= '<i class="fas fa-cogs text-4xl mb-4"></i>';
    $html .= '<p>Bracket visualization would appear here</p>';
    $html .= '<p class="text-sm mt-2">Matches: ' . count($matches) . ' | Teams: ' . count($teams) . '</p>';
    $html .= '</div>';
    
    return $html;
}
?>