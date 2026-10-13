@php
    $payload = $block->payload ?? [];
    $answers = array_values($payload['answers'] ?? []);
    $corrects = $payload['corrects'] ?? (isset($payload['correct']) ? [$payload['correct']] : []);
    $e = fn ($html) => trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($html ?? ''))));
@endphp

<form method="POST" action="{{ route('admin.exams.learning.block.quiz', $block->id) }}" data-quiz-editor>
    @csrf
    <div class="qe-fields">
        <div class="editor-field">
            <label class="editor-label">Question <span class="qe-muted">— required</span></label>
            <textarea name="title" rows="2" class="form-control qe-input" required placeholder="Type or edit the question">{{ $e($payload['title'] ?? '') }}</textarea>
        </div>

        <div class="editor-field">
            <label class="editor-label">Answers <span class="qe-muted">— at least two, tick the correct one</span></label>
            <div class="qe-answers" data-quiz-answers>
                @foreach($answers as $a)
                    @php $id = $a['id'] ?? ''; @endphp
                    <div class="qe-answer">
                        <label class="qe-correct" title="Mark as the correct answer">
                            <input type="radio" name="corrects[]" value="{{ $id }}" @checked(in_array($id, $corrects, true))>
                            <span>Correct</span>
                        </label>
                        <input type="hidden" name="answers[{{ $loop->index }}][id]" value="{{ $id }}">
                        <textarea name="answers[{{ $loop->index }}][title]" rows="2" class="form-control qe-input" required placeholder="Answer text">{{ $e($a['title'] ?? '') }}</textarea>
                        <button type="button" class="btn btn-sm btn-outline-danger qe-remove" title="Remove this answer">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="vertical-align:-1px;"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-light btn-sm qe-add" data-quiz-add-answer>
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" style="vertical-align:-1px; margin-right:4px;"><path d="M12 5v14M5 12h14"/></svg>
                Add answer
            </button>
        </div>

        <div class="editor-field">
            <label class="editor-label">Feedback <span class="qe-muted">— shown to learners after they pass (optional)</span></label>
            <textarea name="feedback" rows="2" class="form-control qe-input" placeholder="Explanation of the correct answer">{{ $e($payload['feedback'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="editor-footer">
        <button type="submit" class="btn btn-primary">Save question</button>
        <button type="button" class="btn btn-light" data-editor-cancel>Cancel</button>
        <span class="muted" style="font-size:12.5px;">Saving resets this quiz's learner progress so it is retaken with the new answers.</span>
    </div>
</form>

<script>
    (function () {
        var form = document.currentScript && document.currentScript.previousElementSibling;
        if (!form || !form.matches('form[data-quiz-editor]')) return;

        var list = form.querySelector('[data-quiz-answers]');
        if (!list) return;

        var reindex = function () {
            [...list.querySelectorAll('.qe-answer')].forEach(function (row, i) {
                row.querySelector('input[name$="[id]"]').name = 'answers[' + i + '][id]';
                row.querySelector('textarea[name$="[title]"]').name = 'answers[' + i + '][title]';
            });
        };

        form.querySelector('[data-quiz-add-answer]').addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'qe-answer';
            row.innerHTML =
                '<label class="qe-correct" title="Mark as the correct answer">' +
                    '<input type="radio" name="corrects[]" value="">' +
                    '<span>Correct</span>' +
                '</label>' +
                '<input type="hidden" name="answers[0][id]" value="">' +
                '<textarea name="answers[0][title]" rows="2" class="form-control qe-input" required placeholder="Answer text"></textarea>' +
                '<button type="button" class="btn btn-sm btn-outline-danger qe-remove" title="Remove this answer">' +
                    '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="vertical-align:-1px;"><path d="M6 6l12 12M18 6L6 18"/></svg>' +
                '</button>';
            list.appendChild(row);
            row.querySelector('input[name$="[id]"]').value = 'opt' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            reindex();
            row.querySelector('textarea').focus();
            row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });

        list.addEventListener('click', function (e) {
            var btn = e.target.closest('.qe-remove');
            if (!btn) return;
            if (list.querySelectorAll('.qe-answer').length <= 2) return;
            var row = btn.closest('.qe-answer');
            var wasChecked = row.querySelector('input[type="radio"]').checked;
            row.remove();
            reindex();
            if (wasChecked) {
                var first = list.querySelector('.qe-correct input');
                if (first) first.checked = true;
            }
        });

        form.querySelector('[data-editor-cancel]').addEventListener('click', function () {
            var host = form.closest('.lms-block');
            if (!host) return; // standalone page: let the link navigate
            host.querySelector('.lms-block-editor').hidden = true;
            host.classList.remove('is-editing');
        });
    })();
</script>