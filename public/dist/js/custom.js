$(function () {
    var traineeTable = $("#traineestable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false }
        ]
    }).buttons().container().appendTo('#traineestable_wrapper .col-md-6:eq(0)');

    var candidateTable = $("#candidatestable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false },
            { "visible": false }
        ]
    }).buttons().container().appendTo('#candidatestable_wrapper .col-md-6:eq(0)');


    var trainersTable = $("#trainerstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": false },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true }
        ]
    }).buttons().container().appendTo('#trainerstable_wrapper .col-md-6:eq(0)');


    var countryRepsTable = $("#crstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true }
        ]
    }).buttons().container().appendTo('#crstable_wrapper .col-md-6:eq(0)');

    var hospitalTable = $("#hospitalTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
        "columns": [
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true },
            { "visible": true }
        ]
    }).buttons().container().appendTo('#hospitalTable_wrapper .col-md-6:eq(0)');


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
    candidateTable.on('draw', function () {
        initPopovers();
    });

    trainersTable.on('draw', function () {
        initPopovers();
    });

    countryRepsTable.on('draw', function () {
        initPopovers();
    });

    hospitalTable.on('draw', function () {
        initPopovers();
    });
});
