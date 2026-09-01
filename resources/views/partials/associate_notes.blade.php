{{--
    Reusable "Notes" card — a running, dated log of free-text notes with an
    optional single attached PDF/image per note (e.g. a deferral note with
    the scanned deferral letter attached), usable on any associate profile
    page. Attachments open in a new tab, where the browser previews the
    PDF/image natively — same pattern as the examiner "Additional Documents"
    list.

    Include with:
    @include('partials.associate_notes', [
        'associateType' => 'fellow',   // must match a key in the API's AssociateNoteController::TYPES
        'associateId'   => $fellow->id,
        'notes'         => $notes,
    ])
--}}
<div class="card mb-3">
    <div class="card-header py-2" style="background:#f8f8f8;">
        <i class="fas fa-sticky-note mr-1"></i>
        <strong>Notes</strong>
        <span class="badge badge-secondary ml-1" style="font-size:.65rem;">{{ count($notes) }}</span>
    </div>
    <div class="card-body">
        @if(count($notes))
            <div class="mb-3" style="max-height:320px;overflow-y:auto;">
                @foreach($notes as $note)
                    @php
                        $noteIcon = match(strtolower($note->file_type ?? '')) {
                            'pdf' => 'fas fa-file-pdf text-danger',
                            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fas fa-file-image text-info',
                            default => null,
                        };
                    @endphp
                    <div class="border rounded p-2 mb-2" style="font-size:.85rem;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div style="white-space:pre-wrap;">{{ $note->note }}</div>
                            <form method="POST"
                                  action="{{ route($associateType . '.notes.destroy', $note->id) }}"
                                  onsubmit="return confirm('Delete this note?')"
                                  class="ml-2" style="flex-shrink:0;">
                                @csrf
                                <input type="hidden" name="back" value="{{ url()->full() }}">
                                <button type="submit" class="btn btn-xs btn-outline-danger"
                                        style="padding:1px 6px;font-size:.7rem;" title="Delete note">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        @if($note->file_path)
                            <div class="mt-1">
                                <a href="{{ \App\Support\ApiAsset::url($note->file_path) }}" target="_blank" rel="noopener">
                                    @if($noteIcon)<i class="{{ $noteIcon }} mr-1"></i>@endif
                                    {{ $note->original_name ?? 'Attachment' }}
                                </a>
                            </div>
                        @endif
                        <div class="text-muted mt-1" style="font-size:.72rem;">
                            {{ $note->created_by_name ?? 'Staff' }} ·
                            {{ \Carbon\Carbon::parse($note->created_at)->format('d M Y, H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-3" style="font-size:.85rem;">No notes yet.</p>
        @endif

        <form method="POST"
              action="{{ route($associateType . '.notes.store', $associateId) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="back" value="{{ url()->full() }}">
            <div class="form-group mb-2">
                <textarea name="note" class="form-control form-control-sm" rows="2"
                          placeholder="Add a note (e.g. deferral reason)…" required></textarea>
            </div>
            <div class="form-group mb-2">
                <input type="file" name="attachment" class="form-control-file form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                <small class="text-muted">Optional — attach a PDF or image (e.g. a deferral letter), max 10MB.</small>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-plus mr-1"></i> Add Note
            </button>
        </form>
    </div>
</div>
