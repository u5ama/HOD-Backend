/**
 * Created by Abdul Rehman.
 */

var errorFound = false;

$(".validate-me input, .validate-me textarea, .validate-me select").on('blur keyup', function()
{
    var isRequired = $(this).attr('data-required');
    var errorMessage = 'Required field';
    var fieldSpecificMessage;

    /**
     * get the global message to show
     * error on field skip.
     */
    var fieldErrorMessage = $('#global-error-message').attr('data-global-error-message');

    fieldSpecificMessage = $(this).attr('data-message');

    if(fieldErrorMessage)
    {
        errorMessage = fieldErrorMessage;
    }

    // if field must be filled then go in
    if(isRequired === "true")
    {
        var ID = $(this).attr('id'); // get id of current required field.

        var name = $(this).attr('name'); // get name of current required field.

        var currentFieldvalue = $(this).val(); // get value of current field.

        // if current field value empty
        if(currentFieldvalue === '')
        {
            // if(data-message="hello")
            $(this).parent().find('span.help-block small').html(errorMessage);
            errorFound = true;
        }
        else
        {
            if(ID === 'password' || ID === 'password-confirm')
            {
                var primaryPassword = $('#password');
                var passwordValue = $('#password').val();
                var passwordLength = passwordValue.length;

                var passwordConfirm = $('#password-confirm');
                var passwordConfirmValue =passwordConfirm.val();
                var passwordConfirmLength = passwordConfirmValue.length;

                if($(this).val().length < 8)
                {
                    $(this).parent().find('span.help-block small').html('Minimum 8 characters');
                    errorFound = true;
                }
                else if( ( passwordValue !== passwordConfirmValue ) && ( passwordConfirmValue !== '' ) )
                {
                    primaryPassword.parent().find('span.help-block small').html('Password does not match.');
                    passwordConfirm.parent().find('span.help-block small').html('Password does not match.');
                    errorFound = true;
                }
                else {
                    primaryPassword.parent().find('span.help-block small').html('');
                    passwordConfirm.parent().find('span.help-block small').html('');
                    errorFound = false;
                }
            }
            else if (currentFieldvalue !== '' && ID === 'website')
            {
                if(fieldSpecificMessage)
                {
                    errorMessage = fieldSpecificMessage;
                }
                else
                {
                    errorMessage = 'Invalid website';
                }

                var domainPattern = new RegExp(/^(https?:\/\/)?((?:[a-z0-9-]+\.)+(?:com|net|biz|life|info|org|co|[a-zA-Z]{2}))(?:\/|$)/i);

                if(!currentFieldvalue.match(domainPattern)) {
                    $(this).parent().find('span small').html(errorMessage);
                    errorFound = true;
                } else{
                    $(this).parent().find('span small').html('');
                    errorFound = false;
                }
            }
            else if (currentFieldvalue !== '' && ID === 'email')
            {
                // check email.
                var emailRegEx = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i;

                // if email not verified on global rule.
                if(!emailRegEx.test(currentFieldvalue))
                {
                    $(this).parent().find('span small').html('E-mail is invalid');
                    errorFound = true;
                }
                else{
                    $(this).parent().find('span small').html('');
                    errorFound = false;
                }
            }
            else {
                $(this).parent().find('span.help-block small').html('');
                errorFound = false;
            }
        }

        /**
         * if error found then add classes on field.
         */
        if(errorFound) {
            $(this).parent().find('span.help-block').removeClass('hide-me');
            $(this).parent().addClass('has-error');
            $(this).parent().find('span.help-block').addClass('error');
        }
        else
        {
            $(this).parent().find('span.help-block').removeClass('error');
            $(this).parent().removeClass('has-error');
            $(this).parent().find('span.help-block').addClass('hide-me');

            if(currentFieldvalue === '' && ID === 'website') {
                $(this).parent().find('span.help-block').removeClass('hide-me');
            }
        }
    }
});

$("form.validate-me").submit(function(e)
{
    console.log("clicked");
    var errorLog = false;
    $('.validate-me input, .validate-me textarea, .validate-me select').each(function() {
        var isRequired = $(this).attr('data-required');

        console.log("is " + isRequired);
        if(isRequired === "true")
        {
            var name = $(this).attr('name');
            var ID = $(this).attr('id');

            if( (this.value === '') )
            {
                $(this).blur();
            }
            else if( (this.value !== '' && ID === 'email') )
            {
                errorLog = errorFound;
                $(this).blur();

                if(errorLog === true && errorFound === false)
                {
                    errorFound = true;
                }
            }
        }
    });

    var saveAction = $('#saveActions');

    console.log("erro " + errorFound);

    if(!errorFound) {
        saveAction.parent().find('span.help-block').removeClass('error');
        saveAction.parent().find('span.help-block').addClass('hide-me');
        errorFound = false;
        return true;
    }

    saveAction.parent().find('span.help-block').removeClass('hide-me');
    saveAction.parent().find('span.help-block').addClass('error');

    e.stopImmediatePropagation();
    return false;
});
