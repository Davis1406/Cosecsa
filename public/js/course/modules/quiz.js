/* Examiner quiz on the MIS. Needs cosecsa-course.js loaded first and window.CS_QUIZ (set by the page).
   Each answer is checked on the server (it alone knows the key), and the score is saved when every question is answered. */
(function(){
var C=window.CS_QUIZ,$=CSCourse.$;if(!C)return;
var ans={},right={},locked=false;
function txt(h){return new DOMParser().parseFromString(String(h||''),'text/html').body.textContent.trim()}
function post(url,body){return fetch(url,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':C.csrf},body:JSON.stringify(body)}).then(function(r){return r.json().then(function(j){return{ok:r.ok,j:j}})})}
function count(o){return Object.keys(o).length}
function build(){document.querySelectorAll('.qlist').forEach(function(box){box.innerHTML='';box.style.display='flex';box.style.flexDirection='column';box.style.gap='12px';
 (C.sections[box.dataset.s]||[]).forEach(function(q){
  var d=document.createElement('div');d.className='qc';d.id='q'+q.id;
  d.innerHTML='<div class="qn">Question '+q.n+' of '+C.total+'</div><div class="qt"></div>';d.querySelector('.qt').textContent=txt(q.title);
  q.answers.forEach(function(a,j){var b=document.createElement('button');b.type='button';b.className='opt';b.dataset.a=a.id;b.innerHTML='<span class="l">'+'ABCDEFGH'[j]+'</span><span></span>';b.lastChild.textContent=txt(a.title);
   b.addEventListener('click',function(){pick(q,a.id,d)});d.appendChild(b)});
  var w=document.createElement('div');w.className='why';w.hidden=true;w.innerHTML='<b>Explanation</b><span></span>';d.appendChild(w);
  box.appendChild(d)})})}
function pick(q,aid,d){if(locked||ans[q.id]!==undefined)return;ans[q.id]=aid;d.classList.add('done');
 post(C.checkUrl,{question_id:q.id,answer_id:aid}).then(function(r){
  if(!r.ok){delete ans[q.id];d.classList.remove('done');return}
  var j=r.j;right[q.id]=!!j.is_correct;
  d.querySelectorAll('button.opt').forEach(function(b){if((j.correct_ids||[]).indexOf(b.dataset.a)>-1)b.classList.add('right');else if(b.dataset.a===aid)b.classList.add('wrong')});
  var w=d.querySelector('.why');w.lastChild.textContent=txt(j.feedback);w.hidden=false;progress()})
 .catch(function(){delete ans[q.id];d.classList.remove('done')})}
function progress(){var done=count(right);$('pc').textContent=done+' of '+C.total+' answered';$('fill').style.width=(done/C.total*100)+'%';
 if(done===C.total)finish()}
function finish(){if(locked)return;locked=true;
 post(C.submitUrl,{answers:ans}).then(function(r){var j=r.j||{};var ok=!!j.passed;var n=Object.keys(right).filter(function(k){return right[k]}).length;
  var sc=$('score');sc.classList.add('on');sc.classList.toggle('fail',!ok);
  sc.querySelector('h2').textContent=ok?'Quiz passed':'Quiz complete: not passed yet';
  $('res').textContent='You scored '+n+' of '+C.total+' ('+(j.score!==undefined?Math.round(j.score):Math.round(n/C.total*100))+'%). The pass mark is '+C.passMark+'% ('+C.needed+' of '+C.total+'). '+(ok?'You have passed.':'You have not passed yet. Read the explanations, then start again.');
  var nx=$('next');if(ok&&C.nextUrl){nx.href=C.nextUrl;nx.textContent='Continue: '+(C.nextTitle||'next module')+' →';nx.hidden=false}else nx.hidden=true;
  sc.scrollIntoView({behavior:'smooth',block:'center'})})
 .catch(function(){locked=false})}
build();progress();
$('again').addEventListener('click',function(){ans={};right={};locked=false;build();$('score').classList.remove('on');progress();window.scrollTo({top:0})});
})();
