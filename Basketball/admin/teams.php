<?php
include 'includes/auth.php';
include 'includes/db.php';

// ========================== FETCH TEAMS (Approved Only) ==========================
$stmt = $pdo->query("SELECT * FROM teams WHERE approval_status = 'approved' ORDER BY id DESC");
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ========================== FETCH MEMBERS ==========================
$membersByTeam = [];
$memberQuery = $pdo->query("
    SELECT 
        pt.id AS player_team_id,
        u.username,
        CONCAT(u.firstname, ' ', u.lastname) AS fullname,
        pt.player_status,
        pt.team_id,
        pt.status AS team_member_status,
        pt.created_at
    FROM player_teams pt
    JOIN users u ON pt.player_username = u.username
    JOIN players p ON u.user_id = p.user_id
    ORDER BY pt.team_id, u.lastname
");
while ($row = $memberQuery->fetch(PDO::FETCH_ASSOC)) {
    $membersByTeam[$row['team_id']][] = $row;
}

// ========================== HANDLE FORM SUBMISSIONS ==========================

// ADD TEAM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_team'])) {
    $barangay = trim($_POST['barangay']);
    $coach = trim($_POST['coach']);
    $status = trim($_POST['status'] ?? 'Active');
    
    if ($barangay !== '' && $coach !== '') {
        $stmt = $pdo->prepare("INSERT INTO teams (barangay, coach_username, status, approval_status) VALUES (?, ?, ?, 'approved')");
        $stmt->execute([$barangay, $coach, $status]);
        header("Location: teams.php?msg=added");
        exit();
    }
}

// UPDATE TEAM STATUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_team_status'])) {
    $team_id = $_POST['team_id'];
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE teams SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $team_id]);
    header("Location: teams.php?msg=team_status_updated");
    exit();
}

// DELETE TEAM
if (isset($_GET['delete_team'])) {
    $stmt = $pdo->prepare("DELETE FROM teams WHERE id = ?");
    $stmt->execute([$_GET['delete_team']]);
    header("Location: teams.php?msg=deleted");
    exit();
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">Manage Teams</h1>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-8">

        <!-- Message Box -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="p-4 rounded <?= ($_GET['msg'] === 'deleted') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-800' ?>">
                <?= [
                    'added' => '✅ Team added successfully.',
                    'updated' => '✅ Team updated successfully.',
                    'deleted' => '🗑️ Team deleted successfully.',
                    'status_updated' => '🔁 Player status updated successfully.',
                    'team_status_updated' => '🔁 Team status updated successfully.'
                ][$_GET['msg']] ?? '' ?>
            </div>
        <?php endif; ?>

        <!-- Add Team Form -->
        <form method="POST" class="space-y-4 bg-white p-6 shadow rounded">
            <input type="hidden" name="add_team" value="1">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Barangay</label>
                    <select name="barangay" class="w-full border p-2 rounded focus:ring focus:ring-blue-300" required>
                        <option value="">Select Barangay</option>
                        <option value="Amaya I">Amaya I</option>
                        <option value="Amaya II">Amaya II</option>
                        <option value="Amaya III">Amaya III</option>
                        <option value="Amaya IV">Amaya IV</option>
                        <option value="Amaya V">Amaya V</option>
                        <option value="Amaya VI">Amaya VI</option>
                        <option value="Amaya VII">Amaya VII</option>
                        <option value="Amaya VIII">Amaya VIII</option>
                        <option value="Amaya IX">Amaya IX</option>
                        <option value="Amaya X">Amaya X</option>
                        <option value="Amaya XI">Amaya XI</option>
                        <option value="Amaya XII">Amaya XII</option>
                        <option value="Bagtas">Bagtas</option>
                        <option value="Biga">Biga</option>
                        <option value="Biwas">Biwas</option>
                        <option value="Bucal">Bucal</option>
                        <option value="Calibuyo">Calibuyo</option>
                        <option value="Capipisa">Capipisa</option>
                        <option value="Daang Amaya I">Daang Amaya I</option>
                        <option value="Daang Amaya II">Daang Amaya II</option>
                        <option value="Daang Amaya III">Daang Amaya III</option>
                        <option value="Halayhay">Halayhay</option>
                        <option value="Julugan I">Julugan I</option>
                        <option value="Julugan II">Julugan II</option>
                        <option value="Julugan III">Julugan III</option>
                        <option value="Julugan IV">Julugan IV</option>
                        <option value="Julugan V">Julugan V</option>
                        <option value="Julugan VI">Julugan VI</option>
                        <option value="Julugan VII">Julugan VII</option>
                        <option value="Julugan VIII">Julugan VIII</option>
                        <option value="Mulawin">Mulawin</option>
                        <option value="Paradahan I">Paradahan I</option>
                        <option value="Paradahan II">Paradahan II</option>
                        <option value="Poblacion I">Poblacion I</option>
                        <option value="Poblacion II">Poblacion II</option>
                        <option value="Poblacion III">Poblacion III</option>
                        <option value="Poblacion IV">Poblacion IV</option>
                        <option value="Punta I">Punta I</option>
                        <option value="Punta II">Punta II</option>
                        <option value="Sahud Ulan">Sahud Ulan</option>
                        <option value="Sanja Mayor">Sanja Mayor</option>
                        <option value="Santol">Santol</option>
                        <option value="Tanauan">Tanauan</option>
                        <option value="Tres Cruses">Tres Cruses</option>
                    </select>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Coach Username</label>
                    <input type="text" name="coach" class="w-full border p-2 rounded focus:ring focus:ring-blue-300" required>
                </div>
                <div>
                    <label class="block font-medium text-gray-700 mb-1">Team Status</label>
                    <select name="status" class="w-full border p-2 rounded focus:ring focus:ring-blue-300">
                        <option value="Active" selected>Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add Team</button>
        </form>

        <!-- Team List -->
        <div class="bg-white p-6 shadow rounded">
            <h2 class="text-lg font-semibold mb-4">Approved Teams</h2>

            <?php if (count($teams) === 0): ?>
                <p class="text-gray-600">No approved teams found.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <div class="max-h-[500px] overflow-y-auto border rounded">
                        <table class="w-full table-auto border-collapse">
                            <thead class="sticky top-0 bg-gray-100 z-10">
                                <tr class="text-left border-b">
                                    <th class="py-2 px-4">Barangay</th>
                                    <th class="py-2 px-4">Team Status</th>
                                    <th class="py-2 px-4">Change Team Status</th>
                                    <th class="py-2 px-4">Coach</th>
                                    <th class="py-2 px-4">Players</th>
                                    <th class="py-2 px-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($teams as $t): ?>
                                    <tr class="border-t hover:bg-gray-50 align-top" data-team-id="<?= $t['id'] ?>">
                                        <td class="py-2 px-4 font-medium">
                                            <?= htmlspecialchars($t['barangay']) ?>
                                        </td>
                                        <td class="py-2 px-4">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $t['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' ?>">
                                                <?= $t['status'] ?>
                                            </span>
                                        </td>
                                        <td class="py-2 px-4">
                                            <form method="POST">
                                                <input type="hidden" name="update_team_status" value="1">
                                                <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                                                <select name="status" onchange="this.form.submit()" class="border text-sm rounded px-2 py-1 focus:ring focus:ring-blue-300">
                                                    <option value="Active" <?= $t['status']==='Active'?'selected':'' ?>>Active</option>
                                                    <option value="Inactive" <?= $t['status']==='Inactive'?'selected':'' ?>>Inactive</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="py-2 px-4"><?= htmlspecialchars($t['coach_username']) ?></td>
                                        <td class="py-2 px-4 text-center">
                                            <?php if (!empty($membersByTeam[$t['id']])): ?>
                                                <button class="text-blue-600 hover:text-blue-800 font-medium view-members-btn" 
                                                        data-team-id="<?= $t['id'] ?>" 
                                                        data-barangay="<?= htmlspecialchars($t['barangay']) ?>">
                                                    View Members (<?= count($membersByTeam[$t['id']]) ?>)
                                                </button>
                                            <?php else: ?>
                                                <span class="text-gray-400 italic">No members</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-4">
                                            <a href="?delete_team=<?= $t['id'] ?>" onclick="return confirm('Delete this team?')" class="text-red-600 hover:text-red-800 inline-block">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Player List Modal -->
<div id="playerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-4">
        <div class="flex justify-between items-center border-b px-6 py-3">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">Team Members</h3>
                <p id="modalTeamName" class="text-sm text-gray-500"></p>
            </div>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-800">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div id="modalContent" class="p-6 max-h-[400px] overflow-y-auto">
            <div class="text-center py-4">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                <p class="text-gray-500 mt-2">Loading team members...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Scripts -->
<script>
// Event delegation for view members buttons
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('view-members-btn')) {
        const teamId = e.target.getAttribute('data-team-id');
        const barangay = e.target.getAttribute('data-barangay');
        openModal(teamId, barangay);
    }
});

function openModal(teamId, barangay) {
    console.log('Opening modal for team:', teamId, 'Barangay:', barangay);
    
    const modal = document.getElementById('playerModal');
    const modalContent = document.getElementById('modalContent');
    const modalTeamName = document.getElementById('modalTeamName');

    modalTeamName.textContent = `Barangay: ${barangay}`;
    
    // Show loading state
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-500 mt-2">Loading team members...</p>
        </div>
    `;
    
    modal.classList.remove('hidden');

    // Fetch players for this team
    fetch(`team_management/get_players.php?team_id=${teamId}`)
        .then(res => {
            if (!res.ok) {
                throw new Error('Network response was not ok');
            }
            return res.text();
        })
        .then(html => {
            modalContent.innerHTML = html;
        })
        .catch((error) => {
            console.error('Fetch error:', error);
            modalContent.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-500">Failed to load team members.</p>
                    <p class="text-sm text-gray-500 mt-2">Please check the console for details.</p>
                </div>
            `;
        });
}

function closeModal() {
    document.getElementById('playerModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('playerModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Player status handler
document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('player-status')) {
        const username = e.target.dataset.username;
        const newStatus = e.target.value;
        const teamId = new URLSearchParams(window.location.search).get('team_id');

        fetch('team_management/update_player_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `username=${encodeURIComponent(username)}&status=${encodeURIComponent(newStatus)}&team_id=${encodeURIComponent(teamId)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const badge = document.getElementById('status-badge-' + username);
                if (badge) {
                    badge.textContent = newStatus;
                    badge.className = 'inline-block mt-1 px-2 py-1 rounded text-xs font-semibold';
                    if (newStatus === 'Active') badge.classList.add('bg-green-100', 'text-green-700');
                    else if (newStatus === 'Inactive') badge.classList.add('bg-gray-100', 'text-gray-700');
                    else if (newStatus === 'Injured') badge.classList.add('bg-yellow-100', 'text-yellow-700');
                    else if (newStatus === 'Disqualified') badge.classList.add('bg-red-100', 'text-red-700');
                    else badge.classList.add('bg-gray-200', 'text-gray-700');
                }
                
                // Show success message
                showNotification('Player status updated successfully!', 'success');
            } else {
                showNotification('Failed to update: ' + data.message, 'error');
            }
        })
        .catch(() => showNotification('Error updating player status.', 'error'));
    }
});

// Notification function
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-red-100 text-red-800 border border-red-300'
    }`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
</script>