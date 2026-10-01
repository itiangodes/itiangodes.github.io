<!-- Rate Performance Modal -->
<div id="ratingSelectionModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-auto max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center p-6 border-b border-gray-200 flex-shrink-0">
      <h3 class="text-xl font-bold text-gray-800">Rate Performance</h3>
      <button type="button" onclick="closeRatingSelectionModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold w-8 h-8 flex items-center justify-center">×</button>
    </div>

    <!-- Info Banner -->
    <div class="px-6 py-3 bg-purple-50 border-b border-purple-100">
      <div class="flex items-center justify-center text-purple-700 font-medium text-sm">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>Select a day with activities to rate the player's performance</span>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-6 py-4">
      <div id="activityDaysList" class="space-y-3">
        <!-- Activity days will be loaded here by JavaScript -->
        <div class="text-center text-gray-500 py-8">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          <p class="text-lg font-medium">Loading activities...</p>
        </div>
      </div>
    </div>

    <!-- Fixed Footer -->
    <div class="border-t border-gray-200 p-6 flex-shrink-0">
      <div class="flex justify-end">
        <button type="button" onclick="closeRatingSelectionModal()" 
                class="px-6 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold text-gray-800 transition-colors text-base">
          Close
        </button>
      </div>
    </div>
  </div>
</div>