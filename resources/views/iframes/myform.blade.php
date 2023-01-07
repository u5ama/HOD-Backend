<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta Declarations -->
    <meta charset="utf-8">
    <title>Heroes of Digital</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">
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
        input {
            outline: none;
            border: none;
        }

        select {
            outline: none;
            border: none;
        }

        input[type="number"] {
            -moz-appearance: textfield;
            appearance: none;
            -webkit-appearance: none;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
        }

        textarea {
            outline: none;
            border: none;
        }

        textarea:focus, input:focus, select:focus {
            border-color: transparent !important;
        }

        input:focus::-webkit-input-placeholder { color:transparent; }
        input:focus:-moz-placeholder { color:transparent; }
        input:focus::-moz-placeholder { color:transparent; }
        input:focus:-ms-input-placeholder { color:transparent; }

        select:focus::-webkit-input-placeholder { color:transparent; }
        select:focus:-moz-placeholder { color:transparent; }
        select:focus::-moz-placeholder { color:transparent; }
        select:focus:-ms-input-placeholder { color:transparent; }

        textarea:focus::-webkit-input-placeholder { color:transparent; }
        textarea:focus:-moz-placeholder { color:transparent; }
        textarea:focus::-moz-placeholder { color:transparent; }
        textarea:focus:-ms-input-placeholder { color:transparent; }

        input::-webkit-input-placeholder { color: #999999;}
        input:-moz-placeholder { color: #999999;}
        input::-moz-placeholder { color: #999999;}
        input:-ms-input-placeholder { color: #999999;}

        select::-webkit-input-placeholder { color: #999999;}
        select:-moz-placeholder { color: #999999;}
        select::-moz-placeholder { color: #999999;}
        select:-ms-input-placeholder { color: #999999;}

        textarea::-webkit-input-placeholder { color: #999999;}
        textarea:-moz-placeholder { color: #999999;}
        textarea::-moz-placeholder { color: #999999;}
        textarea:-ms-input-placeholder { color: #999999;}

        /*---------------------------------------------*/
        button {
            outline: none !important;
            border: none;
            background: transparent;
        }

        button:hover {
            cursor: pointer;
        }

        iframe {
            border: none !important;
        }


        /*---------------------------------------------*/
        .container {
            max-width: 1200px;
        }

        /*//////////////////////////////////////////////////////////////////
        [ Contact ]*/

        .container-contact100 {
            width: 100%;
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: #e6e6e6;

        }

        .wrap-contact100 {
            width: 550px;
            background: transparent;
            padding: 30px 0 80px 0;
        }


        /*==================================================================
        [ Form ]*/

        .contact100-form {
            width: 100%;
        }

        .contact100-form-title {
            display: block;
            font-family: Oswald-Medium;
            font-size: 30px;
            color: #43383e;
            line-height: 1.2;
            text-align: left;
            padding-bottom: 35px;
        }



        /*------------------------------------------------------------------
        [ Input ]*/

        .wrap-input100 {
            width: 100%;
            position: relative;
            background-color: #fff;
            margin-bottom: 17px;
        }

        .label-input100 {
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 58px;
            height: 62px;
            position: absolute;
            top: 0;
            left: 0;
            cursor: pointer;
            font-size: 18px;
            color: #999999;
        }

        .input100 {
            display: block;
            width: 100%;
            background: transparent;
            font-family: Oswald-Medium;
            font-size: 15px;
            color: #43383e;
            line-height: 1.2;
            padding: 0 5px;
        }


        /*---------------------------------------------*/
        input.input100 {
            height: 62px;
            padding: 0 20px 0 58px;
        }

        select.input100 {
            height: 62px;
            padding: 0 20px 0 58px;
        }

        textarea.input100 {
            min-height: 199px;
            padding: 19px 20px 0 23px;
        }


        /*==================================================================
        [ Restyle Checkbox ]*/

        .contact100-form-checkbox {
            padding-top: 12px;
            padding-bottom: 20px;
        }

        .input-checkbox100 {
            display: none;
        }

        .label-checkbox100 {
            display: block;
            position: relative;
            padding-left: 32px;
            cursor: pointer;
            font-family: Oswald-Regular;
            font-size: 15px;
            color: #43383e;
            line-height: 1.2;
        }

        .label-checkbox100::before {
            content: "\f00c";
            font-family: FontAwesome;
            font-size: 15px;
            color: transparent;
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            justify-content: center;
            align-items: center;
            position: absolute;
            width: 22px;
            height: 22px;
            border-radius: 2px;
            background: #fff;
            left: 0;
            top: 50%;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            -o-transform: translateY(-50%);
            transform: translateY(-50%);
        }

        .input-checkbox100:checked + .label-checkbox100::before {
            color: #555555;
        }
        textarea.input100 {
            min-height: 150px;
            padding: 21px 20px 0 58px;
        }

        /*------------------------------------------------------------------
        [ Button ]*/
        .container-contact100-form-btn {
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            flex-wrap: wrap;
            padding-top: 13px;
        }

        .wrap-contact100-form-btn {
            display: block;
            position: relative;
            z-index: 1;
            border-radius: 31px;
            overflow: hidden;
        }

        .contact100-form-bgbtn {
            position: absolute;
            z-index: -1;
            width: 300%;
            height: 100%;
            background: #F1605B;
            top: 0;
            left: -100%;

            -webkit-transition: all 0.4s;
            -o-transition: all 0.4s;
            -moz-transition: all 0.4s;
            transition: all 0.4s;
        }

        .contact100-form-btn {
            display: -webkit-box;
            display: -webkit-flex;
            display: -moz-box;
            display: -ms-flexbox;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 20px;
            min-width: 160px;
            height: 62px;

            font-family: Oswald-Medium;
            font-size: 16px;
            color: #fff;
            line-height: 1.2;
        }
        .contact100-form-title{
            display: block;
            font-family: Oswald-Medium;

            line-height: 1.2;
            text-align: center;
            padding-bottom: 20px;
        }
        .wrap-contact100-form-btn:hover{
            transform: translate3d(0, -1px, 0);
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.4);
        }

        .wrap-contact100-form-btn:hover .contact100-form-bgbtn {
            left: 0;
        }

        /*------------------------------------------------------------------
        [ Responsive ]*/



        /*------------------------------------------------------------------
        [ Alert validate ]*/

        .validate-input {
            position: relative;
        }

        .alert-validate::before {
            content: attr(data-validate);
            position: absolute;
            max-width: 70%;
            background-color: #fff;
            border: 1px solid #c80000;
            border-radius: 2px;
            padding: 4px 25px 4px 10px;
            top: 50%;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            -o-transform: translateY(-50%);
            transform: translateY(-50%);
            right: 2px;
            pointer-events: none;

            font-family: Oswald-Regular;
            color: #c80000;
            font-size: 13px;
            line-height: 1.4;
            text-align: left;

            visibility: hidden;
            opacity: 0;

            -webkit-transition: opacity 0.4s;
            -o-transition: opacity 0.4s;
            -moz-transition: opacity 0.4s;
            transition: opacity 0.4s;
        }

        .alert-validate::after {
            content: "\f06a";
            font-family: FontAwesome;
            display: block;
            position: absolute;
            color: #c80000;
            font-size: 16px;
            top: 50%;
            -webkit-transform: translateY(-50%);
            -moz-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            -o-transform: translateY(-50%);
            transform: translateY(-50%);
            right: 8px;
        }

        .alert-validate:hover:before {
            visibility: visible;
            opacity: 1;
        }

        @media (max-width: 992px) {
            .alert-validate::before {
                visibility: visible;
                opacity: 1;
            }
        }
    </style>
</head>
<body data-spy="scroll">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{url('/api/crm-add-customer')}}" method="POST" enctype="multipart/form-data" id="cusForm" data-parsley-validate="true" autocomplete="off">
                @csrf
                <input type="hidden" name="user_id" id="user_id" value="">
                <input type="hidden" name="param" id="param" value="lead">
                <div class="container-contact100">
                    <span class="throw_error"></span>
                        <div class="alert alert-success" role="alert" id="success" style="width: 75%">
                    </div>
                    @if(!empty($form) && !empty($font))
                    <div class="wrap-contact100">
                        @if(!empty($head) && !empty($font))
                            <span class="contact100-form-title" style="font-size: {{ $head->headFontSize}}; color: {{$head->headingColor}}; font-family: {{$font->allFontFamily}}!important;">
                              {{$head->headingText}}
                            </span>
                        @else
                            <span class="contact100-form-title">
                              Customer Form
                            </span>
                        @endif
                        <div class="wrap-input100 validate-input" data-validate="First Name is required">
                            <input class="input100" type="text" id="firstName" name="firstName" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}!important;" aria-describedby="emailHelp" placeholder="Enter First Name" autocomplete="false">
                            <label class="label-input100" for="name" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                <span class="lnr lnr-user"></span>
                            </label>
                        </div>

                            @if(empty($lastname))
                                <div class="wrap-input100 validate-input" data-validate="Last Name is required">
                                    <input class="input100" type="text" id="lastName" name="lastName" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}};font-family: {{$font->allFontFamily}}!important;" aria-describedby="emailHelp" placeholder="Enter Last Name" autocomplete="false">
                                    <label class="label-input100" for="lastName" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                        <span class="lnr lnr-user"></span>
                                    </label>
                                </div>
                            @endif
                        <div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
                            <input class="input100" type="email"  id="email" name="email" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}!important;" aria-describedby="emailHelp" placeholder="Enter email" autocomplete="false" required data-parsley-type="email" data-parsley-trigger="keyup">
                            <label class="label-input100" for="email" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                <span class="lnr lnr-envelope"></span>
                            </label>
                        </div>

                        <div class="wrap-input100 validate-input" data-validate = "Phone is required">
                            <input class="input100" type="number" name="phoneNumber" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}!important;" id="phone" placeholder="Enter Phone" autocomplete="false" required data-parsley-length="[7,14]" data-parsley-trigger="keyup">
                            <label class="label-input100" for="phone" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                <span class="lnr lnr-phone-handset"></span>
                            </label>
                        </div>
                            @if(empty($comment))
                                <div class="wrap-input100 validate-input" data-validate="Comment is required">
                                    <textarea class="input100" id="cusComment" name="cusComment" placeholder="Your Comment" style="font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; font-family: {{$font->allFontFamily}}!important;"></textarea>
                                    <label class="label-input100" for="cusComment" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                        <span class="lnr lnr-keyboard"></span>
                                    </label>
                                </div>
                            @endif

                            @if(!empty($fields))
                                @foreach($fields as $field)
                                 <div class="wrap-input100 validate-input" data-validate = "Comment is required">
                                    <input class="input100" id="cusComment" type="{{$field->field_type}}" name="{{$field->field_name}}" placeholder="{{$field->field_placeholder}}" style="width:{{$form->width}}%; height: {{$form->height}}px; font-size: {{$form->fontSize}}px; color: {{$form->fontColor}}; ">
                                    <label class="label-input100" for="cusComment" style="color:{{$form->labelColor}}; font-size: {{$form->fontSize}}px;">
                                        <span class="{{$field->label}}"></span>
                                    </label>
                                </div>
                                @endforeach
                            @endif
                        @else
                            <div class="wrap-contact100">
                                <div class="wrap-input100 validate-input" data-validate="First Name is required">
                                    <input class="input100" type="text" id="firstName" name="firstName" aria-describedby="emailHelp" placeholder="Enter First Name" autocomplete="false">
                                    <label class="label-input100" for="name">
                                        <span class="lnr lnr-user"></span>
                                    </label>
                                </div>
                                @if(empty($lastname))
                                    <div class="wrap-input100 validate-input" data-validate="Last Name is required">
                                        <input class="input100" type="text" id="lastName" name="lastName" aria-describedby="emailHelp" placeholder="Enter Last Name" autocomplete="false">
                                        <label class="label-input100" for="lastName">
                                            <span class="lnr lnr-user"></span>
                                        </label>
                                    </div>
                                @endif
                                <div class="wrap-input100 validate-input" data-validate = "Valid email is required: ex@abc.xyz">
                                    <input class="input100" type="email"  id="email" name="email" aria-describedby="emailHelp" placeholder="Enter email" autocomplete="false" required data-parsley-type="email" data-parsley-trigger="keyup">
                                    <label class="label-input100" for="email">
                                        <span class="lnr lnr-envelope"></span>
                                    </label>
                                </div>

                                <div class="wrap-input100 validate-input" data-validate = "Phone is required">
                                    <input class="input100" type="number" name="phoneNumber" id="phone" placeholder="Enter Phone" autocomplete="false" required data-parsley-length="[7,14]" data-parsley-trigger="keyup">
                                    <label class="label-input100" for="phone">
                                        <span class="lnr lnr-phone-handset"></span>
                                    </label>
                                </div>
                                @if(empty($comment))
                                    <div class="wrap-input100 validate-input" data-validate="Comment is required">
                                        <textarea class="input100" id="cusComment" name="cusComment" placeholder="Your Comment"></textarea>
                                        <label class="label-input100" for="cusComment">
                                            <span class="lnr lnr-keyboard"></span>
                                        </label>
                                    </div>
                                @endif
                                @if(!empty($fields))
                                    @foreach($fields as $field)
                                        <div class="wrap-input100 validate-input" data-validate = "Comment is required">
                                            <input class="input100" id="comment" type="{{$field->field_type}}" name="{{$field->field_name}}" placeholder="{{$field->field_placeholder}}" autocomplete="false">
                                            <label class="label-input100" for="comment">
                                                <span class="{{$field->label}}"></span>
                                            </label>
                                        </div>
                                    @endforeach
                                @endif
                        @endif

                        @if(!empty($button) && !empty($font))
                        <div class="container-contact100-form-btn justify-content-center align-items-center">
                            <div class="wrap-contact100-form-btn">
                                <div class="contact100-form-bgbtn"></div>
                                <button type="submit" class="contact100-form-btn" style="width: {{$button->btnWidth}}px; height: {{$button->btnHeight}}px; border-color: {{$button->borderColor}}; background-color: {{$button->backgroundColor}}; font-family: {{$font->allFontFamily}}!important;">
                                    Submit
                                </button>
                            </div>
                        </div>
                        @else
                            <div class="container-contact100-form-btn justify-content-center align-items-center">
                                <div class="wrap-contact100-form-btn">
                                    <div class="contact100-form-bgbtn"></div>
                                    <button type="submit" class="contact100-form-btn">
                                        Submit
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                </div>
            </form>
            <div id="dropDownSelect1"></div>
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
        //parameter
        const param1 = getParamValue('param1');
        // console.log(param1);
        $('#user_id').val(param1);

        $("#cusForm").submit(function(event) {
            const values = $('#cusForm').serialize();
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
                    // console.log(data._metadata.outcome);
                    if (data._metadata.outcome !== 'SUCCESS') { //If fails
                        if (data.errors.name) {
                            $('.throw_error').fadeIn(1000).html(data.errors.name); //Throw relevant error
                        }
                    }
                    else {
                        $('#cusForm')[0].reset();
                        $('#submit').attr('disabled', false);
                        $('#submit').val('Submit');
                        $('#success').show();
                        $('#success').fadeIn(200).append('<span>' + data._metadata.message + '</span>'); //If successful, than throw a success message
                    }
                }
            });
            event.preventDefault(); //Prevent the default submit
        });
    })
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script><script src="https://code.jquery.com/jquery-3.5.1.js" integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc=" crossorigin="anonymous"></script>
</body>
</html>
