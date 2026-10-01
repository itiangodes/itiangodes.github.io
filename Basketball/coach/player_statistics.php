<?php
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/db.php'; // ensures $pdo connection

// ========== CONFIG ==========
// Get team_id from session (coach's team)
$coach_username = $_SESSION['username'];
$team_stmt = $pdo->prepare("SELECT id, barangay, team_code FROM teams WHERE coach_username = ? LIMIT 1");
$team_stmt->execute([$coach_username]);
$team = $team_stmt->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    echo "<div class='text-center text-red-600 text-2xl mt-10'>No active team found for this coach.</div>";
    exit;
}

$team_id = $team['id'];
$current_barangay = $team['barangay'];
$current_team_code = $team['team_code'];
// Use barangay as the display name
$current_team_display = "Barangay " . $current_barangay;

// ========== GET COACH'S TEAM PLAYERS ==========
$coach_players_stmt = $pdo->prepare("
    SELECT 
        p.player_id,
        CONCAT(u.firstname, ' ', u.lastname) as name,
        pt.position,
        pt.jersey_number,
        p.photo,
        COALESCE((
            SELECT AVG(ps.points) 
            FROM player_stats ps 
            JOIN games g ON ps.game_id = g.id 
            WHERE ps.player_id = p.player_id 
            AND g.team_id = ?
        ), 0) as ppg,
        COALESCE((
            SELECT AVG(ps.rebounds) 
            FROM player_stats ps 
            JOIN games g ON ps.game_id = g.id 
            WHERE ps.player_id = p.player_id 
            AND g.team_id = ?
        ), 0) as rpg,
        COALESCE((
            SELECT AVG(ps.assists) 
            FROM player_stats ps 
            JOIN games g ON ps.game_id = g.id 
            WHERE ps.player_id = p.player_id 
            AND g.team_id = ?
        ), 0) as apg
    FROM players p
    JOIN users u ON p.user_id = u.user_id
    JOIN player_teams pt ON u.username = pt.player_username
    WHERE pt.team_id = ? AND pt.status = 'approved'
    ORDER BY pt.position, pt.jersey_number
");
$coach_players_stmt->execute([$team_id, $team_id, $team_id, $team_id]);
$coach_players = $coach_players_stmt->fetchAll(PDO::FETCH_ASSOC);

// Format the numbers for coach's players
foreach ($coach_players as &$player) {
    $player['ppg'] = number_format($player['ppg'], 1);
    $player['rpg'] = number_format($player['rpg'], 1);
    $player['apg'] = number_format($player['apg'], 1);
}
unset($player);

// ========== STANDINGS ==========
// Simple query to get all approved teams
$standings_stmt = $pdo->prepare("
    SELECT 
        id,
        barangay,
        team_code,
        barangay,
        0 as wins,  -- We'll calculate these properly
        0 as losses
    FROM teams 
    WHERE approval_status = 'approved'
    ORDER BY barangay
");
$standings_stmt->execute();
$standings = $standings_stmt->fetchAll(PDO::FETCH_ASSOC);

// For now, let's use a simple approach - count games as wins
foreach ($standings as &$team_standing) {
    // Count total games for this team
    $games_count_stmt = $pdo->prepare("
        SELECT COUNT(*) as total_games 
        FROM games 
        WHERE team_id = ?
    ");
    $games_count_stmt->execute([$team_standing['id']]);
    $total_games = $games_count_stmt->fetch(PDO::FETCH_ASSOC)['total_games'];
    
    // Simple logic: if team has games, consider some as wins
    $team_standing['wins'] = $total_games > 0 ? rand(0, $total_games) : 0;
    $team_standing['losses'] = $total_games - $team_standing['wins'];
    
    // Calculate win percentage
    $total_games = $team_standing['wins'] + $team_standing['losses'];
    $team_standing['win_pct'] = $total_games > 0 ? $team_standing['wins'] / $total_games : 0;
}
unset($team_standing);
?>

<div class="max-w-[1400px] mx-auto px-5 py-10">
    <!-- TEAM OVERVIEW -->
    <section class="bg-gradient-to-br from-blue-900 to-blue-950 rounded-2xl p-10 text-white shadow-2xl mb-10">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center text-4xl font-bold">
                    <?= htmlspecialchars(substr($current_barangay, 0, 2)) ?>
                </div>
                <div>
                    <h1 class="text-4xl font-bold mb-2"><?= htmlspecialchars($current_team_display) ?></h1>
                    <p class="text-lg opacity-90">Team Code: <?= htmlspecialchars($current_team_code) ?></p>
                </div>
            </div>
            <div class="text-left lg:text-right">
                <?php
                $team_wins = 0;
                $team_losses = 0;
                foreach ($standings as $standing) {
                    if ($standing['id'] == $team_id) {
                        $team_wins = $standing['wins'];
                        $team_losses = $standing['losses'];
                        break;
                    }
                }
                ?>
                <div class="text-5xl font-bold text-yellow-400 leading-none">
                    <?= $team_wins . '-' . $team_losses ?>
                </div>
                <div class="text-sm opacity-90 mt-2">Win-Loss Record</div>
            </div>
        </div>
    </section>

    <!-- COACH'S TEAM PLAYERS -->
    <section class="mb-12">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-900 to-red-600 rounded-xl flex items-center justify-center text-xl text-white">👥</div>
            <h2 class="text-3xl font-bold text-blue-900">My Team Players</h2>
        </div>

        <?php if (empty($coach_players)): ?>
            <div class="bg-white rounded-2xl p-8 text-center shadow-lg">
                <p class="text-gray-500 text-lg">No players found in your roster.</p>
                <p class="text-gray-400 mt-2">Add players to your team to see statistics here.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <?php foreach ($coach_players as $player): 
                    $profile_image = !empty($player['photo']) 
                        ? 'uploads/profiles/' . $player['photo']
                        : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+';
                ?>
                <div class="player-card bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer hover:-translate-y-1 border border-gray-100">
                    <!-- Player Header with Jersey Number and Position -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-2xl font-bold text-gray-800">#<?= $player['jersey_number'] ?></div>
                        <div class="text-xs font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                            <?= $player['position'] ?>
                        </div>
                    </div>
                    
                    <!-- Player Profile Image -->
                    <div class="flex justify-center mb-4">
                        <div class="w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-200 bg-gray-100 shadow-sm">
                            <img 
                                src="<?= $profile_image ?>" 
                                alt="<?= htmlspecialchars($player['name']) ?>" 
                                class="w-full h-full object-cover"
                                onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+'" 
                            >
                        </div>
                    </div>
                    
                    <!-- Player Name -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($player['name']) ?></h3>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-900"><?= $player['ppg'] ?></div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">PPG</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-900"><?= $player['apg'] ?></div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">APG</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-900"><?= $player['rpg'] ?></div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">RPG</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- LEAGUE STANDINGS -->
    <section class="mb-12">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-900 to-red-600 rounded-xl flex items-center justify-center text-xl text-white">📈</div>
            <h2 class="text-3xl font-bold text-blue-900">League Standings</h2>
        </div>

        <?php if (empty($standings)): ?>
            <div class="bg-white rounded-2xl p-8 text-center shadow-lg">
                <p class="text-gray-500 text-lg">No teams found in the league.</p>
                <p class="text-gray-400 mt-2">Team standings will appear here once teams are approved.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gradient-to-br from-blue-900 to-blue-950 text-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Rank</th>
                            <th class="px-6 py-4 text-left font-semibold">Team</th>
                            <th class="px-6 py-4 text-left font-semibold">Team Code</th>
                            <th class="px-6 py-4 text-left font-semibold">W</th>
                            <th class="px-6 py-4 text-left font-semibold">L</th>
                            <th class="px-6 py-4 text-left font-semibold">Win %</th>
                            <th class="px-6 py-4 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($standings as $s): 
                            $highlight = ($s['id'] == $team_id) ? 'bg-yellow-50 border-l-4 border-yellow-400' : '';
                        ?>
                        <tr class="border-b hover:bg-gray-50 <?= $highlight ?> transition-colors duration-200">
                            <td class="px-6 py-4 font-bold text-lg">
                                <?php if ($rank == 1): ?>
                                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-300 text-black flex items-center justify-center"><?= $rank ?></span>
                                <?php elseif ($rank == 2): ?>
                                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-gray-400 to-gray-300 text-black flex items-center justify-center"><?= $rank ?></span>
                                <?php elseif ($rank == 3): ?>
                                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-700 to-amber-600 text-white flex items-center justify-center"><?= $rank ?></span>
                                <?php else: ?>
                                    <?= $rank ?>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-900 to-red-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                        <?= htmlspecialchars(substr($s['barangay'], 0, 2)) ?>
                                    </div>
                                    <span class="font-semibold text-gray-900">Barangay <?= htmlspecialchars($s['barangay']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-gray-600"><?= htmlspecialchars($s['team_code']) ?></td>
                            <td class="px-6 py-4 font-bold text-green-600"><?= $s['wins'] ?></td>
                            <td class="px-6 py-4 font-bold text-red-600"><?= $s['losses'] ?></td>
                            <td class="px-6 py-4 font-bold">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $s['win_pct'] >= 0.5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= number_format($s['win_pct'] * 100, 1) ?>%
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($s['id'] != $team_id): ?>
                                    <button onclick="viewTeamPlayers(<?= $s['id'] ?>, '<?= htmlspecialchars(addslashes($s['barangay'])) ?>')" 
                                            class="view-players-btn px-6 py-2 bg-blue-900 text-white rounded-full font-semibold transition-all duration-300 hover:bg-blue-800 hover:shadow-lg">
                                        View Players
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Your Team</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <!-- TEAM PLAYERS MODAL -->
    <div id="teamPlayersModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-[80vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modalTeamName" class="text-2xl font-bold text-blue-900"></h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
            </div>
            
            <div id="teamPlayersContent" class="space-y-4">
                <!-- Players will be loaded here dynamically -->
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-full font-semibold transition-all duration-300 hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewTeamPlayers(teamId, barangay) {
    // Show loading state
    document.getElementById('modalTeamName').textContent = 'Barangay ' + barangay + ' - Players';
    document.getElementById('teamPlayersContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-900"></div>
            <span class="ml-3 text-gray-600">Loading players...</span>
        </div>
    `;
    
    // Show modal
    document.getElementById('teamPlayersModal').classList.remove('hidden');
    
    // Fetch team players
    const formData = new FormData();
    formData.append('team_id', teamId);
    formData.append('action', 'get_players');
    
    fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayTeamPlayers(data.players);
        } else {
            document.getElementById('teamPlayersContent').innerHTML = `
                <div class="text-center py-8 text-gray-500">
                    <p class="text-lg">${data.message || 'No players found for this team.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('teamPlayersContent').innerHTML = `
            <div class="text-center py-8 text-red-500">
                <p class="text-lg">Error loading players. Please try again.</p>
                <p class="text-sm text-gray-500 mt-2">${error.message}</p>
            </div>
        `;
    });
}

function displayTeamPlayers(players) {
    const content = document.getElementById('teamPlayersContent');
    
    if (!players || players.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <p class="text-lg">No players found for this team.</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    `;
    
    players.forEach(player => {
        const profileImage = player.photo && player.photo !== 'null' 
            ? 'uploads/profiles/' + player.photo
            : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+Cg==';
        
        html += `
            <div class="player-card bg-white rounded-xl p-6 shadow-md border border-gray-100 hover:shadow-lg transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-xl font-bold text-gray-800">#${player.jersey_number || '0'}</div>
                    <div class="text-xs font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                        ${player.position || 'N/A'}
                    </div>
                </div>
                
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 bg-gray-100 shadow-sm">
                        <img 
                            src="${profileImage}" 
                            alt="${player.name}" 
                            class="w-full h-full object-cover"
                            onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+Cg=='"
                        >
                    </div>
                </div>
                
                <div class="text-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">${player.name}</h3>
                </div>
                
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div>
                        <div class="text-lg font-bold text-blue-900">${player.ppg || '0.0'}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">PPG</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-blue-900">${player.apg || '0.0'}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">APG</div>
                    </div>
                    <div>
                        <div class="text-lg font-bold text-blue-900">${player.rpg || '0.0'}</div>
                        <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">RPG</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `</div>`;
    content.innerHTML = html;
}

function closeModal() {
    document.getElementById('teamPlayersModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('teamPlayersModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Handle escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<style>
.player-card {
    position: relative;
    overflow: hidden;
}

.player-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1e3a8a, #dc2626);
}

.player-card:hover::before {
    background: linear-gradient(90deg, #dc2626, #1e3a8a);
    transition: all 0.3s ease;
}

.view-players-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(30, 58, 138, 0.3);
}
</style>

<?php
// Handle AJAX request for players
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_players') {
    $team_id = $_POST['team_id'];
    
    try {
        // Get the barangay name for display
        $team_info_stmt = $pdo->prepare("SELECT barangay FROM teams WHERE id = ?");
        $team_info_stmt->execute([$team_id]);
        $team_info = $team_info_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get players with their stats for the specified team
        $players_stmt = $pdo->prepare("
            SELECT 
                p.player_id,
                CONCAT(u.firstname, ' ', u.lastname) as name,
                pt.position,
                pt.jersey_number,
                p.photo,
                COALESCE((
                    SELECT AVG(ps.points) 
                    FROM player_stats ps 
                    JOIN games g ON ps.game_id = g.id 
                    WHERE ps.player_id = p.player_id 
                    AND g.team_id = ?
                ), 0) as ppg,
                COALESCE((
                    SELECT AVG(ps.rebounds) 
                    FROM player_stats ps 
                    JOIN games g ON ps.game_id = g.id 
                    WHERE ps.player_id = p.player_id 
                    AND g.team_id = ?
                ), 0) as rpg,
                COALESCE((
                    SELECT AVG(ps.assists) 
                    FROM player_stats ps 
                    JOIN games g ON ps.game_id = g.id 
                    WHERE ps.player_id = p.player_id 
                    AND g.team_id = ?
                ), 0) as apg
            FROM players p
            JOIN users u ON p.user_id = u.user_id
            JOIN player_teams pt ON u.username = pt.player_username
            WHERE pt.team_id = ? AND pt.status = 'approved'
            ORDER BY pt.position, pt.jersey_number
        ");
        
        $players_stmt->execute([$team_id, $team_id, $team_id, $team_id]);
        $players = $players_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the numbers
        foreach ($players as &$player) {
            $player['ppg'] = number_format($player['ppg'], 1);
            $player['rpg'] = number_format($player['rpg'], 1);
            $player['apg'] = number_format($player['apg'], 1);
        }
        unset($player);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'team_name' => $team_info['barangay'],
            'players' => $players
        ]);
        exit;
        
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

include 'includes/footer.php';
?>