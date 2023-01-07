$(function () {

    function thumbActionEventListener(){
        $(".thumb-action").off("click");
        $(".thumb-action").click(function() {
            var reviewSelection = $(this).attr("data-thumb-action");
            var baseUrl = $('#hfBaseUrl').val();
            $(".alert-danger").hide();

            if(reviewSelection === 'up')
            {
                $(".Interactive-box").hide();
                saveFeedback('up');
            }
            else
            {
                $(".review-content").html("We're sorry to hear that we were not able to meet your expectations. We would like to know more about what happened so we can take the necessary action to improve our products and services in the future.");

                $('.popup-back-btn').removeClass('hide-popup-back-btn');

                var html = "";

                html  = '<div class="form-group">';
                html  += '<textarea id="message" class="form-control" rows="10" style="resize: none;"></textarea>';
                html  += '<span class="error" style="display: none;">Error found.</span>';
                html  += '</div>';
                html  += '<div class="form-group text-center">';
                html  += '<button class="btn btn-info popbox-action-btn send-feedback">Send Feedback</button>';
                html  += '</div>';

                $(".Interactive-box").html(html);
            }
        });
    }
    thumbActionEventListener();

    $(document.body).on('click', '.popup-back-btn', function () {
        $(".review-content").html("Thumbs up if you were happy with our service. <br> Thumbs down if we didn’t meet your expectations.");
        $('.popup-back-btn').addClass('hide-popup-back-btn');

        var baseUrl = $('#hfBaseUrl').val();
        var html = "";
        html  = '<div class="row">'+
                    '<div class="col text-right">'+
                        '<a href="javascript:void(0)" class="thumb-action" data-thumb-action="up">'+
                        '<img src="'+baseUrl+'/public/images/feedback-review/like.png" alt="Like">'+
                        '</a>'+
                    '</div>'+
                    '<div class="col">'+
                        '<a href="javascript:void(0)" class="thumb-action" data-thumb-action="down">'+
                        '<img src="'+baseUrl+'/public/images/feedback-review/dislike.png" alt="Dislike">'+
                        '</a>'+
                    '</div>'+
                '</div>';
        $(".Interactive-box").html(html);
        thumbActionEventListener();
    });

});

$(document.body).on('click', '.send-feedback', function () {
    var message = $("#message").val();
    var errorContainer = $(".error");

    if(message !== '')
    {
        if(message.length < 50)
        {
            errorContainer.show();
            errorContainer.html("Please write more, Minimum 50 characters.");
        }
        else
        {
            errorContainer.hide();
            $(this).hide();
            saveFeedback('down');
        }
    }
    else
    {
        errorContainer.show();
        errorContainer.html("Please fill the content box to send feedback.");
    }
});

$(document.body).on('click', '.site-selection', function () {
    var site = $(this).attr('data-site');
    var email = $("#email").val();
    var secret = $("#secret").val();
    var name = $("#name").val();

    if (site == 'Tripadvisor') {
        site = 'TA';
    } else if (site == 'Google Places') {
        site = 'GP';
    } else if (site == 'Yelp') {
        site = 'YP';
    } else if (site == 'Facebook') {
        site = 'FB';
    }

    var baseUrl = $('#hfBaseUrl').val();
    var parent = $(this);

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url: baseUrl + "/done-me",
        data: {
            send: 'update-site',
            missToken: 'yes',
            site: site,
            email: email,
            secret: secret
        }
    }).done(function (result) {
        // parse data into json
        var json = $.parseJSON(result);

        // get data
        var statusCode = json.status_code;
        var statusMessage = json.status_message;

        if(statusCode == 200)
        {
            if($('.social-media-list li').length == 1)
            {
                location.href = baseUrl+'/business-review-complete/'+email+'/'+secret+'/'+name;
            }
            else
            {
                parent.attr("data-status", 'done');
            }
        }
    });
});


function saveFeedback(thumb)
{
    if(thumb !== '')
    {
        $(".loader").show();

        var formData = false;
        if (window.FormData) formData = new FormData();

        if(thumb === 'down')
        {
            formData.append('message', $("#message").val());
        }

        var email = $("#email").val();
        var secret = $("#secret").val();
        var name = $("#name").val();
        // var businessId = $('#bussinessId').val()

        formData.append('send', "save-feedback");
        formData.append('missToken', "yes");
        formData.append('tumb_status', thumb);
        formData.append('email', email);
        formData.append('secret', secret);
        formData.append('review_id', $('#reviewID').val());
        // formData.append('id', businessId);

        var baseUrl = $('#hfBaseUrl').val();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: baseUrl + "/done-me",
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

            console.log("status " + statusCode);
            if(statusCode == 200)
            {
                if(thumb === 'down')
                {
                    location.href = baseUrl+'/business-review-complete/'+email+'/'+secret+'/'+name;
                }
                else
                {
                    console.log("else up");
                    var html = "";
                    var mediaUrl = baseUrl + '/public/images/feedback-review/';

                    if(data && data != '') {
                        console.log("inside data ");

                        if(data.length >= 1)
                        {
                            console.log("if ");

                            if(data.length == 1)
                            {
                                $.each(data, function (index, value) {
                                    var type = value.type;
                                    type = type.toLowerCase();
                                    var typeTitle = type.replace(" ", "-");
                                    var reviewUrl = value.add_review_url;

                                    if(reviewUrl !== '') {
                                        location.href = reviewUrl;
                                    }
                                    else
                                    {
                                        $(".loader").hide();
                                        $(".review-content").html("Problem in retrieving the site. Please try again later.");
                                    }
                                });
                            }
                            else
                            {
                                $(".loader").hide();
                                html = '<div class="social-media-list">';
                                $(".review-content").html("Select the site where you would like to leave a review.");
                                $.each(data, function (index, value) {
                                    var type = value.type;
                                    type = type.toLowerCase();
                                    var typeTitle = type.replace(" ", "-");
                                    var reviewUrl = value.add_review_url;

                                    if(reviewUrl !== '')
                                    {
                                        html += '<a href="'+reviewUrl+'" target="_blank" class="site-selection" data-site="'+value.type+'"><img src="' + mediaUrl + typeTitle +'-icon.png" alt="'+type+'"></a>';
                                    }
                                });
                                html += '</div>';
                            }
                        }
                        else
                        {
                            console.log("else ");

                            for(var i=1; i<=1; i++)
                            {
                                var type = data['type'];
                                type = type.toLowerCase();
                                var typeTitle = type.replace(" ", "-");

                                var reviewUrl = data['add_review_url'];

                                console.log("reviewUrl " + reviewUrl);
                                if(reviewUrl && reviewUrl !== '') {
                                    console.log("inside > IF ");
                                    // return false;
                                    location.href = reviewUrl;
                                }
                                else
                                {
                                    console.log("inside > else ");
                                    // return false;
                                    $(".loader").hide();
                                    $(".review-content").html("Problem in retrieving the site. Please try again later.");
                                }
                            }

                        }

                        $(".Interactive-box").show();
                        $(".Interactive-box").html(html);
                    }
                }
            }
            else
            {
                $(".loader").hide();
                if(thumb === 'down')
                {
                    $(".error").show();
                    $(".error").html(statusMessage);
                    $('.send-feedback').show();
                }
                else
                {
                    $(".Interactive-box").show();
                    $(".alert-danger").show();
                    $(".alert-danger").html(statusMessage);
                }
            }
        });
    }
}
