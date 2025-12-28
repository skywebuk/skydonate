(function($) {
    $(document).on('ready', function() {

        // ==============================
        // Project Closed Settings Toggle
        // ==============================
        function toggleProjectClosedFields() {
            const isChecked = $('input[name="close_project"]').is(':checked');
            $('.project-closed-message-field, .project-closed-title-field').toggle(isChecked);
        }
        // Initial toggle on page load
        toggleProjectClosedFields();
        // Toggle when the checkbox changes
        $('input[name="close_project"]').on('change', toggleProjectClosedFields);


        // ==============================
        // End Date Field Toggle
        // ==============================
        function toggleEndDateField() {
            const isEnabled = $('input[name="enable_end_date"]').is(':checked');
            $('.end-date-group-field').toggle(isEnabled);
        }
        // Initial toggle on page load
        toggleEndDateField();
        // Toggle when the checkbox changes
        $('input[name="enable_end_date"]').on('change', toggleEndDateField);



        // Start Date Field Toggle
        // ==============================
        function toggleStartDateField() {
            const isEnabled = $('input[name="enable_start_date"]').is(':checked');
            $('.start-date-group-field').toggle(isEnabled);
        }
        // Initial toggle on page load
        toggleStartDateField();
        // Toggle when the checkbox changes
        $('input[name="enable_start_date"]').on('change', toggleStartDateField);



        $('.woocommerce-help-tip').each(function() {
            $(this).tipTip({
                attribute: 'data-tip', // Use the custom data attribute for the tooltip content
                fadeIn: 50,            // Fade in duration in ms
                fadeOut: 50,           // Fade out duration in ms
                delay: 200             // Delay before showing the tooltip
            });
        });

        // ==============================
        // Donation Frequency Visibility Toggle
        // ==============================
        function toggleActionButtons() {
            var $showDaily = $('input[name="button_visibility[]"][value="show_daily"]');
            var $showMonthly = $('input[name="button_visibility[]"][value="show_monthly"]');
            var $showOnce = $('input[name="button_visibility[]"][value="show_once"]');
            var $showWeekly = $('input[name="button_visibility[]"][value="show_weekly"]');
            var $showYearly = $('input[name="button_visibility[]"][value="show_yearly"]');

            var showDailyChecked = $showDaily.is(':checked');
            var showMonthlyChecked = $showMonthly.is(':checked');
            var showOnceChecked = $showOnce.is(':checked');
            var showWeeklyChecked = $showWeekly.is(':checked');
            var showYearlyChecked = $showYearly.is(':checked');

            var checkedCount = [showDailyChecked, showMonthlyChecked, showOnceChecked, showWeeklyChecked, showYearlyChecked].filter(Boolean).length;

            if (checkedCount >= 1) {
                $('.active-donation-frequency').show();
            } else {
                $('.active-donation-frequency').hide();
            }

            if (showDailyChecked) {
                $('.daily-date-card').show();
                $('.active-donation-frequency .skydonate-radio.daily').show();
            } else {
                $('.daily-date-card').hide();
                $('.active-donation-frequency .skydonate-radio.daily').hide();
            }

            if (showMonthlyChecked) {
                $('.active-donation-frequency .skydonate-radio.monthly').show();
            } else {
                $('.active-donation-frequency .skydonate-radio.monthly').hide();
            }

            if (showOnceChecked) {
                $('.active-donation-frequency .skydonate-radio.once').show();
            } else {
                $('.active-donation-frequency .skydonate-radio.once').hide();
            }

            if (showWeeklyChecked) {
                $('.active-donation-frequency .skydonate-radio.weekly').show();
            } else {
                $('.active-donation-frequency .skydonate-radio.weekly').hide();
            }

            if (showYearlyChecked) {
                $('.active-donation-frequency .skydonate-radio.yearly').show();
            } else {
                $('.active-donation-frequency .skydonate-radio.yearly').hide();
            }

            // Ensure a visible radio button is selected
            if (!$('.active-donation-frequency input[type="radio"]:checked').is(':visible')) {
                $('.active-donation-frequency input[type="radio"]:visible').first().prop('checked', true);
            }

            // Update donation tabs visibility
            updateDonationTabsVisibility();
        }

        // Initial load
        toggleActionButtons();

        // Checkbox change listener
        $('.button-display-options input[type="checkbox"]').on('change', function() {
            toggleActionButtons();
        });

        // ==============================
        // Donation Amount Tabs
        // ==============================

        // Tab switching
        $(document).on('click', '.skydonate-tab-btn', function() {
            var $btn = $(this);
            var tabKey = $btn.data('tab');

            // Update tab buttons
            $('.skydonate-tab-btn').removeClass('active');
            $btn.addClass('active');

            // Update tab panels - hide all, show selected
            $('.skydonate-tab-panel').removeClass('active').css('display', 'none');
            $('.skydonate-tab-panel[data-panel="' + tabKey + '"]').addClass('active').css('display', 'block');
        });

        // Default amounts for each frequency
        var defaultAmounts = {
            once: [50, 100, 200, 1000, 500, 300],
            daily: [200, 100, 50, 30, 25, 15],
            weekly: [200, 100, 50, 30, 25, 15],
            monthly: [200, 100, 50, 30, 25, 15],
            yearly: [200, 100, 50, 300, 500, 1000]
        };

        // Add default amounts to a tab if empty
        function addDefaultAmountsToTab(freq) {
            var $container = $('.skydonate-amounts-container[data-frequency="' + freq + '"]');
            if ($container.find('.skydonate-amount-row').length > 0) {
                return; // Already has amounts
            }

            var amounts = defaultAmounts[freq] || [50, 100, 200, 500, 1000, 300];
            var labelText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Label', 'skydonate') : 'Label';
            var amountText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Amount', 'skydonate') : 'Amount';
            var defaultText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Default', 'skydonate') : 'Default';
            var hideText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Hide', 'skydonate') : 'Hide';
            var removeText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Remove', 'skydonate') : 'Remove';
            var placeholderText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('e.g., Basic Support', 'skydonate') : 'e.g., Basic Support';

            amounts.forEach(function(amount, index) {
                var isDefault = (index === 0) ? ' checked' : '';
                var newRow = '<div class="skydonate-amount-row" data-index="' + index + '">' +
                    '<div class="skydonate-amount-fields">' +
                        '<div class="skydonate-field-group skydonate-field-label">' +
                            '<label>' + labelText + '</label>' +
                            '<input type="text" name="donation_options[' + freq + '][' + index + '][label]" value="" placeholder="' + placeholderText + '">' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-amount">' +
                            '<label>' + amountText + '</label>' +
                            '<input type="number" name="donation_options[' + freq + '][' + index + '][amount]" value="' + amount + '" min="0" step="0.01">' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-checkbox">' +
                            '<label>' +
                                '<input type="radio" name="donation_options[' + freq + '][default]" value="' + index + '"' + isDefault + '> ' +
                                defaultText +
                            '</label>' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-checkbox">' +
                            '<label>' +
                                '<input type="checkbox" name="donation_options[' + freq + '][' + index + '][publish]" value="1"> ' +
                                hideText +
                            '</label>' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-actions">' +
                            '<button type="button" class="button skydonate-remove-amount">' + removeText + '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';
                $container.append(newRow);
            });

            $container.closest('.skydonate-tab-panel').find('.skydonate-no-amounts-message').hide();
            initAmountsSortable();
        }

        // Update tab visibility based on frequency checkboxes
        function updateDonationTabsVisibility() {
            var $tabs = $('.skydonate-tab-btn');
            var $firstVisibleTab = null;

            $tabs.each(function() {
                var visibilityKey = $(this).data('visibility');
                var tabKey = $(this).data('tab');
                var $checkbox = $('input[name="button_visibility[]"][value="' + visibilityKey + '"]');
                var isChecked = $checkbox.is(':checked');

                // Show/hide tab button
                if (isChecked) {
                    $(this).show();
                    // Add default amounts if tab is empty
                    addDefaultAmountsToTab(tabKey);
                } else {
                    $(this).hide();
                }

                if (isChecked && !$firstVisibleTab) {
                    $firstVisibleTab = $(this);
                }
            });

            // Ensure at least one tab is active and visible
            if ($firstVisibleTab && !$('.skydonate-tab-btn.active:visible').length) {
                $firstVisibleTab.trigger('click');
            }
        }

        // Initial visibility check
        updateDonationTabsVisibility();

        // ==============================
        // Add Amount to ALL Tabs
        // ==============================
        $(document).on('click', '.skydonate-add-amount-all', function() {
            var frequencies = ['once', 'daily', 'weekly', 'monthly', 'yearly'];

            var labelText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Label', 'skydonate') : 'Label';
            var amountText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Amount', 'skydonate') : 'Amount';
            var defaultText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Default', 'skydonate') : 'Default';
            var hideText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Hide', 'skydonate') : 'Hide';
            var removeText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('Remove', 'skydonate') : 'Remove';
            var placeholderText = typeof wp !== 'undefined' && wp.i18n ? wp.i18n.__('e.g., Basic Support', 'skydonate') : 'e.g., Basic Support';

            frequencies.forEach(function(freq) {
                var $container = $('.skydonate-amounts-container[data-frequency="' + freq + '"]');
                var currentCount = $container.find('.skydonate-amount-row').length;
                var amountsArray = defaultAmounts[freq];
                var defaultAmount = (currentCount < amountsArray.length) ? amountsArray[currentCount] : amountsArray[0];
                var newIndex = currentCount;

                var newRow = '<div class="skydonate-amount-row" data-index="' + newIndex + '">' +
                    '<div class="skydonate-amount-fields">' +
                        '<div class="skydonate-field-group skydonate-field-label">' +
                            '<label>' + labelText + '</label>' +
                            '<input type="text" name="donation_options[' + freq + '][' + newIndex + '][label]" value="" placeholder="' + placeholderText + '">' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-amount">' +
                            '<label>' + amountText + '</label>' +
                            '<input type="number" name="donation_options[' + freq + '][' + newIndex + '][amount]" value="' + defaultAmount + '" min="0" step="0.01">' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-checkbox">' +
                            '<label>' +
                                '<input type="radio" name="donation_options[' + freq + '][default]" value="' + newIndex + '"> ' +
                                defaultText +
                            '</label>' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-checkbox">' +
                            '<label>' +
                                '<input type="checkbox" name="donation_options[' + freq + '][' + newIndex + '][publish]" value="1"> ' +
                                hideText +
                            '</label>' +
                        '</div>' +
                        '<div class="skydonate-field-group skydonate-field-actions">' +
                            '<button type="button" class="button skydonate-remove-amount">' + removeText + '</button>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                $container.append(newRow);
                $container.closest('.skydonate-tab-panel').find('.skydonate-no-amounts-message').hide();
            });

            // Reinitialize sortable for new rows
            initAmountsSortable();
        });

        // ==============================
        // Remove Amount from Single Tab
        // ==============================
        $(document).on('click', '.skydonate-remove-amount', function() {
            var $row = $(this).closest('.skydonate-amount-row');
            var $container = $row.closest('.skydonate-amounts-container');
            var freq = $container.data('frequency');

            $row.remove();

            // Re-index remaining rows
            reindexAmountRows($container, freq);

            // Show empty message if no amounts left
            if ($container.find('.skydonate-amount-row').length === 0) {
                $container.closest('.skydonate-tab-panel').find('.skydonate-no-amounts-message').show();
            }
        });

        // Re-index amount rows after removal or sorting
        function reindexAmountRows($container, freq) {
            $container.find('.skydonate-amount-row').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        // Update the index in the name attribute
                        var newName = name.replace(
                            /donation_options\[([^\]]+)\]\[\d+\]/,
                            'donation_options[$1][' + index + ']'
                        );
                        $(this).attr('name', newName);

                        // Update radio value for default selection
                        if ($(this).attr('type') === 'radio' && name.includes('[default]')) {
                            $(this).val(index);
                        }
                    }
                });
            });
        }

        // ==============================
        // Make Amount Rows Sortable (per tab)
        // ==============================
        function initAmountsSortable() {
            if (typeof $.fn.sortable !== 'undefined') {
                $('.skydonate-amounts-container').each(function() {
                    var $container = $(this);
                    var freq = $container.data('frequency');

                    // Destroy existing sortable if any
                    if ($container.hasClass('ui-sortable')) {
                        $container.sortable('destroy');
                    }

                    $container.sortable({
                        items: '.skydonate-amount-row',
                        handle: '.skydonate-amount-fields',
                        placeholder: 'skydonate-sortable-placeholder',
                        cursor: 'move',
                        start: function(event, ui) {
                            ui.placeholder.height(ui.item.outerHeight());
                        },
                        stop: function(event, ui) {
                            reindexAmountRows($container, freq);
                        }
                    });
                });
            }
        }

        // Initialize sortable on page load
        initAmountsSortable();


        // ==============================
        // Title Prefix Toggle
        // ==============================
        var $prefixCheckbox = $('#enable_title_prefix');
        var $titlePrefix = $('.title_prefix_row');
        $prefixCheckbox.on('change', function() {
            $titlePrefix.toggle();
        });

    });
})(jQuery);
