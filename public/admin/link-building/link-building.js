/**
 * Created by Usman Manzoor on 9/6/2018.
 */

/*--------------------Popover--------------------------------*/
$('#CSViconTooltip').popover({
    html: true
});

$('#CSViconTooltip').on('inserted.bs.popover', function () {
    $('.popover').css({
        'color': 'red'
    });
});

$('body').on('mouseover', '#CSViconTooltip', function(e){
    var isDisabled=$('#CSViconTooltip').hasClass('disabled');
    if(isDisabled){
        $('#CSViconTooltip').popover('show');
    }
});

$('body').on('mouseleave', '#CSViconTooltip', function(e){
    $('#CSViconTooltip').popover('hide');
});
/*--------------------Popover--------------------------------*/

function initializeDataTable(){
    window.linkBuildingTable = $("#linkBuildingTable").DataTable({
        "pageLength": 25,
        /* Disable initial sort */
        "aaSorting": [],
        "language": {
            "emptyTable":     "No data available in table",
            "info":           "Showing _START_ to _END_ of _TOTAL_ entries",
            "infoEmpty":      "Showing 0 to 0 of 0 entries",
            "infoFiltered":   "(filtered from _MAX_ total entries)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "_MENU_ records per page",
            "loadingRecords": "Loading...",
            "processing":     "Processing...",
            "search":         "Search: ",
            "zeroRecords":    "No matching records found",
            "paginate": {
                "first":      "First",
                "last":       "Last",
                "next":       "Next",
                "previous":   "Previous"
            },
            "aria": {
                "sortAscending":  ": activate to sort column ascending",
                "sortDescending": ": activate to sort column descending"
            }
        }
    });
}

jQuery(document).ready(function($) {

    $('.chosen-select').selectpicker();

    initializeDataTable();

    function showPreloader() {
        var preloader = $('.preloader');
        preloader.show();
        $("body").addClass('hide-overflow');
        preloader.addClass('preloader-opacity');
    }

    function hidePreloader() {
        var preloader = $('.preloader');
        preloader.removeClass('preloader-opacity');
        $("body").removeClass('hide-overflow');
        preloader.hide();
    }

    $('#update-card-details-modal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    var windowHeight = window.innerHeight - 100;
    $('.full-page-view').css({ 'margin-bottom':'0','min-height':windowHeight});

    $(document).on("click", "#uploadCSV", function(){
        var isDisabled=$(this).parent().hasClass('disabled');
        if(!isDisabled){
            var $el = $('#fileUploadForm');
            $el.wrap('<form>').closest('form').get(0).reset();
            $el.unwrap();

            var fileError = $(".error");
            fileError.hide();
            fileError.html('');

            $('#update-card-details-modal').modal('show');
        }
    });

    function addLinkBuildingCSV(business_id){
        var baseUrl = $('#hfBaseUrl').val();

        var formData = false;
        if (window.FormData) formData = new FormData();
        var file_data = document.getElementById('lbCSVfile').files[0];
        formData.append('business_id', business_id);
        formData.append('file', file_data);

        var fileError = $(".error");
        fileError.hide();
        fileError.html('');

        $('#upload_csv').button('loading');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url:  baseUrl + "/admin/add-link-building-csv",
            contentType: false,
            cache: false,
            processData: false,
            data: formData,
            success: function (response, status) {
                var statusCode = response._metadata.outcomeCode;
                var statusMessage = response._metadata.outcome;
                var message = response._metadata.message;
                var numOfRecords = response._metadata.numOfRecords;
                var records = response.records;
                var errors = response.errors;

                if (statusCode == 200) {
                    $('#update-card-details-modal').modal('hide');

                    swal({
                        title: "",
                        text: message,
                        type: 'success',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },function(){
                        swal.close();
                        showPreloader();
                        getLinkBuildingByBusinessId(business_id);
                    });
                }
                else{
                    fileError.show();
                    fileError.html(message);
                }
                $('#upload_csv').button('reset');
            },
            error: function (data, status) {
                fileError.show();
                fileError.html('OOPs! Something went wrong...');
                $('#upload_csv').button('reset');
            }
        })
    }

    $(document).on("click", "#upload_csv", function(e){
        e.preventDefault();
        var business_id=$('#clients-dropdown').find(":selected").attr('data-business-id');

        if(typeof(business_id)=='undefined' ||(business_id=='null') ||(business_id==null ||(business_id==''))){
            var fileError = $(".error");
            fileError.hide();
            fileError.html('');
            fileError.show();
            fileError.html('OOPs! Something went wrong...');
            return false;
        }

        if( document.getElementById("lbCSVfile").files.length == 0 ){
            var fileError = $(".error");
            fileError.hide();
            fileError.html('');
            fileError.show();
            fileError.html('File is required. Please upload CSV file.');
            return false;
        }
        addLinkBuildingCSV(business_id);
    });

    function getLinkBuildingByBusinessId(business_id){
        var baseUrl = $('#hfBaseUrl').val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "GET",
            url:  baseUrl + "/admin/get-link-building-by-business-id?business_id="+business_id,
            success: function (response, status) {
                var statusCode = response._metadata.outcomeCode;
                var statusMessage = response._metadata.outcome;
                var message = response._metadata.message;
                var numOfRecords = response._metadata.numOfRecords;
                var records = response.records;
                var errors = response.errors;

                if (statusCode == 200) {
                    window.linkBuildingTable.destroy();

                    $('#linkBuildingTable tbody').empty();
                    var x;
                    for(x in records){
                        $('#linkBuildingTable tbody').append('' +
                        '<tr>'+
                        '<td>'+records[x].directory+'</td>'+
                        '<td>'+records[x].link+'</td>'+
                        '<td>'+records[x].user_name+'</td>'+
                        '<td>'+records[x].email+'</td>'+
                        '<td>'+records[x].password+'</td>'+
                        '</tr>');
                    }
                }
                initializeDataTable();
                hidePreloader();
            },
            error: function (data, status) {
                hidePreloader();
            }
        })
    }

    $(document).on("change", "#clients-dropdown", function(e){
        e.preventDefault();
        var business_name=$(this).val();
        var business_id=$(this).find(":selected").attr('data-business-id');
        if(typeof(business_id)!=='undefined' ||(business_id!='null') ||(business_id!=null ||(business_id!=''))){
            $('#uploadCSV').parent().removeClass('disabled');
            showPreloader();
            getLinkBuildingByBusinessId(business_id);
        }
    });
});

formError = true;
// We can attach the `fileselect` event to all file inputs on the page
$(document).on('change', ':file', function() {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
});

$(':file').on('fileselect', function(event, numFiles, label) {
    var ext = $('#lbCSVfile').val().split('.').pop().toLowerCase();
    var input = $(this).parents('.input-group').find(':text'),
        log = numFiles > 1 ? numFiles + ' files selected' : label;
    var fileError = $(".error");

    if(log) {
        if ($.inArray(ext, ['csv']) == -1) {
            fileError.show();
            fileError.html(' Invalid Format, Please upload CSV file.');
            input.val('');

            var $el = $('#fileUploadForm');
            $el.wrap('<form>').closest('form').get(0).reset();
            $el.unwrap();
        }
        else {
            fileError.hide();
            fileError.html('');

            if (input.length) {
                input.val(log);
                formError = false;
            } else {
                if (log) alert(log);
            }
        }
    }
});
