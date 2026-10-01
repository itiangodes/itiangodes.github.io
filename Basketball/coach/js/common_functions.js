// Common functions used by both workouts and drills
function addRestDay(weekId, dayName, dayDate) {
  const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
  const isRestDay = dayElement && dayElement.querySelector('.rest-day-indicator');
  const isPast = dayElement && dayElement.classList.contains('bg-gray-100');
  
  if (isRestDay) {
      alert('This day is already marked as a Rest Day! 😴');
      return;
  }
  
  if (isPast) {
      alert('Cannot modify past days! ❌');
      return;
  }

  const dayColumn = document.querySelector(`[data-week="${weekId}"] [data-day="${dayName}"]`);
  const hasActivities = dayColumn && (dayColumn.textContent.includes('Workout') || dayColumn.textContent.includes('Drill') || dayColumn.textContent.includes('🏋️') || dayColumn.textContent.includes('🏀'));

  let message;
  if (hasActivities) {
      message = `⚠️ WARNING: ${dayName} already has activities!\n\nMarking as Rest Day will REMOVE ALL existing activities.\n\nDo you want to continue?`;
  } else {
      message = `Mark ${dayName} as a Rest Day?`;
  }

  if (!confirm(message)) return;

  const form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = `
    <input type="hidden" name="save_activity" value="1">
    <input type="hidden" name="week_id" value="${weekId}">
    <input type="hidden" name="day_name" value="${dayName}">
    <input type="hidden" name="type" value="Rest">
    <input type="hidden" name="activity_date" value="${dayDate}">
    <input type="hidden" name="title" value="Rest Day">
    <input type="hidden" name="description" value="Recovery and muscle relaxation day.">
    <input type="hidden" name="clear_existing" value="1">
  `;
  document.body.appendChild(form);
  form.submit();
}

function openDetailsByName(title, description, activityDate) {
  const modal = document.getElementById('infoModal');
  const body = document.getElementById('infoModalBody');
  const heading = document.getElementById('infoModalTitle');

  heading.innerText = title;
  if (!description) description = "No details provided.";

  // Parse details from the enhanced description format
  const details = {
    Date: activityDate ? new Date(activityDate).toLocaleDateString() : '—',
    // Extract Workout Type or Drill Category
    Type: description.match(/Type:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
          description.match(/Category:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
    Duration: description.match(/Duration:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
    Sets: description.match(/Sets:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
    Location: description.match(/Location:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
    CallTime: description.match(/Call Time:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
    Instructions: description.match(/Instructions:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
                  description.split('Instructions:').pop() || '—'
  };

  body.innerHTML = `
    <table class="w-full text-sm text-gray-700 border-collapse">
      <tr class="border-b"><td class="py-2 font-semibold w-1/3">📅 Date</td><td class="py-2">${details.Date}</td></tr>
      <tr class="border-b"><td class="py-2 font-semibold">🎯 Type/Category</td><td class="py-2">${details.Type}</td></tr>
      <tr class="border-b"><td class="py-2 font-semibold">🕒 Duration</td><td class="py-2">${details.Duration} minutes</td></tr>
      <tr class="border-b"><td class="py-2 font-semibold">🔁 Sets</td><td class="py-2">${details.Sets}</td></tr>
      <tr class="border-b"><td class="py-2 font-semibold">📍 Location</td><td class="py-2">${details.Location}</td></tr>
      <tr class="border-b"><td class="py-2 font-semibold">⏰ Call Time</td><td class="py-2">${details.CallTime}</td></tr>
      <tr><td class="py-2 font-semibold align-top">📝 Instructions</td><td class="py-2">${details.Instructions}</td></tr>
    </table>
  `;

  modal.classList.remove('hidden');
}

function closeInfoModal() {
  document.getElementById('infoModal').classList.add('hidden');
}

// Global variables
let currentWeek = null;
let currentDay = null;

// Monthly calendar functions
function openWorkoutModalForMonth() {
    const modal = document.getElementById('workoutModal');
    if (!modal) return;

    modal.classList.remove('hidden');

    // Clear week/day so this comes only from the date picker
    document.getElementById('wkWeekId').value = '';
    document.getElementById('wkDayName').value = '';
    document.getElementById('workoutDate').value = '';

    // Optional text in the blue bar
    const label = document.getElementById('workoutModalDateSelected');
    if (label) {
        label.textContent = 'Select a date below';
    }

    document.getElementById('workoutModalTitle').textContent = 'Add Workout';
}

function openDrillModalForMonth() {
    const modal = document.getElementById('drillModal');
    if (!modal) return;

    modal.classList.remove('hidden');

    // Clear week/day; drill will use the date from the picker
    document.getElementById('drWeekId').value = '';
    document.getElementById('drDayName').value = '';
    document.getElementById('drillDate').value = '';

    // Optional text in the yellow bar
    const label = document.getElementById('drillModalDateSelected');
    if (label) {
        label.textContent = 'Select a date below';
    }

    document.getElementById('drillModalTitle').textContent = 'Add Drill';
}


function openRatingModalForMonth() {
    // Get all calendar days with activities
    const calendarDays = document.querySelectorAll('#monthlyCalendar [data-date]');
    const daysWithActivities = [];
    
    calendarDays.forEach(day => {
        const date = day.dataset.date;
        const weekId = day.dataset.week;
        const dayName = day.dataset.day;
        
        // Check if day has workout or drill activities (exclude rest days)
        const hasWorkout = day.querySelector('.activity-icon.text-blue-600'); // Workout icon
        const hasDrill = day.querySelector('.activity-icon.text-yellow-600'); // Drill icon
        const isRestDay = day.querySelector('.activity-icon.text-green-600'); // Rest day icon
        
        // Only include days with workouts or drills, exclude rest days
        if ((hasWorkout || hasDrill) && !isRestDay && date && weekId && dayName) {
            daysWithActivities.push({
                date: date,
                weekId: weekId,
                dayName: dayName,
                displayDate: new Date(date + 'T00:00:00').toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                })
            });
        }
    });
    
    if (daysWithActivities.length === 0) {
        alert('No training days with workouts or drills found this month. Please add activities first.');
        return;
    }
    
    // Build the day selection list
    let dayListHTML = '<div class="space-y-2 max-h-96 overflow-y-auto">';
    
    daysWithActivities.forEach(day => {
        dayListHTML += `
            <button type="button" 
                onclick="selectDayForRating('${day.weekId}', '${day.dayName}', '${day.date}')" 
                class="w-full text-left px-4 py-3 bg-white hover:bg-purple-50 border border-gray-200 rounded-lg transition-colors flex items-center justify-between group">
                <div>
                    <div class="font-semibold text-gray-800">${day.dayName}</div>
                    <div class="text-sm text-gray-600">${day.displayDate}</div>
                </div>
                <div class="text-purple-600 opacity-0 group-hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </button>
        `;
    });
    
    dayListHTML += '</div>';
    
    // Show modal with day selection
    showDaySelectionModal('Select Training Day to Rate', dayListHTML);
}

function selectDayForRating(weekId, dayName, date) {
    // Close the day selection modal
    closeDaySelectionModal();
    
    // Open the rating modal with the selected day
    openRatingModal(dayName, date, 0, '');
}

function showDaySelectionModal(title, content) {
    const modal = document.createElement('div');
    modal.id = 'daySelectionModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">${title}</h3>
                <button type="button" onclick="closeDaySelectionModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
            </div>
            <div class="p-6">
                ${content}
            </div>
            <div class="border-t border-gray-200 p-4">
                <button type="button" onclick="closeDaySelectionModal()" class="w-full px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeDaySelectionModal() {
    const modal = document.getElementById('daySelectionModal');
    if (modal) {
        modal.remove();
    }
}


// Enhanced day activity options with delete and rating features
function showDayActivityOptions(weekId, dayName, date) {
    // Check if this day has activities
    const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
    const hasActivities = dayElement && (dayElement.querySelector('.activity-icon-container') || dayElement.textContent.includes('😴'));
    
    let ratingButton = '';
    let deleteAllButton = '';
    
    if (hasActivities) {
        ratingButton = `
            <button type="button" 
                    onclick="openRatingModal('${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                ⭐ Rate Performance
            </button>
        `;
        
        deleteAllButton = `
            <button type="button" 
                    onclick="deleteAllActivities('${weekId}', '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                🗑️ Delete All Activities
            </button>
        `;
    }

    const options = `
        <div class="space-y-2 p-4">
            <button type="button" 
                    onclick="openWorkoutModal(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                🏋️ Add Workout
            </button>
            <button type="button" 
                    onclick="openAddDrillModal(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                🏀 Add Drill
            </button>
            <button type="button" 
                    onclick="addRestDay(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                😴 Mark as Rest Day
            </button>
            ${ratingButton}
            ${deleteAllButton}
            <button type="button" 
                    onclick="viewDayActivities(${weekId}, '${dayName}', '${date}'); closeDayOptionsModal();" 
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-3 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                👁️ View Activities
            </button>
        </div>
    `;
    
    showDayOptionsModal(`Select Action for ${dayName} (${date})`, options);
}

function showDayOptionsModal(title, content) {
    const modal = document.createElement('div');
    modal.id = 'dayOptionsModal';
    modal.className = 'fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-auto">
            <div class="flex justify-between items-center p-6 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-800">${title}</h3>
                <button type="button" onclick="closeDayOptionsModal()" class="text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            </div>
            <div class="p-6">
                ${content}
            </div>
            <div class="border-t border-gray-200 p-4">
                <button type="button" onclick="closeDayOptionsModal()" class="w-full px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">Cancel</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function closeDayOptionsModal() {
    const modal = document.getElementById('dayOptionsModal');
    if (modal) {
        modal.remove();
    }
}

function viewDayActivities(weekId, dayName, date) {
    // This would show a modal with all activities for the selected day
    alert(`Viewing activities for ${dayName} (${date})\nWeek ID: ${weekId}\n\nThis would show a detailed list of all workouts, drills, and rest days for this date.`);
}

// NEW FUNCTIONS: Activity Management and Month Navigation

// Activity deletion
function deleteAllActivities(weekId, dayName, date) {
    if (hasPerformanceRating(date)) {
        alert('Cannot delete activities from a rated day!');
        return;
    }

    if (!confirm(`Are you sure you want to delete ALL activities for ${dayName} (${date})? This action cannot be undone.`)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="delete_all_activities" value="1">
        <input type="hidden" name="week_id" value="${weekId}">
        <input type="hidden" name="day_name" value="${dayName}">
        <input type="hidden" name="activity_date" value="${date}">
    `;
    document.body.appendChild(form);
    form.submit();
}



function deleteActivity(activityId, activityType) {

    // 🔍 Skip rating check if no activity element
    let activityElement = document.querySelector(`[data-activity-id="${activityId}"]`);
    let activityDate = activityElement ? activityElement.dataset.date : null;

    // ⛔ Prevent deletion if rated
    if (activityDate && hasPerformanceRating(activityDate)) {
        alert('Cannot delete activities from a day that has been rated!');
        return;
    }

    // 🗑 Confirm deletion
    if (!confirm(`Delete this ${activityType || 'activity'}?`)) {
        return;
    }

    // 📝 Submit deletion to PHP
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="delete_activity" value="1">
        <input type="hidden" name="activity_id" value="${activityId}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Month navigation
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

function previousMonth() {
    currentMonth--;
    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    }
    loadMonthCalendar(currentYear, currentMonth);
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }
    loadMonthCalendar(currentYear, currentMonth);
}

function loadMonthCalendar(year, month) {
    // This would typically make an AJAX call to reload the calendar
    // For now, we'll reload the page with the new month
    const url = new URL(window.location.href);
    url.searchParams.set('month', month + 1);
    url.searchParams.set('year', year);
    window.location.href = url.toString();
}

// Enhanced activity details with delete option
function showActivityDetails(title, description, activityDate, activityType, activityId) {
    const modal = document.getElementById('infoModal');
    const body = document.getElementById('infoModalBody');
    const heading = document.getElementById('infoModalTitle');

    heading.innerText = `${activityType}: ${title}`;
    
    if (!description) description = "No details provided.";

    // Parse details from the enhanced description format
    const details = {
        Date: activityDate ? new Date(activityDate).toLocaleDateString() : '—',
        Type: description.match(/Type:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
              description.match(/Category:\s*(.*?)\s*(?=\||$)/i)?.[1] || activityType,
        Duration: description.match(/Duration:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
        Sets: description.match(/Sets:\s*([\d]+.*?)\s*(?=\||$)/i)?.[1] || '—',
        Location: description.match(/Location:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
        CallTime: description.match(/Call Time:\s*(.*?)\s*(?=\||$)/i)?.[1] || '—',
        Instructions: description.match(/Instructions:\s*(.*?)\s*(?=\||$)/i)?.[1] || 
                      description.split('Instructions:').pop() || '—'
    };

    body.innerHTML = `
        <table class="w-full text-sm text-gray-700 border-collapse">
            <tr class="border-b"><td class="py-2 font-semibold w-1/3">📅 Date</td><td class="py-2">${details.Date}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🎯 Type</td><td class="py-2">${details.Type}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🕒 Duration</td><td class="py-2">${details.Duration} minutes</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">🔁 Sets</td><td class="py-2">${details.Sets}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">📍 Location</td><td class="py-2">${details.Location}</td></tr>
            <tr class="border-b"><td class="py-2 font-semibold">⏰ Call Time</td><td class="py-2">${details.CallTime}</td></tr>
            <tr><td class="py-2 font-semibold align-top">📝 Instructions</td><td class="py-2">${details.Instructions}</td></tr>
        </table>
        <div class="mt-4 flex justify-end space-x-3">
            <button type="button" 
                    onclick="deleteActivity(${activityId}, '${activityType}'); closeInfoModal();" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition-colors">
                🗑️ Delete Activity
            </button>
            <button type="button" 
                    onclick="closeInfoModal()" 
                    class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-semibold">
                Close
            </button>
        </div>
    `;

    modal.classList.remove('hidden');
}

function hasPerformanceRating(date) {
    const dayElement = document.querySelector(`[data-date="${date}"]`);
    if (!dayElement) return false;
    return dayElement.dataset.hasRating === 'true';
}