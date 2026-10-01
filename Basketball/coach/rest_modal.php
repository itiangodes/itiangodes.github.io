<!-- Add Rest Day Modal -->
<div id="restDayModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-auto max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center p-6 border-b border-gray-200 flex-shrink-0">
      <h3 id="restDayModalTitle" class="text-xl font-bold text-gray-800">Add Rest Day</h3>
      <button type="button" onclick="closeRestDayModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold w-8 h-8 flex items-center justify-center">×</button>
    </div>

    <!-- Date Selected Section -->
    <div class="px-6 py-3 bg-green-50 border-b border-green-100">
      <div class="flex items-center justify-center text-green-700 font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span id="restDayModalDate">Select a date below</span>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-6 py-4">
      <form id="restDayForm" method="POST" class="space-y-4">
        <input type="hidden" name="saveactivity" value="1">
        <input type="hidden" name="week_id" id="restWeekId">
        <input type="hidden" name="day_name" id="restDayName">
        <input type="hidden" name="type" value="Rest">
        <input type="hidden" name="title" value="Rest Day">
        <input type="hidden" name="clearexisting" value="1">

        <!-- Date selection -->
        <div>
          <label class="font-semibold text-gray-700 block mb-2">Date</label>
          <input type="date" id="restDate" name="activitydate" 
                 class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 text-base" required>
        </div>

        <!-- Description -->
        <div>
          <label class="font-semibold text-gray-700 block mb-2">Notes (Optional)</label>
          <textarea id="restDescription" name="description" 
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-green-200 focus:border-green-500 text-base" 
                    rows="3" 
                    placeholder="Recovery and muscle relaxation day.">Recovery and muscle relaxation day.</textarea>
        </div>

        <!-- Warning Message -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-700">
                <strong>Warning:</strong> If this date already has activities (workouts or drills), they will be removed and replaced with this rest day.
              </p>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Fixed Footer -->
    <div class="border-t border-gray-200 p-6 flex-shrink-0">
      <div class="flex flex-col sm:flex-row gap-3 justify-end">
        <button type="button" onclick="closeRestDayModal()" 
                class="w-full sm:w-auto px-6 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold text-gray-800 transition-colors text-base">
          Cancel
        </button>
        <button type="button" onclick="saveRestDay()" 
                class="w-full sm:w-auto px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold transition-colors text-base">
          Add Rest Day
        </button>
      </div>
    </div>
  </div>
</div>