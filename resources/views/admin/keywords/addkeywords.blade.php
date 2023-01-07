@extends('admin.layout')

@section('title')

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
        <div class="col-md-10 col-md-offset-1 task-page">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title m-t-10">
                       Add Keywords
                    </h3>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="user">Select User</label>
                            <select name="select_user" id="user" class="form-control select2" data-selected-target="">
{{--                                <option value="" selected disabled>Please select user</option>--}}
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->email}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <br>
                    <form action="{{url('/api/add-se-project')}}" method="POST" enctype="multipart/form-data" id="projectForm">
                        <input type="hidden" name="user_id" id="user_id">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="business_name">Business Name</label>
                                <input type="text" class="form-control" name="business_name" id="business_name" autocomplete="off" readonly data-required="true">
                                <span class="help-block hide-me"><small></small></span>
                            </div>
                            <div class="col-md-4">
                                <label for="business_url">Business Website</label>
                                <input type="text" class="form-control" name="business_url" id="business_url" autocomplete="off" readonly data-required="true">
                                <span class="help-block hide-me"><small></small></span>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-10 text-center">
                                <button type="submit" id="submit-form" class="btn btn-success">Add Project</button>
                            </div>
                        </div>
                    </form>

                <div class="row text-center" id="keyform" style="display: none;">
                    <div class="col-md-9">
                        <form id="keywordForm" action="{{url('/api/add-se-keywords')}}" method="POST" enctype="multipart/form-data" >
                            <input type="hidden" name="u_id" id="u_id">
                            <label>Add Keywords:</label>
                            <input id="form-tags-4" class="form-control" name="keywords" type="text" value="" placeholder="Add new Keywords" data-required="true">
                            <span class="help-block hide-me"><small></small></span>
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" id="submit-form" class="btn btn-success">Add Keywords</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="box-footer">
            </div><!-- /.box-footer-->
        </div>
    </div>
</div>
@endsection

@section('after_styles')
    <style>
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
        label{display: block;padding: 20px 0 5px 0;}
        .tagsinput,.tagsinput *{box-sizing:border-box}
        .tagsinput{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-flex-wrap:wrap;-ms-flex-wrap:wrap;flex-wrap:wrap;background:#fff;font-family:sans-serif;font-size:14px;line-height:20px;color:#556270;padding:5px 5px 0;border:1px solid #e6e6e6;border-radius:2px}
        .tagsinput.focus{border-color:#ccc}
        .tagsinput .tag{position:relative;background:#556270;display:block;max-width:100%;word-wrap:break-word;color:#fff;padding:5px 30px 5px 5px;border-radius:2px;margin:0 5px 5px 0}
        .tagsinput .tag .tag-remove{position:absolute;background:0 0;display:block;width:30px;height:30px;top:0;right:0;cursor:pointer;text-decoration:none;text-align:center;color:#ff6b6b;line-height:30px;padding:0;border:0}
        .tagsinput .tag .tag-remove:after,.tagsinput .tag .tag-remove:before{background:#ff6b6b;position:absolute;display:block;width:10px;height:2px;top:14px;left:10px;content:''}
        .tagsinput .tag .tag-remove:before{-webkit-transform:rotateZ(45deg);transform:rotateZ(45deg)}
        .tagsinput .tag .tag-remove:after{-webkit-transform:rotateZ(-45deg);transform:rotateZ(-45deg)}
        .tagsinput div{-webkit-box-flex:1;-webkit-flex-grow:1;-ms-flex-positive:1;flex-grow:1}
        .tagsinput div input{background:0 0;display:block;width:100%;font-size:14px;line-height:20px;padding:5px;border:0;margin:0 5px 5px 0}
        .tagsinput div input.error{color:#ff6b6b}
        .tagsinput div input::-ms-clear{display:none}
        .tagsinput div input::-webkit-input-placeholder{color:#ccc;opacity:1}
        .tagsinput div input:-moz-placeholder{color:#ccc;opacity:1}
        .tagsinput div input::-moz-placeholder{color:#ccc;opacity:1}
        .tagsinput div input:-ms-input-placeholder{color:#ccc;opacity:1}
    </style>
@endsection

@section('after_scripts')
    <script src="{{ asset('public/admin/task/custom-validation.js') }}"></script>
    <script>
        $(function() {
            $('#form-tags-4').tagsInput({
                'unique': true,
                'minChars': 2,
                'maxChars': 10,
                'limit': 5,
                'validationPattern': new RegExp('^[a-zA-Z]+$')
            });
        });
    </script>
    <script>
        /* jQuery Tags Input Revisited Plugin */

        (function($) {
            $(".select2").select2();
            var delimiter = [];
            var inputSettings = [];
            var callbacks = [];

            $.fn.addTag = function(value, options) {
                options = jQuery.extend({
                    focus: false,
                    callback: true
                }, options);

                this.each(function() {
                    var id = $(this).attr('id');

                    var tagslist = $(this).val().split(_getDelimiter(delimiter[id]));
                    if (tagslist[0] === '') tagslist = [];

                    value = jQuery.trim(value);

                    if ((inputSettings[id].unique && $(this).tagExist(value)) || !_validateTag(value, inputSettings[id], tagslist, delimiter[id])) {
                        $('#' + id + '_tag').addClass('error');
                        return false;
                    }

                    $('<span>', {class: 'tag'}).append(
                        $('<span>', {class: 'tag-text'}).text(value),
                        $('<button>', {class: 'tag-remove'}).click(function() {
                            return $('#' + id).removeTag(encodeURI(value));
                        })
                    ).insertBefore('#' + id + '_addTag');

                    tagslist.push(value);

                    $('#' + id + '_tag').val('');
                    if (options.focus) {
                        $('#' + id + '_tag').focus();
                    } else {
                        $('#' + id + '_tag').blur();
                    }

                    $.fn.tagsInput.updateTagsField(this, tagslist);

                    if (options.callback && callbacks[id] && callbacks[id]['onAddTag']) {
                        var f = callbacks[id]['onAddTag'];
                        f.call(this, this, value);
                    }

                    if (callbacks[id] && callbacks[id]['onChange']) {
                        var i = tagslist.length;
                        var f = callbacks[id]['onChange'];
                        f.call(this, this, value);
                    }
                });

                return false;
            };

            $.fn.removeTag = function(value) {
                value = decodeURI(value);

                this.each(function() {
                    var id = $(this).attr('id');

                    var old = $(this).val().split(_getDelimiter(delimiter[id]));

                    $('#' + id + '_tagsinput .tag').remove();

                    var str = '';
                    for (i = 0; i < old.length; ++i) {
                        if (old[i] != value) {
                            str = str + _getDelimiter(delimiter[id]) + old[i];
                        }
                    }

                    $.fn.tagsInput.importTags(this, str);

                    if (callbacks[id] && callbacks[id]['onRemoveTag']) {
                        var f = callbacks[id]['onRemoveTag'];
                        f.call(this, this, value);
                    }
                });

                return false;
            };

            $.fn.tagExist = function(val) {
                var id = $(this).attr('id');
                var tagslist = $(this).val().split(_getDelimiter(delimiter[id]));
                return (jQuery.inArray(val, tagslist) >= 0);
            };

            $.fn.importTags = function(str) {
                var id = $(this).attr('id');
                $('#' + id + '_tagsinput .tag').remove();
                $.fn.tagsInput.importTags(this, str);
            };

            $.fn.tagsInput = function(options) {
                var settings = jQuery.extend({
                    interactive: true,
                    placeholder: 'Add new keyword',
                    minChars: 0,
                    maxChars: null,
                    limit: null,
                    validationPattern: null,
                    width: 'auto',
                    height: 'auto',
                    autocomplete: null,
                    hide: true,
                    delimiter: ',',
                    unique: true,
                    removeWithBackspace: true
                }, options);

                var uniqueIdCounter = 0;

                this.each(function() {
                    if (typeof $(this).data('tagsinput-init') !== 'undefined') return;

                    $(this).data('tagsinput-init', true);

                    if (settings.hide) $(this).hide();

                    var id = $(this).attr('id');
                    if (!id || _getDelimiter(delimiter[$(this).attr('id')])) {
                        id = $(this).attr('id', 'tags' + new Date().getTime() + (++uniqueIdCounter)).attr('id');
                    }

                    var data = jQuery.extend({
                        pid: id,
                        real_input: '#' + id,
                        holder: '#' + id + '_tagsinput',
                        input_wrapper: '#' + id + '_addTag',
                        fake_input: '#' + id + '_tag'
                    }, settings);

                    delimiter[id] = data.delimiter;
                    inputSettings[id] = {
                        minChars: settings.minChars,
                        maxChars: settings.maxChars,
                        limit: settings.limit,
                        validationPattern: settings.validationPattern,
                        unique: settings.unique
                    };

                    if (settings.onAddTag || settings.onRemoveTag || settings.onChange) {
                        callbacks[id] = [];
                        callbacks[id]['onAddTag'] = settings.onAddTag;
                        callbacks[id]['onRemoveTag'] = settings.onRemoveTag;
                        callbacks[id]['onChange'] = settings.onChange;
                    }

                    var markup = $('<div>', {id: id + '_tagsinput', class: 'tagsinput'}).append(
                        $('<div>', {id: id + '_addTag'}).append(
                            settings.interactive ? $('<input>', {id: id + '_tag', class: 'tag-input', value: '', placeholder: settings.placeholder}) : null
                        )
                    );

                    $(markup).insertAfter(this);

                    $(data.holder).css('width', settings.width);
                    $(data.holder).css('min-height', settings.height);
                    $(data.holder).css('height', settings.height);

                    if ($(data.real_input).val() !== '') {
                        $.fn.tagsInput.importTags($(data.real_input), $(data.real_input).val());
                    }

                    // Stop here if interactive option is not chosen
                    if (!settings.interactive) return;

                    $(data.fake_input).val('');
                    $(data.fake_input).data('pasted', false);

                    $(data.fake_input).on('focus', data, function(event) {
                        $(data.holder).addClass('focus');

                        if ($(this).val() === '') {
                            $(this).removeClass('error');
                        }
                    });

                    $(data.fake_input).on('blur', data, function(event) {
                        $(data.holder).removeClass('focus');
                    });

                    if (settings.autocomplete !== null && jQuery.ui.autocomplete !== undefined) {
                        $(data.fake_input).autocomplete(settings.autocomplete);
                        $(data.fake_input).on('autocompleteselect', data, function(event, ui) {
                            $(event.data.real_input).addTag(ui.item.value, {
                                focus: true,
                                unique: settings.unique
                            });

                            return false;
                        });

                        $(data.fake_input).on('keypress', data, function(event) {
                            if (_checkDelimiter(event)) {
                                $(this).autocomplete("close");
                            }
                        });
                    } else {
                        $(data.fake_input).on('blur', data, function(event) {
                            $(event.data.real_input).addTag($(event.data.fake_input).val(), {
                                focus: true,
                                unique: settings.unique
                            });

                            return false;
                        });
                    }

                    // If a user types a delimiter create a new tag
                    $(data.fake_input).on('keypress', data, function(event) {
                        if (_checkDelimiter(event)) {
                            event.preventDefault();

                            $(event.data.real_input).addTag($(event.data.fake_input).val(), {
                                focus: true,
                                unique: settings.unique
                            });

                            return false;
                        }
                    });

                    $(data.fake_input).on('paste', function () {
                        $(this).data('pasted', true);
                    });

                    // If a user pastes the text check if it shouldn't be splitted into tags
                    $(data.fake_input).on('input', data, function(event) {
                        if (!$(this).data('pasted')) return;

                        $(this).data('pasted', false);

                        var value = $(event.data.fake_input).val();

                        value = value.replace(/\n/g, '');
                        value = value.replace(/\s/g, '');

                        var tags = _splitIntoTags(event.data.delimiter, value);

                        if (tags.length > 1) {
                            for (var i = 0; i < tags.length; ++i) {
                                $(event.data.real_input).addTag(tags[i], {
                                    focus: true,
                                    unique: settings.unique
                                });
                            }

                            return false;
                        }
                    });

                    // Deletes last tag on backspace
                    data.removeWithBackspace && $(data.fake_input).on('keydown', function(event) {
                        if (event.keyCode == 8 && $(this).val() === '') {
                            event.preventDefault();
                            var lastTag = $(this).closest('.tagsinput').find('.tag:last > span').text();
                            var id = $(this).attr('id').replace(/_tag$/, '');
                            $('#' + id).removeTag(encodeURI(lastTag));
                            $(this).trigger('focus');
                        }
                    });

                    // Removes the error class when user changes the value of the fake input
                    $(data.fake_input).keydown(function(event) {
                        // enter, alt, shift, esc, ctrl and arrows keys are ignored
                        if (jQuery.inArray(event.keyCode, [13, 37, 38, 39, 40, 27, 16, 17, 18, 225]) === -1) {
                            $(this).removeClass('error');
                        }
                    });
                });

                return this;
            };

            $.fn.tagsInput.updateTagsField = function(obj, tagslist) {
                var id = $(obj).attr('id');
                $(obj).val(tagslist.join(_getDelimiter(delimiter[id])));
            };

            $.fn.tagsInput.importTags = function(obj, val) {
                $(obj).val('');

                var id = $(obj).attr('id');
                var tags = _splitIntoTags(delimiter[id], val);

                for (i = 0; i < tags.length; ++i) {
                    $(obj).addTag(tags[i], {
                        focus: false,
                        callback: false
                    });
                }

                if (callbacks[id] && callbacks[id]['onChange']) {
                    var f = callbacks[id]['onChange'];
                    f.call(obj, obj, tags);
                }
            };

            var _getDelimiter = function(delimiter) {
                if (typeof delimiter === 'undefined') {
                    return delimiter;
                } else if (typeof delimiter === 'string') {
                    return delimiter;
                } else {
                    return delimiter[0];
                }
            };

            var _validateTag = function(value, inputSettings, tagslist, delimiter) {
                var result = true;

                if (value === '') result = false;
                if (value.length < inputSettings.minChars) result = false;
                if (inputSettings.maxChars !== null && value.length > inputSettings.maxChars) result = false;
                if (inputSettings.limit !== null && tagslist.length >= inputSettings.limit) result = false;
                if (inputSettings.validationPattern !== null && !inputSettings.validationPattern.test(value)) result = false;

                if (typeof delimiter === 'string') {
                    if (value.indexOf(delimiter) > -1) result = false;
                } else {
                    $.each(delimiter, function(index, _delimiter) {
                        if (value.indexOf(_delimiter) > -1) result = false;
                        return false;
                    });
                }

                return result;
            };

            var _checkDelimiter = function(event) {
                var found = false;

                if (event.which === 13) {
                    return true;
                }

                if (typeof event.data.delimiter === 'string') {
                    if (event.which === event.data.delimiter.charCodeAt(0)) {
                        found = true;
                    }
                } else {
                    $.each(event.data.delimiter, function(index, delimiter) {
                        if (event.which === delimiter.charCodeAt(0)) {
                            found = true;
                        }
                    });
                }

                return found;
            };

            var _splitIntoTags = function(delimiter, value) {
                if (value === '') return [];

                if (typeof delimiter === 'string') {
                    return value.split(delimiter);
                } else {
                    var tmpDelimiter = '∞';
                    var text = value;

                    $.each(delimiter, function(index, _delimiter) {
                        text = text.split(_delimiter).join(tmpDelimiter);
                    });

                    return text.split(tmpDelimiter);
                }

                return [];
            };
        })(jQuery);

    </script>
    <script>
        $(function () {
            $('#user').on('change', function () {
                var siteUrl = $('#hfBaseUrl').val();
                var user_id = $(this).children("option:selected"). val();
                $('#user_id').val(user_id);
                $('#u_id').val(user_id);
                showPreloader();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "GET",
                    url: siteUrl + "/api/get-business-data",
                    data: {
                        user_id: user_id,
                    },
                }).done(function (result) {
                    var business_name = result.businessData.business_name;
                    $('#business_name').val(business_name);

                    var business_web = result.businessData.website;
                    $('#business_url').val(business_web);

                    hidePreloader();
                });
            });

            $("#projectForm").submit(function(event) {
                const values = $('#projectForm').serialize();
                const url  = $(this).attr("action");

                showPreloader();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "POST",
                    url: url,
                    data: values,
                }).done(function (result) {
                   // console.log(result);
                    hidePreloader();
                    if (result.success == 'true') {
                        swal({
                            title: "Success!",
                            text: 'Project added Successfully!',
                            type: 'success'
                        }, function () {
                            // showPreloader();
                            $('#projectForm').css({
                                display: 'none'
                            });
                            $('#keyform').css({
                                display: 'block'
                            })
                        });
                    } else {
                        swal({
                            title: "Error!",
                            text: 'Error in adding Project',
                            type: 'error'
                        }, function () {
                        });
                    }
                });
                event.preventDefault(); //Prevent the default submit
            });

            $("#keywordForm").submit(function(event) {
                const values = $('#keywordForm').serialize();
                const url  = $(this).attr("action");

                showPreloader();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "POST",
                    url: url,
                    data: values,
                }).done(function (result) {
                   console.log(result);
                    hidePreloader();
                    if (result.success == 'true') {
                        swal({
                            title: "Success!",
                            text: 'Keywords added Successfully!',
                            type: 'success'
                        }, function () {
                            showPreloader();
                            location.reload();
                            //window.location = '/keywords';
                        });
                    } else {
                        swal({
                            title: "Error!",
                            text: 'Error in adding Project',
                            type: 'error'
                        }, function () {
                        });
                    }
                });
                event.preventDefault(); //Prevent the default submit
            });
        });
    </script>
@endsection
