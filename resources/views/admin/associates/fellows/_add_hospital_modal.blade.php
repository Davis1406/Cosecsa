{{--
    Inline "add a hospital" modal shared by the fellow add/edit pages. Creates
    a real hospital through cosecsa-api (POST admin/hospitals, via
    FellowsController::quickAddHospital()), then adds it to the
    select[name="organization"] dropdown and selects it.
--}}
<div class="modal fade" id="addHospitalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form id="addHospitalForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hospital mr-1"></i> Add Hospital</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="addHospitalAlert" class="alert alert-danger d-none" role="alert"></div>
                <div class="form-group">
                    <label>Hospital Name <span class="text-danger">*</span></label>
                    <input type="text" id="addHospitalName" class="form-control" placeholder="e.g. Queen Elizabeth Central Hospital" required>
                </div>
                <div class="form-group mb-0">
                    <label>Country <span class="text-danger">*</span></label>
                    <select id="addHospitalCountry" class="form-control" required>
                        <option value="">Select Country</option>
                        @foreach(($getCountry ?? collect()) as $country)
                            <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                        @endforeach
                    </select>
                </div>
                <p class="small text-muted mt-2 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>This creates a real hospital record in the system.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="addHospitalSubmit"
                        style="background:#a02626; border-color:#a02626;">
                    <i class="fas fa-save mr-1"></i>Save Hospital
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(function () {
    // Hospital dropdown: searchable + free-text (staff can pick a real hospital
    // or type a value that isn't in the list yet).
    $('.select2-tags').each(function () {
        if (!$(this).data('select2')) {
            $(this).select2({
                tags: true,
                width: '100%',
                placeholder: function () { return $(this).data('placeholder') || ''; },
                allowClear: true
            });
        }
    });

    var $form = $('#addHospitalForm');
    if (!$form.length) return;

    var url = '{{ route("fellows.quick-add-hospital") }}';

    $form.on('submit', function (e) {
        e.preventDefault();

        var name      = ($('#addHospitalName').val() || '').trim();
        var countryId = $('#addHospitalCountry').val();
        var $alert    = $('#addHospitalAlert').addClass('d-none');
        if (!name || !countryId) return;

        var $btn = $('#addHospitalSubmit').prop('disabled', true).text('Saving...');

        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', name: name, country_id: countryId }
        }).done(function (res) {
            var $select = $('select[name="organization"]');
            var exists  = $select.find('option').filter(function () { return this.value === res.name; }).length > 0;
            if (res.name && !exists) {
                $select.append($('<option>').val(res.name).text(res.name));
            }
            $select.val(res.name).trigger('change');
            $('#addHospitalModal').modal('hide');
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to create hospital.';
            $alert.removeClass('d-none').text(msg);
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Hospital');
        });
    });

    $('#addHospitalModal').on('hidden.bs.modal', function () {
        $('#addHospitalAlert').addClass('d-none');
        $form[0].reset();
    });
});
</script>
@endpush
