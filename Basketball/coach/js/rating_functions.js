// Rating Modal Functions

function openRatingModalForMonth() {
    const modal = document.getElementById('ratingSelectionModal');
    if (!modal) {
        console.error('Rating selection modal not found!');
        return;
    }

    modal.classList.remove('hidden');
    loadActivityDays();
}

function closeRatingSelectionModal() {
    const modal = document.getElementById('ratingSelectionModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function loadActivityDays() {
    const listContainer = document.getElementById('activityDaysList');
    if (!listContainer) return;

    // Get all calendar days that have activities
    const calendarDays = document.querySelectorAll('#monthlyCalendar > div[data-date]');
    const daysWithActivities = [];

    calendarDays.forEach(day => {
        const date = day.dataset.date;
        const weekId = day.dataset.week;
        const dayName = day.dataset.day;

        // Check if this day has activities (not empty and not just empty past day)
        const hasActivities = day.querySelector('.activity-icon-container');

        if (hasActivities && date && weekId && dayName) {
            // Get all activity icons for this day
            const activityIcons = day.querySelectorAll('.activity-icon');
            const activities = [];

            activityIcons.forEach(icon => {
                const title = icon.getAttribute('title');
                if (title) {
                    activities.push(title);
                }
            });

            daysWithActivities.push({
                date: date,
                weekId: weekId,
                dayName: dayName,
                activities: activities
            });
        }
    });

    // Sort by date (newest first)
    daysWithActivities.sort((a, b) => new Date(b.date) - new Date(a.date));

    // Display the days
    if (daysWithActivities.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg font-medium mb-2">No activities found</p>
                <p class="text-sm">Add workouts or drills first before rating performance.</p>
            </div>
        `;
        return;
    }

    let html = '';
    daysWithActivities.forEach(day => {
        const dateObj = new Date(day.date);
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });

        // Get activity icons
        let activityIcons = '';
        day.activities.forEach(activity => {
            if (activity === 'Workout') {
                activityIcons += '<span class="text-blue-600 text-lg mr-1" title="Workout">🏋️</span>';
            } else if (activity === 'Drill') {
                activityIcons += '<span class="text-yellow-600 text-lg mr-1" title="Drill">🏀</span>';
            } else if (activity === 'Rest Day') {
                activityIcons += '<span class="text-green-600 text-lg mr-1" title="Rest Day">😴</span>';
            }
        });

        html += `
            <div class="border border-gray-200 rounded-lg p-4 hover:bg-purple-50 hover:border-purple-300 transition cursor-pointer"
                 onclick="selectDayForRating('${day.date}', '${day.dayName}')">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-800">${formattedDate}</p>
                        <div class="flex items-center mt-2">
                            ${activityIcons}
                            <span class="text-sm text-gray-600 ml-2">${day.activities.length} activit${day.activities.length === 1 ? 'y' : 'ies'}</span>
                        </div>
                    </div>
                    <div>
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

function selectDayForRating(date, dayName) {
    // Close the selection modal
    closeRatingSelectionModal();

    // Open the rating modal for the selected day
    openRatingModal(dayName, date);
}