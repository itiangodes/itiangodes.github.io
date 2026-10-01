<?php
include 'includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT m.*, t.name as tournament_name, 
               ta.team_name as teamA_name, tb.team_name as teamB_name,
               p.firstname as mvp_firstname, p.lastname as mvp_lastname
        FROM matches m 
        LEFT JOIN tournaments t ON m.tournament_id = t.id 
        LEFT JOIN teams ta ON m.teamA_id = ta.id
        LEFT JOIN teams tb ON m.teamB_id = tb.id
        LEFT JOIN players p ON m.mvp_player_id = p.player_id
        ORDER BY m.date DESC, m.time DESC";

$result = $conn->query($sql);
$matches = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
}

echo json_encode(['success' => true, 'matches' => $matches]);
$conn->close();
?>