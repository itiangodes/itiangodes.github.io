<?php include 'includes/player_header.php'; ?>
<?php include 'includes/player_nav.php'; ?>

<!-- Team Management Section -->
<section id="team-management" class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center border-b pb-2">
            <i class="fas fa-users text-blue-600 mr-3"></i>
            Team Management
        </h2>

        <!-- Success Messages -->
        <?php if (isset($_SESSION['join_success'])): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                ✅ <?= htmlspecialchars($_SESSION['join_success']); unset($_SESSION['join_success']); ?>
            </div>
        <?php endif; ?>

        <!-- Error Messages -->
        <?php if (isset($_SESSION['join_error'])): ?>
            <div class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
                ❌ <?= htmlspecialchars($_SESSION['join_error']); unset($_SESSION['join_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Jersey Assignment Message -->
        <?php if (isset($_SESSION['jersey_msg'])): ?>
            <div class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
                ✅ <?= htmlspecialchars($_SESSION['jersey_msg']); unset($_SESSION['jersey_msg']); ?>
            </div>
        <?php endif; ?>

        <?php
        include '../includes/db.php';
        $username = $_SESSION['username'] ?? null;

        // Fetch player's barangay
        $playerStmt = $pdo->prepare("SELECT barangay FROM users WHERE username = ?");
        $playerStmt->execute([$username]);
        $player = $playerStmt->fetch(PDO::FETCH_ASSOC);
        $player_barangay = $player['barangay'] ?? null;

        // Fetch the team joined by the player with detailed information
        $teamStmt = $pdo->prepare("
            SELECT 
                t.id, 
                t.barangay,
                u.firstname as coach_firstname,
                u.lastname as coach_lastname,
                pt.id as player_team_id,
                pt.status AS join_status,
                pt.jersey_number,
                pt.position
            FROM teams t
            JOIN player_teams pt ON t.id = pt.team_id 
            JOIN users u ON t.coach_username = u.username
            WHERE pt.player_username = ?
        ");
        $teamStmt->execute([$username]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);
        ?>

        <div class="space-y-6">
            <!-- Current Team Status Card -->
            <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                <header class="mb-4">
                    <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                        <i class="fas fa-user-check text-green-600 mr-2"></i>
                        Current Team Status
                    </h3>
                    <p class="text-sm text-gray-600">
                        Your current team membership information
                    </p>
                </header>

                <div class="space-y-4">
                    <?php if ($team): ?>
                        <!-- Team Information -->
                        <div class="p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <p class="text-gray-700">
                                        <strong>Team:</strong> 
                                        <span class="font-semibold text-blue-700"><?= htmlspecialchars($team['barangay']); ?></span>
                                    </p>
                                    <p class="text-gray-700">
                                        <strong>Coach:</strong> <?= htmlspecialchars($team['coach_firstname'] . ' ' . $team['coach_lastname']); ?>
                                    </p>
                                    
                                    <!-- Jersey Number Section - Only show if approved -->
                                    <?php if ($team['join_status'] === 'approved'): ?>
                                        <div class="mt-4">
                                            <p class="text-gray-700 mb-2">
                                                <strong>Your Jersey Number:</strong>
                                            </p>
                                            <?php if (empty($team['jersey_number'])): ?>
                                                <form action="assign_jersey_player.php" method="POST" class="flex items-center space-x-2">
                                                    <input type="hidden" name="player_team_id" value="<?= $team['player_team_id'] ?>">
                                                    <input type="number" name="jersey_number" min="0" max="99" 
                                                        class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                                                        placeholder="0-99" required>
                                                    <button type="submit"
                                                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center text-sm">
                                                        <i class="fas fa-t-shirt mr-1"></i> Set Jersey
                                                    </button>
                                                </form>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Choose your jersey number (0-99). Once set, it cannot be changed.
                                                </p>
                                            <?php else: ?>
                                                <div class="flex items-center space-x-3">
                                                    <span class="inline-flex items-center justify-center w-12 h-12 bg-blue-100 text-blue-800 font-bold text-lg rounded-full border-2 border-blue-300">
                                                        #<?= $team['jersey_number'] ?>
                                                    </span>
                                                    <span class="text-green-600 font-semibold">
                                                        <i class="fas fa-check-circle mr-1"></i>Assigned
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Position Information - Only show if approved -->
                                        <?php if (!empty($team['position'])): ?>
                                            <div class="mt-3">
                                                <p class="text-gray-700">
                                                    <strong>Your Position:</strong> 
                                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">
                                                        <?= htmlspecialchars($team['position']); ?>
                                                    </span>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $status = strtolower(trim($team['join_status'] ?? ''));
                                    if ($status === 'approved'): ?>
                                        <p class="text-green-600 font-semibold flex items-center">
                                            <i class="fas fa-check-circle mr-2"></i> Active Member (Approved)
                                        </p>
                                        <?php if (empty($team['jersey_number'])): ?>
                                            <p class="text-blue-600 text-sm flex items-center">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                You can now choose your jersey number above.
                                            </p>
                                        <?php endif; ?>
                                    <?php elseif ($status === 'pending'): ?>
                                        <p class="text-yellow-600 font-semibold flex items-center">
                                            <i class="fas fa-clock mr-2"></i> Waiting for Team Approval
                                        </p>
                                        <p class="text-gray-600 text-sm">
                                            Once the coach approves your request, you'll be able to choose your jersey number.
                                        </p>
                                    <?php elseif ($status === 'rejected'): ?>
                                        <p class="text-red-600 font-semibold flex items-center">
                                            <i class="fas fa-times-circle mr-2"></i> Rejected by Coach
                                        </p>
                                        <p class="text-gray-600 text-sm">
                                            Your request to join the team was not approved by the coach.
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex flex-col items-center justify-center">
                                    <?php if ($team['join_status'] === 'approved'): ?>
                                        <div class="text-6xl text-green-500 mb-4">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    <?php elseif ($team['join_status'] === 'pending'): ?>
                                        <div class="text-6xl text-yellow-500 mb-4">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                    <?php elseif ($team['join_status'] === 'rejected'): ?>
                                        <div class="text-6xl text-red-500 mb-4">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Leave Team Button -->
                                    <form action="leave_team.php" method="POST" class="w-full">
                                        <input type="hidden" name="team_id" value="<?= htmlspecialchars($team['id']); ?>">
                                        <button type="submit" 
                                                class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg font-medium transition duration-200 flex items-center justify-center group"
                                                onclick="return confirm('Are you sure you want to leave your team?');">
                                            <i class="fas fa-sign-out-alt mr-2 group-hover:scale-110 transition"></i>
                                            Leave Team
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- No Team State -->
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4 text-gray-300">
                                <i class="fas fa-users"></i>
                            </div>
                            <p class="text-gray-500 text-lg">You're not currently in any team.</p>
                            <p class="text-gray-400 text-sm mt-2">Join your barangay team below to get started with competitions.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </article>

            <!-- Teammates Section - Only show if approved -->
            <?php if ($team && $team['join_status'] === 'approved'): ?>
                <?php
                // Fetch teammates information (including coach)
                $teammatesStmt = $pdo->prepare("
                    SELECT 
                        u.firstname,
                        u.lastname,
                        u.username,
                        pt.jersey_number,
                        pt.position,
                        pt.status,
                        CASE 
                            WHEN u.username = t.coach_username THEN 'coach'
                            ELSE 'player'
                        END as role
                    FROM player_teams pt
                    JOIN users u ON pt.player_username = u.username
                    JOIN teams t ON pt.team_id = t.id
                    WHERE pt.team_id = ? AND pt.status = 'approved'
                    ORDER BY 
                        role DESC, -- Coach first
                        pt.jersey_number IS NULL, 
                        pt.jersey_number ASC
                ");
                $teammatesStmt->execute([$team['id']]);
                $teammates = $teammatesStmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                    <header class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                            <i class="fas fa-users text-blue-600 mr-2"></i>
                            Your Teammates - <?= htmlspecialchars($team['barangay']) ?>
                            <span class="ml-2 bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                                <?= count($teammates) ?>
                            </span>
                        </h3>
                        <p class="text-sm text-gray-600">
                            Players in your <?= htmlspecialchars($team['barangay']) ?> team
                        </p>
                    </header>

                    <?php if (empty($teammates)): ?>
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4 text-gray-300">👥</div>
                            <p class="text-gray-500 text-lg">No teammates yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <?php foreach ($teammates as $teammate): ?>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($teammate['jersey_number'])): ?>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-10 h-10 bg-blue-100 text-blue-800 font-bold rounded-full border border-blue-300 text-sm">
                                                    #<?= $teammate['jersey_number'] ?>
                                                </span>
                                            </div>
                                        <?php else: ?>
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center justify-center w-10 h-10 bg-gray-100 text-gray-400 font-bold rounded-full border border-gray-300 text-sm">
                                                    #?
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                <?= htmlspecialchars($teammate['firstname'] . ' ' . $teammate['lastname']) ?>
                                                <?php if ($teammate['role'] === 'coach'): ?>
                                                    <span class="ml-1 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Coach</span>
                                                <?php elseif ($teammate['username'] === $username): ?>
                                                    <span class="ml-1 text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full">You</span>
                                                <?php endif; ?>
                                            </p>
                                            
                                            <?php if (!empty($teammate['position'])): ?>
                                                <p class="text-xs text-purple-600 font-medium">
                                                    <?= htmlspecialchars($teammate['position']) ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-xs text-gray-500">No position assigned</p>
                                            <?php endif; ?>
                                            
                                            <?php if ($teammate['role'] === 'coach'): ?>
                                                <p class="text-xs text-yellow-600 font-medium mt-1">
                                                    <i class="fas fa-whistle"></i> Team Coach
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endif; ?>

            <!-- Join Team Section - Only show if player has no team and their barangay has a team -->
            <?php if (!$team && $player_barangay): ?>
                <?php
                // Check if there's an approved team for the player's barangay
                $barangayTeamStmt = $pdo->prepare("
                    SELECT id, barangay, coach_username 
                    FROM teams 
                    WHERE barangay = ? AND approval_status = 'approved'
                ");
                $barangayTeamStmt->execute([$player_barangay]);
                $barangay_team = $barangayTeamStmt->fetch(PDO::FETCH_ASSOC);
                ?>

                <?php if ($barangay_team): ?>
                    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                        <header class="mb-4">
                            <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                                <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                                Join Your Barangay Team
                            </h3>
                            <p class="text-sm text-gray-600">
                                Join your barangay's official team: <strong><?= htmlspecialchars($player_barangay) ?></strong>
                            </p>
                        </header>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-blue-800"><?= htmlspecialchars($player_barangay) ?> Team</p>
                                    <p class="text-sm text-blue-600">Your barangay's official basketball team</p>
                                </div>
                                <form action="join_team_action.php" method="POST" class="flex-shrink-0">
                                    <input type="hidden" name="team_id" value="<?= htmlspecialchars($barangay_team['id']) ?>">
                                    <button type="submit"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition duration-200 flex items-center">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Join Team
                                    </button>
                                </form>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            As a resident of <?= htmlspecialchars($player_barangay) ?>, you can only join your barangay's official team.
                        </p>
                    </article>
                <?php else: ?>
                    <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                        <header class="mb-4">
                            <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                                <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                                Join Your Barangay Team
                            </h3>
                        </header>
                        <div class="text-center py-6">
                            <div class="text-4xl mb-3 text-gray-400">
                                <i class="fas fa-home"></i>
                            </div>
                            <p class="text-gray-500">There is currently no approved team for your barangay (<strong><?= htmlspecialchars($player_barangay) ?></strong>).</p>
                            <p class="text-gray-400 text-sm mt-2">Please check back later or contact your barangay officials.</p>
                        </div>
                    </article>
                <?php endif; ?>
            <?php elseif (!$team && !$player_barangay): ?>
                <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
                    <header class="mb-4">
                        <h3 class="font-semibold text-gray-900 text-lg flex items-center">
                            <i class="fas fa-user-plus text-blue-600 mr-2"></i>
                            Join Your Barangay Team
                        </h3>
                    </header>
                    <div class="text-center py-6">
                        <div class="text-4xl mb-3 text-yellow-500">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p class="text-gray-500">Your barangay information is not set in your profile.</p>
                        <p class="text-gray-400 text-sm mt-2">Please update your profile with your barangay to join a team.</p>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/player_footer.php'; ?>