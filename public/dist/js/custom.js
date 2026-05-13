$(function () {

    // ── Shared: re-init Bootstrap dropdowns after every DataTable draw ───────────
    function reinitDropdowns(tableEl) {
        $(tableEl).find('[data-toggle="dropdown"]').dropdown();
    }

    // ── Trainees ─────────────────────────────────────────────────────────────────
    // Columns: #(0) Name(1) Gender(2) AdmNo(3) Email(4) Programme(5) Hospital(6)
    //          Country(7) Status(8) | hidden: SFSUser(9) SFSPass(10) AdmLetter(11)
    //          InvLetter(12) AdmYear(13) ProgYear(14) ExamYear(15) ProgDuration(16)
    //          Invoice#(17) InvDate(18) InvStatus(19) Sponsor(20) ModePayment(21)
    //          AmtPaid(22) DatePaid(23) | Action(24)
    var traineeTable = (function () {
        if (!$("#traineestable").length) return { on: function () {} };
        return $("#traineestable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "pageLength": 25,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm",
                  title: "Trainees List", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23] } },
                { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",
                  title: "Trainees List", orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // 0  #
                { "visible": true  },  // 1  Name
                { "visible": true  },  // 2  Gender
                { "visible": true  },  // 3  Admission Number
                { "visible": true  },  // 4  Email
                { "visible": true  },  // 5  Programme
                { "visible": true  },  // 6  Hospital
                { "visible": true  },  // 7  Country
                { "visible": true  },  // 8  Status
                { "visible": false },  // 9  SFS Username
                { "visible": false },  // 10 SFS Password
                { "visible": false },  // 11 Admission Letter Status
                { "visible": false },  // 12 Invitation Letter Status
                { "visible": false },  // 13 Admission Year
                { "visible": false },  // 14 Programme Year
                { "visible": false },  // 15 Exam Year
                { "visible": false },  // 16 Programme Duration
                { "visible": false },  // 17 Invoice Number
                { "visible": false },  // 18 Invoice Date
                { "visible": false },  // 19 Invoice Status
                { "visible": false },  // 20 Sponsor
                { "visible": false },  // 21 Mode of Payment
                { "visible": false },  // 22 Amount Paid
                { "visible": false },  // 23 Date Paid
                { "visible": true,  "orderable": false, "searchable": false }  // 24 Action
            ],
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }());

    // ── Candidates ───────────────────────────────────────────────────────────────
    // Columns: #(0) Name(1) PEN(2) CandNo(3) ExamType(4) Hospital(5) Country(6)
    //          Gender(7) FeePaid(8) | hidden: Email(9) RepP1(10) RepP2(11)
    //          MMed(12) Sponsor(13) ExamYear(14) ModePayment(15) | Action(16)
    if ($("#candidatestable").length) {
        var candidateTable = $("#candidatestable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "pageLength": 25,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm",
                  title: "Candidates List", exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15] } },
                { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",
                  title: "Candidates List", orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm",
                  exportOptions: { columns: [1,2,3,4,5,6,7,8] } },
                { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // 0  #
                { "visible": true  },  // 1  Name
                { "visible": true  },  // 2  PEN
                { "visible": true  },  // 3  Cand. No.
                { "visible": true  },  // 4  Exam Type
                { "visible": true  },  // 5  Hospital
                { "visible": true  },  // 6  Country
                { "visible": true  },  // 7  Gender
                { "visible": true  },  // 8  Fee Paid
                { "visible": false },  // 9  Email
                { "visible": false },  // 10 Repeat P1
                { "visible": false },  // 11 Repeat P2
                { "visible": false },  // 12 MMed
                { "visible": false },  // 13 Sponsor
                { "visible": false },  // 14 Exam Year
                { "visible": false },  // 15 Mode of Payment
                { "visible": true,  "orderable": false, "searchable": false }  // 16 Action
            ],
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ── Fellows ──────────────────────────────────────────────────────────────────
    if ($("#fellowstable").length) {
        var fellowstable = $("#fellowstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "paging": true,
            "pageLength": 25,
            "stateSave": true,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm",
                  title: "Fellows List", exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",
                  title: "Fellows List", orientation: "landscape", pageSize: "A4", exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm",
                  exportOptions: { columns: [1,2,3,4,5,6] } },
                { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // 0 #
                { "visible": true  },  // 1 Name
                { "visible": true  },  // 2 Email
                { "visible": true  },  // 3 Country
                { "visible": true  },  // 4 Specialty
                { "visible": true  },  // 5 Fellowship Type
                { "visible": true  },  // 6 Fellowship Year
                { "visible": true,  "orderable": false, "searchable": false }  // 7 Action
            ],
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
                reinitDropdowns(this);
            }
        });
    }

    // ── Trainers / Programme Directors ───────────────────────────────────────────
    if ($("#trainerstable").length) {
        $("#trainerstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "paging": true,
            "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  },  // #
                { "visible": true  },  // Name
                { "visible": true  },  // Email
                { "visible": true  },  // Hospital
                { "visible": true  },  // Country
                { "visible": true  },  // Phone Number
                { "visible": true  },  // Assistant PD
                { "visible": true  },  // Asst PD Email
                { "visible": true  },  // Mobile Number
                { "visible": true,  "orderable": false, "searchable": false }  // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#trainerstable_wrapper .col-md-6:eq(0)');
    }

    // ── Examiners ────────────────────────────────────────────────────────────────
    if ($("#examinerstable").length) {
        $("#examinerstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "paging": true,
            "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  },  // #
                { "visible": true  },  // Name
                { "visible": true  },  // Email
                { "visible": true  },  // Country
                { "visible": true  },  // Examiner ID
                { "visible": true  },  // Exam Group
                { "visible": true,  "orderable": false, "searchable": false }  // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#examinerstable_wrapper .col-md-6:eq(0)');
    }

    // ── Examiner confirmation ────────────────────────────────────────────────────
    if ($("#examinerconfirmationtable").length) {
        $("#examinerconfirmationtable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "processing": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columnDefs": [
                { "targets": [9, 10], "visible": false },   // Mobile, Updated Date
                { "targets": [11], "orderable": false }      // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');
    }

    // ── Members ──────────────────────────────────────────────────────────────────
    if ($("#memberstable").length) {
        $("#memberstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }  // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#memberstable_wrapper .col-md-6:eq(0)');
    }

    // ── Country Representatives ──────────────────────────────────────────────────
    if ($("#crstable").length) {
        $("#crstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }  // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#crstable_wrapper .col-md-6:eq(0)');
    }

    // ── Hospitals ────────────────────────────────────────────────────────────────
    if ($("#hospitalTable").length) {
        $("#hospitalTable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": true,
            "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true  },
                { "visible": true,  "orderable": false, "searchable": false }  // Action
            ],
            "drawCallback": function () { reinitDropdowns(this); }
        }).buttons().container().appendTo('#hospitalTable_wrapper .col-md-6:eq(0)');
    }

    // ── Result / report tables (no action column) ─────────────────────────────────
    if ($("#resultstable").length) {
        $("#resultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false, "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true}]
        }).buttons().container().appendTo('#results_wrapper .col-md-6:eq(0)');
    }

    if ($("#adminresultstable").length) {
        $("#adminresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false, "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true}]
        }).buttons().container().appendTo('#adminresultstable_wrapper .col-md-6:eq(0)');
    }

    if ($("#gsresultstable").length) {
        $("#gsresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false, "stateSave": true, "paging": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": [{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true},{visible:true}]
        }).buttons().container().appendTo('#gsresultstable_wrapper .col-md-6:eq(0)');
    }

    if ($("#fcsresultstable").length) {
        $("#fcsresultstable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false, "stateSave": true, "paging": true,
            "pageLength": 25,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
            "columns": Array(22).fill({ "visible": true })
        }).buttons().container().appendTo('#fcsresultstable_wrapper .col-md-6:eq(0)');
    }

    if ($("#hospitalProgrammesTable").length) {
        $("#hospitalProgrammesTable").DataTable({
            "responsive": true, "lengthChange": true, "autoWidth": false, "paging": true, "stateSave": true,
            "buttons": ["copy", "csv", "excel", "pdf", "colvis"]
        }).buttons().container().appendTo('#hospitalProgrammesTable_wrapper .col-md-6:eq(0)');
    }

    // ── Global: close open dropdowns when clicking outside any DataTable ─────────
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
