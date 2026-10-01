<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========================== DATABASE CONNECTION ==========================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "basketball_league";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ========================== LOGIN PROCESS ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo "<script>
            alert('Please enter both username and password.');
            window.history.back();
        </script>";
        exit();
    }

    // Fetch user info + status
    $stmt = $conn->prepare("
        SELECT user_id, username, password, role, status,
               CONCAT(firstname, ' ', lastname) AS fullname
        FROM users
        WHERE username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $dbUsername, $hashedPassword, $role, $status, $fullname);
        $stmt->fetch();

        // BLOCK LOGIN IF STATUS IS PENDING
        if (strtolower($status) === 'pending') {
            echo "<script>
                alert('Your account is still pending approval. Please wait for the admin to verify your account.');
                window.history.back();
            </script>";
            exit();
        }

        // BLOCK LOGIN IF STATUS IS REJECTED
        if (strtolower($status) === 'rejected') {
            echo "<script>
                alert('Your account has been rejected. Please contact support.');
                window.history.back();
            </script>";
            exit();
        }

        // BLOCK LOGIN IF STATUS IS DISABLED
        if (strtolower($status) === 'disabled') {
            echo "<script>
                alert('Your account has been disabled. Please contact the administrator.');
                window.history.back();
            </script>";
            exit();
        }

        // Verify password
        if (password_verify($password, $hashedPassword)) {

            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $dbUsername;
            $_SESSION['role'] = $role;
            $_SESSION['fullname'] = $fullname;

            session_regenerate_id(true);

            // Redirect based on role
            switch (strtolower($role)) {
                case 'admin':
                    $redirect = '../admin/dashboard.php';
                    break;
                case 'coach':
                    $redirect = '../coach/coach_dashboard.php';
                    break;
                case 'player':
                    $redirect = '../player/player_dashboard.php';
                    break;
                case 'community':
                    $redirect = '../community/community_dashboard.php';
                    break;
                default:
                    $redirect = 'dashboard.php';
            }

            echo "<script>
                alert('Welcome back, $fullname!');
                window.location.href = '$redirect';
            </script>";
            exit();

        } else {
            echo "<script>
                alert('Incorrect password.');
                window.history.back();
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('No account found with that username.');
            window.history.back();
        </script>";
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
