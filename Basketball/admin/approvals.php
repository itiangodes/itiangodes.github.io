<?php
include 'includes/auth.php'; // Check if admin
include 'includes/db.php';

// ====== APPROVE TEAM ======
if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    
    try {
        // Get tournament info for this team
        $stmt = $pdo->prepare("
            SELECT tr.tournament_id, tn.max_teams, tn.name as tournament_name,
                   (SELECT COUNT(*) FROM tournament_registrations 
                    WHERE tournament_id = tr.tournament_id AND status = 'approved') as approved_count
            FROM tournament_registrations tr
            LEFT JOIN tournaments tn ON tr.tournament_id = tn.id
            WHERE tr.team_id = ?
        ");
        $stmt->execute([$id]);
        $tournament_info = $stmt->fetch();
        
        if ($tournament_info) {
            // Check if tournament has reached capacity
            if ($tournament_info['approved_count'] >= $tournament_info['max_teams']) {
                $_SESSION['error'] = "Tournament '{$tournament_info['tournament_name']}' has reached maximum capacity ({$tournament_info['max_teams']} teams). Cannot approve more teams.";
                header("Location: approvals.php");
                exit();
            }
            
            // Check if this barangay already has an approved team in this tournament
            $stmt_check = $pdo->prepare("
                SELECT t.barangay 
                FROM teams t
                JOIN tournament_registrations tr ON t.id = tr.team_id
                WHERE t.barangay = (SELECT barangay FROM teams WHERE id = ?)
                AND tr.tournament_id = ?
                AND tr.status = 'approved'
                AND t.id != ?
            ");
            $stmt_check->execute([$id, $tournament_info['tournament_id'], $id]);
            $existing_team = $stmt_check->fetch();
            
            if ($existing_team) {
                $_SESSION['error'] = "This barangay already has an approved team in tournament '{$tournament_info['tournament_name']}'.";
                header("Location: approvals.php");
                exit();
            }
        }
        
        // Proceed with approval
        $pdo->beginTransaction();
        
        // Update team approval status
        $stmt = $pdo->prepare("UPDATE teams SET approval_status = 'approved', rejection_reason = NULL WHERE id = ?");
        $stmt->execute([$id]);
        
        // Update tournament registration status
        $stmt = $pdo->prepare("UPDATE tournament_registrations SET status = 'approved' WHERE team_id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Team approved successfully for tournament '{$tournament_info['tournament_name']}'.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error approving team: " . $e->getMessage();
    }
    
    header("Location: approvals.php");
    exit();
}

// ====== REJECT TEAM WITH REASON ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_id'])) {
    $id = $_POST['reject_id'];
    $reason = trim($_POST['rejection_reason']);

    try {
        $pdo->beginTransaction();
        
        // Update team approval status
        $stmt = $pdo->prepare("UPDATE teams SET approval_status = 'rejected', rejection_reason = ? WHERE id = ?");
        $stmt->execute([$reason, $id]);
        
        // Update tournament registration status
        $stmt = $pdo->prepare("UPDATE tournament_registrations SET status = 'rejected' WHERE team_id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        
        $_SESSION['success'] = "Team rejected successfully.";
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error rejecting team: " . $e->getMessage();
    }

    header("Location: approvals.php");
    exit();
}

// ====== FETCH PENDING TEAMS WITH TOURNAMENT INFO ======
$stmt = $pdo->prepare("
    SELECT 
        t.*,
        tr.tournament_id,
        tn.name as tournament_name,
        tn.start_date,
        tn.registration_deadline,
        tn.max_teams,
        (SELECT COUNT(*) FROM tournament_registrations 
         WHERE tournament_id = tr.tournament_id AND status = 'approved') as approved_count,
        u.firstname as coach_firstname,
        u.lastname as coach_lastname
    FROM teams t
    JOIN tournament_registrations tr ON t.id = tr.team_id
    JOIN tournaments tn ON tr.tournament_id = tn.id
    LEFT JOIN users u ON t.coach_username = u.username
    WHERE t.approval_status = 'pending'
    ORDER BY tn.start_date ASC, t.barangay ASC
");
$stmt->execute();
$pending_teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group teams by tournament for better organization
$teams_by_tournament = [];
foreach ($pending_teams as $team) {
    $tournament_id = $team['tournament_id'];
    if (!isset($teams_by_tournament[$tournament_id])) {
        $teams_by_tournament[$tournament_id] = [
            'tournament_name' => $team['tournament_name'],
            'start_date' => $team['start_date'],
            'registration_deadline' => $team['registration_deadline'],
            'max_teams' => $team['max_teams'],
            'approved_count' => $team['approved_count'],
            'teams' => []
        ];
    }
    $teams_by_tournament[$tournament_id]['teams'][] = $team;
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Team Approvals</h1>
            <div class="text-sm text-gray-600">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                Manage team registrations for tournaments
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-8">
        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 bg-green-100 text-green-800 rounded-lg border border-green-200">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 bg-red-100 text-red-800 rounded-lg border border-red-200">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($pending_teams)): ?>
            <!-- No Pending Teams -->
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <div class="text-6xl mb-4 text-gray-300">✅</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">All Caught Up!</h3>
                <p class="text-gray-500">There are no pending team registrations at the moment.</p>
            </div>
        <?php else: ?>
            <!-- Pending Teams by Tournament -->
            <?php foreach ($teams_by_tournament as $tournament_id => $tournament_data): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Tournament Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4 text-white">
                        <div class="flex justify-between items-center">
                            <div>
                                <h2 class="text-xl font-bold"><?= htmlspecialchars($tournament_data['tournament_name']) ?></h2>
                                <div class="flex items-center space-x-4 mt-1 text-blue-100 text-sm">
                                    <span>
                                        <i class="fas fa-calendar-day mr-1"></i>
                                        Starts: <?= date('M j, Y', strtotime($tournament_data['start_date'])) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-clock mr-1"></i>
                                        Register until: <?= date('M j, Y', strtotime($tournament_data['registration_deadline'])) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-users mr-1"></i>
                                        Teams: <?= $tournament_data['approved_count'] ?>/<?= $tournament_data['max_teams'] ?> approved
                                    </span>
                                </div>
                            </div>
                            <div class="bg-blue-400 px-3 py-1 rounded-full text-sm font-semibold">
                                <?= count($tournament_data['teams']) ?> pending
                            </div>
                        </div>
                    </div>

                    <!-- Teams Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-left border-b">
                                    <th class="py-3 px-4 font-semibold text-gray-700">Barangay</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700">Coach</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700">Registered On</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700">Status</th>
                                    <th class="py-3 px-4 font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tournament_data['teams'] as $team): ?>
                                    <tr class="border-b hover:bg-gray-50 transition duration-150">
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($team['barangay']) ?></div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Team Code: <?= htmlspecialchars($team['team_code']) ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-gray-900">
                                                <?= htmlspecialchars($team['coach_firstname'] . ' ' . $team['coach_lastname']) ?>
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                @<?= htmlspecialchars($team['coach_username']) ?>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600">
                                            <?= date('M j, Y', strtotime($team['created_at'])) ?>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>
                                                Pending Review
                                            </span>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex space-x-2">
                                                <!-- Approve Button -->
                                                <a href="?approve=<?= $team['id'] ?>"
                                                   class="p-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition duration-200 group relative"
                                                   onclick="return confirm('Approve <?= htmlspecialchars($team['barangay']) ?> team for <?= htmlspecialchars($tournament_data['tournament_name']) ?>?')"
                                                   title="Approve Team">
                                                    <i class="fas fa-check-circle"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition duration-200 whitespace-nowrap z-10">
                                                        Approve Team
                                                    </span>
                                                </a>

                                                <!-- Reject Button -->
                                                <button 
                                                    class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition duration-200 group relative"
                                                    onclick="openRejectModal(<?= $team['id'] ?>, '<?= htmlspecialchars($team['barangay']) ?>', '<?= htmlspecialchars($tournament_data['tournament_name']) ?>')"
                                                    title="Reject Team">
                                                    <i class="fas fa-times-circle"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition duration-200 whitespace-nowrap z-10">
                                                        Reject Team
                                                    </span>
                                                </button>

                                                <!-- View Details Button -->
                                                <button 
                                                    class="p-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition duration-200 group relative"
                                                    onclick="openTeamModal(<?= htmlspecialchars(json_encode($team)) ?>)"
                                                    title="View Team Details">
                                                    <i class="fas fa-eye"></i>
                                                    <span class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition duration-200 whitespace-nowrap z-10">
                                                        View Details
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Reject Team Registration</h3>
        <p id="rejectTeamInfo" class="text-gray-600 mb-4 text-sm"></p>
        <form method="POST">
            <input type="hidden" name="reject_id" id="reject_id">
            <label class="block text-gray-700 text-sm font-medium mb-2">Reason for rejection *</label>
            <textarea name="rejection_reason" required rows="4"
                      class="w-full border border-gray-300 rounded-md p-3 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
                      placeholder="Please provide a reason for rejecting this team registration..."></textarea>
            <div class="flex justify-end mt-4 space-x-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition duration-200">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition duration-200">
                    <i class="fas fa-ban mr-1"></i> Reject Team
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Team Details Modal -->
<div id="teamModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-2xl shadow-lg mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Team Registration Details</h3>
        <div id="teamDetails" class="space-y-4">
            <!-- Team details will be populated by JavaScript -->
        </div>
        <div class="flex justify-end mt-6">
            <button type="button" onclick="closeTeamModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 transition duration-200">
                <i class="fas fa-times mr-1"></i> Close
            </button>
        </div>
    </div>
</div>

<script>
function openRejectModal(teamId, barangay, tournament) {
    document.getElementById('reject_id').value = teamId;
    document.getElementById('rejectTeamInfo').textContent = `Team: ${barangay} (${tournament})`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.querySelector('textarea[name="rejection_reason"]').value = '';
}

function openTeamModal(team) {
    const detailsDiv = document.getElementById('teamDetails');
    detailsDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2">Team Information</h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Barangay:</strong> ${team.barangay}</p>
                    <p><strong>Team Code:</strong> <code class="bg-gray-200 px-2 py-1 rounded">${team.team_code}</code></p>
                    <p><strong>Status:</strong> <span class="text-yellow-600">Pending Review</span></p>
                    <p><strong>Created:</strong> ${new Date(team.created_at).toLocaleDateString()}</p>
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-2">Coach Information</h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Name:</strong> ${team.coach_firstname} ${team.coach_lastname}</p>
                    <p><strong>Username:</strong> ${team.coach_username}</p>
                </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                <h4 class="font-semibold text-gray-700 mb-2">Tournament Information</h4>
                <div class="space-y-2 text-sm">
                    <p><strong>Tournament:</strong> ${team.tournament_name}</p>
                    <p><strong>Start Date:</strong> ${new Date(team.start_date).toLocaleDateString()}</p>
                    <p><strong>Registration Deadline:</strong> ${new Date(team.registration_deadline).toLocaleDateString()}</p>
                    <p><strong>Capacity:</strong> ${team.approved_count}/${team.max_teams} teams approved</p>
                </div>
            </div>
        </div>
    `;
    document.getElementById('teamModal').classList.remove('hidden');
}

function closeTeamModal() {
    document.getElementById('teamModal').classList.add('hidden');
}

// Close modals when clicking outside
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target.id === 'rejectModal') {
        closeRejectModal();
    }
});

document.getElementById('teamModal').addEventListener('click', function(e) {
    if (e.target.id === 'teamModal') {
        closeTeamModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
        closeTeamModal();
    }
});
</script>

<style>
/* Smooth transitions */
button, a {
    transition: all 0.2s ease-in-out;
}

/* Ensure tooltips stay on top */
.relative .absolute {
    z-index: 1000;
}
</style>

<?php include 'includes/footer.php'; ?>