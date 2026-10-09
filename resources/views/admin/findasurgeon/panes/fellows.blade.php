

<div class="fas-pane-head mb-3"></div>
<div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline mb-3">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2 mb-2" placeholder="Name, email or hospital…" style="max-width:260px;">
                        <select name="country_id" class="form-control mr-2 mb-2">
                            <option value="">All countries</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $c->country_name }}</option>
                            @endforeach
                        </select>
                        <select name="usage" class="form-control mr-2 mb-2">
                            <option value="">All Fellows</option>
                            <option value="used" {{ request('usage') === 'used' ? 'selected' : '' }}>Has used the app</option>
                            <option value="never" {{ request('usage') === 'never' ? 'selected' : '' }}>Never signed in</option>
                        </select>
                        <button class="btn btn-sm btn-dark mr-2 mb-2" type="submit">Filter</button>
                        <a href="{{ route('admin.findasurgeon.fellows') }}" class="btn btn-sm btn-outline-secondary mb-2">Reset</a>
                        <span class="ml-auto fas-muted mb-2">{{ number_format($fellows->total()) }} Fellow{{ $fellows->total() == 1 ? '' : 's' }}</span>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead><tr>
                                <th>Fellow</th><th>Email</th><th>Phone</th><th>Country</th><th>Specialty</th><th>Hospital</th>
                                <th class="text-center">Photo</th><th class="text-center">Bio</th><th class="text-center">Edits</th>
                                <th>Last login</th><th>Last active</th>
                            </tr></thead>
                            <tbody>
                            @forelse ($fellows as $f)
                                <tr>
                                    <td><strong>{{ $f->name }}</strong></td>
                                    <td>{{ $f->personal_email ?: '—' }}</td>
                                    <td>{{ $f->phone_number ?: '—' }}</td>
                                    <td>{{ $f->country_name ?: '—' }}</td>
                                    <td>{{ $f->current_specialty ?: '—' }}</td>
                                    <td>{{ $f->organization ?: '—' }}</td>
                                    <td class="text-center">{!! $f->has_photo ? '<i class="fas fa-check text-success"></i>' : '<span class="fas-muted">—</span>' !!}</td>
                                    <td class="text-center">{!! $f->has_bio ? '<i class="fas fa-check text-success"></i>' : '<span class="fas-muted">—</span>' !!}</td>
                                    <td class="text-center">
                                        @if ($f->changes_count) <a href="{{ route('admin.findasurgeon.changes', ['q' => $f->name]) }}">{{ $f->changes_count }}</a>
                                        @else <span class="fas-muted">0</span> @endif
                                    </td>
                                    <td>{{ $f->app_last_login_at ? \Carbon\Carbon::parse($f->app_last_login_at)->format('d M Y H:i') : '—' }}</td>
                                    <td>{{ $f->last_active ? \Carbon\Carbon::parse($f->last_active)->diffForHumans() : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center fas-muted py-4">No Fellows match.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{!! $fellows->links() !!}</div>
                    <p class="fas-muted mt-3 mb-0" style="font-size:.8rem;"><i class="fas fa-info-circle mr-1"></i>Active Fellows only. Sign-in times are recorded from 9 Oct 2026; earlier logins are unknown, so "Never signed in" means none seen since then.</p>
                </div>
            </div>
</div>
