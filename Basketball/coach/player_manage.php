<?php
session_start();
include 'includes/db.php';

// Debug session information
error_log("=== PLAYER_MANAGE SESSION DEBUG ===");
error_log("Username: " . ($_SESSION['username'] ?? 'NOT SET'));
error_log("User type: " . ($_SESSION['user_type'] ?? 'NOT SET'));
error_log("All session data: " . print_r($_SESSION, true));

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    error_log("Redirecting to login: No username in session");
    header("Location: login.php");
    exit();
}

// Check user type - try different possible session variable names
$is_coach = false;
$user_type = '';

if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coach') {
    $is_coach = true;
    $user_type = $_SESSION['user_type'];
    error_log("User type is coach");
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'coach') {
    $is_coach = true;
    $user_type = $_SESSION['role'];
    error_log("Role is coach");
} elseif (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'coach') {
    $is_coach = true;
    $user_type = $_SESSION['usertype'];
    error_log("Usertype is coach");
} else {
    error_log("User is not a coach. User type found: " . ($_SESSION['user_type'] ?? 'NOT SET'));
}

if (!$is_coach) {
    error_log("User is not a coach, redirecting to login");
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_id = $_POST['player_id'] ?? '';
    $action = $_POST['action'] ?? '';
    $position = $_POST['position'] ?? '';
    
    error_log("Processing action: player_id=$player_id, action=$action, position=$position");
    
    if (empty($player_id) || empty($action)) {
        $_SESSION['msg'] = "Invalid request!";
        header("Location: create_team.php");
        exit();
    }
    
    try {
        // Validate that the coach owns this team
        $coach_username = $_SESSION['username'];
        
        error_log("Checking permissions for coach: $coach_username");
        
        $stmt = $pdo->prepare("
            SELECT pt.* 
            FROM player_teams pt 
            JOIN teams t ON pt.team_id = t.id 
            WHERE pt.id = ? AND t.coach_username = ?
        ");
        $stmt->execute([$player_id, $coach_username]);
        $player_team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$player_team) {
            error_log("Permission denied: Player team not found for coach");
            $_SESSION['msg'] = "Player not found or you don't have permission!";
            header("Location: create_team.php");
            exit();
        }
        
        error_log("Permission granted. Player team data: " . print_r($player_team, true));
        
        // Handle different actions
        $message = '';
        
        if ($action === 'approve') {
            // Approve player - update status to 'approved'
            $update_stmt = $pdo->prepare("UPDATE player_teams SET status = 'approved' WHERE id = ?");
            $result = $update_stmt->execute([$player_id]);
            $message = "Player approved successfully!";
            error_log("Approving player, rows affected: " . $update_stmt->rowCount());
            
        } elseif ($action === 'reject') {
            // Reject player - update status to 'rejected'
            $update_stmt = $pdo->prepare("UPDATE player_teams SET status = 'rejected' WHERE id = ?");
            $result = $update_stmt->execute([$player_id]);
            $message = "Player rejected successfully!";
            error_log("Rejecting player, rows affected: " . $update_stmt->rowCount());
            
        } elseif ($action === 'remove') {
            // COMPLETELY REMOVE player from team - DELETE the record
            $delete_stmt = $pdo->prepare("DELETE FROM player_teams WHERE id = ?");
            $result = $delete_stmt->execute([$player_id]);
            $message = "Player removed from team successfully!";
            error_log("Removing player (deleting record), rows affected: " . $delete_stmt->rowCount());
            
        } elseif ($action === 'assign_position') {
            // Assign or update player position
            if (empty($position)) {
                // Clear position if empty value is selected
                $update_stmt = $pdo->prepare("UPDATE player_teams SET position = NULL WHERE id = ?");
                $result = $update_stmt->execute([$player_id]);
                $message = "Position cleared successfully!";
                error_log("Clearing player position, rows affected: " . $update_stmt->rowCount());
            } else {
                // Update position
                $update_stmt = $pdo->prepare("UPDATE player_teams SET position = ? WHERE id = ?");
                $result = $update_stmt->execute([$position, $player_id]);
                $message = "Position assigned successfully!";
                error_log("Assigning player position: $position, rows affected: " . $update_stmt->rowCount());
            }
            
        } else {
            $_SESSION['msg'] = "Invalid action!";
            header("Location: create_team.php");
            exit();
        }
        
        if ($result) {
            $_SESSION['msg'] = $message;
            error_log("Action completed successfully: $message");
        } else {
            $_SESSION['msg'] = "Failed to update player. Please try again.";
            error_log("Action failed - no changes made");
        }
        
    } catch (PDOException $e) {
        error_log("Player management error: " . $e->getMessage());
        $_SESSION['msg'] = "Error updating player status. Please try again.";
    }
    
    header("Location: create_team.php");
    exit();
}

// If not POST request, redirect
header("Location: create_team.php");
exit();
?>