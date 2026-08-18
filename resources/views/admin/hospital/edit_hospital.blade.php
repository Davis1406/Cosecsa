@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Hospital</div>
    <div class="ms2-page-sub">Update hospital information and accreditation details.</div>

    <div class="ms2-card">
        <form method="POST" action="{{ url('admin/hospital/edit_hospital/' . $getRecord->id) }}">
            @csrf
            <div class="ms2-body">

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Hospital Name <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-hospital"></i>
                            <input type="text" name="name" class="ms2-input"
                                   value="{{ old('name', $getRecord->name) }}" required placeholder="Hospital name">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Country <span class="req">*</span></label>
                        <select name="country_id" class="ms2-input" required>
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->id }}" {{ $country->id == $getRecord->country_id ? 'selected' : '' }}>
                                    {{ $country->country_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Hospital Type</label>
                        <select name="hospital_type" class="ms2-input">
                            @php $hospTypeOpts = [1=>'Government', 2=>'NGO / Faith-Based', 3=>'Private', 4=>'University Teaching']; @endphp
                            @foreach($hospTypeOpts as $val => $label)
                                <option value="{{ $val }}" {{ $getRecord->hospital_type == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Contact Email</label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-envelope"></i>
                            <input type="email" name="contact_email" class="ms2-input"
                                   value="{{ $getRecord->contact_email }}" placeholder="Used for accreditation reminders">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Status</label>
                        <div class="ms2-input" style="background:#f8f9fa;">
                            @if($getRecord->status == 0)
                                <span class="dot dot-active_acc"></span> Active
                            @else
                                <span class="dot dot-expired"></span> Inactive
                            @endif
                        </div>
                        <small class="text-muted">Derived automatically — Active whenever at least one accredited programme is Active.</small>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/hospital/list') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
