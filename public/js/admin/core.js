function showPreloader() {
    var preloader = $('.preloader');
    preloader.show();
    $("body").addClass('hide-overflow');
    preloader.addClass('preloader-opacity');
}


function hidePreloader() {
    var preloader = $('.preloader');
    preloader.removeClass('preloader-opacity');
    $("body").removeClass('hide-overflow');
    preloader.hide();
}

function resetLoaderButton($this) {
    $this.attr("disabled", false);
    $this.html($this.data('original-text'));
}

function showLoaderButton(target, message) {
    console.log("showLoaderButton");
    var $this = $(target);
    $this.attr("disabled", true);
    var loadingText;

    if(message && message !== '')
    {
        loadingText = '<i class="fa fa-circle-o-notch fa-spin" style="margin-right: 5px;"></i>'+ message +' ...';
    }
    else
    {
        loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> Processing...';
    }

    if ($this.html() !== loadingText) {
        $this.data('original-text', $this.html());
        $this.html(loadingText);
    }

    return $this;
}