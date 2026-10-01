<?php 
include 'includes/header.php'; 
include 'includes/sidebar.php'; 

// Fetch coach's user data to get their barangay
$coach_username = $_SESSION['username'];
$stmt_coach = $pdo->prepare("SELECT barangay FROM users WHERE username = ?");
$stmt_coach->execute([$coach_username]);
$coach_data = $stmt_coach->fetch(PDO::FETCH_ASSOC);
$coach_barangay = $coach_data['barangay'] ?? null;

// Fetch coach's existing teams with tournament info
$stmt = $pdo->prepare("
    SELECT 
        t.*,
        tr.tournament_id,
        tr.status as registration_status
    FROM teams t
    LEFT JOIN tournament_registrations tr ON t.id = tr.team_id
    WHERE t.coach_username = ? 
    ORDER BY t.id DESC
");
$stmt->execute([$coach_username]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get tournament details for each team
foreach ($teams as &$team) {
    if ($team['tournament_id']) {
        $stmt_tournament = $pdo->prepare("SELECT * FROM tournaments WHERE id = ?");
        $stmt_tournament->execute([$team['tournament_id']]);
        $tournament = $stmt_tournament->fetch(PDO::FETCH_ASSOC);
        if ($tournament) {
            $team['tournament_name'] = $tournament['name'] ?? 'Tournament #' . $tournament['id'];
            $team['start_date'] = $tournament['start_date'] ?? null;
            $team['registration_deadline'] = $tournament['registration_deadline'] ?? null;
            $team['tournament_location'] = $tournament['location'] ?? 'N/A';
        }
    }
}
unset($team); // break reference

// Get the most recent team
$current_team = !empty($teams) ? $teams[0] : null;

// Fetch available tournaments that are in registration phase
$current_date = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT * FROM tournaments 
    WHERE status = 'registration_open' 
    AND registration_deadline >= ? 
    AND start_date > ?
    ORDER BY start_date ASC
");
$stmt->execute([$current_date, $current_date]);
$available_tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filter tournaments to only show those where coach's barangay is available
$filtered_tournaments = [];
foreach ($available_tournaments as $tournament) {
    // Check if coach's barangay is already registered for this tournament
    $stmt_check = $pdo->prepare("
        SELECT COUNT(*) 
        FROM teams t 
        JOIN tournament_registrations tr ON t.id = tr.team_id 
        WHERE t.barangay = ? 
        AND tr.tournament_id = ? 
        AND t.approval_status = 'approved' 
        AND tr.status = 'approved'
    ");
    $stmt_check->execute([$coach_barangay, $tournament['id']]);
    $is_registered = $stmt_check->fetchColumn() > 0;
    
    if (!$is_registered) {
        $filtered_tournaments[] = $tournament;
    }
}

// Get used jersey numbers for current team
$used_jersey_numbers = [];
if ($current_team && $current_team['approval_status'] === 'approved') {
    $stmt_jerseys = $pdo->prepare("SELECT jersey_number FROM player_teams WHERE team_id = ? AND jersey_number IS NOT NULL");
    $stmt_jerseys->execute([$current_team['id']]);
    $used_jersey_numbers = $stmt_jerseys->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!-- Team Management Section -->
<section id="team-management" class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center border-b pb-2">
            <i class="fas fa-users text-green-600 mr-3"></i>
            <?= $current_team ? "Your Team - " . htmlspecialchars($current_team['barangay']) : "Create Your Team" ?>
        </h2>

        <!-- Success Messages -->
        <?php if (isset($_SESSION['team_success'])): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                ✅ <?= htmlspecialchars($_SESSION['team_success']); unset($_SESSION['team_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                ✅ <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?>
            </div>
        <?php endif; ?>

        <!-- No Available Tournaments Notice -->
        <?php if (empty($filtered_tournaments) && (!$current_team || $current_team['approval_status'] === 'rejected')): ?>
            <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <div>
                        <strong>No Tournaments Available</strong>
                        <p class="text-sm mt-1">
                            <?php if ($coach_barangay): ?>
                                There are currently no tournaments where your barangay (<strong><?= htmlspecialchars($coach_barangay) ?></strong>) can register a new team. This could be because:
                                <ul class="list-disc ml-5 mt-2 text-sm">
                                    <li>All tournaments are full for your barangay</li>
                                    <li>Your barangay already has an approved team in ongoing tournaments</li>
                                    <li>No tournaments are currently open for registration</li>
                                </ul>
                            <?php else: ?>
                                There are currently no tournaments open for registration. Please check back later or contact the tournament administrator.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <!-- Rejected Team Notice -->
            <?php if ($current_team && $current_team['approval_status'] === 'rejected'): ?>
                <article class="bg-red-50 border border-red-200 rounded-xl shadow-sm p-5">
                    <header class="mb-3">
                        <h3 class="font-semibold text-red-900 text-lg flex items-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Team Request Rejected
                        </h3>
                        <p class="text-sm text-red-700">
                            Your previous team request for <?= htmlspecialchars($current_team['barangay']) ?> was not approved. You can create a new team below.
                        </p>
                    </header>
                    
                    <?php if (!empty($current_team['rejection_reason'])): ?>
                        <div class="mt-3 p-3 bg-white rounded-lg border border-red-100">
                            <p class="text-sm text-gray-700">
                                <strong class="text-red-800">Reason:</strong> 
                                <?= htmlspecialchars($current_team['rejection_reason']); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <!-- Create Team Form -->
            <?php if ((!$current_team || $current_team['approval_status'] === 'rejected') && !empty($filtered_tournaments)): ?>
                <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                    <header class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                            Register Team for Tournament
                        </h3>
                        <p class="text-sm text-gray-600">
                            Register your barangay (<strong><?= htmlspecialchars($coach_barangay) ?></strong>) for an available tournament
                        </p>
                    </header>

                    <form action="create_team_action.php" method="POST" id="teamRegistrationForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tournament Selection -->
                            <div class="input-group">
                                <label class="block text-gray-700 text-sm font-medium mb-2">
                                    <i class="fas fa-trophy text-gray-500 mr-1"></i>
                                    Select Tournament
                                    <span class="text-red-500">*</span>
                                </label>
                                <select name="tournament_id" required id="tournament_select"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition">
                                    <option value="">Choose a tournament</option>
                                    <?php foreach ($filtered_tournaments as $tournament): 
                                        $registration_deadline = date('M j, Y', strtotime($tournament['registration_deadline']));
                                        $start_date = date('M j, Y', strtotime($tournament['start_date']));
                                        $tournament_name = $tournament['name'] ?? 'Tournament #' . $tournament['id'];
                                    ?>
                                        <option value="<?= $tournament['id'] ?>" 
                                                data-deadline="<?= $tournament['registration_deadline'] ?>"
                                                data-start-date="<?= $tournament['start_date'] ?>"
                                                data-max-teams="<?= $tournament['max_teams'] ?>"
                                                data-location="<?= htmlspecialchars($tournament['location']) ?>">
                                            <?= htmlspecialchars($tournament_name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Tournaments available for <?= htmlspecialchars($coach_barangay) ?></p>
                            </div>

                            <!-- Barangay Display (Read-only) -->
                            <div class="input-group">
                                <label class="block text-gray-700 text-sm font-medium mb-2">
                                    <i class="fas fa-map-marker-alt text-gray-500 mr-1"></i>
                                    Your Barangay (Team Name)
                                </label>
                                <div class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                                    <strong class="text-blue-700"><?= htmlspecialchars($coach_barangay) ?></strong>
                                </div>
                                <input type="hidden" name="barangay" value="<?= htmlspecialchars($coach_barangay) ?>">
                                <p class="text-xs text-gray-500 mt-1">Your assigned barangay (cannot be changed)</p>
                            </div>
                        </div>

                        <!-- Tournament Information Card -->
                        <div id="tournament_info" class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg hidden">
                            <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <i class="fas fa-info-circle mr-2"></i>
                                Tournament Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-blue-700">Registration Deadline:</span>
                                    <div id="info_deadline" class="text-blue-600 font-semibold"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-700">Start Date:</span>
                                    <div id="info_start_date" class="text-blue-600 font-semibold"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-700">Location:</span>
                                    <div id="info_location" class="text-blue-600 font-semibold"></div>
                                </div>
                                <div>
                                    <span class="font-medium text-blue-700">Team Capacity:</span>
                                    <div id="info_capacity" class="text-blue-600 font-semibold"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" id="submit_btn"
                            class="mt-6 w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-medium transition duration-200 flex items-center justify-center group disabled:bg-gray-400 disabled:cursor-not-allowed"
                            disabled>
                            <i class="fas fa-save mr-2 group-hover:scale-110 transition"></i>
                            Register <?= htmlspecialchars($coach_barangay) ?> Team
                        </button>
                    </form>
                </article>

            <!-- Pending Approval -->
            <?php elseif ($current_team && $current_team['approval_status'] === 'pending'): ?>
                <!-- ... (pending approval section remains the same) ... -->
            <?php elseif ($current_team && $current_team['approval_status'] === 'approved'): ?>
                <!-- ... (approved team section remains the same) ... -->
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tournamentSelect = document.getElementById('tournament_select');
    const tournamentInfo = document.getElementById('tournament_info');
    const submitBtn = document.getElementById('submit_btn');
    const form = document.getElementById('teamRegistrationForm');

    tournamentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const tournamentId = this.value;
        
        if (tournamentId) {
            // Show tournament info
            const deadline = selectedOption.getAttribute('data-deadline');
            const startDate = selectedOption.getAttribute('data-start-date');
            const maxTeams = selectedOption.getAttribute('data-max-teams');
            const location = selectedOption.getAttribute('data-location');
            
            document.getElementById('info_deadline').textContent = new Date(deadline).toLocaleDateString();
            document.getElementById('info_start_date').textContent = new Date(startDate).toLocaleDateString();
            document.getElementById('info_location').textContent = location;
            document.getElementById('info_capacity').textContent = `${maxTeams} teams maximum`;
            tournamentInfo.classList.remove('hidden');
            
            // Enable submit button
            submitBtn.disabled = false;
        } else {
            tournamentInfo.classList.add('hidden');
            submitBtn.disabled = true;
        }
    });

    // Form validation
    form.addEventListener('submit', function(e) {
        if (!tournamentSelect.value) {
            e.preventDefault();
            alert('Please select a tournament.');
            return;
        }

        // Show confirmation
        const tournamentName = tournamentSelect.options[tournamentSelect.selectedIndex].text;
        const barangayName = '<?= addslashes($coach_barangay) ?>';
        if (!confirm(`Register ${barangayName} team for ${tournamentName}?`)) {
            e.preventDefault();
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>