<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['role'])) {
    header("Location: ../login/Login.html");
    exit();
}

// Only allow admins
if (strtolower($_SESSION['role']) !== 'admin') {
    header("Location: ../unauthorized.php");
    exit();
}
?>
