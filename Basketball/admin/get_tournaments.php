<?php
include 'includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT t.*, COUNT(m.id) as match_count 
        FROM tournaments t 
        LEFT JOIN matches m ON t.id = m.tournament_id 
        GROUP BY t.id 
        ORDER BY t.year DESC, t.name ASC";

$result = $conn->query($sql);
$tournaments = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $tournaments[] = $row;
    }
}

echo json_encode(['success' => true, 'tournaments' => $tournaments]);
$conn->close();
?>