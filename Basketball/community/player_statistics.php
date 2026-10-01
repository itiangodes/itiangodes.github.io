<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<style>
    .stats-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
    }

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .filter-input {
        width: 100%;
        padding: 12px 18px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }

    .filter-input:focus {
        outline: none;
        border-color: #7c3aed;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
    }

    /* Stats Category Tabs */
    .stats-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        flex-wrap: wrap;
    }

    .stat-tab {
        flex: 1;
        min-width: 150px;
        padding: 20px;
        background: white;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 3px solid transparent;
    }

    .stat-tab:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
    }

    .stat-tab.active {
        border-color: #7c3aed;
        background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(109, 40, 217, 0.05));
    }

    .stat-tab-icon {
        width: 50px;
        height: 50px;
        margin: 0 auto 10px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-tab-label {
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        font-size: 0.9rem;
    }

    /* Players Table */
    .players-table {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .players-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .players-table thead {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        color: white;
    }

    .players-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .players-table td {
        padding: 15px;
        border-bottom: 1px solid #f1f5f9;
    }

    .players-table tbody tr:hover {
        background: #f8fafc;
    }

    .rank-cell {
        font-weight: 700;
        font-size: 1.1rem;
        color: #7c3aed;
        width: 60px;
    }

    .rank-badge {
        display: inline-block;
        width: 35px;
        height: 35px;
        line-height: 35px;
        text-align: center;
        border-radius: 8px;
        font-weight: 700;
    }

    .rank-1 { background: linear-gradient(135deg, #ffd700, #ffed4e); color: #000; }
    .rank-2 { background: linear-gradient(135deg, #c0c0c0, #e8e8e8); color: #000; }
    .rank-3 { background: linear-gradient(135deg, #cd7f32, #e59a5d); color: #fff; }

    .player-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .player-avatar {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: white;
        font-size: 0.9rem;
    }

    .player-details {
        flex: 1;
    }

    .player-name {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .player-team {
        font-size: 0.85rem;
        color: #64748b;
    }

    .stat-value {
        font-size: 1.3rem;
        font-weight: 700;
        color: #7c3aed;
    }

    /* Summary Cards */
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .summary-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border-left: 4px solid;
    }

    .summary-card.ppg { border-color: #f59e0b; }
    .summary-card.apg { border-color: #3b82f6; }
    .summary-card.rpg { border-color: #10b981; }

    .summary-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-size: 1.5rem;
        color: white;
    }

    /* Player Cards Grid - Updated to match view_teams.php */
    .player-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-top: 30px;
    }

    .player-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.04);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .player-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    }

    .player-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #7c3aed, #6d28d9);
    }

    .player-card:hover::before {
        background: linear-gradient(90deg, #6d28d9, #7c3aed);
    }

    .player-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .player-card-number {
        font-size: 28px;
        font-weight: 800;
        color: #1e293b;
    }

    .player-card-position {
        font-size: 12px;
        font-weight: 600;
        background: #f1f5f9;
        color: #64748b;
        padding: 6px 12px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .player-card-profile {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .player-card-avatar {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #f1f5f9;
        background: #f8fafc;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }

    .player-card-name {
        text-align: center;
        margin-bottom: 24px;
    }

    .player-card-name h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .player-card-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        text-align: center;
    }

    .player-card-stat-value {
        font-size: 24px;
        font-weight: 800;
        color: #1e3a8a;
        margin-bottom: 4px;
    }

    .player-card-stat-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-tabs {
            flex-direction: column;
        }

        .stat-tab {
            min-width: 100%;
        }

        .players-table {
            overflow-x: auto;
        }

        .players-table table {
            min-width: 700px;
        }

        .player-cards-grid {
            grid-template-columns: 1fr;
        }

        .player-card-stats {
            grid-template-columns: repeat(3, 1fr);
        }
    }
</style>

<div class="stats-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-chart-line mr-2"></i>Player Statistics Browser
        </h1>
        <p class="text-purple-100">Browse all players by team - View PPG, APG, and RPG statistics</p>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <h3 class="font-bold text-gray-800 mb-3">
            <i class="fas fa-filter mr-2"></i>Filter Players
        </h3>
        <div class="filter-grid">
            <input type="text" class="filter-input" placeholder="🔍 Search by player name..." id="searchPlayer">
            
            <select class="filter-input" id="filterTeam">
                <option value="">All Teams</option>
                <option value="Punta I Warriors">Punta I Warriors</option>
                <option value="Amaya Titans">Amaya Titans</option>
                <option value="Punta II Knights">Punta II Knights</option>
                <option value="Poblace Stars">Poblace Stars</option>
                <option value="Bucal Rangers">Bucal Rangers</option>
                <option value="Sahud Ulan Thunder">Sahud Ulan Thunder</option>
                <option value="Bagbag Eagles">Bagbag Eagles</option>
                <option value="Balaas Lions">Balaas Lions</option>
            </select>

            <button class="filter-input" style="background: #7c3aed; color: white; border-color: #7c3aed; cursor: pointer; font-weight: 600;" onclick="resetFilters()">
                <i class="fas fa-sync-alt mr-2"></i>Reset Filters
            </button>
        </div>
    </div>

    <!-- Stats Category Tabs -->
    <div class="stats-tabs" id="statsTabs">
        <div class="stat-tab active" data-stat="ppg">
            <div class="stat-tab-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-basketball-ball"></i>
            </div>
            <div class="stat-tab-label">Points Per Game</div>
            <div style="text-align: center; font-size: 0.8rem; color: #64748b; margin-top: 5px;">PPG</div>
        </div>

        <div class="stat-tab" data-stat="apg">
            <div class="stat-tab-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div class="stat-tab-label">Assists Per Game</div>
            <div style="text-align: center; font-size: 0.8rem; color: #64748b; margin-top: 5px;">APG</div>
        </div>

        <div class="stat-tab" data-stat="rpg">
            <div class="stat-tab-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-hands"></i>
            </div>
            <div class="stat-tab-label">Rebounds Per Game</div>
            <div style="text-align: center; font-size: 0.8rem; color: #64748b; margin-top: 5px;">RPG</div>
        </div>
    </div>

    <!-- League Leaders Summary -->
    <div class="summary-grid" id="summaryGrid">
        <div class="summary-card ppg">
            <div class="summary-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <i class="fas fa-basketball-ball"></i>
            </div>
            <div class="text-sm text-gray-600 mb-1">Highest PPG</div>
            <div class="text-2xl font-bold text-gray-800">John Doe</div>
            <div class="text-lg font-semibold" style="color: #f59e0b;">28.5 PPG</div>
            <div class="text-xs text-gray-500 mt-1">Punta I Warriors</div>
        </div>

        <div class="summary-card apg">
            <div class="summary-icon" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <i class="fas fa-hand-holding"></i>
            </div>
            <div class="text-sm text-gray-600 mb-1">Highest APG</div>
            <div class="text-2xl font-bold text-gray-800">Carlos Perez</div>
            <div class="text-lg font-semibold" style="color: #3b82f6;">9.5 APG</div>
            <div class="text-xs text-gray-500 mt-1">Punta II Knights</div>
        </div>

        <div class="summary-card rpg">
            <div class="summary-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fas fa-hands"></i>
            </div>
            <div class="text-sm text-gray-600 mb-1">Highest RPG</div>
            <div class="text-2xl font-bold text-gray-800">Miguel Villar</div>
            <div class="text-lg font-semibold" style="color: #10b981;">12.8 RPG</div>
            <div class="text-xs text-gray-500 mt-1">Amaya Titans</div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="players-table" id="playersTable">
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player</th>
                    <th>Team</th>
                    <th>Games</th>
                    <th>PPG</th>
                    <th>Total Points</th>
                </tr>
            </thead>
            <tbody>
                <!-- Player 1 -->
                <tr data-team="Punta I Warriors">
                    <td class="rank-cell">
                        <span class="rank-badge rank-1">1</span>
                    </td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">JD</div>
                            <div class="player-details">
                                <div class="player-name">John Doe</div>
                                <div class="player-team">PG</div>
                            </div>
                        </div>
                    </td>
                    <td>Punta I Warriors</td>
                    <td>24</td>
                    <td><span class="stat-value">28.5</span></td>
                    <td>684</td>
                </tr>

                <!-- Player 2 -->
                <tr data-team="Amaya Titans">
                    <td class="rank-cell">
                        <span class="rank-badge rank-2">2</span>
                    </td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #ef4444, #dc2626);">MS</div>
                            <div class="player-details">
                                <div class="player-name">Mark Santos</div>
                                <div class="player-team">SF</div>
                            </div>
                        </div>
                    </td>
                    <td>Amaya Titans</td>
                    <td>25</td>
                    <td><span class="stat-value">26.3</span></td>
                    <td>658</td>
                </tr>

                <!-- Player 3 -->
                <tr data-team="Bagbag Eagles">
                    <td class="rank-cell">
                        <span class="rank-badge rank-3">3</span>
                    </td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #ec4899, #db2777);">PR</div>
                            <div class="player-details">
                                <div class="player-name">Paolo Reyes</div>
                                <div class="player-team">SG</div>
                            </div>
                        </div>
                    </td>
                    <td>Bagbag Eagles</td>
                    <td>23</td>
                    <td><span class="stat-value">24.8</span></td>
                    <td>570</td>
                </tr>

                <!-- Player 4 -->
                <tr data-team="Punta II Knights">
                    <td class="rank-cell">4</td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">RC</div>
                            <div class="player-details">
                                <div class="player-name">Rico Cruz</div>
                                <div class="player-team">PF</div>
                            </div>
                        </div>
                    </td>
                    <td>Punta II Knights</td>
                    <td>24</td>
                    <td><span class="stat-value">23.2</span></td>
                    <td>557</td>
                </tr>

                <!-- Player 5 -->
                <tr data-team="Poblace Stars">
                    <td class="rank-cell">5</td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #f59e0b, #d97706);">AL</div>
                            <div class="player-details">
                                <div class="player-name">Alex Lopez</div>
                                <div class="player-team">C</div>
                            </div>
                        </div>
                    </td>
                    <td>Poblace Stars</td>
                    <td>22</td>
                    <td><span class="stat-value">22.1</span></td>
                    <td>486</td>
                </tr>

                <!-- Player 6 -->
                <tr data-team="Bucal Rangers">
                    <td class="rank-cell">6</td>
                    <td>
                        <div class="player-cell">
                            <div class="player-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">EM</div>
                            <div class="player-details">
                                <div class="player-name">Edwin Martinez</div>
                                <div class="player-team">SF</div>
                            </div>
                        </div>
                    </td>
                    <td>Bucal Rangers</td>
                    <td>21</td>
                    <td><span class="stat-value">21.5</span></td>
                    <td>452</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Player Cards Section -->
    <h2 class="text-2xl font-bold mb-6 text-gray-800 flex items-center">
        <i class="fas fa-id-card mr-3 text-purple-600"></i>All Players
    </h2>
    <div class="player-cards-grid" id="playerCardsContainer">
        <!-- Player cards will be dynamically inserted here -->
    </div>
</div>

<script>
    // Player data
    const playersData = {
        'John Doe': {
            number: '23',
            name: 'John Doe',
            position: 'PG',
            team: 'Punta I Warriors',
            games: 24,
            ppg: 28.5,
            apg: 5.2,
            rpg: 3.8,
            totalPoints: 684,
            totalAssists: 125,
            totalRebounds: 91,
            avatarBg: 'linear-gradient(135deg, #3b82f6, #2563eb)',
            initials: 'JD'
        },
        'Mark Santos': {
            number: '24',
            name: 'Mark Santos',
            position: 'SF',
            team: 'Amaya Titans',
            games: 25,
            ppg: 26.3,
            apg: 3.2,
            rpg: 7.1,
            totalPoints: 658,
            totalAssists: 80,
            totalRebounds: 178,
            avatarBg: 'linear-gradient(135deg, #ef4444, #dc2626)',
            initials: 'MS'
        },
        'Paolo Reyes': {
            number: '8',
            name: 'Paolo Reyes',
            position: 'SG',
            team: 'Bagbag Eagles',
            games: 23,
            ppg: 24.8,
            apg: 7.2,
            rpg: 4.1,
            totalPoints: 570,
            totalAssists: 166,
            totalRebounds: 94,
            avatarBg: 'linear-gradient(135deg, #ec4899, #db2777)',
            initials: 'PR'
        },
        'Rico Cruz': {
            number: '15',
            name: 'Rico Cruz',
            position: 'PF',
            team: 'Punta II Knights',
            games: 24,
            ppg: 23.2,
            apg: 4.3,
            rpg: 6.8,
            totalPoints: 557,
            totalAssists: 103,
            totalRebounds: 163,
            avatarBg: 'linear-gradient(135deg, #8b5cf6, #7c3aed)',
            initials: 'RC'
        },
        'Alex Lopez': {
            number: '6',
            name: 'Alex Lopez',
            position: 'C',
            team: 'Poblace Stars',
            games: 22,
            ppg: 22.1,
            apg: 2.3,
            rpg: 11.5,
            totalPoints: 486,
            totalAssists: 51,
            totalRebounds: 253,
            avatarBg: 'linear-gradient(135deg, #f59e0b, #d97706)',
            initials: 'AL'
        },
        'Edwin Martinez': {
            number: '12',
            name: 'Edwin Martinez',
            position: 'SF',
            team: 'Bucal Rangers',
            games: 21,
            ppg: 21.5,
            apg: 3.7,
            rpg: 8.9,
            totalPoints: 452,
            totalAssists: 78,
            totalRebounds: 187,
            avatarBg: 'linear-gradient(135deg, #10b981, #059669)',
            initials: 'EM'
        }
    };

    // Generate player cards on page load
    document.addEventListener('DOMContentLoaded', function() {
        generatePlayerCards();
    });

    function generatePlayerCards() {
        const container = document.getElementById('playerCardsContainer');
        container.innerHTML = '';
        
        Object.values(playersData).forEach(player => {
            const playerCard = document.createElement('div');
            playerCard.className = 'player-card';
            playerCard.setAttribute('data-team', player.team);
            
            playerCard.innerHTML = `
                <!-- Player Header with Jersey Number and Position -->
                <div class="player-card-header">
                    <div class="player-card-number">#${player.number}</div>
                    <div class="player-card-position">${player.position}</div>
                </div>
                
                <!-- Player Profile Image -->
                <div class="player-card-profile">
                    <div class="player-card-avatar">
                        <img 
                            src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik00MCA0MEM0Ni4wNzI0IDQwIDUxIDM1LjA3MjQgNTEgMjlDNTEgMjIuOTI3NiA0Ni4wNzI0IDE4IDQwIDE4QzMzLjkyNzYgMTggMjkgMjIuOTI3NiAyOSAyOUMyOSAzNS4wNzI0IDMzLjkyNzYgNDAgNDAgNDBaIiBmaWxsPSIjOEU5MEEwIi8+CjxwYXRoIGQ9Ik00MCA0M0M0Ny4xNzkgNDMgNTMgNDcuODIxIDUzIDU1VjU3QzUzIDU4LjEwNDYgNTIuMTA0NiA1OSA1MSA1OUgyOUMyNy44OTU0IDU5IDI3IDU4LjEwNDYgMjcgNTdWNTVDMjcgNDcuODIxIDMyLjgyMSA0MyA0MCA0M1oiIGZpbGw9IiM4RTkwQTAiLz4KPC9zdmc+" 
                            alt="${player.name}" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                </div>
                
                <!-- Player Name -->
                <div class="player-card-name">
                    <h3>${player.name}</h3>
                </div>
                
                <!-- Stats Grid -->
                <div class="player-card-stats">
                    <div class="text-center">
                        <div class="player-card-stat-value">${player.ppg}</div>
                        <div class="player-card-stat-label">PPG</div>
                    </div>
                    <div class="text-center">
                        <div class="player-card-stat-value">${player.apg}</div>
                        <div class="player-card-stat-label">APG</div>
                    </div>
                    <div class="text-center">
                        <div class="player-card-stat-value">${player.rpg}</div>
                        <div class="player-card-stat-label">RPG</div>
                    </div>
                </div>
            `;
            
            container.appendChild(playerCard);
        });
        
        // Apply filters to player cards
        applyFiltersToCards();
    }

    function applyFiltersToCards() {
        const team = document.getElementById('filterTeam').value;
        const searchTerm = document.getElementById('searchPlayer').value.toLowerCase();
        const cards = document.querySelectorAll('.player-card');
        
        cards.forEach(card => {
            const cardTeam = card.getAttribute('data-team');
            const playerName = card.querySelector('h3').textContent.toLowerCase();
            
            let showCard = true;
            
            if (team && cardTeam !== team) {
                showCard = false;
            }
            
            if (searchTerm && !playerName.includes(searchTerm)) {
                showCard = false;
            }
            
            card.style.display = showCard ? 'block' : 'none';
        });
    }

    // Tab switching functionality
    const statTabs = document.querySelectorAll('.stat-tab');
    statTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            statTabs.forEach(t => t.classList.remove('active'));
            // Add active to clicked tab
            this.classList.add('active');
            
            const statType = this.getAttribute('data-stat');
            // In a real application, you would fetch different data based on statType
            console.log('Switched to:', statType);
            
            // Update table headers based on stat type
            updateTableForStat(statType);
        });
    });

    function updateTableForStat(statType) {
        const thead = document.querySelector('.players-table thead tr');
        
        if (statType === 'ppg') {
            thead.innerHTML = `
                <th>Rank</th>
                <th>Player</th>
                <th>Team</th>
                <th>Games</th>
                <th>PPG</th>
                <th>Total Points</th>
            `;
        } else if (statType === 'apg') {
            thead.innerHTML = `
                <th>Rank</th>
                <th>Player</th>
                <th>Team</th>
                <th>Games</th>
                <th>APG</th>
                <th>Total Assists</th>
            `;
        } else if (statType === 'rpg') {
            thead.innerHTML = `
                <th>Rank</th>
                <th>Player</th>
                <th>Team</th>
                <th>Games</th>
                <th>RPG</th>
                <th>Total Rebounds</th>
            `;
        }
    }

    // Search functionality
    const searchInput = document.getElementById('searchPlayer');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.players-table tbody tr');
        
        rows.forEach(row => {
            const playerName = row.querySelector('.player-name').textContent.toLowerCase();
            if (playerName.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Also filter player cards
        applyFiltersToCards();
    });

    // Team filter
    const teamFilter = document.getElementById('filterTeam');
    teamFilter.addEventListener('change', function() {
        applyFilters();
        applyFiltersToCards();
    });

    function applyFilters() {
        const team = teamFilter.value;
        const rows = document.querySelectorAll('.players-table tbody tr');
        
        rows.forEach(row => {
            const rowTeam = row.getAttribute('data-team');
            
            let showRow = true;
            
            if (team && rowTeam !== team) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
        });
    }

    function resetFilters() {
        document.getElementById('searchPlayer').value = '';
        document.getElementById('filterTeam').value = '';
        
        const rows = document.querySelectorAll('.players-table tbody tr');
        rows.forEach(row => {
            row.style.display = '';
        });
        
        // Also reset player cards
        applyFiltersToCards();
    }
</script>

<?php include 'includes/footer.php'; ?>