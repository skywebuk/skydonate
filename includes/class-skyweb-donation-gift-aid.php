<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Skyweb_Donation_Gift_Aid' ) ) :

class Skyweb_Donation_Gift_Aid {

    public function __construct() {



        // AJAX handlers for exporting Gift Aid orders
        add_action( 'wp_ajax_export_gift_aid_orders_ajax', [ $this, 'export_gift_aid_orders_ajax' ] );
        add_action( 'wp_ajax_export_gift_aid_orders_by_date', [ $this, 'export_gift_aid_orders_by_date' ] );
        

		if (get_option('enable_gift_aid', 0) == 1) {
            // Initialize admin settings
            add_action( 'admin_init', array( $this, 'admin_init' ) );

            // Add Gift Aid section to My Account page
            add_action( 'woocommerce_before_my_account', array( $this, 'my_account_gift_aid_field' ) );
            add_action( 'wp_ajax_save_gift_aid', [$this, 'skyweb_ajax_save_gift_aid'] );

            // Save the Gift Aid checkbox value when order is processed
            add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_gift_aid_checkbox' ), 45, 2 );

            // Save Gift Aid meta during WooCommerce checkout
            add_action( 'woocommerce_checkout_create_order', array( $this, 'save_gift_aid_meta' ), 10, 2 );

            // Apply Gift Aid to subscription renewals
            add_action( 'woocommerce_subscriptions_renewal_order_created', array( $this, 'apply_user_gift_aid_to_subscription' ), 10, 2 );

            // Include Gift Aid meta in WooCommerce emails
            add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'email_gift_aid_meta' ), 10, 3 );
		}


    }


    // Save checkbox value
    public function save_gift_aid_checkbox( $order_id ) {
        $order = wc_get_order( $order_id );
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
        $gift_aid_logo         = get_option( 'gift_aid_logo' ) ?: SKYWEB_DONATION_SYSTEM_PUBLIC_ASSETS . '/img/gift-aid-uk-logo.svg';
        $default_checked       = get_option( 'gift_aid_default_status', 1 ) ?: 1;
        ?>
        <div class="my-account-gift-aid skyweb-gift-aid-wrapper">
            <img src="<?php echo esc_url( $gift_aid_logo ); ?>" alt="<?php esc_attr_e( 'Gift Aid Logo', 'skydonate' ); ?>" class="skyweb-gift-aid-logo">

            <h3 class="skyweb-gift-aid-title"><?php esc_html_e( 'Gift Aid', 'skydonate' ); ?></h3>

            <p class="skyweb-gift-aid-description">
                <?php echo esc_html( $gift_aid_description ); ?>
            </p>

            <form id="gift-aid-form" class="skyweb-gift-aid-form" method="post">
                <p class="form-row skyweb-gift-aid-checkbox-row">
                    <label class="sky-smart-switch checkbox">
                        <input type="checkbox"
                            class="input-checkbox"
                            name="gift_aid_status"
                            value="yes"
                            <?php
                                // If user already has it enabled or admin default says "checked"
                                checked( $gift_aid === 'yes' || ( $gift_aid === 'no' && $default_checked === 1 ) );
                            ?>
                        >
                        <span class="switch"></span>
                        <?php echo esc_html( $gift_aid_label ); ?>
                    </label>
                </p>

                <p class="gift-aid-note skyweb-gift-aid-note-text">
                    <?php echo esc_html( $gift_aid_note ); ?>
                </p>

                <p class="mb-0 skyweb-gift-aid-button-row">
                    <button type="submit" class="button primary-button skyweb-gift-aid-submit">
                        <?php esc_html_e( 'Save changes', 'skydonate' ); ?>
                    </button>
                </p>

                <div id="gift-aid-message" class="skyweb-gift-aid-message"></div>
            </form>
        </div>
        <?php
    }


    public function skyweb_ajax_save_gift_aid() {
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

        // Update past orders
        $orders = wc_get_orders( array(
            'customer_id' => $user_id,
            'limit'       => -1,
            'status'      => array( 'wc-completed', 'wc-processing' ),
        ) );

        foreach ( $orders as $order ) {
            $order->update_meta_data( 'gift_aid_status', $gift_aid_value );
            $order->save();
        }

        wp_send_json_success( array( 'message' => 'Gift Aid settings saved.' ) );
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
                $new_columns['gift_aid'] = __( 'Gift Aid', 'skyweb' );
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
            <p><strong><?php esc_html_e('Important:', 'wc-uk-gift-aid'); ?></strong> <?php esc_html_e('You should only check this box if the customer has consented by some other means. It is your responsibility to submit accurate data to HMRC.', 'wc-uk-gift-aid'); ?></p>
            <p>
                <label for="gift_aid_it">
                    <input type="checkbox" name="gift_aid_it" id="gift_aid_it" value="yes" <?php checked( $gift_aid, 'yes' ); ?> />
                    <?php esc_html_e('Gift Aid Consented', 'wc-uk-gift-aid'); ?>
                </label>
            </p>
        </div>
        <?php
    }


    public function export_gift_aid_orders_ajax() {
        // Verify nonce
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'skydonation_settings_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error('Permission denied');
        }

        $paged  = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $limit  = 1000; // process 1000 orders per batch
        $offset = ($paged - 1) * $limit;

        // Allowed order statuses (only completed and renewal)
        $allowed_statuses = array('wc-completed', 'wc-renewal');

        // Query eligible orders
        $args = array(
            'post_type'      => 'shop_order',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'post_status'    => $allowed_statuses,
            'meta_query'     => array(
                array(
                    'key'     => 'gift_aid_it',
                    'value'   => 'yes',
                    'compare' => '=',
                ),
            ),
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        );

        $order_ids = get_posts( $args );

        if ( empty( $order_ids ) ) {
            wp_send_json_success([
                'done' => true,
                'csv' => '',
            ]);
        }

        $output = fopen('php://temp', 'r+');

        if ($paged === 1) {
            fputcsv($output, array(
                'Title', 'First Name', 'Last Name',
                'Address Line 1', 'Address Line 2',
                'City', 'Postcode', 'Country',
                'Date', 'Amount'
            ));
        }

        foreach ( $order_ids as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) continue;

            fputcsv($output, array(
                get_post_meta( $order_id, '_billing_name_title', true ),
                $order->get_billing_first_name(),
                $order->get_billing_last_name(),
                $order->get_billing_address_1(),
                $order->get_billing_address_2(),
                $order->get_billing_city(),
                $order->get_billing_postcode(),
                $order->get_billing_country(),
                $order->get_date_created() ? $order->get_date_created()->date('Y-m-d') : '',
                $order->get_total(),
            ));
        }

        rewind($output);
        $csv_chunk = stream_get_contents($output);
        fclose($output);

        wp_send_json_success([
            'done' => false,
            'page' => $paged,
            'csv'  => $csv_chunk,
        ]);
    }

    public function export_gift_aid_orders_by_date() {
        // Verify nonce and permissions
        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'skydonation_settings_nonce') ) {
            wp_send_json_error('Invalid nonce');
        }

        if ( ! current_user_can('administrator') ) {
            wp_send_json_error('Permission denied');
        }

        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date   = sanitize_text_field($_POST['end_date'] ?? '');
        $paged      = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $limit      = 1000;
        $offset     = ($paged - 1) * $limit;

        if ( empty($start_date) || empty($end_date) ) {
            wp_send_json_error('Please provide both start and end dates.');
        }

        $allowed_statuses = ['wc-completed', 'wc-renewal'];

        $args = [
            'post_type'      => 'shop_order',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'post_status'    => $allowed_statuses,
            'meta_query'     => [
                [
                    'key'   => 'gift_aid_it',
                    'value' => 'yes',
                ],
            ],
            'date_query' => [
                [
                    'after'     => $start_date . ' 00:00:00',
                    'before'    => $end_date . ' 23:59:59',
                    'inclusive' => true,
                ],
            ],
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ];

        $order_ids = get_posts($args);

        if ( empty( $order_ids ) ) {
            wp_send_json_success([
                'done' => true,
                'csv' => '',
                'filename' => 'gift_aid_orders_' . $start_date . '_to_' . $end_date . '.csv',
            ]);
        }

        $output = fopen('php://temp', 'r+');

        if ($paged === 1) {
            fputcsv($output, [
                'Title', 'First Name', 'Last Name',
                'Address Line 1', 'Address Line 2',
                'City', 'Postcode', 'Country',
                'Date', 'Amount'
            ]);
        }

        foreach ($order_ids as $order_id) {
            $order = wc_get_order($order_id);
            if ( ! $order ) continue;

            $order_date = $order->get_date_created();
            $formatted_date = $order_date ? $order_date->date('Y-m-d') : '';

            fputcsv($output, [
                get_post_meta($order_id, '_billing_name_title', true),
                $order->get_billing_first_name(),
                $order->get_billing_last_name(),
                $order->get_billing_address_1(),
                $order->get_billing_address_2(),
                $order->get_billing_city(),
                $order->get_billing_postcode(),
                $order->get_billing_country(),
                $formatted_date,
                $order->get_total(),
            ]);
        }

        rewind($output);
        $csv_chunk = stream_get_contents($output);
        fclose($output);

        wp_send_json_success([
            'done' => false,
            'page' => $paged,
            'csv'  => $csv_chunk,
            'filename' => 'gift_aid_orders_' . $start_date . '_to_' . $end_date . '.csv',
        ]);
    }




}

endif;

new Skyweb_Donation_Gift_Aid();
