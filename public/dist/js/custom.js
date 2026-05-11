$(function () {
    var traineeTable = (function () {
        if (!$("#traineestable").length) return { on: function () {} };
        var t = $("#traineestable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "stateSave": false,
            "paging": true,
            "pageLength": 25,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm", title: "Trainees List", exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
                { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",  title: "Trainees List", orientation: "landscape", pageSize: "A4", exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
                { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm", exportOptions: { columns: [0,1,2,3,4,5,6,7,8] } },
                { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false }, // #
                { "visible": true },  // Name
                { "visible": true },  // Gender
                { "visible": true },  // Admission Number
                { "visible": true },  // Email
                { "visible": true },  // Programme
                { "visible": true },  // Hospital
                { "visible": true },  // Country
                { "visible": true },  // Status
                { "visible": true,  "orderable": false, "searchable": false }, // Action
                { "visible": false }, // SFS Username
                { "visible": false }, // SFS Password
                { "visible": false }, // Admission Letter Status
                { "visible": false }, // Invitation Letter Status
                { "visible": false }, // Admission Year
                { "visible": false }, // Programme Year
                { "visible": false }, // Exam Year
                { "visible": false }, // Programme Duration
                { "visible": false }, // Invoice Number
                { "visible": false }, // Invoice Date
                { "visible": false }, // Invoice Status
                { "visible": false }, // Sponsor
                { "visible": false }, // Mode of Payment
                { "visible": false }, // Amount Paid
                { "visible": false }  // Date Paid
            ],
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1;
                    });
            }
        });
        return t;
    }());

    if ($("#candidatestable").length) {
    var candidateTable = $("#candidatestable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": false,
        "paging": true,
        "pageLength": 25,
        "order": [[1, "asc"]],
        "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
        "buttons": [
            { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm", title: "Candidates List",
              exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9,11,12,13,14,15,16,17] } },
            { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",  title: "Candidates List", orientation: "landscape", pageSize: "A4",
              exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] } },
            { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm",
              exportOptions: { columns: [0,1,2,3,4,5,6,7,8,9] } },
            { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
        ],
        "columns": [
            { "visible": true,  "orderable": false, "searchable": false }, // #
            { "visible": true },  // Name
            { "visible": true },  // PEN
            { "visible": true },  // Exam Type
            { "visible": true },  // Hospital
            { "visible": true },  // Country
            { "visible": true },  // Gender
            { "visible": true },  // Fee Paid
            { "visible": true },  // Invoice #
            { "visible": true },  // Amount
            { "visible": true,  "orderable": false, "searchable": false }, // Action
            { "visible": false }, // Email
            { "visible": false }, // Repeat P1
            { "visible": false }, // Repeat P2
            { "visible": false }, // MMed
            { "visible": false }, // Sponsor
            { "visible": false }, // Exam Year
            { "visible": false }  // Mode of Payment
        ],
        "drawCallback": function () {
            this.api().column(0, { search: "applied", order: "applied" })
                .nodes().each(function (cell, i) { cell.innerHTML = i + 1; });
        }
    });
    }


    var trainersTable = $("#trainerstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "stateSave": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": false
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#trainerstable_wrapper .col-md-6:eq(0)');


    if ($("#fellowstable").length) {
        var fellowstable = $("#fellowstable").DataTable({
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "paging": true,
            "pageLength": 25,
            "stateSave": false,
            "order": [[1, "asc"]],
            "dom": '<"row"<"col-md-4"l><"col-md-4"f><"col-md-4 text-right"B>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            "buttons": [
                { extend: "excelHtml5", text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: "btn btn-success btn-sm", title: "Fellows List", exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: "pdfHtml5",   text: '<i class="fas fa-file-pdf mr-1"></i> PDF',   className: "btn btn-danger btn-sm",  title: "Fellows List", orientation: "landscape", pageSize: "A4", exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: "print",      text: '<i class="fas fa-print mr-1"></i> Print',    className: "btn btn-secondary btn-sm", exportOptions: { columns: [0,1,2,3,4,5,6] } },
                { extend: "colvis",     text: '<i class="fas fa-columns mr-1"></i> Columns', className: "btn btn-outline-secondary btn-sm" }
            ],
            "columns": [
                { "visible": true,  "orderable": false, "searchable": false },
                { "visible": true },
                { "visible": true },
                { "visible": true },
                { "visible": true },
                { "visible": true },
                { "visible": true },
                { "visible": true, "orderable": false, "searchable": false }
            ],
            "drawCallback": function () {
                this.api().column(0, { search: "applied", order: "applied" })
                    .nodes().each(function (cell, i) {
                        cell.innerHTML = i + 1;
                    });
            }
        });
    }


    var examinerstable = $("#examinerstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "stateSave": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }

        ]
    }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');



    var examinerconfirmationtable = $("#examinerconfirmationtable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "processing": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columnDefs": [{
                "targets": [9, 10], // Hide Mobile Number and Updated Date columns
                "visible": false
            },
            {
                "targets": [11], // Make Action column non-orderable
                "orderable": false
            }
        ]
    }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');

    var memberstable = $("#memberstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }

        ]
    }).buttons().container().appendTo('#memberstable_wrapper .col-md-6:eq(0)');

    var countryRepsTable = $("#crstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#crstable_wrapper .col-md-6:eq(0)');

    var resultstable = $("#resultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#results_wrapper .col-md-6:eq(0)');


    var adminresultstable = $("#adminresultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#adminresultstable_wrapper .col-md-6:eq(0)');

    var gsresultstable = $("#gsresultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#gsresultstable_wrapper .col-md-6:eq(0)');


    var fcsresultstable = $("#fcsresultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "pageLength": 25,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": Array(22).fill({"visible": true}) // 22 columns in your table
    }).buttons().container().appendTo('#fcsresultstable_wrapper .col-md-6:eq(0)');



    var hospitalTable = $("#hospitalTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [{
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            },
            {
                "visible": true
            }
        ]
    }).buttons().container().appendTo('#hospitalTable_wrapper .col-md-6:eq(0)');

    var hospitalProgrammesTable = $("#hospitalProgrammesTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "stateSave": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"]
    }).buttons().container().appendTo('#hospitalProgrammesTable_wrapper .col-md-6:eq(0)');



    // Function to initialize popovers for action buttons
    function initPopovers() {
        $(document).on('click', '.action-icon', function () {
            $(this).popover({
                placement: 'right',
                trigger: 'focus',
                html: true,
                content: $(this).data('content')
            }).popover('toggle');
            return false;
        });
    }

    // Initialize popovers on page load
    initPopovers();

    // Reinitialize popovers after each draw for both tables
    traineeTable.on('draw', function () {
        initPopovers();
    });
    if (typeof candidateTable !== 'undefined') {
        candidateTable.on('draw', function () {
            initPopovers();
        });
    }

    trainersTable.on('draw', function () {
        initPopovers();
    });

    countryRepsTable.on('draw', function () {
        initPopovers();
    });

    hospitalTable.on('draw', function () {
        initPopovers();
    });
    fellowstable.on('draw', function () {
        initPopovers();
    });

    examinerstable.on('draw', function () {
        initPopovers();
    });

    resultstable.on('draw', function () {
        initPopovers();
    });

    adminresultstable.on('draw', function () {
        initPopovers();
    });

    gsresultstable.on('draw', function () {
        initPopovers();
    });

    fcsresultstable.on('draw', function () {
        initPopovers();
    });

    // examinerscandidatestable.on('draw', function () {
    //     initPopovers();
    // });

    examinerconfirmationtable.on('draw', function () {
        initPopovers();
    });


    memberstable.on('draw', function () {
        initPopovers();
    });

    hospitalProgrammesTable.on('draw', function () {
        initPopovers();
    });


    document.addEventListener('DOMContentLoaded', function () {
        // Ensure the values are formatted as yyyy-mm on form submission
        document.querySelector('form').addEventListener('submit', function (event) {
            const accreditedDateInput = document.getElementById('accredited_date');
            const expiryDateInput = document.getElementById('expiry_date');

            // Get values
            const accreditedDate = accreditedDateInput.value;
            const expiryDate = expiryDateInput.value;

            // Validate the date format if necessary
            // Example: yyyy-mm
            const dateRegex = /^\d{4}-(0[1-9]|1[0-2])$/;

            if (!dateRegex.test(accreditedDate) || !dateRegex.test(expiryDate)) {
                event.preventDefault();
                alert('Please select a valid month and year.');
                return false;
            }
        });
    });

});
