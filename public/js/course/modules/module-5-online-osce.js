/* Module 5 extras. Needs cosecsa-course.js loaded first */
(function(){
var topics=CSCourse.topics,seen=CSCourse.seen,paint=CSCourse.paint,KEY=CSCourse.key;
var cards=[["Objective","The content and scoring are standardised. Each station focuses on one area of clinical competence."],["Structured","Every candidate faces the same problem and task in the same time."],["Clinical","Tasks represent real-life clinical situations."],["Examination","The OSCE gives a reliable assessment of a candidate's competence."]];
var cw=document.getElementById('cards');
cards.forEach(function(c){var d=document.createElement('div');d.className='fc';d.innerHTML='<button type="button" aria-pressed="false"><div class="f"><b>'+c[0]+'</b><span>Tap to reveal</span></div><div class="b">'+c[1]+'</div></button>';
 d.firstChild.addEventListener('click',function(){d.classList.toggle('flip');this.setAttribute('aria-pressed',d.classList.contains('flip'))});cw.appendChild(d)});

var $=function(i){return document.getElementById(i)};
var gl='Pass';
function calc(){var k=+$('k').value||0,p=+$('p').value,c=+$('c').value,t=+$('t').value,s=k+p+c+t;
 $('tot').textContent=s+' / 20';
 var f=$('flag');
 if(s>20){f.textContent='Above 20. Check the knowledge marks.'}
 else if(gl==='Fail'&&s>=14){f.textContent='High total with a Fail rating: the examiner should review this discrepancy.'}
 else if(gl==='Pass'&&s<=9){f.textContent='Low total with a Pass rating: the examiner should review this discrepancy.'}
 else f.textContent='Total and global assessment are consistent. The result is set by comparing the total with the approved pass mark.'}
['k','p','c','t'].forEach(function(i){$(i).addEventListener('input',calc)});
Array.prototype.forEach.call($('g').children,function(b){b.addEventListener('click',function(){gl=b.textContent;Array.prototype.forEach.call($('g').children,function(x){x.setAttribute('aria-pressed',x===b)});calc()})});
calc();

var qs=[
["What standard do you apply to Membership candidates?",["The top of the class","Minimum competence to safely progress to first-year specialty training","A fully qualified surgeon"],1,"You are identifying those with the minimum competencies to progress."],
["A candidate is lost and silent. What may you do?",["Hint toward the expected answer","Restate or redirect using judgement, without prompting the answer","End the station early"],1,"You may clarify language, but must not prompt."],
["When do you record the global assessment?",["After the candidate has left, before totalling the domain marks","Before the candidate enters","After comparing marks with a colleague"],0,"Mark it once the candidate has left, and do not compare marks."],
["How does the global assessment relate to the total domain score?",["It replaces it","It complements it and does not replace it","It is ignored unless the candidate fails"],1,"The two together support calibration and quality assurance."],
["A candidate's internet is lost mid-station. What happens?",["The station is repeated at the end of the cycle","The candidate is failed","You mark what you saw"],0,"The station is repeated at the end of the cycle, or a spare examiner is invited if an examiner is lost."]];

var scenes=[["10:00","Join early. Admit the candidate and check their ID against the rotation. Welcome them, and do not use their name or country."],
["10:00","The clock starts. Read the instructions and the first question exactly as written."],
["07:40","Ask each question as written. Mark as you go, with the marking sheet out of the candidate's view."],
["04:15","If the candidate does not know an answer, move on to the next question. Do not give hints."],
["00:20","At the final time signal the candidate leaves, mid-sentence if necessary. No extra time is given."],
["00:00","Only after the candidate has left, record your global assessment. Do not discuss the candidate with anyone."],
["03:50","If a connection is lost, tell your moderator in the group's WhatsApp group. The station is repeated at the end of the cycle, or a spare examiner is invited."]];
var sc=0,auto=true,tm=null,stage=$('stage');
function show(n){sc=(n+scenes.length)%scenes.length;stage.dataset.scene=sc;$('timer').textContent=scenes[sc][0];$('cap').textContent=scenes[sc][1];$('pos').textContent=(sc+1)+' / '+scenes.length}
function tick(){clearTimeout(tm);if(auto)tm=setTimeout(function(){show(sc+1);tick()},5500)}
$('next').addEventListener('click',function(){show(sc+1);tick()});
$('prev').addEventListener('click',function(){show(sc-1);tick()});
$('play').addEventListener('click',function(){auto=!auto;this.textContent=auto?'Pause':'Play';tick()});
if(matchMedia('(prefers-reduced-motion:reduce)').matches){auto=false;$('play').textContent='Play'}
show(0);tick();
})();
