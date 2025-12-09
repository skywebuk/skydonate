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
                $('.active-donation-frequency .skyweb-radio.daily').show();
                $('#skyweb-donation-fields-container .skyweb-input-group.daily-field').show();
            } else {
                $('.daily-date-card').hide();
                $('.active-donation-frequency .skyweb-radio.daily').hide();
                $('#skyweb-donation-fields-container .skyweb-input-group.daily-field').hide();
            }

            if (showMonthlyChecked) {
                $('.active-donation-frequency .skyweb-radio.monthly').show();
                $('#skyweb-donation-fields-container .skyweb-input-group.monthly-field').show();
            } else {
                $('.active-donation-frequency .skyweb-radio.monthly').hide();
                $('#skyweb-donation-fields-container .skyweb-input-group.monthly-field').hide();
            }

            if (showOnceChecked) {
                $('.active-donation-frequency .skyweb-radio.once').show();
                $('#skyweb-donation-fields-container .skyweb-input-group.once-field').show();
            } else {
                $('.active-donation-frequency .skyweb-radio.once').hide();
                $('#skyweb-donation-fields-container .skyweb-input-group.once-field').hide();
            }

            if (showWeeklyChecked) {
                $('.active-donation-frequency .skyweb-radio.weekly').show();
                $('#skyweb-donation-fields-container .skyweb-input-group.weekly-field').show();
            } else {
                $('.active-donation-frequency .skyweb-radio.weekly').hide();
                $('#skyweb-donation-fields-container .skyweb-input-group.weekly-field').hide();
            }

            if (showYearlyChecked) {
                $('.active-donation-frequency .skyweb-radio.yearly').show();
                $('#skyweb-donation-fields-container .skyweb-input-group.yearly-field').show();
            } else {
                $('.active-donation-frequency .skyweb-radio.yearly').hide();
                $('#skyweb-donation-fields-container .skyweb-input-group.yearly-field').hide();
            }

            // Ensure a visible radio button is selected
            if (!$('.active-donation-frequency input[type="radio"]:checked').is(':visible')) {
                $('.active-donation-frequency input[type="radio"]:visible').first().prop('checked', true);
            }
        }

        // Initial load
        toggleActionButtons();

        // Checkbox change listener
        $('.button-display-options input[type="checkbox"]').on('change', function() {
            toggleActionButtons();
        });

        
        // Add custom donation option
        $('.add_custom_option').on('click', function() {
            var count = $('#skyweb-donation-fields-container .skyweb-donation-fields').length;

            // ----- Default arrays -----
            const onceDefaults    = [50, 100, 200, 1000, 500, 300];
            const dailyDefaults   = [200, 100, 50, 30, 25, 15];
            const weeklyDefaults  = [200, 100, 50, 30, 25, 15];
            const monthlyDefaults = [200, 100, 50, 30, 25, 15];
            const yearlyDefaults  = [200, 100, 50, 300, 500, 1000];

            // Use existing index for defaults
            const onceValue    = onceDefaults[count]    ?? 0;
            const dailyValue   = dailyDefaults[count]   ?? 0;
            const weeklyValue  = weeklyDefaults[count]  ?? 0;
            const monthlyValue = monthlyDefaults[count] ?? 0;
            const yearlyValue  = yearlyDefaults[count]  ?? 0;

            var newOption = `
                <div class="skyweb-donation-fields">
                    <div class="header">
                        <h4 class="title">${wp.i18n.__('Donation Option', 'skydonate')} ${count + 1}</h4>
                        <button type="button" class="action toggle-option"><span class="toggle-indicator"></span></button>
                    </div>
                    <div class="fields">

                        <div class="skyweb-input-group">
                            <label>${wp.i18n.__('Label', 'skydonate')}</label>
                            <input type="text" class="short" name="custom_option_label[]" placeholder="${wp.i18n.__('Option label', 'skydonate')}">
                        </div>

                        <div class="skyweb-input-group once-field">
                            <label>${wp.i18n.__('One-Time', 'skydonate')}</label>
                            <input type="number" class="short" name="custom_option_price[]" value="${onceValue}" min="0">
                        </div>

                        <div class="skyweb-input-group daily-field">
                            <label>${wp.i18n.__('Daily', 'skydonate')}</label>
                            <input type="number" class="short" name="custom_option_daily[]" value="${dailyValue}" min="0">
                        </div>

                        <div class="skyweb-input-group weekly-field">
                            <label>${wp.i18n.__('Weekly', 'skydonate')}</label>
                            <input type="number" class="short" name="custom_option_weekly[]" value="${weeklyValue}" min="0">
                        </div>

                        <div class="skyweb-input-group monthly-field">
                            <label>${wp.i18n.__('Monthly', 'skydonate')}</label>
                            <input type="number" class="short" name="custom_option_monthly[]" value="${monthlyValue}" min="0">
                        </div>

                        <div class="skyweb-input-group yearly-field">
                            <label>${wp.i18n.__('Yearly', 'skydonate')}</label>
                            <input type="number" class="short" name="custom_option_yearly[]" value="${yearlyValue}" min="0">
                        </div>

                        <div class="skyweb-input-group">
                            <label>${wp.i18n.__('Default', 'skydonate')}</label>
                            <input type="radio" name="default_option" value="${count + 1}">
                        </div>

                        <div class="skyweb-input-group">
                            <label>${wp.i18n.__('Hide', 'skydonate')}</label>
                            <input type="checkbox" name="publish_project_item[]" value="${count + 1}">
                        </div>

                        <div class="skyweb-input-group">
                            <button type="button" class="button remove_custom_option">${wp.i18n.__('Remove', 'skydonate')}</button>
                        </div>

                    </div>
                </div>
            `;

            $('#skyweb-donation-fields-container').append(newOption);
            toggleActionButtons();
        });



        function initCustomOptionToggle() {
            // Set default state
            $('.skyweb-donation-fields:first-child .header').addClass('active');
            $('.skyweb-donation-fields:not(:first-child) .fields').hide();

            $(document).on('click', '.skyweb-donation-fields .header', function() {
                var $fields = $(this).siblings('.fields');

                // Close all
                $('.skyweb-donation-fields .header').removeClass('active');
                $('.skyweb-donation-fields .fields').slideUp();

                // If this one was closed, open it
                if (!$fields.is(':visible')) {
                    $(this).addClass('active');
                    $fields.slideDown();
                }
            });

        }
        initCustomOptionToggle();


        // Remove custom option
        $(document).on('click', '.remove_custom_option', function() {
            $(this).closest('.skyweb-donation-fields').remove();
            toggleActionButtons();
        });

        var $prefixCheckbox = $('#enable_title_prefix'); 
        var $titlePrefix = $('.title_prefix_row');
        $prefixCheckbox.on('change', function() {
            $titlePrefix.toggle();
        });

        // ==============================
        // Make Donation Fields Sortable
        // ==============================
        if (typeof $.fn.sortable !== 'undefined') {
            $('#skyweb-donation-fields-container').sortable({
                items: '.skyweb-donation-fields',
                handle: '.header', // drag using header only
                placeholder: 'skyweb-sortable-placeholder',
                start: function (event, ui) {
                    ui.placeholder.height(ui.item.outerHeight());
                },
                stop: function (event, ui) {
                    // Reorder titles after sorting
                    $('#skyweb-donation-fields-container .skyweb-donation-fields').each(function (index) {
                        $(this).find('.header .title').text('Donation Option ' + (index + 1));
                        // Update radio and checkbox values to match new order
                        $(this).find('input[name="default_option"]').val(index + 1);
                        $(this).find('input[name="publish_project_item[]"]').val(index + 1);
                    });
                }
            }).disableSelection();
        } else {
            console.warn('jQuery UI Sortable is not loaded. Drag-and-drop reordering will not work.');
        }



    });
})(jQuery);
