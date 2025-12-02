(function ($) {
    var skywebDonationCardAddon = function ($scope, $) {
        var $dataSettings = $scope.find('.donation-cards-wrapper').first();

        function formatNumber(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function animateCard($card) {
            var progressBar = $card.find('.progress-bar');
            var percentage = $card.find('.percentage .count');
            var percent = progressBar.data('percent');
            var raisedAmount = $card.find('.raised-amount');  
            var targetAmount = $card.find('.target-amount');
            var raisedEnd = raisedAmount.find('.amount').data('number');
            var targetEnd = targetAmount.find('.amount').data('number');
            var raisedElement = raisedAmount.find('.number');
            var targetElement = targetAmount.find('.number');

            // Animate the progress bar width in sync with the label
            progressBar.animate({ minWidth: percent + '%' }, 1500);
            animateNumber(raisedElement, 0, raisedEnd, 1500);
            animateNumber(targetElement, 0, targetEnd, 1500);
            animateNumber(percentage, 0, percent, 1500);
        }

        function animateNumber($element, start, end, duration) {
            var raisedStart = start;
            var raisedEnd = end;
            var raisedStepTime = 100;
            var raisedStepValue = Math.ceil((raisedEnd - raisedStart) / (duration / raisedStepTime));

            if (raisedStepValue === 0) {
                $element.text(raisedEnd);
                return;
            }

            var raisedCounterInterval = setInterval(function () {
                if (raisedStart < raisedEnd) {
                    raisedStart += raisedStepValue;
                    if (raisedStart > raisedEnd) {
                        raisedStart = raisedEnd;
                    }
                    $element.text(formatNumber(Math.floor(raisedStart)));
                } else {
                    clearInterval(raisedCounterInterval);
                }
            }, raisedStepTime);
        }

        // Intersection Observer
        function initObserver() {
            var options = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1 // Trigger when 10% of the element is visible
            };

            var observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCard($(entry.target)); // Animate the card when it is visible
                        observer.unobserve(entry.target); // Stop observing after animation
                    }
                });
            }, options);

            $scope.find('.donation-card').each(function() {
                observer.observe(this); // Observe each donation card
            });
        }

        // Initial check for already visible elements
        initObserver();

        if ($dataSettings.length > 0) {
            // Open modal on click of .quick-button
            $scope.on('click', '.quick-button', function () {
                var $button = $(this);
                var donation_card = $button.closest('.donation-card');
                var modal = donation_card.find('.quick-modal');
                modal.fadeIn();
                $('html').css('overflow', 'hidden'); 
            });

            // Close modal
            $scope.on('click', '.quick-modal-close, .quick-modal-overlay', function () {
                var modal = $(this).closest('.quick-modal');
                modal.fadeOut();
                $('html').css('overflow', ''); 
            });

            $('html').on('added_to_cart', function(event, fragments, cart_hash, $button) {
                $('.quick-modal').fadeOut();
                $('html').css('overflow', ''); 
            });

            // Close modal on pressing 'Escape'
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    var modal = $('.quick-modal:visible');
                    if (modal.length) {
                        modal.fadeOut();
                        $('html').css('overflow', ''); 
                    }
                }
            });
        }
    };

    // Initialize Elementor hook
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_card_addon.default', skywebDonationCardAddon);
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_card_addon_2.default', skywebDonationCardAddon);
    });
}(jQuery));