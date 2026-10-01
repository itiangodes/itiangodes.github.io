<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    $_SESSION['join_error'] = "You must log in first.";
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $team_id = trim($_POST['team_id'] ?? '');

    if (empty($team_id)) {
        $_SESSION['join_error'] = "Team information is missing.";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Get player's barangay
    $playerStmt = $pdo->prepare("SELECT barangay FROM users WHERE username = ?");
    $playerStmt->execute([$username]);
    $player = $playerStmt->fetch(PDO::FETCH_ASSOC);
    $player_barangay = $player['barangay'] ?? null;

    if (!$player_barangay) {
        $_SESSION['join_error'] = "Your barangay information is not set. Please update your profile.";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Check if the team exists and get its barangay
    $stmt = $pdo->prepare("SELECT id, barangay, approval_status FROM teams WHERE id = ?");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        $_SESSION['join_error'] = "Team not found.";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Check if team is from player's barangay
    if ($team['barangay'] !== $player_barangay) {
        $_SESSION['join_error'] = "You can only join teams from your own barangay ($player_barangay).";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Check if team is approved
    if (strtolower($team['approval_status']) !== 'approved') {
        $_SESSION['join_error'] = "This team is not yet approved. Please contact the coach.";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Check if player is already part of any team (pending or approved)
    $checkStmt = $pdo->prepare("
        SELECT status FROM player_teams 
        WHERE player_username = ? AND status IN ('pending', 'approved')
    ");
    $checkStmt->execute([$username]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $_SESSION['join_error'] = "You are already in a team or waiting for approval.";
        header("Location: join_team.php");
        exit();
    }

    // ✅ Insert join request with pending status
    $insertStmt = $pdo->prepare("
        INSERT INTO player_teams (player_username, team_id, status)
        VALUES (?, ?, 'pending')
    ");

    if ($insertStmt->execute([$username, $team['id']])) {
        $_SESSION['join_success'] = "Your join request has been sent to the coach of $player_barangay team. Please wait for approval.";
    } else {
        $_SESSION['join_error'] = "Something went wrong. Please try again.";
    }

} else {
    $_SESSION['join_error'] = "Invalid request method.";
}

header("Location: join_team.php");
exit();