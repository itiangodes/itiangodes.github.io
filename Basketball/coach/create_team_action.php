<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php'; // ensures coach is logged in

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barangay = trim($_POST['barangay']);
    $tournament_id = $_POST['tournament_id'];
    $coach_username = $_SESSION['username'];

    try {
        // Get coach's barangay from user data to verify they're creating team for their own barangay
        $stmt_coach = $pdo->prepare("SELECT barangay FROM users WHERE username = ?");
        $stmt_coach->execute([$coach_username]);
        $coach_data = $stmt_coach->fetch(PDO::FETCH_ASSOC);
        $coach_barangay = $coach_data['barangay'] ?? null;

        // Security check: Ensure coach can only create team for their own barangay
        if ($coach_barangay !== $barangay) {
            $_SESSION['team_success'] = "❌ You can only create a team for your assigned barangay: " . htmlspecialchars($coach_barangay);
            header("Location: create_team.php");
            exit;
        }

        // Check if coach already has a pending or approved team
        $stmt = $pdo->prepare("
            SELECT t.*, tr.status as registration_status 
            FROM teams t 
            LEFT JOIN tournament_registrations tr ON t.id = tr.team_id 
            WHERE t.coach_username = ? AND t.approval_status IN ('pending', 'approved')
        ");
        $stmt->execute([$coach_username]);
        $existingTeam = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingTeam) {
            if ($existingTeam['approval_status'] === 'pending') {
                $_SESSION['team_success'] = "❌ You already have a team pending approval.";
            } else {
                $_SESSION['team_success'] = "❌ You already have an approved team.";
            }
            header("Location: create_team.php");
            exit;
        }

        // Check if this barangay already has an APPROVED team for this tournament
        $stmt = $pdo->prepare("
            SELECT t.*, tr.tournament_id 
            FROM teams t 
            JOIN tournament_registrations tr ON t.id = tr.team_id 
            WHERE t.barangay = ? 
            AND tr.tournament_id = ? 
            AND t.approval_status = 'approved' 
            AND tr.status = 'approved'
        ");
        $stmt->execute([$barangay, $tournament_id]);
        $barangayTeam = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($barangayTeam) {
            $_SESSION['team_success'] = "❌ " . htmlspecialchars($barangay) . " already has an approved team for this tournament.";
            header("Location: create_team.php");
            exit;
        }

        // Check tournament availability and capacity
        $stmt_tournament = $pdo->prepare("
            SELECT t.*, 
                   (SELECT COUNT(*) FROM tournament_registrations tr 
                    JOIN teams tm ON tr.team_id = tm.id 
                    WHERE tr.tournament_id = t.id AND tm.approval_status = 'approved' AND tr.status = 'approved') as registered_teams
            FROM tournaments t 
            WHERE t.id = ? 
            AND t.status = 'registration_open' 
            AND t.registration_deadline >= CURDATE()
        ");
        $stmt_tournament->execute([$tournament_id]);
        $tournament = $stmt_tournament->fetch(PDO::FETCH_ASSOC);

        if (!$tournament) {
            $_SESSION['team_success'] = "❌ Tournament is not available for registration.";
            header("Location: create_team.php");
            exit;
        }

        if ($tournament['registered_teams'] >= $tournament['max_teams']) {
            $_SESSION['team_success'] = "❌ Tournament is already full. Maximum " . $tournament['max_teams'] . " teams allowed.";
            header("Location: create_team.php");
            exit;
        }

        // Start transaction
        $pdo->beginTransaction();

        // Insert new team (without team_code)
        $stmt = $pdo->prepare("INSERT INTO teams (barangay, coach_username, approval_status) VALUES (?, ?, 'pending')");
        $stmt->execute([$barangay, $coach_username]);
        $team_id = $pdo->lastInsertId();

        // Register team for tournament
        $stmt = $pdo->prepare("INSERT INTO tournament_registrations (tournament_id, team_id, status) VALUES (?, ?, 'pending')");
        $stmt->execute([$tournament_id, $team_id]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['team_success'] = "✅ Team created successfully! Waiting for admin approval.";
        header("Location: create_team.php");
        exit;

    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // More specific error message
        if (strpos($e->getMessage(), 'team_code') !== false) {
            $_SESSION['team_success'] = "❌ Database configuration error. Please contact administrator.";
        } else {
            $_SESSION['team_success'] = "❌ Error: " . $e->getMessage();
        }
        header("Location: create_team.php");
        exit;
    }
} else {
    header("Location: create_team.php");
    exit;
}
?>