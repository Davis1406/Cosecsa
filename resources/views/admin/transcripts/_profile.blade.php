{{-- Transcript tab on a fellow's profile (TranscriptController::profile). --}}
@php
    $latest = $shares->first();
    $when = fn ($at) => \Illuminate\Support\Carbon::parse($at)->format('d M Y, H:i');
    $canDelete = Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('transcripts.manage');
@endphp

<div class="d-flex justify-content-between align-items-center flex-wrap mb-2" style="gap:.5rem;">
    <p class="sect-div mb-0">Transcript</p>
    <div>
        @if($latest)
            <a href="{{ url('admin/transcripts/copy/'.$userId) }}" target="_blank" class="btn btn-sm btn-cosecsa-yellow">
                <i class="fas fa-file-pdf mr-1"></i> Open PDF
            </a>
        @endif
        <a href="{{ url('admin/transcripts/edit/'.$userId) }}" class="btn btn-sm btn-cosecsa">
            <i class="fas fa-file-signature mr-1"></i> {{ $record ? 'Edit / Share Again' : 'Issue Transcript' }}
        </a>
        @if($record && $canDelete)
            <form method="POST" action="{{ url('admin/transcripts/delete/'.$userId) }}" class="d-inline"
                  onsubmit="return confirm('Delete this transcript, including every shared copy kept here? Emails already sent are not affected.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-cosecsa-danger"><i class="fas fa-trash mr-1"></i> Delete Transcript</button>
            </form>
        @endif
    </div>
</div>

@if($latest)
    <p class="text-muted mb-2" style="font-size:.85rem;">
        Shared with <strong>{{ $latest['sent_to'] }}</strong> on {{ $when($latest['at']) }}{{ $latest['by_name'] ? ' by '.$latest['by_name'] : '' }}.
        This is the copy that was emailed.
    </p>
    <iframe src="{{ url('admin/transcripts/copy/'.$userId) }}" title="Shared transcript"
            style="width:100%; height:640px; border:1px solid #e0e9f2; border-radius:6px;"></iframe>

    <p class="sect-div mt-3">Share History</p>
    <table class="table table-sm table-hover mb-0" style="font-size:.85rem;">
        <thead>
            <tr><th style="width:36px;">#</th><th>Shared</th><th>Sent to</th><th>By</th><th class="text-right">Copy</th></tr>
        </thead>
        <tbody>
            @foreach($shares as $s)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $when($s['at']) }}</td>
                    <td>{{ $s['sent_to'] }}</td>
                    <td>{{ $s['by_name'] ?? '—' }}</td>
                    <td class="text-right" style="white-space:nowrap;">
                        <a href="{{ url('admin/transcripts/copy/'.$userId.'?dispatch='.$s['dispatch_id']) }}" target="_blank" class="btn btn-sm btn-cosecsa-yellow">
                            <i class="fas fa-file-pdf mr-1"></i> View
                        </a>
                        @if($canDelete)
                            <form method="POST" action="{{ url('admin/transcripts/delete/'.$userId.'/share/'.$s['dispatch_id']) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this shared transcript and its saved copy? The fellow keeps the email they received.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-cosecsa-danger" title="Delete this share"><i class="fas fa-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@elseif($record)
    <p class="text-muted mb-0" style="font-size:.85rem;">
        A transcript was saved on {{ $when($record['updated_at']) }} but has not been shared with the fellow yet.
        <a href="{{ url('admin/transcripts/pdf/'.$userId) }}" target="_blank" class="btn btn-sm btn-cosecsa-yellow ml-1">
            <i class="fas fa-file-pdf mr-1"></i> Preview saved version
        </a>
    </p>
@else
    <p class="text-muted mb-0" style="font-size:.85rem;">No transcript has been issued for this fellow yet.</p>
@endif
