<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

// Get match data from URL parameters if available
$prefill_team1 = '';
$prefill_team2 = '';
$match_id = '';

if (isset($_GET['match_id']) && isset($_GET['team1']) && isset($_GET['team2'])) {
    $match_id = $_GET['match_id'];
    $prefill_team1 = htmlspecialchars(urldecode($_GET['team1']));
    $prefill_team2 = htmlspecialchars(urldecode($_GET['team2']));
}
?>

<aside class="print:hidden">

</aside>

<div class="flex-1 flex flex-col overflow-hidden bg-gray-100">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b px-6 py-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">FIBA Basketball Scoresheet</h1>
    <?php if ($match_id): ?>
      <div class="text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
        <i class="fas fa-link mr-1"></i>Match ID: <?php echo $match_id; ?>
      </div>
    <?php endif; ?>
    <button onclick="printScoresheet()" class="bg-yellow-400 text-blue-900 px-4 py-2 rounded font-semibold hover:bg-yellow-500">
      <i class="fas fa-print mr-2"></i> Print
    </button>
  </header>

  <main class="flex-1 overflow-y-auto p-6 space-y-8">

    <!-- Game Information -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Game Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div>
          <label class="font-semibold text-blue-900">Team A Name</label>
          <input type="text" id="teamAName" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" 
                 placeholder="Enter Team A Name" oninput="updateTeamLabels()"
                 value="<?php echo $prefill_team1; ?>">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Team B Name</label>
          <input type="text" id="teamBName" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" 
                 placeholder="Enter Team B Name" oninput="updateTeamLabels()"
                 value="<?php echo $prefill_team2; ?>">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Referee</label>
          <input type="text" id="referee" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Enter Referee">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Umpire 1</label>
          <input type="text" id="umpire1" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Enter Umpire 1">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Umpire 2</label>
          <input type="text" id="umpire2" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Enter Umpire 2">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Scorer</label>
          <input type="text" id="scorer" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Enter Scorer Name">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Game Date</label>
          <input type="date" id="gameDate" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Game Time</label>
          <input type="time" id="gameTime" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300">
        </div>
        <div>
          <label class="font-semibold text-blue-900">Venue</label>
          <input type="text" id="venue" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Enter Venue">
        </div>
      </div>
    </section>

    <!-- Teams Section with Timeouts and Fouls -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Teams</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <!-- Team A -->
        <div class="team-box border-2 border-gray-300 p-4 rounded-lg">
          <div class="team-header bg-blue-900 text-white p-3 text-center font-bold rounded mb-4" id="teamAHeader">TEAM A</div>
          
          <!-- Timeouts -->
          <div class="timeout-section mb-4">
            <h4 class="font-semibold text-blue-900 mb-2">Time-outs</h4>
            <div class="mb-2">
              <span class="period-label text-sm text-gray-600">Period 1 (Q1-Q2):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamA-timeout-p1"></div>
            </div>
            <div class="mb-2">
              <span class="period-label text-sm text-gray-600">Period 2 (Q3-Q4):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamA-timeout-p2"></div>
            </div>
            <div>
              <span class="period-label text-sm text-gray-600">Extra Period (OT):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamA-timeout-ot"></div>
            </div>
          </div>

          <!-- Team Fouls -->
          <div class="foul-section mb-4">
            <h4 class="font-semibold text-blue-900 mb-2">Team Fouls</h4>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <span class="period-label text-sm text-gray-600">Q1:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamA-fouls-q1"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q2:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamA-fouls-q2"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q3:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamA-fouls-q3"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q4:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamA-fouls-q4"></div>
              </div>
            </div>
          </div>

          <!-- Coach -->
          <div class="info-field mb-4">
            <label class="font-semibold text-blue-900">Coach</label>
            <input type="text" id="coachA" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Coach Name" required>
            <span class="error-msg text-red-600 text-sm hidden" id="coachA-error">Coach name is required</span>
          </div>
        </div>

        <!-- Team B -->
        <div class="team-box border-2 border-gray-300 p-4 rounded-lg">
          <div class="team-header bg-red-700 text-white p-3 text-center font-bold rounded mb-4" id="teamBHeader">TEAM B</div>
          
          <!-- Timeouts -->
          <div class="timeout-section mb-4">
            <h4 class="font-semibold text-blue-900 mb-2">Time-outs</h4>
            <div class="mb-2">
              <span class="period-label text-sm text-gray-600">Period 1 (Q1-Q2):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamB-timeout-p1"></div>
            </div>
            <div class="mb-2">
              <span class="period-label text-sm text-gray-600">Period 2 (Q3-Q4):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamB-timeout-p2"></div>
            </div>
            <div>
              <span class="period-label text-sm text-gray-600">Extra Period (OT):</span>
              <div class="timeout-boxes flex gap-2 mt-1" id="teamB-timeout-ot"></div>
            </div>
          </div>

          <!-- Team Fouls -->
          <div class="foul-section mb-4">
            <h4 class="font-semibold text-blue-900 mb-2">Team Fouls</h4>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <span class="period-label text-sm text-gray-600">Q1:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamB-fouls-q1"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q2:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamB-fouls-q2"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q3:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamB-fouls-q3"></div>
              </div>
              <div>
                <span class="period-label text-sm text-gray-600">Q4:</span>
                <div class="foul-boxes flex gap-1 mt-1" id="teamB-fouls-q4"></div>
              </div>
            </div>
          </div>

          <!-- Coach -->
          <div class="info-field mb-4">
            <label class="font-semibold text-blue-900">Coach</label>
            <input type="text" id="coachB" class="w-full border rounded p-2 mt-1 focus:ring focus:ring-blue-300" placeholder="Coach Name" required>
            <span class="error-msg text-red-600 text-sm hidden" id="coachB-error">Coach name is required</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Player Setup -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Player Setup (Q1)</h2>
      <p class="text-gray-500 mb-4">Enter players for Q1. They will automatically appear in Q2, Q3, Q4, and OT.</p>
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <h3 id="setupTeamAName" class="text-lg font-semibold text-blue-900 mb-2">Team A Players</h3>
          <div id="setupTeamA" class="space-y-2"></div>
          <button onclick="addPlayerSetup('A')" class="mt-3 bg-blue-900 text-white px-4 py-2 rounded hover:bg-blue-800">+ Add Player (Team A)</button>
        </div>
        <div>
          <h3 id="setupTeamBName" class="text-lg font-semibold text-red-700 mb-2">Team B Players</h3>
          <div id="setupTeamB" class="space-y-2"></div>
          <button onclick="addPlayerSetup('B')" class="mt-3 bg-red-700 text-white px-4 py-2 rounded hover:bg-red-800">+ Add Player (Team B)</button>
        </div>
      </div>
      <button onclick="generateQuarters()" class="mt-6 w-full bg-blue-900 text-white py-2 rounded font-semibold hover:bg-blue-800">Generate All Quarters</button>
    </section>

    <!-- Quarters -->
    <section id="quartersContainer" class="space-y-6"></section>

    <!-- Running Score -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">RUNNING SCORE - Game History</h2>
      <div class="score-grid grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 max-h-80 overflow-y-auto p-4 border border-gray-300 rounded" id="runningScore"></div>
    </section>

    <!-- Score Summary -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Score Summary by Quarter</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full border text-center">
          <thead class="bg-blue-900 text-white">
            <tr>
              <th class="py-3 px-4">Quarter</th>
              <th id="summaryTeamA" class="py-3 px-4">Team A</th>
              <th id="summaryTeamB" class="py-3 px-4">Team B</th>
            </tr>
          </thead>
          <tbody>
            <tr><td class="border py-2">Q1</td><td id="q1TeamA" class="border py-2">0</td><td id="q1TeamB" class="border py-2">0</td></tr>
            <tr><td class="border py-2">Q2</td><td id="q2TeamA" class="border py-2">0</td><td id="q2TeamB" class="border py-2">0</td></tr>
            <tr><td class="border py-2">Q3</td><td id="q3TeamA" class="border py-2">0</td><td id="q3TeamB" class="border py-2">0</td></tr>
            <tr><td class="border py-2">Q4</td><td id="q4TeamA" class="border py-2">0</td><td id="q4TeamB" class="border py-2">0</td></tr>
            <tr><td class="border py-2">OT</td><td id="otTeamA" class="border py-2">0</td><td id="otTeamB" class="border py-2">0</td></tr>
            <tr class="bg-yellow-300 font-semibold">
              <td class="border py-2">FINAL</td><td id="finalTeamA" class="border py-2">0</td><td id="finalTeamB" class="border py-2">0</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- MVP Section -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Best Player / MVP of the Game</h2>
      <div class="mb-4">
        <p class="text-sm text-gray-600">Select the Most Valuable Player from the dropdown below.</p>
      </div>
      <select class="mvp-select w-full border rounded p-3 mt-1 focus:ring focus:ring-blue-300" id="mvpSelect">
        <option value="">-- Select Best Player --</option>
      </select>
      <div class="mt-4 text-center text-xl font-bold" id="mvpDisplay"></div>
    </section>

    <!-- Winning Team -->
    <section class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold text-blue-900 border-b-2 border-yellow-400 pb-2 mb-4">Winning Team</h2>
      <div class="text-center">
        <label class="font-bold text-lg">Name of Winning Team</label>
        <input type="text" id="winningTeam" class="w-full md:w-1/2 mt-2 p-3 text-center border-2 border-green-500 rounded font-bold text-lg" placeholder="Winning Team" readonly>
      </div>
    </section>
  </main>

  <!-- Action Buttons -->
  <div class="bg-white shadow-inner py-6 flex justify-center gap-4 border-t mt-8 rounded-lg print:hidden">
    <button onclick="calculateTotals()" class="bg-blue-900 text-white px-6 py-2 rounded font-semibold hover:bg-blue-800">Calculate Totals</button>
    <button onclick="saveScoresheet()" class="bg-green-600 text-white px-6 py-2 rounded font-semibold hover:bg-green-700">Save Scoresheet</button>
    <button onclick="printScoresheet()" class="bg-yellow-400 text-blue-900 px-6 py-2 rounded font-semibold hover:bg-yellow-500">Print Scoresheet</button>
    <button onclick="clearScoresheet()" class="bg-red-600 text-white px-6 py-2 rounded font-semibold hover:bg-red-700">Clear All</button>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.quarter-division {
  background: white;
  padding: 25px;
  margin-bottom: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  border-left: 4px solid #ffc800;
}

.quarter-label {
  background: #0033a0;
  color: white;
  padding: 5px 15px;
  border-radius: 4px;
  font-weight: 600;
}

.player-item {
  display: flex;
  flex-direction: column;
  padding: 12px;
  margin-bottom: 8px;
  background: white;
  border-radius: 4px;
  border: 1px solid #e0e0e0;
}

.player-item:hover {
  background: #f0f8ff;
  border-color: #0033a0;
}

.player-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.jersey-number {
  font-weight: 700;
  color: #0033a0;
  font-size: 1.1em;
  text-align: center;
  background: #e8f4f8;
  padding: 8px 12px;
  border-radius: 4px;
  min-width: 50px;
}

.player-name {
  font-weight: 500;
  color: #333;
  flex: 1;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 8px;
}

.stat-box {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.stat-input {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  text-align: center;
  font-size: 0.95em;
}

.stat-input:focus {
  outline: none;
  border-color: #0033a0;
  box-shadow: 0 0 0 2px rgba(0, 51, 160, 0.1);
}

.stat-label {
  font-size: 0.75em;
  color: #666;
  font-weight: 600;
  text-align: center;
  margin-bottom: 3px;
}

.timeout-box, .foul-box {
  width: 30px;
  height: 30px;
  border: 2px solid #333;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  background: white;
  font-weight: bold;
  border-radius: 4px;
}

.timeout-box.used, .foul-box.used {
  background: #333;
  color: white;
}

.timeout-box:hover, .foul-box:hover {
  background: #f0f0f0;
}

.score-cell {
  border: 1px solid #333;
  padding: 8px 4px;
  text-align: center;
  font-size: 11px;
  background: white;
  border-radius: 4px;
}

.score-cell.teamA {
  background: #e3f2fd;
}

.score-cell.teamB {
  background: #fff3e0;
}

.mvp-candidate {
  background-color: #fff9e6;
  border-left: 4px solid #ffc107;
}

@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>

<script>
let playerData = { A: [], B: [] };
let runningScoreData = [];
let playerScores = { A: {}, B: {} };
let playerStats = { A: {}, B: {} }; // Store complete player stats

function updateTeamLabels() {
  const teamAName = document.getElementById('teamAName').value || 'TEAM A';
  const teamBName = document.getElementById('teamBName').value || 'TEAM B';
  document.getElementById('teamAHeader').textContent = teamAName;
  document.getElementById('teamBHeader').textContent = teamBName;
  document.getElementById('setupTeamAName').textContent = teamAName + ' Players';
  document.getElementById('setupTeamBName').textContent = teamBName + ' Players';
  document.getElementById('summaryTeamA').textContent = teamAName;
  document.getElementById('summaryTeamB').textContent = teamBName;
  
  // Update all quarter divisions
  document.querySelectorAll('.team-section h3').forEach(header => {
    if (header.textContent.includes('Team A')) {
      header.textContent = teamAName;
    } else if (header.textContent.includes('Team B')) {
      header.textContent = teamBName;
    }
  });
}

function initializeTimeoutsFouls() {
  // Team A Timeouts
  createBoxes('teamA-timeout-p1', 2, 'timeout', 'A', 'p1');
  createBoxes('teamA-timeout-p2', 3, 'timeout', 'A', 'p2');
  createBoxes('teamA-timeout-ot', 3, 'timeout', 'A', 'ot');

  // Team B Timeouts
  createBoxes('teamB-timeout-p1', 2, 'timeout', 'B', 'p1');
  createBoxes('teamB-timeout-p2', 3, 'timeout', 'B', 'p2');
  createBoxes('teamB-timeout-ot', 3, 'timeout', 'B', 'ot');

  // Team A Fouls
  createBoxes('teamA-fouls-q1', 4, 'foul', 'A', 'q1');
  createBoxes('teamA-fouls-q2', 4, 'foul', 'A', 'q2');
  createBoxes('teamA-fouls-q3', 4, 'foul', 'A', 'q3');
  createBoxes('teamA-fouls-q4', 4, 'foul', 'A', 'q4');

  // Team B Fouls
  createBoxes('teamB-fouls-q1', 4, 'foul', 'B', 'q1');
  createBoxes('teamB-fouls-q2', 4, 'foul', 'B', 'q2');
  createBoxes('teamB-fouls-q3', 4, 'foul', 'B', 'q3');
  createBoxes('teamB-fouls-q4', 4, 'foul', 'B', 'q4');
}

function createBoxes(containerId, count, type, team, period) {
  const container = document.getElementById(containerId);
  container.innerHTML = ''; // Clear existing boxes
  for (let i = 1; i <= count; i++) {
    const box = document.createElement('div');
    box.className = type === 'timeout' ? 'timeout-box' : 'foul-box';
    box.textContent = i;
    box.onclick = function() {
      this.classList.toggle('used');
    };
    container.appendChild(box);
  }
}

function addPlayerSetup(team) {
  const container = document.getElementById('setupTeam' + team);
  const playerIndex = playerData[team].length;
  const div = document.createElement('div');
  div.className = "grid grid-cols-[80px_1fr_60px] gap-2 items-center mb-2";
  div.innerHTML = `
    <input type="number" id="jersey${team}${playerIndex}" class="border rounded p-2 text-center" placeholder="#" min="0" max="99">
    <input type="text" id="name${team}${playerIndex}" class="border rounded p-2" placeholder="Player Name">
    <button type="button" onclick="removePlayerSetup('${team}', ${playerIndex})" class="bg-red-600 text-white px-2 py-2 rounded hover:bg-red-700">✕</button>
  `;
  container.appendChild(div);
  playerData[team].push({ jersey: '', name: '', index: playerIndex });
  playerScores[team][playerIndex] = 0;
  // Initialize player stats
  playerStats[team][playerIndex] = {
    points: 0,
    assists: 0,
    rebounds: 0,
    twoPointers: 0,
    threePointers: 0,
    freeThrows: 0,
    fouls: 0
  };
}

function removePlayerSetup(team, index) {
  playerData[team] = playerData[team].filter(p => p.index !== index);
  delete playerScores[team][index];
  delete playerStats[team][index];
  
  // Re-index the remaining players
  const container = document.getElementById('setupTeam' + team);
  container.innerHTML = '';
  
  playerData[team].forEach((player, newIndex) => {
    const div = document.createElement('div');
    div.className = "grid grid-cols-[80px_1fr_60px] gap-2 items-center mb-2";
    div.innerHTML = `
      <input type="number" id="jersey${team}${newIndex}" class="border rounded p-2 text-center" placeholder="#" min="0" max="99" value="${player.jersey}">
      <input type="text" id="name${team}${newIndex}" class="border rounded p-2" placeholder="Player Name" value="${player.name}">
      <button type="button" onclick="removePlayerSetup('${team}', ${newIndex})" class="bg-red-600 text-white px-2 py-2 rounded hover:bg-red-700">✕</button>
    `;
    container.appendChild(div);
  });
  
  updateMVPDropdown();
}

function generateQuarters() {
  // Validate player data first
  let hasPlayers = false;
  ['A', 'B'].forEach(team => {
    const container = document.getElementById('setupTeam' + team);
    const inputs = container.children;
    
    if (inputs.length > 0) {
      hasPlayers = true;
    }
  });

  if (!hasPlayers) {
    alert('Please add at least one player for each team before generating quarters.');
    return;
  }

  // Collect player data from setup
  ['A', 'B'].forEach(team => {
    playerData[team] = [];
    const container = document.getElementById('setupTeam' + team);
    const inputs = container.children;
    
    for (let i = 0; i < inputs.length; i++) {
      const jerseyInput = document.getElementById(`jersey${team}${i}`);
      const nameInput = document.getElementById(`name${team}${i}`);
      
      if (jerseyInput && nameInput) {
        const jersey = jerseyInput.value;
        const name = nameInput.value;
        if (jersey && name) {
          playerData[team].push({ jersey, name, index: i });
          playerScores[team][i] = 0;
          // Initialize player stats if not already set
          if (!playerStats[team][i]) {
            playerStats[team][i] = {
              points: 0,
              assists: 0,
              rebounds: 0,
              twoPointers: 0,
              threePointers: 0,
              freeThrows: 0,
              fouls: 0
            };
          }
        }
      }
    }
  });

  // Generate quarter divisions
  const quartersContainer = document.getElementById('quartersContainer');
  quartersContainer.innerHTML = '';
  
  const quarters = ['Q1', 'Q2', 'Q3', 'Q4', 'OT'];
  const teamAName = document.getElementById('teamAName').value || 'Team A';
  const teamBName = document.getElementById('teamBName').value || 'Team B';
  
  quarters.forEach(quarter => {
    const quarterDiv = document.createElement('div');
    quarterDiv.className = 'quarter-division';
    quarterDiv.id = quarter.toLowerCase();
    
    let html = `
      <h2 class="text-xl font-semibold text-blue-900 mb-4"><span class="quarter-label">${quarter}</span> Player Statistics</h2>
      <div class="grid md:grid-cols-2 gap-6">
        <div class="team-section bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-semibold text-blue-900 mb-3">${teamAName}</h3>
          <div class="space-y-3">
    `;
    
    playerData.A.forEach((player, idx) => {
      html += `
        <div class="player-item" id="playerA-${idx}-${quarter}">
          <div class="player-header">
            <div class="jersey-number">#${player.jersey}</div>
            <div class="player-name">${player.name}</div>
            <div class="flex gap-1">
              <button class="score-btn bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600" onclick="addScoreFromStats('A', ${idx}, 1, '${quarter}')">+1</button>
              <button class="score-btn bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" onclick="addScoreFromStats('A', ${idx}, 2, '${quarter}')">+2</button>
              <button class="score-btn bg-purple-500 text-white px-2 py-1 rounded text-xs hover:bg-purple-600" onclick="addScoreFromStats('A', ${idx}, 3, '${quarter}')">+3</button>
            </div>
          </div>
          <div class="stats-grid">
            <div class="stat-box">
              <div class="stat-label">2PT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="2pt" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'twoPointers', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">3PT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="3pt" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'threePointers', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">FT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="ft" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'freeThrows', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">REB</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="reb" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'rebounds', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">AST</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="ast" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'assists', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">PF</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="A" data-player="${idx}" data-stat="pf" value="0" min="0" oninput="updatePlayerStats('A', ${idx}, 'fouls', this.value, '${quarter}')">
            </div>
          </div>
        </div>
      `;
    });
    
    html += `
          </div>
        </div>
        <div class="team-section bg-gray-50 p-4 rounded-lg">
          <h3 class="text-lg font-semibold text-red-700 mb-3">${teamBName}</h3>
          <div class="space-y-3">
    `;
    
    playerData.B.forEach((player, idx) => {
      html += `
        <div class="player-item" id="playerB-${idx}-${quarter}">
          <div class="player-header">
            <div class="jersey-number">#${player.jersey}</div>
            <div class="player-name">${player.name}</div>
            <div class="flex gap-1">
              <button class="score-btn bg-green-500 text-white px-2 py-1 rounded text-xs hover:bg-green-600" onclick="addScoreFromStats('B', ${idx}, 1, '${quarter}')">+1</button>
              <button class="score-btn bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600" onclick="addScoreFromStats('B', ${idx}, 2, '${quarter}')">+2</button>
              <button class="score-btn bg-purple-500 text-white px-2 py-1 rounded text-xs hover:bg-purple-600" onclick="addScoreFromStats('B', ${idx}, 3, '${quarter}')">+3</button>
            </div>
          </div>
          <div class="stats-grid">
            <div class="stat-box">
              <div class="stat-label">2PT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="2pt" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'twoPointers', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">3PT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="3pt" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'threePointers', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">FT</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="ft" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'freeThrows', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">REB</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="reb" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'rebounds', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">AST</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="ast" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'assists', this.value, '${quarter}')">
            </div>
            <div class="stat-box">
              <div class="stat-label">PF</div>
              <input type="number" class="stat-input" data-quarter="${quarter}" data-team="B" data-player="${idx}" data-stat="pf" value="0" min="0" oninput="updatePlayerStats('B', ${idx}, 'fouls', this.value, '${quarter}')">
            </div>
          </div>
        </div>
      `;
    });
    
    html += `
          </div>
        </div>
      </div>
    `;
    
    quarterDiv.innerHTML = html;
    quartersContainer.appendChild(quarterDiv);
  });
  
  updateMVPDropdown();
}

function updatePlayerStats(team, playerIndex, stat, value, quarter) {
  if (!playerStats[team][playerIndex]) {
    playerStats[team][playerIndex] = {
      points: 0,
      assists: 0,
      rebounds: 0,
      twoPointers: 0,
      threePointers: 0,
      freeThrows: 0,
      fouls: 0
    };
  }
  
  const intValue = parseInt(value) || 0;
  
  // Update the specific stat
  if (stat === 'twoPointers' || stat === 'threePointers' || stat === 'freeThrows') {
    playerStats[team][playerIndex][stat] = intValue;
    
    // Recalculate total points
    const twoPoints = playerStats[team][playerIndex].twoPointers * 2;
    const threePoints = playerStats[team][playerIndex].threePointers * 3;
    const freePoints = playerStats[team][playerIndex].freeThrows;
    playerStats[team][playerIndex].points = twoPoints + threePoints + freePoints;
    playerScores[team][playerIndex] = playerStats[team][playerIndex].points;
  } else {
    playerStats[team][playerIndex][stat] = intValue;
  }
  
  updateQuarterTotal();
  updateMVPDropdown();
}

function addScoreFromStats(team, playerIndex, points, quarter) {
  const player = playerData[team][playerIndex];
  if (!player) return;
  
  const playerName = player.name;
  const playerNumber = player.jersey;

  // Update the corresponding stat input
  let statType = '2pt';
  let statField = 'twoPointers';
  if (points === 1) {
    statType = 'ft';
    statField = 'freeThrows';
  } else if (points === 3) {
    statType = '3pt';
    statField = 'threePointers';
  }
  
  const statInput = document.querySelector(`input[data-quarter="${quarter}"][data-team="${team}"][data-player="${playerIndex}"][data-stat="${statType}"]`);
  if (statInput) {
    const newValue = parseInt(statInput.value) + 1;
    statInput.value = newValue;
    updatePlayerStats(team, playerIndex, statField, newValue, quarter);
  }
  
  // Add to running score
  const totalScoreA = Object.values(playerScores.A).reduce((a, b) => a + b, 0);
  const totalScoreB = Object.values(playerScores.B).reduce((a, b) => a + b, 0);

  runningScoreData.push({
    team: team,
    playerName: playerName,
    playerNumber: playerNumber,
    points: points,
    totalA: totalScoreA,
    totalB: totalScoreB,
    time: new Date().toLocaleTimeString(),
    quarter: quarter
  });

  updateRunningScore();
  updateQuarterTotal();
  updateMVPDropdown();
}

function updateRunningScore() {
  const container = document.getElementById('runningScore');
  container.innerHTML = '';

  runningScoreData.forEach((entry, index) => {
    const cell = document.createElement('div');
    cell.className = `score-cell team${entry.team}`;
    cell.innerHTML = `
      <div class="font-bold">#${entry.playerNumber}</div>
      <div class="text-xs">${entry.playerName}</div>
      <div class="text-green-600 font-bold">+${entry.points} pts</div>
      <div class="text-xs">${entry.totalA} - ${entry.totalB}</div>
      <div class="text-xs text-gray-500">${entry.time}</div>
      <div class="text-xs text-gray-500">${entry.quarter}</div>
    `;
    container.appendChild(cell);
  });
  
  // Scroll to bottom
  container.scrollTop = container.scrollHeight;
}

function updateQuarterTotal() {
  const quarters = ['Q1', 'Q2', 'Q3', 'Q4', 'OT'];
  
  quarters.forEach(quarter => {
    let teamATotal = 0;
    let teamBTotal = 0;
    
    // Calculate points from 2PT, 3PT, and FT
    document.querySelectorAll(`input[data-quarter="${quarter}"]`).forEach(input => {
      const team = input.dataset.team;
      const stat = input.dataset.stat;
      const value = parseInt(input.value) || 0;
      
      if (stat === '2pt') {
        if (team === 'A') teamATotal += value * 2;
        else if (team === 'B') teamBTotal += value * 2;
      } else if (stat === '3pt') {
        if (team === 'A') teamATotal += value * 3;
        else if (team === 'B') teamBTotal += value * 3;
      } else if (stat === 'ft') {
        if (team === 'A') teamATotal += value * 1;
        else if (team === 'B') teamBTotal += value * 1;
      }
    });
    
    const qKey = quarter.toLowerCase();
    document.getElementById(`${qKey}TeamA`).textContent = teamATotal;
    document.getElementById(`${qKey}TeamB`).textContent = teamBTotal;
  });
  
  calculateFinalScores();
}

function calculateFinalScores() {
  const quarters = ['q1', 'q2', 'q3', 'q4', 'ot'];
  let finalA = 0;
  let finalB = 0;
  
  quarters.forEach(q => {
    finalA += parseInt(document.getElementById(`${q}TeamA`).textContent) || 0;
    finalB += parseInt(document.getElementById(`${q}TeamB`).textContent) || 0;
  });
  
  document.getElementById('finalTeamA').textContent = finalA;
  document.getElementById('finalTeamB').textContent = finalB;
  
  // Determine winner
  const teamAName = document.getElementById('teamAName').value || 'Team A';
  const teamBName = document.getElementById('teamBName').value || 'Team B';

  if (finalA > finalB) {
    document.getElementById('winningTeam').value = teamAName;
  } else if (finalB > finalA) {
    document.getElementById('winningTeam').value = teamBName;
  } else {
    document.getElementById('winningTeam').value = 'TIE';
  }
}

function updateMVPDropdown() {
  const select = document.getElementById('mvpSelect');
  const currentValue = select.value;
  select.innerHTML = '<option value="">-- Select Best Player --</option>';

  playerData.A.forEach((player, index) => {
    if (player.name && playerStats.A[index]) {
      const stats = playerStats.A[index];
      const option = document.createElement('option');
      option.value = `A-${index}`;
      option.textContent = `Team A - #${player.jersey} ${player.name} (${stats.points}PTS, ${stats.assists}AST, ${stats.rebounds}REB)`;
      select.appendChild(option);
    }
  });

  playerData.B.forEach((player, index) => {
    if (player.name && playerStats.B[index]) {
      const stats = playerStats.B[index];
      const option = document.createElement('option');
      option.value = `B-${index}`;
      option.textContent = `Team B - #${player.jersey} ${player.name} (${stats.points}PTS, ${stats.assists}AST, ${stats.rebounds}REB)`;
      select.appendChild(option);
    }
  });

  select.value = currentValue;
}

// MVP selection handler
document.getElementById('mvpSelect').addEventListener('change', function() {
  if (this.value) {
    const [team, playerIndex] = this.value.split('-');
    const player = playerData[team][playerIndex];
    const stats = playerStats[team][playerIndex];
    document.getElementById('mvpDisplay').textContent = 
      `🏆 MVP: #${player.jersey} ${player.name} - ${stats.points} Points, ${stats.assists} Assists, ${stats.rebounds} Rebounds`;
    highlightMVP(team, playerIndex);
  } else {
    document.getElementById('mvpDisplay').textContent = '';
    // Remove highlights
    document.querySelectorAll('.player-item').forEach(item => {
      item.classList.remove('mvp-candidate');
    });
  }
});

function highlightMVP(team, playerIndex) {
  // Remove previous highlights
  document.querySelectorAll('.player-item').forEach(item => {
    item.classList.remove('mvp-candidate');
  });
  
  // Highlight MVP in all quarters
  const quarters = ['Q1', 'Q2', 'Q3', 'Q4', 'OT'];
  quarters.forEach(quarter => {
    const mvpElement = document.getElementById(`player${team}-${playerIndex}-${quarter}`);
    if (mvpElement) {
      mvpElement.classList.add('mvp-candidate');
    }
  });
}

function calculateTotals() {
  updateQuarterTotal();
  
  const finalA = parseInt(document.getElementById('finalTeamA').textContent) || 0;
  const finalB = parseInt(document.getElementById('finalTeamB').textContent) || 0;
  const teamAName = document.getElementById('teamAName').value || 'Team A';
  const teamBName = document.getElementById('teamBName').value || 'Team B';

  let message = 'Totals calculated successfully!';
  
  if (finalA > finalB) {
    document.getElementById('winningTeam').value = teamAName;
    message += ` Winning team: ${teamAName}`;
  } else if (finalB > finalA) {
    document.getElementById('winningTeam').value = teamBName;
    message += ` Winning team: ${teamBName}`;
  } else {
    document.getElementById('winningTeam').value = 'TIE';
    message += ' The game is tied!';
  }
  
  alert(message);
}

function saveScoresheet() {
    const teamA = document.getElementById('teamAName').value;
    const teamB = document.getElementById('teamBName').value;
    const referee = document.getElementById('referee').value;
    const umpire1 = document.getElementById('umpire1').value;
    const umpire2 = document.getElementById('umpire2').value;
    const scorer = document.getElementById('scorer').value;
    const gameDate = document.getElementById('gameDate').value;
    const gameTime = document.getElementById('gameTime').value;
    const venue = document.getElementById('venue').value;
    const coachA = document.getElementById('coachA').value;
    const coachB = document.getElementById('coachB').value;

    // Validate required fields
    if (!coachA || !coachB) {
        alert('Please fill in both coach names');
        return;
    }

    // Collect timeout and foul data
    const timeoutsFouls = {
        teamA: {
            timeouts: {
                p1: document.querySelectorAll('#teamA-timeout-p1 .timeout-box.used').length,
                p2: document.querySelectorAll('#teamA-timeout-p2 .timeout-box.used').length,
                ot: document.querySelectorAll('#teamA-timeout-ot .timeout-box.used').length
            },
            fouls: {
                q1: document.querySelectorAll('#teamA-fouls-q1 .foul-box.used').length,
                q2: document.querySelectorAll('#teamA-fouls-q2 .foul-box.used').length,
                q3: document.querySelectorAll('#teamA-fouls-q3 .foul-box.used').length,
                q4: document.querySelectorAll('#teamA-fouls-q4 .foul-box.used').length
            }
        },
        teamB: {
            timeouts: {
                p1: document.querySelectorAll('#teamB-timeout-p1 .timeout-box.used').length,
                p2: document.querySelectorAll('#teamB-timeout-p2 .timeout-box.used').length,
                ot: document.querySelectorAll('#teamB-timeout-ot .timeout-box.used').length
            },
            fouls: {
                q1: document.querySelectorAll('#teamB-fouls-q1 .foul-box.used').length,
                q2: document.querySelectorAll('#teamB-fouls-q2 .foul-box.used').length,
                q3: document.querySelectorAll('#teamB-fouls-q3 .foul-box.used').length,
                q4: document.querySelectorAll('#teamB-fouls-q4 .foul-box.used').length
            }
        }
    };

    // Collect summary scores
    const scores = {
        q1A: parseInt(document.getElementById('q1TeamA').innerText) || 0,
        q1B: parseInt(document.getElementById('q1TeamB').innerText) || 0,
        q2A: parseInt(document.getElementById('q2TeamA').innerText) || 0,
        q2B: parseInt(document.getElementById('q2TeamB').innerText) || 0,
        q3A: parseInt(document.getElementById('q3TeamA').innerText) || 0,
        q3B: parseInt(document.getElementById('q3TeamB').innerText) || 0,
        q4A: parseInt(document.getElementById('q4TeamA').innerText) || 0,
        q4B: parseInt(document.getElementById('q4TeamB').innerText) || 0,
        otA: parseInt(document.getElementById('otTeamA').innerText) || 0,
        otB: parseInt(document.getElementById('otTeamB').innerText) || 0,
        finalA: parseInt(document.getElementById('finalTeamA').innerText) || 0,
        finalB: parseInt(document.getElementById('finalTeamB').innerText) || 0
    };

    const winningTeam = document.getElementById('winningTeam').value;
    const mvp = document.getElementById('mvpSelect').value;

    if (!winningTeam) {
        alert('Please calculate totals first to determine the winning team');
        return;
    }

    // Prepare data for saving
    const data = {
        match_id: '<?php echo $match_id; ?>',
        teamA: teamA,
        teamB: teamB,
        referee: referee,
        umpire1: umpire1,
        umpire2: umpire2,
        scorer: scorer,
        gameDate: gameDate,
        gameTime: gameTime,
        venue: venue,
        coachA: coachA,
        coachB: coachB,
        timeoutsFouls: timeoutsFouls,
        scores: scores,
        winningTeam: winningTeam,
        mvp: mvp,
        playerStats: playerStats,
        runningScore: runningScoreData
    };

    console.log('Saving scoresheet data:', data);

    // Show loading state
    const saveBtn = event.target;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    saveBtn.disabled = true;

    // Send to PHP backend
    fetch('save_scoresheet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        return res.json();
    })
    .then(res => {
        console.log('Save response:', res);
        if (res.success) {
            let successMessage = 'Scoresheet saved successfully!';
            if (res.winner_advanced) {
                successMessage += ' The winning team has been advanced in the bracket.';
            } else {
                successMessage += ' Note: Winner was not advanced in bracket (might be final match or team name mismatch).';
            }
            
            // Show debug info in console
            if (res.debug_info) {
                console.log('Debug info:', res.debug_info);
            }
            
            alert(successMessage);
            
            // If in modal, close it after successful save
            if (window.location.search.includes('modal=true')) {
                setTimeout(() => {
                    if (window.parent && typeof window.parent.closeScoresheetModal === 'function') {
                        window.parent.closeScoresheetModal();
                    }
                }, 2000);
            }
        } else {
            throw new Error(res.message || 'Unknown error occurred');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Error saving scoresheet: ' + err.message);
    })
    .finally(() => {
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    });
}

// Add test function for debugging
function testBracketAdvancement() {
    const testData = {
        match_id: '<?php echo $match_id; ?>',
        teamA: 'Test Team A',
        teamB: 'Test Team B',
        scores: {
            finalA: 10,
            finalB: 8
        },
        winningTeam: 'Test Team A',
        coachA: 'Test Coach A',
        coachB: 'Test Coach B',
        playerStats: {},
        runningScore: []
    };

    console.log('Testing bracket advancement with:', testData);

    fetch('save_scoresheet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(testData)
    })
    .then(res => res.json())
    .then(res => {
        console.log('Bracket advancement test result:', res);
        alert('Test completed. Check console for details.');
    })
    .catch(err => {
        console.error('Test error:', err);
        alert('Test failed: ' + err.message);
    });
}

function printScoresheet() { 
  window.print(); 
}

function clearScoresheet() {
  if (confirm('Are you sure you want to clear all data? This cannot be undone.')) {
    location.reload();
  }
}

// Initialize on page load
window.onload = function() {
  initializeTimeoutsFouls();
  // Initialize with empty setup
  for (let i = 0; i < 2; i++) { 
    addPlayerSetup('A'); 
    addPlayerSetup('B'); 
  }
  
  // Set default date to today
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('gameDate').value = today;
  
  // Auto-update team labels if pre-filled
  updateTeamLabels();
  
  console.log('Scoresheet initialized for match: <?php echo $match_id; ?>');
};
</script>