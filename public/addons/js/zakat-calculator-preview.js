(function ($) {
    var skyweb_donation_zakat_preview = function ($scope, $) {
        var zakat_preview = $scope.find('.zakat_preview').eq(0);

        var product_id = zakat_preview.find('.product_id').val();
        var zakat_input = zakat_preview.find('.zakat_input');
        var submit_button = zakat_preview.find('.preview_footer button[type="button"]');
        zakat_input.val('0'); // Update value to 0 for demonstration

        // Add Zakat to cart on button click
        submit_button.on('click', function () {
            var $btn = $(this);
            $btn.addClass('loading').prop('disabled', true);
            var zakat_value = zakat_input.val();
            $.ajax({
                type: 'POST',
                url: skyweb_extra_donation_ajax.ajax_url,
                data: {
                    action: 'add_extra_donation_to_cart',
                    product_id: product_id,
                    amount: zakat_value,
                    nonce: skyweb_extra_donation_ajax.nonce
                },
                success: function (response) {
                    window.location.href = skyweb_extra_donation_ajax.cart_url;
                },
                error: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
    };

    // Run this code under Elementor.
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_zakat_preview.default', skyweb_donation_zakat_preview);
    });
}(jQuery));
