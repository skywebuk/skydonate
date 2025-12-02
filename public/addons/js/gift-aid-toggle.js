(function ($) {
    var skyweb_gift_aid_toggle = function ($scope, $) {
        var $giftAid = $scope.find('.skyweb-gift-aid-toggle');
        var $checkbox = $giftAid.find('#gift_aid_it');
        var defaultState = $checkbox.data('value'); // "checked" or ""

        function toggleGiftAid() {
            var country = $('#billing_country').val();

            if (country === 'GB') {
                if (!$giftAid.is(':visible')) {
                    $giftAid.stop(true, true).slideDown(200);
                }
                $checkbox.prop('checked', defaultState === 'checked');
            } else {
                if ($giftAid.is(':visible')) {
                    $giftAid.stop(true, true).slideUp(200);
                }
                $checkbox.prop('checked', false);
            }
        }

        // Run on load and on billing country change
        toggleGiftAid();
        $(document.body).on('change', '#billing_country', toggleGiftAid);
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_gift_aid_toggle.default',
            skyweb_gift_aid_toggle
        );
    });
})(jQuery);
