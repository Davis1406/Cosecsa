

@php
    $canManage = Auth::user()->hasPermission('findasurgeon.manage');
    $pending   = $showing === 'pending';
@endphp

<style>
    .hr-chip { display:inline-block; padding:1px 8px; margin:0 4px 4px 0; border-radius:10px; background:#f1f3f5; font-size:.78rem; }
    body.dark-mode .hr-chip { background:#2b3040; }
    .hr-sugg { margin:0 6px 6px 0; }
</style>

<div class="fas-pane-head mb-3"><div class="fas-muted" style="font-size:.85rem;">Fellows pick their Current Hospital from the official list. When theirs is not listed they type it, and it waits here. Link it to the right hospital, add it to the list, or dismiss it.</div></div>
<div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline mb-3">
                        @if (! $pending)<input type="hidden" name="show" value="dismissed">@endif
                        <input type="text" size="17" name="q" value="{{ request('q') }}" class="form-control mr-2 mb-2" placeholder="Hospital name…">
                        <select name="country_id" class="form-control mr-2 mb-2">
                            <option value="">All countries</option>
                            @foreach ($countries as $c)<option value="{{ $c->id }}" {{ (string) request('country_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->country_name }}</option>@endforeach
                        </select>
                        <select name="source" class="form-control mr-2 mb-2">
                            <option value="">Typed in the app &amp; older entries</option>
                            <option value="typed" {{ request('source') === 'typed' ? 'selected' : '' }}>Typed in the app</option>
                            <option value="legacy" {{ request('source') === 'legacy' ? 'selected' : '' }}>Older entries (never matched)</option>
                        </select>
                        <button class="btn btn-sm btn-dark mr-2 mb-2" type="submit">Filter</button>
                        <a href="{{ route('admin.findasurgeon.hospitals', $pending ? [] : ['show' => 'dismissed']) }}" class="btn btn-sm btn-outline-secondary mb-2">Reset</a>
                        <span class="ml-auto mb-2">
                            @if ($pending)
                                <a href="{{ route('admin.findasurgeon.hospitals', ['show' => 'dismissed']) }}" class="fas-muted">Show dismissed</a>
                            @else
                                <a href="{{ route('admin.findasurgeon.hospitals') }}" class="fas-muted">&larr; Back to review list</a>
                            @endif
                            &nbsp;·&nbsp; <span class="fas-muted">{{ $groups->count() }} name{{ $groups->count() == 1 ? '' : 's' }}</span>
                        </span>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>#</th><th>Hospital as typed</th><th>Country</th><th>Fellows</th><th>Closest listed hospitals</th><th class="text-right">Actions</th></tr></thead>
                            <tbody>
                            @forelse ($groups as $g)
                                <tr>
                                    <td class="fas-muted">{{ $loop->iteration }}</td>
                                    <td style="min-width:220px;">
                                        <strong>{{ $g->name }}</strong>
                                        @if ($g->typed_by_fellow)
                                            <span class="badge badge-warning ml-1" title="A Fellow typed this in the app{{ $g->last_typed_at ? ' on ' . \Carbon\Carbon::parse($g->last_typed_at)->format('d M Y') : '' }}">Typed in app</span>
                                        @else
                                            <span class="badge badge-light border ml-1" title="Older entry that never matched a listed hospital">Older entry</span>
                                        @endif
                                    </td>
                                    <td>{{ $g->country ?: '—' }}</td>
                                    <td style="min-width:200px;">
                                        <strong>{{ $g->fellows_count }}</strong>
                                        <div>@foreach ($g->fellows as $f)<span class="hr-chip">{{ $f->name }}</span>@endforeach
                                            @if ($g->fellows_count > count($g->fellows))<span class="fas-muted">+{{ $g->fellows_count - count($g->fellows) }} more</span>@endif</div>
                                    </td>
                                    <td style="min-width:240px;">
                                        @forelse ($g->suggestions as $s)
                                            @if ($canManage && $pending)
                                                <form method="post" action="{{ route('admin.findasurgeon.hospitals.link') }}" class="d-inline" onsubmit="return confirm('Link {{ $g->fellows_count }} Fellow(s) to {{ addslashes($s->name) }}?');">
                                                    @csrf
                                                    <input type="hidden" name="name" value="{{ $g->name }}"><input type="hidden" name="country_id" value="{{ $g->country_id }}"><input type="hidden" name="hospital_id" value="{{ $s->hospital_id }}">
                                                    <button class="btn btn-xs btn-outline-success hr-sugg" type="submit" title="{{ $s->score }}% similar">Link to {{ $s->name }}</button>
                                                </form>
                                            @else
                                                <span class="hr-chip">{{ $s->name }} · {{ $s->score }}%</span>
                                            @endif
                                        @empty
                                            <span class="fas-muted">No close match on the list.</span>
                                        @endforelse
                                    </td>
                                    <td class="text-right" style="white-space:nowrap;">
                                        @if ($canManage)
                                            @if ($pending)
                                                <button type="button" class="btn btn-xs btn-outline-dark js-link" data-name="{{ $g->name }}" data-country="{{ $g->country_id }}" data-count="{{ $g->fellows_count }}">Link to…</button>
                                                <button type="button" class="btn btn-xs btn-dark js-add" data-name="{{ $g->name }}" data-country="{{ $g->country_id }}" data-count="{{ $g->fellows_count }}">Add to list</button>
                                                <form method="post" action="{{ route('admin.findasurgeon.hospitals.dismiss') }}" class="d-inline" onsubmit="return confirm('Dismiss this name? It will leave the review list; Fellows keep what they typed.');">
                                                    @csrf<input type="hidden" name="name" value="{{ $g->name }}"><input type="hidden" name="country_id" value="{{ $g->country_id }}">
                                                    <button class="btn btn-xs btn-outline-secondary" type="submit">Dismiss</button>
                                                </form>
                                            @else
                                                <form method="post" action="{{ route('admin.findasurgeon.hospitals.restore') }}" class="d-inline">
                                                    @csrf<input type="hidden" name="name" value="{{ $g->name }}"><input type="hidden" name="country_id" value="{{ $g->country_id }}">
                                                    <button class="btn btn-xs btn-outline-dark" type="submit">Restore</button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="fas-muted">View only</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center fas-muted py-4">{{ $pending ? 'Nothing to review. Every hospital a Fellow uses is on the official list.' : 'No dismissed names.' }}</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
</div>

@if ($canManage && $pending)
{{-- Link to any listed hospital --}}
<div class="modal fade" id="linkModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('admin.findasurgeon.hospitals.link') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Link to a listed hospital</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <p class="mb-2">Typed as <strong id="lmName"></strong> · <span id="lmCount"></span> Fellow(s)</p>
                <input type="hidden" name="name" id="lmNameIn"><input type="hidden" name="country_id" id="lmCountryIn">
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" class="custom-control-input" id="lmAll"><label class="custom-control-label" for="lmAll">Show hospitals from every country</label>
                </div>
                <select name="hospital_id" id="lmHospital" class="form-control" required></select>
                <p class="fas-muted mt-2 mb-0" style="font-size:.82rem;">Each Fellow's Current Hospital becomes the listed hospital's name, and the change is recorded.</p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Link</button></div>
        </form>
    </div>
</div>

{{-- Add to the official list --}}
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" action="{{ route('admin.findasurgeon.hospitals.add') }}" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Add to the hospital list</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body">
                <p class="mb-2">Typed as <strong id="amName"></strong> · <span id="amCount"></span> Fellow(s)</p>
                <input type="hidden" name="name" id="amNameIn"><input type="hidden" name="country_id" id="amCountryIn">
                <div class="form-group"><label>Hospital name</label><input type="text" name="hospital_name" id="amHospital" class="form-control" minlength="3" maxlength="255" required></div>
                <div class="form-row">
                    <div class="form-group col-7"><label>Country</label>
                        <select name="hospital_country_id" id="amCountry" class="form-control" required>
                            @foreach ($countries as $c)<option value="{{ $c->id }}">{{ $c->country_name }}</option>@endforeach
                        </select></div>
                    <div class="form-group col-5"><label>Type</label>
                        <select name="hospital_type" class="form-control"><option value="1">Government</option><option value="2">NGO</option><option value="3">Private</option><option value="4">University</option></select></div>
                </div>
                <div class="alert alert-warning mb-0" style="font-size:.84rem;">
                    This adds a real entry to the main hospital list, as an <strong>unaccredited (inactive)</strong> hospital. It will also appear in hospital pickers elsewhere in the MIS.
                    Check the spelling first; correct its type later under Hospitals. If a similar hospital is already listed, use <em>Link</em> instead.
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-dark">Add and link</button></div>
        </form>
    </div>
</div>
@endif

@if ($canManage && $pending)
<script>
(function () {
    var hospitals = @json($hospitals->map(fn ($h) => ['id' => $h->id, 'name' => $h->name, 'country_id' => $h->country_id])->values());
    var countryId = null;

    function fillHospitals() {
        var all = $('#lmAll').is(':checked'), $s = $('#lmHospital').empty();
        var list = hospitals.filter(function (h) { return all || !countryId || String(h.country_id) === String(countryId); });
        $s.append('<option value="">Choose a hospital…</option>');
        list.forEach(function (h) { $s.append($('<option>').val(h.id).text(h.name)); });
    }

    $(document).off('click.fasHr', '.js-link').on('click.fasHr', '.js-link', function () {
        var d = $(this).data(); countryId = d.country || null;
        $('#lmName').text(d.name); $('#lmCount').text(d.count);
        $('#lmNameIn').val(d.name); $('#lmCountryIn').val(d.country || '');
        $('#lmAll').prop('checked', !countryId); fillHospitals(); $('#linkModal').modal('show');
    });
    $('#lmAll').on('change', fillHospitals);

    $(document).off('click.fasHr2', '.js-add').on('click.fasHr2', '.js-add', function () {
        var d = $(this).data();
        $('#amName').text(d.name); $('#amCount').text(d.count);
        $('#amNameIn').val(d.name); $('#amCountryIn').val(d.country || '');
        $('#amHospital').val(d.name); if (d.country) { $('#amCountry').val(String(d.country)); } else { $('#amCountry').prop('selectedIndex', -1); }
        $('#addModal').modal('show');
    });
})();
</script>
@endif
