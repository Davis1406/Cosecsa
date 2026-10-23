@extends('course.layout', ['mod'=>'8','slug'=>'module-8-portal','key'=>'m8-seen','lite'=>false,'moduleJs'=>true])

@section('title', 'Module 8 Using the Marking Portal')

@php
// Each topic: [icon, title, subtitle, screen, highlighted field, screen option, url shown in the browser bar, blocks (html)]
$steps = [
 ['i1', 'Choose your role', 'Everyone · the role page', 'role', 'role', '', 'cosecsamis.org',
  ['<p>Open cosecsamis.org and choose <b>Examiner</b> under Select Role, then click <b>Continue</b>.</p>',
   '<ul class="pts"><li>Wrong role? <b>Reset Role</b> takes you back to start again.</li></ul>']],
 ['i4', 'Sign in', 'Everyone · email or username', 'login', 'user', '', 'cosecsamis.org/login',
  ['<p>Type the email or username COSECSA registered for you, then your password. The eye icon shows or hides what you typed. Click <b>Login</b> to open your Examiner Portal.</p>',
   '<ul class="pts"><li>Forgot your password? Use <b>Forget Password</b> to reset it.</li><li><b>Remember Me</b> keeps you signed in on this device.</li></ul>']],
 ['i5', 'Choose MCS or FCS', 'Everyone · the dashboard', 'dash', 'exam', 'mcs', 'cosecsamis.org/examiner/dashboard',
  ['<p>After you sign in, the Examiner Portal asks which exam you are marking. Open <b>Select Exam Type</b> and pick <b>MCS</b> or your <b>FCS</b> specialty, then click <b>Proceed</b>.</p>',
   '<div class="callout">MCS is marked online. FCS specialties have a Clinical and a Viva form, and can be marked offline.</div>']],
 ['i6', 'MCS: enter marks and grade', 'MCS only · the marking form', 'mcsForm', 'marks', '', 'cosecsamis.org/examiner/examiner_form',
  ['<p>Choose the group first so its candidates load, then the candidate and the station. Type the mark for each question and use <b>+ Add Question</b> for more.</p>',
   '<ul class="pts"><li>The total for a station cannot exceed 20. Half marks such as 3.5 are allowed.</li><li><b>Overall Marks</b> is calculated for you.</li><li>Choose <b>Pass</b>, <b>Borderline</b> or <b>Fail</b> in Grade, add remarks if needed, then press <b>SUBMIT</b>.</li></ul>']],
 ['i7', 'MCS: check your results', 'MCS only · the results page', 'results', 'tbl', 'mcs', 'cosecsamis.org/examiner/results',
  ['<p>Every MCS submission is listed in <b>MCS Results</b> with its question marks, Total Marks and Overall Grade. <b>View</b> opens the saved marks.</p>']],
 ['i8', 'MCS: resubmit a mark', 'MCS only · correcting a mark', 'mcsForm', 'marks', 'resubmit', 'cosecsamis.org/examiner/resubmit/…',
  ['<p>From Results click <b>Resubmit</b>. Group, candidate and station are read-only. Change the mark that is wrong, check the Grade, then press <b>Submit</b>.</p>',
   '<div class="callout">Resubmit updates the existing record. It does not create a duplicate.</div>']],
 ['i1', 'FCS: choose Clinical or Viva', 'FCS only · the examination format', 'fmt', 'clin', '', 'cosecsamis.org/examiner/urology',
  ['<p><b>Clinical</b> (red) is for evaluating clinical cases at your station. <b>Viva</b> (gold) is for the oral examination. <b>Back to Dashboard</b> returns you to the exam list.</p>']],
 ['i4', 'FCS: select the candidate and enter marks', 'FCS only · the evaluation form', 'fcsForm', 'marks', '', 'cosecsamis.org/examiner/urology/clinical',
  ['<p>Choose the group first so its candidates load, then the candidate and the station (1 to 8). Choose <b>2, 4, 6, 8 or 10</b> for every question. Some Viva forms also offer 0.</p>',
   '<ul class="pts"><li><b>+ Add Question</b> adds the next question where the form allows it.</li><li>The case heading follows the station you pick.</li></ul>']],
 ['i5', 'FCS: check the total and submit', 'FCS only · remarks and SUBMIT', 'fcsForm', 'total', '', 'cosecsamis.org/examiner/urology/clinical',
  ['<p><b>Overall Marks</b> is calculated from your marks and cannot be typed. Add <b>Examiner Remarks</b> if needed, then press <b>SUBMIT</b>. A green success message confirms it.</p>']],
 ['i6', 'FCS: check your results', 'FCS only · the results page', 'results', 'tbl', 'fcs', 'cosecsamis.org/examiner/results',
  ['<p>Each submission shows its Clinical, Viva and Overall totals in <b>FCS Specialty Results</b>. <b>View</b> opens the saved marks.</p>',
   '<ul class="pts"><li>A <b>Resubmit Viva</b> button appears only for marks you submitted.</li></ul>']],
 ['i7', 'FCS: resubmit a mark', 'FCS only · Update Evaluation', 'fcsForm', 'marks', 'resubmit', 'cosecsamis.org/examiner/fcs-resubmit/…/clinical',
  ['<p>From Results click <b>Resubmit Clinical</b> or <b>Resubmit Viva</b>. Group, candidate and station are read-only. Pick the correct value, check Overall Marks, then press <b>Update Evaluation</b> (or Cancel).</p>']],
 ['i8', 'Get ready to mark offline', 'FCS · before the exam', 'offReady', 'e3', '', 'cosecsamis.org/examiner/dashboard',
  ['<p>Open the dashboard while you have internet. The card under the welcome text shows <b>Not prepared</b>, then <b>Preparing</b>, then <b>Ready</b>. Green means you can keep marking if the connection drops.</p>',
   '<div class="callout">Use the same browser and device for marking.</div>']],
 ['i1', 'Submit with no connection', 'FCS · marking offline', 'offSaved', 'toast', '', 'cosecsamis.org/examiner/urology/clinical',
  ['<p>Fill in the form and press <b>SUBMIT</b> even with no internet. A green message says the mark is saved on this device and the form clears for the next candidate. The pill shows how many marks are waiting.</p>']],
 ['i4', 'Back online: sync your marks', 'FCS · after marking offline', 'offSync', 'sync', '', 'cosecsamis.org/examiner/results',
  ['<p>When you have a connection the marks submit by themselves. You can also open the pill and press <b>Sync now</b>. <b>1 mark synced.</b> confirms it reached the server.</p>',
   '<ul class="pts"><li>Do not clear browser data or switch device until the list is empty.</li><li>If your session ended, sign in again while online.</li></ul>']],
];
@endphp

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span></div>
<div class="hero">
<svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round"><rect x="24" y="36" width="152" height="108" rx="10"/><path d="M24 62h152M70 170h60M100 144v26"/></svg>
<div class="k">Module 8 · Using the marking portal</div>
<h1>Find your way around the marking portal</h1>
<p class="l">A step-by-step tour of signing in, marking MCS and FCS exams, correcting a mark and marking offline.</p>
<div class="prog"><span id="pc">0 of {{ count($steps) }} topics read</span><span>Saved on this device</span></div>
<div class="bar"><i id="fill"></i></div>
</div>
<div class="topics" id="topics">
@foreach($steps as $i => [$icon, $title, $sub, $screen, $hl, $opt, $url, $blocks])

<div class="topic" data-i="{{ $i }}"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#{{ $icon }}"/></svg></span><span><b>{{ $title }}</b><small>{{ $sub }}</small></span><span class="ch"></span></button><div class="pn">
<div class="wt" data-screen="{{ $screen }}" data-hl="{{ $hl }}" data-opt="{{ $opt }}" data-url="{{ $url }}" role="img" aria-label="Replica of the marking portal screen with the relevant part highlighted"></div>
@foreach($blocks as $b)
{!! $b !!}
@endforeach
</div></div>
@endforeach

</div>
<div class="done" id="done"><h2>You have read all {{ count($steps) }} topics of Module 8.</h2><p>Use the course menu to move to the quiz.</p>
<div class="label" style="color:var(--good)">Remember</div>
<ol><li>Choose your role, then Examiner, then your exam.</li><li>Check the group, candidate and station before you submit.</li><li>Overall Marks is calculated for you.</li><li>Use Resubmit from Results to correct a mark.</li><li>Marks saved offline sync when you are back online.</li></ol></div>
</div>
@endsection
