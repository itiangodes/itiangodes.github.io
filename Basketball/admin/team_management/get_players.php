<?php
include '../includes/db.php';

if (!isset($_GET['team_id'])) {
    echo "<p class='text-red-500'>Team ID is required.</p>";
    exit;
}

$team_id = intval($_GET['team_id']);

try {
    $stmt = $pdo->prepare("
        SELECT u.firstname, u.middlename, u.lastname, u.username, pt.player_status
        FROM player_teams pt
        JOIN users u ON pt.player_username = u.username
        WHERE pt.team_id = ?
        ORDER BY u.lastname, u.firstname
    ");
    $stmt->execute([$team_id]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($players)) {
        echo "<p class='text-gray-500 italic text-center py-4'>No players found for this team.</p>";
        exit;
    }
} catch (Exception $e) {
    echo "<p class='text-red-500'>Error loading players: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>

<ul class="divide-y divide-gray-200">
<?php foreach ($players as $m): 
    $status = $m['player_status'] ?? 'Active';
    $username = htmlspecialchars($m['username']);
    // Handle middlename - include it only if it exists
    $fullname = htmlspecialchars(
        $m['firstname'] . 
        ($m['middlename'] ? ' ' . $m['middlename'] . ' ' : ' ') . 
        $m['lastname']
    );
    
    $statusColor = match ($status) {
        'Active' => 'bg-green-100 text-green-700',
        'Inactive' => 'bg-gray-100 text-gray-700',
        'Injured' => 'bg-yellow-100 text-yellow-700',
        'Disqualified' => 'bg-red-100 text-red-700',
        default => 'bg-gray-200 text-gray-700'
    };
?>
    <li class="py-3 flex justify-between items-center">
        <div class="flex-1">
            <p class="font-medium text-gray-800"><?= $fullname ?></p>
            <p class="text-sm text-gray-500">@<?= $username ?></p>
            <span id="status-badge-<?= $username ?>" class="inline-block mt-1 px-2 py-1 rounded text-xs font-semibold <?= $statusColor ?>">
                <?= htmlspecialchars($status) ?>
            </span>
        </div>
        <div class="ml-4">
            <select 
                class="player-status border border-gray-300 text-sm rounded px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400"
                data-username="<?= $username ?>">
                <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="Injured" <?= $status === 'Injured' ? 'selected' : '' ?>>Injured</option>
                <option value="Disqualified" <?= $status === 'Disqualified' ? 'selected' : '' ?>>Disqualified</option>
            </select>
        </div>
    </li>
<?php endforeach; ?>
</ul>