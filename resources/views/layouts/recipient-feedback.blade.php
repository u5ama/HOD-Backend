<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('public/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link type="text/css" href="{{ asset('public/css/reviews-recipient/style.css') }}" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css?family=Muli&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('public/images/favicon.png') }}" />

    <title>Heroes of Digital</title>

    <style>
        .pop-box-header h3 {
            word-break: unset !important;
            word-wrap: break-word !important;
        }
    </style>
</head>
<body>

<div class="fb-wrapper">
    <div class="container">
        <div class="row">

        <div class="popup-box">
                <div class="pop-box-header">
                    <div class="popup-back-btn hide-popup-back-btn">
                        <img src="{{ asset('public/images/feedback-review/fb-back.png') }}" alt="Like">
                    </div>

                    <h3 class="business-title">{{ str_replace('+', ' ', $name) }}</h3>

                    @if(!empty($message))
                        <h4 class="review-content">{{ $message }}</h4>
                    @else
                        <h4 class="review-content">Thumbs up if you were happy with our service. <br> Thumbs down if we didn’t meet your expectations.</h4>
                    @endif
                </div>
                @if(empty($message))
                <div class="pop-box-body Interactive-box">
                    <div class="row">


                            <div class="col text-right">
                                <a href="javascript:void(0)" class="thumb-action" data-thumb-action="up">
                                    <img src="{{ asset('public/images/feedback-review/like.png') }}" alt="Like">
                                </a>
                            </div>
                            <div class="col">
                                <a href="javascript:void(0)" class="thumb-action" data-thumb-action="down">
                                    <img src="{{ asset('public/images/feedback-review/dislike.png') }}" alt="Dislike">
                                </a>
                            </div>


                    </div>
                </div>
                @endif
                <div class="alert alert-danger" style="display: none;margin-top: 10px;text-align: center;"></div>
                <img class="loader" style="display: none;" src="{{ asset('public/images/loader.gif') }}">

                <div class="pop-box-footer">
                    <label>Powered by <a href="javascript:void(0);"><span>Heroes of Digital</span></a></label>
                </div>

            </div>

    </div>
    </div>
</div>

{{ csrf_field() }}
<input type="hidden" id="hfBaseUrl" value="{{ URL('/') }}" />
<input type="hidden" id="email" value="{{ $email }}" />
<input type="hidden" id="name" value="{{ $name }}" />
<input type="hidden" id="secret" value="{{ $secret }}" />
<input type="hidden" id="reviewID" value="{{ $reviewID }}" />
{{--<input type="hidden" id="bussinessId" value="{{ $id }}" />--}}

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="{{ asset('public/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('public/js/recipient/popper.min.js') }}"></script>
{{--<script src="{{ asset('public/js/bootstrap-4.min.js') }}"></script>--}}
<script src="{{ asset('public/plugins/bootstrap/js/bootstrap.min.js') }}"></script>

@if(empty($pageType))
<script src="{{ asset('public/js/recipient/business-review.js') }}"></script>
@endif

</body>
</html>
