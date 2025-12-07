(function ($) {
    var skydonate_zakat_calculator = function ($scope, $) {
        var zakat_calculator = $scope.find('.zakat-calculator').eq(0);

        if (zakat_calculator.length > 0) {
            var pricing = zakat_calculator.data('settings');
            var product_id = zakat_calculator.data('product');

            // Function to format numbers with commas as thousands separators
            function formatWithCommas(number) {
                return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

            // Function to update the currency symbol
            function updateCurrencySymbol() {
                var selectedCurrency = zakat_calculator.find('.zakat-currency-select').val() || 'GBP';
                var currencySymbolMap = {
                    'GBP': '£',
                    'USD': '$',
                    'EUR': '€'
                };

                // Get the currency symbol or default to '£' if not found
                var currencySymbol = currencySymbolMap[selectedCurrency] || '£';

                // Check if the .currency-name element exists before updating
                var currencyNameElement = zakat_calculator.find('.currency-name');
                if (currencyNameElement.length) {
                    currencyNameElement.text(currencySymbol);
                } else {
                    console.error('.currency-name element not found.');
                }
            }

            function updateMetalValue() {
                var currencySymbolMap = {
                    'GBP': '£',
                    'USD': '$',
                    'EUR': '€'
                };

                var selectedCurrency = zakat_calculator.find('.zakat-currency-select').val() || 'GBP';
                var currencySymbol = currencySymbolMap[selectedCurrency] || '£'; // Default to GBP if not found

                // Ensure that getMetalPrice returns valid numbers
                var goldPricePerGram = parseFloat(getMetalPrice('gold', selectedCurrency)) || 0;
                var silverPricePerGram = parseFloat(getMetalPrice('silver', selectedCurrency)) || 0;

                // Update the UI with the calculated values
                $('.metal-value .currency').text(currencySymbol);

                if (goldPricePerGram > 0) {
                    $('.metal-value.gold .metal-title .value').text(formatWithCommas((goldPricePerGram * 87.48).toFixed(2)));
                    $('.metal-value.gold .metal-description .value').text(formatWithCommas(goldPricePerGram.toFixed(2)));
                } else {
                    console.error('Gold price per gram is not valid.');
                }

                if (silverPricePerGram > 0) {
                    $('.metal-value.silver .metal-title .value').text(formatWithCommas((silverPricePerGram * 612.36).toFixed(2)));
                    $('.metal-value.silver .metal-description .value').text(formatWithCommas(silverPricePerGram.toFixed(2)));
                } else {
                    console.error('Silver price per gram is not valid.');
                }
            }

            // Function to get the metal price from the JSON data
            function getMetalPrice(metal, currency) {
                return pricing.data[metal.toLowerCase()][currency.toLowerCase()];
            }

            // Function to calculate and display zakat
            function calculateZakat(goldPricePerGram, silverPricePerGram) {
                var selectedCurrency = zakat_calculator.find('.zakat-currency-select').val() || 'GBP';
                var metal = zakat_calculator.find('#zakat_metal_select').val() || 'XAG';

                // Collect loop data
                var totalAssets = 0;
                var totalLiabilities = 0;

                zakat_calculator.find('.zakat-form-group .loop-input').each(function () {
                    var inputName = $(this).attr('name');
                    var inputValue = parseFloat($(this).val()) || 0;

                    if (inputName.startsWith('assets')) {
                        totalAssets += inputValue;
                    } else if (inputName.startsWith('liabilities')) {
                        totalLiabilities += inputValue;
                    }
                });

                // Nisab values
                var nisabGold = 87.48; // grams
                var nisabSilver = 612.36; // grams

                // Convert Nisab to currency values
                var nisabValue = 0;
                if (metal === 'XAU') { // Gold
                    nisabValue = parseFloat((nisabGold * goldPricePerGram).toFixed(2));
                } else if (metal === 'XAG') { // Silver
                    nisabValue = parseFloat((nisabSilver * silverPricePerGram).toFixed(2));
                }

                // Check if total assets exceed Nisab
                if ((totalAssets - totalLiabilities) >= nisabValue) {
                    // Calculate zakat due (assuming 2.5% rate)
                    var zakatDue = (totalAssets - totalLiabilities) * 0.025;
                    // Display result
                    zakat_calculator.find('.zakat-preview').slideDown();
                    zakat_calculator.find('.zakat-preview .donate-button').slideDown();
                    zakat_calculator.find('.zakat-deu-amount').text(`${formatWithCommas(zakatDue.toFixed(2))} ${selectedCurrency}`);
                    zakat_calculator.find('.donate-now-button').attr('data-amount', zakatDue.toFixed(2));
                } else {
                    // If below Nisab, show message
                    zakat_calculator.find('.zakat-preview').slideDown();
                    zakat_calculator.find('.zakat-preview .donate-button').slideUp();
                    zakat_calculator.find('.zakat-deu-amount').text(`0.00 ${selectedCurrency}`);
                }
            }

            // Handle form submission
            zakat_calculator.find('#zakat-calculator-form').on('submit', function (event) {
                var selectedCurrency = zakat_calculator.find('.zakat-currency-select').val() || 'GBP';
                event.preventDefault(); // Prevent default form submission
                zakat_calculator.find('.zakat-submit-button').addClass('loading');

                var selectedMetal = zakat_calculator.find('#zakat_metal_select').val();
                // Get metal prices from JSON data
                var goldPricePerGram = getMetalPrice('gold', selectedCurrency);
                var silverPricePerGram = getMetalPrice('silver', selectedCurrency);

                zakat_calculator.find('.zakat-submit-button').removeClass('loading');
                calculateZakat(selectedMetal === 'XAU' ? goldPricePerGram : null, selectedMetal === 'XAG' ? silverPricePerGram : null); // Calculate zakat and update DOM
            });

            // Update the currency symbol on page load
            updateCurrencySymbol();

            // Update the currency symbol when the currency is changed
            zakat_calculator.find('.zakat-currency-select').on('change', function () {
                updateCurrencySymbol();
                updateMetalValue();
            });
            // Add Zakat to cart on button click
            zakat_calculator.find('.donate-now-button').on('click', function () {
                var $button = $(this);

                // disable + loading state
                $button.addClass('loading').prop('disabled', true);

                var zakatDueAmount = $button.data('amount');
                var productId = product_id; // Ensure this is set globally

                $.ajax({
                    type: 'POST',
                    url: skydonate_extra_donation_ajax.ajax_url,
                    data: {
                        action: 'add_extra_donation_to_cart',
                        product_id: productId,
                        amount: zakatDueAmount,
                        nonce: skydonate_extra_donation_ajax.nonce
                    },
                    success: function (response) {
                        if (response.success) {
                            window.location.href = skydonate_extra_donation_ajax.cart_url;
                        } else {
                            console.error(response.data || 'Something went wrong.');
                            $button.removeClass('loading').prop('disabled', false);
                        }
                    },
                    error: function () {
                        console.error('Failed to add Zakat Fund to the cart.');
                        $button.removeClass('loading').prop('disabled', false);
                    },
                    complete: function () {
                        // If redirect doesn’t happen, re-enable
                        $button.removeClass('loading').prop('disabled', false);
                    }
                });

                return false;
            });


        }
    };

    // Run this code under Elementor.
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skydonate_zakat_calculator.default', skydonate_zakat_calculator);
    });
}(jQuery));
