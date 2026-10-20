@extends('course.layout', ['mod'=>'1','slug'=>'module-1-welcome','key'=>'m1-seen','lite'=>false,'moduleJs'=>false])

@section('title', 'Module 1 Welcome Examiners')

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span><button id="theme" type="button">Light / dark</button></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"><path d="M100 30l70 30-70 30-70-30zM50 80v40c0 14 22 26 50 26s50-12 50-26V80"/></svg><div class="k">Module 1 · Welcome</div><h1>Welcome, COSECSA examiner</h1><p class="l">What this course covers, who can examine, and how to complete it.</p>
<div class="prog"><span id="pc">0 of 6 topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i10"/></svg></span><span><b>About COSECSA</b><small>Who we are</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">The College of Surgeons of East, Central and Southern Africa (COSECSA) is a non-profit professional body that fosters postgraduate education in surgery and provides surgical training throughout the East, Central and Southern Africa region.</p>
<p>Visit the COSECSA website to learn more about the organisation.</p>
<div class="callout">COSECSA is fortunate and very appreciative to regularly have Examiners from over 30 countries and from a variety of backgrounds who give their time and expertise to the College.</div></div></div>

<div class="topic" data-i="1"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i17"/></svg></span><span><b>Course objectives</b><small>What this course is for</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">The objectives of this course are:</p>
<ol class="nums"><li>To ensure that you are familiar with the COSECSA Membership and Fellowship exams, and your role as an Examiner in either or both.</li><li>To ensure that you examine all candidates in a fair, standardized and consistent manner.</li><li>To ensure that your actions as an Examiner uphold the integrity and quality of the COSECSA exams.</li></ol>
<div class="callout">All invited Examiners, whether you have examined for COSECSA previously or will be joining us for the first time, are required to complete this course.</div>
<p>This course assumes some familiarity with serving as an Examiner in surgical exams, and is not intended to cover every aspect of exams.</p></div></div>

<div class="topic" data-i="2"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i19"/></svg></span><span><b>How the course works</b><small>Navigation, completion and animations</small></span><span class="ch"></span></button><div class="pn"><h3>Course navigation</h3>
<p>Each module is one short page. Tap a topic to open it, and tap it again to close it. Topics can be opened in any order. A tick shows the topics you have read, and the bar at the top shows how far you have got. Use the course menu to move between modules.</p>
<h3>Completion</h3>
<p>You may complete this course at any time up to one week prior to the exam. When you have finished all the modules there is a quiz at the end that summarises the main learning points of the course. The final page says "Congratulations, you have completed this course." This marks completion of the course.</p>
<h3>Animations</h3>
<p>Some modules include animations to help illustrate examples of positive and negative interactions in exams, and what you as an Examiner should do. You can control the playing speed of the animations using the playback control in the menu bar.</p>
<h3>Your exam instructions</h3>
<p>Separate from this course you will receive a specific set of instructions (dates, times, links, locations and so on) for the COSECSA exam(s) you have been invited to.</p></div></div>

<div class="topic" data-i="3"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i17"/></svg></span><span><b>Examiner eligibility</b><small>Observers, examiners and non-fellows</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">At a minimum, Examiners must have received an invitation from COSECSA to participate in the next upcoming exams and meet the criteria below.</p>
<h3>Part 1: Observer eligibility</h3>
<ul class="ticks"><li>Fully paid-up Fellow or Member of COSECSA</li><li>Active trainer at a COSECSA-accredited or university-based training programme</li><li>At least 3 years of practice as a specialist</li><li>At least 2 years of experience at a teaching facility</li></ul>
<h3>Part 2: Examiner eligibility</h3>
<ul class="ticks"><li>Fully paid-up Fellow or Member of COSECSA</li><li>Active trainer at a COSECSA-accredited or university-based training programme</li><li>At least 5 years of practice as a specialist surgeon</li><li>Holds or has held a full consultant post (not a locum post)</li><li>At least 3 years of experience at a teaching facility</li><li>Has observed at least 2 COSECSA examinations</li></ul>
<h3>Invited examiners who are not COSECSA Fellows</h3>
<p>All three of these must apply.</p>
<ul class="ticks"><li>Certified surgeon in their country of practice</li><li>More than 5 years of experience in teaching and trainee assessment</li><li>Recommended by 2 COSECSA examiners</li></ul></div></div>

<div class="topic" data-i="4"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i16"/></svg></span><span><b>Personal attributes</b><small>The commitment we expect</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">In their personal attributes, Examiners must show commitment to:</p>
<ul class="ticks"><li>High professional standards as an Examiner, including understanding of appropriate examining techniques and a policy of courtesy, fairness and non-discrimination towards all candidates.</li><li>High professional standards in teaching, including an ability and positive attitude towards teaching.</li><li>Professional competence in the practice of surgery and an interest in the relevant basic science disciplines.</li><li>Commitment to the examination process.</li><li>Fulfilling obligations such as writing questions and attending examinations except in exceptional circumstances.</li><li>Commitment to ongoing assessment, training and development as an Examiner.</li></ul></div></div>

<div class="topic" data-i="5"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i15"/></svg></span><span><b>Questions and thanks</b><small>Who to contact</small></span><span class="ch"></span></button><div class="pn"><div class="callout">Any questions? Something not working? Email Mr Amani Pascal, Exams Officer: <b>exams_asst@@cosecsa.org</b></div>
<p class="cs-lead">Thank you for your commitment to COSECSA and to the quality and integrity of the COSECSA exams, which you are demonstrating by taking this course.</p></div></div>

</div>
<div class="done" id="done"><h2>You have read all 6 topics of Module 1.</h2><p>Use the course menu to move to Module 2.</p></div>
</div>
@endsection
