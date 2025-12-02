(function($) {
    $(document).ready(function() {
        
        $('.donation-form .donation-type-btn').on('click', function() {
            const $iconContainer = $(this).find('.heart-icon');

            // Append the flying heart
            const $heart = $('<i class="fas fa-heart move-heart"></i>');
            $iconContainer.append($heart);

            // Remove after animation completes
            setTimeout(() => {
            $heart.remove();
            }, 2500);
        });

        $('.donation-tabs .button.monthly-button').on('click', function() {
            const $iconContainer = $(this).find('.heart-icon');
            // Append the flying heart
            const $heart = $('<i class="fas fa-heart move-heart"></i>');
            $iconContainer.append($heart);
            // Remove after animation completes
            setTimeout(() => {
            $heart.remove();
            }, 2500);
        });
    });
})(jQuery);
