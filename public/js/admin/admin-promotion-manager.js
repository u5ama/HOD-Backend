window.attachedLogoArray = [];

$(function () {
    var siteUrl = $("#hfBaseUrl").val();

    $("body").addClass('hide-sidebar');
    $(".main-sidebar, .sidebar-toggle").hide();
    $(".content-wrapper").addClass('removeSidebar');

    $(".select2").select2();

    $('#plan').multiselect({
        includeSelectAllOption: true,
        selectAllText: 'SELECT ALL',
        allSelectedText: 'All Selected',
        // nonSelectedText: 'Choose Plan',
        selectAllNumber: false,
        buttonText: function(options, select) {
            if(options.length === 0)
            {
                return '';
            }
            else if(options.length === 3)
            {
                return 'All Selected';
            }
            else if(options.length >= 1)
            {
                return options.length + ' selected';
            }
        }
    });

    $("#industry").change(function () {
        var selectedIndustry = $(this).val();
        var siteUrl = $('#hfBaseUrl').val();

        // console.log("selected is  " + selectedIndustry);

        if (selectedIndustry !== '') {
            console.log("not empty ");

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "GET",
                url: siteUrl + "/industry-niches",
                data: {
                    'industry': selectedIndustry
                }
            }).done(function (result) {
                // parse data into json
                var json = $.parseJSON(result);

                // get data
                var statusCode = json.status_code;
                var statusMessage = json.status_message;
                var data = json.data;

                // console.log("status code " + statusCode);
                // console.log("statusMessage " + statusMessage);
                // console.log(data);

                if (statusCode == 200) {
                    console.log("data");
                    console.log(data);
                    if (data) {
                        var html = '';
                        //html += '<option value="">SELECT A NICHE</option>';

                        $.each(data, function (index, value) {
                            html += '<option value="' + value.id + '">';
                            html += value.niche;
                            html += '</option>';
                        });

                        var nicheSelected = $("#niche").attr("data-selected-target");

                        console.log("html");
                        console.log(html);

                        if (html !== '') {
                            $("#niche").attr("disabled", false);
                            $("#niche").html(html);
                            $("#niche").select2();

                            if(nicheSelected && nicheSelected !== '')
                            {
                                $("#niche").val(nicheSelected);
                                $("#niche").select2();
                            }
                        }
                        else
                        {
                            $("#niche").html("");
                            $("#niche").select2();
                        }
                    }
                }

                // location.href = baseUrl+'/thank-you';
            });
        } else {
            $("#niche").html("<option>SELECT A NICHE</option>");
            $("#niche").attr("disabled", true);
        }

    });

    $("#industry").change();


    var pixie = new Pixie({
        // image: state,
        state: state,
        openImageDialog: false,
        visible: true,
        // history: true,
        // allowZoom: false,
        // compact: false, // filter to left
        // showExportPanel: true, it will requrie some image before export
        // toolbar: {
        // hide: false,
        // hideOpenButton: false,
        // hideSaveButton: false,
        // openButtonAction: true,
        // },
        // },
        // watermarkText: 'Pixie Demo',
        // image: 'http://url-to-image.png',
        baseUrl: siteUrl+'/public/plugins/pixie/',
        onLoad: function() {

            // window.postMessage('pixieLoaded', '*');

            $(".open-button").before('<a href="'+siteUrl+'/admin/promotions/list" class="btn btn-default back-button" style="padding-left: 30px;padding-right: 30px; margin-right: 16px;">Back</a>');

            $(".export-button").after('<button class="mat-button publish-button" mat-button=""><span class="mat-button-wrapper"><mat-icon class="mat-icon mat-icon-no-color" role="img" svgicon="file-download" aria-hidden="true" style="\n' +
                '    margin-right: 5px;\n' +
                '">' +
                '<img style="width: 28px;margin-top: 0px;padding-right: 3px;" src="'+siteUrl+'/public/images/icons/publish_icon.png" />' +
                '</mat-icon>' +
                '<span class="name" trans="">Publish</span></span><div class="mat-button-ripple mat-ripple" matripple=""></div><div class="mat-button-focus-overlay"></div></button>');

            getTemplate(window.templateId);

            // pixie.get('canvas').on('object:added', function(e) {
            //     console.log(e.target);
            // });

            // var state = pixie.getState();

            // console.log("siteUrl ");
            // console.log(siteUrl);
            //
            // // var state ;
            // console.log("state post");
            // console.log(state);


            // pixie.loadState(state).then(function() {
            //     //state has been loaded
            // });

            // console.log(state);
            // pixie.http().post('http://nichepractice.test/done-me', {state: state});

            // pixie.http().get(state).then(function() {
            //     console.log("state has been loaded ");
            // });

            // pixie.loadStateFromUrl('https://your-site.com/state.json').then(function() {
            //     //state has been loaded
            // });
        },
        onSave: function(data, name) {
            // console.log("save called");
            // console.log(data);
            // console.log("name");
            // console.log(name);

            // pixie.http().post(
            //     siteUrl + "/done-me",
            //     {
            //         response: state,
            //         send: 'save-promotion-template',
            //     }
            //     ).subscribe(function(response) {
            //     console.log(response);
            //     });

            var state = pixie.getState();
            console.log("templateId");
            console.log(templateId);
            saveTemplate(state, "", templateId);
        }
    });
});

setTimeout(function () {
    $('.cdk-overlay-connected-position-bounding-box').bind("DOMNodeRemoved", function(e)
    {
        $(".input-form").show();
        console.log("removed");
    });
}, 1000);


$(document).on('click',"#logo",function (e) {
    $("#add_logo_image").click();
});

function setupReader(file,preview) {
    console.log("file");
    console.log(file);

    console.log("preview");
    console.log(preview);
    var reader  = new FileReader();

    console.log("reader");
    console.log(reader.result);

    reader.onloadend = function () {
        preview.src = reader.result;

    };

    console.log("preview src");
    console.log(preview.src);

    if (file) {
        console.log("file");
        console.log(file);

        reader.readAsDataURL(file);
    } else {
        preview.src = "";
    }
}

$(document).on('change',"#add_logo_image",function (e){
    console.log("add_logo_image");
    var imagePicker = $("#add_logo_image");
    var attachedImages= $('.logo-image-container .attached_images_container .show-image');
    console.log(attachedImages);

    var fileUploadStatus = false;
    var NumOfAttachedImages = attachedImages.length;

    var limitsArray=[];

    var files  = document.querySelector("#add_logo_image").files;

    console.log("add_logo_image > NumOfAttachedImages");
    console.log(NumOfAttachedImages);

    console.log("add files");
    console.log(files);


    for (var y = 0; y < files.length; y++) {
        var file    = files[y];
        var fileType=file.type;
        var fileSize=file.size;

        var validImageTypes=['image/png','image/jpeg'];
        var checkFileType=$.inArray( fileType, validImageTypes ) ;
        //var res = fileType.match(/image\.*/i);
        if(checkFileType == -1){
            $('.logo-image-container .limit_exceeded_error_msg').text("File format is invalid. Please upload valid image formats like <jpg,png>.");
            $('.logo-image-container .limit_exceeded_error_msg_container').removeClass('hide');

            //$('#add_post_modal .help-block small').text('').text("File format is invalid. Please upload valid image formats like <jpg,png>.");

            imagePicker.val('');
            return false;
        }

        if(fileSize>10485760){
            $('.logo-image-container .limit_exceeded_error_msg').text("File size cannot be more than 10MB.");
            $('.logo-image-container .limit_exceeded_error_msg_container').removeClass('hide');
            imagePicker.val('');
            return false;
        }

    }

    $('.logo-image-container .limit_exceeded_error_msg_container').addClass('hide');
    // var allowedImages = minLimit;

    var images = attachedImages;
    var imagesLength = images.length;
    var customImgId = '';

    if(images.length == 0){
        customImgId = images.length+1;
    }
    else{
        var lastImageEl=images[images.length-1];
        var lastImageClass=$(lastImageEl).find('img').attr('class');
        var num = parseInt(lastImageClass.match(/\d+/));
        customImgId=num+1;
    }

    for (var x = 0; x < files.length; x++) {
        var file    = files[x];
        var fileType=file.type;
        var fileSize=file.size;

        var validImageTypes=['image/png','image/jpeg'];
        var checkFileType=$.inArray( fileType, validImageTypes ) ;
        //var res = fileType.match(/image\.*/i);
        if(checkFileType == -1){
            $('.logo-image-container .limit_exceeded_error_msg').text("File format is invalid. Please upload valid image formats like <jpg,png>.");
            $('.logo-image-container .limit_exceeded_error_msg_container').removeClass('hide');

            //$('#add_post_modal .help-block small').text('').text('Invalid Image');

            imagePicker.val('');
            return false;
        }

        if(fileSize>10485760){
            $('.logo-image-container .limit_exceeded_error_msg').text("File size cannot be more than 10MB.");
            $('.logo-image-container .limit_exceeded_error_msg_container').removeClass('hide');
            imagePicker.val('');
            return false;
        }

        var newCustomImgId = customImgId+x;
        var imageTemplate='<div class="small-4 columns show-image"><img data-name="'+file.name+'" class="attached_image_'+newCustomImgId+'" src=""><span class="remove_image">x</span> </div>';
        $('.logo-image-container .attached_images_container').html(imageTemplate);
        var preview = document.querySelector('.logo-image-container img.attached_image_'+newCustomImgId);

        // console.log("in");
        // console.log(preview);
        // return false;
        setupReader(file,preview);

        window.attachedLogoArray[0] = file;

        fileUploadStatus = true;
        // window.attachedLogoArray = file;
    }

    imagePicker.val('');

    if(fileUploadStatus === true)
    {
        console.log("ready to logo Image save");
        // $("form.validate-image").submit();
    }
});

$(document).on('click',".remove_image",function (e) {
    console.log("called");
    if(typeof($(this).closest('.show-image').attr('data-attachment-id'))!='undefined'){
        console.log("has " + $(this).closest('.form-group').attr('id'));
        var attachmentId = '';
        if($(this).closest('.form-group').attr('id') === "logo-container")
        {
            console.log("inside logo");
            console.log(window.attachedLogoDeletedArray);
            attachmentId = $(this).closest('.show-image').attr('data-attachment-id');
            window.attachedLogoDeletedArray.push(attachmentId);

            console.log("Logo inside after att " + attachmentId);
            console.log(window.attachedLogoDeletedArray);
        }
        else
        {
            console.log("inside");
            console.log(window.attachedDeletedArray);
            attachmentId = $(this).closest('.show-image').attr('data-attachment-id');
            window.attachedDeletedArray.push(attachmentId);

            console.log("inside after att " + attachmentId);
            console.log(window.attachedDeletedArray);
        }
    }

    var imageName=$(this).closest('.show-image').find('img').attr('data-name');
    window.attachedImagesArray = $.grep(window.attachedImagesArray, function(item) {
        return item.name !== imageName;
    });

    window.attachedLogoArray = $.grep(window.attachedLogoArray, function(item) {
        return item.name !== imageName;
    });

    $(this).closest('.show-image').remove();
    var images=$('.attached_images_container .show-image');
    var imagesLength=images.length;

    if(imagesLength==0){
        $('#add_video_btn').removeClass('disabled').removeAttr('disabled');
        $('span.add-video-btn-disabled-tooltip').tooltip('destroy');
    }
    else if(imagesLength>0){
        $('#add_video_btn').addClass('disabled').attr('disabled','disabled');
    }
    if(imagesLength<4){
        $('.help-block small').text('');
    }
    $('#add_image_file').val('');

    /*-----------Images Limit Validation Code -------------------*/

    $('#post_now_btn,.send_post_options button').removeClass('disabled').removeAttr('disabled');
    $('span.posts-btn-disabled-tooltip').tooltip('destroy');

    $('#add_post_modal .limit_exceeded_error_msg').text('');
    $('.limit_exceeded_error_msg_container').addClass('hide');

    var facebook_images_limit=window.facebook_images_limit;
    var twitter_images_limit=window.twitter_images_limit;
    var instagram_images_limit=window.instagram_images_limit;
    var linkedin_images_limit=window.linkedin_images_limit;

    var attachedImages=$('#add_post_modal .attached_images_container .show-image');
    var NumOfAttachedImages=attachedImages.length;

    var remainingFacebookImages=facebook_images_limit-NumOfAttachedImages;
    var remainingTwitterImages=twitter_images_limit-NumOfAttachedImages;
    var remainingInstagramImages=instagram_images_limit-NumOfAttachedImages;
    var remainingLinkedinImages=linkedin_images_limit-NumOfAttachedImages;

    var checkImagesError=false;
    (!$('.facebook-social-media-button.selected-social-media').length==0 && remainingFacebookImages<0) ? checkImagesError=true : '';
    (!$('.twitter-social-media-button.selected-social-media').length==0 && remainingTwitterImages<0) ? checkImagesError=true : '';
    (!$('.instagram-social-media-button.selected-social-media').length==0 && remainingInstagramImages<0) ? checkImagesError=true : '';
    (!$('.linkedin-social-media-button.selected-social-media').length==0 && remainingLinkedinImages<0) ? checkImagesError=true : '';

    if(checkImagesError){

        var limitsImagesArray=[],limitedImagesNetworks=[];
        var selectedNetworksImagesArr=$('.select-social-media-buttons-container button.selected-social-media');
        $(selectedNetworksImagesArr).each(function (a,b) {
            var selectedNetwork=$(b);
            if($(b).hasClass('facebook-social-media-button') && remainingFacebookImages<0){
                limitsImagesArray.push(facebook_images_limit);
                limitedImagesNetworks.push('Facebook');
            }
            else if($(b).hasClass('twitter-social-media-button') && remainingTwitterImages<0){
                limitsImagesArray.push(twitter_images_limit);
                limitedImagesNetworks.push('Twitter');
            }
            else if($(b).hasClass('instagram-social-media-button') && remainingInstagramImages<0){
                limitsImagesArray.push(instagram_images_limit);
                limitedImagesNetworks.push('Instagram');
            }
            else if($(b).hasClass('linkedin-social-media-button') && remainingLinkedinImages<0){
                limitsImagesArray.push(linkedin_images_limit);
                limitedImagesNetworks.push('Linkedin');
            }
        });

        var minImagesLimit=arrayMin(limitsImagesArray);

        if(limitedImagesNetworks.length>1){
            var limitedImagesNetworksFirstHalf = limitedImagesNetworks.slice(0, limitedImagesNetworks.length-1);
            var limitedImagesNetworksFirstHalfStr=limitedImagesNetworksFirstHalf.join(", ");
            var limitedImagesNetworksSecondHalf = limitedImagesNetworks.slice(limitedImagesNetworks.length-1, limitedImagesNetworks.length);
            var limitedImagesNetworksStr=limitedImagesNetworksFirstHalfStr+" and "+limitedImagesNetworksSecondHalf;
            var strMsg="Limit exceeded of images(s) for " + limitedImagesNetworksStr;
        }
        else{
            var limitedImagesNetworksStr=limitedImagesNetworks.join(", ");

            if(minImagesLimit==0){
                var strMsg='We currently don\'t support publishing multimedia to ' + limitedImagesNetworksStr + '. Deselect ' + limitedImagesNetworksStr + ' if you want to publish a multimedia post to other social media pages.';
            }
            else{
                var strMsg="Can’t upload more than " + minImagesLimit + " images(s) on " + limitedImagesNetworksStr ;
            }

        }

        $('#add_post_modal .limit_exceeded_error_msg').text('').text(strMsg);
        $('.limit_exceeded_error_msg_container').removeClass('hide');


        $('#post_now_btn,.send_post_options button').addClass('disabled').attr('disabled','disabled');
        $('span.posts-btn-disabled-tooltip').tooltip('destroy');
        setTimeout(function () {
            $("span.posts-btn-disabled-tooltip").tooltip({
                placement : 'top',
                title: "Post cannot be made as image(s) limit exceeded."
            });
        },200);
    }

});

$(document).on('click',".remove_link",function (e) {
    $(this).parent().addClass('hide');
});

$(document).on('click',".remove_limit_exceeded_error",function (e) {
    $(this).parent().addClass('hide');
});


$(document.body).on('click', '.publish-button', function (e) {
    var action = $(this).attr("data-action");
    var baseUrl = $('#hfBaseUrl').val();

    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
    $(mainModel).removeClass('welcome-process');
    $(mainModel).addClass('modal-publish');

    var html = '';
    html += '<div class="modal-body">\n' +
        '                <div class="jumbotron" style="border-radius: 0; display: flex; padding: 10px 20px; align-items: center; justify-content: space-between;">\n' +
        '                    <div>\n' +
        '                        <h3 class="m-0" style="font-weight: bold;">Social Media</h3>\n' +
        '                        <p class="m-0" style="font-size: 15px;">Apply to facebook pages or twitter accounts.</p>\n' +
        '                    </div>\n' +
        '                    <div>\n' +
        '                        <button class="btn btn-primary" style="border-radius: 0;padding: 10px 15px; font-size: 17px;">Select</button>\n' +
        '                    </div>\n' +
        '                </div>\n' +
        '\n' +
        '                <div class="jumbotron" style="border-radius: 0; display: flex; padding: 10px 20px; align-items: center; justify-content: space-between;">\n' +
        '                        <div>\n' +
        '                            <h3 class="m-0" style="font-weight: bold;">Embed Links</h3>\n' +
        '                            <p class="m-0" style="font-size: 15px;">Embed this design in your blog or website.</p>\n' +
        '                        </div>\n' +
        '                        <div>\n' +
        '                            <button class="btn btn-primary" style="border-radius: 0;padding: 10px 15px; font-size: 17px;">Select</button>\n' +
        '                        </div>\n' +
        '                    </div>\n' +
        '            </div>';

    mainModel.modal('show');
    $(".modal-header").prepend('<h4 class="modal-title" id="publishModalLabel">What Do you like to do with your promotion?</h4>');
    $(".modal-header").after(html);
});

$('#main-modal').on('hidden.bs.modal', function () {
    var mainModel = $('#main-modal');
    $(".modal-title", mainModel).remove();
});

function saveTemplate(json, html, templateId) {
    console.log("save called");
    // return true;
    var siteUrl = $('#hfBaseUrl').val();
    // Implement your own close callback
    // Data variable contains the response data of the save request

    var actionStatus = '';
    saveTemplateCall = 'progress';
    console.log("before ajax " + saveTemplateCall);

    console.log("window.actionStatus " + actionStatus);

    var dataItems = [];
    var data;
    var replyEmail;

    var title = $("#title").val();
    var industry = $("#industry").val();
    var niche = $("#niche").val();
    // var plan = $("#plan").val();
    var logo;

    if(window.attachedLogoArray.length !=0){
        logo = window.attachedLogoArray;
    }

    var selected = $('#plan option:selected');
    var plan = [];
    if(selected.length !== 0)
    {
        selected.each(function(){
            plan.push($(this).val());
        });
    }
    console.log("plan");
    console.log(plan);


    if(actionStatus === 'sendnow')
    {
        replyEmail = $("#reply-email").val();

        if(replyEmail === '')
        {
            // replyEmail = 'noreply@nichepractice.com';
        }

        data = {
            send: 'save-template',
            subject: $("#subject").val(),
            from: $("#from").val(),
            reply_email: replyEmail,
            status: 'published',
            id: templateId,
            response: json,
            template_preview: html
        };
    }
    else if(actionStatus === 'schedule')
    {
        replyEmail = $("#reply-email").val();

        if(replyEmail === '')
        {
            // replyEmail = 'noreply@nichepractice.com';
        }

        var scheduleAt = $("#datepicker").val() + ' ' + $("#custom_timepicker_hour_selector").val() + ':' + $("#custom_timepicker_minutes_selector").val() + ':00';

        data = {
            send: 'save-template',
            subject: $("#subject").val(),
            from: $("#from").val(),
            reply_email: replyEmail,
            schedule_at: scheduleAt,
            status: 'schedule',
            id: templateId,
            response: json,
            template_preview: html
        };
    }
    else
    {
        data = {
            send: 'admin-save-promotion-template',
            id: templateId,
            response: json,
            title: title,
            industry: industry,
            niche: niche,
            plan: plan
        };
    }

    dataItems.push(data);

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url: siteUrl + "/done-me",
        data: data
    }).done(function (result) {
        // console.log("res");
        // console.log(result);
        hidePreloader();

        saveTemplateCall = 'done';
        // console.log("After ajax " + saveTemplateCall);

        var json = $.parseJSON(result);
        var statusCode = json.status_code;
        var statusMessage = json.status_message;
        var data = json.data;

        // console.log(json);
        // return false;

        if(data.id && data.id !== '')
        {
            var template = data.id;
            var response = data.response;

            console.log("template > " + template + " ) > aa > " + templateId);

            if(template !== templateId)
            {
                var uri = window.location.toString();
                console.log("uri");
                console.log(uri);
                var clean_uri = uri.substring(0, uri.indexOf("promotion-template"));
                console.log("clean");
                console.log(clean_uri);
                clean_uri = clean_uri + 'promotion-template/'+template;
                console.log("modified Clean URL");
                console.log(clean_uri);
                window.history.replaceState({}, document.title, clean_uri);

                window.templateId = template;
            }
        }

        console.log("tem window.actionStatus " + actionStatus);

        if(statusCode === 200)
        {
            // hidePreloader();
            if((logo && logo.length > 0) && (window.templateId && window.templateId !== ''))
            {
                var formData = new FormData();

                formData.append('send', 'admin-save-promotion-template');
                formData.append('id', window.templateId);

                console.log("logo in");
                $.each(logo, function (i, obj) {
                    formData.append('attach_logo[' + i + ']', obj);
                });

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "POST",
                    url: siteUrl + "/done-me",
                    contentType: false,
                    cache: false,
                    processData: false,
                    data: formData,
                }).done(function (result) {
                });
            }

            swal({
                title: "Successful!",
                text: 'Changes Saved.',
                type: "success"
            }, function () {
            });

            // if(actionStatus === 'sendnow' || actionStatus === 'schedule')
            // {
            //     templateCustomerLink(actionStatus);
            //
            //     // if(actionStatus === 'sendnow')
            //     // {
            //     //     // send emails to recipients
            //     //     sendPreview(templateId, userId);
            //     // }
            //     //
            //     // swal({
            //     //     title: "Successful!",
            //     //     text: (actionStatus === 'sendnow') ? "Email set in queue for sending." : "Email has been Scheduled for " + scheduleAt,
            //     //     type: "success"
            //     // }, function () {
            //     //     showPreloader();
            //     //     location.href = siteUrl+'/email-campaigns';
            //     // });
            // }
        }
        else
        {
            hidePreloader();

            swal({
                title: "Error!",
                text: statusMessage,
                type: 'error'
            }, function () {
            });
        }

        // TopolPlugin.load("{\"tagName\":\"mj-global-style\",\"children\":[{\"tagName\":\"mj-container\",\"attributes\":{\"background-color\":\"#FFFFFF\",\"containerWidth\":600},\"children\":[{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"9px 0px 9px 0px\",\"background-color\":\"#F0C9D2\",\"background-url\":null},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"50%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"left\",\"font-size\":\"11\",\"locked\":\"true\",\"editable\":\"true\",\"padding-bottom\":\"0\",\"padding-top\":\"0\",\"containerWidth\":600,\"color\":\"#131212\",\"padding\":\"0px 0px 0px 0px\"},\"content\":\"<p>E-mail preheader</p>\\n\",\"uid\":\"iS11MzSD4\"}],\"uid\":\"_qoy4D-qm\"},{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"50%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"right\",\"font-size\":\"11\",\"locked\":\"true\",\"editable\":\"false\",\"padding-bottom\":\"0\",\"padding-top\":\"0\",\"containerWidth\":600,\"padding\":\"0px 0px 0px 0px\"},\"content\":\"<p><a draggable=\\\"false\\\" href=\\\"*|WEBVERSION|*\\\" style=\\\"color: #808080;\\\">Web version</a></p>\\n\",\"uid\":\"BLgQ51VTb\"}],\"uid\":\"XKU2mfGIam\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"Cr8P8-2HW\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-spacer\",\"attributes\":{\"height\":11,\"containerWidth\":600},\"uid\":\"7PUZ2FYlvo\"}],\"uid\":\"8E1XEl4Gw3\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"vzay56h_k\"},{\"tagName\":\"mj-section\",\"attributes\":{\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\",\"background-url\":\"https://storage.googleapis.com/afuxova10642/5a2b21eb054845.4677968115127761710216.png\",\"full-width\":\"full-width\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-image\",\"attributes\":{\"src\":\"https://storage.googleapis.com/afuxova10642/logo-2.png\",\"padding\":\"13px 13px 13px 13px\",\"alt\":\"\",\"href\":\"\",\"containerWidth\":150,\"width\":150,\"widthPercent\":100},\"uid\":\"H5Yi56k6X\"}],\"uid\":\"B13SjbN1E\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"Hk-_8ZN1V\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-spacer\",\"attributes\":{\"height\":11,\"containerWidth\":600},\"uid\":\"kbYc75QEdi\"}],\"uid\":\"KOjijFltLh\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"E2Oj0ukto\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"9px 0px 9px 0px\",\"background-color\":\"#F0C9D2\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"locked\":\"true\",\"editable\":\"false\",\"padding-bottom\":\"0\",\"padding-top\":\"0\",\"containerWidth\":600,\"padding\":\"0px 0px 0px 0px\"},\"content\":\"<p style=\\\"font-size: 11px;\\\"><span style=\\\"font-size:22px;\\\">Smartphones</span></p>\\n\\n<p style=\\\"font-size: 11px;\\\">Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium lectus id turpis. Mauris tincidunt sem sed arcu. Nulla est. Donec vitae arcu. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus. Fusce dui leo, imperdiet in</p>\\n\",\"uid\":\"VM_r3CEd4\"},{\"tagName\":\"mj-button\",\"attributes\":{\"align\":\"center\",\"background-color\":\"#6E9DE7\",\"color\":\"#fff\",\"border-radius\":\"24px\",\"font-size\":13,\"padding\":\"20px 20px 20px 20px\",\"inner-padding\":\"9px 26px\",\"href\":\"https://google.com\",\"font-family\":\"Ubuntu, Helvetica, Arial, sans-serif, Helvetica, Arial, sans-serif\",\"containerWidth\":600,\"border\":\"0px solid #000\"},\"content\":\"Discover more\",\"uid\":\"T6rNEjGaWF\"}],\"uid\":\"49VuRDdGAZ\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"JFgczWEh6\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-spacer\",\"attributes\":{\"height\":11,\"containerWidth\":600},\"uid\":\"FnDnKo6tk6\"}],\"uid\":\"KOjijFltLh\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"fw_PFqgPv\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"9px 0px 9px 0px\",\"background-color\":\"#EEE9E9\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"33.333333%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-image\",\"attributes\":{\"src\":\"https://storage.googleapis.com/afuxova10642/kisspng-telegram-computer-icons-apple-icon-image-format-telegram-icon-enkel-iconset-froyoshark-5ab08446a53055.4844118815215176386766.png\",\"padding\":\"0px 0px 0px 0px\",\"alt\":\"\",\"href\":\"\",\"containerWidth\":200,\"width\":90,\"widthPercent\":45},\"uid\":\"Fka2JLAsRB\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"0px 0px 0px 0px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"3Uvc77Km7\",\"content\":\"<p><strong>CLOUD</strong></p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"1px 1px 1px 1px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"9slQPSH12\",\"content\":\"<p>Nulla est. Donec vitae arcu. Duis bibendum</p>\\n\"}],\"uid\":\"P8a9SJGo9Z\"},{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"33.333333%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-image\",\"attributes\":{\"src\":\"https://storage.googleapis.com/afuxova10642/kisspng-telegram-computer-icons-apple-icon-image-format-telegram-icon-enkel-iconset-froyoshark-5ab08446a53055.4844118815215176386766.png\",\"padding\":\"0px 0px 0px 0px\",\"alt\":\"\",\"href\":\"\",\"containerWidth\":200,\"width\":90,\"widthPercent\":45},\"uid\":\"Z1lAj3nxk\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"0px 0px 0px 0px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"mOXsNQEVG\",\"content\":\"<p><b>INTERNET</b></p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"1px 1px 1px 1px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"U9TOgYtTn\",\"content\":\"<p>Nulla est. Donec vitae arcu. Duis bibendum</p>\\n\"}],\"uid\":\"gSAkKrXsPY\"},{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"33.333333%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-image\",\"attributes\":{\"src\":\"https://storage.googleapis.com/afuxova10642/kisspng-telegram-computer-icons-apple-icon-image-format-telegram-icon-enkel-iconset-froyoshark-5ab08446a53055.4844118815215176386766.png\",\"padding\":\"0px 0px 0px 0px\",\"alt\":\"\",\"href\":\"\",\"containerWidth\":200,\"width\":90,\"widthPercent\":45},\"uid\":\"qYFFLHlYQ\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"0px 0px 0px 0px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"0OYDcwxMf\",\"content\":\"<p><strong>APPS</strong></p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"1px 1px 1px 1px\",\"line-height\":1.5,\"containerWidth\":200},\"uid\":\"Mr0nB3c8_\",\"content\":\"<p>Nulla est. Donec vitae arcu. Duis bibendum</p>\\n\"}],\"uid\":\"KoDnmLsJQC\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"zdIAE2Ctp\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-spacer\",\"attributes\":{\"height\":11,\"containerWidth\":600},\"uid\":\"lhVergt8k\"}],\"uid\":\"8E1XEl4Gw3\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"G1VKUAZas\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"9px 0px 9px 0px\",\"background-color\":\"#F0C9D2\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"60%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-image\",\"attributes\":{\"src\":\"https://storage.googleapis.com/afuxova10642/kisspng-mobile-app-development-infographic-mobile-device-vector-phone-app-5aa8c45ba73be3.064688941521009755685.png\",\"padding\":\"0px 0px 0px 0px\",\"alt\":\"\",\"href\":\"\",\"containerWidth\":300,\"width\":300,\"widthPercent\":100},\"uid\":\"o7NXhRUX6\"}],\"uid\":\"g-Grf7aLX\"},{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"40%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"left\",\"font-size\":\"11\",\"padding\":\"5px 5px 5px 5px\",\"line-height\":1.5,\"containerWidth\":300},\"uid\":\"o6nDKFpdv\",\"content\":\"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium</p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"left\",\"font-size\":\"11\",\"padding\":\"5px 0px 5px 15px\",\"line-height\":1.5,\"containerWidth\":300},\"uid\":\"gPFW27gCu\",\"content\":\"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium lectus id turpis.</p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"left\",\"font-size\":\"11\",\"padding\":\"5px 5px 5px 5px\",\"line-height\":1.5,\"containerWidth\":300},\"uid\":\"nW-n40KBk\",\"content\":\"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Pellentesque pretium</p>\\n\"}],\"uid\":\"xTvKhTYdTK\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"zdix1J84_\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"0px 0px 0px 0px\",\"background-color\":\"#FFFFFF\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-spacer\",\"attributes\":{\"height\":11,\"containerWidth\":600},\"uid\":\"yXtby-V9h2\"}],\"uid\":\"q9ZBP_aJDV\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"QjHhIg5da\"},{\"tagName\":\"mj-section\",\"attributes\":{\"full-width\":\"full-width\",\"padding\":\"9px 0px 9px 0px\",\"background-color\":\"#FFFFFF\",\"background-url\":\"https://storage.googleapis.com/afuxova10642/5a2b21eb054845.4677968115127761710216-1.png\"},\"type\":null,\"children\":[{\"tagName\":\"mj-column\",\"attributes\":{\"width\":\"100%\",\"vertical-align\":\"top\"},\"children\":[{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"padding\":\"15px 15px 15px 15px\",\"line-height\":1.5,\"containerWidth\":600},\"uid\":\"AcvebToUz\",\"content\":\"<p>Contact address</p>\\n\\n<p>Why You get this newsletter?</p>\\n\"},{\"tagName\":\"mj-text\",\"attributes\":{\"align\":\"center\",\"font-size\":\"11\",\"locked\":\"true\",\"editable\":\"true\",\"padding-bottom\":\"0\",\"padding-top\":\"0\",\"containerWidth\":600,\"padding\":\"0px 0px 0px 0px\"},\"content\":\"<p style=\\\"font-size: 11px;\\\">No more offers? <strong><span style=\\\"color: rgb(0, 0, 0);\\\"><a href=\\\"*|UNSUB|*\\\" style=\\\"color: #000000;\\\">Unsubscribe</a>.</span></strong></p>\\n\",\"uid\":\"Lt7pq5THM\"}],\"uid\":\"Rz7zv2CJTn\"}],\"layout\":1,\"backgroundColor\":\"\",\"backgroundImage\":\"\",\"paddingTop\":0,\"paddingBottom\":0,\"paddingLeft\":0,\"paddingRight\":0,\"uid\":\"1xXLEbUPm\"}]}],\"style\":[],\"attributes\":{\"mj-text\":{\"line-height\":1.5},\"mj-button\":[],\"mj-section\":{\"background-color\":\"#FFFFFF\"}},\"fonts\":[]}");
        // TopolPlugin.load(data);
    });

    console.log("After call " + saveTemplateCall);
}

function getTemplate(templateId) {
    console.log("tem");
    console.log(templateId);

    // $(".steps-nav").show();

    // $(".loading-bar").hide();
    // $(".action-center").show();



    if(templateId && templateId !== '')
    {
        $(".input-form").show();
        // console.log("id");
        var siteUrl = $('#hfBaseUrl').val();
        // Implement your own close callback
        // Data variable contains the response data of the save request

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: siteUrl + "/done-me",
            data: {
                send: 'admin-get-promotion-template',
                id: templateId,
                // user_id: userId,
                // response: json
            }
        }).done(function (result) {
            // console.log("res");
            // console.log(result);

            var json = $.parseJSON(result);
            var data = json.data;
            console.log("getdata");
            console.log(data);

            if(data.id && data.id !== '') {
                var title = data.title;
                var industry = data.industry;
                var thumbnail = data.thumbnail;
                var industryNiche = data.niche;
                var plan = data.template_plans;

                $("#title").val(title);

                if(industry && industry !== '')
                {
                    $("#industry").val(industry);
                    $("#industry").change();

                    if(industryNiche && industryNiche !== '')
                    {
                        $("#niche").attr("data-selected-target", industryNiche);
                        $("#niche").val(industryNiche);
                        $("#niche").select2();
                    }
                }

                console.log("plan");
                console.log(plan);

                if(plan)
                {
                    console.log("inside");
                    var planVal = '';
                    $.each(plan, function (index, value) {
                        planVal = value.plan;
                        console.log("planVal");
                        console.log(planVal);

                        // $("#plan").multiselect("widget").find(":checkbox[value='"+planVal+"']").attr("checked","checked");
                        $("#plan option[value='" + planVal + "']").attr("selected", true);
                        $("#plan").multiselect("refresh");
                    });
                }

                if(thumbnail && thumbnail !== '')
                {
                    var thumbnailUrl = siteUrl+'/storage/app/'+thumbnail;

                    $(".attached_images_container").html('<div class="small-4 columns show-image" data-attachment-id="'+thumbnail+'">\n' +
                        '                                                    <img data-name="0x.jpg" class="attached_image_ox" src="'+thumbnailUrl+'" />\n' +
                        '                                                    <span class="remove_image">x</span>\n' +
                        '                                                </div>');
                }
            }
        });
    }
}


