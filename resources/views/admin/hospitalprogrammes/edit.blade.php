@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Hospital Programme</div>
    <div class="ms2-page-sub">Update assigned programmes and accreditation dates for this hospital.</div>

    <div class="ms2-card">
        <form method="POST" action="{{ url('admin/hospitalprogrammes/edit/' . $hospitalProgramme->id) }}"
              onsubmit="return validateForm()">
            @csrf
            <div class="ms2-body">

                {{-- Hospital (read-only) --}}
                <div class="ms2-row">
                    <div class="ms2-col" style="flex:1 1 100%;">
                        <label class="ms2-label">Hospital</label>
                        <div class="ms2-input-group">
                            <i class="fas fa-hospital"></i>
                            <select name="hospital_id_display" class="ms2-input" disabled>
                                @foreach($getHospital as $hospital)
                                    <option value="{{ $hospital->id }}" {{ $hospitalProgramme->hospital_id == $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->name }} — {{ $hospital->country_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="hospital_id" value="{{ $hospitalProgramme->hospital_id }}">
                        <div style="font-size:.75rem;color:#94a3b8;margin-top:3px;">Hospital cannot be changed here.</div>
                    </div>
                </div>

                {{-- Programme checkboxes --}}
                <div class="ms2-row">
                    <div class="ms2-col" style="flex:1 1 100%;">
                        <label class="ms2-label">Assigned Programmes <span class="req">*</span></label>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
                            @foreach($getProgramme as $programme)
                                <label style="display:flex;align-items:center;gap:6px;font-size:.88rem;cursor:pointer;
                                              padding:6px 14px;border-radius:6px;border:1px solid #4a5568;
                                              background:{{ in_array($programme->id, $assignedProgrammes) ? 'rgba(160,38,38,0.12)' : 'transparent' }};">
                                    <input type="checkbox" name="programme_id[]" value="{{ $programme->id }}"
                                           {{ in_array($programme->id, $assignedProgrammes) ? 'checked' : '' }}
                                           style="accent-color:#a02626;">
                                    {{ $programme->name }}
                                </label>
                                @if(in_array($programme->id, $assignedProgrammes))
                                    <input type="hidden" name="existing_programme_id[]" value="{{ $programme->id }}">
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Accredited Date</label>
                        <input type="month" name="accredited_date" class="ms2-input"
                               value="{{ \Carbon\Carbon::parse($hospitalProgramme->accredited_date)->format('Y-m') }}">
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Expiry Date</label>
                        <input type="month" name="expiry_date" class="ms2-input"
                               value="{{ \Carbon\Carbon::parse($hospitalProgramme->expiry_date)->format('Y-m') }}">
                    </div>
                </div>

                {{-- Status --}}
                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Status</label>
                        <select name="status" class="ms2-input">
                            <option value="Active"   {{ $hospitalProgramme->status == 'Active'   ? 'selected' : '' }}>Active</option>
                            <option value="Expired"  {{ $hospitalProgramme->status == 'Expired'  ? 'selected' : '' }}>Expired</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/hospitalprogrammes/list') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>

<script>
function validateForm() {
    var checked = document.querySelectorAll('input[name="programme_id[]"]:checked');
    if (checked.length === 0) {
        alert('Please select at least one programme.');
        return false;
    }
    return true;
}
</script>
@endsection
