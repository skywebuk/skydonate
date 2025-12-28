<?php

/**
 * SkyDonate Remote Core Functions
 *
 * THIS FILE IS LOADED REMOTELY FROM THE LICENSE SERVER
 * It contains the protected core business logic functions
 *
 * IMPORTANT: This file should be hosted on your license server,
 * NOT included in the plugin distribution.
 *
 * @package SkyDonate
 * @version 2.0.13
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prevent double declaration - check constant first
if ( defined( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED' ) && SKYDONATE_REMOTE_FUNCTIONS_LOADED === true ) {
    return;
}

// Define constant immediately to prevent re-entry
define( 'SKYDONATE_REMOTE_FUNCTIONS_LOADED', true );

/**
 * ============================================================================
 * DONATION CALCULATION FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Get total donation sales for a product
 * Core revenue calculation function - calculates from order items table
 *
 * @param int $product_id The product ID
 * @return float Total sales amount
 */
if ( ! function_exists( 'skydonate_remote_get_total_donation_sales' ) ) :
function skydonate_remote_get_total_donation_sales( $product_id ) {
    global $wpdb;

    // Check if HPOS is enabled
    if ( skydonate_remote_is_hpos_active() ) {
        // HPOS-compatible query using wc_orders table
        $total_amount = $wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(CAST(oim_total.meta_value AS DECIMAL(10,2))), 0)
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om
                ON oi.order_item_id = om.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_total
                ON oi.order_item_id = oim_total.order_item_id AND oim_total.meta_key = '_line_total'
            INNER JOIN {$wpdb->prefix}wc_orders AS o
                ON oi.order_id = o.id
            WHERE om.meta_key = '_product_id'
                AND om.meta_value = %d
                AND o.status = 'wc-completed'
        ", $product_id));
    } else {
        // Legacy query using posts table
        $total_amount = $wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(CAST(oim_total.meta_value AS DECIMAL(10,2))), 0)
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om
                ON oi.order_item_id = om.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS oim_total
                ON oi.order_item_id = oim_total.order_item_id AND oim_total.meta_key = '_line_total'
            INNER JOIN {$wpdb->prefix}posts AS p
                ON oi.order_id = p.ID
            WHERE om.meta_key = '_product_id'
                AND om.meta_value = %d
                AND p.post_status = 'wc-completed'
        ", $product_id));
    }

    // Ensure the value is not null
    $total_amount = $total_amount ? floatval( $total_amount ) : 0;

    // Cache the total amount
    update_post_meta($product_id, '_total_sales_amount', $total_amount);

    return $total_amount;
}
endif;

/**
 * Get donation order count for a product
 *
 * @param int $product_id The product ID
 * @return int Order count
 */
if ( ! function_exists( 'skydonate_remote_get_donation_order_count' ) ) :
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
endif;

/**
 * Get order IDs by product ID
 *
 * @param array  $product_ids   Array of product IDs
 * @param array  $order_status  Array of order statuses
 * @param int    $limit         Limit for results
 * @param string $start_date    Start date filter
 * @return array Order IDs
 */
if ( ! function_exists( 'skydonate_remote_get_orders_ids_by_product_id' ) ) :
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
endif;

/**
 * Get top amount orders by product IDs
 *
 * @param array  $product_ids   Array of product IDs
 * @param array  $order_status  Array of order statuses
 * @param int    $limit         Limit for results
 * @param string $start_date    Start date filter
 * @return array Order IDs sorted by amount
 */
if ( ! function_exists( 'skydonate_remote_get_top_amount_orders_by_product_ids' ) ) :
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
endif;

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
if ( ! function_exists( 'skydonate_remote_convert_currency' ) ) :
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
endif;

/**
 * Get exchange rate between currencies
 *
 * @param string $from From currency code
 * @param string $to   To currency code
 * @return float|null Exchange rate or null
 */
if ( ! function_exists( 'skydonate_remote_get_rate' ) ) :
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
endif;

/**
 * Get saved currency rates
 *
 * @return array Currency rates
 */
if ( ! function_exists( 'skydonate_remote_get_saved_rates' ) ) :
function skydonate_remote_get_saved_rates() {
    $data = get_option( 'skydonate_currency_rates', [] );
    return $data['rates'] ?? [];
}
endif;

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
if ( ! function_exists( 'skydonate_remote_export_gift_aid_orders' ) ) :
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
endif;

/**
 * Export Gift Aid orders by date range
 *
 * @param string $start_date Start date
 * @param string $end_date   End date
 * @param int    $paged      Page number
 * @return array Export data
 */
if ( ! function_exists( 'skydonate_remote_export_gift_aid_orders_by_date' ) ) :
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
endif;

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
if ( ! function_exists( 'skydonate_remote_calculate_donation_fee' ) ) :
function skydonate_remote_calculate_donation_fee( $cart_total ) {
    $percentage = (float) get_option( 'donation_fee_percentage', 1.7 );
    $fee = ( $percentage / 100 ) * $cart_total;
    return round( $fee, 2 );
}
endif;

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
if ( ! function_exists( 'skydonate_remote_is_hpos_active' ) ) :
function skydonate_remote_is_hpos_active() {
    if ( ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
        return false;
    }

    return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
}
endif;

/**
 * Render recent donations - Layout One
 *
 * @param array $order_ids    Order IDs
 * @param array $product_ids  Product IDs
 * @param bool  $hidden_class Whether to add hidden class
 */
if ( ! function_exists( 'skydonate_remote_render_recent_donations_layout_one' ) ) :
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
endif;

/**
 * Render recent donations - Layout Two
 *
 * @param array  $order_ids   Order IDs
 * @param array  $product_ids Product IDs
 * @param string $list_icon   Icon HTML
 */
if ( ! function_exists( 'skydonate_remote_render_recent_donations_layout_two' ) ) :
function skydonate_remote_render_recent_donations_layout_two( $order_ids, $product_ids, $list_icon = '<i class="fas fa-hand-holding-heart"></i>', $hidden_class = false ) {
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

        $item_hidden_class = ($counter <= 3 && $order_count >= 5 && $hidden_class)
            ? ' hidden-order'
            : '';

        echo '<li class="sky-order' . $item_hidden_class . '">';
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
endif;

/**
 * Get currency by country code
 *
 * @param string $country_code Country code
 * @return string Currency code
 */
if ( ! function_exists( 'skydonate_remote_get_currency_by_country_code' ) ) :
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
endif;

/**
 * Get user country name from geolocation
 *
 * @param string $format 'name' or 'code'
 * @return string Country name or code
 */
if ( ! function_exists( 'skydonate_remote_get_user_country_name' ) ) :
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
endif;

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
if ( ! function_exists( 'skydonate_remote_capture_cart_item_data' ) ) :
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
endif;

/**
 * Apply subscription scheme to cart item
 *
 * @param array $cart_item Cart item data
 * @return array Modified cart item
 */
if ( ! function_exists( 'skydonate_remote_apply_subscription' ) ) :
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
endif;

/**
 * Get subscription scheme from cart item
 *
 * @param array $cart_item Cart item data
 * @return array Subscription scheme
 */
if ( ! function_exists( 'skydonate_remote_get_subscription_scheme' ) ) :
function skydonate_remote_get_subscription_scheme( $cart_item ) {
    return isset($cart_item['bos4w_data']) ? $cart_item['bos4w_data'] : [];
}
endif;

/**
 * Set subscription meta for product/cart item
 *
 * @param array $cart_item Cart item data
 * @param array $scheme    Subscription scheme
 * @return bool Success status
 */
if ( ! function_exists( 'skydonate_remote_set_subscription_scheme' ) ) :
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
endif;

/**
 * Apply subscriptions to all applicable cart items
 *
 * @param WC_Cart $cart Cart object
 */
if ( ! function_exists( 'skydonate_remote_apply_subscriptions' ) ) :
function skydonate_remote_apply_subscriptions( $cart ) {
    foreach ($cart->cart_contents as $key => $item) {
        if (isset($item['bos4w_data']) && !empty($item['bos4w_data'])) {
            $cart->cart_contents[$key] = skydonate_remote_apply_subscription($item);
        }
    }
}
endif;

/**
 * Save order item custom data for donations
 *
 * @param WC_Order_Item $item          Order item
 * @param string        $cart_item_key Cart item key
 * @param array         $values        Cart item values
 * @param WC_Order      $order         Order object
 */
if ( ! function_exists( 'skydonate_remote_save_order_item_data' ) ) :
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
endif;

/**
 * Custom subscription price string for daily donations
 *
 * @param string     $subscription_string Subscription string
 * @param WC_Product $product            Product object
 * @param array      $include            Include options
 * @return string Modified subscription string
 */
if ( ! function_exists( 'skydonate_remote_subscription_price_string' ) ) :
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
endif;

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
if ( ! function_exists( 'skydonate_remote_render_layout_one' ) ) :
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
        'monthly' => __('Monthly', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
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
endif;

/**
 * Render donation form - Layout Two
 *
 * @param int   $id   Product ID
 * @param array $atts Shortcode attributes
 */
if ( ! function_exists( 'skydonate_remote_render_layout_two' ) ) :
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
        'monthly' => __('Monthly', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
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
endif;

/**
 * Render donation form - Layout Three
 *
 * @param int   $id   Product ID
 * @param array $atts Shortcode attributes
 */
if ( ! function_exists( 'skydonate_remote_render_layout_three' ) ) :
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
        'monthly' => __('Monthly', 'skydonate'),
        'weekly'  => __('Weekly', 'skydonate'),
        'daily'   => __('Daily', 'skydonate'),
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
        echo '<div class="donation-daily-dates-group">';
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
endif;

/**
 * Render name on plaque field
 *
 * @param int $id Product ID
 */
if ( ! function_exists( 'skydonate_remote_render_name_on_plaque' ) ) :
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
endif;

/**
 * ============================================================================
 * WOOCOMMERCE PRODUCT DATA TABS FUNCTIONS (PROTECTED)
 * ============================================================================
 */

/**
 * Register custom product data tabs for donations
 *
 * @param array $tabs Existing tabs
 * @return array Modified tabs
 */
if ( ! function_exists( 'skydonate_remote_register_product_data_tabs' ) ) :
function skydonate_remote_register_product_data_tabs( $tabs ) {
    $tabs['skydonate_donation_fields'] = [
        'label' => __('Donation Fields', 'skydonate'),
        'target' => 'skydonate_options_data',
        'class' => ['show_if_simple', 'show_if_variable'],
        'priority' => 21,
    ];
    return $tabs;
}
endif;

/**
 * Render product data panels for donations
 */
if ( ! function_exists( 'skydonate_remote_render_product_data_panels' ) ) :
function skydonate_remote_render_product_data_panels() {
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

                <!-- Monthly Donation -->
                <label class="skydonate-radio monthly">
                    <input type="radio"
                        name="donation_frequency"
                        value="monthly"
                        <?php checked($donation_frequency, 'monthly'); ?>>
                    <?php _e('Monthly Donation', 'skydonate'); ?>
                </label>

                <!-- Weekly Donation -->
                <label class="skydonate-radio weekly">
                    <input type="radio"
                        name="donation_frequency"
                        value="weekly"
                        <?php checked($donation_frequency, 'weekly'); ?>>
                    <?php _e('Weekly Donation', 'skydonate'); ?>
                </label>

                <!-- Daily Donation -->
                <label class="skydonate-radio daily">
                    <input type="radio"
                        name="donation_frequency"
                        value="daily"
                        <?php checked($donation_frequency, 'daily'); ?>>
                    <?php _e('Daily Donation', 'skydonate'); ?>
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
endif;

/**
 * Save product fields for donations
 *
 * @param int $post_id Post ID
 */
if ( ! function_exists( 'skydonate_remote_save_product_fields' ) ) :
function skydonate_remote_save_product_fields( $post_id ) {
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
endif;

/**
 * Display cart item custom data for donations
 *
 * @param array $item_data  Item data
 * @param array $cart_item  Cart item
 * @return array Modified item data
 */
if ( ! function_exists( 'skydonate_remote_display_cart_item_custom_data' ) ) :
function skydonate_remote_display_cart_item_custom_data( $item_data, $cart_item ) {
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
endif;

/**
 * Determine if a donation product should be treated as a subscription
 *
 * @param bool       $is_subscription Whether the product is already considered a subscription
 * @param int        $product_id      The product ID being checked
 * @param WC_Product $product         The product object
 * @return bool
 */
if ( ! function_exists( 'skydonate_remote_maybe_mark_donation_as_subscription' ) ) :
function skydonate_remote_maybe_mark_donation_as_subscription( $is_subscription, $product_id, $product ) {
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
endif;

/**
 * Apply subscription schemes when adding to cart
 *
 * @param string $item_key     Cart item key
 * @param int    $product_id   Product ID
 * @param int    $quantity     Quantity
 * @param int    $variation_id Variation ID
 * @param array  $variation    Variation data
 * @param array  $cart_item    Cart item data
 */
if ( ! function_exists( 'skydonate_remote_subscription_schemes_on_add_to_cart' ) ) :
function skydonate_remote_subscription_schemes_on_add_to_cart( $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item ) {
    skydonate_remote_apply_subscriptions( WC()->cart );
}
endif;

// Add action to confirm remote functions are active
add_action( 'init', function() {
    do_action( 'skydonate_remote_functions_loaded' );
}, 6 );
