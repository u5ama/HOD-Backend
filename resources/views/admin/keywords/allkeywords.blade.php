@extends('admin.layout')

@section('title')

@section('header')
    <section class="content-header">
        <ol class="breadcrumb">
            <li><a href="{{ route('keywords') }}">{{ appName() }}</a></li>
            <li class="active">Keywords</li>
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
                        Keywords
                    </h3>
                </div>

                <div class="box-body">
                    <div id="crudTable_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="d-table-head">
                                    <div class="row">
                                        <div class="col-sm-4 col-md-5">
                                        </div>
                                        <div class="col-sm-2 col-md-7" style="text-align: right">
                                            <a href="{{ route('add-keyword') }}"><button class="btn btn-primary" style="background-color: #e76461;border-color: #e76461;">Add Keyword</button></a>
                                        </div>
                                    </div>
                                </div>

                                <table id="taskTable" class="table table-bordered table-striped display dataTable"
                                       role="grid">
                                    <thead>
                                        <tr role="row">
                                            <th>Name</th>
                                            <th>Website</th>
                                            <th>Keywords</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($records))
                                        @foreach($records as $record)
                                            <tr>
                                                <td>{{$record['users'][0]['first_name'] .' '. $record['users'][0]['last_name']}}</td>

                                                <td>{{$record['project_url']}}</td>

                                                @if(count($record['local_keywords'])>0)
                                                    <td>
                                                        @foreach($record['local_keywords'] as $key)
                                                            <span>{{$key['keyword']}}, </span>
                                                        @endforeach
                                                    </td>
                                                         @else
                                                            <td>Null</td>
                                                         @endif
                                                    <td>
                                                        <a style="padding-left: 0px;"
                                                       data-user-email="{{ $record['user_id'] }}"
                                                       href="{{route('keyword/edit', $record['user_id'])}}" class="btn btn-sm btn-link edit-me"
                                                       data-target-id="{{ $record['user_id'] }}"><i class="fa fa-pencil"></i>
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
                    ordering: true,
                    paging: true,
                    searching: true,
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
                serializeData('.' + source, column);
            });

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
                    '<p style="color: #000;font-size: 15px;">Deleting user project from SERanking will not be show in admin panel and user can not see his rankings.</p></div></div></div></div>';
                html += '<div class="modal-footer"><button type="button" class="btn btn-default close-modal" data-dismiss="modal">Cancel</button><button type="button" class="btn btn-danger deleting-processed">Delete</button></div>';

                mainModel.modal('show');
                $(".modal-header").after(html);

                return false;
            });
            $(document.body).on('click', '.deleting-processed', function () {
                deleteCampaign(window.currentTarget);
            });
        });
        function deleteCampaign(currentTarget) {
            var siteUrl = $('#hfBaseUrl').val();
            var template = currentTarget.attr('data-target-id');

            // console.log(template);

            showPreloader();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: siteUrl + "/api/delete-se-project",
                data: {
                    send: 'delete-account',
                    id: template,
                },
            }).done(function (result) {

                hidePreloader();

                if (result.success == true) {
                    swal({
                        title: "Success!",
                        text: 'Project Deleted Successfully!',
                        type: 'success'
                    }, function () {
                        showPreloader();
                        location.reload();
                    });
                } else {
                    swal({
                        title: "Error!",
                        text: 'Error in Deleting project',
                        type: 'error'
                    }, function () {
                    });
                }
            });
        }
    </script>
@endsection
