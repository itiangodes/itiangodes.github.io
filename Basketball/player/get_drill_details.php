<?php
// get_drill_details.php
include '../includes/db.php';
session_start();

if (!isset($_SESSION['username'])) {
    echo "<p class='text-red-500'>Please log in to view drill details.</p>";
    exit;
}

if (!isset($_GET['activity_id'])) {
    echo "<p class='text-red-500'>No drill specified.</p>";
    exit;
}

$activity_id = intval($_GET['activity_id']);

// COMPLETE Drill descriptions array
$drillDescriptions = [
    // Shooting Drills
    "Perfects" => [
        "purpose" => "This is a great basketball drill for players to practice shooting with perfect form and also for coaches to teach and correct shooting form.",
        "setup" => "Players form three lines a couple of feet out from the basket. Use both ends of the court if possible so that kids get to take more shots. Every player has a basketball.",
        "execution" => [
            "Players then take it in turns shooting with the aim to swish each shot through the net. The swish is important because we're trying to teach the kids how to shoot with enough arc on the shot.",
            "After a player has taken a shot, they can either return to the end of the same line or rotate lines either clockwise or anticlockwise."
        ],
        "image" => [
            "url" => "image/drills/Perfect.jpg",
            "alt" => "Basketball shooting form practice",
            "caption" => "Proper shooting form demonstration"
        ],
        "coachingpoints" => [
            "Players must hold their shooting form until the shot has been made or missed.",
            "Coaches must view each player's shot at different angles. Different angles will show different technique points.",
            "You can extend the distance of the shot but make sure it's not too far. The purpose of this drill is shooting with perfect form around the basket."
        ]
    ],
    "21 Cones" => [
        "purpose" => "21 cones is a variation of the drill 23 cones which is a drill I recommend for high school level and higher. All players are in two teams and each time a player hits a shot, they're awarded a cone for their team.",
        "setup" => "Place 21 cones to the baseline of one end of the court and then split your players up into two teams. Each team has only one basketball.",
        "execution" => [
            "The two teams of players shoot from the designated spot.",
            "When a shot is made, the shooter is rewarded by being allowed to sprint to the other end of the court and retrieve a cone for their team.",
            "The team that finishes with the most cones is the winner."
        ],
        "coachingpoints" => [
            "Everyone must be shooting. Not just the best shooters on each team.",
            "If you don't have cones, you could use tennis balls or anything else similar.",
            "You can decrease or increase the number of cones."
        ],
        "image" => [
            "url" => "image/drills/21cones.jpg",
            "alt" => "21 Cones shooting drill",
            "caption" => "Team shooting competition with cones"
        ]
    ],
    "Pivot Shooting" => [
        "purpose" => "This is a great drill for incorporating footwork into a shooting drill that players will enjoy. Players perform a jump stop on receiving the pass from the coach, pivot around to square up to the basket, and then make a variety of scoring moves.",
        "setup" => "Players all start on the baseline in two lines. There are two coaches/parents at the top of the key. One in front of each line. Every player has a basketball.",
        "execution" => [
            "Players begin by making a chest pass out to the coach in front of them.",
            "Immediately after making the chest pass, the player explodes to the free-throw line where the coach will pass the ball back to them.",
            "After catching the basketball in a jump stop, the player must pivot around using good technique and square up to the basket before shooting or attacking the ring.",
            "The coach decides which scoring move they want the players to make."
        ],
        "coachingpoints" => [
            "Make sure every player jumps stopping correctly.",
            "Players should not raise up out of their low stance when pivoting.",
            "Change up whether your team attacks the rim or takes a jump stop."
        ],
        "image" => [
            "url" => "image/drills/pivotshooting.jpg",
            "alt" => "Pivot shooting drill",
            "caption" => "Player executing pivot move before shooting"
        ]
    ],
    "Chase Down Layups" => [
        "purpose" => "Chase down layups is used to teach players to finish layups at full speed and with pressure. Since youth basketball is normally decided by which team makes more layups, this is a basketball drill you must use often.",
        "setup" => "The drill begins with two lines of players down each end of the floor. One offensive line and one defensive line. One basketball starts at the front of the offensive line at each end of the court.",
        "execution" => [
            "The coach brings the offensive player out from the baseline and gives them an advantage over the defender.",
            "On 'GO', both players sprint to the other end of the floor.",
            "The offensive player must try and finish at the rim and the defender must pressure the shot without fouling.",
            "The pair then passes the basketball to the next player in line."
        ],
        "coachingpoints" => [
            "No fouling to prevent injuries.",
            "Switch sides of the floor so that players are dribbling and finishing with their left hand.",
            "Make sure players are attacking the ring at the correct angle."
        ],
        "image" => [
            "url" => "image/drills/chasedownlayup.jpg",
            "alt" => "Chase down layups drill",
            "caption" => "Fast break layup under defensive pressure"
        ]
    ],
    "Pressure" => [
        "purpose" => "Pressure is a simple and fun end-of-practice game that works on shooting free throws while under pressure.",
        "setup" => "All players form one line at the free throw line. The drill requires only one basketball.",
        "execution" => [
            "Players take it in turns shooting free throws.",
            "When a player makes a free throw, the person behind them is put under pressure.",
            "If they miss under pressure, they're out of the game.",
            "Pressure continues until someone misses, then no pressure until another shot is made."
        ],
        "coachingpoints" => [
            "Players are not allowed to put each other off.",
            "Players should be going through their full free throw routine on each shot.",
            "Coaches should join in to make it more fun."
        ],
        "image" => [
            "url" => "image/drills/pressure.jpg",
            "alt" => "Pressure free throw drill",
            "caption" => "Free throw shooting under pressure"
        ]
    ],
    "Hand-Off Shooting Drill" => [
        "purpose" => "To work on shooting off hand-offs and performing them as they can be tricky for players to master unless drilled often.",
        "setup" => "Two lines at the top of the key with basketballs. One line of players on the lower end of each wing.",
        "execution" => [
            "Players from the top dribble down to the wing on their respective sides.",
            "Wing player cuts towards baseline then explodes up towards the wing.",
            "Receive hand-off and perform specified shot.",
            "Hand-off player joins wing line, shooter rebounds and joins opposite top line."
        ],
        "coachingpoints" => [
            "Change speeds when cutting to receive hand-off",
            "Maintain proper spacing during the exchange",
            "Use both hands for hand-offs"
        ],
        "image" => [
            "url" => "image/drills/handoffshootingdrill.jpg",
            "alt" => "Hand-off shooting drill",
            "caption" => "Executing hand-off and shooting"
        ]
    ],
    "23 Cones Shooting Drill" => [
        "purpose" => "Fun variation to a normal shooting drill that keeps players interested and excited. Players are shooting under a lot of pressure on the second shot.",
        "setup" => "Place 23 cones at the opposite end of the court. Split group into two teams with one basketball per team.",
        "execution" => [
            "Players start shooting on the coach's whistle.",
            "On every make, the shooter rebounds and passes to next person, then sprints to other end.",
            "At the other end, they get one attempt at a three-pointer to win a cone.",
            "If made, they collect a cone; if missed, they return empty-handed."
        ],
        "coachingpoints" => [
            "Emphasize quick transitions between shots",
            "Maintain proper shooting form under pressure",
            "Encourage team communication and support"
        ],
        "image" => [
            "url" => "image/drills/23conesshootingdrill.jpg",
            "alt" => "23 cones shooting drill",
            "caption" => "Competitive cone collection shooting game"
        ]
    ],
    "Pressure Jump Shots" => [
        "purpose" => "Allows players to practice shooting open jump shots while under mental pressure.",
        "setup" => "4 lines of players on each elbow with basketballs. Can be as many players as you like.",
        "execution" => [
            "First person in each line takes a shot and returns ball to same line.",
            "If they make the shot they move to next line, if miss they join same line to try again.",
            "Process continues until player has made total of eight shots (two from each spot)."
        ],
        "coachingpoints" => [
            "Maintain focus despite the pressure",
            "Use consistent shooting form every time",
            "Don't rush the shot - take your time"
        ],
        "image" => [
            "url" => "image/drills/pressurejumpshots.jpg",
            "alt" => "Pressure jump shots drill",
            "caption" => "Jump shooting under mental pressure"
        ]
    ],
    "Speed Shooting Drill" => [
        "purpose" => "This drill makes sure players are fatigued when shooting and works on players learning to decelerate and be on balance when shooting the ball.",
        "setup" => "Split team into 3-4 groups along baseline. Each group has one basketball.",
        "execution" => [
            "First player sprints to other end with ball, pulls up for shot, rebounds.",
            "Sprints back down other end and shoots again, rebounds.",
            "Passes to next player in line who cannot start until receiving basketball.",
            "Next player repeats the process."
        ],
        "coachingpoints" => [
            "Focus on balance when shooting while tired",
            "Maintain proper form despite fatigue",
            "Control breathing during sprints"
        ],
        "image" => [
            "url" => "image/drills/speed_shootingdrill.jpg",
            "alt" => "Speed shooting drill",
            "caption" => "Shooting while fatigued from sprinting"
        ]
    ],
    "Off the Dribble Form Shooting" => [
        "purpose" => "To teach players to shoot off the dribble using either the 1-2 step or the hop with correct footwork and while balanced.",
        "setup" => "Every player has basketball. Three lines a couple of metres out from three-point line.",
        "execution" => [
            "Players start in triple threat stance.",
            "Practice 1-2 step or hop footwork two times with pump fake.",
            "Third time use footwork to shoot actual jump shot.",
            "Focus on balance and proper form throughout."
        ],
        "coachingpoints" => [
            "Stay balanced throughout the movement",
            "Use consistent footwork every repetition",
            "Keep eyes on target during dribble"
        ],
        "image" => [
            "url" => "image/drills/off_the_dribble_form_shooting.jpg",
            "alt" => "Off dribble form shooting",
            "caption" => "Shooting form after dribble moves"
        ]
    ],
    "Weave Layups" => [
        "purpose" => "A fast paced drill that works on passing and layups while at full speed and under time pressure. Great for intensity at training.",
        "setup" => "Three even lines at half court with at least two players in each. One basketball in middle line.",
        "execution" => [
            "Middle player passes to left wing who immediately passes to right wing for layup.",
            "Left wing player sprints across court to receive outlet pass.",
            "Middle player rebounds and outlets to wing player.",
            "Wing player passes to next person in middle line."
        ],
        "coachingpoints" => [
            "Communicate during the weave",
            "Make crisp, accurate passes on the move",
            "Time cuts properly to maintain spacing"
        ],
        "image" => [
            "url" => "image/drills/Weave_layups.jpg",
            "alt" => "Weave layups drill",
            "caption" => "Three-player weave leading to layup"
        ]
    ],
    "Cincinnati Layups" => [
        "purpose" => "Great warm-up drill for young players that works on layups and passing skills. Emphasizes that ball should never hit the floor.",
        "setup" => "Line of players at half court, line on wing, single player on free-throw line. Basketballs with half court group.",
        "execution" => [
            "Half court player passes to free throw player then replaces them.",
            "Free throw player passes to cutting wing player for layup without dribble.",
            "Passer rebounds, outlets to layup player who passed to half court line.",
            "Players rotate through positions."
        ],
        "coachingpoints" => [
            "Ball should never touch the floor",
            "Make sharp, accurate passes",
            "Cut hard to the basket"
        ],
        "image" => [
            "url" => "image/drills/Cincinati_layups.jpg",
            "alt" => "Cincinnati layups drill",
            "caption" => "Passing and layup warm-up drill"
        ]
    ],
    "Give and Go Shooting" => [
        "purpose" => "To work on dribbling skills, footwork off the catch and variety of shots.",
        "setup" => "Every player has basketball. Two coaches. Two lines on half-way line. Optional cones for dribbling.",
        "execution" => [
            "Player weaves through cones dribbling.",
            "Makes pass to coach and receives return pass.",
            "Performs specified shot (catch-shoot, pump-fake-drive, etc.).",
            "Rebounds and joins opposite line."
        ],
        "coachingpoints" => [
            "Maintain control while dribbling through cones",
            "Use proper footwork when catching and shooting",
            "Vary the types of shots taken"
        ],
        "image" => [
            "url" => "image/drills/give_and_go_shooting.jpg",
            "alt" => "Give and go shooting drill",
            "caption" => "Dribbling into give-and-go action"
        ]
    ],
    "Screen Shooting" => [
        "purpose" => "Teach players how to use different cuts off an off-ball screen and practice scoring off those cuts.",
        "setup" => "Chairs or cones as screens on both sides. All players lined up at top of key. Front player has no ball.",
        "execution" => [
            "First player cuts under basket then explodes off screen.",
            "Next player in line passes to shooter coming off screen.",
            "Passer becomes next cutter using screen on opposite side.",
            "Continue rotating through different cuts and shots."
        ],
        "coachingpoints" => [
            "Change pace when coming off screens",
            "Read the defense when choosing cuts",
            "Practice all four main cuts: flare, straight, curl, backdoor"
        ],
        "image" => [
            "url" => "image/drills/screen_shooting.jpg",
            "alt" => "Screen shooting drill",
            "caption" => "Shooting off screens and cuts"
        ]
    ],
    "Partner Form Shooting" => [
        "purpose" => "Great drill for basic shooting technique mastery. Gets in many quick repetitions and allows coaches to correct form.",
        "setup" => "Players in partners about 7-10 feet apart. One basketball between two players.",
        "execution" => [
            "Players shoot ball to each other using correct technique.",
            "Partner should catch without moving their feet.",
            "Focus on proper shooting form and arc.",
            "Coaches circulate and provide individual corrections."
        ],
        "coachingpoints" => [
            "Focus on proper shooting mechanics",
            "Use correct follow-through",
            "Aim for soft, catchable passes"
        ],
        "image" => [
            "url" => "image/drills/partner_form_shooting.jpg",
            "alt" => "Partner form shooting",
            "caption" => "Basic shooting form with partner"
        ]
    ],
    "30 and 1 Shooting Drill" => [
        "purpose" => "Fun, competitive shooting drill that works on shots from different spots including long-range game-winner.",
        "setup" => "Groups of 3-5 players. Each group has one basketball. Three shooting spots plus half-court.",
        "execution" => [
            "Teams shoot from first spot until 10 makes.",
            "Move to second spot for 10 makes, then third spot.",
            "After 30 makes, attempt half-court game-winner.",
            "First team to complete all shots wins."
        ],
        "coachingpoints" => [
            "Maintain focus through the entire drill",
            "Use consistent form on every shot",
            "Encourage teammates throughout"
        ],
        "image" => [
            "url" => "image/drills/30_and_1_shooting_drill.jpg",
            "alt" => "30 and 1 shooting drill",
            "caption" => "Progressive shooting to half-court shot"
        ]
    ],
    "Fatigue Shooting Drill" => [
        "purpose" => "Fast-paced drill that allows athletes to practice shooting while fatigued. Great for conditioning.",
        "setup" => "Groups of 3-4 players. Players on baselines with basketballs. Maximum 3 groups per court.",
        "execution" => [
            "Middle players sprint towards partner, receive pass and shoot.",
            "Shooter rebounds, dribbles out of bounds, waits to pass.",
            "Passer immediately sprints to other end to become shooter.",
            "Continuous cycle for 1-3 minutes per shot type."
        ],
        "coachingpoints" => [
            "Maintain form despite fatigue",
            "Focus on footwork when receiving passes",
            "Control breathing during sprints"
        ],
        "image" => [
            "url" => "image/drills/fatique_shooting_drill.jpg",
            "alt" => "Fatigue shooting drill",
            "caption" => "Shooting while physically exhausted"
        ]
    ],
    "5 Spot Variety" => [
        "purpose" => "Great for practicing variety of shots from all over the floor. Moves quickly with minimal waiting.",
        "setup" => "5 cones outside three-point line at baseline, wings, and top. All players with basketballs behind one cone.",
        "execution" => [
            "All players start at same cone with specified shot type.",
            "After shot, player joins next cone.",
            "Continue until all players have shot from all cones.",
            "Change shot type and repeat process."
        ],
        "coachingpoints" => [
            "Practice different types of shots",
            "Maintain focus through rotation",
            "Use proper form at every spot"
        ],
        "image" => [
            "url" => "image/drills/5_spot_variety.jpg",
            "alt" => "5 spot variety drill",
            "caption" => "Shooting from multiple court locations"
        ]
    ],
    "Drive and Kick" => [
        "purpose" => "Teaches players to explode off dribble, attack gaps, force help defense, then pass to open teammate.",
        "setup" => "Player under ring with ball. Players on corners and top of key. Others on baseline waiting.",
        "execution" => [
            "Player under ring passes to corner, then fills spot.",
            "Corner player attacks key with two dribbles, passes to top.",
            "Top player attacks key, passes to opposite corner for shot.",
            "Shooter rebounds, next player continues sequence."
        ],
        "coachingpoints" => [
            "Attack gaps aggressively",
            "Make quick, accurate passes",
            "Read help defense positions"
        ],
        "image" => [
            "url" => "image/drills/drive_and_kick.jpg",
            "alt" => "Drive and kick drill",
            "caption" => "Driving and kicking to open shooters"
        ]
    ],
    "Titan Shooting" => [
        "purpose" => "Fantastic team conditioning shooting drill for limited baskets. Works on shooting under fatigue.",
        "setup" => "Three lines of players on high posts and middle of free throw line. One basketball per line.",
        "execution" => [
            "First player in each line shoots, rebounds, passes back to line.",
            "After passing, runs to designated line and back.",
            "Joins different line upon return.",
            "Continuous for time limit (2-4 minutes)."
        ],
        "coachingpoints" => [
            "Shoot quickly but with proper form",
            "Sprint on all running portions",
            "Communicate with teammates"
        ],
        "image" => [
            "url" => "image/drills/titan_shooting.jpg",
            "alt" => "Titan shooting drill",
            "caption" => "High-intensity conditioning shooting"
        ]
    ],
    "Rainbow Shooting" => [
        "purpose" => "Great warm-up and shooting drill especially for youth. High intensity with everyone encouraging each other.",
        "setup" => "Two lines on baseline at key width. Two basketballs. Even number of players per line.",
        "execution" => [
            "Player without ball does half circle, receives pass for layup.",
            "Rebounds, passes to line received from, joins that line.",
            "Passer does half circle behind shooter to receive next pass.",
            "Continue through all shooting spots."
        ],
        "coachingpoints" => [
            "Keep the ball moving quickly",
            "Encourage teammates throughout",
            "Maintain proper form on all shots"
        ],
        "image" => [
            "url" => "image/drills/rainbow_shooting.jpg",
            "alt" => "Rainbow shooting drill",
            "caption" => "Continuous movement shooting warm-up"
        ]
    ],
    "31 Shooting Drill" => [
        "purpose" => "Works on shooting from all different spots on court while under pressure and at game speed.",
        "setup" => "4 even groups forming lines outside 3-point line on each wing. 3-5 players per team. Basketballs with first players.",
        "execution" => [
            "First player takes three-point shot (3 points if made).",
            "Rebounds, retreats outside key for jump shot (2 points).",
            "Rebounds, takes shot inside key (1 point).",
            "Passes to next player who repeats."
        ],
        "coachingpoints" => [
            "Maintain composure under pressure",
            "Use consistent shooting mechanics",
            "Transition quickly between shots"
        ],
        "image" => [
            "url" => "image/drills/31_shooting_drill.jpg",
            "alt" => "31 shooting drill",
            "caption" => "Progressive point shooting game"
        ]
    ],

    // Defense Drills
    "Defensive Mirrors" => [
        "purpose" => "This is a fun drill for working on defensive footwork. Players mimic their partner's movements which is great for developing reactions while working on defensive footwork.",
        "setup" => "Players find partners and stand in pairs behind baseline. Use both ends of court if possible. Players set up opposite each other on parallel lines of the key.",
        "execution" => [
            "First pair comes out and sets up opposite each other on key lines.",
            "Coach assigns one as offensive player, drill begins immediately.",
            "Defensive player stays directly in line with offensive player.",
            "Offensive player works to separate themselves by sliding up and down the line.",
            "After 15 seconds, players swap roles. After 30 seconds, new pair comes in."
        ],
        "coachingpoints" => [
            "Stay in low defensive stance with hands out wide entire time",
            "Offensive player should use head fakes and quick changes of pace",
            "Cover proper defensive stance before running drill"
        ],
        "image" => [
            "url" => "image/drills/defensive_mirrors.jpg",
            "alt" => "Defensive mirrors drill",
            "caption" => "Mirroring defensive footwork movements"
        ]
    ],
    "Defensive Specialist" => [
        "purpose" => "Continuous drill that works on different defensive movements including closeouts, defensive sliding, back-pedalling, and sprinting.",
        "setup" => "Four cones set up in defensive course pattern. All players begin in straight line on baseline.",
        "execution" => [
            "Players perform defensive course one-by-one.",
            "First movement: sprint and close out to front cone.",
            "Back-pedal around cone behind them, then slide across to other side of court.",
            "When first defender slides past line, next player starts.",
            "After sliding around cone, sprint to close out again, slide to opposite side, return to line."
        ],
        "coachingpoints" => [
            "Sprint and slide at 100% effort throughout drill",
            "Hold closeout for 1-2 seconds before moving on",
            "Focus on proper defensive footwork technique"
        ],
        "image" => [
            "url" => "image/drills/defensive_specialist.jpg",
            "alt" => "Defensive specialist drill",
            "caption" => "Complete defensive movement course"
        ]
    ],
    "One-on-One" => [
        "purpose" => "Teaches both defense and offense by forcing on-ball defender to 'guard their yard' with no help defense. Players learn to stay in front and challenge shots.",
        "setup" => "Two players at free-throw line or top of key. Defensive player starts with basketball. Other players wait behind near half-court.",
        "execution" => [
            "Defender hands basketball to offensive player to ensure close defense.",
            "Offensive player has maximum 2-3 dribbles to attack ring and get clear shot.",
            "After make or miss, new offensive player comes in.",
            "Previous offensive player switches to defense, previous defender joins line."
        ],
        "coachingpoints" => [
            "Enforce 2-3 dribble maximum rule",
            "Defensive player should get up close and play hard defense",
            "Use good footwork and fakes on both offense and defense"
        ],
        "image" => [
            "url" => "image/drills/one_on_one.png",
            "alt" => "One-on-one defense drill",
            "caption" => "Individual defensive matchup practice"
        ]
    ],
    "Zig-Zag Slides" => [
        "purpose" => "Great beginning team drill teaching proper defensive sliding technique and how to drop step when playing defense.",
        "setup" => "All players line up on baseline corner. No basketballs needed.",
        "execution" => [
            "First player defensive slides from corner to high post.",
            "Perform 90-degree drop step to slide back to opposite sideline.",
            "Continue sliding side-to-side with drop steps to opposite baseline.",
            "Return down opposite side using same principles."
        ],
        "coachingpoints" => [
            "Teach proper defensive slide and drop step technique first",
            "Stay in low stance - no straight legs",
            "Never cross feet when sliding"
        ],
        "image" => [
            "url" => "image/drills/zigzag_slides.png",
            "alt" => "Zig-zag slides drill",
            "caption" => "Defensive sliding with drop steps"
        ]
    ],
    "1-on-1 Continuous" => [
        "purpose" => "Fast-paced competitive drill focusing on attacking defenders off closeouts and guarding in isolation. Fantastic for player development.",
        "setup" => "Line at top of key with basketballs. Offensive players on wings. One defender guarding wing player.",
        "execution" => [
            "Offensive player v-cuts to get open for pass from top.",
            "Play 1-on-1 until score or change of possession.",
            "Top line passes to opposite wing, fills free wing.",
            "Previous offensive player immediately closes out to defend opposite wing.",
            "Continuous transitions between offense and defense."
        ],
        "coachingpoints" => [
            "1-on-1 games can't cross to opposite side of court",
            "Focus on closeout footwork and offensive footwork on catch",
            "Offensive player should attack immediately on catch",
            "Time passes from top correctly"
        ],
        "image" => [
            "url" => "image/drills/1_on_1_continuous.jpg",
            "alt" => "1-on-1 continuous drill",
            "caption" => "Continuous transition 1-on-1 play"
        ]
    ],
    "4-Point Closeouts" => [
        "purpose" => "Primary purpose is to work on closeout technique - specifically footwork and staying on balance while incorporating conditioning.",
        "setup" => "4 offensive players or coaches spread around 3-point arc. Defensive players under basket. Offensive players all have basketballs.",
        "execution" => [
            "First defender sprints out to first offensive player clockwise and closes out.",
            "Pressure offensive player for 2-3 seconds.",
            "Back-pedal to charge circle, then sprint to next player.",
            "Next defender starts when first player begins sprinting to next offensive player.",
            "After closing out all 4 players, join end of line."
        ],
        "coachingpoints" => [
            "Use short, choppy steps on closeouts",
            "Get one hand up to contest shot, maintain balance",
            "Trace basketball with one hand when pressuring",
            "Sprint and back-pedal at game pace for conditioning"
        ],
        "image" => [
            "url" => "image/drills/4_point_closeouts.jpg",
            "alt" => "4-point closeouts drill",
            "caption" => "Multiple closeout repetitions from different angles"
        ]
    ],
    "Mass Sliding" => [
        "purpose" => "Defensive drill focusing on fundamentals of individual defense while incorporating conditioning. Improves defensive footwork and technique.",
        "setup" => "Players spread out in half or full court with space between each. Coach stands in front to give instructions.",
        "execution" => [
            "Players start in low stance with 'pitter-pattering' feet.",
            "Coach uses visual and verbal cues for defensive movements.",
            "Continue for 2-3 minutes with various defensive commands.",
            "Movements include: lateral slides, drop steps, closeouts, charges, rebounds, back-pedals, sprints."
        ],
        "coachingpoints" => [
            "Remain in low, wide defensive stance entire drill",
            "Focus on correct footwork and good balance",
            "Keep arms out to sides throughout drill",
            "Be loud on closeouts, charge calls, and communications"
        ],
        "image" => [
            "url" => "image/drills/mass_sliding.jpg",
            "alt" => "Mass sliding drill",
            "caption" => "Group defensive footwork and conditioning"
        ]
    ],
    "Pass Denial" => [
        "purpose" => "Practice positioning and reacting to offensive movements to deny passes. Essential skill for traditional man-to-man defense.",
        "setup" => "Player with basketball on strong-side slot. Offensive player on wing. Defensive player guarding offensive player.",
        "execution" => [
            "Offensive player walks defender in, explodes out to receive pass.",
            "Defender constantly denies pass to offensive player.",
            "Offensive player uses v-cuts and speed changes on wing-basket line.",
            "After catch, immediately pass back to slot, continue drill.",
            "After third catch, players swap positions."
        ],
        "coachingpoints" => [
            "In denial position, chest faces offensive player with arm out",
            "Defender maintains arm-bar contact without pushing foul",
            "Offensive player establishes higher foot, explodes out to wing",
            "Watch for backdoor passes when overplaying"
        ],
        "image" => [
            "url" => "image/drills/pass_denial.jpg",
            "alt" => "Pass denial drill",
            "caption" => "Denying wing passes in man-to-man defense"
        ]
    ],

    // Passing Drills
    "Partner Passing" => [
        "purpose" => "Teaches the absolute basics of passing and allows players to practice different types of passes and correct technique. Great for kids beginning to learn basketball.",
        "setup" => "Players get into pairs with one basketball between them. Partners stand on parallel lines facing each other.",
        "execution" => [
            "Coach explains which type of pass to perform.",
            "Players pass back and forth to each other.",
            "Every minute, coach changes pass type or increases distance.",
            "Focus on proper technique for each pass type."
        ],
        "coachingpoints" => [
            "Mix up pass types: bounce pass, chest pass, one-handed push-pass, etc.",
            "Don't allow players to throw basketball too hard at partners",
            "All coaches should teach same passing technique to avoid confusion"
        ],
        "image" => [
            "url" => "image/drills/partner_passing.png",
            "alt" => "Partner passing drill",
            "caption" => "Basic passing technique with partners"
        ]
    ],
    "Stationary Keepings Off" => [
        "purpose" => "Teaches basics of spacing between players and decision making on the catch. Shows players it's easier to keep ball away from defense when spread apart.",
        "setup" => "Select 1-2 defenders. Rest of players spread out in small area like three-point line. Offense has one basketball.",
        "execution" => [
            "Defenders run around trying to steal basketball from offensive team.",
            "Offensive players stay in one space and pass ball around.",
            "Goal is to keep basketball away from defenders.",
            "After 1-2 minutes, swap defenders."
        ],
        "coachingpoints" => [
            "Allow defenders to sprint around wildly - they'll have fun",
            "Encourage offensive team to make quick decisions when receiving ball",
            "Ensure everyone gets turn to pass - coach can join in if needed"
        ],
        "image" => [
            "url" => "image/drills/stationary_keepings_off.png",
            "alt" => "Stationary keepings off drill",
            "caption" => "Passing under defensive pressure"
        ]
    ],
    "Count Em Up" => [
        "purpose" => "Advanced version of keepings off game. Works on getting open, denying offensive player, and making smart passes to limit turnovers.",
        "setup" => "Split kids into two even teams with different colors. Only one basketball needed.",
        "execution" => [
            "All players match up and stick to individual opponents.",
            "Goal: make set number of passes (5-20) without opposition deflecting or stealing.",
            "No dribbling or shooting allowed.",
            "Players can move anywhere within playing area.",
            "If defenders steal or deflect ball out of bounds, they get possession."
        ],
        "coachingpoints" => [
            "Encourage players to set screens and use body fakes to get open",
            "Have best players challenge each other",
            "Emphasize spacing - don't allow players to sprint at basketball"
        ],
        "image" => [
            "url" => "image/drills/count_em_up.png",
            "alt" => "Count em up passing drill",
            "caption" => "Competitive passing under pressure"
        ]
    ],
    "Continuous 3 on 2" => [
        "purpose" => "One of the best drills for improving passing and decision making. Extra player on offense means there's always someone open with proper spacing.",
        "setup" => "3 offensive players in middle court, 2 defenders in each half court, rest of players out of bounds at half court line. One basketball.",
        "execution" => [
            "Three offensive players attack two defenders at one end.",
            "After score or defensive rebound/steal, defenders outlet to next player at half court.",
            "Two defenders become offense with extra player from sideline.",
            "They attack other end 3 on 2.",
            "Previous offensive players: 2 become defenders, 1 joins out of bounds line."
        ],
        "coachingpoints" => [
            "Offensive players must maintain spacing for open looks",
            "Offensive team should take open shots",
            "For advanced version: no dribbling allowed"
        ],
        "image" => [
            "url" => "image/drills/continuos_3_on_2.png",
            "alt" => "Continuous 3 on 2 drill",
            "caption" => "Fast break passing and decision making"
        ]
    ],
    "CHEST PASS" => [
        "purpose" => "Fundamental pass originating from the chest. Most common and reliable pass in basketball.",
        "setup" => "Players in pairs facing each other, proper distance apart.",
        "execution" => [
            "Grip ball on sides with thumbs directly behind ball.",
            "Throw pass with fingers rotated behind ball, thumbs turned down.",
            "Follow through with back of hands facing each other, thumbs straight down.",
            "Aim for receiver's chest level with nice backspin."
        ],
        "coachingpoints" => [
            "Pass to receiver's chest level - avoid low to high or high to low passes",
            "Use proper backspin for easier catching",
            "Step into pass for more power and accuracy"
        ]
    ],
    "BOUNCE PASS" => [
        "purpose" => "Effective pass that uses the floor to bypass defenders. Great for post entries and passing in traffic.",
        "setup" => "Players in pairs, proper distance for bounce pass.",
        "execution" => [
            "Use same motion as chest pass but aim at floor.",
            "Throw far enough out that ball bounces waist high to receiver.",
            "Start with throwing 3/4 of way to receiver as reference.",
            "Use proper and consistent backspin for easier distance judgment."
        ],
        "coachingpoints" => [
            "Bounce should reach receiver at waist level",
            "Use consistent backspin for better control",
            "Aim bounce point based on defender's position"
        ]
    ],
    "OVERHEAD PASS" => [
        "purpose" => "Often used as outlet pass or to pass over defenders. Provides good visibility of the court.",
        "setup" => "Players in pairs, slightly farther distance than chest pass.",
        "execution" => [
            "Bring ball directly above forehead with both hands on sides.",
            "Follow through toward target.",
            "Aim for teammate's chin.",
            "Keep ball in front of head, not behind."
        ],
        "coachingpoints" => [
            "Don't bring ball behind head - can be stolen and takes longer",
            "Aim for teammate's chin for optimal catch position",
            "Use for outlet passes and skipping ball across court"
        ]
    ],
    "WRAP AROUND PASS" => [
        "purpose" => "Used to pass around defenders by stepping around them. Effective for perimeter and post entry passes.",
        "setup" => "Players practice with defensive positioning.",
        "execution" => [
            "Step around defense with non-pivot foot.",
            "Pass ball with one hand (outside hand).",
            "Can be used as air pass or bounce pass.",
            "Common: wrap-around air pass on perimeter, bounce pass for post entry."
        ],
        "coachingpoints" => [
            "Use proper footwork when stepping around defender",
            "Choose between air and bounce pass based on situation",
            "Protect ball with body during passing motion"
        ]
    ],
    "BASEBALL PASS" => [
        "purpose" => "One-handed pass using baseball throwing motion for long passes down court.",
        "setup" => "Players at longer distances for full-court passing.",
        "execution" => [
            "Use one-handed pass with same motion as baseball throw.",
            "Generate power from full body rotation.",
            "Use for long passes in transition situations.",
            "Follow through completely for accuracy and power."
        ],
        "coachingpoints" => [
            "Be careful with young players - don't overstress arms",
            "Use for outlet passes and fast break situations",
            "Practice proper throwing mechanics to prevent injury"
        ]
    ],
    "DRIBBLE PASS" => [
        "purpose" => "Quick pass with one hand off the dribble. Used to maintain offensive flow and speed.",
        "setup" => "Players practice while dribbling, partners ready to receive.",
        "execution" => [
            "Pass ball with one hand directly from dribble.",
            "Can be used as air pass or bounce pass.",
            "Maintain dribble rhythm while preparing to pass.",
            "Execute quickly to catch defense off guard."
        ],
        "coachingpoints" => [
            "Keep eyes up while dribbling to see passing opportunities",
            "Time pass with receiver's movement",
            "Use for quick ball movement in offensive sets"
        ]
    ],
    "BEHIND-THE-BACK PASS" => [
        "purpose" => "Advanced pass to avoid defenders when front pass would be risky. Used on fast breaks and in creative situations.",
        "setup" => "Advanced players only, heavy practice recommended.",
        "execution" => [
            "Wrap ball around back to throw to teammate.",
            "Use for situations where front pass would be intercepted.",
            "Can be used to hit trailing player on fast break.",
            "Practice extensively before game use."
        ],
        "coachingpoints" => [
            "Not recommended for game use until heavily practiced",
            "Use sparingly and only when necessary",
            "Focus on accuracy over flashiness"
        ]
    ],
    "PICK AND ROLL PASS" => [
        "purpose" => "Specialized pass used when defenders double-team or switch on pick and roll situations.",
        "setup" => "Players practice pick and roll scenarios with defenders.",
        "execution" => [
            "If dribbling right, left side faces target.",
            "Bring ball up from right side to throw overhead.",
            "Pass to screener rolling to basket or popping to perimeter.",
            "Use 'hook shot' fashion to shield ball from defender."
        ],
        "coachingpoints" => [
            "Advanced players can fade away from defender during pass",
            "Read defender's reaction to pick and roll",
            "Time pass with screener's movement"
        ]
    ],

    // Dribbling Drills
    "Dribbling Lines" => [
        "purpose" => "Simple drill to teach basics of dribbling to new players. Introduces new moves without overwhelming and improves technique of known movements.",
        "setup" => "Every player has basketball and lines up on baseline. For more than 8 players, create two lines.",
        "execution" => [
            "Coach instructs players to use different dribbling movements.",
            "Players dribble to half-court or full court using specified moves.",
            "Common moves: right hand up/left hand back, crossovers, behind-the-back, through-the-legs, dribble low, dribbling backwards.",
            "Focus on control and proper technique."
        ],
        "coachingpoints" => [
            "Players must keep heads up at all times",
            "Once technique is good, focus on pushing off with outside foot during moves",
            "Write down dribbling moves to remember all variations"
        ],
        "image" => [
            "url" => "image/drills/dribbling_lines.png",
            "alt" => "Dribbling lines drill",
            "caption" => "Fundamental dribbling technique practice"
        ]
    ],
    "Dribble Knockout" => [
        "purpose" => "Works on ball-handling and protecting the dribble under pressure. Players dribble in small area while trying to knock others' basketballs out.",
        "setup" => "Determine dribbling area based on player count (usually three-point line or 1/3 court). All players have basketballs.",
        "execution" => [
            "On coach's call, all players begin dribbling.",
            "Players attempt to knock each other's basketballs out of playing area.",
            "As players get out, coach pauses and makes playing area smaller.",
            "Continue until one winner remains."
        ],
        "coachingpoints" => [
            "Players foul out for traveling, double dribbling, or fouling others",
            "Constantly remind players to keep heads up",
            "Have designated area for players who get out"
        ],
        "image" => [
            "url" => "image/drills/dribble_knockout.png",
            "alt" => "Dribble knockout drill",
            "caption" => "Competitive dribbling under pressure"
        ]
    ],
    "Collision Dribbling" => [
        "purpose" => "Improves ball-handling by forcing players to react to others. Players navigate through crowded space using dribbling moves and creativity.",
        "setup" => "All players have basketballs in small space determined by coach.",
        "execution" => [
            "On coach's call, all players start dribbling around each other.",
            "Aim to keep dribble under control while avoiding collisions.",
            "Players must react to others rather than predetermine actions.",
            "Forces players to keep heads up to avoid running into others."
        ],
        "coachingpoints" => [
            "Don't allow all players to dribble in same direction",
            "Constantly remind players to keep heads up",
            "Encourage use of both hands, not just strong hand"
        ],
        "image" => [
            "url" => "image/drills/collision_dribbling.png",
            "alt" => "Collision dribbling drill",
            "caption" => "Dribbling in crowded spaces"
        ]
    ],
    "Scarecrow Tiggy" => [
        "purpose" => "Fun drill that develops ball-handling skills. Players dribble around avoiding taggers while freeing frozen teammates.",
        "setup" => "All players start with basketball in half court except two taggers. Taggers wear different colored singlets for identification.",
        "execution" => [
            "On 'GO', taggers attempt to tag dribblers.",
            "When tagged, dribbler stands with legs wide, ball on head.",
            "Frozen players can be freed by others rolling basketball through their legs.",
            "Switch taggers every couple minutes."
        ],
        "coachingpoints" => [
            "Ball must be rolled through legs, not thrown",
            "Dribblers foul out for traveling, double dribbling, or violations",
            "Adjust taggers and playing space based on player count"
        ],
        "image" => [
            "url" => "image/drills/scarecrow_tiggy.png",
            "alt" => "Scarecrow tiggy drill",
            "caption" => "Fun dribbling tag game"
        ]
    ],
    "Dribble Tag" => [
        "purpose" => "Similar to scarecrow tiggy but players are out when tagged. Focuses on dribbling under pressure and evasion.",
        "setup" => "Decide playing area based on player count. Select two taggers, everyone else spreads out with basketballs.",
        "execution" => [
            "Taggers attempt to tag as many dribblers as possible.",
            "When tagged, dribbler is out and waits on sideline.",
            "Continue until one dribbler remains as winner.",
            "Taggers must also dribble while tagging."
        ],
        "coachingpoints" => [
            "If taggers struggle, allow them to run without dribbling",
            "Vary court size and tagger count based on players",
            "Dribbling violations result in automatic elimination"
        ],
        "image" => [
            "url" => "image/drills/dribble_tag.png",
            "alt" => "Dribble tag drill",
            "caption" => "Dribbling evasion and tagging"
        ]
    ],
    "Sharks and Minnows" => [
        "purpose" => "Favorite youth game where minnows dribble baseline to baseline without getting tagged by sharks. Develops speed dribbling and awareness.",
        "setup" => "Select 1-2 'sharks' as taggers. Everyone else on baseline with basketballs as 'minnows'.",
        "execution" => [
            "On coach's call, minnows attempt to dribble to other baseline without shark tags.",
            "If tagged, minnow becomes scarecrow - stands with ball between feet.",
            "Scarecrows can tag minnows who come within reach.",
            "Last untagged player wins."
        ],
        "coachingpoints" => [
            "Dribbling violations result in immediate elimination",
            "Scarecrows must hold ball between feet and stay balanced",
            "Implement time limit if players take too long"
        ],
        "image" => [
            "url" => "image/drills/shark_and_minnows.png",
            "alt" => "Sharks and minnows drill",
            "caption" => "Dribbling through defensive pressure"
        ]
    ],
    "Ball Slaps" => [
        "purpose" => "Warm-up drill to get hands ready for workout. Develops hand strength and ball familiarity.",
        "execution" => [
            "Continuously slap basketball from one hand to other.",
            "Keep hands active and engaged.",
            "Use as beginning drill for ball-handling workouts."
        ],
        "coachingpoints" => [
            "Use fingertips, not palms",
            "Keep ball moving quickly between hands",
            "Good warm-up before more complex drills"
        ]
    ],
    "Straight Arm Finger Taps" => [
        "purpose" => "Develops hand speed and control while keeping elbows locked.",
        "execution" => [
            "Keep elbows locked while tapping basketball back and forth.",
            "Start straight out in front of you.",
            "When proficient, move ball up and down while tapping."
        ],
        "coachingpoints" => [
            "Maintain locked elbow position",
            "Use quick, controlled finger taps",
            "Progress to vertical movements"
        ]
    ],
    "Wraps Around Ankle" => [
        "purpose" => "Develops ball control and hand-eye coordination around lower body.",
        "execution" => [
            "Wrap ball around lower leg/ankles without touching ground.",
            "Maintain control throughout circular motion.",
            "Use both directions for complete development."
        ],
        "coachingpoints" => [
            "Keep ball close to body",
            "Use fingertips for control",
            "Practice both clockwise and counter-clockwise"
        ]
    ],
    "Wraps Around Waist" => [
        "purpose" => "Improves ball control around midsection and core area.",
        "execution" => [
            "Wrap ball in circle motion around waist.",
            "Keep ball moving smoothly without dropping.",
            "Maintain good posture throughout."
        ],
        "coachingpoints" => [
            "Keep elbows out for better control",
            "Use waist as guide for circle size",
            "Increase speed as control improves"
        ]
    ],
    "Wraps Around Head" => [
        "purpose" => "Develops ball control around upper body and improves peripheral vision.",
        "execution" => [
            "Wrap ball in circle motion around head.",
            "Keep head still while ball moves around it.",
            "Maintain control without losing ball."
        ],
        "coachingpoints" => [
            "Keep ball close to head for control",
            "Use peripheral vision to track ball",
            "Practice both directions equally"
        ]
    ],
    "Wraps Around the World" => [
        "purpose" => "Combines all wrap motions into continuous flow drill for complete ball control.",
        "execution" => [
            "Start with wraps around head.",
            "Bring down to wraps around waist.",
            "Continue to wraps around ankles.",
            "Return back up through waist to head.",
            "Create continuous flowing motion."
        ],
        "coachingpoints" => [
            "Maintain smooth transitions between levels",
            "Keep ball under control throughout",
            "Practice both up and down sequences"
        ]
    ],
    "Wraps Figure 8 Around Legs" => [
        "purpose" => "Develops coordination and ball control through complex leg movements.",
        "execution" => [
            "Wrap ball in figure 8 motion around legs.",
            "Keep ball low to ground for control.",
            "Maintain continuous motion without pauses."
        ],
        "coachingpoints" => [
            "Bend knees for better control",
            "Use both hands equally",
            "Keep eyes up when possible"
        ]
    ],
    "Pound Dribble Ankle Height Right Hand" => [
        "purpose" => "Develops control and strength with low dribbles using right hand.",
        "execution" => [
            "Dribble basketball couple inches off ground with right hand.",
            "Maintain low, controlled bounces.",
            "Focus on wrist action and control."
        ],
        "coachingpoints" => [
            "Use wrist, not arm, for control",
            "Keep ball close to body",
            "Maintain athletic stance"
        ]
    ],
    "Pound Dribble Waist High Right Hand" => [
        "purpose" => "Develops power and control with waist-high dribbles.",
        "execution" => [
            "In athletic stance, pound ball hard into ground at waist height.",
            "Use only right hand for repetition.",
            "Focus on powerful, controlled bounces."
        ],
        "coachingpoints" => [
            "Maintain proper dribbling stance",
            "Use full arm motion for power",
            "Keep ball in control despite power"
        ]
    ],
    "Crossover Dribble" => [
        "purpose" => "Fundamental move for changing directions and beating defenders.",
        "execution" => [
            "Cross ball continuously in front of body.",
            "Make wide crossover motions.",
            "Maintain control during transition between hands."
        ],
        "coachingpoints" => [
            "Cross over wide for game situations",
            "Low to high motion for protection",
            "Practice at different speeds"
        ]
    ],
    "Behind the Back Dribble" => [
        "purpose" => "Advanced move for protecting ball from defenders while changing direction.",
        "execution" => [
            "Cross ball continuously behind body.",
            "Make wide, controlled motions.",
            "Maintain balance and control throughout."
        ],
        "coachingpoints" => [
            "Keep ball close to body for control",
            "Use wide motions for game realism",
            "Practice both directions equally"
        ]
    ],
    "Freestyle" => [
        "purpose" => "Creative drill combining all moves in stationary position to develop personal style.",
        "execution" => [
            "Using all moves in arsenal, combine as many as possible.",
            "Stay in stationary position.",
            "Be creative and work on personal handle.",
            "Focus on smooth transitions between moves."
        ],
        "coachingpoints" => [
            "Encourage creativity and experimentation",
            "Focus on smooth transitions",
            "Practice both strong and weak hand moves"
        ]
    ],
    "Spider Dribble" => [
        "purpose" => "Develops quick hands and ball control in multiple positions around body.",
        "execution" => [
            "Knees shoulder width apart and bent.",
            "Dribble with right hand in front.",
            "Left hand in front.",
            "Right hand behind knee.",
            "Left hand behind knee.",
            "Return to right hand in front."
        ],
        "coachingpoints" => [
            "Keep ball underneath body entire time",
            "Quick, controlled hand movements",
            "Maintain low stance throughout"
        ]
    ],
    "Kills Right Hand" => [
        "purpose" => "Develops ball control through height variation and quick stops.",
        "execution" => [
            "Start dribbling at ankles.",
            "Gradually dribble higher each bounce.",
            "At shoulder height, 'kill' ball by stopping it inches off ground.",
            "Dribble back up to shoulder height.",
            "Repeat continuous motion."
        ],
        "coachingpoints" => [
            "Smooth height transitions",
            "Quick, controlled 'kill' stops",
            "Consistent rhythm throughout"
        ]
    ],
    "Double Pound at Waist Height" => [
        "purpose" => "Advanced two-ball dribbling for coordination and ambidextrous development.",
        "execution" => [
            "Dribble both basketballs as hard as possible at waist height.",
            "Maintain control despite power.",
            "Keep balls in rhythm with each other."
        ],
        "coachingpoints" => [
            "Focus on coordination between hands",
            "Maintain consistent rhythm",
            "Keep eyes up when possible"
        ]
    ],
    "Wraps Around Right Leg" => [
        "purpose" => "Develops ball control and coordination around a single leg, focusing on right-side ball handling.",
        "execution" => [
            "Starting with your right leg in front and your left leg back",
            "Wrap the ball around only your right leg without letting it touch the ground",
            "Maintain control and rhythm throughout the circular motion",
            "Use both clockwise and counter-clockwise directions"
        ]
    ],
    "Wraps Around Left Leg" => [
        "purpose" => "Develops ball control and coordination around a single leg, focusing on left-side ball handling.",
        "execution" => [
            "Starting with your left leg in front and your right leg back",
            "Wrap the ball around only your left leg without letting it touch the ground",
            "Maintain control and rhythm throughout the circular motion",
            "Use both clockwise and counter-clockwise directions"
        ]
    ],
    "Wraps Double Leg" => [
        "purpose" => "Combines single-leg wraps with double-leg wraps for comprehensive ball control development.",
        "execution" => [
            "Start with your legs together",
            "Step your right leg forward and circle your right leg with the basketball",
            "Immediately step back with your right leg so that your feet are together and circle both legs",
            "Step out with your left leg and circle it before stepping back together and wrapping the ball around them both",
            "Continue this process maintaining smooth transitions"
        ]
    ],
    "Single Leg" => [
        "purpose" => "Advanced coordination drill that alternates between single-leg wraps in a flowing motion.",
        "execution" => [
            "Part of the double leg/single leg combination drill",
            "Alternate wrapping around individual legs while maintaining ball control",
            "Focus on smooth footwork transitions between positions",
            "Keep the ball close to your body throughout the movements"
        ]
    ],
    "Drops" => [
        "purpose" => "Develops hand speed and coordination by quickly moving hands from front to back while the ball is in motion.",
        "execution" => [
            "Start in a squat position with both hands and the basketball in front of you",
            "Drop the ball between your legs (only a few inches off the ground)",
            "Let it bounce once, then take both hands behind your legs before catching it",
            "Then drop the ball again from behind and take both hands back to the front and catch it",
            "Repeat continuously with quick hand movements"
        ]
    ],
    "Straddle Flip" => [
        "purpose" => "Advanced hand-eye coordination drill that requires quick hand swapping while the ball is airborne.",
        "execution" => [
            "Start with one hand in front and one hand behind while holding the ball between your legs",
            "Quickly flip the ball up an inch or two",
            "Swap hand positions from front to back",
            "Catch the ball before it hits the ground",
            "Repeat with alternating hand positions"
        ]
    ],
    "Machine Gun" => [
        "purpose" => "Develops quick hands and low dribbling control while in a kneeling position.",
        "execution" => [
            "Kneel down onto the ground",
            "Alternate both hands to keep the ball as low as possible to the ground",
            "The ball should stay in the same spot",
            "Focus on speed and control with minimal ball movement"
        ]
    ],
    "Pound Dribble Shoulder Height Right Hand" => [
        "purpose" => "Challenges players to maintain control while dribbling at an uncomfortable height with their right hand.",
        "execution" => [
            "Pound the ball as hard as you can while dribbling at around shoulder height",
            "Use only your right hand for the entire drill",
            "Maintain proper stance and balance despite the high dribble",
            "Focus on wrist control and ball placement"
        ]
    ],
    "Pound Dribble Shoulder Height Left Hand" => [
        "purpose" => "Challenges players to maintain control while dribbling at an uncomfortable height with their left hand.",
        "execution" => [
            "Pound the ball as hard as you can while dribbling at around shoulder height",
            "Use only your left hand for the entire drill",
            "Maintain proper stance and balance despite the high dribble",
            "Focus on wrist control and ball placement"
        ]
    ],
    "Dribble around Right Leg Right Hand" => [
        "purpose" => "Develops ball control and coordination while dribbling in a circular motion around the right leg.",
        "execution" => [
            "Start in a wide stance",
            "Keeping the ball low to the ground, dribble the ball in a circle around your right leg",
            "Use only your right hand for the entire motion",
            "Maintain control throughout the circular path"
        ]
    ],
    "Dribble around Left Leg Left Hand" => [
        "purpose" => "Develops ball control and coordination while dribbling in a circular motion around the left leg.",
        "execution" => [
            "Start in a wide stance",
            "Keeping the ball low to the ground, dribble the ball in a circle around your left leg",
            "Use only your left hand for the entire motion",
            "Maintain control throughout the circular path"
        ]
    ],
    "Kills Left Hand" => [
        "purpose" => "Develops ball control through height variation and quick stops using the left hand.",
        "execution" => [
            "Start by dribbling the ball at your ankles with left hand",
            "Gradually dribble the ball higher on each bounce",
            "When you get to as high as you can, 'kill' the basketball by stopping it a few inches off the ground",
            "Dribble back up to your shoulder height",
            "Repeat continuous motion with left hand only"
        ]
    ],
    "3-Dribble Through the Legs" => [
        "purpose" => "Combines pound dribbles with through-the-legs crossovers for advanced ball handling.",
        "execution" => [
            "Pound the ball 3 times before crossing it over through your legs",
            "Then pound the ball 3 times before crossing it back",
            "Repeat this process making sure that you're pounding the ball hard",
            "Maintain rhythm and control throughout the sequence"
        ]
    ],
    "3-Dribble Behind the Back" => [
        "purpose" => "Combines pound dribbles with behind-the-back crossovers for advanced ball handling.",
        "execution" => [
            "Pound the ball 3 times before crossing it behind your back",
            "Then pound the ball 3 times before crossing it back",
            "Repeat this process making sure that you're pounding the ball hard",
            "Maintain rhythm and control throughout the sequence"
        ]
    ],
    "Triples Crossover, Through the Legs, Behind the Back" => [
        "purpose" => "Advanced combination drill that sequences three different crossover moves in a specific pattern.",
        "execution" => [
            "Perform the moves in this exact sequence: crossover, between the legs, behind the back, through the legs",
            "Continue this sequence without breaking rhythm",
            "Focus on smooth transitions between each move type",
            "Maintain control and proper form throughout"
        ]
    ],
    "Front V-Dribble Right Hand" => [
        "purpose" => "Develops side-to-side ball control using only the right hand in a V-shaped pattern.",
        "execution" => [
            "Using only your right hand, dribble the ball from side to side in the shape of a 'v' in front of your body",
            "Keep the ball under control throughout the V-motion",
            "Maintain consistent rhythm and ball height",
            "Focus on wrist control and hand placement"
        ]
    ],
    "Front V-Dribble Left Hand" => [
        "purpose" => "Develops side-to-side ball control using only the left hand in a V-shaped pattern.",
        "execution" => [
            "Using only your left hand, dribble the ball from side to side in the shape of a 'v' in front of your body",
            "Keep the ball under control throughout the V-motion",
            "Maintain consistent rhythm and ball height",
            "Focus on wrist control and hand placement"
        ]
    ],
    "Side V-Dribble Right Hand" => [
        "purpose" => "Develops front-to-back ball control using only the right hand in a V-shaped pattern beside the body.",
        "execution" => [
            "Using only your right hand, dribble the ball backwards and forwards beside your body in the shape of a 'v'",
            "Keep the ball close to your body throughout the motion",
            "Maintain control and consistent ball height",
            "Focus on smooth forward and backward movements"
        ]
    ],
    "Side V-Dribble Left Hand" => [
        "purpose" => "Develops front-to-back ball control using only the left hand in a V-shaped pattern beside the body.",
        "execution" => [
            "Using only your left hand, dribble the ball backwards and forwards beside your body in the shape of a 'v'",
            "Keep the ball close to your body throughout the motion",
            "Maintain control and consistent ball height",
            "Focus on smooth forward and backward movements"
        ]
    ],
    "Double Pound at Ankle Height" => [
        "purpose" => "Develops ambidextrous control with low, powerful dribbles using both hands simultaneously.",
        "execution" => [
            "Dribble both basketballs as hard as you can at ankle height",
            "Maintain control despite the power and low height",
            "Keep both balls in rhythm with each other",
            "Focus on wrist strength and ball control"
        ]
    ],
    "Double Pound at Shoulders Height" => [
        "purpose" => "Challenges players to maintain control with high, powerful dribbles using both hands simultaneously.",
        "execution" => [
            "Dribble both basketballs as hard as you can at shoulder height",
            "Maintain control despite the power and height",
            "Keep both balls in rhythm with each other",
            "Focus on wrist strength and proper form"
        ]
    ],
    "Double Pound Alternating" => [
        "purpose" => "Develops coordination and timing by alternating the dribble of each basketball.",
        "execution" => [
            "With the balls at a comfortable height, alternate the dribbling of each basketball",
            "Create a rhythmic pattern between the two balls",
            "Maintain control and consistent height for both balls",
            "Focus on timing and hand coordination"
        ]
    ],
    "One HighOne Low" => [
        "purpose" => "Advanced coordination drill that challenges players to control balls at different heights simultaneously.",
        "execution" => [
            "Dribble one of the basketballs at ankle height and one of them at shoulder height",
            "Maintain control of both balls despite the height difference",
            "Keep both balls in their respective height ranges",
            "Focus on divided attention and hand independence"
        ]
    ],
    "Double Wall Dribbling" => [
        "purpose" => "Develops power and control by dribbling both basketballs against a wall simultaneously.",
        "execution" => [
            "Dribble both basketballs against the wall simultaneously at shoulder height",
            "Maintain control and rhythm with both balls",
            "Focus on consistent power and ball placement",
            "Use proper wrist snap for maximum control"
        ]
    ],
    "3 Dribble Double Crossover" => [
        "purpose" => "Advanced two-ball drill that combines pound dribbles with simultaneous crossovers.",
        "execution" => [
            "Pound dribble both basketballs 3 times",
            "Then cross the balls over at the same time",
            "Perform another 3 dribbles before crossing over again",
            "Maintain rhythm and control with both balls throughout"
        ]
    ],
    "3 Dribble Through the LegsCrossover" => [
        "purpose" => "Complex two-ball drill combining different crossover types simultaneously.",
        "execution" => [
            "Pound dribble both basketballs 3 times",
            "Then cross one ball over in front of you and one ball through your legs at the same time",
            "Maintain control and balance during the simultaneous crossovers",
            "Focus on hand independence and coordination"
        ]
    ],
    "3 Dribble Behind the BackCrossover" => [
        "purpose" => "Advanced two-ball drill combining front and behind-the-back crossovers simultaneously.",
        "execution" => [
            "Pound dribble both basketballs 3 times",
            "Then cross one ball over in front of you and one ball behind your back at the same time",
            "Maintain control and balance during the complex movements",
            "Focus on spatial awareness and hand coordination"
        ]
    ],
    "Two Ball Figure Eight" => [
        "purpose" => "Develops ambidextrous ball control by dribbling both balls in figure-eight patterns around each leg.",
        "execution" => [
            "Keeping the ball close to the ground",
            "Use your right hand to dribble one ball around your right leg",
            "Use your left hand to dribble the other ball around your left leg",
            "Maintain control and rhythm with both balls simultaneously",
            "Focus on hand independence and ball placement"
        ]
    ],
    "Double V-Dribble in Front" => [
        "purpose" => "Develops coordinated side-to-side ball control with both hands working simultaneously.",
        "execution" => [
            "Dribble both basketballs side to side in front of you simultaneously",
            "Maintain the V-shaped pattern with both balls",
            "Keep both balls in sync with each other",
            "Focus on wrist control and consistent rhythm"
        ]
    ],
    "Double V-Dribble on Side" => [
        "purpose" => "Develops coordinated front-to-back ball control with both hands working simultaneously.",
        "execution" => [
            "Dribble both basketballs from back to front beside you simultaneously",
            "Maintain the V-shaped pattern with both balls",
            "Keep both balls in sync with each other",
            "Focus on smooth forward and backward movements"
        ]
    ],
    "Kills" => [
        "purpose" => "Advanced two-ball version of the kills drill, developing height control with both hands simultaneously.",
        "execution" => [
            "Pound both basketballs higher and higher starting from ankle height",
            "When you reach the shoulders, 'kill' both balls stopping them a few inches from the ground",
            "Dribble both balls back up to shoulder height",
            "Repeat continuous motion with both hands working together"
        ]
    ]
];

try {
    // Fetch drill information
    $stmt = $pdo->prepare("
        SELECT 
            ta.*, 
            td.day_name, 
            tw.week_number
        FROM training_activities ta
        JOIN training_days td ON ta.day_id = td.day_id
        JOIN training_weeks tw ON td.week_id = tw.week_id
        WHERE ta.activity_id = ? AND ta.type = 'Drill'
    ");
    $stmt->execute([$activity_id]);
    $drill = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$drill) {
        echo "<div class='text-center text-red-600 py-8'>";
        echo "<p class='text-lg font-semibold'>❌ Drill Not Found</p>";
        echo "<p class='text-sm text-gray-600 mt-2'>The requested drill details are not available.</p>";
        echo "</div>";
        exit;
    }

    // Get detailed description from our array
    $drillName = $drill['title'];
    $detailedDescription = isset($drillDescriptions[$drillName]) ? $drillDescriptions[$drillName] : null;

    // Display drill details
    echo "<div class='space-y-6'>";
    
    // Header Section
    echo "
    <div class='text-center border-b border-yellow-200 pb-6'>
        <h2 class='text-2xl font-bold text-yellow-800 mb-2'>🏀 " . htmlspecialchars($drill['title']) . "</h2>
        <div class='flex flex-wrap justify-center gap-4 text-sm text-gray-600'>
            <span class='bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full'>Drill</span>
            <span class='bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full'>Week " . htmlspecialchars($drill['week_number']) . "</span>
            <span class='bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full'>" . htmlspecialchars($drill['day_name']) . "</span>
        </div>
    </div>
    ";

    // Drill Image Section (if available)
    if ($detailedDescription && isset($detailedDescription['image'])) {
        $imagePath = '../' . $detailedDescription['image']['url'];
        echo "
        <div class='bg-white rounded-lg p-4 border border-yellow-200'>
            <h4 class='font-bold text-lg text-yellow-800 mb-3'>🖼️ Drill Demonstration</h4>
            <div class='flex justify-center'>
                <img src='" . htmlspecialchars($imagePath) . "' 
                     alt='" . htmlspecialchars($detailedDescription['image']['alt']) . "' 
                     class='max-w-full h-auto rounded-lg shadow-md max-h-96 object-contain'
                     onerror=\"this.style.display='none'\">
            </div>
            <p class='text-center text-sm text-gray-600 mt-2'>" . htmlspecialchars($detailedDescription['image']['caption']) . "</p>
        </div>
        ";
    }

    // Basic Information
    echo "
    <div class='bg-yellow-50 rounded-lg p-4'>
        <h4 class='font-bold text-lg text-yellow-800 mb-3'>📋 Drill Information</h4>
        <div class='space-y-3 text-sm'>
            <div><span class='font-semibold'>Week:</span> Week " . htmlspecialchars($drill['week_number']) . "</div>
            <div><span class='font-semibold'>Day:</span> " . htmlspecialchars($drill['day_name']) . "</div>
            <div><span class='font-semibold'>Description:</span> " . htmlspecialchars($drill['description'] ?? 'No description available') . "</div>
        </div>
    </div>
    ";

    // Detailed Description Section
    if ($detailedDescription) {
        echo "
        <div class='bg-blue-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-blue-800 mb-3'>📖 Drill Details</h4>
            <div class='space-y-4 text-sm'>
                <div>
                    <h5 class='font-semibold text-blue-700 mb-2'>Purpose</h5>
                    <p class='text-gray-700'>" . htmlspecialchars($detailedDescription['purpose']) . "</p>
                </div>
        ";
        
        if (isset($detailedDescription['setup'])) {
            echo "
                <div>
                    <h5 class='font-semibold text-blue-700 mb-2'>Setup</h5>
                    <p class='text-gray-700'>" . htmlspecialchars($detailedDescription['setup']) . "</p>
                </div>
            ";
        }
        
        echo "
                <div>
                    <h5 class='font-semibold text-blue-700 mb-2'>Execution</h5>
                    <ul class='list-disc list-inside space-y-2 text-gray-700'>
        ";
        
        foreach ($detailedDescription['execution'] as $step) {
            echo "<li>" . htmlspecialchars($step) . "</li>";
        }
        
        echo "
                    </ul>
                </div>
        ";
        
        if (isset($detailedDescription['coachingpoints'])) {
            echo "
                <div>
                    <h5 class='font-semibold text-blue-700 mb-2'>Coaching Points</h5>
                    <ul class='list-disc list-inside space-y-2 text-gray-700'>
            ";
            
            foreach ($detailedDescription['coachingpoints'] as $point) {
                echo "<li>" . htmlspecialchars($point) . "</li>";
            }
            
            echo "
                    </ul>
                </div>
            ";
        }
        
        echo "
            </div>
        </div>
        ";
    } else {
        echo "
        <div class='bg-blue-50 rounded-lg p-4'>
            <h4 class='font-bold text-lg text-blue-800 mb-3'>📖 Drill Details</h4>
            <p class='text-gray-700 text-sm'>Detailed description not available for this drill.</p>
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