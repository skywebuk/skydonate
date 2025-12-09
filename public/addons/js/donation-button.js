(function ($) {
    var skyweb_donation_button = function ($scope, $) {
        var $button = $scope.find('.modal-open-button'); // Find all slider items

        $button.on('click', function(){
            $scope.find('.quick-modal').fadeIn();
            $('html').css('overflow', 'hidden'); 
            return false;
        });

        $('html').on('added_to_cart', function() {
            $('.quick-modal').fadeOut();
            $('html').css('overflow', ''); 
        });

        // Close modal when clicking the close button or overlay
        $scope.on('click', '.quick-modal-close, .quick-modal-overlay', function () {
            var modal = $(this).closest('.quick-modal');
            modal.fadeOut();
            $('html').css('overflow', ''); 
        });
    };

    // Initialize Elementor hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_button.default', skyweb_donation_button);
    });
}(jQuery));
