// Batch 2: forward throws without loading uke on the back. See shoulder-throws.js for the frame conventions.
const T0 = {z:-0.33, pitch:5, bend:8, lKnee:16, rKnee:16, lHipF:9, rHipF:9, lShF:50, lElb:60, rShF:55, rElb:70, ik_l_sleeve:1, ik_r_lapel:1};
const U0 = {z:0.33, yaw:180, pitch:4, bend:7, lKnee:16, rKnee:16, lHipF:9, rHipF:9, lShF:55, lElb:70, rShF:50, rElb:60, ik_r_lapel:1, ik_l_sleeve:1};
const LAND_FWD = {pitch:270, bend:12, plant:1, lHipF:45, rHipF:40, lKnee:45, rKnee:35, lShF:30, lShA:60, lElb:5, rShF:25, rShA:45, rElb:5, head:40, ik_r_lapel:0, ik_l_sleeve:0};
const PH = (k, kz, ts, tk, kk, uk) => [
  {t:0, name:'Kumikata', en:'Gripping', text:k},
  {t:kz[0], name:'Kuzushi', en:'Breaking balance', text:kz[1]},
  {t:ts[0], name:'Tsukuri', en:'Entry', text:ts[1]},
  {t:tk[0], name:'Kake', en:'Execution', text:tk[1]},
  {t:kk[0], name:'Ukemi', en:'Breakfall', text:uk},
];
const GRIP = 'Right-handed grip: tori holds uke’s left lapel with the right hand and uke’s right sleeve with the left.';

const taiOtoshi = {
  slug:'tai-otoshi', duration:5,
  phases: PH(GRIP,
    [1.0, 'Tori pulls uke to the right front corner. Uke steps forward onto the right foot, and the weight goes over its toes.'],
    [1.5, 'Tori turns to the left on the balls of the feet. The left leg bends and takes the weight, and the straight right leg is placed across the outside of uke’s right shin. Tori’s hips stay clear of uke.'],
    [2.1, 'Tori turns the upper body to the left, pulling round with the left hand and pushing with the right as if turning a steering wheel. Uke trips over the blocking right leg.'],
    [2.9, null],
    'Uke rolls forward over tori’s leg and lands on the back to tori’s right front.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.4, {z:-0.22, x:0.02, yaw:40, rHipF:30, rKnee:22, lKnee:28, lHipF:15, lShF:75, lElb:25}],
    [1.75, {z:-0.14, x:-0.08, yaw:125, lHipF:35, lKnee:55, rHipF:12, rKnee:12, rHipA:18, pitch:8}],
    [2.05, {z:-0.12, x:-0.14, yaw:160, pitch:10, bend:10, lHipF:52, lKnee:68, lHipA:10, rHipF:-13, rHipA:24, rKnee:4, head:5}],
    [2.4, {twist:25, bend:18, roll:-5, pitch:14, head:15, rHipF:-9, lHipF:56, ik_l_sleeve:0, lShF:70, lShA:-40, lElb:30}],
    [2.75, {twist:38, bend:28, pitch:20, roll:-8, rHipF:-3, lHipF:62, lShF:35, lShA:-20, lElb:20, ik_r_lapel:0, rShF:70, rShA:20, rElb:45}],
    [3.4, {twist:-10, bend:45, pitch:24, roll:0, lKnee:50, rHipF:0, lHipF:66, ik_l_sleeve:1}],
    [3.9, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.5, {pitch:8, bend:14, twist:0, rHipF:10, rHipA:10, rKnee:12, lHipF:14, lKnee:22, lHipA:8, head:6}],
    [5.0, {}],
  ],
  uke: [
    [0, U0],
    [0.9, {}],
    [1.45, {z:0.30, x:0.04, pitch:10, rHipF:28, rKnee:15, lHipF:-6, head:5}],
    [1.95, {z:0.26, x:0.10, pitch:16, bend:10, rHipF:22, rKnee:10, lHipF:-14, lKnee:28, head:8}],
    [2.3, {plant:0, y:0.98, x:0.18, z:0.10, yaw:168, pitch:68, bend:14, rHipF:-22, rKnee:8, lHipF:12, lKnee:40, ik_r_lapel:0, ik_l_sleeve:0, ik_r_wrist:1, lShF:75, lShA:40, lElb:20, rShF:100, rShA:15, rElb:10, head:12}],
    [2.62, {y:0.78, x:0.33, z:-0.22, yaw:152, pitch:165, lHipF:30, rHipF:22, lKnee:50, rKnee:40, roll:-8}],
    [2.95, {...LAND_FWD, y:0.15, x:0.40, z:-0.50, yaw:142, roll:-10, ik_r_wrist:0, rShF:105, rShA:8, rElb:10}],
    [3.6, {rShF:25, rShA:45, rElb:5}],
    [4.0, {rHipF:30, lHipF:24, head:28}],
    [5.0, {}],
  ],
};

const ukiOtoshi = {
  slug:'uki-otoshi', duration:5,
  phases: PH(GRIP,
    [1.0, 'Tori steps back and draws uke forward. Uke follows with the right foot, and the weight moves onto the front of the feet.'],
    [1.5, 'As uke steps in, tori slides the left foot far back and drops onto the left knee, keeping both hands pulling.'],
    [2.0, 'Tori pulls down and round to the left in one sweep of both arms, as if pulling a rope. There is no contact with the body or legs: uke is thrown by the hands alone.'],
    [2.8, null],
    'Uke somersaults forward past tori’s left side and lands on the back.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.4, {z:-0.44, lHipF:-20, lKnee:22, rHipF:20, rKnee:25, pitch:0, bend:4, lShF:70, lElb:30, rShF:70, rElb:40}],
    [1.9, {z:-0.62, x:-0.05, yaw:20, pitch:5, bend:6, lHipF:5, lKnee:92, rHipF:92, rKnee:95, lHipA:12, rHipA:10, twist:12, head:4}],
    [2.25, {twist:28, bend:16, pitch:8, lHipF:8, rHipF:95, head:10, ik_l_sleeve:0, lShF:70, lShA:-35, lElb:30}],
    [2.7, {twist:38, bend:24, pitch:12, lHipF:12, rHipF:99, roll:-6, lShF:35, lShA:-15, lElb:20, ik_r_lapel:0, rShF:60, rShA:25, rElb:40}],
    [3.4, {twist:30, bend:30, pitch:14, lHipF:14, rHipF:101, roll:0, ik_l_sleeve:1}],
    [4.0, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.6, {twist:5, bend:15, head:6}],
    [5.0, {}],
  ],
  uke: [
    [0, U0],
    [0.9, {}],
    [1.4, {z:0.24, pitch:10, rHipF:30, rKnee:16, lHipF:-10, head:4}],
    [1.9, {z:0.06, x:0.20, yaw:165, pitch:28, bend:12, lHipF:28, lKnee:22, rHipF:-18, rKnee:10, head:10}],
    [2.22, {plant:0, y:0.95, z:-0.16, x:0.28, yaw:155, pitch:95, bend:15, rHipF:-30, lHipF:10, lKnee:30, rKnee:20, ik_r_lapel:0, ik_l_sleeve:0, ik_r_wrist:1, lShF:120, lShA:30, lElb:10, rShF:95, rShA:10, rElb:10}],
    [2.55, {y:0.72, x:0.44, z:-0.40, yaw:135, pitch:185, lHipF:30, rHipF:20, lKnee:50, rKnee:40, roll:10}],
    [2.85, {...LAND_FWD, y:0.15, x:0.58, z:-0.62, yaw:120, roll:6, ik_r_wrist:0, rShF:105, rShA:8, rElb:10}],
    [3.6, {rShF:25, rShA:45, rElb:5}],
    [4.0, {rHipF:30, lHipF:24, head:28}],
    [5.0, {}],
  ],
};

const uchiMataSukashi = {
  slug:'uchi-mata-sukashi', duration:5,
  phases: PH(GRIP+' Here uke attacks first.',
    [1.0, 'Uke turns in for a right uchi-mata, back to tori, and starts to sweep the right leg up between tori’s legs.'],
    [1.5, 'Tori slips the attack: the left leg is drawn back and to the side, and the hips turn left, so uke’s sweeping leg meets only air.'],
    [2.0, 'With uke stranded on one leg and already falling forward, tori pulls the sleeve round and down to the left and turns. Uke’s own sweep carries them over.'],
    [2.8, null],
    'Uke turns over in the air and lands on the back in front of tori.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.3, {z:-0.36}],
    [1.6, {ik_r_lapel:0, ik_r_beltback:1, ik_l_sleeve:0, lShF:60, lShA:-15, lElb:40}],
    [1.8, {x:0.08, z:-0.42, yaw:25, lHipF:-25, lKnee:30, lHipA:20, rKnee:25, rHipF:15, pitch:5}],
    [2.1, {yaw:40, twist:20, bend:22, pitch:12, lHipF:-10, lKnee:25, roll:-5, lShF:65, lShA:-45, lElb:20}],
    [2.6, {yaw:45, twist:25, bend:38, pitch:20, lShF:60, lShA:-40, lElb:10, ik_r_beltback:0, rShF:60, rShA:20, rElb:50}],
    [3.3, {twist:-5, bend:55, pitch:25, lHipF:40, lKnee:30, rHipF:35, rKnee:28, lShF:75, lShA:-35, lElb:8}],
    [3.9, {lShF:45, lShA:0, lElb:50}],
    [4.5, {twist:0, bend:15, pitch:8, roll:0, lHipF:10, lKnee:20, rHipF:10, rKnee:18}],
    [5.0, {}],
  ],
  uke: [
    [0, U0],
    [0.9, {}],
    [1.3, {z:0.18, x:-0.04, yaw:265, lHipF:25, lKnee:30, rKnee:25, bend:10}],
    [1.6, {z:0.02, x:-0.03, yaw:352, pitch:20, bend:20, lHipF:30, lKnee:35, rHipF:-30, rKnee:20, head:8, ik_r_lapel:0, ik_r_wrist:1}],
    [1.9, {pitch:42, bend:32, rHipF:-80, rKnee:5, rHipA:8, lKnee:28, head:14}],
    [2.2, {plant:0, y:1.02, z:0.14, x:-0.10, yaw:360, pitch:112, bend:24, rHipF:-60, lHipF:-18, lKnee:12, ik_l_sleeve:0, lShF:110, lShA:35, rShF:95, rShA:15, rElb:10}],
    [2.52, {y:0.82, z:0.28, x:-0.20, pitch:200, roll:18, rHipF:10, lHipF:20, lKnee:40}],
    [2.85, {...LAND_FWD, y:0.15, x:-0.24, z:0.40, yaw:355, roll:10, ik_r_wrist:1}],
    [3.9, {ik_r_wrist:0, rShF:25, rShA:45, rElb:5}],
    [4.3, {rHipF:30, lHipF:24, head:28}],
    [5.0, {}],
  ],
};

module.exports = {taiOtoshi, ukiOtoshi, uchiMataSukashi};
