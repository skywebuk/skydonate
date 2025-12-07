;(function($) {
    $(document).ready(function() {

        function convertCurrency(baseCurrency, targetCurrency, amount) {
            var rates = skydonate_currency_changer_ajax.rates || {};
            var from = (baseCurrency || '').toUpperCase();
            var to = (targetCurrency || '').toUpperCase();

            if (!rates[from] || !rates[to]) {
                console.warn('Missing currency rate:', from, to);
                return amount; // fallback to original
            }

            var rate = rates[to] / rates[from];
            var converted = amount * rate;

            // Use toFixed for precise rounding if needed
            return Math.round((converted * 100) / 100); 
        }

        // ✅ Handle currency selectors
        $('.skydonate-currency-select').each(function() {
            var $select = $(this);
            var $wrapper = $select.closest('.skydonate-currency-wrapper');
            var $label = $wrapper.find('.currency-symbol');

            // 🔹 Set initial symbol
            var $selected = $select.find(':selected');
            var initialValue = $selected.val();
            var initialSymbol = $selected.data('symbol') || initialValue;

            if (initialSymbol) {
                $label.text(initialSymbol);
                $('.currency-symbol').html(initialSymbol); // global update
            }

            // 🔹 Handle change
            $select.on('change', function() {
                var $this = $(this);
                var selectedCurrency = $this.val();
                var symbol = $this.find(':selected').data('symbol') || selectedCurrency;

                // Update all currency labels and selects
                $('.currency-symbol').html(symbol);
                $('.skydonate-currency-select').val(selectedCurrency);

                // 🔹 AJAX: save selected currency
                $.ajax({
                    url: skydonate_currency_changer_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'skydonate_change_currency',
                        currency: selectedCurrency,
                        nonce: skydonate_currency_changer_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // 🔁 Refresh WooCommerce fragments
                            $(document.body).trigger('wc_fragment_refresh');
                        } else {
                            console.error('Currency change error:', response.data || response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });

                // 🔹 Convert all donation buttons
                $('.donation-btn').each(function() {
                    var $btn = $(this);
                    var originalAmount = parseFloat($btn.data('original'));

                    if (!isNaN(originalAmount)) {
                        var converted = convertCurrency(
                            skydonate_currency_changer_ajax.woocommerce_currency,
                            selectedCurrency,
                            originalAmount
                        );
                        $btn.data('amount', converted);
                        $btn.find('.btn-amount').text(converted);
                    }
                });

                // 🔹 Update donation forms’ input fields
                $('.donation-form').each(function() {
                    var $form = $(this);
                    var $activeBtn = $form.find('.donation-amount-group.active .donation-btn.active');
                    var amount = $activeBtn.data('amount');
                    if (amount) {
                        $form.find('input.custom-amount-input').val(amount);
                    }
                });
            });
        });

    });
})(jQuery);
