{{-- Row action menu (View / Reset quiz / Reset course) shared by the Completed, Ongoing and Pending tabs.
     Expects $row and $quizModule. Reset forms are confirmed by the modal on the page. --}}
<div class="dropdown d-inline-block">
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
