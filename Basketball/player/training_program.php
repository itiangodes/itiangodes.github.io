<?php
include 'includes/player_header.php';
include 'includes/player_nav.php';
include '../includes/db.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$player_username = $_SESSION['username'];

// 🔹 Get player info from users table
$stmt = $pdo->prepare("SELECT user_id, firstname, middlename, lastname FROM users WHERE username = ? AND role = 'player'");
$stmt->execute([$player_username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p class='text-center text-red-500 mt-10'>Player not found.</p>";
    include 'includes/player_footer.php';
    exit;
}

// Combine names for display
$user['fullname'] = trim($user['firstname'] . ' ' . 
                        ($user['middlename'] ? $user['middlename'] . ' ' : '') . 
                        $user['lastname']);

// 🔹 Get player_id from players table
$stmt_player = $pdo->prepare("SELECT player_id FROM players WHERE user_id = ?");
$stmt_player->execute([$user['user_id']]);
$player_data = $stmt_player->fetch(PDO::FETCH_ASSOC);

if (!$player_data) {
    echo "<p class='text-center text-red-500 mt-10'>Player profile not found.</p>";
    include 'includes/player_footer.php';
    exit;
}

$player_id = $player_data['player_id'];

// 🔹 Fetch training program
$stmt_prog = $pdo->prepare("SELECT * FROM training_programs WHERE player_id = ? LIMIT 1");
$stmt_prog->execute([$player_id]);
$program = $stmt_prog->fetch(PDO::FETCH_ASSOC);

// Set timezone for date calculations
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
?>

<section class="p-6 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-running text-purple-600 mr-2"></i>Training Program
    </h2>

    <?php if (!$program): ?>
        <div class="text-center bg-white rounded-xl shadow p-8">
            <h3 class="text-xl font-semibold text-gray-800 mb-2">🏀 No Training Program Yet</h3>
            <p class="text-gray-600">Your coach hasn't assigned a training plan yet. Please check again later.</p>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($user['fullname']); ?>'s Training Plan</h3>
                <span class="px-3 py-1 text-sm font-semibold rounded-full <?= $program['status'] === 'In Progress' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'; ?>">
                    <?= htmlspecialchars($program['status']); ?>
                </span>
            </div>
            <p class="text-gray-600 mb-6">📅 Duration: <?= htmlspecialchars($program['duration']); ?></p>

            <?php
            $stmt_weeks = $pdo->prepare("SELECT * FROM training_weeks WHERE program_id = ? ORDER BY week_number ASC");
            $stmt_weeks->execute([$program['program_id']]);
            $weeks = $stmt_weeks->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($weeks)): ?>
                <p class="text-gray-500 italic text-center">No weeks added yet by your coach.</p>
            <?php else: ?>
                <?php foreach ($weeks as $week): 
                    // Calculate week dates
                    $week_start_date = $week['start_date'] ?? date('Y-m-d', strtotime('monday this week'));
                    $days_of_week = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
                    $week_dates = [];
                    
                    for ($i = 0; $i < 7; $i++) {
                        $current_date = date('Y-m-d', strtotime($week_start_date . " +$i days"));
                        $week_dates[$days_of_week[$i]] = $current_date;
                    }
                ?>
                    <div class="mb-10 border-t border-gray-200 pt-6">
                        <h4 class="text-xl font-bold text-gray-800 mb-3">🏁 Week <?= $week['week_number']; ?>
                            <span class="text-sm font-normal text-gray-600 ml-2">
                                (<?= date('M j', strtotime($week_start_date)) ?> - <?= date('M j, Y', strtotime($week_start_date . ' +6 days')) ?>)</span>
                        </h4>

                        <!-- Weekly Summary -->
                        <?php
                        $stmt_summary = $pdo->prepare("
                            SELECT 
                                SUM(type = 'Workout') AS workouts,
                                SUM(type = 'Drill') AS drills,
                                SUM(type = 'Rest') AS rest_days
                            FROM training_activities ta
                            JOIN training_days td ON ta.day_id = td.day_id
                            WHERE td.week_id = ?
                        ");
                        $stmt_summary->execute([$week['week_id']]);
                        $summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);
                        ?>
                        
                        <div class="flex flex-wrap gap-3 mb-6 text-sm">
                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-medium">🏋️ <?= $summary['workouts'] ?? 0; ?> Workouts</span>
                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full font-medium">🏀 <?= $summary['drills'] ?? 0; ?> Drills</span>
                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full font-medium">😴 <?= $summary['rest_days'] ?? 0; ?> Rest Days</span>
                        </div>

                        <!-- Weekly Overview with Performance Ratings -->
                        <div class="weekly-overview-grid">
                            <?php foreach ($days_of_week as $day): 
                                $day_date = $week_dates[$day] ?? '';
                                $is_past = $day_date && strtotime($day_date) < strtotime($today);
                                $is_today = $day_date === $today;
                                $is_future = $day_date && strtotime($day_date) > strtotime($today);
                                
                                // Find the day_id for this week and day
                                $stmt_day = $pdo->prepare("
                                    SELECT td.day_id 
                                    FROM training_days td 
                                    WHERE td.week_id = ? AND td.day_name = ?
                                ");
                                $stmt_day->execute([$week['week_id'], $day]);
                                $day_row = $stmt_day->fetch(PDO::FETCH_ASSOC);

                                $activities = [];
                                $has_activities = false;
                                $is_rest_day = false;

                                // Fetch activities if day exists
                                if ($day_row) {
                                    $stmt_acts = $pdo->prepare("
                                        SELECT * FROM training_activities 
                                        WHERE day_id = ?
                                        ORDER BY activity_id ASC
                                    ");
                                    $stmt_acts->execute([$day_row['day_id']]);
                                    $activities = $stmt_acts->fetchAll(PDO::FETCH_ASSOC);
                                    $has_activities = count($activities) > 0;
                                    
                                    // Check if it's a rest day
                                    foreach ($activities as $act) {
                                        if ($act['type'] === 'Rest') {
                                            $is_rest_day = true;
                                            break;
                                        }
                                    }
                                }

                                // Get performance rating for this day
                                $existing_rating = null;
                                if ($day_date) {
                                    $stmt_rating = $pdo->prepare("
                                        SELECT * FROM training_performance_ratings 
                                        WHERE player_id = ? AND rating_date = ?
                                    ");
                                    $stmt_rating->execute([$player_id, $day_date]);
                                    $existing_rating = $stmt_rating->fetch(PDO::FETCH_ASSOC);
                                }
                                
                                $current_rating = $existing_rating ? $existing_rating['performance_rating'] : 0;
                                $has_rating = $current_rating > 0;
                            ?>
                            
                            <div class="day-column 
                                <?= $is_rest_day ? 'rest-day' : ''; ?>
                                <?= $is_past ? 'past-day' : ''; ?>
                                <?= $is_today ? 'today' : ''; ?>
                                <?= $is_future ? 'future-day' : ''; ?>
                                <?= $has_activities ? 'has-activities' : ''; ?>">
                                
                                <!-- Day Header -->
                                <div class="text-center mb-4">
                                    <div class="font-bold text-gray-800 text-lg"><?= $day; ?></div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        <?= $day_date ? date('M j', strtotime($day_date)) : ''; ?>
                                    </div>
                                    <div class="text-xs font-semibold mt-1">
                                        <?php if ($is_today): ?>
                                            <span class="text-blue-600">📍 Today</span>
                                        <?php elseif ($is_past): ?>
                                            <span class="text-red-600">⏰ Past</span>
                                        <?php elseif ($is_future): ?>
                                            <span class="text-green-600">📮 Future</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Activities Section -->
                                <div class="activities-scroll">
                                    <?php if ($is_rest_day): ?>
                                        <div class="text-center text-green-600 bg-green-50 py-3 rounded-lg border border-green-200">
                                            <div class="text-2xl mb-1">😴</div>
                                            <div class="text-sm font-semibold">Rest Day</div>
                                        </div>
                                    <?php elseif (empty($activities)): ?>
                                        <div class="empty-activities">
                                            – No Activities –
                                        </div>
                                    <?php else: ?>
                                        <div class="space-y-2">
                                            <?php foreach ($activities as $act): 
                                                // Skip rest day activities in the display when there are other activities
                                                if ($act['type'] === 'Rest') continue;
                                                
                                                $color = $act['type'] === 'Workout' ? 'bg-blue-100 text-blue-700 border-blue-200' :
                                                        ($act['type'] === 'Drill' ? 'bg-yellow-100 text-yellow-700 border-yellow-200' :
                                                        'bg-green-100 text-green-700 border-green-200');
                                            ?>
                                            <div class="<?= $color; ?> px-3 py-2 rounded-lg border flex items-center justify-between">
                                                <span class="text-sm font-medium">
                                                    <?php 
                                                        if ($act['type'] === 'Workout') echo '🏋️ Workout';
                                                        elseif ($act['type'] === 'Drill') echo '🏀 Drill';
                                                        else echo '😴 Rest';
                                                    ?>
                                                </span>
                                                <div class="flex gap-1">
                                                    <?php if ($act['type'] !== 'Rest'): ?>
                                                        <!-- Box "i" for basic activity info -->
                                                        <button 
                                                            type="button" 
                                                            onclick="showActivityDetails('<?= htmlspecialchars(addslashes($act['title']), ENT_QUOTES); ?>', '<?= htmlspecialchars(addslashes($act['description']), ENT_QUOTES); ?>', '<?= $act['activity_date'] ?? ''; ?>')" 
                                                            class="icon-btn flex-shrink-0 w-8 h-8 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition" 
                                                            title="View basic activity info">
                                                            <span class="text-gray-700 font-bold">i</span>
                                                        </button>
                                                        
                                                        <!-- Circular "i" for detailed drill/workout info -->
                                                        <button 
                    type="button" 
                    <?php if ($act['type'] === 'Drill'): ?>
                        onclick="showDrillDetails(<?= $act['activity_id']; ?>)"
                        class="icon-btn flex-shrink-0 w-8 h-8 flex items-center justify-center border border-yellow-300 rounded-full hover:bg-yellow-50 transition" 
                        title="View drill details"
                    <?php elseif ($act['type'] === 'Workout'): ?>
                        onclick="showWorkoutDetails(<?= $act['activity_id']; ?>)"
                        class="icon-btn flex-shrink-0 w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50 transition" 
                        title="View workout details"
                    <?php endif; ?>
                    >
                    <svg width="16" height="16" fill="none" aria-hidden="true">
                        <circle cx="8" cy="8" r="7" stroke="#21808d" stroke-width="1.5"/>
                        <text x="8" y="11" font-size="8" text-anchor="middle" fill="#21808d" font-weight="bold">i</text>
                    </svg>
                </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Performance Rating Section -->
                                <div class="mt-auto pt-3 border-t border-gray-100">
                                    <?php if ($has_activities && !$is_rest_day): ?>
                                        <!-- Rating Display -->
                                        <div class="rating-status <?= $has_rating ? 'rated' : 'not-rated'; ?>">
                                            <?php if ($has_rating): ?>
                                                ⭐ Rated: <?= number_format($current_rating, 1); ?>/5.0
                                            <?php else: ?>
                                                📊 Not Rated Yet
                                            <?php endif; ?>
                                        </div>

                                        <!-- View Rating Details Button -->
                                        <?php if ($has_rating): ?>
                                            <button type="button" 
                                                    onclick="showRatingDetails(<?= $current_rating; ?>, `<?= $existing_rating ? htmlspecialchars($existing_rating['coach_notes']) : ''; ?>`, '<?= $day; ?>', '<?= $day_date; ?>')"
                                                    class="mt-2 w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-2 rounded text-xs font-medium transition">
                                                📊 View Performance Rating
                                            </button>
                                        <?php endif; ?>

                                        <!-- View Coach Notes Button -->
                                        <?php if ($existing_rating && !empty($existing_rating['coach_notes'])): ?>
                                            <button type="button" 
                                                    onclick="showCoachNotes('<?= htmlspecialchars($existing_rating['coach_notes'], ENT_QUOTES); ?>')"
                                                    class="mt-2 w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-2 rounded text-xs font-medium transition">
                                                📝 View Coach Notes
                                            </button>
                                        <?php endif; ?>
                                    <?php elseif ($is_rest_day): ?>
                                        <div class="empty-rating">
                                            😴 Rest Day - No Rating
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-rating">
                                            No training to rate
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Activity Details Modal -->
<div id="infoModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 relative">
    <div class="flex justify-between items-center mb-4">
      <h3 id="infoModalTitle" class="text-xl font-bold text-gray-800">Activity Details</h3>
      <button type="button" onclick="closeInfoModal()" class="text-gray-500 hover:text-gray-800">✕</button>
    </div>
    <div id="infoModalBody" class="text-gray-700"></div>
    <div class="mt-6 flex justify-end">
      <button type="button" onclick="closeInfoModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
    </div>
  </div>
</div>

<!-- Drill Details Modal -->
<div id="drillModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-auto max-h-[90vh] overflow-hidden">
    <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-yellow-50">
      <h3 id="drillModalTitle" class="text-xl font-bold text-yellow-800">🏀 Drill Details</h3>
      <button type="button" onclick="closeDrillModal()" class="text-yellow-600 hover:text-yellow-800 text-2xl font-bold">×</button>
    </div>
    <div class="p-6 overflow-y-auto max-h-[70vh]">
      <div id="drillContent" class="text-gray-700">
        <!-- Content will be loaded via AJAX -->
      </div>
      <div class="mt-6 flex justify-end">
        <button type="button" onclick="closeDrillModal()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Workout Details Modal -->
<div id="workoutModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-auto max-h-[90vh] overflow-hidden">
    <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-blue-50">
      <h3 id="workoutModalTitle" class="text-xl font-bold text-blue-800">🏋️ Workout Details</h3>
      <button type="button" onclick="closeWorkoutModal()" class="text-blue-600 hover:text-blue-800 text-2xl font-bold">×</button>
    </div>
    <div class="p-6 overflow-y-auto max-h-[70vh]">
      <div id="workoutContent" class="text-gray-700">
        <!-- Content will be loaded via AJAX -->
      </div>
      <div class="mt-6 flex justify-end">
        <button type="button" onclick="closeWorkoutModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Rating Details Modal -->
<div id="ratingDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-auto">
    <div class="flex justify-between items-center p-6 border-b border-gray-200">
      <h3 id="ratingDetailsTitle" class="text-xl font-bold text-gray-800">Performance Rating</h3>
      <button type="button" onclick="closeRatingDetailsModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
    </div>
    <div class="p-6">
      <div class="mb-6 text-center">
        <div class="text-4xl mb-2" id="ratingStarsDisplay"></div>
        <div id="ratingValueDisplay" class="text-2xl font-bold text-gray-800 mb-1"></div>
        <div id="ratingDescription" class="text-sm text-gray-600"></div>
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-2">Coach's Notes & Advice</label>
        <div id="coachNotesDisplay" class="bg-gray-50 border border-gray-300 rounded-lg p-3 text-sm text-gray-700 whitespace-pre-wrap min-h-[100px]"></div>
      </div>

      <div class="flex justify-end">
        <button type="button" onclick="closeRatingDetailsModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Coach Notes Modal -->
<div id="notesModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-auto">
    <div class="flex justify-between items-center p-6 border-b border-gray-200">
      <h3 class="text-xl font-bold text-gray-800">Coach's Notes</h3>
      <button type="button" onclick="closeNotesModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
    </div>
    <div class="p-6">
      <div id="notesContent" class="text-gray-700 whitespace-pre-wrap"></div>
      <div class="mt-6 flex justify-end">
        <button type="button" onclick="closeNotesModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Activity Details Functions
function showActivityDetails(title, description, activityDate) {
    const modal = document.getElementById('infoModal');
    const body = document.getElementById('infoModalBody');
    const heading = document.getElementById('infoModalTitle');

    heading.innerText = title;
    if (!description) description = "No details provided.";

    // Parse details from the enhanced description format
    const details = {
        Date: activityDate ? new Date(activityDate).toLocaleDateString() : '—',
        Type: description.match(/Type:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
              description.match(/Category:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
        Duration: description.match(/Duration:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
        Sets: description.match(/Sets:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
        Location: description.match(/Location:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
        CallTime: description.match(/Call Time:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
        Instructions: description.match(/Instructions:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
                      description.split('Instructions:').pop() || '—'
    };

    body.innerHTML = `
        <table class="w-full text-sm text-gray-700 border-collapse">
            <tr class="border-b"><td class="py-2 font-semibold w-1/3">📅 Date</td><td class="py-2">${details.Date}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🎯 Type/Category</td><td class="py-2">${details.Type}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🕒 Duration</td><td class="py-2">${details.Duration} minutes</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🔁 Sets</td><td class="py-2">${details.Sets}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">📍 Location</td><td class="py-2">${details.Location}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">⏰ Call Time</td><td class="py-2">${details.CallTime}</td></tr>
            <tr><td class="py-2 font-semibold align-top">📝 Instructions</td><td class="py-2">${details.Instructions}</td></tr>
        </table>
    `;

    modal.classList.remove('hidden');
}

function closeInfoModal() {
    document.getElementById('infoModal').classList.add('hidden');
}

// Drill Details Functions
function showDrillDetails(activityId) {
    const modal = document.getElementById('drillModal');
    const content = document.getElementById('drillContent');
    
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-yellow-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading drill details...</p>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // AJAX call to fetch drill details
    fetch(`get_drill_details.php?activity_id=${activityId}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center text-red-600 py-8">
                    <p>❌ Error loading drill details. Please try again.</p>
                    <p class="text-sm text-gray-500 mt-2">${error}</p>
                </div>
            `;
        });
}

function closeDrillModal() {
    document.getElementById('drillModal').classList.add('hidden');
}

// Workout Details Functions
function showWorkoutDetails(activityId) {
    const modal = document.getElementById('workoutModal');
    const content = document.getElementById('workoutContent');
    
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading workout details...</p>
        </div>
    `;
    
    modal.classList.remove('hidden');
    
    // AJAX call to fetch workout details
    fetch(`get_workout_details.php?activity_id=${activityId}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="text-center text-red-600 py-8">
                    <p>❌ Error loading workout details. Please try again.</p>
                    <p class="text-sm text-gray-500 mt-2">${error}</p>
                </div>
            `;
        });
}

function closeWorkoutModal() {
    document.getElementById('workoutModal').classList.add('hidden');
}

// Rating Details Functions
function showRatingDetails(rating, coachNotes, dayName, dayDate) {
    const modal = document.getElementById('ratingDetailsModal');
    const starsDisplay = document.getElementById('ratingStarsDisplay');
    const valueDisplay = document.getElementById('ratingValueDisplay');
    const descriptionDisplay = document.getElementById('ratingDescription');
    const notesDisplay = document.getElementById('coachNotesDisplay');
    const titleDisplay = document.getElementById('ratingDetailsTitle');

    titleDisplay.textContent = `Performance Rating - ${dayName}`;
    
    // Display stars
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            starsHtml += '★';
        } else {
            starsHtml += '☆';
        }
    }
    starsDisplay.innerHTML = starsHtml;
    starsDisplay.className = 'text-4xl mb-2 text-yellow-400';
    
    // Display rating value and description
    valueDisplay.textContent = `${rating.toFixed(1)}/5.0 - ${getRatingDescription(rating)}`;
    descriptionDisplay.textContent = getRatingCriteria(rating);
    
    // Display coach notes
    notesDisplay.textContent = coachNotes || 'No additional notes from coach.';

    modal.classList.remove('hidden');
}

function closeRatingDetailsModal() {
    document.getElementById('ratingDetailsModal').classList.add('hidden');
}

function getRatingDescription(rating) {
    if (rating >= 4.5) return 'Outstanding';
    if (rating >= 3.5) return 'Very Satisfactory';
    if (rating >= 2.5) return 'Satisfactory';
    if (rating >= 1.5) return 'Fair';
    return 'Needs Improvement';
}

function getRatingCriteria(rating) {
    if (rating >= 4.5) return 'Exceptional effort, technique, and attitude';
    if (rating >= 3.5) return 'Strong performance with good execution';
    if (rating >= 2.5) return 'Adequate performance with room for improvement';
    if (rating >= 1.5) return 'Basic requirements met, needs work';
    return 'Significant improvement needed';
}

// Coach Notes Functions
function showCoachNotes(notes) {
    document.getElementById('notesModal').classList.remove('hidden');
    document.getElementById('notesContent').textContent = notes;
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
}

// Close modals when clicking outside
document.addEventListener('click', function(e) {
    if (e.target.id === 'infoModal') closeInfoModal();
    if (e.target.id === 'drillModal') closeDrillModal();
    if (e.target.id === 'workoutModal') closeWorkoutModal();
    if (e.target.id === 'ratingDetailsModal') closeRatingDetailsModal();
    if (e.target.id === 'notesModal') closeNotesModal();
});
</script>

<style>
.weekly-overview-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 12px;
    margin-top: 16px;
}

.day-column {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    min-height: 300px;
    transition: all 0.2s ease;
}

.day-column:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.day-column.rest-day {
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.day-column.past-day {
    background: #f9fafb;
    opacity: 0.7;
}

.day-column.today {
    border-color: #3b82f6;
    border-width: 2px;
}

.day-column.future-day {
    border-color: #bbf7d0;
}

.activities-scroll {
    flex: 1;
    overflow-y: auto;
    max-height: 200px;
    margin-bottom: 12px;
}

.empty-activities {
    text-align: center;
    color: #9ca3af;
    font-style: italic;
    padding: 20px 0;
}

.rating-status {
    text-align: center;
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.rating-status.rated {
    color: #059669;
}

.rating-status.not-rated {
    color: #6b7280;
}

.empty-rating {
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
    padding: 12px 0;
}

@media (max-width: 1024px) {
    .weekly-overview-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 768px) {
    .weekly-overview-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .weekly-overview-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include 'includes/player_footer.php'; ?>