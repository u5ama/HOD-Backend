window.onload = function() {
    setTimeout(function () {
        runSocialAction();

        if (typeof runAuthAction !== 'undefined' && $.isFunction(runAuthAction)) {
            runAuthAction();
        }

    }, 1000);
};

$(document.body).on('click', '.connect-app' ,function() {
    // $('.remove-business-body, .social-module, .local-module, .select-business-body').hide();

    
    // $(".")
    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
    // $(".welcome-process", mainModel).remove();
    $(mainModel).removeClass('welcome-process');
    $(mainModel).addClass('connect-app-interface');

    var actionType = $(this).attr('data-type').toLowerCase();
    var originalAction =  $(this).attr('data-type');

    var html = '';

    if(actionType === 'facebook' || actionType === 'twitter')
    {
        html += '<div class="modal-body">';

        html += '<div class="social-module" style="">';

        html += '<div class="social-modal-content">';

        html += '<h3 class="">Authorize Trustyy on '+originalAction+'</h3>';

        html += '<div class="social-list">';
        html += '<label>By connecting your account, you will be able to:</label>';
        html += '<ul>';
        html += '<li>Monitor activity and manage reviews.</li>';

        if(actionType === 'facebook')
        {
            html += '<li>Get More reviews from your customers</li>';
        }
        html += '</ul>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
        html += '<input type="hidden" id="actionRequest" value="" />';

        html += '</div>';

        html += '<div class="modal-footer">';
        html += '<button type="button" class="btn btn-dismiss" data-dismiss="modal">Cancel</button>';

        if(actionType === 'facebook') {
            html += '<button type="button" id="loginSocial" class="btn facebook-widget-btn"> Connect ' + originalAction + '</button>';
        }
        else
        {
            html += '<button type="button" class="btn twitter-widget-btn connect-in"> Connect ' + originalAction + '</button>';
        }

        // html += '<input type="hidden" id="actionApiUrl" value="https://dev-api.netblaze.com/public/">';
        html += '</div>';

    }
    else
    {
        html += '<form class="validate-me" accept-charset="UTF-8">';

        html += '<div class="modal-body">';

        html += '<div class="interface-module" style="">';

        html += '<div class="alert" style="display: none;"></div>';

        html += '<div class="remove-business-modal">';
        html += '<h3 class="action-title"></h3>';
        html += '<div class="user-guidance-message">Enter Your Profile url to connect your app. By Connect your app here you can manage your reviews from here.</div>';

        html += '<div class="form-group">';
        html += '<input id="website" name="website" type="text" class="form-control input-lg" data-required="true" data-message="Invalid URL">';
        html += '<span class="help-block"><small style="float: left;"></small></span>';
        html += '</div>';

        html += '</div>';


        html += '<input type="hidden" id="global-error-message" data-global-error-message="URL is required">';
        html += '<input type="hidden" id="actionRequest" value="" />';

        html += '</div>';
        html += '</div>';

        html += '<div class="modal-footer">';
        html += '<button type="button" class="btn btn-dismiss" data-dismiss="modal">Cancel</button>';
        html += '<button type="submit" class="btn btn-primary connect-me">Connect</button>';
        html += '</div>';

        html += '</form>';
    }



    // var modelAlert = $('.modal-body .alert');
    // modelAlert.hide();
    // modelAlert.html('');

    mainModel.modal('show');
    $(".modal-header").after(html);

    var actionTitle = $('.action-title');

    actionType = actionType.replace(" ", "");
    var actionTypeTitle = $(this).attr('data-type');

    if(actionType === 'googleplaces')
    {
        actionTypeTitle = 'Google';
    }


    actionTitle.html('Connect '+ actionTypeTitle);
    $('#actionRequest').val(actionType);


    // if(actionType == 'facebook')
    // {
    //     $('.social-module').show();
    // }
    // else
    // {
    //     $('.local-module').show();
    //
    //     $('form.validate-me')[0].reset();
    //
    //     // news alert for the popup if any noted happened from backend
    //     // like this service not currently available.
    //     $('.news-alert').remove();
    //
    //     actionTitle.html('Connect your '+actionTypeTitle+' Page');
    //
    //     var guidanceMessage = 'If an incorrect listing was discovered during our scan, You can correct <br> it by adding your page URL below.';
    //
    //     if(actionType === 'googleplaces') {
    //         var supportLink=$('#googleManualConnectSupportLink').val();
    //         guidanceMessage += ' <a href="'+supportLink+'" target="_blank">Click here</a> to learn how to get your page URL.';
    //     }
    //
    //     $('.user-guidance-message').html(guidanceMessage);
    //
    //     $(".validate-me .form-group").removeClass('has-error');
    //     $(".validate-me .form-group small").html('');
    // }

    // $('#connectBusinessModal').modal('show');
});

$('#main-modal').on('hidden.bs.modal', function () {
    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
});


$(document.body).on('click', '.unlink-app', function() {
    // $('.remove-business-body, .social-module, .local-module, .select-business-body').hide();

    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
    // $(".welcome-process", mainModel).remove();
    $(mainModel).removeClass('welcome-process');
    $(mainModel).addClass('connect-app-interface');


    // hide the previous alert
    var modelAlert = $('.modal-body .alert');
    modelAlert.hide();
    modelAlert.html('');

    // $('.remove-business-body').show();

    var type = $(this).attr('data-type');
    var baseUrl = $('#hfBaseUrl').val();


    var actionType = $(this).attr('data-type').toLowerCase();
    var actionTitle = $('.action-title');

    actionType = actionType.replace(" ", "");
    var actionTypeTitle = $(this).attr('data-type');
    console.log("actionTypeTitle " + actionType);
    var changedHeading = (type === 'Google Places') ? 'Google' : type;

    // hide the previous alert
    var html = '';

    html += '<div class="modal-body">';

    html += '<div class="interface-module" style="">';

    html += '<div class="alert" style="display: none;"></div>';

    html += '<div class="remove-business-modal">';
    html += '<div class="remove-action-note"><img src="'+baseUrl+'/public/images/delete-listing.png" /> <h3 style="font-size: 28px;margin-bottom: 25px;font-weight: 300;color: #000;">Are you sure you want to remove <br /> '+ changedHeading +'?</h3></div>';
    html += '</div>';

    html += '<input type="hidden" id="actionRequest" value="'+type+'" />';

    html += '</div>';

    html += '</div>';

    html += '<div class="modal-footer">';
    html += '<button type="button" class="btn btn-dismiss" data-dismiss="modal">Cancel</button>';
    html += '<button type="submit" class="btn btn-danger deleting-processed">Delete</button>';
    html += '</div>';

    // var actionTitle = $('.remove-business-modal');

    // actionTitle.prepend('<div class="remove-action-note"><img src="'+baseUrl+'/public/images/delete-listing.png" /> <h3 style="font-size: 28px;margin-bottom: 25px;font-weight: 300;color: #000;">Are you sure you want to remove <br /> '+type+'?</h3></div>');


    // var modelAlert = $('.modal-body .alert');
    // modelAlert.hide();
    // modelAlert.html('');

    mainModel.modal('show');

    $(".modal-header").after(html);

    $('.deleting-processed').unbind().click(function()
    {
        var baseUrl = $('#hfBaseUrl').val();

        var type = $('#actionRequest').val();

        showPreloader();

        // var screenType = $("#screen-type").val();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/done-me",
            data: {
                send: 'unlink-app',
                type : type
            }
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);

            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;

            hidePreloader();
            var currentType = $('#actionRequest').val();
            // currentType = currentType.replace(" ", "");

            console.log("Before currentType " + currentType);

            if(currentType === 'Google Places')
            {
                console.log("in");
                currentType = 'Google';
            }

            type = type.toLowerCase();
            type = type.replace(" ", "");

            console.log("type " + type);
            console.log("currentType " + currentType);

            $('#main-modal').modal('hide');

            $('tr.' + type).addClass("fadeRow");

            if( statusCode == 200 ) {
                swal({
                    title: "Successful!",
                    text: currentType + ' App has been removed from your account.',
                    type: "success"
                }, function () {
                    showPreloader();
                    location.reload();
                    console.log("type " + type);
                    if(type === 'facebook' || type === 'twitter')
                    {
                        showPreloader();
                        location.reload();
                    }
                });

                $('.'+type+'-widget .btn-site-disconnect').remove();

                $('.'+type+'-widget label').after('<a href="javascript:void(0);" class="btn btn-primary btn-new-site connect-app" data-type="'+$('#actionRequest').val()+'">Add '+ currentType +'</a>');

            }
            else
            {
                swal("", statusMessage, "error");
            }
        });
    });
});

/**
 * submit form to manual connect business
 */
// $(document).on('submit', 'form.validate-me', function (e)
$(document.body).on('submit', 'form.validate-me', function(e)
{
    console.log("hi");
// $("form.validate-me").submit(function (e) {
    e.preventDefault();

    manualConnectBusiness();
});

/**
 * oauth process begin to login with social account.
 */
$(document.body).on('click', '#loginSocial', function() {
    // swal("", "working continue on facebook", "info");
    // return false;
    var baseUrl = $('#hfBaseUrl').val();
    var currentPage = $('#currentPage').val();

    var actionRequest = $('#actionRequest');
    var type = actionRequest.val();

    var business_id = $('#business_id').val();

    // var actionApiUrl = $('#actionApiUrl').val();
    var actionApiUrl = baseUrl+'/api/';

    var preloader = $('.preloader');
    preloader.show();
    preloader.addClass('preloader-opacity');


    if(type == 'facebook')
    {

        if(currentPage === 'posts' || currentPage === 'get_started' || currentPage === 'social_post_settings')
        {
            location.href = actionApiUrl+'social-media/login?referType='+currentPage+'&business_id='+business_id;
        }
        else
        {
            location.href = actionApiUrl+'social-media/login?business_id='+business_id;
        }
        // window.open('http://localhost/projects/madison-api/public/social-media/login?business_id='+business_id);
    }
});

$(document.body).on('hidden.bs.modal', '.facebook-connectmodal', function () {
    console.log("hidden > " + $('#actionRequest').val() + ' > ' + $('#accessToken').val());
    var modelId = $(".facebook-connectmodal");
    var modelBody = $(".modal-body", modelId);

    $(".div-separator .modal-title", modelId).remove();
    modelId.removeClass("social-model-dialog page-modal");
    $(".modal-dialog", modelId).removeClass("modal-lg modal-dialog-centered");


    var accessTokenSelector = $('#accessToken');
    var accessToken = accessTokenSelector.val();
    var actionType = accessTokenSelector.attr("data-type");

    if( actionType == 'facebook' && $('#accessToken').val() == 1)
    {
        var baseUrl = $('#hfBaseUrl').val();
        // clear the access token
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + '/business/remove-access-token'
        }).done(function (result) {});
    }
});

function runSocialAction()
{
    console.log("runSocialAction");
    var accessTokenSelector = $('#accessToken');
    var accessToken = accessTokenSelector.val();
    var actionType = accessTokenSelector.attr("data-type");

    console.log("runnng " + accessToken);
    console.log("actionType " + actionType);

    if(accessToken == 1 && actionType === 'facebook')
    {
        console.log("IN " + actionType);

        var currentPage = $('#currentPage').val();
        var baseUrl = $('#hfBaseUrl').val();
        var business_id = $('#business_id').val();

        // var actionType = 'facebook'accessTokenSelector.attr('data-type');
        $('#actionRequest').val(actionType);

        // var businessBody = $('.select-business-body');
        // $('.social-module, .local-module, .select-business-body').hide();

        // pop up show
        // $('#connectBusinessModal').modal('show');
        // $('#connectBusinessModal').addClass('page-modal');

        var mainModel = $('#main-modal');
        $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
        // $(".welcome-process", mainModel).remove();
        $(mainModel).removeClass('welcome-process');
        $(mainModel).addClass('facebook-connectmodal');

        mainModel.modal('show');


        var html = '';
        html += '<span class="div-separator"></span>';

        html += '<div class="modal-body">';
        html += '<div class="alert" style="display: none;"></div>';

        html += '<h3 class="processing-message">Retrieving Facebook Pages.</h3>';

        html += '</div>';

        html += '<span class="div-separator"></span>';

        html += '<div class="modal-footer">';
        html += '<button type="button" class="btn btn-dismiss btn-s-cancel" data-dismiss="modal">Cancel</button>';
        // html += '<input type="hidden" id="actionApiUrl" value="https://dev-api.netblaze.com/public/">';
        html += '</div>';


        $(".modal-header").after(html);

        $(".modal-header", mainModel).append('<h4 class="modal-title">Select Facebook Page</h4>');
        // business pages content area show
        // businessBody.show();

        // businessBody.prepend('<h3 class="processing-message">Retrieving Facebook Pages.</h3>');

        // preloader must show after one second.
        setTimeout(function () {
            showPreloader();
        }, 1000);

        var formData = false;
        if (window.FormData) formData = new FormData();

        formData.append('business_id', business_id);
        formData.append('type', actionType);
        formData.append('accessToken', accessToken);

        var screenType = $("#screen-type").val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/business/social-selection-pages",
            contentType: false,
            cache: false,
            processData: false,
            data: formData
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);

            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;
            var data = json.data;

            $('.processing-message').remove();

            var modelAlert = $('.modal-body .alert');
            var responseClass = (statusCode == 200) ? 'alert-success' : 'alert-danger';

            setTimeout(function () {
                hidePreloader();
            }, 1200);

            // console.log("ff " . type);
            if(statusCode == 200)
            {
                // modelAlert.show();
                // modelAlert.addClass(responseClass);
                // modelAlert.html(statusMessage);

                // $('.page-content').show();
                var html = '';
                var checkedBusiness;
                var checkedBusinessClass;

                console.log("main data");
                console.log(data.data);

                var type = 'facebook';

                $.each(data.data, function (index, value) {
                    checkedBusiness = (index === 0) ? 'checked' : '';
                    checkedBusinessClass = (index === 0) ? 'selected-page' : '';

                    console.log("data " + index);
                    console.log(data.data[index]);

                    pageLogo = value.logo;

                    pageLogo = (pageLogo != '') ? pageLogo : 'http://via.placeholder.com/75x75';
                    pageAddress = (value.address != '') ? value.address : '';
                    pagePhone = (value.phone != '') ? value.phone : '';

                    if(index === 0)
                    {
                        html += '<div class="alert" style="display: none;"></div>';
                        html += '<p class="panel-heading">Following pages found to be connected with your Facebook account.</p>';
                    }

                    html += '<div class="page-panel '+checkedBusinessClass+'">';

                    html += '<img class="media-object img-circle" src="'+pageLogo+'" />';

                    html += '<div class="page-content">';
                    html += '<h3>'+value.name+'</h3>';

                    html += '<p>' + ( ( value.page_likes_count !== '' ) ? value.page_likes_count : 0) + ' Likes</p>';

                    html += '<p>' + ( ( pagePhone !== '' ) ? pagePhone : 'No Phone Available') + '</p>';
                    html += '<p>' + (( pageAddress !== "" ) ? pageAddress : 'No Address Available') + '</p>';

                    html += '</div>';

                    html += '<div class="add-icon">';
                    html += '<a href="javascript:void(0);" class="add-me" data-page-id="'+value.id+'"><i class="fa fa-plus"></i></a>';
                    html += '</div>';

                    html += '</div>';
                });

                html += '<input type="hidden" id="actionRequest" value="'+actionType+'" />';
                $(".facebook-connectmodal .modal-body").html(html);


                setTimeout(function () {
                    modelAlert.hide();
                }, 3000);
            }
            else
            {
                $(mainModel).modal('hide');
                swal("", statusMessage, "error");
            }

            $('[data-toggle="tooltip"]').tooltip();
        });
    }
}

$(document.body).on('click', '.add-me' ,function() {
    errorFound = false;
    $(this).addClass('chosen-page');
    showPreloader();
    setTimeout(function () {
        manualConnectBusiness();
    }, 300);
});

function manualConnectBusiness()
{
    if (!errorFound) {
        var currentPage = $('#currentPage').val();
        var baseUrl = $('#hfBaseUrl').val();

        // $('.modal-footer').after('<img src="'+baseUrl+'/public/images/loader.gif" class="loader" />');
        // $('.modal-footer').hide();

        var type = $('#actionRequest').val();

        showPreloader();

        var formData = false;
        if (window.FormData) formData = new FormData();

        // formData.append('type', type);
        // formData.append('email', email);
        var actionToPost = '';
        var data;

        if (type === 'facebook') {
            var selectedBusiness = $('.chosen-page').attr('data-page-id');

            if (selectedBusiness) {
                actionToPost = 'manual-connect-business';
                // selectedBusiness = selectedBusiness;
                // formData.append('send', 'social_page_select');
                // actionToPost = '/send-reference-request';
                // which business user select
                // formData.append('selectedBusiness', selectedBusiness);

                // formData.append('accessToken', $('#accessToken').val());

                data = {
                    send: actionToPost,
                    type: type,
                    page_id: selectedBusiness,
                    accessToken: $('#accessToken').val(),
                    business_id: $('#business_id').val()
                };
            }
        }
        else {
            var url = $('#website').val();
            // formData.append('targetUrl', url);

            actionToPost = 'manual-connect-business';

            data = {
                send: actionToPost,
                type: type,
                targetUrl: url
            };
        }

        var screenType = $("#screen-type").val();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/done-me",
            data: data
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);

            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;

            hidePreloader();

            var currentType = $('#actionRequest').val();

            var modelAlert = $('.modal-body .alert');
            modelAlert.hide();
            modelAlert.html('');

            console.log("type " + type);
            console.log("currentType " + currentType);

            if (type === currentType) {
                console.log("statu co " + statusCode);
                var responseClass = (statusCode === 200) ? 'alert-success' : 'alert-danger';

                if(statusCode !== 200)
                {
                    modelAlert.show();
                    modelAlert.addClass(responseClass);
                    modelAlert.html(statusMessage);
                }
            }

            console.log("200");
            if (statusCode === 200) {
                $('#main-modal').modal('hide');

                var typeTitle = type.charAt(0).toUpperCase() + type.substr(1).toLowerCase();
                var originalTitle = 'Click to manually connect your page if a wrong page was discovered.';

                console.log("typeTitle " + typeTitle);

                if(type === 'facebook')
                {
                    originalTitle = 'Add Facebook';
                }

                if(type === 'googleplaces')
                {
                    typeTitle = 'Google Places';
                }

                $('.'+type+'-widget .connect-app').remove();

                // $('.'+type+'-widget label').after('<a href="javascript:void(0);" class="btn btn-primary btn-new-site connect-app" data-type="'+$('#actionRequest').val()+'">Add '+ currentType +'</a>');
                $('.'+type+'-widget label').after('<a href="javascript:void(0);" class="btn btn-primary btn-site-disconnect unlink-app" data-type="'+typeTitle+'"> Disconnect </a>');

                if(type === 'googleplaces')
                {
                    typeTitle = 'Google';
                }

                if(currentPage && currentPage === 'get_started')
                {
                    $(".btn-connect-social", '.'+type).html("Connected");
                    $(".btn-connect-social", '.'+type).addClass("btn-connected-social");
                    $(".btn-connected-social", '.'+type).removeClass("btn-connect-social, connect-app");
                    // $(".btn-connect-social").addClass("btn-connected-social);
                }

                var responseMessage = typeTitle + ' Successfully Connected.';

                swal({
                    title: "Successful!",
                    text: responseMessage,
                    type: "success"
                }, function () {
                    console.log("inside " + type);
                    showPreloader();
                    location.reload();
                    if(type === 'facebook')
                    {
                        showPreloader();
                        location.reload();
                    }

                    // setTimeout(function () {
                    //     $('tr.' + type).removeClass("fadeRow");
                    // }, 3000);
                });


            }
            else if (statusCode == 404) {
                if (type == 'facebook') {
                    console.log("facebook 404;")
                    // if(screenType === 'mobile')
                    // {
                    //     swal({
                    //         title: "",
                    //         text: statusMessage,
                    //         type: "error"
                    //     }, function () {
                    //         showPreloader();
                    //         setTimeout(function () {
                    //             location.reload();
                    //         }, 200);
                    //     });
                    // }
                    // else
                    // {
                    //     if ($('.' + type).find('.oauth-cell').length != 1) {
                    //         $('.name-cell, .website-cell, .address-cell, .action-cell', '.' + type).remove();
                    //         $('.issuesList', row).html('');
                    //
                    //         newTableCell += '<td class="name-cell"><div class="name"><div class="business-name-cell"><div class="business-error-state"><i class="fa fa-exclamation-circle"></i>Missing Listing</div></div></div></td>';
                    //
                    //         newTableCell += '<td class="website-cell oauth-cell">';
                    //
                    //         newTableCell += '<div class="social-media-btn">';
                    //         newTableCell += '<button type="button" class="btn btn-color connectBusinessBtn" data-type="facebook">';
                    //         newTableCell += 'Connect Facebook';
                    //         newTableCell += '</div>';
                    //
                    //         newTableCell += '</td>';
                    //
                    //
                    //         newTableCell += '<td class="empty-col"></td>';
                    //         newTableCell += '<td class="empty-col"></td>';
                    //
                    //         $("td:first-child", '.' + type).after(newTableCell);
                    //     }
                    //
                    //     $('tr.' + type).addClass("fadeRow");
                    //
                    //     issueHtml += '<div class="action-column">';
                    //     issueHtml += '<div class="alert-icon">';
                    //     issueHtml += '<i data-toggle="tooltip" class="tooltip-danger mdi mdi-alert alert-icon"  data-placement="top" title="" data-original-title="Not listed"></i>';
                    //     issueHtml += '</div>';
                    //     issueHtml += '</div>';
                    //
                    //     // $('.issuesList', row).html(issueHtml);
                    //     if($("td.name-cell", "tr.facebook").length == 0)
                    //     {
                    //         // $("td:first-child", row).after('<td class="name-cell"> <div class="name"> <div class="text-danger">Not Found</div></div></td>');
                    //     }
                    //     else
                    //     {
                    //         // $("td,name-cell", row).html('<div class="name"> <div class="text-danger">Not Found</div></div>');
                    //     }
                    //
                    //     $('#connectBusinessModal').modal('hide');
                    //
                    //     swal("", statusMessage, "error");
                    //
                    //     setTimeout(function () {
                    //         $('tr.' + type).removeClass("fadeRow");
                    //     }, 3000);
                    // }
                }
            }

            $('[data-toggle="tooltip"]').tooltip();
        });
    }
}

