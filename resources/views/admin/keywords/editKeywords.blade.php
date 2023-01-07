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
                    Edit Keywords
                </h3>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-9 task-page" style="border: 1px solid #7777776e; padding: 0px 20px 40px 15px; margin-left: 2%;">
                        <h3>User Keywords</h3>
                        @if(!empty($records))
                            @foreach($records as $record)
                                <div class="left keyword-data keyword-1" data-keyword-type="selected" data-keyword-rank="" data-keyword-value="{{$record['keyword']}}" data-keyword-volume="">
                                    <label class="label-on keyword-show" for="keyword-1" style="min-width: 160px;">
                                        <p class="keyword-text">{{$record['keyword']}}</p>
                                        <span class="keyword-value">
                                            @if($record['rank'] !== null)
                                                {{$record['rank']}}
                                                @else
                                                0
                                            @endif
                                        </span>
                                        <span class="keyword-reset" data-target-id="{{$record['keyword_id']}}">x</span>
                                    </label>
                                    <input class="nocheckbox" type="checkbox" id="keyword-1" checked="checked" disabled="disabled">
                                </div>
                            @endforeach
                            @else
                            <p>No Keywords Found for user</p>
                        @endif
                    </div>
                </div>
                <br>
                <div class="row text-center" id="keyform" >
                    <div class="col-md-9">
                        <form id="keywordForm" action="{{url('/api/add-se-keywords')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="u_id" id="u_id">
                            <label>Add Keywords:</label>
                            <input id="form-tags-4" class="form-control" name="keywords" type="text" value="" placeholder="Add new Keywords">
                            <br>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" id="submit-form" class="btn btn-success">Update Keywords</button>
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
    <input type="hidden" name="user_id" id="user_id" value="{{Request::segment(3)}}">
    </div>
@endsection

@section('after_styles')
    <style>
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
    <style>
        .nocheckbox {
            display: none;
        }
        .keyword-data {
            margin-bottom: 10px;
        }
        .label-on {
            background: #1D32A0 !important;
        }
        .label-on {
            background: #0093FE;
            box-sizing: border-box;
            border-radius: 32px;
            padding: 5px 10px;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
            margin-right: 15px;
        }
        .keyword-text {
            display: inline-block;
            overflow: hidden;
            max-width: 1440px;
            width: auto;
            margin-top: 5px;
            text-overflow: ellipsis;
            vertical-align: middle;
            margin-bottom: 5px;
        }
        .label-on span {
            border-radius: 26px;
            background: #fff;
            color: #000;
            margin-left: 5px;
            float: right;
            padding: 0px 6px !important;
            height: 30px;
            line-height: 30px;
            text-align: center;
        }
        .keyword-reset {
            background: #596ABF !important;
        }
        .keyword-reset {
            cursor: pointer;
            display: none;
            background: #5AB5F7 !important;
            color: #fff !important;
            padding: 0 6px !important;
            margin-right: 6px;
            float: right !important;
            width: 30px;
        }
        .keyword-data:hover .keyword-reset{
            display: inline-block;
        }
        .keyword-data:hover .keyword-value{
            display: none;
        }
    </style>
@endsection

@section('after_scripts')
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
        /* jQuery Tags Input Revisited Plugin*/

        (function($) {
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
            var user_id = $('#user_id').val();
            $('#u_id').val(user_id);

            $(document.body).on('click', '.keyword-reset', function () {
                var siteUrl = $('#hfBaseUrl').val();
                var target = $(this).attr('data-target-id');
                 var user_id = $('#user_id').val();


                currentTarget = $(this);
                showPreloader();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    type: "POST",
                    url: siteUrl + "/api/delete-se-keyword",
                    data: {
                        id: target,
                        user_id: user_id,
                    },
                }).done(function (result) {

                    hidePreloader();
                    location.reload();
                });
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
                   // console.log(result);
                    hidePreloader();
                    if (result.success == 'true') {
                        swal({
                            title: "Success!",
                            text: 'Keywords added Successfully!',
                            type: 'success'
                        }, function () {
                            showPreloader();
                            location.reload();
                           // window.location = 'keywords';
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
