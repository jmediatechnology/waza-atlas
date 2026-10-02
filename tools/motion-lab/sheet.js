// node sheet.js <motion.json> <out.png> [frames]
// Renders side, front, three-quarter and top views at evenly spaced times. Tori is black, uke blue; left limbs are lighter.
const {chromium}=require('playwright');
const fs=require('fs'); require('./build-core.js');
(async()=>{
  const m=JSON.parse(fs.readFileSync(process.argv[2],'utf8'));
  const n=+(process.argv[4]||10); const times=[...Array(n)].map((_,i)=>+(m.duration*i/(n-1)).toFixed(2));
  const b=await chromium.launch(process.env.CHROMIUM_PATH?{executablePath:process.env.CHROMIUM_PATH}:{}); const p=await b.newPage({viewport:{width:1900,height:800}});
  const errs=[]; p.on('pageerror',e=>errs.push(e.message));
  await p.goto('file://'+__dirname+'/sheet.html'); await p.evaluate(([m,t])=>window.render(m,t),[m,times]);
  await p.locator('#c').screenshot({path:process.argv[3]}); if(errs.length) console.log(errs); await b.close();
})();
