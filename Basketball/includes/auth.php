<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../login.html");
    exit();
}

if (strtolower($_SESSION['role']) !== 'coach') {
    header("Location: ../unauthorized.php");
    exit();
}

// Optional: store user info
$fullname = $_SESSION['fullname'];
$username = $_SESSION['username'];
