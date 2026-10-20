@extends('course.layout', ['mod'=>'5','slug'=>'module-5-online-osce','key'=>'m5-seen','lite'=>false,'moduleJs'=>true])

@section('title', 'Module 5 Online OSCE')

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"><circle cx="100" cy="100" r="70"/><path d="M60 100h80M100 60v80"/><circle cx="100" cy="100" r="12" fill="#fff"/></svg><div class="k">Module 5 · Membership examination</div><h1>The online OSCE</h1><p class="l">How the MCS Part II online OSCE is built, run and marked, and how to examine fairly and to the approved standard.</p>
<div class="prog"><span id="pc">0 of 7 topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i11"/></svg></span><span><b>What you will learn</b><small>Five outcomes for this module</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">By the end of this module you will be able to:</p><ol class="nums">
    <li>Explain what the MCS Part II OSCE is for and the minimum standard expected.</li>
    <li>Describe how the examination runs across the Entrust and Zoom platforms, and what to do if the connection fails.</li>
    <li>Run a station and mark it without prompting or bias.</li>
    <li>Record domain marks and a global assessment, and explain how they relate to the pass mark.</li>
    <li>Behave professionally before, during and after the examination.</li>
  </ol></div></div>

<div class="topic" data-i="1"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i17"/></svg></span><span><b>Purpose and standard</b><small>Who you examine and the pass standard</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">The Membership OSCE decides whether a trainee is ready to progress to the first year of specialty training. It does not decide whether they are a finished surgeon.</p>
  <div class="grid2">
    <div class="cs-card"><h3>Who you will examine</h3><p>Candidates have already passed the MCS Part I written examination. Together the two parts test whether trainees have the knowledge, skills and attributes needed to progress to specialist training in surgery.</p></div>
    <div class="cs-card"><h3>The standard</h3><p>You are not looking for the top of the class. As a group of examiners you are finding the candidates with the minimum competencies to progress.</p></div>
  </div>
  <div class="callout"><b>Ask yourself:</b> if I were responsible for training this person in FCS specialty surgery, would I be satisfied that they are safe and adequately trained to this point?</div>
  <div class="scroll cs-card"><table>
    <tr><th></th><th>MCS Part I</th><th>MCS Part II (OSCE)</th></tr>
    <tr><td>Format</td><td>Multiple-choice questions</td><td>Objective Structured Clinical Examination</td></tr>
    <tr><td>Level of thinking</td><td>Lower levels (Bloom's taxonomy)</td><td>Applied, in clinical scenarios</td></tr>
    <tr><td>Shows what the candidate</td><td><b>Knows</b></td><td><b>Shows how</b> (Miller's pyramid)</td></tr>
  </table></div>
  <h3>How the Membership examination has changed</h3>
  <p>The Membership examination is taken at the end of the second year of surgical training. It used to be an in-person OSCE with real patients or actors. Because the number of candidates has grown, it is now delivered online, on the Entrust and Zoom platforms (see Structure and timing).</p></div></div>

<div class="topic" data-i="2"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i14"/></svg></span><span><b>OSCE principles</b><small>Objective, structured, clinical, examination</small></span><span class="ch"></span></button><div class="pn"><p>You are asked to make expert judgements about professional behaviour. Scenarios are scripted so that one candidate's experience of a station matches another's, and so that the marking is the same whichever examiner delivers it. Tap each card.</p>
  <div class="grid2" id="cards"></div>
  <div class="grid2">
    <div class="cs-card"><h3>Objectivity: what it means for you</h3>
      <ul style="margin:8px 0 0;padding-left:18px"><li>Judge only what you see.</li><li>You may restate or clarify language, but do not prompt in most circumstances.</li><li>Do not assume intent for a behaviour you cannot observe.</li><li>Do not change acceptable answers or the marking scheme to suit your opinion.</li></ul></div>
    <div class="cs-card"><h3>Why structure matters</h3>
      <ul style="margin:8px 0 0;padding-left:18px"><li>You mark one facet of a greater whole. The outcome is the composite judgement of many observers.</li><li>No examiner or station holds a veto.</li><li>The marking scheme is the arbiter of the standard.</li><li>Those who deserve to pass should pass, and those who deserve to fail should fail.</li></ul></div>
  </div>
  <h3>Online OSCE compared with other formats</h3>
  <div class="scroll cs-card"><table>
    <tr><th>Feature</th><th>Online OSCE</th><th>Patient-based OSCE</th><th>Patientless in-person</th><th>Viva voce</th></tr>
    <tr><td>Setting</td><td>Secure online platform</td><td>Examination room or clinical area</td><td>Skills lab</td><td>Quiet room</td></tr>
    <tr><td>Patient</td><td>None: scenarios, images, video</td><td>Real or simulated</td><td>Models, equipment</td><td>None</td></tr>
    <tr><td>Strength</td><td>Flexible, standardised, multimedia</td><td>Realistic communication</td><td>Safe, repeatable skills</td><td>Depth of reasoning</td></tr>
    <tr><td>Limit</td><td>Limited physical examination, technical issues</td><td>Needs trained patients and logistics</td><td>Less realistic</td><td>No practical skills</td></tr>
  </table></div></div></div>

<div class="topic" data-i="3"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i13"/></svg></span><span><b>Structure and timing</b><small>16 stations, 10 minutes each</small></span><span class="ch"></span></button><div class="pn"><p>The 2026 format is shown below. Format can change between cycles, so treat these figures as this cycle's, not a permanent rule.</p>
  <div class="cs-card">
    <div class="lane"><b>ENTRUST</b><div class="chips e"><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i><i>6</i><i>7</i><i>8</i></div></div>
    <div class="lane"><b>ZOOM</b><div class="chips z"><i>1</i><i>2</i><i>3</i><i>4</i><i>5</i><i>6</i><i>7</i><i>8</i></div></div>
    <div class="lane"><b>BREAKS</b><div class="chips k" style="grid-template-columns:repeat(3,minmax(0,1fr))"><i>10 min</i><i>10 min</i><i style="opacity:.55">10 min (some sessions)</i></div></div>
    <div class="stats">
      <div><b>16</b><span>stations per candidate: 8 on Entrust, 8 on Zoom</span></div>
      <div><b>10 min</b><span>per station</span></div>
      <div><b>2 or 3</b><span>10-minute breaks (2 is standard)</span></div>
      <div><b>8</b><span>Zoom stations with examiners, held on the first day</span></div>
    </div>
  </div>
  <div class="grid2">
    <div class="cs-card"><span class="tag ent">ENTRUST · for your information</span><h3>Online case-based stations</h3><p>Candidates work through cases on the Entrust platform. No examiners take part in these stations. You do not need to do anything for them. There are 8 Entrust cases each year. Each case has a 10-minute simulation followed by 5 minutes of multiple-choice questions. They are described here so you know the whole examination.</p></div>
    <div class="cs-card"><span class="tag zm">ZOOM · examiners take part</span><h3>Examiner-led stations</h3><p>Held on the first day. The candidate meets you on Zoom. You read the scenario, ask the scripted questions and mark as you go.</p></div>
  </div>
  <h3>Rules that do not change</h3>
  <ul style="margin:0;padding-left:20px;max-width:66ch"><li>No extra time is given.</li><li>The candidate leaves at the final time signal, mid-sentence if necessary.</li><li>If you finish early, stay silent until the time ends.</li></ul>
  <h3>Your examiner group</h3>
  <div class="stats">
    <div><b>8</b><span>examiners per group</span></div>
    <div><b>Varies</b><span>candidates per group, depending on the total that year</span></div>
    <div><b>AM or PM</b><span>some groups sit in the morning, others in the afternoon</span></div>
  </div>
  <div class="grid2">
    <div class="cs-card"><h3>Moderator</h3><p>Each group has a moderator who runs the examination for that group. Follow the moderator's instructions on timing and rotation.</p></div>
    <div class="cs-card"><h3>WhatsApp group</h3><p>The secretariat creates a WhatsApp group for each examiner group. The secretariat, the ECC Chair and the Panel Head are in it and can help with any problem related to the examination.</p></div>
  </div>
  <h3>Sample rotation: Group A</h3>
<p>This is a sample timetable for one group. The number of candidates in a group changes each year with the total number of candidates; in this sample there are 11 candidates (MCS001 to MCS011), 8 Zoom stations and 3 rest positions. Each row is a time slot and each column is a station or a rest position. Every candidate moves one column to the right in each slot, so every candidate sees every station once and has the rests. With a different group size the table is built the same way.</p>
<div class="callout">Each station is 10 minutes. A new slot starts every 15 minutes, so there are 5 minutes between stations. Your moderator confirms the timetable for your group.</div>
<div class="scroll rot"><table><thead><tr><th>Time</th><th class="">Station 1</th><th class="">Station 2</th><th class="">Station 3</th><th class="rs">REST 1</th><th class="">Station 4</th><th class="">Station 5</th><th class="">Station 6</th><th class="rs">REST 2</th><th class="">Station 7</th><th class="">Station 8</th><th class="rs">REST 3</th></tr></thead><tbody><tr><th>9:00-9:10</th><td class="">MCS001</td><td class="">MCS002</td><td class="">MCS003</td><td class="rs">MCS004</td><td class="">MCS005</td><td class="">MCS006</td><td class="">MCS007</td><td class="rs">MCS008</td><td class="">MCS009</td><td class="">MCS010</td><td class="rs">MCS011</td></tr><tr><th>9:15-9:25</th><td class="">MCS011</td><td class="">MCS001</td><td class="">MCS002</td><td class="rs">MCS003</td><td class="">MCS004</td><td class="">MCS005</td><td class="">MCS006</td><td class="rs">MCS007</td><td class="">MCS008</td><td class="">MCS009</td><td class="rs">MCS010</td></tr><tr><th>9:30-9:40</th><td class="">MCS010</td><td class="">MCS011</td><td class="">MCS001</td><td class="rs">MCS002</td><td class="">MCS003</td><td class="">MCS004</td><td class="">MCS005</td><td class="rs">MCS006</td><td class="">MCS007</td><td class="">MCS008</td><td class="rs">MCS009</td></tr><tr><th>9:45-9:55</th><td class="">MCS009</td><td class="">MCS010</td><td class="">MCS011</td><td class="rs">MCS001</td><td class="">MCS002</td><td class="">MCS003</td><td class="">MCS004</td><td class="rs">MCS005</td><td class="">MCS006</td><td class="">MCS007</td><td class="rs">MCS008</td></tr><tr><th>10:00-10:10</th><td class="">MCS008</td><td class="">MCS009</td><td class="">MCS010</td><td class="rs">MCS011</td><td class="">MCS001</td><td class="">MCS002</td><td class="">MCS003</td><td class="rs">MCS004</td><td class="">MCS005</td><td class="">MCS006</td><td class="rs">MCS007</td></tr><tr><th>10:15-10:25</th><td class="">MCS007</td><td class="">MCS008</td><td class="">MCS009</td><td class="rs">MCS010</td><td class="">MCS011</td><td class="">MCS001</td><td class="">MCS002</td><td class="rs">MCS003</td><td class="">MCS004</td><td class="">MCS005</td><td class="rs">MCS006</td></tr><tr><th>10:30-10:40</th><td class="">MCS006</td><td class="">MCS007</td><td class="">MCS008</td><td class="rs">MCS009</td><td class="">MCS010</td><td class="">MCS011</td><td class="">MCS001</td><td class="rs">MCS002</td><td class="">MCS003</td><td class="">MCS004</td><td class="rs">MCS005</td></tr><tr><th>10:45-10:55</th><td class="">MCS005</td><td class="">MCS006</td><td class="">MCS007</td><td class="rs">MCS008</td><td class="">MCS009</td><td class="">MCS010</td><td class="">MCS011</td><td class="rs">MCS001</td><td class="">MCS002</td><td class="">MCS003</td><td class="rs">MCS004</td></tr><tr><th>11:00-11:10</th><td class="">MCS004</td><td class="">MCS005</td><td class="">MCS006</td><td class="rs">MCS007</td><td class="">MCS008</td><td class="">MCS009</td><td class="">MCS010</td><td class="rs">MCS011</td><td class="">MCS001</td><td class="">MCS002</td><td class="rs">MCS003</td></tr><tr><th>11:15-11:25</th><td class="">MCS003</td><td class="">MCS004</td><td class="">MCS005</td><td class="rs">MCS006</td><td class="">MCS007</td><td class="">MCS008</td><td class="">MCS009</td><td class="rs">MCS010</td><td class="">MCS011</td><td class="">MCS001</td><td class="rs">MCS002</td></tr><tr><th>11:30-11:40</th><td class="">MCS002</td><td class="">MCS003</td><td class="">MCS004</td><td class="rs">MCS005</td><td class="">MCS006</td><td class="">MCS007</td><td class="">MCS008</td><td class="rs">MCS009</td><td class="">MCS010</td><td class="">MCS011</td><td class="rs">MCS001</td></tr></tbody></table></div>
<p class="note">Swipe sideways to see all columns.</p>
<h3>If the connection is lost</h3>
  <ol class="steps">
    <li><div>Tell your moderator in the group's WhatsApp group straight away. Do not keep marking a station that was cut off.</div></li>
    <li><div>If the candidate's internet is lost, the station is <b>repeated at the end of the cycle</b>.</div></li>
    <li><div>If an examiner is lost, a <b>new examiner is invited from the spare list</b>.</div></li>
    <li><div>Note what happened on your marking sheet so the result can be reviewed.</div></li>
  </ol>
  
  <h3>Late examiners</h3>
  <p>Be on time and take breaks only in the scheduled breaks. In the Membership examination a candidate is assessed on the full examination, so only the marking sheet of an examiner present for all of it is submitted. The Membership OSCE can be delivered with one examiner per station. Fellowship examinations use two (Module 6).</p></div></div>

<div class="topic" data-i="4"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i19"/></svg></span><span><b>The online process</b><small>Walk through a Zoom station</small></span><span class="ch"></span></button><div class="pn"><p>Watch a Zoom station from start to finish. This is an animation made for training, not a recording of the real platform.</p>
  <div class="cs-card demo" id="demo">
    <div class="stage" id="stage" data-scene="0" aria-live="polite">
      <div class="zoom">
        <div class="tile ex"><span class="who">Examiner</span><span class="av">E</span></div>
        <div class="tile ca" id="cand"><span class="who">Candidate 07</span><span class="av">C</span><span class="lost">Connection lost</span></div>
        <div class="timer" id="timer">10:00</div>
      </div>
      <div class="side">
        <div class="q1">Q1 shown on screen</div>
        <div class="sheet-m"><b>Marking sheet</b>
          <i class="m m1">Q1 ✓</i><i class="m m2">Q2 ✓</i><i class="m m3">Q3 –</i>
          <i class="gl">Global: Pass</i></div>
        <div class="wa"><b>WhatsApp · Group 3</b><span>Moderator, station 4: candidate 07 lost connection.</span></div>
      </div>
    </div>
    <p id="cap" class="cap"></p>
    <div class="ctl"><button type="button" id="prev">Back</button><button type="button" id="play">Pause</button><button type="button" id="next">Next</button><span id="pos"></span></div>
  </div>
  <figure style="margin:0;max-width:760px"><img src="{{ asset('images/course/be7db846d6.jpg') }}" alt="Entrust virtual patient simulation screen with a patient, vital signs monitor, order buttons and case notes" style="width:100%;height:auto;border-radius:10px;display:block"><figcaption style="font-size:14px;color:var(--muted);margin-top:6px"><span class="tag ent">Entrust · for information</span> Examiners do not use Entrust, so no walkthrough is needed. Candidates work through a virtual patient case, then answer multiple-choice questions.</figcaption></figure>
  
  <h3>Your first time as an online examiner</h3>
  <ol class="steps">
    <li><div><b>Before the day.</b> Test your device, camera, microphone and connection. Use a quiet, private room. Read your station materials and marking criteria.</div></li>
    <li><div><b>Log in early.</b> Join at the time given and check you can see the station and the marking sheet.</div></li>
    <li><div><b>Join your WhatsApp group.</b> Meet your moderator. Confirm your session (morning or afternoon), the breaks and what to do if the connection fails.</div></li>
    <li><div><b>Meet each candidate.</b> Welcome them and check their candidate ID against the rotation. Do not use their name or country.</div></li>
    <li><div><b>Run the station.</b> Read each question as written, mark as you go, keep time, do not prompt.</div></li>
    <li><div><b>Close the station.</b> The candidate leaves at the final signal. Record the global assessment after they have left.</div></li>
    <li><div><b>Submit and move on.</b> Check your marks are saved. Do not discuss the candidate with anyone.</div></li>
  </ol></div></div>

<div class="topic" data-i="5"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i14"/></svg></span><span><b>Station format and marking sheet</b><small>Try the marking sheet</small></span><span class="ch"></span></button><div class="pn"><p>Every station uses one standard format and one marking sheet, so every candidate is judged the same way.</p>
  <div class="scroll cs-card"><table>
    <tr><th>Part of a station</th><th>Contains</th></tr>
    <tr><td>Scenario summary</td><td>Title, syllabus area, content area, station type, domains with marks, source, last updated, years used, props and equipment</td></tr>
    <tr><td>Candidate instructions</td><td>"In this station, you will ..."</td></tr>
    <tr><td>Questions and expected responses</td><td>Numbered questions, the answers that earn marks, marks per question</td></tr>
    <tr><td>Domain ratings</td><td>A mark for each domain assessed</td></tr>
    <tr><td>Global assessment</td><td>Fail, Borderline or Pass</td></tr>
  </table></div>
  <h3>Try a marking sheet</h3>
  <p style="color:var(--muted);font-size:15px">A practice tool with example values. Change the marks to see how the total and the global assessment relate.</p>
  <div class="cs-card sheet">
    <div class="dom"><div><b>Clinical knowledge</b><p>Applied knowledge: understands, synthesises and applies knowledge in a clinical context.</p></div><input id="k" type="number" min="0" max="20" value="9" aria-label="Clinical knowledge marks"></div>
    <div class="dom"><div><b>Professionalism (1 to 4)</b><p>Sound judgement, considers all facts, anticipates, puts patient safety first.</p></div><select id="p" aria-label="Professionalism mark"><option>1</option><option>2</option><option selected>3</option><option>4</option></select></div>
    <div class="dom"><div><b>Communication (1 to 4)</b><p>Conveys what matters clearly, adjusts to the situation, engages in open dialogue.</p></div><select id="c" aria-label="Communication mark"><option>1</option><option>2</option><option selected>3</option><option>4</option></select></div>
    <div class="dom"><div><b>Clinical and technical skills (1 to 4)</b><p>Sound clinical reasoning to a provisional diagnosis; manual dexterity and coordination.</p></div><select id="t" aria-label="Technical skills mark"><option>1</option><option>2</option><option>3</option><option selected>4</option></select></div>
    <div class="dom"><div><b>Global assessment</b><p>Circle one before totalling the domain marks.</p></div>
      <div class="seg" id="g"><button type="button" aria-pressed="false">Fail</button><button type="button" aria-pressed="false">Borderline</button><button type="button" aria-pressed="true">Pass</button></div></div>
    <div class="total"><span>Total for all domains</span><b id="tot">19 / 20</b></div>
    <p id="flag" style="color:var(--muted);font-size:14px"></p>
  </div>
  <div class="grid2">
    <div class="cs-card"><h3>Total domain score</h3><p>Sum of domain marks against predefined criteria, for example 7/20. Compared with the approved pass mark (modified Angoff method) to decide the result.</p></div>
    <div class="cs-card"><h3>Global assessment</h3><p>Your holistic judgement of competence, clinical reasoning, judgement and safety. It supports calibration and quality assurance. It complements the domain score and does not replace it.</p></div>
  </div></div></div>

<div class="topic" data-i="6"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i7"/></svg></span><span><b>Examiner conduct</b><small>Do and do not</small></span><span class="ch"></span></button><div class="pn"><div class="grid2 dd">
    <div class="cs-card do"><h3>Do</h3><ul>
      <li>Welcome the candidate and confirm their ID against the rotation.</li>
      <li>Put the candidate at ease. Speak clearly and slowly in English.</li>
      <li>Move on if the candidate does not know the answer.</li>
      <li>Be early and watch the time signals.</li>
      <li>Finish all questions and control the time.</li>
      <li>Mark each question as you go, out of view.</li>
      <li>Record the global assessment after the candidate has left.</li></ul></div>
    <div class="cs-card dont"><h3>Do not</h3><ul>
      <li>Use the candidate's name or country.</li>
      <li>Take a candidate out of order or schedule.</li>
      <li>Give feedback, verbal or visual. Read the question as written.</li>
      <li>Let the candidate waste time.</li>
      <li>Discuss a candidate's performance or compare marks, even after they leave.</li></ul></div>
  </div>
  </div></div>

</div>
<div class="done" id="done"><h2>You have read all 7 topics of Module 5.</h2><p>Use the course menu to move to the next module or the quiz module.</p></div>
</div>
@endsection
