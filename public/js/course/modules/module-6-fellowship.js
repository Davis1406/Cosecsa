/* Module 6 extras. Needs cosecsa-course.js loaded first */
(function(){
var topics=CSCourse.topics,seen=CSCourse.seen,paint=CSCourse.paint,KEY=CSCourse.key;
var $=function(i){return document.getElementById(i)};
var cards=[["Already passed the COSECSA exam", "Some candidates passed the COSECSA Membership examination a few years ago."], ["Hold an MMed degree", "Candidates may hold an MMed (specialty in surgery) degree from a university in the region."], ["Other surgeons", "Candidates may be surgeons with a degree from a certifying body outside the region."]];var cw=$('cards');
if(cw)cards.forEach(function(c){var d=document.createElement('div');d.className='fc';d.innerHTML='<button type="button" aria-pressed="false"><div class="f"><b>'+c[0]+'</b><span>Tap to reveal</span></div><div class="b">'+c[1]+'</div></button>';d.firstChild.addEventListener('click',function(){d.classList.toggle('flip');this.setAttribute('aria-pressed',d.classList.contains('flip'))});cw.appendChild(d)});
var qs=[];
})();
