<?php
include 'includes/auth.php';
include 'includes/db.php';

// Fetch settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    header("Location: settings.php?updated=true");
    exit();
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm border-b px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-800">System Settings</h1>
    </header>

    <main class="flex-1 overflow-y-auto p-6 max-w-3xl mx-auto">
        <?php if (isset($_GET['updated'])): ?>
            <div class="p-4 mb-4 bg-green-100 text-green-800 rounded">
                Settings updated successfully.
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6 bg-white p-6 rounded shadow">
            <div>
                <label class="block text-sm font-medium text-gray-700">League Name</label>
                <input type="text" name="league_name" value="<?= htmlspecialchars($settings['league_name']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Season / Year</label>
                <input type="text" name="season_year" value="<?= htmlspecialchars($settings['season_year']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Admin Email</label>
                <input type="email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email']) ?>" class="w-full mt-1 p-2 border rounded" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Registration Status</label>
                <select name="registration_status" class="w-full mt-1 p-2 border rounded">
                    <option value="open" <?= $settings['registration_status'] === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= $settings['registration_status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>

            <div class="pt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </main>
</div>

<?php include 'includes/footer.php'; ?>
