/**
 * Created by Abdul Rehman on 03-Aug-17.
 */
window.prevFocus;
taskEditor = $('#description');

jQuery(document).ready(function($) {
    var html = '';
    var baseUrl = $("#adminBaseUrl").val();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "GET",
        url: baseUrl + "/variable-list"
    }).done(function (result) {
        $('.variablesUList').append(result);
    });

    /**
     * Add variable button in editor.
     * @param context
     */
    var addVariableBtn = function (context) {
        var ui = $.summernote.ui;
        var button = ui.buttonGroup([
            ui.button({
                contents: 'Variables <span class="note-icon-caret"></span> ',
                tooltip: 'Add Variable',
                className: 'dropdown-toggle',
                data: {
                    toggle: 'dropdown'
                }
            }),
            ui.dropdown({
                className: 'dropdown-menu',
                contents: "<div class='variablesUList'></div>"
            })
        ]);
        return button.render();   // return button as jquery object
    };

    /**
     * Editor Initiate
     */
    taskEditor.summernote({
        height: 250,
        dialogsInBody: true,
        oninit: function() {
        },
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']],
            ['help', ['help']],
            ['variables', ['addVariable']]

        ],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36', '48' , '64', '82', '150'],
        buttons: {
            addVariable: addVariableBtn
        }
    });

    var noteEditable = $('.note-editable');

    // SELECT THE CURRENT NODE //

    var lastCaretPos = 0;
    var parentNode;
    var range;
    var selection;

    noteEditable.on('mouseup',function (e){

        var scratch_content=taskEditor.summernote('code');
        if(scratch_content==""){
            taskEditor.next().find('.note-editable').append('&nbsp;');
        }

        selection = window.getSelection();

        range = selection.getRangeAt(0);
        parentNode = range.commonAncestorContainer.parentNode;
        //console.log(parentNode);
    });

    noteEditable.on('keyup',function (e){

        var scratch_content=taskEditor.summernote('code');
        if(scratch_content==""){
            taskEditor.next().find('.note-editable').append('&nbsp;');
        }

        selection = window.getSelection();
        range = selection.getRangeAt(0);
        parentNode = range.commonAncestorContainer.parentNode;
        //console.log(parentNode);
    });

    taskEditor.next().find('.note-editable').click(function() {
        console.log('clicked');
        prevFocus = $(this).attr('rel','body');
    });

    $('body').on('click','.selectVariable',function() {

        var SelectedVariable = $(this).html();
        console.log("selec " + SelectedVariable);

        var SelectedParent = $(this).closest('.parent').attr('data-container');

        var SelectionText = SelectedParent + ' ' + SelectedVariable;
        var variableTag = '<%'+SelectedParent + ' ' + SelectedVariable +'>';
        // SelectedVariable = SelectionText + ' ' + variableTag;
        SelectedVariable = variableTag;

        var scratch_content = taskEditor.summernote('code');

        if (scratch_content == "") {
            taskEditor.next().find('.note-editable').append('&nbsp;');
        }


        if(typeof prevFocus == "undefined"){
            console.log('undefined');
            //toastr.error('Please select the right position with cursor where you want to insert the Variable!');
            return false;
        }
        var rel = prevFocus.attr('rel');
        console.log(rel);

        if (rel == "body") {
            // if ($(parentNode).parents().is('.note-editable') || $(parentNode).is('.note-editable')) {
            var span = document.createElement('span');
            span.innerHTML = SelectedVariable;
            range.deleteContents();
            range.insertNode(span);

            // taskEditor.summernote('code', taskEditor.summernote('code'));

            //cursor at the last with this
            range.collapse(false);
            selection.removeAllRanges();
            selection.addRange(range);

            var context_textarea = window.document.getElementById('description');
            context_textarea.value = taskEditor.summernote('code');

            // var editorContent = taskEditor.summernote('code');
            //
            // console.log(editorContent);
            //
            // $('#description').html('');
            // $('#description').html(editorContent);
            // taskEditor.onblur();

            // $('#description').html(taskEditor.summernote('code'));


            /**
             * failed when I have insert a new lien and it will failed
             */
            // var contentWritten = taskEditor.summernote('code');
            // console.log("conte " + contentWritten);
            // $('#description').html(contentWritten);

            //} else {
            //    console.log('here');
            //    return;
            //}
        }

    });

});



