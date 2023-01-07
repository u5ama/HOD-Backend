@extends('admin.layout')

@section('title', 'CSM Template')

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-default">
                <form class="box-body validate-me" action="{{url('/admin/csm-update', $csm_id)}}" method="POST" enctype="multipart/form-data" id="csmCreate">
                    @csrf
                    <div class="col-sm-12 input-form">
                        <h3 class="box-title">
                            Edit CSM
                        </h3>
                        <div class="col-sm-12">
                            <div class="col-sm-8">
                                <div class="col-sm-6 input-field">
                                    <label>Name</label>
                                    <input type="text" class="form-control" id="title" name="user_name" value="{{$records['name']}}" data-required="true">
                                    <span class="help-block hide-me"><small></small></span>
                                </div>

                                <div class="col-sm-6 input-field">
                                    <label>Email</label>
                                    <input type="email" class="form-control" id="title" name="email" value="{{$records['email']}}" data-required="true">
                                    <span class="help-block hide-me"><small></small></span>
                                </div>

                                <div class="col-sm-6 input-field">
                                    <label>Phone</label>
                                    <input type="tel" class="form-control" id="title" name="phone_number" value="{{$records['phone_number']}}" data-required="true">
                                    <span class="help-block hide-me"><small></small></span>
                                </div>

                                <div class="col-sm-6 input-field">
                                    <label for="user">Select User</label>
                                    <select name="select_user" id="user" class="form-control select2" data-selected-target="" value="{{$records['selected_user_id']}}">
                                        <option value="" selected disabled>Please select user</option>
                                        @foreach($users as $user)
                                            <option value="{{$user->id}}" {{ $records['selected_user_id'] == $user->id ? 'selected' : ''}}>{{$user->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <br><br>
                                <div class="col-sm-4">
                                    <div class="profile-info">
                                        <div class="add-praticelogo logo-image-container" id="logo-image-container">
                                            <img src="{{ asset('public/images/icons/right-arrow.png') }}">
                                            <a id="logo" href="javascript:void(0);">
                                                <label>
                                                    Add Upload Thumbnail
                                                </label>
                                            </a>

                                            <div class="attachment_container">
                                                <input type="file" id="add_logo_image" name="user_picture">
                                            </div>

                                            <div class="limit_exceeded_error_msg_container hide" style="margin-top:10px; margin-bottom: 15px;padding: 10px 5px 10px 10px ">
                                                <span class="remove_limit_exceeded_error"><i class="fa fa-times" aria-hidden="true"></i></span>
                                                <span class="limit_exceeded_error_msg"></span>
                                            </div>
                                            @if(!empty($records['image']))
                                                <div class="attached_images_container p-l-image">
                                                    <img class="img-responsive no-image" src="{{ $records['image'] }}">
                                                </div>
                                                @else
                                                <div class="attached_images_container p-l-image">
                                                    <img class="img-responsive no-image" src="{{ asset('public/images/no-image.png') }}">
                                                    <label>No Image</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div id="saveActions" class="form-group" style="margin-left: 5%;">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-success">
                                    <span class="fa fa-save" aria-hidden="true"></span> &nbsp;<span>Save</span>
                                </button>
                            </div>
                            <a href="{{ route('csm') }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbspCancel</a>
                        </div>
                        <span class="help-block hide-me"><strong>Required fields must be filled.</strong></span>
                    </div><!-- /.box-footer-->
                </form>
            </div>
        </div>
    </div>
    </div>

@endsection
@section('after_styles')
    <style>
        .plan-block .btn-group
        {
            width: 100%;
        }
        .plan-block .multiselect.dropdown-toggle {
            width: 100%;
            text-align: left;
            height: 38px;
            opacity: 1 !important;
            background: #ffffff;
        }
        .plan-block .multiselect.dropdown-toggle .caret
        {
            float: right;
            margin-top: 8px;
        }

        .box.box-default
        {
            border: none;
        }
        /*Select 2*/
        .select2-container .select2-choice {
            background-image: none !important;
            border: none !important;
            height: auto !important;
            padding: 0px !important;
            line-height: 22px !important;
            background-color: transparent !important;
            box-shadow: none !important;
        }
        .select2-container .select2-choice .select2-arrow {
            background-image: none !important;
            background: transparent;
            border: none;
            width: 14px;
            top: -2px;
        }
        .select2-container .select2-container-multi.form-control {
            height: auto;
        }
        .select2-results .select2-highlighted {
            color: #262626;
            background-color:#f0f0f0;
        }
        .select2-drop-active {
            border: 1px solid #e3e3e3 !important;
            padding-top: 5px;
        }
        .select2-search input {
            border: 1px solid rgba(120, 130, 140, 0.13);
        }
        .select2-container-multi {
            width: 100%;
        }
        .select2-container-multi .select2-choices {
            border: 1px solid !important;
            box-shadow: none !important;
            background-image: none !important;
            border-radius: 0px !important;
            min-height: 38px;
        }
        .select2-container-multi .select2-choices .select2-search-choice {
            padding: 4px 7px 4px 18px;
            margin: 5px 0 3px 5px;
            color: #555555;
            background: #f5f5f5;
            border-color: rgba(120, 130, 140, 0.13);
            -webkit-box-shadow: none;
            box-shadow: none;
        }
        .select2-container-multi .select2-choices .select2-search-field input {
            padding: 7px 7px 7px 10px;
            font-family: inherit;
        }
        .box {
            background: none !important;
        }
        .input-form
        {
            margin-bottom: 20px;
            background: #ffffff;
            padding-bottom: 10px;
        }
        .input-form .box-title
        {
            padding-left: 40px;
            padding-bottom: 20px;
        }
        .add-praticelogo {
            margin: 0;
        }
        #logo label {
            cursor: pointer;
            display: inline-block;
            max-width: 100%;
            margin-bottom: 5px;
        }

        .profile-info .p-l-image {
            border: 1px solid #ddd;
            text-align: center;
            width: 200px;
            margin: 10px 0;
            height: 140px;
        }
        .profile-info .p-l-image .no-image
        {
            padding: 22px 0;
        }
        .logo-image-container div.show-image {
            background: none;
            border-radius: 4px;
            overflow: hidden;
            max-width: 160px;
            max-height: 140px;
            margin-left: 0;
            position: relative;
            /* margin: 5px; */
            display: inline-block;
        }
        #add_video_file_demo, #add_image_file, #add_video_file, #add_logo, #add_logo_image {
            display: none;
        }
        .logo-image-container div.show-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .profile-info .p-l-image img {
            margin: auto;
        }
        div.show-image span.remove_image {
            top: 4px;
            right: 4px;
        }
        .attached_images_container .remove_image {
            display: none !important;
        }
        div.show-image span {
            position: absolute;
            display: none;
            float: right;
            border-radius: 81px;
            width: 16px;
            text-align: center;
            height: 16px;
            font-size: 13px;
            background: #FFFFFF;
            line-height: 1.2;
            cursor: pointer;
        }
        .campaign-steps
        {
            display: none;
            margin-top: 5px;
        }
        .campaign-steps span, .campaign-steps span a
        {
            /*color: #3899ec;*/
            font-size: 16px;
            margin-right: 20px;
            color: #B0D5ED;
        }
        .campaign-steps span a
        {
            cursor: pointer;
        }
        .campaign-steps span .active
        {
            cursor: default;
        }
        .campaign-steps span .active, .campaign-steps span a:hover
        {
            color: #03A9F4;
            /*color: #3D4A9E;*/
        }
        .steps-nav
        {
            margin-bottom: 20px;
            background: #fff;
            padding: 15px;
        }

        .next-action
        {
            color: #fff;
            background-color: #03A9F4 !important;
            border-color: #0697d9 !important;
            float: right;
            padding-right: 40px;
            padding-left: 40px;
        }

        .save-action
        {
            background-color: #fff !important;
            float: right;
            padding-right: 25px;
            padding-left: 25px;
            color: #20a0ff !important;
            border-color: #20a0ff !important;
            margin-right: 20px;
        }
    </style>

    <link type="text/css" rel="stylesheet" href="{{ asset('public/plugins/bootstrap-multiselect/bootstrap-multiselect.css?ver=') }}" />
@endsection

@section('after_scripts')
    <script src="{{ asset('public/plugins/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
    {{--<script src="{{ asset('public/js/admin/template-manager.js') }}"></script>--}}
    <script>
        function setupReader(file,preview) {
            var reader  = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;

            };

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = "";
            }
        }
        $(function () {
            $(".select2").select2();
            window.attachedLogoArray = [];
            $(document).on('change',"#add_logo_image",function (e){
                var imagePicker = $("#add_logo_image");
                var attachedImages= $('.logo-image-container .attached_images_container .show-image');

                var fileUploadStatus = false;
                var NumOfAttachedImages = attachedImages.length;

                var limitsArray=[];

                var files  = document.querySelector("#add_logo_image").files;

                for (var y = 0; y < files.length; y++) {
                    var file    = files[y];
                    var fileType=file.type;
                    var fileSize=file.size;

                    var validImageTypes=['image/png','image/jpeg','image/jpg'];
                    var checkFileType=$.inArray( fileType, validImageTypes ) ;
                    //var res = fileType.match(/image\.*/i);
                    if(checkFileType == -1){
                        $('.logo-image-container .limit_exceeded_error_msg').text("File format is invalid. Please upload valid image formats like <jpg,png>.");
                        $('.logo-image-container .limit_exceeded_error_msg_container').removeClass('hide');

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

                    var validImageTypes=['image/png','image/jpeg','image/jpg'];
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

                    setupReader(file,preview);

                    window.attachedLogoArray[0] = file;

                    fileUploadStatus = true;
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
        });
    </script>
    <script>
        $(document).on('click',"#logo",function (e) {
            $("#add_logo_image").click();
        });
        $("#csmCreate").submit(function(event) {
            //  const values = $('#csmCreate').serialize();
            const url  = $(this).attr("action");

            var avatar = [];
            if(window.attachedLogoArray.length !=0){
                avatar = window.attachedLogoArray;
            }
            var user_name = $("input[name='user_name']").val();
            var email = $("input[name='email']").val();
            var phone_number = $("input[name='phone_number']").val();
            var selected_user = $("input[name='select_user']").val();

            var formData = new FormData();

            formData.append('user_name', user_name);
            formData.append('email', email);
            formData.append('phone_number', phone_number);
            formData.append('select_user', selected_user);

            $.each(avatar, function(i, obj) {
                formData.append('user_image['+i+']' , obj);
            });

            showPreloader();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                url: url,
                type: "POST",
                contentType: false,
                cache: false,
                processData: false,
                data: formData,
            }).done(function (result) {
                // console.log(result);
                hidePreloader();
                if (result.success == 'true') {
                    swal({
                        title: "Success!",
                        text: 'CSM updated Successfully!',
                        type: 'success'
                    }, function () {
                        showPreloader();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: 'Error in updated CSM',
                        type: 'error'
                    }, function () {
                    });
                }
            });
            event.preventDefault(); //Prevent the default submit
        });
    </script>
    <script src="{{ asset('public/admin/task/custom-validation.js') }}"></script>
@endsection
