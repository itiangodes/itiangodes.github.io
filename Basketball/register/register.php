<?php
session_start();

// ================= DATABASE CONFIG =================
$host = 'localhost';
$dbname = 'basketball_league';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ================= HANDLE POST ======================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data
    $role = $_POST['role'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'] ?? '';
    $lastname = $_POST['lastname'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $barangay = $_POST['barangay'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Initialize variables
    $document_path = null;
    $photo_path = null;
    $errors = [];

    // ============ BASIC VALIDATION =================
    if (
        empty($role) || empty($firstname) || empty($lastname) ||
        empty($gender) || empty($birthday) || empty($barangay) ||
        empty($email) || empty($username) || empty($password)
    ) {
        $errors[] = "All required fields must be filled.";
    }

    // ============ STRICT COACH VALIDATION =================
    if ($role == 'coach') {
        // Check if there's already an approved coach in this barangay
        $stmt = $pdo->prepare("
            SELECT u.user_id, u.firstname, u.lastname 
            FROM users u 
            WHERE u.role = 'coach' 
            AND u.barangay = ? 
            AND u.status = 'approved'
            LIMIT 1
        ");
        $stmt->execute([$barangay]);
        
        if ($existingCoach = $stmt->fetch()) {
            $errors[] = "This barangay already has an approved coach (" . $existingCoach['firstname'] . " " . $existingCoach['lastname'] . "). Only one coach per barangay is allowed. You cannot register as a coach for this barangay.";
        }
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // ============ AGE VALIDATION =================
    $birthDate = new DateTime($birthday);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
    
    if ($role == 'player') {
        if ($age < 15 || $age > 30) {
            $errors[] = "Players must be between 15 and 30 years old.";
        }
    } else if ($role == 'coach') {
        if ($age < 18) {
            $errors[] = "Coaches must be at least 18 years old.";
        }
    } else if ($role == 'community') {
        if ($age < 15) {
            $errors[] = "Community members must be at least 15 years old.";
        }
    }

    // Check email uniqueness
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists. Please use a different email address.";
        }
    }

    // Check username uniqueness
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
    }

    // ============ UPLOAD VALIDATION =================
    // Only require documents and photos for coaches and players
    if (($role == 'player' || $role == 'coach')) {
        
        // Handle document upload
        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            // Set upload directory based on role
            if ($role == 'player') {
                $uploadDir = '../uploads/documents/players/';
            } else {
                $uploadDir = '../uploads/documents/coaches/';
            }

            // Create folder if does not exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $ext = pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "_" . $username . "_document." . $ext;
            $document_path = $uploadDir . $filename;

            // Validate file size (5MB max)
            if ($_FILES['document']['size'] > 5 * 1024 * 1024) {
                $errors[] = "Document must be less than 5MB.";
            } else {
                // Move file
                if (!move_uploaded_file($_FILES['document']['tmp_name'], $document_path)) {
                    $errors[] = "Failed to upload document. Please try again.";
                }
            }
        } else {
            $errors[] = "Voter's Certificate or Barangay Clearance/Indigency is required for this role.";
        }

        // Handle single photo upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Set upload directory for photos
            if ($role == 'player') {
                $photoDir = '../uploads/photos/players/';
            } else {
                $photoDir = '../uploads/photos/coaches/';
            }

            // Create folder if does not exist
            if (!is_dir($photoDir)) {
                mkdir($photoDir, 0755, true);
            }

            // Generate unique filename
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . "_" . $username . "_photo." . $ext;
            $photo_path = $photoDir . $filename;

            // Validate image type
            $allowed_types = ['image/jpeg', 'image/png'];
            $file_type = $_FILES['photo']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "ID photo must be JPG or PNG format.";
            } else {
                // Validate file size (2MB max)
                if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
                    $errors[] = "ID photo must be less than 2MB.";
                } else {
                    // Move file
                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                        $errors[] = "Failed to upload ID photo. Please try again.";
                    }
                }
            }
        } else {
            $errors[] = "ID photo is required for this role.";
        }
    }
    // Community users don't need documents or photos

    // If errors → return to form with error parameters
    if (!empty($errors)) {
        $error_string = urlencode(implode("|", $errors));
        header("Location: register.html?error=" . $error_string);
        exit();
    }

    // ========= INSERT INTO DATABASE ===========
    try {
        $pdo->beginTransaction();

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Set status based on role - community is automatically approved
        $status = ($role == 'community') ? 'approved' : 'pending';

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users 
            (role, firstname, middlename, lastname, gender, birthday, barangay, email, username, password, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $role, $firstname, $middlename, $lastname, $gender,
            $birthday, $barangay, $email, $username, $hashedPassword, $status
        ]);

        $user_id = $pdo->lastInsertId();

        // Insert into specific tables (only for coaches and players)
        if ($role == 'player') {
            $stmt = $pdo->prepare("INSERT INTO players (user_id, document, photo) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $document_path, $photo_path]);
        }

        if ($role == 'coach') {
            $stmt = $pdo->prepare("INSERT INTO coaches (user_id, document, photo) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $document_path, $photo_path]);
        }

        $pdo->commit();

        // Redirect with appropriate success message based on role
        if ($role == 'community') {
            header("Location: register.html?success=true&message=" . urlencode("Registration successful! Your account has been approved and you can now login."));
        } else {
            header("Location: register.html?success=true&message=" . urlencode("Registration successful! Your account is pending approval."));
        }
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error_string = urlencode("Registration failed: " . $e->getMessage());
        header("Location: register.html?error=" . $error_string);
        exit();
    }
}

// If direct access
header("Location: register.html");
exit();
?>