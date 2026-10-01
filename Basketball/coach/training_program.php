<?php
ob_start();
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/db.php';

// =================== DELETE HANDLERS USING PDO ===================

// DELETE SINGLE ACTIVITY
if (isset($_POST['delete_activity'])) {

    $activity_id = $_POST['activity_id']; // from JS

    $stmt = $pdo->prepare("DELETE FROM training_activities WHERE activity_id = ?");
    $stmt->execute([$activity_id]);

    header("Location: training_program.php");
    exit;
}

// DELETE ALL ACTIVITIES ON A SPECIFIC DATE
if (isset($_POST['delete_all_activities'])) {

    $activity_date = $_POST['activity_date'];

    $stmt = $pdo->prepare("DELETE FROM training_activities WHERE activity_date = ?");
    $stmt->execute([$activity_date]);

    header("Location: training_program.php");
    exit;
}


// DEBUG: Check if delete is being triggered
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteactivity'])) {
    error_log("DELETE ACTIVITY TRIGGERED - Activity ID: " . ($_POST['activityid'] ?? 'none'));
    error_log("User ID: " . ($user_id ?? 'none') . ", Player ID: " . ($player_id ?? 'none'));
}


// Set Manila timezone to ensure correct date calculations
date_default_timezone_set('Asia/Manila');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n'); // 1–12

// Compute previous & next month based on the selected month (NOT today)
$prev_month = $month - 1;
$prev_year  = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year  = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

// Month name for this page
$month_name = date("F", strtotime("$year-$month-01"));


// =================== TOAST HANDLER ===================
$toast = null;
if (isset($_SESSION['toast'])) {
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

// =================== LOGIN CHECK ===================
if (!isset($_SESSION['username'])) {
    echo "<p class='text-center text-red-500 mt-10'>You must be logged in as a coach to access this page.</p>";
    include 'includes/footer.php';
    ob_end_flush();
    exit;
}

$coach_username = $_SESSION['username'];

// =================== TEAM CHECK ===================
// Modified: Check for coach's barangay team instead of team_name
$stmt_team = $pdo->prepare("SELECT * FROM teams WHERE coach_username = ? LIMIT 1");
$stmt_team->execute([$coach_username]);
$team = $stmt_team->fetch(PDO::FETCH_ASSOC);

if (!$team):
?>

<section class="p-6">
  <div class="max-w-2xl mx-auto text-center bg-white shadow-md rounded-xl p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-3">🚫 You don't have a team yet</h2>
    <p class="text-gray-600 mb-6">Create a team first before setting up a training program for your players.</p>
    <a href="create_team.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition">
      ➕ Go to Create Team
    </a>
  </div>
</section>
<?php
include 'includes/footer.php';
ob_end_flush();
exit;
endif;

$team_id = $team['id'];

// Handle month navigation
if (isset($_GET['month']) && isset($_GET['year'])) {
    $current_month = intval($_GET['month']);
    $current_year = intval($_GET['year']);
} else {
    $current_month = date('m');
    $current_year = date('Y');
}

// =================== FETCH PLAYERS ===================
$stmt_players = $pdo->prepare("
  SELECT u.user_id, u.firstname, u.middlename, u.lastname, u.username, u.barangay
  FROM player_teams pt
  JOIN users u ON pt.player_username = u.username
  WHERE pt.team_id = ?
");
$stmt_players->execute([$team_id]);
$players = $stmt_players->fetchAll(PDO::FETCH_ASSOC);

// Combine firstname, middlename, and lastname into fullname for display
foreach ($players as &$player) {
    $player['fullname'] = trim($player['firstname'] . ' ' . 
                          ($player['middlename'] ? $player['middlename'] . ' ' : '') . 
                          $player['lastname']);
    // Add player_id as user_id for compatibility
    $player['player_id'] = $player['user_id'];
}
unset($player); // break the reference

// =================== HELPER FUNCTIONS ===================
function findWeekForDate($weeks, $targetDate) {
    foreach ($weeks as $week) {
        $weekStart = $week['start_date'];
        for ($i = 0; $i < 7; $i++) {
            $currentDate = date('Y-m-d', strtotime($weekStart . " +$i days"));
            if ($currentDate === $targetDate) {
                $days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
                return [
                    'week_id' => $week['week_id'],
                    'day_name' => $days[$i]
                ];
            }
        }
    }
    return null;
}


// =================== PLAYER SELECTION ===================
$selected_player = null;
$program = null;
$weeks = [];
$player_id = null; // This will be the actual player_id for database
$user_id = null;   // This will be the user_id from URL for navigation

if (isset($_GET['player_id'])) {
    $user_id = intval($_GET['player_id']); // This is user_id from URL
    
    // Get the actual player_id from players table
    $stmt_sel = $pdo->prepare("
        SELECT p.player_id, u.user_id, u.firstname, u.middlename, u.lastname, u.username, u.barangay 
        FROM players p 
        JOIN users u ON p.user_id = u.user_id 
        WHERE u.user_id = ?
    ");
    $stmt_sel->execute([$user_id]);
    $selected_player = $stmt_sel->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_player) {
        $player_id = $selected_player['player_id']; // This is the actual player_id for training_programs
        $selected_player['fullname'] = trim($selected_player['firstname'] . ' ' . 
                                          ($selected_player['middlename'] ? $selected_player['middlename'] . ' ' : '') . 
                                          $selected_player['lastname']);
    }

            if ($selected_player) {
            $player_id = $selected_player['player_id'];
            $selected_player['fullname'] = trim($selected_player['firstname'] . ' ' . ($selected_player['middlename'] ? $selected_player['middlename'] . ' ' : '') . $selected_player['lastname']);
            
            // *** ADD THESE LINES HERE ***
            $stmtprog = $pdo->prepare("SELECT * FROM training_programs WHERE player_id = ? LIMIT 1");
            $stmtprog->execute([$player_id]);
            $program = $stmtprog->fetch(PDO::FETCH_ASSOC);
            
            if ($program) {
                $stmtweeks = $pdo->prepare("SELECT * FROM training_weeks WHERE program_id = ? ORDER BY week_number");
                $stmtweeks->execute([$program['program_id']]);
                $weeks = $stmtweeks->fetchAll(PDO::FETCH_ASSOC);
            }
            // *** END OF NEW LINES ***
        }


    // POST actions - update all redirects to use $user_id
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // Performance rating handler
       if (isset($_POST['save_performance_rating'])) {
            $player_id = intval($_POST['player_id']);
            $week_id = intval($_POST['week_id']);
            $day_name = $_POST['day_name'];
            $rating_date = $_POST['rating_date'];
            $performance_rating = floatval($_POST['performance_rating']);
            $coach_notes = $_POST['coach_notes'];

            // Validate rating
            if ($performance_rating < 0 || $performance_rating > 5) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Invalid rating value!'];
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }

            // ========== ADD THIS: Find or create week for the rating date ==========
            if (!$week_id && $rating_date) {
                // Try to find existing week for this date
                $weekInfo = findWeekForDate($weeks, $rating_date);
                
                if ($weekInfo) {
                    $week_id = $weekInfo['week_id'];
                    $day_name = $weekInfo['day_name'];
                } else {
                    // Create a new week for this date if it doesn't exist
                    $activityWeekStart = date('Y-m-d', strtotime('monday this week', strtotime($rating_date)));
                    
                    // Check if week already exists
                    $stmtCheckWeek = $pdo->prepare("SELECT week_id FROM training_weeks WHERE program_id = ? AND start_date = ?");
                    $stmtCheckWeek->execute([$program['program_id'], $activityWeekStart]);
                    $existingWeek = $stmtCheckWeek->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingWeek) {
                        $week_id = $existingWeek['week_id'];
                    } else {
                        // Get next week number
                        $stmtMaxWeek = $pdo->prepare("SELECT MAX(week_number) as maxweek FROM training_weeks WHERE program_id = ?");
                        $stmtMaxWeek->execute([$program['program_id']]);
                        $maxWeek = $stmtMaxWeek->fetchColumn();
                        $newWeekNumber = $maxWeek ? $maxWeek + 1 : 1;
                        
                        // Create new week
                        $stmtNewWeek = $pdo->prepare("INSERT INTO training_weeks (program_id, week_number, start_date) VALUES (?, ?, ?)");
                        $stmtNewWeek->execute([$program['program_id'], $newWeekNumber, $activityWeekStart]);
                        $week_id = $pdo->lastInsertId();
                        
                        // Update program duration
                        $newDuration = $newWeekNumber . " week" . ($newWeekNumber > 1 ? "s" : "");
                        $pdo->prepare("UPDATE training_programs SET duration = ? WHERE program_id = ?")->execute([$newDuration, $program['program_id']]);
                    }
                    
                    // Get day name from date
                    $day_name = date('D', strtotime($rating_date));
                }
            }
            
            // Validate we have a valid week_id
            if (!$week_id) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Unable to determine week for this rating!'];
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }
            // ========== END OF NEW CODE ==========

            // Check if rating already exists for this day
            $stmtcheck = $pdo->prepare("SELECT id FROM training_performance_ratings WHERE player_id=? AND rating_date=?");
            $stmtcheck->execute([$player_id, $rating_date]);
            $existing = $stmtcheck->fetch();

            if ($existing) {
                // Update existing rating
                $stmt = $pdo->prepare("UPDATE training_performance_ratings SET performance_rating=?, coach_notes=?, coach_username=?, week_id=? WHERE player_id=? AND rating_date=?");
                $stmt->execute([$performance_rating, $coach_notes, $coach_username, $week_id, $player_id, $rating_date]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Performance rating updated!'];
            } else {
                // Insert new rating
                $stmt = $pdo->prepare("INSERT INTO training_performance_ratings (player_id, week_id, day_name, rating_date, performance_rating, coach_notes, coach_username) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$player_id, $week_id, $day_name, $rating_date, $performance_rating, $coach_notes, $coach_username]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Performance rating saved!'];
            }

                header("Location: training_program.php?player_id=" . $user_id);
                exit;
        }
        if (isset($_POST['saveactivity'])) {
            // Fix: Use null coalescing operator to avoid undefined key warnings
            $week_id = isset($_POST['weekid']) ? intval($_POST['weekid']) : 0;
            $day_name = $_POST['dayname'] ?? '';
            $type = $_POST['type'];
            $title = $_POST['title'];
            $desc = $_POST['description'];
            $activitydate = $_POST['activitydate'] ?? null;
            $clear_existing = isset($_POST['clearexisting']);

            // Check if this day already has a performance rating
            if ($activitydate && $player_id) {
                $stmtCheckRating = $pdo->prepare("SELECT id FROM training_performance_ratings WHERE player_id = ? AND rating_date = ?");
                $stmtCheckRating->execute([$player_id, $activitydate]);
                $hasRating = $stmtCheckRating->fetchColumn();
                
                if ($hasRating) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Cannot add activities to a day that has already been rated!'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
            }

            // Handle monthly calendar activities (weekid/dayname empty but activitydate provided)
            if (!$week_id && empty($day_name) && $activitydate) {
                $weekInfo = findWeekForDate($weeks, $activitydate);
                
                if ($weekInfo) {
                    $week_id = $weekInfo['week_id'];
                    $day_name = $weekInfo['day_name'];
                } else {
                    // Activity date doesn't fall within any existing week - create new week
                    $activityWeekStart = date('Y-m-d', strtotime('monday this week', strtotime($activitydate)));
                    
                    // Check if week already exists
                    $stmtCheckWeek = $pdo->prepare("SELECT week_id FROM training_weeks WHERE program_id = ? AND start_date = ?");
                    $stmtCheckWeek->execute([$program['program_id'], $activityWeekStart]);
                    $existingWeek = $stmtCheckWeek->fetch(PDO::FETCH_ASSOC);
                    
                    if ($existingWeek) {
                        $week_id = $existingWeek['week_id'];
                    } else {
                        // Get next week number
                        $stmtMaxWeek = $pdo->prepare("SELECT MAX(week_number) as maxweek FROM training_weeks WHERE program_id = ?");
                        $stmtMaxWeek->execute([$program['program_id']]);
                        $maxWeek = $stmtMaxWeek->fetchColumn();
                        $newWeekNumber = $maxWeek ? $maxWeek + 1 : 1;
                        
                        // Create new week
                        $stmtNewWeek = $pdo->prepare("INSERT INTO training_weeks (program_id, week_number, start_date) VALUES (?, ?, ?)");
                        $stmtNewWeek->execute([$program['program_id'], $newWeekNumber, $activityWeekStart]);
                        $week_id = $pdo->lastInsertId();
                        
                        // Update program duration
                        $newDuration = $newWeekNumber . " week" . ($newWeekNumber > 1 ? "s" : "");
                        $pdo->prepare("UPDATE training_programs SET duration = ? WHERE program_id = ?")->execute([$newDuration, $program['program_id']]);
                    }
                    
                    // Get day name from date (abbreviated for ENUM: Mon, Tue, Wed, etc.)
                    $day_name = date('D', strtotime($activitydate));
                }
            }

            // Validate we have week_id and day_name
            if (!$week_id || empty($day_name)) {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Unable to determine week and day for this activity!'];
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }

            // REST DAY LOGIC - CLEAR EXISTING ACTIVITIES
            if ($type == 'Rest') {
                // Find the day_id for this week and day
                $stmtfindday = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=? AND day_name=?");
                $stmtfindday->execute([$week_id, $day_name]);
                $existingday = $stmtfindday->fetch(PDO::FETCH_ASSOC);
                
                if ($existingday) {
                    // Delete all existing activities for this day
                    $pdo->prepare("DELETE FROM training_activities WHERE day_id=?")->execute([$existingday['day_id']]);
                    
                    // If clear_existing is set, we're done - just add the rest day
                    if ($clear_existing) {
                        // Now add the rest day activity
                        $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, 'Rest', 'Rest Day', 'Recovery and muscle relaxation day.', ?)")
                            ->execute([$existingday['day_id'], $activitydate]);
                        
                        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day set! All existing activities have been removed.'];
                        header("Location: training_program.php?player_id=" . $user_id);
                        exit;
                    }
                } else {
                    // Create new day and add rest day
                    $pdo->prepare("INSERT INTO training_days (week_id, day_name) VALUES (?, ?)")->execute([$week_id, $day_name]);
                    $day_id = $pdo->lastInsertId();
                    
                    $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, 'Rest', 'Rest Day', 'Recovery and muscle relaxation day.', ?)")
                        ->execute([$day_id, $activitydate]);
                    
                    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day set successfully!'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
            }

            // REST DAY VALIDATION
            // Check if this day is already a rest day
            $stmtcheckrest = $pdo->prepare("
                SELECT ta.activity_id 
                FROM training_days td 
                JOIN training_activities ta ON td.day_id = ta.day_id 
                WHERE td.week_id = ? AND td.day_name = ? AND ta.type = 'Rest'
            ");
            $stmtcheckrest->execute([$week_id, $day_name]);
            $isRestDay = $stmtcheckrest->fetchColumn();
            
            if ($isRestDay && $type != 'Rest') {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Cannot add activities to a rest day!'];
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }

            // Check if we're trying to add rest day but day already has activities
            if ($type == 'Rest' && !$clear_existing) {
                $stmtcheckactivities = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM training_days td 
                    JOIN training_activities ta ON td.day_id = ta.day_id 
                    WHERE td.week_id = ? AND td.day_name = ? AND ta.type != 'Rest'
                ");
                $stmtcheckactivities->execute([$week_id, $day_name]);
                $hasActivities = $stmtcheckactivities->fetchColumn();
                
                if ($hasActivities > 0) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'This day already has activities! Click "Rest Day" again to confirm removing all activities.'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
            }

            // Continue with normal activity saving (for non-Rest types)
            $stmt = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=? AND day_name=?");
            $stmt->execute([$week_id, $day_name]);
            $day = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$day) {
                $pdo->prepare("INSERT INTO training_days (week_id, day_name) VALUES (?, ?)")->execute([$week_id, $day_name]);
                $day_id = $pdo->lastInsertId();
            } else {
                $day_id = $day['day_id'];
            }

            if (!empty($_POST['activityid'])) {
                // Update existing activity
                $aid = intval($_POST['activityid']);
                $pdo->prepare("UPDATE training_activities SET type=?, title=?, description=?, activity_date=? WHERE activity_id=?")
                    ->execute([$type, $title, $desc, $activitydate, $aid]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Activity updated successfully!'];
            } else {
                // Insert new activity
                $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$day_id, $type, $title, $desc, $activitydate]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Activity added successfully!'];
            }

                header("Location: training_program.php?player_id=" . $user_id);
                exit;
        }

        // Add this to your existing POST handlers in training_program.php
        if (isset($_POST['remove_rest_day'])) {
            $week_id = intval($_POST['week_id']);
            $day_name = $_POST['day_name'];
            $activity_date = $_POST['activity_date'];
            
            // Find the day_id for this week and day
            $stmt_day = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=? AND day_name=?");
            $stmt_day->execute([$week_id, $day_name]);
            $day_row = $stmt_day->fetch(PDO::FETCH_ASSOC);
            
            if ($day_row) {
                // Find and delete the rest day activity
                $stmt_rest = $pdo->prepare("DELETE FROM training_activities WHERE day_id=? AND type='Rest'");
                $stmt_rest->execute([$day_row['day_id']]);
                
                // Check if there are any other activities for this day
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM training_activities WHERE day_id=?");
                $stmt_check->execute([$day_row['day_id']]);
                $activity_count = $stmt_check->fetchColumn();
                
                // If no activities left, delete the day record too
                if ($activity_count == 0) {
                    $pdo->prepare("DELETE FROM training_days WHERE day_id=?")->execute([$day_row['day_id']]);
                }
                
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day removed! You can now add activities.'];
            } else {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Rest day not found!'];
            }
            
            header("Location: training_program.php?player_id=" . $user_id);
            exit;
        }

        if (isset($_POST['start_program'])) {
            $pdo->prepare("INSERT INTO training_programs (player_id, duration, total_drills, status) VALUES (?, '1 week', 0, 'In Progress')")
                ->execute([$player_id]);
            $pid = $pdo->lastInsertId();
            
            // Start from current week's Monday
            $current_week_monday = date('Y-m-d', strtotime('monday this week'));
            
            $pdo->prepare("INSERT INTO training_weeks (program_id, week_number, start_date) VALUES (?, 1, ?)")->execute([$pid, $current_week_monday]);
            $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Training program started! 🎯'];
            header("Location: training_program.php?player_id=" . $user_id);
            exit;
        }


        if (isset($_POST['delete_week'])) {
            $week_id = intval($_POST['week_id']);

            // Get the program ID before deleting the week
            $stmt_prog_id = $pdo->prepare("SELECT program_id FROM training_weeks WHERE week_id = ?");
            $stmt_prog_id->execute([$week_id]);
            $program_id = $stmt_prog_id->fetchColumn();

            if ($program_id) {
                // Delete related days and activities
                $stmt_days = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=?");
                $stmt_days->execute([$week_id]);
                $days = $stmt_days->fetchAll(PDO::FETCH_COLUMN);

                if ($days) {
                    $in = str_repeat('?,', count($days) - 1) . '?';
                    $pdo->prepare("DELETE FROM training_activities WHERE day_id IN ($in)")->execute($days);
                    $pdo->prepare("DELETE FROM training_days WHERE day_id IN ($in)")->execute($days);
                }

                // Delete the week itself
                $pdo->prepare("DELETE FROM training_weeks WHERE week_id=?")->execute([$week_id]);

                // Recount remaining weeks to update duration
                $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM training_weeks WHERE program_id=?");
                $stmt_count->execute([$program_id]);
                $week_count = $stmt_count->fetchColumn();

                $new_duration = $week_count . " week" . ($week_count > 1 ? "s" : "");
                $pdo->prepare("UPDATE training_programs SET duration=? WHERE program_id=?")->execute([$new_duration, $program_id]);
            }

            $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Week deleted successfully! 🗑️'];
            header("Location: training_program.php?player_id=" . $user_id);
            exit;
        }
 
            if (isset($_POST['deleteactivity'])) {
                $activityid = intval($_POST['activityid'] ?? 0);
                
                error_log("DELETE PROCESSING - Activity ID: " . $activityid);
                
                if ($activityid === 0) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Invalid activity ID!'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
                
                // First, get the activity details to check if it's a rest day
                $stmtact = $pdo->prepare("SELECT ta.*, td.week_id, td.day_name 
                                        FROM training_activities ta 
                                        JOIN training_days td ON ta.day_id = td.day_id 
                                        WHERE ta.activity_id = ?");
                $stmtact->execute([$activityid]);
                $activity = $stmtact->fetch(PDO::FETCH_ASSOC);
                
                if (!$activity) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Activity not found!'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
                
                error_log("Activity found: " . print_r($activity, true));
                
                // Check if this day has been rated
                if ($activity['activity_date']) {
                    $stmtCheckRating = $pdo->prepare("SELECT id FROM training_performance_ratings WHERE player_id = ? AND rating_date = ?");
                    $stmtCheckRating->execute([$player_id, $activity['activity_date']]);
                    $hasRating = $stmtCheckRating->fetchColumn();
                    
                    if ($hasRating) {
                        $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Cannot delete activities from a day that has been rated!'];
                        header("Location: training_program.php?player_id=" . $user_id);
                        exit;
                    }
                }
                
                // Delete the activity
                $stmtDelete = $pdo->prepare("DELETE FROM training_activities WHERE activity_id = ?");
                $stmtDelete->execute([$activityid]);
                $deleted = $stmtDelete->rowCount();
                
                error_log("Delete query executed. Rows affected: " . $deleted);
                
                if ($deleted > 0) {
                    // Check if there are any other activities for this day
                    $stmtCheckDay = $pdo->prepare("SELECT COUNT(*) FROM training_activities WHERE day_id = ?");
                    $stmtCheckDay->execute([$activity['day_id']]);
                    $remainingActivities = $stmtCheckDay->fetchColumn();
                    
                    // If no activities left, delete the day record too
                    if ($remainingActivities == 0) {
                        $pdo->prepare("DELETE FROM training_days WHERE day_id = ?")->execute([$activity['day_id']]);
                        error_log("Day record also deleted");
                    }
                    
                    // If it was a rest day, show appropriate message
                    if ($activity['type'] == 'Rest') {
                        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day removed! Activities can now be added.'];
                    } else {
                        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Activity deleted successfully!'];
                    }
                } else {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Failed to delete activity!'];
                }
                
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }
        if (isset($_POST['save_activity'])) {
            $week_id = intval($_POST['week_id']);
            $day_name = $_POST['day_name'];
            $type = $_POST['type'];
            $title = $_POST['title'];
            $desc = $_POST['description'];
            $activity_date = $_POST['activity_date'] ?? null;
            $clear_existing = isset($_POST['clear_existing']);

                // Handle monthly calendar activities (weekid/dayname empty but activitydate provided)
                if ((!$weekid || empty($dayname)) && $activitydate) {
                    $weekInfo = findWeekForDate($weeks, $activitydate);
                    if ($weekInfo) {
                        $weekid = $weekInfo['weekid'];
                        $dayname = $weekInfo['dayname'];
                    } else {
                        // Activity date doesn't fall within any existing week - create new week
                        $activityWeekStart = date('Y-m-d', strtotime('monday this week', strtotime($activitydate)));
                        
                        // Check if week already exists
                        $stmtCheckWeek = $pdo->prepare("SELECT weekid FROM training_weeks WHERE programid = ? AND startdate = ?");
                        $stmtCheckWeek->execute([$program['programid'], $activityWeekStart]);
                        $existingWeek = $stmtCheckWeek->fetch(PDO::FETCH_ASSOC);
                        
                        if ($existingWeek) {
                            $weekid = $existingWeek['weekid'];
                        } else {
                            // Get next week number
                            $stmtMaxWeek = $pdo->prepare("SELECT MAX(weeknumber) as maxweek FROM training_weeks WHERE programid = ?");
                            $stmtMaxWeek->execute([$program['programid']]);
                            $maxWeek = $stmtMaxWeek->fetchColumn();
                            $newWeekNumber = $maxWeek ? $maxWeek + 1 : 1;
                            
                            // Create new week
                            $stmtNewWeek = $pdo->prepare("INSERT INTO training_weeks (programid, weeknumber, startdate) VALUES (?, ?, ?)");
                            $stmtNewWeek->execute([$program['programid'], $newWeekNumber, $activityWeekStart]);
                            $weekid = $pdo->lastInsertId();
                            
                            // Update program duration
                            $newDuration = $newWeekNumber . " week" . ($newWeekNumber > 1 ? "s" : "");
                            $pdo->prepare("UPDATE training_programs SET duration = ? WHERE programid = ?")->execute([$newDuration, $program['programid']]);
                        }
                        
                        // Get day name from date (abbreviated for ENUM: Mon, Tue, Wed, etc.)
                        $dayname = date('D', strtotime($activitydate));
                    }
                }

                // Validate we have weekid and dayname
                if (!$weekid || empty($dayname)) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Unable to determine week and day for this activity!'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }


            // ============ REST DAY LOGIC - CLEAR EXISTING ACTIVITIES ============
            if ($type === 'Rest') {
                // Find the day_id for this week and day
                $stmt_find_day = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=? AND day_name=?");
                $stmt_find_day->execute([$week_id, $day_name]);
                $existing_day = $stmt_find_day->fetch(PDO::FETCH_ASSOC);
                
                if ($existing_day) {
                    // Delete all existing activities for this day
                    $pdo->prepare("DELETE FROM training_activities WHERE day_id=?")->execute([$existing_day['day_id']]);
                    
                    // If clear_existing is set, we're done - just add the rest day
                    if ($clear_existing) {
                        // Now add the rest day activity
                        $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, 'Rest', 'Rest Day', 'Recovery and muscle relaxation day.', ?)")
                            ->execute([$existing_day['day_id'], $activity_date]);
                        
                        $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day set! All existing activities have been removed. 😴'];
                        header("Location: training_program.php?player_id=" . $user_id);
                        exit;
                    }
                } else {
                    // Create new day and add rest day
                    $pdo->prepare("INSERT INTO training_days (week_id, day_name) VALUES (?, ?)")->execute([$week_id, $day_name]);
                    $day_id = $pdo->lastInsertId();
                    $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, 'Rest', 'Rest Day', 'Recovery and muscle relaxation day.', ?)")
                        ->execute([$day_id, $activity_date]);
                        
                    $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Rest day set successfully! 😴'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
            }

            // ============ REST DAY VALIDATION ============
            // Check if this day is already a rest day
            $stmt_check_rest = $pdo->prepare("
                SELECT ta.activity_id 
                FROM training_days td 
                JOIN training_activities ta ON td.day_id = ta.day_id 
                WHERE td.week_id = ? AND td.day_name = ? AND ta.type = 'Rest'
            ");
            $stmt_check_rest->execute([$week_id, $day_name]);
            $isRestDay = $stmt_check_rest->fetchColumn();

            if ($isRestDay && $type !== 'Rest') {
                $_SESSION['toast'] = ['type' => 'error', 'msg' => 'Cannot add activities to a rest day! ❌'];
                header("Location: training_program.php?player_id=" . $user_id);
                exit;
            }

            // Check if we're trying to add rest day but day already has activities
            if ($type === 'Rest' && !$clear_existing) {
                $stmt_check_activities = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM training_days td 
                    JOIN training_activities ta ON td.day_id = ta.day_id 
                    WHERE td.week_id = ? AND td.day_name = ? AND ta.type != 'Rest'
                ");
                $stmt_check_activities->execute([$week_id, $day_name]);
                $hasActivities = $stmt_check_activities->fetchColumn();

                if ($hasActivities > 0) {
                    $_SESSION['toast'] = ['type' => 'error', 'msg' => 'This day already has activities! Click \"Rest Day\" again to confirm removing all activities.'];
                    header("Location: training_program.php?player_id=" . $user_id);
                    exit;
                }
            }
            // ============ END REST DAY VALIDATION ============

            // Continue with normal activity saving for non-Rest types
            $stmt = $pdo->prepare("SELECT day_id FROM training_days WHERE week_id=? AND day_name=?");
            $stmt->execute([$week_id, $day_name]);
            $day = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$day) {
                $pdo->prepare("INSERT INTO training_days (week_id, day_name) VALUES (?, ?)")->execute([$week_id, $day_name]);
                $day_id = $pdo->lastInsertId();
            } else {
                $day_id = $day['day_id'];
            }

            if (!empty($_POST['activity_id'])) {
                $aid = intval($_POST['activity_id']);
                $pdo->prepare("UPDATE training_activities SET type=?, title=?, description=?, activity_date=? WHERE activity_id=?")
                    ->execute([$type, $title, $desc, $activity_date, $aid]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Activity updated successfully! ✏️'];
            } else {
                $pdo->prepare("INSERT INTO training_activities (day_id, type, title, description, activity_date) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$day_id, $type, $title, $desc, $activity_date]);
                $_SESSION['toast'] = ['type' => 'success', 'msg' => 'Activity added successfully! ✅'];
            }
            
            header("Location: training_program.php?player_id=" . $user_id);
            exit;
        }

        header("Location: training_program.php?player_id=" . $user_id);
        exit;
    } // This closes the if ($_SERVER['REQUEST_METHOD'] === 'POST') block

} // This closes the if (isset($_GET['player_id'])) block
?>

<section class="p-6">
  <div class="max-w-7xl mx-auto">
    <div class="mb-8 text-center">
      <!-- MODIFIED: Display barangay instead of team_name -->
      <h1 class="text-3xl font-bold text-gray-800 mb-2">🏀 <?= htmlspecialchars($team['barangay'] ?? 'Barangay'); ?> Training Program</h1>
      <p class="text-gray-600">Assign drills and workouts for your players</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Sidebar -->
      <aside class="col-span-1">
        <div class="bg-white rounded-xl shadow p-4 mb-4">
          <h2 class="text-lg font-semibold text-gray-800 mb-3">Team Players</h2>
          <div class="space-y-2">
            <?php foreach ($players as $p): ?>
    <a href="training_program.php?player_id=<?= $p['user_id']; ?>"
        class="flex items-center p-3 rounded-lg hover:bg-blue-50 border-l-4 <?= (isset($user_id)&&$p['user_id']==$user_id)?'border-blue-600 bg-blue-50':'border-transparent'; ?>">
                <div class="w-10 h-10 rounded-full bg-blue-500 text-white flex items-center justify-center font-semibold mr-3"><?= strtoupper(substr($p['fullname'],0,2)); ?></div>
                <div>
                  <p class="font-medium text-gray-800"><?= htmlspecialchars($p['fullname']); ?></p>
                  <!-- MODIFIED: Show barangay instead of assuming it's part of address -->
                  <p class="text-sm text-gray-500"><?= htmlspecialchars($p['barangay']); ?></p>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </aside>

      <!-- Main -->
      <main class="col-span-3 bg-white rounded-xl shadow p-6 relative">
        <?php if (!$selected_player): ?>
          <div class="border border-dashed rounded-xl p-6 text-center text-gray-500">👈 Select a player to view their training program</div>
        <?php else: ?>
          <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= htmlspecialchars($selected_player['fullname']); ?>'s Program</h2>

          <?php if (!$program): ?>
            <div class="text-center border border-dashed rounded-xl p-6 text-gray-600">
              🕒 No training program assigned yet.<br>
              <form method="POST" class="mt-4">
                <button name="start_program" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-semibold">▶️ Start Training Program</button>
              </form>
            </div>
          <?php else: ?>

            <!-- Monthly Calendar Section -->
            <div class="mt-8">
                <div class="month-navigation">
                    <a href="?player_id=<?= $user_id ?>&month=<?= $prev_month ?>&year=<?= $prev_year ?>" 
                    class="month-nav-btn">⬅️ Previous Month</a>

                    <h4 class="current-month"><?= $month_name . " " . $year ?></h4>

                    <a href="?player_id=<?= $user_id ?>&month=<?= $next_month ?>&year=<?= $next_year ?>" 
                    class="month-nav-btn">Next Month ➡️</a>
                </div>

                <!-- Activity Buttons -->
                <div class="flex flex-wrap gap-3 mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <button type="button" 
                            onclick="openWorkoutModalForMonth()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                        🏋️ Add Workout
                    </button>
                    <button type="button" 
                            onclick="openDrillModalForMonth()" 
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                        🏀 Add Drill
                    </button>
                    <button type="button" 
                            onclick="addRestDayForMonth()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                        😴 Add Rest Day
                    </button>
                    <button type="button" 
                            onclick="openRatingModalForMonth()" 
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-semibold transition flex items-center gap-2">
                        ⭐ Rate Performance
                    </button>
                </div>

                <!-- Monthly Calendar Grid -->
                <div class="bg-white rounded-xl shadow border border-gray-200 p-6">
                    <!-- Calendar Header -->
                    <div class="grid grid-cols-7 gap-2 mb-4 text-center font-semibold text-gray-700 border-b pb-2">
                        <div>Sun</div>
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-2" id="monthlyCalendar">
                        <?php
                        // Get current month info
                        date_default_timezone_set('Asia/Manila');

                        // Detect selected month/year from URL
                        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                        $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

                        // Calendar calculations using the selected month
                        $firstDayOfMonth = date('N', strtotime("$year-$month-01"));
                        $daysInMonth     = date('t', strtotime("$year-$month-01"));
                        $today           = date('j');
                        
                        // Calculate start offset for Sunday-first calendar
                        // Convert Monday-based (1-7) to Sunday-based (0-6)
                        $startOffset = $firstDayOfMonth == 7 ? 0 : $firstDayOfMonth;
                        
                        // Empty cells for days before the first day of month
                        for ($i = 0; $i < $startOffset; $i++) {
                            echo '<div class="h-24 bg-gray-100 rounded-lg border border-gray-200 p-2"></div>';
                        }
                        
                        // Generate days of the month
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $currentDate = date('Y-m-d', strtotime("$year-$month-$day"));
                            $isToday = ($day == $today);
                            $isPast = strtotime($currentDate) < strtotime(date('Y-m-d'));
                            
                            // Find which week this day belongs to
                            $weekInfo = findWeekForDate($weeks, $currentDate);
                            $weekId = $weekInfo['week_id'] ?? null;
                            $dayName = $weekInfo['day_name'] ?? null;
                            
                            // Get activities for this day
                            $activities = [];
                            $isRestDay = false;
                            $hasRating = false; // Initialize this

                            if ($weekId && $dayName) {
                                $stmtday = $pdo->prepare("SELECT td.day_id FROM training_days td WHERE td.week_id = ? AND td.day_name = ?");
                                $stmtday->execute([$weekId, $dayName]);
                                $dayrow = $stmtday->fetch(PDO::FETCH_ASSOC);
                                
                                if ($dayrow) {
                                    $stmtacts = $pdo->prepare("SELECT * FROM training_activities WHERE day_id = ? ORDER BY activity_id ASC");
                                    $stmtacts->execute([$dayrow['day_id']]);
                                    $activities = $stmtacts->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    foreach ($activities as $act) {
                                        if ($act['type'] == 'Rest') {
                                            $isRestDay = true;
                                            break;
                                        }
                                    }
                                }
                            }

                            // Check if this day has a performance rating
                            if ($player_id) {
                                $stmtRating = $pdo->prepare("SELECT id FROM training_performance_ratings WHERE player_id = ? AND rating_date = ?");
                                $stmtRating->execute([$player_id, $currentDate]);
                                $hasRating = $stmtRating->fetchColumn() ? true : false;
                            }
                            
                            // Day cell with proper styling
                            $dayClass = "h-24 rounded-lg border border-gray-200 p-2 relative overflow-y-auto";
                            $dayClass .= $isToday ? "border-2 border-blue-500 bg-blue-50 " : "bg-white ";
                            $dayClass .= $isPast ? "opacity-75 " : "";
                            $dayClass .= ($hasRating ? " border-purple-300 bg-purple-50" : ""); 
                            
                            echo "<div class='{$dayClass}' data-date='{$currentDate}' data-week='{$weekId}' data-day='{$dayName}' element.dataset.hasRating='" . ($hasRating ? 'true' : 'false') . "'>";
                            
                            // DAY NUMBER - This was missing!
                            echo '<div class="text-sm font-semibold mb-1 ' . ($isToday ? 'text-blue-600' : 'text-gray-700') . '">' . $day . '</div>';
                            
                            if ($hasRating) {
                                echo '<div class="absolute top-1 right-1 text-[10px] px-1.5 py-0.5 rounded bg-purple-600 text-white font-semibold">Rated</div>';
                            }

                            // Activity icons
                            echo '<div class="flex flex-wrap gap-1 justify-center">';
                            // Inside your calendar day generation loop, update the activity display:
                            if ($isRestDay) {
                                echo '<div class="activity-icon-container" 
                                    data-activity-id="' . $act['activity_id'] . '" 
                                    data-date="' . $currentDate . '">';

                                echo '<span class="activity-icon text-green-600 text-xs" title="Rest Day">😴</span>';
                                // Find the rest day activity ID
                                $restActivity = null;
                                foreach ($activities as $act) {
                                    if ($act['type'] === 'Rest') {
                                        $restActivity = $act;
                                        break;
                                    }
                                }
                                if ($restActivity) {
                                    echo '<div class="activity-buttons">';
                                    echo '<button class="details-btn" onclick="showActivityDetails(\'' . htmlspecialchars($act['title']) . '\', \'' . htmlspecialchars($act['description']) . '\', \'' . $act['activity_date'] . '\', \'' . $act['type'] . '\', ' . $act['activity_id'] . ')">📋</button>';
                                    // Add data-date attribute for rating check
                                    echo '<button class="delete-activity-btn" data-date="' . $currentDate . '" onclick="deleteActivity(' . $act['activity_id'] . ', \'' . $act['type'] . '\')">🗑️</button>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                $activityCount = 0;
                                foreach ($activities as $act) {
                                    if ($activityCount >= 3) break; // Limit display
                                    
                                    if ($act['type'] === 'Workout' || $act['type'] === 'Drill') {
                                        echo '<div class="activity-icon-container">';
                                        if ($act['type'] === 'Workout') {
                                            echo '<span class="activity-icon text-blue-600 text-xs" title="Workout">🏋️</span>';
                                        } elseif ($act['type'] === 'Drill') {
                                            echo '<span class="activity-icon text-yellow-600 text-xs" title="Drill">🏀</span>';
                                        }
                                        echo '<button class="details-btn" onclick="showActivityDetails(\'' . htmlspecialchars($act['title']) . '\', \'' . htmlspecialchars($act['description']) . '\', \'' . $act['activity_date'] . '\', \'' . $act['type'] . '\', ' . $act['activity_id'] . ')">📋</button>';
                                        // Add data-date attribute for rating check
                                        echo '<button class="delete-activity-btn" data-date="' . $currentDate . '" onclick="deleteActivity(' . $act['activity_id'] . ', \'' . $act['type'] . '\')">🗑️</button>';
                                        echo '</div>';
                                        $activityCount++;
                                    }
                                }
                            }
                            echo '</div>';
                            
                            echo '</div>'; // Close day div
                            echo '<!-- Debug: Activities for ' . $currentDate . ' -->';
                            echo '<!-- Activity count: ' . count($activities) . ' -->';
                            foreach ($activities as $act) {
                                echo '<!-- Activity: ' . $act['activity_id'] . ' - ' . $act['type'] . ' -->';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Monthly Overview section with Performance Rating -->
            <?php if (!empty($weeks)): 
                // Get current month and year
                $current_year = date('Y');
                $current_month = date('m');
                $today = date('Y-m-d');
                
                // Get all activities for the current month
                $month_activities = [];
                $month_ratings = [];
                
                if ($player_id) {
                    // Get activities for the current month
                    $stmt_month_activities = $pdo->prepare("
                        SELECT ta.*, td.day_name, tw.start_date 
                        FROM training_activities ta 
                        JOIN training_days td ON ta.day_id = td.day_id 
                        JOIN training_weeks tw ON td.week_id = tw.week_id 
                        WHERE tw.program_id = ? 
                        AND MONTH(tw.start_date) = ? 
                        AND YEAR(tw.start_date) = ?
                        ORDER BY tw.start_date, td.day_name
                    ");
                    $stmt_month_activities->execute([$program['program_id'], $current_month, $current_year]);
                    $month_activities = $stmt_month_activities->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Get ratings for the current month
                    $stmt_month_ratings = $pdo->prepare("
                        SELECT * FROM training_performance_ratings 
                        WHERE player_id = ? 
                        AND MONTH(rating_date) = ? 
                        AND YEAR(rating_date) = ?
                    ");
                    $stmt_month_ratings->execute([$player_id, $current_month, $current_year]);
                    $month_ratings_data = $stmt_month_ratings->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Convert ratings to date-based array for easy lookup
                    foreach ($month_ratings_data as $rating) {
                        $month_ratings[$rating['rating_date']] = $rating;
                    }
                }
            ?>

            <?php
            // SAFE MONTH/YEAR HANDLING — MUST be placed BEFORE any HTML uses these
            $current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
            $current_year  = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

            // Ensure valid ranges
            if ($current_month < 1 || $current_month > 12) $current_month = date('n');
            if ($current_year < 2000 || $current_year > 2100) $current_year = date('Y');

            // Generate month name
            $display_month_name = date('F', mktime(0, 0, 0, $current_month, 1));
            ?>

            <div class="mt-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                📊 Monthly Performance Overview
                <span class="ml-2 text-gray-500 text-sm font-normal">(<?= $display_month_name . " " . $current_year ?>)</span>
            </h4>

            <div class="monthly-overview-grid">
                <?php
                // Prevent errors when empty
                $month_activities = $month_activities ?? [];
                $month_ratings = $month_ratings ?? [];
                $month_ratings_data = $month_ratings_data ?? [];

                // Safe defaults
                $days_in_month = cal_days_in_month(CAL_GREGORIAN, $current_month, $current_year);

                $total_workouts = 0;
                $total_drills = 0;
                $total_rest_days = 0;
                $rated_days = 0;
                $total_rating = 0;

                foreach ($month_activities as $activity) {
                    if ($activity['type'] === 'Workout') $total_workouts++;
                    if ($activity['type'] === 'Drill')   $total_drills++;
                    if ($activity['type'] === 'Rest')    $total_rest_days++;
                }

                foreach ($month_ratings as $rating) {
                    $rated_days++;
                    $total_rating += $rating['performance_rating'];
                }

                $average_rating = $rated_days > 0 ? $total_rating / $rated_days : 0;
                $days_with_activities = !empty($month_activities)
                    ? count(array_unique(array_column($month_activities, 'day_id')))
                    : 0;
                ?>

                <!-- Monthly Stats Cards -->
                <div class="monthly-stats">
                    <div class="stat-card">
                        <div class="stat-icon">🏋️</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $total_workouts ?></div>
                            <div class="stat-label">Workouts</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🏀</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $total_drills ?></div>
                            <div class="stat-label">Drills</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">😴</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $total_rest_days ?></div>
                            <div class="stat-label">Rest Days</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">⭐</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= number_format($average_rating, 1) ?></div>
                            <div class="stat-label">Avg Rating</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📅</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $days_with_activities ?></div>
                            <div class="stat-label">Active Days</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🎯</div>
                        <div class="stat-content">
                            <div class="stat-number"><?= $rated_days ?></div>
                            <div class="stat-label">Rated Days</div>
                        </div>
                    </div>
                </div>

                <!-- Activity Distribution -->
                <div class="activity-distribution">
                    <h5 class="distribution-title">Activity Distribution</h5>
                    <div class="distribution-bars">
                        <?php
                        $total_activities = $total_workouts + $total_drills + $total_rest_days;

                        if ($total_activities > 0):
                            $workout_percent = ($total_workouts / $total_activities) * 100;
                            $drill_percent   = ($total_drills   / $total_activities) * 100;
                            $rest_percent    = ($total_rest_days / $total_activities) * 100;
                        ?>
                        <div class="distribution-bar">
                            <div class="bar-segment workout-segment" style="width: <?= $workout_percent ?>%">
                                <span class="segment-label">Workouts: <?= $total_workouts ?></span>
                            </div>
                            <div class="bar-segment drill-segment" style="width: <?= $drill_percent ?>%">
                                <span class="segment-label">Drills: <?= $total_drills ?></span>
                            </div>
                            <div class="bar-segment rest-segment" style="width: <?= $rest_percent ?>%">
                                <span class="segment-label">Rest: <?= $total_rest_days ?></span>
                            </div>
                        </div>

                        <div class="distribution-legend">
                            <div class="legend-item"><span class="legend-color workout-color"></span> Workouts (<?= number_format($workout_percent, 1) ?>%)</div>
                            <div class="legend-item"><span class="legend-color drill-color"></span> Drills (<?= number_format($drill_percent, 1) ?>%)</div>
                            <div class="legend-item"><span class="legend-color rest-color"></span> Rest Days (<?= number_format($rest_percent, 1) ?>%)</div>
                        </div>

                        <?php else: ?>
                            <div class="no-data">No activities recorded this month</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Ratings -->
                <div class="recent-ratings">
                    <h5 class="ratings-title">Recent Performance Ratings</h5>
                    <div class="ratings-list">
                        <?php if (!empty($month_ratings_data)):

                            usort($month_ratings_data, fn($a,$b) => strtotime($b['rating_date']) - strtotime($a['rating_date']));
                            $recent_ratings = array_slice($month_ratings_data, 0, 5);

                            foreach ($recent_ratings as $rating):
                        ?>
                        <div class="rating-item">
                            <div class="rating-date"><?= date('M j', strtotime($rating['rating_date'])) ?></div>
                            <div class="rating-stars">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                    <span class="star <?= $i <= $rating['performance_rating'] ? 'filled' : '' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-value"><?= number_format($rating['performance_rating'], 1) ?></div>

                            <?php if (!empty($rating['coach_notes'])): ?>
                            <button type="button" class="notes-btn" onclick="showCoachNotes('<?= htmlspecialchars($rating['coach_notes'], ENT_QUOTES) ?>')">📝</button>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>

                        <?php else: ?>
                            <div class="no-data">No ratings this month</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Monthly Progress -->
                <div class="monthly-progress">
                    <h5 class="progress-title">Monthly Progress</h5>
                    <div class="progress-stats">
                        <div class="progress-item">
                            <div class="progress-label">Completion Rate</div>
                            <?php
                            $completionRate = $days_in_month > 0 ? ($days_with_activities / $days_in_month) * 100 : 0;
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min($completionRate, 100) ?>%"></div>
                            </div>
                            <div class="progress-value"><?= number_format($completionRate, 1) ?>%</div>
                        </div>

                        <div class="progress-item">
                            <div class="progress-label">Rating Consistency</div>
                            <?php
                            $consistency = $days_with_activities > 0 ? ($rated_days / $days_with_activities) * 100 : 0;
                            ?>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= min($consistency, 100) ?>%"></div>
                            </div>
                            <div class="progress-value"><?= number_format($consistency, 1) ?>%</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
            <?php endif; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>
</section>

<!-- Performance Rating Modal -->
<div id="ratingModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-auto">
        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h3 id="ratingModalTitle" class="text-xl font-bold text-gray-800">Rate Performance</h3>
            <button type="button" onclick="closeRatingModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
        </div>

    <div id="ratingModalContent" class="p-6">
      <form id="ratingForm" method="POST">
        <input type="hidden" name="save_performance_rating" value="1">
        <input type="hidden" name="player_id" value="<?= $player_id ?>">
        <input type="hidden" name="week_id" value="">
        <input type="hidden" name="day_name" id="ratingDayName">
        <input type="hidden" name="rating_date" id="ratingDate">
        <input type="hidden" name="performance_rating" id="performanceRating" value="0">
        <input type="hidden" name="coach_notes" id="coachNotes">

        <div class="mb-6">
          <label class="block text-sm font-semibold text-gray-700 mb-3">Performance Rating</label>
          <div class="flex justify-center space-x-2 mb-2" id="starRating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <button type="button" 
                      class="star-select text-3xl text-gray-300 hover:text-yellow-400 transition" 
                      data-rating="<?= $i ?>">☆</button>
            <?php endfor; ?>
          </div>
          <div class="text-center">
            <span id="ratingText" class="text-sm font-semibold text-gray-600">Not Rated</span>
            <div id="ratingDescription" class="text-xs text-gray-500 mt-1"></div>
          </div>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Coach's Notes & Advice</label>
          <textarea name="coach_notes" id="coachNotesTextarea" rows="4" 
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-sm"
                    placeholder="Enter your feedback, observations, and advice for the player..."></textarea>
        </div>

        <div class="flex justify-end space-x-3">
          <button type="button" onclick="closeRatingModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Save Rating</button>
        </div>
      </form>
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

<!-- Include Workout Modal -->
<?php include 'workout_modal.php'; ?>

<!-- Include Drill Modal -->
<?php include 'drill_modal.php'; ?>

<!-- Include Rest Day Modal -->
<?php include 'rest_modal.php'; ?>

<!-- Info Modal -->
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

<?php if ($toast): ?>
<div id="toast"
     class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 flex items-center space-x-2 text-white px-5 py-3 rounded-lg shadow-lg transition-all duration-500 opacity-0 translate-y-5
     <?= $toast['type'] === 'success' ? 'bg-green-600' : 'bg-red-600'; ?>">
  <span class="font-medium"><?= htmlspecialchars($toast['msg']); ?></span>
</div>

<script>
  const toast = document.getElementById('toast');
  if (toast) {
    setTimeout(() => {
      toast.classList.remove('opacity-0', 'translate-y-5');
      toast.classList.add('opacity-100', 'translate-y-0');
    }, 100);

    setTimeout(() => {
      toast.classList.remove('opacity-100', 'translate-y-0');
      toast.classList.add('opacity-0', 'translate-y-5');
    }, 3000);
  }
  // Check if a day has performance rating
function hasPerformanceRating(date) {
    const dayElement = document.querySelector(`[data-date="${date}"]`);
    if (!dayElement) return false;
    return dayElement.dataset.hasRating === 'true';
}

    // Check if a day has performance rating before allowing activity addition
function checkDayRating(date) {
    if (hasPerformanceRating(date)) {
        alert('Cannot modify activities on a day that has been rated!');
        return false;
    }
    return true;
}


// Update openWorkoutModalForMonth - REPLACE the existing function
function openWorkoutModalForMonth() {
    if (!selectedCalendarDate) {
        alert('Please select a day in the calendar first!');
        return;
    }
    
    if (hasPerformanceRating(selectedCalendarDate)) {
        alert('Cannot add activities to a day that has already been rated!');
        return;
    }
    
    const modal = document.getElementById('workoutModal');
    if (!modal) return;
    
    modal.classList.remove('hidden');
    document.getElementById('wkWeekId').value = '';
    document.getElementById('wkDayName').value = '';
    document.getElementById('workoutDate').value = selectedCalendarDate;
    
    const label = document.getElementById('workoutModalDate');
    if (label) {
        const dateObj = new Date(selectedCalendarDate + 'T00:00:00');
        label.textContent = dateObj.toLocaleDateString('en-US', {
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
    }
    
    document.getElementById('workoutModalTitle').textContent = 'Add Workout';
}

// Update openDrillModalForMonth - REPLACE the existing function
function openDrillModalForMonth() {
    if (!selectedCalendarDate) {
        alert('Please select a day in the calendar first!');
        return;
    }
    
    if (hasPerformanceRating(selectedCalendarDate)) {
        alert('Cannot add activities to a day that has already been rated!');
        return;
    }
    
    const modal = document.getElementById('drillModal');
    if (!modal) return;
    
    modal.classList.remove('hidden');
    document.getElementById('drWeekId').value = '';
    document.getElementById('drDayName').value = '';
    document.getElementById('drillDate').value = selectedCalendarDate;
    
    const label = document.getElementById('drillModalDate');
    if (label) {
        const dateObj = new Date(selectedCalendarDate + 'T00:00:00');
        label.textContent = dateObj.toLocaleDateString('en-US', {
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
    }
    
    document.getElementById('drillModalTitle').textContent = 'Add Drill';
}

// Update addRestDayForMonth - REPLACE the existing function
function addRestDayForMonth() {
    if (!selectedCalendarDate) {
        alert('Please select a day in the calendar first!');
        return;
    }
    
    if (hasPerformanceRating(selectedCalendarDate)) {
        alert('Cannot add activities to a day that has already been rated!');
        return;
    }
    
    const dayElement = document.querySelector(`[data-date="${selectedCalendarDate}"]`);
    if (dayElement) {
        const existingRestDay = dayElement.querySelector('.activity-icon.text-green-600');
        if (existingRestDay) {
            alert('This day is already marked as a rest day!');
            return;
        }
        
        const hasWorkout = dayElement.querySelector('.activity-icon.text-blue-600');
        const hasDrill = dayElement.querySelector('.activity-icon.text-yellow-600');
        
        if (hasWorkout || hasDrill) {
            const confirmMsg = 'This day already has workouts or drills! Marking it as a Rest Day will REMOVE ALL existing activities.\n\nDo you want to continue?';
            if (!confirm(confirmMsg)) return;
        }
    }
    
    const modal = document.getElementById('restDayModal');
    if (!modal) return;
    
    modal.classList.remove('hidden');
    document.getElementById('restWeekId').value = '';
    document.getElementById('restDayName').value = '';
    document.getElementById('restDate').value = selectedCalendarDate;
    document.getElementById('restDescription').value = 'Recovery and muscle relaxation day.';
    
    const label = document.getElementById('restDayModalDate');
    if (label) {
        const dateObj = new Date(selectedCalendarDate + 'T00:00:00');
        label.textContent = dateObj.toLocaleDateString('en-US', {
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
    }
    
    document.getElementById('restDayModalTitle').textContent = 'Add Rest Day';
}

function deleteActivity(activityId, activityType) {

    // Find the activity container using the id
    const activityElement = document.querySelector(`[data-activity-id="${activityId}"]`);

    if (!activityElement) {
        alert("Error: Activity element not found.");
        return;
    }

    const activityDate = activityElement.dataset.date;

    // Get the day DIV using the date
    const dayElement = document.querySelector(`[data-date="${activityDate}"]`);

    if (!dayElement) {
        alert("Error: Calendar day not found.");
        return;
    }

    const isRated = dayElement.dataset.hasRating === "true";

    // BLOCK deletion if rated
    if (isRated) {
        alert("❌ This day has been rated. You cannot delete activities from a rated day.");
        return;
    }

    if (!confirm(`Delete this ${activityType}?`)) return;

    const form = document.createElement("form");
    form.method = "POST";
    form.innerHTML = `
        <input type="hidden" name="delete_activity" value="1">
        <input type="hidden" name="activity_id" value="${activityId}">
    `;
    document.body.appendChild(form);
    form.submit();
}


</script>
<?php endif; ?>

<!-- Include JavaScript files -->
<script src="js/workout_functions.js"></script>
<script src="js/drill_functions.js"></script>
<script src="js/rest_functions.js?v=2"></script>s
<script src="js/common_functions.js"></script>



<script>
// Update modal functions to accept date parameter
function openWorkoutModal(weekId, dayName, dayDate) {
    const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
    const isRestDay = dayElement && dayElement.querySelector('.rest-day-indicator');
    const isPast = dayElement && dayElement.classList.contains('bg-gray-100');
    
    if (isRestDay) {
        alert('Cannot add workouts to a rest day! ❌');
        return;
    }
    
    if (isPast) {
        alert('Cannot add activities to past days! ❌');
        return;
    }
    
    document.getElementById('workoutModal').classList.remove('hidden');
    document.getElementById('wkWeekId').value = weekId;
    document.getElementById('wkDayName').value = dayName;
    document.getElementById('workoutDate').value = dayDate;
    document.getElementById('workoutModalTitle').textContent = 'Add Workout - ' + dayName;
}

function openAddDrillModal(weekId, dayName, dayDate) {
    const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
    const isRestDay = dayElement && dayElement.querySelector('.rest-day-indicator');
    const isPast = dayElement && dayElement.classList.contains('bg-gray-100');
    
    if (isRestDay) {
        alert('Cannot add drills to a rest day! ❌');
        return;
    }
    
    if (isPast) {
        alert('Cannot add activities to past days! ❌');
        return;
    }
    
    document.getElementById('drillModal').classList.remove('hidden');
    document.getElementById('drWeekId').value = weekId;
    document.getElementById('drDayName').value = dayName;
    document.getElementById('drillDate').value = dayDate;
    document.getElementById('drillModalTitle').textContent = 'Add Drill - ' + dayName;
}

// Monthly calendar functions
function openWorkoutModalForMonth() {
    const modal = document.getElementById('workoutModal');
    if (!modal) return;

    modal.classList.remove('hidden');

    // Clear week/day so this comes only from the date picker
    document.getElementById('wkWeekId').value = '';
    document.getElementById('wkDayName').value = '';
    document.getElementById('workoutDate').value = '';

    // Optional text in the blue bar
    const label = document.getElementById('workoutModalDateSelected');
    if (label) {
        label.textContent = 'Select a date below';
    }

    document.getElementById('workoutModalTitle').textContent = 'Add Workout';
}

function openDrillModalForMonth() {
    const modal = document.getElementById('drillModal');
    if (!modal) return;

    modal.classList.remove('hidden');

    // Clear week/day; drill will use the date from the picker
    document.getElementById('drWeekId').value = '';
    document.getElementById('drDayName').value = '';
    document.getElementById('drillDate').value = '';

    // Optional text in the yellow bar
    const label = document.getElementById('drillModalDateSelected');
    if (label) {
        label.textContent = 'Select a date below';
    }

    document.getElementById('drillModalTitle').textContent = 'Add Drill';
}


function showDayActivityOptions(weekId, dayName, date) {
    const options = `
        <div class="space-y-2 p-4">
            <button type="button" 
                    onclick="openWorkoutModal(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                🏋️ Add Workout
            </button>
            <button type="button" 
                    onclick="openAddDrillModal(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                🏀 Add Drill
            </button>
            <button type="button" 
                    onclick="addRestDay(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                😴 Mark as Rest Day
            </button>
            <button type="button" 
                    onclick="viewDayActivities(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                👁️ View Activities
            </button>
        </div>
    `;
    
    showDayOptionsModal(`Select Activity for ${dayName} (${date})`, options);
}

function showDayOptionsModal(title, content) {
    const modal = document.createElement('div');
    modal.id = 'dayOptionsModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">${title}</h3>
                <button type="button" onclick="closeDayOptionsModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            </div>
            <div class="p-6">
                ${content}
            </div>
            <div class="border-t border-gray-200 p-4">
                <button type="button" onclick="closeDayOptionsModal()" class="w-full px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeDayOptionsModal() {
    const modal = document.getElementById('dayOptionsModal');
    if (modal) {
        modal.remove();
    }
}

function viewDayActivities(weekId, dayName, date) {
    // This would show a modal with all activities for the selected day
    alert(`Viewing activities for ${dayName} (${date})\nWeek ID: ${weekId}\n\nThis would show a detailed list of all workouts, drills, and rest days for this date.`);
}

// Rest day functions
function addRestDay(weekId, dayName, dayDate) {
    const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
    const isRestDay = dayElement && dayElement.querySelector('.rest-day-indicator');
    const isPast = dayElement && dayElement.classList.contains('bg-gray-100');
    
    if (isRestDay) {
        alert('This day is already marked as a Rest Day! 😴');
        return;
    }
    
    if (isPast) {
        alert('Cannot modify past days! ❌');
        return;
    }

    const dayColumn = document.querySelector(`[data-week="${weekId}"] [data-day="${dayName}"]`);
    const hasActivities = dayColumn && (dayColumn.textContent.includes('Workout') || dayColumn.textContent.includes('Drill') || dayColumn.textContent.includes('🏋️') || dayColumn.textContent.includes('🏀'));

    let message;
    if (hasActivities) {
        message = `⚠️ WARNING: ${dayName} already has activities!\n\nMarking as Rest Day will REMOVE ALL existing activities.\n\nDo you want to continue?`;
    } else {
        message = `Mark ${dayName} as a Rest Day?`;
    }

    if (!confirm(message)) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="save_activity" value="1">
        <input type="hidden" name="week_id" value="${weekId}">
        <input type="hidden" name="day_name" value="${dayName}">
        <input type="hidden" name="type" value="Rest">
        <input type="hidden" name="activity_date" value="${dayDate}">
        <input type="hidden" name="title" value="Rest Day">
        <input type="hidden" name="description" value="Recovery and muscle relaxation day.">
        <input type="hidden" name="clear_existing" value="1">
    `;
    document.body.appendChild(form);
    form.submit();
}

function removeRestDay(weekId, dayName, dayDate) {
    if (!confirm(`Remove rest day from ${dayName}? This will allow adding activities to this day.`)) {
        return;
    }

    // Create a form to delete the rest day activity
    const form = document.createElement('form');
    form.method = 'POST';
    
    // First, we need to find the rest day activity ID for this specific day
    // We'll submit to PHP which will handle finding and deleting the rest day
    form.innerHTML = `
        <input type="hidden" name="remove_rest_day" value="1">
        <input type="hidden" name="week_id" value="${weekId}">
        <input type="hidden" name="day_name" value="${dayName}">
        <input type="hidden" name="activity_date" value="${dayDate}">
    `;
    
    document.body.appendChild(form);
    form.submit();
}

function showActivityDetails(title, description, activityDate) {
    const modal = document.getElementById('infoModal');
    const body = document.getElementById('infoModalBody');
    const heading = document.getElementById('infoModalTitle');

    heading.innerText = title;
    if (!description) description = "No details provided.";

    // Parse details from the enhanced description format
    const details = {
        Date: activityDate ? new Date(activityDate).toLocaleDateString() : '—',
        // Extract Workout Type or Drill Category
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

// Performance Rating Functions
let currentRating = 0;

function openRatingModalForMonth() {
    const modal = document.getElementById('ratingModal');
    if (!modal) return;
    
    // Get all days that have activities
    const daysWithActivities = document.querySelectorAll('#monthlyCalendar > div[data-date]');
    const activeDates = [];
    
    daysWithActivities.forEach(day => {
    const hasWorkout = day.querySelector('.activity-icon.text-blue-600');
    const hasDrill = day.querySelector('.activity-icon.text-yellow-600');
    const hasRest = day.querySelector('.activity-icon.text-green-600');
    
    // Only include days with workouts or drills, NOT rest days
    if ((hasWorkout || hasDrill) && !hasRest) {
            const dateValue = day.dataset.date;
            const dateObj = new Date(dateValue + 'T00:00:00');
            const isRated = day.dataset.hasRating === 'true';
            
            activeDates.push({
                date: dateValue,
                formatted: dateObj.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }),
                dayName: dateObj.toLocaleDateString('en-US', { weekday: 'short' }),
                isRated: isRated
            });
        }
    });
    
    activeDates.sort((a, b) => new Date(a.date) - new Date(b.date));
    
    if (activeDates.length === 0) {
        alert('No training activities found this month to rate!');
        return;
    }
    
    showDateSelector(activeDates);
    modal.classList.remove('hidden');
}

function showDateSelector(dates) {
    const content = document.getElementById('ratingModalContent');
    const title = document.getElementById('ratingModalTitle');
    
    title.textContent = 'Select a Day to Rate';
    
    content.innerHTML = `
        <div class="space-y-3 max-h-96 overflow-y-auto">
            ${dates.map(dateInfo => `
                <button 
                    type="button"
                    onclick="showRatingForm('${dateInfo.date}', '${dateInfo.dayName}', '${dateInfo.formatted}')"
                    class="w-full text-left p-4 rounded-lg border-2 transition ${
                        dateInfo.isRated 
                        ? 'border-purple-300 bg-purple-50 hover:bg-purple-100' 
                        : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'
                    }"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-gray-800">${dateInfo.formatted}</div>
                        </div>
                        ${dateInfo.isRated 
                            ? '<span class="text-purple-600 text-sm font-semibold">✓ Rated</span>' 
                            : '<span class="text-blue-600 text-sm font-semibold">→ Rate</span>'
                        }
                    </div>
                </button>
            `).join('')}
        </div>
        <div class="mt-6">
            <button 
                type="button" 
                onclick="closeRatingModal()" 
                class="w-full px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold"
            >Cancel</button>
        </div>
    `;
}

function showRatingForm(ratingDate, dayName, formattedDate) {
    const content = document.getElementById('ratingModalContent');
    const title = document.getElementById('ratingModalTitle');
    
    title.textContent = formattedDate;
    
    content.innerHTML = `
        <form id="ratingForm" method="POST" class="space-y-4">
            <input type="hidden" name="save_performance_rating" value="1">
            <input type="hidden" name="player_id" value="<?= $player_id ?>">
            <input type="hidden" name="week_id" value="">
            <input type="hidden" name="day_name" value="${dayName}">
            <input type="hidden" name="rating_date" value="${ratingDate}">
            <input type="hidden" name="performance_rating" id="performanceRating" value="0">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Performance Rating</label>
                <div class="flex justify-center space-x-2 mb-3" id="starRating">
                    ${[1,2,3,4,5].map(i => `
                        <button 
                            type="button" 
                            class="star-btn text-4xl text-gray-300 hover:text-yellow-400 transition" 
                            data-rating="${i}"
                            onclick="setRating(${i})"
                        >★</button>
                    `).join('')}
                </div>
                <div class="text-center">
                    <span id="ratingText" class="text-sm font-semibold text-gray-600">Not Rated</span>
                </div>
                <div id="ratingDescription" class="text-xs text-gray-500 mt-1 text-center">Click stars to rate performance</div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Coach's Notes & Advice</label>
                <textarea 
                    name="coach_notes" 
                    id="coachNotesTextarea" 
                    rows="4" 
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-sm"
                    placeholder="Enter your feedback, observations, and advice..."
                ></textarea>
            </div>
            
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="openRatingModalForMonth()" 
                    class="flex-1 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold"
                >← Back</button>
                <button 
                    type="submit" 
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                >Save Rating</button>
            </div>
        </form>
    `;
    
    currentRating = 0;
}

function setRating(rating) {
    currentRating = rating;
    document.getElementById('performanceRating').value = rating;
    
    const stars = document.querySelectorAll('.star-btn');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
    
    const descriptions = {
        5: '5.0 - Outstanding',
        4: '4.0 - Very Satisfactory',
        3: '3.0 - Satisfactory',
        2: '2.0 - Fair',
        1: '1.0 - Needs Improvement'
    };
    
    document.getElementById('ratingText').textContent = descriptions[rating] || 'Not Rated';
}


function showRatingDateSelector(dates) {
    const modal = document.getElementById('ratingModal');
    const modalContent = modal.querySelector('.p-6');
    
    // Replace modal content with date selector
    modalContent.innerHTML = `
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Select a day to rate:</h4>
        <div class="space-y-2 max-h-96 overflow-y-auto">
            ${dates.map(dateInfo => `
                <button 
                    type="button"
                    onclick="openRatingFormForDate('${dateInfo.date}', '${dateInfo.dayName}')"
                    class="w-full text-left p-4 rounded-lg border-2 transition ${
                        dateInfo.isRated 
                        ? 'border-purple-300 bg-purple-50 hover:bg-purple-100' 
                        : 'border-gray-200 hover:border-blue-500 hover:bg-blue-50'
                    }"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-gray-800">${dateInfo.formatted}</div>
                            <div class="text-sm text-gray-500">${dateInfo.dayName}</div>
                        </div>
                        ${dateInfo.isRated 
                            ? '<span class="text-purple-600 text-sm font-semibold">✓ Already Rated</span>' 
                            : '<span class="text-blue-600 text-sm">→ Rate Now</span>'
                        }
                    </div>
                </button>
            `).join('')}
        </div>
        <div class="mt-6 flex justify-end">
            <button 
                type="button" 
                onclick="closeRatingModal()" 
                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold"
            >Cancel</button>
        </div>
    `;
    
    modal.classList.remove('hidden');
}

function openRatingFormForDate(ratingDate, dayName) {
    const modal = document.getElementById('ratingModal');
    const modalContent = modal.querySelector('.p-6');
    
    // Check if already rated and get existing rating
    fetch(`get_rating.php?playerid=<?= $player_id ?>&date=${ratingDate}`)
        .then(response => response.json())
        .then(data => {
            const existingRating = data.rating || 0;
            const existingNotes = data.notes || '';
            
            // Restore original rating form
            modalContent.innerHTML = `
                <form id="ratingForm" method="POST">
                    <input type="hidden" name="save_performance_rating" value="1">
                    <input type="hidden" name="player_id" value="<?= $player_id ?>">
                    <input type="hidden" name="week_id" value="">
                    <input type="hidden" name="day_name" value="${dayName}">
                    <input type="hidden" name="rating_date" value="${ratingDate}">
                    <input type="hidden" name="performance_rating" id="performanceRating" value="${existingRating}">
                    <input type="hidden" name="coach_notes" id="coachNotes">
                    
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Performance Rating</label>
                        <div class="flex justify-center space-x-2 mb-2" id="starRating">
                            ${[1,2,3,4,5].map(i => `
                                <button 
                                    type="button" 
                                    class="star-select text-3xl ${i <= existingRating ? 'text-yellow-400' : 'text-gray-300'} hover:text-yellow-400 transition" 
                                    data-rating="${i}"
                                    onclick="selectRating(${i})"
                                >★</button>
                            `).join('')}
                        </div>
                        <div class="text-center">
                            <span id="ratingText" class="text-sm font-semibold text-gray-600">${existingRating > 0 ? existingRating.toFixed(1) : 'Not Rated'}</span>
                        </div>
                        <div id="ratingDescription" class="text-xs text-gray-500 mt-1 text-center"></div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Coach's Notes / Advice</label>
                        <textarea 
                            name="coach_notes" 
                            id="coachNotesTextarea" 
                            rows="4" 
                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-sm"
                            placeholder="Enter your feedback, observations, and advice for the player..."
                        >${existingNotes}</textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeRatingModal()" 
                            class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold"
                        >Cancel</button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold"
                        >Save Rating</button>
                    </div>
                </form>
            `;
            
            // Initialize rating
            currentRating = existingRating;
            updateStarDisplay(existingRating);
            
            // Update modal title
            const modalTitle = document.getElementById('ratingModalTitle');
            if (modalTitle) {
                const dateObj = new Date(ratingDate + 'T00:00:00');
                const formattedDate = dateObj.toLocaleDateString('en-US', {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric'
                });
                modalTitle.textContent = `Rate Performance - ${formattedDate}`;
            }
        });
}

function selectRating(rating) {
    currentRating = rating;
    document.getElementById('performanceRating').value = rating;
    document.getElementById('coachNotes').value = document.getElementById('coachNotesTextarea').value;
    updateStarDisplay(rating);
}


function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    currentRating = 0;
}

function updateStarDisplay(rating) {
    const stars = document.querySelectorAll('.star-select');
    const ratingText = document.getElementById('ratingText');
    const ratingDescription = document.getElementById('ratingDescription');
    
    stars.forEach((star, index) => {
        const starValue = index + 1;
        if (starValue <= rating) {
            star.textContent = '★';
            star.classList.add('text-yellow-400');
            star.classList.remove('text-gray-300', 'hover:text-yellow-400');
        } else {
            star.textContent = '☆';
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300', 'hover:text-yellow-400');
        }
    });
    
    // Update rating text and description
    ratingText.textContent = rating > 0 ? `${rating.toFixed(1)} - ${getRatingDescription(rating)}` : 'Not Rated';
    
    if (rating > 0) {
        ratingDescription.textContent = getRatingCriteria(rating);
    } else {
        ratingDescription.textContent = 'Click stars to rate performance';
    }
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

function showCoachNotes(notes) {
    document.getElementById('notesModal').classList.remove('hidden');
    document.getElementById('notesContent').textContent = notes;
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
}

// Star rating interaction
document.addEventListener('DOMContentLoaded', function() {
    // Star selection
    const starRating = document.getElementById('starRating');
    if (starRating) {
        document.querySelectorAll('.star-select').forEach(star => {
            star.addEventListener('click', function() {
                const rating = parseInt(this.dataset.rating);
                currentRating = rating;
                document.getElementById('performanceRating').value = rating;
                updateStarDisplay(rating);
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = parseInt(this.dataset.rating);
                updateStarDisplay(rating);
            });
        });
        
        // Reset stars when mouse leaves the container
        starRating.addEventListener('mouseleave', function() {
            updateStarDisplay(currentRating);
        });
    }

    // Handle rating form submission
    const ratingForm = document.getElementById('ratingForm');
    if (ratingForm) {
        ratingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (currentRating === 0) {
                alert('Please select a rating before saving.');
                return;
            }
            
            this.submit();
        });
    }

    // Add scroll indicator for weekly overview
    const overviewGrid = document.querySelector('.weekly-overview-grid');
    if (overviewGrid) {
        // Check if content is scrollable
        const isScrollable = overviewGrid.scrollWidth > overviewGrid.clientWidth;
        
        if (isScrollable) {
            // Add a subtle hint that content is scrollable
            overviewGrid.style.background = 'linear-gradient(90deg, transparent 95%, #f0f0f0 100%)';
            
            // Remove the gradient when user starts scrolling
            overviewGrid.addEventListener('scroll', function() {
                overviewGrid.style.background = 'transparent';
            }, { once: true });
        }
    
    }
});

</script>

<style>
.weekly-overview-grid {
    display: grid;
    grid-template-columns: repeat(7, minmax(180px, 1fr));
    gap: 12px;
    margin-top: 16px;
    overflow-x: auto;
    padding-bottom: 16px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e0 #f7fafc;
}

/* Custom scrollbar for Webkit browsers (Chrome, Safari, Edge) */
.weekly-overview-grid::-webkit-scrollbar {
    height: 8px;
}

.weekly-overview-grid::-webkit-scrollbar-track {
    background: #f7fafc;
    border-radius: 4px;
}

.weekly-overview-grid::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 4px;
}

.weekly-overview-grid::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

.day-column {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    min-height: 300px;
    min-width: 180px;
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

.rating-button {
    width: 100%;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.rating-button:hover {
    background: #2563eb;
}

.rating-button.edit {
    background: #f59e0b;
}

.rating-button.edit:hover {
    background: #d97706;
}

.empty-rating {
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
    padding: 12px 0;
}

/* Monthly Calendar Styles */
#monthlyCalendar > div {
    transition: all 0.2s ease;
}

#monthlyCalendar > div:hover:not(.bg-gray-100) {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

#monthlyCalendar > div.bg-gray-100 {
    cursor: default;
}

/* Activity icons in calendar */
.activity-icon-container {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 2px;
}

.activity-icon {
    font-size: 16px;
}

.activity-buttons {
    display: inline-flex;
    gap: 4px;
    margin-left: 4px;
}

.details-btn {
    background: #3b82f6;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    transition: background 0.2s;
}

.details-btn:hover {
    background: #2563eb;
}

.delete-activity-btn {
    background: #ef4444;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    transition: background 0.2s;
}

.delete-activity-btn:hover {
    background: #dc2626;
}

.details-btn {
    background: #e5e7eb;      /* light gray */
    border: none;
    cursor: pointer;
    font-size: 12px;          /* gawin 14–16 kung gusto mo mas kita */
    color: #374151;
    margin-left: 4px;
    padding: 4px 8px;         /* dagdag padding para lumaki */
    border-radius: 9999px;    /* pill button */
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.details-btn:hover {
    background: #d1d5db;
}

/* Monthly navigation */
.month-navigation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}

.month-nav-btn {
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 8px 16px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.2s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.month-nav-btn:hover {
    background: #2563eb;
}

.current-month {
    font-size: 1.25rem;
    font-weight: 700;
    color: #374151;
}

/* Responsive calendar */
@media (max-width: 768px) {
    .weekly-overview-grid {
        grid-template-columns: repeat(7, minmax(160px, 1fr));
    }
    
    #monthlyCalendar {
        gap: 1px;
    }
    
    #monthlyCalendar > div {
        height: 80px;
        padding: 4px;
        font-size: 0.8rem;
    }
    
    #monthlyCalendar .flex-wrap {
        gap: 0.5px;
    }
    
    #monthlyCalendar .flex-wrap span {
        font-size: 0.7rem;
    }
    
    .month-navigation {
        flex-direction: column;
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .weekly-overview-grid {
        grid-template-columns: repeat(7, minmax(120px, 1fr));
    }
}

/* Monthly Overview Styles */
.monthly-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 16px;
}

.monthly-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    grid-column: 1 / -1;
}

.stat-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 24px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8fafc;
    border-radius: 10px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.activity-distribution,
.recent-ratings,
.monthly-progress {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
}

.distribution-title,
.ratings-title,
.progress-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 16px;
}

.distribution-bar {
    display: flex;
    height: 30px;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 12px;
}

.bar-segment {
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
}

.bar-segment:hover {
    transform: scale(1.05);
}

.workout-segment {
    background: #3b82f6;
}

.drill-segment {
    background: #f59e0b;
}

.rest-segment {
    background: #10b981;
}

.segment-label {
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.3);
}

.distribution-legend {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.workout-color { background: #3b82f6; }
.drill-color { background: #f59e0b; }
.rest-color { background: #10b981; }

.ratings-list .rating-item + .rating-item {
    margin-top: 8px;
}

.rating-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
}

.rating-item:last-child {
    border-bottom: none;
}

.rating-date {
    font-size: 0.875rem;
    color: #6b7280;
    min-width: 60px;
}

.rating-stars {
    display: flex;
    gap: 2px;
}

.star {
    color: #d1d5db;
    font-size: 1rem;
}

.star.filled {
    color: #f59e0b;
}

.rating-value {
    font-weight: 600;
    color: #374151;
    min-width: 30px;
}

.notes-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.notes-btn:hover {
    background: #f3f4f6;
}

.progress-stats .progress-item + .progress-item {
    margin-top: 12px;
}

.progress-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.progress-label {
    font-size: 0.875rem;
    color: #374151;
    min-width: 120px;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #34d399);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    min-width: 50px;
}

.no-data {
    text-align: center;
    color: #9ca3af;
    font-style: italic;
    padding: 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .monthly-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .monthly-overview-grid {
        grid-template-columns: 1fr;
    }
    
    .distribution-legend {
        flex-direction: column;
        gap: 8px;
    }
    
    .progress-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .progress-label {
        min-width: auto;
    }
}

/* Custom scrollbar for calendar day cells */
#monthlyCalendar > div[data-date] {
    overflow-y: auto;
    scrollbar-width: thin; /* For Firefox */
    scrollbar-color: #cbd5e0 transparent; /* For Firefox */
}

/* For Chrome, Safari, Edge */
#monthlyCalendar > div[data-date]::-webkit-scrollbar {
    width: 4px;
}

#monthlyCalendar > div[data-date]::-webkit-scrollbar-track {
    background: transparent;
}

#monthlyCalendar > div[data-date]::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 10px;
}

#monthlyCalendar > div[data-date]::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}

</style>

<?php include 'includes/footer.php'; ?>
<?php endif; ?>
<?php ob_end_flush(); ?>