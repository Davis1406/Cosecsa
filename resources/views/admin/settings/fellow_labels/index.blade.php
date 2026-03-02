@extends('layout.app')

@push('styles')
<style>
.label-swatch {
    display: inline-block;
    width: 18px; height: 18px;
    border-radius: 50%;
    vertical-align: middle;
    margin-right: 6px;
    border: 1px solid rgba(0,0,0,.15);
}
.label-preview {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 11px;
    font-size: .75rem;
    font-weight: 600;
    border: 1px solid;
}
.color-btn {
    width: 26px; height: 26px;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: border-color .15s, transform .1s;
    flex-shrink: 0;
}
.color-btn:hover, .color-btn.selected { border-color: #333; transform: scale(1.15); }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-tags mr-2" style="color:#a02626;"></i>Fellow Labels</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Settings – Fellow Labels</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                {{-- ── Add New Label ── --}}
                <div class="col-md-4">
                    <div class="card" style="border-top:3px solid #a02626;">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus-circle mr-2" style="color:#a02626;"></i>Add New Label</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ url('admin/settings/fellow-labels') }}">
                                @csrf
                                <div class="form-group">
                                    <label>Label Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                           placeholder="e.g. High Achiever" value="{{ old('name') }}" required maxlength="80">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="form-group">
                                    <label>Colour <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 mb-2" id="colorPalette">
                                        @foreach(['#a02626','#FEC503','#155724','#0c5460','#856404','#5a2070','#0d6efd','#fd7e14','#20c997','#dc3545','#6c757d','#343a40'] as $c)
                                            <button type="button" class="color-btn"
                                                    style="background:{{ $c }};"
                                                    data-color="{{ $c }}"
                                                    onclick="pickColor('{{ $c }}', this)"></button>
                                        @endforeach
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text p-1" id="colorPreviewBox"
                                                  style="width:36px; background:#6c757d; border-radius:4px 0 0 4px;"></span>
                                        </div>
                                        <input type="text" name="color" id="colorInput" class="form-control"
                                               value="#6c757d" placeholder="#6c757d" maxlength="20" required
                                               oninput="document.getElementById('colorPreviewBox').style.background=this.value">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Description <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="description" class="form-control"
                                           placeholder="Brief description" value="{{ old('description') }}" maxlength="255">
                                </div>

                                <div class="form-group mb-1">
                                    <label>Preview:</label><br>
                                    <span class="label-preview" id="labelPreview" style="background:#6c757d22; color:#6c757d; border-color:#6c757d80;">
                                        Sample Label
                                    </span>
                                </div>

                                <button type="submit" class="btn btn-block mt-3"
                                        style="background:#a02626; color:#fff; font-weight:700;">
                                    <i class="fas fa-plus mr-1"></i> Create Label
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Existing Labels ── --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title"><i class="fas fa-list mr-2" style="color:#a02626;"></i>All Labels ({{ $labels->count() }})</h3>
                        </div>
                        <div class="card-body p-0">
                            @if($labels->isEmpty())
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-tags fa-3x mb-3"></i><br>
                                    No labels created yet. Add your first label on the left.
                                </div>
                            @else
                                <table class="table table-hover mb-0" style="font-size:.85rem;">
                                    <thead style="background:#f5f5f5;">
                                        <tr>
                                            <th>Label</th>
                                            <th>Description</th>
                                            <th>Used by</th>
                                            <th>Active</th>
                                            <th style="width:120px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($labels as $label)
                                    <tr>
                                        <td>
                                            <span class="label-preview"
                                                  style="background:{{ $label->color }}22; color:{{ $label->color }}; border-color:{{ $label->color }}80;">
                                                {{ $label->name }}
                                            </span>
                                        </td>
                                        <td class="text-muted" style="font-size:.78rem;">{{ $label->description ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ \DB::table('fellow_label_assignments')->where('label_id',$label->id)->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($label->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-xs btn-outline-warning"
                                                    onclick="editLabel({{ $label->id }}, '{{ addslashes($label->name) }}', '{{ $label->color }}', '{{ addslashes($label->description ?? '') }}', {{ $label->is_active }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ url('admin/settings/fellow-labels/' . $label->id) }}"
                                                  style="display:inline;"
                                                  onsubmit="return confirm('Delete label \'{{ $label->name }}\'? It will be removed from all fellows.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

{{-- Edit Label Modal --}}
<div class="modal fade" id="editLabelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editLabelForm">
                @csrf @method('PUT')
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;"><i class="fas fa-edit mr-2"></i>Edit Label</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Label Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required maxlength="80">
                    </div>
                    <div class="form-group">
                        <label>Colour <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-2 mb-2" id="editColorPalette">
                            @foreach(['#a02626','#FEC503','#155724','#0c5460','#856404','#5a2070','#0d6efd','#fd7e14','#20c997','#dc3545','#6c757d','#343a40'] as $c)
                                <button type="button" class="color-btn"
                                        style="background:{{ $c }};"
                                        data-color="{{ $c }}"
                                        onclick="pickEditColor('{{ $c }}', this)"></button>
                            @endforeach
                        </div>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text p-1" id="editColorBox" style="width:36px;"></span>
                            </div>
                            <input type="text" name="color" id="editColor" class="form-control" required maxlength="20"
                                   oninput="document.getElementById('editColorBox').style.background=this.value">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" id="editDescription" class="form-control" maxlength="255">
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="editIsActive" name="is_active">
                            <label class="custom-control-label" for="editIsActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function pickColor(color, btn) {
    document.getElementById('colorInput').value = color;
    document.getElementById('colorPreviewBox').style.background = color;
    updateLabelPreview();
    document.querySelectorAll('#colorPalette .color-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

function pickEditColor(color, btn) {
    document.getElementById('editColor').value = color;
    document.getElementById('editColorBox').style.background = color;
    document.querySelectorAll('#editColorPalette .color-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}

function updateLabelPreview() {
    var name  = document.getElementById('labelPreviewName')?.value || 'Sample Label';
    var color = document.getElementById('colorInput').value;
    var el    = document.getElementById('labelPreview');
    if (el) {
        el.style.background    = color + '22';
        el.style.color         = color;
        el.style.borderColor   = color + '80';
    }
}

// Live preview update
document.addEventListener('DOMContentLoaded', function() {
    var nameInput  = document.querySelector('input[name="name"]');
    var colorInput = document.getElementById('colorInput');
    var preview    = document.getElementById('labelPreview');

    if (nameInput) nameInput.addEventListener('input', function() {
        if (preview) preview.textContent = this.value || 'Sample Label';
    });
    if (colorInput) colorInput.addEventListener('input', function() {
        updateLabelPreview();
    });
});

function editLabel(id, name, color, description, isActive) {
    document.getElementById('editLabelForm').action = '/admin/settings/fellow-labels/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editColor').value = color;
    document.getElementById('editColorBox').style.background = color;
    document.getElementById('editDescription').value = description;
    document.getElementById('editIsActive').checked = isActive == 1;
    // Highlight matching color button
    document.querySelectorAll('#editColorPalette .color-btn').forEach(function(btn) {
        btn.classList.toggle('selected', btn.dataset.color === color);
    });
    $('#editLabelModal').modal('show');
}
</script>
@endpush

@endsection
