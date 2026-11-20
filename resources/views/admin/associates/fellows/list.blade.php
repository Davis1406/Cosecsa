@extends('layout.app')

@section('content')
@include('admin._dt_prefetch', ['url' => url('admin/associates/fellows/list-rows')])
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/fellows/reports') }}"
                           class="btn btn-info mr-2">
                            <span class="fas fa-chart-bar mr-1"></span> Analytics
                        </a>
                        <a href="{{ url('admin/associates/fellows/import_fellows') }}"
                           class="btn btn-secondary mr-2"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <span class="fas fa-upload mr-1"></span> Upload Fellows
                        </a>
                        <button type="button" class="btn btn-outline-danger mr-2"
                                data-toggle="modal" data-target="#addFromAssociateModal">
                            <span class="fas fa-user-friends mr-1"></span> Add Fellow from Associate
                        </button>
                        <button type="button" class="btn btn-outline-secondary mr-2"
                                data-toggle="modal" data-target="#mergeProfilesModal">
                            <span class="fas fa-code-branch mr-1"></span> Merge Profiles
                        </button>
                        <a href="{{ url('admin/associates/fellows/add') }}"
                           class="btn btn-primary"
                           style="background-color:#a02626; border-color:#a02626;">
                            <span class="fas fa-user-plus mr-1"></span> Add New Fellow
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- Filter Bar --}}
                <div class="card card-outline card-secondary mb-2 shadow-sm">
                    <div class="card-body py-2">
                        @php
                        $filterDefs = [
                            ['id'=>'filterProgramme', 'label'=>'Programme',       'options'=>$filterProgrammes,         'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterCountry',   'label'=>'Country',         'options'=>$filterCountries,          'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterType',      'label'=>'Fellowship Type', 'options'=>$filterTypes,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterYear',      'label'=>'Year',            'options'=>$filterYears,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterGender',    'label'=>'Gender',          'options'=>collect(['Male','Female']), 'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterTags',      'label'=>'Tags',            'options'=>$filterTags,               'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterAlumni',    'label'=>'Alumni',          'options'=>collect(['unique','all','0']), 'default'=>[], 'optLabels'=>[
                                'unique' => 'Unique Alumni (' . number_format($uniqueAlumniCount ?? 0) . ')',
                                'all'    => 'All Alumni (' . number_format($allAlumniCount ?? 0) . ')',
                                '0'      => 'Non-Alumni Only',
                            ]],
                        ];
                        @endphp
                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            @foreach($filterDefs as $fd)
                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                    {{ $fd['label'] }}
                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                </button>
                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                    @if($fd['id'] === 'filterAlumni')
                                    <div class="small text-muted mb-2" style="border-bottom:1px solid #eee;padding-bottom:.4rem;">
                                        "Unique" = one row per person. "All" also lists each additional FCS specialty as its own row, matching the source alumni spreadsheet.
                                    </div>
                                    @endif
                                    @if(collect($fd['options'])->count() > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}">
                                            {{ !empty($fd['optLabels'][$opt]) ? $fd['optLabels'][$opt] : $opt }}
                                        </label>
                                        @endforeach
                                    </div>
                                    <div class="chk-footer">
                                        <a href="#" class="chk-select-all small">All</a>
                                        <a href="#" class="chk-clear small text-danger">Clear</a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                            <small class="text-muted ml-auto" id="filteredCount"></small>
                        </div>
                    </div>
                </div>

                {{-- Certificate QR codes: the QR printed on each certificate. Candidates fee paid, promoted to Part II and outside MCS, and the Fellows by
                     Election, Overseas, Associate and Honorary Fellows of the year. The QR shows when hovering a name; see also Fellow page > Certificate QR. --}}
                <div class="card card-outline card-success mb-2 shadow-sm" id="qrCard">
                    <div class="card-header py-2 d-flex align-items-center flex-wrap" style="gap:.75rem;">
                        <img src="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}" alt="COSECSA" width="42" height="42" style="object-fit:contain;flex:0 0 auto;">
                        <div style="line-height:1.2;">
                            <div style="font-size:1.05rem;font-weight:700;color:#222;">Certificate QR codes</div>
                            <small class="text-muted">College of Surgeons of East, Central and Southern Africa</small>
                        </div>
                        <div class="ml-auto d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <label class="mb-0 small text-muted" for="qrYear" title="The fellowship year of the Fellows listed; the candidates do not depend on it">Fellowship year</label>
                            <select id="qrYear" class="form-control form-control-sm" style="width:auto;">
                                @foreach(range(now()->year, now()->year - 4) as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                            </select>
                            <button type="button" class="btn btn-sm btn-success" id="qrShow"><i class="fas fa-eye mr-1"></i>Show list</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="qrSettingsBtn" data-toggle="modal" data-target="#qrSettingsModal"><i class="fas fa-sliders-h mr-1"></i>QR settings</button>
                        </div>
                    </div>
                    <div class="card-body py-2 d-none" id="qrBody"></div>
                </div>

                {{-- the QR of the name being hovered --}}
                <div id="qrHover" style="display:none;position:fixed;z-index:1080;width:210px;background:#fff;border:1px solid #dfe3ec;border-radius:12px;box-shadow:0 10px 30px rgba(20,30,60,.22);padding:12px;text-align:center;pointer-events:none;">
                    <div id="qrHoverQr" style="line-height:0;"></div>
                    <div id="qrHoverName" style="margin-top:8px;font-weight:700;font-size:.9rem;line-height:1.25;"></div>
                    <div id="qrHoverType" class="small text-muted"></div>
                </div>

                {{-- QR settings: the wording of the public verification page, section by section, the graduation date, the scan counts --}}
                <div class="modal fade" id="qrSettingsModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                        <div class="modal-content">
                            <div class="modal-header py-2">
                                <h5 class="modal-title"><i class="fas fa-sliders-h mr-1"></i> QR settings</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <div id="qsLoading" class="text-muted">Loading…</div>
                                <form id="qsForm" class="d-none" autocomplete="off" onsubmit="return false;">
                                    <p class="small text-muted mb-3">Each box holds the wording exactly as the verification page reads now. Rewrite it, then use <strong>Save this section</strong>; each section saves on its own, so the others are not touched. To see a change, open a person's page from their name.</p>
                                <div class="qs-section border rounded p-3 mb-3" data-section="graduation">
                                    <h6 class="font-weight-bold mb-1">Graduation date</h6>
                                    <div class="small text-muted mb-2"></div>
                                    <div class="d-flex align-items-center flex-wrap mb-1" style="gap:.6rem;">
                                        <input type="date" class="form-control form-control-sm qs-field" id="qs_graduation_date" name="graduation_date" style="width:auto;">
                                        <small class="text-muted" style="flex:1 1 280px;">Shown as "Graduated in" for the Fellows by Examination who became Fellows in that year and have no graduation date of their own (a date set on a fellow's own page wins). Empty = just the year.</small>
                                    </div>
                                    <div class="d-flex align-items-center mt-2" style="gap:.6rem;">
                                        <span class="qs-status small"></span>
                                        <button type="button" class="btn btn-success btn-sm qs-save ml-auto"><i class="fas fa-save mr-1"></i>Save this section</button>
                                    </div>
                                </div>
                                <div class="qs-section border rounded p-3 mb-3" data-section="election">
                                    <h6 class="font-weight-bold mb-1">Fellow by Election</h6>
                                    <div class="small text-muted mb-2">The page shows the name, the sentence, then these three rows in order (a row with no value for that person is left off).</div>
                                        <div class="form-group mb-2"><label class="small text-muted mb-0" for="qs_election_sentence">Sentence under the name</label>
                                            <textarea class="form-control form-control-sm qs-field" id="qs_election_sentence" name="election_sentence" rows="2" maxlength="300"></textarea></div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_election_type">Row 1 · fellowship type</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_election_type" name="election_type" maxlength="60" autocomplete="off"></div>
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_election_specialty">Row 2 · specialty</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_election_specialty" name="election_specialty" maxlength="60" autocomplete="off"></div>
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_election_year">Row 3 · fellowship year</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_election_year" name="election_year" maxlength="60" autocomplete="off"></div>
                                        </div>
                                    <div class="d-flex align-items-center mt-2" style="gap:.6rem;">
                                        <button type="button" class="btn btn-link btn-sm qs-restore p-0 mr-3">Use the standard wording</button><span class="qs-status small"></span>
                                        <button type="button" class="btn btn-success btn-sm qs-save ml-auto"><i class="fas fa-save mr-1"></i>Save this section</button>
                                    </div>
                                </div>
                                <div class="qs-section border rounded p-3 mb-3" data-section="exam">
                                    <h6 class="font-weight-bold mb-1">Fellow by Examination</h6>
                                    <div class="small text-muted mb-2">The page shows the name, the sentence, then these four rows in order.</div>
                                        <div class="form-group mb-2"><label class="small text-muted mb-0" for="qs_exam_sentence">Sentence under the name</label>
                                            <textarea class="form-control form-control-sm qs-field" id="qs_exam_sentence" name="exam_sentence" rows="2" maxlength="300"></textarea></div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_exam_type">Row 1 · fellowship type</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_exam_type" name="exam_type" maxlength="60" autocomplete="off"></div>
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_exam_year">Row 2 · exam year</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_exam_year" name="exam_year" maxlength="60" autocomplete="off"></div>
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_exam_specialty">Row 3 · specialty</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_exam_specialty" name="exam_specialty" maxlength="60" autocomplete="off"></div>
                                            <div class="form-group col-md-6 mb-2"><label class="small text-muted mb-0" for="qs_exam_graduated">Row 4 · graduation (date, or the year)</label>
                                                <input type="text" class="form-control form-control-sm qs-field" id="qs_exam_graduated" name="exam_graduated" maxlength="60" autocomplete="off"></div>
                                        </div>
                                    <div class="d-flex align-items-center mt-2" style="gap:.6rem;">
                                        <button type="button" class="btn btn-link btn-sm qs-restore p-0 mr-3">Use the standard wording</button><span class="qs-status small"></span>
                                        <button type="button" class="btn btn-success btn-sm qs-save ml-auto"><i class="fas fa-save mr-1"></i>Save this section</button>
                                    </div>
                                </div>
                                <div class="qs-section border rounded p-3" data-section="scans">
                                    <h6 class="font-weight-bold mb-2">Scan counts</h6>
                                    <div class="d-flex align-items-center flex-wrap" style="gap:.7rem;">
                                        <span id="qsScans" class="small"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="qsResetAll"><i class="fas fa-undo mr-1"></i>Reset all scan counts</button>
                                    </div>
                                    <div class="small text-muted mt-1">Starts everyone from zero, for example after testing. This cannot be undone. One person's count is reset from the list (the arrow next to their number).</div>
                                    <div class="qs-status small mt-1" id="qsScanStatus"></div>
                                </div>
                                </form>
                            </div>
                            <div class="modal-footer py-2"><button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Fellows</h3></div>
                            <div class="card-body">
                                <table id="fellowstable" class="table table-bordered table-striped"
                                       data-rows-url="{{ url('admin/associates/fellows/list-rows') }}"
                                       data-base-url="{{ url('admin/associates/fellows') }}"
                                       data-app-url="{{ url('') }}"
                                       data-can-transcript="{{ Auth::user()->hasPermission('transcripts.view') ? 1 : 0 }}"
                                       data-can-delete="{{ Auth::user()->isSuperAdmin() ? 1 : 0 }}"
                                       data-can-unapprove="{{ Auth::user()->hasPermission('fellows.manage') ? 1 : 0 }}"
                                       data-unapprove-url="{{ url('admin/associates/fellows/__ID__/unapprove') }}"
                                       data-csrf="{{ csrf_token() }}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Specialty</th>
                                            <th>Fellowship Type</th>
                                            <th>Fellowship Year</th>
                                            <th>Hospital / Organisation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div> <!-- /.container-wrapper -->
        </section>
    </div>
</div>

{{-- Add Fellow from Associate: someone already an Examiner/Country Rep/Member
     gets a fellows record created off their existing login, instead of a
     brand-new duplicate account. Reverse of the "Add Role" flow on the
     fellow view page. --}}
<div class="modal fade" id="addFromAssociateModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="addFromAssociateForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-friends mr-1"></i> Add Fellow from Associate</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="afaAlert" class="alert d-none" role="alert"></div>

                <div class="form-group">
                    <label>Search In</label>
                    <select class="form-control" id="afa_type" name="source_type">
                        <option value="examiner">Examiners</option>
                        <option value="country_rep">Country Representatives</option>
                        <option value="member">Members</option>
                        <option value="programme_director">Programme Directors</option>
                        <option value="trainee">Trainees</option>
                        <option value="candidate">Candidates</option>
                        <option value="trainer">Trainers (ToT)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Search Name / Email <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="afa_search" placeholder="Type a name or email...">
                    <div class="list-group mt-1 d-none afa-results" id="afa_results" style="max-height:180px; overflow-y:auto;"></div>
                    <div class="d-none mt-2" id="afa_selected"></div>
                    <input type="hidden" id="afa_source_id" name="source_id">
                </div>

                <div class="d-none" id="afa_fields">
                    <hr>
                    <p class="small text-muted mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        A Fellow ID is assigned automatically (next number after the last fellow).
                    </p>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Fellowship Type</label>
                            <select class="form-control" name="category_id">
                                <option value="">Select…</option>
                                <option value="5">Fellow by Examination</option>
                                <option value="6">Foundation Fellow</option>
                                <option value="7">Fellow By Election</option>
                                <option value="8">Honorary Fellow (ASEA)</option>
                                <option value="9">Overseas Fellow</option>
                                <option value="10">Honorary Fellow (COSECSA)</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Admission Year</label>
                            <input type="text" class="form-control" name="admission_year" placeholder="e.g. 2015">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Fellowship Year</label>
                            <input type="text" class="form-control" name="fellowship_year" placeholder="e.g. 2018">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="afaSubmit" disabled>Add as Fellow</button>
            </div>
        </form>
    </div>
</div>

{{-- Confirm a tentative person as a Fellow: they are admitted, so they get a login, a Fellow ID and the chosen fellowship type and year --}}
<div class="modal fade" id="confirmTentativeModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="confirmTentativeForm" class="modal-content" autocomplete="off">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-1 text-success"></i> Confirm as Fellow</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="ctAlert" class="alert d-none" role="alert"></div>
                <p class="mb-2"><strong id="ctName"></strong></p>
                <p class="small text-muted">They become a real Fellow (counted in the Fellows total, on the QR list for the year chosen) with a login and the next Fellow ID, and leave the tentative list.</p>
                <input type="hidden" id="ctId">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Fellowship Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="ctCategory" name="category_id">
                            <option value="2">Associate Fellow</option>
                            <option value="7">Fellow By Election</option>
                            <option value="9">Overseas Fellow</option>
                            <option value="8">Honorary Fellow (ASEA)</option>
                            <option value="10">Honorary Fellow (COSECSA)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Fellowship Year <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ctYear" name="fellowship_year" maxlength="4" placeholder="e.g. 2026">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email (their login) <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="ctEmail" name="email" placeholder="name@example.org">
                </div>
                <div class="form-group mb-0">
                    <label>Country <span class="text-danger">*</span></label>
                    <select class="form-control" id="ctCountry" name="country_id">
                        <option value="">Select…</option>
                        @foreach($fellowCountries ?? [] as $c)<option value="{{ $c->id }}">{{ $c->country_name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success" id="ctSubmit"><i class="fas fa-check mr-1"></i>Confirm as Fellow</button>
            </div>
        </form>
    </div>
</div>

{{-- Merge Profiles: the same person held as a Fellow and under another associate type. Pick both, compare value by value, choose which
     value to keep where they differ; the other profile then moves to the Fellow's login and its duplicate login is retired. --}}
<div class="modal fade" id="mergeProfilesModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-code-branch mr-1"></i> Merge Profiles</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="mpAlert" class="alert d-none" role="alert"></div>
                <p class="small text-muted mb-2">For one person who has a Fellow profile and a second profile under another type (member, trainee, examiner…).
                    The Fellow profile stays; you choose which value to keep wherever the two differ.</p>

                <div class="form-group mb-2">
                    <label class="mb-1">1. The Fellow <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="mp_fellow_search" placeholder="Type a name, email or Fellow ID…" autocomplete="off">
                    <div class="list-group mt-1 d-none" id="mp_fellow_results" style="max-height:180px; overflow-y:auto;"></div>
                    <div class="d-none mt-2" id="mp_fellow_selected"></div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4 mb-2">
                        <label class="mb-1">2. Their other profile is a</label>
                        <select class="form-control" id="mp_type">
                            <option value="member">Member</option>
                            <option value="trainee">Trainee</option>
                            <option value="candidate">Candidate</option>
                            <option value="examiner">Examiner</option>
                            <option value="country_rep">Country Representative</option>
                            <option value="programme_director">Programme Director</option>
                            <option value="trainer">Trainer (ToT)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8 mb-2">
                        <label class="mb-1">Search that profile <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="mp_other_search" placeholder="Type a name or email…" autocomplete="off" disabled>
                        <div class="list-group mt-1 d-none" id="mp_other_results" style="max-height:180px; overflow-y:auto;"></div>
                        <div class="d-none mt-2" id="mp_other_selected"></div>
                    </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="mpCompare" disabled><i class="fas fa-columns mr-1"></i>Compare the two profiles</button>

                <div class="d-none" id="mp_compare"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="mpMerge" disabled><i class="fas fa-code-branch mr-1"></i>Merge profiles</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* ── Checkbox filter dropdowns ── */
    .chk-filter-wrap { position: relative; display: inline-block; }
    .chk-filter-panel {
        position: absolute; top: calc(100% + 4px); left: 0; z-index: 1055;
        background: #fff; border: 1px solid #ced4da; border-radius: 6px;
        min-width: 190px; max-width: 260px; padding: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .chk-list { max-height: 220px; overflow-y: auto; }
    .chk-item {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 2px; font-size: .82rem; font-weight: normal;
        cursor: pointer; white-space: nowrap; margin: 0;
    }
    .chk-item:hover { background: #f8f0f0; border-radius: 4px; }
    .chk-item input[type="checkbox"] { margin: 0; cursor: pointer; accent-color: #a02626; }
    .chk-footer {
        display: flex; justify-content: space-between;
        border-top: 1px solid #eee; margin-top: 6px; padding-top: 5px;
        font-size: .78rem;
    }
    .chk-footer a { color: #6c757d; }
    .chk-footer a:hover { color: #a02626; text-decoration: none; }
    .chk-filter-btn { white-space: nowrap; }
    /* ── Table ── */
    #fellowstable td { vertical-align: middle; }
    .fellow-spec-count {
        background:#a02626; color:#fff; border-radius:50%; width:14px; height:14px;
        font-size:.6rem; font-weight:700; display:inline-flex; align-items:center;
        justify-content:center; line-height:1; flex-shrink:0;
    }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    #fellowFilters label { color: #555; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return this.value; }).get();
    }

    function updateBadge(filterId) {
        var checked = getChecked(filterId);
        var $badge  = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        if (checked.length) $badge.text(checked.length).show();
        else $badge.hide();
    }

    function redraw() {
        var dt   = $('#fellowstable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#filteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    // DataTable custom search filter (rows are JSON objects, see custom.js)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (settings.nTable.id !== 'fellowstable') return true;

        return matchesSelected(row);
    });

    // the usual filters
    function matchesSelected(row) {
        var chkProgramme = getChecked('filterProgramme');
        var chkCountry   = getChecked('filterCountry');
        var chkType      = getChecked('filterType');
        var chkYear      = getChecked('filterYear');
        var chkGender    = getChecked('filterGender');
        var chkAlumni    = getChecked('filterAlumni');
        if (chkProgramme.length && chkProgramme.indexOf(String(row.programme || '')) === -1) return false;
        if (chkCountry.length   && chkCountry.indexOf(String(row.country     || '')) === -1) return false;
        if (chkType.length      && chkType.indexOf(String(row.ftype          || '')) === -1) return false;
        if (chkYear.length      && chkYear.indexOf(String(row.year           || '')) === -1) return false;
        if (chkGender.length    && chkGender.indexOf(String(row.gender       || '')) === -1) return false;
        var chkTags = getChecked('filterTags');
        if (chkTags.length) {
            var rowTags = row.tags || [];
            if (!chkTags.some(function (t) { return rowTags.indexOf(t) !== -1; })) return false;
        }
        if (chkAlumni.length) {
            var wantsYes = chkAlumni.indexOf('unique') !== -1 || chkAlumni.indexOf('all') !== -1;
            var wantsNo  = chkAlumni.indexOf('0') !== -1;
            var rowAlumni = String(row.alumni);
            if (!((wantsYes && rowAlumni === '1') || (wantsNo && rowAlumni === '0'))) return false;
        }
        return true;
    }

    // ── "All Alumni" split-specialty rows — added/removed from the table
    // (not just hidden), so the default row count never changes unless this
    // view is actively selected. ──
    var extraRowsAdded  = false;

    // Additional-specialty rows (loaded with the table data) as row objects.
    function extraRowData(r) {
        return {
            kind: 'extra', id: r.fellow_id, name: r.name, email: r.email,
            cid: r.country_id, country: r.country_name, pid: null, programme: '',
            specialty: (r.specialty || '').replace(/^FCS\s+/i, '') || null,
            ftype: 'Fellow by Examination', year: r.year, hospital: null,
            gender: '', alumni: 1, specs: 1
        };
    }

    function syncExtraAlumniRows() {
        var dt = $('#fellowstable').DataTable();
        var wantsAll = getChecked('filterAlumni').indexOf('all') !== -1;
        if (wantsAll && !extraRowsAdded) {
            dt.rows.add(($('#fellowstable').data('extra-rows') || []).map(extraRowData)).draw(false);
            extraRowsAdded = true;
        } else if (!wantsAll && extraRowsAdded) {
            dt.rows(function (idx, d) { return d.kind === 'extra'; }).remove().draw(false);
            extraRowsAdded = false;
        }
    }

    // Panel open/close
    $(document).on('click', '.chk-filter-btn', function (e) {
        e.stopPropagation();
        var filterId = $(this).data('filter');
        var $panel   = $('#' + filterId + '-panel');
        $('.chk-filter-panel').not($panel).hide();
        $panel.toggle();
    });
    $(document).on('click', '.chk-filter-panel', function (e) { e.stopPropagation(); });
    $(document).on('click', function () { $('.chk-filter-panel').hide(); });

    // In-panel search
    $(document).on('input', '.chk-search', function () {
        var q = $(this).val().toLowerCase();
        $(this).closest('.chk-filter-panel').find('.chk-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });

    // Checkbox change
    $(document).on('change', '.chk-option', function () {
        var filterId = $(this).data('filter');
        // "Unique Alumni" and "All Alumni" are two views of the same set, not
        // combinable filters — selecting one clears the other.
        if (filterId === 'filterAlumni' && this.checked && (this.value === 'unique' || this.value === 'all')) {
            var other = this.value === 'unique' ? 'all' : 'unique';
            $('.chk-option[data-filter="filterAlumni"][value="' + other + '"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Select All / Clear per panel
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        if (filterId === 'filterAlumni') {
            // "all" supersedes "unique" when both would otherwise be checked
            $('.chk-option[data-filter="filterAlumni"][value="unique"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });
    $(document).on('click', '.chk-clear', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-option').prop('checked', false);
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Clear All
    $('#btnClearFilters').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        syncExtraAlumniRows();
        redraw();
        $('#filteredCount').text('');
    });
});
</script>
@endpush

@push('scripts')
<script>
$(function () {
    // ── Add Fellow from Associate ───────────────────────────────────────────
    var afaTimer = null;

    function afaResetSelection() {
        $('#afa_source_id').val('');
        $('#afa_selected').addClass('d-none').empty();
        $('#afa_fields').addClass('d-none');
        $('#afaSubmit').prop('disabled', true);
    }

    $('#addFromAssociateModal').on('hidden.bs.modal', function () {
        $('#afa_search').val('');
        $('#afa_results').addClass('d-none').empty();
        $('#afaAlert').addClass('d-none');
        afaResetSelection();
    });

    $('#afa_type').on('change', afaResetSelection);

    $('#afa_search').on('input', function () {
        var q = $(this).val().trim();
        afaResetSelection();
        clearTimeout(afaTimer);

        if (q.length < 2) {
            $('#afa_results').addClass('d-none').empty();
            return;
        }

        afaTimer = setTimeout(function () {
            $.get('{{ route("fellows.search-associates") }}', { type: $('#afa_type').val(), q: q })
                .done(function (res) {
                    var results = res.results || [];
                    var $list = $('#afa_results').empty();

                    if (!results.length) {
                        $list.append('<div class="list-group-item small text-muted">No matches (or already a fellow).</div>');
                    } else {
                        results.forEach(function (r) {
                            var name = r.name || '';
                            var $item = $('<button type="button" class="list-group-item list-group-item-action"></button>')
                                .text(name + (r.email ? ' — ' + r.email : '') + (r.country ? ' (' + r.country + ')' : ''))
                                .data('id', r.id)
                                .data('label', name);
                            $list.append($item);
                        });
                    }
                    $list.removeClass('d-none');
                });
        }, 300);
    });

    $(document).on('click', '#afa_results .list-group-item-action', function () {
        var id = $(this).data('id');
        var label = $(this).data('label');

        $('#afa_source_id').val(id);
        $('#afa_selected').removeClass('d-none')
            .html('<span class="badge badge-secondary p-2"><i class="fas fa-check mr-1"></i>' +
                  $('<div>').text(label).html() + '</span>');
        $('#afa_results').addClass('d-none').empty();
        $('#afa_fields').removeClass('d-none');
        $('#afaSubmit').prop('disabled', false);
    });

    $('#addFromAssociateForm').on('submit', function (e) {
        e.preventDefault();
        if (!$('#afa_source_id').val()) return;

        var $btn = $('#afaSubmit').prop('disabled', true).text('Adding...');
        var $alert = $('#afaAlert').addClass('d-none');

        $.ajax({
            url: '{{ route("fellows.from-associate") }}',
            method: 'POST',
            data: $(this).serialize(),
        }).done(function (res) {
            $alert.removeClass('d-none alert-danger').addClass('alert-success').text(res.message || 'Fellow added.');
            setTimeout(function () {
                window.location.href = '{{ url("admin/associates/fellows/view") }}/' + res.fellow_id;
            }, 900);
        }).fail(function (xhr) {
            var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Failed to add fellow.';
            $alert.removeClass('d-none alert-success').addClass('alert-danger').text(msg);
            $btn.prop('disabled', false).text('Add as Fellow');
        });
    });
});
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/vendor/qrcode-generator-1.4.4.min.js') }}"></script>
<script src="{{ asset('js/vendor/jszip-3.10.1.min.js') }}"></script>
<script src="{{ asset('js/fellow-qr.js') }}?v={{ filemtime(public_path('js/fellow-qr.js')) }}"></script>   {{-- the version changes with the file, so a browser never runs an old copy against this page --}}
<script>
// Certificate QR codes (including the TENTATIVE people: expected at a coming meeting, not Fellows or in any total until approved): the candidates with Fee Paid = Paid, Part II = Promoted and any programme but MCS (those repeating exams included), plus the Fellows by Election, Overseas, Associate and Honorary Fellows of the chosen year. The list
// is plain text; a name's QR appears while hovering it. The QR codes are drawn here in the browser; downloads are a
// >= 4000 px PNG and a vector SVG per person, or those currently listed in one ZIP.
(function () {
    var batchUrl = @json(url('admin/associates/fellows/certificate-batch')),
        profile  = { fellow: @json(url('admin/associates/fellows/view')), candidate: @json(url('admin/associates/candidates/view')) },
        verifyBase = @json(rtrim(config('app.url'), '/') . '/verify/'),
        settingsUrl = @json(url('admin/associates/fellows/qr-settings')), resetUrl = @json(url('admin/associates/fellows/qr-scans/reset')), csrf = @json(csrf_token()),
        approveUrl = @json(url('admin/associates/fellows/__ID__/approve')), removeUrl = @json(url('admin/associates/fellows/tentative/delete/__ID__'));
    var $body = $('#qrBody'), $show = $('#qrShow'), $hover = $('#qrHover'), people = [], year = '', made = {}, hideTimer = null, totalEntries = 0, page = 1, PER_PAGE = 25;

    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
    function qrOf(p) { return made[p.key] || (made[p.key] = FellowQR.make(verifyBase + p.token)); }   // drawn only when needed
    function typeBadge(t, kind) {
        var c = /election/i.test(t) ? ['#fff4d6', '#8a6100']
              : /overseas/i.test(t) ? ['#e6f6ec', '#1d6b3a']
              : /honorary/i.test(t) ? ['#f1e8fa', '#5b2d8a']
              : /associate/i.test(t) ? ['#fde9e4', '#a23b1d']
              : ['#e3f0fb', '#1a5d96'];
        return '<span class="badge" style="background:' + c[0] + ';color:' + c[1] + ';font-weight:600;font-size:.76rem;padding:.35em .6em;">' + esc(t) + '</span>';
    }
    function when(t) { return t ? t.replace(/:\d\d$/, '') : ''; }
    function personOf(el) { var k = $(el).closest('tr').data('key'); return people.filter(function (p) { return p.key === k; })[0]; }
    // the file is named after the person: First Last, or First Middle Last (see FellowQR.fileBase)
    function fileName(p, used) { return FellowQR.fileBase(p.name, used); }

    // ── the list, with search and filters ──
    function shown() {
        var q = $.trim($('#qrSearch').val()).toLowerCase(), t = $('#qrType').val(), g = $('#qrProg').val(), st = $('#qrStatus').val();
        return people.filter(function (p) {
            return (!t || p.type === t) && (!g || p.trained_in === g) && (!st || (st === 'tentative') === !!p.tentative) && (!q || (p.name + ' ' + (p.country || '')).toLowerCase().indexOf(q) !== -1);
        });
    }
    function draw() {
        var list = shown(), pages = Math.max(1, Math.ceil(list.length / PER_PAGE));
        page = Math.min(Math.max(1, page), pages);
        var from = (page - 1) * PER_PAGE, rows = list.slice(from, from + PER_PAGE).map(function (p, i) {
            return '<tr data-key="' + p.key + '"><td class="text-muted">' + (from + i + 1) + '</td>' +
                '<td>' + (profile[p.kind]
                    ? '<a href="' + esc(profile[p.kind]) + '/' + p.id + '" class="qr-name" style="color:#222;font-weight:600;">' + esc(p.name) + '</a>'
                    : '<span class="qr-name" style="color:#222;font-weight:600;cursor:default;">' + esc(p.name) + '</span>') + '</td>' +
                '<td>' + typeBadge(p.type, p.kind) + (p.tentative ? ' <span class="badge" style="background:#fff4d6;color:#8a6100;font-weight:600;font-size:.76rem;padding:.35em .6em;" title="Expected at a coming meeting: not a Fellow, not in the Fellows table or any total, and the QR page does not verify until approved">Tentative</span>' : '') + '</td>' +
                '<td>' + esc(p.trained_in || '—') + '</td><td>' + esc(p.country || '—') + '</td>' +
                '<td class="small text-muted">' + esc(p.basis) + '</td>' +
                '<td class="text-center text-nowrap" title="' + (p.last_scan ? 'Last scanned ' + esc(when(p.last_scan)) : 'Not scanned yet') + '">' + (p.scans ? '<strong style="color:#2e8b57;">' + p.scans + '</strong> <a href="#" class="qr-reset text-muted" title="Reset this person\'s scan count to zero"><i class="fas fa-undo-alt"></i></a>' : '<span class="text-muted">0</span>') + '</td>' +
                '<td class="text-nowrap"><button type="button" class="btn btn-xs btn-outline-secondary qr-png" title="PNG, 4000 px or more"><i class="fas fa-download"></i> PNG</button> ' +
                '<button type="button" class="btn btn-xs btn-outline-secondary qr-svg" title="Vector, sharp at any size"><i class="fas fa-download"></i> SVG</button> ' +
                '<button type="button" class="btn btn-xs btn-outline-secondary qr-peek" title="Show the QR"><i class="fas fa-qrcode"></i></button></td>' +
                '<td class="text-nowrap">' + (!p.tentative ? '' : (p.kind === 'fellow'
                    ? '<button type="button" class="btn btn-xs btn-success qr-approve" title="Approve: they become a counted Fellow and their QR page verifies"><i class="fas fa-check mr-1"></i>Approve</button>'
                    : '<button type="button" class="btn btn-xs btn-success qr-confirm" title="Confirm as a Fellow: needs an email and country"><i class="fas fa-check mr-1"></i>Confirm as Fellow</button> ' +
                      '<button type="button" class="btn btn-xs btn-outline-danger qr-remove" title="Take them off the tentative list"><i class="fas fa-trash"></i></button>')) + '</td></tr>';
        }).join('');
        $('#qrRows').html(rows || '<tr><td colspan="9" class="text-center text-muted py-3">Nobody matches.</td></tr>');
        // the same wording as the Candidates table: "Showing 1 to 25 of 226 entries (filtered from 1,460 total entries)"
        var total = Math.max(totalEntries, people.length), nf = function (n) { return Number(n).toLocaleString('en-US'); };
        $('#qrCount').text(list.length
            ? 'Showing ' + nf(from + 1) + ' to ' + nf(Math.min(from + PER_PAGE, list.length)) + ' of ' + nf(list.length) + ' entries' + (list.length !== total ? ' (filtered from ' + nf(total) + ' total entries)' : '')
            : 'Showing 0 entries' + (total ? ' (filtered from ' + nf(total) + ' total entries)' : ''));
        $('#qrPrev').prop('disabled', page <= 1); $('#qrNext').prop('disabled', page >= pages); $('#qrPage').text('Page ' + page + ' of ' + pages);
        $('#qrZipPng, #qrZipSvg').prop('disabled', !list.length);
    }
    function render(data) {
        people = data.fellows || []; year = data.year; totalEntries = data.total_entries || people.length; page = 1; made = {};
        $hover.hide();
        if (!people.length) {
            $body.html('<p class="text-muted mb-0 py-2">Nobody yet: no candidate is fee paid, promoted to Part II and outside MCS, and no Fellow by Election, Overseas, Associate or Honorary Fellow has fellowship year ' + esc(year) + '.</p>').removeClass('d-none');
            return;
        }
        var nc = people.filter(function (p) { return p.kind === 'candidate'; }).length;
        var nt = people.filter(function (p) { return p.tentative; }).length;
        var byType = {};
        people.forEach(function (p) { if (p.kind !== 'candidate' && !p.tentative) { byType[p.type] = (byType[p.type] || 0) + 1; } });
        var fellowParts = Object.keys(byType).sort().map(function (k) { return byType[k] + ' ' + esc(k); }).join(', ');
        var types = {}, progs = {};
        people.forEach(function (p) { types[p.type] = 1; if (p.trained_in) progs[p.trained_in] = 1; });
        function opts(o) { return Object.keys(o).sort().map(function (k) { return '<option value="' + esc(k) + '">' + esc(k) + '</option>'; }).join(''); }
        $body.html(
            '<p class="small text-muted mb-2"><strong>' + people.length + ' people for ' + esc(year) + '</strong>: ' + nc + ' Fellow' + (nc === 1 ? '' : 's') + ' by Examination (candidates with Fee Paid, promoted to Part II, any programme except MCS; those repeating exams are included)' + (fellowParts ? ' and, by fellowship year ' + esc(year) + ': ' + fellowParts : '') + (nt ? ', plus <strong>' + nt + ' tentative</strong> (expected at the coming meeting, approved afterwards: not Fellows yet, not in the Fellows table or any total)' : '') + '. <strong>Hover a name to see their QR.</strong> Each QR opens that person\'s public verification page.</p>' +
            '<div class="d-flex align-items-center flex-wrap mb-2" style="gap:.5rem;">' +
              '<input type="search" id="qrSearch" class="form-control form-control-sm" style="width:210px;" placeholder="Search name or country">' +
              '<select id="qrType" class="form-control form-control-sm" style="width:auto;"><option value="">All types</option>' + opts(types) + '</select>' +
              '<select id="qrProg" class="form-control form-control-sm" style="width:auto;"><option value="">All programmes</option>' + opts(progs) + '</select>' +
              (nt ? '<select id="qrStatus" class="form-control form-control-sm" style="width:auto;"><option value="">All (approved and tentative)</option><option value="tentative">Tentative only</option><option value="approved">Approved only</option></select>' : '<span id="qrStatus" class="d-none"></span>') +
              '<span class="ml-auto"><button type="button" class="btn btn-sm btn-outline-success" id="qrZipPng" title="Everything listed, one PNG each (4000 px or more), as one ZIP; can be large"><i class="fas fa-file-archive mr-1"></i>ZIP of PNGs</button> ' +
              '<button type="button" class="btn btn-sm btn-outline-success" id="qrZipSvg" title="Everything listed, one vector SVG each, as one ZIP"><i class="fas fa-file-archive mr-1"></i>ZIP of SVGs</button></span>' +
            '</div>' +
            '<div id="qrNote"></div>' +
            '<div style="max-height:62vh;overflow:auto;border:1px solid #eee;border-radius:6px;"><table class="table table-sm table-hover mb-0" id="qrTable"><thead style="position:sticky;top:0;background:#f7f8fa;z-index:1;"><tr><th>#</th><th>Name</th><th>Type</th><th>Programme</th><th>Country</th><th>Included because</th><th class="text-center">Scans</th><th>Download</th><th>Tentative</th></tr></thead><tbody id="qrRows"></tbody></table></div>' +
            '<div class="d-flex align-items-center flex-wrap mt-2" style="gap:.5rem;"><small class="text-muted" id="qrCount"></small>' +
              '<span class="ml-auto d-flex align-items-center" style="gap:.5rem;"><button type="button" class="btn btn-sm btn-outline-secondary" id="qrPrev">Previous</button><small class="text-muted" id="qrPage"></small><button type="button" class="btn btn-sm btn-outline-secondary" id="qrNext">Next</button></span></div>'
        ).removeClass('d-none');
        draw();
    }

    $show.on('click', function () {
        $show.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading…');
        $.getJSON(batchUrl, { year: $('#qrYear').val() })
            .done(render)
            .fail(function (xhr) { $body.html('<div class="alert alert-danger mb-0">' + esc((xhr.responseJSON && xhr.responseJSON.message) || 'The list could not be loaded.') + '</div>').removeClass('d-none'); })
            .always(function () { $show.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i>Refresh'); });
    });
    $body.on('input change', '#qrSearch, #qrType, #qrProg, #qrStatus', function () { page = 1; draw(); });
    $body.on('click', '#qrPrev', function () { page--; draw(); $('#qrTable').parent().scrollTop(0); });
    $body.on('click', '#qrNext', function () { page++; draw(); $('#qrTable').parent().scrollTop(0); });

    // ── tentative people: approve (an existing Fellow record), confirm (no record yet: opens the form), remove ──
    function qrNote(kind, msg) { $('#qrNote').html('<div class="alert alert-' + kind + ' py-2 mb-2 small">' + esc(msg) + '</div>'); }
    // a second click within four seconds does it (a question on the button itself, not a browser pop-up)
    function twice($b, label, go) {
        if ($b.data('armed')) { clearTimeout($b.data('armed')); $b.data('armed', null); go(); return; }
        var html = $b.html();
        $b.html(label).addClass('btn-warning').data('armed', setTimeout(function () { $b.html(html).removeClass('btn-warning').data('armed', null); }, 4000));
    }
    $body.on('click', '.qr-approve', function (e) {
        e.stopPropagation();
        var p = personOf(this), $b = $(this);
        twice($b, 'Sure? Approve', function () {
            $b.prop('disabled', true);
            $.post(approveUrl.replace('__ID__', p.id), { _token: csrf })
                .done(function (d) { $show.trigger('click'); setTimeout(function () { qrNote('success', d.message || 'Approved.'); }, 600); })
                .fail(function (xhr) { $b.prop('disabled', false); qrNote('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'They could not be approved.'); });
        });
    });
    $body.on('click', '.qr-confirm', function (e) {
        e.stopPropagation();
        var p = personOf(this);
        window.openConfirmTentative({ id: p.id, name: p.name, type: p.type, year: p.fellowship_year });
    });
    $body.on('click', '.qr-remove', function (e) {
        e.stopPropagation();
        var p = personOf(this), $b = $(this);
        twice($b, 'Sure? Remove', function () {
            $b.prop('disabled', true);
            $.get(removeUrl.replace('__ID__', p.id))
                .done(function () { $show.trigger('click'); })
                .fail(function () { $b.prop('disabled', false); qrNote('danger', 'They could not be removed.'); });
        });
    });

    // ── the QR while hovering a name (or after tapping the QR button, for touch screens) ──
    function showHover(el, p) {
        clearTimeout(hideTimer);
        $('#qrHoverQr').html(qrOf(p).svg()); $('#qrHoverName').text(p.name); $('#qrHoverType').text([p.type, p.trained_in].filter(Boolean).join(' · '));
        $hover.show();
        var r = el.getBoundingClientRect(), w = $hover.outerWidth(), h = $hover.outerHeight(), x = r.right + 14, y = r.top + r.height / 2 - h / 2;
        if (x + w > window.innerWidth - 8) x = Math.max(8, r.left - w - 14);
        $hover.css({ left: x + 'px', top: Math.min(Math.max(8, y), window.innerHeight - h - 8) + 'px' });
    }
    function hideHover() { hideTimer = setTimeout(function () { $hover.hide(); }, 80); }
    $body.on('mouseenter', '.qr-name', function () { showHover(this, personOf(this)); });
    $body.on('mouseleave', '.qr-name', hideHover);
    $body.on('click', '.qr-peek', function (e) {
        e.stopPropagation();
        var p = personOf(this), shownNow = $hover.is(':visible') && $('#qrHoverName').text() === p.name;
        if (shownNow) { $hover.hide(); } else { showHover($(this).closest('tr').find('.qr-name')[0], p); }
    });
    $(document).on('click', function () { $hover.hide(); });
    $body.on('scroll', function () { $hover.hide(); });

    // ── QR settings: the wording of the page, section by section; the graduation date; scan counts ──
    var $qs = $('#qrSettingsModal'), qsDefaults = {}, qsBase = {};
    function qsSection(el) { return $(el).closest('.qs-section'); }
    function qsFields($sec) { return $sec.find('.qs-field'); }
    function qsStatus($sec, text, cls) { $sec.find('.qs-status').first().attr('class', 'qs-status small ' + (cls || 'text-muted')).text(text || ''); }
    function qsDirty($sec) {
        var changed = false;
        qsFields($sec).each(function () { if ($.trim($(this).val()) !== (qsBase[this.name] || '')) changed = true; });
        return changed;
    }
    function qsFill($sec, values) {
        qsFields($sec).each(function () { var v = values[this.name] || ''; $(this).val(v); qsBase[this.name] = v; });
        qsStatus($sec, '');
    }
    function qsValues(d) { return d.values || $.extend({}, d.defaults, d.custom); }
    $qs.on('show.bs.modal', function () {
        $('#qsForm').addClass('d-none'); $('#qsLoading').removeClass('d-none').text('Loading…'); $('#qsScanStatus').text('');
        $.getJSON(settingsUrl).done(function (d) {
            qsDefaults = d.defaults || {};
            var v = qsValues(d);
            $('#qsForm .qs-section').each(function () { qsFill($(this), v); });
            $('#qsScans').text(d.scans_total + ' scan' + (d.scans_total === 1 ? '' : 's') + ' recorded in all');
            $('#qsLoading').addClass('d-none'); $('#qsForm').removeClass('d-none');
        }).fail(function (xhr) { $('#qsLoading').text((xhr.responseJSON && xhr.responseJSON.message) || 'The settings could not be loaded.'); });
    });
    $qs.on('input change', '.qs-field', function () {
        var $sec = qsSection(this);
        qsStatus($sec, qsDirty($sec) ? 'Unsaved changes' : '', 'text-warning');
    });
    // put this section's standard wording in the boxes (saved only when the section is saved)
    $qs.on('click', '.qs-restore', function () {
        var $sec = qsSection(this);
        qsFields($sec).each(function () { $(this).val(qsDefaults[this.name] || ''); });
        qsStatus($sec, qsDirty($sec) ? 'Standard wording restored: save the section to keep it' : 'Already the standard wording', 'text-warning');
    });
    // save only this section's fields; the other sections are not sent and keep what is typed in them
    $qs.on('click', '.qs-save', function () {
        var $sec = qsSection(this), btn = $(this).prop('disabled', true), data = { _token: csrf };
        qsFields($sec).each(function () { data[this.name] = $.trim($(this).val()); });
        qsStatus($sec, 'Saving…');
        $.post(settingsUrl, data).done(function () {
            // show what the page now says (a box that was emptied shows the standard wording again)
            $.getJSON(settingsUrl).done(function (d) { qsFill($sec, qsValues(d)); qsStatus($sec, 'Saved', 'text-success'); });
        }).fail(function (xhr) {
            var j = xhr.responseJSON || {}, first = j.errors ? j.errors[Object.keys(j.errors)[0]][0] : null;
            qsStatus($sec, first || j.message || 'This section could not be saved.', 'text-danger');
        }).always(function () { btn.prop('disabled', false); });
    });
    function afterReset(removed, personKey) {
        people.forEach(function (p) { if (!personKey || p.key === personKey) { p.scans = 0; p.last_scan = null; } });
        if (people.length) draw();
        return removed;
    }
    $('#qsResetAll').on('click', function () {
        if (!confirm('Reset ALL scan counts to zero?\n\nEvery recorded scan is removed. This cannot be undone.')) return;
        var btn = $(this).prop('disabled', true);
        $.post(resetUrl, { _token: csrf, scope: 'all' }).done(function (d) {
            afterReset(d.removed); $('#qsScans').text('0 scans recorded in all'); $('#qsScanStatus').attr('class', 'qs-status small mt-1 text-success').text(d.message);
        }).fail(function (xhr) { $('#qsScanStatus').attr('class', 'qs-status small mt-1 text-danger').text((xhr.responseJSON && xhr.responseJSON.message) || 'The scan counts could not be reset.'); })
          .always(function () { btn.prop('disabled', false); });
    });
    $body.on('click', '.qr-reset', function (e) {
        e.preventDefault(); e.stopPropagation();
        var p = personOf(this);
        if (!confirm('Reset the scan count of ' + p.name + ' to zero? This cannot be undone.')) return;
        $.post(resetUrl, { _token: csrf, subject_type: p.kind, subject_id: p.id })
            .done(function () { afterReset(0, p.key); })
            .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'The scan count could not be reset.'); });
    });

    // ── downloads ──
    $body.on('click', '.qr-png', function () {
        var p = personOf(this), btn = $(this).prop('disabled', true);
        qrOf(p).png().then(function (b) { FellowQR.save(b, fileName(p) + '.png'); }).finally(function () { btn.prop('disabled', false); });
    });
    $body.on('click', '.qr-svg', function () {
        var p = personOf(this);
        FellowQR.save(new Blob([qrOf(p).svg(1024)], { type: 'image/svg+xml' }), fileName(p) + '.svg');
    });

    // Everything currently listed in one ZIP (with a CSV of who and which link): PNGs (large) or SVGs (small).
    async function zipListed(kind, $btn) {
        var list = shown(), label = $btn.html(), z = new JSZip(), used = {}, csv = [['Name', 'Type', 'Programme', 'Country', 'Included because', 'Status', 'Verification link', 'File']];
        if (kind === 'png' && list.length > 60 && !confirm(list.length + ' PNGs of 4000 px each make a large ZIP (roughly ' + Math.round(list.length * 0.35) + ' MB) and may take a minute. Continue?\n\nTip: the SVG ZIP is tiny and prints sharper.')) return;
        $('#qrZipPng, #qrZipSvg').prop('disabled', true);
        try {
            for (var i = 0; i < list.length; i++) {
                var p = list[i], base = fileName(p, used), q = qrOf(p);
                $btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>' + (i + 1) + ' of ' + list.length + '…');
                if (kind === 'png') { z.file(base + '.png', await q.png()); } else { z.file(base + '.svg', q.svg(1024)); }
                csv.push([p.name, p.type, p.trained_in, p.country, p.basis, p.tentative ? 'Tentative' : 'Approved', verifyBase + p.token, base + '.' + kind].map(function (v) { return '"' + String(v == null ? '' : v).replace(/"/g, '""') + '"'; }));
            }
            z.file('qr-codes.csv', '\ufeff' + csv.map(function (r) { return r.join(','); }).join('\r\n'));
            FellowQR.save(await z.generateAsync({ type: 'blob', compression: 'STORE' }), 'cosecsa-certificate-qr-' + year + '-' + kind + '.zip');
        } catch (e) {
            alert('The ZIP could not be made: ' + (e && e.message ? e.message : e));
        }
        $btn.html(label);
        $('#qrZipPng, #qrZipSvg').prop('disabled', !shown().length);
    }
    $body.on('click', '#qrZipPng', function () { zipListed('png', $(this)); });
    $body.on('click', '#qrZipSvg', function () { zipListed('svg', $(this)); });
})();
</script>
@endpush

@push('scripts')
<script>
// Merge Profiles: pick the Fellow and their other profile, compare, choose, merge. The API does the work and keeps a backup.
$(function () {
    var urls = {
        fellowSearch: @json(route('fellows.search')),
        otherSearch:  @json(url('admin/associates/fellows/__ID__/merge/search')),
        preview:      @json(url('admin/associates/fellows/__ID__/merge/preview')),
        merge:        @json(url('admin/associates/fellows/__ID__/merge')),
        view:         @json(url('admin/associates/fellows/view')),
        csrf:         @json(csrf_token())
    };
    var fellowId = null, otherId = null, timer = null, comparing = false;
    function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }
    function at(u) { return u.replace('__ID__', fellowId); }
    function alertBox(kind, msg) { $('#mpAlert').removeClass('d-none alert-danger alert-success alert-warning').addClass('alert-' + kind).text(msg); }
    function resetCompare() { $('#mp_compare').addClass('d-none').empty(); $('#mpMerge').prop('disabled', true); comparing = false; }
    function readyToCompare() { $('#mpCompare').prop('disabled', !(fellowId && otherId)); }
    function resetOther() {
        otherId = null; $('#mp_other_selected').addClass('d-none').empty(); $('#mp_other_results').addClass('d-none').empty(); $('#mp_other_search').val('');
        resetCompare(); readyToCompare();
    }

    $('#mergeProfilesModal').on('hidden.bs.modal', function () {
        fellowId = null; $('#mp_fellow_search').val(''); $('#mp_fellow_results').addClass('d-none').empty(); $('#mp_fellow_selected').addClass('d-none').empty();
        $('#mpAlert').addClass('d-none'); $('#mp_other_search').prop('disabled', true); resetOther();
    });

    // 1. the Fellow
    $('#mp_fellow_search').on('input', function () {
        var q = $.trim($(this).val());
        clearTimeout(timer);
        fellowId = null; $('#mp_fellow_selected').addClass('d-none').empty(); $('#mp_other_search').prop('disabled', true); resetOther();
        if (q.length < 2) { $('#mp_fellow_results').addClass('d-none').empty(); return; }
        timer = setTimeout(function () {
            $.get(urls.fellowSearch, { q: q }).done(function (res) {
                var $l = $('#mp_fellow_results').empty(), rows = res.fellows || [];
                if (!rows.length) { $l.append('<div class="list-group-item small text-muted">No Fellow found.</div>'); }
                rows.forEach(function (r) {
                    $l.append($('<button type="button" class="list-group-item list-group-item-action mp-fellow"></button>')
                        .text(r.name + (r.email ? ' — ' + r.email : '')).data('id', r.fellow_id).data('label', r.name));
                });
                $l.removeClass('d-none');
            });
        }, 300);
    });
    $(document).on('click', '#mp_fellow_results .mp-fellow', function () {
        fellowId = $(this).data('id');
        $('#mp_fellow_selected').removeClass('d-none').html('<span class="badge badge-secondary p-2"><i class="fas fa-check mr-1"></i>' + esc($(this).data('label')) + '</span>');
        $('#mp_fellow_results').addClass('d-none').empty();
        $('#mp_other_search').prop('disabled', false).focus();
        readyToCompare();
    });

    // 2. the other profile
    $('#mp_type').on('change', resetOther);
    $('#mp_other_search').on('input', function () {
        var q = $.trim($(this).val());
        clearTimeout(timer); otherId = null; $('#mp_other_selected').addClass('d-none').empty(); resetCompare(); readyToCompare();
        if (!fellowId || q.length < 2) { $('#mp_other_results').addClass('d-none').empty(); return; }
        timer = setTimeout(function () {
            $.get(at(urls.otherSearch), { type: $('#mp_type').val(), q: q }).done(function (res) {
                var $l = $('#mp_other_results').empty(), rows = res.results || [];
                if (!rows.length) { $l.append('<div class="list-group-item small text-muted">No matches (profiles on this Fellow\'s own login are left out).</div>'); }
                rows.forEach(function (r) {
                    $l.append($('<button type="button" class="list-group-item list-group-item-action mp-other"></button>')
                        .text((r.name || '') + (r.email ? ' — ' + r.email : '') + (r.country ? ' (' + r.country + ')' : '')).data('id', r.id).data('label', r.name || ''));
                });
                $l.removeClass('d-none');
            }).fail(function (xhr) { alertBox('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'The search failed.'); });
        }, 300);
    });
    $(document).on('click', '#mp_other_results .mp-other', function () {
        otherId = $(this).data('id');
        $('#mp_other_selected').removeClass('d-none').html('<span class="badge badge-secondary p-2"><i class="fas fa-check mr-1"></i>' + esc($(this).data('label')) + '</span>');
        $('#mp_other_results').addClass('d-none').empty();
        readyToCompare();
    });

    // 3. compare: every value both profiles carry. Same value: shown once. Different, or only one has it: choose.
    var statusLabel = { conflict: 'Different', other_only: 'Only on the other profile', fellow_only: 'Only on the Fellow' };
    $('#mpCompare').on('click', function () {
        var $b = $(this).prop('disabled', true); $('#mpAlert').addClass('d-none');
        $.get(at(urls.preview), { type: $('#mp_type').val(), other_id: otherId }).done(function (p) {
            var rows = '', diff = 0;
            p.fields.forEach(function (f) {
                if (f.status === 'same') {
                    rows += '<tr class="text-muted"><td>' + esc(f.label) + '</td><td colspan="2">' + esc(f.fellow_display) + ' <small>(same on both)</small></td></tr>';
                    return;
                }
                diff++;
                function cell(side, val) {
                    var empty = val === null || val === '';
                    return '<td><label class="mb-0 d-block" style="cursor:pointer;"><input type="radio" class="mp-choice" name="mp_' + esc(f.key) + '" value="' + side + '"' + (f.default === side ? ' checked' : '') + '> ' +
                        (empty ? '<em class="text-muted">empty</em>' : esc(val)) + '</label></td>';
                }
                rows += '<tr' + (f.status === 'conflict' ? ' style="background:#fff8e1;"' : '') + '><td>' + esc(f.label) + '<br><small class="text-muted">' + statusLabel[f.status] + '</small></td>' +
                    cell('fellow', f.fellow_display) + cell('other', f.other_display) + '</tr>';
            });
            var notes = (p.notes || []).map(function (n) { return '<li>' + esc(n) + '</li>'; }).join('');
            $('#mp_compare').removeClass('d-none').html(
                '<div class="d-flex justify-content-between mb-1"><strong>' + esc(p.fellow.name) + ' <small class="text-muted">(Fellow)</small></strong>' +
                '<strong>' + esc(p.other.name) + ' <small class="text-muted">(' + esc(p.other.type_label) + ')</small></strong></div>' +
                '<div style="max-height:46vh; overflow:auto; border:1px solid #eee; border-radius:6px;"><table class="table table-sm mb-0"><thead style="position:sticky;top:0;background:#f7f8fa;"><tr><th style="width:26%;">Value</th><th>Keep the Fellow\'s</th><th>Take the other profile\'s</th></tr></thead><tbody>' +
                (rows || '<tr><td colspan="3" class="text-center text-muted py-3">Nothing to compare.</td></tr>') + '</tbody></table></div>' +
                '<p class="small text-muted mt-2 mb-1">' + diff + ' value' + (diff === 1 ? '' : 's') + ' to decide. Values that are the same on both are not touched.</p>' +
                '<ul class="small mb-0 pl-3">' + notes + '</ul>');
            comparing = true; $('#mpMerge').prop('disabled', false);
        }).fail(function (xhr) {
            alertBox('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'The profiles could not be compared.');
        }).always(function () { $b.prop('disabled', false); });
    });

    // 4. merge
    $('#mpMerge').on('click', function () {
        if (!comparing || !fellowId || !otherId) return;
        var choices = {};
        $('#mp_compare .mp-choice:checked').each(function () { choices[$(this).attr('name').replace(/^mp_/, '')] = $(this).val(); });
        if (!window.confirm('Merge these two profiles?\n\nThe other profile moves to the Fellow\'s login and its duplicate login is retired (it can no longer sign in). A backup of what was changed is kept on the server.')) return;
        var $b = $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Merging…');
        $.ajax({ url: at(urls.merge), method: 'POST', data: { _token: urls.csrf, type: $('#mp_type').val(), other_id: otherId, choices: choices } })
            .done(function (res) {
                alertBox('success', res.message || 'Merged.');
                setTimeout(function () { window.location.href = urls.view + '/' + fellowId; }, 1200);
            }).fail(function (xhr) {
                alertBox('danger', (xhr.responseJSON && xhr.responseJSON.message) || 'The merge failed; nothing was changed.');
                $b.prop('disabled', false).html('<i class="fas fa-code-branch mr-1"></i>Merge profiles');
            });
    });
});
</script>
@endpush

@push('scripts')
<script>
// Confirm as Fellow, for a tentative person with no Fellow record yet (opened from the Certificate QR section).
$(function () {
    var ctTypes = { 'associate fellow': 2, 'fellow by election': 7, 'overseas fellow': 9, 'honorary fellow (asea)': 8, 'honorary fellow (cosecsa)': 10 };
    var ctUrl = @json(url('admin/associates/fellows/tentative/__ID__/confirm')), ctCsrf = @json(csrf_token());
    function ctAlert(kind, msg) { $('#ctAlert').removeClass('d-none alert-danger alert-success').addClass('alert-' + kind).text(msg); }

    // opened from the Certificate QR section (a tentative person with no Fellow record yet)
    window.openConfirmTentative = function (o) {
        $('#ctAlert').addClass('d-none');
        $('#ctId').val(o.id);
        $('#ctName').text(o.name);
        $('#ctCategory').val(ctTypes[String(o.type || '').toLowerCase()] || 2);
        $('#ctYear').val(o.year || new Date().getFullYear());
        $('#ctEmail').val(o.email || '');
        $('#ctCountry').val(o.cid || '');
        $('#ctSubmit').prop('disabled', false);
        $('#confirmTentativeModal').modal('show');
    };

    $('#confirmTentativeForm').on('submit', function (e) {
        e.preventDefault();
        var $btn = $('#ctSubmit').prop('disabled', true), id = $('#ctId').val();
        $.post(ctUrl.replace('__ID__', id), {
            _token: ctCsrf, category_id: $('#ctCategory').val(), fellowship_year: $.trim($('#ctYear').val()),
            email: $.trim($('#ctEmail').val()), country_id: $('#ctCountry').val()
        }).done(function (d) {
            ctAlert('success', d.message || 'Confirmed.');
            setTimeout(function () {
                $('#confirmTentativeModal').modal('hide');
                if ($('#qrShow').length) { $('#qrShow').trigger('click'); } else { window.location.reload(); }   // the QR list shows them as approved Fellows
            }, 700);
        }).fail(function (xhr) {
            var j = xhr.responseJSON || {}, first = j.errors ? j.errors[Object.keys(j.errors)[0]][0] : null;
            ctAlert('danger', first || j.message || 'They could not be confirmed.');
            $btn.prop('disabled', false);
        });
    });
});
</script>
@endpush
