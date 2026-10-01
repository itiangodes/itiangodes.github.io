<?php include 'includes/player_header.php'; ?>
<?php include 'includes/player_nav.php'; ?>

<!-- Upcoming Matches Section -->
<section id="upcoming-matches" class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center border-b pb-2">
            <i class="fas fa-calendar-alt text-green-600 mr-3"></i>
            Upcoming Matches
        </h2>

        <div class="space-y-6">
            <!-- Match 1 -->
            <article class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5">
                <header class="mb-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-clock mr-2"></i>
                            Tomorrow • 2:00 PM
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-trophy mr-1"></i> SEMIFINALS
                        </span>
                    </div>
                </header>

                <div class="grid grid-cols-3 items-center text-center mb-4">
                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-lg">Punta I</p>
                        <p class="text-sm text-gray-600">Warriors</p>
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                            <span class="font-bold text-blue-600">WI</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="text-gray-400 font-medium text-xl">VS</p>
                        <div class="text-xs text-gray-500 bg-gray-100 rounded-lg py-1 px-2">
                            Best of 3
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-lg">Amaya</p>
                        <p class="text-sm text-gray-600">Titans</p>
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                            <span class="font-bold text-red-600">AT</span>
                        </div>
                    </div>
                </div>

                <footer class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        Municipal Gym - Court 1
                    </div>
                    <div class="flex justify-center space-x-4 mt-2 text-xs text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-users mr-1"></i> 5v5
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-basketball-ball mr-1"></i> Basketball
                        </span>
                    </div>
                </footer>
            </article>

            <!-- Match 2 -->
            <article class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5">
                <header class="mb-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center text-sm text-gray-500">
                            <i class="fas fa-clock mr-2"></i>
                            June 20 • 4:00 PM
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <i class="fas fa-crown mr-1"></i> FINALS
                        </span>
                    </div>
                </header>

                <div class="grid grid-cols-3 items-center text-center mb-4">
                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-lg">Punta I</p>
                        <p class="text-sm text-gray-600">Warriors</p>
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
                            <span class="font-bold text-blue-600">WI</span>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="text-gray-400 font-medium text-xl">VS</p>
                        <div class="text-xs text-gray-500 bg-gray-100 rounded-lg py-1 px-2">
                            Championship
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <p class="font-bold text-gray-900 text-lg">Bunga</p>
                        <p class="text-sm text-gray-600">Bulls</p>
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto">
                            <span class="font-bold text-yellow-600">BB</span>
                        </div>
                    </div>
                </div>

                <footer class="pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-center text-sm text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                        Municipal Gym - Court 1
                    </div>
                    <div class="flex justify-center space-x-4 mt-2 text-xs text-gray-500">
                        <span class="flex items-center">
                            <i class="fas fa-users mr-1"></i> 5v5
                        </span>
                        <span class="flex items-center">
                            <i class="fas fa-basketball-ball mr-1"></i> Basketball
                        </span>
                    </div>
                </footer>
            </article>

            <!-- Empty State (if no matches) -->
            <!--
            <article class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 text-center">
                <div class="py-8">
                    <div class="text-6xl mb-4 text-gray-300">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <p class="text-gray-500 text-lg">No upcoming matches</p>
                    <p class="text-gray-400 text-sm mt-2">Check back later for scheduled games</p>
                </div>
            </article>
            -->
        </div>
    </div>
</section>

<?php include 'includes/player_footer.php'; ?>