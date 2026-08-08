@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Programme</div>
    <div class="ms2-page-sub">Update programme name, type, duration and fee information.</div>

    <div class="ms2-card">
        <form method="POST" action="{{ url('admin/programmes/edit_programmes/' . $getRecord->id) }}">
            @csrf
            <div class="ms2-body">

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Programme Name <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-graduation-cap"></i>
                            <input type="text" name="name" class="ms2-input"
                                   value="{{ old('name', $getRecord->name) }}" required placeholder="Programme name">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Programme Type <span class="req">*</span></label>
                        <input type="text" name="programme_type" class="ms2-input"
                               value="{{ old('programme_type', $getRecord->programme_type) }}" required placeholder="e.g. Surgical">
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Duration <span class="req">*</span></label>
                        <input type="text" name="duration" class="ms2-input"
                               value="{{ old('duration', $getRecord->duration) }}" required placeholder="e.g. 5 years">
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Entry Fee <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-dollar-sign"></i>
                            <input type="text" name="entry_fee" class="ms2-input"
                                   value="{{ old('entry_fee', $getRecord->entry_fee) }}" required placeholder="USD amount">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Exam Fee <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-dollar-sign"></i>
                            <input type="text" name="exam_fee" class="ms2-input"
                                   value="{{ old('exam_fee', $getRecord->exam_fee) }}" required placeholder="USD amount">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Repeat Fee <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="fas fa-dollar-sign"></i>
                            <input type="text" name="repeat_fee" class="ms2-input"
                                   value="{{ old('repeat_fee', $getRecord->repeat_fee) }}" required placeholder="USD amount">
                        </div>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/programmes') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
