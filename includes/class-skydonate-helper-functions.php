<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;

class Skydonate_Functions {

    /**
     * Constructor to initialize hooks and filters.
     */
    public function __construct() {
        // Hook the title update method early
        add_action('init', [$this, 'donation_title_update']);
        
        // Hook into WooCommerce product list actions and filters
        add_filter('manage_edit-product_columns', [$this, 'add_custom_product_columns']);
        add_action('manage_product_posts_custom_column', [$this, 'populate_custom_product_columns'], 10, 2);
        add_filter('manage_edit-product_sortable_columns', [$this, 'make_custom_columns_sortable']);
        add_action('pre_get_posts', [$this, 'custom_columns_sorting']);

        if ( $this->is_HPOS_active()) {
        	add_filter( 'woocommerce_orders_table_query_clauses', array( $this, 'filter_hpos_query' ), 10, 2 );
        } else {
        	add_filter( 'posts_where', array( $this, 'filter_where' ), 10, 2 );
        }
    
        add_shortcode( 'product_add_to_cart', [$this, 'custom_product_add_to_cart_shortcode'] );

        add_action('wp_ajax_load_more_donations', [$this, 'load_more_donations']);
        add_action('wp_ajax_nopriv_load_more_donations', [$this, 'load_more_donations']);

        add_action( 'init', [$this, 'register_donation_type_taxonomy'] );

        // Add "Name Title" before first name when billing fields are loaded
        add_filter( 'woocommerce_admin_billing_fields', [ $this, 'add_billing_name_title_field' ], 10, 1 );

        // Save the new field when order is updated
        add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save_custom_field' ], 45, 2 );


        add_action('wp_ajax_skydonate_load_more_donations', [$this, 'skydonate_load_more_donations']);
        add_action('wp_ajax_nopriv_skydonate_load_more_donations', [$this, 'skydonate_load_more_donations']);

        // Update raised amount when order status changes
        add_action('woocommerce_order_status_completed', [$this, 'update_product_raised_amount_on_order'], 10, 1);
        add_action('woocommerce_order_status_changed', [$this, 'update_product_raised_amount_on_status_change'], 10, 4);

    }

    public static function get_user_country_name( $format = 'name' ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->get_user_country_name($format);
    }

    public static function get_currency_by_country_code( $country_code ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->get_currency_by_country_code($country_code);
    }

    
    // Add Name Title before First Name
    public function add_billing_name_title_field( $fields ) {
        $new_fields = [];

        foreach ( $fields as $key => $field ) {
            if ( $key === 'first_name' ) {
                $order_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
                $value    = '';

                // Use HPOS-compatible method to get order meta
                if ( $order_id ) {
                    $order = wc_get_order( $order_id );
                    if ( $order ) {
                        $value = $order->get_meta( '_billing_name_title', true );
                    }
                }

                $new_fields['name_title'] = [
                    'label' => __( 'Name Title', 'skydonate' ),
                    'show'  => false,
                    'type'  => 'text',
                    'value' => $value ? $value : __( 'Mr.', 'skydonate' ),
                ];
            }
            $new_fields[ $key ] = $field;
        }

        return $new_fields;
    }

    // Save field value (HPOS-compatible)
    public function save_custom_field( $order_id, $post ) {
        if ( isset( $_POST['billing_name_title'] ) ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $order->update_meta_data( '_billing_name_title', sanitize_text_field( $_POST['billing_name_title'] ) );
                $order->save();
            }
        }
    }

    public function register_donation_type_taxonomy() {
        $labels = array(
            'name'              => _x( 'Donation Types', 'taxonomy general name', 'skydonate' ),
            'singular_name'     => _x( 'Donation Type', 'taxonomy singular name', 'skydonate' ),
            'search_items'      => __( 'Search Donation Types', 'skydonate' ),
            'all_items'         => __( 'All Donation Types', 'skydonate' ),
            'parent_item'       => __( 'Parent Donation Type', 'skydonate' ),
            'parent_item_colon' => __( 'Parent Donation Type:', 'skydonate' ),
            'edit_item'         => __( 'Edit Donation Type', 'skydonate' ),
            'update_item'       => __( 'Update Donation Type', 'skydonate' ),
            'add_new_item'      => __( 'Add New Donation Type', 'skydonate' ),
            'new_item_name'     => __( 'New Donation Type Name', 'skydonate' ),
            'menu_name'         => __( 'Donation Type', 'skydonate' ),
        );
    
        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true, // Set to true if you want a category-like structure
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'donation-type' ),
        );
    
        register_taxonomy( 'donation_type', 'product', $args );
    } 


    public static function format_large_number($number) {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M'; // Format as millions
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K'; // Format as thousands
        }
        return (string) $number; // Return as is if less than 1k
    }
    

    public function load_more_donations() {
        // Verify nonce for security
        check_ajax_referer('skydonate_nonce', 'nonce');

        $product_ids = isset($_POST['product_ids']) ? json_decode(sanitize_text_field(wp_unslash($_POST['product_ids'])), true) : [];
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $tab_order_ids = self::get_orders_ids_by_product_id($product_ids, ['wc-completed'], $offset, '');
        $name_display_option = isset($_POST['namestate']) ? sanitize_text_field($_POST['namestate']) : 'first_last_initial';

        // Check if 'icon' exists in the POST data and is an array - sanitize icon data
        $icon_data = [];
        if (isset($_POST['icon']) && is_array($_POST['icon'])) {
            $icon_data = array_map('sanitize_text_field', $_POST['icon']);
        }
    
        ob_start();
        ?>
        <?php foreach ($tab_order_ids as $order_id):
            $order = wc_get_order($order_id);
            $is_anonymous = $order->get_meta('_anonymous_donation', true);

            if ($is_anonymous === '1') {
                $customer_name = esc_html__('Anonymous', 'skydonate');
            } else {
                if ($name_display_option === 'full_name') {
                    // Display full name (first and last)
                    $customer_name = esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                } elseif ($name_display_option === 'first_last_initial') {
                    // Display first name and last initial
                    $customer_name = esc_html($order->get_billing_first_name() . ' ' . strtoupper(substr($order->get_billing_last_name(), 0, 1)) . '.');
                } elseif ($name_display_option === 'first_name') {
                    // Display only first name
                    $customer_name = esc_html($order->get_billing_first_name());
                } elseif ($name_display_option === 'initial') {
                    // Display only first initial with last name
                    $first_initial = strtoupper(substr($order->get_billing_first_name(), 0, 1));
                    $customer_name = esc_html($first_initial . ' ' . $order->get_billing_last_name());
                }
            }

            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );
            $donation_amount = 0;

            // Calculate donation amount for relevant products in this order
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                if (in_array($product_id, $product_ids)) { // Ensure this is a relevant product
                    $donation_amount += $item->get_total();
                }
            }
    
            $donation_amount = esc_html(number_format((float) $donation_amount, 0));
            $time_ago = esc_html(human_time_diff(strtotime($order->get_date_created()), time()));
            ?>
            <li class="woocommerce-order" data-order-id="<?php echo esc_attr($order_id); ?>">
                <div class="list-box">
                    <span class="list-icon">
                        <?php 
                        if (!empty($icon_data)) {
                            \Elementor\Icons_Manager::render_icon($icon_data, ['aria-hidden' => 'true']); 
                        } else {
                            // Fallback icon if $icon_data is empty or invalid
                            echo '<i class="fa fa-gift" aria-hidden="true"></i>';
                        }
                        ?>
                    </span>
                    <h4 class="donar-name"><?php echo $customer_name; ?></h4>
                    <div class="donate-price">
                        <strong><?php echo $currency_symbol . $donation_amount; ?></strong> 
                        <small class="fa-solid fa-circle"></small> 
                        <span class="time"><?php echo $time_ago; ?></span>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
        <?php
        $html = ob_get_clean();
    
        // Send JSON response
        wp_send_json_success([
            'html'  => $html,
            'count' => count($tab_order_ids)
        ]);
        // wp_send_json_success already calls wp_die(), no need to call it again
    }
    

    public static function get_all_countries_from_orders() {
        // Retrieve completed orders with a reasonable limit
        $completed_orders = wc_get_orders(array(
            'status' => 'completed',
            'limit' => 1000 // Limit to prevent performance issues
        ));

        $countries = array();

        // Add an option for "All" at the beginning
        $countries['all'] = 'All';

        // Loop through each order to get the billing country code (HPOS-compatible)
        foreach ($completed_orders as $order) {
            $country_code = strtoupper( $order->get_billing_country() );

            // Check if country code is valid, then map it to the country name
            if (!empty($country_code) && isset(WC()->countries->countries[$country_code])) {
                $country_name = WC()->countries->countries[$country_code];
                $country_slug = strtolower($country_code); // Set slug as lowercase country code

                // Add country name with slug key if it's not already present
                if (!isset($countries[$country_slug])) {
                    $countries[$country_slug] = $country_name;
                }
            }
        }

        return $countries; // Return unique country names with slugs as keys
    }
    
    // Shortcode to display only the product add-to-cart form
    public function custom_product_add_to_cart_shortcode( $atts ) {
        global $post, $product;

        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) ) {
            return esc_html__( 'WooCommerce is not installed or active.', 'skydonate' );
        }

        // Parse shortcode attributes
        $atts = shortcode_atts(
            array(
                'id' => null, // You can pass the product ID through the shortcode
            ),
            $atts,
            'product_add_to_cart'
        );

        // If the ID is passed, get the product by ID
        if ( $atts['id'] ) {
            $product_id = absint( $atts['id'] );
            $post = get_post( $product_id );
            $product = wc_get_product( $product_id );
        }

        // Ensure that it's a valid product
        if ( $product && $product->is_purchasable() ) {
            ob_start();

            // Display the add-to-cart form
            woocommerce_template_single_add_to_cart();

            return ob_get_clean();
        } else {
            return esc_html__( 'This product is not available for purchase.', 'skydonate' );
        }
    }

	/**
	 * Filter HPOS orders query to show only orders containing a specific product.
	 * Uses _skydonate_product_id order meta for efficient filtering.
	 */
	public function filter_hpos_query( $pieces, $args ) {
		if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wc-orders' && ! empty( $_GET['product_id'] ) ) {
			global $wpdb;
			$product_id = intval( $_GET['product_id'] );
			$meta_table = $wpdb->prefix . 'wc_orders_meta';
			$orders_table = $wpdb->prefix . 'wc_orders';

			// Filter using _skydonate_product_id order meta
			$pieces['where'] .= $wpdb->prepare(
				" AND {$orders_table}.id IN (
					SELECT order_id FROM {$meta_table}
					WHERE meta_key = '_skydonate_product_id'
					AND meta_value = %s
				)",
				$product_id
			);
		}

		return $pieces;
	}

	/**
	 * Filter legacy orders query to show only orders containing a specific product.
	 * Uses _skydonate_product_id order meta for efficient filtering.
	 */
	public function filter_where( $where, $query ) {
		if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wc-orders' && ! empty( $_GET['product_id'] ) ) {
			global $wpdb;
			$product_id = intval( $_GET['product_id'] );

			// Filter using _skydonate_product_id order meta
			$where .= $wpdb->prepare(
				" AND {$wpdb->posts}.ID IN (
					SELECT post_id FROM {$wpdb->postmeta}
					WHERE meta_key = '_skydonate_product_id'
					AND meta_value = %s
				)",
				$product_id
			);
		}
		return $where;
	}
	
    public function is_HPOS_active() {
        if ( ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
            return false;
        }

        if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add custom columns to the WooCommerce product list.
     */
    public function add_custom_product_columns($columns) {
        $columns['total_sales_amount'] = __('Raised Amount', 'woocommerce');
        $columns['order_count'] = __('Donation Count', 'woocommerce');
        return $columns;
    }

    /**
     * Populate the custom columns with data.
     */
    public function populate_custom_product_columns($column, $post_id) {
        switch ($column) {
            case 'total_sales_amount':
                $total_sales = $this->get_total_donation_sales($post_id);
                echo wc_price($total_sales);
                break;
    
            case 'order_count':
                $order_count = $this->get_donation_order_count($post_id);
                // Make the donation count clickable with product ID filter on WooCommerce Orders page
                $url = admin_url('admin.php?page=wc-orders&product_id=' . $post_id);
                echo '<a href="' . esc_url($url) . '">' . esc_html($order_count) . '</a>';
                break;
        }
    }
    

    /**
     * Make custom columns sortable.
     */
    public function make_custom_columns_sortable($columns) {
        $columns['total_sales_amount'] = 'total_sales_amount';
        $columns['order_count'] = 'order_count';
        return $columns;
    }

    /**
     * Handle sorting for custom columns.
     */
    public function custom_columns_sorting($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }

        $orderby = $query->get('orderby');

        if ('total_sales_amount' === $orderby) {
            $query->set('meta_key', '_total_sales_amount');
            $query->set('orderby', 'meta_value_num');
        } elseif ('order_count' === $orderby) {
            $query->set('meta_key', '_order_count');
            $query->set('orderby', 'meta_value_num');
        }
    }

    /**
     * Get total donation sales for a product using cached meta value.
     *
     * @param int $product_id Product ID
     * @return float Total sales amount
     */
    public function get_total_donation_sales($product_id) {
        $cached = get_post_meta($product_id, '_total_sales_amount', true);
        return $cached ? floatval($cached) : 0;
    }

    /**
     * Get donation order count for a product using cached meta value.
     *
     * @param int $product_id Product ID
     * @return int Order count
     */
    public function get_donation_order_count($product_id) {
        $cached = get_post_meta($product_id, '_order_count', true);
        return $cached ? intval($cached) : 0;
    }

    /**
     * Recalculate and update product donation meta via remote function.
     *
     * @param int $product_id Product ID
     */
    private function recalculate_product_donation_meta($product_id) {
        // Use remote stubs to recalculate and update the meta
        if (skydonate_remote_stubs()->is_remote_available()) {
            skydonate_remote_stubs()->get_total_donation_sales($product_id);
            skydonate_remote_stubs()->get_donation_order_count($product_id);
        }
    }

    /**
     * Update raised amount for all products in an order when order is completed.
     * Also stores product IDs as order meta for efficient filtering.
     *
     * @param int $order_id The order ID.
     */
    public function update_product_raised_amount_on_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $updated_products = [];
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($product_id && !in_array($product_id, $updated_products)) {
                $this->recalculate_product_donation_meta($product_id);
                $updated_products[] = $product_id;
            }
        }

        // Store product IDs as order meta for efficient filtering
        if (!empty($updated_products)) {
            foreach ($updated_products as $product_id) {
                $order->add_meta_data('_skydonate_product_id', $product_id, false);
            }
            $order->save();
        }
    }

    /**
     * Update raised amount when order status changes (handles refunds, cancellations).
     *
     * @param int    $order_id   The order ID.
     * @param string $old_status Old order status.
     * @param string $new_status New order status.
     * @param object $order      The order object.
     */
    public function update_product_raised_amount_on_status_change($order_id, $old_status, $new_status, $order) {
        // Only update if transitioning from/to completed status
        $relevant_statuses = ['completed', 'refunded', 'cancelled'];
        if (!in_array($old_status, $relevant_statuses) && !in_array($new_status, $relevant_statuses)) {
            return;
        }

        // Skip if this is a new completion (already handled by woocommerce_order_status_completed)
        if ($new_status === 'completed' && $old_status !== 'completed') {
            return;
        }

        $updated_products = [];
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            if ($product_id && !in_array($product_id, $updated_products)) {
                $this->recalculate_product_donation_meta($product_id);
                $updated_products[] = $product_id;
            }
        }
    }


    /**
     * Initialize all the hooks and filters.
     */
    public function donation_title_update() {
        $title_prefix = get_option( 'wc_custom_title_prefix_enable', 'no' );
        // Check if the title prefix option is enabled
        if ( $title_prefix == 'yes' ) {
            // Remove the default WooCommerce shop loop item title action
            remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
            remove_action( 'woocommerce_before_template_part', 'MagicLogin\WooCommerce\maybe_add_magic_login_to_woocommerce_login_form', 10, 2 );
            remove_action( 'woocommerce_after_template_part', 'MagicLogin\WooCommerce\maybe_add_magic_login_to_woocommerce_login_form', 10, 2 );
            
            // Add custom title action
            add_action( 'woocommerce_shop_loop_item_title', [ $this, 'custom_sponsor_shop_loop_item_title' ], 10 );
        }
    }

    /**
     * Custom title display for the shop loop.
     */
    public function custom_sponsor_shop_loop_item_title() {
        global $product;

        // Initialize variables
        $product_title = get_the_title();
        $attributes = '';

        // Check if the product is a variation
        if ( $product->is_type( 'variation' ) ) {
            $parent_id = $product->get_parent_id();
            $product_title = get_the_title( $parent_id );
            $attributes = wc_get_formatted_variation( $product, true, false, false );
        }

        // Display different titles based on attributes
        if ( ! empty( $attributes ) ) {
            echo '<h2 class="wd-entities-title large"><a href="' . get_the_permalink() . '">' . __( 'My Name is', 'skydonate' ) . ' ' . esc_html( $attributes ) . '</a></h2>';
            echo '<p class="text-white">' . esc_html( $product_title ) . '</p>';
        } else {
            echo '<h3 class="wd-entities-title large"><a href="' . get_the_permalink() . '">' . esc_html( $product_title ) . '</a></h3>';
        }
    }
    
    public static function Get_Taxonomies( $sky_texonomy = 'category' ){
        $options = array(); // Initialize variable
        $terms = get_terms( array(
            'taxonomy' => $sky_texonomy,
            'hide_empty' => true,
        ));
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
            foreach ( $terms as $term ) {
                $options[ $term->slug ] = $term->name;
            }
        }
        return $options; // Always return array
    }

    public static function Get_Title( $sky_type = 'post', $return_type = 'ids', $additional_option = [] ) {
        // Query for the specified post type
        $post_type_query = new WP_Query(  
            array(  
                'post_type'      => $sky_type,  
                'posts_per_page' => -1  
            )  
        );
    
        // Retrieve the posts from the query
        $posts_array = $post_type_query->posts;
    
        // Initialize $post_data with $additional_option to ensure it appears first
        $post_data = $additional_option;
    
        // Determine what to return based on the $return_type parameter
        if ( $return_type === 'slugs' ) {
            // Add slugs as keys and post titles as values
            $post_data += wp_list_pluck( $posts_array, 'post_title', 'post_name' );
        } else {
            // Add IDs as keys and post titles as values
            $post_data += wp_list_pluck( $posts_array, 'post_title', 'ID' );
        }
    
        return $post_data;
    }
    
    
    public static function get_orders_ids_by_product_id( $product_ids = [], $order_status = ['wc-completed'], $limit = 100, $start_date = '' ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->get_orders_ids_by_product_id($product_ids, $order_status, $limit, $start_date);
    }

    public static function get_top_amount_orders_by_product_ids(
        $product_ids = [],
        $order_status = ['wc-completed'],
        $limit = 100,
        $start_date = ''
    ) {
        // Use remote stub for protected function
        return skydonate_remote_stubs()->get_top_amount_orders_by_product_ids($product_ids, $order_status, $limit, $start_date);
    }

    public static function get_product_ids_by_multiple_taxonomies( $slugs, $taxonomy = 'product_cat' ) {
        // Ensure $slugs is an array before proceeding
        if ( ! is_array( $slugs ) ) {
            return []; // Return an empty array if $slugs is not an array
        }
    
        $all_product_ids = []; // Initialize an empty array to store product IDs
    
        // Loop through each slug (category or tag)
        foreach ( $slugs as $slug ) {
            // Get the term object by slug for the given taxonomy
            $term = get_term_by( 'slug', $slug, $taxonomy );
    
            // Skip if the term doesn't exist
            if ( ! $term || is_wp_error( $term ) ) {
                continue;
            }
    
            // Query to get product IDs for the current term
            $query_args = [
                'post_type'      => 'product',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => [
                    [
                        'taxonomy' => $taxonomy,
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ],
                ],
            ];
    
            $product_ids = get_posts( $query_args );
    
            // Merge product IDs into the main array
            $all_product_ids = array_merge( $all_product_ids, $product_ids );
        }
        // Remove duplicates if products belong to multiple terms
        $all_product_ids = array_unique( $all_product_ids );
        return $all_product_ids;
    }
    
    public static function Get_Currency_Symbol() {
        // Get the current WooCommerce currency
        $currency = get_option('woocommerce_currency');
        // Get the currency symbol and decode HTML entities
        $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $currency ) );
        return $currency_symbol;
    }

    public static function loader_icon($width = '32px', $height = '32px'){
        return '<svg class="loader-svg" width="'.esc_attr($width).'" height="'.esc_attr($height).'" viewBox="0 0 85 75"><circle class="loader-circle" cx="42" cy="40" r="30" fill="none" stroke="currentColor" stroke-width="7" stroke-linecap="round"/></svg>';
    }


    public static function skydonate_submit_button( $atts = array() ) {
        // Loader SVG
        $loader_svg = self::loader_icon();
        echo '<button type="submit" class="form-submit-button">';
            if ( ! empty( $atts["before_icon"] ) ) {
                echo '<span class="icon left">' . html_entity_decode( $atts["before_icon"] ) . '</span>';
            }
            echo '<span class="button-text">' . esc_html( $atts["button_text"] ) . '</span>';
            echo '<span class="icon right">';
                echo $loader_svg;
                if ( ! empty( $atts["after_icon"] ) ) {
                    echo '<span class="loader-icon">';
                    echo html_entity_decode( $atts["after_icon"] );
                    echo '</span>';
                }
            echo '</span>';
        echo '</button>';
    }

    public function skydonate_load_more_donations() {
        // Verify nonce for security
        check_ajax_referer('skydonate_nonce', 'nonce');

        // Sanitize inputs
        $type        = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'all';
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
        $offset      = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit       = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
        

        // Validate required params
        if (empty($product_ids) || $limit <= 0) {
            wp_send_json_error("Missing parameters");
        }



        // Fetch enough orders to apply offset + limit
        $fetch_limit = $offset + $limit;

        if ($type === 'top') {
            $order_ids = Skydonate_Functions::get_top_amount_orders_by_product_ids(
                $product_ids,
                ['wc-completed'],
                $fetch_limit
            );
        } else {
            $order_ids = Skydonate_Functions::get_orders_ids_by_product_id(
                $product_ids,
                ['wc-completed'],
                $fetch_limit
            );
        }

        // No orders at all
        if (empty($order_ids)) {
            wp_send_json_success(['html' => '', 'done' => true]);
        }

        // Only return the page portion
        $paged_order_ids = array_slice($order_ids, $offset, $limit);

        // No more orders left after slice
        if (empty($paged_order_ids)) {
            wp_send_json_success(['html' => '', 'done' => true]);
        }

        ob_start();

        if (skydonate_get_layout('recent_donation') == 'layout-2') {
            $list_icon = isset($_POST['list_icon']) ? wp_kses_post(wp_unslash($_POST['list_icon'])) : '<i class="fas fa-hand-holding-heart"></i>';
            Skydonate_Functions::render_recent_donations_item_layout_two(
                $paged_order_ids,
                $product_ids,
                $list_icon
            );
        } else {
            Skydonate_Functions::render_recent_donations_item_layout_one(
                $paged_order_ids,
                $product_ids,
                false
            );
        }

        $html = ob_get_clean();

        wp_send_json_success([
            'html'   => $html,
            'done'   => ($html === ''),      // true if nothing rendered
            'offset' => $offset,
            'limit'  => $limit,
            'count'  => count($paged_order_ids)
        ]);
    }


    public static function render_recent_donations_item_layout_one($order_ids, $product_ids, $hidden_class = false) {
        // Use remote stub for protected function
        skydonate_remote_stubs()->render_recent_donations_layout_one($order_ids, $product_ids, $hidden_class);
    }

    public static function render_recent_donations_item_layout_two($order_ids, $product_ids, $list_icon = '<i class="fas fa-hand-holding-heart"></i>') {
        // Use remote stub for protected function
        skydonate_remote_stubs()->render_recent_donations_layout_two($order_ids, $product_ids, $list_icon);
    }



}

// Initialize the class
new Skydonate_Functions();