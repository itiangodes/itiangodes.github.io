<?php
// migrate_start_date.php
include 'includes/auth.php';
include 'includes/db.php';

try {
    // Check if start_date column exists
    $check_start_date = $pdo->query("SHOW COLUMNS FROM tournaments LIKE 'start_date'");
    if (!$check_start_date->fetch()) {
        echo "Adding start_date column to tournaments table...<br>";
        
        // Add start_date column
        $pdo->exec("ALTER TABLE tournaments ADD COLUMN start_date DATE NULL AFTER date");
        echo "start_date column added successfully.<br>";
        
        // Migrate data from date to start_date
        $pdo->exec("UPDATE tournaments SET start_date = date WHERE start_date IS NULL");
        echo "Data migrated from date to start_date.<br>";
        
        // Make start_date NOT NULL after migration
        $pdo->exec("ALTER TABLE tournaments MODIFY start_date DATE NOT NULL");
        echo "start_date column set to NOT NULL.<br>";
        
        echo "<strong>Successfully migrated to start_date column!</strong><br>";
        echo "All existing tournaments have been updated to use start_date.";
    } else {
        echo "start_date column already exists! No migration needed.";
    }
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage();
}