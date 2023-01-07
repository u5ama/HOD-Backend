/**
 * Register admin
 *
 * Created by Abdul Rehman on 21-Jun-17.
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

            var first_name = $('#first_name').val();
            var email = $('#email').val();
            var status = $('#status').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: baseUrl + "/store-admin-user",
                data: {
                    first_name: first_name,
                    email: email,
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
