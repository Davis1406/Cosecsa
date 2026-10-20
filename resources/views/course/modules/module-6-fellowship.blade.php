@extends('course.layout', ['mod'=>'6','slug'=>'module-6-fellowship','key'=>'m6-seen','lite'=>false,'moduleJs'=>true])

@section('title', 'Module 6 The Fellowship Examination')

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span><button id="theme" type="button">Light / dark</button></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"><path d="M30 160V90M70 160V60M110 160V110M150 160V40M20 160h160"/></svg><div class="k">Module 6 · Fellowship examination</div><h1>The Fellowship examination</h1><p class="l">COSECSA's clinical and oral Fellowship examinations: who the candidates are, how each station type is run, and how to mark fairly.</p>
<div class="prog"><span id="pc">0 of 7 topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i11"/></svg></span><span><b>What you will learn</b><small>Outcomes for this module</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">By the end of this module you will be able to:</p><ol class="nums"><li>Describe the standard expected of a Fellowship candidate.</li><li>Run a patient-based OSCE, a patientless OSCE and a viva voce station.</li><li>Complete the marking sheet and record the total domain score and global assessment separately.</li><li>Work with your co-examiner and with patients and actors.</li><li>Avoid the common errors that occur during testing.</li></ol></div></div>

<div class="topic" data-i="1"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i4"/></svg></span><span><b>Candidates and standard</b><small>Who you examine and the "Day 1 consultant" standard</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">Candidates have already passed the FCS Part I written examination. For Part II there are three kinds of candidate. Tap each card.</p>
<div class="grid2" id="cards"></div>
<p>For some sub-specialties, for example Paediatric Orthopaedics, candidates may already hold an FCS in Orthopaedics.</p>
<div class="callout"><b>Standard of competence.</b> A successful FCS candidate is ready to begin independent practice as a "Day 1" consultant. The examination assesses everyday scenarios across five domains.</div>
<div class="chipcloud"><span>Patient care</span><span>Medical knowledge</span><span>Practice-based learning and improvement</span><span>Professionalism</span><span>Systems-based practice</span></div></div></div>

<div class="topic" data-i="2"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i13"/></svg></span><span><b>Format of the examination</b><small>Two examiners, 20 and 30 minute stations</small></span><span class="ch"></span></button><div class="pn"><p>Every specialty except General Surgery, Urology and Cardiothoracic Surgery has structured short clinical stations and viva voce (oral) stations.</p>
<div class="stats"><div><b>2</b><span>examiners per station and per viva room</span></div><div><b>20 min</b><span>typical clinical station</span></div><div><b>30 min</b><span>typical viva station</span></div><div><b>2 days</b><span>held from the end of November to early December</span></div></div>
<ul style="margin:0;padding-left:20px;max-width:66ch;display:flex;flex-direction:column;gap:6px">
<li>You always have a co-examiner, but you <b>mark independently</b>. Do not compare marks or discuss candidates.</li>
<li>Stay in your assigned location for each part, except in break times.</li>
<li>Your Panel Head decides the number of stations and tells all examiners in advance. Timing changes are also announced in advance.</li>
<li>Whistles, bells or other alarms mark the time. Remote video-conferencing examiners may use a different system.</li></ul>
<div class="callout"><b>General Surgery, Urology and Cardiothoracic Surgery</b> are patientless examinations. Follow the Patientless OSCE topic for them.</div></div></div>

<div class="topic" data-i="3"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i16"/></svg></span><span><b>Patient-based OSCE</b><small>What you assess and what you do</small></span><span class="ch"></span></button><div class="pn"><div class="grid2"><div class="cs-card"><h3>What you assess</h3><ul style="margin:8px 0 0;padding-left:18px"><li>History taking, clinical examination and interpreting findings.</li><li>Clinical reasoning, diagnosis, choice of investigations and management plan.</li><li>Communication, professionalism, consent and patient safety.</li></ul></div>
<div class="cs-card"><h3>What you do</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Use the approved marking sheet and award marks by the predefined criteria.</li><li>Protect the patient's dignity, privacy and comfort throughout.</li><li>Record the total domain score and the global assessment separately, where required.</li></ul></div></div>
<h3>Patients and actors</h3>
<p>Make sure the patient or standardised patient (actor) is fully briefed on their role and is safe and comfortable all day. If a patient or actor does not wish to continue, tell the Invigilator. The Invigilator tells the Panel Head and alternative arrangements are made.</p></div></div>

<div class="topic" data-i="4"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i14"/></svg></span><span><b>Patientless OSCE</b><small>Photographs, imaging and investigations</small></span><span class="ch"></span></button><div class="pn"><div class="grid2"><div class="cs-card"><h3>What you assess</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Interpreting clinical photographs, imaging, investigations, scenarios, instruments, specimens or other station material.</li><li>Clinical reasoning, diagnosis, choice of investigations and management decisions.</li></ul></div>
<div class="cs-card"><h3>What you do</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Follow the standard station instructions and the allocated time.</li><li>Use the predefined questions, expected responses and marking criteria.</li><li>Award marks for accurate interpretation, sound reasoning and justified decisions.</li><li>Do not lead, over-prompt or add tasks outside the station objectives.</li><li>Record the total domain score and global assessment separately, where required.</li></ul></div></div></div></div>

<div class="topic" data-i="5"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i20"/></svg></span><span><b>Viva voce</b><small>Cases A, B and C</small></span><span class="ch"></span></button><div class="pn"><p>The viva tests depth and breadth of specialty knowledge, clinical judgement, reasoning, management decisions, recognising complications, prioritising, and defending decisions at Fellowship level.</p>
<div class="grid2"><div class="cs-card"><h3>Standard questions</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Use the approved questions written by the specialty.</li><li>Know the expected answers, marking criteria and objectives beforehand.</li><li>Use the same questions and follow-up probes for every candidate.</li><li>Do not add unrelated questions or change the difficulty without reason.</li></ul></div>
<div class="cs-card"><h3>Conducting the viva</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Ask clearly, neutrally and professionally.</li><li>Give the candidate time to think.</li><li>Use follow-ups to explore reasoning.</li><li>No leading questions, hints or steering.</li><li>Keep a consistent approach and time.</li></ul></div>
<div class="cs-card"><h3>Marking</h3><ul style="margin:8px 0 0;padding-left:18px"><li>Judge accuracy, depth and breadth.</li><li>Judge reasoning, prioritisation and justification.</li><li>Mark by the predefined criteria.</li><li>Accept a clinically justified alternative answer.</li><li>Do not let personal preference lower a fair alternative.</li></ul></div></div>
<h3>Cases and sheets</h3>
<p>A viva has at least two cases, A and B, and sometimes a case C. You and your co-examiner take turns presenting the cases to each candidate, but you both mark both cases. Each examiner completes one marking sheet per candidate, and all cases go on that same sheet.</p></div></div>

<div class="topic" data-i="6"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i17"/></svg></span><span><b>Marking sheets and grading</b><small>Clinical and viva sheets</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">Each examiner marks independently, using the descriptors, and never compares marks with a co-examiner.</p>
<div class="callout">Marks are currently entered in the online marking portal. The two sheets below show the layout you will see there and on any paper copy.</div>
<h3>The marks</h3>
<ol class="bands"><li><b>2</b><span>Seriously deficient</span></li><li><b>4</b><span>Below expected standard</span></li><li><b>6</b><span>Meets expected standard</span></li><li><b>8</b><span>Above expected standard</span></li><li><b>10</b><span>Outstanding</span></li></ol>
<ul class="pts"><li>Refer to the descriptors when marking (see Module 7).</li><li>All marks are completed by each examiner independently.</li><li>No discussion during marking.</li></ul>
<h3>Clinical examination sheet</h3>
<div class="sheet"><div class="sh-h"><span>Candidate number</span><span>Clinical station # and case type</span></div>
<div class="scroll"><table><thead><tr><th></th><th>Overall professional capacity and patient care</th><th>Knowledge and judgment</th><th>Quality of response</th><th>Bedside manner</th></tr></thead><tbody><tr><th>Marks</th><td>2, 4, 6, 8 or 10</td><td>2, 4, 6, 8 or 10</td><td>2, 4, 6, 8 or 10</td><td>2, 4, 6, 8 or 10</td></tr></tbody></table></div>
<p class="note">Then: comments if any (based on the descriptors), examiner's name and signature.</p></div>
<h3>Viva sheet</h3>
<div class="sheet"><div class="sh-h"><span>Candidate number</span><span>Viva station #</span></div>
<div class="halves"><div><div class="label">First half: first 15 minutes</div><b>Examiner 1 leads</b><p>Case 1. Write the broad areas of discussion, then give two marks (rows 1 and 2), each for <b>knowledge and judgement</b> and <b>quality of response</b>.</p></div>
<div><div class="label">Second half: second 15 minutes</div><b>Examiner 2 leads</b><p>Case 2. Write the broad areas of discussion, then give two marks (rows 1 and 2), each for <b>knowledge and judgement</b> and <b>quality of response</b>.</p></div></div>
<p class="note">Then: examiner's name and signature.</p></div>
<ul class="pts"><li>Mark by the predefined criteria and accept a clinically justified alternative answer.</li><li>You will receive the expected answers for your station before the examination.</li></ul></div></div>

</div>
<div class="done" id="done"><h2>You have read all 7 topics of Module 6.</h2><p>The full marking descriptors are in Module 7. Use the course menu to move on.</p></div>
</div>
@endsection
