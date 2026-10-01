<?php
// ======================== ENABLE ERROR REPORTING ========================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session at the VERY TOP
session_start();

// Prevent header already sent errors
ob_start();

include 'includes/db.php'; // $pdo
include 'includes/header.php';
include 'includes/sidebar.php';

// ======================== EMAIL FUNCTION ========================
function sendStatusEmail($email, $fullname, $status, $role) {
    // Find vendor autoload.php automatically
    $vendorPaths = [
        __DIR__ . '/vendor/autoload.php',           // vendor in admin folder
        __DIR__ . '/../vendor/autoload.php',        // vendor in project root (most common)
        __DIR__ . '/../../vendor/autoload.php',     // vendor two levels up
        'C:/xampp/htdocs/Basketball/vendor/autoload.php', // absolute path
        dirname(__DIR__) . '/vendor/autoload.php',  // dynamic project root
        $_SERVER['DOCUMENT_ROOT'] . '/Basketball/vendor/autoload.php', // from document root
    ];
    
    $autoloadPath = null;
    foreach ($vendorPaths as $path) {
        if (file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }
    
    if ($autoloadPath) {
        require $autoloadPath;
    } else {
        // If vendor not found, log error and continue without email
        error_log("Vendor autoload.php not found. Email not sent to: $email");
        return true; // Return true to continue the process
    }
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'interbarangay6@gmail.com'; // Your Gmail address
        $mail->Password = 'memu igfl jfbb oaje'; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('basketball.system.2024@gmail.com', 'Basketball System');
        $mail->addAddress($email, $fullname);
        
        // Content
        $mail->isHTML(true);
        
        if ($status === 'approved') {
            $mail->Subject = 'Account Approval Notification - Basketball System';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #22c55e; text-align: center;'>🎉 Account Approved!</h2>
                    <div style='background: #f9fafb; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p>Dear <strong>$fullname</strong>,</p>
                        <p>We are pleased to inform you that your <strong>$role</strong> account has been <strong style='color: #22c55e;'>APPROVED</strong> by our administration team.</p>
                        <p>You can now log in to the Basketball System and access all features available to $role accounts.</p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/Basketball/login.php' style='background: #22c55e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Login to Your Account</a>
                    </div>
                    <p style='color: #6b7280; font-size: 14px; text-align: center;'>
                        Best regards,<br>
                        <strong>Basketball System Team</strong>
                    </p>
                </div>
            ";
        } else {
            $mail->Subject = 'Account Registration Update - Basketball System';
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #ef4444; text-align: center;'>❌ Account Not Approved</h2>
                    <div style='background: #fef2f2; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <p>Dear <strong>$fullname</strong>,</p>
                        <p>We regret to inform you that your <strong>$role</strong> account registration has been <strong style='color: #ef4444;'>REJECTED</strong>.</p>
                        <p>This decision may be due to incomplete documentation, verification issues, or other administrative reasons.</p>
                        <p>If you believe this is an error or would like more information, please contact our support team.</p>
                    </div>
                    <div style='text-align: center; margin: 30px 0;'>
                        <p>You may re-apply with complete and valid documents if you wish to try again.</p>
                    </div>
                    <p style='color: #6b7280; font-size: 14px; text-align: center;'>
                        Best regards,<br>
                        <strong>Basketball System Team</strong>
                    </p>
                </div>
            ";
        }
        
        $mail->AltBody = strip_tags($mail->Body);
        
        $mail->send();
        error_log("Email sent successfully to: $email - Status: $status - Role: $role");
        return true;
        
    } catch (Exception $e) {
        error_log("Email could not be sent to: $email. Error: " . $mail->ErrorInfo);
        return false;
    }
}

// ======================== DELETE DOCUMENT FILE ========================
function deleteDocumentFile($documentPath) {
    if ($documentPath && file_exists($documentPath)) {
        unlink($documentPath);
        error_log("Deleted document file: $documentPath");
    }
}

// ======================== DELETE PHOTO FILE ========================
function deletePhotoFile($photoPath) {
    if ($photoPath && file_exists($photoPath)) {
        unlink($photoPath);
        error_log("Deleted photo file: $photoPath");
    }
}

// ======================== APPROVE / REJECT HANDLER ========================
if (isset($_GET['role'], $_GET['id'], $_GET['action'])) {
    $role = $_GET['role'];
    $id = intval($_GET['id']);
    $action = $_GET['action']; // "approve" or "reject"

    if ($role === 'player') {
        $table = 'players';
        $id_column = 'player_id';
    } elseif ($role === 'coach') {
        $table = 'coaches';
        $id_column = 'coach_id';
    } else {
        $_SESSION['error'] = "Invalid role";
        header("Location: user_approvals.php");
        exit();
    }

    $status = $action === 'approve' ? 'approved' : 'rejected';

    try {
        $pdo->beginTransaction();

        if ($action === 'reject') {
            // Get document path, photo path and user_id before deleting
            $stmt = $pdo->prepare("SELECT document, photo, user_id FROM $table WHERE $id_column = ?");
            $stmt->execute([$id]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $documentPath = $userData['document'];
            $photoPath = $userData['photo'];
            $user_id = $userData['user_id'];

            // Get user details for email before deleting
            $stmt = $pdo->prepare("SELECT firstname, middlename, lastname, email FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete from specific table (players or coaches)
            $stmt = $pdo->prepare("DELETE FROM $table WHERE $id_column = ?");
            $stmt->execute([$id]);

            // Delete from users table
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);

            // Delete the document file
            if ($documentPath) {
                deleteDocumentFile($documentPath);
            }

            // Delete the photo file
            if ($photoPath) {
                deletePhotoFile($photoPath);
            }

            // Send rejection email
            if ($user) {
                $fullname = trim($user['firstname'] . ' ' . ($user['middlename'] ?? '') . ' ' . $user['lastname']);
                $email = $user['email'];
                
                $emailSent = sendStatusEmail($email, $fullname, $status, $role);
                
                if (!$emailSent) {
                    error_log("Failed to send rejection email to: " . $email);
                    // Don't stop the process if email fails
                }
            }

            $message = "User rejected and removed from system successfully";

        } else {
            // APPROVE ACTION - update status only
            // Update role table
            $stmt = $pdo->prepare("UPDATE $table SET status = ? WHERE $id_column = ?");
            $stmt->execute([$status, $id]);

            // Update users table
            $stmt2 = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = (SELECT user_id FROM $table WHERE $id_column = ?)");
            $stmt2->execute([$status, $id]);

            // Get user details for email
            $stmt3 = $pdo->prepare("SELECT firstname, middlename, lastname, email FROM users WHERE user_id = (SELECT user_id FROM $table WHERE $id_column = ?)");
            $stmt3->execute([$id]);
            $user = $stmt3->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $fullname = trim($user['firstname'] . ' ' . ($user['middlename'] ?? '') . ' ' . $user['lastname']);
                $email = $user['email'];
                
                // Send email notification
                $emailSent = sendStatusEmail($email, $fullname, $status, $role);
                
                if (!$emailSent) {
                    error_log("Failed to send approval email to: " . $email);
                    // Don't stop the process if email fails
                }
            }

            $message = "User approved successfully";
        }

        $pdo->commit();
        
        // Clear output buffer before redirect
        ob_end_clean();
        
        // Show success message
        $_SESSION['success'] = $message;
        header("Location: user_approvals.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        ob_end_clean();
        $_SESSION['error'] = "Error processing user: " . $e->getMessage();
        header("Location: user_approvals.php");
        exit();
    }
}

// ======================== FETCH PENDING USERS ========================
// Pending Players
$pendingPlayers = $pdo->query("
    SELECT p.player_id, p.document, p.photo, u.firstname, u.middlename, u.lastname, u.username, u.email
    FROM players p
    JOIN users u ON p.user_id = u.user_id
    WHERE u.status = 'pending' AND p.status = 'pending'
    ORDER BY p.player_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$pendingCoaches = $pdo->query("
    SELECT c.coach_id, c.document, c.photo, u.firstname, u.middlename, u.lastname, u.username, u.email
    FROM coaches c
    JOIN users u ON c.user_id = u.user_id
    WHERE u.status = 'pending' AND c.status = 'pending'
    ORDER BY c.coach_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ======================== DOCUMENT PATH FUNCTION ========================
function getDocumentUrl($documentPath, $role) {
    // If no document, return placeholder
    if (!$documentPath || empty(trim($documentPath))) {
        return 'assets/no-image.png';
    }

    // Get the base URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = "$protocol://$host/Basketball/";

    // Extract just the filename with null safety
    $filename = $documentPath ? basename($documentPath) : '';
    
    // Build the correct web URL
    if ($role === 'player') {
        $webUrl = $baseUrl . 'uploads/documents/players/' . $filename;
    } else {
        $webUrl = $baseUrl . 'uploads/documents/coaches/' . $filename;
    }

    // Verify the file actually exists on server
    $folder = ($role === 'player') ? 'players' : 'coaches';
    $serverPath = __DIR__ . '/../uploads/documents/' . $folder . '/' . $filename;
    $fileExists = $filename && file_exists($serverPath);

    if (!$fileExists) {
        return 'assets/no-image.png';
    }

    return $webUrl;
}

// ======================== PHOTO PATH FUNCTION ========================
function getPhotoUrl($photoPath, $role) {
    // If no photo, return placeholder
    if (!$photoPath || empty(trim($photoPath))) {
        return 'assets/no-image.png';
    }

    // Get the base URL
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = "$protocol://$host/Basketball/";

    // Extract just the filename with null safety
    $filename = $photoPath ? basename($photoPath) : '';
    
    // Build the correct web URL
    if ($role === 'player') {
        $webUrl = $baseUrl . 'uploads/photos/players/' . $filename;
    } else {
        $webUrl = $baseUrl . 'uploads/photos/coaches/' . $filename;
    }

    // Verify the file actually exists on server
    $folder = ($role === 'player') ? 'players' : 'coaches';
    $serverPath = __DIR__ . '/../uploads/photos/' . $folder . '/' . $filename;
    $fileExists = $filename && file_exists($serverPath);

    if (!$fileExists) {
        return 'assets/no-image.png';
    }

    return $webUrl;
}

// ======================== SAFE BASENAME FUNCTION ========================
function safeBasename($path) {
    if (!$path || empty(trim($path))) {
        return '';
    }
    return basename($path);
}
?>

<!-- ======================== MAIN CONTENT ======================== -->
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">📝 User Approvals</h1>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-8">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 bg-green-100 text-green-800 rounded">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 bg-red-100 text-red-800 rounded">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Pending Players -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Pending Players (<?= count($pendingPlayers) ?>)</h2>
            <?php if (count($pendingPlayers) === 0): ?>
                <p class="text-gray-600">No pending players.</p>
            <?php else: ?>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2">Full Name</th>
                            <th class="px-4 py-2">Username</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">1x1 Photo</th>
                            <th class="px-4 py-2">Document</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingPlayers as $p):
                            $fullname = trim($p['firstname'] . ' ' . ($p['middlename'] ?? '') . ' ' . $p['lastname']);
                            $documentSrc = getDocumentUrl($p['document'], 'player');
                            $photoSrc = getPhotoUrl($p['photo'], 'player');
                            $documentFilename = safeBasename($p['document']);
                            $photoFilename = safeBasename($p['photo']);
                            $documentServerPath = $documentFilename ? __DIR__ . '/../uploads/documents/players/' . $documentFilename : '';
                            $photoServerPath = $photoFilename ? __DIR__ . '/../uploads/photos/players/' . $photoFilename : '';
                            $documentExists = $documentFilename && file_exists($documentServerPath);
                            $photoExists = $photoFilename && file_exists($photoServerPath);
                        ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($fullname) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($p['username']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($p['email']) ?></td>
                            <td class="px-4 py-2">
                                <div class="relative">
                                    <img src="<?= htmlspecialchars($photoSrc) ?>"
                                         alt="1x1 Photo"
                                         class="w-20 h-20 object-cover rounded-full border cursor-pointer bg-gray-100"
                                         onerror="this.onerror=null; this.src='assets/no-image.png'; this.classList.add('border-red-300');"
                                         onclick="openModal('<?= htmlspecialchars($photoSrc) ?>')" />
                                    <?php if (!$photoExists && $p['photo']): ?>
                                        <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded">Missing</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="relative">
                                    <img src="<?= htmlspecialchars($documentSrc) ?>"
                                         alt="Document"
                                         class="w-32 h-32 object-contain rounded border cursor-pointer bg-gray-100"
                                         onerror="this.onerror=null; this.src='assets/no-image.png'; this.classList.add('border-red-300');"
                                         onclick="openModal('<?= htmlspecialchars($documentSrc) ?>')" />
                                    <?php if (!$documentExists && $p['document']): ?>
                                        <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded">Missing</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="user_approvals.php?role=player&id=<?= $p['player_id'] ?>&action=approve" 
                                   class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" 
                                   onclick="return confirm('Approve <?= htmlspecialchars($fullname) ?> as player?');">Approve</a>
                                <a href="user_approvals.php?role=player&id=<?= $p['player_id'] ?>&action=reject" 
                                   class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700" 
                                   onclick="return confirm('Reject <?= htmlspecialchars($fullname) ?>? This will permanently delete the user and their data.');">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Pending Coaches -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Pending Coaches (<?= count($pendingCoaches) ?>)</h2>
            <?php if (count($pendingCoaches) === 0): ?>
                <p class="text-gray-600">No pending coaches.</p>
            <?php else: ?>
                <table class="w-full table-auto border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="px-4 py-2">Full Name</th>
                            <th class="px-4 py-2">Username</th>
                            <th class="px-4 py-2">Email</th>
                            <th class="px-4 py-2">1x1 Photo</th>
                            <th class="px-4 py-2">Document</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingCoaches as $c):
                            $fullname = trim($c['firstname'] . ' ' . ($c['middlename'] ?? '') . ' ' . $c['lastname']);
                            $documentSrc = getDocumentUrl($c['document'], 'coach');
                            $photoSrc = getPhotoUrl($c['photo'], 'coach');
                            $documentFilename = safeBasename($c['document']);
                            $photoFilename = safeBasename($c['photo']);
                            $documentServerPath = $documentFilename ? __DIR__ . '/../uploads/documents/coaches/' . $documentFilename : '';
                            $photoServerPath = $photoFilename ? __DIR__ . '/../uploads/photos/coaches/' . $photoFilename : '';
                            $documentExists = $documentFilename && file_exists($documentServerPath);
                            $photoExists = $photoFilename && file_exists($photoServerPath);
                        ?>
                        <tr class="border-b">
                            <td class="px-4 py-2"><?= htmlspecialchars($fullname) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($c['username']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($c['email']) ?></td>
                            <td class="px-4 py-2">
                                <div class="relative">
                                    <img src="<?= htmlspecialchars($photoSrc) ?>"
                                         alt="1x1 Photo"
                                         class="w-20 h-20 object-cover rounded-full border cursor-pointer bg-gray-100"
                                         onerror="this.onerror=null; this.src='assets/no-image.png'; this.classList.add('border-red-300');"
                                         onclick="openModal('<?= htmlspecialchars($photoSrc) ?>')" />
                                    <?php if (!$photoExists && $c['photo']): ?>
                                        <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded">Missing</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-2">
                                <div class="relative">
                                    <img src="<?= htmlspecialchars($documentSrc) ?>"
                                         alt="Document"
                                         class="w-32 h-32 object-contain rounded border cursor-pointer bg-gray-100"
                                         onerror="this.onerror=null; this.src='assets/no-image.png'; this.classList.add('border-red-300');"
                                         onclick="openModal('<?= htmlspecialchars($documentSrc) ?>')" />
                                    <?php if (!$documentExists && $c['document']): ?>
                                        <div class="absolute top-0 right-0 bg-red-500 text-white text-xs px-2 py-1 rounded">Missing</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-2 space-x-2">
                                <a href="user_approvals.php?role=coach&id=<?= $c['coach_id'] ?>&action=approve" 
                                   class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" 
                                   onclick="return confirm('Approve <?= htmlspecialchars($fullname) ?> as coach?');">Approve</a>
                                <a href="user_approvals.php?role=coach&id=<?= $c['coach_id'] ?>&action=reject" 
                                   class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700" 
                                   onclick="return confirm('Reject <?= htmlspecialchars($fullname) ?>? This will permanently delete the user and their data.');">Reject</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Lightbox Modal -->
        <div id="idModal" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center hidden z-50">
            <div class="relative bg-white rounded shadow-lg p-4 max-w-4xl w-full mx-4">
                <span id="modalClose" class="absolute top-2 right-3 cursor-pointer text-gray-700 text-2xl font-bold hover:text-gray-900">&times;</span>
                <img id="modalImg" src="" alt="Document" class="w-full h-auto rounded max-h-96 object-contain" onerror="this.src='assets/no-image.png'">
            </div>
        </div>

        <script>
        function openModal(imgPath) {
            const modal = document.getElementById('idModal');
            const modalImg = document.getElementById('modalImg');
            modalImg.src = imgPath;
            modal.classList.remove('hidden');
        }

        document.getElementById('modalClose').onclick = function() {
            document.getElementById('idModal').classList.add('hidden');
        }

        document.getElementById('idModal').onclick = function(e) {
            if(e.target.id === 'idModal') {
                this.classList.add('hidden');
            }
        }

        // Enhanced error handling for images
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('img').forEach(img => {
                img.addEventListener('error', function() {
                    this.src = 'assets/no-image.png';
                    this.classList.add('border-red-300');
                });
                
                // Check if image loaded successfully
                img.addEventListener('load', function() {
                    if (this.naturalWidth === 0 || this.naturalHeight === 0) {
                        this.src = 'assets/no-image.png';
                        this.classList.add('border-red-300');
                    }
                });
            });
        });
        </script>
    </main>
</div>

<?php include 'includes/footer.php'; ?>