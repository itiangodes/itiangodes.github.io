<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['join_error'] = "You must be logged in to leave a team.";
    header("Location: login.php"); // redirect to login instead of unauthorized
    exit();
}

$username = $_SESSION['username'];
$team_id = $_POST['team_id'] ?? null;

// Validate team_id
if (empty($team_id)) {
    $_SESSION['join_error'] = "Invalid team selection.";
    header("Location: join_team.php");
    exit();
}

// Check if the player is actually in this team
$checkStmt = $pdo->prepare("SELECT * FROM player_teams WHERE player_username = ? AND team_id = ?");
$checkStmt->execute([$username, $team_id]);
$membership = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$membership) {
    $_SESSION['join_error'] = "You are not a member of this team.";
    header("Location: join_team.php");
    exit();
}

// Remove player from the team
$deleteStmt = $pdo->prepare("DELETE FROM player_teams WHERE player_username = ? AND team_id = ?");
$deleteStmt->execute([$username, $team_id]);

$_SESSION['join_success'] = "You have successfully left the team.";
header("Location: join_team.php");
exit();
