// Workout functions from workout.php
function openWorkoutModal(weekId, dayName, dayDate) {
  const dayElement = document.querySelector(`[data-week="${weekId}"][data-day="${dayName}"]`);
  const isRestDay = dayElement && dayElement.querySelector('.rest-day-indicator');
  const isPast = dayElement && dayElement.classList.contains('bg-gray-100');
  
  if (isRestDay) {
      alert('Cannot add workouts to a rest day! ❌');
      return;
  }
  
  if (isPast) {
      alert('Cannot add activities to past days! ❌');
      return;
  }
  
  document.getElementById('workoutModal').classList.remove('hidden');
  document.getElementById('wkWeekId').value = weekId;
  document.getElementById('wkDayName').value = dayName;
  document.getElementById('workoutDate').value = dayDate;
  document.getElementById('workoutModalTitle').textContent = 'Add Workout - ' + dayName;
  
  // ADDED: Display the date in the modal
  const dateDisplay = document.getElementById('workoutModalDate');
  if (dateDisplay && dayDate) {
    const formattedDate = new Date(dayDate).toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
    dateDisplay.textContent = formattedDate;
  }
  
  // Add event listener for the info button
  document.getElementById('workoutDetailsBtn').onclick = showWorkoutDetails;
  
  // Clear form
  clearWorkoutForm();
}

function closeWorkoutModal() {
  document.getElementById('workoutModal').classList.add('hidden');
  clearWorkoutForm();
}

function clearWorkoutForm() {
  document.getElementById('drillCategoryFilter').value = '';
  document.getElementById('workoutSelectGroup').classList.add('hidden');
  document.getElementById('workoutSelect').innerHTML = '<option value="">Choose a specific workout</option>';
  document.getElementById('workoutDuration').value = '';
  document.getElementById('workoutSets').value = '';
  document.getElementById('workoutLocation').value = '';
  document.getElementById('workoutCallTime').value = '';
  document.getElementById('workoutDescription').value = '';
}

function filterWorkoutsByCategory() {
  const category = document.getElementById('drillCategoryFilter').value;
  const group = document.getElementById('workoutSelectGroup');
  group.classList.toggle('hidden', !category);

  document.querySelectorAll('#workoutSelect optgroup').forEach(g => {
    g.style.display = (g.dataset.category === category) ? 'block' : 'none';
  });
}

function updateWorkoutSelectionDuration() {
  const selected = document.querySelector('#workoutSelect option:checked');
  if (selected) {
    // Auto-populate description with selected workout
    const workoutName = selected.value;
    document.getElementById('workoutDescription').value = workoutName + ' - ';
  }
}

// Workout descriptions object
const workoutDescriptions = {
  // Core Workout
  "Toe Touches": {
    purpose: "Stretch and activate hamstrings/lower core, aids flexibility and trunk mobility for shooting, cutting, and stability in motion.",
    execution: [
      "• Lie flat on your back with legs extended straight up so feet are above hips.",
      "• Reach hands toward toes by curling the upper body and lifting shoulders off the floor, focusing on the abs.",
      "• Pause briefly at peak contraction, then lower with control."
    ]
  },
  "Suitcase Crunches": {
    purpose: "Improves abdominal strength for body control, strong posture, and absorbing contact.",
    execution: [
      "• Lie flat with legs straight and arms overhead.",
      "• Simultaneously bring knees toward chest and lift torso, reaching hands to meet shins above midsection as if folding.",
      "• Hold briefly, then return to the starting position fully extended."
    ]
  },
  "Sit-Ups": {
    purpose: "Builds rectus abdominis strength, contributing to trunk flexion, force transfer, and resisting defenders.",
    execution: [
      "• Lie on your back with knees bent and feet on the floor.",
      "• Cross arms over chest or keep them at your sides.",
      "• Engage abs to curl the torso up to the thighs while exhaling on the way up.",
      "• Lower with control until shoulders touch the ground."
    ]
  },
  "Planks": {
    purpose: "Trains overall core endurance and stability crucial for balance, coordination, and transferring lower/upper limb force in basketball.",
    execution: [
      "• Place forearms on the floor with elbows under shoulders and feet hip-width apart.",
      "• Keep the body in a straight line, avoiding hips dropping or raising.",
      "• Brace the core and maintain position for the desired time (start with 20–30 seconds)."
    ]
  },
  "Stir the Pot (on Stability Ball)": {
    purpose: "Challenges anti-extension and anti-rotation stability for better defensive stance and strong post moves.",
    execution: [
      "• Start in a forearm plank with forearms on a stability ball.",
      "• Brace the core and make slow, controlled circles with the forearms, keeping hips steady.",
      "• Perform 6–10 circles each direction, rest, and repeat."
    ]
  },
  "Medicine Ball Slams": {
    purpose: "Builds explosive core/passing power and quick movement transfer for rebounding and outlet passes.",
    execution: [
      "• Stand upright holding a medicine ball overhead with arms fully extended.",
      "• Forcefully slam the ball to the floor while crunching the core and driving the hips.",
      "• Retrieve the ball and reset quickly for the next repetition."
    ]
  },
  "Med Ball Side Toss": {
    purpose: "Improves rotational core power for passes, shooting, and rapid directional changes.",
    execution: [
      "• Stand sideways to a wall with feet shoulder-width apart and knees slightly bent.",
      "• Hold the medicine ball at the hip, rotate the torso, and throw the ball forcefully into the wall using core rotation.",
      "• Catch or retrieve the ball and repeat on both sides."
    ]
  },
  "Hanging Straight Leg Raises": {
    purpose: "Strengthens lower abs and hip flexors for high jumps, sprints, and body control in the air.",
    execution: [
      "• Hang from a pull-up bar with arms fully extended and body straight.",
      "• Brace the core and lift legs together in front (knees straight) until legs reach parallel to the floor or higher.",
      "• Lower legs with control and avoid swinging."
    ]
  },
  "Windshield Wipers": {
    purpose: "Develops oblique and core rotational strength, supporting twisting motions in layups and defense.",
    execution: [
      "• Hang from a bar (or lie on your back with arms out for a beginner option) with legs extended upward.",
      "• Keeping legs straight, move them side-to-side in a wide, controlled arc like windshield wipers.",
      "• Maintain slow tempo and minimize swinging."
    ]
  },
  "Hyperextensions": {
    purpose: "Strengthens lower back and glutes for upright posture and safe landings after jumps.",
    execution: [
      "• Set up on a back extension bench with the pad just below the hips.",
      "• Cross arms over chest or place hands behind head.",
      "• Lower the torso by hinging at the hips until the body is nearly perpendicular to the legs.",
      "• Raise the torso until the body is straight without forcing into hyperextension."
    ]
  },
  "Clamshells (each side)": {
    purpose: "Activates glute medius and hip stabilizers for defensive stance, lateral movement, and knee health.",
    execution: [
      "• Lie on your side with knees bent at 90° and hips stacked.",
      "• Keep feet together and lift the top knee up without rotating the torso, focusing on the glute.",
      "• Lower the knee with control and repeat on each side."
    ]
  },
  "Fire Hydrant (each side)": {
    purpose: "Targets glutes and hip rotators, aiding lateral explosiveness and stability.",
    execution: [
      "• Kneel on all fours with hands under shoulders and knees under hips.",
      "• Lift one knee out to the side while keeping the knee bent; pause briefly.",
      "• Lower with control while keeping the core braced and hips square."
    ]
  },
  "Side Plank": {
    purpose: "Builds anti-lateral flexion strength vital for holding position, absorbing contact, and balance during layups.",
    execution: [
      "• Lie on your side and support the body on the forearm (elbow under shoulder) and the side of the foot.",
      "• Stack hips and shoulders so the body forms a straight line.",
      "• Hold for time, then switch sides as needed."
    ]
  },
  "Russian Twists": {
    purpose: "Trains rotational power in the trunk, necessary for passing, shooting, and quick direction changes.",
    execution: [
      "• Sit with knees bent and feet lifted off the floor (or keep feet down for an easier version).",
      "• Lean back slightly and hold a weight or ball at the chest.",
      "• Rotate the torso to one side and touch the weight to the floor, then rotate to the other side and repeat."
    ]
  },
  "Dead Bug": {
    purpose: "Teaches core stabilization and cross-body coordination for athletic movements and injury prevention.",
    execution: [
      "• Lie on your back with arms straight up and knees bent to 90° over the hips.",
      "• Lower one arm and the opposite leg toward the floor while keeping the lower back flat.",
      "• Return to the start position, switch sides, and repeat with controlled reps."
    ]
  },
  // Upper Body Weight Training
  "Bench press": {
    purpose: "Builds chest, shoulders, and triceps strength for stronger passes, finishes through contact, box outs, and absorbing contact at the rim. Upper-body strength supports rebounding and shielding the ball.",
    execution: [
      "• Lie on a bench with feet planted on the floor.",
      "• Maintain a light arch in your lower back and pull your shoulder blades back and down.",
      "• Grip the bar slightly wider than your shoulders.",
      "• Lower the bar to your mid-chest, keeping your forearms vertical.",
      "• Press the bar back up to the starting position, keeping your wrists stacked over your elbows."
    ]
  },
  "Bicep curl": {
    purpose: "Improves elbow flexor strength for securing rebounds, strong rip-throughs, and resisting ball strips when protecting the ball. Use as an accessory exercise.",
    execution: [
      "• Stand tall with your core braced.",
      "• Keep your elbows close to your sides throughout the movement.",
      "• Curl the weight up without swinging your body.",
      "• Squeeze your biceps at the top of the movement.",
      "• Lower the weight under control."
    ]
  },
  "Chest fly": {
    purpose: "Trains horizontal adduction to aid chest strength and control when extending arms for passes and shielding. Use light-to-moderate loads.",
    execution: [
      "• Lie on a bench with dumbbells held over your chest.",
      "• Maintain a slight bend in your elbows.",
      "• Open your arms in a wide, controlled arc until your elbows are level with your torso.",
      "• Squeeze your chest to 'hug' the weights back to the starting position.",
      "• Avoid overstretching at the bottom of the movement."
    ]
  },
  "Front raise (front delt raise)": {
    purpose: "Strengthens anterior delts for ball pickups, straight-arm contests, and overhead ball control.",
    execution: [
      "• Stand tall, holding dumbbells with a thumbs-up or neutral grip.",
      "• Raise the weights to shoulder height without using momentum.",
      "• Pause briefly at the top.",
      "• Lower the weights slowly and with control."
    ]
  },
  "Overhead press": {
    purpose: "Builds shoulder and triceps strength for strong overhead passing, rebounding at full reach, and resisting contact with arms extended.",
    execution: [
      "• Stand with the bar or dumbbells at shoulder height.",
      "• Squeeze your glutes and brace your core.",
      "• Press the weight overhead, moving your head slightly backward to clear a path.",
      "• Lock out with your biceps by your ears.",
      "• Control the weight on the way down."
    ]
  },
  "Pullover / overhead skull crusher": {
    purpose: "Develops lats and serratus strength for overhead control, rebounding reach, and core connection.",
    execution: [
      "• Lie on a bench with a dumbbell held over your chest.",
      "• Maintain a slight bend in your elbows.",
      "• Reach the dumbbell back behind your head until you feel a stretch in your lats.",
      "• Pull the weight back to the start using your lats, without flaring your ribs."
    ]
  },
  "Rear delt raise": {
    purpose: "Trains posterior delts and upper back for posture, shooting mechanics, and deceleration.",
    execution: [
      "• Hinge at your hips, keeping your back flat.",
      "• Let the dumbbells hang under your shoulders.",
      "• Raise the weights out and slightly back, leading with your elbows.",
      "• Squeeze your shoulder blades together gently at the top.",
      "• Lower the weights with control."
    ]
  },
  "Seated row": {
    purpose: "Builds horizontal pulling strength for battling for position, securing rebounds, and balancing pressing volume.",
    execution: [
      "• Sit tall on the machine with your chest up.",
      "• Pull the handle to your lower ribs.",
      "• Keep your shoulders down and back during the pull.",
      "• Pause briefly when the handle touches your torso.",
      "• Extend your arms fully without rounding your back."
    ]
  },
  "Triceps extension": {
    purpose: "Strengthens triceps for shooting extension, strong passes, and finishing through contact.",
    execution: [
      "• Keep your upper arm stable and completely vertical.",
      "• Hinge only at the elbow to lower and raise the weight.",
      "• Use a full range of motion without letting your shoulder move.",
      "• Control the weight on the eccentric (lowering) portion."
    ]
  },
  "Wide-grip pull-up": {
    purpose: "Develops lats, upper back, and grip for vertical pulling, key for rebounding and overhead control.",
    execution: [
      "• Start from a dead hang with arms fully extended.",
      "• Set your shoulders by pulling your shoulder blades down and back.",
      "• Pull your chest toward the bar without craning your neck.",
      "• Lower your body with control back to the dead hang position."
    ]
  },
  "Shoulder press": {
    purpose: "Builds shoulder and triceps strength for strong overhead passing, rebounding at full reach, and resisting contact with arms extended.",
    execution: [
      "• Stand with the bar or dumbbells at shoulder height.",
      "• Squeeze your glutes and brace your core.",
      "• Press the weight overhead, moving your head slightly backward to clear a path.",
      "• Lock out with your biceps by your ears.",
      "• Control the weight on the way down."
    ]
  },
  "Incline press": {
    purpose: "Targets upper chest and anterior delts for angled pushes like contested finishes.",
    execution: [
      "• Set a bench to a 30–45° angle.",
      "• Lower the dumbbells or bar to your upper chest.",
      "• Keep your forearms vertical throughout the movement.",
      "• Press the weight up, keeping your shoulders down."
    ]
  },
  "High row": {
    purpose: "Emphasizes upper back and rear delts for posture and contesting shots.",
    execution: [
      "• On a cable or machine, pull the handles with your elbows high and wide.",
      "• Pull toward your upper ribs or lower chest.",
      "• Keep your shoulders down; avoid shrugging.",
      "• Return the weight slowly to the start."
    ]
  },
  "Chest press (machine)": {
    purpose: "Provides safer, stable pressing to build pushing strength with less fatigue.",
    execution: [
      "• Adjust the seat so the handles align with your mid-chest.",
      "• Keep your feet planted on the floor.",
      "• Press the handles forward without locking your elbows out hard.",
      "• Control the weight back to a light stretch."
    ]
  },
  "Incline row": {
    purpose: "Chest-supported row that builds upper back strength for rebounding and defense with low-back stress.",
    execution: [
      "• Lie prone on an incline bench, chest against the pad.",
      "• Pull the dumbbells toward your lower chest.",
      "• Squeeze your shoulder blades together at the top.",
      "• Lower the weights fully, allowing your shoulders to stretch forward."
    ]
  },
  "Inverted row": {
    purpose: "Bodyweight horizontal pull that builds scapular control and trunk stiffness.",
    execution: [
      "• Set a bar at approximately waist height.",
      "• Grip the bar with an overhand grip, hands wider than shoulders.",
      "• Keep your body straight from your heels to your head.",
      "• Pull your chest to the bar.",
      "• Pause, then lower yourself with control."
    ]
  },
  "Lat pulldown": {
    purpose: "Builds lats and scapular strength, a good alternative to pull-ups.",
    execution: [
      "• Sit tall on the machine with a slight lean back.",
      "• Pull the bar to your upper chest by driving your elbows down.",
      "• Keep your chest up and shoulders down.",
      "• Control the bar back to full elbow extension."
    ]
  },
  "Triceps dip": {
    purpose: "Compound movement for triceps, chest, and shoulders for finishing power.",
    execution: [
      "• On parallel bars, set your shoulders down and back.",
      "• Lower your body until your upper arms are at least parallel to the floor.",
      "• Press back up to the starting position without flaring your elbows excessively."
    ]
  },
  "Upright row": {
    purpose: "Targets upper traps and delts for shoulder girdle strength in rebounding.",
    execution: [
      "• Use a grip slightly wider than your shoulders.",
      "• Pull the bar or dumbbells to your upper ribs/lower chest.",
      "• Keep your elbows just above your wrists.",
      "• Avoid yanking the weight; use a controlled motion."
    ]
  },
  "Seated row (machine) if not covered above": {
    purpose: "Machine variation that focuses on scapular retraction for posture.",
    execution: [
      "• Maintain a neutral spine throughout the movement.",
      "• Pull the handles to your lower ribs.",
      "• Pause and squeeze your shoulder blades together.",
      "• Control the weight forward without rounding your back."
    ]
  },
  "Wide-grip pull-up tips": {
    purpose: "Develops lats, upper back, and grip for vertical pulling, key for rebounding and overhead control.",
    execution: [
      "• Start from a dead hang with arms fully extended.",
      "• Set your shoulders by pulling your shoulder blades down and back.",
      "• Pull your chest toward the bar without craning your neck.",
      "• Lower your body with control back to the dead hang position.",
      "• Use assistance bands if needed to maintain proper form."
    ]
  },
  "Landmine press": {
    purpose: "Shoulder-friendly angled press that builds diagonal pushing power.",
    execution: [
      "• Anchor one end of a barbell in a landmine base or corner.",
      "• Hold the sleeve end of the bar at your chest.",
      "• Assume a staggered stance for stability.",
      "• Press the bar forward and upward in an arc.",
      "• Control the bar back to the start position."
    ]
  },
  // Lower Body Weight Training
  "Calf Raise": {
    purpose: "Builds calf strength and ankle stability, which help with explosive jumps, sprinting, quick direction changes, and injury prevention (such as ankle sprains).",
    execution: [
      "• Stand with the balls of your feet on the edge of a step, heels hanging off, or flat on the floor.",
      "• Hold onto something for balance.",
      "• Press through your toes to raise your heels as high as you can, pause at the top.",
      "• Lower your heels slowly below the step (for a full stretch) or back to the floor.",
      "• Keep the movement slow and controlled; avoid bouncing."
    ]
  },
  "Front Squat": {
    purpose: "Strengthens quads, glutes, and core—essential for vertical jumping, sprinting, and stable landings.",
    execution: [
      "• Stand with feet shoulder-width. Hold a barbell on the front of your shoulders, elbows high and parallel to the ground.",
      "• Keep your torso upright, brace your core.",
      "• Squat down, keeping knees tracking over toes, until thighs are parallel or lower.",
      "• Drive through your heels to stand up, maintaining core bracing and upright posture."
    ]
  },
  "Hamstring Curl": {
    purpose: "Targets the hamstrings, which are crucial for sprinting speed, deceleration, landing control, and preventing knee injuries.",
    execution: [
      "• Lie face down on a leg curl machine or with feet hooked under a resistance band or stability ball.",
      "• Flex your knees, pulling your feet toward your glutes against resistance.",
      "• Pause at the top, then lower slowly to full extension.",
      "• Move in a controlled manner to maximize activation and protect the knees."
    ]
  },
  "Hip Adduction": {
    purpose: "Strengthens inner thigh muscles (adductors) for lateral movement, defensive shuffling, and stabilization during quick changes of direction.",
    execution: [
      "• Sit on a hip adduction machine, positioning pads on the inside of your knees or thighs.",
      "• Squeeze your legs together against the resistance.",
      "• Pause and slowly return to the starting position, resisting the movement back.",
      "• Maintain upright posture, do not lean forward or backward during the movement."
    ]
  },
  "Leg Extension": {
    purpose: "Isolates and strengthens the quadriceps for explosive sprints, vertical jumps, and powerful knee extension.",
    execution: [
      "• Sit on the leg extension machine with legs under the pad, feet pointing forward.",
      "• Extend your knees and lift the weight until your legs are straight but not locked.",
      "• Pause, then lower slowly back to the starting position.",
      "• Avoid using excessive weight to prevent knee strain."
    ]
  },
  "Lower Back Extension": {
    purpose: "Strengthens the lower back and glutes, which are important for maintaining posture, absorbing force on landings, and preventing lower-back injuries.",
    execution: [
      "• Position yourself on a hyperextension bench, feet secure, pad just below your hips.",
      "• Cross arms or place hands behind head.",
      "• Keeping your back neutral, lower your torso until you feel a slight stretch in your hamstrings.",
      "• Contract your glutes and lower back to raise your torso to the starting position; avoid excessive hyperextension."
    ]
  },
  "Dumbbell Lunge": {
    purpose: "Builds single-leg strength, balance, and coordination for jumping, driving to the basket, and stable landings.",
    execution: [
      "• Stand with a dumbbell in each hand, arms at sides.",
      "• Step forward with one leg, lowering hips until both knees are bent at 90° (back knee just above floor).",
      "• Keep your torso upright and core engaged.",
      "• Push through your front foot to return to standing, then alternate legs."
    ]
  },
  "Glute Ham Raises": {
    purpose: "Strong hamstrings and glutes support sprinting, jumping, and knee stability, reducing injury risk.",
    execution: [
      "• Kneel on a glute-ham developer machine with ankles secured.",
      "• From upright, slowly lower your torso by straightening your knees and hip, keeping torso and thighs straight.",
      "• Use your hamstrings to resist as you lower, then contract hamstrings and glutes to return to the start.",
      "• Use hands for assistance if needed; avoid dropping quickly."
    ]
  },
  "Hip Abduction": {
    purpose: "Trains outer thigh/hip muscles for lateral movement, defensive slides, and hip stability.",
    execution: [
      "• Sit on a hip abduction machine, pads on the outside of your knees or thighs.",
      "• Press your legs outward against resistance.",
      "• Pause at full abduction, then return slowly in control.",
      "• Stay upright and avoid leaning."
    ]
  },
  "Leg Press": {
    purpose: "Compound movement to strengthen quads, glutes, and hamstrings for overall lower-body power (jumping, sprinting, shuffling).",
    execution: [
      "• Sit on the leg press machine, feet shoulder-width on platform.",
      "• Unlock safety handles, lower platform toward your chest by bending knees (keep heels on platform).",
      "• Stop when knees are at 90° or slightly less.",
      "• Press through heels to extend legs, but don't lock knees at top."
    ]
  },
  "Romanian Deadlift (RDL)": {
    purpose: "Hamstring and glute strength, vital for explosive sprints, jumps, and injury prevention.",
    execution: [
      "• Stand tall holding barbell/dumbbells in front of thighs.",
      "• Soften knees, hinge hips back, lowering weights while keeping back flat and chest up.",
      "• Lower until hamstrings feel a stretch (bar close to legs), then drive hips forward to return to standing.",
      "• Avoid rounding your lower back."
    ]
  },
  "Split Squat": {
    purpose: "Develops single-leg strength, balance, and hip/knee stability for powerful take-offs and deceleration.",
    execution: [
      "• Stand with one foot in front and other behind (about two-three feet apart).",
      "• Lower back knee to the floor, keeping front knee above ankle and torso upright.",
      "• Press through front heel to rise up, keeping core engaged.",
      "• Complete all reps on one side, then switch."
    ]
  },
  // Upper Body Plyometric Workout
  "Plyometric Push-Ups / Push-Up Clap": {
    purpose: "Develops explosiveness in chest, shoulders, and triceps for powerful passes, contact finishes, and fast shot releases.",
    execution: [
      "• Start in a traditional push-up position with your hands slightly wider than shoulder-width apart and your body in a straight line from head to toe.",
      "• Lower your chest toward the ground by bending your elbows, keeping them at a 45-degree angle to your body.",
      "• Push explosively through your hands, propelling your upper body off the ground.",
      "• In mid-air, quickly bring your hands back to the starting position, ready to absorb the impact.",
      "• Land softly with your elbows slightly bent to cushion the landing.",
      "• Immediately go into the next repetition, repeating the explosive push-off and soft landing.",
      "• Keep your core engaged and maintain proper form throughout the exercise."
    ]
  },
  "Medicine Ball Chest Pass (against wall or with partner)": {
    purpose: "Builds power in chest, shoulders, and triceps for powerful passes and quick ball movement.",
    execution: [
      "• Stand facing a wall or partner, holding medicine ball at chest.",
      "• Step forward slightly, explosively throw the ball out from your chest as far/hard as possible.",
      "• Catch the ball (or let it rebound), reset, and repeat for reps."
    ]
  },
  "Overhead Medicine Ball Toss": {
    purpose: "Trains with overhead power needed for outlet passes, rebounding, and blocking shots.",
    execution: [
      "• Stand feet shoulder-width, hold medicine ball overhead with elbows bent.",
      "• Extend and throw ball upward, releasing explosively from your shoulders and triceps.",
      "• Catch or let ball rebound, quickly return to starting position and repeat."
    ]
  },
  "Plyometric Dips": {
    purpose: "Builds explosive triceps and shoulder strength for quick shot releases, passing, and powering through contact.",
    execution: [
      "• Using parallel bars, lower into a dip.",
      "• Explosively press up, trying to 'pop' your hands off the bars briefly (advanced).",
      "• Lower with control, reset, repeat. (Use caution to protect shoulders.)"
    ]
  },
  "Plyometric Pull-Ups": {
    purpose: "Develops explosive upper back and biceps strength for powerful rebounds and hanging or finishing at the rim.",
    execution: [
      "• Start at a dead hang on a pull-up bar.",
      "• Pull up forcefully so your chin rises rapidly above the bar and your grip may briefly leave the bar (advanced: release bar and catch on descent).",
      "• Lower under control, repeat. Use only when proficient in pull-ups."
    ]
  },
  "Medicine Ball Slams": {
    purpose: "Builds total upper body explosiveness and core power for quick directional changes, hard passes, and energy transfer.",
    execution: [
      "• Stand with medicine ball lifted overhead.",
      "• Explosively slam the ball into the floor using full upper body and core power.",
      "• Catch ball on the bounce or reset, repeat quickly for reps."
    ]
  },
  "Plyometric Push-Ups on Elevated Surface (Depth Plyo Push-Up)": {
    purpose: "Increases explosive strength for chest and triceps when performing passes after landing or absorbing contact.",
    execution: [
      "• Place hands on two boxes or platforms, in push-up position.",
      "• Drop hands to the ground between the boxes, absorb, immediately explode back onto the boxes.",
      "• Repeat in a controlled rhythm."
    ]
  },
  // Lower Body Plyometric Workout
  "Box Jumps": {
    purpose: "Develops explosive vertical power for rebounding, blocking shots, and sprinting down the court.",
    execution: [
      "• Stand about a foot away from a sturdy box, feet shoulder-width apart.",
      "• Lower into a quarter squat, swing your arms back, and then explosively jump upward and forward so both feet land softly on the box.",
      "• Land with knees slightly bent to absorb impact, stand tall, then step safely down.",
      "• Focus on smooth landings—don't let your knees cave inward."
    ]
  },
  "Depth Jumps": {
    purpose: "Trains explosive power and reactive strength for rapid jumping after quick landings, simulating game movements like going up for rebounds after landing.",
    execution: [
      "• Stand on a low box (12–24 inches).",
      "• Step off (do NOT jump off), land on both feet, and immediately jump as high as possible upon landing.",
      "• Minimize ground contact time—react quickly from landing to jump."
    ]
  },
  "Lateral Bounds": {
    purpose: "Builds lateral power and stability, crucial for defensive slides, changing directions, and fast cuts.",
    execution: [
      "• Stand on one leg with a slightly bent knee.",
      "• Push off powerfully to the side, landing on the opposite leg.",
      "• Absorb impact with a bent knee and controlled trunk.",
      "• Immediately bound back in the other direction and repeat."
    ]
  },
  "Alternate Leg Bounding": {
    purpose: "Improves running and jumping coordination, leg power, and stride length—important for driving to the basket or sprinting in transition.",
    execution: [
      "• Start jogging; launch off one leg powerfully, driving the opposite knee forward and up.",
      "• Land on the opposite leg and immediately bound forward off that leg, repeating the movement.",
      "• Focus on covering ground and maintaining rhythm."
    ]
  },
  "Single Leg Hops": {
    purpose: "Builds single-leg power and stability, essential for jumping off one foot—key for layups, drives, and off-balance finishes.",
    execution: [
      "• Stand on one foot, arms at sides.",
      "• Use your leg to hop forward (or sideways), controlling your landing with a bent knee.",
      "• Repeat for reps, then switch legs."
    ]
  },
  "Step Ups": {
    purpose: "Builds functional leg strength and single-leg balance for powerful pushes and safe landings.",
    execution: [
      "• Stand facing a box or bench and place one foot on it.",
      "• Push through your elevated foot to stand up, bringing the opposite knee up for an extra challenge.",
      "• Step down under control and switch sides.",
      "• Option: Hold dumbbells for additional resistance."
    ]
  },
  "Squat Jumps": {
    purpose: "Build explosive vertical power for rebounding, finishing above defenders, and quick takeoffs.",
    execution: [
      "• Stand with feet shoulder-width apart and squat until thighs are parallel.",
      "• Swing arms and jump up powerfully, landing softly back in a squat.",
      "• Immediately repeat for reps, minimizing ground contact time."
    ]
  },
  "Split Squat Jumps (aka Lunge Jumps)": {
    purpose: "Improve single-leg explosiveness and quickness important for drives, layups, and changing direction.",
    execution: [
      "• Start in a lunge position.",
      "• Jump up explosively and switch leg positions mid-air.",
      "• Land softly in a reverse lunge and repeat, moving quickly and safely."
    ]
  },
  "Step Up Jumps (Alternating Step-Up Jumps)": {
    purpose: "Build single-leg strength and coordination for pushing off on drives and jumping off one leg.",
    execution: [
      "• Stand facing a box and place one foot on it.",
      "• Jump up, switch feet in the air, and land with the other foot on the box.",
      "• Keep chest upright and repeat with smooth transitions."
    ]
  },
  "Zigzag or Agility Ladder Hops": {
    purpose: "Enhance coordination and quickness for directional changes during play.",
    execution: [
      "• Stand beside an agility ladder.",
      "• Hop quickly side-to-side down the length of the ladder.",
      "• Minimize double hopping and ground contact, keeping torso stable."
    ]
  }
};

// Show workout details function - MODAL VERSION
function showWorkoutDetails() {
  const specificWorkout = document.getElementById('workoutSelect').value;
  
  if (!specificWorkout) {
    alert('Please select a specific workout first.');
    return;
  }
  
  const desc = workoutDescriptions[specificWorkout];
  
  if (!desc) {
    alert('No details available for this workout yet.');
    return;
  }

  const html = `
    <div class="workout-details-modal">
      <div class="header">
        <div class="header-title">Workout Details</div>
        <span class="header-pill">${specificWorkout}</span>
      </div>
      <div class="main">
        <section class="workout-section">
          <div class="workout-num">1</div>
          <div class="workout-details">
            <h2>${specificWorkout}</h2>
            <h3>Purpose</h3>
            <p>${desc.purpose}</p> 
            <h3>Execution</h3>
            <ul class="execution-list">
              ${desc.execution.map(step => `<li>${step.replace('• ', '')}</li>`).join('')}
            </ul>
          </div>
        </section>
      </div>
    </div>
  `;

  // Create and show modal
  showDetailsModal(html, 'workout-details-modal');
}

// Save Workout into Training Activities
function saveWorkout() {
    const weekId = document.getElementById('wkWeekId').value;
    const dayName = document.getElementById('wkDayName').value;
    const activityDate = document.getElementById('workoutDate').value;
    const workoutType = document.getElementById('drillCategoryFilter').value;
    const specificWorkout = document.getElementById('workoutSelect').value;
    const duration = document.getElementById('workoutDuration').value;
    const sets = document.getElementById('workoutSets').value;
    const location = document.getElementById('workoutLocation').value;
    const callTime = document.getElementById('workoutCallTime').value;
    const description = document.getElementById('workoutDescription').value;

    // Validation
    if (!weekId && !activityDate) {
        alert('Please select a date for the workout.');
        return;
    }

    if (!workoutType || !specificWorkout) {
        alert('Please fill in all required fields (Workout Type and Specific Workout are required).');
        return;
    }

    if (!duration || !sets || !location || !callTime || !description) {
        alert('Please fill in all required fields (Duration, Sets, Location, Call Time, and Instructions are required).');
        return;
    }

    // Check if date is in the past
    if (activityDate) {
        const selectedDate = new Date(activityDate + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            alert('Cannot add workouts to past dates!');
            return;
        }
    }

    // Check if the day is a rest day
    if (activityDate) {
        const dayElement = document.querySelector(`[data-date="${activityDate}"]`);
        if (dayElement) {
            const isRestDay = dayElement.querySelector('.activity-icon.text-green-600');
            if (isRestDay) {
                alert('Cannot add workouts to a rest day! Please remove the rest day first.');
                return;
            }
        }
    }

    // Check for duplicate workout on the same day
    if (activityDate) {
        const dayElement = document.querySelector(`[data-date="${activityDate}"]`);
        if (dayElement) {
            // Get all activity details buttons to check existing workouts
            const detailsButtons = dayElement.querySelectorAll('.details-btn');
            let isDuplicate = false;
            
            detailsButtons.forEach(btn => {
                const onclick = btn.getAttribute('onclick');
                if (onclick && onclick.includes(specificWorkout)) {
                    isDuplicate = true;
                }
            });
            
            if (isDuplicate) {
                alert(`"${specificWorkout}" already exists on this day! Please choose a different workout.`);
                return;
            }
        }
    }

    const title = specificWorkout;
    const fullDescription = `Type: ${workoutType} | Duration: ${duration} mins | Sets: ${sets} | Location: ${location} | Call Time: ${callTime} | Instructions: ${description}`;

    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="saveactivity" value="1">
        <input type="hidden" name="weekid" value="${weekId}">
        <input type="hidden" name="dayname" value="${dayName}">
        <input type="hidden" name="type" value="Workout">
        <input type="hidden" name="activitydate" value="${activityDate}">
        <input type="hidden" name="title" value="${title.replace(/"/g, '&quot;')}">
        <input type="hidden" name="description" value="${fullDescription.replace(/"/g, '&quot;')}">
        <input type="hidden" name="calltime" value="${callTime}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Add event listener for the workout details button
document.addEventListener('DOMContentLoaded', function() {
  const workoutDetailsBtn = document.getElementById('workoutDetailsBtn');
  if (workoutDetailsBtn) {
    workoutDetailsBtn.addEventListener('click', showWorkoutDetails);
  }
  
  // Inject styles when the page loads
  if (!document.getElementById('workoutDetailsModalStyles')) {
    const workoutModalStyles = `
    <style id="workoutDetailsModalStyles">
    .workout-details-modal .header {
      background: #21808d;
      padding: 30px 25px 25px;
      text-align: center;
      position: relative;
      border-radius: 12px 12px 0 0;
    }

    .workout-details-modal .header-title {
      color: #fff;
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 12px;
      letter-spacing: 0.02em;
    }

    .workout-details-modal .header-pill {
      display: inline-block;
      background: rgba(255,255,255,0.2);
      color: #fff;
      font-weight: 500;
      border-radius: 20px;
      padding: 6px 20px;
      font-size: 1rem;
      backdrop-filter: blur(10px);
    }

    .workout-details-modal .main {
      padding: 25px;
      background: #fcfdf7;
    }

    .workout-details-modal .workout-section {
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      display: flex;
      align-items: flex-start;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      border-left: 5px solid #21808d;
    }

    .workout-details-modal .workout-num {
      width: 45px;
      height: 45px;
      background: #21808d;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      font-weight: 700;
      margin-right: 20px;
      flex-shrink: 0;
    }

    .workout-details-modal .workout-details h2 {
      margin: 0 0 12px 0;
      color: #21576b;
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: 0.02em;
    }

    .workout-details-modal .workout-details h3 {
      margin: 18px 0 8px 0;
      color: #21808d;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .workout-details-modal .workout-details p {
      margin: 0 0 12px 0;
      color: #14343b;
      font-size: 1rem;
      line-height: 1.6;
    }

    .workout-details-modal .execution-list {
      margin: 8px 0;
      padding-left: 0;
    }

    .workout-details-modal .execution-list li {
      color: #14343b;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 6px;
      list-style-type: none;
      position: relative;
      padding-left: 18px;
    }

    .workout-details-modal .execution-list li:before {
      content: "•";
      color: #21808d;
      font-weight: bold;
      position: absolute;
      left: 0;
      font-size: 1.2rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .workout-details-modal .workout-section {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
      }
      
      .workout-details-modal .workout-num {
        margin-bottom: 12px;
        margin-right: 0;
      }
      
      .workout-details-modal .workout-details h2 {
        font-size: 1.3rem;
      }
      
      .workout-details-modal .workout-details h3 {
        font-size: 1.1rem;
      }
    }

    @media (max-width: 480px) {
      .workout-details-modal .header {
        padding: 25px 20px 20px;
      }
      
      .workout-details-modal .header-title {
        font-size: 1.7rem;
      }
      
      .workout-details-modal .main {
        padding: 20px;
      }
      
      .workout-details-modal .workout-section {
        padding: 18px;
      }
    }
    </style>
    `;
    document.head.insertAdjacentHTML('beforeend', workoutModalStyles);
  }
});