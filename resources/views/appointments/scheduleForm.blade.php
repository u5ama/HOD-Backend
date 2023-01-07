<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Heroes of Digital</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js" integrity="sha512-eyHL1atYNycXNXZMDndxrDhNAegH2BDWt1TmkXJPoGf1WLlNYt08CSjkqF5lnCRmdm3IrkHid8s2jOUY4NIZVQ==" crossorigin="anonymous"></script>    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        input.parsley-success,
        select.parsley-success,
        textarea.parsley-success {
            color: #468847;
            background-color: #DFF0D8;
            border: 1px solid #D6E9C6;
        }

        input.parsley-error,
        select.parsley-error,
        textarea.parsley-error {
            color: #B94A48;
            background-color: #F2DEDE;
            border: 1px solid #EED3D7;
        }

        .parsley-errors-list {
            margin: 2px 0 3px;
            padding: 0;
            list-style-type: none;
            font-size: 0.9em;
            line-height: 0.9em;
            opacity: 0;

            transition: all .3s ease-in;
            -o-transition: all .3s ease-in;
            -moz-transition: all .3s ease-in;
            -webkit-transition: all .3s ease-in;
        }

        .parsley-errors-list.filled {
            opacity: 1;
        }

        .parsley-type, .parsley-required, .parsley-equalto, .parsley-pattern, .parsley-length{
            color:#ff0000;
        }
        /*---------------------------------------------*/
        .overlay{
            background-color: #e6e6e6;
            padding: 10px 25px 25px;
            height: 100vh;
        }
        .wrap{
            width:100%;
            margin:0 auto;
        }
        h1{
            text-align: center;
            color: #212529;
            margin-top: 10px;
            font-size: 24px;
            font-family: inherit;
            text-shadow: 0px 4px 3px rgba(0,0,0,0.2),
            0px 8px 13px rgba(0,0,0,0.1),
            0px 18px 23px rgba(0,0,0,0.1);
        }
        .app-form-style{
            width:100%;
            padding:15px;
            margin-bottom:20px;
            border-radius:5px;
            border:none;
            outline:none;
            font-size: 14px;
            font-family: inherit;
        }
        .input100{
            min-height: 150px;
            padding: 15px;
            width:100%;
            margin-bottom:20px;
            border-radius:5px;
            border:none;
            outline:none;
            font-size: 14px;
            font-family: inherit;
        }
        .input100:focus{
            border-left:5px solid #e74c3c;
            -webkit-transition:0.4s ease;
        }
        input[type="text"]:focus{
            border-left:5px solid #e74c3c;
            -webkit-transition:0.4s ease;
        }

        input[type="date"]:focus{
            border-left:5px solid #e74c3c;
            -webkit-transition:0.4s ease;
        }
        .button-style{
            background:#e74c3c;
            color:#FFF;
            width:100%;
            padding:15px;
            font-weight:700;
            outline: none;
        }


        .button-style:hover{
            cursor:pointer;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="prefix-class-align">
                <form id="appointmentForm" action="{{url('/api/add-appointment')}}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="userId" id="user_id" value="">
                    <div class="overlay">
                        <div class="wrap">
                            <span class="throw_error"></span>
                            <div class="alert alert-success" role="alert" id="success">
                            </div>
                    @if(!empty($form) && !empty($font))
                            @if(!empty($head) && !empty($font))
                            <h1 style="font-size: {{ $head->headFontSize}}; color: {{$head->headingColor}}; font-family: {{$font->allFontFamily}}"> {{$head->headingText}} </h1>
                            @else
                                <h1 class="contact100-form-title">
                                    Appointment Form
                                </h1>
                            @endif
                                <label for="appointment_name" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">Appointment Name</label>
                                    <input type="text" class="app-form-style" id="appointment_name" name="appointment_name" data-parsley-type="text" data-parsley-trigger="keyup" required placeholder="Enter Appointment Name" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}">

                                <label for="appointment_date" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">Appointment Date</label>
                                    <input type="date" class="app-form-style" placeholder="Select Appointment Date" id="appointment_date" name="appointment_date" required data-parsley-type="date" data-parsley-trigger="keyup" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}};font-family: {{$font->allFontFamily}}">

                                <label for="appointment_description" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">Appointment Description</label><br />
                                    <textarea class="input100" name="appointment_description" placeholder="Appointment description" required style="font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}"></textarea>
                            @else
                                <label for="appointment_name">Appointment Name</label>
                                    <input type="text" class="app-form-style" id="appointment_name" name="appointment_name" required placeholder="Enter Appointment Name">

                                <label for="appointment_date">Appointment Date</label>
                                    <input type="date" class="app-form-style" placeholder="Select Appointment Date" id="appointment_date" name="appointment_date" required>

                                <label for="appointment_description">Appointment Description</label><br />
                                    <textarea class="input100" name="appointment_description" placeholder="Appointment description" required></textarea>
                        @endif
                        @if(!empty($button) && !empty($font))
                        <div class="w-100 text-center">
                            <button type="submit" id="submit" class="btn button-style" style="width: {{$button->btnWidth}}px; height: {{$button->btnHeight}}px; border-color: {{$button->borderColor}}; background-color: {{$button->backgroundColor}}; font-family: {{$font->allFontFamily}}">Submit</button>
                        </div>
                        @else
                        <div class="w-100 text-center">
                            <button type="submit" id="submit" class="btn button-style">Submit</button>
                        </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>
</div>

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
    $(document).ready(function() {
        $('#success').hide();
        //function call
        getParamValue();

        const param1 = getParamValue('param1');
       // console.log(param1);
        $('#user_id').val(param1);

        $("#appointmentForm").submit(function(event) {
            const values = $('#appointmentForm').serialize();
            const url  = $(this).attr("action");
            $.ajax({
                type      : 'POST', //Method type
                url       :  url, //Your form processing file URL
                data      :  values,
                dataType  : 'json',
                beforeSend:function()
                {
                    $('#submit').attr('disabled', 'disabled');
                    $('#submit').val('Submitting...');
                },
                success: function (data) {
                    // console.log(data);
                    if (data.success !== 'true') { //If fails
                        if (data.message) {
                            $('.throw_error').fadeIn(1000).html(data.message); //Throw relevant error
                        }
                    }
                    else {
                        $('#appointmentForm')[0].reset();
                        $('#submit').attr('disabled', false);
                        $('#submit').val('Submit');
                        $('#success').show();
                        $('#success').fadeIn(200).append('<span>' + data.message + '</span>'); //If successful, than throw a success message
                    }
                    // if(data.success == 'true'){
                    //     window.location = 'appointmentForm';
                    // }
                }
            });
            event.preventDefault(); //Prevent the default submit
        });
    })
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>

