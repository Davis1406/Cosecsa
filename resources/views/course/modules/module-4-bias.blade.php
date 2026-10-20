@extends('course.layout', ['mod'=>'4','slug'=>'module-4-bias','key'=>'m4-seen','lite'=>false,'moduleJs'=>false])

@section('title', 'Module 4 Bias and Unconscious Bias in Examinations')

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span><button id="theme" type="button">Light / dark</button></div>
<div class="hero">
<svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6"><circle cx="90" cy="90" r="70"/><circle cx="90" cy="90" r="42"/><circle cx="90" cy="90" r="14" fill="#fff"/><path d="M142 142l46 46" stroke-linecap="round"/></svg>
<div class="k">Module 4 · Fair assessment</div>
<h1>Bias and Unconscious Bias in Examinations</h1>
<p class="l">Judge the performance, not the person. Bias, especially unconscious bias, can quietly shape how you treat candidates and score them. This module shows where it comes from and how to keep it out of the exam room.</p>
<div class="prog"><span id="pc">0 of 9 topics read</span><span>Saved on this device</span></div>
<div class="bar"><i id="fill"></i></div>
</div>

<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i1"/></svg></span><span><b>What is bias?</b><small>And how it shows up in an exam</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">As an examiner, you are expected to assess every candidate fairly, objectively and consistently. Bias is a tendency to judge or respond to a person or situation in a way that may not be based on objective evidence.</p>
  <img class="pic" alt="A diverse group of people standing together" src="{{ asset('images/course/ab93dd4b5b.gif') }}">
  <div class="quote"><p>We see the world, not as it is, but as we are, or as we are conditioned to see it.</p><cite>Stephen Covey, author and businessman</cite></div>
  <p>Biases come in many forms. They can be linked with stereotyping, prejudice or bigotry against an individual, a race, a religion, a culture or a social class. They are formed by our socialization, our personal experience and our exposure to other cultures and points of view.</p>
  <div><div class="label" style="margin-bottom:8px">In an exam, bias could influence</div>
  <ul class="pts"><li>how encouraging or discouraging your body language is</li><li>how attentively you listen to different candidates</li><li>your tone of voice or eye contact</li><li>how readily you accept or challenge an answer</li><li>your overall impression of a candidate</li></ul></div>
</div></div>

<div class="topic" data-i="1"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i2b"/></svg></span><span><b>Unconscious bias</b><small>Assumptions you do not know you are making</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">Unconscious bias is an automatic assumption or preference that can influence your judgement without you being aware of it.</p>
  <p>You may feel more positive towards a candidate who shares your university, professional background, language or culture. Another candidate may get a less favourable impression for no good reason.</p>
  <div class="callout">The candidate's performance, not your impression of the candidate, must determine the assessment.</div>
  <p>Everyone has some unconscious bias. It does not usually come from bad intent. Because we are often unaware of it, it can be harder to combat than the biases we know we hold, and it can push us to act in a positive or a negative way towards others.</p>
  <p>Research from the neuro, social and cognitive sciences shows that unconscious biases are pervasive, operate largely outside awareness, and affect how we see and treat others even when we are determined to be fair and objective.</p>
  <img class="pic cover" alt="Cover of the Implicit Bias Review 2016, Kirwan Institute" src="{{ asset('images/course/212f339a35.jpg') }}">
  <div><div class="label" style="margin-bottom:8px">Common forms of bias</div>
  <div class="chips"><div class="chip"><b>Gender bias</b><span>Judging by gender</span></div><div class="chip"><b>Affinity bias</b><span>Favouring people like you</span></div><div class="chip"><b>Confirmation bias</b><span>Seeing what backs your first impression</span></div><div class="chip"><b>First-impression bias</b><span>Appearance sets the tone</span></div><div class="chip"><b>Ambiguity bias</b><span>Marking down what is unfamiliar</span></div><div class="chip"><b>Comparison bias</b><span>Judging against the last candidate</span></div></div></div>
</div></div>

<div class="topic" data-i="2"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i3"/></svg></span><span><b>Gender bias</b><small>Watch the short animation</small></span><span class="ch"></span></button><div class="pn">
  <div class="video poster" style="background-image:url({{ asset('images/course/6cb433d10e.jpg') }})"><div class="play"></div><small>Video: Gender bias example</small></div>
  <p>The animation is an extreme example of an examiner letting biased thinking about gender affect the interaction with a candidate. His facial expressions and tone of voice become discouraging, and he disregards the candidate's answers.</p>
  <div class="callout">Avoid all biased thinking. Assess the candidate on objective performance in the exam only.</div>
  <ul class="pts"><li>We are more open to biased thinking when we are tired or under time pressure.</li><li>COSECSA exams can take several hours, so stay mindful that you do not slip into biased thinking.</li><li>Look out for your fellow examiner, and make sure you both take the breaks in the schedule.</li></ul>
</div></div>

<div class="topic" data-i="3"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i4"/></svg></span><span><b>Affinity bias</b><small>The "mini me" effect</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">We like people who are like us: the same religion, tribe, city or university. Conversation is easier and we treat them more favourably. It is natural, but it leads to assessments that are not objective.</p>
  <img class="pic" alt="A hand pushes away one green fish while orange fish stay together" src="{{ asset('images/course/e1455ec6af.jpg') }}">
  <div class="video"><div class="play"></div><small>Video: Example of affinity bias</small></div>
  <p>The animation shows an extreme case: a candidate dresses to create a positive impression and improve his chances. In the COSECSA exam, candidates are asked to dress neutrally and not to wear hospital, university or college crests.</p>
  <p>Bias can work in a candidate's favour as well as against them. You may share a university, tribe, city or religion with a candidate. Do not let that count in the marks.</p>
  <div class="callout">Tired or under pressure? You are more open to bias. Take your breaks and look out for your fellow examiner.</div>
</div></div>

<div class="topic" data-i="4"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i5"/></svg></span><span><b>Confirmation bias</b><small>Seeing what you expect to see</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">Confirmation bias is interpreting information in a way that supports an initial impression.</p>
  <div class="video poster" style="background-image:url({{ asset('images/course/38943076b3.jpg') }})"><div class="play"></div><small>Video: Example of confirmation bias</small></div>
  <p>We often reinforce our own biases by seeing agreement with our thinking where there is none. Stay mindful of this as an examiner.</p>
  <div class="callout">It is unprofessional to discuss candidates' performances at any time.</div>
</div></div>

<div class="topic" data-i="5"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i8"/></svg></span><span><b>Ambiguity bias</b><small>Marking down what you do not recognise</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">Ambiguity bias is a pull towards the familiar and the certain. An answer that is unusual, unexpected or not fully clear is harder to judge, so it can get a lower mark even when it is correct.</p>
  <img class="pic" alt="The word UNKNOWN with UN crossed out, becoming KNOWN" src="{{ asset('images/course/fb9e5aaba9.jpg') }}">
  <ul class="pts"><li>Do not assume an unfamiliar answer is a wrong answer.</li><li>If you are unsure, ask the candidate to clarify, using neutral words and without giving clues.</li><li>A reasonable alternative answer should be discussed with the other examiner or the examination lead when necessary.</li></ul>
  <div class="callout">When an answer is not what you expected, check it against the marking criteria, not against what you are used to.</div>
</div></div>

<div class="topic" data-i="6"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i9"/></svg></span><span><b>Comparison bias</b><small>Judging against the last candidate</small></span><span class="ch"></span></button><div class="pn">
  <p class="cs-lead">Comparison bias is judging a candidate against the one before, instead of against the standard. After a weak candidate an average one looks strong. After a very strong one the next looks poor.</p>
  <img class="pic" alt="One smiling face lifted among happy faces while others are pushed down among unhappy faces" src="{{ asset('images/course/6edc93a5aa.jpg') }}">
  <div class="callout">Compare every candidate with the marking criteria only, never with other candidates.</div>
</div></div>

<div class="topic" data-i="7"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i6"/></svg></span><span><b>When bias creeps in</b><small>Tiredness, pressure and how to guard against them</small></span><span class="ch"></span></button><div class="pn">
  <p>Being biased is part of being human. Challenging bias is hard because it does not feel wrong; it often feels right. If we are mindful of our decisions we can control them better, but control weakens when we are:</p>
  <div class="tired"><span>Stressed</span><span>Rushing</span><span>Angry or upset</span><span>Physically tired</span><span>Relying on habit</span></div>
  <p>These conditions can occur even in the best and most objective processes.</p>
  <div class="callout">COSECSA exams run to a schedule with allotted breaks to guard against tiredness. They can still be tiring for everyone involved, so take every break.</div>
</div></div>

<div class="topic" data-i="8"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i7"/></svg></span><span><b>Do's and don'ts</b><small>Fourteen reminders for every exam</small></span><span class="ch"></span></button><div class="pn">
  <p>You are required to be mindful of your decision-making and how you deliver the exam, so that it is fair for all candidates.</p>
    <div class="dd">
   <div class="cs-col do"><h3>Do</h3>
    <p class="it">Apply the same objective criteria to every candidate.</p>
    <p class="it">Focus on what the candidate demonstrates during the examination.</p>
    <p class="it">Use the agreed questions, marking criteria and examination format.</p>
    <p class="it">Be aware of how your body language, tone and interaction may affect candidates.</p>
    <p class="it">Clarify questions appropriately when language affects comprehension, without giving clues.</p>
    <p class="it">Arrive prepared and on time to avoid unnecessary time pressure.</p>
    <p class="it">Take the scheduled breaks, particularly as the examination day progresses.</p>
    <p class="it">Look out for signs of fatigue or bias in yourself and your fellow examiner.</p>
   </div>
   <div class="cs-col dont"><h3>Don't</h3>
    <p class="it">Allow personal knowledge, appearance, background or characteristics to influence your assessment.</p>
    <p class="it">Compare candidates with each other when making individual assessment decisions.</p>
    <p class="it">Discuss candidates with other examiners before they have been independently assessed.</p>
    <p class="it">Discuss candidates during breaks in a way that could influence another examiner's judgement.</p>
    <p class="it">Allow your first impression of a candidate to influence subsequent scoring.</p>
    <p class="it">Let fatigue, pressure or distractions affect your assessment.</p>
   </div>
  </div>
</div></div>
</div>

<div class="done" id="done"><h2>You have read all 9 topics of Module 4.</h2>
<p>Use the course menu to move to the next module or the quiz module.</p>
<div class="label" style="color:var(--good)">Remember</div>
<ol><li>Judge the performance, not the person.</li><li>Everyone has bias. Notice yours.</li><li>Tiredness and time pressure make bias worse. Take your breaks.</li><li>Do not discuss candidates until each has been assessed independently.</li></ol></div>
@endsection
