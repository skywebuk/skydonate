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
 * DONATION OPTIONS FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Capture cart item custom data for donations and subscriptions
 *
 * @param array $cart_item    Cart item data
 * @param int   $product_id   Product ID
 * @param int   $variation_id Variation ID
 * @return array Modified cart item
 */
function skydonate_remote_capture_cart_item_data( $cart_item, $product_id, $variation_id = 0 ) {
    // Handle variable products
    $product_id = $variation_id ? $variation_id : $product_id;
    $today   = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    // Donation option with fallbacks
    if (!empty($_POST['donation_option'])) {
        $donation_option = sanitize_text_field($_POST['donation_option']);
    } elseif (!empty($cart_item['donation_option'])) {
        $donation_option = sanitize_text_field($cart_item['donation_option']);
    } else {
        $donation_option = 'Once';
    }

    // Other fields with defaults
    $start_date          = !empty($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : $today;
    $end_date            = !empty($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
    $selected_amount     = !empty($_POST['selected_amount']) ? floatval($_POST['selected_amount']) : 0;
    $custom_option       = !empty($_POST['custom_option']) ? floatval($_POST['custom_option']) : $selected_amount;
    $donation_type       = !empty($_POST['donation_type']) ? sanitize_text_field($_POST['donation_type']) : '';
    $custom_amount_label = !empty($_POST['custom_amount_label']) ? sanitize_text_field($_POST['custom_amount_label']) : '';

    // Prevent past end date
    if (!empty($end_date) && strtotime($end_date) < strtotime($today)) {
        $end_date = $today;
    }

    // For non-daily donations, force start date to today
    if (strtolower($donation_option) !== 'daily') {
        $start_date = $today;
    }

    // Merge cart data safely
    if (empty($cart_item['start_date'])) {
        $cart_item['start_date'] = $start_date;
    }
    if (empty($cart_item['end_date'])) {
        $cart_item['end_date'] = $end_date;
    }
    if (empty($cart_item['donation_option'])) {
        $cart_item['donation_option'] = $donation_option;
    }
    if (empty($cart_item['donation_amount'])) {
        $cart_item['donation_amount'] = $selected_amount;
    }
    if (empty($cart_item['custom_option_price'])) {
        $cart_item['custom_option_price'] = $custom_option;
    }
    if ($donation_type) {
        $cart_item['donation_type'] = $donation_type;
    }
    if ($custom_amount_label) {
        $cart_item['custom_amount_label'] = $custom_amount_label;
    }

    // Period mapping
    $period_map = [
        'daily'   => 'day',
        'day'     => 'day',
        'weekly'  => 'week',
        'week'    => 'week',
        'monthly' => 'month',
        'month'   => 'month',
        'yearly'  => 'year',
        'year'    => 'year',
    ];

    $donation_option_lower = strtolower($donation_option);
    $selected_period = $period_map[$donation_option_lower] ?? 'week';
    $period_interval = 1;

    // Subscription setup for recurring donations
    if ($donation_option_lower !== 'once') {
        if ($donation_option_lower === 'daily') {
            // Trial: days until start
            $trial_length = (strtotime($start_date) - strtotime($today)) / DAY_IN_SECONDS;
            $trial_length = max(0, (int)$trial_length);

            // Subscription length: days between start and end
            $subs_length = !empty($end_date) ? (strtotime($end_date) - strtotime($start_date)) / DAY_IN_SECONDS : 0;
            $subs_length = max(0, (int)$subs_length);
        } else {
            $trial_length = 0;
            $subs_length  = 0;
        }

        $cart_item['bos4w_data'] = [
            'selected_subscription' => "1_{$selected_period}_{$trial_length}",
            'discounted_price'      => 0,
            'subscription_length'   => $subs_length,
        ];

        $cart_item['_subscription_period']          = $selected_period;
        $cart_item['_subscription_period_interval'] = $period_interval;
        $cart_item['_subscription_length']          = $subs_length;
        $cart_item['_subscription_trial_length']    = $trial_length;
        $cart_item['_subscription_trial_period']    = $selected_period;
    }

    return $cart_item;
}

/**
 * Apply subscription scheme to cart item
 *
 * @param array $cart_item Cart item data
 * @return array Modified cart item
 */
function skydonate_remote_apply_subscription( $cart_item ) {
    $scheme = skydonate_remote_get_subscription_scheme($cart_item);
    $use_regular_price = apply_filters('bos_use_regular_price', false);

    if ($scheme) {
        skydonate_remote_set_subscription_scheme($cart_item, $scheme);

        $product = $cart_item['data'];

        // Safely get selected_subscription
        $selected_subscription = $scheme['selected_subscription'] ?? '';
        if (!empty($selected_subscription)) {
            $plan_data = explode('_', $selected_subscription);
            $discount = end($plan_data);

            // Calculate price
            $price = !$use_regular_price ? $product->get_price() : $product->get_regular_price();
            $discounted_price = $discount > 0 ? $price - ($price * ($discount / 100)) : $price;

            $product->set_price(round($discounted_price, wc_get_price_decimals()));
        }
    }

    return apply_filters('bos4w_cart_item_data', $cart_item);
}

/**
 * Get subscription scheme from cart item
 *
 * @param array $cart_item Cart item data
 * @return array Subscription scheme
 */
function skydonate_remote_get_subscription_scheme( $cart_item ) {
    return isset($cart_item['bos4w_data']) ? $cart_item['bos4w_data'] : [];
}

/**
 * Set subscription meta for product/cart item
 *
 * @param array $cart_item Cart item data
 * @param array $scheme    Subscription scheme
 * @return bool Success status
 */
function skydonate_remote_set_subscription_scheme( $cart_item, $scheme ) {
    // Make sure selected_subscription exists
    if (empty($scheme['selected_subscription'])) {
        return false;
    }

    $plan_data = explode('_', $scheme['selected_subscription']);

    // Safely assign values
    $interval     = isset($plan_data[0]) ? $plan_data[0] : 1;
    $period       = isset($plan_data[1]) ? $plan_data[1] : 'day';
    $trial_length = isset($plan_data[2]) ? (int)$plan_data[2] : 0;

    // Save subscription meta
    $cart_item['data']->update_meta_data('_subscription_period', $period);
    $cart_item['data']->update_meta_data('_subscription_period_interval', $interval);
    $cart_item['data']->update_meta_data('_subscription_plan_data', $plan_data);

    if (!empty($scheme['subscription_length'])) {
        $cart_item['data']->update_meta_data('_subscription_length', $scheme['subscription_length']);
    }

    // Add trial meta
    if (!empty($trial_length)) {
        $cart_item['data']->update_meta_data('_subscription_trial_length', $trial_length);
        $cart_item['data']->update_meta_data('_subscription_trial_period', 'day');
    }

    // Optional: discounted price
    if (isset($scheme['discounted_price'])) {
        $cart_item['data']->update_meta_data('_subscription_price', $scheme['discounted_price']);
    }

    return true;
}

/**
 * Apply subscriptions to all applicable cart items
 *
 * @param WC_Cart $cart Cart object
 */
function skydonate_remote_apply_subscriptions( $cart ) {
    foreach ($cart->cart_contents as $key => $item) {
        if (isset($item['bos4w_data']) && !empty($item['bos4w_data'])) {
            $cart->cart_contents[$key] = skydonate_remote_apply_subscription($item);
        }
    }
}

/**
 * Save order item custom data for donations
 *
 * @param WC_Order_Item $item          Order item
 * @param string        $cart_item_key Cart item key
 * @param array         $values        Cart item values
 * @param WC_Order      $order         Order object
 */
function skydonate_remote_save_order_item_data( $item, $cart_item_key, $values, $order ) {
    if (!empty($values['donation_option']) && $values['donation_option'] === 'Daily') {
        // Save daily donation meta
        $item->add_meta_data('_start_date', $values['start_date'], true);
        $item->add_meta_data('_end_date', $values['end_date'], true);
        $item->add_meta_data('_billing_period', 'day', true);
        $item->add_meta_data('_billing_interval', 1, true);
        $item->add_meta_data('_daily_price', $values['daily_price'] ?? $values['custom_option_price'] ?? 0, true);

        // Calculate and save total days
        $start = new DateTime($values['start_date']);
        $end = new DateTime($values['end_date']);
        $days = $start->diff($end)->days;
        $item->add_meta_data('Total Days', $days, true);
        $item->add_meta_data('Donation Schedule', sprintf('%s to %s', $start->format('M j, Y'), $end->format('M j, Y')), true);
    }

    if (isset($values['custom_option_price'])) {
        $item->add_meta_data('Amount', $values['custom_option_price'], true);
    }

    if (!empty($values['donation_type'])) {
        $item->add_meta_data('Donation Type', $values['donation_type'], true);
    }

    if (!empty($values['custom_amount_label'])) {
        $item->add_meta_data('Amount Label', $values['custom_amount_label'], true);
    }
}

/**
 * Custom subscription price string for daily donations
 *
 * @param string     $subscription_string Subscription string
 * @param WC_Product $product            Product object
 * @param array      $include            Include options
 * @return string Modified subscription string
 */
function skydonate_remote_subscription_price_string( $subscription_string, $product, $include ) {
    if ( ! function_exists( 'wcs_get_price_including_tax' ) || ! is_object( $product ) ) {
        return $subscription_string;
    }

    // Get subscription details
    $base_price          = floatval( $product->get_price() );
    $price_html          = wc_price( $base_price );
    $billing_interval    = WC_Subscriptions_Product::get_interval( $product );
    $billing_period      = WC_Subscriptions_Product::get_period( $product );
    $subscription_length = WC_Subscriptions_Product::get_length( $product );

    // Trial = future donation period
    $trial_length = WC_Subscriptions_Product::get_trial_length( $product );
    $trial_period = WC_Subscriptions_Product::get_trial_period( $product );

    // Only change daily subscriptions
    if ( $billing_period !== 'day' ) {
        return $subscription_string;
    }

    // Calculate total
    $total_amount      = $subscription_length > 0 ? $base_price * $subscription_length : 0;
    $total_amount_html = wc_price( $total_amount );

    // Format billing period label
    $period_label = $billing_interval > 1 ? sprintf( _n( '%d day', '%d days', $billing_interval, 'skydonate' ), $billing_interval ) : __( 'Day', 'skydonate' );

    $tip_text = '';

    // Build tooltip text
    if ( $subscription_length <= 0 ) {
        if ( $trial_length > 0 ) {
            $day_label = _n( 'day', 'days', $trial_length, 'skydonate' );
            $tip_text .= sprintf( __( '%d %s future donation period.', 'skydonate' ), $trial_length, $day_label );
        }
    } else {
        $length_label = _n( 'day', 'days', $subscription_length, 'skydonate' );
        $tip_text .= sprintf( __( "You'll donate %s daily, totaling %s over %d %s.", 'skydonate' ), $price_html, $total_amount_html, $subscription_length, $length_label );

        if ( $trial_length > 0 ) {
            $trial_label = _n( 'day', 'days', $trial_length, 'skydonate' );
            $tip_text .= sprintf( __( " With a %d-%s future donation period.", 'skydonate' ), $trial_length, $trial_label );
        }
    }

    // Build new price string
    $new_string  = $price_html . " / {$period_label}";
    if (empty($tip_text)) {
        return $new_string;
    }
    $new_string .= '<span class="tiptip">';
    $new_string .= '<i class="far fa-circle-info"></i>';
    $new_string .= '<span class="tip-text">';
    $new_string .= wp_kses_post( $tip_text );
    $new_string .= '</span>';
    $new_string .= '</span>';

    return $new_string;
}

/**
 * ============================================================================
 * SHORTCODE RENDER FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Render donation form - Layout One
 *
 * @param int   $id   Product ID
 * @param array $atts Shortcode attributes
 */
function skydonate_remote_render_layout_one( $id, $atts ) {
    $btn_label = false;
    $today     = date('Y-m-d');
    $tomorrow  = date('Y-m-d', strtotime('+1 day'));
    $one_year_later = date('Y-m-d', strtotime('+1 year'));
    $heart_switcher = get_option('donation_monthly_heart_icon');
    $start_date = get_post_meta($id, '_start_date', true);
    $enable_start_date = get_post_meta($id, '_enable_start_date', true);
    $end_date = get_post_meta($id, '_end_date', true);
    $enable_end_date = get_post_meta($id, '_enable_end_date', true);
    if ($start_date && $today <= $start_date) {
        $start_date = $today;
    }
    if ($end_date && $end_date <= $today) {
        $end_date = '';
    }
    if(!empty($start_date)){
        $start_date = strtotime($start_date);
        $start_date = date('d-m-Y', $start_date);
    }
    if(!empty($end_date)){
        $end_date = strtotime($end_date);
        $end_date = date('d-m-Y', $end_date);
    }

    $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
    $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
    $card_layout             = get_post_meta($id, '_skydonate_selected_layout', true) ?: 'layout_one';
    $card_layout             = ($card_layout == 'layout_one') ? 'grid-layout' : 'list-layout';
    $custom_options     = get_post_meta($id, '_custom_options', true);
    $custom_options     = is_array($custom_options) ? $custom_options : [];
    $default_option     = get_post_meta($id, '_default_option', true);
    $box_title          = get_post_meta($id, '_box_title', true);
    $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

    $deafult_amount = '';

    echo '<form class="donation-form ' . esc_attr($card_layout) . '" data-product="' . esc_attr($id) . '">';

    // ----- Donation Frequency Buttons -----
    $frequencies = [
        'once'    => __('Once', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'monthly' => __('Monthly', 'skydonate'),
        'yearly'  => __('Yearly', 'skydonate'),
    ];



    if (count($button_visibility) >= 2) {
        echo '<div class="donation-type-switch buttons-' . esc_attr(min(count($button_visibility), 3)) . '">';
        foreach ($frequencies as $key => $label) {
            $meta_key = "show_{$key}";
            if (!in_array($meta_key, $button_visibility, true)) continue;
            $active_class = ($key === $donation_frequency) ? ' active' : '';
            $heart = ($key === 'monthly' && $heart_switcher == 1) ? '<span class="heart-icon"><i class="fas fa-heart"></i></span>' : '';
            echo '<button type="button" class="donation-type-btn' . esc_attr($active_class) . ' ' . esc_attr($key) . '" data-type="' . esc_attr($key) . '">' . esc_html($label) . $heart . '</button>';
        }
        echo '</div>';
    } else {
        echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
    }

    // ----- Box Title -----
    if (!empty($box_title)) {
        $arrow_icon = '';
        if ($box_arrow_hide !== 'yes') {
            $arrow_icon = '<svg class="arrow-up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21.048 12.8102L19.478 6.57021C19.4283 6.37774 19.3053 6.21238 19.1352 6.10957C18.9651 6.00676 18.7614 5.97468 18.568 6.02021L12.328 7.60021C12.1357 7.6559 11.9734 7.7857 11.8768 7.96105C11.7802 8.13639 11.7573 8.34293 11.813 8.53521C11.8687 8.72749 11.9985 8.88977 12.1738 8.98635C12.3492 9.08293 12.5557 9.1059 12.748 9.05021L17.168 7.94021C15.748 10.5902 11.518 17.1302 3.57797 19.0202C3.39912 19.0624 3.24198 19.1687 3.13634 19.319C3.0307 19.4694 2.98393 19.6533 3.0049 19.8358C3.02587 20.0183 3.11312 20.1868 3.25009 20.3093C3.38706 20.4318 3.56423 20.4997 3.74797 20.5002H3.91797C12.538 18.4502 16.918 11.5502 18.458 8.66021L19.598 13.1902C19.6398 13.3524 19.7341 13.4962 19.8661 13.5993C19.9981 13.7023 20.1605 13.7589 20.328 13.7602H20.508C20.6082 13.7374 20.7026 13.694 20.7852 13.6327C20.8677 13.5715 20.9366 13.4936 20.9874 13.4043C21.0382 13.3149 21.0698 13.2159 21.0803 13.1136C21.0907 13.0113 21.0797 12.908 21.048 12.8102Z" fill="currentColor"/></svg>';
        }
        echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
    }


    // ----- Donation Amounts -----
    echo '<div class="donation-amount-groups">';
    foreach ($frequencies as $key => $label) {
        if($card_layout == 'list-layout'){
            $btn_label = true;
        }else{
            $btn_label = false;
        }
        $meta_key = "show_{$key}";
        if (!in_array($meta_key, $button_visibility, true)) continue;

        $group_active = ($key === $donation_frequency) ? ' active' : '';
        echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
        echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 3)) . '">';
        $i = 1;

        foreach ($custom_options as $option) {
            $amount_key = ($key === 'once') ? 'price' : $key;
            $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
            if(!empty($_COOKIE['skydonate_selected_currency']) && $_COOKIE['skydonate_selected_currency'] !== get_option('woocommerce_currency')){
                $new_amount = Skydonate_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skydonate_selected_currency'], $amount);
            }else {
                $new_amount = $amount;
            }
            $is_active = ($default_option == $i) ? ' active' : '';
            if (($key === $donation_frequency) && ($default_option == $i)) {
                $deafult_amount = $new_amount;
            }

            echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
            echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
            if ($btn_label) {
                echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
            }elseif($key === 'daily'){
                echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
            }
            echo '</button>';
            $i++;
        }
        echo '</div></div>';
    }

    // ----- Custom Input -----
    echo '<div class="donation-custom">';
    if (class_exists('Skydonate_Currency_Changer')) {
        echo Skydonate_Currency_Changer::currency_changer();
    }
    echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($deafult_amount) . '" placeholder="0.00">';
    if(!empty($atts['placeholder'])){
        echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
    }
    echo '</div>';

    // ----- Date Pickers for Daily -----
    if (in_array('show_daily', $button_visibility) && ($enable_start_date == 1 || $enable_end_date == 1)) {
        echo '<div class="donation-daily-dates-group">';
        echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
        echo '<div class="donation-dates">';
        if ($enable_start_date == 1) {
            echo '<div class="donation-date-field"><label>' . esc_html__('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        if($enable_end_date == 1){
            echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        echo '</div></div>';
    }

    // ----- Name on Plaque (Text Box) -----
    skydonate_remote_render_name_on_plaque($id);

    echo '</div>'; // .donation-amount-groups


    Skydonate_Functions::skydonate_submit_button($atts);

    echo '</form>';
}

/**
 * Render donation form - Layout Two
 *
 * @param int   $id   Product ID
 * @param array $atts Shortcode attributes
 */
function skydonate_remote_render_layout_two( $id, $atts ) {
    $btn_label = false;
    $today     = date('Y-m-d');
    $tomorrow  = date('Y-m-d', strtotime('+1 day'));
    $one_year_later = date('Y-m-d', strtotime('+1 year'));
    $heart_switcher = get_option('donation_monthly_heart_icon');
    $start_date = get_post_meta($id, '_start_date', true);
    $enable_start_date = get_post_meta($id, '_enable_start_date', true);
    $end_date = get_post_meta($id, '_end_date', true);
    $enable_end_date = get_post_meta($id, '_enable_end_date', true);
    if ($start_date && $today <= $start_date) {
        $start_date = $today;
    }
    if ($end_date && $end_date <= $today) {
        $end_date = '';
    }
    if(!empty($start_date)){
        $start_date = strtotime($start_date);
        $start_date = date('d-m-Y', $start_date);
    }
    if(!empty($end_date)){
        $end_date = strtotime($end_date);
        $end_date = date('d-m-Y', $end_date);
    }

    $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
    $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
    $card_layout             = get_post_meta($id, '_skydonate_selected_layout', true) ?: 'layout_one';
    $card_layout             = ($card_layout == 'layout_one') ? 'grid-layout' : 'list-layout';
    $custom_options     = get_post_meta($id, '_custom_options', true);
    $custom_options     = is_array($custom_options) ? $custom_options : [];
    $default_option     = get_post_meta($id, '_default_option', true);
    $box_title          = get_post_meta($id, '_box_title', true);
    $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

    $deafult_amount = '';

    echo '<form class="donation-form ' . esc_attr($card_layout) . '" data-product="' . esc_attr($id) . '">';

    // ----- Donation Frequency Buttons -----
    $frequencies = [
        'once'    => __('Once', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'monthly' => __('Monthly', 'skydonate'),
        'yearly'  => __('Yearly', 'skydonate'),
    ];



    // Box title
    if (!empty($box_title)) {
        $arrow_icon = '';
        if($box_arrow_hide !== 'yes'){
            $arrow_icon = '<span class="arrow-icon"></span>';
        }
        echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
    }


    if (count($button_visibility) >= 2) {
        echo '<div class="donation-type-switch buttons-' . esc_attr(min(count($button_visibility), 3)) . '">';
        foreach ($frequencies as $key => $label) {
            $meta_key = "show_{$key}";
            if (!in_array($meta_key, $button_visibility, true)) continue;
            $active_class = ($key === $donation_frequency) ? ' active' : '';
            $heart = ($key === 'monthly' && $heart_switcher == 1) ? '<span class="heart-icon"><i class="fas fa-heart"></i></span>' : '';
            echo '<button type="button" class="donation-type-btn' . esc_attr($active_class) . ' ' . esc_attr($key) . '" data-type="' . esc_attr($key) . '">' . esc_html($label) . $heart . '</button>';
        }
        echo '</div>';
    } else {
        echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
    }

    // ----- Donation Amounts -----
    echo '<div class="donation-amount-groups">';
    foreach ($frequencies as $key => $label) {
        if($card_layout == 'list-layout'){
            $btn_label = true;
        }else{
            $btn_label = false;
        }
        $meta_key = "show_{$key}";
        if (!in_array($meta_key, $button_visibility, true)) continue;

        $group_active = ($key === $donation_frequency) ? ' active' : '';
        echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
        echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 3)) . '">';
        $i = 1;
        foreach ($custom_options as $option) {
            $amount_key = ($key === 'once') ? 'price' : $key;
            $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
            if(!empty($_COOKIE['skydonate_selected_currency']) && $_COOKIE['skydonate_selected_currency'] !== get_option('woocommerce_currency')){
                $new_amount = Skydonate_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skydonate_selected_currency'], $amount);
            }else {
                $new_amount = $amount;
            }
            $is_active = ($default_option == $i) ? ' active' : '';
            if (($key === $donation_frequency) && ($default_option == $i)) {
                $deafult_amount = $new_amount;
            }
            echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
            echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
            if ($btn_label) {
                echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
            }elseif($key === 'daily'){
                echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
            }
            echo '</button>';
            $i++;
        }

        echo '</div></div>';
    }

    // ----- Custom Input -----
    echo '<div class="donation-custom">';
    if (class_exists('Skydonate_Currency_Changer')) {
        echo Skydonate_Currency_Changer::currency_changer();
    }
    echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($deafult_amount) . '" placeholder="0.00">';
    if(!empty($atts['placeholder'])){
        echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
    }
    echo '</div>';

    // ----- Date Pickers for Daily -----
    if (in_array('show_daily', $button_visibility) && ($enable_start_date == 1 || $enable_end_date == 1)) {
        echo '<div class="donation-daily-dates-group">';
        echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
        echo '<div class="donation-dates">';
        if ($enable_start_date == 1) {
            echo '<div class="donation-date-field"><label>' . esc_html__('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        if($enable_end_date == 1){
            echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        echo '</div></div>';
    }

    // ----- Name on Plaque (Text Box) -----
    skydonate_remote_render_name_on_plaque($id);

    echo '</div>'; // .donation-amount-groups

    Skydonate_Functions::skydonate_submit_button($atts);

    echo '</form>';
}

/**
 * Render donation form - Layout Three
 *
 * @param int   $id   Product ID
 * @param array $atts Shortcode attributes
 */
function skydonate_remote_render_layout_three( $id, $atts ) {
    $btn_label = false;
    $today     = date('Y-m-d');
    $tomorrow  = date('Y-m-d', strtotime('+1 day'));
    $one_year_later = date('Y-m-d', strtotime('+1 year'));
    $heart_switcher = get_option('donation_monthly_heart_icon');
    $start_date = get_post_meta($id, '_start_date', true);
    $enable_start_date = get_post_meta($id, '_enable_start_date', true);
    $end_date = get_post_meta($id, '_end_date', true);
    $enable_end_date = get_post_meta($id, '_enable_end_date', true);
    if ($start_date && $today <= $start_date) {
        $start_date = $today;
    }
    if ($end_date && $end_date <= $today) {
        $end_date = '';
    }
    if(!empty($start_date)){
        $start_date = strtotime($start_date);
        $start_date = date('d-m-Y', $start_date);
    }
    if(!empty($end_date)){
        $end_date = strtotime($end_date);
        $end_date = date('d-m-Y', $end_date);
    }

    $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
    $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
    $card_layout             = get_post_meta($id, '_skydonate_selected_layout', true) ?: 'layout_one';
    $card_layout             = ($card_layout == 'layout_one') ? 'grid-layout' : 'list-layout';
    $custom_options     = get_post_meta($id, '_custom_options', true) ?: [];
    $default_option     = get_post_meta($id, '_default_option', true);
    $box_title          = get_post_meta($id, '_box_title', true);
    $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

    $default_amount = '';

    $frequencies = [
        'once'    => __('One-off', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'monthly' => __('Monthly', 'skydonate'),
        'yearly'  => __('Yearly', 'skydonate'),
    ];

    echo '<form class="donation-form ' . esc_attr($card_layout) . '" data-product="' . esc_attr($id) . '">';

    // ----- Donation Type Toggle -----
    if (count($button_visibility) >= 2) {
        echo '<div class="donation-type-switch">';
        echo '<button type="button" class="one-off ' . ($donation_frequency === 'once' ? ' active' : '') . '">' . __('One-off', 'skydonate') . '</button>';
        echo '<button type="button" class="recurring ' . ($donation_frequency !== 'once' ? ' active' : '') . '">' . __('Recurring', 'skydonate') . '</button>';
        echo '</div>';
    } else {
        echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
    }

    // ----- Recurring Frequency Selector -----
    echo '<div class="period-select-options" style="' . ($donation_frequency === 'once' ? 'display:none;' : 'display:block;') . '">';
    echo '<label>' . __('Select recurring frequency', 'skydonate') . ' *</label>';
    echo '<select class="select-option" name="recurring_frequency">';
    foreach ($frequencies as $key => $label) {
        if ($key === 'once') continue; // skip One-off in dropdown
        if (!in_array("show_{$key}", $button_visibility, true)) continue;
        $selected = ($key === $donation_frequency) ? ' selected' : '';
        echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($label) . '</option>';
    }
    echo '</select></div>';

    // ----- Box Title -----
    if (!empty($box_title)) {
        $arrow_icon = '';
        if ($box_arrow_hide !== 'yes') {
            $arrow_icon = '<svg class="arrow-up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21.048 12.8102L19.478 6.57021C19.4283 6.37774 19.3053 6.21238 19.1352 6.10957C18.9651 6.00676 18.7614 5.97468 18.568 6.02021L12.328 7.60021C12.1357 7.6559 11.9734 7.7857 11.8768 7.96105C11.7802 8.13639 11.7573 8.34293 11.813 8.53521C11.8687 8.72749 11.9985 8.88977 12.1738 8.98635C12.3492 9.08293 12.5557 9.1059 12.748 9.05021L17.168 7.94021C15.748 10.5902 11.518 17.1302 3.57797 19.0202C3.39912 19.0624 3.24198 19.1687 3.13634 19.319C3.0307 19.4694 2.98393 19.6533 3.0049 19.8358C3.02587 20.0183 3.11312 20.1868 3.25009 20.3093C3.38706 20.4318 3.56423 20.4997 3.74797 20.5002H3.91797C12.538 18.4502 16.918 11.5502 18.458 8.66021L19.598 13.1902C19.6398 13.3524 19.7341 13.4962 19.8661 13.5993C19.9981 13.7023 20.1605 13.7589 20.328 13.7602H20.508C20.6082 13.7374 20.7026 13.694 20.7852 13.6327C20.8677 13.5715 20.9366 13.4936 20.9874 13.4043C21.0382 13.3149 21.0698 13.2159 21.0803 13.1136C21.0907 13.0113 21.0797 12.908 21.048 12.8102Z" fill="currentColor"/></svg>';
        }
        echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
    }

    // ----- Donation Amount Buttons -----
    echo '<div class="donation-amount-groups">';
    foreach ($frequencies as $key => $label) {
        if($card_layout == 'list-layout'){
            $btn_label = true;
        }else{
            $btn_label = false;
        }
        $meta_key = "show_{$key}";
        if (!in_array($meta_key, $button_visibility, true)) continue;

        $group_active = ($key === $donation_frequency) ? ' active' : '';
        echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
        echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 4)) . '">';
        $i = 1;

        foreach ($custom_options as $option) {
            $amount_key = ($key === 'once') ? 'price' : $key;
            $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
            if(!empty($_COOKIE['skydonate_selected_currency']) && $_COOKIE['skydonate_selected_currency'] !== get_option('woocommerce_currency')){
                $new_amount = Skydonate_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skydonate_selected_currency'], $amount);
            }else {
                $new_amount = $amount;
            }
            $is_active = ($default_option == $i) ? ' active' : '';
            if (($key === $donation_frequency) && ($default_option == $i)) {
                $default_amount = $new_amount;
            }
            echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
            echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
            if ($btn_label) {
                echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
            } elseif ($key === 'daily') {
                echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
            }
            echo '</button>';
            $i++;
        }

        echo '</div></div>';
    }

    // ----- Custom Amount Input -----
    echo '<div class="donation-custom">';
    if (class_exists('Skydonate_Currency_Changer')) {
        echo Skydonate_Currency_Changer::currency_changer();
    }
    echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($default_amount) . '" placeholder="' . (!empty($atts['placeholder']) ? esc_attr($atts['placeholder']) : esc_attr(__('0.00','skydonate'))) . '">';
    if(!empty($atts['placeholder'])){
        echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
    }
    echo '</div>';

    // ----- Daily Date Picker -----
    if (in_array('show_daily', $button_visibility) && ($enable_start_date == 1 || $enable_end_date == 1)) {
        echo '<div class="donation-daily-dates-group" style="' . ($donation_frequency === 'daily' ? 'display:block;' : 'display:none;') . '">';
        echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
        echo '<div class="donation-dates">';
        if ($enable_start_date == 1) {
            echo '<div class="donation-date-field"><label>' . __('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        if($enable_end_date == 1){
            echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
        }
        echo '</div></div>';
    }

    // ----- Name on Plaque (Text Box) -----
    skydonate_remote_render_name_on_plaque($id);

    echo '</div>'; // .donation-amount-groups

    Skydonate_Functions::skydonate_submit_button($atts);

    echo '</form>';
}

/**
 * Render name on plaque field
 *
 * @param int $id Product ID
 */
function skydonate_remote_render_name_on_plaque( $id ) {
    $field_visibility_enabled  = get_post_meta($id, '_field_visibility_enabled', true);
    $field_visibility_value    = (float) get_post_meta($id, '_field_visibility_value', true);
    $field_label               = get_post_meta($id, '_field_label', true);
    $field_placeholder         = get_post_meta($id, '_field_placeholder', true);
    $field_label_visibility    = get_post_meta($id, '_field_label_visibility', true);

    if ($field_visibility_enabled === 'yes') {
        echo '<div class="name-on-plaque" data-visible="' . esc_attr($field_visibility_value) . '">';
        if ($field_label_visibility === 'yes' && !empty($field_label)) {
            echo '<label for="custom_text">' . esc_html($field_label) . '</label>';
        }
        echo '<input type="text" name="cart_custom_text" class="short" placeholder="' . esc_attr($field_placeholder) . '">';
        echo '</div>';
    }
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
