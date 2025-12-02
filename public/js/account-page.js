;(function($){
    $(document).ready(function() {

        $('#gift-aid-form').on('submit', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $button = $form.find('button[type="submit"]');
            var gift_aid_value = $form.find('input[name="gift_aid_status"]').is(':checked') ? 'yes' : 'no';
            var $message = $('#gift-aid-message');

            // Add loading state and disable button
            $button.prop('disabled', true).addClass('loading');
            $message.removeClass('success-message error-message').html('Saving...');

            $.post(account_page_ajax.ajax_url, {
                action: 'save_gift_aid',
                gift_aid_status: gift_aid_value,
                gift_aid_nonce: account_page_ajax.nonce
            }, function(response) {
                if(response.success) {
                    $message.addClass('success-message').html('<span style="color:green;">' + response.data.message + '</span>');
                } else {
                    $message.addClass('error-message').html('<span style="color:red;">' + response.data.message + '</span>');
                }
            }).always(function() {
                // Remove loading state and enable button
                $button.prop('disabled', false).removeClass('loading');
            });
        });

    });
})(jQuery);
