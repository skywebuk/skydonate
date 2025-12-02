(function ($) {
    var skyweb_donation_progress = function ($scope, $) {
        var data_settings = $scope.find('.donation-progress').eq(0);
        if (data_settings.length > 0) {
            var settings = data_settings.data('settings');
            var duration = settings.duration ? settings.duration : 1500;
            var raised = parseFloat(settings.raised);
            var target = parseFloat(settings.target);
            var progressBar = $scope.find('.progress-bar');
            var progressCircle = $scope.find('.circle-progress-bar .circle');
            var percentageLabel = $scope.find('.percent');
            var raisedTag = $scope.find('.raised');

            var xraised = 0;

            // Helper: adds commas to large numbers
            function formatNumber(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            if (data_settings.length > 0) {
                // Calculate the true percentage
                if(raised >= target){
                    xraised = target;
                }else {
                    xraised = raised;
                }

                var rawPercentage = (xraised / target) * 100;

                // The bar stays capped at 100%, but text can exceed 100%
                var barPercentage = Math.min(rawPercentage, 100);

                // Reset the bar for animation
                progressBar.css('width', '0%');

                // Animate the raised amount text
                if (raisedTag.length > 0) {
                    var raisedStart = 0;
                    var raisedEnd = Math.round(raised);
                    // We'll count in 100 steps or as many points as your percentage
                    var steps = 100;
                    var stepTime = Math.max(1, Math.floor(duration / steps));
                    var stepValue = raisedEnd / steps;

                    var raisedInterval = setInterval(function () {
                        if (raisedStart < raisedEnd) {
                            raisedStart += stepValue;
                            if (raisedStart > raisedEnd) {
                                raisedStart = raisedEnd;
                            }
                            raisedTag.text(settings.symbol + formatNumber(Math.round(raisedStart)));
                        } else {
                            clearInterval(raisedInterval);
                        }
                    }, stepTime);
                }

                // Animate the percentage text: allows going past 100%
                if (percentageLabel.length > 0) {
                    var percentStart = 0;
                    var percentEnd = Math.round(rawPercentage);
                    if (percentEnd < 0) { percentEnd = 0; }
                    var steps2 = Math.abs(percentEnd - percentStart) || 1;
                    var percentStepTime = Math.max(1, Math.floor(duration / steps2));


                    var percentInterval = setInterval(function () {
                        if (percentStart < percentEnd) {
                            percentStart++;
                            percentageLabel.text(percentStart + '%');
                            if(percentStart > 50){
                                percentageLabel.addClass('full');
                            }
                        } else {
                            clearInterval(percentInterval);
                        }
                    }, percentStepTime);
                }

                // Animate the bar and circle
                // This keeps the bar at maximum 100% for visuals
                const radius = 50;
                const circumference = 2 * Math.PI * radius;
                const offset = circumference - (barPercentage / 100) * circumference;

                // Animate horizontal bar
                progressBar.animate({ minWidth: barPercentage + '%' }, duration);

                // If you have a circle <svg> with a dasharray set to circumference:
                if (progressCircle.length) {
                    progressCircle.animate({ strokeDashoffset: offset }, duration);
                }
            }
        }
    };

    // Elementor hooks
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_donation_progress.default',
            skyweb_donation_progress
        );
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_donation_progress_2.default',
            skyweb_donation_progress
        );
    });
}(jQuery));
