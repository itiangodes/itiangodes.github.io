[file name]: assign_position.php
[file content begin]
<?php
session_start();
include 'includes/db.php';

// Check if user is logged in and is a coach
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check user type
$is_coach = false;
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'coach') {
    $is_coach = true;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'coach') {
    $is_coach = true;
} elseif (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'coach') {
    $is_coach = true;
}

if (!$is_coach) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $player_team_id = $_POST['player_team_id'] ?? '';
    $position = $_POST['position'] ?? '';
    
    if (empty($player_team_id)) {
        $_SESSION['msg'] = "Invalid request!";
        header("Location: create_team.php");
        exit();
    }
    
    try {
        // Validate that the coach owns this team
        $coach_username = $_SESSION['username'];
        
        $stmt = $pdo->prepare("
            SELECT pt.* 
            FROM player_teams pt 
            JOIN teams t ON pt.team_id = t.id 
            WHERE pt.id = ? AND t.coach_username = ?
        ");
        $stmt->execute([$player_team_id, $coach_username]);
        $player_team = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$player_team) {
            $_SESSION['msg'] = "Player not found or you don't have permission!";
            header("Location: create_team.php");
            exit();
        }
        
        // Update player position
        $update_stmt = $pdo->prepare("UPDATE player_teams SET position = ? WHERE id = ?");
        $result = $update_stmt->execute([$position, $player_team_id]);
        
        if ($result) {
            if (empty($position)) {
                $_SESSION['msg'] = "Position cleared successfully!";
            } else {
                $_SESSION['msg'] = "Position assigned successfully!";
            }
        } else {
            $_SESSION['msg'] = "Failed to assign position. Please try again.";
        }
        
    } catch (PDOException $e) {
        error_log("Position assignment error: " . $e->getMessage());
        $_SESSION['msg'] = "Error assigning position. Please try again.";
    }
    
    header("Location: create_team.php");
    exit();
}

// If not POST request, redirect
header("Location: create_team.php");
exit();
?>
[file content end]