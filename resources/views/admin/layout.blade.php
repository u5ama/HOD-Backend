<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Admin-Heroes of Digital</title>

    @yield('before_styles')
    <link rel="icon" type="image/x-icon" href="{{ asset('public/images/my-logo.png') }}">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="{{ asset('public/admin/adminlte//bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/icons/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/icons/ionicons/ionicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('public/plugins/custom-select/custom-select.css') }}">

    <link rel="stylesheet" href="{{ asset('public/admin/adminlte//dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('public/admin/adminlte//dist/css/skins/_all-skins.min.css') }}">

    <link rel="stylesheet" href="{{ asset('public/admin/adminlte//plugins/pace/pace.min.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/pnotify/pnotify.custom.min.css') }}">

    <link rel="stylesheet" href="{{ asset('public/css/app/spinners.css') }}">
    <link rel="stylesheet" href="{{ asset('public/admin/base.css') }}">
    <link type="text/css" rel="stylesheet" href="{{ asset('public/plugins/sweetalert/sweetalert.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" integrity="sha512-mSYUmp1HYZDFaVKK//63EcZq4iFWFjxSL+Z3T/aCt4IO9Cejm03q3NKKYN6pFQzY0SBOr8h+eCIAZHPXcpZaNw==" crossorigin="anonymous" />
    @yield('after_styles')

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-blue-light hold-transition sidebar-mini">
<div class="preloader" style="display: none;">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
    </svg>
</div>
<?php
$appName = getDynamicAppName();
?>
    <!-- Site wrapper -->
    <div class="wrapper">

      <header class="main-header">
        <!-- Logo -->
        <a href="{{ route('adminDashboard') }}" class="logo" style="background: white !important;">
          {{--<span class="logo-mini">H</span>
          <span class="logo-lg">{{ $appName }}</span>--}}
            <img src="{{asset('public/images/full-logo.png')}}" alt="" class="img-fluid" style="width: 60%;
    background: transparent;">
        </a>

        <nav class="navbar navbar-static-top" role="navigation" style="background: white !important;">
          <!-- Sidebar toggle button-->
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button" style="color: black !important;">
            <span class="sr-only">Toggle Navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>

          @include('admin.partials.menu')
        </nav>
      </header>

     @if(!isset($showHeader))
        @include('admin.partials.sidebar')
    @endif

      <div class="content-wrapper">
        <section class="content">
          @yield('content')
        </section>
      </div>
    </div>
    @include('admin.partials.footer')

<input type="hidden" id="hfBaseUrl" value="{{ URL('/') }}" />
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input id="adminBaseUrl" type="hidden" value="{{ URL('/admin') }}" />


@include('admin.partials.popup-manager')

@yield('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js" integrity="sha512-T/tUfKSV1bihCnd+MxKD0Hm1uBBroVYBOYSk1knyvQ9VyZJpc/ALb4P0r6ubwVPSGB2GvjeoMAJJImBG12TiaQ==" crossorigin="anonymous"></script>
</body>
</html>
