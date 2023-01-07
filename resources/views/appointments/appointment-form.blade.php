<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Form</title>

    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1" />
    <!--  ////////////////////////////////////////-->
    <!--  /////////evo calender CSS/////////////////-->
    <!--  ////////////////////////////////////////-->
    <!-- Core Stylesheet -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
    <link rel="stylesheet" href="{{asset('public/css/appointment/evo-calendar/css/evo-calendar.css')}}" />
    <!--  ////////////////////////////////////////-->
    <!--  /////////evo calender CSS/////////////////-->
    <!--  ////////////////////////////////////////-->
    <!-- Core Stylesheet -->

    <link rel="stylesheet" href="{{asset('public/css/appointment/first.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/appointment/firstcss/oct-commonca80.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/appointment/firstcss/oct-frontendca80.css')}}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" rel="stylesheet"  type='text/css'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>
        .oct-button{
            color : #ffffff !important;
            background-color: #6ba5e3 !important;
        }

        .oct-booking-step{
            border-bottom-color: #f1605b !important;
        }
        .oct-booking-step ul li.active,
        .oct-booking-step ul li span.sep.active {
            color: #00152b !important;
        }
        .oct-booking-step ul li {
            /*color: #1a1a1a !important;*/
            color: #6ba5e3 !important;
        }
        .fa-chevron-right{
            color: #6ba5e3;
        }

        .oct-loader .oct-first{
            border: 3px solid #ffffff !important;
        }
        .oct-loader .oct-second{
            border: 3px solid #6ba5e3 !important;
        }
        .oct-loader .oct-third{
            border: 3px solid #00152b !important;
        }

        .calendar-header{
            background-color: #6ba5e3 !important;
        }

        .today-date .oct-selected-date-view .custom-check:before{
            border-left: 2px solid #00152b !important;
            border-bottom: 2px solid #00152b !important;
        }
        .calendar-body .dates .oct-week.by_default_today_selected,
        .calendar-body .oct-show-time .time-slot-container ul li.time-slot,
        .calendar-body .oct-show-time .time-slot-container .oct-slot-legends .oct-available-new{
            background: #6ba5e3 !important;
        }

    </style>
</head>
<body>

<div class="container pt-3">
    <div>
        <div>
            <div class="oct-first-step form-inner visible" >
                <div class="oct-booking-step" data-current="1">
                    <ul class="oct-list-inline nm">
                        <li class="active" id="first">Service, Staff and Time<span class="sep"><i class="fas fa-chevron-right"></i></span></li>
                        <li id="second">Info and Checkout<span class="sep"><i class="fas fa-chevron-right"></i></span></li>
                        <li id="third">Done</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5 first-div">
    <form id="page1Form" action="{{url('/api/step-one-info')}}">
    <div class="row">
        <div class="col-12">
            <input type="hidden" name="oct_selected_location" id="oct_selected_location"/>
            <input type="hidden" name="oct_selected_service" id="oct_selected_service"/>
            <input type="hidden" name="oct_selected_staff" id="oct_selected_staff"/>
            <input type="hidden" name="userId" id="userId">
            <section class="oct-display-middle oct-main-left oct-md-8 oct-sm-7 oct-xs-12 np pull-left no-sidebar-right oct_remove_left_sidebar_class">
                <div class="oct-main-inner fullwidth">
                    <div class="hide-data visible cb oct_login_form_check_validate" id="oct_second_step">
                        <div class="oct-second-step form-inner visible" >

                            <div class="visible cb" id="oct_first_step" >

                                <div class="oct-form-common oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 pull-left">
                                    <div class="common-inner">
                                        <div class="pr oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 np">
                                            <div class="oct-form-row fullwidth">
                                                <h3 class="block-title"><i class="fas fa-map-marker"></i> Choose Location</h3>
                                                <span id="oct_location_error" class="oct-error">Please select location</span>
                                                <div class="pr oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 np">
                                                    <div id="cus-select1" class="cus-location fullwidth custom-input nmt">
                                                        <div class="common-selection-main location-selection">
                                                            <div class="selected-is select-location position-relative" title="Choose Your Selection">
                                                                <div class="data-list" id="selected_location">
                                                                    <div class="oct-value change-value" id="a1">Please choose location</div>
                                                                </div>
                                                                <i class="fas fa-caret-down position-absolute"></i>
                                                            </div>
                                                            <ul class="common-data-dropdown location-dropdown custom-dropdown">
                                                                @if(!empty($locations))
                                                                    @foreach($locations as $location)
                                                                    <li class="data-list select_location">
                                                                        <div class="oct-value1" data-value="{{$location->locations_name}}" data-value-id="{{$location->id}}">{{$location->locations_name}}</div>
                                                                    </li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <i class="bottom-line"></i>
                                                    <label class="oct-relative oct-error error-location"></label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="oct-form-row fullwidth"> <!-- start select service -->
                                            <h3 class="block-title"><i class="fas fa-th-large mr-2"></i> Which service you would like us to provide?</h3>
                                            <span id="oct_service_error" class="oct-error oct-hide">Please check service area.</span>
                                            <div class="pr oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 np">
                                                <div id="cus-select1" class="cus-select fullwidth custom-input nmt">
                                                    <div class="common-selection-main service-selection">
                                                        <div class="selected-is select-custom" title="Choose Your Selection">
                                                            <div class="data-list" id="selected_custom">
                                                                <div class="oct-value change-value1">Please choose service</div>
                                                            </div>
                                                            <i class="fas fa-caret-down position-absolute"></i>
                                                        </div>
                                                        <ul id="oct_services" class="common-data-dropdown service-dropdown custom-dropdown">

                                                        </ul>
                                                    </div>
                                                </div>
                                                <i class="bottom-line"></i>
                                                <label class="oct-relative oct-error error-service"></label>
                                            </div>

                                            <div id="oct_service_detail" class="pr oct-sm-12 oct-xs-12 np oct-hide service-details">
                                            </div>

                                        </div>  <!-- end select service -->

                                        <!-- Service Addons Container -->
                                        <div id="oct_service_addons" class="oct-form-row fullwidth oct-hide"></div>
                                        <!-- End Service Addons Container -->

                                        <div class="oct-form-row fullwidth"> <!-- Select staff start -->
                                            <h3 class="block-title"><i class="far fa-user mr-2"></i>To whom you want to select as service provider?</h3>
                                            <div class="pr oct-sm-12 oct-xs-12 np" id="oct_staff_info">
                                                <div class="oct-service-staff-list oct-common-box">
                                                    <ul class="staff-list fullwidth np" id="staff-lists">
                                                    </ul>
                                                    <label class="oct-relative oct-error error-provider"></label>
                                                </div>

                                            </div>
                                            <span id="oct_staff_error" class="oct-error">Please choose service provider</span>
                                        </div> <!-- Select staff end -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <div class="row pb-5">
                <div class="col-12 mx-auto">
                    <div id="evoCalendar"></div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-12 text-center align-self-center">
                    <button type="submit" class="btn btn-next-page px-5 mb-5 next-div">NEXT</button>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>
</div>
</div><!-- main view content end here -->


<div class="second-div py-5">
    <form method="post" id="page2Form" action="{{url('/api/step-two-info')}}">
        <input type="hidden" name="user_Id" id="user_Id">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="hide-data visible cb oct_login_form_check_validate" id="oct_second_step">
                    <div class="oct-second-step form-inner visible" >

                        <div class="oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 pull-left">
                            <h3 class="block-title"><i class="far fa-user mr-2"></i> User Information</h3>
                            <div class="common-inner oct-info-input">
                                <div class="oct-main-inner" id="user-login">
                                    <div class="user-login-main oct-form-row fullwidth" >
                                    </div>
                                </div>
                                <div class="oct-main-inner" id="new-user">
                                        <div class="row oct-common-inputs fullwidth new-user-personal-detail-area">
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="email" class="custom-input" name="email" id="new_user_preferred_username" value="" />
                                                    <label class="custom">Preferred Email</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                                <label class="oct-relative oct-error email-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="text" class="custom-input" name="first_name" id="new_user_firstname" value="" />
                                                    <label class="custom">First Name</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                               <label class="oct-relative oct-error first-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="text" class="custom-input" name="last_name" id="new_user_lastname" value="" />
                                                    <label class="custom">Last Name</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                                <label class="oct-relative oct-error last-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="hidden" class="input_flg" value="+1">
                                                    <input type="text" name="phone_number" class="custom-input oct-phone-input" value="" data-ccode="" id="oct-front-phone" />
                                                    <label class="custom oct-phone-label">Phone number</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                                <label class="oct-relative oct-error phone-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12">
                                                <div class="fullwidth">
                                                    <label class="oct-relative">Gender</label>
                                                    <div class="oct-custom-radio">
                                                        <ul class="oct-radio-list">
                                                            <li class="oct-first-radio oct-xs-6">
                                                                <input id="oct-male" class="input-radio new_user_gender" name="gender"  type="radio" value="Male" />
                                                                <label for="oct-male" class="oct-relative"><span></span>Male</label>
                                                            </li>
                                                            <li class="oct-second-radio oct-xs-6">
                                                                <input id="oct-female" class="input-radio new_user_gender" name="gender"  type="radio" value="Female" />
                                                                <label for="oct-female" class="oct-relative"><span></span>Female</label>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="oct-form-row oct-sm-6 oct-xs-6 oct-fw">
                                                <div class="pr">
                                                    <input type="text" class="custom-input" name="street_address" id="new_user_street_address" value="" />
                                                    <label class="custom">Street Address</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                                <label class="oct-relative oct-error street-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="text" class="custom-input" name="city" id="new_user_city" value="" />
                                                    <label class="custom">Town/City</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                               <label class="oct-relative oct-error city-error"></label>
                                            </div>
                                            <div class="oct-form-row oct-md-6 oct-lg-6 oct-sm-12 oct-xs-12">
                                                <div class="pr">
                                                    <input type="text" class="custom-input" name="state" id="new_user_state" value="" />
                                                    <label class="custom">State</label>
                                                    <i class="bottom-line"></i>
                                                </div>
                                                <label class="oct-relative oct-error state-error"></label>
                                            </div>

                                        </div>
                                        <!--            //////////////////////////////////-->
                                        <!--            ///////////guest user/////////////-->
                                        <!--            //////////////////////////////////-->

                                        <!--            //////////////////////////////////-->
                                        <!--            ///////////guest user/////////////-->
                                        <!--            //////////////////////////////////-->
                                        <div class="oct-form-row fullwidth"> <!-- Select staff start -->
                                            <h3 class="block-title"><i class="fas fa-money-bill-wave mr-2"></i>Choose Payment Method</h3>
                                            <div class="pr oct-sm-12 oct-xs-12 np" id="oct_staff_info">
                                                <div class="payment_box" style="display: flex;justify-content: center;">

                                                </div>
                                                <div class="oct-service-staff-list oct-common-box payment-form">
                                                    <ul class="staff-list fullwidth np">
                                                        <li data-staffid="44" class="oct-staff-box oct-sm-6 oct-md-3 oct-lg-3 oct-xs-12 mb-15 payment-section">
                                                            <input type="radio" name="payment" class="staff-radio check-box-tick" id="oct-staff-41" value="paypal" style="display: block!important;" />
                                                            <label class="oct-staff border-c" for="oct-staff-41">
                                                                <span class="br-100"></span>
                                                                <div class="oct-staff-img oct-staff-img1">
                                                                    <img class="br-100" src="{{asset('public/images/paypal.svg')}}" />
                                                                </div>
                                                                <div class="staff-name fullwidth text-center">Paypal</div>
                                                            </label>
                                                        </li>
                                                        <li data-staffid="45" class="oct-staff-box oct-sm-6 oct-md-3 oct-lg-3 oct-xs-12 mb-15">
                                                            <input type="radio" name="payment" class="staff-radio check-box-tick" value="cash" id="oct-staff-43" style="display: block!important;"  />
                                                            <label class="oct-staff border-c" for="oct-staff-43">
                                                                <span class="br-100"></span>
                                                                <div class="oct-staff-img oct-staff-img1">
                                                                    <img class="br-100" src="{{asset('public/images/cash.svg')}}" />
                                                                </div>
                                                                <div class="staff-name fullwidth text-center">Cash Payment</div>
                                                            </label>
                                                        </li>
                                                    </ul>
                                                </div>

                                            </div>
                                            <span id="oct_staff_error" class="oct-error">Please choose service provider</span>
                                        </div> <!-- Select staff end -->
                                </div><!-- oct main inner end -->
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
        <input type="hidden" id="appt_id" name="appointment_id">
        <div class="row">
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-next-page px-5 user-next">Next</button>
            </div>
        </div>
    </div>
</form>
</div>
<div class="container third-div py-5">
    <div class="row">
        <div class="col-12">
            <div class="hide-data visible cb" id="oct_third_step">
                <div class="oct-third-step form-inner visible" >
                    <div class="oct-md-12 oct-lg-12 oct-sm-12 oct-xs-12 pull-left">
                        <!-- <h3>3. Done</h3> -->
                        <div class="common-inner">
                            <div class="booking-thankyou">
                                <h1 class="header1">Congratulations</h1>
                                <h3 class="header3">Your Appointment created successfully!</h3>
                                <p class="thankyou-text">You will be notified with details of appointment.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--////////////////////////////////////////////-->
<!--////////////////////////////////////////////-->
<!--////////////////////////////////////////////-->

<!--////////////////////////////////////////////-->
<!--////////////////////////////////////////////-->
<!--////////////////////////////////////////////-->
<div class="modal fade" id="myModal123" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-md">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Appointment</h4>
                <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
            </div>
            <form method="POST" id="appForm" action="{{url('/api/add-appointment')}}">
                <input type="hidden" name="user_id" id="user_id">
                <input type="hidden" name="appointment_date" id="appointment_date">
            <div class="modal-body">
                <span class="throw_error"></span>
                <div class="alert alert-success" role="alert" id="success">
                </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="title" placeholder="Enter Appointment Title" name="appointment_name" required>
                    </div>
                    <h1 style="margin: 0;padding: 15px 0;font-size: 16px;font-weight: 500">Description</h1>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12">
                                <textarea class="text-box" name="appointment_description" placeholder="Enter Appointment Description" required></textarea>
                            </div>
                        </div>
                    </div>
                    <select name="appointment_time" id="time-slots" required>

                    </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-save" id="save-appointment">Save</button>
                <button type="button" class="btn btn-close" data-dismiss="modal">Close</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    function getParamValue()
    {
        const url = window.location.search.substring(1); //get rid of "?" in querystring
        const qArray = url.split('&'); //get key-value pairs
        for (var i = 0; i < qArray.length; i++)
        {
            const pArr = qArray[i].split('='); //split key and value
            return pArr[1]; //return value
        }
    }
    $(function () {
        const url = 'https://staging-api.heroesofdigital.io/';
       // const url = 'http://localhost/hod_backend/';
        $('#success').hide();
        $('.oct-value1').click(function(e) {
            e.preventDefault();
            let value = $(this).closest('.oct-value1').data('value-id');

            $('#oct_selected_location').val(value);
            let siteUrl = $('#hfBaseUrl').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "GET",
                url: url +'appointmentServices',
                data: {
                    location_id: value,
                },
            }).done(function (result) {
                var json = $.parseJSON(result);
                var data = json.services;
                var html = '';
                for( var j=0; j < data.length; j++ ) {
                    html += '<li class="data-list service-category"><div class="oct-value22" disabled> '+ data[j].category_name +'</div></li>';
                    for( var k=0; k < data[j].services.length; k++ ) {
                        html += '<li class="data-list select_custom" data-sid="' + data[j].services[k].id +'"><div class="oct-value2" data-value="' + data[j].services[k].service_name + '" data-value-id="' + data[j].services[k].id +'">' + data[j].services[k].service_name +'</div></li>'
                    }
                }
                $("#oct_services").html(html);
            });
        });
        $(document.body).on('click', '.oct-value2' ,function(e) {
            e.preventDefault();
            getParamValue();

            const param1 = getParamValue('param1');
            $('#userId').val(param1);

            $('#user_Id').val(param1);

            var value = $(this).closest('.oct-value2').data('value-id'); // = 9
            $('#oct_selected_service').val(value);
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "GET",
                url:  url +'appointmentProviders',
                data: {
                    service_id: value,
                },
            }).done(function (result) {
                var json = $.parseJSON(result);
                var data = json.providers;
                var html = '';
                for( var j=0; j < data.length; j++ ) {
                    html += '<li id="provider" data-staffid="'+ data[j].id +'" class="oct-staff-box oct-sm-6 oct-md-3 oct-lg-3 oct-xs-12 mb-15"><input type="radio" name="provider_list" class="staff-radio" data-value-id="'+ data[j].id +'" id="oct-staff-41" /> <label class="oct-staff border-c" for="oct-staff-41"> <span class="br-100"></span> <div class="oct-staff-img "> <img class="br-100" src="{{asset('public/images/staff.png')}}" /> </div> <div class="staff-name fullwidth text-center">'+ data[j].provider_name+'</div> </label> </li>';
                }

                $("#staff-lists").html(html);
            });
        });
        $(document.body).on('click', '.day' ,function() {
            var appdate = $(this).closest('.calendar-active').data('date-val');
           // console.log(appdate);
            getParamValue();

            const param1 = getParamValue('param1');
            $('#user_id').val(param1);

            $('#appointment_date').val(appdate);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "GET",
                url:  url +'appointmentDates',
                data: {
                    date: appdate,
                    user_id: param1
                },
            }).done(function (result) {
                var json = $.parseJSON(result);
                var data = json.appointments;
                //console.log(data);
                var html = '';
                html += '<option selected disabled>Please select available slots</option>';
                for( var j=0; j < data.length; j++ ) {
                    html += '<option value="'+ data[j].available_time +'">'+ data[j].available_time +'</option>';
                }
                $("#time-slots").html(html);
            });
        });

        $(document.body).on('click', '.payment-section' ,function() {
            console.log("clicked");

            var appointment_service = $("input[name='oct_selected_service']").val();
           // console.log(appointment_service);

            getParamValue();
            const param1 = getParamValue('param1');

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "GET",
                url:  url +'appointmentScript',
                data: {
                    service_id: appointment_service,
                    user_id: param1
                },

            }).done(function (result) {
                var json = $.parseJSON(result);
                var data = json.script;
              //  console.log(data);
                var strTest = data.payment_script;
              //  var modifiedScript = strTest.split('<script>').pop().split('<\/script>')[0];

                str = strTest.replace(/\n/g, '');
                str2 = str.replace(/<script>.*?<\/script>/g, '');

                console.log(str2);
                $(".payment-form").hide();

                $(".payment_box").html(str2);

                var modifiedScript = strTest.split('<script>').pop().split('<\/script>')[0];

                 console.log("modifiedScript the things");
                 console.log(modifiedScript);

                    setTimeout(function () {
                        console.log("inside");
                        $(".payment_box").append('<script>'+modifiedScript.replace(/\"/g, "")+'<\/script>');
                    },1000);
                });
            });

        $("#appForm").submit(function(event) {

            const values = $('#appForm').serialize();
            const url  = $(this).attr("action");
            $.ajax({
                type      : 'POST', //Method type
                url       :  url, //Your form processing file URL
                data      :  values,
                dataType  : 'json',
                beforeSend:function()
                {
                    $('#save-appointment').attr('disabled', 'disabled');
                    $('#save-appointment').val('Saving...');
                },
                success: function (data) {
                    // console.log(data);
                    if (data.success == 'true') { //If fails
                        if (data.appointment) {
                            var appoint_id = data.appointment.id;
                           $('#appt_id').val(appoint_id);
                        }
                        $('#appForm')[0].reset();
                        $('#save-appointment').attr('disabled', false);
                        $('#save-appointment').val('Save');
                       // $('#success').show();
                        $('#myModal123').modal('hide');
                    }
                    else {
                        $('.throw_error').fadeIn(200).append('<span>' + data.message + '</span>'); //If successful, than throw a success message
                    }
                }
            });
            event.preventDefault(); //Prevent the default submit
        });

        $("#page1Form").submit(function(e){
            e.preventDefault();

            var _token = $("input[name='_token']").val();
            var appointment_location = $("input[name='oct_selected_location']").val();
            var appointment_service = $("input[name='oct_selected_service']").val();
            var appointment_provider = $('#provider').attr("data-staffid");
            var user_id = $("input[name='userId']").val();
            const url  = $(this).attr("action");

            $.ajax({
                url: url,
                type:'PUT',
                data: {_token:_token, user_id:user_id, appointment_location:appointment_location, appointment_service:appointment_service, appointment_provider:appointment_provider},
                success: function(data) {
                   // console.log(data);
                    if($.isEmptyObject(data.error)){
                        $(".first-div").css("display", "none");
                        $(".second-div").css("display", "block");
                        $('#second').addClass('active');
                        $('#first').removeClass('active');
                        $(window).scrollTop(0);
                    }else{
                        printErrorMsg(data.error);
                    }
                }
            });
        });

        $("#page2Form").submit(function(e){
            e.preventDefault();

            const values = $('#page2Form').serialize();
            const url  = $(this).attr("action");

            $.ajax({
                url: url,
                type:'POST',
                data: values,
                success: function(data) {
                   // console.log(data);
                    if($.isEmptyObject(data.error)){
                        $(".second-div").css("display", "none");
                        $(".third-div").css("display", "block");
                        $('#second').removeClass('active');
                        $('#third').addClass('active');
                        $(window).scrollTop(0);
                    }else{
                        printPageErrorMsg(data.error);
                    }
                }
            });
        });
    });

    function printErrorMsg (msg) {
        $.each( msg, function( key, value ) {
            if (value == 'The appointment location field is required.'){
                $(".error-location").append(value);
            }
            if(value == 'The appointment service field is required.'){
                $(".error-service").append(value);
            }
            if (value == 'The appointment provider field is required.'){
                $(".error-provider").append(value);
            }
        });
    }
    function printPageErrorMsg (msg) {
        $.each( msg, function( key, value ) {
            if (value == 'The email field is required.'){
                $(".email-error").append(value);
            }
            if(value == 'The first name field is required.'){
                $(".first-error").append(value);
            }
            if (value == 'The last name field is required.'){
                $(".last-error").append(value);
            }
            if (value == 'The phone number field is required.'){
                $(".phone-error").append(value);
            }
            if (value == 'The street address field is required.'){
                $(".street-error").append(value);
            }
            if (value == 'The city field is required.'){
                $(".city-error").append(value);
            }
            if (value == 'The state field is required.'){
                $(".state-error").append(value);
            }
        });
    }
</script>

<script src="{{asset('public/css/appointment/evo-calendar/js/evo-calendar.js')}}"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
<script src="{{asset('public/js/appointment/first.js')}}"></script>
</body>
</html>

