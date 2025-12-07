(function ($) {
    var skydonate_impact_slider = function ($scope, $) {
        var $button = $scope.find('.modal-open-button'); // Find all slider items

        $button.on('click', function(){
            var input_val = $scope.find('input[type="radio"]:checked').val(); // Get the value of the selected radio button
            $scope.find('.other-ammount-toggle').hide();
            $scope.find('.custom-amount-box').show();
            $scope.find('.custom-option-button').removeClass('selected');
            $scope.find('.selected_amount').val(input_val); // Corrected to .val()
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
        elementorFrontend.hooks.addAction('frontend/element_ready/skydonate_impact_slider.default', skydonate_impact_slider);
    });
}(jQuery));
