;(function($){
    $(document).ready(function() {
        // Function to download CSV
        function downloadCSV(csvData, filename) {
            var blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement("a");
            var url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", filename);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        // Display temporary message
        function displayTemporaryMessage(message, color) {
            var $msg = $('<div>')
                .text(message)
                .css({
                    background: color === 'green' ? '#4CAF50' : '#f44336',
                    color: '#fff',
                    padding: '10px 15px',
                    marginTop: '10px',
                    borderRadius: '3px',
                    position: 'fixed',
                    top: '10px',
                    right: '10px',
                    zIndex: 9999
                });
            $('body').append($msg);
            setTimeout(function () {
                $msg.fadeOut(400, function () { $(this).remove(); });
            }, 3000);
        }
        // Handle Full Export Form Submit
        $('.gift-aid-full-export').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            $btn.addClass('loading').prop('disabled', true);

            let page = 1;
            let csvContent = '';

            function processBatch() {
                $.ajax({
                    url: skydonation_setting.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'export_gift_aid_orders_ajax',
                        nonce: skydonation_setting.nonce,
                        page: page
                    },
                    success: function (response) {
                        if (response.success) {
                            if (response.data.csv) {
                                csvContent += response.data.csv;
                            }

                            if (response.data.done) {
                                downloadCSV(csvContent, 'gift_aid_orders.csv');
                                $btn.removeClass('loading').prop('disabled', false);
                                displayTemporaryMessage('Gift Aid export completed successfully.', 'green');
                            } else {
                                page++;
                                processBatch(); // next batch
                            }
                        } else {
                            $btn.removeClass('loading').prop('disabled', false);
                            displayTemporaryMessage(response.data || 'Error exporting CSV', 'red');
                            console.log('Server response:', response);
                        }
                    },
                    error: function (xhr) {
                        $btn.removeClass('loading').prop('disabled', false);
                        displayTemporaryMessage('Error exporting CSV', 'red');
                        console.log('AJAX Error:', xhr);
                    }
                });
            }

            processBatch();
        });
        // Handle Date Range Export Form Submit
        $('form.gift-aid-date-export').on('submit', function (e) {
            e.preventDefault();

            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            var start_date = $form.find('input[name="start_date"]').val();
            var end_date = $form.find('input[name="end_date"]').val();

            if (!start_date || !end_date) {
                displayTemporaryMessage('Please select both start and end dates.', 'red');
                return;
            }

            $btn.addClass('loading').prop('disabled', true);

            let page = 1;
            let csvContent = '';
            let filename = 'gift_aid_orders_' + start_date + '_to_' + end_date + '.csv';

            function processBatch() {
                $.ajax({
                    url: skydonation_setting.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'export_gift_aid_orders_by_date',
                        nonce: skydonation_setting.nonce,
                        start_date: start_date,
                        end_date: end_date,
                        page: page
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            if (response.data.csv) {
                                csvContent += response.data.csv;
                            }

                            filename = response.data.filename || filename;

                            if (response.data.done) {
                                downloadCSV(csvContent, filename);
                                $btn.removeClass('loading').prop('disabled', false);
                                displayTemporaryMessage('Gift Aid export completed successfully.', 'green');
                            } else {
                                page++;
                                processBatch(); // next batch
                            }
                        } else {
                            $btn.removeClass('loading').prop('disabled', false);
                            displayTemporaryMessage(response.data || 'Error exporting CSV', 'red');
                            console.log('Server response:', response);
                        }
                    },
                    error: function (xhr) {
                        $btn.removeClass('loading').prop('disabled', false);
                        displayTemporaryMessage('Error exporting CSV', 'red');
                        console.log('AJAX Error:', xhr);
                    }
                });
            }

            processBatch();
        });
        // Initialize Select2 if available
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select_items').select2({
                placeholder: "Select an option",
                allowClear: true,
            });

            $('.select_type_items').select2({
                placeholder: "Select an option",
                allowClear: true,
                tags: true,
                tokenSeparators: [',', ' ']
            });
        }

        $('.skydonation-general-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_general_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
        
        $('.skydonation-advanced-form').on('submit', function (e) {
            
            e.preventDefault(); // Prevent default form submission
            
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_advanced_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skydonation-currency-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_currency_changer_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        
        $('.skydonation-donation-fees-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_fees_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skyweb-address-autoload-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'save_address_autoload_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skydonation-colors-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serializeArray();
            var $btn = $('.skydonation-button');

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: skydonation_setting.ajax_url,
                method: 'POST',
                data: {
                    action: 'save_skydonation_color_settings',
                    nonce: skydonation_setting.nonce,
                    formData: formData
                },
                success: function(response) {
                    let messageText = response.success
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function(xhr, status, error) {
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function() {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });

        });
        
        $('.skyweb-gift-aid-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            const $form = $(this);
            const $btn = $form.find('.skydonation-button');
            const formData = $form.serializeArray();

            // Add loading state to button
            $btn.addClass('loading').prop('disabled', true);

            // Perform AJAX request
            $.ajax({
                url: skydonation_setting.ajax_url,
                method: 'POST',
                data: {
                    action: 'save_skyweb_gift_aid_settings',
                    nonce: skydonation_setting.nonce,
                    formData: formData
                },
                success: function (response) {
                    const messageText = response.success 
                        ? response.data 
                        : `Error: ${response.data}`;
                    const messageColor = response.success ? 'green' : 'red';

                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skydonation-api-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();
            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_api_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skydonation-extra-donation-form').on('submit', function (e) {
            e.preventDefault();

            var formDataArray = $(this).serializeArray();
            

            // Convert array into object for easy access
            var formDataObject = {};
            formDataArray.forEach(function (field) {
                formDataObject[field.name] = field.value;
            });

            // Extract donation items
            var donationItems = [];
            var tempItem = {};

            formDataArray.forEach(function (field) {
                if (field.name === 'donation_item[][id]') {
                    tempItem.id = field.value;
                } 
                else if (field.name === 'donation_item[][amount]') {
                    tempItem.amount = field.value;
                }
                else if (field.name === 'donation_item[][title]') {
                    tempItem.title = field.value;

                    // ✅ Push item only after all three fields are captured
                    donationItems.push(tempItem);
                    tempItem = {};
                }
            });

            var data = {
                action: 'skydonation_extra_donation_settings', // Must match WP hook
                nonce: skydonation_setting.nonce,
                donation_items: donationItems
            };


            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);
            
            $.ajax({
                url: skydonation_setting.ajax_url,
                method: 'POST',
                data: data,
                success: function (response) {
                    let messageText = response.success
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        $('.skydonation-notification-form').on('submit', function (e) {
            e.preventDefault(); // Prevent default form submission
            
            // Serialize form data into an array of objects
            var formData = $(this).serializeArray();

            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);

            // AJAX request to handle form submission
            $.ajax({
                url: skydonation_setting.ajax_url, // Use the configured AJAX URL
                method: 'POST', // Use POST method
                data: {
                    action: 'skydonation_notification_settings', // Action name
                    nonce: skydonation_setting.nonce, // Security nonce
                    formData: formData // Form data
                },
                success: function (response) {
                    // Check the response and display a message
                    let messageText = response.success 
                        ? response.data
                        : `Error: ${response.data}`;
                    let messageColor = response.success ? 'green' : 'red';

                    // Call the display message function
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function (xhr, status, error) {
                    // Display AJAX error message
                    displayTemporaryMessage(`AJAX Error: ${error}`, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
        
        $('.skydonation-widget-form').on('submit', function(e) {
            e.preventDefault();

            let widgets = {};
            // Collect widgets from checkboxes
            $('.skydonation-widgets input[type="checkbox"]').each(function() {
                widgets[$(this).attr('id')] = $(this).is(':checked') ? 'on' : 'off';
            });


            // Add loading state
            var $btn = $('.skydonation-button');
            $btn.addClass('loading').prop('disabled', true);


            // AJAX request to save widgets
            $.ajax({
                url: skydonation_setting.ajax_url,
                method: 'POST',
                data: {
                    action: 'skydonation_widget_save_setting',
                    nonce: skydonation_setting.nonce,
                    widgets: widgets,
                },
                success: function(response) {
                    let messageText = response.success ? response.data : 'Error: ' + response.data;
                    let messageColor = response.success ? 'green' : 'red';
                    displayTemporaryMessage(messageText, messageColor);
                },
                error: function(xhr, status, error) {
                    displayTemporaryMessage('AJAX Error: ' + error, 'red');
                },
                complete: function () {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });


        // Toggle all checkboxes
        $('#toggleAll').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.skydonation-checkboxs input[type="checkbox"]').prop('checked', isChecked);
        });
    });

})(jQuery);
