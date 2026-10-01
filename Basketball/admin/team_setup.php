<?php
// team_setup.php
include 'includes/auth.php';
include 'includes/db.php';

// Check if coming from previous step or from setup action
if (empty($_SESSION['tournament_data'])) {
    header("Location: tournament_details.php");
    exit();
}

$tournament_id = $_SESSION['tournament_data']['id'] ?? null;

// Fetch approved teams from database
$existing_teams = [];
try {
    $teams_stmt = $pdo->prepare("
        SELECT id, barangay, coach_username, status, approval_status 
        FROM teams 
        WHERE approval_status = 'approved' 
        AND status = 'Active'
        ORDER BY barangay
    ");
    $teams_stmt->execute();
    $existing_teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error fetching teams: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use selected existing teams
    if (isset($_POST['existing_teams']) && is_array($_POST['existing_teams'])) {
        $selected_teams = [];
        foreach ($_POST['existing_teams'] as $team_id) {
            $team = array_filter($existing_teams, function($t) use ($team_id) {
                return $t['id'] == $team_id;
            });
            if (!empty($team)) {
                $team = reset($team);
                $selected_teams[] = [
                    'id' => $team['id'],
                    'barangay' => $team['barangay'],
                    'coach_username' => $team['coach_username'],
                    'approval_status' => $team['approval_status']
                ];
            }
        }
        
        if (count($selected_teams) < 2) {
            $_SESSION['error'] = "Please select at least 2 teams for the tournament.";
        } else {
            $_SESSION['tournament_data']['teams'] = $selected_teams;
            $_SESSION['tournament_data']['team_count'] = count($selected_teams);
            $_SESSION['tournament_data']['selected_team_ids'] = $_POST['existing_teams'];
            
            header("Location: bracket_setup.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Please select at least 2 teams for the tournament.";
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">
                <?php echo $tournament_id ? 'Setup Tournament' : 'Create Tournament'; ?> - Step 2: Select Teams
            </h1>
            <div class="flex items-center space-x-4">
                <a href="tournament.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Back to Tournaments
                </a>
                <span class="text-sm text-gray-500">Welcome, <?php echo $_SESSION['username'] ?? 'Admin'; ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Progress Steps -->
        <div class="max-w-4xl mx-auto mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="progress-step <?php echo $tournament_id ? 'completed' : 'completed'; ?>">
                        <div class="step-number">1</div>
                        <div class="step-label">Tournament Details</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step active">
                        <div class="step-number">2</div>
                        <div class="step-label">Select Teams</div>
                    </div>
                    <div class="progress-connector"></div>
                    <div class="progress-step">
                        <div class="step-number">3</div>
                        <div class="step-label">Bracket Setup</div>
                    </div>
                    <div class="progress-connector"></div>
                    <div class="progress-step">
                        <div class="step-number">4</div>
                        <div class="step-label">Review & Save</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-6xl mx-auto">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-red-800">Error</h4>
                            <p class="text-sm text-red-700 mt-1"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-users text-green-500"></i> 
                    <?php echo $tournament_id ? 'Select Teams for Tournament' : 'Select Teams from Database'; ?>
                </h2>
                
                <!-- Tournament Info Display -->
                <?php if ($tournament_id && isset($_SESSION['tournament_data']['name'])): ?>
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800">Setting up: <?php echo htmlspecialchars($_SESSION['tournament_data']['name']); ?></h4>
                                <p class="text-sm text-blue-700 mt-1">
                                    Select teams to participate in this tournament. You can always add more teams later.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6" id="teamForm">
                    <?php if (!empty($existing_teams)): ?>
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    Select Teams for Tournament *
                                </label>
                                <div class="text-sm text-gray-500">
                                    <?php echo count($existing_teams); ?> approved teams available
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fas fa-info-circle"></i>
                                Select at least 2 approved teams to <?php echo $tournament_id ? 'setup' : 'create'; ?> a tournament. Only approved and active teams are shown.
                            </p>

                            <!-- Select All Controls -->
                            <div class="flex justify-between items-center mb-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg select-all-controls">
                                <div class="flex items-center space-x-3">
                                    <button type="button" 
                                            onclick="selectAllTeams()" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                                        <i class="fas fa-check-double"></i>
                                        Select All Teams
                                    </button>
                                    <button type="button" 
                                            onclick="deselectAllTeams()" 
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition duration-200 flex items-center gap-2 shadow-sm hover:shadow-md">
                                        <i class="fas fa-times"></i>
                                        Deselect All
                                    </button>
                                </div>
                                <div class="text-sm font-medium bg-white px-3 py-2 rounded-lg border border-blue-200 shadow-sm">
                                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                                    <span id="selectionSummary">0 selected</span>
                                </div>
                            </div>
                            
                            <div class="max-h-96 overflow-y-auto border border-gray-300 rounded-lg p-4 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <?php foreach ($existing_teams as $team): ?>
                                        <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-white hover:border-blue-300 cursor-pointer transition duration-200 bg-white team-checkbox-label">
                                            <input 
                                                type="checkbox" 
                                                name="existing_teams[]" 
                                                value="<?php echo $team['id']; ?>" 
                                                class="mr-3 rounded border-gray-300 text-blue-600 focus:ring-blue-500 team-checkbox mt-1"
                                                onchange="updateSelectedCount()"
                                                <?php 
                                                    if (isset($_SESSION['tournament_data']['selected_team_ids']) && 
                                                        in_array($team['id'], $_SESSION['tournament_data']['selected_team_ids'])) {
                                                        echo 'checked';
                                                    }
                                                ?>
                                            >
                                            <div class="flex-1">
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($team['barangay']); ?></div>
                                                <div class="text-xs text-gray-500 mt-2 space-y-1">
                                                    <?php if (!empty($team['coach_username'])): ?>
                                                        <div><strong>Coach:</strong> <?php echo htmlspecialchars($team['coach_username']); ?></div>
                                                    <?php endif; ?>
                                                    <div class="flex items-center gap-1">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                        <span class="text-green-600 font-medium">Approved & Active</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Teams Counter -->
                        <div id="selectedCountDisplay" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle text-blue-600 mr-3"></i>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-800">Teams Selected</h4>
                                        <p class="text-sm text-blue-700 mt-1">
                                            <span id="selectedCount">0</span> team(s) selected for tournament
                                        </p>
                                    </div>
                                </div>
                                <div id="minTeamsWarning" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                        <span class="text-sm text-yellow-700">Minimum 2 teams required</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team Preview -->
                        <div id="teamPreviewSection" class="hidden">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="text-lg font-semibold text-gray-800">Selected Teams Preview</h3>
                                <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
                                    <i class="fas fa-list-ol mr-1"></i>
                                    <span id="previewCount">0</span> teams
                                </span>
                            </div>
                            <div id="teamPreview" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-60 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-white">
                                <!-- Teams will be previewed here -->
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                            <i class="fas fa-users text-gray-400 text-5xl mb-4"></i>
                            <h4 class="text-xl font-medium text-gray-900 mb-2">No Approved Teams Available</h4>
                            <p class="text-gray-500 mb-4">There are no approved teams in the database yet.</p>
                            <div class="space-y-3">
                                <a href="teams.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200">
                                    <i class="fas fa-cog"></i>
                                    Manage Teams
                                </a>
                                <p class="text-sm text-gray-500">Go to teams management to create or approve teams</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-between pt-4">
                        <?php if ($tournament_id): ?>
                            <a href="tournament.php" class="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i>
                                Back to Tournaments
                            </a>
                        <?php else: ?>
                            <a href="tournament_details.php" class="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                                <i class="fas fa-arrow-left"></i>
                                Back to Details
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($existing_teams)): ?>
                            <button type="submit" id="submitButton" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition duration-200 flex items-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                                Next: Bracket Setup
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
// Store existing teams data for JavaScript
const existingTeamsData = <?php echo json_encode($existing_teams); ?>;

function selectAllTeams() {
    const checkboxes = document.querySelectorAll('.team-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    updateSelectedCount();
    
    // Show success feedback
    showSelectionFeedback('All teams selected successfully!', 'success');
}

function deselectAllTeams() {
    const checkboxes = document.querySelectorAll('.team-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    updateSelectedCount();
    
    // Show info feedback
    showSelectionFeedback('All teams deselected.', 'info');
}

function updateSelectedCount() {
    const selectedTeams = document.querySelectorAll('.team-checkbox:checked');
    const selectedCount = selectedTeams.length;
    const totalTeams = document.querySelectorAll('.team-checkbox').length;
    const selectedCountSpan = document.getElementById('selectedCount');
    const selectionSummary = document.getElementById('selectionSummary');
    const previewCount = document.getElementById('previewCount');
    const submitButton = document.getElementById('submitButton');
    const warningDiv = document.getElementById('minTeamsWarning');
    const previewSection = document.getElementById('teamPreviewSection');
    
    // Update selected count display
    selectedCountSpan.textContent = selectedCount;
    selectionSummary.textContent = `${selectedCount} of ${totalTeams} selected`;
    previewCount.textContent = selectedCount;
    
    // Enable/disable submit button based on minimum teams
    if (selectedCount >= 2) {
        submitButton.disabled = false;
        warningDiv.classList.add('hidden');
    } else {
        submitButton.disabled = true;
        warningDiv.classList.remove('hidden');
    }
    
    // Update selection summary color based on count
    if (selectedCount === totalTeams) {
        selectionSummary.className = 'text-sm text-green-600 font-medium';
    } else if (selectedCount >= 2) {
        selectionSummary.className = 'text-sm text-blue-600 font-medium';
    } else {
        selectionSummary.className = 'text-sm text-gray-600 font-medium';
    }
    
    // Update team preview
    updateTeamPreview(selectedTeams);
}

function updateTeamPreview(selectedTeams) {
    const preview = document.getElementById('teamPreview');
    const previewSection = document.getElementById('teamPreviewSection');
    
    if (selectedTeams.length === 0) {
        previewSection.classList.add('hidden');
        return;
    }
    
    preview.innerHTML = '';
    
    Array.from(selectedTeams).forEach((checkbox, index) => {
        const teamId = parseInt(checkbox.value);
        const team = existingTeamsData.find(t => t.id == teamId);
        
        if (team) {
            const teamElement = document.createElement('div');
            teamElement.className = 'team-preview-item';
            teamElement.innerHTML = `
                <div class="team-preview-number">${index + 1}</div>
                <div class="team-preview-content">
                    <div class="team-preview-name">${team.barangay}</div>
                    <div class="team-preview-details">
                        ${team.coach_username ? `<div class="team-coach text-xs text-gray-500 mt-1">Coach: ${team.coach_username}</div>` : ''}
                    </div>
                </div>
            `;
            preview.appendChild(teamElement);
        }
    });
    
    previewSection.classList.remove('hidden');
}

function showSelectionFeedback(message, type) {
    // Remove any existing feedback
    const existingFeedback = document.getElementById('selectionFeedback');
    if (existingFeedback) {
        existingFeedback.remove();
    }
    
    // Create feedback element
    const feedback = document.createElement('div');
    feedback.id = 'selectionFeedback';
    feedback.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-0 ${
        type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 
        type === 'info' ? 'bg-blue-50 border border-blue-200 text-blue-800' : 
        'bg-gray-50 border border-gray-200 text-gray-800'
    }`;
    
    feedback.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas ${type === 'success' ? 'fa-check-circle text-green-600' : type === 'info' ? 'fa-info-circle text-blue-600' : 'fa-bell text-gray-600'}"></i>
            <span class="font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700 transition-colors duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(feedback);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (feedback.parentElement) {
            feedback.remove();
        }
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedCount();
    
    // Add event listeners to all team checkboxes
    const teamCheckboxes = document.querySelectorAll('.team-checkbox');
    teamCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+A to select all (but prevent default text selection)
        if (e.ctrlKey && e.key === 'a') {
            e.preventDefault();
            selectAllTeams();
        }
        // Escape to deselect all
        if (e.key === 'Escape') {
            e.preventDefault();
            deselectAllTeams();
        }
    });

    // Add click handlers for better UX
    const teamLabels = document.querySelectorAll('.team-checkbox-label');
    teamLabels.forEach(label => {
        label.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                const checkbox = this.querySelector('.team-checkbox');
                checkbox.checked = !checkbox.checked;
                updateSelectedCount();
            }
        });
    });
});
</script>

<style>
.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e5e7eb;
    border: 3px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #6b7280;
    transition: all 0.3s ease;
    margin-bottom: 8px;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.3s ease;
}

.progress-connector {
    height: 3px;
    background-color: #e5e7eb;
    flex: 1;
    min-width: 60px;
    margin: 0 10px;
    position: relative;
    top: -25px;
    z-index: 1;
}

.progress-step.active .step-number {
    background-color: #3b82f6;
    border-color: #3b82f6;
    color: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.progress-step.active .step-label {
    color: #3b82f6;
    font-weight: 600;
}

.progress-step.completed .step-number {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.progress-step.completed .step-label {
    color: #10b981;
    font-weight: 600;
}

.progress-connector.completed {
    background-color: #10b981;
}

/* Select All Controls */
.select-all-controls {
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    border: 1px solid #bae6fd;
}

/* Team checkbox label hover effects */
.team-checkbox-label {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}

.team-checkbox-label:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
}

/* Selection feedback animation */
@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

#selectionFeedback {
    animation: slideInRight 0.3s ease-out;
}

#selectionFeedback.removing {
    animation: slideOutRight 0.3s ease-in;
}

/* Team preview enhancements */
.team-preview-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.team-preview-item:hover {
    border-color: #3b82f6;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.team-preview-number {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.team-preview-content {
    flex: 1;
    min-width: 0;
}

.team-preview-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.team-preview-details {
    display: flex;
    gap: 12px;
    font-size: 0.75rem;
    color: #6b7280;
}

/* Button hover effects */
button {
    transition: all 0.2s ease-in-out;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

button:active {
    transform: translateY(0);
}

/* Custom checkbox styling */
.team-checkbox {
    transform: scale(1.2);
    transition: all 0.2s ease;
}

.team-checkbox:checked {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

/* Hover effects for team selection */
.team-checkbox-label:hover {
    background-color: #f8fafc;
}

/* Scrollbar styling */
.max-h-96::-webkit-scrollbar {
    width: 8px;
}

.max-h-96::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.max-h-96::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Selection summary styling */
#selectionSummary {
    font-weight: 600;
}

/* Preview section styling */
#teamPreviewSection {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .select-all-controls {
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
    
    .select-all-controls > div {
        justify-content: center;
    }
    
    .team-preview-item {
        flex-direction: column;
        text-align: center;
        gap: 8px;
    }
    
    .team-preview-number {
        align-self: center;
    }
}
</style>

<?php include 'includes/footer.php'; ?>