<?php
// tournament.php (updated with new status system)
include 'includes/auth.php';
include 'includes/db.php';

// Clear success state if requested
if (isset($_GET['clear_success'])) {
    unset($_SESSION['success_tournament_id']);
    unset($_SESSION['success_message']);
    header("Location: tournament.php");
    exit();
}

// Handle tournament actions (delete, activate, etc.)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $tournament_id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        // Get current tournament status first
        $stmt = $pdo->prepare("SELECT status FROM tournaments WHERE id = ? AND created_by = ?");
        $stmt->execute([$tournament_id, $_SESSION['user_id']]);
        $tournament = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tournament) {
            $_SESSION['error'] = "Tournament not found or access denied";
            header("Location: tournament.php");
            exit();
        }
        
        $current_status = $tournament['status'];
        
        switch ($action) {
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM tournaments WHERE id = ?");
                $stmt->execute([$tournament_id]);
                $_SESSION['message'] = "Tournament deleted successfully";
                break;
                
            case 'close_registration':
                if ($current_status === 'registration_open') {
                    $stmt = $pdo->prepare("UPDATE tournaments SET status = 'registration_closed' WHERE id = ?");
                    $stmt->execute([$tournament_id]);
                    $_SESSION['message'] = "Registration closed successfully";
                } else {
                    $_SESSION['error'] = "Cannot close registration for tournament in current status";
                }
                break;
                
            case 'activate':
                if ($current_status === 'setup_complete') {
                    $stmt = $pdo->prepare("UPDATE tournaments SET status = 'active' WHERE id = ?");
                    $stmt->execute([$tournament_id]);
                    $_SESSION['message'] = "Tournament activated successfully";
                } else {
                    $_SESSION['error'] = "Tournament must be in setup_complete status to activate";
                }
                break;
                
            case 'complete':
                if ($current_status === 'active') {
                    $stmt = $pdo->prepare("UPDATE tournaments SET status = 'completed' WHERE id = ?");
                    $stmt->execute([$tournament_id]);
                    $_SESSION['message'] = "Tournament marked as completed";
                } else {
                    $_SESSION['error'] = "Only active tournaments can be completed";
                }
                break;
                
            case 'cancel':
                $stmt = $pdo->prepare("UPDATE tournaments SET status = 'cancelled' WHERE id = ?");
                $stmt->execute([$tournament_id]);
                $_SESSION['message'] = "Tournament cancelled";
                break;
                
            case 'setup':
                // Only allow setup if registration is closed or setup not complete
                if ($current_status === 'registration_closed' || $current_status === 'registration_open') {
                    // Get tournament data
                    $stmt = $pdo->prepare("SELECT id, name, start_date, location FROM tournaments WHERE id = ? AND created_by = ?");
                    $stmt->execute([$tournament_id, $_SESSION['user_id']]);
                    $tournament_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($tournament_data) {
                        $_SESSION['tournament_data'] = [
                            'id' => $tournament_id,
                            'name' => $tournament_data['name'],
                            'start_date' => $tournament_data['start_date'],
                            'location' => $tournament_data['location']
                        ];
                        header("Location: team_setup.php");
                        exit();
                    }
                } else {
                    $_SESSION['error'] = "Cannot setup tournament in current status";
                }
                break;
        }
        
        header("Location: tournament.php");
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Error performing action: " . $e->getMessage();
        header("Location: tournament.php");
        exit();
    }
}

// Check for success from redirect
$show_success_modal = false;
$success_tournament_id = null;
$success_message = null;

if (isset($_GET['success']) && $_GET['success'] == '1') {
    if (isset($_SESSION['success_tournament_id'])) {
        $show_success_modal = true;
        $success_tournament_id = $_SESSION['success_tournament_id'];
        $success_message = $_SESSION['success_message'] ?? "Tournament created successfully!";
        unset($_SESSION['success_tournament_id']);
        unset($_SESSION['success_message']);
    }
}

// Fetch existing tournaments with team counts
$existing_tournaments = [];
$has_existing_tournament = false;

try {
    // First get all tournaments
    $tournaments_stmt = $pdo->prepare("
        SELECT * FROM tournaments 
        WHERE created_by = ?
        ORDER BY 
            CASE status 
                WHEN 'registration_open' THEN 1
                WHEN 'registration_closed' THEN 2
                WHEN 'setup_complete' THEN 3
                WHEN 'active' THEN 4
                WHEN 'completed' THEN 5
                WHEN 'cancelled' THEN 6
                ELSE 7
            END,
            start_date DESC
    ");
    $tournaments_stmt->execute([$_SESSION['user_id']]);
    $existing_tournaments = $tournaments_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if user already has a tournament
    $has_existing_tournament = count($existing_tournaments) > 0;
    
    // Then get team counts for each tournament
    foreach ($existing_tournaments as &$tournament) {
        $team_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as registered_teams,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_teams
            FROM teams 
            WHERE tournament_id = ?
        ");
        $team_stmt->execute([$tournament['id']]);
        $team_counts = $team_stmt->fetch(PDO::FETCH_ASSOC);
        
        $tournament['registered_teams'] = $team_counts['registered_teams'] ?? 0;
        $tournament['approved_teams'] = $team_counts['approved_teams'] ?? 0;
        
        // Get match counts
        $match_stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_matches,
                COUNT(CASE WHEN winner_id IS NOT NULL THEN 1 END) as completed_matches
            FROM matches 
            WHERE tournament_id = ?
        ");
        $match_stmt->execute([$tournament['id']]);
        $match_counts = $match_stmt->fetch(PDO::FETCH_ASSOC);
        
        $tournament['total_matches'] = $match_counts['total_matches'] ?? 0;
        $tournament['completed_matches'] = $match_counts['completed_matches'] ?? 0;
        
        // Check if tournament is ready for setup (has approved teams)
        $tournament['ready_for_setup'] = ($tournament['approved_teams'] > 0);
        
        // Determine available actions based on status
        $tournament['can_close_registration'] = ($tournament['status'] === 'registration_open');
        $tournament['can_setup'] = ($tournament['status'] === 'registration_open' || $tournament['status'] === 'registration_closed') && $tournament['ready_for_setup'];
        $tournament['can_activate'] = ($tournament['status'] === 'setup_complete');
        $tournament['can_complete'] = ($tournament['status'] === 'active');
    }
    unset($tournament); // unset reference
    
} catch (Exception $e) {
    error_log("Error fetching tournaments: " . $e->getMessage());
    $error = "Error loading tournaments: " . $e->getMessage();
}

// Check for session messages
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <!-- Top Bar -->
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Tournament Management</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <!-- Success Message -->
        <?php if (isset($message)): ?>
            <div class="max-w-7xl mx-auto mb-6">
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded flex justify-between items-center">
                    <span><?php echo htmlspecialchars($message); ?></span>
                    <button onclick="this.parentElement.style.display='none'" class="text-green-700 hover:text-green-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="max-w-7xl mx-auto mb-6">
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded flex justify-between items-center">
                    <span><strong>Error:</strong> <?php echo htmlspecialchars($error); ?></span>
                    <button onclick="this.parentElement.style.display='none'" class="text-red-700 hover:text-red-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Existing Tournaments Section -->
        <div class="max-w-7xl mx-auto">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-trophy text-yellow-500"></i>
                        Your Tournament
                    </h2>
                    <?php if (!$has_existing_tournament): ?>
                        <a 
                            href="tournament_details.php"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-200 inline-flex items-center gap-2"
                        >
                            <i class="fas fa-plus"></i>
                            Create Tournament
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (empty($existing_tournaments)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-trophy text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">No Tournament Yet</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Create your tournament to start managing competitions and brackets</p>
                        <a 
                            href="tournament_details.php"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 inline-flex items-center gap-2"
                        >
                            <i class="fas fa-plus"></i>
                            Create Tournament
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($existing_tournaments as $tournament): ?>
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-200 bg-white">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800 truncate" title="<?php echo htmlspecialchars($tournament['name']); ?>">
                                        <?php echo htmlspecialchars($tournament['name']); ?>
                                    </h3>
                                    <span class="status-badge status-<?php echo str_replace('_', '-', $tournament['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $tournament['status'])); ?>
                                    </span>
                                </div>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-calendar text-gray-400 w-5"></i>
                                        <span>Starts: <?php echo date('M j, Y', strtotime($tournament['start_date'])); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                                        <span class="truncate" title="<?php echo htmlspecialchars($tournament['location']); ?>">
                                            <?php echo htmlspecialchars($tournament['location']); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-users text-gray-400 w-5"></i>
                                        <span>
                                            <?php echo $tournament['approved_teams']; ?> approved / 
                                            <?php echo $tournament['registered_teams']; ?> total barangays
                                        </span>
                                    </div>
                                    
                                    <?php if (in_array($tournament['status'], ['registration_open', 'registration_closed'])): ?>
                                        <!-- Registration Phase Info -->
                                        <div class="bg-blue-50 border border-blue-200 rounded p-3 mt-2">
                                            <div class="flex items-center text-sm text-blue-700">
                                                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                                                <span>
                                                    <?php if ($tournament['approved_teams'] == 0): ?>
                                                        <strong>No approved teams yet.</strong> Approve teams in Team Management to proceed with setup.
                                                    <?php elseif ($tournament['status'] === 'registration_open'): ?>
                                                        <strong><?php echo $tournament['approved_teams']; ?> teams approved.</strong> Close registration to proceed with setup.
                                                    <?php else: ?>
                                                        <strong><?php echo $tournament['approved_teams']; ?> teams approved.</strong> Ready for tournament setup.
                                                    <?php endif; ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php elseif ($tournament['status'] === 'setup_complete'): ?>
                                        <div class="bg-green-50 border border-green-200 rounded p-3 mt-2">
                                            <div class="flex items-center text-sm text-green-700">
                                                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                                <span><strong>Setup complete.</strong> Ready to activate tournament.</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i class="fas fa-sitemap text-gray-400 w-5"></i>
                                            <span><?php echo ucfirst(str_replace('_', ' ', $tournament['format'] ?? 'Not set')); ?></span>
                                        </div>
                                        <?php if ($tournament['total_matches'] > 0): ?>
                                            <div class="flex items-center text-sm text-gray-600">
                                                <i class="fas fa-gamepad text-gray-400 w-5"></i>
                                                <span>
                                                    <?php echo $tournament['completed_matches']; ?> / <?php echo $tournament['total_matches']; ?> 
                                                    matches completed
                                                </span>
                                            </div>
                                            <?php if ($tournament['total_matches'] > 0): ?>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 h-2 rounded-full" 
                                                         style="width: <?php echo ($tournament['completed_matches'] / $tournament['total_matches']) * 100; ?>%">
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                    <div class="flex flex-wrap gap-2">
                                        <?php if (in_array($tournament['status'], ['registration_open', 'registration_closed'])): ?>
                                            <!-- Close Registration Button -->
                                            <?php if ($tournament['can_close_registration']): ?>
                                                <a 
                                                    href="?action=close_registration&id=<?php echo $tournament['id']; ?>"
                                                    class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                    title="Close registration for this tournament"
                                                >
                                                    <i class="fas fa-lock"></i>
                                                    Close Registration
                                                </a>
                                            <?php endif; ?>
                                            
                                            <!-- Setup Tournament Button -->
                                            <?php if ($tournament['can_setup']): ?>
                                                <a 
                                                    href="?action=setup&id=<?php echo $tournament['id']; ?>"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                    title="Setup tournament brackets and start competition"
                                                >
                                                    <i class="fas fa-cogs"></i>
                                                    Setup Tournament
                                                </a>
                                            <?php else: ?>
                                                <button 
                                                    disabled
                                                    class="bg-gray-400 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 cursor-not-allowed"
                                                    title="Approve teams first to setup tournament"
                                                >
                                                    <i class="fas fa-cogs"></i>
                                                    Setup Tournament
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Team Management Link -->
                                            <a 
                                                href="team_management.php?tournament_id=<?php echo $tournament['id']; ?>"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="Manage team registrations"
                                            >
                                                <i class="fas fa-user-friends"></i>
                                                Manage Teams
                                            </a>
                                            
                                        <?php elseif ($tournament['status'] === 'setup_complete'): ?>
                                            <!-- Activate Tournament Button -->
                                            <?php if ($tournament['can_activate']): ?>
                                                <a 
                                                    href="?action=activate&id=<?php echo $tournament['id']; ?>"
                                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                    title="Activate and start the tournament"
                                                >
                                                    <i class="fas fa-play"></i>
                                                    Activate Tournament
                                                </a>
                                            <?php endif; ?>
                                            
                                        <?php elseif ($tournament['status'] === 'active'): ?>
                                            <!-- Active Tournament Actions -->
                                            <a 
                                                href="simulate_bracket.php?id=<?php echo $tournament['id']; ?>"
                                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="Simulate tournament matches"
                                            >
                                                <i class="fas fa-play-circle"></i>
                                                Simulate
                                            </a>
                                            
                                            <?php if ($tournament['can_complete']): ?>
                                                <a 
                                                    href="?action=complete&id=<?php echo $tournament['id']; ?>"
                                                    class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                    title="Mark tournament as completed"
                                                >
                                                    <i class="fas fa-flag-checkered"></i>
                                                    Complete
                                                </a>
                                            <?php endif; ?>
                                            
                                        <?php elseif ($tournament['status'] === 'completed'): ?>
                                            <!-- Completed Tournament Actions -->
                                            <a 
                                                href="tournament_bracket.php?id=<?php echo $tournament['id']; ?>"
                                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="View tournament bracket"
                                            >
                                                <i class="fas fa-sitemap"></i>
                                                View Bracket
                                            </a>
                                            
                                            <a 
                                                href="tournament_results.php?id=<?php echo $tournament['id']; ?>"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="View tournament results"
                                            >
                                                <i class="fas fa-chart-bar"></i>
                                                Results
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- View Bracket (available for active/completed tournaments) -->
                                        <?php if (in_array($tournament['status'], ['active', 'completed', 'setup_complete'])): ?>
                                            <a 
                                                href="tournament_bracket.php?id=<?php echo $tournament['id']; ?>"
                                                class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="View tournament bracket"
                                            >
                                                <i class="fas fa-sitemap"></i>
                                                View Bracket
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <?php if ($tournament['status'] === 'registration_open'): ?>
                                            <!-- Registration Portal Link -->
                                            <a 
                                                href="registration_portal.php?tournament_id=<?php echo $tournament['id']; ?>"
                                                class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                title="View registration portal"
                                            >
                                                <i class="fas fa-door-open"></i>
                                                Registration
                                            </a>
                                        <?php endif; ?>
                                        
                                        <!-- Cancel Tournament Button -->
                                        <?php if (in_array($tournament['status'], ['registration_open', 'registration_closed', 'setup_complete', 'active'])): ?>
                                            <a 
                                                href="?action=cancel&id=<?php echo $tournament['id']; ?>"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                                onclick="return confirm('Are you sure you want to cancel this tournament? This action cannot be undone.')"
                                                title="Cancel tournament"
                                            >
                                                <i class="fas fa-times"></i>
                                                Cancel
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a 
                                            href="?action=delete&id=<?php echo $tournament['id']; ?>"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-sm font-medium flex items-center gap-1 transition duration-200"
                                            onclick="return confirm('Are you sure you want to delete this tournament? This action cannot be undone and will remove all associated data.')"
                                            title="Delete tournament"
                                        >
                                            <i class="fas fa-trash"></i>
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center <?php echo $show_success_modal ? '' : 'hidden'; ?> z-50 transition-opacity duration-300">
    <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4 transform transition-transform duration-300 scale-100">
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Success!</h3>
            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($success_message); ?></p>
            <?php if ($success_tournament_id): ?>
                <p class="text-sm text-gray-500 mb-4">Tournament ID: <span class="font-mono"><?php echo $success_tournament_id; ?></span></p>
            <?php endif; ?>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <?php if ($success_tournament_id): ?>
                    <a 
                        href="team_management.php?tournament_id=<?php echo $success_tournament_id; ?>"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center gap-2"
                    >
                        <i class="fas fa-user-friends"></i>
                        Manage Teams
                    </a>
                <?php endif; ?>
                <button 
                    onclick="hideSuccessModal()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center gap-2"
                >
                    <i class="fas fa-times"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.status-registration-open { 
    background-color: #fef3c7; 
    color: #92400e; 
    border: 1px solid #f59e0b;
}
.status-registration-closed { 
    background-color: #f3f4f6; 
    color: #374151; 
    border: 1px solid #9ca3af;
}
.status-setup-complete { 
    background-color: #d1fae5; 
    color: #065f46; 
    border: 1px solid #10b981;
}
.status-active { 
    background-color: #dbeafe; 
    color: #1e40af; 
    border: 1px solid #3b82f6;
}
.status-completed { 
    background-color: #ede9fe; 
    color: #5b21b6; 
    border: 1px solid #8b5cf6;
}
.status-cancelled { 
    background-color: #fecaca; 
    color: #991b1b; 
    border: 1px solid #ef4444;
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}
</style>

<script>
function hideSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.add('opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// Close success modal when clicking outside or pressing Escape
document.addEventListener('DOMContentLoaded', function() {
    const successModal = document.getElementById('successModal');
    
    if (successModal) {
        successModal.addEventListener('click', function(e) {
            if (e.target === this) {
                hideSuccessModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !successModal.classList.contains('hidden')) {
                hideSuccessModal();
            }
        });
        
        // Auto-hide success modal after 8 seconds
        if (!successModal.classList.contains('hidden')) {
            setTimeout(() => {
                hideSuccessModal();
            }, 8000);
        }
    }
    
    // Add hover effects to tournament cards
    const tournamentCards = document.querySelectorAll('.bg-white.rounded-lg');
    tournamentCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>