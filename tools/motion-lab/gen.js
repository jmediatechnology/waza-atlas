// node gen.js [slug ...]
// Writes data/motions/<slug>.json for every motion in src/ (or only the slugs given),
// then prints the geometry report and renders sheets/<slug>.png for review.
const fs = require('fs');
const path = require('path');
const {execFileSync} = require('child_process');

const motions = {};
for (const f of fs.readdirSync(path.join(__dirname, 'src')).filter(f => f.endsWith('.js'))) {
  Object.assign(motions, require(path.join(__dirname, 'src', f)));
}
const want = process.argv.slice(2);
const outDir = path.join(__dirname, '../../data/motions');
fs.mkdirSync(path.join(__dirname, 'sheets'), {recursive: true});

for (const m of Object.values(motions)) {
  if (want.length && !want.includes(m.slug)) continue;
  const json = {format: 'keyframes', version: 1, source: 'Hand-keyed animation', duration: m.duration, phases: m.phases, tracks: {tori: m.tori, uke: m.uke}};
  const out = path.join(outDir, m.slug + '.json');
  fs.writeFileSync(out, JSON.stringify(json, null, 1) + '\n');
  console.log(execFileSync('node', [path.join(__dirname, 'check.js'), out]).toString().trimEnd());
  execFileSync('node', [path.join(__dirname, 'sheet.js'), out, path.join(__dirname, 'sheets', m.slug + '.png'), '11']);
}
