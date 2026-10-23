/* Module 8: step screens. Needs cosecsa-course.js loaded first.
   Each .wt block shows a replica of one portal screen (sample data) with the relevant part highlighted. */
(function(){
var root=document.querySelector('.cs-course.mod-8');
if(!root)return;
var EDIT=!!root.querySelector('[data-tid]'); /* the page editor's preview: same player, free to step through, nothing recorded, blocks stay clickable */
var topicEls=[].slice.call(root.querySelectorAll('.topic'));
if(!topicEls.length||!window.CSCourse)return;
const LOGO=((window.CSCourse&&CSCourse.img)||'/images/course/')+'cosecsa-crest.png';
const I={
 dash:'<svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>',
 chart:'<svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-5 3 3 5-7"/></svg>',
 cap:'<svg viewBox="0 0 24 24"><path d="M2 9l10-5 10 5-10 5z"/><path d="M6 11v5c3 2 9 2 12 0v-5"/></svg>',
 user:'<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="10" r="3"/><path d="M6 19c2-4 10-4 12 0"/></svg>',
 chat:'<svg viewBox="0 0 24 24"><path d="M4 5h16v11H9l-5 4z"/></svg>',
 out:'<svg viewBox="0 0 24 24"><path d="M10 4H4v16h6"/><path d="M14 8l5 4-5 4M19 12H9"/></svg>',
 bars:'<svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>',
 bell:'<svg viewBox="0 0 24 24"><path d="M6 17V11a6 6 0 0112 0v6l2 2H4z"/></svg>',
 moon:'<svg viewBox="0 0 24 24"><path d="M20 14A8 8 0 1110 4a7 7 0 0010 10z"/></svg>',
 expand:'<svg viewBox="0 0 24 24"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>',
 steth:'<svg viewBox="0 0 24 24" stroke="#fff"><path d="M6 3v6a4 4 0 008 0V3"/><path d="M10 13v3a4 4 0 008 0v-2"/><circle cx="18" cy="12" r="2"/></svg>',
 talk:'<svg viewBox="0 0 24 24" stroke="#a02626"><path d="M3 5h12v9H8l-3 3v-3H3z"/><path d="M17 9h4v9h-2v3l-3-3h-5"/></svg>'
};
const shell=(active,inner,url)=>{const n=[['dash','Dashboard'],['chart','Results'],['cap','Examiner Training'],['user','Profile'],['chat','Messages']];
 return `<div class="m-app"><div class="m-side"><div class="m-brand"><img class="m-logo" src="${LOGO}" alt="">COSECSA-MIS</div><div class="m-user"><span class="m-av"></span>Dr. Jane Sample</div><div class="m-nl">Navigation</div>${n.map(([i,t])=>`<div class="m-nav ${t===active?'on':''}">${I[i]}${t}</div>`).join('')}<div class="m-nl">Account</div><div class="m-nav">${I.out}Logout</div></div><div class="m-top">${I.bars.replace('<svg','<svg style="margin:0"')}<div style="display:flex">${I.bell}${I.moon}${I.expand}</div></div><div class="m-page" id="page">${inner}</div></div>`};
const sel=(t,c='')=>`<div class="m-sel ${c}">${t}</div>`;
const grp=(l,t,ph,id)=>`<div class="m-col" ${id?`data-t="${id}"`:''}><label class="m-lab">${l}</label>${sel(t,ph?'ph':'')}</div>`;
const ro=(l,t)=>`<div class="m-col"><label class="m-lab">${l}</label><div class="m-sel m-ro" style="padding-right:12px">${t}<style>.m-ro::after{display:none}</style></div></div>`;
const q=(l,v,id)=>`<div class="m-fg" ${id?`data-t="${id}"`:''}><label class="m-lab">${l}</label>${sel(v||'Select Mark',v?'':'ph')}</div>`;
const ni=(l,v,id)=>`<div class="m-fg" ${id?`data-t="${id}"`:''}><label class="m-lab">${l}</label><div class="m-inp" style="width:100%">${v}</div></div>`;
const ta=(v)=>`<div class="m-ta ${v?'':'ph'}">${v||'Enter remarks'}</div>`;
const SCREENS={
 role:()=>`<div class="m-auth"><div class="m-card"><div class="m-ch"><img class="m-logo" src="${LOGO}" alt=""><h1>COSECSA-MIS</h1></div><div class="m-cb"><p class="m-msg" style="margin-bottom:22px">Please select your role to continue</p><label style="display:block;margin-bottom:6px">Select Role</label><div data-t="role">${sel('Examiner')}</div><div class="m-b2"><div style="background:#6c757d" data-t="reset">Reset Role</div><div style="background:#a02626" data-t="cont">Continue →</div></div></div></div></div>`,
 login:()=>`<div class="m-auth"><div class="m-card"><div class="m-ch"><img class="m-logo" src="${LOGO}" alt=""><h1>COSECSA-MIS</h1></div><div class="m-cb"><p class="m-msg">Login as <b>Examiner</b></p><p class="m-a">⇄ Change role</p><div class="m-in" data-t="user">Email or Username</div><div class="m-in" data-t="pass" style="color:#495057">••••••••<span style="position:absolute;right:14px;color:#6c757d">◉</span></div><div style="margin-bottom:6px;display:flex;gap:8px;align-items:center"><span style="width:16px;height:16px;border:1px solid #adb5bd;border-radius:2px"></span>Remember Me</div><div class="m-b2"><div style="background:#6c757d" data-t="forget">🔓 Forget Password</div><div style="background:#a02626" data-t="login">Login</div></div></div></div></div>`,
 dash:(mcs)=>shell('Dashboard',`<div class="m-pad"><div class="m-panel"><div class="m-fs"><legend>Examiner Portal</legend><h3 style="font-size:28px;font-weight:500;margin:8px 0 8px">Dear Dr. Jane Sample,</h3><p style="margin:0 0 18px">Welcome to the COSECSA marking portal. Please choose the EXAM TYPE you want to evaluate:</p><div class="eo ok" data-t="eo">Ready for offline marking. Last updated 23 Oct, 08:15. <a>Update now</a></div><div style="text-align:left;padding:0 10px"><b>Select Exam Type</b><div data-t="exam" style="margin-top:5px"><div class="m-sel" style="height:48px;line-height:34px;border-color:#fec503">${mcs?'MCS':'FCS Urology'}</div></div></div><div data-t="proceed" style="display:inline-block;margin-top:22px;background:#fec503;border-radius:4px;padding:7px 16px;color:#000">Proceed →</div></div></div></div>`),
 fmt:()=>shell('Dashboard',`<div class="m-pad"><div class="m-fs" style="max-width:660px;margin-top:44px"><legend style="text-align:center;font-size:22px">FCS Urology</legend><h3 style="font-size:28px;font-weight:500;margin:6px 0 4px">Select Examination Format</h3><p style="color:#6c757d;margin:0">Please choose between Clinical or Viva examination for Urology</p><div class="fmt"><div data-t="clin" style="background:#a02626;color:#fff">${I.steth.replace('<svg','<svg stroke="#fff"')}<b>Clinical</b><span>Evaluate clinical cases</span></div><div data-t="viva" style="background:#fec503;color:#a02626">${I.talk}<b>Viva</b><span>Conduct oral examination</span></div></div><div data-t="back" class="m-btn">← Back to Dashboard</div></div></div>`),
 mcsForm:(res)=>shell(res?'Results':'Dashboard',`<div class="m-pad"><div class="m-panel"><div class="m-h2">Candidate Evaluation Form</div>
 <div class="m-row" data-t="sel">${res?ro('Group','Group A')+ro('Candidate','CAND-031')+ro('Station','Station 3'):grp('Select Group','Group A')+grp('Select Candidate','CAND-031')+grp('Select Station','Station 3')}</div>
 <div class="m-row" style="justify-content:center"><div style="width:420px" data-t="marks"><label class="m-lab" style="margin-bottom:0">Enter Marks for Questions${res?'':','}</label>${(res?[4,3.5,4,4,3]:[9]).map((v,i)=>`<label class="m-lab" style="margin:8px 0 6px">Question ${i+1}:</label><div class="m-inp">${v}</div>`).join('')}</div></div>
 <div class="m-add" style="margin-top:16px" data-t="add">+ Add Question</div>
 <div class="m-row"><div class="m-col" data-t="total"><label class="m-lab">Overall Marks</label><div class="m-inp m-ro">${res?'18.5':'9.0'}</div></div><div class="m-col" data-t="grade"><label class="m-lab">Grade</label>${sel('Pass')}</div></div>
 <div class="m-row"><div class="m-col" data-t="remarks"><label class="m-lab">Examiner Remarks</label>${ta('Answered all questions clearly.')}</div></div>
 <div style="margin-top:6px" data-t="submit"><span class="m-sub">Submit</span></div></div></div>`),
 fcsForm:(res)=>shell(res?'Results':'Dashboard',`<div class="m-pad"><div class="m-panel"><div class="m-h2">${res?'Resubmit Urology - Clinical Evaluation':'FCS Urology - Clinical Evaluation Form'}</div>
 <div class="m-row" data-t="sel">${res?ro('Group','Group A')+ro('Candidate','CAND-014')+ro('Station','Station 2'):grp('Select Group','Group A')+grp('Select Candidate','CAND-014')+grp('Select Station','Station 2')}</div>
 <div class="m-case"><h5>Case 2</h5><div data-t="marks">${q('Overall Professional Capacity and Patient Care:','6')}${q('Knowledge and Judgement:','8')}${q('Quality of Response:','6')}</div><span class="m-add" data-t="add">+ Add Question</span></div>
 <div class="m-row" style="justify-content:center"><div style="width:420px" data-t="total"><label class="m-lab">Overall Marks</label><div class="m-inp m-ro">20</div></div></div>
 <div class="m-row"><div class="m-col" data-t="remarks"><label class="m-lab">Examiner Remarks</label>${ta('Confident history taking; reviewed imaging well.')}</div></div>
 <div style="margin-top:6px" data-t="submit">${res?'<span class="m-btn" style="margin-right:8px;height:42px;line-height:30px">← Cancel</span>':''}<span class="m-sub">${res?'Update Evaluation':'Submit'}</span></div></div></div>`),
 results:(mcs)=>shell('Results',`<div class="m-card2" style="margin-top:20px"><div class="hd" style="background:${mcs?'#17a2b8;color:#fff':'#fec503;color:#a02626'}">${mcs?'MCS Results':'FCS Specialty Results'}</div><div class="bd"><div class="m-dt"><span>Show <span class="m-sel" style="display:inline-block;width:62px;height:30px;padding:2px 10px">10</span> entries</span><span>Search: <span class="m-inp" style="display:inline-block;width:150px;height:30px;vertical-align:middle"></span></span></div>
 <table class="m-tb" data-t="tbl"><thead><tr>${mcs?'<th>Candidate ID</th><th>Group</th><th>Question 1</th><th>Question 2</th><th>Question 3</th><th>Total Marks</th><th>Overall Grade</th><th>Actions</th>':'<th>Candidate ID</th><th>Group</th><th>Clinical Total</th><th>Viva Total</th><th>Overall Total</th><th>Actions</th>'}</tr></thead><tbody>
 ${(mcs?[['CAND-031','Group A','4','3.5','4','18.5','pass'],['CAND-032','Group A','3','3','4','15.5','borderline'],['CAND-040','Group B','4','4','4','20','pass']].map(r=>`<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td><td>${r[4]}</td><td><b>${r[5]}</b></td><td>${r[6]}</td><td data-t="act"><span class="m-v">View</span><span class="m-r">Resubmit</span></td></tr>`):[['CAND-014','Group A','24','22','46'],['CAND-015','Group A','28','26','54'],['CAND-021','Group B','20','24','44']].map(r=>`<tr><td>${r[0]}</td><td>${r[1]}</td><td><b>${r[2]}</b></td><td><b>${r[3]}</b></td><td><b>${r[4]}</b></td><td data-t="act"><span class="m-v">View</span><span class="m-r">Resubmit Clinical</span><span class="m-r">Resubmit Viva</span></td></tr>`)).join('')}</tbody></table>
 <div class="m-dt" style="margin:14px 0 0"><span>Showing 1 to 3 of 3 entries</span><span class="m-pg"><span>Previous</span><span class="a">1</span><span>Next</span></span></div></div></div>`),
 offReady:()=>shell('Dashboard',`<div class="m-pad"><div class="m-panel"><div style="padding:0 30px;text-align:left"><p style="font-weight:700;color:#6b7280;font-size:14px;margin:0 0 6px">Not prepared yet</p><div class="eo" data-t="e1">Not prepared for offline marking yet. <a>Prepare now</a></div><p style="font-weight:700;color:#6b7280;font-size:14px;margin:18px 0 6px">Preparing</p><div class="eo" data-t="e2">Preparing this device for offline marking…</div><p style="font-weight:700;color:#6b7280;font-size:14px;margin:18px 0 6px">Ready</p><div class="eo ok" data-t="e3">Ready for offline marking. Last updated 23 Oct, 08:15. <a>Update now</a></div></div></div></div>`),
 offSaved:()=>`<div style="position:absolute;inset:0">${SCREENS.fcsForm(false).replace(/Group A/,'Select Group...').replace(/CAND-014/,'Choose a Candidate...').replace(/Station 2/,'Choose a Station...')}<div class="toast" style="background:#2e7d32" data-t="toast">Saved on this device. It will be submitted automatically when you are back online.</div><div class="pill" style="background:#8a5a00" data-t="pill">Offline · 1 mark waiting</div></div>`,
 offSync:()=>`<div style="position:absolute;inset:0">${shell('Results',`<div class="eo" style="margin:20px 15px 0" data-t="queue"><b>Saved on this device, not yet submitted (1)</b><br>They will be submitted automatically when you are back online.<table class="m-tb" style="margin-top:8px;font-size:13px"><thead><tr><th>Candidate</th><th>Station</th><th>Group</th><th>Exam</th><th>Saved</th><th>Status</th><th>Actions</th></tr></thead><tbody><tr style="background:none"><td>CAND-014</td><td>Station 2</td><td>Group A</td><td>FCS Urology</td><td>23 Oct, 09:40</td><td>Waiting to sync</td><td><span class="gb" style="padding:3px 8px;background:#e0e0e0">View</span> <span class="gb" style="padding:3px 8px">Edit</span> <span class="gb" style="padding:3px 8px;background:#c62828;color:#fff">Discard</span></td></tr></tbody></table></div>`)}<div class="panel" data-t="panel"><b style="color:#a02626">Marks saved on this device</b><div style="margin-top:6px"><b>CAND-014 · Station 2 · Group A</b><br><small style="color:#666">FCS Urology — 23 Oct, 09:40</small></div><span class="gb" data-t="sync">Sync now</span></div><div class="pill" style="background:#8a5a00">1 mark waiting to sync</div><div class="toast" style="background:#2e7d32" data-t="synced">1 mark synced.</div></div>`,
 cover:()=>shell('Examiner Training',`<div class="m-hero" data-t="hero"><p>Prepare to examine with fairness, consistency and confidence across COSECSA’s Membership and Fellowship examinations.</p><div class="pills"><span>6 modules</span><span>About 45 minutes</span><span>10-question quiz · 80% to pass</span></div><span class="go" data-t="start">Start course →</span></div>
 <div class="lcard" style="display:block" data-t="prog"><b>Your progress</b><div style="font-size:13px;color:#64748b">33% complete</div><div style="height:8px;background:#e9edf2;border-radius:99px;margin-top:10px"><div style="width:33%;height:8px;background:#99060a;border-radius:99px"></div></div></div>
 <div data-t="mods">${[['✓','Welcome to COSECSA examining','5 min',1],['✓','Fair and consistent marking','8 min',1],['03','Using the marking portal','10 min',0],['04','Knowledge check · Quiz','Quiz · 10 min',0]].map(m=>`<div class="lcard"><div class="mn ${m[3]?'d':''}">${m[0]}</div><div><b style="font-size:15px">${m[1]}</b><div style="font-size:12.5px;color:#64748b">${m[2]}</div></div><span class="bdg ${m[3]?'g':''}">${m[3]?'Completed':'Not started'}</span></div>`).join('')}</div>`),
 quiz:()=>shell('Examiner Training',`<div style="padding:14px 24px"><div style="color:#99060a;font-weight:600;font-size:14px;margin-bottom:10px">← Back to the course</div><div class="m-hero" style="margin:0 0 14px;padding:20px 26px;border-radius:16px"><div style="font-size:11.5px;letter-spacing:.08em;opacity:.9">MODULE 4 · QUIZ</div><div style="font:700 30px/1.2 'Source Sans 3'">Knowledge check</div></div><div data-t="count" style="font-size:11.5px;letter-spacing:.06em;color:#99060a;font-weight:700;margin-bottom:6px">3 OF 10 ANSWERED</div><div style="height:8px;background:#e9edf2;border-radius:99px;margin-bottom:12px"><div style="width:30%;height:8px;background:#99060a;border-radius:99px"></div></div><div class="lcard" style="display:block;margin:0"><div style="font-size:11.5px;letter-spacing:.06em;color:#99060a;font-weight:700">QUESTION 3 OF 10</div><p style="font-size:17px;font-weight:500;margin:6px 0 12px">A candidate’s answer is correct but delivered hesitantly. What should drive the mark?</p><div data-t="opts"><div class="qopt w"><i>✕</i>The candidate’s confidence</div><div class="qopt r"><i>✓</i>The accuracy and quality of the content</div><div class="qopt"><i>C</i>How the previous candidate scored</div></div><div data-t="fb" style="background:#f0fdf4;border:1px solid #bbf7d0;color:#14532d;border-radius:12px;padding:10px 14px;font-size:14.5px"><b>Correct</b><br>Marks reflect the quality of the answer, not how it is delivered.</div></div></div>`),
 cert:()=>shell('Examiner Training',`<div style="padding:16px 24px"><div style="display:flex;justify-content:space-between;align-items:center"><b style="font-size:22px">Your certificate</b><span class="m-btn" style="background:#fff;color:#99060a;border:1px solid #99060a">Back to course</span></div><div class="cert"><img class="m-logo" style="width:46px;height:46px;display:block;margin:0 auto 2px" src="${LOGO}" alt=""><div style="font-size:12px;letter-spacing:2px;color:#380911;font-weight:700">COSECSA</div><div style="font:italic 700 44px/1.1 Georgia,serif;color:#2d3748;margin-top:14px">Certificate</div><div style="font:12px sans-serif;letter-spacing:2px;color:#888;margin-top:2px">OF COMPLETION</div><div style="font:700 36px Georgia,serif;color:#6b1724;margin-top:14px">Dr. Jane Sample</div><div style="font:15px sans-serif;color:#555;margin-top:10px">has successfully completed</div><div style="font:700 20px sans-serif;color:#2d3748">Examiner Training</div></div><div style="text-align:center;margin-top:14px" data-t="dl"><span class="m-btn" style="background:#99060a;border-radius:10px;font-weight:700;padding:9px 20px">Print / Save as PDF</span> <span class="m-btn" style="background:#99060a;border-radius:10px;font-weight:700;padding:9px 20px">Download PNG</span></div></div>`)
};



var TOKENS={dash:function(o){return o==='mcs'},results:function(o){return o==='mcs'},mcsForm:function(o){return o==='resubmit'},fcsForm:function(o){return o==='resubmit'}};
function arg(screen,opt){var f=TOKENS[screen];return f?f(opt||''):undefined}
var RM=window.matchMedia&&matchMedia('(prefers-reduced-motion: reduce)').matches;

/* ---- the steps are the course topic cards (the page editor edits those) ---- */
var STEPS=topicEls.map(function(t,k){
  var wt=t.querySelector('.wt'),d=wt?wt.dataset:{},left=[],right=[];
  [].slice.call(t.querySelectorAll('.pn > *')).forEach(function(n){if(n===wt)return;(n.tagName==='P'?left:right).push(n.outerHTML)});
  return {k:k,tid:t.getAttribute('data-tid')||'',wtBid:wt?(wt.getAttribute('data-bid')||''):'',deck:d.deck||'all',sec:d.sec||'',kick:d.kick||'',screen:d.screen,hl:d.hl,opt:d.opt||'',url:d.url||'cosecsamis.org',gate:d.gate==='1',choose:d.choose==='1',
    title:t.querySelector('.th b').innerHTML,sub:t.querySelector('.th small').innerHTML,left:left,right:right};
}).filter(function(s){return SCREENS[s.screen]});

var wp=document.createElement('div');wp.className='wp';
wp.innerHTML='<div class="wp-top"><div class="wp-tabs" role="tablist" aria-label="Exam type"><button type="button" role="tab" data-deck="mcs" aria-selected="true">MCS (online)</button><button type="button" role="tab" data-deck="fcs" aria-selected="false">FCS specialties</button></div></div>'+
 '<div class="wp-grid"><aside class="wp-board" aria-label="Steps"><div class="wp-prog"><div class="wp-bar"><i></i></div><output></output></div><div class="wp-list"></div><button class="wp-reset" type="button">Clear my ticks</button></aside>'+
 '<div class="wp-stage"><div class="wp-shot"><div class="wp-chrome"><i></i><i></i><i></i><span></span></div><div class="wp-fit"><div class="wt-mock"></div></div><section class="wp-card" aria-live="polite"></section></div></div></div>';
root.querySelector('.topics').before(wp);
root.classList.add('wp-on');
var $=function(s){return wp.querySelector(s)};
var elList=$('.wp-list'),elCard=$('.wp-card'),elFit=$('.wp-fit'),elMock=$('.wt-mock'),elUrl=$('.wp-chrome span'),elTabs=$('.wp-tabs'),elBar=$('.wp-bar i'),elCount=$('.wp-prog output');

var deck='mcs',cur=0,prevKey=null,prevSec=null,tk=0,spotR=null,raf=0,shownKey=null,pending=false,gateOK=EDIT,fitScale=1;
var ticks={};try{ticks=JSON.parse(localStorage.getItem('m8-ticks')||'{}')||{}}catch(e){}
function saveTicks(){try{localStorage.setItem('m8-ticks',JSON.stringify(ticks))}catch(e){}}
function steps(){return STEPS.filter(function(s){return s.deck==='all'||s.deck===deck})}
var isDone=function(s){return !!ticks[deck+':'+s.k]};
function setDone(s,v){ticks[deck+':'+s.k]=v;saveTicks()}
function gatePending(){var s=steps()[cur];return !!(s&&s.gate&&!gateOK)}

/* reading a step also marks its course topic as read (what completes the module) */
function markRead(s){if(EDIT)return;var t=topicEls[s.k];if(t&&!CSCourse.seen[t.dataset.i]){var b=t.querySelector('.th');if(b&&!t.classList.contains('open'))b.click()}}
function markAllRead(){if(EDIT)return;topicEls.forEach(function(t){if(!CSCourse.seen[t.dataset.i]){var b=t.querySelector('.th');if(b&&!t.classList.contains('open'))b.click()}})}

function renderList(){
  var st=steps(),html='',last='';
  st.forEach(function(s,i){
    if(s.sec!==last){html+=(last?'</div>':'')+'<div class="wp-sec"><h3>'+s.sec+'</h3>';last=s.sec}
    html+='<div class="wp-item'+(i===cur?' cur':'')+(isDone(s)?' done':'')+'" data-i="'+i+'"'+(EDIT?' data-tid="'+s.tid+'"':'')+' role="button" tabindex="0"><button class="wp-tick" type="button" aria-label="Mark step '+(i+1)+' done" data-tick="'+i+'"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5 9-10"/></svg></button><span class="wp-t">'+s.title+'<span class="wp-s">'+s.sub+'</span></span></div>';
  });
  elList.innerHTML=html+'</div>';
  var n=st.filter(isDone).length;elCount.textContent=n+' of '+st.length+' done';elBar.style.width=(100*n/st.length)+'%';
}
function renderCard(){
  var st=steps(),s=st[cur],n=st.length;
  elCard.innerHTML='<div class="wp-c1"><div class="wp-kick">Step '+(cur+1)+' of '+n+' · '+s.kick+'</div><h2>'+s.title+'</h2>'+s.left.join('')+
   '<div class="wp-nav"><button class="wp-btn" id="wp-prev"'+(cur===0?' disabled':'')+'>← Back</button><button class="wp-btn pri" id="wp-next"'+(s.gate&&!gateOK?' disabled':'')+'>'+(s.gate&&!gateOK?'Click the highlighted area to continue':cur===n-1?'Finish':s.choose?'Continue with '+(deck==='mcs'?'MCS':'FCS')+' →':'Done, next step →')+'</button></div></div>'+
   '<div class="wp-c2">'+s.right.join('')+(s.choose?'<div class="wp-picks"><button type="button" class="wp-pick'+(deck==='mcs'?' on':'')+'" data-pick="mcs"><b>MCS</b></button><button type="button" class="wp-pick'+(deck==='fcs'?' on':'')+'" data-pick="fcs"><b>FCS specialties</b></button></div>':'')+'</div>';
  if(EDIT){[].forEach.call(elCard.querySelectorAll('.wp-c1,.wp-c2'),function(c){c.setAttribute('data-tid',s.tid)});if(s.wtBid)elFit.setAttribute('data-bid',s.wtBid);else elFit.removeAttribute('data-bid')}
  elCard.classList.remove('swap');void elCard.offsetWidth;elCard.classList.add('swap');
  elTabs.classList.toggle('glow',!!s.choose);
  $('#wp-prev').onclick=function(){go(cur-1)};
  $('#wp-next').onclick=function(){if(gatePending()){nudge();return}advance()};
  [].forEach.call(elCard.querySelectorAll('[data-pick]'),function(b){b.onclick=function(){if(b.dataset.pick!==deck)setDeck(b.dataset.pick,steps().indexOf(s));[].forEach.call(elCard.querySelectorAll('[data-pick]'),function(x){x.classList.toggle('on',x.dataset.pick===deck)})}});
}
function nudge(){var c=$('.wp-coach');if(c){c.classList.remove('shake');void c.offsetWidth;c.classList.add('shake')}}
function advance(){var st=steps(),s=st[cur];if(s.gate)gateOK=true;setDone(s,true);markRead(s);if(cur<st.length-1)go(cur+1);else{markAllRead();renderList()}}

function layout(){
  var tight=window.innerWidth>=900,box=elFit.parentElement,availW=box.clientWidth-24;
  var sc=availW/1000;
  if(tight){var availH=window.innerHeight-300;sc=Math.min(sc,Math.max(.5,availH/620))}
  fitScale=sc;elFit.style.width=(1000*sc)+'px';elFit.style.height=(620*sc)+'px';elMock.style.transform='scale('+sc+')';
}
var targetOf=function(s){return elMock.querySelector('[data-t="'+s.hl+'"]')};
function rectOf(t){var m=elMock.getBoundingClientRect(),r=t.getBoundingClientRect(),p=6;return{x:(r.left-m.left)/fitScale-p,y:(r.top-m.top)/fitScale-p,w:r.width/fitScale+p*2,h:r.height/fitScale+p*2}}
function drawSpot(R){var sp=elMock.querySelector('.spot');if(!sp)return;sp.style.left=R.x+'px';sp.style.top=R.y+'px';sp.style.width=R.w+'px';sp.style.height=R.h+'px'}
function placeCoach(R){
  var c=elMock.querySelector('.wp-coach');if(!c)return;var w=290,h=c.offsetHeight||90,left=Math.max(12,Math.min(1000-w-12,R.x+R.w/2-w/2));
  var top=R.y-h-18,dir='up';if(top<64){top=R.y+R.h+18;dir='down'}
  c.style.left=left+'px';c.style.top=top+'px';c.className='wp-coach '+dir+(c.classList.contains('shake')?' shake':'');c.style.setProperty('--ax',(R.x+R.w/2-left)+'px');
}
function follow(t,from,ms){
  cancelAnimationFrame(raf);var sp=elMock.querySelector('.spot');if(!sp||!t)return;
  sp.style.opacity=1;sp.style.pointerEvents='auto';sp.style.cursor='pointer';sp.onclick=function(){advance()};
  var c=from||rectOf(t),t0=performance.now();
  (function step(now){var tg=rectOf(t),k=RM?1:.2;c={x:c.x+(tg.x-c.x)*k,y:c.y+(tg.y-c.y)*k,w:c.w+(tg.w-c.w)*k,h:c.h+(tg.h-c.h)*k};drawSpot(c);spotR=c;placeCoach(c);
    if(now-t0<ms)raf=requestAnimationFrame(step);else{drawSpot(tg);spotR=tg;placeCoach(tg)}})(t0);
}
var SCROLL={mcsForm:{marks:'marks'},fcsForm:{marks:'marks',total:'total'}};
function scrollTop(s){var page=elMock.querySelector('.m-page'),k=(SCROLL[s.screen]||{})[s.hl];if(!page||!k)return 0;var t=elMock.querySelector('[data-t="'+k+'"]');return t?Math.max(0,t.offsetTop-(page.clientHeight-t.offsetHeight)/3):0}
function chapter(name){
  var old=elFit.querySelector('.wp-chap');if(old)old.remove();
  var c=document.createElement('div');c.className='wp-chap';c.innerHTML='<small>Next section</small><b>'+name+'</b>';elFit.appendChild(c);setTimeout(function(){c.remove()},1700);
}
function screenArg(s){return s.screen==='dash'?deck==='mcs':arg(s.screen,s.opt)}
function newScreen(s,dir,secChanged,first){
  var my=++tk,old=elMock.querySelector('.wp-scr');cancelAnimationFrame(raf);var sp0=elMock.querySelector('.spot');if(sp0)sp0.style.opacity=0;
  if(secChanged&&!RM)chapter(s.sec);
  var wait=RM||first?0:(secChanged?450:240);pending=true;
  if(old&&!RM)old.classList.add(dir>0?'out-f':'out-b');
  setTimeout(function(){
    if(my!==tk)return;pending=false;shownKey=s.screen+':'+s.opt+':'+deck;
    elMock.innerHTML='<div class="wp-scr '+(RM||first?'':(dir>0?'in-f':'in-b'))+'">'+SCREENS[s.screen](screenArg(s))+'</div><div class="spot"></div>'+(s.gate&&!gateOK?'<div class="wp-coach"><b>Your turn</b>Click the highlighted section to move forward.</div>':'');
    elUrl.textContent=s.url;
    var page=elMock.querySelector('.m-page'),t=targetOf(s);spotR=null;
    setTimeout(function(){if(my!==tk||!t)return;var top=scrollTop(s);if(page&&top>0)page.scrollTo({top:top,behavior:RM?'auto':'smooth'});follow(t,null,page&&top>0?1100:300)},RM?0:(first?200:520));
  },wait);
}
function sameScreen(s){
  tk++;var t=targetOf(s);if(!t)return;var page=elMock.querySelector('.m-page'),top=scrollTop(s);
  if(page)page.scrollTo({top:top,behavior:RM?'auto':'smooth'});follow(t,spotR,1100);
}
function go(i){
  if(i>cur&&gatePending()){nudge();return}
  var st=steps(),ni=Math.max(0,Math.min(st.length-1,i)),dir=ni>=cur?1:-1;cur=ni;
  renderList();renderCard();layout();
  var s=st[cur],key=s.screen+':'+s.opt+':'+deck,secChanged=prevSec!==null&&s.sec!==prevSec;
  if(prevKey===null)newScreen(s,dir,false,true);
  else if(!pending&&key===shownKey&&!secChanged)sameScreen(s);
  else newScreen(s,dir,secChanged,false);
  prevKey=key;prevSec=s.sec;
  try{sessionStorage.setItem('m8-pos-'+deck,cur)}catch(e){}
}
function setDeck(d,keep){
  deck=d;[].forEach.call(elTabs.querySelectorAll('button'),function(b){b.setAttribute('aria-selected',b.dataset.deck===d)});
  var c=0;try{c=+sessionStorage.getItem('m8-pos-'+d)||0}catch(e){}
  if(keep!==undefined)c=keep;else if(cur<=2&&c>2)c=cur;cur=Math.min(c,steps().length-1);prevKey=null;prevSec=null;layout();go(cur);
}
[].forEach.call(elTabs.querySelectorAll('button'),function(b){b.onclick=function(){setDeck(b.dataset.deck)}});
elList.addEventListener('click',function(e){var t=e.target.closest('[data-tick]');if(t){var s=steps()[+t.dataset.tick];setDone(s,!isDone(s));renderList();return}var it=e.target.closest('.wp-item');if(it)go(+it.dataset.i)});
elList.addEventListener('keydown',function(e){if((e.key==='Enter'||e.key===' ')&&e.target.classList.contains('wp-item')){e.preventDefault();go(+e.target.dataset.i)}});
$('.wp-reset').onclick=function(){steps().forEach(function(s){delete ticks[deck+':'+s.k]});saveTicks();renderList()};
if('ResizeObserver' in window)new ResizeObserver(function(){layout();var s=steps()[cur],t=s&&targetOf(s);if(t){var R=rectOf(t);drawSpot(R);spotR=R}}).observe(wp.querySelector('.wp-shot'));
else window.addEventListener('resize',layout);
/* the editor's preview asks for the step that holds a block (cw-reveal, sent by the preview frame) */
if(EDIT)document.addEventListener('cw-reveal',function(e){
  var s=STEPS.filter(function(x){return x.k===+e.detail.k})[0];if(!s)return;
  if(s.deck!=='all'&&s.deck!==deck){deck=s.deck;setDeck(s.deck,steps().indexOf(s))}else go(steps().indexOf(s));
});
setDeck('mcs');
})();
