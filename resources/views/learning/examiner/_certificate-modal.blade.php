{{-- Modal body shown when an examiner finishes the course. Filled in by the player page (data-certificate). --}}
<div class="lmscert-modal">
    <button type="button" class="close lmscert-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <div class="lmscert-congrats">
        <div class="lmscert-congrats-icon" aria-hidden="true">🎉</div>
        <h4>Congratulations{{ $firstName ? ', '.$firstName : '' }}!</h4>
        <p>{{ ($demo ?? false) ? 'This is the demo of what examiners see when they finish the '.$course['title'].' course.' : 'You have completed the '.$course['title'].' course. Here is your certificate.' }}</p>
        @if($demo ?? false)<span class="badge badge-warning">Demo — nothing is recorded</span>@endif
    </div>

    @include('learning.partials.certificate', ['cert' => $cert, 'name' => $name, 'signature' => $signature, 'logo' => asset('dist/img/Cosecsa_Logo.png'), 'fitTop' => 250, 'plain' => true])

    <div class="lmscert-actions">
        <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
        <button type="button" class="btn btn-primary" onclick="downloadCertificatePng(this, 'COSECSA-Examiner-Training-Certificate-{{ \Illuminate\Support\Str::slug($name) }}.png')">Download PNG</button>
        @if($demo ?? false)
            <button type="button" class="btn btn-light" data-dismiss="modal">Close demo</button>
        @else
            <a href="{{ route('examiner.learning') }}" class="btn btn-light">Back to course home</a>
        @endif
    </div>
</div>
