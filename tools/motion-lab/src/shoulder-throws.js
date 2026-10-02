// Hand-keyed te-waza for Waza Atlas. Right-handed tori throughout.
// Frame: tori starts at z<0 facing +z (yaw 0); uke at z>0 facing -z (yaw 180).
// Tori's right side is world -x while facing uke; after turning in (yaw 180) it is world +x.
// Uke's right side is world +x. Pitch > 0 leans forward; a forward somersault ends at pitch 270 (on the back).

const T0 = {z:-0.33, pitch:5, bend:8, lKnee:16, rKnee:16, lHipF:9, rHipF:9, lShF:50, lElb:60, rShF:55, rElb:70, ik_l_sleeve:1, ik_r_lapel:1};
const U0 = {z:0.33, yaw:180, pitch:4, bend:7, lKnee:16, rKnee:16, lHipF:9, rHipF:9, lShF:55, lElb:70, rShF:50, rElb:60, ik_r_lapel:1, ik_l_sleeve:1};
// Uke on the back after a forward rotation, slapping with the free (left) arm.
const LAND_FWD = {pitch:270, bend:12, plant:1, lHipF:45, rHipF:40, lKnee:45, rKnee:35, lShF:30, lShA:60, lElb:5, rShF:25, rShA:45, rElb:5, head:40, ik_r_lapel:0, ik_l_sleeve:0};
// Uke on the back after falling backward.
const LAND_BACK = {pitch:-90, bend:12, plant:1, lHipF:45, rHipF:40, lKnee:45, rKnee:35, lShF:25, lShA:50, lElb:5, rShF:25, rShA:50, rElb:5, head:40, ik_r_lapel:0, ik_l_sleeve:0};

const PH = (k, kz, ts, tk, kk, uk) => [
  {t:0, name:'Kumikata', en:'Gripping', text:k},
  {t:kz[0], name:'Kuzushi', en:'Breaking balance', text:kz[1]},
  {t:ts[0], name:'Tsukuri', en:'Entry', text:ts[1]},
  {t:tk[0], name:'Kake', en:'Execution', text:tk[1]},
  {t:kk[0], name:'Ukemi', en:'Breakfall', text:uk},
];
const GRIP = 'Right-handed grip: tori holds uke’s left lapel with the right hand and uke’s right sleeve with the left.';

/* ---------- Shoulder throws ---------- */

// Shared shape of the over-the-shoulder flight: uke rolls over tori's right shoulder (world +x after tori turns in).
function overShoulderUke({tLoad, tLift, tTop, tDown, tLand, xSide=0.15, landZ=-0.95, peakY=1.32, extra={}}) {
  return [
    [0, U0],
    [0.9, {}],
    [tLoad - 0.5, {z:0.31, pitch:9, rHipF:22, rKnee:18, lHipF:-5, head:4}],
    // Loaded: uke lets go of tori's gi; the right arm is drawn forward over tori's right shoulder.
    [tLoad, {z:0.34, pitch:26, bend:14, rHipF:14, lHipF:4, lKnee:10, rKnee:10, ik_r_lapel:0, ik_l_sleeve:0, rShF:100, rShA:12, rElb:10, lShF:45, lShA:30, lElb:40, ...extra.load}],
    [tLift, {plant:0, y:1.10, z:0.24, x:xSide*0.4, pitch:60, bend:18, lHipF:-8, rHipF:-4, lKnee:22, rKnee:18, head:15, rShF:120, ik_r_wrist:1, ...extra.lift}],
    [tTop, {y:peakY, z:0.0, x:xSide, pitch:130, bend:8, lHipF:20, rHipF:16, lKnee:40, rKnee:30, roll:8, lShF:60, lShA:45, rShF:110, rShA:20}],
    [tDown, {y:0.80, z:(landZ*0.55), x:xSide, pitch:205, lHipF:40, rHipF:32, lKnee:50, rKnee:40, roll:14}],
    [tLand, {...LAND_FWD, y:0.16, z:landZ, x:xSide, roll:12, ik_r_wrist:0, rShF:105, rShA:8, rElb:10}],
    [tLand + 0.65, {rShF:25, rShA:45, rElb:5}],
    [tLand + 1.0, {rHipF:30, lHipF:22, head:28}],
    [5.0, {}],
  ];
}

const seoiNage = {
  slug:'seoi-nage', duration:5,
  phases: PH(GRIP,
    [1.0, 'Tori pulls uke forward and up with the left hand, so uke rises onto the toes of the right foot.'],
    [1.5, 'Tori steps the right foot in front of uke’s right foot and pivots on it, turning the back to uke. The right elbow goes under uke’s right armpit, and both knees bend so tori’s hips sit below uke’s.'],
    [2.2, 'Tori straightens the legs, bends forward and pulls the left hand down past the left hip. Uke goes over tori’s right shoulder.'],
    [3.1, null],
    'Uke lands on the back in front of tori. Tori keeps hold of the sleeve to control the fall.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.4, {z:-0.12, yaw:40, rHipF:35, rKnee:45, lKnee:35, lHipF:20, lShF:72, lElb:30, bend:12}],
    [1.7, {z:0.0, yaw:120, lKnee:65, rKnee:60, lHipF:35, rHipF:38}],
    [1.95, {z:0.08, yaw:180, pitch:12, bend:18, lHipF:42, rHipF:42, lKnee:82, rKnee:82, lHipA:14, rHipA:14, head:5}],
    [2.35, {z:0.05, pitch:28, bend:45, ik_l_sleeve:0, lShF:70, lShA:-40, lElb:30, lKnee:55, rKnee:55, lHipF:58, rHipF:58, twist:8, head:18}],
    [2.85, {z:0.02, pitch:40, bend:70, lKnee:28, rKnee:28, lHipF:70, rHipF:70, twist:22, roll:-8, head:25, lShF:35, lShA:-20, lElb:20}],
    [3.4, {pitch:25, bend:60, twist:-12, roll:0, ik_l_sleeve:1, lKnee:38, rKnee:38, ik_r_lapel:0, rShF:40, rElb:30}],
    [3.9, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.5, {pitch:10, bend:20, head:8}],
    [5.0, {}],
  ],
  uke: overShoulderUke({tLoad:1.95, tLift:2.35, tTop:2.65, tDown:2.95, tLand:3.25}),
};

const ipponSeoiNage = {
  slug:'ippon-seoi-nage', duration:5,
  phases: PH(GRIP,
    [1.0, 'Tori pulls uke’s right sleeve forward and up, and lets go of the lapel with the right hand.'],
    [1.5, 'Tori steps in and turns, driving the right arm up under uke’s right armpit and clamping uke’s upper arm in the crook of the elbow. Tori’s hips go low, below uke’s belt.'],
    [2.2, 'Tori lifts with the legs, bends forward and turns to the left. Uke is carried over tori’s right shoulder by the trapped arm.'],
    [3.1, null],
    'Uke lands on the back. Tori still holds uke’s right sleeve.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.4, {z:-0.12, yaw:40, rHipF:35, rKnee:45, lKnee:35, lHipF:20, lShF:72, lElb:30, bend:12, ik_r_lapel:0, rShF:80, rShA:30, rElb:40}],
    [1.7, {z:0.0, yaw:120, lKnee:65, rKnee:60, lHipF:35, rHipF:38}],
    [1.95, {z:0.08, yaw:180, pitch:12, bend:18, lHipF:42, rHipF:42, lKnee:85, rKnee:85, lHipA:14, rHipA:14, head:5, ik_r_shoulder_s:1}],
    [2.35, {z:0.05, pitch:28, bend:45, ik_l_sleeve:0, lShF:70, lShA:-40, lElb:30, lKnee:55, rKnee:55, lHipF:58, rHipF:58, twist:8, head:18}],
    [2.85, {z:0.02, pitch:40, bend:72, lKnee:26, rKnee:26, lHipF:72, rHipF:72, twist:24, roll:-8, head:25, lShF:35, lShA:-20, lElb:20}],
    [3.4, {pitch:25, bend:60, twist:-12, roll:0, ik_l_sleeve:1, lKnee:38, rKnee:38, ik_r_shoulder_s:0, rShF:40, rShA:15, rElb:30}],
    [3.9, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.5, {pitch:10, bend:20, head:8}],
    [5.0, {}],
  ],
  uke: overShoulderUke({tLoad:1.95, tLift:2.35, tTop:2.65, tDown:2.95, tLand:3.25,
    extra:{load:{rShF:95, rShA:20, rElb:15}, lift:{rShF:120, rElb:10}}}),
};

const seoiOtoshi = {
  slug:'seoi-otoshi', duration:5,
  phases: PH(GRIP,
    [1.0, 'Tori pulls uke forward to the right front corner, so uke’s weight comes onto the right foot.'],
    [1.5, 'Tori turns in as for seoi-nage, but keeps going down: the right knee drops to the mat in front of uke’s right foot, with tori’s back against uke’s chest.'],
    [2.2, 'Tori pulls down hard with both hands and turns to the left. Uke tips over tori’s right shoulder and the blocking right leg.'],
    [3.0, null],
    'Uke lands on the back in front of tori, who stays on one knee holding the sleeve.'),
  tori: [
    [0, T0],
    [0.9, {bend:9}],
    [1.4, {z:-0.12, yaw:40, rHipF:35, rKnee:45, lKnee:35, lHipF:20, lShF:72, lElb:30, bend:12}],
    [1.7, {z:0.02, yaw:120, lKnee:75, rKnee:80, lHipF:50, rHipF:20}],
    [1.95, {z:0.10, yaw:180, pitch:15, bend:22, lHipF:88, lKnee:95, rHipF:15, rKnee:92, lHipA:12, rHipA:8, head:5}],
    [2.35, {z:0.06, pitch:28, bend:45, lHipF:100, rHipF:28, ik_l_sleeve:0, lShF:70, lShA:-40, lElb:30, twist:10, head:18, lShF:40, lElb:30}],
    [2.8, {pitch:35, bend:60, lHipF:108, rHipF:35, twist:26, roll:-10, head:22, lShF:35, lShA:-20, lElb:20}],
    [3.4, {pitch:22, bend:60, lHipF:95, rHipF:22, twist:-12, roll:0, ik_l_sleeve:1, ik_r_lapel:0, rShF:40, rElb:30}],
    [3.9, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.5, {pitch:10, bend:20, lHipF:85, rHipF:10, head:8}],
    [5.0, {}],
  ],
  uke: overShoulderUke({tLoad:1.95, tLift:2.35, tTop:2.62, tDown:2.9, tLand:3.15, peakY:1.12, landZ:-0.9}),
};

const yamaArashi = {
  slug:'yama-arashi', duration:5,
  phases: PH('Tori holds uke’s right lapel deep with the right hand, on the same side, and uke’s right sleeve with the left.',
    [1.0, 'Tori pulls uke forward and to the right front corner, drawing uke’s right side tight against tori’s chest.'],
    [1.5, 'Tori turns in with the right hip and shoulder against uke’s right side. The right-hand lapel grip pins uke’s right shoulder.'],
    [2.2, 'Tori sweeps uke’s right leg back and up with the right leg while pulling with both hands. Uke is wheeled over tori’s right shoulder and hip.'],
    [3.1, null],
    'Uke lands on the back with a wide arc, slapping with the left arm.'),
  tori: [
    [0, {...T0, ik_r_lapel:0, ik_r_lapel_s:1}],
    [0.9, {bend:9}],
    [1.4, {z:-0.12, yaw:40, rHipF:35, rKnee:45, lKnee:35, lHipF:20, lShF:72, lElb:30, bend:12}],
    [1.7, {z:0.02, x:0.02, yaw:115, lKnee:50, rKnee:45, lHipF:28, rHipF:28}],
    [1.95, {z:0.10, x:0.04, yaw:165, pitch:12, bend:16, lHipF:30, rHipF:30, lKnee:55, rKnee:50, lHipA:10, rHipA:12, head:5}],
    [2.4, {pitch:30, bend:40, rHipF:-45, ik_l_sleeve:0, lShF:70, lShA:-40, lElb:30, rHipA:20, rKnee:10, lKnee:35, twist:10, head:15}],
    [2.85, {pitch:42, bend:62, rHipF:-70, rHipA:15, rKnee:5, lKnee:22, twist:26, roll:-10, head:22, lShF:35, lShA:-20, lElb:20}],
    [3.4, {pitch:25, bend:60, rHipF:20, rHipA:10, rKnee:25, twist:-12, roll:0, ik_l_sleeve:1, ik_r_lapel_s:0, rShF:40, rElb:30}],
    [3.9, {ik_l_sleeve:0, lShF:45, lElb:50}],
    [4.5, {pitch:10, bend:20, head:8}],
    [5.0, {}],
  ],
  uke: overShoulderUke({tLoad:1.95, tLift:2.4, tTop:2.7, tDown:3.0, tLand:3.3, xSide:0.22, peakY:1.42, landZ:-1.0,
    extra:{lift:{rHipF:-20, lHipF:10, rKnee:10}}}),
};

module.exports = {seoiNage, ipponSeoiNage, seoiOtoshi, yamaArashi};
