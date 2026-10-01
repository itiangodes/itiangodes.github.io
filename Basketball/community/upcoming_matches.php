<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<style>
    .matches-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: linear-gradient(135deg, #10b981, #059669);
        border-radius: 15px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .filter-bar {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 10px 20px;
        border: 2px solid #e5e7eb;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s;
    }

    .filter-tab:hover {
        border-color: #10b981;
        color: #10b981;
    }

    .filter-tab.active {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .match-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
        border: 2px solid transparent;
    }

    .match-card:hover {
        border-color: #10b981;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
        transform: translateY(-3px);
    }

    .match-badge {
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .badge-semifinals { background: #fee2e2; color: #991b1b; }
    .badge-finals { background: #fef3c7; color: #92400e; }
    .badge-regular { background: #dbeafe; color: #1e40af; }
    .badge-playoffs { background: #f3e8ff; color: #6b21a8; }

    .team-display {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .team-logo {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        color: white;
    }

    .calendar-mini {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        position: sticky;
        top: 20px;
    }

    .month-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }

    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .calendar-day:hover {
        background: #f3f4f6;
    }

    .calendar-day.has-match {
        background: #d1fae5;
        color: #065f46;
        font-weight: 700;
    }

    .calendar-day.today {
        background: #fef3c7;
        border: 2px solid #f59e0b;
    }

    .two-column-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 25px;
    }

    @media (max-width: 1024px) {
        .two-column-layout {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="matches-container">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="text-3xl font-bold mb-2">
            <i class="fas fa-calendar-alt mr-2"></i>Upcoming Matches
        </h1>
        <p class="text-green-100">Stay updated with all scheduled basketball games across Tanza</p>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-gray-800">Filter Matches</h3>
            <span class="text-sm text-gray-600">8 matches found</span>
        </div>
        <div class="filter-tabs">
            <button class="filter-tab active">All Matches</button>
            <button class="filter-tab">Regular Season</button>
            <button class="filter-tab">Playoffs</button>
            <button class="filter-tab">Finals</button>
            <button class="filter-tab">This Week</button>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="two-column-layout">
        <!-- Left: Matches List -->
        <div>
            <!-- Match 1 -->
            <div class="match-card">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>November 15, 2025
                        </span>
                        <span class="text-sm font-medium text-gray-500 ml-3">
                            <i class="fas fa-clock mr-1"></i>3:00 PM
                        </span>
                    </div>
                    <span class="match-badge badge-semifinals">Semifinals</span>
                </div>

                <div class="grid grid-cols-3 items-center gap-4 mb-4">
                    <div class="team-display">
                        <div class="team-logo" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">PI</div>
                        <div>
                            <p class="font-bold text-gray-900">Punta I</p>
                            <p class="text-sm text-gray-600">Warriors</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-400">VS</p>
                    </div>

                    <div class="team-display justify-end">
                        <div>
                            <p class="font-bold text-gray-900 text-right">Amaya</p>
                            <p class="text-sm text-gray-600 text-right">Titans</p>
                        </div>
                        <div class="team-logo" style="background: linear-gradient(135deg, #ef4444, #dc2626);">AM</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1"></i>Punta Basketball Court
                    </span>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                        <i class="fas fa-info-circle mr-1"></i>Details
                    </button>
                </div>
            </div>

            <!-- Match 2 -->
            <div class="match-card">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>November 16, 2025
                        </span>
                        <span class="text-sm font-medium text-gray-500 ml-3">
                            <i class="fas fa-clock mr-1"></i>5:00 PM
                        </span>
                    </div>
                    <span class="match-badge badge-regular">Regular Season</span>
                </div>

                <div class="grid grid-cols-3 items-center gap-4 mb-4">
                    <div class="team-display">
                        <div class="team-logo" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">PII</div>
                        <div>
                            <p class="font-bold text-gray-900">Punta II</p>
                            <p class="text-sm text-gray-600">Knights</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-400">VS</p>
                    </div>

                    <div class="team-display justify-end">
                        <div>
                            <p class="font-bold text-gray-900 text-right">Poblace</p>
                            <p class="text-sm text-gray-600 text-right">Stars</p>
                        </div>
                        <div class="team-logo" style="background: linear-gradient(135deg, #f59e0b, #d97706);">PS</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1"></i>Central Sports Complex
                    </span>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                        <i class="fas fa-info-circle mr-1"></i>Details
                    </button>
                </div>
            </div>

            <!-- Match 3 -->
            <div class="match-card">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>November 18, 2025
                        </span>
                        <span class="text-sm font-medium text-gray-500 ml-3">
                            <i class="fas fa-clock mr-1"></i>2:00 PM
                        </span>
                    </div>
                    <span class="match-badge badge-playoffs">Playoffs</span>
                </div>

                <div class="grid grid-cols-3 items-center gap-4 mb-4">
                    <div class="team-display">
                        <div class="team-logo" style="background: linear-gradient(135deg, #10b981, #059669);">BU</div>
                        <div>
                            <p class="font-bold text-gray-900">Bucal</p>
                            <p class="text-sm text-gray-600">Rangers</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-400">VS</p>
                    </div>

                    <div class="team-display justify-end">
                        <div>
                            <p class="font-bold text-gray-900 text-right">Sahud Ulan</p>
                            <p class="text-sm text-gray-600 text-right">Thunder</p>
                        </div>
                        <div class="team-logo" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">SU</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1"></i>Bucal Community Court
                    </span>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                        <i class="fas fa-info-circle mr-1"></i>Details
                    </button>
                </div>
            </div>

            <!-- Match 4 -->
            <div class="match-card">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <span class="text-sm font-medium text-gray-500">
                            <i class="fas fa-calendar mr-1"></i>November 20, 2025
                        </span>
                        <span class="text-sm font-medium text-gray-500 ml-3">
                            <i class="fas fa-clock mr-1"></i>4:00 PM
                        </span>
                    </div>
                    <span class="match-badge badge-finals">Finals</span>
                </div>

                <div class="grid grid-cols-3 items-center gap-4 mb-4">
                    <div class="team-display">
                        <div class="team-logo" style="background: linear-gradient(135deg, #ec4899, #db2777);">BA</div>
                        <div>
                            <p class="font-bold text-gray-900">Bagbag</p>
                            <p class="text-sm text-gray-600">Eagles</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-3xl font-bold text-gray-400">VS</p>
                    </div>

                    <div class="team-display justify-end">
                        <div>
                            <p class="font-bold text-gray-900 text-right">TBD</p>
                            <p class="text-sm text-gray-600 text-right">Winner SF2</p>
                        </div>
                        <div class="team-logo" style="background: linear-gradient(135deg, #6b7280, #4b5563);">?</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-3 border-t">
                    <span class="text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-1"></i>Championship Arena
                    </span>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700 transition">
                        <i class="fas fa-info-circle mr-1"></i>Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Mini Calendar -->
        <div>
            <div class="calendar-mini">
                <div class="month-header">
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
                        <i class="fas fa-chevron-left text-gray-600"></i>
                    </button>
                    <h3 class="font-bold text-gray-800">November 2025</h3>
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100">
                        <i class="fas fa-chevron-right text-gray-600"></i>
                    </button>
                </div>

                <div class="calendar-grid mb-2">
                    <div class="text-center text-xs font-semibold text-gray-500">S</div>
                    <div class="text-center text-xs font-semibold text-gray-500">M</div>
                    <div class="text-center text-xs font-semibold text-gray-500">T</div>
                    <div class="text-center text-xs font-semibold text-gray-500">W</div>
                    <div class="text-center text-xs font-semibold text-gray-500">T</div>
                    <div class="text-center text-xs font-semibold text-gray-500">F</div>
                    <div class="text-center text-xs font-semibold text-gray-500">S</div>
                </div>

                <div class="calendar-grid">
                    <div class="calendar-day" style="opacity: 0.3;">27</div>
                    <div class="calendar-day" style="opacity: 0.3;">28</div>
                    <div class="calendar-day" style="opacity: 0.3;">29</div>
                    <div class="calendar-day" style="opacity: 0.3;">30</div>
                    <div class="calendar-day" style="opacity: 0.3;">31</div>
                    <div class="calendar-day">1</div>
                    <div class="calendar-day">2</div>
                    <div class="calendar-day">3</div>
                    <div class="calendar-day">4</div>
                    <div class="calendar-day">5</div>
                    <div class="calendar-day">6</div>
                    <div class="calendar-day">7</div>
                    <div class="calendar-day">8</div>
                    <div class="calendar-day today">9</div>
                    <div class="calendar-day">10</div>
                    <div class="calendar-day">11</div>
                    <div class="calendar-day">12</div>
                    <div class="calendar-day">13</div>
                    <div class="calendar-day">14</div>
                    <div class="calendar-day has-match">15</div>
                    <div class="calendar-day has-match">16</div>
                    <div class="calendar-day">17</div>
                    <div class="calendar-day has-match">18</div>
                    <div class="calendar-day">19</div>
                    <div class="calendar-day has-match">20</div>
                    <div class="calendar-day">21</div>
                    <div class="calendar-day">22</div>
                    <div class="calendar-day">23</div>
                    <div class="calendar-day">24</div>
                    <div class="calendar-day">25</div>
                    <div class="calendar-day">26</div>
                    <div class="calendar-day">27</div>
                    <div class="calendar-day">28</div>
                    <div class="calendar-day">29</div>
                    <div class="calendar-day">30</div>
                </div>

                <div class="mt-4 text-xs text-gray-600">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-4 h-4 rounded bg-green-200"></div>
                        <span>Match Day</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded bg-yellow-200 border-2 border-yellow-500"></div>
                        <span>Today</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
