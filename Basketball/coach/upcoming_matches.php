<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Upcoming Matches -->
            <section id="matches" class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-red-600 mr-2"></i>Upcoming Matches
                </h2>
                <div class="space-y-4">
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-500">June 15 • 2:00 PM</span>
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">SEMIFINALS</span>
                        </div>
                        <div class="grid grid-cols-3 items-center text-center">
                            <div>
                                <p class="font-bold text-gray-900">Punta I</p>
                                <p class="text-sm text-gray-600">Warriors</p>
                            </div>
                            <div>
                                <p class="text-gray-400 font-medium">VS</p>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">Amaya</p>
                                <p class="text-sm text-gray-600">Titans</p>
                            </div>
                        </div>
                        <div class="mt-3 flex space-x-2">
                            <button class="flex-1 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">
                                Game Plan
                            </button>
                            <button class="flex-1 py-2 bg-gray-600 text-white rounded-lg text-sm hover:bg-gray-700 transition">
                                Stats
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            

<?php include 'includes/footer.php'; ?>