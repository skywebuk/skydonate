<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Skydonate_Gift_Aid' ) ) :

class Skydonate_Gift_Aid {

    public function __construct() {

        // AJAX handlers for exporting Gift Aid orders
        add_action( 'wp_ajax_export_gift_aid_orders_ajax', [ $this, 'export_gift_aid_orders_ajax' ] );
        add_action( 'wp_ajax_export_gift_aid_orders_by_date', [ $this, 'export_gift_aid_orders_by_date' ] );

        // Initialize admin settings
        add_action( 'admin_init', array( $this, 'admin_init' ) );

        // Register My Account endpoints
        add_action( 'init', array( $this, 'add_my_account_endpoints' ) );
        add_filter( 'woocommerce_account_menu_items', array( $this, 'add_my_account_menu_items' ) );
        add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );

        // Add endpoint content
        add_action( 'woocommerce_account_gift-aid_endpoint', array( $this, 'gift_aid_endpoint_content' ) );
        add_action( 'woocommerce_account_anonymous-donations_endpoint', array( $this, 'anonymous_donations_endpoint_content' ) );

        // AJAX handlers for saving preferences
        add_action( 'wp_ajax_save_gift_aid', [$this, 'skydonate_ajax_save_gift_aid'] );
        add_action( 'wp_ajax_save_anonymous_donation', [ $this, 'skydonate_ajax_save_anonymous_donation' ] );

        // Save the Gift Aid checkbox value when order is processed
        add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_gift_aid_checkbox' ), 45, 2 );

        // Save Gift Aid meta during WooCommerce checkout
        add_action( 'woocommerce_checkout_create_order', array( $this, 'save_gift_aid_meta' ), 10, 2 );

        // Apply user preferences to new orders
        add_action( 'woocommerce_checkout_create_order', array( $this, 'apply_user_anonymous_preference' ), 10, 2 );

        // Apply Gift Aid to subscription renewals
        add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'apply_user_gift_aid_to_subscription' ), 10, 2 );

        // Apply Anonymous preference to subscription renewals
        add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'apply_user_anonymous_to_subscription' ), 10, 2 );

        // Include Gift Aid meta in WooCommerce emails
        add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'email_gift_aid_meta' ), 10, 3 );
    }


    // Save checkbox value
    public function save_gift_aid_checkbox( $order_id ) {
        // Verify user has permission to edit orders
        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        if ( isset( $_POST['gift_aid_it'] ) && $_POST['gift_aid_it'] === 'yes' ) {
            $order->update_meta_data( 'gift_aid_it', 'yes' );
        } else {
            $order->update_meta_data( 'gift_aid_it', 'no' );
        }
        $order->save();
    }

    /**
     * Save Gift Aid value in order meta during checkout
     */
    public function save_gift_aid_meta( $order, $data ) {
        $user_id = get_current_user_id();

        // Determine checkbox value
        $gift_aid_value = isset( $_POST['gift_aid_it'] ) && $_POST['gift_aid_it'] === 'on' ? 'yes' : 'no';

        // If 'yes', update user meta and past orders
        if ( $gift_aid_value === 'yes' && $user_id ) {
            update_user_meta( $user_id, 'gift_aid_status', $gift_aid_value );
        }

        // Save value for the current order (always)
        $order->update_meta_data( 'gift_aid_it', $gift_aid_value );
    }

    /**
     * Apply Gift Aid to subscription renewal orders
     */
    public function apply_user_gift_aid_to_subscription( $renewal_order, $subscription ) {
        $user_id = $subscription->get_user_id();
        if ( ! $user_id ) return;

        $gift_aid_value = get_user_meta( $user_id, 'gift_aid_status', true );
        
        if ( empty( $gift_aid_value ) ) {
            $gift_aid_value = 'no';
        }

        $renewal_order->update_meta_data( 'gift_aid_it', $gift_aid_value );
        $renewal_order->save();
    }

    /**
     * Add Gift Aid info to order emails
     */
    public function email_gift_aid_meta( $fields, $sent_to_admin, $order ) {
        $gift_aid = $order->get_meta( 'gift_aid_it', true );

        if ( $gift_aid === 'yes' ) {
            $fields['gift_aid_it'] = array(
                'label' => esc_html__( 'Gift Aid', 'skydonate' ),
                'value' => esc_html__( 'Yes, customer opted in', 'skydonate' ),
            );
        }

        return $fields;
    }

    /**
     * Register custom endpoints for My Account
     */
    public function add_my_account_endpoints() {
        add_rewrite_endpoint( 'gift-aid', EP_ROOT | EP_PAGES );
        add_rewrite_endpoint( 'anonymous-donations', EP_ROOT | EP_PAGES );
    }

    /**
     * Add query vars for custom endpoints
     */
    public function add_query_vars( $vars ) {
        $vars['gift-aid'] = 'gift-aid';
        $vars['anonymous-donations'] = 'anonymous-donations';
        return $vars;
    }

    /**
     * Add menu items to My Account navigation (before logout)
     */
    public function add_my_account_menu_items( $items ) {
        // Remove logout temporarily
        $logout = false;
        if ( isset( $items['customer-logout'] ) ) {
            $logout = $items['customer-logout'];
            unset( $items['customer-logout'] );
        }

        // Add Gift Aid link
        $items['gift-aid'] = __( 'Gift Aid', 'skydonate' );

        // Add Anonymous Donations link
        $items['anonymous-donations'] = __( 'Anonymous Donations', 'skydonate' );

        // Re-add logout at the end
        if ( $logout ) {
            $items['customer-logout'] = $logout;
        }

        return $items;
    }

    /**
     * Gift Aid endpoint content
     */
    public function gift_aid_endpoint_content() {
        $this->my_account_gift_aid_field();
    }

    /**
     * Anonymous Donations endpoint content
     */
    public function anonymous_donations_endpoint_content() {
        $this->my_account_anonymous_donation_field();
    }

    public function my_account_gift_aid_field() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id  = get_current_user_id();
        $gift_aid = get_user_meta( $user_id, 'gift_aid_status', true ) ?: 'no';
        // Fetch saved admin settings or use defaults
        $gift_aid_enabled      = get_option( 'enable_gift_aid', 0 ) ?: 1;
        $gift_aid_description  = get_option( 'gift_aid_description' ) ?: 'Boost your donation by 25p of Gift Aid for every £1 you donate, at no extra cost to you.';
        $gift_aid_label        = get_option( 'gift_aid_checkbox_label' ) ?: 'Yes, I would like to claim Gift Aid';
        $gift_aid_note         = get_option( 'gift_aid_note' ) ?: 'I understand that if I pay less Income Tax and/or Capital Gains Tax than the amount of Gift Aid claimed on all my donations in that tax year it is my responsibility to pay any difference. Please remember to notify Global Helping Hands: if you want to cancel this declaration, change your name or home address or no longer pay sufficient tax on your income and/or capital gains.';
        $gift_aid_logo         = get_option( 'gift_aid_logo' ) ?: SKYDONATE_PUBLIC_ASSETS . '/img/gift-aid-uk-logo.svg';
        $default_checked       = get_option( 'gift_aid_default_status', 1 ) ?: 1;
        ?>
        <div class="my-account-gift-aid skydonate-gift-aid-wrapper">
            <img src="<?php echo esc_url( $gift_aid_logo ); ?>" alt="<?php esc_attr_e( 'Gift Aid Logo', 'skydonate' ); ?>" class="skydonate-gift-aid-logo">

            <h3 class="skydonate-gift-aid-title"><?php esc_html_e( 'Gift Aid', 'skydonate' ); ?></h3>

            <p class="skydonate-gift-aid-description">
                <?php echo esc_html( $gift_aid_description ); ?>
            </p>

            <form id="gift-aid-form" class="skydonate-gift-aid-form" method="post">
                <?php wp_nonce_field( 'save_account_data', 'gift_aid_nonce' ); ?>
                <p class="form-row skydonate-gift-aid-checkbox-row">
                    <label class="sky-smart-switch checkbox">
                        <input type="checkbox"
                            class="input-checkbox"
                            name="gift_aid_status"
                            value="yes"
                            <?php
                                // Only check if user explicitly enabled it (not based on default for existing users)
                                checked( $gift_aid, 'yes' );
                            ?>
                        >
                        <span class="switch"></span>
                        <?php echo esc_html( $gift_aid_label ); ?>
                    </label>
                </p>

                <p class="gift-aid-note skydonate-gift-aid-note-text">
                    <?php echo esc_html( $gift_aid_note ); ?>
                </p>

                <p class="mb-0 skydonate-gift-aid-button-row">
                    <button type="submit" class="button primary-button skydonate-gift-aid-submit">
                        <?php esc_html_e( 'Save changes', 'skydonate' ); ?>
                    </button>
                </p>

                <div id="gift-aid-message" class="skydonate-gift-aid-message"></div>
            </form>
        </div>
        <?php
    }


    public function skydonate_ajax_save_gift_aid() {
        // Check nonce
        if ( ! isset($_POST['gift_aid_nonce']) || ! wp_verify_nonce( $_POST['gift_aid_nonce'], 'save_account_data' ) ) {
            wp_send_json_error( array( 'message' => 'Nonce verification failed.' ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => 'You must be logged in.' ) );
        }

        $user_id = get_current_user_id();
        $gift_aid_value = ( isset($_POST['gift_aid_status']) && $_POST['gift_aid_status'] === 'yes' ) ? 'yes' : 'no';

        // Save user meta
        update_user_meta( $user_id, 'gift_aid_status', $gift_aid_value );

        // Update past orders - limit to recent orders to prevent memory issues
        // Process in batches if needed for sites with many orders
        $page = 1;
        $per_page = 100;
        $updated_count = 0;

        do {
            $orders = wc_get_orders( array(
                'customer_id' => $user_id,
                'limit'       => $per_page,
                'page'        => $page,
                'status'      => array( 'wc-completed', 'wc-processing' ),
            ) );

            foreach ( $orders as $order ) {
                $order->update_meta_data( 'gift_aid_status', $gift_aid_value );
                $order->save();
                $updated_count++;
            }

            $page++;
            // Limit total updates to prevent timeout (max 1000 orders)
        } while ( count( $orders ) === $per_page && $updated_count < 1000 );

        wp_send_json_success( array( 'message' => 'Gift Aid settings saved.' ) );
    }

    /**
     * Display Anonymous Donation field in My Account page
     */
    public function my_account_anonymous_donation_field() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id = get_current_user_id();
        $anonymous_status = get_user_meta( $user_id, 'anonymous_donation_status', true ) ?: 'no';
        ?>
        <div class="my-account-anonymous-donation skydonate-anonymous-wrapper">
            <h3 class="skydonate-anonymous-title"><?php esc_html_e( 'Anonymous Donations', 'skydonate' ); ?></h3>

            <p class="skydonate-anonymous-description">
                <?php esc_html_e( 'When enabled, your name will be hidden from public donation lists and notifications. Your donations will appear as "Anonymous" to others.', 'skydonate' ); ?>
            </p>

            <form id="anonymous-donation-form" class="skydonate-anonymous-form" method="post">
                <?php wp_nonce_field( 'save_anonymous_donation', 'anonymous_donation_nonce' ); ?>
                <p class="form-row skydonate-anonymous-checkbox-row">
                    <label class="sky-smart-switch checkbox">
                        <input type="checkbox"
                            class="input-checkbox"
                            name="anonymous_donation_status"
                            value="yes"
                            <?php checked( $anonymous_status, 'yes' ); ?>
                        >
                        <span class="switch"></span>
                        <?php esc_html_e( 'Make all my donations anonymous', 'skydonate' ); ?>
                    </label>
                </p>

                <p class="anonymous-note skydonate-anonymous-note-text">
                    <?php esc_html_e( 'This setting will apply to all your past and future donations. Your billing details will still be visible to the organization for record-keeping purposes.', 'skydonate' ); ?>
                </p>

                <p class="mb-0 skydonate-anonymous-button-row">
                    <button type="submit" class="button primary-button skydonate-anonymous-submit">
                        <?php esc_html_e( 'Save changes', 'skydonate' ); ?>
                    </button>
                </p>

                <div id="anonymous-donation-message" class="skydonate-anonymous-message"></div>
            </form>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#anonymous-donation-form').on('submit', function(e) {
                e.preventDefault();
                var $form = $(this);
                var $button = $form.find('button[type="submit"]');
                var $message = $('#anonymous-donation-message');

                $button.prop('disabled', true).text('<?php esc_html_e( 'Saving...', 'skydonate' ); ?>');

                $.ajax({
                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                    type: 'POST',
                    data: {
                        action: 'save_anonymous_donation',
                        anonymous_donation_nonce: $form.find('[name="anonymous_donation_nonce"]').val(),
                        anonymous_donation_status: $form.find('[name="anonymous_donation_status"]').is(':checked') ? 'yes' : 'no'
                    },
                    success: function(response) {
                        if (response.success) {
                            $message.html('<p class="success">' + response.data.message + '</p>').show();
                        } else {
                            $message.html('<p class="error">' + response.data.message + '</p>').show();
                        }
                        $button.prop('disabled', false).text('<?php esc_html_e( 'Save changes', 'skydonate' ); ?>');
                        setTimeout(function() { $message.fadeOut(); }, 3000);
                    },
                    error: function() {
                        $message.html('<p class="error"><?php esc_html_e( 'An error occurred. Please try again.', 'skydonate' ); ?></p>').show();
                        $button.prop('disabled', false).text('<?php esc_html_e( 'Save changes', 'skydonate' ); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for saving anonymous donation preference
     */
    public function skydonate_ajax_save_anonymous_donation() {
        // Check nonce
        if ( ! isset( $_POST['anonymous_donation_nonce'] ) || ! wp_verify_nonce( $_POST['anonymous_donation_nonce'], 'save_anonymous_donation' ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'skydonate' ) ) );
        }

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'skydonate' ) ) );
        }

        $user_id = get_current_user_id();
        $anonymous_value = ( isset( $_POST['anonymous_donation_status'] ) && $_POST['anonymous_donation_status'] === 'yes' ) ? 'yes' : 'no';

        // Save user meta
        update_user_meta( $user_id, 'anonymous_donation_status', $anonymous_value );

        // Update past orders - process in batches
        $page = 1;
        $per_page = 100;
        $updated_count = 0;

        do {
            $orders = wc_get_orders( array(
                'customer_id' => $user_id,
                'limit'       => $per_page,
                'page'        => $page,
                'status'      => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
            ) );

            foreach ( $orders as $order ) {
                if ( $anonymous_value === 'yes' ) {
                    $order->update_meta_data( '_anonymous_donation', '1' );
                } else {
                    $order->delete_meta_data( '_anonymous_donation' );
                }
                $order->save();
                $updated_count++;
            }

            $page++;
            // Limit total updates to prevent timeout (max 1000 orders)
        } while ( count( $orders ) === $per_page && $updated_count < 1000 );

        wp_send_json_success( array(
            'message' => sprintf(
                __( 'Anonymous donation settings saved. %d orders updated.', 'skydonate' ),
                $updated_count
            )
        ) );
    }

    /**
     * Apply user's anonymous preference to new orders during checkout
     */
    public function apply_user_anonymous_preference( $order, $data ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return;
        }

        $anonymous_status = get_user_meta( $user_id, 'anonymous_donation_status', true );

        if ( $anonymous_status === 'yes' ) {
            $order->update_meta_data( '_anonymous_donation', '1' );
        }
    }

    /**
     * Apply anonymous preference to subscription renewal orders
     */
    public function apply_user_anonymous_to_subscription( $renewal_order, $subscription ) {
        $user_id = $subscription->get_user_id();
        if ( ! $user_id ) {
            return;
        }

        $anonymous_status = get_user_meta( $user_id, 'anonymous_donation_status', true );

        if ( $anonymous_status === 'yes' ) {
            $renewal_order->update_meta_data( '_anonymous_donation', '1' );
            $renewal_order->save();
        }
    }

    public function admin_init() {
        // Legacy (pre-WC 7)
        add_filter( 'manage_edit-shop_order_columns', array( $this, 'shop_order_columns' ) );
        add_action( 'manage_shop_order_posts_custom_column', array( $this, 'legacy_shop_order_custom_column' ), 10, 2 );
        
        // Modern WC Admin (WC 7+)
        add_filter( 'woocommerce_shop_order_list_table_columns', array( $this, 'shop_order_columns' ) );
        add_action( 'woocommerce_shop_order_list_table_custom_column', array( $this, 'shop_order_custom_column' ), 10, 2 );
        
        // Add Gift Aid info under shipping address
        add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'order_custom_fields' ), 10, 1 );
    }

    public function shop_order_columns( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;

            if ( 'order_status' === $key ) {
                $new_columns['gift_aid'] = __( 'Gift Aid', 'skydonate' );
            }
        }

        return $new_columns;
    }

    public function shop_order_custom_column( $column, $order ) {
        if ( 'gift_aid' === $column && is_a( $order, 'WC_Order' ) ) {
            $this->render_gift_aid_column( $order->get_meta( 'gift_aid_it' ) );
        }
    }

    public function legacy_shop_order_custom_column( $column, $post_id ) {
        if ( 'gift_aid' === $column ) {
            $this->render_gift_aid_column( get_post_meta( $post_id, 'gift_aid_it', true ) );
        }
    }

    private function render_gift_aid_column( $gift_aid ) {
        if ( $gift_aid === 'yes' ) {
            echo '<div style="color: green;"><span class="dashicons dashicons-yes-alt"></span></div>';
        } else {
            echo '<div>-</div>';
        }
    }

    // Display Gift Aid consent checkbox under shipping address
    public function order_custom_fields( $order ) {
        $gift_aid = $order->get_meta( 'gift_aid_it' );
        ?>
        <div style="border:0.25em solid lightgreen; border-radius: 0.5em; padding: 1em; margin-top:1em;">
            <p><strong><?php esc_html_e('Important:', 'skydonate'); ?></strong> <?php esc_html_e('You should only check this box if the customer has consented by some other means. It is your responsibility to submit accurate data to HMRC.', 'skydonate'); ?></p>
            <p>
                <label for="gift_aid_it">
                    <input type="checkbox" name="gift_aid_it" id="gift_aid_it" value="yes" <?php checked( $gift_aid, 'yes' ); ?> />
                    <?php esc_html_e('Gift Aid Consented', 'skydonate'); ?>
                </label>
            </p>
        </div>
        <?php
    }


    public function export_gift_aid_orders_ajax() {
        // Verify nonce
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'skydonate_settings_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error('Permission denied');
        }

        $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;

        // Use remote stub for protected function
        $result = skydonate_remote_stubs()->export_gift_aid_orders($paged);

        if ( isset($result['error']) ) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success($result);
    }

    public function export_gift_aid_orders_by_date() {
        // Verify nonce and permissions
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'skydonate_settings_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error('Permission denied');
        }

        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['end_date'] ?? '');
        $paged      = isset($_POST['page']) ? absint($_POST['page']) : 1;

        if ( empty($start_date) || empty($end_date) ) {
            wp_send_json_error('Please provide both start and end dates.');
        }

        // Use remote stub for protected function
        $result = skydonate_remote_stubs()->export_gift_aid_orders_by_date($start_date, $end_date, $paged);

        if ( isset($result['error']) ) {
            wp_send_json_error($result['error']);
        }

        wp_send_json_success($result);
    }




}

endif;

new Skydonate_Gift_Aid();
