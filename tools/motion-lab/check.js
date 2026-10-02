// node check.js <motion.json>
// Reports what the eye misses on a contact sheet: joints under the floor, bodies passing through each other,
// and grips whose hand cannot reach its target ("short").
const fs=require('fs'); require('./build-core.js');
const C=require('./build/core.js');
const m=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
const M={duration:m.duration,phases:m.phases,tori:C.keyed(m.tracks.tori),uke:C.keyed(m.tracks.uke)};
function segDist(p1,q1,p2,q2){ // closest distance between segments
  const d1=C.sub(q1,p1),d2=C.sub(q2,p2),r=C.sub(p1,p2),a=C.dot(d1,d1),e=C.dot(d2,d2),f=C.dot(d2,r);
  let s,t; const c=C.dot(d1,r),b=C.dot(d1,d2),den=a*e-b*b;
  s=den>1e-9?Math.min(1,Math.max(0,(b*f-c*e)/den)):0; t=(b*s+f)/e;
  if(t<0){t=0;s=Math.min(1,Math.max(0,-c/a));} else if(t>1){t=1;s=Math.min(1,Math.max(0,(b-c)/a));}
  return C.len(C.sub(C.add(p1,C.sc(d1,s)),C.add(p2,C.sc(d2,t))));
}
const BODY=[['head','neck'],['neck','pelvis'],['lHip','lKnee'],['lKnee','lAnk'],['rHip','rKnee'],['rKnee','rAnk']];
const issues=[]; const worst={}; let minTorso=9, minTorsoT=0, minLimb=9, minLimbT=0, nanAt=null;
for(let t=0;t<=m.duration+1e-9;t+=0.05){
  const pt=C.sample(M.tori,t), pu=C.sample(M.uke,t);
  const [Jt,Ju]=C.frame(M,t);
  for(const [nm,J] of [['tori',Jt],['uke',Ju]]) for(const j of C.JN){ if(J[j].some(Number.isNaN)) nanAt??=`${nm}.${j}@${t.toFixed(2)}`; if(J[j][1]<-0.02) issues.push(`${t.toFixed(2)}s ${nm} ${j} below floor (${J[j][1].toFixed(2)})`); }
  const dt=segDist(Jt.head,Jt.pelvis,Ju.head,Ju.pelvis); if(dt<minTorso){minTorso=dt;minTorsoT=t;}
  for(const A of BODY) for(const B of BODY){ const d=segDist(Jt[A[0]],Jt[A[1]],Ju[B[0]],Ju[B[1]]); if(d<minLimb){minLimb=d;minLimbT=t;} }
  // grip reach: an active grip whose wrist cannot reach its target
  for(const [who,p,J,O] of [['tori',pt,Jt,Ju],['uke',pu,Ju,Jt]]) for(const k of ['l','r']) for(const nm of C.GRIP_TARGETS) for(const sfx of ['','_s']){
    const w=p[`ik_${k}_${nm}${sfx}`]||0; if(w<0.95) continue;
    const side=sfx?k:(k==='l'?'r':'l'); const T=C.gripTarget(nm,O,side); const err=C.len(C.sub(J[k+'Wr'],T));
    if(err>0.05){ issues.push(`${t.toFixed(2)}s ${who} ${k}-hand ${nm}${sfx} short`); const key=`${who} ${k}-hand ${nm}${sfx} short`; worst[key]=Math.max(worst[key]||0,err); }
  }
}
const [JtE,JuE]=C.frame(M,m.duration);
const ukeUp=C.sub(JuE.neck,JuE.pelvis); const lying=Math.abs(ukeUp[1])/C.len(ukeUp)<0.5;
// collapse repeated issues into ranges
const grouped={}; for(const s of issues){const [t,...rest]=s.split(' '); const k=rest.join(' ').replace(/\(.*?\)|by [\d.]+ m/,'').trim(); (grouped[k]??=[]).push(parseFloat(t));}
console.log(`${process.argv[2].split('/').pop()}: ${m.duration}s, phases ${m.phases.map(p=>p.name+'@'+p.t).join(' ')}`);
if(nanAt) console.log('  NaN at '+nanAt);
console.log(`  closest torsos ${minTorso.toFixed(2)} m @${minTorsoT.toFixed(2)}s, closest legs/torsos ${minLimb.toFixed(2)} m @${minLimbT.toFixed(2)}s`);
console.log(`  end: uke pelvis y ${JuE.pelvis[1].toFixed(2)}, uke ${lying?'lying':'NOT lying'}; tori pelvis y ${JtE.pelvis[1].toFixed(2)}; uke at (${JuE.pelvis[0].toFixed(2)}, ${JuE.pelvis[2].toFixed(2)})`);
for(const [k,ts] of Object.entries(grouped)) console.log(`  ! ${k}: ${ts[0].toFixed(2)}–${ts[ts.length-1].toFixed(2)}s (${ts.length} samples)${worst[k]?` worst ${worst[k].toFixed(2)} m`:''}`);
