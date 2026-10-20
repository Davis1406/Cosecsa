/* Module 7 extras. Needs cosecsa-course.js loaded first */
(function(){
var $=CSCourse.$,topics=CSCourse.topics,seen=CSCourse.seen,paint=CSCourse.paint,KEY=CSCourse.key;
document.querySelectorAll('[role=tablist]').forEach(function(list){var tabs=[].slice.call(list.querySelectorAll('[role=tab]')),scope=list.parentNode;tabs.forEach(function(b){b.addEventListener('click',function(){tabs.forEach(function(x){x.setAttribute('aria-selected',x===b?'true':'false')});tabs.forEach(function(x){var p=document.getElementById(x.getAttribute('aria-controls'));if(p)p.classList.toggle('on',x===b)})})})});
})();
