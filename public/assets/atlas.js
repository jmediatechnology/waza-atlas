'use strict';
/* Waza Atlas viewer. Data comes from /api; the skeleton and renderer are plain canvas, no dependencies. */
(()=>{
/* ---------- 3D maths ---------- */
const D = Math.PI/180;
const rx=a=>{const c=Math.cos(a),s=Math.sin(a);return[1,0,0,0,c,-s,0,s,c]};
const ry=a=>{const c=Math.cos(a),s=Math.sin(a);return[c,0,s,0,1,0,-s,0,c]};
const rz=a=>{const c=Math.cos(a),s=Math.sin(a);return[c,-s,0,s,c,0,0,0,1]};
const mm=(A,B)=>{const r=[];for(let i=0;i<3;i++)for(let j=0;j<3;j++)r[i*3+j]=A[i*3]*B[j]+A[i*3+1]*B[3+j]+A[i*3+2]*B[6+j];return r};
const mv=(A,v)=>[A[0]*v[0]+A[1]*v[1]+A[2]*v[2],A[3]*v[0]+A[4]*v[1]+A[5]*v[2],A[6]*v[0]+A[7]*v[1]+A[8]*v[2]];
const add=(a,b)=>[a[0]+b[0],a[1]+b[1],a[2]+b[2]];
const sub=(a,b)=>[a[0]-b[0],a[1]-b[1],a[2]-b[2]];
const sc=(a,k)=>[a[0]*k,a[1]*k,a[2]*k];
const dot=(a,b)=>a[0]*b[0]+a[1]*b[1]+a[2]*b[2];
const len=a=>Math.hypot(a[0],a[1],a[2]);
const nrm=a=>{const l=len(a)||1;return sc(a,1/l)};
const cross=(a,b)=>[a[1]*b[2]-a[2]*b[1],a[2]*b[0]-a[0]*b[2],a[0]*b[1]-a[1]*b[0]];
const lerp3=(a,b,w)=>[a[0]+(b[0]-a[0])*w,a[1]+(b[1]-a[1])*w,a[2]+(b[2]-a[2])*w];
const c01=v=>Math.max(0,Math.min(1,v));

/* ---------- Skeleton: forward kinematics + two-bone IK for grips ---------- */
const JN=['head','neck','lSh','rSh','lElb','rElb','lWr','rWr','pelvis','lHip','rHip','lKnee','rKnee','lAnk','rAnk'];
const BONES=[['head','neck'],['neck','lSh'],['neck','rSh'],['lSh','lElb'],['lElb','lWr'],['rSh','rElb'],['rElb','rWr'],['neck','pelvis'],['pelvis','lHip'],['pelvis','rHip'],['lHip','lKnee'],['lKnee','lAnk'],['rHip','rKnee'],['rKnee','rAnk']];

function fk(p){
  const Rb=mm(mm(ry(p.yaw*D),rx(p.pitch*D)),rz(p.roll*D));
  const pel=[p.x,p.y,p.z], J={pelvis:pel};
  const Rsp=mm(mm(Rb,ry(p.twist*D)),rx(p.bend*D));
  J.neck=add(pel,mv(Rsp,[0,0.5,0]));
  J.head=add(J.neck,mv(mm(Rsp,rx(p.head*D)),[0,0.22,0.03]));
  for(const [s,k] of [[1,'l'],[-1,'r']]){
    const hip=add(pel,mv(Rb,[s*0.1,-0.03,0]));
    const Rt=mm(mm(Rb,rz(s*p[k+'HipA']*D)),rx(-p[k+'HipF']*D));
    const knee=add(hip,mv(Rt,[0,-0.44,0]));
    const ank=add(knee,mv(mm(Rt,rx(p[k+'Knee']*D)),[0,-0.43,0]));
    const sh=add(J.neck,mv(Rsp,[s*0.19,-0.03,0]));
    const Ru=mm(mm(Rsp,rz(s*p[k+'ShA']*D)),rx(-p[k+'ShF']*D));
    const elb=add(sh,mv(Ru,[0,-0.30,0]));
    const wr=add(elb,mv(mm(Ru,rx(-p[k+'Elb']*D)),[0,-0.27,0]));
    Object.assign(J,{[k+'Hip']:hip,[k+'Knee']:knee,[k+'Ank']:ank,[k+'Sh']:sh,[k+'Elb']:elb,[k+'Wr']:wr});
  }
  J.fwd=mv(Rsp,[0,0,1]); J.up=mv(Rsp,[0,1,0]); J.side=mv(Rsp,[1,0,0]);
  J.pfwd=mv(Rb,[0,0,1]); J.pup=mv(Rb,[0,1,0]); J.pside=mv(Rb,[1,0,0]);
  return J;
}
function plant(J,w){
  let m=Infinity; for(const n of JN) m=Math.min(m,J[n][1]);
  const floor=0.06, off=w*(floor-m)+(1-w)*Math.max(0,floor-m);
  for(const n of JN) J[n]=[J[n][0],J[n][1]+off,J[n][2]];
}
function ik(S,T,hint){
  const a=0.30,b=0.27, d=sub(T,S), L=len(d), dir=sc(d,1/(L||1));
  const Lc=Math.min(Math.max(L,0.08),a+b-0.002);
  const x=(a*a-b*b+Lc*Lc)/(2*Lc), h=Math.sqrt(Math.max(0,a*a-x*x));
  let pole=sub(hint,S); pole=sub(pole,sc(dir,dot(pole,dir)));
  if(len(pole)<1e-4) pole=cross(dir,[1,0,0]);
  pole=nrm(pole);
  return {elb:add(S,add(sc(dir,x),sc(pole,h))), wr:add(S,sc(dir,Lc))};
}
/* Grip targets on the partner. A key "ik_<hand>_<target>" pulls that hand onto the partner's
   opposite side (tori's left hand to uke's right sleeve); add "_s" for the same side ("ik_r_thigh_s"). */
const GRIP_TARGETS=['sleeve','heel','lapel','collar','shoulder','belt','beltback','knee','thigh','wrist'];
function gripTarget(name,opp,side){
  const s=side==='l'?1:-1;
  switch(name){
    case 'sleeve': return add(opp[side+'Elb'],sc(opp.up,-0.04));
    case 'heel': return add(opp[side+'Ank'],sc(opp.fwd,-0.05));
    case 'shoulder': return add(add(opp[side+'Sh'],sc(opp.up,-0.07)),sc(opp.fwd,0.03)); // upper arm by the armpit
    case 'wrist': return opp[side+'Wr']; // follow the partner's hand, e.g. a sleeve that tori is pulling
    case 'collar': return add(add(opp.neck,sc(opp.fwd,-0.07)),sc(opp.up,0.02));
    case 'belt': return add(add(opp.pelvis,sc(opp.pfwd,0.12)),sc(opp.pup,0.06));
    case 'beltback': return add(add(opp.pelvis,sc(opp.pfwd,-0.12)),sc(opp.pup,0.06));
    case 'knee': return add(opp[side+'Knee'],sc(opp.pfwd,-0.07));
    case 'thigh': return add(lerp3(opp[side+'Hip'],opp[side+'Knee'],0.55),sc(opp.pside,-s*0.06));
    default: return add(add(add(opp.neck,sc(opp.side,s*0.08)),sc(opp.up,-0.14)),sc(opp.fwd,0.11)); // lapel
  }
}
function applyGrips(J,p,opp){
  for(const k of ['l','r']){
    const oside=k==='l'?'r':'l'; let ws=0, T=[0,0,0];
    for(const nm of GRIP_TARGETS){
      for(const [key,sd] of [['ik_'+k+'_'+nm,oside],['ik_'+k+'_'+nm+'_s',k]]){
        const w=c01(p[key]||0);
        if(w>0){T=add(T,sc(gripTarget(nm,opp,sd),w)); ws+=w;}
      }
    }
    if(ws<0.001) continue;
    T=sc(T,1/ws); const W=Math.min(1,ws), s=k==='l'?1:-1;
    const hint=add(add(J[k+'Elb'],sc(J.side,s*0.12)),[0,-0.1,0]);
    const r=ik(J[k+'Sh'],T,hint);
    J[k+'Elb']=lerp3(J[k+'Elb'],r.elb,W); J[k+'Wr']=lerp3(J[k+'Wr'],r.wr,W);
  }
}

/* ---------- Keyframes ---------- */
const BASE={x:0,y:1,z:0,yaw:0,pitch:3,roll:0,bend:5,twist:0,head:0,lHipF:5,lHipA:7,lKnee:12,rHipF:5,rHipA:7,rKnee:12,
  lShF:15,lShA:10,lElb:20,rShF:15,rShA:10,rElb:20,plant:1,ik_l_sleeve:0,ik_l_heel:0,ik_l_lapel:0,ik_r_sleeve:0,ik_r_heel:0,ik_r_lapel:0};
// Expand [time, changes] keys into full poses. A value first used part-way through (a new grip, say)
// starts at 0 on the earlier keys, so it blends in instead of interpolating from nothing.
function keyed(list){
  const all={}; for(const [,d] of list) for(const k in d) all[k]=0;
  let cur={...all,...BASE};
  return list.map(([t,d])=>{cur={...cur,...d};return {t,p:{...cur}}});
}
function sample(keys,t){
  const n=keys.length; if(t<=keys[0].t) return keys[0].p; if(t>=keys[n-1].t) return keys[n-1].p;
  let i=0; while(t>keys[i+1].t) i++;
  const k0=keys[Math.max(0,i-1)],k1=keys[i],k2=keys[i+1],k3=keys[Math.min(n-1,i+2)];
  const h=k2.t-k1.t,u=(t-k1.t)/h,u2=u*u,u3=u2*u;
  const h00=2*u3-3*u2+1,h10=u3-2*u2+u,h01=-2*u3+3*u2,h11=u3-u2, out={};
  for(const key in k1.p){
    const p1=k1.p[key],p2=k2.p[key];
    const m1=k0===k1?0:(p2-k0.p[key])/(k2.t-k0.t), m2=k3===k2?0:(k3.p[key]-p1)/(k3.t-k1.t);
    out[key]=h00*p1+h10*h*m1+h01*p2+h11*h*m2;
  }
  return out;
}

const IDLE={
  duration:5, idle:true, phases:[],
  tori:keyed([[0,{z:-0.9,lShF:4,rShF:4,lShA:8,rShA:8,lElb:8,rElb:8}],[2.5,{bend:7,head:3}],[5,{bend:5,head:0}]]),
  uke:keyed([[0,{z:0.9,yaw:180,lShF:4,rShF:4,lShA:8,rShA:8,lElb:8,rElb:8}],[2.5,{bend:7,head:3}],[5,{bend:5,head:0}]])
};
function frame(m,t){
  const pt=sample(m.tori,t), pu=sample(m.uke,t);
  const Jt=fk(pt); plant(Jt,c01(pt.plant));
  const Ju=fk(pu); plant(Ju,c01(pu.plant));
  // Tori, then uke, then tori again: uke's own grips move uke's elbows, and tori's sleeve grip must follow them.
  applyGrips(Jt,pt,Ju); applyGrips(Ju,pu,Jt); applyGrips(Jt,pt,Ju);
  return [Jt,Ju];
}

/* ---------- API ---------- */
const $=s=>document.querySelector(s);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
async function api(url){
  const r=await fetch(url,{headers:{Accept:'application/json'}});
  const body=await r.json().catch(()=>null);
  if(!r.ok) throw new Error(body?.error||`The server answered ${r.status}.`);
  return body;
}

/* ---------- Catalogue ---------- */
const ui={fam:'all',cls:null,motionOnly:false,q:'',sel:null};
let TAX=[], CAT={}, results=[], listSeq=0;
const GOKYO_NAMES=['','dai-ikkyo','dai-nikyo','dai-sankyo','dai-yonkyo','dai-gokyo'];

function renderFilters(){
  const total=TAX.reduce((a,f)=>a+f.techniques,0);
  const fams=[['all','All',total],...TAX.map(f=>[f.slug,f.name,f.techniques])];
  $('#fam').innerHTML=fams.map(([id,l,n])=>`<button type="button" data-f="${esc(id)}" aria-pressed="${ui.fam===id}">${esc(l)}<span class="n">${n}</span></button>`).join('');
  const cls=TAX.filter(f=>ui.fam==='all'||f.slug===ui.fam).flatMap(f=>f.categories);
  const nMo=TAX.flatMap(f=>f.categories).reduce((a,c)=>a+c.withMotion,0);
  $('#chips').innerHTML=cls.map(c=>`<button type="button" class="chip" data-c="${esc(c.slug)}" aria-pressed="${ui.cls===c.slug}">${esc(c.name)}<span class="n">${c.techniques}</span></button>`).join('')+
    `<button type="button" class="chip motion" data-m="1" aria-pressed="${ui.motionOnly}">With motion<span class="n">${nMo}</span></button>`;
}

async function refreshList(){
  const seq=++listSeq, p=new URLSearchParams();
  if(ui.q.trim()) p.set('q',ui.q.trim());
  if(ui.fam!=='all') p.set('family',ui.fam);
  if(ui.cls) p.set('category',ui.cls);
  if(ui.motionOnly) p.set('motion','1');
  $('#list').setAttribute('aria-busy','true');
  try{
    const d=await api('/api/techniques?'+p);
    if(seq!==listSeq) return;
    results=d.techniques; renderList();
  }catch(e){
    if(seq===listSeq) $('#list').innerHTML=`<div class="empty-list err">${esc(e.message)}</div>`;
  }finally{
    if(seq===listSeq) $('#list').removeAttribute('aria-busy');
  }
}

function renderList(){
  if(!results.length){
    $('#list').innerHTML=`<div class="empty-list">No technique matches “${esc(ui.q)}”. Try a romaji name, an English name or a category such as “te waza”.</div>`;
    return;
  }
  let html='';
  for(const f of TAX) for(const c of f.categories){
    const g=results.filter(t=>t.category===c.slug); if(!g.length) continue;
    html+=`<div class="group"><h3>${esc(c.name)}<span class="k">${esc(c.kanji)}</span><span class="n">${g.length}</span></h3>`;
    for(const t of g){
      const tags=[t.hasMotion?'<span class="tag mo">Motion</span>':'',t.gokyo?`<span class="tag">Gokyo ${t.gokyo}</span>`:'',t.prohibited?'<span class="tag px">Prohibited</span>':''].join('');
      html+=`<button type="button" class="row" data-id="${esc(t.slug)}" aria-current="${ui.sel===t.slug}"><span class="nm">${esc(t.name)}</span><span class="kj">${esc(t.kanji)}</span><span class="en">${esc(t.english)}</span>${tags?`<span class="tags">${tags}</span>`:''}</button>`;
    }
    html+='</div>';
  }
  $('#list').innerHTML=html;
}

let qTimer=0;
$('#q').addEventListener('input',e=>{ui.q=e.target.value; clearTimeout(qTimer); qTimer=setTimeout(refreshList,140);});
$('#fam').addEventListener('click',e=>{
  const b=e.target.closest('button'); if(!b) return;
  ui.fam=b.dataset.f;
  if(ui.cls&&ui.fam!=='all'&&CAT[ui.cls].family!==ui.fam) ui.cls=null;
  renderFilters(); refreshList();
});
$('#chips').addEventListener('click',e=>{
  const b=e.target.closest('button'); if(!b) return;
  if(b.dataset.m) ui.motionOnly=!ui.motionOnly; else ui.cls=ui.cls===b.dataset.c?null:b.dataset.c;
  renderFilters(); refreshList();
});
$('#list').addEventListener('click',e=>{const b=e.target.closest('.row'); if(b) select(b.dataset.id,'push');});
$('#back').addEventListener('click',()=>{
  document.body.classList.remove('detail-open');
  try{history.pushState(null,'','/')}catch(e){}
  window.scrollTo(0,0);
});
window.addEventListener('popstate',()=>{
  const m=location.pathname.match(/^\/waza\/([a-z0-9-]+)$/);
  if(m) select(m[1],'none'); else document.body.classList.remove('detail-open');
});

/* ---------- Detail ---------- */
const player={m:IDLE,t:0,playing:true,speed:1,bones:false,trails:true};
const reduce=matchMedia('(prefers-reduced-motion: reduce)').matches;
const narrow=()=>matchMedia('(max-width: 860px)').matches;
let selSeq=0;

function toMotion(data,phases,source){
  return {
    duration:+data.duration,
    phases:(phases&&phases.length?phases:data.phases)||[],
    source:source||data.source||'',
    tori:keyed(data.tracks.tori),
    uke:keyed(data.tracks.uke)
  };
}

async function select(slug,nav){
  const seq=++selSeq; ui.sel=slug;
  document.querySelectorAll('.row').forEach(r=>r.setAttribute('aria-current',String(r.dataset.id===slug)));
  if(nav==='push'){try{history.pushState(null,'','/waza/'+slug)}catch(e){}}
  if(nav!=='init'&&narrow()){document.body.classList.add('detail-open'); window.scrollTo(0,0);}

  let t, m=IDLE, nomo=null;
  try{ t=await api('/api/techniques/'+encodeURIComponent(slug)); }
  catch(e){
    if(seq!==selSeq) return;
    $('#dName').textContent='Technique not found'; $('#dKanji').textContent=''; $('#dEn').textContent=e.message;
    $('#crumb').textContent=''; $('#dBadges').innerHTML=''; $('#steps').innerHTML=''; $('#facts').innerHTML=''; $('#srcNote').textContent='';
    return;
  }
  if(t.motion){
    if(t.motion.format==='keyframes'){
      try{ m=toMotion(await api(t.motion.dataUrl),t.motion.phases,t.motion.source); }
      catch(e){ nomo=['The motion could not be loaded',e.message]; }
    }else{
      nomo=[`${t.motion.format.toUpperCase()} motion on file`,`This viewer plays keyframe motions so far. The ${t.motion.format.toUpperCase()} file can be downloaded below.`];
    }
  }else nomo=['No motion recorded yet','Showing both players in shizentai as a placeholder.'];
  if(seq!==selSeq) return;

  const fam=t.category.family, c=t.category, playable=m!==IDLE;
  document.title=`${t.name} · Waza Atlas`;
  $('#crumb').innerHTML=`${esc(fam.name)} <span class="k">${esc(fam.kanji)}</span> &nbsp;›&nbsp; ${esc(c.name)} <span class="k">${esc(c.kanji)}</span>`;
  $('#dName').textContent=t.name; $('#dKanji').textContent=t.kanji; $('#dEn').textContent=t.english;
  $('#dBadges').innerHTML=[t.motion?'<span class="tag mo">Motion available</span>':'<span class="tag">No motion yet</span>',
    t.gokyo?`<span class="tag">Gokyo · group ${t.gokyo}</span>`:'',t.prohibited?'<span class="tag px">Prohibited in shiai</span>':''].join('');

  player.m=m; player.t=reduce&&playable?Math.min(m.duration,(m.phases[3]?.t??0)+0.3):0; player.playing=!reduce;
  $('#scrub').max=m.duration;
  $('#ovNomo').hidden=!nomo;
  if(nomo){$('#nomoTitle').textContent=nomo[0]; $('#nomoText').textContent=nomo[1];}
  $('#timeline').hidden=!playable; $('#ovPhase').hidden=!playable||!m.phases.length; $('#ovTime').hidden=!playable;

  $('#phases').innerHTML=m.phases.map((p,i)=>{const end=i<m.phases.length-1?m.phases[i+1].t:m.duration;return `<button type="button" data-i="${i}" style="flex:${Math.max(0.05,end-p.t).toFixed(2)} 1 0">${esc(p.name)}</button>`}).join('');
  const phaseList=playable?m.phases:(t.motion?.phases||[]);
  $('#steps').innerHTML=phaseList.length
    ?phaseList.map((p,i)=>`<li><button type="button" class="step" data-i="${i}"><span class="t">${(+p.t).toFixed(2)} s</span><span class="h">${esc(p.name)}${p.en?`<i>${esc(p.en)}</i>`:''}</span><span class="d">${esc(p.text)}</span></button></li>`).join('')
    :`<li class="waiting">The step-by-step breakdown appears here once a motion for ${esc(t.name)} is recorded. Each step is tied to a phase on the timeline: kumikata, kuzushi, tsukuri, kake and ukemi.</li>`;

  const facts=[['Family',`${esc(fam.name)} · ${esc(fam.english)}`],['Category',`${esc(c.name)} · ${esc(c.english)}`],
    ['Gokyo',t.gokyo?`Group ${t.gokyo} (${GOKYO_NAMES[t.gokyo]})`:'Not in the Gokyo']];
  if(t.prohibited) facts.push(['Shiai','Prohibited in Kodokan competition']);
  if(t.notes) facts.push(['Note',esc(t.notes)]);
  if(t.motion){
    facts.push(['Motion',`${esc(t.motion.source)}${t.motion.duration?` · ${(+t.motion.duration).toFixed(1)} s`:''}`]);
    facts.push(['File',`<a class="dl" href="${esc(t.motion.dataUrl)}">${esc(t.motion.format.toUpperCase())}</a>`]);
  }else facts.push(['Motion','Not recorded']);
  $('#facts').innerHTML=facts.map(([k,v])=>`<dt>${k}</dt><dd>${v}</dd>`).join('');
  $('#srcNote').textContent=!t.motion
    ?'Placeholder stance only. Upload a keyframe, BVH or C3D file through the API to add a motion.'
    :t.motion.format==='keyframes'?'Keyframe motions are played directly in the browser. Captured BVH or C3D files can be uploaded through the API.':'';
  syncControls(); lastPhase=-2;
}

function syncControls(){
  $('#play').setAttribute('aria-label',player.playing?'Pause':'Play');
  $('#play').innerHTML=player.playing?'<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><rect x="3" y="2" width="3.5" height="12" rx="1" fill="currentColor"/><rect x="9.5" y="2" width="3.5" height="12" rx="1" fill="currentColor"/></svg>'
    :'<svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 2.5v11l9.5-5.5z" fill="currentColor"/></svg>';
  document.querySelectorAll('#speed button').forEach(b=>b.setAttribute('aria-pressed',String(+b.dataset.v===player.speed)));
}
$('#play').addEventListener('click',()=>{player.playing=!player.playing; if(player.playing&&player.t>=player.m.duration-0.01) player.t=0; syncControls();});
$('#speed').addEventListener('click',e=>{const b=e.target.closest('button'); if(!b) return; player.speed=+b.dataset.v; syncControls();});
$('#bones').addEventListener('change',e=>player.bones=e.target.checked);
$('#trails').addEventListener('change',e=>player.trails=e.target.checked);
$('#scrub').addEventListener('input',e=>{player.t=+e.target.value; if(player.playing){player.playing=false; syncControls();}});
function jump(i){ if(player.m.phases[i]) player.t=player.m.phases[i].t+0.001; }
$('#phases').addEventListener('click',e=>{const b=e.target.closest('button'); if(b) jump(+b.dataset.i);});
$('#steps').addEventListener('click',e=>{const b=e.target.closest('.step'); if(b) jump(+b.dataset.i);});

/* ---------- Camera & rendering ---------- */
const VIEWS={side:{th:72,ph:12},front:{th:8,ph:10},top:{th:90,ph:80}};
const cam={th:72*D,ph:12*D,dist:4.3,target:[0,0.7,0.3],goal:null};
function setView(v){cam.goal={th:VIEWS[v].th*D,ph:VIEWS[v].ph*D};document.querySelectorAll('#views button').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.v===v)));}
$('#views').addEventListener('click',e=>{const b=e.target.closest('button');if(b)setView(b.dataset.v);});
document.querySelectorAll('#views button').forEach(b=>b.setAttribute('aria-pressed',String(b.dataset.v==='side')));

const cv=$('#cv'), ctx=cv.getContext('2d');
let W=0,H=0,dpr=1;
function resize(){dpr=Math.min(2,window.devicePixelRatio||1);W=cv.clientWidth;H=cv.clientHeight;cv.width=Math.round(W*dpr);cv.height=Math.round(H*dpr);}
new ResizeObserver(resize).observe(cv);

let drag=null, hinted=false;
cv.addEventListener('pointerdown',e=>{drag={x:e.clientX,y:e.clientY};cv.setPointerCapture(e.pointerId);cam.goal=null;if(!hinted){hinted=true;$('#ovHint').style.opacity=0;}
  document.querySelectorAll('#views button').forEach(b=>b.setAttribute('aria-pressed','false'));});
cv.addEventListener('pointermove',e=>{if(!drag)return;cam.th-=(e.clientX-drag.x)*0.008;cam.ph=Math.max(2*D,Math.min(85*D,cam.ph+(e.clientY-drag.y)*0.006));drag={x:e.clientX,y:e.clientY};});
cv.addEventListener('pointerup',()=>drag=null); cv.addEventListener('pointercancel',()=>drag=null);
cv.addEventListener('wheel',e=>{e.preventDefault();cam.dist=Math.max(2.2,Math.min(8,cam.dist*(1+e.deltaY*0.001)));},{passive:false});

let C={};
function readColors(){const s=getComputedStyle(document.documentElement);for(const k of ['stage','stage-ink','stage-muted','mat','tori','uke'])C[k]=s.getPropertyValue('--'+k).trim();}
readColors();
matchMedia('(prefers-color-scheme: dark)').addEventListener('change',readColors);
new MutationObserver(readColors).observe(document.documentElement,{attributes:true,attributeFilter:['data-theme']});

let P={};
function setupCam(){
  const cp=Math.cos(cam.ph);
  P.pos=add(cam.target,sc([cp*Math.sin(cam.th),Math.sin(cam.ph),cp*Math.cos(cam.th)],cam.dist));
  P.f=nrm(sub(cam.target,P.pos)); P.r=nrm(cross(P.f,[0,1,0])); P.u=cross(P.r,P.f);
  P.focal=Math.min(W,H*1.6)*1.05; P.cx=W/2; P.cy=H*0.56;
}
function proj(p){const v=sub(p,P.pos),z=dot(v,P.f);if(z<0.15)return null;const k=P.focal/z;return [P.cx+dot(v,P.r)*k,P.cy-dot(v,P.u)*k,z,k];}
function rgba(hex,a){const h=hex.replace('#','');const n=parseInt(h.length===3?h.split('').map(c=>c+c).join(''):h,16);return `rgba(${n>>16&255},${n>>8&255},${n&255},${a})`;}

function drawMats(){
  ctx.lineWidth=1;
  for(let r=0;r<4;r++)for(let c=0;c<2;c++){
    const x0=-1.82+c*1.82,z0=-1.5+r*0.91, pts=[[x0,0,z0],[x0+1.82,0,z0],[x0+1.82,0,z0+0.91],[x0,0,z0+0.91]].map(proj);
    if(pts.some(p=>!p)) continue;
    ctx.beginPath(); pts.forEach((p,i)=>i?ctx.lineTo(p[0],p[1]):ctx.moveTo(p[0],p[1])); ctx.closePath();
    ctx.fillStyle=rgba(C.mat,(r+c)%2?0.9:0.6); ctx.fill();
    ctx.strokeStyle=rgba(C['stage-muted'],0.25); ctx.stroke();
  }
}
function drawFigures(F,alpha){
  const cols=[C.tori,C.uke];
  // floor shadows
  F.forEach((J,fi)=>{for(const n of JN){const p=proj([J[n][0],0.001,J[n][2]]);if(!p)continue;ctx.fillStyle=`rgba(0,0,0,${0.35*alpha})`;ctx.beginPath();ctx.ellipse(p[0],p[1],0.03*p[3],0.012*p[3],0,0,7);ctx.fill();}});
  if(player.bones){
    ctx.lineWidth=1.5; ctx.lineCap='round';
    F.forEach((J,fi)=>{ctx.strokeStyle=rgba(cols[fi],0.35*alpha);for(const [a,b] of BONES){const p=proj(J[a]),q=proj(J[b]);if(!p||!q)continue;ctx.beginPath();ctx.moveTo(p[0],p[1]);ctx.lineTo(q[0],q[1]);ctx.stroke();}});
  }
  const dots=[];
  F.forEach((J,fi)=>{for(const n of JN){const p=proj(J[n]);if(p)dots.push([p,fi,n==='head'?1.5:1]);}});
  dots.sort((a,b)=>b[0][2]-a[0][2]);
  for(const [p,fi,s] of dots){
    const r=Math.max(2.4,0.03*s*p[3]);
    ctx.shadowColor=rgba(cols[fi],0.9*alpha); ctx.shadowBlur=r*2.2;
    ctx.fillStyle=rgba(cols[fi],alpha); ctx.beginPath(); ctx.arc(p[0],p[1],r,0,7); ctx.fill();
  }
  ctx.shadowBlur=0;
}
function drawTrails(m,t){
  const N=12,dt=0.035,hist=[];
  for(let k=N;k>=0;k--) hist.push(frame(m,Math.max(0,t-k*dt)));
  const cols=[C.tori,C.uke]; ctx.lineWidth=1.6; ctx.lineCap='round';
  for(let fi=0;fi<2;fi++) for(const n of JN){
    for(let k=1;k<hist.length;k++){
      const p=proj(hist[k-1][fi][n]),q=proj(hist[k][fi][n]); if(!p||!q) continue;
      ctx.strokeStyle=rgba(cols[fi],0.28*k/hist.length); ctx.beginPath(); ctx.moveTo(p[0],p[1]); ctx.lineTo(q[0],q[1]); ctx.stroke();
    }
  }
}

let last=performance.now(), lastPhase=-2;
function tick(now){
  const dt=Math.min(0.05,(now-last)/1000); last=now;
  const m=player.m;
  if(player.playing){player.t+=dt*player.speed; if(player.t>m.duration) player.t=0;}
  if(cam.goal){cam.th+=(cam.goal.th-cam.th)*0.12; cam.ph+=(cam.goal.ph-cam.ph)*0.12; if(Math.abs(cam.goal.th-cam.th)<1e-3&&Math.abs(cam.goal.ph-cam.ph)<1e-3)cam.goal=null;}
  if(W>0&&H>0){
    ctx.setTransform(dpr,0,0,dpr,0,0);
    ctx.fillStyle=C.stage; ctx.fillRect(0,0,W,H);
    setupCam(); drawMats();
    const t=player.t;
    let alpha=1;
    if(!m.idle&&player.playing){ if(t<0.2) alpha=t/0.2; else if(t>m.duration-0.25) alpha=Math.max(0,(m.duration-t)/0.25); }
    if(player.trails&&!m.idle) drawTrails(m,t);
    drawFigures(frame(m,t),alpha);
    if(!m.idle){
      $('#scrub').value=t.toFixed(2);
      $('#ovTime').textContent=t.toFixed(2)+' s';
      let pi=0; m.phases.forEach((p,i)=>{if(t>=p.t)pi=i;});
      if(m.phases.length&&pi!==lastPhase){
        lastPhase=pi; const ph=m.phases[pi];
        $('#ovPhase b').textContent=ph.name; $('#ovPhase span').textContent=ph.en;
        document.querySelectorAll('#phases button').forEach((b,i)=>b.setAttribute('aria-current',String(i===pi)));
        document.querySelectorAll('#steps .step').forEach((b,i)=>b.setAttribute('aria-current',String(i===pi)));
      }
      if(!player.playing&&t>=m.duration) syncControls();
    }
  }
  requestAnimationFrame(tick);
}

/* ---------- Start ---------- */
(async()=>{
  try{ TAX=(await api('/api/categories')).families; }
  catch(e){ $('#list').innerHTML=`<div class="empty-list err">${esc(e.message)}</div>`; requestAnimationFrame(tick); return; }
  for(const f of TAX) for(const c of f.categories) CAT[c.slug]={...c,family:f.slug};
  renderFilters();
  const withMotion=(await api('/api/techniques?motion=1').catch(()=>({techniques:[]}))).techniques;
  if(withMotion[0]){
    $('#calloutName').textContent=withMotion[0].name; $('#callout').hidden=false;
    $('#callout').addEventListener('click',()=>select(withMotion[0].slug,'push'));
  }
  await refreshList();
  const initial=document.body.dataset.initialSlug;
  const start=initial||withMotion[0]?.slug||results[0]?.slug;
  if(start){ await select(start,'init'); if(initial&&narrow()) document.body.classList.add('detail-open'); }
  requestAnimationFrame(tick);
})();
})();
