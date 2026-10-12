@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h1 style="font-size:1.4rem;">Upload Part I Results</h1>
                    <p class="text-muted mb-0" style="font-size:.85rem;">
                        Upload the examination officer's "Part I Results" spreadsheet. You'll see how every row matches before anything is saved.
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-cosecsa-dark">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Candidates
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('_message')

            <div class="row">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">Spreadsheet</h3></div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('admin/associates/candidates/part-one/preview') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="p1file">Part I Results file (.xlsx)</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="p1file" name="file" accept=".xlsx,.xls" required>
                                        <label class="custom-file-label" for="p1file">Choose file…</label>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        First sheet, first row = column headings. The exam year is read from <strong>Class</strong> (e.g. "FCS ORTHO 2026").
                                    </small>
                                </div>
                                <button type="submit" class="btn btn-cosecsa">
                                    <i class="fas fa-search mr-1"></i> Preview
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">What the upload uses</h3></div>
                        <div class="card-body py-2">
                            @include('admin.associates.candidates._part_one_columns')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('p1file').addEventListener('change', function () {
    this.nextElementSibling.textContent = this.files.length ? this.files[0].name : 'Choose file…';
});
</script>
@endpush
