<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'basketball_league';
$username = 'root';
$password = '';

header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['available' => true, 'error' => 'Database connection failed']);
    exit();
}

if (isset($_GET['barangay'])) {
    $barangay = $_GET['barangay'];
    
    // Map barangay codes to display names
    $barangayNames = [
        'amaya1' => 'Amaya I', 'amaya2' => 'Amaya II', 'amaya3' => 'Amaya III',
        'amaya4' => 'Amaya IV', 'amaya5' => 'Amaya V', 'amaya6' => 'Amaya VI',
        'amaya7' => 'Amaya VII', 'amaya8' => 'Amaya VIII', 'amaya9' => 'Amaya IX',
        'amaya10' => 'Amaya X', 'amaya11' => 'Amaya XI', 'amaya12' => 'Amaya XII',
        'bagtas' => 'Bagtas', 'biga' => 'Biga', 'biwas' => 'Biwas',
        'bucal' => 'Bucal', 'calibuyo' => 'Calibuyo', 'capipisa' => 'Capipisa',
        'daang_amaya1' => 'Daang Amaya I', 'daang_amaya2' => 'Daang Amaya II',
        'daang_amaya3' => 'Daang Amaya III', 'halayhay' => 'Halayhay',
        'julugan1' => 'Julugan I', 'julugan2' => 'Julugan II', 'julugan3' => 'Julugan III',
        'julugan4' => 'Julugan IV', 'julugan5' => 'Julugan V', 'julugan6' => 'Julugan VI',
        'julugan7' => 'Julugan VII', 'julugan8' => 'Julugan VIII', 'mulawin' => 'Mulawin',
        'paradahan1' => 'Paradahan I', 'paradahan2' => 'Paradahan II',
        'poblacion1' => 'Poblacion I', 'poblacion2' => 'Poblacion II',
        'poblacion3' => 'Poblacion III', 'poblacion4' => 'Poblacion IV',
        'punta1' => 'Punta I', 'punta2' => 'Punta II', 'sahud_ulan' => 'Sahud Ulan',
        'sanja_mayor' => 'Sanja Mayor', 'santol' => 'Santol', 'tanauan' => 'Tanauan',
        'tres_cruses' => 'Tres Cruses'
    ];
    
    $barangayDisplayName = $barangayNames[$barangay] ?? $barangay;
    
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
        echo json_encode([
            'available' => false,
            'message' => "Barangay {$barangayDisplayName} already has an approved coach: {$existingCoach['firstname']} {$existingCoach['lastname']}. Only one coach per barangay is allowed.",
            'barangay_name' => $barangayDisplayName,
            'existing_coach' => "{$existingCoach['firstname']} {$existingCoach['lastname']}"
        ]);
    } else {
        echo json_encode([
            'available' => true,
            'message' => "Barangay {$barangayDisplayName} is available for coach registration!",
            'barangay_name' => $barangayDisplayName
        ]);
    }
} else {
    echo json_encode(['available' => true, 'error' => 'No barangay specified']);
}
?>