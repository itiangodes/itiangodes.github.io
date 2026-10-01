<?php
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['team_id'])) {
    echo json_encode(['success' => false, 'message' => 'Team ID is required']);
    exit;
}

$team_id = $_GET['team_id'];

try {
    // Get players with their stats for the specified team
    $players_stmt = $pdo->prepare("
        SELECT 
            p.player_id,
            CONCAT(u.firstname, ' ', u.lastname) as name,
            p.position,
            p.jersey_number,
            u.profile_picture,
            COALESCE((
                SELECT AVG(ps.points) 
                FROM player_stats ps 
                JOIN games g ON ps.game_id = g.id 
                WHERE ps.player_id = p.player_id 
                AND g.team_id = ?
            ), 0) as ppg,
            COALESCE((
                SELECT AVG(ps.rebounds) 
                FROM player_stats ps 
                JOIN games g ON ps.game_id = g.id 
                WHERE ps.player_id = p.player_id 
                AND g.team_id = ?
            ), 0) as rpg,
            COALESCE((
                SELECT AVG(ps.assists) 
                FROM player_stats ps 
                JOIN games g ON ps.game_id = g.id 
                WHERE ps.player_id = p.player_id 
                AND g.team_id = ?
            ), 0) as apg
        FROM players p
        JOIN users u ON p.user_id = u.user_id
        JOIN player_teams pt ON u.username = pt.player_username
        WHERE pt.team_id = ? AND pt.status = 'approved'
        ORDER BY p.position, p.jersey_number
    ");
    
    $players_stmt->execute([$team_id, $team_id, $team_id, $team_id]);
    $players = $players_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the numbers
    foreach ($players as &$player) {
        $player['ppg'] = number_format($player['ppg'], 1);
        $player['rpg'] = number_format($player['rpg'], 1);
        $player['apg'] = number_format($player['apg'], 1);
    }
    unset($player);
    
    echo json_encode([
        'success' => true,
        'players' => $players
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>