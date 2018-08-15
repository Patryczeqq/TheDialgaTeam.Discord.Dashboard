(function ($) {
    $(document).ready(function () {
        $('.guildId').on('change', function () {
            window.location.href = '/server/' + $(this).val();
        });

        $('.overlay').on('click', function () {
            $('#sidebar').removeClass('active');
            $('.overlay').removeClass('active');
        });

        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
            $('.overlay').addClass('active');
            $('.collapse.in').toggleClass('in');
            $('a[aria-expanded=true]').attr('aria-expanded', 'false');
        });
    });
})(jQuery);