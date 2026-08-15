@extends('layout.app')
@section('content')
<div class="content-wrapper">
<div class="ms2-wrap">

    <div class="ms2-page-title">Edit Trainer</div>
    <div class="ms2-page-sub">Update this trainer's roster details — organisation/hospital, country attended in, specialty and ToT years attended.</div>

    <div class="ms2-card">
        <form method="POST" action="{{ url('admin/associates/trainers/edit/' . $trainer->id) }}">
            @csrf

            <div class="ms2-body">

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Name <span class="req">*</span></label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-user"></i>
                            <input type="text" name="name" class="ms2-input" value="{{ old('name', $trainer->name) }}" required>
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Email</label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-envelope"></i>
                            <input type="email" name="email" class="ms2-input" value="{{ old('email', $trainer->email) }}">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Organisation <span class="text-muted small">(free text, kept as-is)</span></label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-building"></i>
                            <input type="text" name="organisation" class="ms2-input" value="{{ old('organisation', $trainer->organisation) }}">
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Linked Hospital</label>
                        <select name="hospital_id" class="ms2-input">
                            <option value="">— Not linked —</option>
                            @foreach($allHospitals as $h)
                                <option value="{{ $h->id }}" {{ old('hospital_id', $trainer->hospital->id ?? null) == $h->id ? 'selected' : '' }}>
                                    {{ $h->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Specialty (Programme)</label>
                        <select name="programme_id" class="ms2-input">
                            <option value="">— No matching main programme —</option>
                            @foreach($allProgrammes->where('programme_type', 'Fellowship') as $p)
                                <option value="{{ $p->id }}" {{ old('programme_id', $trainer->programme_id) == $p->id ? 'selected' : '' }}>
                                    {{ preg_replace('/^FCS\s+/', '', $p->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Specialty <span class="text-muted small">(raw text, shown as subspecialty when no programme above)</span></label>
                        <div class="ms2-input-group">
                            <i class="ms2-icon fas fa-stethoscope"></i>
                            <input type="text" name="specialty_raw" class="ms2-input" value="{{ old('specialty_raw', $trainer->specialty) }}">
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Country Attended In <span class="text-muted small">(select all that apply)</span></label>
                        <div class="border rounded p-2" style="max-height:170px;overflow-y:auto;">
                            @php $selectedCountryIds = collect(old('country_ids', collect($trainer->countries ?? [])->pluck('id')->all())); @endphp
                            @foreach($allCountries as $c)
                            <label class="d-block mb-0" style="font-weight:normal;">
                                <input type="checkbox" name="country_ids[]" value="{{ $c->id }}" {{ $selectedCountryIds->contains($c->id) ? 'checked' : '' }}>
                                {{ $c->country_name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">ToT Years Attended</label>
                        <div class="border rounded p-2" style="max-height:170px;overflow-y:auto;">
                            @php $selectedYearIds = collect(old('tot_year_ids', collect($trainer->tot_years ?? [])->pluck('id')->all())); @endphp
                            @foreach($allTotYears as $y)
                            <label class="d-block mb-0" style="font-weight:normal;">
                                <input type="checkbox" name="tot_year_ids[]" value="{{ $y->id }}" {{ $selectedYearIds->contains($y->id) ? 'checked' : '' }}>
                                {{ $y->label_full }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col">
                        <label class="ms2-label">Master Trainer</label>
                        <input type="hidden" name="is_master_trainer" value="0">
                        <label class="d-block" style="font-weight:normal;">
                            <input type="checkbox" name="is_master_trainer" value="1" {{ old('is_master_trainer', $trainer->is_master_trainer) ? 'checked' : '' }}>
                            Yes, this person is a Master Trainer
                        </label>
                    </div>
                    <div class="ms2-col">
                        <label class="ms2-label">Specialty Surgeon (SS)</label>
                        <input type="hidden" name="is_specialty_surgeon" value="0">
                        <label class="d-block" style="font-weight:normal;">
                            <input type="checkbox" name="is_specialty_surgeon" value="1" {{ old('is_specialty_surgeon', $trainer->is_specialty_surgeon) ? 'checked' : '' }}>
                            Yes, this person is a Specialty Surgeon
                        </label>
                    </div>
                </div>

                <div class="ms2-row">
                    <div class="ms2-col" style="flex:1 1 100%;">
                        <label class="ms2-label">Comment</label>
                        <textarea name="comment" class="ms2-input" rows="3">{{ old('comment', $trainer->comment) }}</textarea>
                    </div>
                </div>

            </div>
            <div class="ms2-footer">
                <a href="{{ url('admin/associates/trainers/list') }}" class="ms2-btn-back">Cancel</a>
                <button type="submit" class="ms2-btn-submit">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>

</div>
</div>
@endsection
