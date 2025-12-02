jQuery(document).ready(function($) {
    // Create tooltip element if not exists
    if ( $('.tooltip-text').length === 0 ) {
        $('body').append('<span class="tooltip-text"></span>');
    }
    $(".donation-start-date, .donation-end-date").each(function () {
        let minDate = $(this).attr('min') || null;
        let maxDate = $(this).attr('max') || null;
        $(this).datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDate ? new Date(minDate) : null,
            maxDate: maxDate ? new Date(maxDate) : null
        });
    });
    $('.custom-tooltip').hover(function() {
        var tip = $(this).attr('data-tip');
        var tooltip = $('.tooltip-text');
        tooltip.text(tip);
        // Get position of the icon
        var offset = $(this).offset();
        tooltip.css({
            top: offset.top - $(this).outerHeight() - 8,
            left: offset.left
        });
        tooltip.addClass('active');
    }, function() {
        $('.tooltip-text').removeClass('active');
    });
});
