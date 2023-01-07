function sendEmailSMSBackgroundSevice(first_id,flag){
    var baseUrl = $('#hfBaseUrl').val();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url:  baseUrl + "/crm-background-service",
        data:{
            first_id: first_id,
            flag: flag
        },
        success: function (response, status) {
            console.log(response);
            var statusCode = response._metadata.outcomeCode;
            var statusMessage = response._metadata.outcome;
            var message = response._metadata.message;
            var errors = response.errors;
            var records = response.records;

            if(statusCode==200){
            }
            else{
            }
        },
        error: function (data, status) {
        }
    })
}

function loadAddSingleCustomerStep2Settings(){
    console.log(reviewRequestSettingsData);

    var enableGetReviews= reviewRequestSettingsData.enable_get_reviews;
    var sendingOption= reviewRequestSettingsData.sending_option;
    var smart_routing= reviewRequestSettingsData.smart_routing;
    var review_site= reviewRequestSettingsData.review_site;
    var reminder= reviewRequestSettingsData.reminder;
    var customize_email= reviewRequestSettingsData.customize_email;
    var customize_sms= reviewRequestSettingsData.customize_sms;

    if(enableGetReviews==''){
        enableGetReviews=null;
    }

    if(sendingOption==''){
        sendingOption=null;
    }

    if(smart_routing==''){
        smart_routing=null;
    }

    if(review_site==''){
        review_site=null;
    }

    if(reminder==''){
        reminder=null;
    }

    if(typeof(customize_email)=='undefined' || customize_email==null || customize_email==''){
        $('#email-preview-wrap .content-body').removeClass('editor-mode');
        $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content">Hi there!\n' +
            'Thanks for choosing '+businessName+'. If you have a ' +
            'few minutes, I\'d like to invite you to tell us about your ' +
            'experience. Your feedback is very important to us and it would be ' +
            'awesome if you can share it with us and our potential customers.' +
            '<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>' +
            '</p>' +
            '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#email-preview-wrap .content-body').attr('data-default','true');
    }
    else{
        $('#email-preview-wrap .content-body').removeClass('editor-mode');
        $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content"></p>' +
            '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#email-preview-wrap .content-body').attr('data-default','false');
        $('#email-content').html(customize_email+'<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>');
    }

    if(typeof(customize_sms)=='undefined' || customize_sms==null || customize_sms==''){
        $('#sms-preview-wrap .content-body').removeClass('editor-mode');
        $('#sms-preview-wrap .content-body').html('' +
            '<p class="sms-content" id="sms-content">Hi there!\n' +
            'Thanks for choosing '+businessName+'. I\'d like to invite you to tell us about your experience. Any feedback is appreciated.' +
            '<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>' +
            '</p>' +
            '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#sms-preview-wrap .content-body').attr('data-default','true');
    }
    else{
        $('#sms-preview-wrap .content-body').removeClass('editor-mode');
        $('#sms-preview-wrap .content-body').html('' +
            '<p class="sms-content" id="sms-content"></p>' +
            '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#sms-preview-wrap .content-body').attr('data-default','false');
        $('#sms-content').html(customize_sms+'<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>');
    }

    console.log(enableGetReviews);
    console.log(sendingOption);

    if(enableGetReviews=='Yes'){
        $('#sending_option_panel').removeClass('hide');
        $('#smart_routing_panel').removeClass('hide');
        $('#send_reminder_panel').removeClass('hide');

        var val=sendingOption;
        if(val=='1'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
        else if(val=='2'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
        else if(val=='3'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').addClass('hide');
        }
        else if(val=='4'){
            $('#preview_panel').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
            $('.email-preview-wrap').addClass('hide');
        }
        else if(val=='5'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
    }

    $('#enable_get_reviews').selectpicker('val', enableGetReviews);
    $('#sending_option').selectpicker('val', sendingOption);
    $('#smart_routing').selectpicker('val', smart_routing);
    $('#review_site').selectpicker('val', review_site);
    $('#send_reminder').selectpicker('val', reminder);

    if(enableGetReviews=='Yes' && smart_routing=='Disable' ){
        $('#review_site_panel').removeClass('hide');
    }
}

function ValidateEmail(mail) {
    if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(mail))
    {
        return true;
    }
    else{
        return false;
    }
}

// Function to remove array element
function removeA(arr) {
    var what, a = arguments, L = a.length, ax;
    while (L > 1 && arr.length) {
        what = a[--L];
        while ((ax= arr.indexOf(what)) !== -1) {
            arr.splice(ax, 1);
        }
    }
    return arr;
}

function checkUpdateCustomerButtonStatus(){
    var contactFirstName = $.trim($("#edit_contact_form #first_name").val());
    var contactLastName = $.trim($("#edit_contact_form #last_name").val());
    var contactEmailAddress = $.trim($("#edit_contact_form #email").val());
    var contactPhoneNumber = $.trim($("#edit_contact_form #phone_number").val());

    if(window.editCustomerFirstName==contactFirstName && window.editCustomerLastName==contactLastName && window.editCustomerEmail==contactEmailAddress && window.editCustomerPhoneNumber==contactPhoneNumber){
        $('#update-contact').addClass('disabled').attr('disabled','disabled');

        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#add_contact_form #error_Msg").addClass('hide-me');
        $("#edit_contact_form #error_Msg2").addClass('hide-me');
    }
    else{
        $('#update-contact').removeClass('disabled').removeAttr('disabled');
    }
}

function updatedBulkDeleteButton(customersListTable){
    var noOfSelectedRows= window.selectedCustomersIndexes.length;
    if(noOfSelectedRows!=0){
        $("#delete_customers_button").removeClass('hide').addClass('show-inline-block');
        $("#delete_customers_button").parent().removeClass('hide').addClass('show-inline-block');
        //$("#num_selected_records").text(noOfSelectedRows);
    }
    else{
        $("#delete_customers_button").removeClass('show-inline-block').addClass('hide');
        $("#delete_customers_button").parent().removeClass('show-inline-block').addClass('hide');
        //$("#num_selected_records").text('0');
    }
}

function createModalForEdit(){
    var id = 'edit_contact_modal';

    var html = '';
    html += '<div class="edit_contact_modal">';

    html += '<form id="edit_contact_form">';

    html += '<div class="form-group">';
    html += '<div class="row">';
    html += '<div class="col-md-3">';
    html += '<label>First Name</label>';
    html += '</div>';
    html += '<div class="col-md-9">';
    html += '<input type="text" id="first_name" class="form-control">';
    html += '<input type="hidden" id="edit-customer-id">';
    html += '<span class="help-block hide-me"><small></small></span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    html += '<div class="form-group">';
    html += '<div class="row">';
    html += '<div class="col-md-3">';
    html += '<label>Last Name</label>';
    html += '</div>';
    html += '<div class="col-md-9">';
    html += '<input type="text" id="last_name" class="form-control">';
    html += '<span class="help-block hide-me"><small></small></span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';


    html += '<div class="form-group">';
    html += '<div class="row">';
    html += '<div class="col-md-3">';
    html += '<label>Email Address</label>';
    html += '</div>';
    html += '<div class="col-md-9">';
    html += '<input type="email" id="email" class="form-control">';
    html += '<span class="help-block hide-me"><small></small></span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';


    html += '<div class="form-group">';
    html += '<div class="row">';
    html += '<div class="col-md-3">';
    html += '<label>Phone Number</label>';
    html += '</div>';
    html += '<div class="col-md-9">';
    html += '<input type="text" id="phone_number" placeholder="+1" class="form-control">';
    html += '<span class="help-block hide-me"><small></small></span>';
    html += '<span class="help-text-phone-number hide"><small>The country code is required and it has been pre-filled for you. Start adding the phone number after the country code.</small></span>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    html += '<span id="error_Msg2" class="help-block hide-me"><small></small></span>';
    html += '</form>';

    html +='<div class="modal-footer">';
    html +='<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    html +='<button type="button" id="update-contact" class="btn btn-info" data-title="There are no changes to update." data-loading-text="<i class=\'fa fa-spinner fa-spin \'></i> Please wait...">Update Customer</button>';
    html +='</div>';

    html +='</div>';

    loadModalForDetail(id);

    $(".modal-title", '#'+id).remove();
    $(".modal-header #titleHeadingText", '#'+id).remove();
    $('.modal-header', '#'+id).append('<h4 class="modal-title">Update Customer</h4>');
    $('.modal-body', '#'+id).css('padding-top', '15px');
    $('.modal-body', '#'+id).html(html);

    $('#'+id+' .modal-dialog').css('width','39%');

    $("form#edit_contact_form input[type='text'], form#edit_contact_form input[type='email']").off('blur keyup');

    $("form#edit_contact_form input[type='text'], form#edit_contact_form input[type='email']").on('blur keyup', function(){
        var input=$(this);
        var check=input.hasClass('fieldToValidate');
        console.log(check);
        if(check){
            var spanEl=input.next("span");
            if(input.val()==''){
                spanEl.removeClass('hide-me').addClass('errorMsg');
                input.closest('.form-group').addClass('has-error');
                $("small", spanEl).html("Required Field");
            }
            else{
                if(input.attr('type')=='email'){
                    var email=input.val();
                    var validEmailCheck=ValidateEmail(email);
                    if(validEmailCheck==false){
                        spanEl.removeClass('hide-me').addClass('errorMsg');
                        input.closest('.form-group').addClass('has-error');
                        $("small", spanEl).html("Enter Valid Email");
                    }
                    else{
                        spanEl.addClass('hide-me').removeClass('errorMsg');
                        input.closest('.form-group').removeClass('has-error');
                        $("small", spanEl).text("");
                    }
                }
                else{
                    spanEl.addClass('hide-me').removeClass('errorMsg');
                    input.closest('.form-group').removeClass('has-error');
                    $("small", spanEl).text("");
                }
            }
        }
        checkUpdateCustomerButtonStatus();
    });

    $('form#edit_contact_form input').off("keypress");
    $('form#edit_contact_form input').on("keypress", function(e) {
        if(e.which == 13) {
            $('#update-contact').trigger('click');
        }
    });

}

function getCustomerDetail(customerID){
    var baseUrl = $('#hfBaseUrl').val();
    var data={
        customerID: customerID
    };
    showPreloader();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url:  baseUrl + "/crm-customers-detail",
        data: data,
        success: function (response, status) {
            console.log(response);
            var statusCode = response._metadata.outcomeCode;
            var statusMessage = response._metadata.outcome;
            var message = response._metadata.message;
            var errors = response.errors;
            var records = response.records;

            if(statusCode==200){
                var id = response.records.id;
                var email = response.records.email;
                var phone_number = response.records.phone_number;
                var created_at = response.records.created_at;
                var first_name = response.records.first_name;
                var last_name = response.records.last_name;
                var name = response.records.name;

                if(response.records.email==null){
                    window.editCustomerEmail = '';
                }
                else{
                    window.editCustomerEmail = response.records.email;
                }

                if(response.records.phone_number==null){
                    window.editCustomerPhoneNumber = '';
                }
                else{
                    window.editCustomerPhoneNumber = response.records.phone_number;
                }

                if(response.records.first_name==null){
                    window.editCustomerFirstName = '';
                }
                else{
                    window.editCustomerFirstName = response.records.first_name;
                }

                if(response.records.last_name==null){
                    window.editCustomerLastName = '';
                }
                else{
                    window.editCustomerLastName = response.records.last_name;
                }

                createModalForEdit();

                phone_number=$.trim(phone_number);
                if(phone_number=='' || phone_number==null || phone_number=='null' || typeof(phone_number)=='undefined'){
                    phone_number = '+1';
                    $("#edit_contact_form .help-text-phone-number").removeClass('hide');
                }
                else{
                    $("#edit_contact_form .help-text-phone-number").addClass('hide');
                }

                $("#edit_contact_form #edit-customer-id").val(id);
                $("#edit_contact_form #first_name").val(first_name);
                $("#edit_contact_form #last_name").val(last_name);
                $("#edit_contact_form #email").val(email);
                $("#edit_contact_form #phone_number").val(phone_number);
                checkUpdateCustomerButtonStatus();
                $('#edit_contact_modal').modal('show');
            }
            else{
                if(errors.length!=0) {
                    $.each(errors, function (index, value) {
                        toastr.error(value.message);
                    })
                }
                else{
                    swal({
                        title: "",
                        text: message,
                        type: 'error',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    });
                }
            }
            hidePreloader();
        },
        error: function (data, status) {
            swal({
                title: "",
                text: "OOPs! Something went wrong...",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            hidePreloader();
        }
    })
}

function deleteCustomer(customerID,customersListTable){
    var baseUrl = $('#hfBaseUrl').val();
    showPreloader();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "GET",
        url:  baseUrl + "/crm-delete-customer?customerID="+customerID,
        success: function (response, status) {
            console.log(response);
            var statusCode = response._metadata.outcomeCode;
            var statusMessage = response._metadata.outcome;
            var message = response._metadata.message;
            var errors = response.errors;
            var records = response.records;

            if(statusCode==200){

                // var dataTablePageInfo = customersListTable.page.info();
                // var arrayCheck=Array.isArray(customerID);
                // console.log(arrayCheck);
                // if(arrayCheck==true){
                //     $.each(customerID, function (index, value) {
                //         customersListTable.row('[data-customer-id="'+value+'"]').remove().draw();
                //     });
                // }
                // else{
                //     customersListTable.row('[data-customer-id="'+customerID+'"]').remove().draw();
                // }

                $('#customers-list thead tr th:eq(0)').removeClass('selected');
                customersListTable.rows().deselect();
                window.selectedCustomersIndexes=[];
                updatedBulkDeleteButton(customersListTable);

                // var currentDataTablePageInfo = customersListTable.page.info();
                // if(dataTablePageInfo.pages==currentDataTablePageInfo.pages){
                //     customersListTable.page(dataTablePageInfo.page).draw( 'page' );
                // }

                customersListTable.draw();

                // $("#total_customers").text(currentDataTablePageInfo.recordsTotal);

                swal({
                    title: "",
                    text: message,
                    type: 'success',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                },function(){
                    swal.close();
                });
            }
            else{
                if(errors.length!=0) {
                    $.each(errors, function (index, value) {
                        toastr.error(value.message);
                    })
                }
                else{
                    swal({
                        title: "",
                        text: message,
                        type: 'error',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    });
                }
            }
            hidePreloader();
        },
        error: function (data, status) {
            swal({
                title: "",
                text: "OOPs! Something went wrong...",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            hidePreloader();
        }
    })
}

function addCustomersCSV(file_data){
    var baseUrl = $('#hfBaseUrl').val();

    var formData = false;
    if (window.FormData) formData = new FormData();

    formData.append('file', file_data);

    // var fileError = $(".upload_CSV_modal .error");
    // fileError.hide();
    // fileError.html('');

    // $('#upload_csv').button('loading');
    showPreloader();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url:  baseUrl + "/crm-upload-customer-csv",
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
                // $('#uploadCustomersCSVFile').val('');
                // var $el = $('#fileUploadForm');
                // $el.wrap('<form>').closest('form').get(0).reset();
                // $el.unwrap();
                //
                // window.csvFileArray = [];
                // $('#csvFileName').text('');

                $('#upload_csv').attr('data-already-added', 'true');

                $('#customizeReviewRequestsBtn').removeAttr('data-customer-id');
                $('#customizeReviewRequestsBtn').removeAttr('data-varification-code');

                $('#customizeReviewRequestsBtn').attr('data-type', 'multiple');
                $('#customizeReviewRequestsBtn').attr('data-first_id', records.first_id);
                $('#customizeReviewRequestsBtn').attr('data-flag', records.flag);

                $('#customizeReviewRequestsBtn').text('Add Multiple and Send Review Request');

                $('.st-1-header').hide();
                $('.st-2-header').show();
                $('.st-3-header').hide();

                $('#addMultipleCustomerStep3').modal('hide');

                loadAddSingleCustomerStep2Settings();

                $('span.help-block small').text('');
                $('span.help-block').closest('.form-group').removeClass('has-error');
                $('span.help-block').addClass('hide-me').removeClass('errorMsg');
                $("#addCustomerStep2 .error_Msg").addClass('hide-me');

                $('#addCustomerStep2').modal('show');
            }
            else{
                if(errors.length!=0) {
                    $.each(errors, function (index, value) {
                        toastr.error(value.message);
                    })
                }
                else{
                    swal({
                        title: "",
                        text: message,
                        type: 'error',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    });
                    hidePreloader();
                }
            }
            hidePreloader();
        },
        error: function (data, status) {
            swal({
                title: "",
                text: 'OOPs! Something went wrong...',
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            hidePreloader();
        }
    })
}

window.selectedCustomersIndexes=[];

window.csvFileArray = [];

$(document).ready( function () {

    $('#addCustomerStep2 .selectpicker').selectpicker({
        noneSelectedText : 'Please Select' // by this default 'Nothing selected' -->will change to Please Select
    });

    $('.send_review_requests_tooltip').tooltip({
        title:'Click to learn more',
        html: true,
        placement: 'right',
        trigger: 'manual'
    });

    $('.how_to_send_review_requests_tooltip').tooltip({
        title:'<a target="_blank" class="crm_dropdown_tooltip" href="'+HowtoSendReviewRequestsTooltip+'">Click</a> to learn more',
        html: true,
        placement: 'right',
        trigger: 'manual'
    });

    $('.smart_routing_tooltip').tooltip({
        title:'<a target="_blank" class="crm_dropdown_tooltip" href="'+smartRoutingTooltip+'">Click</a> to learn more',
        html: true,
        placement: 'right',
        trigger: 'manual'
    });

    $('.review_site_tooltip').tooltip({
        title:'Click to learn more',
        html: true,
        placement: 'right',
        trigger: 'manual'
    });

    $('.send_reminder_tooltip').tooltip({
        title:'Click to learn more',
        html: true,
        placement: 'right',
        trigger: 'manual'
    });

    $('.send_review_requests_tooltip').on("click", function (e) {
        $(".tooltip").tooltip("hide");
        $(".send_review_requests_tooltip").tooltip("show");
    });

    $('.how_to_send_review_requests_tooltip').on("click", function (e) {
        $(".tooltip").tooltip("hide");
        $(".how_to_send_review_requests_tooltip").tooltip("show");
    });

    $('.smart_routing_tooltip').on("click", function (e) {
        $(".tooltip").tooltip("hide");
        $(".smart_routing_tooltip").tooltip("show");
    });

    $('.review_site_tooltip').on("click", function (e) {
        $(".tooltip").tooltip("hide");
        $(".review_site_tooltip").tooltip("show");
    });

    $('.send_reminder_tooltip').on("click", function (e) {
        $(".tooltip").tooltip("hide");
        $(".send_reminder_tooltip").tooltip("show");
    });

    $('body').on('click',function (e){
        if(!$(e.target).hasClass('crm_tooltip')) {
            $(".send_review_requests_tooltip,.how_to_send_review_requests_tooltip,.smart_routing_tooltip,.review_site_tooltip,.send_reminder_tooltip").tooltip("hide");
        }
    });

    $('#countryList,#countryCodesList').selectpicker({
        liveSearch: true
    });

    $('#countryList').selectpicker('val', '');
    $('#countryCodesList').selectpicker('val', '');

    $('#countryList').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        $('#countryCodesList').selectpicker('val', $('option:selected', this).attr("data-dial-code"));
    });

    $('.modal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    var baseUrl = $('#hfBaseUrl').val();

    //$('[data-toggle="popover"]').popover();

    $('.selectpicker').selectpicker();

    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    function generateIconColor(){
        var num = Math.floor((Math.random() * 4) + 1);
        var customerNameIconColorClass="";
        if(num==1){
            var customerNameIconColorClass="customer-name-icon-orange-color";
        }
        else if(num==2){
            var customerNameIconColorClass="customer-name-icon-red-color";
        }
        else if(num==3){
            var customerNameIconColorClass="customer-name-icon-yellow-color";
        }
        else if(num==4){
            var customerNameIconColorClass="customer-name-icon-blue-color";
        }
        return customerNameIconColorClass;
    }

    var height=$('body').height()-150;
    console.log(height);

    let customersListTable =$('#customers-list').DataTable({
        processing: true,
        serverSide: true,
        // pageLength: 20,
        // lengthChange: true,
        // lengthMenu: [[10, 20, 50, 100], ['10 Rows', '25 Rows', '50 Rows', '100 Rows']],
        searching: false,
        ordering: true,

        "order": [[ 1, "asc" ]],
        info: true,
        language: {
            emptyTable: "Customers not found.",
            paginate: {
                first: "First",
                previous: "<i class='fa fa-caret-left'></i>",
                next: "<i class='fa fa-caret-right'></i>",
                last:  "Last"
            },
            "lengthMenu": "_MENU_ ",
            "info": "_START_ to _END_ of _TOTAL_",
            "infoEmpty": "0 of 0",
            processing: '<img class="web-loader" src="'+baseUrl+'/public/images/transparent_loader.gif" style="display: table; width: 48px; margin: 0px auto;">'
        },
        columnDefs: [
            { "orderable": false, "targets": 0 },
            { "orderable": false, "targets": 5 },
            {
                orderable: false,
                className: 'select-checkbox',
                targets: 0
            }
        ],
        select: {
            style: 'multi',
            selector: 'td:first-child' //td:first-child //tr td.select
        },
        //dom: '<"top"i>rt<"bottom"flp><"clear">'
        //dom: '<"wrapper"ipt>',
        dom: '<"pagination-container"iptr>',
        ajax: baseUrl + "/crm-customers-list",
        "columns": [
            { "data": "extra" },
            { "data": "name" },
            { "data": "email" },
            { "data": "phone_number" },
            { "data": "created_at" },
            { "data": "extra" }
        ],
        drawCallback: function( settings ) {
            var data=settings.json.data;
            var rows= $("#customers-list tbody tr");

            var checkEmptyRow=rows[0];
            var flag=$(checkEmptyRow).find('td:eq(0)').hasClass('dataTables_empty');
            if(!flag){
                $.each(rows, function( index, value ) {
                    var customerRecord=data[index];
                    var name=$(value).find('td:eq(1)').html();
                    $(value).attr('data-customer-id',customerRecord.id);

                    if(name!=''){
                        var arr_name = name.split(" ");
                        if(arr_name.length==1){
                            var name_symbol = arr_name[0].substr(0, 1);
                        }
                        else{
                            var first_chr = arr_name[0].substr(0, 1);
                            var second_chr = arr_name[1].substr(0, 1);
                            var name_symbol=first_chr+second_chr;
                        }
                        var name_symbol=name_symbol.toUpperCase();
                        var customer_name_icon_visibility='';
                    }
                    else{
                        var name_symbol='NA';
                        var customer_name_icon_visibility='customer-name-icon-hide';
                    }

                    var customerNameIconColorClass=generateIconColor();
                    var temp="<div class='customer-name-icon "+customerNameIconColorClass+" "+customer_name_icon_visibility+"'>"+
                        String(name_symbol) +
                        "</div>"+
                        String(name);
                    $(value).find('td:eq(1)').html(String(name));

                    var temp2='<div class="actions-container">\n' +
                        '   <a class="edit-button" data-customer-id="'+customerRecord.id+'"><i class="mdi mdi-pencil" aria-hidden="true"></i></a>\n' +
                        '   <a class="delete-button" data-customer-id="'+customerRecord.id+'"><i class="fa fa-trash-o icon" aria-hidden="true"></i></a>\n' +
                        ' </div>';
                    // $(value).find('td:eq(5)').html(temp2);
                    $(value).find('td:eq(5)').html('');

                    $(value).find('td:eq(2),td:eq(3),td:eq(4),td:eq(5)').addClass('text-verticle-align');
                    $(value).find('td:eq(5)').css('width', '200px');
                });

                $("#total_customers").text(settings.json.recordsTotal);
            }
            else{
                $("#total_customers").text('0');
            }
        },
        scrollY: height,
        scrollX: true,
        scroller: {
            // loadingIndicator: true
        }
    });


    // $('.dataTables_scrollBody').slimScroll({
    //     height: height,
    //     width: 300,
    //     size: '5px',
    //     alwaysVisible: true,
    //     allowPageScroll: true,
    //     axis: 'both'
    // });

    // var settings = customersListTable.fnSettings();
    //console.log(customersListTable.select);

    $(".dataTables_scrollHead table").on("click", "th.select-checkbox", function() {
        if ($("th.select-checkbox").hasClass("selected")) {  //De Select All
            customersListTable.rows().deselect();
            $("th.select-checkbox").removeClass("selected");

            window.selectedCustomersIndexes=[];
            updatedBulkDeleteButton(customersListTable);
            console.log(window.selectedCustomersIndexes);
        }
        else {
            customersListTable.rows().select();             //Select All
            $("th.select-checkbox").addClass("selected");

            window.selectedCustomersIndexes=[];
            var indexes=customersListTable.rows({selected: true}).toArray()[0];
            var info = customersListTable.page.info();
            //indexes=indexes.slice(info.page*info.length,(info.page+1)*info.length);

            window.selectedCustomersIndexes=indexes;
            updatedBulkDeleteButton(customersListTable);
            console.log(window.selectedCustomersIndexes);
        }
    });

    $(document).on('click', '#customizeReviewRequest', function () {
        var indexes=customersListTable.rows({selected: true}).toArray()[0];
        if(indexes.length==0){
            swal({
                title: "",
                text: 'Please select the customers first',
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }

        $('#customizeReviewRequestsBtn').attr('data-type', 'extCustomers');
        $('#customizeReviewRequestsBtn').text('Send Review Request');
        $('#add-single-customer-back-step').text('Cancel');
        $("#addCustomerStep2 button.close ").remove();

        $('.st-1-header').hide();
        $('.st-2-header').hide();
        $('.st-3-header').show();
        loadAddSingleCustomerStep2Settings();

        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#addCustomerStep2 .error_Msg").addClass('hide-me');

        $('#addCustomerStep2').modal('show');
    });

    customersListTable.on( 'select', function ( e, dt, type, indexes ) {
        if (customersListTable.rows({selected: true}).count() !== customersListTable.rows().count()) {
            $("th.select-checkbox").removeClass("selected");
        }
        else {
            $("th.select-checkbox").addClass("selected");
        }

        window.selectedCustomersIndexes.push(indexes[0]);
        updatedBulkDeleteButton(customersListTable);

    } ).on( 'deselect', function ( e, dt, type, indexes ) {
        if (customersListTable.rows({selected: true}).count() !== customersListTable.rows().count()) {
            $("th.select-checkbox").removeClass("selected");
        }
        else {
            $("th.select-checkbox").addClass("selected");
        }

        removeA(window.selectedCustomersIndexes, indexes[0]);
        updatedBulkDeleteButton(customersListTable);
    } );

    $("select[name='customers-list_length']").addClass('form-control');
    $("select[name='customers-list_length']").css({
        'width':'150px',
        'font-size':'14px',
        'font-weight': 500,
        'border-radius': '5px'
    });

    //var element=$(".dataTables_paginate");

    var dataTables_info_element=$(".dataTables_info");

    $(".dataTables_scrollHead table thead th:eq(5) #info_cont").html( dataTables_info_element);
    //$(".dataTables_scrollHead table thead th:eq(5) #pagination_cont").html( element);

    $(".dataTables_scrollHead table thead th:eq(0)").removeClass('sorting_asc').addClass('sorting_disabled');

    // $("#total_customers").text(noOfRecords);

    $('#customers-list').on('page.dt', function (){
        customersListTable.rows().deselect();
        window.selectedCustomersIndexes=[];
        updatedBulkDeleteButton(customersListTable);
    });

    $('#customers-list').on('order.dt', function (){
        customersListTable.rows().deselect();
        window.selectedCustomersIndexes=[];
        updatedBulkDeleteButton(customersListTable);
    });


    // $("#add_contact_button").click(function(){
    //     var enableGetReviewsCheck=enable_get_reviews;
    //     enableGetReviewsCheck=enableGetReviewsCheck.trim();
    //     console.log(enableGetReviewsCheck);
    //     if(enableGetReviewsCheck=='enabled'){
    //         var modalTitle="Add Customer and Send Review Request";
    //         var saveButtonText="Save Customer and Send Review Request";
    //         var headingText="<p id='titleHeadingText' style='margin: 10px 0px 0px 0px;'>Add your customer details below. "+dynamicAppName+" will also send a review request to this customer via Email or SMS. This is enabled by default. If you want to disable it or change how to send the review requests, <a href='"+baseUrl+"/crm-customers-settings'>click here</a>.</p>";
    //     }
    //     else if(enableGetReviewsCheck=='disabled'){
    //         var modalTitle="Add New Customer";
    //         var saveButtonText="Save Customer";
    //         var headingText="";
    //     }
    //     else{
    //         var modalTitle="Add New Customer";
    //         var saveButtonText="Save Customer";
    //         var headingText="";
    //     }
    //
    //     var id = 'add_contact_modal';
    //
    //     var html = '';
    //     html += '<div class="add_contact_modal">';
    //
    //     html += '<form id="add_contact_form">';
    //
    //     html += '<div class="form-group">';
    //     html += '<div class="row">';
    //     html += '<div class="col-md-3">';
    //     html += '<label>First Name</label>';
    //     html += '</div>';
    //     html += '<div class="col-md-9">';
    //     html += '<input type="text" id="first_name" class="form-control">';
    //     html += '<span class="help-block hide-me"><small></small></span>';
    //     html += '</div>';
    //     html += '</div>';
    //     html += '</div>';
    //
    //     html += '<div class="form-group">';
    //     html += '<div class="row">';
    //     html += '<div class="col-md-3">';
    //     html += '<label>Last Name</label>';
    //     html += '</div>';
    //     html += '<div class="col-md-9">';
    //     html += '<input type="text" id="last_name" class="form-control">';
    //     html += '<span class="help-block hide-me"><small></small></span>';
    //     html += '</div>';
    //     html += '</div>';
    //     html += '</div>';
    //
    //
    //     html += '<div class="form-group">';
    //     html += '<div class="row">';
    //     html += '<div class="col-md-3">';
    //     html += '<label>Email Address</label>';
    //     html += '</div>';
    //     html += '<div class="col-md-9">';
    //     html += '<input type="email" id="email" class="form-control">';
    //     html += '<span class="help-block hide-me"><small></small></span>';
    //     html += '</div>';
    //     html += '</div>';
    //     html += '</div>';
    //
    //
    //     html += '<div class="form-group">';
    //     html += '<div class="row">';
    //     html += '<div class="col-md-3">';
    //     html += '<label>Phone Number</label>';
    //     html += '</div>';
    //     html += '<div class="col-md-9">';
    //     html += '<input type="text" id="phone_number" placeholder="+1" value="+1" class="form-control">';
    //     html += '<span class="help-block hide-me"><small></small></span>';
    //     html += '<span class="help-text-phone-number"><small>The country code is required and it has been pre-filled for you. Start adding the phone number after the country code.</small></span>';
    //     html += '</div>';
    //     html += '</div>';
    //     html += '</div>';
    //
    //     html += '<span id="error_Msg" class="help-block hide-me"><small></small></span>';
    //     html += '</form>';
    //
    //     html +='<div class="modal-footer">';
    //     html +='<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    //     html +='<button type="button" id="add-contact" class="btn btn-info" data-loading-text="<i class=\'fa fa-spinner fa-spin \'></i> Please wait...">'+saveButtonText+'</button>';
    //     html +='</div>';
    //
    //     html +='</div>';
    //
    //     loadModal(id);
    //
    //     $(".modal-title", '#'+id).remove();
    //     $(".modal-header #titleHeadingText", '#'+id).remove();
    //     $('.modal-header', '#'+id).append('<h4 class="modal-title">'+modalTitle+'</h4>'+headingText);
    //     $('.modal-body', '#'+id).css('padding-top', '15px');
    //     $('.modal-body', '#'+id).html(html);
    //
    //     $('#'+id+' .modal-dialog').css('width','39%');
    //
    //     $("form#add_contact_form input[type='text'], form#add_contact_form input[type='email']").off('blur keyup');
    //
    //     $("form#add_contact_form input[type='text'], form#add_contact_form input[type='email']").on('blur keyup', function(){
    //         var input=$(this);
    //         var check=input.hasClass('fieldToValidate');
    //         console.log(check);
    //         if(check){
    //             var spanEl=input.next("span");
    //             if(input.val()==''){
    //                 spanEl.removeClass('hide-me').addClass('errorMsg');
    //                 input.closest('.form-group').addClass('has-error');
    //                 $("small", spanEl).html("Required Field");
    //             }
    //             else{
    //                 if(input.attr('type')=='email'){
    //                     var email=input.val();
    //                     var validEmailCheck=ValidateEmail(email);
    //                     if(validEmailCheck==false){
    //                         spanEl.removeClass('hide-me').addClass('errorMsg');
    //                         input.closest('.form-group').addClass('has-error');
    //                         $("small", spanEl).html("Enter Valid Email");
    //                     }
    //                     else{
    //                         spanEl.addClass('hide-me').removeClass('errorMsg');
    //                         input.closest('.form-group').removeClass('has-error');
    //                         $("small", spanEl).text("");
    //                     }
    //                 }
    //                 else{
    //                     spanEl.addClass('hide-me').removeClass('errorMsg');
    //                     input.closest('.form-group').removeClass('has-error');
    //                     $("small", spanEl).text("");
    //                 }
    //             }
    //         }
    //     });
    //
    //     $('form#add_contact_form input').off("keypress");
    //     $('form#add_contact_form input').on("keypress", function(e) {
    //         if(e.which == 13) {
    //             $('#add-contact').trigger('click');
    //         }
    //     });
    //
    // });

    $(".modal").on('shown.bs.modal', function(){
        $("body").addClass('modal-open');
    });

    $(".modal").on('hidden.bs.modal', function(){
        $("body").removeClass('modal-open');
    });

    function addSingleCustomer() {
        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#addCustomerStep1 .error_Msg").addClass('hide-me');

        var contactFirstName = $.trim($("#addCustomerStep1 #first_name").val());
        var contactLastName = $.trim($("#addCustomerStep1 #last_name").val());
        var contactEmailAddress = $.trim($("#addCustomerStep1 #email").val());
        var contactPhoneNumber = $.trim($("#addCustomerStep1 #phone_number").val());
        var country = $.trim($("#addCustomerStep1 #countryList").val());
        var countryCode = $.trim($("#addCustomerStep1 #countryCodesList").val());

        var baseUrl = $('#hfBaseUrl').val();
        showPreloader();
        $('#add-single-customer-next-step').addClass('disabled').attr('disabled','disabled');
        $('#addCustomerStep1 input').blur();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/crm-add-customer",
            data: {
                'first_name': contactFirstName,
                'last_name': contactLastName,
                'email': contactEmailAddress,
                'phone_number': contactPhoneNumber,
                'country': country,
                'country_code': countryCode
            },
            success: function (response, status) {
                console.log("response");
                console.log(response);
                var statusCode = response._metadata.outcomeCode;
                var statusMessage = response._metadata.outcome;
                var message = response._metadata.message;
                var errors = response.errors;
                var records = response.records;

                if(statusCode == 200){
                    var records = response.records;
                    var enable_get_reviews_check = enable_get_reviews.toLowerCase();
                    if(enable_get_reviews_check=='disabled'){
                        $('#addCustomerStep1').modal('hide');
                        swal({
                            title: "",
                            text: message,
                            type: 'success',
                            allowOutsideClick: false,
                            html: true,
                            showCancelButton: false,
                            confirmButtonColor: '#8CD4F5',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'OK',
                            cancelButtonText: "Cancel",
                            closeOnConfirm: true,
                            closeOnCancel: true
                        },function(){
                            swal.close();
                            showPreloader();
                            location.href = baseUrl+'/crm-customers';
                        });
                    }
                    else{
                        var customer_id= records.customer_id;
                        var varification_code= records.varification_code;
                        $('#add-single-customer-next-step').attr('data-already-added', 'true');

                        $('#customizeReviewRequestsBtn').removeAttr('data-first_id');
                        $('#customizeReviewRequestsBtn').removeAttr('data-flag');

                        $('#customizeReviewRequestsBtn').attr('data-type', 'single');
                        $('#customizeReviewRequestsBtn').attr('data-customer-id',customer_id);
                        $('#customizeReviewRequestsBtn').attr('data-varification-code',varification_code);

                        $('#customizeReviewRequestsBtn').text('Add Contact and Send Review Request');

                        $('.st-1-header').show();
                        $('.st-2-header').hide();
                        $('.st-3-header').hide();

                        $('#addCustomerStep1').modal('hide');
                        loadAddSingleCustomerStep2Settings();

                        $('span.help-block small').text('');
                        $('span.help-block').closest('.form-group').removeClass('has-error');
                        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
                        $("#addCustomerStep2 .error_Msg").addClass('hide-me');

                        $('#addCustomerStep2').modal('show');
                        $('#add-single-customer-next-step').removeClass('disabled').removeAttr('disabled');
                        hidePreloader();
                    }
                }
                else{
                    if(errors.length!=0){
                        $.each(errors, function (index, value) {
                            var errorSelector = $("#addCustomerStep1 #"+value.map).next("span");
                            errorSelector.removeClass('hide-me');
                            $("#addCustomerStep1 #"+value.map).closest('.form-group').addClass('has-error');
                            errorSelector.addClass('errorMsg');
                            $("small", errorSelector).html(value.message);
                        })
                    }
                    else{
                        $("#addCustomerStep1 .error_Msg").removeClass('hide-me');
                        $("#addCustomerStep1 .error_Msg small").html(message);
                    }
                    hidePreloader();
                    $('#add-single-customer-next-step').removeClass('disabled').removeAttr('disabled');
                }
            },
            error: function (data, status) {
                $("#addCustomerStep1 .error_Msg").removeClass('hide-me');
                $("#addCustomerStep1 .error_Msg small").html("OOPs! Something went wrong...");
                $('#add-single-customer-next-step').removeClass('disabled').removeAttr('disabled');
                hidePreloader();
            }
        });
    }

    function addSingleCustomerStep2() {
        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#addCustomerStep2 .error_Msg").addClass('hide-me');

        var contactFirstName = $.trim($("#addCustomerStep1 #first_name").val());
        var contactLastName = $.trim($("#addCustomerStep1 #last_name").val());
        var contactEmailAddress = $.trim($("#addCustomerStep1 #email").val());
        var contactPhoneNumber = $.trim($("#addCustomerStep1 #phone_number").val());
        var country = $.trim($("#addCustomerStep1 #countryList").val());
        var countryCode = $.trim($("#addCustomerStep1 #countryCodesList").val());

        var enable_get_reviews= $('#enable_get_reviews').val();

        var smart_routing= $('#smart_routing').val();
        if(smart_routing=='Enable'){
            var review_site='';
        }
        else{
            var review_site= $('#review_site').val();
        }

        var send_reminder= $('#send_reminder').val();
        var sending_option= $('#sending_option').val();

        if(typeof(enable_get_reviews)=='undefined' || enable_get_reviews==null || enable_get_reviews=='null' || enable_get_reviews==''){
            swal({
                title: "",
                text: 'Please select "Send Review Requests after adding a customer?" option',
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }
        if(enable_get_reviews=='Yes'){
            if(typeof(sending_option)=='undefined' || sending_option==null || sending_option=='null' || sending_option==''){
                swal({
                    title: "",
                    text: 'Please select "How do you want to send your review requests?" option',
                    type: 'error',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5 ',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                });
                return false;
            }

            if(typeof(smart_routing)=='undefined' || smart_routing==null || smart_routing=='null' || smart_routing==''){
                swal({
                    title: "",
                    text: 'Please select the Smart Routing option',
                    type: 'error',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5 ',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                });
                return false;
            }

            if(typeof(send_reminder)=='undefined' || send_reminder==null || send_reminder=='null' || send_reminder==''){
                swal({
                    title: "",
                    text: 'Please select the Send Reminder option',
                    type: 'error',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5 ',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                });
                return false;
            }
        }
        else{
            sending_option=null;
            smart_routing=null;
            review_site=null;
            send_reminder=null;
        }

        if(smart_routing=='Disable'){
            if(typeof(review_site)=='undefined' || review_site==null || review_site=='null' || review_site==''){
                swal({
                    title: "",
                    text: 'Please select the Review Site',
                    type: 'error',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5 ',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                });
                return false;
            }
        }

        /*---*/
        var requestType=$('#customizeReviewRequestsBtn').attr('data-type');

        if(requestType=='single'){
            var customerID=$('#customizeReviewRequestsBtn').attr('data-customer-id');
            var verificationCode=$('#customizeReviewRequestsBtn').attr('data-varification-code');

            var data={
                customer_id: customerID,
                varification_code: verificationCode,
                first_name: contactFirstName,
                last_name: contactLastName,
                email: contactEmailAddress,
                phone_number: contactPhoneNumber,
                country: country,
                country_code: countryCode,
                enable_get_reviews: enable_get_reviews,
                smart_routing: smart_routing,
                sending_option: sending_option,
                review_site: review_site,
                reminder: send_reminder
            };


            if($('#email-content').attr('data-default')!='true'){
                var emailContent=$('#email-content').html();
                emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_email=emailContent;
            }
            if($('#sms-content').attr('data-default')!='true'){
                var smsContent=$('#sms-content').html();
                smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_sms=smsContent;
            }
            console.log(data);
            var baseUrl = $('#hfBaseUrl').val();
            var apiURl=baseUrl + "/crm-add-customer";
            var apiMethod='POST';
        }

        if(requestType=='multiple'){
            var dataFirstID=$('#customizeReviewRequestsBtn').attr('data-first_id');
            var dataFlag=$('#customizeReviewRequestsBtn').attr('data-flag');

            var data={
                first_id: dataFirstID,
                flag: dataFlag,
                enable_get_reviews: enable_get_reviews,
                smart_routing: smart_routing,
                sending_option: sending_option,
                review_site: review_site,
                reminder: send_reminder
            };

            if($('#email-content').attr('data-default')!='true'){
                var emailContent=$('#email-content').html();
                emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_email=emailContent;
            }
            if($('#sms-content').attr('data-default')!='true'){
                var smsContent=$('#sms-content').html();
                smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_sms=smsContent;
            }
            console.log(data);
            var baseUrl = $('#hfBaseUrl').val();
            var apiURl= baseUrl + "/crm-background-service";
            var apiMethod='POST';
        }

        if(requestType=='extCustomers'){
            var rowData = customersListTable.rows( window.selectedCustomersIndexes ).data().toArray();
            window.selectedCustomersIDs=[];
            $.each(rowData, function (index, value) {
                var actionsRow=rowData[index];
                var customerID=actionsRow.id;
                var cust={
                    id: customerID
                };
                window.selectedCustomersIDs.push(cust);
            });
            console.log(window.selectedCustomersIDs);

            var data={
                enable_get_reviews: enable_get_reviews,
                smart_routing: smart_routing,
                sending_option: sending_option,
                review_site: review_site,
                reminder: send_reminder,
                customers: window.selectedCustomersIDs
            };

            if($('#email-content').attr('data-default')!='true'){
                var emailContent=$('#email-content').html();
                emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_email=emailContent;
            }
            if($('#sms-content').attr('data-default')!='true'){
                var smsContent=$('#sms-content').html();
                smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
                data.customize_sms=smsContent;
            }
            console.log(data);
            var baseUrl = $('#hfBaseUrl').val();
            var apiURl=baseUrl + "/sendingExistingCustomerReviewRequest";
            var apiMethod='POST';
        }

        /*---*/

        showPreloader();
        $('#customizeReviewRequestsBtn').addClass('disabled').attr('disabled','disabled');
        $('#addCustomerStep2 input').blur();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: apiMethod,
            url: apiURl,
            data: data,
            success: function (response, status) {
                console.log(response);
                var statusCode = response._metadata.outcomeCode;
                var statusMessage = response._metadata.outcome;
                var message = response._metadata.message;
                var errors = response.errors;
                var records = response.records;

                if(statusCode==200){
                    var records = response.records;

                    $('#addCustomerStep2').modal('hide');
                    hidePreloader();
                    $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');

                    if(requestType=='extCustomers'){
                        swal({
                            title: '',
                            text: message,
                            confirmButtonText: "Ok",
                            type: 'success',
                            html: true
                        }, function (callAlert) {
                            swal.close();
                            showPreloader();
                            location.href = baseUrl + '/crm-customers';
                        });
                    }
                    else{
                        swal({
                            title: 'Notification Summary',
                            text: '' +
                                '<div style="margin-top: 30px;">' +
                                '<p style="margin-bottom: 10px;"><i class="fa fa-check" aria-hidden="true"></i> <span>Customer added. </span></p>'+
                                '<p><i class="fa fa-check" aria-hidden="true"></i> <span>Review Request sent.</span></p>'+
                                '</div>',
                            confirmButtonText: "Ok",
                            type: 'success',
                            html: true
                        }, function (callAlert) {
                            swal.close();
                            showPreloader();
                            location.href = baseUrl + '/crm-customers';
                        });
                    }
                }
                else{
                    if(errors.length!=0){
                        $.each(errors, function (index, value) {
                            var errorSelector = $("#addCustomerStep2 #"+value.map).next("span");
                            errorSelector.removeClass('hide-me');
                            $("#addCustomerStep2 #"+value.map).closest('.form-group').addClass('has-error');
                            errorSelector.addClass('errorMsg');
                            $("small", errorSelector).html(value.message);
                        })
                    }
                    else{
                        $("#addCustomerStep2 .error_Msg").removeClass('hide-me');
                        $("#addCustomerStep2 .error_Msg small").html(message);
                    }
                    hidePreloader();
                    $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
                }
            },
            error: function (data, status) {
                $("#addCustomerStep2 .error_Msg").removeClass('hide-me');
                $("#addCustomerStep2 .error_Msg small").html("OOPs! Something went wrong...");
                $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
                hidePreloader();
            }
        });
    }

    $("#add_contact_button").click(function(){
        $('#first_name,#last_name,#email,#phone_number').val('');
        $('#countryList').selectpicker('val', '');
        $('#countryCodesList').selectpicker('val', '');
        $('#add-single-customer-next-step').attr('data-already-added', 'false');

        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#addCustomerStep1 .error_Msg").addClass('hide-me');

        $('#addCustomerStep1').modal('show');
    });

    $("#add-single-customer-next-step").click(function(){
        var alreadyAddedCheck= $('#add-single-customer-next-step').attr('data-already-added');
        if(alreadyAddedCheck=='true'){
            $('#addCustomerStep1').modal('hide');
            $('#addCustomerStep2').modal('show');
        }
        else{
            addSingleCustomer();
        }
    });

    $("#customizeReviewRequestsBtn").click(function(){
        addSingleCustomerStep2();
    });

    $("#add-single-customer-back-step").click(function(){
        var requestType=$('#customizeReviewRequestsBtn').attr('data-type');
        if(requestType=='single'){
            $('#addCustomerStep2').modal('hide');
            $('#addCustomerStep1').modal('show');
        }
        if(requestType=='multiple'){
            $('#addCustomerStep2').modal('hide');
            $('#addMultipleCustomerStep3').modal('show');
        }
        if(requestType=='extCustomers'){
            $('#addCustomerStep2').modal('hide');
        }
    });

    $(".closeAddCustomerStep2").click(function(){
        $('#addCustomerStep2').modal('hide');
        swal({
            title: 'Notification Summary',
            text: '' +
                '<div style="margin-top: 30px;">' +
                '<p style="margin-bottom: 10px;"><i class="fa fa-check" aria-hidden="true"></i> <span>Customer added. </span></p>'+
                '<p><i class="fa fa-times" aria-hidden="true"></i> <span>Review Request not sent.</span></p>'+
                '</div>',
            confirmButtonText: "Ok",
            type: 'success',
            html: true
        }, function (callAlert) {
            swal.close();
            showPreloader();
            location.href = baseUrl + '/crm-customers';
        });
    });

    $(".closeAddMultipleCustomerStep2").click(function(){
        $('#addMultipleCustomerStep2').modal('hide');
    });

    $(".closeAddMultipleCustomerStep3").click(function(){
        var alreadyAddedCheck= $('#upload_csv').attr('data-already-added');
        if(alreadyAddedCheck=='true'){
            $('#addMultipleCustomerStep3').modal('hide');
            swal({
                title: 'Notification Summary',
                text: '' +
                    '<div style="margin-top: 30px;">' +
                    '<p style="margin-bottom: 10px;"><i class="fa fa-check" aria-hidden="true"></i> <span>Customers added. </span></p>'+
                    '<p><i class="fa fa-times" aria-hidden="true"></i> <span>Review Request not sent.</span></p>'+
                    '</div>',
                confirmButtonText: "Ok",
                type: 'success',
                html: true
            }, function (callAlert) {
                swal.close();
                showPreloader();
                location.href = baseUrl + '/crm-customers';
            });
        }
        else{
            $('#addMultipleCustomerStep3').modal('hide');
        }
    });

    $(".closeAddSingleCustomerStep1").click(function(){
        var alreadyAddedCheck= $('#add-single-customer-next-step').attr('data-already-added');
        if(alreadyAddedCheck=='true'){
            $('#addCustomerStep1').modal('hide');
            swal({
                title: 'Notification Summary',
                text: '' +
                    '<div style="margin-top: 30px;">' +
                    '<p style="margin-bottom: 10px;"><i class="fa fa-check" aria-hidden="true"></i> <span>Customers added. </span></p>'+
                    '<p><i class="fa fa-times" aria-hidden="true"></i> <span>Review Request not sent.</span></p>'+
                    '</div>',
                confirmButtonText: "Ok",
                type: 'success',
                html: true
            }, function (callAlert) {
                swal.close();
                showPreloader();
                location.href = baseUrl + '/crm-customers';
            });
        }
        else{
            $('#addCustomerStep1').modal('hide');
        }
    });

    $(".closeAddMultipleCustomerStep4").click(function () {
        $('#addMultipleCustomerStep4').modal('hide');
        swal({
            title: 'Notification Summary',
            text: '' +
                '<div style="margin-top: 30px;">' +
                '<p style="margin-bottom: 10px;"><i class="fa fa-check" aria-hidden="true"></i> <span>Customers added. </span></p>' +
                '<p><i class="fa fa-times" aria-hidden="true"></i> <span>Review Request not sent.</span></p>' +
                '</div>',
            confirmButtonText: "Ok",
            type: 'success',
            html: true
        }, function (callAlert) {
            swal.close();
            showPreloader();
            location.href = baseUrl + '/crm-customers';
        });
    });

    $("#addCustomerStep1 input[type='text'], #addCustomerStep1 input[type='email']").off('blur keyup');

    $("#addCustomerStep1 input[type='text'], #addCustomerStep1 input[type='email']").on('blur keyup', function(){
        var input=$(this);
        var check=input.hasClass('fieldToValidate');
        console.log(check);
        if(check){
            var spanEl=input.next("span");
            if(input.val()==''){
                spanEl.removeClass('hide-me').addClass('errorMsg');
                input.closest('.form-group').addClass('has-error');
                $("small", spanEl).html("Required Field");
            }
            else{
                if(input.attr('type')=='email'){
                    var email=input.val();
                    var validEmailCheck=ValidateEmail(email);
                    if(validEmailCheck==false){
                        spanEl.removeClass('hide-me').addClass('errorMsg');
                        input.closest('.form-group').addClass('has-error');
                        $("small", spanEl).html("Enter Valid Email");
                    }
                    else{
                        spanEl.addClass('hide-me').removeClass('errorMsg');
                        input.closest('.form-group').removeClass('has-error');
                        $("small", spanEl).text("");
                    }
                }
                else{
                    spanEl.addClass('hide-me').removeClass('errorMsg');
                    input.closest('.form-group').removeClass('has-error');
                    $("small", spanEl).text("");
                }
            }
        }
    });

    $('#addCustomerStep1 input').off("keypress");
    $('#addCustomerStep1 input').on("keypress", function(e) {
        if(e.which == 13) {
            $('#add-single-customer-next-step').trigger('click');
        }
    });


    // $(document).on('click',"#add-contact",function () {
    //
    //     var plainInputsValid = false;
    //
    //     $('span.help-block small').text('');
    //     $('span.help-block').closest('.form-group').removeClass('has-error');
    //     $('span.help-block').addClass('hide-me').removeClass('errorMsg');
    //     $("#add_contact_form #error_Msg").addClass('hide-me');
    //
    //     var formClass = '.add_contact_modal';
    //     var example = document.querySelector(formClass);
    //     var form = example.querySelector('form');
    //
    //     Array.prototype.forEach.call(
    //         form.querySelectorAll(
    //             "#add_contact_form input[type='text'], #add_contact_form input[type='email']"
    //         ),
    //         function(input) {
    //             var input=$(input);
    //             var check=input.hasClass('fieldToValidate');
    //             console.log(check);
    //             if(check){
    //                 var spanEl=$(input).next("span");
    //                 if($(input).val()==''){
    //                     spanEl.removeClass('hide-me').addClass('errorMsg');
    //                     $(input).closest('.form-group').addClass('has-error');
    //                     $("small", spanEl).html("Required Field");
    //                     plainInputsValid = true
    //                 }
    //                 else{
    //                     if($(input).attr('type')=='email'){
    //                         var email=$(input).val();
    //                         var validEmailCheck=ValidateEmail(email);
    //                         if(validEmailCheck==false){
    //                             spanEl.removeClass('hide-me').addClass('errorMsg');
    //                             $(input).closest('.form-group').addClass('has-error');
    //                             $("small", spanEl).html("Enter Valid Email");
    //                             plainInputsValid = true
    //                         }
    //
    //                     }
    //                 }
    //             }
    //         }
    //     );
    //
    //     if (plainInputsValid) {
    //         return false;
    //     }
    //
    //     var contactFirstName = $.trim($("#add_contact_form #first_name").val());
    //     var contactLastName = $.trim($("#add_contact_form #last_name").val());
    //     var contactEmailAddress = $.trim($("#add_contact_form #email").val());
    //     var contactPhoneNumber = $.trim($("#add_contact_form #phone_number").val());
    //
    //     var baseUrl = $('#hfBaseUrl').val();
    //     showPreloader();
    //     $('#add-contact').addClass('disabled').attr('disabled','disabled');
    //     $('form#add_contact_form input').blur();
    //
    //     $.ajax({
    //         headers: {
    //             'X-CSRF-TOKEN': $('input[name="_token"]').val()
    //         },
    //         type: "POST",
    //         url: baseUrl + "/crm-add-customer",
    //         data: {
    //             'first_name': contactFirstName,
    //             'last_name': contactLastName,
    //             'email': contactEmailAddress,
    //             'phone_number': contactPhoneNumber
    //         },
    //         success: function (response, status) {
    //             console.log(response);
    //             var statusCode = response._metadata.outcomeCode;
    //             var statusMessage = response._metadata.outcome;
    //             var message = response._metadata.message;
    //             var errors = response.errors;
    //             var records = response.records;
    //
    //             if(statusCode==200){
    //                 $('#add_contact_modal').modal('hide');
    //                 swal({
    //                     title: "",
    //                     text: message,
    //                     type: 'success',
    //                     allowOutsideClick: false,
    //                     html: true,
    //                     showCancelButton: false,
    //                     confirmButtonColor: '#8CD4F5',
    //                     cancelButtonColor: '#d33',
    //                     confirmButtonText: 'OK',
    //                     cancelButtonText: "Cancel",
    //                     closeOnConfirm: true,
    //                     closeOnCancel: true
    //                 },function(){
    //                     swal.close();
    //                     showPreloader();
    //                     location.href = baseUrl+'/crm-customers';
    //                 });
    //             }
    //             else{
    //                 if(errors.length!=0){
    //                     $.each(errors, function (index, value) {
    //                         var errorSelector = $("#"+value.map).next("span");
    //                         errorSelector.removeClass('hide-me');
    //                         $("#"+value.map).closest('.form-group').addClass('has-error');
    //                         errorSelector.addClass('errorMsg');
    //                         $("small", errorSelector).html(value.message);
    //                     })
    //                 }
    //                 else{
    //                     $("#add_contact_form #error_Msg").removeClass('hide-me');
    //                     $("#add_contact_form #error_Msg small").html(message);
    //                 }
    //             }
    //             hidePreloader();
    //             $('#add-contact').removeClass('disabled').removeAttr('disabled');
    //         },
    //         error: function (data, status) {
    //             $("#add_contact_form #error_Msg").removeClass('hide-me');
    //             $("#add_contact_form #error_Msg small").html("OOPs! Something went wrong...");
    //             $('#add-contact').removeClass('disabled').removeAttr('disabled');
    //             hidePreloader();
    //         }
    //     });
    // });

    $(document).on('click',"#delete_customers_button",function () {
        if(window.selectedCustomersIndexes.length==0){
            swal({
                title: "",
                text: "No customer(s) are selected...",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }
        var rowData = customersListTable.rows( window.selectedCustomersIndexes ).data().toArray();
        window.selectedCustomersIDs=[];
        $.each(rowData, function (index, value) {
            var actionsRow=rowData[index];
            // var deleteColumn=$($(actionsRow[5])[0]).find('.delete-button');
            // var customerID=$(deleteColumn[0]).attr('data-customer-id');
            var customerID=actionsRow.id;
            window.selectedCustomersIDs.push(customerID);
        });
        console.log(window.selectedCustomersIDs);

        var noOfSelectedRows= window.selectedCustomersIDs.length;
        var str= noOfSelectedRows>1? ' these '+noOfSelectedRows+' selected customers?' : 'this selected customer?';
        swal({
            title: "",
            text: "Are you sure you want to delete "+str,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ff6666",
            confirmButtonText: "Yes, delete!"
        }, function (isConfirm) {
            if(isConfirm){
                var customerID=window.selectedCustomersIDs;
                console.log(customerID);
                if(typeof(customerID)=='undefined' || customerID=='null' || customerID==null || customerID==''){
                    swal({
                        title: "",
                        text: "OOPs! Something went wrong...",
                        type: 'error',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    });
                    return false;
                }
                deleteCustomer(customerID,customersListTable);
            }
            else{
                swal.close();
            }
        });
    });

    $(document).on('click',".delete-button",function () {
        var element=$(this);
        swal({
            title: "",
            text: "Are you sure you want to delete this customer?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ff6666",
            confirmButtonText: "Yes, delete!"
        }, function (isConfirm) {
            if(isConfirm){
                var customerID=element.attr('data-customer-id');
                console.log(customerID);
                if(typeof(customerID)=='undefined' || customerID=='null' || customerID==null || customerID==''){
                    swal({
                        title: "",
                        text: "OOPs! Something went wrong...",
                        type: 'error',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5 ',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    });
                    return false;
                }
                deleteCustomer(customerID,customersListTable);
            }
            else{
                swal.close();
            }
        });
    });

    // $(document).on('click',"#upload_CSV_button",function () {
    //     var enableGetReviewsCheck=enable_get_reviews;
    //     enableGetReviewsCheck=enableGetReviewsCheck.trim();
    //     console.log(enableGetReviewsCheck);
    //     if(enableGetReviewsCheck=='enabled'){
    //         var enabledHelpText="<span class='enabled-get-reviews-help-text'>IMPORTANT: Automatic sending of review request is enabled. This means that the contacts you are uploading will be receiving a review request via email or text immediately after they are uploaded. If you want to review your settings first before uploading, <a href='"+baseUrl+"/crm-customers-settings'>click here.</a></span>";
    //     }
    //     else if(enableGetReviewsCheck=='disabled'){
    //         var enabledHelpText="";
    //     }
    //     else{
    //         var enabledHelpText="";
    //     }
    //
    //     var id = 'upload_CSV_modal';
    //
    //     var html = '';
    //     html += '<div class="upload_CSV_modal">';
    //
    //     html += '<p style="font-size: 15px; font-weight: 500; margin-bottom: 11px;">To add multiple customers easily, you may add them to a CSV file and upload them here. Follow the steps below:</h4>';
    //
    //     html += '<div>';
    //
    //     html += '<p style="margin-bottom: 0px; margin-top: 11px;">Step 1: Download CSV Template</p>';
    //     html += '<a href="'+baseUrl+'/public/files/customers_template.csv" download>Download CSV Template</a> ';
    //     //html += '<a href="javascript:;" download>Download CSV Template</a> ';
    //     html += '<p style="margin-top: 17px;">Step 2: Add your contacts to the CSV file you downloaded. Follow the format shown in the CSV.</p>';
    //     html += '<p style="margin-top: 17px; margin-bottom: 17px;">Step 3: Browse your customer and select the CSV file that contains your customers.</p>';
    //
    //     html += '<form id="fileUploadForm">';
    //     html += '<div class="input-group">'+
    //         '<input type="text" class="form-control" readonly>'+
    //         '<label class="input-group-btn">'+
    //         '<span class="btn btn-info">Browse&hellip; <input type="file" name="file" id="uploadCustomersCSVFile" style="display: none;"></span>'+
    //         '</label>'+
    //         '</div>';
    //     html += '</form>';
    //
    //     html += '<p style="margin-top: 15px;">Step 4: Click on Upload.</p>';
    //
    //     html += enabledHelpText;
    //
    //     html += '<span class="error" style="display: none; margin-top: 10px;"></span></div">';
    //
    //     html +='<div class="modal-footer">';
    //     html +='<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    //     html +='<button type="button" id="upload_csv" class="btn btn-info" data-loading-text="<i class=\'fa fa-spinner fa-spin \'></i> Please wait...">Upload</button>';
    //     html +='</div>';
    //
    //     html +='</div>';
    //
    //     loadModal(id);
    //
    //     $(".modal-title", '#'+id).remove();
    //     $(".modal-header #titleHeadingText", '#'+id).remove();
    //     $('.modal-header', '#'+id).append('<h4 class="modal-title">Upload CSV</h4>');
    //     $('.modal-body', '#'+id).css('padding-top', '0px');
    //     $('.modal-body', '#'+id).html(html);
    //
    //     $('#'+id+' .modal-dialog').css('width','43%');
    //
    //     // We can attach the `fileselect` event to all file inputs on the page
    //
    //     $(document).off("change", "#uploadCustomersCSVFile");
    //     $(document).on('change', '#uploadCustomersCSVFile', function() {
    //         var input = $(this),
    //             numFiles = input.get(0).files ? input.get(0).files.length : 1,
    //             label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    //         input.trigger('fileselect', [numFiles, label]);
    //
    //         if(input.get(0).files.length==0){
    //             input.val('');
    //             var $el = $('#fileUploadForm');
    //             $el.wrap('<form>').closest('form').get(0).reset();
    //             $el.unwrap();
    //         }
    //     });
    //
    //     $('#uploadCustomersCSVFile').off('fileselect');
    //     $('#uploadCustomersCSVFile').on('fileselect', function(event, numFiles, label) {
    //         var ext = $('#uploadCustomersCSVFile').val().split('.').pop().toLowerCase();
    //         var input = $(this).parents('.input-group').find(':text'),
    //             log = numFiles > 1 ? numFiles + ' files selected' : label;
    //         var fileError = $(".error");
    //
    //         if(log) {
    //             if ($.inArray(ext, ['csv']) == -1) {
    //                 fileError.show();
    //                 fileError.html(' Invalid Format, Please upload CSV file.');
    //                 input.val('');
    //
    //                 var $el = $('#fileUploadForm');
    //                 $el.wrap('<form>').closest('form').get(0).reset();
    //                 $el.unwrap();
    //             }
    //             else {
    //                 fileError.hide();
    //                 fileError.html('');
    //
    //                 if (input.length) {
    //                     input.val(log);
    //                 } else {
    //                     // if (log) alert(log);
    //                 }
    //             }
    //         }
    //     });
    //
    //     $(document).off("click", "#upload_csv");
    //     $(document).on("click", "#upload_csv", function(e){
    //         e.preventDefault();
    //         if( document.getElementById("uploadCustomersCSVFile").files.length == 0 ){
    //             var fileError = $(".upload_CSV_modal .error");
    //             fileError.hide();
    //             fileError.html('');
    //             fileError.show();
    //             fileError.html('File is required. Please upload CSV file.');
    //             return false;
    //         }
    //         addCustomersCSV();
    //     });
    //
    // });

    $(document).on('click',"#upload_CSV_button",function (){
        $('#uploadCustomersCSVFile').val('');
        var $el = $('#fileUploadForm');
        $el.wrap('<form>').closest('form').get(0).reset();
        $el.unwrap();

        window.csvFileArray = [];
        $('#csvFileName').text('');

        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#addCustomerStep2 .error_Msg").addClass('hide-me');

        $('#upload_csv').attr('data-already-added', 'false');
        $('#addMultipleCustomerStep1').modal('show');
    });

    $(document).on('click',"#showAddMultipleCustomerStep2Modal",function () {
        $('#addMultipleCustomerStep1').modal('hide');
        $('#addMultipleCustomerStep2').modal('show');
    });

    $(document).on('click',"#showAddMultipleCustomerStep3Modal",function () {
        // $('#uploadCustomersCSVFile').val('');
        // var $el = $('#fileUploadForm');
        // $el.wrap('<form>').closest('form').get(0).reset();
        // $el.unwrap();
        //
        // window.csvFileArray = [];
        // $('#csvFileName').text('');

        $('#addMultipleCustomerStep2').modal('hide');
        $('#addMultipleCustomerStep3').modal('show');
    });

    $(document).on('click',"#showAddMultipleCustomerStep4Modal",function () {
        $('#addMultipleCustomerStep3').modal('hide');
        $('#addMultipleCustomerStep4').modal('show');
    });

    $(document).on('click',"#backToAddMultipleCustomerStep1Modal",function () {
        $('#addMultipleCustomerStep2').modal('hide');
        $('#addMultipleCustomerStep1').modal('show');
    });

    $(document).on('click',"#backToAddMultipleCustomerStep2Modal",function () {
        $('#addMultipleCustomerStep3').modal('hide');
        $('#addMultipleCustomerStep2').modal('show');
    });

    // $(document).on('click',"#backToAddMultipleCustomerStep3Modal",function () {
    //     // $('#uploadCustomersCSVFile').val('');
    //     // var $el = $('#fileUploadForm');
    //     // $el.wrap('<form>').closest('form').get(0).reset();
    //     // $el.unwrap();
    //     //
    //     // window.csvFileArray = [];
    //     // $('#csvFileName').text('');
    //
    //     $('.modal').modal('hide');
    //     // $('#addCustomerStep2').modal('hide');
    //     $('#addMultipleCustomerStep3').modal('show');
    // });

    $(document).on('click',".edit-button",function () {
        var customerID=$(this).attr('data-customer-id');
        console.log(customerID);
        if(typeof(customerID)=='undefined' || customerID=='null' || customerID==null || customerID==''){
            swal({
                title: "",
                text: "OOPs! Something went wrong...",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }
        getCustomerDetail(customerID);
    });

    $(document).on('click',"#update-contact",function () {

        var plainInputsValid = false;

        $('span.help-block small').text('');
        $('span.help-block').closest('.form-group').removeClass('has-error');
        $('span.help-block').addClass('hide-me').removeClass('errorMsg');
        $("#edit_contact_form #error_Msg2").addClass('hide-me');

        var formClass = '.edit_contact_modal';
        var example = document.querySelector(formClass);
        var form = example.querySelector('form');

        Array.prototype.forEach.call(
            form.querySelectorAll(
                "#edit_contact_form input[type='text'], #edit_contact_form input[type='email']"
            ),
            function(input) {
                var input=$(input);
                var check=input.hasClass('fieldToValidate');
                console.log(check);
                if(check){
                    var spanEl=$(input).next("span");
                    if($(input).val()==''){
                        spanEl.removeClass('hide-me').addClass('errorMsg');
                        $(input).closest('.form-group').addClass('has-error');
                        $("small", spanEl).html("Required Field");
                        plainInputsValid = true
                    }
                    else{
                        if($(input).attr('type')=='email'){
                            var email=$(input).val();
                            var validEmailCheck=ValidateEmail(email);
                            if(validEmailCheck==false){
                                spanEl.removeClass('hide-me').addClass('errorMsg');
                                $(input).closest('.form-group').addClass('has-error');
                                $("small", spanEl).html("Enter Valid Email");
                                plainInputsValid = true
                            }

                        }
                    }
                }
            }
        );

        if (plainInputsValid) {
            return false;
        }

        var customerID = $.trim($("#edit_contact_form #edit-customer-id").val());
        var contactFirstName = $.trim($("#edit_contact_form #first_name").val());
        var contactLastName = $.trim($("#edit_contact_form #last_name").val());
        var contactEmailAddress = $.trim($("#edit_contact_form #email").val());
        var contactPhoneNumber = $.trim($("#edit_contact_form #phone_number").val());

        var baseUrl = $('#hfBaseUrl').val();
        showPreloader();
        $('#update-contact').addClass('disabled').attr('disabled','disabled');
        $('form#edit_contact_form input').blur();

        document.styleSheets[0].addRule('#update-contact.disabled:before', 'display: none;');
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/crm-update-customer",
            data: {
                'customer_id': customerID,
                'first_name': contactFirstName,
                'last_name': contactLastName,
                'email': contactEmailAddress,
                'phone_number': contactPhoneNumber
            },
            success: function (response, status) {
                console.log(response);
                var statusCode = response._metadata.outcomeCode;
                var statusMessage = response._metadata.outcome;
                var message = response._metadata.message;
                var errors = response.errors;
                var records = response.records;

                if(statusCode==200){
                    $('#edit_contact_modal').modal('hide');
                    swal({
                        title: "",
                        text: message,
                        type: 'success',
                        allowOutsideClick: false,
                        html: true,
                        showCancelButton: false,
                        confirmButtonColor: '#8CD4F5',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'OK',
                        cancelButtonText: "Cancel",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },function(){
                        swal.close();
                        showPreloader();
                        location.href = baseUrl+'/crm-customers';
                    });
                }
                else{
                    if(errors.length!=0){
                        $.each(errors, function (index, value) {
                            var errorSelector = $("#"+value.map).next("span");
                            errorSelector.removeClass('hide-me');
                            $("#"+value.map).closest('.form-group').addClass('has-error');
                            errorSelector.addClass('errorMsg');
                            $("small", errorSelector).html(value.message);
                        })
                    }
                    else{
                        $("#edit_contact_form #error_Msg2").removeClass('hide-me');
                        $("#edit_contact_form #error_Msg2 small").html(message);
                    }
                }
                hidePreloader();
                $('#update-contact').removeClass('disabled').removeAttr('disabled');
                document.styleSheets[0].addRule('#update-contact.disabled:before', 'display: inline-block;');
            },
            error: function (data, status) {
                $("#edit_contact_form #error_Msg2").removeClass('hide-me');
                $("#edit_contact_form #error_Msg2 small").html("OOPs! Something went wrong...");
                $('#update-contact').removeClass('disabled').removeAttr('disabled');
                document.styleSheets[0].addRule('#update-contact.disabled:before', 'display: inline-block;');
                hidePreloader();
            }
        });
    });

    $('body').on('hidden.bs.modal', '#add_contact_modal', function () {
        $(".modal-header #titleHeadingText").remove();
    });


    $('body').on('click',function (e){
        if($(e.target).hasClass('search-user')){
            $('span.search-user').next().val('');
            $('span.search-user').addClass('hide');
            $('span.search-user').next().removeClass('hide');
        }
        else{
            $('span.search-user').next().addClass('hide');
            $('span.search-user').removeClass('hide');
        }
    });

    $(document).on('change',"#sending_option",function (e) {
        e.preventDefault();
        var val=$(this).val();
        if(val=='1'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
        else if(val=='2'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
        else if(val=='3'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').addClass('hide');
        }
        else if(val=='4'){
            $('#preview_panel').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
            $('.email-preview-wrap').addClass('hide');
        }
        else if(val=='5'){
            $('#preview_panel').removeClass('hide');
            $('.email-preview-wrap').removeClass('hide');
            $('#sms-preview-wrap').removeClass('hide');
        }
    });

    $(document).on('change',"#enable_get_reviews",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var sendingOption=$('#sending_option').val();
        var smart_routing=$('#smart_routing').val();

        if(val=='Yes'){
            $('#sending_option_panel').removeClass('hide');

            $('#smart_routing_panel').removeClass('hide');
            var smart_routing=$('#smart_routing').val();
            if(smart_routing=='Disable'){
                $('#review_site_panel').removeClass('hide');
            }
            $('#send_reminder_panel').removeClass('hide');

            if(sendingOption!=null){
                if(sendingOption=='1'){
                    $('#preview_panel').removeClass('hide');
                    $('.email-preview-wrap').removeClass('hide');
                    $('#sms-preview-wrap').removeClass('hide');
                }
                else if(sendingOption=='2'){
                    $('#preview_panel').removeClass('hide');
                    $('.email-preview-wrap').removeClass('hide');
                    $('#sms-preview-wrap').removeClass('hide');
                }
                else if(sendingOption=='3'){
                    $('#preview_panel').removeClass('hide');
                    $('.email-preview-wrap').removeClass('hide');
                    $('#sms-preview-wrap').addClass('hide');
                }
                else if(sendingOption=='4'){
                    $('#preview_panel').removeClass('hide');
                    $('#sms-preview-wrap').removeClass('hide');
                    $('.email-preview-wrap').addClass('hide');
                }
                else if(sendingOption=='5'){
                    $('#preview_panel').removeClass('hide');
                    $('.email-preview-wrap').removeClass('hide');
                    $('#sms-preview-wrap').removeClass('hide');
                }
                $('#sending_option').selectpicker('val', sendingOption);
            }
            else{
                $('#sending_option').selectpicker('val', '');
            }
        }
        else{
            $('#sending_option_panel').addClass('hide');
            $('#smart_routing_panel').addClass('hide');
            $('#review_site_panel').addClass('hide');
            $('#send_reminder_panel').addClass('hide');

            $('#preview_panel').addClass('hide');
            $('.email-preview-wrap').addClass('hide');
            $('#sms-preview-wrap').addClass('hide');
        }
    });

    $(document).on('change',"#smart_routing",function (e) {
        e.preventDefault();
        var val=$(this).val();
        if(val=='Enable'){
            $('#review_site_panel').addClass('hide');
        }
        else if(val=='Disable'){
            $('#review_site_panel').removeClass('hide');
        }
    });

    /*--------------*/

    $(document).on('click',"#reset-email-default-preview",function (e){
        $('#email-preview-wrap .content-body').removeClass('editor-mode');
        $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content">Hi there!\n' +
            'Thanks for choosing '+businessName+'. If you have a ' +
            'few minutes, I\'d like to invite you to tell us about your ' +
            'experience. Your feedback is very important to us and it would be ' +
            'awesome if you can share it with us and our potential customers.' +
            '<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>' +
            '</p>' +
            '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#email-preview-wrap .content-body').attr('data-default','true');
    });

    $(document).on('click',"#reset-sms-default-preview",function (e){
        $('#sms-preview-wrap .content-body').removeClass('editor-mode');
        $('#sms-preview-wrap .content-body').html('' +
            '<p class="sms-content" id="sms-content">Hi there!\n' +
            'Thanks for choosing '+businessName+'. I\'d like to invite you to tell us about your experience. Any feedback is appreciated.' +
            '<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>' +
            '</p>' +
            '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#sms-preview-wrap .content-body').attr('data-default','true');
    });

    $(document).on('click',"#showCustomizeEmailModal",function (e){
        $('#customizeReviewRequestsBtn').addClass('disabled').attr('disabled','disabled');
        var checkDefault=$('#email-preview-wrap .content-body').attr('data-default');
        if(checkDefault=='false'){
            var emailContent=$('#email-content').html();
            emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
            emailContent=$.trim(emailContent);
            window.emailContent=emailContent;

            $('#email-preview-wrap .content-body').addClass('editor-mode');
            $('#email-preview-wrap .content-body').html('<textarea id="customizedEmailMessage" class="form-control" data-placeholder="Enter Text Here..."></textarea>\n' +
                '<div class="edit-icons">\n' +
                '  <a href="javascript:;" id="cancelCustomizedEmail"><span class="fa fa-remove"></span></a>\n' +
                '  <a href="javascript:;" id="updateCustomizedEmail"><span class="fa fa-check"></span></a>\n' +
                '</div>'
            );
            $('#customizedEmailMessage').val(emailContent);
        }
        else{
            $('#email-preview-wrap .content-body').addClass('editor-mode');
            $('#email-preview-wrap .content-body').html('<textarea id="customizedEmailMessage" class="form-control" data-placeholder="Enter Text Here..."></textarea>\n' +
                '<div class="edit-icons">\n' +
                '  <a href="javascript:;" id="cancelCustomizedEmail"><span class="fa fa-remove"></span></a>\n' +
                '  <a href="javascript:;" id="updateCustomizedEmail"><span class="fa fa-check"></span></a>\n' +
                '</div>'
            );
        }
    });

    $(document).on('click',"#showCustomizeSMSModal",function (e){
        $('#customizeReviewRequestsBtn').addClass('disabled').attr('disabled','disabled');
        var checkDefault=$('#sms-preview-wrap .content-body').attr('data-default');
        if(checkDefault=='false'){
            var smsContent=$('#sms-content').html();
            smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
            smsContent=$.trim(smsContent);
            window.smsContent=smsContent;

            $('#sms-preview-wrap .content-body').addClass('editor-mode');
            $('#sms-preview-wrap .content-body').html('<textarea id="customizedSMSMessage" class="form-control" data-placeholder="Enter Text Here..."></textarea>\n' +
                '<div class="edit-icons">\n' +
                '  <a href="javascript:;" id="cancelCustomizedSMS"><span class="fa fa-remove"></span></a>\n' +
                '  <a href="javascript:;" id="updateCustomizedSMS"><span class="fa fa-check"></span></a>\n' +
                '</div>'
            );
            $('#customizedSMSMessage').val(smsContent);
        }
        else{
            $('#sms-preview-wrap .content-body').addClass('editor-mode');
            $('#sms-preview-wrap .content-body').html('<textarea id="customizedSMSMessage" class="form-control" data-placeholder="Enter Text Here..."></textarea>\n' +
                '<div class="edit-icons">\n' +
                '  <a href="javascript:;" id="cancelCustomizedSMS"><span class="fa fa-remove"></span></a>\n' +
                '  <a href="javascript:;" id="updateCustomizedSMS"><span class="fa fa-check"></span></a>\n' +
                '</div>'
            );
        }
    });

    $(document).on('click',"#updateCustomizedEmail",function (e){
        var customizedEmailMessage=$('#customizedEmailMessage').val();
        console.log(customizedEmailMessage);
        if(customizedEmailMessage.length<=0){
            swal({
                title: "",
                text: "Please enter the email content",
                type: 'error',
                allowOutsideClick: false
            });
            return false;
        }
        customizedEmailMessage=customizedEmailMessage.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
        customizedEmailMessage=customizedEmailMessage+'<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>';
        $('#email-preview-wrap .content-body').removeClass('editor-mode');
        $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content"></p>' +
            '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#email-content').html(customizedEmailMessage);
        $('#email-preview-wrap .content-body').attr('data-default','false');


        var emailEditorMode=$('#email-preview-wrap .content-body').hasClass('editor-mode');
        var smsEditorMode=$('#sms-preview-wrap .content-body').hasClass('editor-mode');
        if(emailEditorMode==false && smsEditorMode==false){
            $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('click',"#updateCustomizedSMS",function (e){
        var customizedSMSMessage=$('#customizedSMSMessage').val();
        customizedSMSMessage=$.trim(customizedSMSMessage);
        console.log(customizedSMSMessage);
        console.log(customizedSMSMessage.length);

        if(customizedSMSMessage.length<=0){
            swal({
                title: "",
                text: "Please enter the text content",
                type: 'error',
                allowOutsideClick: false
            });
            return false;
        }

        if(customizedSMSMessage.length>160){
            swal({
                title: "",
                text: "The text message has exceeded 160 characters.",
                type: 'error',
                allowOutsideClick: false
            });
            return false;
        }
        customizedSMSMessage=customizedSMSMessage+'<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>';
        $('#sms-preview-wrap .content-body').removeClass('editor-mode');
        $('#sms-preview-wrap .content-body').html('' +
            '<p class="sms-content" id="sms-content"></p>' +
            '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
        );
        $('#sms-content').html(customizedSMSMessage);
        $('#sms-preview-wrap .content-body').attr('data-default','false');


        var emailEditorMode=$('#email-preview-wrap .content-body').hasClass('editor-mode');
        var smsEditorMode=$('#sms-preview-wrap .content-body').hasClass('editor-mode');
        if(emailEditorMode==false && smsEditorMode==false){
            $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('click',"#cancelCustomizedEmail",function (e){
        var checkDefault=$('#email-preview-wrap .content-body').attr('data-default');
        if(checkDefault=='false'){
            var emailContent=window.emailContent;
            $('#email-preview-wrap .content-body').removeClass('editor-mode');
            $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content"></p>' +
                '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
            );
            $('#email-content').html(emailContent+'<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>');
            $('#email-preview-wrap .content-body').attr('data-default','false');
        }
        else{
            $('#email-preview-wrap .content-body').removeClass('editor-mode');
            $('#email-preview-wrap .content-body').html('<p class="email-content" id="email-content">Hi there!\n' +
                'Thanks for choosing '+businessName+'. If you have a ' +
                'few minutes, I\'d like to invite you to tell us about your ' +
                'experience. Your feedback is very important to us and it would be ' +
                'awesome if you can share it with us and our potential customers.' +
                '<br><a class="demoLink" href="javascript:;">Add a Quick Review</a>' +
                '</p>' +
                '<a id="showCustomizeEmailModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
            );
            $('#email-preview-wrap .content-body').attr('data-default','true');
        }
        $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
    });

    $(document).on('click',"#cancelCustomizedSMS",function (e){
        var checkDefault=$('#sms-preview-wrap .content-body').attr('data-default');
        if(checkDefault=='false'){
            var smsContent=window.smsContent;
            $('#sms-preview-wrap .content-body').removeClass('editor-mode');
            $('#sms-preview-wrap .content-body').html('<p class="sms-content" id="sms-content"></p>' +
                '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
            );
            $('#sms-content').html(smsContent+'<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>');
            $('#sms-preview-wrap .content-body').attr('data-default','false');
        }
        else{
            $('#sms-preview-wrap .content-body').removeClass('editor-mode');
            $('#sms-preview-wrap .content-body').html('' +
                '<p class="sms-content" id="sms-content">Hi there!\n' +
                'Thanks for choosing '+businessName+'. I\'d like to invite you to tell us about your experience. Any feedback is appreciated.' +
                '<br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>' +
                '</p>' +
                '<a id="showCustomizeSMSModal" href="javascript:;"><span class="mdi mdi-pencil"></span></a>'
            );
            $('#sms-preview-wrap .content-body').attr('data-default','true');
        }
        $('#customizeReviewRequestsBtn').removeClass('disabled').removeAttr('disabled');
    });

    /*--------------*/
    // Makes sure the dataTransfer information is sent when we
    // Drop the item in the drop box.
    jQuery.event.props.push('dataTransfer');

    var z = -40;
    // The number of images to display
    var maxFiles = 5;
    var errMessage = 0;

    // Get all of the data URIs and put them in an array


    // Bind the drop event to the dropzone.
    $('#drop-files').bind('drop', function(e) {
        window.csvFileArray = [];
        var files = e.dataTransfer.files;
        console.log(files);
        if(files.length>1){
            swal({
                title: "",
                text: "Only 1 File is allowed to Upload",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }

        var csvfile=files[0];
        var res = csvfile.name.match(/.csv/g);
        console.log(res);
        if(res==null){
            swal({
                title: "",
                text: "Invalid Format, Please upload CSV file.",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }

        $('#csvFileName').text(csvfile.name);

        window.csvFileArray.push(files[0]);
        console.log(window.csvFileArray);

        $('#uploadCustomersCSVFile').val('');
        var $el = $('#fileUploadForm');
        $el.wrap('<form>').closest('form').get(0).reset();
        $el.unwrap();
    });

    // Just some styling for the drop file container.
    $('#drop-files').bind('dragenter', function() {
        $(this).css({'box-shadow' : 'inset 0px 0px 20px rgba(0, 0, 0, 0.1)', 'border' : '4px dashed #bb2b2b'});
        return false;
    });

    $('#drop-files').bind('drop', function() {
        $(this).css({'box-shadow' : 'none', 'border' : '4px dashed rgba(0,0,0,0.2)'});
        return false;
    });

    // For the file list
    /*-----------*/

    $(document).on('change', '#uploadCustomersCSVFile', function () {
        var input = $(this),
            numFiles = input.get(0).files ? input.get(0).files.length : 1,
            label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);

        if (input.get(0).files.length == 0) {
            $('#csvFileName').text('');
            input.val('');
            var $el = $('#fileUploadForm');
            $el.wrap('<form>').closest('form').get(0).reset();
            $el.unwrap();
        }
    });

    $('#uploadCustomersCSVFile').on('fileselect', function (event, numFiles, label) {
        var ext = $('#uploadCustomersCSVFile').val().split('.').pop().toLowerCase();
        var input = $(this).parents('.input-group').find(':text'),
            log = numFiles > 1 ? numFiles + ' files selected' : label;
        if (log) {
            if ($.inArray(ext, ['csv']) == -1) {
                swal({
                    title: "",
                    text: "Invalid Format, Please upload CSV file.",
                    type: 'error',
                    allowOutsideClick: false,
                    html: true,
                    showCancelButton: false,
                    confirmButtonColor: '#8CD4F5 ',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                });

                $('#csvFileName').text('');
                input.val('');

                var $el = $('#fileUploadForm');
                $el.wrap('<form>').closest('form').get(0).reset();
                $el.unwrap();
            } else {
                var csvfile = document.getElementById('uploadCustomersCSVFile').files[0];
                $('#csvFileName').text(csvfile.name);

                window.csvFileArray = [];

                if (input.length) {
                    input.val(log);
                } else {
                    // if (log) alert(log);
                }
            }
        }
    });

    $(document).on("click", "#upload_csv", function (e) {
        var alreadyAddedCheck= $('#upload_csv').attr('data-already-added');
        if(alreadyAddedCheck=='true'){
            $('#addMultipleCustomerStep3').modal('hide');
            $('#addCustomerStep2').modal('show');
            return false;
        }

        e.preventDefault();
        if (document.getElementById("uploadCustomersCSVFile").files.length == 0 && window.csvFileArray.length == 0) {
            swal({
                title: "",
                text: "File is required. Please upload CSV file.",
                type: 'error',
                allowOutsideClick: false,
                html: true,
                showCancelButton: false,
                confirmButtonColor: '#8CD4F5 ',
                cancelButtonColor: '#d33',
                confirmButtonText: 'OK',
                cancelButtonText: "Cancel",
                closeOnConfirm: true,
                closeOnCancel: true
            });
            return false;
        }
        console.log(window.csvFileArray);

        var file_data = document.getElementById('uploadCustomersCSVFile').files[0];
        console.log(file_data);
        if(typeof(file_data)=="undefined"){
            file_data=window.csvFileArray[0];
        }
        console.log(file_data);
        addCustomersCSV(file_data);
    });














});