;(function ($) {
    var skydonate_icon_list = function ($scope, $) {
        var slider_elem = $scope.find('.swiper-container').eq(0);
        slider_elem.fadeIn(200);
        if (slider_elem.length > 0) {
            let settings = slider_elem.data('settings') || {};
            let slpaginate = false;
            let slloop = settings['slloop'] || false;
            let sleffect = settings['sleffect'] || 'slide';
            let slautoplaydelay = settings['slautoplaydelay'] || 5000;
            let slanimation_speed = settings['slanimation_speed'] || 600;
            let coverflow_rotate = parseInt(settings['coverflow_rotate'] || 0);
            let coverflow_stretch = parseInt(settings['coverflow_stretch'] || 0);
            let coverflow_depth = parseInt(settings['coverflow_depth'] || 0);
            let coverflow_shadow = settings['coverflow_shadow'] === 'yes';
            let sldisplay_columns = parseInt(settings['sldisplay_columns'] || 1);
            let slcenter = settings['slcenter'] || false;
            let sldirection = settings['sldirection'] || 'horizontal';
            let slcenter_padding = parseInt(settings['slcenter_padding'] || 0);

            if (settings['slpaginate']) {
                slpaginate = {
                    el: $scope.find('.swiper-pagination'),
                    clickable: true,
                    type: 'bullets',
                    renderBullet: function (i) {
                        return `<span class="dot swiper-pagination-bullet"><svg><circle style="animation-duration: ${slautoplaydelay / 1000}s;" cx="11" cy="11" r="10"></circle></svg></span>`;
                    }
                };
            }

            let laptop_width = parseInt(settings['laptop_width'] || 1024);
            let tablet_width = parseInt(settings['tablet_width'] || 768);
            let mobile_width = parseInt(settings['mobile_width'] || 480);
            let laptop_padding = parseInt(settings['laptop_padding'] || 10);
            let tablet_padding = parseInt(settings['tablet_padding'] || 10);
            let mobile_padding = parseInt(settings['mobile_padding'] || 10);
            let laptop_display_columns = parseInt(settings['laptop_display_columns'] || 2);
            let tablet_display_columns = parseInt(settings['tablet_display_columns'] || 1);
            let mobile_display_columns = parseInt(settings['mobile_display_columns'] || 1);

            var swiperOptions = {
                loop: slloop,
                speed: slanimation_speed,
                centeredSlides: slcenter,
                slidesPerView: mobile_display_columns,
                spaceBetween: mobile_padding,
                direction: sldirection,
                effect: sleffect,
                coverflowEffect: {
                    rotate: coverflow_rotate,
                    stretch: coverflow_stretch,
                    depth: coverflow_depth,
                    modifier: 1,
                    slideShadows: coverflow_shadow,
                },
                scrollbar: {
                  el: $scope.find('.swiper-scrollbar'),
                  draggable: true,
                },
                autoplay: {
                    delay: slautoplaydelay,
                    disableOnInteraction: false
                },
                navigation: {
                    prevEl: $scope.find('.swiper-navigation .swiper-prev')?.get(0),
                    nextEl: $scope.find('.swiper-navigation .swiper-next')?.get(0),
                },
                pagination: slpaginate,
                breakpoints: {
                    [mobile_width]: {
                        slidesPerView: tablet_display_columns,
                        spaceBetween: tablet_padding,
                    },
                    [tablet_width]: {
                        slidesPerView: laptop_display_columns,
                        spaceBetween: laptop_padding,
                    },
                    [laptop_width]: {
                        slidesPerView: sldisplay_columns,
                        spaceBetween: slcenter_padding,
                    },
                },
            };

            var swiper = new Swiper(slider_elem[0], swiperOptions);
        }

    }
    var modal_actions = function ($scope, $) {


        // Define the target button element inside the $scope
        var $button = $scope.find('.donation-modal-button'); // Replace '.your-button-selector' with your actual selector

        // Set up click event for each button
        $button.on('click', function() {
            var target_id = $(this).data('target'); // Corrected to include quotes around 'target'
            $scope.find('#' + target_id).fadeIn();  // Find element by ID and fade it in
            $('html').css('overflow', 'hidden'); 
        });

        // Hide modal on 'added_to_cart' event
        $('html').on('added_to_cart', function() {
            $('.quick-modal').fadeOut();
            $('html').css('overflow', ''); 
        });

        // Close modal when clicking the close button or overlay
        $scope.on('click', '.quick-modal-close, .quick-modal-overlay', function() {
            var modal = $(this).closest('.quick-modal');
            modal.fadeOut();
            $('html').css('overflow', ''); 
        });

    }

    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_icon_list.default', skydonate_icon_list);
        elementorFrontend.hooks.addAction('frontend/element_ready/skyweb_donation_icon_list.default', modal_actions);
    });
}(jQuery));
