(function($) {
    $(document).ready(function() {
        // Toggle functionality for the element
        $('.showskylogin').on('click', function() {
            $('.sky-checkout-login .sky-donation-login').slideToggle();
            return false;
        });
    });
})(jQuery);
