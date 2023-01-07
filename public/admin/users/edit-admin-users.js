/**
 * Edit admin user
 */
$(function()
{
    $("form.validate-me").submit(function(e)
    {
        e.preventDefault();

        var baseUrl = $('#adminBaseUrl').val();

        /**
         * errorFound false shows there's no error found while
         * validating fields and all is set.
         *
         * emailVerified true increase security to check that yes we can go in.
         */
        //if(errorFound === false && emailVerified === true) {
        if(errorFound === false) {

            $('#saveActions .btn-group').hide();
            $('.post-save .loader').show();

            // action url where data to be submit.
            var actionUrl = $('#saveActionUrl').val();

            var user = $('#user_action').val();
            var password = $('#password').val();
            var status = $('#status').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: actionUrl,
                data: {
                    userId: user,
                    password: password,
                    status: status
                }
            }).done(function (result) {
                // parse data into json
                var json = $.parseJSON(result);

                // get data
                var statusCode = json.status_code;
                var statusMessage = json.status_message;

                $('.post-save .loader').hide();
                var baseUrl = $('#adminBaseUrl').val();

                // if no error found.
                if (statusCode === 200) {
                    swal({
                        title: "Successful!",
                        text: statusMessage,
                        type: "success"
                    }, function() {
                        window.location = baseUrl+"/list";
                    });
                }
                else {
                    swal("Error", statusMessage, "error");
                    // if error found.
                    $('#saveActions .btn-group').show();
                }

            });
        }
    });
});
