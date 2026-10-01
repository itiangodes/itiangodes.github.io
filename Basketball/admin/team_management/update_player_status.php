<?php
include '../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid method']);
    exit;
}

$username = $_POST['username'] ?? '';
$status = $_POST['status'] ?? '';
$team_id = $_POST['team_id'] ?? '';

if (!$username || !$status) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit;
}

$allowed = ['Active', 'Inactive', 'Injured', 'Disqualified'];
if (!in_array($status, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

try {
    // Update player_status in player_teams table
    if ($team_id) {
        // Update for specific team
        $stmt = $pdo->prepare("UPDATE player_teams SET player_status = ? WHERE player_username = ? AND team_id = ?");
        $success = $stmt->execute([$status, $username, $team_id]);
    } else {
        // Update for all teams (fallback)
        $stmt = $pdo->prepare("UPDATE player_teams SET player_status = ? WHERE player_username = ?");
        $success = $stmt->execute([$status, $username]);
    }
    
    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Player status updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Player not found in team or no changes made']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}