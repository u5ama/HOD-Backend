// saveTemplateCall = progress, done
saveTemplateCall = 'done';
actionStatus = '';

$(function () {

    // getTemplate(52);

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
            }else if(options.length >= 1)
            {
                return options.length + ' selected';
            }
            // else if(options.length > 1)
            // {
            //     return options;
            // }
            // return options.length == 3 ? 'All Selected':'Choose Plan';
        }
    });

    $("body").addClass('hide-sidebar');
    $(".main-sidebar, .sidebar-toggle").hide();
    $(".content-wrapper").addClass('removeSidebar');
    // $(".steps-nav").show();
    // $(".loading-bar").hide();


    // $("#industry").change();

    // $("#industry option:eq(1)").attr("selected","selected");


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
                console.log("loaded");

                if (statusCode == 200) {
                    if (data) {
                        var html = '';
                        //html += '<option value="">SELECT A NICHE</option>';

                        $.each(data, function (index, value) {
                            html += '<option value="' + value.id + '">';
                            html += value.niche;
                            html += '</option>';
                        });

                        var nicheSelected = $("#niche").attr("data-selected-target");


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

    $(".custom-checkbox").click(function () {
        var action = $(this).attr("data-action");
        var checkBox = $(".custom-checkbox");
        var currentType = ($(this).hasClass("custom-checkbox--checked") === true) ? 'selected' : '';

        if(action === 'all')
        {
            if(currentType === 'selected')
            {
                checkBox.removeClass('custom-checkbox--checked');
            }
            else
            {
                checkBox.addClass('custom-checkbox--checked');
            }
        }
        else
        {
            if(currentType === 'selected')
            {
                $(this).removeClass('custom-checkbox--checked');
            }
            else
            {
                $(this).addClass('custom-checkbox--checked');
            }
        }
    });

    $('#app iframe').on("load", function() {
        console.log("iframe loaded > " + userId);
        getTemplate(templateId);
    });

    $(".campaign-steps span a").click(function () {
        var action = $(this).attr("data-action");
        // console.log("action " + action);
        // console.log("this ");
        // console.log($(this));

        if( action !== '' && !($(this).hasClass('active')) )
        {
            // console.log("Hi Inside");
            $(".campaign-steps span a").removeClass('active');
            $(this).addClass('active');

            // hide all the steps section
            $('.steps-section').hide();

            if(action === 'publish-container')
            {
                // check recipients are selected
                var recipients = [];
                recipients = getRecipients();

                // console.log('ac recipients');
                // console.log(recipients.length);

                if(recipients.length === 0)
                {
                    // console.log('insid');
                    $(".publish-footer .btn").attr("disabled", true);
                    $("."+action + ' .alert').show();
                    // return false;
                }
                else
                {
                    // console.log('ELSE');
                    $(".publish-footer .btn").attr("disabled", false);
                    $("."+action + ' .alert').hide();
                }
            }

            // console.log("Go Next");
            $('.'+action).show();
        }
    });

    $(".save-action").click(function () {
        showPreloader();
        TopolPlugin.save();
    });
});


$(".btn-sendnow").click(function () {
    showPreloader();
    window.actionStatus = 'sendnow';
    $(".save-action").click();
});

var baseUrl = $('#hfBaseUrl').val();
// var docLogo = baseUrl + '/public/images/data-logo-template-doctor-logo.jpg';
// var docPhoto = baseUrl + '/public/images/data-photo-template-doc-photo.jpg';

var imageDir = $('#storage-path').val();

var docLogo = imageDir + '/template-doctor-logo.jpg';
var docPhoto = imageDir + '/template-doc-photo.jpg';

// var userBusinessData = userData.business[0];
var TOPOL_OPTIONS = {
    id: "#app",
    authorize: {
        apiKey: "Gp1QsJyfAZwRlizPdy1pZ0pnASId49umK6Y5ptc99OoycrumsNHmTRPwEXTw",
        userId: userId,
    },
    language: "en",
    light: true,
    topBarOptions: [
        // 'saveButtons'
        "undoRedo",
        "changePreview",
        "previewSize",
        "previewTestMail"
    ],
    sendTestEmail: true,
    // removeTopBar: false,
    disableAlerts: true,
    // changePreview: true,
    // templateId: 1,
    mergeTags: [
            { name: 'token', // Group name
            items: [
                {
                    // value: "<img style='min-width:300px; min-height: 60px;' data-token-name='Doctor_Logo' src=\"%%Doctor_Logo%%\">",
                    // value: "<img style='min-width:300px; min-height: 60px;' data-token-name='Doctor_Logo' src=\"%%Doctor_Logo%%\">",
                    value: '<img style="max-width:250px; max-height: 60px;" class="template-token-tag" data-token-name="Doctor_Logo" src="'+docLogo+'" />',
                    text: "Doctor Logo",
                },
                {
                    // value: "<img style='min-width:140px; min-height: 160px;' data-token-name='Doctor_Photo' src=\"%%Doctor_Photo%%\">",
                    // value: "<img style='min-width:140px; min-height: 160px;' data-token-name='Doctor_Photo' src=>",
                    value: '<img style="max-width:140px; max-height: 160px;" class="template-token-tag" data-token-name="Doctor_Photo" src="'+docPhoto+'" />',
                    text: "Doctor Photo",
                },
                {
                    value: "%%Doctor_Practice%%",
                    text: "Doctor Practice Name",
                },
                {
                    value: "%%Doctor_Website%%",
                    text: "Doctor Website",
                },
                {
                    value: "%%Doctor_Phone%%",
                    text: "Doctor Phone",
                },
                {
                    value: "%%Doctor_Name%%",
                    text: "Doctor Name",
                },
                {
                    value: "%%Doctor_first_name%%",
                    text: "Doctor First Name",
                },{
                    value: "%%Doctor_Last_Name%%",
                    text: "Doctor Last Name",
                },{
                    value: "%%Doctor_Email%%",
                    text: "Doctor Email",
                },{
                    value: "%%Doctor_Address%%",
                    text: "Doctor Address",
                },{
                    value: "%%Doctor_City%%",
                    text: "Doctor City",
                },{
                    value: "%%Doctor_State%%",
                    text: "Doctor State",
                },{
                    value: "%%Doctor_Zip%%",
                    text: "Doctor Zip",
                }
            ]
        }
    ],
    title: "My template builder",

    callbacks: {
        onSaveAndClose: function(json, html) {
            // console.log("onSaveAndClose");
            // console.log(json);
            // console.log(userId + ' > ' + templateId);
            // saveTemplate(json, html, templateId);
            // HTML of the email
            // console.log(html);
            // JSON object of the email
        },
        onSave: function(json, html) {
            // console.log("onSave");
            saveTemplate(json, html, templateId);

            // console.log("html");
            // console.log(html);
            // HTML of the email
            // console.log(html);
            // JSON object of the email
            // console.log(json);
        },
        onTestSend: function(email, json, html) {
            console.log("onTestSend");

            sendTestMail(email, html);
        },
    }
};

TopolPlugin.init(TOPOL_OPTIONS);


function initiateDatePicker() {
    /*--FOR DATE----*/
    var date = new Date();
    var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    $('#datepicker').datepicker({
        // format: 'dd-mm-yyyy',
        format: 'yyyy-mm-dd',
        container:'#main-modal',
        todayHighlight: true,
        startDate: today,
        endDate:0
    });
}

function sendTestMail(email, html) {

    var siteUrl = $('#hfBaseUrl').val();
    showPreloader();

    var dataItems = [];
    var data;


    data = {
        send: 'test-email',
        email: email,
        template_preview: html,
    };

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

        var json = $.parseJSON(result);
        var statusCode = json.status_code;
        var statusMessage = json.status_message;
        var data = json.data;

        if(statusCode == 200)
        {
            // hidePreloader();
                swal({
                title: "Success!",
                text: statusMessage,
                type: 'success'
                }, function () {
                });
        }
        else
        {
            swal({
                title: "Error!",
                text: statusMessage,
                type: 'error'
            }, function () {
            });
        }
    });

    // console.log("After call " + saveTemplateCall);
}

function saveTemplate(json, html, templateId) {
    console.log("save");

    // return true;
    var siteUrl = $('#hfBaseUrl').val();
    // Implement your own close callback
    // Data variable contains the response data of the save request

    saveTemplateCall = 'progress';
    // console.log("before ajax " + saveTemplateCall);
    //
    // console.log("window.actionStatus " + actionStatus);
    var dataItems = [];
    var data;
    var replyEmail;

    var title = $("#title").val();
    var industry = $("#industry").val();
    var niche = $("#niche").val();

    var category = $("#category").val();
    var type = $("#type").val();

    // var plan = $("#plan").val();
    var subject = $("#subject").val();
    var logo;

    if(window.attachedLogoArray.length !=0){
        logo = window.attachedLogoArray;
        // formData.append('logo', logo);
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


    // formData.append('send', 'admin-save-template');
    // formData.append('id', templateId);
    // formData.append('response', json);
    // formData.append('template_preview', html);
    // formData.append('title', title);
    // formData.append('niche', niche);
    // formData.append('industry', industry);
    // formData.append('plan:', plan);
    // formData.append('subject', subject);

    data = {
        send: 'admin-save-template',
        id: templateId,
        template_type_link: type,
        category: category,
        title: title,
        industry: industry,
        niche: niche,
        plan: plan,
        subject: subject,
        response: json,
        template_preview: html
    };

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

        if(data.id && data.id !== '')
        {
            var template = data.id;
            var response = data.response;

            // console.log("template > " + template + " ) > aa > " + templateId);

            if(template !== templateId)
            {
                var uri = window.location.toString();
                // console.log("uri");
                // console.log(uri);
                var clean_uri = uri.substring(0, uri.indexOf("templates"));
                // console.log("clean");
                // console.log(clean_uri);
                clean_uri = clean_uri + 'templates/email-template/'+template;
                // console.log("modified Clean URL");
                // console.log(clean_uri);
                window.history.replaceState({}, document.title, clean_uri);

                window.templateId = template;
            }
        }

        // console.log("tem window.actionStatus " + actionStatus);

        if(statusCode === 200)
        {
            if((logo && logo.length > 0) && (window.templateId && window.templateId !== ''))
            {
                var formData = new FormData();

                formData.append('send', 'admin-save-template');
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


            // hidePreloader();
                swal({
                title: "Success!",
                text: "Template saved.",
                type: 'success'
                }, function () {
                });
        }
        else
        {
            // hidePreloader();

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

    // console.log("After call " + saveTemplateCall);
}

function getTemplate(templateId) {
    $(".steps-nav").show();
    $(".input-form").show();
    $(".loading-bar").hide();
    $(".action-center").show();

    if(templateId && templateId !== '')
    {
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
                send: 'admin-get-template',
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
                var subject = data.subject;
                var industry = data.industry;
                var thumbnail = data.thumbnail;
                var industryNiche = data.niche;
                var plan = data.template_plans;

                var category = data.category;
                var type = data.template_type_link;

                $("#subject").val(subject);
                $("#title").val(title);

                if(category && category !== '')
                {
                    $("#category").val(category);
                    $("#category").change();
                }

                if(type && type !== '')
                {
                    $("#type").val(type);
                    $("#type").change();
                }

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

                // $("#from").val(title);
            }

            var response = data.response;

            TopolPlugin.load(response);
        });
    }
    else
    {
        // console.log("yes");
        TopolPlugin.load(
            JSON.stringify(
                {
                    "tagName": "mj-global-style",
                    "attributes": {
                        "h1:color": "#000",
                        "h1:font-family": "Helvetica, sans-serif",
                        "h2:color": "#000",
                        "h2:font-family": "Ubuntu, Helvetica, Arial, sans-serif",
                        "h3:color": "#000",
                        "h3:font-family": "Ubuntu, Helvetica, Arial, sans-serif",
                        ":color": "#000",
                        ":font-family": "Ubuntu, Helvetica, Arial, sans-serif",
                        ":line-height": "1.5",
                        "a:color": "#24bfbc",
                        "button:background-color": "#e85034",
                        "containerWidth": 600,
                        "fonts": "Helvetica,sans-serif,Ubuntu,Arial",
                        "mj-text": {
                            "line-height": 1.5,
                            "font-size": 15
                        },
                        "mj-button": []
                    },
                    "children": [
                        {
                            "tagName": "mj-container",
                            "attributes": {
                                "background-color": "#FFFFFF",
                                "containerWidth": 600
                            },
                            "children": [
                                {
                                    "tagName": "mj-section",
                                    "attributes": {
                                        "full-width": false,
                                        "padding": "9px 0px 9px 0px"
                                    },
                                    "children": [
                                        {
                                            "tagName": "mj-column",
                                            "attributes": {
                                                "width": "100%"
                                            },
                                            "children": [],
                                            "uid": "HJQ8ytZzW"
                                        }
                                    ],
                                    "layout": 1,
                                    "backgroundColor": null,
                                    "backgroundImage": null,
                                    "paddingTop": 0,
                                    "paddingBottom": 0,
                                    "paddingLeft": 0,
                                    "paddingRight": 0,
                                    "uid": "Byggju-zb"
                                }
                            ]
                        }
                    ],
                    "style": {
                        "h1": {
                            "font-family": "\"Cabin\", sans-serif"
                        }
                    },
                    "fonts": [
                        "\"Cabin\", sans-serif"
                    ]
                }
            )
        );
    }
}

function actionButtonStatus(accessType) {

    if(accessType && accessType === 'revoke')
    {
        $(".publish-footer .btn, .save-action").attr("disabled", true);
    }
    else
    {
        $(".publish-footer .btn, .save-action").attr("disabled", false);
    }
}

window.attachedLogoArray = [];
window.attachedAvatarArray=[];
window.attachedVideosArray=[];

window.attachedDeletedArray=[];
window.attachedLogoDeletedArray=[];

function createTextLinks(text) {
    return (text || "").replace(
        /([^\S]|^)(((https?\:\/\/)|(www\.))(\S+)|([a-zA-Z0-9]+\.[^\s]{2,}))/gi,
        function(match, space, url){
            var hyperlink = url;
            // console.log(hyperlink);
            if (!hyperlink.match('^https?:\/\/')) {
                hyperlink = 'http://' + hyperlink;
            }
            return space + '<a target="_blank" href="' + hyperlink + '">' + url + '</a>';
        }
    );
}

function onImageLoadError(img){
    $(img).closest('.link_preview_image_container').hide();
}

var errorFound = false;
$(".mad-validate input:text(#facebook-url, #instagram-url. #twitter-url), textarea").on('blur keyup', function()
{

    var ID = $(this).attr('id');

    // console.log("id " + ID);

    if(ID)
    {
        var name = $(this).attr('name');
        var currentFieldValue = $(this).val();
        var type = ID.replace("-url", "");
        var totalLimit = '';

        currentFieldValue = currentFieldValue.replace(/\s+/g, '');

        // console.log("blur > " + ID + " > " + name + " > (" + currentFieldValue + ") > " + type);

        if(ID === 'headline' || ID === 'description')
        {
            totalLimit = parseInt($(this).attr("data-limit"));

            currentFieldValue = $.trim($(this).val());
            var characterLength = currentFieldValue.length;

            var counter = $(this).closest(".form-group").find('.counter');

            // console.log("counter " + counter);
            // console.log("totalLimit " + totalLimit);
            // console.log("characterLength " + characterLength);

            var remainingCharCount = totalLimit - characterLength;
            // console.log("remainingCharCount " + remainingCharCount);

            if(remainingCharCount < 0)
            {
                counter.css('color', 'red');
                counter.html(remainingCharCount);
                $(this).parent().find('span small').html('Character limit exceeded');
                errorFound = true;
            }
            else
            {
                counter.css('color', 'green');
                counter.html(remainingCharCount);
                $(this).parent().find('span small').html('');
                errorFound = false;
            }
        }
        else if(currentFieldValue !== '')
        {
            // console.log("in");
            var domainPattern = new RegExp(/^(https?:\/\/)?((?:[a-z0-9-]+\.)+(?:com|net|biz|info|nyc|org|co|[a-zA-Z]{2}))(?:\/|$)/i);

            if(!currentFieldValue.match(domainPattern)) {
                $(this).parent().find('span small').html('Invalid URL');
                errorFound = true;
            } else{

                // console.log("type " + type);
                // console.log("checl "+ currentFieldValue.indexOf(type+".com"));

                if(currentFieldValue.indexOf(type+".com") === -1)
                {
                    $(this).parent().find('span small').html('Please give '+type+' URL here.');
                    errorFound = true;
                }
                else
                {
                    $(this).parent().find('span small').html('');
                    errorFound = false;
                }
            }
        }
        else
        {
            errorFound = false;
        }

        if(errorFound) {
            $(this).parent().find('span').removeClass('hide-me');
            $(this).parent().find('span').addClass('has-error error');
        }
        else
        {
            $(this).parent().removeClass('has-error error');
            $(this).parent().find('span').removeClass('has-error error');
            $(this).parent().find('span').addClass('hide-me');
        }
    }
});

$(document.body).on('submit', 'form.validate-image', function(e)
{
    // console.log("submit image");
    e.preventDefault();

    // var avatar = [];
    var avatar = [];
    var logo = [];

    if(window.attachedAvatarArray.length !=0){
        avatar = window.attachedAvatarArray;
    }

    if(window.attachedLogoArray.length !=0){
        logo = window.attachedLogoArray;
        // formData.append('logo', logo);
    }

    var formData = new FormData();

    var baseUrl = $('#hfBaseUrl').val();

    // console.log("avatar");
    // console.log(avatar);

    if(avatar.length > 0)
    {
        $(".logo-container .attached_images_container div").append('<div class="dashboard-card-loader"></div><div class="cover-loader-img"> <img src="'+baseUrl+'/public/images/spinner.gif" /> </div>');
    }

    if(logo.length > 0)
    {
        $(".logo-image-container .attached_images_container").append('<div class="dashboard-card-loader"></div><div class="cover-loader-img"> <img src="'+baseUrl+'/public/images/spinner.gif" /> </div>');
    }

    $.each(avatar, function(i, obj) {
        formData.append('attach_avatar['+i+']' , obj);
    });

    // console.log("logo");
    // console.log(logo);
    if(logo.length > 0) {
        // console.log("logo in");
        $.each(logo, function (i, obj) {
            formData.append('attach_logo[' + i + ']', obj);
        });
    }

    formData.append('send', 'update-business-profile');

    var targetButton = $(".btn-save", $(this));
    var $this = showLoaderButton(targetButton, "Saving");

    var url= baseUrl + "/done-me";


    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        contentType: false,
        cache: false,
        processData: false,
        data: formData,
        url:  url
    }).done(function (result) {
        // parse data into json
        var json = $.parseJSON(result);

        // get data
        var statusCode = json.status_code;
        var statusMessage = json.status_message;
        var data = json.data;

        resetLoaderButton($this);

        // console.log("hidden ");
        // console.log(window.attachedAvatarArray);
        // console.log(avatar);

        // console.log("logo ");
        // console.log(logo);

        if(window.attachedAvatarArray.length != 0)
        {
            // console.log("hidden avaatr");
            $(".logo-container .attached_images_container .dashboard-card-loader, .logo-container .attached_images_container .cover-loader-img").remove();
        }

        if(logo.length > 0)
        {
            console.log("hidden logo");
            $(".logo-image-container .attached_images_container .dashboard-card-loader, .logo-image-container .attached_images_container .cover-loader-img").remove();
        }


        if( statusCode == 200 ) {
            if(window.attachedAvatarArray.length != 0)
            {
                window.attachedAvatarArray = [];
            }

            if(logo.length > 0)
            {
                window.attachedLogoArray = [];
            }
            swal({
                title: "Successful!",
                text: "Your requested Image updated.",
                type: "success"
            }, function () {});

            // worked for empty array because it has length=0 variable
            if(data.length > 0)
            {
                // console.log("if");
                // console.log(data);
                if(data[0] && data[0].avatar !== '')
                {
                    var baseUrl = $("#hfBaseUrl").val();
                    var avatar = data[0].avatar;


                    // console.log("inner first if");
                    // console.log(data);
                    $(".avatar-icon").html('<img class="has-avatar" src="'+baseUrl+'/storage/app/'+avatar+'">');
                }

                // if(data[1] && data[1].avatar)
                // {
                //     console.log("inner if");
                //     console.log(data);
                // }
            }
        }
        else
        {
            swal("", statusMessage, "error");
        }
    });

});

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


// {
//     "tagName"
// :
//     "mj-global-style", "children"
// :
//     [{
//         "tagName": "mj-body", "attributes": {"background-color": "#f0f0f0", "containerWidth": "600"}, "children": [{
//             "tagName": "mj-section",
//             "attributes": {
//                 "locked": "true",
//                 "full-width": "full-width",
//                 "containerWidth": "600",
//                 "background-color": "#f0f0f0",
//                 "padding": "3px 0px 3px 0px"
//             },
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "66.66666666666666%", "vertical-align": "middle", "containerWidth": "400"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "locked": "true",
//                         "editable": "true",
//                         "padding-bottom": "0",
//                         "padding-top": "0",
//                         "containerWidth": "396",
//                         "color": "#7a7a7a",
//                         "font-family": "Cabin, sans-serif",
//                         "padding": "0px 0px 0px 0px"
//                     },
//                     "content": "<p><span style=\"font-size: 11px;\">Preheader<\/span><\/p>",
//                     "uid": "BJZTmSlqh7"
//                 }],
//                 "uid": "BJxa7Hl9hQ"
//             }, {
//                 "tagName": "mj-column",
//                 "attributes": {"width": "33.33333333333333%", "vertical-align": "middle", "containerWidth": "134"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "right",
//                         "font-size": "11px",
//                         "locked": "true",
//                         "editable": "false",
//                         "padding-bottom": "0",
//                         "padding-top": "0",
//                         "containerWidth": "198",
//                         "font-family": "Cabin, sans-serif",
//                         "padding": "0px 0px 0px 0px",
//                         "color": "#511423"
//                     },
//                     "content": "<p><span style=\"color: rgb(81, 20, 35);\"><a href=\"*|WEBVERSION|*\" style=\"color: #511423;\">Web version<\/a><\/span><\/p>",
//                     "uid": "rk76mHgchm"
//                 }],
//                 "uid": "r1fTQBlc3m"
//             }],
//             "uid": "r1pQrlc2Q"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {"padding": "17px 0px 17px 0px", "background-color": "#FFFFFF", "containerWidth": "600"},
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "60%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-image",
//                     "attributes": {
//                         "src": "https:\/\/storage.googleapis.com\/topolio14345\/plugin-assets\/6320\/14345\/doctor-logo%20copy.jpg",
//                         "padding": "0px 0px 0px 0px",
//                         "alt": null,
//                         "href": null,
//                         "containerWidth": "360",
//                         "width": "252",
//                         "widthPercent": "70"
//                     },
//                     "uid": "UytJ11UX7"
//                 }],
//                 "uid": "bX0oJ0hG_"
//             }, {
//                 "tagName": "mj-column",
//                 "attributes": {"width": "40%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "right",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.5",
//                         "containerWidth": "240"
//                     },
//                     "uid": "78AJdrFGH",
//                     "content": "<p><span style=\"font-size: 18px;\">CALL: 456-456-7894<\/span><\/p>"
//                 }],
//                 "uid": "xxwjfAITBM"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "aLLCAZDcC"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "9px 0px 9px 0px",
//                 "background-color": "#e0c5b6",
//                 "background-url": "https:\/\/storage.googleapis.com\/topolio14345\/plugin-assets\/6320\/14345\/cov.jpg",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "40%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-spacer",
//                     "attributes": {"height": "50px", "containerWidth": "240"},
//                     "uid": "Hp5EvxFas"
//                 }],
//                 "uid": "f0QZ9Ozhn"
//             }, {
//                 "tagName": "mj-column",
//                 "attributes": {"width": "60%", "padding": "2px 2px 2px 2px", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-spacer",
//                     "attributes": {"height": "15px", "containerWidth": "360"},
//                     "uid": "9t5m5qUk6"
//                 }, {
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "31px 31px 31px 31px",
//                         "line-height": "1.5",
//                         "color": "#181414",
//                         "containerWidth": "360"
//                     },
//                     "uid": "ll--zhFYG",
//                     "content": "<p>&nbsp;<\/p>\n<p><span style=\"font-size: 24px; background-color: #7e8c8d; color: #ffffff;\">I'm Dr. Ted Jones<\/span><\/p>\n<p><span style=\"font-size: 24px; background-color: #7e8c8d; color: #ffffff;\">I enpower people to Look Good, Feel Good, adn Love their smail!<\/span><\/p>\n<p>&nbsp;<\/p>\n<p>&nbsp;<\/p>\n<p>&nbsp;<\/p>"
//                 }],
//                 "uid": "6tzCwnD2Wg"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "Sh-t4K46F"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {"padding": "9px 0px 9px 0px", "background-color": "#FFF", "containerWidth": "600"},
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "border": "0px #000000 solid", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.5",
//                         "containerWidth": "600"
//                     },
//                     "uid": "WKg1mUMTpU",
//                     "content": "<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">WELCOME TO MY NEWSLETTER<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">My Practice is %%Doctor_Practice%%<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">%%Doctor_Name%%<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">my last name is<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">%%Doctor_Last_Name%%<\/span><\/p>"
//                 }],
//                 "uid": "HdGZHqZAlS"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "UfwZ799eK"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {"padding": "9px 0px 9px 0px", "background-color": "#FFFFFF", "containerWidth": "600"},
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 2px 15px",
//                         "line-height": "1.8",
//                         "containerWidth": "600"
//                     },
//                     "uid": "1y-m5CG-un",
//                     "content": "<p><span style=\"font-size: 14px;\">I realized that many of our patients have exhausted their efforts in trying to find an affordable solution for their tooth pain and cosmetic problems. &nbsp;We usually see patients who are at the &ldquo;end of their ropes&rdquo; and take extreme pride in being able to solve their issues.&nbsp;<\/span><\/p>"
//                 }],
//                 "uid": "Ij10vPtjJV"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "sR4vSqK-u"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {"padding": "9px 0px 9px 0px", "background-color": "#FFFFFF", "containerWidth": "600"},
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {
//                     "width": "50%",
//                     "background-color": "#EFECEC",
//                     "padding": "1px 1px 1px 4px",
//                     "border": "1px #000000 solid",
//                     "vertical-align": "top"
//                 },
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.5",
//                         "containerWidth": "300"
//                     },
//                     "uid": "VuZf5_W9h",
//                     "content": "<p style=\"text-align: center;\"><span style=\"font-size: 18px;\">----ONE WEEK ONLY ---<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 36px;\">TAKE 10% OFF<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 36px;\">YOUR VISIT<\/span><\/p>\n<p style=\"text-align: center;\"><span style=\"font-size: 24px;\">_________________<\/span><\/p>"
//                 }, {
//                     "tagName": "mj-button",
//                     "attributes": {
//                         "align": "center",
//                         "background-color": "#e85034",
//                         "color": "#fff",
//                         "border-radius": "24px",
//                         "font-size": "13px",
//                         "padding": "20px 20px 20px 20px",
//                         "inner-padding": "9px 26px 9px 26px",
//                         "href": "https:\/\/google.com",
//                         "font-family": "Ubuntu, Helvetica, Arial, sans-serif, Helvetica, Arial, sans-serif",
//                         "containerWidth": "300",
//                         "border": "0px solid #000"
//                     },
//                     "content": "<div>CALL TO SCHEDULE<\/div>",
//                     "uid": "nBk4O-zb7"
//                 }],
//                 "uid": "FCFs-A3LUp"
//             }, {
//                 "tagName": "mj-column",
//                 "attributes": {"width": "50%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.5",
//                         "containerWidth": "300"
//                     },
//                     "uid": "HjprzFMoU",
//                     "content": "<p><span style=\"font-size: 14px;\">We don&rsquo;t want to be &ldquo;another dentist&rdquo; or &ldquo;another office&rdquo; that you go to. We want to be the practice that treats your concerns with a high degree of success.&nbsp;<\/span><\/p>\n<p>&nbsp;<\/p>\n<p><span style=\"font-size: 14px;\">As a show of appreciation for your loyalty and confidence in our team, please take 10% off on your next dental procedure. Simply print this email and present it to us at your office visit.&nbsp;<\/span><\/p>"
//                 }],
//                 "uid": "mH4kbdcCo8"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "bnqkm4SyJ"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {"padding": "9px 0px 9px 0px", "background-color": "#FFFFFF", "containerWidth": "600"},
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 2px 15px",
//                         "line-height": "1.8",
//                         "containerWidth": "600"
//                     },
//                     "uid": "SDOlX6R_XD",
//                     "content": "<p><span style=\"font-size: 14px;\">&nbsp;Also, twice a month, I'll share email information on the latest dental procedures, special promotions and personalized content just for your needs. If you have any questions or need our help to improve your dental health, please don&rsquo;t hesitate to reach out to me personally or to my staff.<\/span><\/p>"
//                 }],
//                 "uid": "Ij10vPtjJV"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "q0WCfjQgw"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "9px 0px 9px 0px",
//                 "background-color": "#FFFFFF",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-social",
//                     "attributes": {
//                         "padding": "10px 10px 10px 10px",
//                         "text-mode": "false",
//                         "icon-size": "35px",
//                         "align": "center",
//                         "containerWidth": "600"
//                     },
//                     "children": [{
//                         "tagName": "mj-social-element",
//                         "attributes": {
//                             "src": "https:\/\/s3-eu-west-1.amazonaws.com\/ecomail-assets\/editor\/social-icos\/rounded\/facebook.png",
//                             "name": "Facebook",
//                             "href": "https:\/\/www.facebook.com\/PROFILE",
//                             "background-color": "transparent"
//                         }
//                     }, {
//                         "tagName": "mj-social-element",
//                         "attributes": {
//                             "src": "https:\/\/s3-eu-west-1.amazonaws.com\/ecomail-assets\/editor\/social-icos\/rounded\/twitter.png",
//                             "name": "Twitter",
//                             "href": "https:\/\/www.twitter.com\/PROFILE",
//                             "background-color": "transparent"
//                         }
//                     }, {
//                         "tagName": "mj-social-element",
//                         "attributes": {
//                             "src": "https:\/\/s3-eu-west-1.amazonaws.com\/ecomail-assets\/editor\/social-icos\/rounded\/linkedin.png",
//                             "name": "LinkedIn",
//                             "href": "https:\/\/www.linkedin.com\/PROFILE",
//                             "background-color": "transparent"
//                         }
//                     }],
//                     "uid": "IJXEJqk6b",
//                     "style": "rounded"
//                 }],
//                 "uid": "D2Zy88lpW9"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "N1hqL6fZV"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "0px 0px 0px 0px",
//                 "background-color": "#FFFFFF",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "center",
//                         "font-size": "11px",
//                         "padding": "0px 35px 10px 35px",
//                         "color": "#511423",
//                         "containerWidth": "600"
//                     },
//                     "uid": "CguxnAL6VB",
//                     "content": "<p><span style=\"font-size: 12px; color: #34495e;\">You Received this email as a registered subscriber of Dr Jones<\/span><\/p>"
//                 }],
//                 "uid": "S1W4Vg4gQ"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "BvGk5Bmv2"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "0px 0px 0px 0px",
//                 "background-color": "#FFFFFF",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "center",
//                         "font-size": "11px",
//                         "padding": "0px 35px 10px 35px",
//                         "color": "#511423",
//                         "containerWidth": "600"
//                     },
//                     "uid": "SkQnfvW52m",
//                     "content": "<p><span style=\"font-size: 12px;\"><a style=\"color: #511423;\" href=\"*|UNSUB|*\"><span style=\"color: #660000;\">Unsubscribe<\/span><\/a><\/span><\/p>"
//                 }],
//                 "uid": "S1W4Vg4gQ"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "S1VhzDZqhX"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "9px 0px 9px 0px",
//                 "background-color": "#f1f1f1",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-spacer",
//                     "attributes": {"height": "25px", "containerWidth": "600"},
//                     "uid": "z5P3tBvFx"
//                 }, {
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.5",
//                         "containerWidth": "600"
//                     },
//                     "uid": "bqU0v-6-6",
//                     "content": "<p style=\"text-align: center;\">Restrictions apply. Cannot be combined with other offers. Call for details. Expires in 30 days.<\/p>"
//                 }],
//                 "uid": "vW4OdJANKn"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "54ZMc4LAy"
//         }, {
//             "tagName": "mj-section",
//             "attributes": {
//                 "full-width": "false",
//                 "padding": "9px 0px 9px 0px",
//                 "background-color": "#7b2e00",
//                 "containerWidth": "600"
//             },
//             "type": null,
//             "children": [{
//                 "tagName": "mj-column",
//                 "attributes": {"width": "100%", "vertical-align": "top"},
//                 "children": [{
//                     "tagName": "mj-text",
//                     "attributes": {
//                         "align": "left",
//                         "font-size": "11px",
//                         "padding": "15px 15px 15px 15px",
//                         "line-height": "1.8",
//                         "containerWidth": "600"
//                     },
//                     "uid": "WPlfRplrA",
//                     "content": "<p style=\"text-align: center;\"><span style=\"color: #ffffff;\">%%Dcompany%%<\/span><br \/><span style=\"color: #ffffff;\">%%Doctor_Address%% | %%Doctor_City%% | %%Doctor_State%% | %%Doctor_Zip%%<\/span><br \/><span style=\"color: #ffffff;\">Ph. %%Doctor_Phone%% | Visit Our Website | Email Us | Unsubscribe | %%Doctor_practice%%<\/span><\/p>"
//                 }],
//                 "uid": "t2tNzwKszS"
//             }],
//             "layout": "1",
//             "backgroundColor": null,
//             "backgroundImage": null,
//             "paddingTop": "0",
//             "paddingBottom": "0",
//             "paddingLeft": "0",
//             "paddingRight": "0",
//             "uid": "Yg2D7KYpJ"
//         }]
//     }], "style"
// :
//     {
//         "h1"
//     :
//         {
//             "font-family"
//         :
//             "PT Serif, Georgia, serif"
//         }
//     ,
//         "p"
//     :
//         {
//             "font-family"
//         :
//             "Cabin, sans-serif"
//         }
//     ,
//         "h2"
//     :
//         {
//             "font-family"
//         :
//             "PT Serif, Georgia, serif"
//         }
//     ,
//         "h3"
//     :
//         {
//             "font-family"
//         :
//             "Cabin, sans-serif"
//         }
//     }
// ,
//     "attributes"
// :
//     {
//         "mj-text"
//     :
//         {
//             "line-height"
//         :
//             "1.5", "font-family"
//         :
//             "Cabin, sans-serif"
//         }
//     ,
//         "mj-button"
//     :
//         {
//             "font-family"
//         :
//             "Cabin, sans-serif"
//         }
//     ,
//         "containerWidth"
//     :
//         "600"
//     }
// ,
//     "fonts"
// :
//     ["Cabin, sans-serif", "PT Serif, Georgia, serif"]
// }
