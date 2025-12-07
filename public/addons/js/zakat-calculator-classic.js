(function ($) {
    var skydonate_zakat_calculator_classic = function ($scope, $) {
        var zakat_calculator = $scope.find('.classic-zakat-calculator').eq(0);
        var pricing = zakat_calculator.data('settings');
        var toggle_button = zakat_calculator.find('.zakat-toggle-button');
        var assets_input = zakat_calculator.find('.zakat-calculator-assets .zakat-input');
        var liabilities_input = zakat_calculator.find('.zakat-calculator-liabilities .zakat-input');
        var total_amount_display = zakat_calculator.find('.zakat-calculator-footer .total-amount .total');
        var metal_input = zakat_calculator.find('.zakat-calculator-header input[name="metal"]');

        if(pricing.preview_id != ''){
            var pre_assets = $('#'+pricing.preview_id).find('.assets .num span');
            var pre_liabilities = $('#'+pricing.preview_id).find('.liabilities .num span');
            var pre_zakatable = $('#'+pricing.preview_id).find('.zakatable .num span');
            var pre_to_pay = $('#'+pricing.preview_id).find('.to_pay .num span');
            var pre_footer = $('#'+pricing.preview_id).find('.preview_footer');
            var pre_input = $('#'+pricing.preview_id).find('.zakat_input');
        }
        
        // Nisab values in grams
        var nisabGold = 87.48;
        var nisabSilver = 612.36;

        function formatWithCommas(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function getSelectedMetalPrice() {
            var selectedMetal = metal_input.filter(':checked').val(); // Get selected metal (gold/silver)
            var currency = "gbp"; // Default currency

            if (pricing && pricing.data) {
                if (selectedMetal === "gold" && pricing.data.gold[currency]) {
                    return parseFloat(pricing.data.gold[currency]) || 0;
                }
                if (selectedMetal === "silver" && pricing.data.silver[currency]) {
                    return parseFloat(pricing.data.silver[currency]) || 0;
                }
            }
            return 0;
        }

        function calculateZakat() {
            var total_assets = 0;
            var total_liabilities = 0;

            assets_input.each(function () {
                var value = parseFloat($(this).val()) || 0;
                total_assets += value;
            });

            liabilities_input.each(function () {
                var value = parseFloat($(this).val()) || 0;
                total_liabilities += value;
            });

            var net_assets = total_assets - total_liabilities;
            var metal_price = getSelectedMetalPrice();
            var nisab_threshold = metal_input.filter(':checked').val() === "gold" ? nisabGold : nisabSilver;
            var nisab_value = metal_price * nisab_threshold;

            var zakat = net_assets >= nisab_value ? net_assets * 0.025 : 0;


            total_amount_display.text(formatWithCommas(zakat.toFixed(2)));

            if (pricing.preview_id != '') {
                if(zakat > 0){
                    pre_footer.slideDown();
                    pre_input.val(zakat.toFixed(2));
                }else {
                    pre_footer.slideUp();
                    pre_input.val('');
                }
                // Update the preview values with the actual values
                pre_assets.text(formatWithCommas(total_assets.toFixed(2)));
                pre_liabilities.text(formatWithCommas(total_liabilities.toFixed(2)));
                pre_zakatable.text(formatWithCommas(net_assets.toFixed(2)));
                pre_to_pay.text(formatWithCommas(zakat.toFixed(2)));
            }

        }

        toggle_button.on('click', function () {
            $(this).toggleClass('active');
            $(this).closest('.zakat-form-group').find('.zakat-info').slideToggle();
        });

        // Recalculate when assets, liabilities, or metal selection changes
        assets_input.on('input', calculateZakat);
        liabilities_input.on('input', calculateZakat);
        metal_input.on('change', calculateZakat); // Detect metal change
    };

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skydonate_zakat_calculator_classic.default', skydonate_zakat_calculator_classic);
    });
}(jQuery));
