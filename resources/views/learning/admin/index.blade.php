@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')
            @php $quizModule = collect($modules)->firstWhere('type', 'quiz'); @endphp

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="card card-pad mb-3 mb-md-0">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Confirmed examiners</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($learners) }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-pad">
                        <div class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase;">Course modules</div>
                        <div style="font-size:30px; font-weight:800;">{{ count($modules) }}</div>
                    </div>
                </div>
            </div>

            <div class="card card-pad">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:16px;">Learner progress</h5>
                <div class="table-responsive">
                    <table class="tbl" id="learners-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Modules done</th>
                                <th>Progress</th>
                                <th>Quiz score</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($learners as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="muted">{{ $row['email'] }}</td>
                                    <td data-order="{{ $row['completed'] }}">{{ $row['completed'] }} / {{ $row['total'] }}</td>
                                    <td style="width:200px;" data-order="{{ $row['percent'] }}">
                                        <div class="progress-track"><div class="progress-fill" style="width: {{ $row['percent'] }}%;"></div></div>
                                    </td>
                                    <td data-order="{{ $row['quiz_score'] ?? -1 }}">
                                        @if($row['quiz_score'] !== null) {{ round($row['quiz_score']) }}% @else <span class="muted">—</span> @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Actions for {{ $row['name'] }}"><i class="fas fa-bars"></i></button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                <a class="dropdown-item" href="{{ route('admin.exams.learning.user', $row['id']) }}"><i class="fas fa-eye text-info mr-2"></i> View</a>
                                                @if($quizModule)
                                                    <form method="POST" action="{{ route('admin.exams.learning.user.reset', $row['id']) }}"
                                                          data-confirm-title="Reset quiz?" data-confirm-name="{{ $row['name'] }}"
                                                          data-confirm-text="Their quiz score, answers and attempts are cleared and they retake the quiz. Other modules are not affected."
                                                          data-confirm-button="Reset quiz">
                                                        @csrf
                                                        <input type="hidden" name="module_id" value="{{ $quizModule['id'] }}">
                                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-redo mr-2"></i> Reset quiz</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.exams.learning.user.reset', $row['id']) }}"
                                                      data-confirm-title="Reset course?" data-confirm-name="{{ $row['name'] }}"
                                                      data-confirm-text="All completed modules, the quiz score and attempts are cleared and they start again from Module 1. This cannot be undone."
                                                      data-confirm-button="Reset course">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger"><i class="fas fa-undo mr-2"></i> Reset course</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="confirmResetModal" tabindex="-1" role="dialog" aria-labelledby="confirmResetTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content confirm-reset">
            <div class="modal-body text-center">
                <div class="confirm-reset-icon"><i class="fas fa-undo"></i></div>
                <h5 class="confirm-reset-title" id="confirmResetTitle">Reset?</h5>
                <div class="confirm-reset-name" id="confirmResetName"></div>
                <p class="confirm-reset-text" id="confirmResetText"></p>
                <div class="confirm-reset-actions">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmResetOk">Reset</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<style>
    /* let the action menu open past the bottom of the table instead of being clipped by the scroll box */
    @media (min-width: 768px) { .lms .table-responsive { overflow: visible; } }

    /* confirmation popup for the reset actions */
    .confirm-reset { border: 0; border-radius: 14px; box-shadow: 0 24px 60px rgba(16,24,40,.28); overflow: hidden; }
    .confirm-reset .modal-body { padding: 30px 28px 24px; }
    .confirm-reset-icon { width: 56px; height: 56px; margin: 0 auto 14px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: rgba(160,38,38,.10); color: #a02626; font-size: 22px; }
    .confirm-reset-title { font-size: 19px; font-weight: 800; margin: 0 0 4px; }
    .confirm-reset-name { display: inline-block; margin: 6px 0 10px; padding: 3px 12px; border-radius: 999px; font-size: 13px; font-weight: 700;
        background: #ecf5fe; color: #141d23; }
    .confirm-reset-text { color: #59413f; font-size: 14px; line-height: 1.55; margin: 0 0 22px; }
    .confirm-reset-actions { display: flex; gap: 10px; justify-content: center; }
    .confirm-reset-actions .btn { min-width: 120px; border-radius: 8px; font-weight: 600; }
    .confirm-reset-actions .btn-danger { background: #a02626; border-color: #a02626; }
    .confirm-reset-actions .btn-danger:hover { background: #870f0f; border-color: #870f0f; }
    .dark-mode .confirm-reset-name { background: #2d3748; color: #fff; }
    .dark-mode .confirm-reset-text { color: #cbd5e0; }
    .dark-mode .confirm-reset-icon { background: rgba(254,197,3,.14); color: #FEC503; }
</style>
@endpush

@push('scripts')
<script>
    $(function () {
        $('#learners-table').DataTable({ pageLength: 25, order: [[3, 'desc']], columnDefs: [{ orderable: false, targets: 5 }] });

        // Reset actions confirm in a modal instead of the browser's confirm() box.
        var pending = null;
        $(document).on('submit', 'form[data-confirm-title]', function (e) {
            e.preventDefault();
            pending = this;
            $('#confirmResetTitle').text(this.dataset.confirmTitle);
            $('#confirmResetName').text(this.dataset.confirmName);
            $('#confirmResetText').text(this.dataset.confirmText);
            $('#confirmResetOk').text(this.dataset.confirmButton).prop('disabled', false);
            $('#confirmResetModal').modal('show');
        });
        $('#confirmResetOk').on('click', function () {
            if (!pending) return;
            $(this).prop('disabled', true);
            pending.submit(); // native submit skips the handler above
        });
        $('#confirmResetModal').on('hidden.bs.modal', function () { pending = null; });
    });
</script>
@endpush
