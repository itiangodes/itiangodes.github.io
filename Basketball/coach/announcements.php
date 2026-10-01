<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<?php include 'includes/db.php'; ?>

<!-- Announcements Section -->
<section id="announcements" class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center border-b pb-2">
            <i class="fas fa-bullhorn text-yellow-600 mr-3"></i>
            Announcements
        </h2>

        <!-- Success Messages -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="mb-6 p-4 rounded-lg <?php 
                echo $_GET['msg'] === 'created' ? 'bg-green-100 text-green-800' : 
                     ($_GET['msg'] === 'updated' ? 'bg-blue-100 text-blue-800' : 
                     'bg-red-100 text-red-800'); 
            ?>">
                <?php
                    $messages = [
                        'created' => '✅ Announcement created successfully!',
                        'updated' => '✅ Announcement updated successfully!',
                        'deleted' => '✅ Announcement deleted successfully!'
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
                
                // Debug: Check if announcements are being fetched
                if (empty($announcements)) {
                    echo '<div class="text-center py-8 text-gray-500">No announcements found in database.</div>';
                }
                
            } catch (PDOException $e) {
                $announcements = [];
                echo '<div class="bg-red-100 text-red-800 p-4 rounded">Error loading announcements: ' . htmlspecialchars($e->getMessage()) . '</div>';
                error_log("Database error in announcements: " . $e->getMessage());
            }

            if (!empty($announcements)):
                foreach ($announcements as $a):
                    // ✅ Improved image path handling
                    $photoPath = '';
                    if (!empty($a['photo'])) {
                        // Check multiple possible locations for the image
                        $possiblePaths = [
                            $a['photo'],
                            '../' . $a['photo'],
                            'uploads/announcements/' . basename($a['photo']),
                            '../uploads/announcements/' . basename($a['photo']),
                            'admin/uploads/announcements/' . basename($a['photo']),
                            '../admin/uploads/announcements/' . basename($a['photo'])
                        ];
                        
                        foreach ($possiblePaths as $path) {
                            if (file_exists($path) && is_file($path)) {
                                $photoPath = $path;
                                break;
                            }
                        }
                        
                        // If no file found, check if it's a relative path from admin
                        if (empty($photoPath) && strpos($a['photo'], 'uploads/announcements/') !== false) {
                            $relativePath = str_replace('uploads/announcements/', '../uploads/announcements/', $a['photo']);
                            if (file_exists($relativePath)) {
                                $photoPath = $relativePath;
                            }
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

                    <!-- 🖼️ Photo preview -->
                    <?php if (!empty($photoPath) && file_exists($photoPath)): ?>
                        <div class="relative group rounded-lg overflow-hidden mb-4">
                            <img src="<?= htmlspecialchars($photoPath); ?>"
                                alt="Announcement Photo"
                                class="rounded-lg shadow-md w-full max-h-80 object-cover cursor-pointer transition-transform duration-200 group-hover:scale-[1.02]"
                                onclick="openModal('<?= htmlspecialchars($photoPath); ?>')">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 transition bg-black bg-opacity-50 px-3 py-1 rounded-full text-sm">
                                    🔍 Click to enlarge
                                </span>
                            </div>
                        </div>
                    <?php elseif (!empty($a['photo'])): ?>
                        <!-- Show broken image placeholder if file exists in DB but not on server -->
                        <div class="bg-gray-100 rounded-lg p-4 text-center text-gray-500 text-sm mb-4">
                            <i class="fas fa-image text-gray-300 text-2xl mb-2 block"></i>
                            Photo unavailable (file not found)
                        </div>
                    <?php endif; ?>

                    <!-- Admin Actions (only show if coach is admin) -->
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <div class="flex space-x-2 mt-4 pt-4 border-t border-gray-100">
                            <a href="admin/announcements.php?edit=<?= $a['id'] ?>" 
                               class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                                ✏️ Edit
                            </a>
                            <a href="admin/announcements.php?delete=<?= $a['id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this announcement?')"
                               class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition">
                                🗑️ Delete
                            </a>
                        </div>
                    <?php endif; ?>
                </article>
            <?php
                endforeach;
            else:
            ?>
                <div class="text-center py-10">
                    <div class="text-6xl mb-4">📢</div>
                    <p class="text-gray-500 text-lg">No announcements yet.</p>
                    <p class="text-gray-400 text-sm mt-2">Check back later for updates from your admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 🖼️ Modal for full-size image preview -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
    <div class="relative max-w-4xl w-full max-h-full">
        <button onclick="closeModal()" 
            class="absolute -top-12 right-0 text-white text-3xl font-bold hover:text-yellow-400 transition z-10 bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center">
            &times;
        </button>
        <div class="bg-white rounded-lg overflow-hidden shadow-2xl">
            <img id="modalImage" src="" 
                alt="Preview" 
                class="w-full h-auto max-h-[80vh] object-contain">
        </div>
        <p class="text-white text-center mt-2 text-sm">Click outside to close</p>
    </div>
</div>

<script>
    // 🔍 Modal image preview logic
    function openModal(src) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImage");
        modalImg.src = src;
        modal.classList.remove("hidden");
        document.body.classList.add("overflow-hidden");
    }

    function closeModal() {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("modalImage");
        modal.classList.add("hidden");
        modalImg.src = "";
        document.body.classList.remove("overflow-hidden");
    }

    // Close modal on background click
    document.getElementById("imageModal").addEventListener("click", (e) => {
        if (e.target.id === "imageModal") closeModal();
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>

<?php include 'includes/footer.php'; ?>