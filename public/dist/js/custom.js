$(function () {

    // ── Loader styles (injected once into <head>) ────────────────────────────────
    if (!document.getElementById('dt-loader-style')) {
        var s = document.createElement('style');
        s.id = 'dt-loader-style';
        s.textContent = [
            /* overlay — sits on top of hidden table */
            '.dt-loader-overlay{position:absolute;inset:0;background:#fff;z-index:50;',
            'display:flex;flex-direction:column;overflow:hidden;border-radius:0 0 4px 4px;}',

            /* animated progress bar */
            '.dt-bar-track{height:3px;background:#fbe8e8;width:100%;flex-shrink:0;}',
            '.dt-bar{height:100%;background:linear-gradient(90deg,#c0392b,#a02626);',
            'animation:dt-bar-anim 1.8s ease-in-out infinite;}',
            '@keyframes dt-bar-anim{',
            '0%{width:0%;opacity:1}55%{width:75%;opacity:1}',
            '88%{width:96%;opacity:.7}100%{width:0%;opacity:0}}',

            /* skeleton table wrapper */
            '.dt-sk-wrap{flex:1;overflow:hidden;}',
            '.dt-sk-table{width:100%;border-collapse:collapse;table-layout:fixed;}',

            /* header: real column names, styled like Bootstrap thead */
            '.dt-sk-table thead th{',
            'padding:9px 10px;font-size:13px;font-weight:600;color:#495057;',
            'background:#f8f9fa;border:1px solid #dee2e6;',
            'border-bottom:2px solid #dee2e6;white-space:nowrap;',
            'overflow:hidden;text-overflow:ellipsis;}',

            /* body cells */
            '.dt-sk-table tbody td{',
            'padding:9px 10px;border:1px solid #dee2e6;vertical-align:middle;}',
            '.dt-sk-table tbody tr:nth-child(even) td{background:#f9f9f9;}',
            '.dt-sk-table tbody tr:nth-child(odd)  td{background:#fff;}',

            /* shimmer pill inside each cell */
            '.dt-sk-c{',
            'height:13px;border-radius:6px;display:block;',
            'background:linear-gradient(90deg,#efefef 25%,#e3e3e3 50%,#efefef 75%);',
            'background-size:400% 100%;',
            'animation:dt-shimmer 1.55s ease infinite;}',

            /* stagger rows */
            '.dt-sk-table tbody tr:nth-child(1) .dt-sk-c{animation-delay:0s}',
            '.dt-sk-table tbody tr:nth-child(2) .dt-sk-c{animation-delay:.07s}',
            '.dt-sk-table tbody tr:nth-child(3) .dt-sk-c{animation-delay:.14s}',
            '.dt-sk-table tbody tr:nth-child(4) .dt-sk-c{animation-delay:.21s}',
            '.dt-sk-table tbody tr:nth-child(5) .dt-sk-c{animation-delay:.28s}',
            '.dt-sk-table tbody tr:nth-child(6) .dt-sk-c{animation-delay:.35s}',
            '.dt-sk-table tbody tr:nth-child(7) .dt-sk-c{animation-delay:.42s}',
            '.dt-sk-table tbody tr:nth-child(8) .dt-sk-c{animation-delay:.49s}',
            '.dt-sk-table tbody tr:nth-child(9) .dt-sk-c{animation-delay:.56s}',

            /* vary pill width column-by-column for realism */
            '.dt-sk-table tbody td:nth-child(1) .dt-sk-c{width:60%}',
            '.dt-sk-table tbody td:nth-child(2) .dt-sk-c{width:88%}',
            '.dt-sk-table tbody td:nth-child(3) .dt-sk-c{width:70%}',
            '.dt-sk-table tbody td:nth-child(4) .dt-sk-c{width:80%}',
            '.dt-sk-table tbody td:nth-child(5) .dt-sk-c{width:75%}',
            '.dt-sk-table tbody td:nth-child(6) .dt-sk-c{width:82%}',
            '.dt-sk-table tbody td:nth-child(7) .dt-sk-c{width:65%}',
            '.dt-sk-table tbody td:nth-child(8) .dt-sk-c{width:55%}',
            '.dt-sk-table tbody td:nth-child(9) .dt-sk-c{width:72%}',
            '.dt-sk-table tbody td:nth-child(10) .dt-sk-c{width:60%}',

            '@keyframes dt-shimmer{',
            '0%{background-position:100% 0}100%{background-position:-100% 0}}'
        ].join('');
        document.head.appendChild(s);
    }

    // ── Build loader from the table's real <thead> ───────────────────────────────
    function makeLoader(id) {
        var $ths = $('#' + id).find('thead th');

        // Build colgroup so skeleton columns match the real table's widths
        var colgroup = '<colgroup>';
        $ths.each(function () {
            var w = $(this).outerWidth();
            colgroup += '<col' + (w ? ' style="width:' + w + 'px"' : '') + '>';
        });
        colgroup += '</colgroup>';

        // Real header cells (visible columns only — skip hidden ones)
        var thead = '<thead><tr>';
        $ths.each(function () {
            var label = $(this).text().trim() || '&nbsp;';
            thead += '<th>' + label + '</th>';
        });
        thead += '</tr></thead>';

        // 9 shimmer rows with the same column count
        var emptyRow = '<tr>' + $ths.toArray().map(function () {
            return '<td><span class="dt-sk-c"></span></td>';
        }).join('') + '</tr>';
        var tbody = '<tbody>' + Array(9).fill(emptyRow).join('') + '</tbody>';

        return '<div class="dt-loader-overlay" aria-hidden="true">' +
            '<div class="dt-bar-track"><div class="dt-bar"></div></div>' +
            '<div class="dt-sk-wrap">' +
                '<table class="dt-sk-table">' +
                    colgroup + thead + tbody +
                '</table>' +
            '</div>' +
        '</div>';
    }

    // ── Show / hide helpers ──────────────────────────────────────────────────────
    function showLoader(id) {
        var $cb = $('#' + id).closest('.card-body');
        if (!$cb.length) return;
        $cb.css('position', 'relative');
        $cb.find('.dt-loader-overlay').remove();
        $cb.append(makeLoader(id));
    }

    function hideLoader(id) {
        // Fade out the skeleton overlay
        $('#' + id).closest('.card-body').find('.dt-loader-overlay')
            .stop(true).fadeOut(380, function () { $(this).remove(); });
        // Reveal the table (CSS transition handles the ease)
        $('#' + id).css('opacity', 1);
    }

    // ── Dropdown re-init after every draw ────────────────────────────────────────
    function reinitDropdowns(tableEl) {
        $(tableEl).find('[data-toggle="dropdown"]').dropdown();
    }

    // ── Tables whose rows arrive as JSON (<table data-rows-url="...">) ─────────
    // The page renders without rows; DataTables fetches them and, with
    // deferRender, only builds <tr>s for the page on screen. Column renderers
    // return HTML for "display" and plain text for sort/search/export.
    function dtEsc(v) {
        return String(v == null ? "" : v).replace(/[&<>"']/g, function (c) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
        });
    }
    function dtDash(v) { return v == null || v === "" ? "-" : v; }
    function dtText(d, type) { return type === "display" ? dtEsc(d) : (d == null ? "" : d); }
    function dtDashText(d, type) { return type === "display" ? dtEsc(dtDash(d)) : dtDash(d); }
    function dtLink(href, text, style) {
        return '<a href="' + href + '" style="' + (style || "") + 'text-decoration:none;">' + dtEsc(text) + '</a>';
    }
    // Maroon link when there's an id to link to, plain text otherwise.
    function dtRefLink(url, id, text, type) {
        if (type !== "display") return text == null ? "" : text;
        return id ? dtLink(url + id, text == null ? "" : text, "color:#a02626;font-weight:500;") : dtEsc(text);
    }
    function dtActionMenu(items) {
        return '<div class="dropdown"><button class="btn btn-sm btn-light border dropdown-toggle action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
            '<div class="dropdown-menu dropdown-menu-right shadow-sm">' + items + '</div></div>';
    }
    function dtMenuItem(href, icon, label, extra) {
        return '<a class="dropdown-item' + (extra && extra.cls ? " " + extra.cls : "") + '" href="' + href + '"' +
            (extra && extra.confirm ? ' onclick="return confirm(\'' + extra.confirm + '\')"' : "") +
            '><i class="' + icon + ' mr-2"></i> ' + label + '</a>';
    }
    // Uses the request _dt_prefetch.blade.php started early when there is one,
    // falling back to a normal request.
    function dtAjax($table, onJson) {
        var url = $table.data("rows-url");
        var rows = function (json) {
            if (onJson) onJson(json);
            return json.rows || [];
        };
        var early = window.dtRowsPrefetch && window.dtRowsPrefetch[url];
        if (!early) return { "url": url, "dataSrc": rows };
        delete window.dtRowsPrefetch[url];
        return function (data, callback) {
            early.then(null, function () { return $.getJSON(url); })
                 .then(function (json) { callback({ data: rows(json) }); });
        };
    }
    // "#" column: number the current page's rows (only those exist with deferRender).
    function dtNumberRows(api) {
        var start = api.page.info().start;
        api.column(0, { page: "current" }).nodes().each(function (cell, i) { cell.innerHTML = start + i + 1; });
    }

    // ── Global DataTables defaults ───────────────────────────────────────────────
    $.extend(true, $.fn.dataTable.defaults, {
        "dom": '<"row"<"col-md-6"l><"col-md-6"f>>rt<"row"<"col-md-5"i><"col-md-7"p>>'
    });

    // ── Show info text at the top of every table as well as the bottom ───────────
    $(document).on('draw.dt', function (e) {
        var wrapper = $(e.target).closest('.dataTables_wrapper');
        var info    = wrapper.find('.dataTables_info').not('.dt-top-info');
        if (!info.length) return;
        var topInfo = wrapper.find('.dt-top-info');
        if (topInfo.length) {
            topInfo.html(info.html());
        } else {
            info.clone()
                .addClass('dt-top-info')
                .css('margin-bottom', '4px')
                .insertBefore(wrapper.find('table.dataTable').first());
        }
    });

    // ═══════════════════════════════════════════════════════════════════════════════
    // Trainees
    // Cols: #(0) Name(1) Gender(2) AdmNo(3) Email(4) Programme(5) Hospital(6)
    //       Country(7) Status(8) | hidden: SFSUser(9) SFSPass(10) AdmLetter(11)
    //       InvLetter(12) AdmYear(13) ProgYear(14) ExamYear(15) ResumeYear(16)
    //       ProgDuration(17) Invoice#(18) InvDate(19) InvStatus(20) Sponsor(21)
    //       ModePayment(22) AmtPaid(23) DatePaid(24) | Action(25)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#traineestable").length) {
        var $tt = $("#traineestable");
        var tBase = $tt.data("base-url"), tApp = $tt.data("app-url"), tCanDelete = $tt.data("can-delete") === 1;
        var traw = { "render": dtText };
        showLoader("traineestable");
        $tt.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "pageLength": 25,
            "deferRender": true,
            "ajax": dtAjax($tt),
            "language": { "loadingRecords": "Loading trainees…" },
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Trainees List",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24], orthogonal: "export" } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Trainees List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8], orthogonal: "export" } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6,7,8], orthogonal: "export" } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "data": null, "defaultContent": "", "className": "row-num", "orderable": false, "searchable": false }, // 0 #
                { "data": "name", "render": function (d, type, r) {
                    if (type !== "display") return d == null ? "" : d;
                    var html = '<a href="' + tBase + '/view/' + r.trainee_id + '" class="trainee-name-link font-weight-500">' + dtEsc(d) + '</a>';
                    if (r.resume_year) {
                        html += ' <span class="badge badge-info ml-1" title="Deferred — resuming in ' + dtEsc(r.resume_year) + '">Resuming ' + dtEsc(r.resume_year) + '</span>';
                    }
                    return html;
                } },
                $.extend({ "data": "gender" }, traw),
                $.extend({ "data": "entry_number" }, traw),
                $.extend({ "data": "personal_email" }, traw),
                { "data": "programme_name", "render": function (d, type, r) { return dtRefLink(tApp + "/admin/programmes/view/", r.programme_id, d, type); } },
                { "data": "hospital_name", "render": function (d, type, r) { return dtRefLink(tApp + "/admin/hospital/view_hospital/", r.hospital_id, d, type); } },
                { "data": "country_name", "render": function (d, type, r) { return dtRefLink(tApp + "/admin/countries/view/", r.country_id, d, type); } },
                $.extend({ "data": "status" }, traw),
                // hidden (export) columns 9–24
                $.extend({ "data": "user_email", "visible": false }, traw),
                $.extend({ "data": "user_password", "visible": false }, traw),
                $.extend({ "data": "admission_letter_status", "visible": false }, traw),
                $.extend({ "data": "invitation_letter_status", "visible": false }, traw),
                $.extend({ "data": "admission_year", "visible": false }, traw),
                $.extend({ "data": "programme_year", "visible": false }, traw),
                $.extend({ "data": "exam_year", "visible": false }, traw),
                $.extend({ "data": "resume_year", "visible": false }, traw),
                { "data": "programme_period", "visible": false, "render": function (d, type) { return dtText((d == null ? "" : d) + " Years", type); } },
                $.extend({ "data": "invoice_number", "visible": false }, traw),
                $.extend({ "data": "invoice_date", "visible": false }, traw),
                $.extend({ "data": "invoice_status", "visible": false }, traw),
                $.extend({ "data": "sponsor", "visible": false }, traw),
                $.extend({ "data": "mode_of_payment", "visible": false }, traw),
                $.extend({ "data": "amount_paid", "visible": false }, traw),
                $.extend({ "data": "payment_date", "visible": false }, traw),
                { "data": null, "orderable": false, "searchable": false, "className": "text-center", // 25 Action
                  "render": function (d, type, r) {
                    if (type !== "display") return "";
                    var items = dtMenuItem(tBase + "/view/" + r.trainee_id, "fas fa-eye text-info", "View") +
                                dtMenuItem(tBase + "/edit/" + r.trainee_id, "fas fa-edit text-warning", "Edit");
                    if (tCanDelete) {
                        items += '<div class="dropdown-divider"></div>' +
                            dtMenuItem(tBase + "/delete/" + r.t_id, "fas fa-trash", "Delete", { cls: "text-danger", confirm: "Delete this trainee?" });
                    }
                    return dtActionMenu(items);
                } }
            ],
            "initComplete": function () { hideLoader("traineestable"); },
            "drawCallback": function () { dtNumberRows(this.api()); reinitDropdowns(this); }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Candidates
    // Cols: #(0) Name(1) PEN(2) CandNo(3) ExamType(4) Hospital(5) Country(6)
    //       Gender(7) FeePaid(8) | hidden: Email(9) RepP1(10) RepP2(11)
    //       MMed(12) Sponsor(13) ExamYear(14) ModePayment(15) | Action(16)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#candidatestable").length) {
        var $ct = $("#candidatestable");
        var cBase = $ct.data("base-url"), cApp = $ct.data("app-url"), cCanDelete = $ct.data("can-delete") === 1,
            cCanManage = $ct.data("can-manage") === 1, cCsrf = $ct.data("csrf");
        var craw = { "render": dtDashText };
        showLoader("candidatestable");
        $ct.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "pageLength": 25,
            "deferRender": true,
            "ajax": dtAjax($ct),
            "language": { "loadingRecords": "Loading candidates…" },
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Candidates List",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17], orthogonal: "export" } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Candidates List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10], orthogonal: "export" } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10], orthogonal: "export" } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "data": null, "defaultContent": "", "className": "row-num", "orderable": false, "searchable": false }, // 0 #
                { "data": "name", "render": function (d, type, r) {
                    if (type !== "display") return dtDash(d);
                    return '<a href="' + cBase + '/view/' + (r.candidates_id || 0) + '" class="candidate-name-link font-weight-500">' + dtEsc(dtDash(d)) + '</a>';
                } },
                $.extend({ "data": "entry_number" }, craw),
                { "data": "candidate_id", "render": function (d, type) {
                    if (type !== "display") return d || "—";
                    return d ? '<span class="badge badge-secondary" style="font-size:.78rem;letter-spacing:.5px;">' + dtEsc(d) + '</span>'
                             : '<span class="text-muted" style="font-size:.78rem;">—</span>';
                } },
                { "data": "programme_name", "render": function (d, type, r) { return dtRefLink(cApp + "/admin/programmes/view/", r.programme_id, dtDash(d), type); } },
                { "data": "hospital_name", "render": function (d, type, r) { return dtRefLink(cApp + "/admin/hospital/view_hospital/", r.hospital_id, dtDash(d), type); } },
                { "data": "country_name", "render": function (d, type, r) { return dtRefLink(cApp + "/admin/countries/view/", r.country_id, dtDash(d), type); } },
                { "data": "gender", "render": function (d, type) { return dtDashText(d === "Female" || d === "Male" ? d : null, type); } },
                { "data": "fee_paid", "className": "text-center", "render": function (d, type) {
                    var paid = (d || "No") === "Yes";
                    if (type !== "display") return paid ? "Paid" : "Unpaid";
                    return paid ? '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Paid</span>'
                                : '<span class="badge badge-danger">Unpaid</span>';
                } },
                // Part I: Pass/Fail only — the marks are on the results pages.
                { "data": "part1_result", "className": "text-center", "render": function (d, type) { // 9 Part I
                    if (type !== "display") return d || "";
                    if (!d) return '<span class="text-muted">—</span>';
                    return '<span class="badge ' + (d === "Pass" ? "badge-success" : "badge-danger") + '">' + dtEsc(d) + '</span>';
                } },
                { "data": "part_two_promoted_at", "className": "text-center", "render": function (d, type) { // 10 Part II
                    if (type !== "display") return d ? "Promoted" : "";
                    return d ? '<span class="badge" style="background:#FEC503;color:#3a2a00;">Promoted</span>' : '<span class="text-muted">—</span>';
                } },
                // hidden (export / search) columns 11–17
                $.extend({ "data": "personal_email", "visible": false }, craw),
                { "data": "repeat_paper_one", "visible": false, "render": function (d, type) { return dtText(d || "No", type); } },
                { "data": "repeat_paper_two", "visible": false, "render": function (d, type) { return dtText(d || "No", type); } },
                { "data": "mmed", "visible": false, "render": function (d, type) { return dtText(d || "No", type); } },
                $.extend({ "data": "sponsor", "visible": false }, craw),
                $.extend({ "data": "exam_year", "visible": false }, craw),
                $.extend({ "data": "mode_of_payment", "visible": false }, craw),
                { "data": null, "orderable": false, "searchable": false, "className": "text-center", // 18 Action
                  "render": function (d, type, r) {
                    if (type !== "display") return "";
                    var id = r.candidates_id || 0;
                    var items = dtMenuItem(cBase + "/view/" + id, "fas fa-eye text-info", "View") +
                                dtMenuItem(cBase + "/edit/" + id, "fas fa-edit text-warning", "Edit");
                    if (cCanManage) {
                        var promoted = !!r.part_two_promoted_at;
                        items += '<form method="POST" action="' + cBase + '/part-two/' + id + '" class="cand-part-two" data-promoted="' + (promoted ? 1 : 0) + '">' +
                            '<input type="hidden" name="_token" value="' + dtEsc(cCsrf) + '">' +
                            '<input type="hidden" name="promote" value="' + (promoted ? 0 : 1) + '">' +
                            '<button type="submit" class="dropdown-item"><i class="fas ' + (promoted ? 'fa-undo text-secondary' : 'fa-level-up-alt text-success') + ' mr-2"></i> ' +
                            (promoted ? "Remove Part II promotion" : "Promote to Part II") + '</button></form>';
                    }
                    if (cCanDelete) {
                        items += '<div class="dropdown-divider"></div>' +
                            dtMenuItem(cBase + "/delete/" + (r.c_id || 0), "fas fa-trash", "Delete", { cls: "text-danger", confirm: "Delete this candidate?" });
                    }
                    return dtActionMenu(items);
                } }
            ],
            "initComplete": function () { hideLoader("candidatestable"); },
            "drawCallback": function () { dtNumberRows(this.api()); reinitDropdowns(this); }
        });

        $ct.on("submit", "form.cand-part-two", function () {
            return confirm($(this).data("promoted") === 1
                ? "Remove this candidate's promotion to Part II?"
                : "Promote this candidate to Part II?");
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Alumni — #(0) Name(1) Email(2) Country(3) Specialty(4) Type(5) Year(6) Action(7)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#alumnitable").length) {
        showLoader("alumnitable");
        $("#alumnitable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "pageLength": 25, "stateSave": true,
            "order": [[6, "desc"], [1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Alumni List",
                  exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Alumni List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("alumnitable"); },
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Fellows — #(0) Name(1) Email(2) Country(3) Specialty(4) Type(5) Year(6)
    //           Hospital/Organisation(7) Action(8)
    // ═══════════════════════════════════════════════════════════════════════════════
    // Rows come as JSON from data-rows-url after the page has rendered, and
    // deferRender only builds the <tr>s for the page being shown, so the page
    // no longer ships ~2,000 server-rendered rows (was 9 MB of HTML).
    if ($("#fellowstable").length) {
        var $ft = $("#fellowstable");
        var fBase = $ft.data("base-url");
        var appUrl = $ft.data("app-url");
        var canTranscript = $ft.data("can-transcript") === 1;
        var canDelete = $ft.data("can-delete") === 1;
        var esc = dtEsc, link = dtLink, plain = dtDash;

        showLoader("fellowstable");
        $ft.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "pageLength": 25, "stateSave": true,
            "deferRender": true,
            "ajax": dtAjax($ft, function (json) { $ft.data("extra-rows", json.extra || []); }),
            "language": { "loadingRecords": "Loading fellows…" },
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Fellows List",
                  exportOptions: { columns: [1,2,3,4,5,6,7], orthogonal: "export" } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Fellows List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7], orthogonal: "export" } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6,7], orthogonal: "export" } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "data": null, "defaultContent": "", "className": "row-num", "orderable": false, "searchable": false },
                { "data": "name", "render": function (d, type, r) {
                    var multi = r.specs > 1;
                    // Multi-specialty fellows sort first, then alphabetically.
                    if (type === "sort") return (multi ? "0_" : "1_") + (d || "");
                    if (type !== "display") return plain(d);
                    if (r.kind === "extra") {
                        return link(fBase + "/view/" + r.id, d || "-", "color:#3a7a1a;") +
                            ' <span class="text-muted small">(add\'l specialty)</span>';
                    }
                    var html = link(fBase + "/view/" + r.id, d || "-", multi ? "color:#a02626; font-weight:600;" : "color:#222;");
                    if (multi) {
                        html += '<sup class="fellow-spec-count" title="' + r.specs + ' FCS specialties">' + r.specs + '</sup>';
                    }
                    return '<span style="display:inline-flex; align-items:center; gap:4px;">' + html + '</span>';
                } },
                { "data": "email", "render": function (d, type) { return type === "display" ? esc(plain(d)) : plain(d); } },
                { "data": "country", "render": function (d, type, r) {
                    if (type !== "display") return plain(d);
                    return r.cid && r.kind !== "extra"
                        ? link(appUrl + "/admin/countries/view/" + r.cid, plain(d), "color:#a02626;font-weight:500;")
                        : esc(plain(d));
                } },
                { "data": "specialty", "render": function (d, type, r) {
                    if (type !== "display") return plain(d);
                    return r.pid ? link(appUrl + "/admin/programmes/view/" + r.pid, plain(d), "color:#a02626;font-weight:500;") : esc(plain(d));
                } },
                { "data": "ftype", "render": function (d, type) { return type === "display" ? esc(plain(d)) : plain(d); } },
                { "data": "year", "render": function (d, type) { return type === "display" ? esc(plain(d)) : plain(d); } },
                // Hospital/Organisation — off by default; staff can turn it on
                // via the Columns button.
                { "data": "hospital", "visible": false, "render": function (d, type) { return type === "display" ? esc(plain(d)) : plain(d); } },
                { "data": null, "orderable": false, "searchable": false, "className": "text-center",
                  "render": function (d, type, r) {
                    if (type !== "display" || r.kind === "extra") return "";
                    var items =
                        dtMenuItem(fBase + "/view/" + r.id, "fas fa-eye text-info", "View") +
                        dtMenuItem(fBase + "/edit/" + r.id, "fas fa-edit text-warning", "Edit");
                    if (canTranscript && r.ftype === "Fellow by Examination") {
                        items += dtMenuItem(appUrl + "/admin/transcripts/edit/" + r.uid, "fas fa-file-signature text-secondary", "Issue Transcript");
                    }
                    if (canDelete) {
                        items += '<div class="dropdown-divider"></div>' +
                            dtMenuItem(fBase + "/delete/" + r.fid, "fas fa-trash", "Delete", { cls: "text-danger", confirm: "Delete this fellow?" });
                    }
                    return dtActionMenu(items);
                } }
            ],
            "initComplete": function () { hideLoader("fellowstable"); },
            "drawCallback": function () { dtNumberRows(this.api()); reinitDropdowns(this); }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Programme Directors
    // Cols: #(0) Name(1) Email(2) Hospital(3) Country(4) Phone(5) Assistant PD(6)
    //       Asst PD Email(7) Mobile(8) | Action(9)
    // Was "#trainerstable" back when PDs and the ToT Trainers roster shared
    // one table/id — PDs kept id="pdtable" after the split (see Trainer
    // model), this block just never got renamed with it, so the PD list was
    // silently running as a bare, unstyled table (no buttons/paging chrome/
    // stateSave, and the dropdown-menu-after-redraw fix below) until a
    // filter checkbox lazily auto-inited it with zero options.
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#pdtable").length) {
        showLoader("pdtable");
        $("#pdtable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("pdtable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#pdtable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Examiners
    // ═══════════════════════════════════════════════════════════════════════════════
    // Rows come from the page's #examinersRows JSON; deferRender builds only
    // the visible page (was ~3.5 MB of server-rendered rows).
    if ($("#examinerstable").length) {
        var $et = $("#examinerstable");
        var eApp = $et.data("app-url"), eCsrf = $et.data("csrf"), eFrom = $et.data("from"), eCanDelete = $et.data("can-delete") === 1;
        var shiftNames = { 1: "Morning", 2: "Morning & Afternoon", 3: "Afternoon" };
        var dash = '<span class="text-muted">—</span>';
        // Export the data columns (2–9) that are currently shown; not the checkbox, #, or Action.
        var eExport = { columns: function (idx) { return idx > 1 && idx < 10 && $et.DataTable().column(idx).visible(); }, orthogonal: "export" };
        showLoader("examinerstable");
        $et.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true, "deferRender": true,
            "data": JSON.parse(document.getElementById("examinersRows").textContent || "[]"),
            "buttons": [
                { extend: "copy", exportOptions: eExport }, { extend: "csv", exportOptions: eExport },
                { extend: "excel", exportOptions: eExport }, { extend: "pdf", exportOptions: eExport }, "colvis"
            ],
            "createdRow": function (tr, r) {
                $(tr).addClass("examiner-row" + (r.participated_last_year ? " last-year-row" : ""));
            },
            "columns": [
                // Col 0: checkbox — not sortable/searchable
                { "data": null, "orderable": false, "searchable": false, "render": function (d, type, r) {
                    if (type !== "display") return "";
                    return '<input type="checkbox" class="row-chk" value="' + r.examin_id + '" data-email="' + dtEsc(r.email) + '" data-name="' + dtEsc(r.examiner_name) + '">';
                } },
                { "data": null, "defaultContent": "", "className": "row-num", "orderable": false, "searchable": false }, // #
                { "data": "examiner_name", "render": function (d, type, r) { // Name
                    return type === "display" ? dtLink(eApp + "/admin/exams/view_examiner/" + r.examin_id, d || "", "color:#a02626;font-weight:500;") : (d || "");
                } },
                { "data": "email", "render": dtText }, // Email
                { "data": "country_name", "render": function (d, type, r) { return dtRefLink(eApp + "/admin/countries/view/", r.country_id, d, type); } }, // Country
                { "data": "examiner_id", "render": dtText }, // Examiner ID
                { "data": "specialty", "render": function (d, type) { return dtText(d || "—", type); } }, // Specialty
                { "data": "examiner_designation", "render": function (d, type) { // Designation
                    if (type !== "display") return d || "—";
                    return d ? '<span class="badge badge-pill" style="background:#a02626;color:#fff;font-size:.75rem;">' + dtEsc(d) + '</span>' : dash;
                } },
                { "data": "shift_id", "visible": false, "render": function (d, type) { // Shift
                    var name = d ? (shiftNames[d] || "Unknown Shift") : "";
                    if (type !== "display") return name || "—";
                    return d ? '<span class="badge badge-info" style="font-size:.75rem;">' + name + '</span>' : dash;
                } },
                { "data": "internal_notes", "render": function (d, type) { // Notes
                    if (type !== "display") return d || "—";
                    if (!d) return '<span class="text-muted" style="font-size:.8rem;">—</span>';
                    var preview = d.length > 60 ? d.substring(0, 60) + "..." : d;
                    return '<span class="text-muted" style="font-size:.8rem;cursor:default;" data-toggle="tooltip" data-placement="left" title="' + dtEsc(d) + '">' + dtEsc(preview) + '</span>';
                } },
                // Col 10: Action — not sortable/searchable
                { "data": null, "orderable": false, "searchable": false, "render": function (d, type, r) {
                    if (type !== "display") return "";
                    var items =
                        '<form action="' + eApp + '/admin/exams/view_examiner/' + r.examin_id + '" method="POST" style="display:inline;">' +
                        '<input type="hidden" name="_token" value="' + dtEsc(eCsrf) + '"><input type="hidden" name="from" value="' + dtEsc(eFrom) + '">' +
                        '<button type="submit" class="dropdown-item"><i class="fa fa-eye"></i> View</button></form>' +
                        '<a href="' + eApp + '/admin/exams/edit_examiner/' + r.examin_id + '" class="dropdown-item"><i class="fa fa-edit"></i> Edit</a>';
                    if (eCanDelete) {
                        items += '<a href="' + eApp + '/admin/exams/delete/' + r.ex_id + '" class="dropdown-item text-danger" onclick="return confirm(\'Are you sure?\')"><i class="fa fa-trash"></i> Delete</a>';
                    }
                    return '<div class="dropdown"><button class="btn btn-sm btn-light border dropdown-toggle action-btn" type="button" data-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="dropdown-menu dropdown-menu-right shadow-sm">' + items + '</div></div>';
                } }
            ],
            "stateLoadParams": function (settings, data) {
                if (!data || (data.columns && data.columns.length !== 11)) { return false; }
            },
            "initComplete": function () { hideLoader("examinerstable"); },
            "drawCallback": function () {
                var api = this.api(), start = api.page.info().start;
                api.column(1, { page: "current" }).nodes().each(function (cell, i) { cell.innerHTML = start + i + 1; });
                reinitDropdowns(this);
            }
        }).buttons().container().appendTo('#examinerstable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Examiner Confirmation
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#examinerconfirmationtable").length) {
        showLoader("examinerconfirmationtable");
        // Rows come from the page's #ecRows JSON (was ~7 KB of HTML per row).
        var $ec = $("#examinerconfirmationtable");
        var ecApp = $ec.data("app-url"), ecCsrf = $ec.data("csrf"), ecFrom = $ec.data("from"),
            ecQuery = $ec.data("query") || {}, ecBack = $ec.data("back"), ecReset = $ec.data("reset-url");
        var ecDash = function (d, type) { var v = d == null || d === "" ? "-" : d; return type === "display" ? dtEsc(v) : v; };
        var ecHidden = function (name, value) { return '<input type="hidden" name="' + dtEsc(name) + '" value="' + dtEsc(value) + '">'; };
        $ec.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "processing": true, "deferRender": true,
            "data": JSON.parse(document.getElementById("ecRows").textContent || "[]"),
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"].map(function (b) {
                return b === "colvis" ? b : { extend: b, exportOptions: { orthogonal: "export" } };
            }),
            "columns": [
                { "data": "n" }, // #
                { "data": "name", "render": function (d, type, r) { // Name
                    return type === "display" ? dtLink(ecApp + "/admin/exams/view_examiner/" + r.id, d || "-", "").replace("<a ", '<a class="cosecsa-link" ') : (d || "-");
                } },
                { "data": "email", "visible": false, "render": ecDash }, // Email
                { "data": "fellow", "visible": false, "render": function (d, type, r) { // Fellowship Status
                    if (type !== "display") return d ? "Fellow" : "Not Fellow";
                    if (!d) return '<span class="badge" style="background:#f0f0f0;color:#777;">Not Fellow</span>';
                    return '<span class="badge" style="background:#d4edda;color:#155724;" title="' + (r.fno ? "Fellow ID " + dtEsc(r.fno) : "") + '">Fellow</span>' +
                        (r.fid ? '<a href="' + ecApp + '/admin/associates/fellows/view/' + r.fid + '" class="cosecsa-link" style="font-size:11px;display:block;">view fellow</a>' : "");
                } },
                { "data": "country", "render": function (d, type, r) { // Country
                    if (type !== "display") return d || "-";
                    return r.cid ? '<a href="' + ecApp + '/admin/countries/view/' + r.cid + '" class="cosecsa-link">' + dtEsc(d || "-") + '</a>' : dtEsc(d || "-");
                } },
                { "data": "spec", "render": ecDash }, // Specialty
                { "data": "avail", "render": function (d, type) { // Availability
                    var list = d || [];
                    if (list.indexOf("Not Available") !== -1) {
                        return type === "display" ? '<span style="color:#a02626;font-weight:600;">Not Available</span>' : "Not Available";
                    }
                    return ecDash(list.join(", "), type);
                } },
                { "data": "shiftName", "visible": false, "render": function (d, type) { // Shift
                    if (type !== "display") return d || "—";
                    return d ? '<span class="badge badge-info" style="font-size:.75rem;">' + dtEsc(d) + '</span>' : '<span class="text-muted">—</span>';
                } },
                { "data": "part", "render": ecDash }, // Participation
                { "data": "dsrc", "render": function (d, type) { // Source
                    var label = d === "self" ? "Self" : (d === "admin" ? "Admin" : "-");
                    if (type !== "display") return label;
                    return d === "self" ? '<span class="badge-source-self">Self</span>'
                         : d === "admin" ? '<span class="badge-source-admin">Admin</span>' : '<span class="badge-email-none">-</span>';
                } },
                { "data": "opened", "render": function (d, type, r) { // Email Status
                    if (type !== "display") return d ? "Opened" : "-";
                    return d ? '<span class="badge-email-opened" title="' + dtEsc(r.emailTitle) + '">Opened</span>' : '<span class="badge-email-none">-</span>';
                } },
                { "data": "upd", "render": function (d, type, r) { // Updated At
                    if (type === "sort" || type === "type") return r.updSort || "";
                    return d || "-";
                } },
                { "data": null, "orderable": false, "searchable": false, "render": function (d, type, r) { // Action
                    if (type !== "display") return "";
                    var view = '<form action="' + ecApp + '/admin/exams/view_examiner/' + r.id + '" method="POST" style="display:inline;">' +
                        ecHidden("_token", ecCsrf) + ecHidden("from", ecFrom) +
                        Object.keys(ecQuery).map(function (k) { return ecHidden(k, ecQuery[k]); }).join("") +
                        '<button type="submit" class="dropdown-item"><i class="fa fa-eye"></i> View</button></form>';
                    var reset = '<form method="POST" action="' + String(ecReset).replace("__ID__", r.id) + '" class="ec-reset" data-name="' + dtEsc(r.name) + '">' +
                        ecHidden("_token", ecCsrf) + ecHidden("type", "soft") + ecHidden("back", ecBack) +
                        '<button type="submit" class="dropdown-item text-warning"><i class="fas fa-undo mr-1"></i> Reset Confirmation</button></form>';
                    var del = '<a href="#" class="dropdown-item text-danger ec-delete" data-id="' + r.id + '" data-name="' + dtEsc(r.name) + '"><i class="fa fa-trash mr-1"></i> Delete Examiner</a>';
                    return '<div class="dropdown"><button class="btn btn-sm btn-light border dropdown-toggle action-btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></button>' +
                        '<div class="dropdown-menu dropdown-menu-right shadow-sm">' + view +
                        '<a href="' + ecApp + '/admin/exams/edit_examiner/' + r.id + '" class="dropdown-item"><i class="fa fa-edit"></i> Edit</a>' +
                        '<div class="dropdown-divider"></div>' + reset + del + '</div></div>';
                } }
            ],
            "stateLoadParams": function (settings, data) {
                if (!data || (data.columns && data.columns.length !== 13)) { return false; }
                // One-time reset: browsers may have already cached a state
                // from when Email/Fellowship Status (columns 2 and 3)
                // defaulted to visible, before that default changed to
                // hidden — discard that stale state so the new default applies.
                if (data.columns && (data.columns[2]?.visible || data.columns[3]?.visible)) { return false; }
            },
            "initComplete": function () { hideLoader("examinerconfirmationtable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');

        $ec.on("submit", "form.ec-reset", function () {
            return confirm("Reset the availability confirmation for " + $(this).data("name") + "? This will clear their submitted availability.");
        });
        $ec.on("click", "a.ec-delete", function (e) {
            e.preventDefault();
            showDeleteModal($(this).data("id"), String($(this).data("name") || ""));
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Members
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#memberstable").length) {
        showLoader("memberstable");
        $("#memberstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("memberstable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#memberstable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Country Representatives
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#crstable").length) {
        showLoader("crstable");
        $("#crstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("crstable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#crstable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Hospitals
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#hospitalTable").length) {
        showLoader("hospitalTable");
        $("#hospitalTable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("hospitalTable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#hospitalTable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Result / report tables (no action column)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#resultstable").length) {
        showLoader("resultstable");
        $("#resultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},
                        {visible:true},{visible:true},{visible:true},{visible:true},{visible:true}],
            "initComplete": function () { hideLoader("resultstable"); }
        }).buttons().container().appendTo('#results_wrapper .col-md-6:eq(0)');
    }

    if ($("#adminresultstable").length) {
        showLoader("adminresultstable");
        $("#adminresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},
                        {visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true}],
            "initComplete": function () { hideLoader("adminresultstable"); }
        }).buttons().container().appendTo('#adminresultstable_wrapper .col-md-6:eq(0)');
    }

    if ($("#gsresultstable").length) {
        showLoader("gsresultstable");
        $("#gsresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},
                        {visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true}],
            "initComplete": function () { hideLoader("gsresultstable"); }
        }).buttons().container().appendTo('#gsresultstable_wrapper .col-md-6:eq(0)');
    }

    if ($("#fcsresultstable").length) {
        showLoader("fcsresultstable");
        $("#fcsresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "pageLength": 25,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": Array(22).fill({ "visible": true }),
            "initComplete": function () { hideLoader("fcsresultstable"); }
        }).buttons().container().appendTo('#fcsresultstable_wrapper .col-md-6:eq(0)');
    }

    // Accreditations (Hospital Accreditation tab + Accreditations page): rows
    // come from the partial's #hpRows JSON; deferRender builds only the page shown.
    if ($("#hospitalProgrammesTable").length) {
        var $hp = $("#hospitalProgrammesTable");
        var hpApp = $hp.data("app-url"), hpCanDelete = $hp.data("can-delete") === 1;
        showLoader("hospitalProgrammesTable");
        $hp.DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true, "deferRender": true,
            "data": JSON.parse((document.getElementById("hpRows") || {}).textContent || "[]"),
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"].map(function (b) {
                return b === "colvis" ? b : { extend: b, exportOptions: { orthogonal: "export" } };
            }),
            "columns": [
                { "data": "n" },
                { "data": "hospital", "render": function (d, type, r) {
                    return type === "display" ? '<a href="' + hpApp + '/admin/hospital/view_hospital/' + r.hid + '" class="entity-link">' + dtEsc(d) + '</a>' : (d || "");
                } },
                { "data": "programme", "render": function (d, type, r) {
                    return type === "display" ? '<a href="' + hpApp + '/admin/programmes/view/' + r.pid + '" class="entity-link">' + dtEsc(d) + '</a>' : (d || "");
                } },
                { "data": "country", "render": dtText },
                { "data": "acc", "render": dtText },
                { "data": "exp", "render": dtText },
                { "data": "status", "render": function (d, type, r) {
                    if (type !== "display") return d || "";
                    var low = String(d || "").toLowerCase(), expired = low === "expired";
                    return '<span class="dot dot-' + dtEsc(low) + '"></span>' + dtEsc(d) +
                        '<button type="button" class="btn btn-xs toggle-status-btn ml-1 ' + (expired ? "btn-outline-success" : "btn-outline-danger") + '"' +
                        ' data-hp-id="' + r.id + '" data-hospital-id="' + r.hid + '" data-programme-id="' + r.pid + '"' +
                        ' data-hospital="' + dtEsc(r.hospital) + '" data-programme="' + dtEsc(r.programme) + '"' +
                        ' data-accredited="' + dtEsc(r.acc) + '" data-expiry="' + dtEsc(r.exp) + '" data-flag="' + (expired ? "expired" : "active") + '"' +
                        ' title="' + (expired ? "Activate accreditation" : "Mark as expired") + '"><i class="fas fa-sync-alt"></i></button>';
                } },
                { "data": null, "orderable": false, "searchable": false, "className": "text-center", "render": function (d, type, r) {
                    if (type !== "display") return "";
                    var items = dtMenuItem(hpApp + "/admin/hospitalprogrammes/edit/" + r.id, "fas fa-edit text-warning", "Edit");
                    if (hpCanDelete) {
                        items += '<div class="dropdown-divider"></div>' +
                            dtMenuItem(hpApp + "/admin/hospitalprogrammes/delete/" + r.id, "fas fa-trash", "Delete", { cls: "text-danger", confirm: "Delete this programme record?" });
                    }
                    return dtActionMenu(items);
                } }
            ],
            "drawCallback": function () { reinitDropdowns(this); },
            "initComplete": function () { hideLoader("hospitalProgrammesTable"); }
        }).buttons().container().appendTo('#hospitalProgrammesTable_wrapper .col-md-6:eq(0)');
    }

    // ── Global: close open dropdowns when clicking outside any DataTable ──────────
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.dropdown').length) {
            $('.dataTables_wrapper .dropdown-menu.show').removeClass('show');
            $('.dataTables_wrapper .dropdown-toggle[aria-expanded="true"]').attr('aria-expanded', false);
        }
    });

    // ── Hospital accreditation date validation ───────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('form');
        if (!form) return;
        var accEl = document.getElementById('accredited_date');
        var expEl = document.getElementById('expiry_date');
        if (!accEl || !expEl) return;
        form.addEventListener('submit', function (event) {
            var dateRegex = /^\d{4}-(0[1-9]|1[0-2])$/;
            if (!dateRegex.test(accEl.value) || !dateRegex.test(expEl.value)) {
                event.preventDefault();
                alert('Please select a valid month and year.');
            }
        });
    });

});
