<?php
/**
 * SkyDonate Remote Core Functions
 *
 * THIS FILE IS LOADED REMOTELY FROM THE LICENSE SERVER
 * It contains the protected core business logic functions
 *
 * @package SkyDonate
 * @version 2.0.11
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ============================================================================
 * DONATION CALCULATION FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Get total donation sales for a product
 * Core revenue calculation function
 *
 * @param int $product_id The product ID
 * @return float Total sales amount
 */
function skydonate_remote_get_total_donation_sales( $product_id ) {
    global $wpdb;

    // Check if HPOS is enabled
    if ( skydonate_remote_is_hpos_active() ) {
        // HPOS-compatible query using wc_orders table
        $total_sales_amount = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(CAST(om2.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1
                ON oi.order_item_id = om1.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2
                ON oi.order_item_id = om2.order_item_id
            INNER JOIN {$wpdb->prefix}wc_orders AS o
                ON oi.order_id = o.id
            WHERE om1.meta_key = '_product_id'
                AND om1.meta_value = %d
                AND om2.meta_key = '_line_total'
                AND o.status IN ('wc-completed')
        ", $product_id));
    } else {
        // Legacy query using posts table
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
    }

    // Ensure the value is not null
    $total_sales_amount = $total_sales_amount ? floatval($total_sales_amount) : 0;

    // Cache the total sales amount
    update_post_meta($product_id, '_total_sales_amount', $total_sales_amount);

    return $total_sales_amount;
}

/**
 * Get donation order count for a product
 *
 * @param int $product_id The product ID
 * @return int Order count
 */
function skydonate_remote_get_donation_order_count( $product_id ) {
    global $wpdb;

    // Check if HPOS is enabled
    if ( skydonate_remote_is_hpos_active() ) {
        // HPOS-compatible query using wc_orders table
        $order_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT oi.order_id)
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om
                ON oi.order_item_id = om.order_item_id
            INNER JOIN {$wpdb->prefix}wc_orders AS o
                ON oi.order_id = o.id
            WHERE om.meta_key = '_product_id'
                AND om.meta_value = %d
                AND o.status IN ('wc-completed')
        ", $product_id));
    } else {
        // Legacy query using posts table
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
    }

    // Ensure the value is not null
    $order_count = $order_count ? (int) $order_count : 0;

    // Cache the order count
    update_post_meta($product_id, '_order_count', $order_count);

    return $order_count;
}

/**
 * Get order IDs by product ID
 *
 * @param array  $product_ids   Array of product IDs
 * @param array  $order_status  Array of order statuses
 * @param int    $limit         Limit for results
 * @param string $start_date    Start date filter
 * @return array Order IDs
 */
function skydonate_remote_get_orders_ids_by_product_id( $product_ids = [], $order_status = ['wc-completed'], $limit = 100, $start_date = '' ) {
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

/**
 * Get top amount orders by product IDs
 *
 * @param array  $product_ids   Array of product IDs
 * @param array  $order_status  Array of order statuses
 * @param int    $limit         Limit for results
 * @param string $start_date    Start date filter
 * @return array Order IDs sorted by amount
 */
function skydonate_remote_get_top_amount_orders_by_product_ids(
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

/**
 * ============================================================================
 * CURRENCY CONVERSION FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Convert currency amount
 *
 * @param string $baseCurrency   Base currency code
 * @param string $targetCurrency Target currency code
 * @param float  $amount         Amount to convert
 * @return float Converted amount
 */
function skydonate_remote_convert_currency( $baseCurrency, $targetCurrency, $amount ) {
    $rates = skydonate_remote_get_saved_rates();
    $baseCurrency   = strtoupper( $baseCurrency );
    $targetCurrency = strtoupper( $targetCurrency );

    // Check if rates exist
    if ( empty( $rates[ $baseCurrency ] ) || empty( $rates[ $targetCurrency ] ) ) {
        return $amount;
    }

    // Prevent division by zero
    if ( floatval( $rates[ $baseCurrency ] ) === 0.0 ) {
        return $amount;
    }

    // Conversion via USD base
    $rate = $rates[ $targetCurrency ] / $rates[ $baseCurrency ];
    $convertedAmount = $amount * $rate;

    return round( $convertedAmount, 2 );
}

/**
 * Get exchange rate between currencies
 *
 * @param string $from From currency code
 * @param string $to   To currency code
 * @return float|null Exchange rate or null
 */
function skydonate_remote_get_rate( $from = 'GBP', $to = 'USD' ) {
    $rates = skydonate_remote_get_saved_rates();
    $from  = strtoupper( $from );
    $to    = strtoupper( $to );

    if ( empty( $rates[ $from ] ) || empty( $rates[ $to ] ) ) {
        return null;
    }

    // Prevent division by zero
    if ( floatval( $rates[ $from ] ) === 0.0 ) {
        return null;
    }

    // Convert via USD base
    $value = $rates[ $to ] / $rates[ $from ];
    return round( $value, 4 );
}

/**
 * Get saved currency rates
 *
 * @return array Currency rates
 */
function skydonate_remote_get_saved_rates() {
    $data = get_option( 'skydonate_currency_rates', [] );
    return $data['rates'] ?? [];
}

/**
 * ============================================================================
 * GIFT AID EXPORT FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Export Gift Aid orders to CSV
 *
 * @param int $paged Page number
 * @return array Export data
 */
function skydonate_remote_export_gift_aid_orders( $paged = 1 ) {
    $limit  = 1000;
    $offset = ($paged - 1) * $limit;

    // Allowed order statuses
    $allowed_statuses = array('completed', 'renewal');

    // Query eligible orders using HPOS-compatible wc_get_orders
    $args = array(
        'limit'       => $limit,
        'offset'      => $offset,
        'status'      => $allowed_statuses,
        'meta_key'    => 'gift_aid_it',
        'meta_value'  => 'yes',
        'return'      => 'ids',
        'orderby'     => 'ID',
        'order'       => 'ASC',
    );

    $order_ids = wc_get_orders( $args );

    if ( empty( $order_ids ) ) {
        return [
            'done' => true,
            'csv'  => '',
        ];
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

    return [
        'done' => false,
        'page' => $paged,
        'csv'  => $csv_chunk,
    ];
}

/**
 * Export Gift Aid orders by date range
 *
 * @param string $start_date Start date
 * @param string $end_date   End date
 * @param int    $paged      Page number
 * @return array Export data
 */
function skydonate_remote_export_gift_aid_orders_by_date( $start_date, $end_date, $paged = 1 ) {
    $limit  = 1000;
    $offset = ($paged - 1) * $limit;

    $allowed_statuses = ['completed', 'renewal'];

    // Query eligible orders using HPOS-compatible wc_get_orders
    $args = [
        'limit'        => $limit,
        'offset'       => $offset,
        'status'       => $allowed_statuses,
        'meta_key'     => 'gift_aid_it',
        'meta_value'   => 'yes',
        'date_created' => $start_date . '...' . $end_date,
        'return'       => 'ids',
        'orderby'      => 'ID',
        'order'        => 'ASC',
    ];

    $order_ids = wc_get_orders($args);

    if ( empty( $order_ids ) ) {
        return [
            'done'     => true,
            'csv'      => '',
            'filename' => 'gift_aid_orders_' . $start_date . '_to_' . $end_date . '.csv',
        ];
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

    return [
        'done'     => false,
        'page'     => $paged,
        'csv'      => $csv_chunk,
        'filename' => 'gift_aid_orders_' . $start_date . '_to_' . $end_date . '.csv',
    ];
}

/**
 * ============================================================================
 * DONATION FEE CALCULATION FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Calculate donation fee
 *
 * @param float $cart_total Cart total
 * @return float Fee amount
 */
function skydonate_remote_calculate_donation_fee( $cart_total ) {
    $percentage = (float) get_option( 'donation_fee_percentage', 1.7 );
    $fee = ( $percentage / 100 ) * $cart_total;
    return round( $fee, 2 );
}

/**
 * ============================================================================
 * HELPER FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Check if HPOS is active
 *
 * @return bool
 */
function skydonate_remote_is_hpos_active() {
    if ( ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
        return false;
    }

    return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}

/**
 * Render recent donations - Layout One
 *
 * @param array $order_ids    Order IDs
 * @param array $product_ids  Product IDs
 * @param bool  $hidden_class Whether to add hidden class
 */
function skydonate_remote_render_recent_donations_layout_one( $order_ids, $product_ids, $hidden_class = false ) {
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

/**
 * Render recent donations - Layout Two
 *
 * @param array  $order_ids   Order IDs
 * @param array  $product_ids Product IDs
 * @param string $list_icon   Icon HTML
 */
function skydonate_remote_render_recent_donations_layout_two( $order_ids, $product_ids, $list_icon = '<i class="fas fa-hand-holding-heart"></i>' ) {
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

/**
 * Get currency by country code
 *
 * @param string $country_code Country code
 * @return string Currency code
 */
function skydonate_remote_get_currency_by_country_code( $country_code ) {
    if ( empty( $country_code ) ) {
        return get_woocommerce_currency();
    }

    $country_code = strtoupper( trim( $country_code ) );

    // Country → Currency mapping
    $map = [
        'AE' => 'AED', 'AF' => 'AFN', 'AL' => 'ALL', 'AM' => 'AMD', 'AO' => 'AOA',
        'AR' => 'ARS', 'AU' => 'AUD', 'AT' => 'EUR', 'AZ' => 'AZN', 'BD' => 'BDT',
        'BE' => 'EUR', 'BG' => 'BGN', 'BH' => 'BHD', 'BN' => 'BND', 'BO' => 'BOB',
        'BR' => 'BRL', 'BT' => 'BTN', 'BY' => 'BYN', 'CA' => 'CAD', 'CH' => 'CHF',
        'CL' => 'CLP', 'CN' => 'CNY', 'CO' => 'COP', 'CR' => 'CRC', 'CZ' => 'CZK',
        'DE' => 'EUR', 'DK' => 'DKK', 'DO' => 'DOP', 'DZ' => 'DZD', 'EG' => 'EGP',
        'ES' => 'EUR', 'ET' => 'ETB', 'FI' => 'EUR', 'FR' => 'EUR', 'GB' => 'GBP',
        'GH' => 'GHS', 'GR' => 'EUR', 'HK' => 'HKD', 'HR' => 'HRK', 'HU' => 'HUF',
        'ID' => 'IDR', 'IE' => 'EUR', 'IL' => 'ILS', 'IN' => 'INR', 'IQ' => 'IQD',
        'IR' => 'IRR', 'IS' => 'ISK', 'IT' => 'EUR', 'JM' => 'JMD', 'JO' => 'JOD',
        'JP' => 'JPY', 'KE' => 'KES', 'KH' => 'KHR', 'KR' => 'KRW', 'KW' => 'KWD',
        'KZ' => 'KZT', 'LA' => 'LAK', 'LB' => 'LBP', 'LK' => 'LKR', 'LY' => 'LYD',
        'MA' => 'MAD', 'MM' => 'MMK', 'MN' => 'MNT', 'MO' => 'MOP', 'MV' => 'MVR',
        'MX' => 'MXN', 'MY' => 'MYR', 'NG' => 'NGN', 'NL' => 'EUR', 'NO' => 'NOK',
        'NP' => 'NPR', 'NZ' => 'NZD', 'OM' => 'OMR', 'PA' => 'PAB', 'PE' => 'PEN',
        'PH' => 'PHP', 'PK' => 'PKR', 'PL' => 'PLN', 'PT' => 'EUR', 'QA' => 'QAR',
        'RO' => 'RON', 'RU' => 'RUB', 'SA' => 'SAR', 'SE' => 'SEK', 'SG' => 'SGD',
        'SI' => 'EUR', 'SK' => 'EUR', 'TH' => 'THB', 'TR' => 'TRY', 'TW' => 'TWD',
        'TZ' => 'TZS', 'UA' => 'UAH', 'UG' => 'UGX', 'US' => 'USD', 'UY' => 'UYU',
        'UZ' => 'UZS', 'VN' => 'VND', 'ZA' => 'ZAR', 'ZW' => 'ZWL',
    ];

    return isset( $map[ $country_code ] ) ? $map[ $country_code ] : get_woocommerce_currency();
}

/**
 * Get user country name from geolocation
 *
 * @param string $format 'name' or 'code'
 * @return string Country name or code
 */
function skydonate_remote_get_user_country_name( $format = 'name' ) {
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

/**
 * ============================================================================
 * REMOTE FUNCTIONS LOADED MARKER
 * ============================================================================
 */

// Set flag that remote functions are loaded
if ( ! defined( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED' ) ) {
    define( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED', true );
}

// Add action to confirm remote functions are active
add_action( 'init', function() {
    do_action( 'skydonate_remote_functions_loaded' );
}, 6 );
