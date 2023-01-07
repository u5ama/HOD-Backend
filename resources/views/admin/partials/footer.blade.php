
@yield('before_scripts')

<!-- jQuery 2.2.0 -->
<script type="text/javascript" src="{{ asset('public/admin/adminlte/plugins/jQuery/jquery-2.2.3.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/admin/adminlte/plugins/jQueryUI/jquery-ui.js') }}"></script>

<!-- Bootstrap 3.3.5 -->
<script type="text/javascript" src="{{ asset('public/admin/adminlte/bootstrap/js/bootstrap.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/admin/adminlte/plugins/pace/pace.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/admin/adminlte/plugins/slimScroll/jquery.slimscroll.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/admin/adminlte/plugins/fastclick/fastclick.js') }}"></script>
<script type="text/javascript" src="{{ asset('public/admin/adminlte/dist/js/app.min.js') }}"></script>

<script src="{{ asset('public/js/admin/core.js') }}"></script>
<script src="{{ asset('public/plugins/custom-select/custom-select.min.js') }}"></script>

<!-- page script -->
<script type="text/javascript">
    // Set active state on menu element
    var current_url = "{{ Request::url() }}";
    $("ul.sidebar-menu li a").each(function() {
        if ($(this).attr('href').startsWith(current_url) || current_url.startsWith($(this).attr('href')))
        {
            $(this).parents('li').addClass('active');
        }
    });
            {{-- Enable deep link to tab --}}
    var activeTab = $('[href="' + location.hash.replace("#", "#tab_") + '"]');
    activeTab && activeTab.tab('show');
    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        location.hash = e.target.hash.replace("#tab_", "#");
    });
</script>
<script src="{{ asset('public/admin/pnotify/pnotify.custom.min.js') }}"></script>
<script src="{{ asset('public/plugins/sweetalert/sweetalert.min.js') }}"></script>
@yield('after_scripts')
