<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<?php include 'includes/db.php'; ?>

<!-- Announcements Section -->
<section id="announcements" class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center border-b pb-2">
            <i class="fas fa-bullhorn text-yellow-600 mr-3"></i>
            Community Announcements
        </h2>

        <!-- Success Messages -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-lg <?php 
                echo $_GET['msg'] === 'created' ? 'bg-green-100 text-green-800' : 
                     ($_GET['msg'] === 'updated' ? 'bg-blue-100 text-blue-800' : 
                     ($_GET['msg'] === 'deleted' ? 'bg-red-100 text-red-800' :
                     'bg-green-100 text-green-800')); 
            ?>">
                <?php
                    $messages = [
                        'created' => '✅ Announcement created successfully!',
                        'updated' => '✅ Announcement updated successfully!',
                        'deleted' => '✅ Announcement deleted successfully!',
                        'media_deleted' => '✅ Media file deleted successfully!'
                    ];
                    echo $messages[$_GET['msg']] ?? 'Operation completed successfully.';
                ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <?php
            // ✅ Fetch announcements from database (newest first)
            try {
                $stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
                $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (PDOException $e) {
                $announcements = [];
                echo '<div class="bg-red-100 text-red-800 p-4 rounded">Error loading announcements.</div>';
            }

            if (!empty($announcements)):
                foreach ($announcements as $a):
                    // ✅ Handle multiple media files from JSON
                    $media_files = [];
                    if (!empty($a['media'])) {
                        $decoded_media = json_decode($a['media'], true);
                        if (is_array($decoded_media)) {
                            $media_files = array_filter($decoded_media, function($media) {
                                return is_array($media) && isset($media['path']) && isset($media['type']);
                            });
                        }
                    }
            ?>
                <article class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5">
                    <header class="mb-3">
                        <h3 class="font-semibold text-gray-900 text-lg"><?= htmlspecialchars($a['title']); ?></h3>
                        <p class="text-xs text-gray-500">
                            📅 <?= date("F j, Y g:i A", strtotime($a['created_at'])); ?>
                        </p>
                    </header>

                    <p class="text-gray-700 text-sm mb-4 leading-relaxed">
                        <?= nl2br(htmlspecialchars($a['content'])); ?>
                    </p>

                    <!-- 🖼️ Multiple Media Files (Images & Videos) -->
                    <?php if (!empty($media_files)): ?>
                        <div class="media-gallery mb-4">
                            <?php foreach ($media_files as $media): ?>
                                <?php if (is_array($media) && isset($media['path']) && isset($media['type'])): ?>
                                    <?php
                                    // ✅ Improved media path handling
                                    $mediaPath = '';
                                    $possiblePaths = [
                                        $media['path'],
                                        '../' . $media['path'],
                                        'uploads/announcements/' . basename($media['path']),
                                        '../uploads/announcements/' . basename($media['path']),
                                        'admin/uploads/announcements/' . basename($media['path']),
                                        '../admin/uploads/announcements/' . basename($media['path'])
                                    ];
                                    
                                    foreach ($possiblePaths as $path) {
                                        if (file_exists($path) && is_file($path)) {
                                            $mediaPath = $path;
                                            break;
                                        }
                                    }
                                    
                                    // If no file found, check if it's a relative path from admin
                                    if (empty($mediaPath) && strpos($media['path'], 'uploads/announcements/') !== false) {
                                        $relativePath = str_replace('uploads/announcements/', '../uploads/announcements/', $media['path']);
                                        if (file_exists($relativePath)) {
                                            $mediaPath = $relativePath;
                                        }
                                    }
                                    ?>
                                    
                                    <?php if (!empty($mediaPath) && file_exists($mediaPath)): ?>
                                        <div class="media-item relative group rounded-lg overflow-hidden mb-3">
                                            <?php if ($media['type'] === 'image'): ?>
                                                <img src="<?= htmlspecialchars($mediaPath); ?>"
                                                    alt="Announcement Media"
                                                    class="rounded-lg shadow-md w-full max-h-80 object-cover cursor-pointer transition-transform duration-200 group-hover:scale-[1.02]"
                                                    onclick="openModal('<?= htmlspecialchars($mediaPath); ?>', 'image')">
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition flex items-center justify-center">
                                                    <span class="text-white opacity-0 group-hover:opacity-100 transition bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                                                        🔍 Click to enlarge
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <video controls 
                                                    class="rounded-lg shadow-md w-full max-h-80 object-cover cursor-pointer"
                                                    onclick="openModal('<?= htmlspecialchars($mediaPath); ?>', 'video')">
                                                    <source src="<?= htmlspecialchars($mediaPath); ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition flex items-center justify-center">
                                                    <span class="text-white opacity-0 group-hover:opacity-100 transition bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                                                        ▶️ Click to play
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Show broken media placeholder if file exists in DB but not on server -->
                                        <div class="bg-gray-100 rounded-lg p-4 text-center text-gray-500 text-sm mb-3">
                                            <i class="fas fa-file text-gray-300 text-2xl mb-2 block"></i>
                                            <?= ucfirst($media['type']) ?> unavailable (file not found)
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Community View - No Edit/Delete Buttons -->
                    <div class="text-xs text-gray-400 mt-2 pt-2 border-t border-gray-100">
                        📢 Official League Announcement
                    </div>
                </article>
            <?php
                endforeach;
            else:
            ?>
                <div class="text-center py-10">
                    <div class="text-6xl mb-4">📢</div>
                    <p class="text-gray-500 text-lg">No announcements yet.</p>
                    <p class="text-gray-400 text-sm mt-2">Check back later for updates from the league.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 🖼️ Modal for full-size media preview -->
<div id="mediaModal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
    <div class="relative max-w-4xl w-full max-h-full">
        <button onclick="closeModal()" 
            class="absolute -top-12 right-0 text-white text-3xl font-bold hover:text-yellow-400 transition z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
            &times;
        </button>
        <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
            <img id="modalImage" src="" 
                alt="Preview" 
                class="w-full h-auto max-h-[80vh] object-contain hidden">
            <video id="modalVideo" controls
                class="w-full h-auto max-h-[80vh] object-contain hidden">
                <source src="" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        <p class="text-white text-center mt-2 text-sm">Click outside to close</p>
    </div>
</div>

<style>
    .media-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
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
        height: 200px;
        object-fit: cover;
    }
</style>

<script>
    // 🔍 Modal media preview logic
    function openModal(src, type) {
        const modal = document.getElementById("mediaModal");
        const modalImg = document.getElementById("modalImage");
        const modalVideo = document.getElementById("modalVideo");
        
        if (type === 'image') {
            modalImg.src = src;
            modalImg.classList.remove("hidden");
            modalVideo.classList.add("hidden");
            modalVideo.pause();
        } else {
            modalVideo.querySelector('source').src = src;
            modalVideo.load();
            modalVideo.classList.remove("hidden");
            modalImg.classList.add("hidden");
        }
        
        modal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeModal() {
        const modal = document.getElementById("mediaModal");
        const modalImg = document.getElementById("modalImage");
        const modalVideo = document.getElementById("modalVideo");
        
        modal.classList.add("hidden");
        modalImg.src = "";
        modalVideo.querySelector('source').src = "";
        modalVideo.pause();
        document.body.classList.remove("overflow-hidden");
    }

    // Close modal on background click
    document.getElementById("mediaModal").addEventListener("click", (e) => {
        if (e.target.id === "mediaModal") closeModal();
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>

<?php include 'includes/footer.php'; ?>