// Pull the skeleton and keyframe code out of the real viewer, so the lab never drifts from what the site plays.
const fs = require('fs');
const path = require('path');
const src = fs.readFileSync(path.join(__dirname, '../../public/assets/atlas.js'), 'utf8');
const a = src.indexOf('/* ---------- 3D maths'), b = src.indexOf('/* ---------- API');
if (a < 0 || b < 0) throw new Error('Section markers not found in public/assets/atlas.js');
const core = src.slice(a, b);
fs.mkdirSync(path.join(__dirname, 'build'), {recursive: true});
fs.writeFileSync(path.join(__dirname, 'build/core.js'), core + '\nmodule.exports={fk,frame,keyed,sample,gripTarget,GRIP_TARGETS,JN,BONES,IDLE,len,sub,add,sc,dot,lerp3};\n');
fs.writeFileSync(path.join(__dirname, 'build/core.browser.js'), core + '\nwindow.LAB={fk,frame,keyed,sample,gripTarget,GRIP_TARGETS,JN,BONES,IDLE};\n');
