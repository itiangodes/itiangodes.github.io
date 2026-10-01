<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- Search and Filter Section -->
<section class="bg-white rounded-xl shadow-md p-6 mb-8">
    <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
        <div class="w-full md:w-auto">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-search text-blue-600 mr-2"></i>Find Teams
            </h2>
            <div class="relative">
                <input type="text" id="team-search" placeholder="Search teams by name or barangay..." 
                       class="w-full md:w-80 px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
        
        <div class="w-full md:w-auto">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Filter by Standing</h3>
            <select id="standing-filter" class="w-full md:w-48 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <option value="all">All Standings</option>
                <option value="1st">1st Place</option>
                <option value="2nd">2nd Place</option>
                <option value="3rd">3rd Place</option>
                <option value="4th-6th">4th-6th Place</option>
            </select>
        </div>
    </div>
</section>

<!-- Tournament Standings Section -->
<section class="bg-gradient-to-r from-blue-900 to-blue-800 rounded-xl shadow-lg p-6 mb-8 text-white">
    <h2 class="text-2xl font-bold mb-6 flex items-center">
        <i class="fas fa-trophy mr-3"></i>Tournament Standings
    </h2>
    
    <div class="overflow-x-auto">
        <table class="w-full text-white">
            <thead>
                <tr class="border-b border-blue-700">
                    <th class="py-3 px-4 text-left font-semibold">Rank</th>
                    <th class="py-3 px-4 text-left font-semibold">Team</th>
                    <th class="py-3 px-4 text-center font-semibold">W-L</th>
                    <th class="py-3 px-4 text-center font-semibold">Win %</th>
                    <th class="py-3 px-4 text-center font-semibold">Streak</th>
                </tr>
            </thead>
            <tbody id="standings-table">
                <!-- Standings will be dynamically populated -->
            </tbody>
        </table>
    </div>
</section>

<!-- All Teams Section -->
<section id="view-teams" class="bg-white rounded-xl shadow-md p-6 mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-users text-blue-600 mr-2"></i>All Barangay Teams
        </h2>
        <div class="text-sm text-gray-500" id="teams-count">
            6 teams
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="teams-container">
        <!-- Team 1 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="punta_i_warriors" data-standing="1st" data-search="punta i warriors barangay punta i">
            <div class="w-20 h-20 mx-auto mb-4 bg-blue-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                PI
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Punta I Warriors</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Punta I</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">12-2</span>
                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">1st Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="punta_i_warriors">
                View Details →
            </button>
        </div>

        <!-- Team 2 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="amaya_titans" data-standing="2nd" data-search="amaya titans barangay amaya">
            <div class="w-20 h-20 mx-auto mb-4 bg-red-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                AM
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Amaya Titans</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Amaya</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">10-4</span>
                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">2nd Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="amaya_titans">
                View Details →
            </button>
        </div>

        <!-- Team 3 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="bunga_bulls" data-standing="3rd" data-search="bunga bulls barangay bunga">
            <div class="w-20 h-20 mx-auto mb-4 bg-green-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                BU
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Bunga Bulls</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Bunga</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">9-5</span>
                <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">3rd Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="bunga_bulls">
                View Details →
            </button>
        </div>

        <!-- Team 4 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="lambingan_lions" data-standing="4th-6th" data-search="lambingan lions barangay lambingan">
            <div class="w-20 h-20 mx-auto mb-4 bg-purple-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                LA
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Lambingan Lions</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Lambingan</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">8-6</span>
                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">4th Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="lambingan_lions">
                View Details →
            </button>
        </div>

        <!-- Team 5 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="sahud_ulan_storms" data-standing="4th-6th" data-search="sahud ulan storms barangay sahud ulan">
            <div class="w-20 h-20 mx-auto mb-4 bg-yellow-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                SU
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Sahud Ulan Storms</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Sahud Ulan</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">7-7</span>
                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">5th Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="sahud_ulan_storms">
                View Details →
            </button>
        </div>

        <!-- Team 6 -->
        <div class="team-card border border-gray-200 rounded-lg p-6 text-center hover:shadow-lg transition" data-team="biwas_blazers" data-standing="4th-6th" data-search="biwas blazers barangay biwas">
            <div class="w-20 h-20 mx-auto mb-4 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                BI
            </div>
            <h3 class="font-bold text-lg text-gray-800 mb-2">Biwas Blazers</h3>
            <p class="text-gray-600 text-sm mb-3">Barangay Biwas</p>
            <div class="flex justify-center space-x-2 mb-3">
                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">6-8</span>
                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">6th Place</span>
            </div>
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium view-details-btn" data-team="biwas_blazers">
                View Details →
            </button>
        </div>
    </div>
    
    <!-- No Results Message -->
    <div id="no-results" class="hidden text-center py-12">
        <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
        <h3 class="text-xl font-bold text-gray-700 mb-2">No Teams Found</h3>
        <p class="text-gray-500">Try adjusting your search or filter criteria</p>
    </div>
</section>

<!-- Team Players Modal -->
<div id="team-players-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-6xl w-full max-h-[90vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-800 text-white p-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold flex items-center">
                <i class="fas fa-users mr-3"></i>
                <span id="modal-team-name">Team Players</span>
            </h2>
            <button id="close-modal" class="text-white hover:text-gray-200 text-2xl font-bold">
                &times;
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
            <div id="players-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Player cards will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    const teamPlayersModal = document.getElementById('team-players-modal');
    const playersContainer = document.getElementById('players-container');
    const modalTeamName = document.getElementById('modal-team-name');
    const closeModalButton = document.getElementById('close-modal');
    
    // Search and Filter Elements
    const teamSearch = document.getElementById('team-search');
    const standingFilter = document.getElementById('standing-filter');
    const teamCards = document.querySelectorAll('.team-card');
    const teamsContainer = document.getElementById('teams-container');
    const noResults = document.getElementById('no-results');
    const teamsCount = document.getElementById('teams-count');
    const standingsTable = document.getElementById('standings-table');

    // Tournament Standings Data
    const standingsData = [
        {
            rank: 1,
            team: 'Punta I Warriors',
            wins: 12,
            losses: 2,
            winPercentage: '85.7%',
            streak: 'W4',
            record: '12-2'
        },
        {
            rank: 2,
            team: 'Amaya Titans',
            wins: 10,
            losses: 4,
            winPercentage: '71.4%',
            streak: 'W2',
            record: '10-4'
        },
        {
            rank: 3,
            team: 'Bunga Bulls',
            wins: 9,
            losses: 5,
            winPercentage: '64.3%',
            streak: 'L1',
            record: '9-5'
        },
        {
            rank: 4,
            team: 'Lambingan Lions',
            wins: 8,
            losses: 6,
            winPercentage: '57.1%',
            streak: 'W1',
            record: '8-6'
        },
        {
            rank: 5,
            team: 'Sahud Ulan Storms',
            wins: 7,
            losses: 7,
            winPercentage: '50.0%',
            streak: 'L2',
            record: '7-7'
        },
        {
            rank: 6,
            team: 'Biwas Blazers',
            wins: 6,
            losses: 8,
            winPercentage: '42.9%',
            streak: 'W1',
            record: '6-8'
        }
    ];

    // Initialize standings table
    populateStandingsTable();

    // Team players data
    const teamsData = {
        'punta_i_warriors': {
            name: 'Punta I Warriors',
            players: [
                { 
                    number: '23', 
                    name: 'sda dasda', 
                    position: 'SF',
                    stats: {
                        ppg: '15.0',
                        apg: '7.0',
                        rpg: '3.5'
                    }
                },
                { 
                    number: '5', 
                    name: 'getgw dgdg', 
                    position: 'PG',
                    stats: {
                        ppg: '10.2',
                        apg: '8.5',
                        rpg: '2.1'
                    }
                },
                { 
                    number: '34', 
                    name: 'thtj hdh', 
                    position: 'C',
                    stats: {
                        ppg: '8.5',
                        apg: '1.2',
                        rpg: '9.8'
                    }
                },
                { 
                    number: '11', 
                    name: 'hresd hdfhd', 
                    position: 'SG',
                    stats: {
                        ppg: '12.3',
                        apg: '3.2',
                        rpg: '4.1'
                    }
                }
            ]
        },
        'amaya_titans': {
            name: 'Amaya Titans',
            players: [
                { 
                    number: '10', 
                    name: 'Player One', 
                    position: 'PG',
                    stats: {
                        ppg: '12.5',
                        apg: '4.2',
                        rpg: '3.1'
                    }
                },
                { 
                    number: '24', 
                    name: 'Player Two', 
                    position: 'SF',
                    stats: {
                        ppg: '8.7',
                        apg: '2.3',
                        rpg: '6.8'
                    }
                },
                { 
                    number: '15', 
                    name: 'Player Three', 
                    position: 'C',
                    stats: {
                        ppg: '6.8',
                        apg: '1.1',
                        rpg: '8.9'
                    }
                },
                { 
                    number: '8', 
                    name: 'Player Four', 
                    position: 'SG',
                    stats: {
                        ppg: '14.2',
                        apg: '2.8',
                        rpg: '3.5'
                    }
                },
                { 
                    number: '33', 
                    name: 'Player Five', 
                    position: 'PF',
                    stats: {
                        ppg: '7.9',
                        apg: '1.5',
                        rpg: '7.2'
                    }
                }
            ]
        },
        'bunga_bulls': {
            name: 'Bunga Bulls',
            players: [
                { 
                    number: '7', 
                    name: 'Bull Player 1', 
                    position: 'C',
                    stats: {
                        ppg: '9.8',
                        apg: '3.4',
                        rpg: '4.2'
                    }
                },
                { 
                    number: '12', 
                    name: 'Bull Player 2', 
                    position: 'PG',
                    stats: {
                        ppg: '11.2',
                        apg: '6.8',
                        rpg: '2.9'
                    }
                },
                { 
                    number: '21', 
                    name: 'Bull Player 3', 
                    position: 'SF',
                    stats: {
                        ppg: '13.5',
                        apg: '2.7',
                        rpg: '5.1'
                    }
                }
            ]
        },
        'lambingan_lions': {
            name: 'Lambingan Lions',
            players: [
                { 
                    number: '33', 
                    name: 'Lion Player 1', 
                    position: 'SF',
                    stats: {
                        ppg: '13.6',
                        apg: '4.8',
                        rpg: '3.9'
                    }
                },
                { 
                    number: '9', 
                    name: 'Lion Player 2', 
                    position: 'PG',
                    stats: {
                        ppg: '10.8',
                        apg: '7.2',
                        rpg: '2.8'
                    }
                },
                { 
                    number: '44', 
                    name: 'Lion Player 3', 
                    position: 'C',
                    stats: {
                        ppg: '8.2',
                        apg: '1.3',
                        rpg: '8.5'
                    }
                }
            ]
        },
        'sahud_ulan_storms': {
            name: 'Sahud Ulan Storms',
            players: [
                { 
                    number: '8', 
                    name: 'Storm Player 1', 
                    position: 'SG',
                    stats: {
                        ppg: '10.4',
                        apg: '3.2',
                        rpg: '4.1'
                    }
                },
                { 
                    number: '3', 
                    name: 'Storm Player 2', 
                    position: 'PG',
                    stats: {
                        ppg: '9.7',
                        apg: '5.6',
                        rpg: '2.4'
                    }
                },
                { 
                    number: '25', 
                    name: 'Storm Player 3', 
                    position: 'PF',
                    stats: {
                        ppg: '7.8',
                        apg: '1.8',
                        rpg: '6.9'
                    }
                }
            ]
        },
        'biwas_blazers': {
            name: 'Biwas Blazers',
            players: [
                { 
                    number: '3', 
                    name: 'Blazer Player 1', 
                    position: 'PG',
                    stats: {
                        ppg: '15.7',
                        apg: '6.3',
                        rpg: '3.4'
                    }
                },
                { 
                    number: '14', 
                    name: 'Blazer Player 2', 
                    position: 'SG',
                    stats: {
                        ppg: '11.9',
                        apg: '2.5',
                        rpg: '3.8'
                    }
                },
                { 
                    number: '32', 
                    name: 'Blazer Player 3', 
                    position: 'PF',
                    stats: {
                        ppg: '8.4',
                        apg: '1.9',
                        rpg: '7.1'
                    }
                }
            ]
        }
    };

    // Populate standings table
    function populateStandingsTable() {
        standingsTable.innerHTML = '';
        
        standingsData.forEach(team => {
            const row = document.createElement('tr');
            row.className = 'border-b border-blue-700 hover:bg-blue-700 transition';
            
            // Determine streak color
            const streakColor = team.streak.startsWith('W') ? 'text-green-300' : 'text-red-300';
            
            row.innerHTML = `
                <td class="py-4 px-4">
                    <div class="flex items-center">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            ${team.rank === 1 ? 'bg-yellow-500 text-white' : 
                              team.rank === 2 ? 'bg-gray-400 text-white' : 
                              team.rank === 3 ? 'bg-orange-600 text-white' : 
                              'bg-blue-600 text-white'}">
                            ${team.rank}
                        </span>
                    </div>
                </td>
                <td class="py-4 px-4 font-medium">${team.team}</td>
                <td class="py-4 px-4 text-center">${team.record}</td>
                <td class="py-4 px-4 text-center">${team.winPercentage}</td>
                <td class="py-4 px-4 text-center ${streakColor} font-semibold">${team.streak}</td>
            `;
            
            standingsTable.appendChild(row);
        });
    }

    // Search and Filter functionality
    function filterTeams() {
        const searchTerm = teamSearch.value.toLowerCase();
        const standingValue = standingFilter.value;
        
        let visibleCount = 0;
        
        teamCards.forEach(card => {
            const searchData = card.getAttribute('data-search').toLowerCase();
            const standing = card.getAttribute('data-standing');
            
            const matchesSearch = searchData.includes(searchTerm);
            const matchesStanding = standingValue === 'all' || standing === standingValue;
            
            if (matchesSearch && matchesStanding) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        if (visibleCount === 0) {
            noResults.classList.remove('hidden');
            teamsContainer.classList.add('hidden');
        } else {
            noResults.classList.add('hidden');
            teamsContainer.classList.remove('hidden');
        }
        
        // Update teams count
        teamsCount.textContent = `${visibleCount} team${visibleCount !== 1 ? 's' : ''}`;
    }

    // Event listeners for search and filter
    teamSearch.addEventListener('input', filterTeams);
    standingFilter.addEventListener('change', filterTeams);

    // View Details button click handler
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const teamId = this.getAttribute('data-team');
            const teamData = teamsData[teamId];
            
            if (teamData) {
                // Update modal team name
                modalTeamName.textContent = teamData.name + ' Players';
                
                // Clear previous players
                playersContainer.innerHTML = '';
                
                // Add player cards
                teamData.players.forEach(player => {
                    const playerCard = document.createElement('div');
                    playerCard.className = 'player-card bg-white rounded-2xl p-6 shadow-lg hover:shadow-2xl transition-all duration-300 cursor-pointer hover:-translate-y-1 border border-gray-100';
                    
                    playerCard.innerHTML = `
                        <!-- Player Header with Jersey Number and Position -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-2xl font-bold text-gray-800">#${player.number || '0'}</div>
                            <div class="text-xs font-semibold bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                ${player.position || 'N/A'}
                            </div>
                        </div>
                        
                        <!-- Player Profile Image -->
                        <div class="flex justify-center mb-4">
                            <div class="w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-200 bg-gray-100 shadow-sm">
                                <img 
                                    src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+" 
                                    alt="${player.name}" 
                                    class="w-full h-full object-cover"
                                >
                            </div>
                        </div>
                        
                        <!-- Player Name -->
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900">${player.name}</h3>
                        </div>
                        
                        <!-- Stats Grid -->
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-900">${player.stats.ppg}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">PPG</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-900">${player.stats.apg}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">APG</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-900">${player.stats.rpg}</div>
                                <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">RPG</div>
                            </div>
                        </div>
                    `;
                    
                    playersContainer.appendChild(playerCard);
                });
                
                // Show modal
                teamPlayersModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        });
    });
    
    // Close modal button click handler
    closeModalButton.addEventListener('click', function() {
        teamPlayersModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    });
    
    // Close modal when clicking outside the content
    teamPlayersModal.addEventListener('click', function(e) {
        if (e.target === teamPlayersModal) {
            teamPlayersModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !teamPlayersModal.classList.contains('hidden')) {
            teamPlayersModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });
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

.hidden {
    display: none;
}

#team-players-modal {
    backdrop-filter: blur(5px);
}

/* Smooth transitions for search and filter */
.team-card {
    transition: all 0.3s ease;
}

/* Standings table hover effects */
#standings-table tr {
    transition: background-color 0.2s ease;
}
</style>

<?php include 'includes/footer.php'; ?>