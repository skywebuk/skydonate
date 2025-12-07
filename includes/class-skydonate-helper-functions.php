<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use Automattic\WooCommerce\Utilities\OrderUtil;

class Skyweb_Donation_Functions {

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


        add_action('wp_ajax_skyweb_load_more_donations', [$this, 'skyweb_load_more_donations']);
        add_action('wp_ajax_nopriv_skyweb_load_more_donations', [$this, 'skyweb_load_more_donations']);

    }

    public static function get_user_country_name( $format = 'name' ) {
        if ( ! class_exists( 'WC_Geolocation' ) ) {
            return 'Unknown';
        }
    
        // Get geolocation data from WooCommerce
        $geo = new WC_Geolocation();
        $user_geo = $geo->geolocate_ip();
    
        $country_code = ! empty( $user_geo['country'] ) ? strtoupper( $user_geo['country'] ) : '';
    
        if ( ! $country_code ) {
            return 'Unknown';
        }
    
        // If format is 'code', return country code directly
        if ( strtolower( $format ) === 'code' ) {
            return $country_code;
        }
    
        // Otherwise, get the full country name
        $wc_countries = new WC_Countries();
        $countries = $wc_countries->get_countries();
    
        return isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : 'Unknown';
    }

    public static function get_currency_by_country_code( $country_code ) {
        if ( empty( $country_code ) ) {
            return get_woocommerce_currency(); // fallback to store currency
        }

        $country_code = strtoupper( trim( $country_code ) );

        // Country → Currency mapping
        $map = [ 'AE' => 'AED', 'AF' => 'AFN', 'AL' => 'ALL', 'AM' => 'AMD', 'AO' => 'AOA', 'AR' => 'ARS', 'AU' => 'AUD', 'AT' => 'EUR', 'AZ' => 'AZN', 'BD' => 'BDT', 'BE' => 'EUR', 'BG' => 'BGN', 'BH' => 'BHD', 'BN' => 'BND', 'BO' => 'BOB', 'BR' => 'BRL', 'BT' => 'BTN', 'BY' => 'BYN', 'CA' => 'CAD', 'CH' => 'CHF', 'CL' => 'CLP', 'CN' => 'CNY', 'CO' => 'COP', 'CR' => 'CRC', 'CZ' => 'CZK', 'DE' => 'EUR', 'DK' => 'DKK', 'DO' => 'DOP', 'DZ' => 'DZD', 'EG' => 'EGP', 'ES' => 'EUR', 'ET' => 'ETB', 'FI' => 'EUR', 'FR' => 'EUR', 'GB' => 'GBP', 'GH' => 'GHS', 'GR' => 'EUR', 'HK' => 'HKD', 'HR' => 'HRK', 'HU' => 'HUF', 'ID' => 'IDR', 'IE' => 'EUR', 'IL' => 'ILS', 'IN' => 'INR', 'IQ' => 'IQD', 'IR' => 'IRR', 'IS' => 'ISK', 'IT' => 'EUR', 'JM' => 'JMD', 'JO' => 'JOD', 'JP' => 'JPY', 'KE' => 'KES', 'KH' => 'KHR', 'KR' => 'KRW', 'KW' => 'KWD', 'KZ' => 'KZT', 'LA' => 'LAK', 'LB' => 'LBP', 'LK' => 'LKR', 'LY' => 'LYD', 'MA' => 'MAD', 'MM' => 'MMK', 'MN' => 'MNT', 'MO' => 'MOP', 'MV' => 'MVR', 'MX' => 'MXN', 'MY' => 'MYR', 'NG' => 'NGN', 'NL' => 'EUR', 'NO' => 'NOK', 'NP' => 'NPR', 'NZ' => 'NZD', 'OM' => 'OMR', 'PA' => 'PAB', 'PE' => 'PEN', 'PH' => 'PHP', 'PK' => 'PKR', 'PL' => 'PLN', 'PT' => 'EUR', 'QA' => 'QAR', 'RO' => 'RON', 'RU' => 'RUB', 'SA' => 'SAR', 'SE' => 'SEK', 'SG' => 'SGD', 'SI' => 'EUR', 'SK' => 'EUR', 'TH' => 'THB', 'TR' => 'TRY', 'TW' => 'TWD', 'TZ' => 'TZS', 'UA' => 'UAH', 'UG' => 'UGX', 'US' => 'USD', 'UY' => 'UYU', 'UZ' => 'UZS', 'VN' => 'VND', 'ZA' => 'ZAR', 'ZW' => 'ZWL', ];

        // Return mapped or fallback currency
        return isset( $map[ $country_code ] ) ? $map[ $country_code ] : get_woocommerce_currency();
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
        check_ajax_referer('skyweb_donation_nonce', 'nonce');

        $product_ids = isset($_POST['product_ids']) ? json_decode(stripslashes($_POST['product_ids']), true) : [];
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

	public function filter_hpos_query( $pieces, $args ) {
		if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wc-orders' && ! empty( $_GET['product_id'] )  ) {
			$product = intval($_GET['product_id']);

			// Check if selected product is inside order query
			$pieces['where'] .= " AND $product IN (";
			$pieces['where'] .= $this->query_by_product_hpos();
			$pieces['where'] .= ")";
		}

		return $pieces;
	}
	
	public static function query_by_product_hpos(){
		global $wpdb;
        $t_orders = $wpdb->prefix . "wc_orders";
		$t_order_items = $wpdb->prefix . "woocommerce_order_items";  
		$t_order_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

		// Build join query, select meta_value
		$query  = "SELECT $t_order_itemmeta.meta_value FROM";
		$query .= " $t_order_items LEFT JOIN $t_order_itemmeta";
		$query .= " on $t_order_itemmeta.order_item_id=$t_order_items.order_item_id";

		// Resultant table after join query
		/*------------------------------------------------------------------
		order_id | order_item_id* | order_item_type | meta_key | meta_value
		-------------------------------------------------------------------*/

		// Build where clause, where order_id = $t_posts.ID
		$query .= " WHERE $t_order_items.order_item_type='line_item'";
		$query .= " AND $t_order_itemmeta.meta_key='_product_id'";
		$query .= " AND $t_orders.Id=$t_order_items.order_id";

		// Visulize result
		/*-------------------------------------------------------------------
		order_id    | order_item_type | meta_key    | meta_value
		$t_posts.ID | line_item       | _product_id | <result>
		---------------------------------------------------------------------*/

		return $query;
	}
    
	public function filter_where( $where, $query ) {
		if ( is_admin() && isset( $_GET['page'] ) && $_GET['page'] === 'wc-orders' && ! empty( $_GET['product_id'] )  ) {
			$product = intval($_GET['product_id']);

			// Check if selected product is inside order query
			$where .= " AND $product IN (";
			$where .= $this->query_by_product();
			$where .= ")";
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


	// Returns list of product id
	protected function query_by_product(){
		global $wpdb;
		$t_posts = $wpdb->posts;
		$t_order_items = $wpdb->prefix . "woocommerce_order_items";
		$t_order_itemmeta = $wpdb->prefix . "woocommerce_order_itemmeta";

		// Build join query, select meta_value
		$query  = "SELECT $t_order_itemmeta.meta_value FROM";
		$query .= " $t_order_items LEFT JOIN $t_order_itemmeta";
		$query .= " on $t_order_itemmeta.order_item_id=$t_order_items.order_item_id";

		// Resultant table after join query
		/*------------------------------------------------------------------
		order_id | order_item_id* | order_item_type | meta_key | meta_value
		-------------------------------------------------------------------*/

		// Build where clause, where order_id = $t_posts.ID
		$query .= " WHERE $t_order_items.order_item_type='line_item'";
		$query .= " AND $t_order_itemmeta.meta_key='_product_id'";
		$query .= " AND $t_posts.ID=$t_order_items.order_id";

		// Visulize result
		/*-------------------------------------------------------------------
		order_id    | order_item_type | meta_key    | meta_value
		$t_posts.ID | line_item       | _product_id | <result>
		---------------------------------------------------------------------*/

		return $query;
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

    public function get_total_donation_sales($product_id) {
        global $wpdb;
        $total_sales_amount = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(om2.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1 
                ON oi.order_item_id = om1.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2 
                ON oi.order_item_id = om2.order_item_id
            INNER JOIN {$wpdb->prefix}posts AS p 
                ON oi.order_id = p.ID
            WHERE om1.meta_key = '_product_id' 
                AND om1.meta_value = %d
                AND om2.meta_key = '_line_total'
                AND p.post_status IN ('wc-completed')
        ", $product_id));
    
        // Ensure the value is not null
        $total_sales_amount = $total_sales_amount ? floatval($total_sales_amount) : 0;
    
        // Cache the total sales amount
        update_post_meta($product_id, '_total_sales_amount', $total_sales_amount);
    
        return $total_sales_amount;
    }
    

    public function get_donation_order_count($product_id) {
        global $wpdb;
        $order_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT oi.order_id)
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om 
                ON oi.order_item_id = om.order_item_id
            INNER JOIN {$wpdb->prefix}posts AS p 
                ON oi.order_id = p.ID
            WHERE om.meta_key = '_product_id' 
                AND om.meta_value = %d
                AND p.post_status IN ('wc-completed')
        ", $product_id));
    
        // Ensure the value is not null
        $order_count = $order_count ? (int) $order_count : 0;
    
        // Cache the order count
        update_post_meta($product_id, '_order_count', $order_count);
    
        return $order_count;
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
            echo '<h2 class="wd-entities-title large"><a href="' . get_the_permalink() . '">' . __( 'My Name is', 'skywebdesign.co.uk' ) . ' ' . esc_html( $attributes ) . '</a></h2>';
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
        global $wpdb;
    
        // Ensure product_ids and order_status are not empty
        if (empty($product_ids) || empty($order_status)) {
            return [];
        }
    
        // Prepare placeholders for product IDs
        $product_placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
    
        // Start SQL query
        $sql = "
            SELECT DISTINCT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta 
                ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts 
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN (" . implode(',', array_fill(0, count($order_status), '%s')) . ")
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND order_item_meta.meta_value IN ($product_placeholders)
        ";
    
        // Add start date filter conditionally
        $query_params = array_merge($order_status, $product_ids);
        if (!empty($start_date)) {
            $sql .= " AND posts.post_date >= %s";
            $query_params[] = $start_date;
        }
    
        // Add ORDER BY clause to sort by post_date (latest first)
        $sql .= " ORDER BY posts.post_date DESC";
    
        // Add LIMIT clause
        if (!empty($limit)) {
            $sql .= " LIMIT %d";
            $query_params[] = intval($limit);
        }
    
        // Prepare and execute query
        $prepared_sql = $wpdb->prepare($sql, $query_params);
        $results = $wpdb->get_col($prepared_sql);
    
        return $results;
    }

    public static function get_top_amount_orders_by_product_ids( 
        $product_ids = [], 
        $order_status = ['wc-completed'], 
        $limit = 100, 
        $start_date = '' 
    ) {
        global $wpdb;

        if (empty($product_ids) || empty($order_status)) {
            return [];
        }

        $product_placeholders = implode(',', array_fill(0, count($product_ids), '%d'));
        $status_placeholders  = implode(',', array_fill(0, count($order_status), '%s'));

        $sql = "
            SELECT DISTINCT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta_product
                ON order_items.order_item_id = meta_product.order_item_id
                AND meta_product.meta_key = '_product_id'
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS meta_total
                ON order_items.order_item_id = meta_total.order_item_id
                AND meta_total.meta_key = '_line_total'
            LEFT JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ($status_placeholders)
            AND order_items.order_item_type = 'line_item'
            AND meta_product.meta_value IN ($product_placeholders)
        ";

        $query_params = array_merge($order_status, $product_ids);

        if (!empty($start_date)) {
            $sql .= " AND posts.post_date >= %s";
            $query_params[] = $start_date;
        }

        // Sort by donation amount DESC (highest first)
        $sql .= " ORDER BY CAST(meta_total.meta_value AS DECIMAL(10,2)) DESC";

        if (!empty($limit)) {
            $sql .= " LIMIT %d";
            $query_params[] = intval($limit);
        }

        $prepared_sql = $wpdb->prepare($sql, $query_params);
        return $wpdb->get_col($prepared_sql);
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

    public function skyweb_load_more_donations() {
        // Verify nonce for security
        check_ajax_referer('skyweb_donation_nonce', 'nonce');

        // Sanitize inputs
        $type        = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'all';
        $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
        $offset      = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $limit       = isset($_POST['limit']) ? intval($_POST['limit']) : 0;
        

        // Validate required params
        if (empty($product_ids) || $limit <= 0) {
            wp_send_json_error("Missing parameters");
        }

        // Layout detection
        $layout = skyweb_donation_layout_option('recent_donation_layout');
        if (!is_array($layout)) {
            $layout = ['layout1'];
        }

        // Fetch enough orders to apply offset + limit
        $fetch_limit = $offset + $limit;

        if ($type === 'top') {
            $order_ids = Skyweb_Donation_Functions::get_top_amount_orders_by_product_ids(
                $product_ids,
                ['wc-completed'],
                $fetch_limit
            );
        } else {
            $order_ids = Skyweb_Donation_Functions::get_orders_ids_by_product_id(
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

        if (in_array('layout2', $layout)) {
            $list_icon = isset($_POST['list_icon']) ? wp_kses_post(stripslashes($_POST['list_icon'])) : '<i class="fas fa-hand-holding-heart"></i>';
            Skyweb_Donation_Functions::render_recent_donations_item_layout_two(
                $paged_order_ids,
                $product_ids,
                $list_icon
            );
        } else {
            Skyweb_Donation_Functions::render_recent_donations_item_layout_one(
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

        $counter = 0;
        $order_count = count($order_ids);

        foreach ($order_ids as $order_id) {

            $counter++;
            $order = wc_get_order($order_id);

            $donation_amount = 0;

            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                if (in_array($product_id, $product_ids)) {
                    $donation_amount += $item->get_total();
                }
            }

            $donation_amount = esc_html(number_format($donation_amount, 0));
            $is_anonymous = $order->get_meta('_anonymous_donation', true);

            $customer_name = ($is_anonymous === '1')
                ? esc_html__('Anonymous', 'skydonate')
                : esc_html(
                    $order->get_billing_first_name() . ' ' .
                    substr($order->get_billing_last_name(), 0, 1) . '.'
                );

            $country = strtolower(esc_attr($order->get_billing_country()));
            $city = esc_html($order->get_billing_city());
            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );
            $time_ago = esc_html(human_time_diff(strtotime($order->get_date_created()), time()));

            if ($donation_amount > 0) {

                $item_hidden_class = ($counter <= 3 && $order_count >= 5 && $hidden_class)
                    ? ' hidden-order'
                    : '';

                echo '<li class="sky-order' . $item_hidden_class . '" data-order-id="' . esc_attr($order_id) . '">';
                    echo '<div class="item-wrap">';
                        echo "<p><strong>{$customer_name}</strong> donated <span>{$currency_symbol}{$donation_amount}</span></p>";
                        $country_code = strtoupper($country);
                        $country_name = WC()->countries->countries[$country_code] ?? '';
                        $clean_country_name = preg_replace('/\s*\(.*?\)/', '', $country_name);
                        echo "<p><span class='flag-icon flag-icon-{$country}'></span> {$city}, " . esc_html($clean_country_name) . "</p>";
                        echo "<p class='time'>{$time_ago} " . esc_html__('ago', 'skydonate') . "</p>";

                    echo '</div>';
                echo '</li>';
            }
        }
    }

    public static function render_recent_donations_item_layout_two($order_ids, $product_ids, $list_icon = '<i class="fas fa-hand-holding-heart"></i>') {
        $counter = 0;
        $order_count = count($order_ids);
        foreach ($order_ids as $order_id) {
            $counter++;
            $order = wc_get_order($order_id);
            $donation_amount = 0;
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                if (in_array($product_id, $product_ids)) {
                    $donation_amount += $item->get_total();
                }
            }
            if ($donation_amount <= 0) {
                continue;
            }
            $donation_amount = esc_html(number_format($donation_amount, 0));
            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );
            $is_anonymous = $order->get_meta('_anonymous_donation', true);
            $customer_name = ($is_anonymous === '1')
                ? esc_html__('Anonymous', 'skydonate')
                : esc_html(
                    $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()
                );
            $time_ago = esc_html(human_time_diff(strtotime($order->get_date_created()), time()));
            echo '<li class="sky-order">';
                echo '<div class="item-wrap">';
                    echo '<div class="avatar">';
                        echo $list_icon;
                    echo '</div>';
                    echo '<div class="content">';
                        echo "<div class='name'>{$customer_name}</div>";
                        echo "<ul class='meta'>";
                            echo "<li class='price'><strong>{$currency_symbol}{$donation_amount}</strong></li>";
                            echo '<li class="dot"><i class="fa-solid fa-dot"></i></li>';
                            echo "<li class='time'>{$time_ago}</li>";
                        echo "</ul>";
                    echo '</div>';
                echo '</div>';
            echo '</li>';
        }
    }



}

// Initialize the class
new Skyweb_Donation_Functions();