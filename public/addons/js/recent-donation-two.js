(function ($) {

    var skydonate_recent_orders = function ($scope, $) {

        const $wrapper = $scope.find('.recent-donation-wrapper');
        if (!$wrapper.length) return;

        const settings = $wrapper.data('settings') || {};
        console.log("Recent Order Settings:", settings);

        const all_list = $scope.find('.sky-modal_tab-all .sky-recent-donations-list');

        /* ------------------------
         * LOAD MORE FUNCTION
         * ------------------------ */
        function loadMore($list, type, limit) {

            // prevent double load
            if (!$list.length || $list.data('loading') || $list.data('done')) return;

            $list.data('loading', true).addClass('loading-running');

            const loader = $list.find('.items-loader');
            if (loader.length) loader.fadeIn();

            const offset = $list.find('.sky-order').length;

            $.ajax({
                url: skydonate_ajax.ajax_url,
                type: "POST",
                data: {
                    action: "skydonate_load_more_donations",
                    type: type,
                    product_ids: settings.product_ids || [],
                    offset: offset,
                    limit: limit || 10,
                    list_icon: settings.list_icon || ""
                },

                success: function (res) {
                    if (res.success && res.data) {

                        if (res.data.html) {
                            const $container = $list.find('.sky-donations-orders');
                            $container.append(res.data.html);

                            // smooth scroll
                            $list.stop().animate({
                                scrollTop: $list[0].scrollTop + 100
                            }, 250);
                        }

                        // DONE CHECK
                        if (res.data.done || res.data.count < res.data.limit) {
                            $list.data('done', true);
                        }
                    }

                    finishLoading($list, loader);
                },

                error: function () {
                    finishLoading($list, loader);
                }
            });
        }

        // helper to clean loading UI
        function finishLoading($list, loader) {
            $list.data('loading', false)
                .removeClass('loading-running');

            if (loader.length) loader.fadeOut();
        }


        /* ------------------------
         * SCROLL DETECTOR
         * ------------------------ */
        function detectScroll($list) {
            if (!$list.length || $list.data('scroll-bound')) return;

            $list.data('scroll-bound', true);

            $list.on('scroll', function () {
                const el = $list[0];
                const nearEnd = el.scrollTop + el.clientHeight >= el.scrollHeight - 80;

                if (nearEnd) {
                    loadMore($list, 'all', settings.see_all_limit);
                }
            });
        }

        // bind scroll for ALL tab
        detectScroll(all_list);


        /* --------------------------
         * MODAL: OPEN — VIEW ALL
         * -------------------------- */
        $scope.find('.see-all-button').on('click', function () {

            $scope.find('.sky-modal, .sky-modal_tab-all').fadeIn();
            $('html').css('overflow', 'hidden');

            // initial load, but only if list is empty
            if (!all_list.find('.sky-order').length) {
                loadMore(all_list, 'all', settings.see_all_limit);
            }
        });


        /* --------------------------
         * MODAL CLOSE & RESET
         * -------------------------- */
        $scope.find('.sky-modal_close, .sky-modal_overlay').on('click', function () {

            $scope.find('.sky-modal, .sky-modal_tab-all').fadeOut();
            $('html').css('overflow', '');

            // hard reset
            all_list.data('loading', false);
            all_list.data('done', false);
            all_list.find('.sky-donations-orders').empty();
            all_list.scrollTop(0);
        });

    };


    /* Elementor Init */
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction(
            'frontend/element_ready/skyweb_donation_recent_orders_2.default',
            skydonate_recent_orders
        );
    });

})(jQuery);
