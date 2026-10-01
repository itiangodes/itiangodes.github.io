<?php
include 'includes/auth.php';
include 'includes/db.php';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $media_files = [];

    // Handle multiple media uploads
    if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $allowed_video_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/quicktime'];
        $max_size = 50 * 1024 * 1024; // 50MB

        $upload_dir = 'uploads/announcements/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Process multiple files
        $file_count = count($_FILES['media']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['media']['type'][$i];
                $file_size = $_FILES['media']['size'][$i];
                $file_name = $_FILES['media']['name'][$i];
                
                // Check if file type is allowed
                $is_image = in_array($file_type, $allowed_image_types);
                $is_video = in_array($file_type, $allowed_video_types);
                
                if (($is_image || $is_video) && $file_size <= $max_size) {
                    
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Generate unique filename
                    if ($is_image) {
                        $file_name = uniqid('announcement_img_') . '.' . $file_ext;
                    } else {
                        $file_name = uniqid('announcement_vid_') . '.' . $file_ext;
                    }
                    
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['media']['tmp_name'][$i], $file_path)) {
                        $media_files[] = [
                            'path' => $file_path,
                            'type' => $is_image ? 'image' : 'video'
                        ];
                    }
                }
            }
        }
    }

    if (!empty($title) && !empty($content)) {
        // Serialize media files array for storage
        $media_json = !empty($media_files) ? json_encode($media_files) : null;
        
        $stmt = $pdo->prepare("INSERT INTO announcements (title, content, media) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $media_json]);
        header("Location: announcements.php?msg=created");
        exit();
    }
}

// Handle Edit - FIXED: Properly preserve existing media
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Get current media from database
    $stmt = $pdo->prepare("SELECT media FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $current_announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Start with existing media
    $media_files = [];
    if ($current_announcement && $current_announcement['media']) {
        $decoded_media = json_decode($current_announcement['media'], true);
        if (is_array($decoded_media)) {
            $media_files = $decoded_media;
        }
    }
    
    // Handle media removal - only remove if explicitly unchecked
    if (isset($_POST['existing_media']) && is_array($_POST['existing_media'])) {
        // User checked some checkboxes - only keep the checked ones
        $checked_media = [];
        foreach ($_POST['existing_media'] as $media_json) {
            $media_data = json_decode($media_json, true);
            if (is_array($media_data) && isset($media_data['path']) && isset($media_data['type'])) {
                $checked_media[] = $media_data;
            }
        }
        $media_files = $checked_media;
    } else {
        // If no checkboxes submitted at all, assume user wants to keep all existing media
        // This handles the case where the form is submitted without touching the checkboxes
        // Only clear if we're explicitly in a scenario where checkboxes should be present
        if (!empty($media_files)) {
            // This means there were existing media files but no checkboxes were submitted
            // This shouldn't happen normally, but as a safety measure, we'll keep existing media
            // unless we're sure the user intended to remove them
        }
    }

    // Handle new media uploads
    if (isset($_FILES['media']) && !empty($_FILES['media']['name'][0])) {
        $allowed_image_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $allowed_video_types = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv', 'video/quicktime'];
        $max_size = 50 * 1024 * 1024; // 50MB

        $upload_dir = 'uploads/announcements/';
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Process multiple files
        $file_count = count($_FILES['media']['name']);
        
        for ($i = 0; $i < $file_count; $i++) {
            if ($_FILES['media']['error'][$i] === UPLOAD_ERR_OK) {
                $file_type = $_FILES['media']['type'][$i];
                $file_size = $_FILES['media']['size'][$i];
                $file_name = $_FILES['media']['name'][$i];
                
                // Check if file type is allowed
                $is_image = in_array($file_type, $allowed_image_types);
                $is_video = in_array($file_type, $allowed_video_types);
                
                if (($is_image || $is_video) && $file_size <= $max_size) {
                    
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                    
                    // Generate unique filename
                    if ($is_image) {
                        $file_name = uniqid('announcement_img_') . '.' . $file_ext;
                    } else {
                        $file_name = uniqid('announcement_vid_') . '.' . $file_ext;
                    }
                    
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($_FILES['media']['tmp_name'][$i], $file_path)) {
                        $media_files[] = [
                            'path' => $file_path,
                            'type' => $is_image ? 'image' : 'video'
                        ];
                    }
                }
            }
        }
    }

    // Serialize media files array for storage
    $media_json = !empty($media_files) ? json_encode($media_files) : null;
    
    $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, media = ? WHERE id = ?");
    $stmt->execute([$title, $content, $media_json, $id]);
    header("Location: announcements.php?msg=updated");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Get media files before deleting
    $stmt = $pdo->prepare("SELECT media FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete media files if they exist
    if ($announcement && $announcement['media']) {
        $media_files = json_decode($announcement['media'], true);
        if (is_array($media_files)) {
            foreach ($media_files as $media) {
                if (is_array($media) && isset($media['path']) && file_exists($media['path'])) {
                    unlink($media['path']);
                }
            }
        }
    }

    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: announcements.php?msg=deleted");
    exit();
}

// Handle individual media deletion
if (isset($_GET['delete_media'])) {
    $announcement_id = $_GET['announcement_id'];
    $media_path = $_GET['delete_media'];
    
    // Get current media files
    $stmt = $pdo->prepare("SELECT media FROM announcements WHERE id = ?");
    $stmt->execute([$announcement_id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($announcement && $announcement['media']) {
        $media_files = json_decode($announcement['media'], true);
        if (is_array($media_files)) {
            $updated_media = [];
            
            foreach ($media_files as $media) {
                // Check if media is properly structured and not the one to delete
                if (is_array($media) && isset($media['path']) && $media['path'] !== $media_path) {
                    $updated_media[] = $media;
                } elseif (is_array($media) && isset($media['path']) && $media['path'] === $media_path) {
                    // Delete the file
                    if (file_exists($media_path)) {
                        unlink($media_path);
                    }
                }
            }
            
            // Update the database
            $media_json = !empty($updated_media) ? json_encode($updated_media) : null;
            $stmt = $pdo->prepare("UPDATE announcements SET media = ? WHERE id = ?");
            $stmt->execute([$media_json, $announcement_id]);
        }
    }
    
    header("Location: announcements.php?msg=media_deleted");
    exit();
}

// Fetch All Announcements
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Decode media for each announcement and ensure it's properly formatted
foreach ($announcements as &$announcement) {
    if ($announcement['media']) {
        $decoded_media = json_decode($announcement['media'], true);
        // Ensure we have a proper array structure
        if (is_array($decoded_media)) {
            // Filter out any invalid entries
            $announcement['media'] = array_filter($decoded_media, function($media) {
                return is_array($media) && isset($media['path']) && isset($media['type']);
            });
        } else {
            $announcement['media'] = [];
        }
    } else {
        $announcement['media'] = [];
    }
}
unset($announcement); // break the reference
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<style>
    .photo-preview, .video-preview {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        margin-top: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .video-preview {
        width: 100%;
        height: auto;
    }
    .file-upload-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        cursor: pointer;
    }
    .file-upload-input {
        position: absolute;
        left: -9999px;
    }
    .file-upload-label {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        background: #f3f4f6;
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-upload-label:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .file-upload-label svg {
        margin-right: 8px;
    }
    .announcement-media {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 12px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .media-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 10px;
        margin: 10px 0;
    }
    .media-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        background: #f5f5f5;
    }
    .media-item img, .media-item video {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }
    .delete-media {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(255,0,0,0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .existing-media-item {
        display: inline-flex;
        align-items: center;
        background: #f3f4f6;
        padding: 5px 10px;
        border-radius: 20px;
        margin: 2px 5px 2px 0;
        font-size: 14px;
    }
    .existing-media-item input {
        margin-right: 5px;
    }
    .no-media {
        color: #6b7280;
        font-style: italic;
    }
    .media-section {
        background: #f9fafb;
        padding: 15px;
        border-radius: 8px;
        margin: 10px 0;
    }
    .media-info {
        font-size: 12px;
        color: #6b7280;
        margin-top: 5px;
    }
</style>

<!-- Main content -->
<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">📢 Announcements</h1>
    </header>

    <main class="flex-1 overflow-y-auto p-6 space-y-8">
        <!-- Status Message -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="p-4 bg-green-100 text-green-800 rounded">
                <?php
                    $messages = [
                        'created' => 'Announcement created successfully.',
                        'updated' => 'Announcement updated successfully.',
                        'deleted' => 'Announcement deleted successfully.',
                        'media_deleted' => 'Media file deleted successfully.'
                    ];
                    echo $messages[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>

        <!-- Create New Announcement -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Create New Announcement</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4" id="createForm">
                <input type="hidden" name="create" value="1">

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Title</label>
                    <input type="text" name="title" class="w-full p-2 border rounded focus:outline-none focus:border-blue-500" required>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Content</label>
                    <textarea name="content" rows="4" class="w-full p-2 border rounded focus:outline-none focus:border-blue-500" required></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Media Files (Optional - Multiple Images/Videos)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="media[]" id="mediaInput" class="file-upload-input" accept="image/*,video/*" multiple onchange="previewMedia(event, 'preview')">
                        <label for="mediaInput" class="file-upload-label">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                            </svg>
                            <span>Click to upload photos/videos (Max 50MB each)</span>
                        </label>
                    </div>
                    <div id="preview" class="media-gallery"></div>
                    <p class="text-sm text-gray-500 mt-2">Accepted formats: JPG, PNG, GIF, MP4, AVI, MOV, WMV, FLV</p>
                </div>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    📤 Post Announcement
                </button>
            </form>
        </div>

        <!-- Announcements List -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">All Announcements</h2>
            <div class="space-y-4">
                <?php if (count($announcements) === 0): ?>
                    <p class="text-gray-600">No announcements yet.</p>
                <?php endif; ?>
                <?php foreach ($announcements as $a): ?>
                    <div class="border-b pb-4 mb-4">
                        <form method="POST" enctype="multipart/form-data" class="space-y-2" id="editForm<?= $a['id'] ?>">
                            <input type="hidden" name="edit" value="1">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Title</label>
                                <input type="text" name="title" class="text-xl font-bold w-full border-b focus:outline-none focus:border-blue-500 p-2" value="<?= htmlspecialchars($a['title']) ?>">
                            </div>

                            <div>
                                <label class="block text-gray-700 font-medium mb-1">Content</label>
                                <textarea name="content" class="w-full border p-2 rounded focus:outline-none focus:border-blue-500" rows="3"><?= htmlspecialchars($a['content']) ?></textarea>
                            </div>

                            <?php if (!empty($a['media']) && is_array($a['media'])): ?>
                                <div class="media-section">
                                    <label class="block text-gray-700 font-medium mb-1">Existing Media</label>
                                    <div class="media-gallery">
                                        <?php foreach ($a['media'] as $index => $media): ?>
                                            <?php if (is_array($media) && isset($media['path']) && isset($media['type'])): ?>
                                                <div class="media-item">
                                                    <?php if ($media['type'] === 'image'): ?>
                                                        <img src="<?= htmlspecialchars($media['path']) ?>" alt="Announcement media">
                                                    <?php else: ?>
                                                        <video controls>
                                                            <source src="<?= htmlspecialchars($media['path']) ?>" type="video/mp4">
                                                            Your browser does not support the video tag.
                                                        </video>
                                                    <?php endif; ?>
                                                    <a href="announcements.php?delete_media=<?= urlencode($media['path']) ?>&announcement_id=<?= $a['id'] ?>" 
                                                       onclick="return confirm('Are you sure you want to delete this media file?')" 
                                                       class="delete-media" title="Delete this media">×</a>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="block text-gray-700 font-medium mb-2">
                                            <input type="checkbox" id="toggleAll<?= $a['id'] ?>" onchange="toggleAllMedia(<?= $a['id'] ?>)" checked> 
                                            Keep Existing Media:
                                        </label>
                                        <div class="media-info">Uncheck to remove media files when saving</div>
                                        <div id="mediaCheckboxes<?= $a['id'] ?>">
                                            <?php foreach ($a['media'] as $media): ?>
                                                <?php if (is_array($media) && isset($media['path']) && isset($media['type'])): ?>
                                                    <label class="existing-media-item">
                                                        <input type="checkbox" name="existing_media[]" value='<?= htmlspecialchars(json_encode($media)) ?>' checked class="media-checkbox">
                                                        <?= basename($media['path']) ?> (<?= $media['type'] ?>)
                                                    </label>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="no-media">No media files attached</div>
                            <?php endif; ?>

                            <div>
                                <label class="block text-gray-700 font-medium mb-2">Add More Media (Optional)</label>
                                <div class="file-upload-wrapper">
                                    <input type="file" name="media[]" id="mediaEdit<?= $a['id'] ?>" class="file-upload-input" accept="image/*,video/*" multiple onchange="previewMedia(event, 'previewEdit<?= $a['id'] ?>')">
                                    <label for="mediaEdit<?= $a['id'] ?>" class="file-upload-label">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                                        </svg>
                                        <span>Add more photos/videos</span>
                                    </label>
                                </div>
                                <div id="previewEdit<?= $a['id'] ?>" class="media-gallery"></div>
                            </div>

                            <div class="text-sm text-gray-500">
                                📅 Posted on <?= date('F j, Y, g:i a', strtotime($a['created_at'])) ?>
                            </div>

                            <div class="space-x-2 mt-2">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">💾 Save</button>
                                <a href="announcements.php?delete=<?= $a['id'] ?>" onclick="return confirm('Are you sure you want to delete this announcement?')" class="inline-block bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">🗑️ Delete</a>
                            </div>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<script>
    function previewMedia(event, previewId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        preview.innerHTML = '';

        if (input.files) {
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const mediaElement = file.type.startsWith('video/') 
                        ? `<video controls class="video-preview"><source src="${e.target.result}" type="${file.type}">Your browser does not support the video tag.</video>`
                        : `<img src="${e.target.result}" class="photo-preview" alt="Preview">`;
                    
                    const mediaItem = document.createElement('div');
                    mediaItem.className = 'media-item';
                    mediaItem.innerHTML = mediaElement;
                    preview.appendChild(mediaItem);
                }

                reader.readAsDataURL(file);
            });
        }
    }

    function toggleAllMedia(announcementId) {
        const toggleAll = document.getElementById('toggleAll' + announcementId);
        const checkboxes = document.querySelectorAll('#mediaCheckboxes' + announcementId + ' .media-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = toggleAll.checked;
        });
    }

    // Ensure form submits properly with checkboxes
    document.querySelectorAll('form[id^="editForm"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            // This ensures the form will submit even if no checkboxes are checked
            // The server-side logic will handle the media preservation correctly
        });
    });
</script>

<?php include 'includes/footer.php'; ?>