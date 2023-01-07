$(function(){
    $('.trash').click(function()
    {
        $('#myModal').modal();

        var id = $('#currentPageId').val();

        $('#deleteModal').modal();

        $('#delete-confirm').click(function()
        {
            var formData = false;
            if (window.FormData) formData = new FormData();

            $('#delete-confirm').attr('disabled', true);

            formData.append('id', id);
            var baseUrl = $('#adminBaseUrl').val();
            var actionToPost = '/delete-list';

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                },
                type: "POST",
                url: baseUrl + actionToPost,
                contentType: false,
                cache: false,
                processData: false,
                data: formData
            }).done(function (result) {
                // parse data into json
                var json = $.parseJSON(result);

                $('#delete-confirm').attr('disabled', false);

                // get data
                var statusCode = json.status_code;
                var statusMessage = json.status_message;
                var redirect = json.redirect;

                console.log(" redir " + redirect);

                $('#deleteModal').modal('hide');

                if (statusCode == 200) {
                    swal({
                        title: '',
                        type: 'success',
                        text: 'Object Deleted',
                        showCancelButton: false,
                        showConfirmButton: false
                    });

                    setTimeout(function () {
                        window.location = redirect;
                    }, 1000);
                }
                else
                {
                    swal("", statusMessage, "error");
                }
            });
        });

    });
});