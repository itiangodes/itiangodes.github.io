<!-- 🏋️ Add Workout Plan Modal -->
<div id="workoutModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl mx-auto max-h-[90vh] flex flex-col">
    <!-- Header -->
    <div class="flex justify-between items-center p-6 border-b border-gray-200 flex-shrink-0">
      <h3 id="workoutModalTitle" class="text-xl font-bold text-gray-800">Add Workout Plan</h3>
      <button type="button" onclick="closeWorkoutModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold w-8 h-8 flex items-center justify-center">×</button>
    </div>

    <div class="px-6 py-3 bg-blue-50 border-b border-blue-100">
      <div class="flex items-center justify-center text-blue-700 font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span id="workoutModalDate">Selected Date</span>
      </div>
    </div>

    <!-- Scrollable Content -->
     <div class="flex-1 overflow-y-auto px-6 py-4">
          <form id="workoutForm" method="POST" class="space-y-4">
          <input type="hidden" name="saveactivity" value="1">
          <input type="hidden" name="weekid" id="wkWeekId">
          <input type="hidden" name="dayname" id="wkDayName">
          <input type="hidden" name="type" value="Workout">

          <!-- Date selection -->
          <div>
              <label class="font-semibold text-gray-700 block mb-2">Date</label>
              <input
                  type="date"
                  id="workoutDate"
                  name="activitydate"
                  class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base"
                  required
              >
          </div>

              <!-- Workout Category -->
              <div>
                <label class="font-semibold text-gray-700 block mb-2">Select Workout Type</label>
                <select id="drillCategoryFilter" onchange="filterWorkoutsByCategory()" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base">
                  <option value="">Choose a workout type</option>
                  <option value="Core Workout">Core Workout</option>
                  <option value="Upper Body Weight Training">Upper Body Weight Training</option>
                  <option value="Lower Body Weight Training">Lower Body Weight Training</option>
                  <option value="Upper Body Plyometric Workout">Upper Body Plyometric Workout</option>
                  <option value="Lower Body Plyometric Workout">Lower Body Plyometric Workout</option>
                </select>
              </div>
              
              <!-- Workout List -->
              <div id="workoutSelectGroup" class="hidden">
                <label class="font-semibold text-gray-700 block mb-2">Select Specific Workout</label>
                <div class="flex items-center gap-2">
                  <button type="button" class="icon-btn flex-shrink-0 w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-50" id="workoutDetailsBtn" title="View workout details">
                    <svg width="20" height="20" fill="none" aria-hidden="true">
                      <circle cx="10" cy="10" r="9" stroke="#21808d" stroke-width="2"/>
                      <text x="10" y="14" font-size="12" text-anchor="middle" fill="#21808d">i</text>
                    </svg>
                  </button>
                  <select id="workoutSelect" onchange="updateWorkoutSelectionDuration()" class="flex-1 border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base">
                    <option value="">Choose a specific workout</option>
                  
                    <!-- Core Workout -->
                    <optgroup label="Core Workout" data-category="Core Workout">
                      <option value="Toe Touches">Toe Touches</option>
                      <option value="Suitcase Crunches">Suitcase Crunches</option>
                      <option value="Sit-Ups">Sit-Ups</option>
                      <option value="Planks">Planks</option>
                      <option value="Stir the Pot (on Stability Ball)">Stir the Pot (on Stability Ball)</option>
                      <option value="Medicine Ball Slams">Medicine Ball Slams</option>
                      <option value="Med Ball Side Toss">Med Ball Side Toss</option>
                      <option value="Hanging Straight Leg Raises">Hanging Straight Leg Raises</option>
                      <option value="Windshield Wipers">Windshield Wipers</option>
                      <option value="Hyperextensions">Hyperextensions</option>
                      <option value="Clamshells (each side)">Clamshells (each side)</option>
                      <option value="Fire Hydrant (each side)">Fire Hydrant (each side)</option>
                      <option value="Side Plank">Side Plank</option>
                      <option value="Russian Twists">Russian Twists</option>
                      <option value="Dead Bug">Dead Bug</option>                
                    </optgroup>

                    <!-- Upper Body Weight Training -->
                    <optgroup label="Upper Body Weight Training" data-category="Upper Body Weight Training">
                      <option value="Bench press">Bench press</option>
                      <option value="Bicep curl">Bicep curl</option>
                      <option value="Chest fly">Chest fly</option>
                      <option value="Front raise (front delt raise)">Front raise (front delt raise)</option>
                      <option value="Overhead press">Overhead press</option>
                      <option value="Pullover / overhead skull crusher">Pullover / overhead skull crusher</option>
                      <option value="Rear delt raise">Rear delt raise</option>
                      <option value="Seated row">Seated row</option>
                      <option value="Triceps extension">Triceps extension</option>
                      <option value="Wide-grip pull-up">Wide-grip pull-up</option>
                      <option value="Shoulder press">Shoulder press</option>
                      <option value="Incline press">Incline press</option>
                      <option value="High row">High row</option>
                      <option value="Chest press (machine)">Chest press (machine)</option>
                      <option value="Incline row">Incline row</option>
                      <option value="Inverted row">Inverted row</option>
                      <option value="Lat pulldown">Lat pulldown</option>
                      <option value="Triceps dip">Triceps dip</option>
                      <option value="Upright row">Upright row</option>
                      <option value="Seated row (machine) if not covered above">Seated row (machine) if not covered above</option>
                      <option value="Wide-grip pull-up tips">Wide-grip pull-up tips</option>
                      <option value="Landmine press">Landmine press</option>
                    </optgroup>

                    <!-- Lower Body Weight Training -->
                    <optgroup label="Lower Body Weight Training" data-category="Lower Body Weight Training">
                      <option value="Calf Raise">Calf Raise</option>
                      <option value="Front Squat">Front Squat</option>
                      <option value="Hamstring Curl">Hamstring Curl</option>
                      <option value="Leg Extension">Leg Extension</option>
                      <option value="Lower Back Extension">Lower Back Extension</option>
                      <option value="Dumbbell Lunge">Dumbbell Lunge</option>
                      <option value="Glute Ham Raises">Glute Ham Raises</option>
                      <option value="Hip Abduction">Hip Abduction</option>
                      <option value="Hip Adduction">Hip Adduction</option>
                      <option value="Leg Press">Leg Press</option>
                      <option value="Romanian Deadlift (RDL)">Romanian Deadlift (RDL)</option>
                      <option value="Split Squat">Split Squat</option>
                    </optgroup>

                    <!-- Upper Body Plyometric Workout -->
                    <optgroup label="Upper Body Plyometric Workout" data-category="Upper Body Plyometric Workout">
                      <option value="Plyometric Push-Ups / Push-Up Clap">Plyometric Push-Ups / Push-Up Clap</option>
                      <option value="Medicine Ball Chest Pass (against wall or with partner)">Medicine Ball Chest Pass (against wall or with partner)</option>
                      <option value="Overhead Medicine Ball Toss">Overhead Medicine Ball Toss</option>
                      <option value="Plyometric Dips">Plyometric Dips</option>
                      <option value="Plyometric Pull-Ups">Plyometric Pull-Ups</option>
                      <option value="Medicine Ball Slams">Medicine Ball Slams</option>
                      <option value="Plyometric Push-Ups on Elevated Surface (Depth Plyo Push-Up)">Plyometric Push-Ups on Elevated Surface (Depth Plyo Push-Up)</option>
                    </optgroup>

                    <!-- Lower Body Plyometric Workout -->    
                    <optgroup label="Lower Body Plyometric Workout" data-category="Lower Body Plyometric Workout">
                      <option value="Box Jumps">Box Jumps</option>
                      <option value="Depth Jumps">Depth Jumps</option>
                      <option value="Lateral Bounds">Lateral Bounds</option>
                      <option value="Alternate Leg Bounding">Alternate Leg Bounding</option>
                      <option value="Single Leg Hops">Single Leg Hops</option>
                      <option value="Step Ups">Step Ups</option>
                      <option value="Squat Jumps">Squat Jumps</option>
                      <option value="Split Squat Jumps (aka Lunge Jumps)">Split Squat Jumps (aka Lunge Jumps)</option>
                      <option value="Lateral Bounds (Side-to-Side Bounds)">Lateral Bounds (Side-to-Side Bounds)</option>
                      <option value="Single Leg Hops / Single-Leg Squat Jumps">Single Leg Hops / Single-Leg Squat Jumps</option>
                      <option value="Step Up Jumps (Alternating Step-Up Jumps)">Step Up Jumps (Alternating Step-Up Jumps)</option>
                      <option value="Zigzag or Agility Ladder Hops">Zigzag or Agility Ladder Hops</option>
                    </optgroup>
                  </select>
                </div>
              </div>

              <!-- Duration / Sets -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="font-semibold text-gray-700 block mb-2">Duration (minutes)</label>
                  <input type="number" id="workoutDuration" name="duration" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., 60" required>
                </div>
                <div>
                  <label class="font-semibold text-gray-700 block mb-2">Number of Sets</label>
                  <input type="number" id="workoutSets" name="sets" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., 3" required>
                </div>
              </div>

              <!-- Location / Call Time -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="font-semibold text-gray-700 block mb-2">Location</label>
                  <input type="text" id="workoutLocation" name="location" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" placeholder="e.g., Main Court" required>
                </div>
                <div>
                  <label class="font-semibold text-gray-700 block mb-2">Call Time</label>
                  <input type="time" id="workoutCallTime" name="calltime" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" required>
                </div>
              </div>

              <!-- Description -->
              <div>
                <label class="font-semibold text-gray-700 block mb-2">Workout Instructions</label>
                <textarea id="workoutDescription" name="description" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-200 focus:border-blue-500 text-base" rows="4" placeholder="Enter specific instructions, notes, or details about this workout..." required></textarea>
              </div>
            </form>
          </div>
        

            <!-- Fixed Footer -->
            <div class="border-t border-gray-200 p-6 flex-shrink-0">
              <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <button type="button" onclick="closeWorkoutModal()" class="w-full sm:w-auto px-6 py-3 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold text-gray-800 transition-colors text-base">Cancel</button>
                <button type="button" onclick="saveWorkout()" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors text-base">Add Workout</button>
              </div>
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
  #workoutModal .mx-auto {
    margin: 1rem;
    width: calc(100% - 2rem);
  }
  
  #workoutModal .max-h-\[90vh\] {
    max-height: 85vh;
  }
  
  #workoutModal .p-6 {
    padding: 1rem;
  }
  
  #workoutModal .text-base {
    font-size: 16px; /* Prevents zoom on iOS */
  }
  
  #workoutModal input, 
  #workoutModal select, 
  #workoutModal textarea {
    min-height: 48px; /* Better touch targets */
  }
}

/* Improve scroll on mobile */
#workoutModal .overflow-y-auto {
  -webkit-overflow-scrolling: touch;
}
</style>