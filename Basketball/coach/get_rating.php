<?php
session_start();
include 'includes/db.php';

if (isset($_GET['playerid']) && isset($_GET['date'])) {
    $player_id = intval($_GET['playerid']);
    $rating_date = $_GET['date'];
    
    $stmt = $pdo->prepare("SELECT performance_rating as rating, coach_notes as notes FROM training_performance_ratings WHERE player_id = ? AND rating_date = ?");
    $stmt->execute([$player_id, $rating_date]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($result ?: ['rating' => 0, 'notes' => '']);
}
?>
