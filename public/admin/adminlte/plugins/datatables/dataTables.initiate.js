/**
 * Created by Abdul Rehman on 20-Jun-17.
 */


jQuery(document).ready(function ($) {
     $("#taskTable").DataTable({
        "pageLength":   100,
        /* Disable initial sort */
         lengthMenu: [ [100, 200, -1], ['100','200', 'All'] ],
        "aaSorting": [],
        "language": {
            "emptyTable": "No data available",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty": "Showing 0 to 0 of 0 entries",
            "infoFiltered": "(filtered from _MAX_ total entries)",
            "infoPostFix": "",
            "thousands": ",",
            "lengthMenu": "_MENU_ records per page",
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            "search": "Search: ",
            "zeroRecords": "No matching records found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            },
            "aria": {
                "sortAscending": ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            }
        },
    });
});

