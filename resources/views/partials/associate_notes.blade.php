{{--
    Reusable "Notes" section — a running, dated log of free-text notes with
    an optional single attached PDF/image per note (e.g. a deferral note
    with the scanned deferral letter attached), usable on any associate
    profile page. Adding a note happens in a modal (matching the page's
    other action modals — Delete, Candidate Results, etc.) rather than a
    permanent inline form. Attachments open in a new tab, where the browser
    previews the PDF/image natively. Styled with the COSECSA brand palette
    (maroon #a02626 / gold #FEC503) — see the "Associate Notes card" block
    in public/dist/css/custom.css.

    Include with:
    @include('partials.associate_notes', [
        'associateType' => 'fellow',   // must match a key in the API's AssociateNoteController::TYPES
        'associateId'   => $fellow->id,
        'notes'         => $notes,
    ])
--}}
@php $assocModalId = 'assocNoteModal_' . $associateType . '_' . $associateId; @endphp

<div class="assoc-notes mb-3">
    <div class="assoc-notes-header">
        <i class="fas fa-sticky-note mr-2"></i>
        Notes
        <span class="assoc-notes-count">{{ count($notes) }}</span>
        <button type="button" class="btn btn-sm assoc-notes-add-btn ml-auto" data-toggle="modal" data-target="#{{ $assocModalId }}">
            <i class="fas fa-plus mr-1"></i> Add Note
        </button>
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
    </div>
</div>

{{-- ── Add Note modal ──────────────────────────────────────────────────── --}}
<div class="modal fade" id="{{ $assocModalId }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST"
              action="{{ route($associateType . '.notes.store', $associateId) }}"
              enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header assoc-modal-header">
                    <h5 class="modal-title"><i class="fas fa-sticky-note mr-2"></i>Add Note</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body assoc-notes-form">
                    @csrf
                    <input type="hidden" name="back" value="{{ url()->full() }}">
                    <div class="form-group mb-2">
                        <label class="font-weight-bold mb-1" style="font-size:.82rem;">Note</label>
                        <textarea name="note" class="form-control" rows="3"
                                  placeholder="e.g. Deferred exam sitting — see attached letter…" required></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold mb-1" style="font-size:.82rem;">Attachment</label>
                        <input type="file" name="attachment" class="form-control-file"
                               accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">
                        <small class="form-text">Optional — PDF or image (e.g. a deferral letter), max 10MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-assoc-add">
                        <i class="fas fa-check mr-1"></i> Save Note
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
