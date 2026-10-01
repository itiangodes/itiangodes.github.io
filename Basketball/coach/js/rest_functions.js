// Rest Day Modal Functions

function addRestDayForMonth() {
    const modal = document.getElementById('restDayModal');
    if (!modal) {
        console.error('Rest day modal not found!');
        return;
    }

    modal.classList.remove('hidden');

    // Clear previous values
    document.getElementById('restWeekId').value = '';
    document.getElementById('restDayName').value = '';
    document.getElementById('restDate').value = '';
    document.getElementById('restDescription').value = 'Recovery and muscle relaxation day.';

    // Update the label
    const label = document.getElementById('restDayModalDate');
    if (label) label.textContent = 'Select a date below';

    document.getElementById('restDayModalTitle').textContent = 'Add Rest Day';
}

function closeRestDayModal() {
    const modal = document.getElementById('restDayModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function saveRestDay() {
    const date = document.getElementById('restDate').value;
    
    if (!date) {
        alert('Please select a date for the rest day.');
        return;
    }

    // Check if date is in the past
    const selectedDate = new Date(date + 'T00:00:00');
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    if (selectedDate < today) {
        alert('Cannot add rest days to past dates!');
        return;
    }

    // Check if day already has rest day
    const dayElement = document.querySelector(`[data-date="${date}"]`);
    if (dayElement) {
        const existingRestDay = dayElement.querySelector('.activity-icon.text-green-600');
        if (existingRestDay) {
            alert('This day is already marked as a rest day!');
            return;
        }

        // Check for existing workouts or drills
        const hasWorkout = dayElement.querySelector('.activity-icon.text-blue-600');
        const hasDrill = dayElement.querySelector('.activity-icon.text-yellow-600');
        
        if (hasWorkout || hasDrill) {
            const confirmMsg = 'This day already has workouts or drills! Marking it as a Rest Day will REMOVE ALL existing activities.\n\nDo you want to continue?';
            if (!confirm(confirmMsg)) {
                return;
            }
        } else {
            // No existing activities, just confirm
            const confirmMsg = 'Mark this day as a Rest Day?';
            if (!confirm(confirmMsg)) {
                return;
            }
        }
    }

    // Submit the form
    document.getElementById('restDayForm').submit();
}


// Update rest date field to show selected date in header
document.addEventListener('DOMContentLoaded', function() {
    const restDateInput = document.getElementById('restDate');
    if (restDateInput) {
        restDateInput.addEventListener('change', function() {
            const label = document.getElementById('restDayModalDate');
            if (label && this.value) {
                const dateObj = new Date(this.value + 'T00:00:00');
                label.textContent = dateObj.toLocaleDateString('en-US', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            }
        });
    }
});