(function ($) {
    var skyweb_quick_donation = function ($scope, $) {

        var $frequency = $scope.find('.qd-frequency');
        var $amount = $scope.find('.qd-amount');
        var $fund = $scope.find('.qd-fund');
        var $button = $scope.find('.form-submit-button');

        // Custom amount input wrapper from PHP render()
        var $customAmountWrap = $scope.find('.qd-custom-amount');
        var $customInput = $customAmountWrap.find('.qd-custom-amount-input');

        // Start hidden
        $customAmountWrap.hide();

        // ===== Toggle custom input visibility =====
        $amount.on('change', function () {
            if ($(this).val().toLowerCase() === 'custom') {
                $customAmountWrap.slideDown(200);
                $customInput.focus();
                $amount.hide();
            } else {
                $customAmountWrap.slideUp(200);
                $customInput.val('');
                $amount.show();
            }
        });

        // ===== Add to Basket Click =====
        $button.on('click', function (e) {
            e.preventDefault();

            var $btn = $(this);

            // Disable submit & show loading indicator
            $btn.addClass('loading-running').prop('disabled', true);
            var selectedFrequency = $frequency.val();
            var selectedAmount = $amount.is(':visible') ? $amount.val() : $customInput.val();
            var productId = $fund.find(':selected').data('product-id');

            // ===== Validation =====
            if (!productId || productId <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Fund',
                    text: 'Please select a valid fund before proceeding.',
                });
                resetButton($btn);
                return;
            }

            if (!selectedAmount || isNaN(selectedAmount) || selectedAmount <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Amount',
                    text: 'Please enter a valid donation amount.',
                }).then(() => {
                    if ($amount.is(':visible')) {
                        $amount.focus();
                    } else {
                        $customInput.focus();
                    }
                });
                resetButton($btn);
                return;
            }

            // ===== Prepare Data =====
            var data = {
                action: 'add_extra_donation_to_cart',
                product_id: productId,
                amount: selectedAmount,
                donation_frequency: selectedFrequency,
                nonce: skydonate_extra_donation_ajax.nonce
            };

            // ===== AJAX Request =====
            $.ajax({
                url: skydonate_extra_donation_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response && response.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error,
                        });
                    } else {
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                        $(document.body).trigger('wc_fragment_refresh');

                        $('.cart-widget-side').addClass('wd-opened');
                        $('.wd-close-side').addClass('wd-close-side-opened');
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: 'There was an error adding your donation. Please try again.',
                    });
                },
                complete: function () {
                    resetButton($btn);
                }
            });
        });

        // ===== Helper: Reset button to normal state =====
        function resetButton($btn) {
            $btn.removeClass('loading-running').prop('disabled', false);
        }

    };

    // Elementor frontend hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_quick_donation.default',
            skyweb_quick_donation
        );
    });

})(jQuery);
