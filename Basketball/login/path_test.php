<?php
echo "<h1>Path Test</h1>";
echo "Current directory: " . __DIR__ . "<br>";

$paths_to_test = [
    '../PHPMailer/src/PHPMailer.php',
    '../../PHPMailer/src/PHPMailer.php',
    'PHPMailer/src/PHPMailer.php'
];

foreach ($paths_to_test as $path) {
    if (file_exists($path)) {
        echo "✓ Found: $path<br>";
    } else {
        echo "✗ Not found: $path<br>";
    }
}
?>