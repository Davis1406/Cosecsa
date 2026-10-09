

<div class="fas-pane-head mb-3"><div class="fas-muted" style="font-size:.85rem;">What Fellows changed on their own profile from the app, and what the Secretariat changed here. Recorded since this section went live.</div></div>
<div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline mb-3">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2 mb-2" placeholder="Fellow or value…" style="max-width:220px;">
                        <select name="field" class="form-control mr-2 mb-2">
                            <option value="">All fields</option>
                            @foreach ($fields as $f)<option value="{{ $f }}" {{ request('field') === $f ? 'selected' : '' }}>{{ $f }}</option>@endforeach
                        </select>
                        <select name="source" class="form-control mr-2 mb-2">
                            <option value="">Fellow &amp; Secretariat</option>
                            <option value="app" {{ request('source') === 'app' ? 'selected' : '' }}>Made by the Fellow</option>
                            <option value="secretariat" {{ request('source') === 'secretariat' ? 'selected' : '' }}>Made by the Secretariat</option>
                        </select>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control mr-1 mb-2" title="From">
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control mr-2 mb-2" title="To">
                        <button class="btn btn-sm btn-dark mr-2 mb-2" type="submit">Filter</button>
                        <a href="{{ route('admin.findasurgeon.changes') }}" class="btn btn-sm btn-outline-secondary mb-2">Reset</a>
                        <span class="ml-auto fas-muted mb-2">{{ number_format($changes->total()) }} change{{ $changes->total() == 1 ? '' : 's' }}</span>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead><tr><th style="white-space:nowrap;">When</th><th>Fellow</th><th>Field</th><th>Before</th><th>After</th><th>By</th></tr></thead>
                            <tbody>
                            @forelse ($changes as $c)
                                <tr>
                                    <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y H:i') }}</td>
                                    <td><strong>{{ $c->fellow_name }}</strong><div class="fas-muted" style="font-size:.78rem;">{{ $c->country_name }}</div></td>
                                    <td>{{ $c->field }}@if ($c->note)<div class="fas-muted" style="font-size:.76rem;">{{ $c->note }}</div>@endif</td>
                                    <td style="max-width:260px;" class="text-break">{{ \Illuminate\Support\Str::limit($c->old_value ?? '—', 140) }}</td>
                                    <td style="max-width:260px;" class="text-break"><strong>{{ \Illuminate\Support\Str::limit($c->new_value ?? '—', 140) }}</strong></td>
                                    <td>@if ($c->source === 'secretariat') <span class="badge badge-warning">Secretariat</span> @else <span class="badge badge-light border">Fellow</span> @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center fas-muted py-4">No changes recorded yet.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{!! $changes->links() !!}</div>
                </div>
            </div>
</div>
