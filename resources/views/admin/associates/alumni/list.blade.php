@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/alumni/reports') }}" class="btn btn-info mr-2">
                            <i class="fas fa-chart-bar mr-1"></i> Analytics
                        </a>
                        <a href="{{ url('admin/associates/alumni/import') }}" class="btn btn-secondary mr-2"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <i class="fas fa-upload mr-1"></i> Import Alumni
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Alumni — COSECSA Fellows</h3></div>
                            <div class="card-body">
                                <table id="alumnitable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Specialty / Programme</th>
                                            <th>Fellowship Type</th>
                                            <th>Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alumni as $a)
                                        <tr>
                                            <td class="row-num"></td>
                                            <td>{{ $a->fellow_name ?? '-' }}</td>
                                            <td>{{ $a->personal_email ?? '-' }}</td>
                                            <td>{{ $a->country_name ?? '-' }}</td>
                                            <td>{{ $a->current_specialty ?: ($a->programme_name ? preg_replace('/^FCS\s+/i','', $a->programme_name) : '-') }}</td>
                                            <td>{{ $a->fellowship_type ?? '-' }}</td>
                                            <td>{{ $a->fellowship_year ?? '-' }}</td>
                                            <td>
                                                <a href="#" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{ url("admin/associates/fellows/view/" . ($a->fellow_id ?? 0)) }}">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                    <a href="{{ url("admin/associates/fellows/edit/" . ($a->fellow_id ?? 0)) }}">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </a>'>
                                                    <i class="fa fa-bars" style="color:#5a6268"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    .popover-content a,.popover-body a{display:block;padding:5px 10px;color:#5a6268;text-decoration:none;border-radius:3px;margin-bottom:2px;transition:all .3s ease}
    .popover-content a:hover,.popover-body a:hover{background-color:#a02626!important;color:#fff!important;text-decoration:none}
    .popover-content a i,.popover-body a i{margin-right:6px;color:inherit}
    .popover-content a:hover i,.popover-body a:hover i{color:#fff!important}
    .action-icon{cursor:pointer;transition:color .3s ease}
    .action-icon:hover{color:#a02626!important}
    .paginate_button.active>.page-link{background-color:#a02626!important;border-color:#a02626!important;color:white}
    .paginate_button>.page-link{color:#a02626}
    .paginate_button>.page-link:focus,.paginate_button.active>.page-link:focus{box-shadow:none!important;outline:none!important}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    if (!$('#alumnitable').length) return;
    var t = $('#alumnitable').DataTable({
        responsive: true,
        lengthChange: true,
        autoWidth: false,
        stateSave: false,
        paging: true,
        pageLength: 25,
        order: [[6, 'desc'], [1, 'asc']],
        dom: '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
        buttons: [
            { extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: 'btn btn-success btn-sm', title: 'Alumni List', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'pdfHtml5',   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: 'btn btn-danger btn-sm',  title: 'Alumni List', orientation: 'landscape', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'print',      text: '<i class="fas fa-print mr-1"></i> Print',    className: 'btn btn-secondary btn-sm', exportOptions: { columns: [0,1,2,3,4,5,6] } },
            { extend: 'colvis',     text: '<i class="fas fa-columns mr-1"></i> Columns', className: 'btn btn-outline-secondary btn-sm' }
        ],
        columns: [
            { visible: true,  orderable: false, searchable: false }, // #
            { visible: true },
            { visible: true },
            { visible: true },
            { visible: true },
            { visible: true },
            { visible: true },
            { visible: true, orderable: false, searchable: false }  // Action
        ],
        drawCallback: function () {
            this.api().column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
        }
    });

    // popovers
    t.on('draw', function () {
        $('[data-toggle="popover"]').each(function () {
            if (!$(this).data('bs.popover')) $(this).popover({ trigger: 'focus', html: true });
        });
    });
    $('[data-toggle="popover"]').each(function () {
        if (!$(this).data('bs.popover')) $(this).popover({ trigger: 'focus', html: true });
    });
});
</script>
@endpush
