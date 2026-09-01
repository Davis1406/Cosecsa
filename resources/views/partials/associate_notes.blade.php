{{--
    Reusable "Notes" card — a running, dated log of free-text notes with an
    optional single attached PDF/image per note (e.g. a deferral note with
    the scanned deferral letter attached), usable on any associate profile
    page. Attachments open in a new tab, where the browser previews the
    PDF/image natively — same pattern as the examiner "Additional Documents"
    list. Styled with the COSECSA brand palette (maroon #a02626 / gold
    #FEC503) — see the "Associate Notes card" block in public/dist/css/custom.css.

    Include with:
    @include('partials.associate_notes', [
        'associateType' => 'fellow',   // must match a key in the API's AssociateNoteController::TYPES
        'associateId'   => $fellow->id,
        'notes'         => $notes,
    ])
--}}
<div class="assoc-notes mb-3">
    <div class="assoc-notes-header">
        <i class="fas fa-sticky-note mr-2"></i>
        Notes
        <span class="assoc-notes-count">{{ count($notes) }}</span>
    </div>
    <div class="assoc-notes-body">
        @if(count($notes))
            <div class="assoc-notes-list">
                @foreach($notes as $note)
                    @php
                        $noteIcon = match(strtolower($note->file_type ?? '')) {
                            'pdf' => 'fas fa-file-pdf',
                            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'fas fa-file-image',
                            default => null,
                        };
                    @endphp
                    <div class="assoc-note">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="assoc-note-body">{{ $note->note }}</div>
                            <form method="POST"
                                  action="{{ route($associateType . '.notes.destroy', $note->id) }}"
                                  onsubmit="return confirm('Delete this note?')"
                                  class="ml-2" style="flex-shrink:0;">
                                @csrf
                                <input type="hidden" name="back" value="{{ url()->full() }}">
                                <button type="submit" class="btn btn-xs btn-outline-secondary assoc-note-delete" title="Delete note">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        @if($note->file_path)
                            <a href="{{ \App\Support\ApiAsset::url($note->file_path) }}"
                               target="_blank" rel="noopener" class="assoc-note-attachment">
                                @if($noteIcon)<i class="{{ $noteIcon }}"></i>@endif
                                <span>{{ $note->original_name ?? 'Attachment' }}</span>
                            </a>
                        @endif
                        <div class="assoc-note-meta">
                            {{ $note->created_by_name ?? 'Staff' }} ·
                            {{ \Carbon\Carbon::parse($note->created_at)->format('d M Y, H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="assoc-notes-empty">
                <i class="far fa-clipboard mb-1" style="font-size:1.3rem;display:block;"></i>
                No notes yet.
            </div>
        @endif

        <form method="POST"
              action="{{ route($associateType . '.notes.store', $associateId) }}"
              enctype="multipart/form-data" class="assoc-notes-form">
            @csrf
            <input type="hidden" name="back" value="{{ url()->full() }}">
            <div class="form-group mb-2">
                <textarea name="note" class="form-control form-control-sm" rows="2"
                          placeholder="Add a note (e.g. deferral reason)…" required></textarea>
            </div>
            <div class="form-group mb-2">
                <input type="file" name="attachment" class="form-control-file form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                <small class="form-text">Optional — attach a PDF or image (e.g. a deferral letter), max 10MB.</small>
            </div>
            <button type="submit" class="btn btn-sm btn-assoc-add">
                <i class="fas fa-plus mr-1"></i> Add Note
            </button>
        </form>
    </div>
</div>
