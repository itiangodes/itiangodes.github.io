<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';
?>

<aside class="print:hidden">
  <?php include 'includes/sidebar.php'; ?>
</aside>

<div class="flex-1 flex flex-col overflow-hidden bg-gray-100">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b px-6 py-4 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800">Scoresheet Documents</h1>
    <div class="flex gap-4">
      <!-- Added Generate Scoresheet Button -->
      <button onclick="generateScoresheet()" class="bg-indigo-600 text-white px-4 py-2 rounded font-semibold hover:bg-indigo-700">
        <i class="fas fa-file-alt mr-2"></i> Generate Scoresheet
      </button>
      <button onclick="showAddTournamentModal()" class="bg-green-600 text-white px-4 py-2 rounded font-semibold hover:bg-green-700">
        <i class="fas fa-plus mr-2"></i> New Tournament
      </button>
      <button onclick="exportAllData()" class="bg-blue-600 text-white px-4 py-2 rounded font-semibold hover:bg-blue-700">
        <i class="fas fa-download mr-2"></i> Export Data
      </button>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-6">
    <!-- Search and Filter Section -->
    <section class="bg-white p-6 rounded-lg shadow mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">Search Matches</label>
          <div class="relative">
            <input type="text" id="searchInput" placeholder="Search by team names, venue, date..." 
                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <i class="fas fa-search text-gray-400"></i>
            </div>
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Tournament</label>
          <select id="tournamentFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Tournaments</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Year</label>
          <select id="yearFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Years</option>
          </select>
        </div>
      </div>
      
      <div class="mt-4 flex flex-wrap gap-2" id="activeFilters"></div>
    </section>

    <!-- Statistics Cards -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
            <i class="fas fa-basketball-ball text-xl"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Total Matches</p>
            <p class="text-2xl font-bold text-gray-900" id="totalMatches">0</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
            <i class="fas fa-trophy text-xl"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Tournaments</p>
            <p class="text-2xl font-bold text-gray-900" id="totalTournaments">0</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
            <i class="fas fa-calendar-alt text-xl"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Current Year</p>
            <p class="text-2xl font-bold text-gray-900" id="currentYear"><?php echo date('Y'); ?></p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
            <i class="fas fa-file-alt text-xl"></i>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">This Month</p>
            <p class="text-2xl font-bold text-gray-900" id="monthMatches">0</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Tournament Categories -->
    <section class="mb-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Tournaments by Year</h2>
        <div class="flex gap-2">
          <button onclick="toggleView('grid')" id="gridViewBtn" class="p-2 rounded bg-blue-600 text-white">
            <i class="fas fa-th-large"></i>
          </button>
          <button onclick="toggleView('list')" id="listViewBtn" class="p-2 rounded bg-gray-200 text-gray-700">
            <i class="fas fa-list"></i>
          </button>
        </div>
      </div>
      
      <div id="tournamentsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Tournaments will be loaded here -->
      </div>
    </section>

    <!-- Matches Table -->
    <section class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-800">All Matches</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Match</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tournament</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MVP</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="matchesTableBody" class="bg-white divide-y divide-gray-200">
            <!-- Matches will be loaded here -->
          </tbody>
        </table>
      </div>
      <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
        <div class="text-sm text-gray-700">
          Showing <span id="showingCount">0</span> of <span id="totalCount">0</span> matches
        </div>
        <div class="flex gap-2" id="pagination">
          <!-- Pagination will be generated here -->
        </div>
      </div>
    </section>
  </main>
</div>

<!-- Add Tournament Modal -->
<div id="addTournamentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
  <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
    <div class="mt-3">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Add New Tournament</h3>
        <button onclick="closeModal('addTournamentModal')" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <form id="tournamentForm" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Tournament Name</label>
          <input type="text" id="tournamentName" required 
                 class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Year</label>
          <select id="tournamentYear" required 
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <option value="">Select Year</option>
            <?php
            $currentYear = date('Y');
            for ($year = $currentYear; $year >= $currentYear - 10; $year--) {
              echo "<option value='$year'>$year</option>";
            }
            ?>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
          <textarea id="tournamentDescription" rows="3"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
          <button type="button" onclick="closeModal('addTournamentModal')" 
                  class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
            Cancel
          </button>
          <button type="submit" 
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Create Tournament
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Match Details Modal -->
<div id="matchDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
  <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
    <div class="mt-3">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Match Details</h3>
        <button onclick="closeModal('matchDetailsModal')" class="text-gray-400 hover:text-gray-600">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div id="matchDetailsContent" class="space-y-4">
        <!-- Match details will be loaded here -->
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<style>
.tournament-card {
  transition: all 0.3s ease;
  border-left: 4px solid;
}

.tournament-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.match-row {
  transition: background-color 0.2s ease;
}

.match-row:hover {
  background-color: #f9fafb;
}

.filter-tag {
  display: inline-flex;
  align-items: center;
  background-color: #e5e7eb;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.875rem;
}

.active-filter {
  background-color: #3b82f6;
  color: white;
}

.view-active {
  background-color: #3b82f6 !important;
  color: white !important;
}
</style>

<script>
// Global variables
let currentView = 'grid';
let currentPage = 1;
const matchesPerPage = 10;
let allMatches = [];
let allTournaments = [];
let filteredMatches = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
  loadTournaments();
  loadMatches();
  setupEventListeners();
});

function setupEventListeners() {
  // Search functionality
  document.getElementById('searchInput').addEventListener('input', function(e) {
    filterMatches();
  });

  // Filter functionality
  document.getElementById('tournamentFilter').addEventListener('change', filterMatches);
  document.getElementById('yearFilter').addEventListener('change', filterMatches);

  // Tournament form submission
  document.getElementById('tournamentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    addTournament();
  });
}

// NEW FUNCTION: Generate Scoresheet
function generateScoresheet() {
  // Redirect to scoresheets.php
  window.location.href = 'scoresheets.php';
}

function loadTournaments() {
  // Simulate API call - replace with actual API
  fetch('get_tournaments.php')
    .then(response => response.json())
    .then(data => {
      allTournaments = data.tournaments || [];
      updateTournamentFilters();
      renderTournaments();
      updateStatistics();
    })
    .catch(error => {
      console.error('Error loading tournaments:', error);
      // For demo purposes, load sample data
      loadSampleTournaments();
    });
}

function loadMatches() {
  // Simulate API call - replace with actual API
  fetch('get_matches.php')
    .then(response => response.json())
    .then(data => {
      allMatches = data.matches || [];
      filteredMatches = [...allMatches];
      renderMatchesTable();
      updateStatistics();
    })
    .catch(error => {
      console.error('Error loading matches:', error);
      // For demo purposes, load sample data
      loadSampleMatches();
    });
}

function loadSampleTournaments() {
  allTournaments = [
    { id: 1, name: 'FIBA World Cup', year: '2023', description: 'International basketball championship', match_count: 12 },
    { id: 2, name: 'NBA Finals', year: '2023', description: 'Professional basketball championship series', match_count: 7 },
    { id: 3, name: 'EuroLeague', year: '2023', description: 'European professional basketball competition', match_count: 15 },
    { id: 4, name: 'Asian Games', year: '2022', description: 'Multi-sport event including basketball', match_count: 8 },
    { id: 5, name: 'NCAA March Madness', year: '2023', description: 'College basketball tournament', match_count: 20 }
  ];
  updateTournamentFilters();
  renderTournaments();
}

function loadSampleMatches() {
  allMatches = [
    {
      id: 1,
      date: '2023-09-10',
      time: '20:00',
      teamA: 'USA',
      teamB: 'Germany',
      scoreA: 113,
      scoreB: 111,
      tournament: 'FIBA World Cup 2023',
      venue: 'Mall of Asia Arena, Manila',
      mvp: 'Anthony Edwards',
      scoresheet_url: 'scoresheet_1.pdf'
    },
    {
      id: 2,
      date: '2023-09-08',
      time: '18:30',
      teamA: 'Spain',
      teamB: 'Canada',
      scoreA: 85,
      scoreB: 88,
      tournament: 'FIBA World Cup 2023',
      venue: 'Indonesia Arena, Jakarta',
      mvp: 'Shai Gilgeous-Alexander',
      scoresheet_url: 'scoresheet_2.pdf'
    },
    {
      id: 3,
      date: '2023-06-12',
      time: '20:00',
      teamA: 'Denver Nuggets',
      teamB: 'Miami Heat',
      scoreA: 94,
      scoreB: 89,
      tournament: 'NBA Finals 2023',
      venue: 'Ball Arena, Denver',
      mvp: 'Nikola Jokić',
      scoresheet_url: 'scoresheet_3.pdf'
    }
  ];
  filteredMatches = [...allMatches];
  renderMatchesTable();
  updateStatistics();
}

function updateTournamentFilters() {
  const tournamentSelect = document.getElementById('tournamentFilter');
  const yearSelect = document.getElementById('yearFilter');
  
  // Clear existing options (keep "All" option)
  tournamentSelect.innerHTML = '<option value="">All Tournaments</option>';
  yearSelect.innerHTML = '<option value="">All Years</option>';
  
  // Get unique years
  const years = [...new Set(allTournaments.map(t => t.year))].sort((a, b) => b - a);
  
  years.forEach(year => {
    const option = document.createElement('option');
    option.value = year;
    option.textContent = year;
    yearSelect.appendChild(option);
  });
  
  // Add tournaments
  allTournaments.forEach(tournament => {
    const option = document.createElement('option');
    option.value = tournament.id;
    option.textContent = `${tournament.name} ${tournament.year}`;
    tournamentSelect.appendChild(option);
  });
}

function renderTournaments() {
  const container = document.getElementById('tournamentsContainer');
  
  if (currentView === 'grid') {
    container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
    container.innerHTML = allTournaments.map(tournament => `
      <div class="tournament-card bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-semibold text-gray-900">${tournament.name}</h3>
          <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">${tournament.year}</span>
        </div>
        <p class="text-gray-600 text-sm mb-4">${tournament.description || 'No description available'}</p>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">${tournament.match_count || 0} matches</span>
          <button onclick="viewTournamentMatches(${tournament.id})" 
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View Matches <i class="fas fa-arrow-right ml-1"></i>
          </button>
        </div>
      </div>
    `).join('');
  } else {
    container.className = 'space-y-4';
    container.innerHTML = allTournaments.map(tournament => `
      <div class="tournament-card bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex justify-between items-center">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">${tournament.name}</h3>
            <p class="text-gray-600 text-sm mt-1">${tournament.description || 'No description available'}</p>
          </div>
          <div class="text-right">
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">${tournament.year}</span>
            <div class="mt-2">
              <span class="text-sm text-gray-500">${tournament.match_count || 0} matches</span>
            </div>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button onclick="viewTournamentMatches(${tournament.id})" 
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            View Matches <i class="fas fa-arrow-right ml-1"></i>
          </button>
        </div>
      </div>
    `).join('');
  }
}

function renderMatchesTable() {
  const tbody = document.getElementById('matchesTableBody');
  const startIndex = (currentPage - 1) * matchesPerPage;
  const endIndex = startIndex + matchesPerPage;
  const currentMatches = filteredMatches.slice(startIndex, endIndex);
  
  tbody.innerHTML = currentMatches.map(match => `
    <tr class="match-row">
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${formatDate(match.date)}</div>
        <div class="text-sm text-gray-500">${match.time}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">${match.teamA} vs ${match.teamB}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${match.tournament}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-500">${match.venue}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm font-medium ${match.scoreA > match.scoreB ? 'text-green-600' : 'text-red-600'}">
          ${match.scoreA} - ${match.scoreB}
        </div>
        <div class="text-xs text-gray-500">${match.scoreA > match.scoreB ? match.teamA : match.teamB} won</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap">
        <div class="text-sm text-gray-900">${match.mvp}</div>
      </td>
      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <button onclick="viewMatchDetails(${match.id})" class="text-blue-600 hover:text-blue-900 mr-3">
          <i class="fas fa-eye"></i>
        </button>
        <button onclick="downloadScoresheet('${match.scoresheet_url}')" class="text-green-600 hover:text-green-900 mr-3">
          <i class="fas fa-download"></i>
        </button>
        <button onclick="printScoresheet(${match.id})" class="text-purple-600 hover:text-purple-900">
          <i class="fas fa-print"></i>
        </button>
      </td>
    </tr>
  `).join('');
  
  updatePagination();
  updateShowingCount();
}

function filterMatches() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const tournamentFilter = document.getElementById('tournamentFilter').value;
  const yearFilter = document.getElementById('yearFilter').value;
  
  filteredMatches = allMatches.filter(match => {
    const matchesSearch = searchTerm === '' || 
      match.teamA.toLowerCase().includes(searchTerm) ||
      match.teamB.toLowerCase().includes(searchTerm) ||
      match.venue.toLowerCase().includes(searchTerm) ||
      match.tournament.toLowerCase().includes(searchTerm);
    
    const matchesTournament = tournamentFilter === '' || 
      match.tournament.includes(allTournaments.find(t => t.id == tournamentFilter)?.name || '');
    
    const matchesYear = yearFilter === '' || 
      match.date.startsWith(yearFilter);
    
    return matchesSearch && matchesTournament && matchesYear;
  });
  
  currentPage = 1;
  renderMatchesTable();
  updateActiveFilters(searchTerm, tournamentFilter, yearFilter);
}

function updateActiveFilters(searchTerm, tournamentFilter, yearFilter) {
  const activeFiltersContainer = document.getElementById('activeFilters');
  activeFiltersContainer.innerHTML = '';
  
  if (searchTerm) {
    addFilterTag(`Search: "${searchTerm}"`, 'search');
  }
  
  if (tournamentFilter) {
    const tournament = allTournaments.find(t => t.id == tournamentFilter);
    if (tournament) {
      addFilterTag(`Tournament: ${tournament.name}`, 'tournament');
    }
  }
  
  if (yearFilter) {
    addFilterTag(`Year: ${yearFilter}`, 'year');
  }
}

function addFilterTag(text, type) {
  const container = document.getElementById('activeFilters');
  const tag = document.createElement('div');
  tag.className = 'filter-tag active-filter';
  tag.innerHTML = `
    ${text}
    <button onclick="removeFilter('${type}')" class="ml-2 hover:text-gray-300">
      <i class="fas fa-times"></i>
    </button>
  `;
  container.appendChild(tag);
}

function removeFilter(type) {
  switch(type) {
    case 'search':
      document.getElementById('searchInput').value = '';
      break;
    case 'tournament':
      document.getElementById('tournamentFilter').value = '';
      break;
    case 'year':
      document.getElementById('yearFilter').value = '';
      break;
  }
  filterMatches();
}

function updatePagination() {
  const totalPages = Math.ceil(filteredMatches.length / matchesPerPage);
  const paginationContainer = document.getElementById('pagination');
  
  if (totalPages <= 1) {
    paginationContainer.innerHTML = '';
    return;
  }
  
  let paginationHTML = '';
  
  // Previous button
  if (currentPage > 1) {
    paginationHTML += `
      <button onclick="changePage(${currentPage - 1})" 
              class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
        <i class="fas fa-chevron-left"></i>
      </button>
    `;
  }
  
  // Page numbers
  for (let i = 1; i <= totalPages; i++) {
    if (i === currentPage) {
      paginationHTML += `
        <button class="px-3 py-1 rounded bg-blue-600 text-white">
          ${i}
        </button>
      `;
    } else {
      paginationHTML += `
        <button onclick="changePage(${i})" 
                class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
          ${i}
        </button>
      `;
    }
  }
  
  // Next button
  if (currentPage < totalPages) {
    paginationHTML += `
      <button onclick="changePage(${currentPage + 1})" 
              class="px-3 py-1 rounded border border-gray-300 text-gray-700 hover:bg-gray-50">
        <i class="fas fa-chevron-right"></i>
      </button>
    `;
  }
  
  paginationContainer.innerHTML = paginationHTML;
}

function changePage(page) {
  currentPage = page;
  renderMatchesTable();
}

function updateShowingCount() {
  const startIndex = (currentPage - 1) * matchesPerPage + 1;
  const endIndex = Math.min(startIndex + matchesPerPage - 1, filteredMatches.length);
  
  document.getElementById('showingCount').textContent = 
    filteredMatches.length === 0 ? '0' : `${startIndex}-${endIndex}`;
  document.getElementById('totalCount').textContent = filteredMatches.length;
}

function updateStatistics() {
  document.getElementById('totalMatches').textContent = allMatches.length;
  document.getElementById('totalTournaments').textContent = allTournaments.length;
  
  const currentMonth = new Date().getMonth() + 1;
  const currentYear = new Date().getFullYear();
  const monthMatches = allMatches.filter(match => {
    const matchDate = new Date(match.date);
    return matchDate.getMonth() + 1 === currentMonth && matchDate.getFullYear() === currentYear;
  }).length;
  
  document.getElementById('monthMatches').textContent = monthMatches;
}

function toggleView(view) {
  currentView = view;
  
  const gridBtn = document.getElementById('gridViewBtn');
  const listBtn = document.getElementById('listViewBtn');
  
  if (view === 'grid') {
    gridBtn.className = 'p-2 rounded view-active';
    listBtn.className = 'p-2 rounded bg-gray-200 text-gray-700';
  } else {
    gridBtn.className = 'p-2 rounded bg-gray-200 text-gray-700';
    listBtn.className = 'p-2 rounded view-active';
  }
  
  renderTournaments();
}

function showAddTournamentModal() {
  document.getElementById('addTournamentModal').classList.remove('hidden');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.add('hidden');
}

function addTournament() {
  const name = document.getElementById('tournamentName').value;
  const year = document.getElementById('tournamentYear').value;
  const description = document.getElementById('tournamentDescription').value;
  
  // Simulate API call - replace with actual API
  const newTournament = {
    id: allTournaments.length + 1,
    name: name,
    year: year,
    description: description,
    match_count: 0
  };
  
  allTournaments.push(newTournament);
  updateTournamentFilters();
  renderTournaments();
  updateStatistics();
  
  // Reset form and close modal
  document.getElementById('tournamentForm').reset();
  closeModal('addTournamentModal');
  
  alert('Tournament added successfully!');
}

function viewTournamentMatches(tournamentId) {
  const tournament = allTournaments.find(t => t.id === tournamentId);
  if (tournament) {
    document.getElementById('tournamentFilter').value = tournamentId;
    filterMatches();
    
    // Scroll to matches table
    document.getElementById('matchesTableBody').scrollIntoView({ behavior: 'smooth' });
  }
}

function viewMatchDetails(matchId) {
  const match = allMatches.find(m => m.id === matchId);
  if (!match) return;
  
  const content = document.getElementById('matchDetailsContent');
  content.innerHTML = `
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <h4 class="font-semibold text-gray-900 mb-2">Match Information</h4>
        <div class="space-y-2">
          <div class="flex justify-between">
            <span class="text-gray-600">Date:</span>
            <span class="font-medium">${formatDate(match.date)}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Time:</span>
            <span class="font-medium">${match.time}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Venue:</span>
            <span class="font-medium">${match.venue}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">Tournament:</span>
            <span class="font-medium">${match.tournament}</span>
          </div>
        </div>
      </div>
      
      <div>
        <h4 class="font-semibold text-gray-900 mb-2">Match Result</h4>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="flex justify-between items-center mb-2">
            <span class="font-medium">${match.teamA}</span>
            <span class="font-bold text-lg ${match.scoreA > match.scoreB ? 'text-green-600' : 'text-gray-900'}">${match.scoreA}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="font-medium">${match.teamB}</span>
            <span class="font-bold text-lg ${match.scoreB > match.scoreA ? 'text-green-600' : 'text-gray-900'}">${match.scoreB}</span>
          </div>
          <div class="mt-3 text-center">
            <span class="text-sm text-gray-600">Winner: </span>
            <span class="font-semibold">${match.scoreA > match.scoreB ? match.teamA : match.teamB}</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mt-6">
      <h4 class="font-semibold text-gray-900 mb-2">Most Valuable Player</h4>
      <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
        <div class="flex items-center">
          <i class="fas fa-trophy text-yellow-500 text-xl mr-3"></i>
          <div>
            <span class="font-semibold">${match.mvp}</span>
            <p class="text-sm text-gray-600 mt-1">Awarded for outstanding performance in this match</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mt-6 flex justify-end gap-3">
      <button onclick="downloadScoresheet('${match.scoresheet_url}')" 
              class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
        <i class="fas fa-download mr-2"></i> Download Scoresheet
      </button>
      <button onclick="printScoresheet(${match.id})" 
              class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
        <i class="fas fa-print mr-2"></i> Print Scoresheet
      </button>
    </div>
  `;
  
  document.getElementById('matchDetailsModal').classList.remove('hidden');
}

function downloadScoresheet(url) {
  // Simulate download - replace with actual download logic
  alert(`Downloading scoresheet: ${url}`);
  // window.location.href = url; // Uncomment for actual download
}

function printScoresheet(matchId) {
  // Simulate print - replace with actual print logic
  alert(`Printing scoresheet for match ${matchId}`);
  // window.open(`print_scoresheet.php?match_id=${matchId}`, '_blank');
}

function exportAllData() {
  // Simulate export - replace with actual export logic
  const data = {
    tournaments: allTournaments,
    matches: allMatches
  };
  
  const dataStr = JSON.stringify(data, null, 2);
  const dataBlob = new Blob([dataStr], {type: 'application/json'});
  
  const url = URL.createObjectURL(dataBlob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `scoresheets_export_${new Date().toISOString().split('T')[0]}.json`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}
</script>