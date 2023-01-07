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
                        Deleted
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
                                                       placeholder="Search a user/practice name/email/phone"/>
                                            </div>
                                        </div>

                                        {{--<div class="col-sm-3 col-md-3">
                                            <div class="form-group sources-list" style="width: 100%;">
                                                <select
                                                    style="height: 38px;width: 100%;padding-left: 10px;padding-right: 15px;">
                                                    <option>Select Status</option>
                                                    <option>Free Trial</option>
                                                    <option>Paid</option>
                                                </select>
                                            </div>
                                        </div>--}}
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
                                        {{--<th>Last Name</th>--}}
                                        <th>Business Name</th>
                                        <th>Email Address</th>
                                        <th>Phone</th>
                                        <th>Website</th>
                                        {{-- <th>Created</th>--}}
                                        <th>Account Status</th>
                                       {{-- <th style="width: 120px;">Trial</th>
                                        <th >Action</th>--}}
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($records))
                                        @foreach($records as $record)

                                            <tr class="">
                                                <td>
                                                    {{ $record['first_name'] }}
                                                </td>

                                                {{--<td>
                                                    {{ $record['last_name'] }}
                                                </td>--}}

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
                                                    {{--                                                    <a data-user-email="{{ $record['email'] }}" style="color: #3c8dbc;" href="javascript:void(0)" class="btn btn-sm btn-link show-user-profile" data-target-id="9"><i class="fa fa-user"></i> Delete</a>--}}
                                                    <a style="padding-left: 0px;"
                                                       data-user-email="{{ $record['email'] }}"
                                                       href="javascript:void(0)" class="btn btn-sm btn-link remove-me"
                                                       data-target-id="{{ $record['id'] }}"><i class="fa fa-trash"></i>
                                                        Delete</a>
                                                    {{--                                                    <a class="inactive change-status" data-target-id="4" data-status="1">Drafts</a>--}}
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

                                                {{--<td>
                                                    {{ $record['created_at'] }}
                                                </td>--}}
                                                {{--
                                                                                                <td>
                                                                                                    Free Trial <br/>
                                                                                                </td>--}}
                                                <td class="status">
                                                    @if($record['account_status'] == 'deleted' && $record['delete_by'] == 1)
                                                        <span data-user-email="{{ $record['email'] }}"
                                                              class="inactive change-status"
                                                              data-target-id="{{ $record['id'] }}"
                                                              data-status="">Drafts</span>
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

                                               {{-- <td class="trial">
                                                    @if(!empty($record['user_trial']))
                                                        {{$record['user_trial']}}
                                                    @else
                                                        <span class="inactive change-trial" data-id="{{ $record['id'] }}">Add trial</span>
                                                    @endif
                                                    --}}{{--                                                    @if($record['user_trial'] ==  date('m/d/yy'))--}}{{--
                                                    --}}{{--                                                        <span class="inactive end-trial" data-id="{{ $record['id'] }}">End trial</span>--}}{{--
                                                    --}}{{--                                                    @endif--}}{{--
                                                </td>
                                                <td>
                                                    <a style="padding-left: 0px;"
                                                       data-user-email="{{ $record['email'] }}"
                                                       href="{{route('userEdit', $record['id'])}}" class="btn btn-sm btn-link"
                                                       data-target-id="{{ $record['id'] }}"><i class="fa fa-pencil"></i>
                                                        Edit</a>
                                                </td>--}}
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
    {{--<script src="{{ asset('public/admin/adminlte/plugins/datatables/jquery.dataTables.js') }}"></script>--}}

    {{--<script src="{{ asset('public/admin/adminlte/plugins/datatables/dataTables.bootstrap.js') }}"></script>--}}

    {{--<script src="{{ asset('public/admin/adminlte/plugins/datatables/dataTables.initiate.js') }}"></script>--}}
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
        });
@endsection
