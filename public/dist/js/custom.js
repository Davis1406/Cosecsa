$(function () {
    var traineeTable = $("#traineestable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
            { "visible": false },
            { "visible": false }
        ]
    }).buttons().container().appendTo('#traineestable_wrapper .col-md-6:eq(0)');

    var candidateTable = $("#candidatestable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
        "stateSave": true,
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


    var fellowstable = $("#fellowstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "stateSave": true,
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
    }).buttons().container().appendTo('#fellowstable_wrapper .col-md-6:eq(0)');


    var examinerstable = $("#examinerstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "paging": true,
        "stateSave": true,
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
    }).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');

    var examinerconfirmationtable = $("#examinerconfirmationtable").DataTable({
    "responsive": true,
    "lengthChange": true,
    "autoWidth": false,
    "stateSave": true,
    "paging": true,
    "buttons": ["copy", "csv", "excel", "pdf", "colvis"],
    "columns": [
        { "visible": true }, // #
        { "visible": true }, // Name
        { "visible": true }, // Email
        { "visible": true }, // Country
        { "visible": true }, // Specialty
        { "visible": true }, // Availability
        { "visible": true }, // MCS Shift
        { "visible": true }, // Participation
        { "visible": true }, // Hospital
        { "visible": true } // Action
    ]
}).buttons().container().appendTo('#examinerconfirmationtable_wrapper .col-md-6:eq(0)');

    var memberstable = $("#memberstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
    }).buttons().container().appendTo('#memberstable_wrapper .col-md-6:eq(0)');

    var countryRepsTable = $("#crstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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

    var resultstable = $("#resultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
            { "visible": true }
        ]
    }).buttons().container().appendTo('#results_wrapper .col-md-6:eq(0)');


    var adminresultstable = $("#adminresultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
            { "visible": true }
        ]
    }).buttons().container().appendTo('#adminresultstable_wrapper .col-md-6:eq(0)');

    var gsresultstable = $("#gsresultstable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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
            { "visible": true }
        ]
    }).buttons().container().appendTo('#gsresultstable_wrapper .col-md-6:eq(0)');


    var hospitalTable = $("#hospitalTable").DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "stateSave": true,
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

    
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure the values are formatted as yyyy-mm on form submission
        document.querySelector('form').addEventListener('submit', function(event) {
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