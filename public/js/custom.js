(function($) {
	'use strict';
	$(window).on("load", function(e) {
		// ______________ Page loading
		$("#global-loader").fadeOut("slow");
		// ______________mCustomScrollbar
		$(".mcs-horizontal-example").mCustomScrollbar({
			axis: "x",
			theme: "dark-3",
			advanced: {
				autoExpandHorizontalScroll: true
			}
		});
		// ______________Popover
		var $popover = $('[data-toggle="popover"]'),
			$popoverClass = '';
		// Methods
		function init($this) {
			if ($this.data('color')) {
				$popoverClass = 'popover-' + $this.data('color');
			}
			var options = {
				trigger: 'focus',
				template: '<div class="popover ' + $popoverClass + '" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
			};
			$this.popover(options);
		}
		// Events
		if ($popover.length) {
			$popover.each(function() {
				init($(this));
			});
		}
	})


	// ______________mCustomScrollbar
	$(".mscroll").mCustomScrollbar();
    // sidenav-toggled
	// $(".app-sidebar").mCustomScrollbar({
	// 	    theme: "minimal",
	// 	autoHideScrollbar: true,
	// 	scrollbarPosition: "outside"
	// });
	// _____________Tooltip
	$('[data-toggle="tooltip"]').tooltip();
	// ______________Chart-circle
	if ($('.chart-circle').length) {
		$('.chart-circle').each(function() {
			let $this = $(this);
			$this.circleProgress({
				fill: {
					color: $this.attr('data-color')
				},
				size: $this.height(),
				startAngle: -Math.PI / 4 * 2,
				emptyFill: '#eceef9',
				lineCap: 'round'
			});
		});
	}
	// ______________Full screen
	$("#fullscreen-button").on("click", function toggleFullScreen() {
		if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
			if (document.documentElement.requestFullScreen) {
				document.documentElement.requestFullScreen();
			} else if (document.documentElement.mozRequestFullScreen) {
				document.documentElement.mozRequestFullScreen();
			} else if (document.documentElement.webkitRequestFullScreen) {
				document.documentElement.webkitRequestFullScreen(Element.ALLOW_KEYBOARD_INPUT);
			} else if (document.documentElement.msRequestFullscreen) {
				document.documentElement.msRequestFullscreen();
			}
		} else {
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();
			} else if (document.msExitFullscreen) {
				document.msExitFullscreen();
			}
		}
	})
	// ______________ Cover images
	$(".cover-image").each(function() {
		var attr = $(this).attr('data-image-src');
		if (typeof attr !== typeof undefined && attr !== false) {
			$(this).css('background', 'url(' + attr + ') center center');
		}
	});

	// ______________ Back to top Button
	$(window).on("scroll", function(e) {
		// ______________ SCROLL TOP
		if ($(this).scrollTop() >300) {
			$('#back-to-top').fadeIn('slow');
		} else {
			$('#back-to-top').fadeOut('slow');
		}
	});
	$('#back-to-top').on("click", function() {
		$("html, body").animate({
			scrollTop: 0
		}, 600);
		return false;
	});
	//side bar
	$(function(e) {
		$(".app-sidebar a").each(function() {
			var pageUrl = window.location.href.split(/[?#]/)[0];
			if (this.href == pageUrl) {
				$(this).addClass("active");
				$(this).parent().addClass("active"); // add active to li of the current link
				$(this).parent().parent().prev().addClass("active"); // add active class to an anchor
				$(this).parent().parent().prev().click(); // click the item to make it drop
			}
		});

	});
	// ______________Copy Clipboard
	// Variables
	var $element = '.btn-icon-clipboard',
		$btn = $($element);
	// Methods
	function init($this) {
		$this.tooltip().on('mouseleave', function() {
			// Explicitly hide tooltip, since after clicking it remains
			// focused (as it's a button), so tooltip would otherwise
			// remain visible until focus is moved away
			$this.tooltip('hide');
		});
		var clipboard = new ClipboardJS($element);
		clipboard.on('success', function(e) {
			$(e.trigger).attr('title', 'Copied!').tooltip('_fixTitle').tooltip('show').attr('title', 'Copy to clipboard').tooltip('_fixTitle')
			e.clearSelection()
		});
	}
	// Events
	if ($btn.length) {
		init($btn);
	}
})(jQuery);

$(function () {
    // $('.selectpicker').selectpicker();


    $(".navbar-top .dropdown-menu").click(function () {
        // return false;
    });

    $(".sign-out , .side-menu__item:not(.no-loader)").on("click",function () {
        showPreloader();
    })

});
$(document).ready(function () {
    $("#sidebar-opener").on("click",function(){
        $('.sidebar').toggleClass("sidebar-show");
    });
    // $(".select2").select2();
    $(function () {
        $(".preloader").fadeOut();
        // $('#side-menu').metisMenu();
    });
    // Theme settings
    $(".open-close").click(function () {
        $("body").toggleClass("show-sidebar").toggleClass("hide-sidebar");
        $(".sidebar-head .open-close i").toggleClass("ti-menu");

    });
    //Open-Close-right sidebar
    $(".right-side-toggle").click(function () {
        $(".right-sidebar").slideDown(50);
        $(".right-sidebar").toggleClass("shw-rside");
        // Fix header
        $(".fxhdr").click(function () {
            $("body").toggleClass("fix-header");
        });
        // Fix sidebar
        $(".fxsdr").click(function () {
            $("body").toggleClass("fix-sidebar");
        });
        // Service panel js
        if ($("body").hasClass("fix-header")) {
            $('.fxhdr').attr('checked', true);
        }
        else {
            $('.fxhdr').attr('checked', false);
        }

    });
    //Loads the correct sidebar on window load,
    //collapses the sidebar on window resize.
    // Sets the min-height of #page-wrapper to window size
    $(function () {
        $(window).bind("load resize", function () {
            topOffset = 60;
            width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
            if (width < 768) {
                $('div.navbar-collapse').addClass('collapse');
                topOffset = 100; // 2-row-menu
            }
            else {
                $('div.navbar-collapse').removeClass('collapse');
            }
            height = ((this.window.innerHeight > 0) ? this.window.innerHeight : this.screen.height) - 1;
            height = height - topOffset;
            if (height < 1) height = 1;
            if (height > topOffset) {
                $("#page-wrapper").css("min-height", (height) + "px");
            }
        });
        var url = window.location;
        console.log("url " + url);
        var element = $('ul.nav a').filter(function () {
            return this.href == url || url.href.indexOf(this.href) == 0;
        }).addClass('active').parent().parent().addClass('in').parent();
        if (element.is('li')) {
            element.addClass('active');
        }
    });
    // This is for resize window
    $(function () {
        $(window).bind("load resize", function () {
            width = (this.window.innerWidth > 0) ? this.window.innerWidth : this.screen.width;
            if (width < 1170) {
                $('body').addClass('content-wrapper');
                $(".sidebar-nav, .slimScrollDiv").css("overflow-x", "visible").parent().css("overflow", "visible");

            }
            else {
                $('body').removeClass('content-wrapper');

            }
        });
    });

    // Collapse Panels
    (function ($, window, document) {
        var panelSelector = '[data-perform="panel-collapse"]';
        $(panelSelector).each(function () {
            var $this = $(this)
                , parent = $this.closest('.panel')
                , wrapper = parent.find('.panel-wrapper')
                , collapseOpts = {
                toggle: false
            };
            if (!wrapper.length) {
                wrapper = parent.children('.panel-heading').nextAll().wrapAll('<div/>').parent().addClass('panel-wrapper');
                collapseOpts = {};
            }
            wrapper.collapse(collapseOpts).on('hide.bs.collapse', function () {
                $this.children('i').removeClass('ti-minus').addClass('ti-plus');
            }).on('show.bs.collapse', function () {
                $this.children('i').removeClass('ti-plus').addClass('ti-minus');
            });
        });
        $(document).on('click', panelSelector, function (e) {
            e.preventDefault();
            var parent = $(this).closest('.panel');
            var wrapper = parent.find('.panel-wrapper');
            wrapper.collapse('toggle');
        });
    }(jQuery, window, document));
    // Remove Panels
    (function ($, window, document) {
        var panelSelector = '[data-perform="panel-dismiss"]';
        $(document).on('click', panelSelector, function (e) {
            e.preventDefault();
            var parent = $(this).closest('.panel');
            removeElement();

            function removeElement() {
                var col = parent.parent();
                parent.remove();
                col.filter(function () {
                    var el = $(this);
                    return (el.is('[class*="col-"]') && el.children('*').length === 0);
                }).remove();
            }
        });
    }(jQuery, window, document));
    //tooltip
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
    //Popover
    $(function () {
        $('[data-toggle="popover"]').popover()
    })
    // Task
    $(".list-task li label").click(function () {
        $(this).toggleClass("task-done");
    });
    $(".settings_box a").click(function () {
        $("ul.theme_color").toggleClass("theme_block");
    });
});
//Colepsible toggle
$(".collapseble").click(function () {
    $(".collapseblebox").fadeToggle(350);
});
// Sidebar
// $('.slimscrollright').slimScroll({
//     height: '100%'
//     , position: 'right'
//     , size: "5px"
//     , color: '#dcdcdc'
//     , });
$('.app-sidebar ul.side-menu').slimScroll({
    height: '550px',
    alwaysVisible: false,
    opacity: 1,
    color: '#C4C4C4',
    size: '6px',
    borderRadius : '5px'
    });
// $('.chat-list').slimScroll({
//     height: '100%'
//     , position: 'right'
//     , size: "0px"
//     , color: '#dcdcdc'
//     , });

// Resize all elements
$("body").trigger("resize");
// visited ul li
$('.visited li a').click(function (e) {
    $('.visited li').removeClass('active');
    var $parent = $(this).parent();
    if (!$parent.hasClass('active')) {
        $parent.addClass('active');
    }
    e.preventDefault();
});
// Login and recover password
$('#to-recover').click(function () {
    $("#loginform").slideUp();
    $("#recoverform").fadeIn();
});
// Update 1.5
// this is for close icon when navigation open in mobile view
$(".navbar-toggle").click(function () {
    $(".navbar-toggle i").toggleClass("ti-menu");
    $(".navbar-toggle i").addClass("ti-close");
});
// Update 1.6


$(document.body).on('click', '.page-help', function(e)
{
    var action = $(this).attr("data-action");
    var baseUrl = $('#hfBaseUrl').val();

    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
    $(mainModel).removeClass('welcome-process');
    $(mainModel).addClass('modal-page-info');
    var pageTitle = $(".page-title").html();

    if(!pageTitle)
    {
        pageTitle = 'Trustyy';
    }

    var html = '';
    html += '<div class="modal-body">\n' +
        '                                <h3 class="modal-title p-b-10">'+pageTitle+'</h3>\n' +
        '                                <div class="row">\n' +
        '                                    <div class="col-md-12">\n' +
        '                                        <div class="text-center">\n' +
        '<i class="fa fa-question-circle" aria-hidden="true" style="font-size: 56px;color: #5495d4;"></i>' +
        '                                            <div class="p-20">\n' +
        '                                                <p>We are here for your help.</p>\n' +
        '                                            </div>\n' +
        '                                        </div>\n' +
        '                                    </div>\n' +
        '\n' +
        '                                \n' +
        '\n' +
        '                                </div>\n' +
        '                            </div>';

    html += '<div class="modal-footer" style="display: table;margin: 0 auto;">';
    html += '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    html += '</div>';

    mainModel.modal('show');
    $(".modal-header").after(html);
});

$(document.body).on('click', '.btn-continue', function(e)
{
    var action = $(this).attr("data-action");
    var baseUrl = $('#hfBaseUrl').val();

    var mainModel = $('#main-modal');
    $(".modal-body, .modal-footer, .validate-me", mainModel).remove();
    $(mainModel).removeClass('welcome-process');
    $(mainModel).addClass('modal-page-info');
    var pageTitle = $(this).closest(".item").find('.subtext').html().trim();

    if(!pageTitle)
    {
        pageTitle = 'Trustyy';
    }

    var html = '';
    html += '<div class="modal-body">\n' +
        '                                <h3 class="modal-title p-b-10">'+pageTitle+'</h3>\n' +
        '                                <div class="row">\n' +
        '                                    <div class="col-md-12">\n' +
        '                                        <div class="text-center">\n' +
        // '<i class="fa fa-question-circle" aria-hidden="true" style="font-size: 56px;color: #5495d4;"></i>' +
        '                                            <div class="p-20">\n' +
        // '                                                <p>We are here for your help.</p>\n' +
        '                                            </div>\n' +
        '                                        </div>\n' +
        '                                    </div>\n' +
        '\n' +
        '                                \n' +
        '\n' +
        '                                </div>\n' +
        '                            </div>';

    html += '<div class="modal-footer" style="display: table;margin: 0 auto;">';
    html += '<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>';
    html += '</div>';

    mainModel.modal('show');
    $(".modal-header").after(html);
});

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
