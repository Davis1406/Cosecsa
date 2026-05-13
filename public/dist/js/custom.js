$(function () {

    // ── Loader styles (injected once into <head>) ────────────────────────────────
    if (!document.getElementById('dt-loader-style')) {
        var s = document.createElement('style');
        s.id = 'dt-loader-style';
        s.textContent = [
            /* overlay */
            '.dt-loader-overlay{position:absolute;inset:0;background:#fff;z-index:50;',
            'display:flex;flex-direction:column;overflow:hidden;border-radius:0 0 4px 4px;}',

            /* animated top bar */
            '.dt-bar-track{height:3px;background:#fbe8e8;width:100%;flex-shrink:0;}',
            '.dt-bar{height:100%;background:linear-gradient(90deg,#c0392b,#a02626);',
            'animation:dt-bar-anim 1.8s ease-in-out infinite;}',
            '@keyframes dt-bar-anim{',
            '0%{width:0%;opacity:1}60%{width:80%;opacity:1}',
            '90%{width:97%;opacity:.7}100%{width:0%;opacity:0}}',

            /* skeleton body */
            '.dt-skeleton{padding:16px 20px;flex:1;display:flex;flex-direction:column;gap:0;}',

            /* header row — slightly darker */
            '.dt-sk-hdr{display:flex;gap:10px;padding-bottom:13px;margin-bottom:13px;',
            'border-bottom:2px solid #f0f0f0;}',

            /* data rows */
            '.dt-sk-row{display:flex;gap:10px;margin-bottom:11px;align-items:center;}',

            /* cells */
            '.dt-sk-c{border-radius:6px;background:linear-gradient(',
            '90deg,#f2f2f2 25%,#e6e6e6 50%,#f2f2f2 75%);',
            'background-size:400% 100%;animation:dt-shimmer 1.6s ease infinite;flex-shrink:0;}',

            '.dt-sk-hdr .dt-sk-c{height:14px;',
            'background:linear-gradient(90deg,#eaeaea 25%,#dedede 50%,#eaeaea 75%);',
            'background-size:400% 100%;}',

            '.dt-sk-row .dt-sk-c{height:12px;}',

            /* stagger */
            '.dt-sk-row:nth-child(2) .dt-sk-c{animation-delay:.08s}',
            '.dt-sk-row:nth-child(3) .dt-sk-c{animation-delay:.16s}',
            '.dt-sk-row:nth-child(4) .dt-sk-c{animation-delay:.24s}',
            '.dt-sk-row:nth-child(5) .dt-sk-c{animation-delay:.32s}',
            '.dt-sk-row:nth-child(6) .dt-sk-c{animation-delay:.40s}',
            '.dt-sk-row:nth-child(7) .dt-sk-c{animation-delay:.48s}',
            '.dt-sk-row:nth-child(8) .dt-sk-c{animation-delay:.56s}',
            '.dt-sk-row:nth-child(9) .dt-sk-c{animation-delay:.64s}',

            '@keyframes dt-shimmer{',
            '0%{background-position:100% 0}100%{background-position:-100% 0}}'
        ].join('');
        document.head.appendChild(s);
    }

    // ── Skeleton HTML builder ────────────────────────────────────────────────────
    function makeSkeletonRow(isHeader) {
        var cls = isHeader ? 'dt-sk-hdr' : 'dt-sk-row';
        return '<div class="' + cls + '">' +
            '<div class="dt-sk-c" style="width:3%"></div>'   +
            '<div class="dt-sk-c" style="width:19%"></div>'  +
            '<div class="dt-sk-c" style="width:8%"></div>'   +
            '<div class="dt-sk-c" style="width:12%"></div>'  +
            '<div class="dt-sk-c" style="width:12%"></div>'  +
            '<div class="dt-sk-c" style="flex:1"></div>'     +
            '<div class="dt-sk-c" style="width:7%"></div>'   +
        '</div>';
    }

    function makeLoader() {
        var rows = makeSkeletonRow(true); // header
        for (var i = 0; i < 9; i++) rows += makeSkeletonRow(false);
        return '<div class="dt-loader-overlay" aria-hidden="true">' +
            '<div class="dt-bar-track"><div class="dt-bar"></div></div>' +
            '<div class="dt-skeleton">' + rows + '</div>' +
        '</div>';
    }

    // ── Show / hide helpers ──────────────────────────────────────────────────────
    function showLoader(id) {
        var $cb = $('#' + id).closest('.card-body');
        if (!$cb.length) return;
        $cb.css('position', 'relative');
        $cb.find('.dt-loader-overlay').remove();
        $cb.append(makeLoader());
    }

    function hideLoader(id) {
        $('#' + id).closest('.card-body').find('.dt-loader-overlay')
            .stop(true).fadeOut(380, function () { $(this).remove(); });
    }

    // ── Dropdown re-init after every draw ────────────────────────────────────────
    function reinitDropdowns(tableEl) {
        $(tableEl).find('[data-toggle="dropdown"]').dropdown();
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Trainees
    // Cols: #(0) Name(1) Gender(2) AdmNo(3) Email(4) Programme(5) Hospital(6)
    //       Country(7) Status(8) | hidden: SFSUser(9) SFSPass(10) AdmLetter(11)
    //       InvLetter(12) AdmYear(13) ProgYear(14) ExamYear(15) ProgDuration(16)
    //       Invoice#(17) InvDate(18) InvStatus(19) Sponsor(20) ModePayment(21)
    //       AmtPaid(22) DatePaid(23) | Action(24)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#traineestable").length) {
        showLoader("traineestable");
        $("#traineestable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "pageLength": 25,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Trainees List",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23] } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Trainees List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // 0  #
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": true,  "orderable": false, "searchable": false }  // 24 Action
            ],
            "initComplete": function () { hideLoader("traineestable"); },
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Candidates
    // Cols: #(0) Name(1) PEN(2) CandNo(3) ExamType(4) Hospital(5) Country(6)
    //       Gender(7) FeePaid(8) | hidden: Email(9) RepP1(10) RepP2(11)
    //       MMed(12) Sponsor(13) ExamYear(14) ModePayment(15) | Action(16)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#candidatestable").length) {
        showLoader("candidatestable");
        $("#candidatestable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "pageLength": 25,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Candidates List",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15] } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Candidates List",
                  orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "print", text: '<i class="fas fa-print mr-1"></i> Print',
                  className: "btn btn-secondary btn-sm", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "colvis", text: '<i class="fas fa-columns mr-1"></i> Columns',
                  className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // 0  #
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false }, { "visible": false }, { "visible": false },
                { "visible": false },
                { "visible": true,  "orderable": false, "searchable": false }  // 16 Action
            ],
            "initComplete": function () { hideLoader("candidatestable"); },
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Fellows — #(0) Name(1) Email(2) Country(3) Specialty(4) Type(5) Year(6) Action(7)
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#fellowstable").length) {
        showLoader("fellowstable");
        $("#fellowstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "pageLength": 25, "stateSave": true,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel',
                  className: "btn btn-success btn-sm", title: "Fellows List",
                  exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "pdfHtml5", text: '<i class="fas fa-file-pdf mr-1"></i> PDF',
                  className: "btn btn-danger btn-sm", title: "Fellows List",
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
            "initComplete": function () { hideLoader("fellowstable"); },
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Trainers / Programme Directors
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#trainerstable").length) {
        showLoader("trainerstable");
        $("#trainerstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("trainerstable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#trainerstable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Examiners
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#examinerstable").length) {
        showLoader("examinerstable");
        $("#examinerstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true  }, { "visible": true  }, { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }
            ],
            "initComplete": function () { hideLoader("examinerstable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#examinerstable_wrapper .col-md-6:eq(0)');
    }

    // ═══════════════════════════════════════════════════════════════════════════════
    // Examiner Confirmation
    // ═══════════════════════════════════════════════════════════════════════════════
    if ($("#examinerconfirmationtable").length) {
        showLoader("examinerconfirmationtable");
        $("#examinerconfirmationtable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "stateSave": true, "paging": true, "processing": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columnDefs": [
                { "targets": [9, 10], "visible": false },
                { "targets": [11], "orderable": false }
            ],
            "initComplete": function () { hideLoader("examinerconfirmationtable"); },
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');
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

    if ($("#hospitalProgrammesTable").length) {
        showLoader("hospitalProgrammesTable");
        $("#hospitalProgrammesTable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false,
            "paging": true, "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
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
