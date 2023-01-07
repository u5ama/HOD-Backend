$(function () {

    $('[data-toggle="popover"]').popover({
        html: true
    });


    /********* Popover ************************/
    var myVar;

    $('body').on('mouseover', '[data-toggle="popover"]', function(e){
        $(this).popover('show');
    });

    $('body').on('mouseleave', '[data-toggle="popover"]', function(e){
        var that = this;
        //console.log('mouseleave');
        myVar = setTimeout(function(){
//  console.log('myVar::mouseleave');
            $(that).popover('hide');
        }, 1000);

    });
    $('body').on('mouseover', '.popover', function (e) {
        clearTimeout(myVar);
        //console.log('clearTimeout::myVar');
        clearTimeout(myVar);
    });
    $('body').on('mouseleave', '.popover', function(e){
        var that = this;
// console.log('mouseleave');
        myVar = setTimeout(function(){
//  console.log('myVar::mouseleave');
            $(that).popover('hide');
        }, 1000);
    });

    /********* Popover ************************/


    $("#first_name").on('keyup', function() {
        var userName = $(this).val();
        var emailCheckbox=$('#addSingleReceiptEmailCheckbox').is(":checked");
        var smsCheckbox=$('#addSingleReceiptSMSCheckbox').is(":checked");
        console.log(emailCheckbox);
        console.log(smsCheckbox);

        if(emailCheckbox==true){
            var emailPreviewWrapper = $(".email-preview-wrap");
            if(userName !== '') {
                emailPreviewWrapper.fadeIn();
                $(".user-name").html(userName);
            }
            else {
                emailPreviewWrapper.fadeOut();
            }
        }

        if(smsCheckbox==true){
            var smsPreviewWrapper = $(".sms-preview-wrap");
            if(userName !== '') {
                smsPreviewWrapper.fadeIn();
                $(".user-name").html(userName);
            }
            else {
                smsPreviewWrapper.fadeOut();
            }
        }

        if(userName == '') {
            $("#preview_box_note").fadeOut();
        }
        else{
            if(emailCheckbox==false && smsCheckbox==false){
                $("#preview_box_note").fadeOut();
            }
            else{
                $("#preview_box_note").fadeIn();
            }
        }
    });

    // Code added
    $('#addSingleReceiptEmailCheckbox').change(function() {
        var userName = $('#first_name').val();
        var emailPreviewWrapper = $(".email-preview-wrap");
        console.log($(this).is(":checked"));
        if($(this).is(":checked")) {
            $('#email').attr('data-required', true);
            if(userName !== '')
            {
                emailPreviewWrapper.fadeIn();
                $(".user-name").html(userName);
            }
            else
            {
                emailPreviewWrapper.fadeOut();
            }

            $('#email_container').removeClass('hide');
        }
        else{
            $('#email').attr('data-required', false);
            emailPreviewWrapper.fadeOut();

            $('#email_container').addClass('hide');

            $('#email').parent().removeClass('has-error');
            $('#email').parent().find('span.help-block').removeClass('error').addClass('hide-me');
            $('#email').parent().find('span.help-block small').text('');
            errorFound = false;
        }

        var emailCheckbox=$('#addSingleReceiptEmailCheckbox').is(":checked");
        var smsCheckbox=$('#addSingleReceiptSMSCheckbox').is(":checked");
        if(emailCheckbox==false && smsCheckbox==false){
            $("#preview_box_note").fadeOut();
        }
        else{
            if(userName !== '') {
                $("#preview_box_note").fadeIn();
            }
        }

    });

    $('#addSingleReceiptSMSCheckbox').change(function() {
        var userName = $('#first_name').val();
        var smsPreviewWrapper = $(".sms-preview-wrap");
        console.log($(this).is(":checked"));
        if($(this).is(":checked")) {
            $('#phone_number').attr('data-required', true);
            if(userName !== '')
            {
                smsPreviewWrapper.fadeIn();
                $(".user-name").html(userName);
            }
            else
            {
                smsPreviewWrapper.fadeOut();
            }

            $('#phone_number_container').removeClass('hide');
        }
        else{
            $('#phone_number').attr('data-required', false);
            smsPreviewWrapper.fadeOut();

            $('#phone_number_container').addClass('hide');

            $('#phone_number').parent().removeClass('has-error');
            $('#phone_number').parent().find('span.help-block').removeClass('error').addClass('hide-me');
            $('#phone_number').parent().find('span.help-block small').text('');
            errorFound = false;
        }

        var emailCheckbox=$('#addSingleReceiptEmailCheckbox').is(":checked");
        var smsCheckbox=$('#addSingleReceiptSMSCheckbox').is(":checked");
        if(emailCheckbox==false && smsCheckbox==false){
            $("#preview_box_note").fadeOut();
        }
        else{
            if(userName !== '') {
                $("#preview_box_note").fadeIn();
            }
        }
    });






});

$("form.validate-me").submit(function(e)
{
    e.preventDefault();

    var emailCheckbox=$('#addSingleReceiptEmailCheckbox').is(":checked");
    var smsCheckbox=$('#addSingleReceiptSMSCheckbox').is(":checked");
    console.log(emailCheckbox);
    console.log(smsCheckbox);

    var request_type={};
    var emailVal='';
    if(emailCheckbox==true){
        emailVal=$("#email").val();

        request_type = {
            "0": 'email'
        };

    }
    else{
        emailVal='';
        //emailVal=$("#email").val();
    }

    var phoneNoVal='';
    if(smsCheckbox==true){
        phoneNoVal=$("#phone_number").val();

        request_type = {
            "0": 'sms'
        };
    }
    else{
        phoneNoVal='';
        //phoneNoVal=$("#phone_number").val();
    }

    if(emailCheckbox==true && smsCheckbox==true){
        request_type = {
            "0": 'email',
            "1": 'sms'
        };
    }

    var smartRouting = ($('#smart_routing').is(":checked")) ? 'enable' : 'disable';

    var baseUrl = $('#hfBaseUrl').val();

     var requestType = 'single';

     if($('#requestType').val() === 'multiple')
     {
         requestType = 'multiple';
     }

     if (requestType === 'single')
     {

         if(emailCheckbox==false && smsCheckbox==false){
             swal({
                 title: "",
                 text: "Please select atleast one request option.",
                 type: "error"
             });
             return false;
         }
         
         $(".action-button").hide();

         $(".alert-news").hide();
         $(".alert-news .alert").removeClass("alert-danger");
         $(".alert-news .alert").html('');

         $(".loader").show();
         $.ajax({
             headers: {
                 'X-CSRF-TOKEN': $('input[name="_token"]').val()
             },
             type: "POST",
             url: baseUrl + "/done-me",
             data: {
                 'send': 'add-recipient',
                 first_name: $("#first_name").val(),
                 last_name: $("#last_name").val(),
                 email: emailVal,
                 phone_number: phoneNoVal,
                 smart_routing: smartRouting,
                 type: request_type
             },
             error: function (data, status) {
                 $(".action-button").show();
                 $(".loader").hide();
                 $(".alert-news").show();
                 $(".alert-news .alert").addClass("alert-danger");
                 $(".alert-news .alert").html('Some Problem happened. please try again.');
                 $('html, body').animate({ scrollTop: 0 }, 'fast');
             }
         }).done(function (result) {
             // parse data into json
             var json = $.parseJSON(result);

             // get data
             var statusCode = json.status_code;
             var statusMessage = json.status_message;

             var errors = json.errors;
             var callAlert = 'finalize';

             $(".action-button").show();
             $(".loader").hide();

             if (statusCode == 200) {
                 swal({
                     title: "Successful!",
                     text: statusMessage,
                     type: "success"
                 }, function (callAlert) {
                     showPreloader();
                     location.href = baseUrl+'/recipients/requests-sent';
                 });
             }
             else {
                 if(statusCode == 70)
                 {
                     var email = json.errors[0].email;
                     var sms = json.errors[0].sms;
                     var email_icon='',sms_icon='';
                     var email_text='',sms_text='';

                     email_icon=email==0?'issue-icon.png':'success-icon.png';
                     email_text=email==0?'Email Failed':'Email Sent';

                     sms_icon=sms==0?'issue-icon.png':'success-icon.png';
                     sms_text=sms==0?'SMS Failed':'SMS Sent';

                     swal({
                         title: '<i class="mdi mdi-alert" style="color: #ffc107; font-size: 100px; line-height: 99px;"></i>',
                         text: statusMessage +
                         '<div style="margin-top: 30px;">' +
                             '<p style="margin-bottom: 10px;"><img style="margin-top: -4px;" src="'+baseUrl+'/public/images/'+email_icon+'"> <span>'+email_text+'</span></p>'+
                             '<p><img style="margin-top: -4px;" src="'+baseUrl+'/public/images/'+sms_icon+'"> <span>'+sms_text+'</span></p>'+
                         '</div>',
                         //imageUrl: baseUrl+'/public/images/'+email_icon,
                         //imageWidth: 600,
                         //imageHeight: 600,
                         customClass: 'swal-wide',
                         //confirmButtonColor: "#DD6B55",
                         confirmButtonText: "Okay",
                         html: true
                     }, function (callAlert) {
                         if(email==0 && sms==0){
                             swal.close();
                         }
                         else{
                             showPreloader();
                             location.href = baseUrl+'/recipients/requests-sent';
                         }
                     });
                 }
                 else
                 {
                     if(statusCode!=2){
                         $(".alert-news").show();
                         $(".alert-news .alert").addClass("alert-danger");
                         $(".alert-news .alert").html(statusMessage);
                     }
                     else{
                         $(".alert-news").hide();
                     }

                     $('html, body').animate({ scrollTop: 0 }, 'fast');

                     if(errors && errors != '')
                     {
                         $.each(errors, function (index, value) {
                             var errorSelector = $("#"+value.map).next("span");

                             errorSelector.removeClass('hide-me');
                             errorSelector.addClass('has-error');
                             errorSelector.addClass('error');
                             $("small", errorSelector).html(value.message);
                         })
                     }
                 }
             }
         });
     }
     else if(requestType === 'multiple')
     {
         var fileErrorContainer = $(".error");
         var fileError = fileErrorContainer.html();

         if(formError == true)
         {
             fileErrorContainer.show();
             fileErrorContainer.html('File is required. Please upload CSV file.');
         }
         else
         {
             fileErrorContainer.hide();
             $(".action-button").hide();

             $(".alert-news").hide();
             $(".alert-news .alert").removeClass("alert-danger");
             $(".alert-news .alert").html('');

             $(".loader").show();
             var formData = false;
             if (window.FormData) formData = new FormData();

             var file_data = document.getElementById('file').files[0];

             formData.append('smart_routing', smartRouting);
             formData.append('file', file_data);

             $.ajax({
                 headers: {
                     'X-CSRF-TOKEN': $('input[name="_token"]').val()
                 },
                 type: "POST",
                 url: baseUrl + "/recipients/save-multiple-recipient",
                 contentType: false,
                 cache: false,
                 processData: false,
                 data: formData,
                 error: function (data, status) {
                     $(".action-button").show();
                     $(".loader").hide();
                     $(".alert-news").show();
                     $(".alert-news .alert").addClass("alert-danger");
                     $(".alert-news .alert").html('Some Problem happened. please try again.');
                     $('html, body').animate({ scrollTop: 0 }, 'fast');
                 }
             }).done(function (result) {
                 // parse data into json
                 var json = $.parseJSON(result);
                 $(".action-button").show();
                 $(".loader").hide();

                 // get data
                 var statusCode = json.status_code;
                 var statusMessage = json.status_message;

                 var errors = json.errors;
                 var callAlert = 'finalize';

                 $(".action-button").show();
                 $(".loader").hide();

                 if (statusCode == 200) {
                     swal({
                         title: "Successful!",
                         text: statusMessage,
                         type: "success"
                     }, function (callAlert) {
                         showPreloader();
                         location.href = baseUrl+'/recipients/requests-sent';
                     });
                 }
                 else {
                     if(statusCode == 70)
                     {
                         swal({
                             title: "",
                             text: statusMessage,
                             type: "error"
                         }, function (callAlert) {
                             showPreloader();
                             location.href = baseUrl+'/recipients/requests-sent';
                         });
                     }
                     else
                     {
                         $(".alert-news").show();
                         $(".alert-news .alert").addClass("alert-danger");
                         $(".alert-news .alert").html(statusMessage);

                         $('html, body').animate({ scrollTop: 0 }, 'fast');

                         if(errors && errors != '')
                         {
                             $.each(errors, function (index, value) {
                                 var errorSelector = $("#"+value.map).next("span");

                                 errorSelector.removeClass('hide-me');
                                 errorSelector.addClass('has-error');
                                 errorSelector.addClass('error');
                                 $(".alert-danger").append('<br>');
                                 $(".alert-danger").append(value.message);
                             })
                         }
                     }
                 }
             });
         }
     }

});