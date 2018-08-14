(function ($) {
    $(document).ready(function () {
        $('.guildId').on('change', function () {
            window.location.href = '/server/' + $(this).val();
        });
    });
})(jQuery);