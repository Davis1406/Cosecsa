/* COSECSA examiner course: accordion, progress, light/dark. No dependencies, no globals except window.CSCourse. */
(function(){
var root=document.querySelector('.cs-course');if(!root)return;
var $=function(i){return document.getElementById(i)};
var topics=[].slice.call(root.querySelectorAll('.topic'));
var KEY=root.getAttribute('data-progress-key')||'';var lite=root.hasAttribute('data-lite');var seen={};
/* On the MIS the read topics live on the server (data-save-url + data-seen); without them (previews) they stay in this browser. */
var SAVE=root.getAttribute('data-save-url')||'';var CSRF=root.getAttribute('data-csrf')||'';
if(SAVE){try{(JSON.parse(root.getAttribute('data-seen')||'[]')||[]).forEach(function(i){seen[i]=1})}catch(e){}
 var sv=root.querySelectorAll('.prog span');if(sv[1])sv[1].textContent='Saved to your account'}
else if(!lite){try{seen=JSON.parse(localStorage.getItem(KEY)||'{}')||{}}catch(e){}}
function save(i){if(!SAVE){try{localStorage.setItem(KEY,JSON.stringify(seen))}catch(e){}return}
 try{fetch(SAVE,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':CSRF},body:JSON.stringify({topics:[+i]})}).catch(function(){})}catch(e){}}
function paint(){if(lite)return;var n=0;topics.forEach(function(t){var s=!!seen[t.dataset.i];if(s)n++;t.classList.toggle('seen',s)});
 if($('pc'))$('pc').textContent=n+' of '+topics.length+' topics read';if($('fill'))$('fill').style.width=(n/topics.length*100)+'%';if($('done'))$('done').classList.toggle('on',n===topics.length)}
topics.forEach(function(t){var b=t.querySelector('.th');b.addEventListener('click',function(){var open=!t.classList.contains('open');
 topics.forEach(function(o){o.classList.remove('open');o.querySelector('.th').setAttribute('aria-expanded','false')});
 if(open){t.classList.add('open');b.setAttribute('aria-expanded','true');
  if(!lite){seen[t.dataset.i]=1;save(t.dataset.i)}
  setTimeout(function(){t.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion:reduce)').matches?'auto':'smooth',block:'start'})},30)}
 paint()})});
if($('theme'))$('theme').addEventListener('click',function(){var dark=root.dataset.theme?root.dataset.theme==='dark':matchMedia('(prefers-color-scheme:dark)').matches;root.dataset.theme=dark?'light':'dark'});
paint();
window.CSCourse={img:root.getAttribute('data-img')||'/images/course/',root:root,$:$,topics:topics,seen:seen,paint:paint,key:KEY};
})();
