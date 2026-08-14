@php
    // Props: $prefix — unique DOM-id prefix so this can be included twice on
    // one page (e.g. 'pd' and 'map'). Wired up by wireFellowPicker() in
    // view_hospital.blade.php.
@endphp
<div class="form-group">
    <label>Search Fellow <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="{{ $prefix }}_fp_search" placeholder="Type a name or email...">
    <div class="list-group mt-1 d-none" id="{{ $prefix }}_fp_results" style="max-height:180px; overflow-y:auto;"></div>
    <div class="text-success small d-none mt-1" id="{{ $prefix }}_fp_selected"></div>
    <button type="button" class="btn btn-link btn-sm p-0 mt-1" id="{{ $prefix }}_fp_not_listed">
        Not in the list? Add a new fellow
    </button>
</div>

<div class="d-none border rounded p-2 mb-2" id="{{ $prefix }}_fp_new">
    <div class="form-row">
        <div class="form-group col-6">
            <label class="small mb-1">First Name</label>
            <input type="text" class="form-control form-control-sm" name="firstname">
        </div>
        <div class="form-group col-6">
            <label class="small mb-1">Last Name</label>
            <input type="text" class="form-control form-control-sm" name="lastname">
        </div>
    </div>
    <div class="form-group">
        <label class="small mb-1">Login Email</label>
        <input type="email" class="form-control form-control-sm" name="email">
    </div>
    <div class="form-row">
        <div class="form-group col-6">
            <label class="small mb-1">Gender</label>
            <select class="form-control form-control-sm" name="gender">
                <option value="">--</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="form-group col-6">
            <label class="small mb-1">Country</label>
            <select class="form-control form-control-sm" name="country_id">
                <option value="">--</option>
                @foreach($allCountries as $c)
                    <option value="{{ $c->id }}">{{ $c->country_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
