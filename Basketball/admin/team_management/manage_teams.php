<?php
// manage_teams.php
include 'includes/auth.php';
include 'includes/db.php';

// Handle team deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_team'])) {
    $team_id = $_POST['team_id'];
    
    try {
        // Check if team exists and belongs to user
        $check_stmt = $pdo->prepare("
            SELECT id, team_name, tournament_id 
            FROM teams 
            WHERE id = ? AND (created_by = ? OR coach_username = ?)
        ");
        $check_stmt->execute([$team_id, $_SESSION['user_id'], $_SESSION['username']]);
        $team = $check_stmt->fetch();
        
        if (!$team) {
            $_SESSION['error'] = "Team not found or you don't have permission to delete it.";
        } elseif ($team['tournament_id']) {
            $_SESSION['error'] = "Cannot delete team '{$team['team_name']}' because it is currently in a tournament. Please remove it from the tournament first.";
        } else {
            // Delete the team
            $delete_stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
            if ($delete_stmt->execute([$team_id])) {
                $_SESSION['success'] = "Team '{$team['team_name']}' deleted successfully.";
            } else {
                $_SESSION['error'] = "Error deleting team.";
            }
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: manage_teams.php");
    exit();
}

// Fetch user's teams
$teams_stmt = $pdo->prepare("
    SELECT t.*, tour.name as tournament_name 
    FROM teams t 
    LEFT JOIN tournaments tour ON t.tournament_id = tour.id 
    WHERE t.created_by = ? OR t.coach_username = ?
    ORDER BY t.team_name
");
$teams_stmt->execute([$_SESSION['user_id'], $_SESSION['username']]);
$teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Manage Teams</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="mb-4">
        <a href="create_team.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Create New Team</a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($teams as $team): ?>
            <div class="bg-white rounded-lg shadow-md p-4 border <?php echo $team['tournament_id'] ? 'border-orange-300' : 'border-gray-200'; ?>">
                <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($team['team_name']); ?></h3>
                <p class="text-sm text-gray-600 mb-1">Code: <?php echo htmlspecialchars($team['team_code']); ?></p>
                <p class="text-sm text-gray-600 mb-1">Barangay: <?php echo htmlspecialchars($team['barangay']); ?></p>
                <p class="text-sm text-gray-600 mb-2">Status: <?php echo htmlspecialchars($team['approval_status']); ?></p>
                
                <?php if ($team['tournament_id']): ?>
                    <p class="text-sm text-orange-600 mb-3">
                        <i class="fas fa-trophy"></i> In Tournament: <?php echo htmlspecialchars($team['tournament_name']); ?>
                    </p>
                <?php else: ?>
                    <p class="text-sm text-green-600 mb-3">
                        <i class="fas fa-check"></i> Available for tournaments
                    </p>
                <?php endif; ?>
                
                <div class="flex space-x-2">
                    <a href="edit_team.php?id=<?php echo $team['id']; ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">Edit</a>
                    
                    <?php if (!$team['tournament_id']): ?>
                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this team?');">
                            <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
                            <button type="submit" name="delete_team" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">Delete</button>
                        </form>
                    <?php else: ?>
                        <button disabled class="bg-gray-400 text-white px-3 py-1 rounded text-sm cursor-not-allowed">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (empty($teams)): ?>
        <div class="text-center py-8">
            <p class="text-gray-500">You don't have any teams yet.</p>
            <a href="create_team.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mt-4 inline-block">Create Your First Team</a>
        </div>
    <?php endif; ?>
</div>