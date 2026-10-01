<?php
include 'includes/db.php'; // ✅ your PDO connection file

header('Content-Type: application/json');

try {
    // Optional: filtering logic from query parameters
    $division = $_GET['division'] ?? '';
    $team = $_GET['team'] ?? '';
    $search = $_GET['search'] ?? '';
    $sort = $_GET['sort'] ?? '';

    $query = "SELECT * FROM player_stats WHERE 1=1";
    $params = [];

    if ($division !== '') {
        $query .= " AND division = :division";
        $params['division'] = $division;
    }

    if ($team !== '') {
        $query .= " AND team = :team";
        $params['team'] = $team;
    }

    if ($search !== '') {
        $query .= " AND player_name LIKE :search";
        $params['search'] = "%$search%";
    }

    // Sorting logic
    $allowedSorts = ['points', 'rebounds', 'assists', 'fg_pct'];
    if (in_array($sort, $allowedSorts)) {
        $query .= " ORDER BY $sort DESC";
    } else {
        $query .= " ORDER BY points DESC";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'players' => $players]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
