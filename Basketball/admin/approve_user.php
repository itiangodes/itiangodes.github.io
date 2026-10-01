<?php
include 'includes/db.php';
include 'includes/auth_admin.php'; // ensure admin access

$role = $_GET['role'] ?? '';
$id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$role || !$id || !in_array($action, ['approve','reject'])) {
    die("Invalid request");
}

// Determine table
$table = $role === 'player' ? 'players' : ($role === 'coach' ? 'coaches' : '');
if (!$table) die("Invalid role");

// Update status
$stmt = $conn->prepare("UPDATE $table SET status=? WHERE id=?");
$stmt->bind_param("si", $action === 'approve' ? 'Approved' : 'Rejected', $id);

if ($stmt->execute()) {
    header("Location: admin_approvals.php?msg=success");
    exit;
} else {
    echo "Error: " . $stmt->error;
}
