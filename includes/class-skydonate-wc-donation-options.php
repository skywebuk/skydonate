<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Custom_Donation_Options {
    public function __construct() {
        add_action('woocommerce_product_data_tabs', [$this, 'skydonate_register_product_data_tabs']);
        add_action('woocommerce_product_data_panels', [$this, 'skydonate_render_product_data_panels']);
        add_action('save_post', [$this, 'skydonate_save_product_fields']);
        add_filter('woocommerce_get_item_data', [$this, 'skydonate_display_cart_item_custom_data'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'skydonate_save_order_item_custom_data'], 10, 4);
        add_filter('woocommerce_add_cart_item_data', [$this, 'skydonate_capture_cart_item_custom_data'], 10, 3);
        add_filter('woocommerce_is_subscription', [$this, 'skydonate_maybe_mark_donation_as_subscription'], 10, 3);
        add_action( 'woocommerce_add_to_cart', array( $this, 'skydonate_subscription_schemes_on_add_to_cart' ), 19, 6 );
        add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'skydonate_apply_subscriptions' ), 5, 1 );
        add_filter( 'wc_stripe_force_save_source', '__return_true' );
        add_filter( 'wc_stripe_skip_payment_request', '__return_false' );
        add_filter( 'woocommerce_cart_needs_payment', [ $this, 'force_payment_for_zero_total' ], 10, 2 );
        add_filter( 'woocommerce_subscriptions_product_price_string', [$this, 'skydonate_custom_subscription_price_string'], 10, 3 );
    }
    
    public function skydonate_custom_subscription_price_string( $subscription_string, $product, $include ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->subscription_price_string( $subscription_string, $product, $include );
    }



    /**
     * Force WooCommerce to require payment even when total = £0
     */
    public function force_payment_for_zero_total( $needs_payment, $cart ) {
        // Force payment collection even when total is £0
        if ( WC()->cart && WC()->cart->total == 0 ) {
            $needs_payment = true;
        }
        return $needs_payment;
    }

    /**
     * Capture custom cart item data for donations and subscriptions.
     */
     public function skydonate_capture_cart_item_custom_data($cart_item, $product_id, $variation_id = 0) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->capture_cart_item_data($cart_item, $product_id, $variation_id);
    }


    /**
     * Apply subscription schemes when adding to cart
     */
    public static function skydonate_subscription_schemes_on_add_to_cart($item_key, $product_id, $quantity, $variation_id, $variation, $cart_item) {
        self::skydonate_apply_subscriptions(WC()->cart);
    }


    /**
     * Apply subscriptions to all applicable cart items
     * Uses remote stub for protected function
     */
    public static function skydonate_apply_subscriptions($cart) {
        skydonate_remote_stubs()->apply_subscriptions($cart);
    }


    /**
     * Apply a subscription plan to a single cart item
     * Uses remote stub for protected function
     */
    public static function apply_subscription($cart_item) {
        return skydonate_remote_stubs()->apply_subscription($cart_item);
    }


    /**
     * Get subscription scheme from cart item
     * Uses remote stub for protected function
     */
    public static function get_subscription_scheme($cart_item) {
        return skydonate_remote_stubs()->get_subscription_scheme($cart_item);
    }


    /**
     * Set subscription meta for a product/cart item
     * Uses remote stub for protected function
     */
    public static function set_subscription_scheme($cart_item, $scheme) {
        return skydonate_remote_stubs()->set_subscription_scheme($cart_item, $scheme);
    }


    
    /**
     * Determine if a donation product should be treated as a subscription.
     *
     * Ensures any donation with a recurring frequency (e.g., daily, weekly, monthly)
     * is recognized by WooCommerce as a subscription-type product,
     * even if it's a simple or variable product.
     *
     * @param bool        $is_subscription Whether the product is already considered a subscription.
     * @param int         $product_id      The product ID being checked.
     * @param WC_Product  $product         The product object.
     * @return bool
     */
    public function skydonate_maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product ) {
        // If WooCommerce cart is not initialized (e.g., in backend or cron)
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            return $is_subscription;
        }
        $plans = $product->get_meta( '_subscription_plan_data' );
        if ( $plans ) {
            return true;
        }

        return $is_subscription;
    }


    public function skydonate_display_cart_item_custom_data($item_data, $cart_item) {
        // Display other fields
        if (!empty($cart_item['donation_type'])) {
            $item_data[] = [
                'name'  => __('Donation Type', 'skydonate'),
                'value' => esc_html($cart_item['donation_type']),
            ];
        }
        
        if (!empty($cart_item['custom_amount_label'])) {
            $item_data[] = [
                'name'  => __('Amount Label', 'skydonate'),
                'value' => esc_html($cart_item['custom_amount_label']),
            ];
        }
    
        return $item_data;
    }
    
    public function skydonate_save_order_item_custom_data($item, $cart_item_key, $values, $order) {
        // Use remote stub for protected function
        skydonate_remote_stubs()->save_order_item_data($item, $cart_item_key, $values, $order);
    }
    
    // Add custom tabs
    public function skydonate_register_product_data_tabs($tabs) {
        $tabs['skydonate_donation_fields'] = [
            'label' => __('Donation Fields', 'skydonate'),
            'target' => 'skydonate_options_data',
            'class' => ['show_if_simple', 'show_if_variable'],
            'priority' => 21,
        ];
        return $tabs;
    }

    public function skydonate_render_product_data_panels() {
        global $post;
        $donation_frequency = get_post_meta($post->ID, '_donation_frequency', true) ?: 'once';
        $button_visibility = get_post_meta($post->ID, '_button_visibility', true) ?: [];
        $custom_options = get_post_meta($post->ID, '_custom_options', true);
        $default_option = get_post_meta($post->ID, '_default_option', true);
        $box_title = get_post_meta($post->ID, '_box_title', true);
        $box_arrow_hide = get_post_meta($post->ID, '_box_arrow_hide', true);
        $donation_currency_override = get_post_meta($post->ID, '_donation_currency_override', true);
        $skydonate_selected_layout = get_post_meta($post->ID, '_skydonate_selected_layout', true) ?: 'layout_one';
        $close_project = get_post_meta($post->ID, '_close_project', true);
        $zakat_applicable = get_post_meta($post->ID, '_zakat_applicable', true);
        $project_closed_message = get_post_meta($post->ID, '_project_closed_message', true);
        $project_closed_title = get_post_meta($post->ID, '_project_closed_title', true);

        $target_sales_goal = esc_attr(get_post_meta($post->ID, '_target_sales_goal', true));
        $offline_donation = esc_attr(get_post_meta($post->ID, '_offline_donation', true));

        wp_nonce_field('save_product_nonce', 'skydonate_product_nonce');
        $count = 1;

        $today     = date('Y-m-d');
        $tomorrow  = date('Y-m-d', strtotime('+1 day'));

        // Start date
        $start_date = get_post_meta($post->ID, '_start_date', true) ?: $today;
        $enable_start_date = get_post_meta($post->ID, '_enable_start_date', true);

        // End date
        $end_date = get_post_meta($post->ID, '_end_date', true) ?: '';
        $enable_end_date = get_post_meta($post->ID, '_enable_end_date', true);

        // Clear end date if it's already in the past
        if ($end_date && $today >= $end_date) {
            $end_date = '';
        }

        ?>
        <div id="skydonate_options_data" class="panel woocommerce_options_panel">
            <!-- Donation Frequency Options -->
            <div class="skydonate-option-card button-display-options">
                <h3 class="skydonate-option-title">
                    <?php _e('Donation Frequency Options', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Select which donation frequency buttons you want to display on the form. You can enable one or multiple options.', 'skydonate'); ?>
                </p>
                <div class="skydonate-inline-options">
                    <!-- One-Time Donation -->
                    <label class="skydonate-checkbox">
                        <input type="checkbox" 
                            name="button_visibility[]" 
                            value="show_once"
                            <?php checked(is_array($button_visibility) && in_array('show_once', $button_visibility)); ?>>
                        <?php _e('One-Time Donation', 'skydonate'); ?>
                    </label>

                    <!-- Monthly Donation -->
                    <label class="skydonate-checkbox">
                        <input type="checkbox" 
                            name="button_visibility[]" 
                            value="show_monthly"
                            <?php checked(is_array($button_visibility) && in_array('show_monthly', $button_visibility)); ?>>
                        <?php _e('Monthly Donation', 'skydonate'); ?>
                    </label>

                    <!-- Weekly Donation -->
                    <label class="skydonate-checkbox">
                        <input type="checkbox" 
                            name="button_visibility[]" 
                            value="show_weekly"
                            <?php checked(is_array($button_visibility) && in_array('show_weekly', $button_visibility)); ?>>
                        <?php _e('Weekly Donation', 'skydonate'); ?>
                    </label>

                    <!-- Daily Donation -->
                    <label class="skydonate-checkbox">
                        <input type="checkbox" 
                            name="button_visibility[]" 
                            value="show_daily"
                            <?php checked(is_array($button_visibility) && in_array('show_daily', $button_visibility)); ?>>
                        <?php _e('Daily Donation', 'skydonate'); ?>
                    </label>

                    <!-- Yearly Donation -->
                    <label class="skydonate-checkbox">
                        <input type="checkbox" 
                            name="button_visibility[]" 
                            value="show_yearly"
                            <?php checked(is_array($button_visibility) && in_array('show_yearly', $button_visibility)); ?>>
                        <?php _e('Yearly Donation', 'skydonate'); ?>
                    </label>
                </div>
            </div>

            <!-- Donation Fields -->
            <div class="skydonate-option-card donation-fields-option">
                <h3 class="skydonate-option-title">
                    <?php _e('Donation Fields', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Add, edit, and configure custom donation options with prices for different frequencies. You can set a default option and hide options as needed.', 'skydonate'); ?>
                </p>

                <div id="skydonate-fields-container" class="skydonate-block-options">
                    <?php if (!empty($custom_options)): ?>
                        <?php foreach ($custom_options as $option):
                            $publish = isset($option['publish']) ? $option['publish'] : 0;
                        ?>
                        <div class="skydonate-fields">
                            <div class="header">
                                <h4 class="title"><?php _e('Donation Option', 'skydonate'); ?> <?php echo $count; ?></h4>
                                <button type="button" class="action toggle-option"><span class="toggle-indicator"></span></button>
                            </div>
                            <div class="fields">
                                <div class="skydonate-input-group">
                                    <label><?php _e('Label', 'skydonate'); ?></label>
                                    <input type="text" class="short" name="custom_option_label[]" value="<?php echo esc_attr($option['label']); ?>" placeholder="<?php _e('Option label', 'skydonate'); ?>">
                                </div>
                                <div class="skydonate-input-group once-field">
                                    <label><?php _e('One-Time', 'skydonate'); ?></label>
                                    <input type="number" class="short" name="custom_option_price[]" value="<?php echo esc_attr($option['price'] ?? 0); ?>" min="0">
                                </div>
                                <div class="skydonate-input-group daily-field">
                                    <label><?php _e('Daily', 'skydonate'); ?></label>
                                    <input type="number" class="short" name="custom_option_daily[]" value="<?php echo esc_attr($option['daily'] ?? 0); ?>" min="0">
                                </div>
                                <div class="skydonate-input-group weekly-field">
                                    <label><?php _e('Weekly', 'skydonate'); ?></label>
                                    <input type="number" class="short" name="custom_option_weekly[]" value="<?php echo esc_attr($option['weekly'] ?? 0); ?>" min="0">
                                </div>
                                <div class="skydonate-input-group monthly-field">
                                    <label><?php _e('Monthly', 'skydonate'); ?></label>
                                    <input type="number" class="short" name="custom_option_monthly[]" value="<?php echo esc_attr($option['monthly'] ?? 0); ?>" min="0">
                                </div>
                                <div class="skydonate-input-group yearly-field">
                                    <label><?php _e('Yearly', 'skydonate'); ?></label>
                                    <input type="number" class="short" name="custom_option_yearly[]" value="<?php echo esc_attr($option['yearly'] ?? 0); ?>" min="0">
                                </div>
                                <div class="skydonate-input-group">
                                    <label><?php _e('Default', 'skydonate'); ?></label>
                                    <input type="radio" name="default_option" value="<?php echo esc_attr($count); ?>" <?php checked($default_option, $count); ?>>
                                    <small><?php _e('Set this option as the pre-selected donation amount.', 'skydonate'); ?></small>
                                </div>
                                <div class="skydonate-input-group">
                                    <label><?php _e('Hide', 'skydonate'); ?></label>
                                    <input type="checkbox" name="publish_project_item[]" value="<?php echo esc_attr($count); ?>" <?php checked($publish, $count); ?>>
                                    <small><?php _e('Hide this option from the donation form.', 'skydonate'); ?></small>
                                </div>
                                <div class="skydonate-input-group">
                                    <button type="button" class="button remove_custom_option"><?php _e('Remove', 'skydonate'); ?></button>
                                </div>
                            </div>
                        </div>

                        <?php $count++; endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Add New Option -->
                <button type="button" class="button add_custom_option"><?php _e('Add Option', 'skydonate'); ?></button>
            </div>




            <!-- Box Title -->
            <div class="skydonate-option-card box-title-option">
                <h3 class="skydonate-option-title">
                    <?php _e('Box Title', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Enter the title that will appear at the top of the donation box.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">
                    <input type="text" class="short-controll" name="box_title" value="<?php echo esc_attr($box_title); ?>">
                </div>

                <label class="skydonate-checkbox close-project-checkbox">
                    <input type="checkbox" 
                        name="box_arrow_hide" 
                        id="box_arrow_hide" 
                        value="yes"
                        <?php checked($box_arrow_hide, 'yes'); ?>> 
                    <?php _e('Hide Arrow', 'skydonate'); ?>
                </label>
            </div>

            <!-- Donation Currency Option -->
            <div class="skydonate-option-card donation-currency-option">
                <h3 class="skydonate-option-title">
                    <?php _e('Donation Currency', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Disable the currency switcher and use the default currency for this donation.', 'skydonate'); ?>
                </p>

                <label class="skydonate-checkbox">
                    <input type="checkbox" 
                        name="donation_currency_override" 
                        id="donation_currency_override" 
                        value="yes"
                        <?php checked($donation_currency_override, 'yes'); ?>> 
                    <?php _e('Use Default Currency (disable switcher)', 'skydonate'); ?>
                </label>
            </div>




            <!-- Set Active Donation Frequency -->
            <div class="skydonate-option-card active-donation-frequency">
                <h3 class="skydonate-option-title">
                    <?php _e('Set Default Donation Frequency', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Choose which donation frequency should be pre-selected by default on the donation form.', 'skydonate'); ?>
                </p>

                <div class="skydonate-inline-options">
                    <!-- One-Time Donation -->
                    <label class="skydonate-radio once">
                        <input type="radio" 
                            name="donation_frequency" 
                            value="once"
                            <?php checked($donation_frequency, 'once'); ?>>
                        <?php _e('One-Time Donation', 'skydonate'); ?>
                    </label>

                    <!-- Daily Donation -->
                    <label class="skydonate-radio daily">
                        <input type="radio" 
                            name="donation_frequency" 
                            value="daily"
                            <?php checked($donation_frequency, 'daily'); ?>>
                        <?php _e('Daily Donation', 'skydonate'); ?>
                    </label>

                    <!-- Weekly Donation -->
                    <label class="skydonate-radio weekly">
                        <input type="radio" 
                            name="donation_frequency" 
                            value="weekly"
                            <?php checked($donation_frequency, 'weekly'); ?>>
                        <?php _e('Weekly Donation', 'skydonate'); ?>
                    </label>

                    <!-- Monthly Donation -->
                    <label class="skydonate-radio monthly">
                        <input type="radio" 
                            name="donation_frequency" 
                            value="monthly"
                            <?php checked($donation_frequency, 'monthly'); ?>>
                        <?php _e('Monthly Donation', 'skydonate'); ?>
                    </label>

                    <!-- Yearly Donation -->
                    <label class="skydonate-radio yearly">
                        <input type="radio" 
                            name="donation_frequency" 
                            value="yearly"
                            <?php checked($donation_frequency, 'yearly'); ?>>
                        <?php _e('Yearly Donation', 'skydonate'); ?>
                    </label>
                </div>
            </div>

            <!-- Daily Donation Date Range -->
            <div class="skydonate-option-card daily-date-card" style="display: <?php echo ($donation_frequency === 'daily') ? 'block' : 'block'; ?>;">

                <h3 class="skydonate-option-title">
                    <?php _e('Enable Donation Start Date', 'skydonate'); ?>
                </h3>

                <p class="skydonate-option-description">
                    <?php _e('Turn this option on to display the start date field on the donation form. This setting applies only to Daily Donations. When disabled, donors will not be able to choose a start date.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">

                    <!-- Enable Start Date Checkbox -->
                    <label class="skydonate-checkbox enable-start-date">
                        <input type="checkbox" 
                            id="enable_start_date" 
                            name="enable_start_date" 
                            value="1" 
                            <?php checked($enable_start_date, 1); ?>>
                        <?php _e('Enable Start Date', 'skydonate'); ?>
                    </label>

                    <!-- Start Date Input Field -->
                    <div class="skydonate-input-group start-date-group-field">
                        <label for="start_date">
                            <?php _e('Start Date', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" data-tip="<?php _e('Select the date when the donation should begin.', 'skydonate'); ?>"></span>
                        <input type="date" 
                            id="start_date" 
                            class="short-controll start-date-field" 
                            name="start_date" 
                            min="<?php echo esc_attr($today); ?>"
                            value="<?php echo esc_attr($start_date > $today ? $start_date : $today); ?>">
                    </div>
                </div>

                <br>

                <h3 class="skydonate-option-title">
                    <?php _e('Enable Donation End Date', 'skydonate'); ?>
                </h3>

                <p class="skydonate-option-description">
                    <?php _e('Turn this option on to display the end date field on the donation form. This setting applies only to Daily Donations. When disabled, donors will not be able to set an end date.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">
                    <!-- Enable End Date Checkbox -->
                    <label class="skydonate-checkbox enable-end-date">
                        <input type="checkbox" 
                            id="enable_end_date" 
                            name="enable_end_date" 
                            value="1" 
                            min="<?php echo esc_attr($today); ?>"
                            <?php checked($enable_end_date, 1); ?>>
                        <?php _e('Enable End Date', 'skydonate'); ?>
                    </label>

                    <!-- End Date Input Field -->
                    <div class="skydonate-input-group end-date-group-field">
                        <label for="end_date">
                            <?php _e('End Date', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" data-tip="<?php _e('Choose the date when the daily donation should stop.', 'skydonate'); ?>"></span>
                        <input type="date" 
                            id="end_date" 
                            class="short-controll end-date-field" 
                            name="end_date"
                            min="<?php echo esc_attr($today); ?>"
                            value="<?php echo esc_attr($end_date); ?>">
                    </div>

                </div>
            </div>
            <!-- Target & Offline Donation Settings -->
            <div class="skydonate-option-card target-sales-offline-settings">
                <h3 class="skydonate-option-title">
                    <?php _e('Donation Goal Settings', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Set the fundraising goal and include any offline donation amounts to be considered.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">
                    <!-- Target Sales Goal -->
                    <div class="skydonate-input-group target-sales-goal-field">
                        <label for="target_sales_goal" class="skydonate-label">
                            <?php _e('Target Sales Goal', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" 
                            data-tip="<?php _e('The total fundraising goal for this project.', 'skydonate'); ?>">
                        </span>
                        <input type="number" class="short-controll" 
                            name="target_sales_goal" 
                            id="target_sales_goal" 
                            value="<?php echo esc_attr($target_sales_goal ?? 0); ?>" 
                            placeholder="<?php _e('Enter target amount', 'skydonate'); ?>" 
                            min="0" step="0.01">
                    </div>

                    <!-- Offline Donation -->
                    <div class="skydonate-input-group offline-donation-field">
                        <label for="offline_donation" class="skydonate-label">
                            <?php _e('Offline Donation', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" 
                            data-tip="<?php _e('Add manually collected offline donation amounts to be included in total raised.', 'skydonate'); ?>">
                        </span>
                        <input type="number" class="short-controll" 
                            name="offline_donation" 
                            id="offline_donation" 
                            value="<?php echo esc_attr($offline_donation ?? 0); ?>" 
                            placeholder="<?php _e('Enter offline donation amount', 'skydonate'); ?>" 
                            min="0" step="0.01">
                    </div>
                </div>
            </div>

            <?php
            
                $grid_image = SKYDONATE_ADMIN_ASSETS . 'images/grid-layout.jpg';
                $list_image = SKYDONATE_ADMIN_ASSETS . 'images/list-layout.jpg';
                // Correct usage of in_array()
                if (skydonate_get_layout('addons_donation_form') == 'layout-2') {
                    $grid_image = SKYDONATE_ADMIN_ASSETS . 'images/grid-layout-2.jpg';
                    $list_image = SKYDONATE_ADMIN_ASSETS . 'images/list-layout-2.jpg';
                }
                if (skydonate_get_layout('addons_donation_form') == 'layout-3') {
                    $grid_image = SKYDONATE_ADMIN_ASSETS . 'images/grid-layout-3.jpg';
                    $list_image = SKYDONATE_ADMIN_ASSETS . 'images/list-layout-3.jpg';
                }
            ?>

            <div class="skydonate-option-card layout-selection">
                <h3 class="skydonate-option-title">
                    <?php _e('Choose Layout Style', 'skydonate'); ?>
                </h3>
                <div class="skydonate-inline-options">
                    <!-- Grid Layout Option -->
                    <div class="layout-option">
                        <label class="skydonate-image-checkbox">
                            <input type="radio"
                                name="skydonate_selected_layout"
                                value="layout_one"
                                <?php checked($skydonate_selected_layout, 'layout_one'); ?> />
                            <img src="<?php echo esc_url($grid_image); ?>"
                                alt="<?php esc_attr_e('Grid Layout', 'skydonate'); ?>" />
                            <span class="checkbox-label">
                                <?php _e('Grid Layout', 'skydonate'); ?>
                            </span>
                        </label>
                    </div>

                    <!-- List Layout Option -->
                    <div class="layout-option">
                        <label class="skydonate-image-checkbox">
                            <input type="radio"
                                name="skydonate_selected_layout"
                                value="layout_two"
                                <?php checked($skydonate_selected_layout, 'layout_two'); ?> />
                            <img src="<?php echo esc_url($list_image); ?>"
                                alt="<?php esc_attr_e('List Layout', 'skydonate'); ?>" />
                            <span class="checkbox-label">
                                <?php _e('List Layout', 'skydonate'); ?>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="skydonate-option-card zakat-applicable-settings">
                <h3 class="skydonate-option-title">
                    <?php _e('Zakat Applicable', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Enable or disable whether this project is eligible for Zakat donations.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">
                    <!-- Zakat Applicable Switch -->
                    <label class="skydonate-checkbox zakat-applicable-switch">
                        <input type="checkbox"
                            name="zakat_applicable"
                            id="zakat_applicable"
                            value="yes"
                            <?php checked($zakat_applicable, 'yes'); ?>>
                        <?php _e('Enable Zakat', 'skydonate'); ?>
                    </label>
                </div>
            </div>
            <!-- Close Project Settings -->
            <div class="skydonate-option-card close-project-settings">
                <h3 class="skydonate-option-title">
                    <?php _e('Close Project Settings', 'skydonate'); ?>
                </h3>
                <p class="skydonate-option-description">
                    <?php _e('Configure the project closure options, including the title and message displayed when a project is closed.', 'skydonate'); ?>
                </p>

                <div class="skydonate-block-options">
                    <!-- Close Project Checkbox -->
                    <label class="skydonate-checkbox close-project-checkbox">
                        <input type="checkbox" 
                            name="close_project" 
                            id="close_project" 
                            value="yes" 
                            <?php checked($close_project, 'yes'); ?>> 
                        <?php _e('Close Project', 'skydonate'); ?>
                    </label>

                    <!-- Project Closed Title -->
                    <div class="skydonate-input-group project-closed-title-field">
                        <label for="project_closed_title">
                            <?php _e('Title', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" data-tip="<?php _e('Title displayed when the project is closed', 'skydonate'); ?>"></span>
                        <input type="text" class="short-controll" 
                            name="project_closed_title" 
                            id="project_closed_title" 
                            value="<?php echo esc_attr($project_closed_title ?: __('This project is closed', 'skydonate')); ?>" 
                            placeholder="<?php _e('Enter closed project title', 'skydonate'); ?>">
                    </div>

                    <!-- Project Closed Subtitle -->
                    <div class="skydonate-input-group form-field project-closed-message-field">
                        <label for="project_closed_message">
                            <?php _e('Subtitle', 'skydonate'); ?>
                        </label>
                        <span class="woocommerce-help-tip" tabindex="0" data-tip="<?php _e('Subtitle displayed when the project is closed', 'skydonate'); ?>"></span>
                        <input type="text" class="short-controll" 
                            name="project_closed_message" 
                            id="project_closed_message" 
                            value="<?php echo esc_attr($project_closed_message ?: __('Thank you for your interest. This campaign is no longer accepting donations.', 'skydonate')); ?>" 
                            placeholder="<?php _e('Enter closed project subtitle', 'skydonate'); ?>">
                    </div>

                </div>
            </div>
        </div>
        <?php
    }

    public function skydonate_save_product_fields($post_id) {
        if (!isset($_POST['skydonate_product_nonce']) || !wp_verify_nonce($_POST['skydonate_product_nonce'], 'save_product_nonce')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    
        $custom_options = [];
        if (isset($_POST['custom_option_label'], $_POST['custom_option_price'], $_POST['custom_option_monthly'], $_POST['custom_option_daily'], $_POST['custom_option_weekly'], $_POST['custom_option_yearly'])) {
            $labels = $_POST['custom_option_label'];
            $prices = $_POST['custom_option_price'];
            $monthlies = $_POST['custom_option_monthly'];
            $dailys = $_POST['custom_option_daily'];
            $weeklies = $_POST['custom_option_weekly'];
            $yearlies = $_POST['custom_option_yearly'];
            $publish = isset($_POST['publish_project_item']) ? array_map('sanitize_text_field', $_POST['publish_project_item']) : [];
    
            foreach ($labels as $index => $label) {
                $custom_options[] = [
                    'label' => sanitize_text_field($label),
                    'price' => floatval($prices[$index]),
                    'monthly' => floatval($monthlies[$index]),
                    'daily' => floatval($dailys[$index]),
                    'weekly' => floatval($weeklies[$index]),
                    'yearly' => floatval($yearlies[$index]),
                    'publish' => in_array($index + 1, $publish) ? $index + 1 : 0,
                ];
            }
        }
   
        $donation_frequency = isset($_POST['donation_frequency']) ? sanitize_text_field($_POST['donation_frequency']) : 'once';
        $button_visibility = isset($_POST['button_visibility']) ? array_map('sanitize_text_field', $_POST['button_visibility']) : [];
        $box_title = isset($_POST['box_title']) ? sanitize_text_field($_POST['box_title']) : '';
        $skydonate_selected_layout = isset($_POST['skydonate_selected_layout']) ? sanitize_text_field($_POST['skydonate_selected_layout']) : 'layout_one';
        $default_option = isset($_POST['default_option']) ? sanitize_text_field($_POST['default_option']) : '';

        $enable_end_date = isset($_POST['enable_end_date']) ? sanitize_text_field($_POST['enable_end_date']) : '0';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        

        $enable_start_date = isset($_POST['enable_start_date']) ? sanitize_text_field($_POST['enable_start_date']) : '0';
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $close_project = isset($_POST['close_project']) ? 'yes' : 'no';
        $zakat_applicable = isset($_POST['zakat_applicable']) ? 'yes' : 'no';
        $box_arrow_hide = isset($_POST['box_arrow_hide']) ? 'yes' : 'no';
        $donation_currency_override = isset($_POST['donation_currency_override']) ? 'yes' : 'no';

        $project_closed_message = isset($_POST['project_closed_message']) ? sanitize_text_field($_POST['project_closed_message']) : '';
        $project_closed_title = isset($_POST['project_closed_title']) ? sanitize_text_field($_POST['project_closed_title']) : '';
        $target_sales_goal = isset($_POST['target_sales_goal']) ? sanitize_text_field($_POST['target_sales_goal']) : '';
        $offline_donation = isset($_POST['offline_donation']) ? sanitize_text_field($_POST['offline_donation']) : '';
  
        // Update post meta with the new values
        update_post_meta($post_id, '_donation_frequency', $donation_frequency);
        update_post_meta($post_id, '_button_visibility', $button_visibility);
        update_post_meta($post_id, '_custom_options', $custom_options);
        update_post_meta($post_id, '_default_option', $default_option);
        update_post_meta($post_id, '_box_title', $box_title);
        update_post_meta($post_id, '_box_arrow_hide', $box_arrow_hide);
        update_post_meta($post_id, '_donation_currency_override', $donation_currency_override);
        update_post_meta($post_id, '_skydonate_selected_layout', $skydonate_selected_layout);
        update_post_meta($post_id, '_enable_end_date', $enable_end_date);
        update_post_meta($post_id, '_end_date', $end_date);
        update_post_meta($post_id, '_enable_start_date', $enable_start_date);
        update_post_meta($post_id, '_start_date', $start_date);
        update_post_meta($post_id, '_close_project', $close_project);
        update_post_meta($post_id, '_zakat_applicable', $zakat_applicable);
        update_post_meta($post_id, '_project_closed_message', $project_closed_message);
        update_post_meta($post_id, '_project_closed_title', $project_closed_title);
        update_post_meta($post_id, '_target_sales_goal', $target_sales_goal);
        update_post_meta($post_id, '_offline_donation', $offline_donation);
    }
    
}