(function ($) {

    var skyweb_donation_recent_orders = function ($scope, $) {

        var $wrapper  = $scope.find('.recent-donation-wrapper');
        if (!$wrapper.length) return;

        var settings = $wrapper.data('settings') || {};

        // Safety fallback
        settings.product_ids    = settings.product_ids || [];
        settings.see_all_limit  = parseInt(settings.see_all_limit) || 10;
        settings.see_top_limit  = parseInt(settings.see_top_limit) || 10;

        var all_list = $scope.find('.sky-modal_tab-all .sky-recent-donations-list');
        var top_list = $scope.find('.sky-modal_tab-top .sky-recent-donations-list');


        /* ======================================================
         * LOAD MORE FUNCTION
         * ====================================================== */
        function loadMore($list, type, limit) {

            if (!$list.length) return;
            if ($list.data('loading') || $list.data('done')) return;

            $list.data('loading', true).addClass('loading-running');

            var loader = $list.find('.items-loader');
            if (loader.length) loader.fadeIn();

            let offset = $list.find('.sky-order').length;

            $.ajax({
                url: skyweb_donation_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "skyweb_load_more_donations",
                    type: type,
                    offset: offset,
                    product_ids: settings.product_ids,
                    limit: limit
                },
                success: function (res) {

                    // Handle missing response safely
                    if (!res || !res.success || !res.data) {
                        finishLoading($list, loader);
                        return;
                    }

                    if (res.data.html) {
                        $list.find('.sky-donations-orders').append(res.data.html);
                        $list.animate({ scrollTop: $list.scrollTop() + 100 }, 250);
                    }

                    // Mark done if no more items
                    if (res.data.done || res.data.count < res.data.limit) {
                        $list.data('done', true);
                    }

                    finishLoading($list, loader);
                },
                error: function () {
                    finishLoading($list, loader);
                }
            });
        }

        function finishLoading($list, loader) {
            $list.data('loading', false).removeClass('loading-running');
            if (loader && loader.length) loader.fadeOut();
        }


        /* ======================================================
         * SCROLL DETECTOR
         * ====================================================== */
        function detectScroll($list, type, limit) {
            if (!$list.length) return;
            if ($list.data('scroll-bound')) return; // prevent double binding

            $list.data('scroll-bound', true);

            $list.on('scroll', function () {
                let el = $list[0];
                let nearBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 70;

                if (nearBottom) {
                    loadMore($list, type, limit);
                }
            });
        }

        detectScroll(all_list, 'all', settings.see_all_limit);
        detectScroll(top_list, 'top', settings.see_top_limit);


        /* ======================================================
         * MODAL OPEN — SEE ALL
         * ====================================================== */
        $scope.find('.sky-modal-actions .see-all-button').on('click', function () {

            $scope.find('.sky-modal_tab-top').hide();
            $scope.find('.sky-modal, .sky-modal_tab-all').fadeIn();

            $scope.find('.all-button, .see-all-button').addClass('active');
            $scope.find('.top-button, .see-top-button').removeClass('active');

            $('html').css('overflow', 'hidden');

            loadMore(all_list, 'all', settings.see_all_limit);
        });


        /* ======================================================
         * MODAL OPEN — SEE TOP
         * ====================================================== */
        $scope.find('.sky-modal-actions .see-top-button').on('click', function () {

            $scope.find('.sky-modal_tab-all').hide();
            $scope.find('.sky-modal, .sky-modal_tab-top').fadeIn();

            $scope.find('.top-button, .see-top-button').addClass('active');
            $scope.find('.all-button, .see-all-button').removeClass('active');

            $('html').css('overflow', 'hidden');

            loadMore(top_list, 'top', settings.see_top_limit);
        });


        /* ======================================================
         * TAB SWITCH — TOP
         * ====================================================== */
        $scope.find('.sky-modal_tabs .button.top-button').on('click', function () {
            var tabs = $scope.find('.sky-modal_tabs .button');
            tabs.removeClass('active');

            $(this).addClass('active');

            $scope.find('.sky-modal_tab-all').hide();
            $scope.find('.sky-modal_tab-top').show();

            loadMore(top_list, 'top', settings.see_top_limit);
        });


        /* ======================================================
         * TAB SWITCH — ALL
         * ====================================================== */
        $scope.find('.sky-modal_tabs .button.all-button').on('click', function () {
            var tabs = $scope.find('.sky-modal_tabs .button');
            tabs.removeClass('active');

            $(this).addClass('active');

            $scope.find('.sky-modal_tab-top').hide();
            $scope.find('.sky-modal_tab-all').show();

            loadMore(all_list, 'all', settings.see_all_limit);
        });

        /* ======================================================
        * MODAL CLOSE
        * ====================================================== */
        $scope.find('.sky-modal_close, .sky-modal_overlay, .sky-modal_footer .button')
            .on('click', function () {

                // Close modal + tabs
                $scope.find('.sky-modal, .sky-modal_tab-all, .sky-modal_tab-top').fadeOut();

                // Remove active states
                $scope.find('.top-button, .see-top-button, .all-button, .see-all-button')
                    .removeClass('active');

                // Reset both lists completely
                all_list.data('loading', false)
                        .data('done', false)
                        .removeClass('loading-running')
                        .find('.sky-donations-orders')
                        .empty();

                top_list.data('loading', false)
                        .data('done', false)
                        .removeClass('loading-running')
                        .find('.sky-donations-orders')
                        .empty();

                // Reset scroll position
                all_list.scrollTop(0);
                top_list.scrollTop(0);

                // Allow scrolling again
                $('html').css('overflow', '');
            });




        let revealCount = 0;
        var orders = $scope.find('.sky-slide-donations .sky-donations-orders .sky-order');
        if (orders.length >= 5) {
            if ($scope.data('sky-interval-running')) return;
            $scope.data('sky-interval-running', true);
            var interval = setInterval(() => {
                var hidden = orders.filter('.hidden-order');
                var visibleItems = orders.not('.hidden-order');
                // REVEAL next hidden item
                var $nextHidden = hidden.eq(0);
                if ($nextHidden.length) {
                    var h = Math.round($nextHidden.find('.item-wrap').outerHeight()) + 1;
                    $nextHidden.css({
                        transform: 'translateY(-20px)'
                    });
                    $nextHidden.animate(
                        { height: h, opacity: 1 },
                        {
                            duration: 300,
                            step: (now, fx) => {
                                if (fx.prop === "opacity") {
                                    // interpolate translateY
                                    var progress = fx.pos;
                                    var y = -20 + (20 * progress); // -20 → 0
                                    $nextHidden.css('transform', `translateY(${y}px)`);
                                }
                            },
                            complete: () => {
                                $nextHidden.css('transform', 'translateY(0)');
                                $nextHidden.removeClass('hidden-order');
                            }
                        }
                    );
                }
                // HIDE last visible item
                var $lastVisible = visibleItems.last();
                if ($lastVisible.length) {
                    $lastVisible.css('transform', 'translateY(0)');
                    $lastVisible.animate(
                        { height: 0, opacity: 0 },
                        {
                            duration: 300,
                            step: (now, fx) => {
                                if (fx.prop === "opacity") {
                                    var progress = fx.pos;    // 0 → 1
                                    var y = 20 * progress;    // 0 → +20px
                                    $lastVisible.css('transform', `translateY(${y}px)`);
                                }
                            },
                            complete: () => {
                                $lastVisible.css('transform', 'translateY(20px)');
                                $lastVisible.addClass('hidden-order');
                            }
                        }
                    );
                }
                revealCount++;
                if (revealCount >= 3) {
                    clearInterval(interval);
                    $scope.data('sky-interval-running', false);
                }
            }, 3000);
        }

    };


    /* =========================================================
     * ELEMENTOR INIT
     * ========================================================= */
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_donation_recent_orders.default',
            skyweb_donation_recent_orders
        );
    });

})(jQuery);
