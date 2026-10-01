<?php
// get_workout_details.php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo "<p class='text-red-500'>Please log in to view workout details.</p>";
    exit;
}

if (!isset($_GET['activity_id'])) {
    echo "<p class='text-red-500'>No workout specified.</p>";
    exit;
}

$activity_id = intval($_GET['activity_id']);

// COMPLETE Workout descriptions from the provided data
$workoutDescriptions = [
    // Core Workout
    "Toe Touches" => [
        "purpose" => "Stretch and activate hamstrings/lower core, aids flexibility and trunk mobility for shooting, cutting, and stability in motion.",
        "execution" => [
            "Lie flat on your back with legs extended straight up so feet are above hips.",
            "Reach hands toward toes by curling the upper body and lifting shoulders off the floor, focusing on the abs.",
            "Pause briefly at peak contraction, then lower with control."
        ]
    ],
    "Suitcase Crunches" => [
        "purpose" => "Improves abdominal strength for body control, strong posture, and absorbing contact.",
        "execution" => [
            "Lie flat with legs straight and arms overhead.",
            "Simultaneously bring knees toward chest and lift torso, reaching hands to meet shins above midsection as if folding.",
            "Hold briefly, then return to the starting position fully extended."
        ]
    ],
    "Sit-Ups" => [
        "purpose" => "Builds rectus abdominis strength, contributing to trunk flexion, force transfer, and resisting defenders.",
        "execution" => [
            "Lie on your back with knees bent and feet on the floor.",
            "Cross arms over chest or keep them at your sides.",
            "Engage abs to curl the torso up to the thighs while exhaling on the way up.",
            "Lower with control until shoulders touch the ground."
        ]
    ],
    "Planks" => [
        "purpose" => "Trains overall core endurance and stability crucial for balance, coordination, and transferring lower/upper limb force in basketball.",
        "execution" => [
            "Place forearms on the floor with elbows under shoulders and feet hip-width apart.",
            "Keep the body in a straight line, avoiding hips dropping or raising.",
            "Brace the core and maintain position for the desired time (start with 20–30 seconds)."
        ]
    ],
    "Stir the Pot (on Stability Ball)" => [
        "purpose" => "Challenges anti-extension and anti-rotation stability for better defensive stance and strong post moves.",
        "execution" => [
            "Start in a forearm plank with forearms on a stability ball.",
            "Brace the core and make slow, controlled circles with the forearms, keeping hips steady.",
            "Perform 6–10 circles each direction, rest, and repeat."
        ]
    ],
    "Medicine Ball Slams" => [
        "purpose" => "Builds explosive core/passing power and quick movement transfer for rebounding and outlet passes.",
        "execution" => [
            "Stand upright holding a medicine ball overhead with arms fully extended.",
            "Forcefully slam the ball to the floor while crunching the core and driving the hips.",
            "Retrieve the ball and reset quickly for the next repetition."
        ]
    ],
    "Med Ball Side Toss" => [
        "purpose" => "Improves rotational core power for passes, shooting, and rapid directional changes.",
        "execution" => [
            "Stand sideways to a wall with feet shoulder-width apart and knees slightly bent.",
            "Hold the medicine ball at the hip, rotate the torso, and throw the ball forcefully into the wall using core rotation.",
            "Catch or retrieve the ball and repeat on both sides."
        ]
    ],
    "Hanging Straight Leg Raises" => [
        "purpose" => "Strengthens lower abs and hip flexors for high jumps, sprints, and body control in the air.",
        "execution" => [
            "Hang from a pull-up bar with arms fully extended and body straight.",
            "Brace the core and lift legs together in front (knees straight) until legs reach parallel to the floor or higher.",
            "Lower legs with control and avoid swinging."
        ]
    ],
    "Windshield Wipers" => [
        "purpose" => "Develops oblique and core rotational strength, supporting twisting motions in layups and defense.",
        "execution" => [
            "Hang from a bar (or lie on your back with arms out for a beginner option) with legs extended upward.",
            "Keeping legs straight, move them side-to-side in a wide, controlled arc like windshield wipers.",
            "Maintain slow tempo and minimize swinging."
        ]
    ],
    "Hyperextensions" => [
        "purpose" => "Strengthens lower back and glutes for upright posture and safe landings after jumps.",
        "execution" => [
            "Set up on a back extension bench with the pad just below the hips.",
            "Cross arms over chest or place hands behind head.",
            "Lower the torso by hinging at the hips until the body is nearly perpendicular to the legs.",
            "Raise the torso until the body is straight without forcing into hyperextension."
        ]
    ],
    "Clamshells (each side)" => [
        "purpose" => "Activates glute medius and hip stabilizers for defensive stance, lateral movement, and knee health.",
        "execution" => [
            "Lie on your side with knees bent at 90° and hips stacked.",
            "Keep feet together and lift the top knee up without rotating the torso, focusing on the glute.",
            "Lower the knee with control and repeat on each side."
        ]
    ],
    "Fire Hydrant (each side)" => [
        "purpose" => "Targets glutes and hip rotators, aiding lateral explosiveness and stability.",
        "execution" => [
            "Kneel on all fours with hands under shoulders and knees under hips.",
            "Lift one knee out to the side while keeping the knee bent; pause briefly.",
            "Lower with control while keeping the core braced and hips square."
        ]
    ],
    "Side Plank" => [
        "purpose" => "Builds anti-lateral flexion strength vital for holding position, absorbing contact, and balance during layups.",
        "execution" => [
            "Lie on your side and support the body on the forearm (elbow under shoulder) and the side of the foot.",
            "Stack hips and shoulders so the body forms a straight line.",
            "Hold for time, then switch sides as needed."
        ]
    ],
    "Russian Twists" => [
        "purpose" => "Trains rotational power in the trunk, necessary for passing, shooting, and quick direction changes.",
        "execution" => [
            "Sit with knees bent and feet lifted off the floor (or keep feet down for an easier version).",
            "Lean back slightly and hold a weight or ball at the chest.",
            "Rotate the torso to one side and touch the weight to the floor, then rotate to the other side and repeat."
        ]
    ],
    "Dead Bug" => [
        "purpose" => "Teaches core stabilization and cross-body coordination for athletic movements and injury prevention.",
        "execution" => [
            "Lie on your back with arms straight up and knees bent to 90° over the hips.",
            "Lower one arm and the opposite leg toward the floor while keeping the lower back flat.",
            "Return to the start position, switch sides, and repeat with controlled reps."
        ]
    ],

    // Upper Body Weight Training
    "Bench press" => [
        "purpose" => "Builds chest, shoulders, and triceps strength for stronger passes, finishes through contact, box outs, and absorbing contact at the rim. Upper-body strength supports rebounding and shielding the ball.",
        "execution" => [
            "Lie on a bench with feet planted on the floor.",
            "Maintain a light arch in your lower back and pull your shoulder blades back and down.",
            "Grip the bar slightly wider than your shoulders.",
            "Lower the bar to your mid-chest, keeping your forearms vertical.",
            "Press the bar back up to the starting position, keeping your wrists stacked over your elbows."
        ]
    ],
    "Bicep curl" => [
        "purpose" => "Improves elbow flexor strength for securing rebounds, strong rip-throughs, and resisting ball strips when protecting the ball. Use as an accessory exercise.",
        "execution" => [
            "Stand tall with your core braced.",
            "Keep your elbows close to your sides throughout the movement.",
            "Curl the weight up without swinging your body.",
            "Squeeze your biceps at the top of the movement.",
            "Lower the weight under control."
        ]
    ],
    "Chest fly" => [
        "purpose" => "Trains horizontal adduction to aid chest strength and control when extending arms for passes and shielding. Use light-to-moderate loads.",
        "execution" => [
            "Lie on a bench with dumbbells held over your chest.",
            "Maintain a slight bend in your elbows.",
            "Open your arms in a wide, controlled arc until your elbows are level with your torso.",
            "Squeeze your chest to 'hug' the weights back to the starting position.",
            "Avoid overstretching at the bottom of the movement."
        ]
    ],
    "Front raise (front delt raise)" => [
        "purpose" => "Strengthens anterior delts for ball pickups, straight-arm contests, and overhead ball control.",
        "execution" => [
            "Stand tall, holding dumbbells with a thumbs-up or neutral grip.",
            "Raise the weights to shoulder height without using momentum.",
            "Pause briefly at the top.",
            "Lower the weights slowly and with control."
        ]
    ],
    "Overhead press" => [
        "purpose" => "Builds shoulder and triceps strength for strong overhead passing, rebounding at full reach, and resisting contact with arms extended.",
        "execution" => [
            "Stand with the bar or dumbbells at shoulder height.",
            "Squeeze your glutes and brace your core.",
            "Press the weight overhead, moving your head slightly backward to clear a path.",
            "Lock out with your biceps by your ears.",
            "Control the weight on the way down."
        ]
    ],
    "Pullover / overhead skull crusher" => [
        "purpose" => "Develops lats and serratus strength for overhead control, rebounding reach, and core connection.",
        "execution" => [
            "Lie on a bench with a dumbbell held over your chest.",
            "Maintain a slight bend in your elbows.",
            "Reach the dumbbell back behind your head until you feel a stretch in your lats.",
            "Pull the weight back to the start using your lats, without flaring your ribs."
        ]
    ],
    "Rear delt raise" => [
        "purpose" => "Trains posterior delts and upper back for posture, shooting mechanics, and deceleration.",
        "execution" => [
            "Hinge at your hips, keeping your back flat.",
            "Let the dumbbells hang under your shoulders.",
            "Raise the weights out and slightly back, leading with your elbows.",
            "Squeeze your shoulder blades together gently at the top.",
            "Lower the weights with control."
        ]
    ],
    "Seated row" => [
        "purpose" => "Builds horizontal pulling strength for battling for position, securing rebounds, and balancing pressing volume.",
        "execution" => [
            "Sit tall on the machine with your chest up.",
            "Pull the handle to your lower ribs.",
            "Keep your shoulders down and back during the pull.",
            "Pause briefly when the handle touches your torso.",
            "Extend your arms fully without rounding your back."
        ]
    ],
    "Triceps extension" => [
        "purpose" => "Strengthens triceps for shooting extension, strong passes, and finishing through contact.",
        "execution" => [
            "Keep your upper arm stable and completely vertical.",
            "Hinge only at the elbow to lower and raise the weight.",
            "Use a full range of motion without letting your shoulder move.",
            "Control the weight on the eccentric (lowering) portion."
        ]
    ],
    "Wide-grip pull-up" => [
        "purpose" => "Develops lats, upper back, and grip for vertical pulling, key for rebounding and overhead control.",
        "execution" => [
            "Start from a dead hang with arms fully extended.",
            "Set your shoulders by pulling your shoulder blades down and back.",
            "Pull your chest toward the bar without craning your neck.",
            "Lower your body with control back to the dead hang position."
        ]
    ],
    "Shoulder press" => [
        "purpose" => "Builds shoulder and triceps strength for strong overhead passing, rebounding at full reach, and resisting contact with arms extended.",
        "execution" => [
            "Stand with the bar or dumbbells at shoulder height.",
            "Squeeze your glutes and brace your core.",
            "Press the weight overhead, moving your head slightly backward to clear a path.",
            "Lock out with your biceps by your ears.",
            "Control the weight on the way down."
        ]
    ],
    "Incline press" => [
        "purpose" => "Targets upper chest and anterior delts for angled pushes like contested finishes.",
        "execution" => [
            "Set a bench to a 30–45° angle.",
            "Lower the dumbbells or bar to your upper chest.",
            "Keep your forearms vertical throughout the movement.",
            "Press the weight up, keeping your shoulders down."
        ]
    ],
    "High row" => [
        "purpose" => "Emphasizes upper back and rear delts for posture and contesting shots.",
        "execution" => [
            "On a cable or machine, pull the handles with your elbows high and wide.",
            "Pull toward your upper ribs or lower chest.",
            "Keep your shoulders down; avoid shrugging.",
            "Return the weight slowly to the start."
        ]
    ],
    "Chest press (machine)" => [
        "purpose" => "Provides safer, stable pressing to build pushing strength with less fatigue.",
        "execution" => [
            "Adjust the seat so the handles align with your mid-chest.",
            "Keep your feet planted on the floor.",
            "Press the handles forward without locking your elbows out hard.",
            "Control the weight back to a light stretch."
        ]
    ],
    "Incline row" => [
        "purpose" => "Chest-supported row that builds upper back strength for rebounding and defense with low-back stress.",
        "execution" => [
            "Lie prone on an incline bench, chest against the pad.",
            "Pull the dumbbells toward your lower chest.",
            "Squeeze your shoulder blades together at the top.",
            "Lower the weights fully, allowing your shoulders to stretch forward."
        ]
    ],
    "Inverted row" => [
        "purpose" => "Bodyweight horizontal pull that builds scapular control and trunk stiffness.",
        "execution" => [
            "Set a bar at approximately waist height.",
            "Grip the bar with an overhand grip, hands wider than shoulders.",
            "Keep your body straight from your heels to your head.",
            "Pull your chest to the bar.",
            "Pause, then lower yourself with control."
        ]
    ],
    "Lat pulldown" => [
        "purpose" => "Builds lats and scapular strength, a good alternative to pull-ups.",
        "execution" => [
            "Sit tall on the machine with a slight lean back.",
            "Pull the bar to your upper chest by driving your elbows down.",
            "Keep your chest up and shoulders down.",
            "Control the bar back to full elbow extension."
        ]
    ],
    "Triceps dip" => [
        "purpose" => "Compound movement for triceps, chest, and shoulders for finishing power.",
        "execution" => [
            "On parallel bars, set your shoulders down and back.",
            "Lower your body until your upper arms are at least parallel to the floor.",
            "Press back up to the starting position without flaring your elbows excessively."
        ]
    ],
    "Upright row" => [
        "purpose" => "Targets upper traps and delts for shoulder girdle strength in rebounding.",
        "execution" => [
            "Use a grip slightly wider than your shoulders.",
            "Pull the bar or dumbbells to your upper ribs/lower chest.",
            "Keep your elbows just above your wrists.",
            "Avoid yanking the weight; use a controlled motion."
        ]
    ],
    "Seated row (machine) if not covered above" => [
        "purpose" => "Machine variation that focuses on scapular retraction for posture.",
        "execution" => [
            "Maintain a neutral spine throughout the movement.",
            "Pull the handles to your lower ribs.",
            "Pause and squeeze your shoulder blades together.",
            "Control the weight forward without rounding your back."
        ]
    ],
    "Wide-grip pull-up tips" => [
        "purpose" => "Develops lats, upper back, and grip for vertical pulling, key for rebounding and overhead control.",
        "execution" => [
            "Start from a dead hang with arms fully extended.",
            "Set your shoulders by pulling your shoulder blades down and back.",
            "Pull your chest toward the bar without craning your neck.",
            "Lower your body with control back to the dead hang position.",
            "Use assistance bands if needed to maintain proper form."
        ]
    ],
    "Landmine press" => [
        "purpose" => "Shoulder-friendly angled press that builds diagonal pushing power.",
        "execution" => [
            "Anchor one end of a barbell in a landmine base or corner.",
            "Hold the sleeve end of the bar at your chest.",
            "Assume a staggered stance for stability.",
            "Press the bar forward and upward in an arc.",
            "Control the bar back to the start position."
        ]
    ],

    // Lower Body Weight Training
    "Calf Raise" => [
        "purpose" => "Builds calf strength and ankle stability, which help with explosive jumps, sprinting, quick direction changes, and injury prevention (such as ankle sprains).",
        "execution" => [
            "Stand with the balls of your feet on the edge of a step, heels hanging off, or flat on the floor.",
            "Hold onto something for balance.",
            "Press through your toes to raise your heels as high as you can, pause at the top.",
            "Lower your heels slowly below the step (for a full stretch) or back to the floor.",
            "Keep the movement slow and controlled; avoid bouncing."
        ]
    ],
    "Front Squat" => [
        "purpose" => "Strengthens quads, glutes, and core—essential for vertical jumping, sprinting, and stable landings.",
        "execution" => [
            "Stand with feet shoulder-width. Hold a barbell on the front of your shoulders, elbows high and parallel to the ground.",
            "Keep your torso upright, brace your core.",
            "Squat down, keeping knees tracking over toes, until thighs are parallel or lower.",
            "Drive through your heels to stand up, maintaining core bracing and upright posture."
        ]
    ],
    "Hamstring Curl" => [
        "purpose" => "Targets the hamstrings, which are crucial for sprinting speed, deceleration, landing control, and preventing knee injuries.",
        "execution" => [
            "Lie face down on a leg curl machine or with feet hooked under a resistance band or stability ball.",
            "Flex your knees, pulling your feet toward your glutes against resistance.",
            "Pause at the top, then lower slowly to full extension.",
            "Move in a controlled manner to maximize activation and protect the knees."
        ]
    ],
    "Hip Adduction" => [
        "purpose" => "Strengthens inner thigh muscles (adductors) for lateral movement, defensive shuffling, and stabilization during quick changes of direction.",
        "execution" => [
            "Sit on a hip adduction machine, positioning pads on the inside of your knees or thighs.",
            "Squeeze your legs together against the resistance.",
            "Pause and slowly return to the starting position, resisting the movement back.",
            "Maintain upright posture, do not lean forward or backward during the movement."
        ]
    ],
    "Leg Extension" => [
        "purpose" => "Isolates and strengthens the quadriceps for explosive sprints, vertical jumps, and powerful knee extension.",
        "execution" => [
            "Sit on the leg extension machine with legs under the pad, feet pointing forward.",
            "Extend your knees and lift the weight until your legs are straight but not locked.",
            "Pause, then lower slowly back to the starting position.",
            "Avoid using excessive weight to prevent knee strain."
        ]
    ],
    "Lower Back Extension" => [
        "purpose" => "Strengthens the lower back and glutes, which are important for maintaining posture, absorbing force on landings, and preventing lower-back injuries.",
        "execution" => [
            "Position yourself on a hyperextension bench, feet secure, pad just below your hips.",
            "Cross arms or place hands behind head.",
            "Keeping your back neutral, lower your torso until you feel a slight stretch in your hamstrings.",
            "Contract your glutes and lower back to raise your torso to the starting position; avoid excessive hyperextension."
        ]
    ],
    "Dumbbell Lunge" => [
        "purpose" => "Builds single-leg strength, balance, and coordination for jumping, driving to the basket, and stable landings.",
        "execution" => [
            "Stand with a dumbbell in each hand, arms at sides.",
            "Step forward with one leg, lowering hips until both knees are bent at 90° (back knee just above floor).",
            "Keep your torso upright and core engaged.",
            "Push through your front foot to return to standing, then alternate legs."
        ]
    ],
    "Glute Ham Raises" => [
        "purpose" => "Strong hamstrings and glutes support sprinting, jumping, and knee stability, reducing injury risk.",
        "execution" => [
            "Kneel on a glute-ham developer machine with ankles secured.",
            "From upright, slowly lower your torso by straightening your knees and hip, keeping torso and thighs straight.",
            "Use your hamstrings to resist as you lower, then contract hamstrings and glutes to return to the start.",
            "Use hands for assistance if needed; avoid dropping quickly."
        ]
    ],
    "Hip Abduction" => [
        "purpose" => "Trains outer thigh/hip muscles for lateral movement, defensive slides, and hip stability.",
        "execution" => [
            "Sit on a hip abduction machine, pads on the outside of your knees or thighs.",
            "Press your legs outward against resistance.",
            "Pause at full abduction, then return slowly in control.",
            "Stay upright and avoid leaning."
        ]
    ],
    "Leg Press" => [
        "purpose" => "Compound movement to strengthen quads, glutes, and hamstrings for overall lower-body power (jumping, sprinting, shuffling).",
        "execution" => [
            "Sit on the leg press machine, feet shoulder-width on platform.",
            "Unlock safety handles, lower platform toward your chest by bending knees (keep heels on platform).",
            "Stop when knees are at 90° or slightly less.",
            "Press through heels to extend legs, but don't lock knees at top."
        ]
    ],
    "Romanian Deadlift (RDL)" => [
        "purpose" => "Hamstring and glute strength, vital for explosive sprints, jumps, and injury prevention.",
        "execution" => [
            "Stand tall holding barbell/dumbbells in front of thighs.",
            "Soften knees, hinge hips back, lowering weights while keeping back flat and chest up.",
            "Lower until hamstrings feel a stretch (bar close to legs), then drive hips forward to return to standing.",
            "Avoid rounding your lower back."
        ]
    ],
    "Split Squat" => [
        "purpose" => "Develops single-leg strength, balance, and hip/knee stability for powerful take-offs and deceleration.",
        "execution" => [
            "Stand with one foot in front and other behind (about two-three feet apart).",
            "Lower back knee to the floor, keeping front knee above ankle and torso upright.",
            "Press through front heel to rise up, keeping core engaged.",
            "Complete all reps on one side, then switch."
        ]
    ],

    // Upper Body Plyometric Workout
    "Plyometric Push-Ups / Push-Up Clap" => [
        "purpose" => "Develops explosiveness in chest, shoulders, and triceps for powerful passes, contact finishes, and fast shot releases.",
        "execution" => [
            "Start in a traditional push-up position with your hands slightly wider than shoulder-width apart and your body in a straight line from head to toe.",
            "Lower your chest toward the ground by bending your elbows, keeping them at a 45-degree angle to your body.",
            "Push explosively through your hands, propelling your upper body off the ground.",
            "In mid-air, quickly bring your hands back to the starting position, ready to absorb the impact.",
            "Land softly with your elbows slightly bent to cushion the landing.",
            "Immediately go into the next repetition, repeating the explosive push-off and soft landing.",
            "Keep your core engaged and maintain proper form throughout the exercise."
        ]
    ],
    "Medicine Ball Chest Pass (against wall or with partner)" => [
        "purpose" => "Builds power in chest, shoulders, and triceps for powerful passes and quick ball movement.",
        "execution" => [
            "Stand facing a wall or partner, holding medicine ball at chest.",
            "Step forward slightly, explosively throw the ball out from your chest as far/hard as possible.",
            "Catch the ball (or let it rebound), reset, and repeat for reps."
        ]
    ],
    "Overhead Medicine Ball Toss" => [
        "purpose" => "Trains with overhead power needed for outlet passes, rebounding, and blocking shots.",
        "execution" => [
            "Stand feet shoulder-width, hold medicine ball overhead with elbows bent.",
            "Extend and throw ball upward, releasing explosively from your shoulders and triceps.",
            "Catch or let ball rebound, quickly return to starting position and repeat."
        ]
    ],
    "Plyometric Dips" => [
        "purpose" => "Builds explosive triceps and shoulder strength for quick shot releases, passing, and powering through contact.",
        "execution" => [
            "Using parallel bars, lower into a dip.",
            "Explosively press up, trying to 'pop' your hands off the bars briefly (advanced).",
            "Lower with control, reset, repeat. (Use caution to protect shoulders.)"
        ]
    ],
    "Plyometric Pull-Ups" => [
        "purpose" => "Develops explosive upper back and biceps strength for powerful rebounds and hanging or finishing at the rim.",
        "execution" => [
            "Start at a dead hang on a pull-up bar.",
            "Pull up forcefully so your chin rises rapidly above the bar and your grip may briefly leave the bar (advanced: release bar and catch on descent).",
            "Lower under control, repeat. Use only when proficient in pull-ups."
        ]
    ],
    "Medicine Ball Slams" => [
        "purpose" => "Builds total upper body explosiveness and core power for quick directional changes, hard passes, and energy transfer.",
        "execution" => [
            "Stand with medicine ball lifted overhead.",
            "Explosively slam the ball into the floor using full upper body and core power.",
            "Catch ball on the bounce or reset, repeat quickly for reps."
        ]
    ],
    "Plyometric Push-Ups on Elevated Surface (Depth Plyo Push-Up)" => [
        "purpose" => "Increases explosive strength for chest and triceps when performing passes after landing or absorbing contact.",
        "execution" => [
            "Place hands on two boxes or platforms, in push-up position.",
            "Drop hands to the ground between the boxes, absorb, immediately explode back onto the boxes.",
            "Repeat in a controlled rhythm."
        ]
    ],

    // Lower Body Plyometric Workout
    "Box Jumps" => [
        "purpose" => "Develops explosive vertical power for rebounding, blocking shots, and sprinting down the court.",
        "execution" => [
            "Stand about a foot away from a sturdy box, feet shoulder-width apart.",
            "Lower into a quarter squat, swing your arms back, and then explosively jump upward and forward so both feet land softly on the box.",
            "Land with knees slightly bent to absorb impact, stand tall, then step safely down.",
            "Focus on smooth landings—don't let your knees cave inward."
        ]
    ],
    "Depth Jumps" => [
        "purpose" => "Trains explosive power and reactive strength for rapid jumping after quick landings, simulating game movements like going up for rebounds after landing.",
        "execution" => [
            "Stand on a low box (12–24 inches).",
            "Step off (do NOT jump off), land on both feet, and immediately jump as high as possible upon landing.",
            "Minimize ground contact time—react quickly from landing to jump."
        ]
    ],
    "Lateral Bounds" => [
        "purpose" => "Builds lateral power and stability, crucial for defensive slides, changing directions, and fast cuts.",
        "execution" => [
            "Stand on one leg with a slightly bent knee.",
            "Push off powerfully to the side, landing on the opposite leg.",
            "Absorb impact with a bent knee and controlled trunk.",
            "Immediately bound back in the other direction and repeat."
        ]
    ],
    "Alternate Leg Bounding" => [
        "purpose" => "Improves running and jumping coordination, leg power, and stride length—important for driving to the basket or sprinting in transition.",
        "execution" => [
            "Start jogging; launch off one leg powerfully, driving the opposite knee forward and up.",
            "Land on the opposite leg and immediately bound forward off that leg, repeating the movement.",
            "Focus on covering ground and maintaining rhythm."
        ]
    ],
    "Single Leg Hops" => [
        "purpose" => "Builds single-leg power and stability, essential for jumping off one foot—key for layups, drives, and off-balance finishes.",
        "execution" => [
            "Stand on one foot, arms at sides.",
            "Use your leg to hop forward (or sideways), controlling your landing with a bent knee.",
            "Repeat for reps, then switch legs."
        ]
    ],
    "Step Ups" => [
        "purpose" => "Builds functional leg strength and single-leg balance for powerful pushes and safe landings.",
        "execution" => [
            "Stand facing a box or bench and place one foot on it.",
            "Push through your elevated foot to stand up, bringing the opposite knee up for an extra challenge.",
            "Step down under control and switch sides.",
            "Option: Hold dumbbells for additional resistance."
        ]
    ],
    "Squat Jumps" => [
        "purpose" => "Build explosive vertical power for rebounding, finishing above defenders, and quick takeoffs.",
        "execution" => [
            "Stand with feet shoulder-width apart and squat until thighs are parallel.",
            "Swing arms and jump up powerfully, landing softly back in a squat.",
            "Immediately repeat for reps, minimizing ground contact time."
        ]
    ],
    "Split Squat Jumps (aka Lunge Jumps)" => [
        "purpose" => "Improve single-leg explosiveness and quickness important for drives, layups, and changing direction.",
        "execution" => [
            "Start in a lunge position.",
            "Jump up explosively and switch leg positions mid-air.",
            "Land softly in a reverse lunge and repeat, moving quickly and safely."
        ]
    ],
    "Step Up Jumps (Alternating Step-Up Jumps)" => [
        "purpose" => "Build single-leg strength and coordination for pushing off on drives and jumping off one leg.",
        "execution" => [
            "Stand facing a box and place one foot on it.",
            "Jump up, switch feet in the air, and land with the other foot on the box.",
            "Keep chest upright and repeat with smooth transitions."
        ]
    ],
    "Zigzag or Agility Ladder Hops" => [
        "purpose" => "Enhance coordination and quickness for directional changes during play.",
        "execution" => [
            "Stand beside an agility ladder.",
            "Hop quickly side-to-side down the length of the ladder.",
            "Minimize double hopping and ground contact, keeping torso stable."
        ]
    ]
];

try {
    // Fetch workout information
    $stmt = $pdo->prepare("
        SELECT 
            ta.*, 
            td.day_name, 
            tw.week_number,
            w.workout_id,
            w.workout_type,
            w.focus_area,
            w.total_duration_minutes,
            w.intensity_level,
            w.workout_notes,
            w.warmup_instructions,
            w.cooldown_instructions
        FROM training_activities ta
        JOIN training_days td ON ta.day_id = td.day_id
        JOIN training_weeks tw ON td.week_id = tw.week_id
        LEFT JOIN workouts w ON ta.activity_id = w.activity_id
        WHERE ta.activity_id = ? AND ta.type = 'Workout'
    ");
    $stmt->execute([$activity_id]);
    $workout = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$workout) {
        echo "<div class='text-center text-red-600 py-8'>";
        echo "<p class='text-lg font-semibold'>❌ Workout Not Found</p>";
        echo "<p class='text-sm text-gray-600 mt-2'>The requested workout details are not available.</p>";
        echo "</div>";
        exit;
    }

    // Fetch workout exercises
    $stmt_exercises = $pdo->prepare("
        SELECT 
            we.*, 
            e.exercise_name, 
            e.description as exercise_description, 
            e.difficulty_level,
            e.muscle_group,
            e.equipment_required
        FROM workout_exercises we
        JOIN exercises e ON we.exercise_id = e.exercise_id
        WHERE we.workout_id = ?
        ORDER BY we.exercise_order ASC
    ");
    $stmt_exercises->execute([$workout['workout_id']]);
    $exercises = $stmt_exercises->fetchAll(PDO::FETCH_ASSOC);

    // Get detailed description from our array
    $workoutTitle = $workout['title'];
    $detailedDescription = isset($workoutDescriptions[$workoutTitle]) ? $workoutDescriptions[$workoutTitle] : null;

    // Display workout details
    echo "<div class='space-y-6'>";
    
    // Header Section
    echo "
    <div class='text-center border-b border-blue-200 pb-6'>
        <h2 class='text-2xl font-bold text-blue-800 mb-2'>🏋️ " . htmlspecialchars($workout['title']) . "</h2>
        <div class='flex flex-wrap justify-center gap-4 text-sm text-gray-600'>
            <span class='bg-blue-100 text-blue-700 px-3 py-1 rounded-full'>" . htmlspecialchars($workout['workout_type']) . "</span>
            <span class='bg-blue-100 text-blue-700 px-3 py-1 rounded-full'>" . htmlspecialchars($workout['focus_area']) . "</span>
            <span class='bg-blue-100 text-blue-700 px-3 py-1 rounded-full'>" . htmlspecialchars($workout['total_duration_minutes']) . " minutes</span>
            <span class='bg-blue-100 text-blue-700 px-3 py-1 rounded-full'>" . htmlspecialchars($workout['intensity_level']) . " Intensity</span>
        </div>
    </div>
    ";

    // Detailed Description Section (from workout_functions.js)
    if ($detailedDescription) {
        echo "
        <div class='bg-green-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-green-800 mb-3'>📖 Exercise Details</h4>
            <div class='space-y-4 text-sm'>
                <div>
                    <h5 class='font-semibold text-green-700 mb-2'>Purpose</h5>
                    <p class='text-gray-700'>" . htmlspecialchars($detailedDescription['purpose']) . "</p>
                </div>
                <div>
                    <h5 class='font-semibold text-green-700 mb-2'>Execution</h5>
                    <ul class='list-disc list-inside space-y-2 text-gray-700'>
        ";
        
        foreach ($detailedDescription['execution'] as $step) {
            echo "<li>" . htmlspecialchars($step) . "</li>";
        }
        
        echo "
                    </ul>
                </div>
            </div>
        </div>
        ";
    }

    // Workout Overview
    echo "
    <div class='grid grid-cols-1 md:grid-cols-2 gap-6'>
        <div class='bg-blue-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-blue-800 mb-3'>📋 Workout Overview</h4>
            <div class='space-y-3 text-sm'>
                <div><span class='font-semibold'>Week:</span> Week " . htmlspecialchars($workout['week_number']) . "</div>
                <div><span class='font-semibold'>Day:</span> " . htmlspecialchars($workout['day_name']) . "</div>
                <div><span class='font-semibold'>Type:</span> " . htmlspecialchars($workout['workout_type']) . "</div>
                <div><span class='font-semibold'>Focus Area:</span> " . htmlspecialchars($workout['focus_area']) . "</div>
                <div><span class='font-semibold'>Total Duration:</span> " . htmlspecialchars($workout['total_duration_minutes']) . " minutes</div>
                <div><span class='font-semibold'>Intensity:</span> " . htmlspecialchars($workout['intensity_level']) . "</div>
            </div>
        </div>
    ";

    // Warmup & Cooldown
    echo "
        <div class='bg-green-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-green-800 mb-3'>🔥 Warmup & Cooldown</h4>
            <div class='space-y-3 text-sm'>
                " . ($workout['warmup_instructions'] ? "<div><span class='font-semibold'>Warmup:</span> " . nl2br(htmlspecialchars($workout['warmup_instructions'])) . "</div>" : "<div class='text-gray-500'>No warmup instructions</div>") . "
                " . ($workout['cooldown_instructions'] ? "<div><span class='font-semibold'>Cooldown:</span> " . nl2br(htmlspecialchars($workout['cooldown_instructions'])) . "</div>" : "<div class='text-gray-500'>No cooldown instructions</div>") . "
            </div>
        </div>
    </div>
    ";

    // Workout Notes
    if ($workout['workout_notes']) {
        echo "
        <div class='bg-yellow-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-yellow-800 mb-3'>📝 Workout Notes</h4>
            <div class='text-sm'>" . nl2br(htmlspecialchars($workout['workout_notes'])) . "</div>
        </div>
        ";
    }

    // Exercises Section
    if (!empty($exercises)) {
        $totalExercises = count($exercises);
        $estimatedTime = $totalExercises * 10; // Rough estimate: 10 minutes per exercise
        
        echo "
        <div class='bg-purple-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-purple-800 mb-3'>💪 Exercises ($totalExercises exercises)</h4>
            <p class='text-sm text-gray-600 mb-4'>Estimated completion time: ~$estimatedTime minutes</p>
            
            <div class='space-y-4'>
        ";
        
        foreach ($exercises as $index => $exercise) {
            $setInfo = [];
            if ($exercise['sets']) $setInfo[] = $exercise['sets'] . " sets";
            if ($exercise['reps']) $setInfo[] = $exercise['reps'] . " reps";
            if ($exercise['rest_seconds']) $setInfo[] = $exercise['rest_seconds'] . "s rest";
            if ($exercise['weight_kg']) $setInfo[] = $exercise['weight_kg'] . "kg";
            if ($exercise['distance_meters']) $setInfo[] = $exercise['distance_meters'] . "m";
            if ($exercise['duration_seconds']) $setInfo[] = $exercise['duration_seconds'] . "s";
            
            $setInfoStr = implode(" • ", $setInfo);
            
            // Get detailed description for this specific exercise
            $exerciseDescription = isset($workoutDescriptions[$exercise['exercise_name']]) ? $workoutDescriptions[$exercise['exercise_name']] : null;
            
            echo "
            <div class='bg-white rounded-lg p-4 border border-purple-200'>
                <div class='flex justify-between items-start mb-2'>
                    <h5 class='font-semibold text-purple-800 text-lg'>" . ($index + 1) . ". " . htmlspecialchars($exercise['exercise_name']) . "</h5>
                    <span class='bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs font-medium'>" . htmlspecialchars($exercise['difficulty_level']) . "</span>
                </div>
                
                <div class='grid grid-cols-2 md:grid-cols-4 gap-2 mb-3 text-sm'>
                    <div><span class='font-medium'>Sets:</span> " . htmlspecialchars($exercise['sets'] ?? '—') . "</div>
                    <div><span class='font-medium'>Reps:</span> " . htmlspecialchars($exercise['reps'] ?? '—') . "</div>
                    <div><span class='font-medium'>Rest:</span> " . htmlspecialchars($exercise['rest_seconds'] ?? '—') . "s</div>
                    <div><span class='font-medium'>Muscle:</span> " . htmlspecialchars($exercise['muscle_group'] ?? '—') . "</div>
                </div>
                
                " . ($setInfoStr ? "<div class='text-sm text-gray-600 mb-2'><span class='font-medium'>Parameters:</span> $setInfoStr</div>" : "") . "
                " . ($exercise['exercise_description'] ? "<div class='text-sm text-gray-700 mb-2'>" . nl2br(htmlspecialchars($exercise['exercise_description'])) . "</div>" : "") . "
            ";
            
            // Add detailed exercise description if available
            if ($exerciseDescription) {
                echo "
                <div class='mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200'>
                    <h6 class='font-semibold text-blue-800 text-sm mb-2'>Exercise Details:</h6>
                    <p class='text-xs text-gray-700 mb-2'><strong>Purpose:</strong> " . htmlspecialchars($exerciseDescription['purpose']) . "</p>
                    <div class='text-xs text-gray-700'>
                        <strong>Execution:</strong>
                        <ul class='list-disc list-inside mt-1 space-y-1'>
                ";
                
                foreach ($exerciseDescription['execution'] as $step) {
                    echo "<li>" . htmlspecialchars($step) . "</li>";
                }
                
                echo "
                        </ul>
                    </div>
                </div>
                ";
            }
            
            echo "
                " . ($exercise['equipment_required'] ? "<div class='text-sm mt-2'><span class='font-medium'>Equipment:</span> " . htmlspecialchars($exercise['equipment_required']) . "</div>" : "") . "
            </div>
            ";
        }
        
        echo "
            </div>
        </div>
        ";
    } else {
        echo "
        <div class='bg-gray-50 rounded-lg p-4 text-center'>
            <p class='text-gray-500'>No exercises added to this workout yet.</p>
        </div>
        ";
    }

    echo "</div>"; // Close main space-y-6 div

} catch (PDOException $e) {
    echo "<div class='text-center text-red-600 py-8'>";
    echo "<p class='text-lg font-semibold'>❌ Database Error</p>";
    echo "<p class='text-sm text-gray-600 mt-2'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

?>