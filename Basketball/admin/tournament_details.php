<?php
// tournament_details.php
include 'includes/auth.php';
include 'includes/db.php';

// Initialize variables
$error = '';
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $name = trim($_POST['tournament_name'] ?? '');
        $start_date = $_POST['tournament_start_date'] ?? '';
        $registration_deadline = $_POST['registration_deadline'] ?? '';
        $location = trim($_POST['tournament_location'] ?? '');
        $max_teams = 41; // Fixed value
        
        // Validate required fields
        if (empty($name)) {
            throw new Exception("Tournament name is required.");
        }
        
        if (empty($start_date)) {
            throw new Exception("Tournament start date is required.");
        }
        
        if (empty($registration_deadline)) {
            throw new Exception("Registration deadline is required.");
        }
        
        if (empty($location)) {
            throw new Exception("Tournament location is required.");
        }
        
        // Validate dates
        $current_date = date('Y-m-d');
        if ($start_date < $current_date) {
            throw new Exception("Tournament start date cannot be in the past. Please select today or a future date.");
        }
        
        if ($registration_deadline < $current_date) {
            throw new Exception("Registration deadline cannot be in the past.");
        }
        
        if ($registration_deadline >= $start_date) {
            throw new Exception("Registration deadline must be before tournament start date.");
        }
        
        // Validate name length
        if (strlen($name) > 100) {
            throw new Exception("Tournament name must be less than 100 characters.");
        }
        
        // Validate location length
        if (strlen($location) > 200) {
            throw new Exception("Location must be less than 200 characters.");
        }
        
        // Check if tournaments table has all required columns, if not alter it
        $check_columns = $pdo->query("SHOW COLUMNS FROM tournaments LIKE 'status'");
        if (!$check_columns->fetch()) {
            // Add missing columns
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN status VARCHAR(20) DEFAULT 'registration_open'");
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN format VARCHAR(50) NULL");
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN team_count INT DEFAULT 0");
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN registration_deadline DATE NULL AFTER start_date");
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN max_teams INT DEFAULT 41 AFTER location");
            error_log("Added missing columns to tournaments table");
        }
        
        // Check if start_date column exists, if not add it
        $check_start_date = $pdo->query("SHOW COLUMNS FROM tournaments LIKE 'start_date'");
        if (!$check_start_date->fetch()) {
            // Add start_date column and migrate data from date column
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN start_date DATE NULL AFTER date");
            $pdo->exec("UPDATE tournaments SET start_date = date WHERE start_date IS NULL");
            $pdo->exec("ALTER TABLE tournaments MODIFY start_date DATE NOT NULL");
            error_log("Added start_date column to tournaments table");
        }
        
        // Check if registration_deadline column exists, if not add it
        $check_registration_deadline = $pdo->query("SHOW COLUMNS FROM tournaments LIKE 'registration_deadline'");
        if (!$check_registration_deadline->fetch()) {
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN registration_deadline DATE NULL AFTER start_date");
            error_log("Added registration_deadline column to tournaments table");
        }
        
        // Check if max_teams column exists, if not add it
        $check_max_teams = $pdo->query("SHOW COLUMNS FROM tournaments LIKE 'max_teams'");
        if (!$check_max_teams->fetch()) {
            $pdo->exec("ALTER TABLE tournaments ADD COLUMN max_teams INT DEFAULT 41 AFTER location");
            error_log("Added max_teams column to tournaments table");
        }
        
        // Insert tournament into database with all fields
        $stmt = $pdo->prepare("
            INSERT INTO tournaments (name, start_date, registration_deadline, location, max_teams, status, created_by, created_at) 
            VALUES (?, ?, ?, ?, ?, 'registration_open', ?, NOW())
        ");
        
        $stmt->execute([
            $name,
            $start_date,
            $registration_deadline,
            $location,
            $max_teams,
            $_SESSION['user_id']
        ]);
        
        $tournament_id = $pdo->lastInsertId();
        
        // Store success data in session for the modal
        $_SESSION['success_tournament_id'] = $tournament_id;
        $_SESSION['success_message'] = "Tournament '{$name}' created successfully! Teams can now register until {$registration_deadline}.";
        
        // Redirect to tournament.php with success parameter
        header("Location: tournament.php?success=1");
        exit();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Store form data for repopulation
        $form_data = [
            'name' => $_POST['tournament_name'] ?? '',
            'start_date' => $_POST['tournament_start_date'] ?? '',
            'registration_deadline' => $_POST['registration_deadline'] ?? '',
            'location' => $_POST['tournament_location'] ?? ''
        ];
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Create New Tournament</h1>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-md p-6">
                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex justify-between items-center">
                        <div>
                            <strong class="font-medium">Error:</strong>
                            <span class="ml-1"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                        <button onclick="this.parentElement.style.display='none'" class="text-red-700 hover:text-red-900">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                <?php endif; ?>

                <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
                    <i class="fas fa-info-circle text-blue-500"></i> Tournament Details
                </h2>
                
                <form method="POST" action="" class="space-y-6" id="tournamentForm">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="tournament_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Tournament Name *
                            </label>
                            <input 
                                type="text" 
                                id="tournament_name" 
                                name="tournament_name"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                placeholder="Enter tournament name"
                                required
                                maxlength="100"
                                value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>"
                            >
                            <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                        </div>

                        <div>
                            <label for="tournament_start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                Tournament Start Date *
                            </label>
                            <input 
                                type="date" 
                                id="tournament_start_date" 
                                name="tournament_start_date"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                required
                                value="<?php echo htmlspecialchars($form_data['start_date'] ?? ''); ?>"
                                min="<?php echo date('Y-m-d'); ?>"
                            >
                            <p class="text-xs text-gray-500 mt-1">Cannot be a past date</p>
                        </div>

                        <div>
                            <label for="registration_deadline" class="block text-sm font-medium text-gray-700 mb-2">
                                Registration Deadline *
                            </label>
                            <input 
                                type="date" 
                                id="registration_deadline" 
                                name="registration_deadline"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                required
                                value="<?php echo htmlspecialchars($form_data['registration_deadline'] ?? ''); ?>"
                                min="<?php echo date('Y-m-d'); ?>"
                            >
                            <p class="text-xs text-gray-500 mt-1">Deadline for team registrations</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Maximum Teams
                            </label>
                            <div class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700">
                                <i class="fas fa-users text-blue-500 mr-2"></i>
                                41 Teams (Fixed)
                            </div>
                            <p class="text-xs text-gray-500 mt-1">All tournaments support exactly 41 teams</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="tournament_location" class="block text-sm font-medium text-gray-700 mb-2">
                                Location *
                            </label>
                            <input 
                                type="text" 
                                id="tournament_location" 
                                name="tournament_location"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                placeholder="Enter tournament location"
                                required
                                maxlength="200"
                                value="<?php echo htmlspecialchars($form_data['location'] ?? ''); ?>"
                            >
                            <p class="text-xs text-gray-500 mt-1">Maximum 200 characters</p>
                        </div>
                    </div>

                    <!-- Information Card -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                            <div>
                                <h4 class="font-medium text-blue-800">Tournament Workflow</h4>
                                <p class="text-blue-700 text-sm mt-1">
                                    After creation, your tournament will go through these phases:
                                </p>
                                <ul class="text-blue-700 text-sm mt-2 space-y-1">
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-user-plus text-xs"></i>
                                        <span><strong>Phase 1:</strong> Tournament Creation (Current)</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-clipboard-list text-xs"></i>
                                        <span><strong>Phase 2:</strong> Team Registration & Approval (41 teams max)</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-cogs text-xs"></i>
                                        <span><strong>Phase 3:</strong> Bracket Setup with Approved Teams</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-play text-xs"></i>
                                        <span><strong>Phase 4:</strong> Tournament Execution</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Original Buttons -->
                    <div class="flex justify-between pt-4">
                        <a href="tournament.php" class="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i>
                            Back to Tournaments
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-3 px-6 rounded-lg font-medium transition duration-200 flex items-center gap-2">
                            <i class="fas fa-plus"></i>
                            Create Tournament
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tournamentForm');
    const tournamentName = document.getElementById('tournament_name');
    const tournamentStartDate = document.getElementById('tournament_start_date');
    const registrationDeadline = document.getElementById('registration_deadline');
    const tournamentLocation = document.getElementById('tournament_location');
    
    // Set minimum dates to today
    const today = new Date().toISOString().split('T')[0];
    tournamentStartDate.min = today;
    registrationDeadline.min = today;
    
    // Set default values if empty
    if (!tournamentStartDate.value) {
        // Default start date: 7 days from today
        const defaultStartDate = new Date();
        defaultStartDate.setDate(defaultStartDate.getDate() + 7);
        tournamentStartDate.value = defaultStartDate.toISOString().split('T')[0];
    }
    
    if (!registrationDeadline.value) {
        // Default registration deadline: 5 days from today
        const defaultDeadline = new Date();
        defaultDeadline.setDate(defaultDeadline.getDate() + 5);
        registrationDeadline.value = defaultDeadline.toISOString().split('T')[0];
    }
    
    // Ensure registration deadline is always before start date
    function updateDateConstraints() {
        const startDateValue = tournamentStartDate.value;
        const deadlineValue = registrationDeadline.value;
        
        if (startDateValue) {
            registrationDeadline.max = new Date(startDateValue);
            registrationDeadline.max.setDate(registrationDeadline.max.getDate() - 1);
            registrationDeadline.max = registrationDeadline.max.toISOString().split('T')[0];
        }
        
        if (deadlineValue) {
            tournamentStartDate.min = new Date(deadlineValue);
            tournamentStartDate.min.setDate(tournamentStartDate.min.getDate() + 1);
            tournamentStartDate.min = tournamentStartDate.min.toISOString().split('T')[0];
        }
    }
    
    // Update constraints when dates change
    tournamentStartDate.addEventListener('change', updateDateConstraints);
    registrationDeadline.addEventListener('change', updateDateConstraints);
    
    // Initialize constraints
    updateDateConstraints();
    
    // Form validation
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validate tournament name
        if (tournamentName.value.trim().length === 0) {
            showError(tournamentName, 'Tournament name is required');
            valid = false;
        } else if (tournamentName.value.trim().length > 100) {
            showError(tournamentName, 'Tournament name must be less than 100 characters');
            valid = false;
        } else {
            clearError(tournamentName);
        }
        
        // Validate start date
        if (!tournamentStartDate.value) {
            showError(tournamentStartDate, 'Tournament start date is required');
            valid = false;
        } else if (tournamentStartDate.value < today) {
            showError(tournamentStartDate, 'Tournament start date cannot be in the past');
            valid = false;
        } else {
            clearError(tournamentStartDate);
        }
        
        // Validate registration deadline
        if (!registrationDeadline.value) {
            showError(registrationDeadline, 'Registration deadline is required');
            valid = false;
        } else if (registrationDeadline.value < today) {
            showError(registrationDeadline, 'Registration deadline cannot be in the past');
            valid = false;
        } else if (registrationDeadline.value >= tournamentStartDate.value) {
            showError(registrationDeadline, 'Registration deadline must be before tournament start date');
            valid = false;
        } else {
            clearError(registrationDeadline);
        }
        
        // Validate location
        if (tournamentLocation.value.trim().length === 0) {
            showError(tournamentLocation, 'Tournament location is required');
            valid = false;
        } else if (tournamentLocation.value.trim().length > 200) {
            showError(tournamentLocation, 'Location must be less than 200 characters');
            valid = false;
        } else {
            clearError(tournamentLocation);
        }
        
        if (!valid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.border-red-500');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
    
    function showError(input, message) {
        clearError(input);
        input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.remove('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'text-red-500 text-xs mt-1';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    }
    
    function clearError(input) {
        input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.add('border-gray-300', 'focus:ring-blue-500', 'focus:border-blue-500');
        
        const errorDiv = input.parentNode.querySelector('.text-red-500');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Real-time validation
    tournamentName.addEventListener('input', function() {
        if (this.value.trim().length > 0 && this.value.trim().length <= 100) {
            clearError(this);
        }
    });
    
    tournamentLocation.addEventListener('input', function() {
        if (this.value.trim().length > 0 && this.value.trim().length <= 200) {
            clearError(this);
        }
    });
    
    tournamentStartDate.addEventListener('change', function() {
        if (this.value && this.value >= today) {
            clearError(this);
            updateDateConstraints();
        }
    });
    
    registrationDeadline.addEventListener('change', function() {
        if (this.value && this.value >= today) {
            clearError(this);
            updateDateConstraints();
        }
    });
    
    // Prevent manual editing of date fields
    [tournamentStartDate, registrationDeadline].forEach(dateInput => {
        dateInput.addEventListener('keydown', function(e) {
            e.preventDefault();
        });
        
        dateInput.addEventListener('input', function() {
            if (this.value < today) {
                this.value = today;
                showError(this, 'Past dates are not allowed. Date reset to today.');
                setTimeout(() => clearError(this), 3000);
            }
        });
    });
});
</script>

<style>
/* Smooth transitions for form elements */
input, button, a {
    transition: all 0.2s ease-in-out;
}

/* Focus styles */
input:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Date input specific styles */
input[type="date"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    padding: 5px;
    border-radius: 3px;
}

input[type="date"]::-webkit-calendar-picker-indicator:hover {
    background-color: #f3f4f6;
}

/* Disable manual editing of date field */
input[type="date"] {
    -moz-appearance: textfield;
}

input[type="date"]::-webkit-inner-spin-button,
input[type="date"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Fixed field styling */
.bg-gray-50 {
    background-color: #f9fafb;
}
</style>

<?php include 'includes/footer.php'; ?>