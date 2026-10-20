@extends('course.layout', ['mod'=>'ack','slug'=>'acknowledgements','key'=>'ack-seen','lite'=>false,'moduleJs'=>false])

@section('title', 'Acknowledgements')

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span><button id="theme" type="button">Light / dark</button></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"><path d="M100 170s-60-36-72-72a38 38 0 0 1 72-24 38 38 0 0 1 72 24c-12 36-72 72-72 72z"/></svg><div class="k">Acknowledgements</div><h1>Thank you</h1><p class="l">This course is the work of examiners, partners and friends of COSECSA from many countries.</p>
<div class="prog"><span id="pc">0 of 4 topics read</span><span>Saved on this device</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">

<div class="topic" data-i="0"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i18"/></svg></span><span><b>Course leadership</b><small>With particular thanks</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">The development of this course was truly an international collaboration, reflecting COSECSA's global diaspora of Examiners and partners.</p>
<p>COSECSA would like to thank particularly:</p>
<ul class="pts"><li><b>Prof. Bruce Steffes</b>, former Panel Head of the COSECSA Membership Training Programme</li><li><b>Prof Abebe Bekele</b>, former Chair of ECC</li><li><b>Dr Hanna Getachew</b>, Chair, Examinations and Credentials Committee, COSECSA</li></ul></div></div>

<div class="topic" data-i="1"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i10"/></svg></span><span><b>Royal College of Surgeons in Ireland</b><small>Advice and guidance</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">Prof Peter Gillen, Dr Dara Cassidy and Ms Michelle Scott at RSCI, for their advice.</p></div></div>

<div class="topic" data-i="2"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i4"/></svg></span><span><b>The animation contributors</b><small>Ten examiners from six countries</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">For their valuable contributions to the development of the animations used throughout the course:</p>
<ul class="pts"><li><b>Dr Agneta Odera</b>, Kenya</li><li><b>Dr Augustine Mapfumo</b>, Zimbabwe</li><li><b>Prof Hillary Sanfey</b>, USA</li><li><b>Dr John Tarpley</b>, Botswana</li><li><b>Dr Mbonisi Malaba</b>, Kenya</li><li><b>Dr Michael Mwachiro</b>, Kenya</li><li><b>Prof Paul Goldberg</b>, South Africa</li><li><b>Dr Peter Nthumba</b>, Kenya</li><li><b>Dr Petronilla Ngiloi</b>, Tanzania</li><li><b>Dr Rose Alenyo</b>, Uganda</li></ul></div></div>

<div class="topic" data-i="3"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#i16"/></svg></span><span><b>With gratitude</b><small>From all of us at COSECSA</small></span><span class="ch"></span></button><div class="pn"><p class="cs-lead">All of the above gave their time and expertise freely to COSECSA in this project and it is greatly appreciated.</p></div></div>

</div>
<div class="done" id="done"><h2>You have read all 4 sections.</h2><p>Use the course menu to finish the course.</p></div>
</div>
@endsection
