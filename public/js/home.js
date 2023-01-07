var processStarted = false;

// window.onload = function() {
//     $(".web-audit").show();
//     setTimeout(function () {
//         if($("#source").length > 0) {
//             var domainSource = 'https://reviewer.nichepractice.com';
//             $(".web-audit").html('<iframe src="' + domainSource + '"><p>Your browser does not support iframes.</p></iframe>');
//         }
//
//     }, 1000);
// };
$(function () {
    // $('.owl-carousel').owlCarousel({
    //     margin:10,
    //     loop:true,
    //     // center: true,
    //     items: 1,
    //     // autoWidth:true,
    //     // singleItem: true,
    //     // autoHeight:true,
    //     dots: false,
    //     nav: true,
    //     navText : ['<i class="fa fa-angle-left" aria-hidden="true"></i>','<i class="fa fa-angle-right" aria-hidden="true"></i>']
    //
    //     // margin:10,
    //     // loop:true,
    //     // // center: true,
    //     //
    //     // items: 1,
    //     // autoWidth:true,
    //     // singleItem: true,
    //     // autoHeight:true,
    //     // nav: true,
    //     // dots: true,
    // });


    $(".btn-begin").click(function () {
        $('.owl-card').hide();
        $(".owl-card-carasol").show();
    });

    // $(".btn-continue").click(function()
    // {
    //     $(".owl-next").click();
    // });

    var status = $("#status").val();

    var welcomeManager = $(".welcome-process");

    var progressBar = $(".progress-bar", welcomeManager);

    console.log("Welcome status " + status);

    if (status != 1) {
        // welcomeManager.show();

        if(status != 6)
        {
            welcomeManager.modal('show');
        }

        console.log("Inside");

        if (status == 0) {
            $(".account").addClass('completed-step');
            progressBar.css('width', '33%');
            // console.log("calling " + updateStatus(4));

            updateStatus(4);
        }
        else if(status == 6)
        {
            console.log("yes 6 ");
            setTimeout(function () {
                welcomeManager.modal('hide');
            },700);
        }
        else {
            // console.log("yes");
            startBusinessProcess();
        }
    } else {
        welcomeManager.modal('hide');
    }

        // $(".owl-carousel").owlCarousel();
});

function initiateWebsitePanel() {
    // $.ajax({
    //     headers: {
    //         'X-CSRF-TOKEN': $('input[name="_token"]').val()
    //     },
    //     type: "POST",
    //     url: "https://reviewer.nichepractice.com"
    // }).done(function (result) {
    //
    // });
    // if($("#source").length > 0)
    // {
    //     $.ajax({
    //         headers: {
    //             'X-CSRF-TOKEN': $('input[name="_token"]').val()
    //         },
    //         type: "GET",
    //         url: "https://reviewer.nichepractice.com"
    //     }).done(function (result) {
    //
    //     });
    // }
}

function startBusinessProcess() {
    var status = $("#status").val();
    console.log("status " + status);
    if (status == 1) {
        return false;
    }

    var welcomeManager = $(".welcome-process");
    var progressBar = $(".progress-bar", welcomeManager);
    var siteUrl = $('#hfBaseUrl').val();

    var tabPane = $('.tab-pane');
    var tab1 = $('#tab1');
    var tab2 = $('#tab2');
    var tab3 = $('#tab3');
    tabPane.hide();

    $(".process-loader").hide();

    if (status == 4) {
        $(".collect-data").next('.process-loader').show();
        tab1.show();
        $(".account").addClass('completed-step');
        progressBar.css('width', '33%');

        var getCode = '';

        if($("#source").length > 0)
        {
            $(".web-audit").show();
            console.log("yes okay");
            processStarted = true;
            setTimeout(function () {

                console.log("process of");
                var source = $("#source").val();
                console.log("source " + source);

                $(".web-audit").append('<iframe src="' + source + '"><p>Your browser does not support iframes.</p></iframe>');
            }, 3000 );
        }

        setTimeout(function () {
            $('.tab-pane').hide();
            $('#tab2').show();
        }, 20000);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: siteUrl + "/done-me",
            data: {
                send: 'business-process'
            }
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);

            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;
            var data = json.data;
            var errors = json.errors;

            if (statusCode == 200) {
                // should be 5
                updateStatus(5);
                // $("#status").val();
                // startBusinessProcess();
            }

            console.log("status code " + statusCode);
            console.log("statusMessage " + statusMessage);
        });
    }
    else if (status == 5) {
        $(".collect-data").next('.process-loader').show();
        tab2.show();

        $(".account").addClass('completed-step');
        // start web process
        $(".collect-data").addClass('completed-step');
        progressBar.css('width', '50%');

        //
        webProcess();



    }
    else if (status == 6) {
        $(".collect-reviews").next('.process-loader').show();
        tab3.show();

        // start web process
        $(".account, .collect-data, .collect-reviews").addClass('completed-step');
        progressBar.css('width', '75%');

        updateStatus(1);
        // execute this request after calling ajax

        // setTimeout(function () {
        //     progressBar.css('width', '100%');
        //     location.reload();
        // }, 2000);

        // setTimeout(function () {
        //     location.reload();
        // }, 3000);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: siteUrl + "/done-me",
            data: {
                send: 'reviews-process',
                type: 'all'
            }
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);

            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;
            var data = json.data;
            var errors = json.errors;

            if (statusCode == 200) {
                // updateStatus(1);

                progressBar.css('width', '100%');

                // setTimeout(function () {
                    // location.reload();
                    // $(".welcome-process").modal('hide');

                // }, 2000);
            }

            console.log("status code " + statusCode);
            console.log("statusMessage " + statusMessage);
        });
    }
}

var counter = 1;

function webProcess() {
    console.log("processStarted");
    console.log(processStarted);

    var time = 70000; // 7 seconds
    var source;

    console.log("process ahead");

    var isIframeLoaded = $("iframe").length;
    var userAgent =  window.navigator.userAgent;

    $(".web-audit").show();

    if(counter === 1 && processStarted === false && $("#source").length > 0)
    {
        console.log("again star");
        source = $("#source").val();
        console.log("source " + source);
        $(".web-audit").append('<iframe src="'+source+'"><p>Your browser does not support iframes.</p></iframe>');
    }
    else if(counter === 2)
    {
        // time = 40000; // 40 seconds
        time = 10000; // 10 seconds
    }
    else
    {
        time = 10000;
    }

    setTimeout(function () {
        var siteUrl = $('#hfBaseUrl').val();

        console.log("web counter " + counter);
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            type: "POST",
            url: siteUrl + "/done-me",
            data: {
                send: 'web-process',
                isIframeLoaded: isIframeLoaded,
                userAgent: userAgent,
                webProcessChecking: true

            }
        }).done(function (result) {
            // parse data into json
            var json = $.parseJSON(result);


            // get data
            var statusCode = json.status_code;
            var statusMessage = json.status_message;
            var data = json.data;
            var errors = json.errors;

            console.log("status code " + statusCode);
            console.log("statusMessage " + statusMessage);

            if (statusCode == 200) {
                // should be 5
                updateStatus(6);
                // $("#status").val();
                // startBusinessProcess();
            }
            else
            {
                if(counter < 4)
                {
                    counter++;
                    webProcess();
                }
                else
                {
                    updateStatus(6);
                }
            }
        });
    },time);
}

function updateStatus(value) {
    var siteUrl = $('#hfBaseUrl').val();
    var getCode = '';

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url: siteUrl + "/done-me",
        data: {
            send: 'status-generate',
            status: value
        }
    }).done(function (result) {
        // parse data into json
        var json = $.parseJSON(result);

        // get data
        var statusCode = json.status_code;
        var statusMessage = json.status_message;
        var data = json.data;
        var errors = json.errors;

        if (statusCode == 200) {
            $("#status").val(value);


            if(value == 1)
            {
                // execute this request after calling ajax

                setTimeout(function () {
                    var welcomeManager = $(".welcome-process");
                    var progressBar = $(".progress-bar", welcomeManager);
                    progressBar.css('width', '100%');

                    // location.reload();
                }, 5000);

                var html = '';
                setTimeout(function () {
                    var welcomeManager = $(".welcome-process");
                    var firstName = $("#first-name").val();
                    // welcomeManager.modal('hide');

                    $(".modal-body, .modal-footer, .validate-me", welcomeManager).remove();

                    html += '<div class="modal-body text-center" style="padding-top: 20px;padding-bottom: 20px; ">\n' +
                        '        <h2 class="font-normal m-0">Welcome to Trustyy, <span>'+firstName+'</span></h2>\n' +
                        '        <h5 class="m-0 font-normal">Your free trial starts today.</h5>\n' +
                        '        <div>\n' +
                        '            <img src="'+siteUrl+'/public/images/dash-modal-1.jpg" style="width: 90%; height: auto;">\n' +
                        '            <p style="max-width: 80%; margin-left: auto; margin-right: auto;">We\’re absolutely thrilled to have you onboard and can\’t wait to see you succeed!</p>\n' +
                        '            <button class="btn btn-primary go-inside">Let\'s Get Started</button>\n' +
                        '        </div>\n' +
                        '      </div>';


                    $(".modal-dialog").css('width', '700px');
                    $(".modal-header").after(html);
                    // location.reload();
                }, 7000);
            }
            else
            {
                startBusinessProcess();
            }
        }

        console.log("status code " + statusCode);
        console.log("statusMessage " + statusMessage);
    });

    // console.log("OUTSIDE code " );
    // console.log(getCode);
    // console.log(getCode.responseText);
    //
    // return getCode;
}

$(document.body).on('click', '.go-inside' ,function() {
    showPreloader();
    location.reload();
});


