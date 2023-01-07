@extends('admin.layout')

@section('title', 'Edit Task')

@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1 task-page">
            <form class="validate-me" method="POST" action="{{route('update.user', $userData['id'])}}" accept-charset="UTF-8">
                {{ method_field('PUT') }}
                {{ csrf_field() }}
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Edit Task</h3>
                    </div>

                    <div class="box-body row">
                        <div class="form-group">
                            <div class="col-md-12">
                                @if (session('message'))
                                    <div class="alert {{ (session('messageCode') != 200) ? 'alert-danger' : 'alert-success' }}">
                                        {{ session('message') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="form-group col-md-12 {{ $errors->has($userData['first_name']) ? ' has-error' : '' }}">
                            <label>First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ $userData['first_name'] }}" class="form-control" data-required="true" required>
                            <span class="help-block {{ $errors->has($userData['first_name']) ? ' error' : '' }}">
                                <small>{{ $errors->first($userData['first_name']) }}</small>
                            </span>
                        </div>
                        <div class="form-group col-md-12 {{ $errors->has($userData['last_name']) ? ' has-error' : '' }}">
                            <label>Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ $userData['last_name'] }}" class="form-control" data-required="true" required>
                            <span class="help-block {{ $errors->has($userData['last_name']) ? ' error' : '' }}">
                                <small>{{ $errors->first($userData['last_name']) }}</small>
                            </span>
                        </div>
                        <div class="form-group col-md-12 {{ $errors->has($userData['email']) ? ' has-error' : '' }}">
                            <label>Email</label>
                            <input type="email" id="email" name="email" value="{{ $userData['email'] }}" class="form-control" data-required="true" required>
                            <span class="help-block {{ $errors->has($userData['email']) ? ' error' : '' }}">
                                <small>{{ $errors->first($userData['email']) }}</small>
                            </span>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div id="saveActions" class="form-group">
                            <div class="btn-group">
                                <button type="submit" class="btn btn-success">
                                    <span class="fa fa-save" aria-hidden="true"></span> &nbsp;<span>Save</span>
                                </button>
                            </div>

                            <a href="{{ route('adminDashboard') }}" class="btn btn-default"><span class="fa fa-ban"></span> &nbspCancel</a>
                        </div>
                        <span class="help-block hide-me"><strong>Required fields must be filled.</strong></span>
                    </div><!-- /.box-footer-->
                </div>
            </form>
        </div>
    </div>
@endsection

@section('after_styles')
    {{--    <link rel="stylesheet" href="{{ asset('public/css/mad-validation.css') }}" />--}}
    <link rel="stylesheet" href="{{ asset('public/plugins/summernote/summernote.css') }}" />

    <link rel="stylesheet" href="{{ asset('public/plugins/bootstrap-select/bootstrap-select.css') }}" />
@endsection
@section('after_scripts')

@endsection
