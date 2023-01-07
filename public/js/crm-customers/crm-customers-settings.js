function saveCustomersSettings(enable_get_reviews,smart_routing,sending_option,review_site,send_reminder){
    var baseUrl = $('#hfBaseUrl').val();
    var data={
        enable_get_reviews: enable_get_reviews,
        smart_routing: smart_routing,
        sending_option: sending_option,
        review_site: review_site,
        reminder: send_reminder
    };

    if($('#email-content').attr('data-default')!='true'){
        var emailContent=$('#email-content').html();
        emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
        data.email_message=emailContent;
    }
    if($('#sms-content').attr('data-default')!='true'){
        var smsContent=$('#sms-content').html();
        smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
        data.sms_message=smsContent;
    }

    $('#save_settings').button('loading');
    document.styleSheets[0].addRule('#save_settings.disabled:before', 'display: none;');
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url:  baseUrl + "/saveCustomersSettings",
        data: data,
        success: function (response, status) {
            console.log(response);
            var statusCode = response._metadata.outcomeCode;
            var statusMessage = response._metadata.outcome;
            var message = response._metadata.message;
            var errors = response.errors;
            var records = response.records;
            if(statusCode==200){
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
                }, function (isConfirm) {
                    if(enable_get_reviews==''){
                        window.enableGetReviews=null;
                    }
                    else{
                        window.enableGetReviews=enable_get_reviews;
                    }
                    if(sending_option==''){
                        window.sendingOption=null;
                    }
                    else{
                        window.sendingOption=sending_option;
                    }
                    $('#save_settings').addClass('disabled').attr('disabled','disabled');
                });

                $('#save_settings').button('reset');
                document.styleSheets[0].addRule('#save_settings.disabled:before', 'display: inline-block;');
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
                $('#save_settings').button('reset');
                document.styleSheets[0].addRule('#save_settings.disabled:before', 'display: inline-block;');
            }
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
            $('#save_settings').button('reset');
            document.styleSheets[0].addRule('#save_settings.disabled:before', 'display: inline-block;');
        }
    })
}

$(document).ready(function(){

    $('.modal').modal({
        backdrop: 'static',
        keyboard: false,
        show: false
    });

    window.enableGetReviews=enable_get_reviews;
    window.sendingOption=sending_option;


    window.smart_routing=smart_routing;
    window.review_site=review_site;
    window.reminder=reminder;



    if(window.enableGetReviews==''){
        window.enableGetReviews=null;
    }

    if(window.sendingOption==''){
        window.sendingOption=null;
    }



    if(window.smart_routing==''){
        window.smart_routing=null;
    }

    if(window.review_site==''){
        window.review_site=null;
    }

    if(window.reminder==''){
        window.reminder=null;
    }



    console.log(recordsData);

    if(typeof(recordsData.customize_email)=='undefined' || recordsData.customize_email==null || recordsData.customize_email==''){
        $('#email-content').attr('data-default','true');
        $('#email-content').html('Hi there!\n' +
            'Thanks for choosing '+businessName+'. If you have a ' +
            'few minutes, I\'d like to invite you to tell us about your ' +
            'experience. Your feedback is very important to us and it would be ' +
            'awesome if you can share it with us and our potential customers.\n' +
            '<a class="demoLink" href="javascript:;">Add a Quick Review</a>'
        );
    }
    else{
        $('#email-content').attr('data-default','false');
        $('#email-content').html(recordsData.customize_email+' <br><a class="demoLink" href="javascript:;">Add a Quick Review</a>');
    }

    if(typeof(recordsData.customize_sms)=='undefined' || recordsData.customize_sms==null || recordsData.customize_sms==''){
        $('#sms-content').attr('data-default','true');
        $('#sms-content').html('' +
            'Hi there!\n' +
            'Thanks for choosing '+businessName+'. I\'d like to invite you to tell us about your experience. Any feedback is appreciated.\n' +
            '<a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>'
        );
    }
    else{
        $('#sms-content').attr('data-default','false');
        $('#sms-content').html(recordsData.customize_sms+' <br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>');
    }

    console.log(window.enableGetReviews);
    console.log(window.sendingOption);

    if(window.enableGetReviews==null && window.sendingOption==null){
        $('#save_settings').removeClass('disabled').removeAttr('disabled');
    }
    else{
        $('#save_settings').addClass('disabled').attr('disabled','disabled');
    }

    if(window.enableGetReviews=='Yes'){
        $('#sending_option_panel').removeClass('hide');
        $('#smart_routing_panel').removeClass('hide');
        $('#send_reminder_panel').removeClass('hide');

        var val=window.sendingOption;
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

    var windowHeight = window.innerHeight - 170;
    $('.customers_settings_wrapper').css({ 'margin-bottom':'0','min-height':windowHeight});

    $('.selectpicker').selectpicker({
        noneSelectedText : 'Please Select' // by this default 'Nothing selected' -->will change to Please Select
    });

    $('#enable_get_reviews').selectpicker('val', window.enableGetReviews);
    $('#sending_option').selectpicker('val', window.sendingOption);



    $('#smart_routing').selectpicker('val', window.smart_routing);
    $('#send_reminder').selectpicker('val', window.reminder);


    if(window.enableGetReviews=='Yes' && window.smart_routing=='Disable' ){
        $('#review_site_panel').removeClass('hide');
    }
    $('#review_site').selectpicker('val', window.review_site);
    // $('#send_reminder').selectpicker('val', window.reminder);



    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": false,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
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

    /*-------------------------MouseOverEvent-------------------------*/
    $('#enable_get_reviews_panel').on({
        mouseover: function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('When set to "Yes", '+dynamicAppName+' automatically sends a review request to the customer you added.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('');
        }
    });

    $('#smart_routing_panel').on({
        mouseover: function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('Smart Routing is an algorithm that decides where to route your customers. It takes into consideration the average rating and review count all of your review sites have. If enabled, reviewers will be routed to the site with the lowest average rating. The purpose for this is to help improve your rating on this site. If all your sites have the same average rating, then we take a look at your total number of reviews.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('');
        }
    });

    $('#send_reminder_panel').on({
        mouseover: function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('Enable Send Reminder if you want us to send an automated review request reminder to the customers you added. This will help increase your chances of getting a review. Reminder will be sent once a week for 3 weeks.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('');
        }
    });

    $('#review_site_panel').on({
        mouseover: function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('Select which review site you want to redirect your customers to.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('');
        }
    });

    function initializeSendingOptionPanelMouseEvent(){
        $('#sending_option_panel').on({
            mouseover: function(e) {
                e.preventDefault();
                $('.help-text-container #helpText').text('Select how you want to send the review requests.');
            },
            mouseout:  function(e) {
                e.preventDefault();
                $('.help-text-container #helpText').text('');
            }
        });
    }
    initializeSendingOptionPanelMouseEvent();

    $('.customers-settings-select li[data-original-index="0"]').on({
        mouseover: function(e) {
            e.preventDefault();
            $('#sending_option_panel').off('mouseover');
            $('.help-text-container #helpText').text('Select Email Primary if most of your customers provide you their email address. This means that '+dynamicAppName+' will prioritize sending the review requests via email. However, if the customer does not have an email address, '+dynamicAppName+' will try to send the request via SMS.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            initializeSendingOptionPanelMouseEvent();
            $('.help-text-container #helpText').text('');
        }
    });

    $('.customers-settings-select li[data-original-index="1"]').on({
        mouseover: function(e) {
            e.preventDefault();
            $('#sending_option_panel').off('mouseover');
            $('.help-text-container #helpText').text('Select SMS Primary if most of your customers provide you their phone number. If selected, '+dynamicAppName+' will prioritize sending of the review requests via SMS. However, if the customer does not have a phone number, '+dynamicAppName+' will try to send the request via Email.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            initializeSendingOptionPanelMouseEvent();
            $('.help-text-container #helpText').text('');
        }
    });

    $('.customers-settings-select li[data-original-index="2"]').on({
        mouseover: function(e) {
            e.preventDefault();
            $('#sending_option_panel').off('mouseover');
            $('.help-text-container #helpText').text('Select Email Only if ALL of your customers provide you their email address. If selected, '+dynamicAppName+' will always try to send the review request via email. If there\'s no email address in the contact, review request will not be sent.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            initializeSendingOptionPanelMouseEvent();
            $('.help-text-container #helpText').text('');
        }
    });

    $('.customers-settings-select li[data-original-index="3"]').on({
        mouseover: function(e) {
            e.preventDefault();
            $('#sending_option_panel').off('mouseover');
            $('.help-text-container #helpText').text('Select SMS Only if ALL of your customers provide you their phone number. If selected, '+dynamicAppName+' will always try to send the review request via SMS. If there\'s no phone number in the contact, review request will not be sent.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            initializeSendingOptionPanelMouseEvent();
            $('.help-text-container #helpText').text('');
        }
    });

    $('.customers-settings-select li[data-original-index="4"]').on({
        mouseover: function(e) {
            e.preventDefault();
            $('#sending_option_panel').off('mouseover');
            $('.help-text-container #helpText').text('Select Email and SMS if ALL your contacts contain email address and phone number. If selected, '+dynamicAppName+' will only send the review request to a contact that contains both email address and phone number.');
        },
        mouseout:  function(e) {
            e.preventDefault();
            initializeSendingOptionPanelMouseEvent();
            $('.help-text-container #helpText').text('');
        }
    });

    $('#preview_panel').on({
        mouseover: function(e) {
            e.preventDefault();
            var sending_option=$('#sending_option').selectpicker('val');
            var helpText='';
            if(sending_option==1 || sending_option==2 || sending_option==5){
                helpText='The email and SMS content in the review request are not customizable.';
            }
            else if(sending_option==3){
                helpText='The email content in the review request is not customizable.';
            }
            else if(sending_option==4){
                helpText='The SMS content in the review request is not customizable.';
            }
            $('.help-text-container #helpText').text(helpText);
        },
        mouseout:  function(e) {
            e.preventDefault();
            $('.help-text-container #helpText').text('');
        }
    });
    /*-------------------------MouseOverEvent-------------------------*/

    // save submit
    $(document).on('click',"#save_settings",function (e) {
        e.preventDefault();
        var enable_get_reviews= $('#enable_get_reviews').val();
        // var smart_routing= 'Enable';

        var smart_routing= $('#smart_routing').val();
        var review_site='';

        if(smart_routing=='Enable'){
            review_site='';
        }
        else{
            review_site= $('#review_site').val();
        }

        // var send_reminder= $('#send_reminder').val();
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

        saveCustomersSettings(enable_get_reviews,smart_routing,sending_option,review_site,send_reminder)
    });

    $(document).on('change',"#enable_get_reviews",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var sendingOption=$('#sending_option').val();

        var smart_routing=$('#smart_routing').val();
        var review_site=$('#review_site').val();
        var send_reminder=$('#send_reminder').val();

        if(val == 'Yes'){
            $('#sending_option_panel').removeClass('hide');

            $('#smart_routing_panel').removeClass('hide');
            var smart_routing=$('#smart_routing').val();
            if(smart_routing=='Disable'){
                $('#review_site_panel').removeClass('hide');
            }

            $('#send_reminder_panel').removeClass('hide');

            var sendingOption=window.sendingOption;
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
            // $('#send_reminder_panel').addClass('hide');

            $('#preview_panel').addClass('hide');
            $('.email-preview-wrap').addClass('hide');
            $('#sms-preview-wrap').addClass('hide');
            $('#send_reminder_panel').addClass('hide');
            sendingOption=null;
        }

        if(window.enableGetReviews==val &&
            window.sendingOption==sendingOption &&
            window.smart_routing==smart_routing &&
            window.review_site==review_site
            // window.reminder==send_reminder
        ){
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
        else{
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('change',"#sending_option",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var enableGetReviews=$('#enable_get_reviews').val();

        var smart_routing=$('#smart_routing').val();
        var review_site=$('#review_site').val();
        var send_reminder=$('#send_reminder').val();

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

        if(window.sendingOption==val &&
            window.enableGetReviews==enableGetReviews  &&
            window.smart_routing==smart_routing &&
            window.review_site==review_site
            // window.reminder==send_reminder
        ){
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
        else{
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('change',"#smart_routing",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var enableGetReviews=$('#enable_get_reviews').val();

        var smart_routing=$('#smart_routing').val();
        var review_site=$('#review_site').val();
        var send_reminder=$('#send_reminder').val();

        if(val=='Enable'){
            $('#review_site_panel').addClass('hide');
        }
        else if(val=='Disable'){
            $('#review_site_panel').removeClass('hide');
        }

        if(window.sendingOption==val &&
            window.enableGetReviews==enableGetReviews &&
            window.smart_routing==smart_routing &&
            window.review_site==review_site
            // window.reminder==send_reminder
        ){
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
        else{
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('change',"#review_site",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var enableGetReviews=$('#enable_get_reviews').val();
        var smart_routing=$('#smart_routing').val();
        var send_reminder=$('#send_reminder').val();

        if(window.review_site==val &&
            window.enableGetReviews==enableGetReviews &&
            window.smart_routing==smart_routing
            // window.reminder==send_reminder
        ){
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
        else{
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
    });

    $(document).on('change',"#send_reminder",function (e) {
        e.preventDefault();
        var val=$(this).val();
        var enableGetReviews=$('#enable_get_reviews').val();
        var smart_routing=$('#smart_routing').val();
        var review_site=$('#review_site').val();

        if(window.reminder==val &&
            window.enableGetReviews==enableGetReviews &&
            window.smart_routing==smart_routing &&
            window.review_site==review_site ){
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
        else{
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
    });
    /*-------------------------New Events-------------------------*/
    $(document).on('click',"#reset-sms-default-preview",function (e){
        var checkDefaultAttr=$('#sms-content').attr('data-default');
        console.log(checkDefaultAttr);

        $('#sms-content').attr('data-default','true');
        $('#sms-content').html('' +
            'Hi there!\n' +
            'Thanks for choosing '+businessName+'. I\'d like to invite you to tell us about your experience. Any feedback is appreciated.\n' +
            '<a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>'
        );

        if(checkDefaultAttr!='true'){
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
        else{
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
    });

    $(document).on('click',"#reset-email-default-preview",function (e){
        var checkDefaultAttr=$('#email-content').attr('data-default');
        console.log(checkDefaultAttr);

        $('#email-content').attr('data-default','true');
        $('#email-content').html('Hi there!\n' +
            'Thanks for choosing '+businessName+'. If you have a ' +
            'few minutes, I\'d like to invite you to tell us about your ' +
            'experience. Your feedback is very important to us and it would be ' +
            'awesome if you can share it with us and our potential customers.\n' +
            '<a class="demoLink" href="javascript:;">Add a Quick Review</a>'
        );

        if(checkDefaultAttr!='true'){
            $('#save_settings').removeClass('disabled').removeAttr('disabled');
        }
        else{
            $('#save_settings').addClass('disabled').attr('disabled','disabled');
        }
    });

    $(document).on('click',"#updateCustomizedEmail",function (e){
        var customizedEmailMessage=$('#customizedEmailMessage').val();
        if(customizedEmailMessage.length<=0){
            swal({
                title: "",
                text: "Please enter the message first",
                type: 'error',
                allowOutsideClick: false
            });
            return false;
        }
        customizedEmailMessage=customizedEmailMessage+' <br><a class="demoLink" href="javascript:;">Add a Quick Review</a>';
        $('#email-content').attr('data-default','false');
        $('#email-content').html(customizedEmailMessage);
        $('#customizeEmailModal').modal('hide');

        $('#save_settings').removeClass('disabled').removeAttr('disabled');
    });

    $(document).on('click',"#updateCustomizedSMS",function (e){
        var customizedSMSMessage=$('#customizedSMSMessage').val();
        customizedSMSMessage=$.trim(customizedSMSMessage);
        if(customizedSMSMessage.length<=0){
            swal({
                title: "",
                text: "Please enter the message first",
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
        customizedSMSMessage=customizedSMSMessage+' <br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>';
        $('#sms-content').attr('data-default','false');
        $('#sms-content').html(customizedSMSMessage);
        $('#customizeSMSModal').modal('hide');

        $('#save_settings').removeClass('disabled').removeAttr('disabled');
    });

    $("#customizedEmailMessage").on('keyup', function() {
        var customizedEmailMessage=$('#customizedEmailMessage').val();
        if($.trim(customizedEmailMessage).length!=0){
            customizedEmailMessage=customizedEmailMessage+' <br><br><a class="demoLink" href="javascript:;">Add a Quick Review</a>';
        }
        $($(this).data('copy')).html(customizedEmailMessage);
    });

    $("#customizedSMSMessage").on('keyup', function() {
        var customizedSMSMessage=$('#customizedSMSMessage').val();
        customizedSMSMessage=$.trim(customizedSMSMessage);
        $('#sms-character-count').text(customizedSMSMessage.length+'/160');
        if($.trim(customizedSMSMessage).length!=0){
            customizedSMSMessage=customizedSMSMessage+' <br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>';
        }
        $($(this).data('copy')).html(customizedSMSMessage);
    });

    $(document).on('click',"#showCustomizeEmailModal",function (e){
        var checkDefault=$('#email-content').attr('data-default');
        if(checkDefault=='false'){
            var emailContent=$('#email-content').html();

            emailContent=emailContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
            emailContent=$.trim(emailContent);

            $('#customizedEmailMessage').val(emailContent);
            emailContent=emailContent+' <br><br><a class="demoLink" href="javascript:;">Add a Quick Review</a>';
            $($('#customizedEmailMessage').data('copy')).html(emailContent);
        }
        else{
            $('#customizedEmailMessage').val('');
            $($('#customizedEmailMessage').data('copy')).html('');
        }
        $('#customizeEmailModal').modal('show');
    });

    $(document).on('click',"#showCustomizeSMSModal",function (e){
        var checkDefault=$('#sms-content').attr('data-default');
        if(checkDefault=='false'){
            var smsContent=$('#sms-content').html();
            smsContent=smsContent.replace(/(<br><a(?: \w+="[^"]+")* class="demoLink"(?: \w+="[^"]+")*>([^<]*)<\/a>)/g,"");
            smsContent=$.trim(smsContent);
            $('#customizedSMSMessage').val(smsContent);
            $('#sms-character-count').text($('#customizedSMSMessage').val().length+'/160');
            smsContent=smsContent+' <br><a class="demoLink" href="javascript:;">http://bit.ly/2LhkWmX</a>';
            $($('#customizedSMSMessage').data('copy')).html(smsContent);
        }
        else{
            $('#customizedSMSMessage').val('');
            $($('#customizedSMSMessage').data('copy')).html('');
            $('#sms-character-count').text($('#customizedSMSMessage').val().length+'/160');
        }
        // $('#sms-character-count').text($('#customizedSMSMessagePreview').text().length+'/160');
        $('#customizeSMSModal').modal('show');
    });

});


