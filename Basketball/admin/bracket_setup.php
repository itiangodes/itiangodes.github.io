<?php
// bracket_setup.php
include 'includes/auth.php';
include 'includes/db.php';

// Check if coming from previous step
if (empty($_SESSION['tournament_data']) || empty($_SESSION['tournament_data']['teams'])) {
    header("Location: team_setup.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['tournament_data']['format'] = $_POST['tournament_format'];
    header("Location: review_tournament.php");
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';

$team_count = $_SESSION['tournament_data']['team_count'];
$teams = $_SESSION['tournament_data']['teams'];
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Create Tournament - Step 3: Bracket Setup</h1>
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
                    <div class="progress-step completed">
                        <div class="step-number">1</div>
                        <div class="step-label">Tournament Details</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step completed">
                        <div class="step-number">2</div>
                        <div class="step-label">Setup Teams</div>
                    </div>
                    <div class="progress-connector completed"></div>
                    <div class="progress-step active">
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

        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Setup Form -->
                <div class="lg:col-span-1 bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-project-diagram text-purple-500"></i> Bracket Setup
                    </h2>
                    
                    <form method="POST" action="" id="bracketForm">
                        <div class="space-y-4">
                            <!-- Bracket Type -->
                            <div>
                                <label for="tournament_format" class="block text-sm font-medium text-gray-700 mb-2">
                                    Bracket Type *
                                </label>
                                <select 
                                    id="tournament_format" 
                                    name="tournament_format"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                    onchange="generateBracketPreview()"
                                >
                                    <option value="">Select Bracket Type</option>
                                    <option value="single" <?php echo (isset($_SESSION['tournament_data']['format']) && $_SESSION['tournament_data']['format'] === 'single') ? 'selected' : ''; ?>>Single Elimination</option>
                                    <option value="double" <?php echo (isset($_SESSION['tournament_data']['format']) && $_SESSION['tournament_data']['format'] === 'double') ? 'selected' : ''; ?>>Double Elimination</option>
                                    <option value="roundrobin" <?php echo (isset($_SESSION['tournament_data']['format']) && $_SESSION['tournament_data']['format'] === 'roundrobin') ? 'selected' : ''; ?>>Round Robin</option>
                                    <option value="groupstage" <?php echo (isset($_SESSION['tournament_data']['format']) && $_SESSION['tournament_data']['format'] === 'groupstage') ? 'selected' : ''; ?>>Group Stage + Knockout</option>
                                    <option value="marchmadness" <?php echo (isset($_SESSION['tournament_data']['format']) && $_SESSION['tournament_data']['format'] === 'marchmadness') ? 'selected' : ''; ?>>March Madness</option>
                                </select>
                            </div>

                            <!-- Team Count Display -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Number of Teams
                                </label>
                                <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                                    <strong><?php echo $team_count; ?></strong> teams
                                </div>
                            </div>

                            <!-- Format Recommendations -->
                            <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-lightbulb text-blue-600 mr-3"></i>
                                    <div>
                                        <h4 class="text-sm font-medium text-blue-800">Recommended Format</h4>
                                        <p class="text-xs text-blue-700 mt-1">
                                            For <?php echo $team_count; ?> teams, <strong>Group Stage + Knockout</strong> is recommended for balanced competition.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Auto Randomization Info -->
                            <div class="mt-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
                                <div class="flex items-center">
                                    <i class="fas fa-random text-purple-600 mr-3"></i>
                                    <div>
                                        <h4 class="text-sm font-medium text-purple-800">Automatic Random Seeding</h4>
                                        <p class="text-xs text-purple-700 mt-1">
                                            Team positions are automatically randomized each time you generate a bracket preview.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                onclick="generateBracketPreview()"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition duration-200 flex items-center justify-center gap-2"
                            >
                                <i class="fas fa-eye"></i>
                                Generate Bracket Preview
                            </button>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg font-medium transition duration-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                Continue to Review
                            </button>
                            <a href="team_setup.php" class="w-full bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg font-medium transition duration-200 flex items-center justify-center gap-2 mt-2">
                                <i class="fas fa-arrow-left"></i>
                                Back to Teams
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bracket Preview -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-800" id="tournamentNameDisplay">
                                <?php echo htmlspecialchars($_SESSION['tournament_data']['name']); ?> - Bracket Preview
                            </h2>
                            <span class="bg-blue-100 text-blue-800 text-sm font-medium px-3 py-1 rounded-full" id="tournamentFormat">
                                Not Selected
                            </span>
                        </div>

                        <!-- Auto Randomization Notice -->
                        <div class="mb-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-random text-purple-600 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-purple-800">Randomized Team Seeding</h4>
                                    <p class="text-sm text-purple-700 mt-1">
                                        Teams are automatically randomized each time you generate a bracket preview for fair placement.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bracket Container -->
                        <div id="bracketContainer" class="border-2 border-dashed border-gray-300 rounded-lg p-8 min-h-[600px] flex items-center justify-center">
                            <div class="text-center" id="emptyState">
                                <i class="fas fa-diagram-project text-gray-400 text-6xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Bracket Generated</h3>
                                <p class="text-gray-500">Select a bracket type and click "Generate Bracket Preview"</p>
                            </div>
                            <div id="bracketContent" class="w-full hidden"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function generateBracketPreview() {
    const format = document.getElementById('tournament_format').value;
    const teams = <?php echo json_encode($teams); ?>;
    
    if (!format) {
        alert('Please select a bracket type first.');
        return;
    }
    
    // Update the tournament info display
    document.getElementById('tournamentNameDisplay').textContent = '<?php echo htmlspecialchars($_SESSION['tournament_data']['name']); ?> - Bracket Preview';
    
    const formatNames = {
        'single': 'Single Elimination',
        'double': 'Double Elimination', 
        'roundrobin': 'Round Robin',
        'groupstage': 'Group Stage + Knockout',
        'marchmadness': 'March Madness'
    };
    
    document.getElementById('tournamentFormat').textContent = formatNames[format] || 'Not Selected';

    // Show bracket content
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('bracketContent').classList.remove('hidden');
    
    // Remove dashed border
    document.getElementById('bracketContainer').classList.remove('border-dashed');
    document.getElementById('bracketContainer').classList.add('border-solid', 'border-blue-200');

    // Build appropriate bracket type
    let bracketHTML = '';
    if (format === 'single') {
        bracketHTML = buildSingleEliminationBracket(teams);
    } else if (format === 'double') {
        bracketHTML = buildDoubleEliminationBracket(teams);
    } else if (format === 'roundrobin') {
        bracketHTML = buildRoundRobinBracket(teams);
    } else if (format === 'groupstage') {
        bracketHTML = buildGroupStageBracket(teams);
    } else if (format === 'marchmadness') {
        bracketHTML = buildMarchMadnessBracket(teams);
    }

    document.getElementById('bracketContent').innerHTML = bracketHTML;
}

// Fisher-Yates shuffle algorithm
function shuffleArray(array) {
    const shuffled = [...array];
    for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
    }
    return shuffled;
}

// Bracket building functions
function buildSingleEliminationBracket(teams) {
    const randomizedTeams = shuffleArray(teams);
    const teamCount = randomizedTeams.length;
    
    // Calculate number of rounds needed
    let rounds = Math.ceil(Math.log2(teamCount));
    let bracketSize = Math.pow(2, rounds);
    
    // Create seeded teams array with byes if needed
    let seededTeams = [...randomizedTeams];
    let byes = bracketSize - teamCount;
    
    // Add bye slots if needed
    for (let i = 0; i < byes; i++) {
        seededTeams.push({barangay: 'BYE', coach_username: '', isBye: true});
    }
    
    // Generate bracket HTML
    let bracketHTML = `
        <div class="text-center mb-6">
            <i class="fas fa-brackets-curly text-4xl text-blue-500 mb-2"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Single Elimination Bracket</h3>
            <p class="text-gray-600">${teamCount} teams | ${rounds} rounds | ${byes > 0 ? byes + ' byes' : 'no byes'}</p>
            <div class="mt-2 text-sm text-purple-600 font-medium">
                <i class="fas fa-random mr-1"></i>Automatically Randomized Team Seeding
            </div>
        </div>
        <div class="overflow-x-auto">
            <div class="bracket-container min-w-max mx-auto py-4">
                <div class="flex space-x-8 justify-center items-start">
    `;
    
    // Generate each round
    for (let round = 0; round < rounds; round++) {
        const matchesInRound = Math.pow(2, rounds - round - 1);
        const roundName = getRoundName(round, rounds, matchesInRound);
        
        bracketHTML += `
            <div class="round flex flex-col space-y-8">
                <div class="text-center font-semibold text-blue-700 mb-2 bg-blue-50 py-2 rounded-lg">${roundName}</div>
        `;
        
        for (let match = 0; match < matchesInRound; match++) {
            let team1, team2;
            
            if (round === 0) {
                // First round - teams are placed directly
                const team1Index = match * 2;
                const team2Index = match * 2 + 1;
                team1 = seededTeams[team1Index] || {barangay: 'TBD', coach_username: '', isBye: false};
                team2 = seededTeams[team2Index] || {barangay: 'TBD', coach_username: '', isBye: false};
            } else {
                // Later rounds - winners from previous rounds
                team1 = {barangay: `Winner M${match * 2 + 1}`, coach_username: '', isBye: false, isWinner: true};
                team2 = {barangay: `Winner M${match * 2 + 2}`, coach_username: '', isBye: false, isWinner: true};
            }
            
            const isByeMatch = team1.isBye || team2.isBye;
            const isWinnerSlot = team1.isWinner || team2.isWinner;
            
            bracketHTML += `
                <div class="match bg-white border-2 ${isByeMatch ? 'border-yellow-300' : isWinnerSlot ? 'border-green-300' : 'border-blue-300'} rounded-lg shadow-sm p-4 min-w-[220px] transition-all duration-200 hover:shadow-md">
                    <div class="text-xs text-gray-500 text-center mb-2 font-medium">Match ${getMatchNumber(round, match)}</div>
                    <div class="team ${getTeamClass(team1)} border-b ${getBorderClass(team1)} px-3 py-2 text-sm font-medium rounded-t">
                        ${getTeamDisplay(team1, round, match, 0)}
                    </div>
                    <div class="team ${getTeamClass(team2)} px-3 py-2 text-sm font-medium rounded-b">
                        ${getTeamDisplay(team2, round, match, 1)}
                    </div>
                    ${isByeMatch ? '<div class="text-xs text-yellow-600 text-center mt-2"><i class="fas fa-bolt mr-1"></i>Auto-advance</div>' : ''}
                </div>
            `;
        }
        
        bracketHTML += `</div>`;
        
        // Add connector arrows between rounds (except last round)
        if (round < rounds - 1) {
            bracketHTML += `
                <div class="connector flex flex-col space-y-8">
                    <div class="text-center font-semibold text-blue-700 mb-2 py-2">&nbsp;</div>
            `;
            
            for (let match = 0; match < matchesInRound; match++) {
                bracketHTML += `
                    <div class="flex items-center justify-center h-24">
                        <div class="w-12 border-t-2 border-blue-400 relative">
                            <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-blue-400 border-y-4 border-y-transparent"></div>
                        </div>
                    </div>
                `;
            }
            
            bracketHTML += `</div>`;
        }
    }
    
    // Add Champion section after the final round
    bracketHTML += `
        <div class="champion-section flex flex-col justify-center items-center space-y-8 ml-8">
            <div class="text-center font-semibold text-green-700 mb-2 bg-green-50 py-2 px-4 rounded-lg">Champion</div>
            <div class="champion-trophy bg-gradient-to-br from-yellow-400 to-yellow-600 border-2 border-yellow-500 rounded-lg p-6 min-w-[240px] text-center shadow-lg transform hover:scale-105 transition-transform duration-200">
                <i class="fas fa-trophy text-white text-3xl mb-3"></i>
                <div class="team bg-white border border-yellow-300 rounded px-3 py-4 text-sm font-bold text-gray-800">
                    🏆 Tournament Winner 🏆
                </div>
                <div class="text-xs text-white font-medium mt-3">CHAMPION</div>
            </div>
        </div>
    `;
    
    bracketHTML += `
                </div>
            </div>
        </div>
        
        <!-- Teams Seeding Section -->
        <div class="mt-12 border-t pt-8">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-list-ol text-purple-500"></i>
                    Randomized Team Positions
                </h4>
                <span class="text-sm text-gray-500 bg-purple-100 px-3 py-1 rounded-full">
                    <i class="fas fa-random mr-1"></i>Auto Randomized
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                ${randomizedTeams.map((team, index) => `
                    <div class="bg-white border-2 border-purple-200 rounded-xl p-4 text-center shadow-sm hover:shadow-md transition-shadow duration-200">
                        <div class="text-lg font-bold text-purple-600 mb-2">Position ${index + 1}</div>
                        <div class="font-semibold text-gray-800 text-base mb-2">${team.barangay}</div>
                        ${team.coach_username ? `
                            <div class="text-xs text-gray-600 bg-gray-100 rounded px-2 py-1 inline-block">
                                <i class="fas fa-user mr-1"></i>${team.coach_username}
                            </div>
                        ` : ''}
                        <div class="mt-3 text-xs text-purple-500 font-medium">
                            <i class="fas fa-random mr-1"></i>Random Assignment
                        </div>
                    </div>
                `).join('')}
                ${byes > 0 ? Array.from({length: byes}, (_, i) => `
                    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4 text-center">
                        <div class="text-lg font-bold text-yellow-600 mb-2">BYE</div>
                        <div class="font-semibold text-yellow-700 text-base mb-2">Auto Advance</div>
                        <div class="text-xs text-yellow-600 bg-yellow-100 rounded px-2 py-1 inline-block">
                            <i class="fas fa-bolt mr-1"></i>Free Win
                        </div>
                    </div>
                `).join('') : ''}
            </div>
            
            <!-- Bracket Statistics -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">${teamCount}</div>
                    <div class="text-sm text-blue-700">Total Teams</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">${rounds}</div>
                    <div class="text-sm text-green-700">Total Rounds</div>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600">${byes}</div>
                    <div class="text-sm text-yellow-700">Bye Slots</div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">${randomizedTeams.length}</div>
                    <div class="text-sm text-purple-700">Randomized</div>
                </div>
            </div>
        </div>
    `;
    
    return bracketHTML;
}

// Helper functions for single elimination bracket
function getMatchNumber(round, match) {
    const matchesInPreviousRounds = Math.pow(2, round) - 1;
    return matchesInPreviousRounds + match + 1;
}

function getTeamClass(team) {
    if (team.isBye) return 'bg-yellow-50 text-yellow-700';
    if (team.isWinner) return 'bg-green-50 text-green-700';
    return 'bg-blue-50 text-blue-800';
}

function getBorderClass(team) {
    if (team.isBye) return 'border-yellow-200';
    if (team.isWinner) return 'border-green-200';
    return 'border-blue-200';
}

function getTeamDisplay(team, round, match, position) {
    if (team.isBye) {
        return '<span class="text-yellow-600"><i class="fas fa-walking mr-1"></i>BYE</span>';
    }
    
    if (team.isWinner) {
        return `<span class="text-green-600"><i class="fas fa-arrow-up mr-1"></i>${team.barangay}</span>`;
    }
    
    // For first round teams, show their actual position in randomized order
    const positionNumber = (match * 2) + position + 1;
    return `
        <div class="flex justify-between items-center">
            <span>${team.barangay}</span>
            <span class="text-xs bg-purple-200 text-purple-800 px-2 py-1 rounded">#${positionNumber}</span>
        </div>
    `;
}

function getRoundName(round, totalRounds, matchesInRound) {
    if (matchesInRound === 1) return 'FINALS';
    if (matchesInRound === 2) return 'SEMI-FINALS';
    if (matchesInRound === 4) return 'QUARTER-FINALS';
    if (round === 0) return `ROUND OF ${matchesInRound * 2}`;
    
    return `ROUND ${round + 1}`;
}

// Enhanced Group Stage Bracket for 41 teams
function buildGroupStageBracket(teams) {
    const randomizedTeams = shuffleArray(teams);
    const teamCount = randomizedTeams.length;
    
    // For 41 teams: 5 groups of 5 + 4 groups of 4
    const groupsOf5 = 5;
    const groupsOf4 = 4;
    const totalGroups = groupsOf5 + groupsOf4;
    
    // Distribute teams into groups
    const groups = [];
    let teamIndex = 0;
    
    // Create groups of 5
    for (let i = 0; i < groupsOf5; i++) {
        groups.push(randomizedTeams.slice(teamIndex, teamIndex + 5));
        teamIndex += 5;
    }
    
    // Create groups of 4
    for (let i = 0; i < groupsOf4; i++) {
        groups.push(randomizedTeams.slice(teamIndex, teamIndex + 4));
        teamIndex += 4;
    }
    
    let bracketHTML = `
        <div class="text-center mb-6">
            <i class="fas fa-object-group text-4xl text-orange-500 mb-2"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Group Stage + Knockout Bracket</h3>
            <p class="text-gray-600">${teamCount} teams | ${totalGroups} groups | 16-team knockout</p>
            <div class="mt-2 text-sm text-purple-600 font-medium">
                <i class="fas fa-random mr-1"></i>Automatically Randomized Team Seeding
            </div>
        </div>
    `;
    
    // Group Stage Display
    bracketHTML += `
        <div class="mb-12">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-users text-orange-500"></i>
                    Group Stage (${totalGroups} Groups)
                </h4>
                <span class="text-sm text-gray-500 bg-orange-100 px-3 py-1 rounded-full">
                    ${groupsOf5} groups of 5 + ${groupsOf4} groups of 4
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    `;
    
    groups.forEach((group, groupIndex) => {
        const groupSize = group.length;
        const groupName = String.fromCharCode(65 + groupIndex);
        
        bracketHTML += `
            <div class="bg-white border-2 border-orange-300 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="flex justify-between items-center mb-3">
                    <div class="font-bold text-lg text-orange-700">Group ${groupName}</div>
                    <div class="text-sm bg-orange-500 text-white px-2 py-1 rounded-full">
                        ${groupSize} teams
                    </div>
                </div>
                <div class="space-y-2">
        `;
        
        group.forEach((team, teamIndex) => {
            bracketHTML += `
                <div class="flex justify-between items-center bg-orange-50 border border-orange-200 rounded-lg px-3 py-2">
                    <div class="font-medium text-gray-800">${team.barangay}</div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-purple-200 text-purple-800 px-2 py-1 rounded">Seed ${teamIndex + 1}</span>
                        ${team.coach_username ? `
                            <span class="text-xs bg-gray-200 text-gray-700 px-2 py-1 rounded">${team.coach_username}</span>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        bracketHTML += `
                </div>
                <div class="mt-3 text-xs text-orange-600">
                    <i class="fas fa-calendar mr-1"></i>
                    ${groupSize === 5 ? '4 games per team' : '3 games per team'}
                </div>
            </div>
        `;
    });
    
    bracketHTML += `
            </div>
        </div>
    `;
    
    // Knockout Stage Preview
    bracketHTML += `
        <div class="border-t pt-8">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-trophy text-green-500"></i>
                    Knockout Stage (16 Teams)
                </h4>
                <span class="text-sm text-gray-500 bg-green-100 px-3 py-1 rounded-full">
                    9 group winners + 7 best runners-up
                </span>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h5 class="font-semibold text-green-800 mb-2">Advancement Rules:</h5>
                        <ul class="space-y-1 text-green-700">
                            <li>• 9 group winners advance automatically</li>
                            <li>• 7 best runners-up advance</li>
                            <li>• 16 teams total in knockout stage</li>
                            <li>• Single elimination to champion</li>
                        </ul>
                    </div>
                    <div>
                        <h5 class="font-semibold text-green-800 mb-2">Knockout Structure:</h5>
                        <ul class="space-y-1 text-green-700">
                            <li>• Round of 16: 8 matches</li>
                            <li>• Quarter-finals: 4 matches</li>
                            <li>• Semi-finals: 2 matches</li>
                            <li>• Final: 1 match</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Simplified knockout bracket preview -->
            <div class="overflow-x-auto">
                <div class="min-w-max mx-auto">
                    <div class="flex space-x-8 justify-center items-start">
                        <!-- Round of 16 -->
                        <div class="round flex flex-col space-y-4">
                            <div class="text-center font-semibold text-blue-700 mb-2 bg-blue-50 py-2 rounded-lg">Round of 16</div>
                            ${Array.from({length: 8}, (_, i) => `
                                <div class="match bg-white border-2 border-blue-300 rounded-lg p-3 min-w-[180px]">
                                    <div class="text-xs text-gray-500 text-center mb-1">Match ${i + 1}</div>
                                    <div class="team bg-blue-50 border-b border-blue-200 px-2 py-1 text-xs font-medium rounded-t">
                                        Group Winner
                                    </div>
                                    <div class="team bg-green-50 px-2 py-1 text-xs font-medium rounded-b">
                                        Runner-up
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Connector -->
                        <div class="connector flex flex-col space-y-4">
                            <div class="text-center font-semibold text-blue-700 mb-2 py-2">&nbsp;</div>
                            ${Array.from({length: 8}, (_, i) => `
                                <div class="flex items-center justify-center h-16">
                                    <div class="w-8 border-t-2 border-blue-400 relative">
                                        <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-blue-400 border-y-4 border-y-transparent"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Quarter Finals -->
                        <div class="round flex flex-col space-y-4">
                            <div class="text-center font-semibold text-purple-700 mb-2 bg-purple-50 py-2 rounded-lg">Quarter Finals</div>
                            ${Array.from({length: 4}, (_, i) => `
                                <div class="match bg-white border-2 border-purple-300 rounded-lg p-3 min-w-[180px]">
                                    <div class="text-xs text-gray-500 text-center mb-1">QF ${i + 1}</div>
                                    <div class="team bg-purple-50 border-b border-purple-200 px-2 py-1 text-xs font-medium rounded-t">
                                        Winner M${i * 2 + 1}
                                    </div>
                                    <div class="team bg-purple-50 px-2 py-1 text-xs font-medium rounded-b">
                                        Winner M${i * 2 + 2}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Connector -->
                        <div class="connector flex flex-col space-y-4">
                            <div class="text-center font-semibold text-purple-700 mb-2 py-2">&nbsp;</div>
                            ${Array.from({length: 4}, (_, i) => `
                                <div class="flex items-center justify-center h-16">
                                    <div class="w-8 border-t-2 border-purple-400 relative">
                                        <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-purple-400 border-y-4 border-y-transparent"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Semi Finals -->
                        <div class="round flex flex-col space-y-4">
                            <div class="text-center font-semibold text-red-700 mb-2 bg-red-50 py-2 rounded-lg">Semi Finals</div>
                            ${Array.from({length: 2}, (_, i) => `
                                <div class="match bg-white border-2 border-red-300 rounded-lg p-3 min-w-[180px]">
                                    <div class="text-xs text-gray-500 text-center mb-1">SF ${i + 1}</div>
                                    <div class="team bg-red-50 border-b border-red-200 px-2 py-1 text-xs font-medium rounded-t">
                                        Winner QF${i * 2 + 1}
                                    </div>
                                    <div class="team bg-red-50 px-2 py-1 text-xs font-medium rounded-b">
                                        Winner QF${i * 2 + 2}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Connector -->
                        <div class="connector flex flex-col space-y-4">
                            <div class="text-center font-semibold text-red-700 mb-2 py-2">&nbsp;</div>
                            ${Array.from({length: 2}, (_, i) => `
                                <div class="flex items-center justify-center h-16">
                                    <div class="w-8 border-t-2 border-red-400 relative">
                                        <div class="absolute -right-2 top-1/2 transform -translate-y-1/2 w-0 h-0 border-l-4 border-l-red-400 border-y-4 border-y-transparent"></div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                        
                        <!-- Final -->
                        <div class="round flex flex-col space-y-4">
                            <div class="text-center font-semibold text-yellow-700 mb-2 bg-yellow-50 py-2 rounded-lg">Final</div>
                            <div class="match bg-white border-2 border-yellow-300 rounded-lg p-3 min-w-[180px]">
                                <div class="text-xs text-gray-500 text-center mb-1">CHAMPIONSHIP</div>
                                <div class="team bg-yellow-50 border-b border-yellow-200 px-2 py-1 text-xs font-medium rounded-t">
                                    Winner SF 1
                                </div>
                                <div class="team bg-yellow-50 px-2 py-1 text-xs font-medium rounded-b">
                                    Winner SF 2
                                </div>
                            </div>
                        </div>
                        
                        <!-- Champion -->
                        <div class="champion-section flex flex-col justify-center items-center space-y-4 ml-4">
                            <div class="text-center font-semibold text-green-700 mb-2 bg-green-50 py-2 px-3 rounded-lg">Champion</div>
                            <div class="champion-trophy bg-gradient-to-br from-yellow-400 to-yellow-600 border-2 border-yellow-500 rounded-lg p-4 min-w-[160px] text-center shadow-lg">
                                <i class="fas fa-trophy text-white text-xl mb-2"></i>
                                <div class="team bg-white border border-yellow-300 rounded px-2 py-2 text-xs font-bold text-gray-800">
                                    🏆 Winner 🏆
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Tournament Statistics
    bracketHTML += `
        <div class="mt-12 border-t pt-8">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800">Tournament Overview</h4>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600">41</div>
                    <div class="text-sm text-blue-700">Total Teams</div>
                </div>
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600">9</div>
                    <div class="text-sm text-orange-700">Total Groups</div>
                </div>
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">16</div>
                    <div class="text-sm text-green-700">Knockout Teams</div>
                </div>
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600">71</div>
                    <div class="text-sm text-purple-700">Total Matches</div>
                </div>
            </div>
            
            ${buildTeamSeedingSection(randomizedTeams)}
        </div>
    `;
    
    return bracketHTML;
}

// Other bracket building functions
function buildDoubleEliminationBracket(teams) {
    const randomizedTeams = shuffleArray(teams);
    return `
        <div class="text-center py-12">
            <i class="fas fa-layer-group text-4xl text-purple-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Double Elimination Bracket</h3>
            <p class="text-gray-600 mb-6">${randomizedTeams.length} teams will compete in double elimination format</p>
            <div class="mt-2 text-sm text-purple-600 font-medium mb-6">
                <i class="fas fa-random mr-1"></i>Automatically Randomized Team Seeding
            </div>
            ${buildTeamSeedingSection(randomizedTeams)}
        </div>
    `;
}

function buildRoundRobinBracket(teams) {
    const randomizedTeams = shuffleArray(teams);
    return `
        <div class="text-center py-12">
            <i class="fas fa-infinity text-4xl text-green-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Round Robin Bracket</h3>
            <p class="text-gray-600 mb-6">${randomizedTeams.length} teams will compete in round robin format</p>
            <div class="mt-2 text-sm text-purple-600 font-medium mb-6">
                <i class="fas fa-random mr-1"></i>Automatically Randomized Team Seeding
            </div>
            ${buildTeamSeedingSection(randomizedTeams)}
        </div>
    `;
}

function buildMarchMadnessBracket(teams) {
    const randomizedTeams = shuffleArray(teams);
    return `
        <div class="text-center py-12">
            <i class="fas fa-basketball-ball text-4xl text-red-500 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">March Madness Bracket</h3>
            <p class="text-gray-600 mb-6">${randomizedTeams.length} teams will compete in March Madness format</p>
            <div class="mt-2 text-sm text-purple-600 font-medium mb-6">
                <i class="fas fa-random mr-1"></i>Automatically Randomized Team Seeding
            </div>
            ${buildTeamSeedingSection(randomizedTeams)}
        </div>
    `;
}

// Common team seeding section for other bracket types
function buildTeamSeedingSection(teams) {
    return `
        <div class="mt-8 border-t pt-8">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-xl font-bold text-gray-800">Randomized Team Positions</h4>
                <span class="text-sm text-gray-500 bg-purple-100 px-3 py-1 rounded-full">
                    <i class="fas fa-random mr-1"></i>Auto Randomized
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                ${teams.map((team, index) => `
                    <div class="bg-white border border-purple-200 rounded-lg p-3 text-center shadow-xs">
                        <div class="text-xs text-purple-600 font-medium mb-1">Position ${index + 1}</div>
                        <div class="font-semibold text-gray-800 text-sm">${team.barangay}</div>
                        ${team.coach_username ? `<div class="text-xs text-gray-500 mt-1">Coach: ${team.coach_username}</div>` : ''}
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}
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

.bracket-container {
    transform: scale(0.9);
    transform-origin: top center;
}

.round {
    min-height: 500px;
}

.match {
    transition: all 0.2s ease;
}

.match:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.connector {
    min-height: 500px;
}

.champion-trophy {
    position: relative;
    overflow: hidden;
}

.champion-trophy::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    transform: rotate(45deg);
    animation: shine 3s infinite;
}

@keyframes shine {
    0% { transform: rotate(45deg) translateX(-100%); }
    100% { transform: rotate(45deg) translateX(100%); }
}

.champion-section {
    min-height: 500px;
}
</style>

<?php include 'includes/footer.php'; ?>