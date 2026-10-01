<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_team_id = $_POST['player_team_id'] ?? '';
    $jersey_number = $_POST['jersey_number'] ?? '';
    $username = $_SESSION['username'];
    
    if (empty($player_team_id) || empty($jersey_number)) {
        $_SESSION['join_error'] = "Invalid request! Please provide a jersey number.";
        header("Location: join_team.php");
        exit();
    }
    
    // Validate jersey number range
    $jersey_number = (int)$jersey_number;
    if ($jersey_number < 0 || $jersey_number > 99) {
        $_SESSION['join_error'] = "Jersey number must be between 0 and 99.";
        header("Location: join_team.php");
        exit();
    }
    
    try {
        // Validate that the player owns this team membership AND is approved
        $stmt = $pdo->prepare("
            SELECT pt.*, t.id as team_id
            FROM player_teams pt 
            JOIN teams t ON pt.team_id = t.id 
            WHERE pt.id = ? AND pt.player_username = ? AND pt.status = 'approved'
        ");
        $stmt->execute([$player_team_id, $username]);
        $player_team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$player_team) {
            $_SESSION['join_error'] = "Player not found, you don't have permission, or you are not yet approved by the coach!";
            header("Location: join_team.php");
            exit();
        }
        
        $team_id = $player_team['team_id'];
        
        // Check if jersey number is already taken in this team
        $stmt_check = $pdo->prepare("
            SELECT id FROM player_teams 
            WHERE team_id = ? AND jersey_number = ? AND id != ? AND status = 'approved'
        ");
        $stmt_check->execute([$team_id, $jersey_number, $player_team_id]);
        $existing_jersey = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if ($existing_jersey) {
            $_SESSION['join_error'] = "Jersey number #$jersey_number is already taken by another player in your team!";
            header("Location: join_team.php");
            exit();
        }
        
        // Check if player already has a jersey number (cannot be changed)
        if (!empty($player_team['jersey_number'])) {
            $_SESSION['join_error'] = "Jersey number cannot be changed once assigned!";
            header("Location: join_team.php");
            exit();
        }
        
        // Assign jersey number
        $update_stmt = $pdo->prepare("UPDATE player_teams SET jersey_number = ? WHERE id = ?");
        $result = $update_stmt->execute([$jersey_number, $player_team_id]);
        
        if ($result) {
            $_SESSION['jersey_msg'] = "Jersey number #$jersey_number assigned successfully!";
        } else {
            $_SESSION['join_error'] = "Failed to assign jersey number. Please try again.";
        }
        
    } catch (PDOException $e) {
        error_log("Player jersey assignment error: " . $e->getMessage());
        
        // Check if it's a duplicate key error
        if (strpos($e->getMessage(), 'unique_team_jersey') !== false) {
            $_SESSION['join_error'] = "Jersey number #$jersey_number is already taken by another player in your team!";
        } else {
            $_SESSION['join_error'] = "Error assigning jersey number. Please try again.";
        }
    }
    
    header("Location: join_team.php");
    exit();
}

// If not POST request, redirect
header("Location: join_team.php");
exit();
?>