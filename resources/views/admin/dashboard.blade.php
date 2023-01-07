@extends('admin.layout')

@section('title', $pageTitle)

@section('header')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="{{ route('adminDashboard') }}">{{ appName() }}</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>
@endsection

@section('content')
    <div class="row">
        <!-- THE ACTUAL CONTENT -->
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title m-t-10">
                        Dashboard
                    </h3>
                </div>

                <div class="box-body">
                    <div class="row" style="margin-bottom: 40px;">
                        <div class="col-xs-2 review-status-box" style="padding-left: 15px;">
                            <div class="listing-box">
                                <h3>0</h3>
                                <label>Users</label>
                            </div>
                        </div>
                    </div>

                    <div id="crudTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-table-head">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                            <div class="form-group head-search-review">
                                                <input id="search-table" type="text" class="form-control"
                                                       placeholder="Search a user/business name/email/phone"/>
                                            </div>
                                        </div>
                                        <div class="col-sm-2 col-md-7" style="text-align: right">
                                            <a href="{{ frontUrl().'auth/register?source=admin' }}"><button class="btn btn-primary" style="background-color: #e76461;border-color: #e76461;">Create New User</button></a>
                                        </div>
                                    </div>
                                </div>

                                <table id="taskTable" class="table table-bordered table-striped display dataTable"
                                       role="grid">
                                    <thead>
                                    <tr role="row">
                                        {{--                                        <th>ID</th>--}}
                                        <th>First Name</th>
                                        <th>Business Name</th>
                                        <th>Email Address</th>
                                        <th>Phone</th>
                                        <th>Website</th>
                                        <th>Account Status</th>
                                        <th style="width: 150px;text-align: center;">Trial</th>
                                        <th >Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($records))
                                        @foreach($records as $record)

                                            <tr class="">
                                                <td>
                                                    {{ $record['first_name'] }}
                                                </td>

                                                <td>
                                                    @if(!empty($record['business'][0]['business_name']))
                                                    {{ $record['business'][0]['business_name'] }}
                                                        @else
                                                    @endif
                                                </td>

                                                <td class="status">
                                                    <span class="user-email">{{ $record['email'] }}</span>
                                                    <br/>
                                                    <a data-user-email="{{ $record['email'] }}" style="color: #3c8dbc;"
                                                       href="javascript:void(0)"
                                                       class="btn btn-sm btn-link show-user-profile"><i
                                                            class="fa fa-user"></i> Log in</a>
                                                    <a style="padding-left: 0px;"
                                                       data-user-email="{{ $record['email'] }}"
                                                       href="javascript:void(0)" class="btn btn-sm btn-link remove-me"
                                                       data-target-id="{{ $record['id'] }}"><i class="fa fa-trash"></i>
                                                        Delete</a>
                                                </td>

                                                <td>
                                                    @if(!empty($record['business'][0]['phone'] ))
                                                        {{ $record['business'][0]['phone'] }}
                                                    @else
                                                    @endif
                                                </td>

                                                <td>
                                                    @if(!empty($record['business'][0]['website'] ))
                                                        {{ $record['business'][0]['website'] }}
                                                    @else
                                                    @endif
                                                </td>
                                                <td class="status">
                                                    @if($record['account_status'] == 'deleted' && $record['delete_by'] == 1)
                                                        <span data-user-email="{{ $record['email'] }}"
                                                              class="inactive change-status"
                                                              data-target-id="{{ $record['id'] }}"
                                                              data-status="">Deactive</span>
                                                    @elseif($record['account_status'] == 'deleted')
                                                        <span data-user-email="{{ $record['email'] }}"
                                                              class="inactive change-status"
                                                              data-target-id="{{ $record['id'] }}" data-status="">Inactive</span>
                                                    @else
                                                        <span data-user-email="{{ $record['email'] }}"
                                                              class="active change-status actstyle"
                                                              data-target-id="{{ $record['id'] }}"
                                                              data-status="deleted">Active</span>
                                                    @endif
                                                </td>

                                                <td class="trial" style="text-align: center">
                                                    @if(!empty($record['user_trial']))
                                                        {{$record['user_trial']}}
                                                    @else
                                                        <span class="inactive change-trial" data-id="{{ $record['id'] }}">Add trial</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a style="padding-left: 0px;"
                                                       data-user-email="{{ $record['email'] }}"
                                                       href="{{route('userEdit', $record['id'])}}" class="btn btn-sm btn-link"
                                                       data-target-id="{{ $record['id'] }}"><i class="fa fa-pencil"></i>
                                                        Edit</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="hodBaseURL" value="{{ frontUrl() }}" />
                </div>
            </div><!-- /.box -->
        </div>
    </div>
{{--  modal--}}
    <div class="modal" tabindex="-1" role="dialog" id="exampleModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Trial End Date</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="userId" id="userId" value=""/>
                    <div class="input-group date" data-provide="datepicker">
                        <input type="text" class="form-control datepicker" id="end-date" required autocomplete="off">
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-th"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary save-trial">Save Trial</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('after_styles')
    <link rel="stylesheet" href="{{ asset('public/admin/adminlte/plugins/datatables/dataTables.bootstrap.css') }}">
    <style>
        .datepicker {
            z-index: 1600 !important; /* has to be larger than 1050 */
        }
    </style>
    <style>
       /* .dataTables_paginate {
            display: none;
        }

        .dataTables_filter {
            display: none;
        }*/
        .dataTables_paginate {
            padding-right: 2%;
            cursor: pointer;
        }
       .actstyle{
           background-color: #e76461 !important;
           border-color: #e76461 !important;
       }
        .dataTables_filter {
            padding-left: 2%;
            cursor: pointer;

        }
       a.paginate_button {
           padding: 1%;
           cursor: pointer;
       }

        .d-table-head {
            padding: 0 25px;
            margin-bottom: 20px;
        }

        .d-table-head .dropdown {
            margin: 0 20px;
        }

        .reviews-panel .d-table-head .dropdown {
            margin: 0 15px;
        }

        .d-table-head .btn-default:active:focus {
            background: #fafafa;
        }

        .d-table-head .btn-default.active, .btn-default:active, .open > .dropdown-toggle.btn-default {
            background: #fafafa;
        }

        .d-table-head .head-search-review .form-control {
            border: 1px solid #a6a7af;
            border-radius: 3px;
            width: 100%;
            display: block;
            height: 38px;
        }

        .form-group.head-search-review {
            position: relative;
            display: block;
            width: 100% !important;

        }

        .head-search-review:before {
            font-family: FontAwesome;
            content: "\f002";
            color: #CFCFD3;
            position: absolute;
            font-size: 16px;
            width: 20px;
            height: 20px;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .review-status-box {
            text-align: center;
            padding-right: 0;
            padding-left: 0;
        }

        .listing-box {
            /* background: #FFFFFF; */
            border: 1px solid #E5E5E5;
            box-sizing: border-box;
            border-radius: 4px;
            padding: 0px 10px;
            height: 90px;
            width: 150px;
            position: relative;
        }

        .review-status-box label {
            color: #000;
            font-weight: 600;
        }

        .review-status-box h3 {
            color: #000;
            font-size: 42px;
            font-weight: 600;
            margin: 0px;
            text-align: left;
        }

        .review-status-box label {
            color: #000;
            font-weight: 600;
            /* text-align: right; */
            display: block;
            position: absolute;
            right: 10px;
            bottom: 0;
        }
       div#taskTable_filter {
           display: none;
       }
       #taskTable .trial .inactive {
           color: #fff;
           background-color: green;
           padding: 2px 15px;
           margin: 0 auto;
           border-radius: 10px;
           cursor: pointer;
       }
    </style>
@endsection

@section('after_scripts')
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/sl-1.3.0/datatables.min.js"></script>
    <script>

        $(function () {
            var table = $('#taskTable').DataTable(
                {
                    // select: false,
                    ordering: true,
                    // Sortable: true
                     paging: true,
                    // "dom": '<"top"i>rt<"bottom"><"clear">'
                     searching: true,
                    // lengthMenu: [ 25, 50, 100, 'All' ],
                   // lengthMenu: [[100, 200, -1], ['100', '200', 'All']],
                    "bLengthChange": false,
                    "language": {
                        "emptyTable": "No data available",
                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                        "infoEmpty": "Showing 0 to 0 of 0 entries",
                        "infoFiltered": "(filtered from _MAX_ total entries)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "_MENU_ records per page",
                        "loadingRecords": "Loading...",
                        "processing": "Processing...",
                        "search": "Search: ",
                        "zeroRecords": "No matching records found",
                        "paginate": {
                            "first": "First",
                            "last": "Last",
                            "next": "Next",
                            "previous": "Previous"
                        },
                        "aria": {
                            "sortAscending": ": activate to sort column ascending",
                            "sortDescending": ": activate to sort column descending"
                        }
                    },
                });

            $(".ordering-date a").click(function () {
                var action = $(this).attr("data-action");

                $(".date-ordering").html($(this).html() + ' <span class="caret"></span>');

                // $(this).remove();

                if (action === 'newest') {
                    $('#t-email-campaigns').DataTable().order([3, 'desc']).draw();
                } else {
                    $('#t-email-campaigns').DataTable().order([3, 'asc']).draw();
                }

            });

            $('#search-table').on('keyup', function () {
                table.search($('#search-table').val()).draw();
            });
            $(document.body).on('click', '.dropdown-menu .checkbox input', function () {
                var column = $(this).closest('ul').attr("data-filter");
                var source = $(this).closest('ul').attr("data-filter-type");
                // var column = 4;
              //  console.log("column " + column);

                serializeData('.' + source, column);
            });

            $(".listing-box h3").html(table.rows().count());

            $(document.body).on('click', '.show-user-profile', function () {
            // $(".show-user-profile").click(function () {
                var siteUrl = $('#hfBaseUrl').val();
                var user = $(this).attr("data-user-email");
                var currentTarget = $(this);

                showPreloader();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "POST",
                    url: siteUrl + "/api/done-me",
                    data: {
                        send: 'super-login',
                        email: user,
                    },
                }).done(function (result) {
                    var json = $.parseJSON(result);
                    var statusCode = json.status_code;
                    var statusMessage = json.status_message;
                    var data = json.data;

                    hidePreloader();

                    var frontBase = $('#hodBaseURL').val();
                    var userToken = data.token;
                    var nRoute = frontBase+'redirect?token='+userToken+'&role=admin';
                    if (statusCode == 200) {
                        window.open(nRoute, '_blank');
                    } else {
                        swal({
                            title: "Error!",
                            text: statusMessage,
                            type: 'error'
                        }, function () {
                        });
                    }
                });
            });
        });
        var currentTarget;
        $(document.body).on('click', '.remove-me', function () {
            var target = $(this).attr('data-target-id');
            currentTarget = $(this);

            var action = $(this).attr("data-action");
            var baseUrl = $('#hfBaseUrl').val();

            var mainModel = $('#main-modal');
            $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
            $(mainModel).removeClass('welcome-process');
            $(mainModel).addClass('modal-user-quit');

            var html = '';

            // console.log("currentTarget");
            // console.log(currentTarget);

            html += '<div class="modal-body"><div class="interface-module" style=""><div class="alert" style="display: none;"></div><div class="remove-business-modal"><div class="remove-action-note"><img src="' + baseUrl + '/public/images/delete-listing.png"> <h3 style="font-size: 22px;margin-bottom: 25px;font-weight: 400;color: #000;">Are you sure you want to remove this Account?</h3>' +
                '<p style="color: #000;font-size: 15px;">Deleting user will not be show in admin panel and user can not access this account and data cannot be roll backed again</p></div></div></div></div>';
            html += '<div class="modal-footer"><button type="button" class="btn btn-default close-modal" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger deleting-processed">Delete</button></div>';

            mainModel.modal('show');
            $(".modal-header").after(html);

            return false;
        });

        $(document.body).on('click', '.change-status', function () {
            var siteUrl = $('#hfBaseUrl').val();
            var template = $(this).attr('data-target-id');
            var status = $(this).attr('data-status');
            var currentTarget = $(this);
            var parentSel = currentTarget.parent('.status');

            var user = $(this).attr("data-user-email");

            showPreloader();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: siteUrl + "/done-me",
                data: {
                    send: 'admin-change-user-account-status',
                    id: template,
                    status: status,
                    email: user
                },
            }).done(function (result) {
                var json = $.parseJSON(result);
                var statusCode = json.status_code;
                var statusMessage = json.status_message;
                var data = json.data;

                if (status == 'deleted') {
                    parentSel.html('<span data-user-email="' + user + '" class="inactive change-status" data-target-id="' + template + '" data-status="">Deactive</span>');
                } else {
                    parentSel.html('<span data-user-email="' + user + '" class="active change-status actstyle" data-target-id="' + template + '" data-status="deleted">Active</span>');
                }


                hidePreloader();

                if (statusCode == 200) {
                    swal({
                        title: "Success!",
                        text: statusMessage,
                        type: 'success'
                    }, function () {
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: statusMessage,
                        type: 'error'
                    }, function () {
                    });
                }
            });
        });

        $(document.body).on('click', '.deleting-processed', function () {
            deleteCampaign(window.currentTarget);
        });

        $(document.body).on('click', '.change-trial', function (e) {
            //get data-id attribute of the clicked element
            var data_id = '';

            if (typeof $(this).data('id') !== 'undefined') {

                data_id = $(this).data('id');
            }

            $('#userId').val(data_id);
            // $('#exampleModal').modal.show();
            $('#exampleModal').modal('toggle');
            initiateDatePicker();
           // console.log( $('.datepicker').datepicker());
        });
        function initiateDatePicker() {
            /*--FOR DATE----*/
            var date = new Date();
            var today = new Date(date.getFullYear(), date.getMonth(), date.getDate());
           // console.log( 'i am here');
            $('.datepicker').datepicker({
                format: 'mm/dd/yyyy',
                todayHighlight: true,
                startDate: today,
                endDate: 0
            });
        }

        $(document.body).on('click', '.save-trial', function(){
            var siteUrl = $('#hfBaseUrl').val();
            var template =  $('#userId').val();
            var endVal = $('#end-date').val();
            var currentTarget = $(this);

            showPreloader();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: siteUrl + "/api/update-trial",
                data: {
                    id: template,
                    endValue: endVal,
                },
            }).done(function (result) {
                hidePreloader();
                if (result.success == 'true') {
                    swal({
                        title: "Success!",
                        text: result.message,
                        type: 'success'
                    }, function () {
                        showPreloader();
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: result.message,
                        type: 'error'
                    }, function () {
                      //  $('#exampleModal').modal('toggle');
                    });
                }
            });
        });

        $(document.body).on('click', '.end-trial', function(){
            var siteUrl = $('#hfBaseUrl').val();
            var template =  $(this).attr('data-id');
            var currentTarget = $(this);

            showPreloader();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: siteUrl + "/api/end-trial",
                data: {
                    id: template,
                    endValue: endVal,
                },
            }).done(function (result) {
                hidePreloader();
                if (result.success == 'true') {
                    swal({
                        title: "Success!",
                        text: result.message,
                        type: 'success'
                    }, function () {
                        $('#exampleModal').modal('toggle');
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: result.message,
                        type: 'error'
                    }, function () {
                      //  $('#exampleModal').modal('toggle');
                    });
                }
            });
        });

        function deleteCampaign(currentTarget) {
            // console.log("currentTarget templat");
            // console.log(currentTarget);
            var siteUrl = $('#hfBaseUrl').val();
            var template = currentTarget.attr('data-target-id');

            // console.log(template);

            showPreloader();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: siteUrl + "/api/done-me",
                data: {
                    send: 'delete-account',
                    id: template,
                },
            }).done(function (result) {
                var json = $.parseJSON(result);
                var statusCode = json.status_code;
                var statusMessage = json.status_message;
                var data = json.data;

                hidePreloader();

                if (statusCode == 200) {
                    $(".close-modal").click();

                    console.log("length ");
                    console.log($("tbody tr").length);
                    swal({
                        title: "Success!",
                        text: statusMessage,
                        type: 'success'
                    }, function () {
                        showPreloader();
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: statusMessage,
                        type: 'error'
                    }, function () {
                    });
                }
            });
        }
    </script>

@endsection
