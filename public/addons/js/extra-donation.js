(function ($) {
    var skyweb_extra_donation = function ($scope, $) {

        $scope.find('.extra-donation-checkbox').on('change', function () {

            var checkbox = $(this),
                prevState = !checkbox.is(':checked'), // store old state BEFORE ajax
                label = checkbox.closest('.sky-smart-switch'),
                product_id = checkbox.data('product-id'),
                amount = checkbox.data('amount'),
                action = checkbox.is(':checked') 
                    ? 'add_extra_donation_to_cart' 
                    : 'remove_extra_donation_from_cart';

            label.addClass('load');
            $scope.find('.extra-donation-checkbox').prop('disabled', true);

            $.ajax({
                url: skydonate_extra_donation_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: action,
                    product_id: product_id,
                    amount: amount,
                    extra: true,
                    nonce: skydonate_extra_donation_ajax.nonce
                },
                success: function (response) {
                    label.removeClass('load');
                    $scope.find('.extra-donation-checkbox').prop('disabled', false);

                    if (response.success) {
                        $(document.body).trigger('update_checkout');
                        $(document.body).trigger('wc_fragment_refresh');
                    } else {
                        alert(response.data.message || 'Error');
                        checkbox.prop('checked', prevState); // revert correctly
                    }
                },
                error: function () {
                    label.removeClass('load');
                    $scope.find('.extra-donation-checkbox').prop('disabled', false);
                    alert('Something went wrong.');
                    checkbox.prop('checked', prevState); // revert correctly
                }
            });

        });

    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_extra_donation.default',
            skyweb_extra_donation
        );
    });

})(jQuery);