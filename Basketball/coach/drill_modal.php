<!-- Add Drill Modal -->
<div id="drillModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-auto max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center p-6 border-b border-gray-200 flex-shrink-0">
      <h3 id="drillModalTitle" class="text-xl font-bold text-gray-800">Add New Drill</h3>
      <button type="button" onclick="closeDrillModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold w-8 h-8 flex items-center justify-center">×</button>
    </div>

    <div class="px-6 py-3 bg-yellow-50 border-b border-yellow-100">
      <div class="flex items-center justify-center text-yellow-700 font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span id="drillModalDate">Selected Date</span>
      </div>
    </div>

    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-6">
      <form id="drillForm" method="POST" class="space-y-4">
        <!-- Mirror hidden fields pattern used by Workout -->
        <input type="hidden" name="saveactivity" value="1"> 
        <input type="hidden" name="weekid" id="drWeekId">
        <input type="hidden" name="dayname" id="drDayName">
        <input type="hidden" name="type" value="Drill">

        <!-- Date selection -->
        <div>
            <label class="font-semibold text-gray-700 block mb-2">Date</label>
            <input
                type="date"
                id="drillDate"
                name="activitydate"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base"
                required
            >
        </div>


        <!-- Category + subcategory (select drill) + info button -->
        <div>
          <label class="font-semibold text-gray-700 block mb-2">Select Drill Category</label>
          <select id="drillCategory" onchange="updateDrillSubcategories()" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" required>
            <option value="">Choose a drill category</option>
            <option value="shooting">Shooting Drill</option>
            <option value="passing">Passing Drill</option>
            <option value="dribbling">Dribbling Drill</option>
            <option value="defense">Defense Drill</option>
          </select>
        </div>

        <div id="drillSubcategoryGroup" class="hidden">
          <label class="font-semibold text-gray-700 block mb-2">Select Specific Drill</label>
          <div class="flex items-center gap-2">
            <button type="button" class="icon-btn flex-shrink-0 w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50" id="drillDetailsBtn" title="View drill details">
              <svg width="20" height="20" fill="none" aria-hidden="true">
                <circle cx="10" cy="10" r="9" stroke="#21808d" stroke-width="2"/>
                <text x="10" y="14" font-size="12" text-anchor="middle" fill="#21808d">i</text>
              </svg>
            </button>
            <select id="drillSubcategory" class="flex-1 border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" required>
              <option value="">Choose a specific drill</option>
            </select>
          </div>
        </div>

        <!-- Duration / Sets -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="font-semibold text-gray-700 block mb-2">Duration (minutes)</label>
            <input type="number" id="drillDuration" name="duration" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., 10" required>
          </div>
          <div>
            <label class="font-semibold text-gray-700 block mb-2">Number of Sets</label>
            <input type="number" id="drillSets" name="sets" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., 3" required>
          </div>
        </div>

        <!-- Location / Call Time -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="font-semibold text-gray-700 block mb-2">Location</label>
            <input type="text" id="drillLocation" name="location" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., Court A" required>
          </div>
          <div>
            <label class="font-semibold text-gray-700 block mb-2">Call Time</label>
            <input type="time" id="drillCallTime" name="calltime" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" required>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="font-semibold text-gray-700 block mb-2">Drill Instructions</label>
          <textarea id="drillDescription" name="description" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" rows="4" placeholder="Enter specific instructions, notes, or details about this drill..." required></textarea>
        </div>
      </form>
    </div>

    <!-- Fixed Footer -->
    <div class="border-t border-gray-200 p-6 flex-shrink-0">
      <div class="flex flex-col sm:flex-row gap-3 justify-end">
        <button type="button" onclick="closeDrillModal()" class="w-full sm:w-auto px-6 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold text-gray-800 transition-colors text-base">Cancel</button>
        <button type="button" onclick="saveDrill()" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors text-base">Add Drill</button>
      </div>
    </div>
  </div>
</div>

<style>

/* Add to both workout_modal.php and drill_modal.php */
.modal-date-header {
  padding: 12px 24px;
  border-bottom: 1px solid #e5e7eb;
  background: #f8fafc;
}

.modal-date-content {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #374151;
  font-weight: 500;
}

.modal-date-content svg {
  margin-right: 8px;
}  

/* Ensure modal is responsive on mobile */
@media (max-width: 640px) {
  #drillModal .mx-auto {
    margin: 1rem;
    width: calc(100% - 2rem);
  }
  
  #drillModal .max-h-\[90vh\] {
    max-height: 85vh;
  }
  
  #drillModal .p-6 {
    padding: 1rem;
  }
  
  #drillModal .text-base {
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  #drillModal input, 
  #drillModal select, 
  #drillModal textarea {
    min-height: 48px; /* Better touch targets */
  }
}

/* Improve scroll on mobile */
#drillModal .overflow-y-auto {
  -webkit-overflow-scrolling: touch;
}
</style>