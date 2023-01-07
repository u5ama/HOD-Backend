$(function () {
    $(document.body).on('submit', 'form.validate-me', function(e)
    {
        e.preventDefault();
        console.log("call B");
        console.log(e);

        console.log("err");
        console.log(errorFound);

        if (!errorFound) {
            console.log("validation passed");

            var $this = $(".submit");
            $this.attr("disabled", true);
            var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Login Processing...';

            if ($this.html() !== loadingText) {
                $this.data('original-text', $this.html());
                $this.html(loadingText);
            }

            login();
        }

    });
});
function login(email, password, source)
{
    var baseUrl = $('#hfBaseUrl').val();

    console.log("emai; " + email);

    if(!email)
    {
        email = $("#email").val();
    }

    if(!password)
    {
        password = $("#password").val();
    }

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        type: "POST",
        url: baseUrl + '/login',
        data: {
            email:email,
            password: password
        }
    }).done(function (result) {
        // parse data into json
        var json = $.parseJSON(result);

        // get data
        var statusCode = json.status_code;
        var statusMessage = json.status_message;

        var alert = $(".response-message");
        alert.show();

        var $this = $(".submit");

        if(statusCode == 200)
        {
            if(source !== 'register')
            {
                alert.html('<div class="alert alert-success">'+statusMessage+'</div>');
            }

            location.href = baseUrl;
        }
        else
        {
            $this.attr("disabled", false);
            $this.html($this.data('original-text'));
            alert.html('<div class="alert alert-danger">'+statusMessage+'</div>');
        }
    });
}