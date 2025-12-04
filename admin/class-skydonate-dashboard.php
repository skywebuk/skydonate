<?php
/**
 * Dashboard Statistics Class
 *
 * Provides data for the donation dashboard charts and metrics
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skyweb_Donation_Dashboard {

    /**
     * Get total donations amount
     */
    public static function get_total_donations( $days = 0 ) {
        global $wpdb;

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND p.post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

        $total = $wpdb->get_var( "
            SELECT SUM(CAST(om.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->prefix}woocommerce_order_itemmeta AS om
            INNER JOIN {$wpdb->prefix}woocommerce_order_items AS oi ON om.order_item_id = oi.order_item_id
            INNER JOIN {$wpdb->posts} AS p ON oi.order_id = p.ID
            WHERE om.meta_key = '_line_total'
            AND oi.order_item_type = 'line_item'
            AND p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            {$date_filter}
        " );

        return $total ? floatval( $total ) : 0;
    }

    /**
     * Get total number of donations
     */
    public static function get_donation_count( $days = 0 ) {
        global $wpdb;

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

        $count = $wpdb->get_var( "
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'shop_order'
            AND post_status = 'wc-completed'
            {$date_filter}
        " );

        return $count ? intval( $count ) : 0;
    }

    /**
     * Get unique donors count
     */
    public static function get_donors_count( $days = 0 ) {
        global $wpdb;

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND p.post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

        $count = $wpdb->get_var( "
            SELECT COUNT(DISTINCT pm.meta_value)
            FROM {$wpdb->postmeta} AS pm
            INNER JOIN {$wpdb->posts} AS p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_billing_email'
            AND p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_value != ''
            {$date_filter}
        " );

        return $count ? intval( $count ) : 0;
    }

    /**
     * Get average donation amount
     */
    public static function get_average_donation( $days = 0 ) {
        $total = self::get_total_donations( $days );
        $count = self::get_donation_count( $days );

        return $count > 0 ? round( $total / $count, 2 ) : 0;
    }

    /**
     * Get donations by date for chart (last N days)
     */
    public static function get_donations_by_date( $days = 30 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT DATE(p.post_date) as donation_date,
                   COUNT(*) as donation_count,
                   SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as donation_total
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_order_total'
            AND p.post_date >= %s
            GROUP BY DATE(p.post_date)
            ORDER BY donation_date ASC
        ", date( 'Y-m-d', strtotime( "-{$days} days" ) ) ), ARRAY_A );

        // Fill in missing dates with zeros
        $data = [];
        $start_date = new DateTime( "-{$days} days" );
        $end_date = new DateTime();
        $interval = new DateInterval( 'P1D' );
        $period = new DatePeriod( $start_date, $interval, $end_date );

        $results_by_date = [];
        foreach ( $results as $row ) {
            $results_by_date[ $row['donation_date'] ] = $row;
        }

        foreach ( $period as $date ) {
            $date_str = $date->format( 'Y-m-d' );
            $data[] = [
                'date'   => $date_str,
                'label'  => $date->format( 'M j' ),
                'count'  => isset( $results_by_date[ $date_str ] ) ? intval( $results_by_date[ $date_str ]['donation_count'] ) : 0,
                'amount' => isset( $results_by_date[ $date_str ] ) ? floatval( $results_by_date[ $date_str ]['donation_total'] ) : 0,
            ];
        }

        return $data;
    }

    /**
     * Get donations by campaign/product
     */
    public static function get_donations_by_campaign( $limit = 10 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                om_product.meta_value as product_id,
                SUM(CAST(om_total.meta_value AS DECIMAL(10,2))) as total_amount,
                COUNT(DISTINCT oi.order_id) as order_count
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om_product
                ON oi.order_item_id = om_product.order_item_id AND om_product.meta_key = '_product_id'
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om_total
                ON oi.order_item_id = om_total.order_item_id AND om_total.meta_key = '_line_total'
            INNER JOIN {$wpdb->posts} AS p ON oi.order_id = p.ID
            WHERE oi.order_item_type = 'line_item'
            AND p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            GROUP BY om_product.meta_value
            ORDER BY total_amount DESC
            LIMIT %d
        ", $limit ), ARRAY_A );

        $data = [];
        foreach ( $results as $row ) {
            $product = wc_get_product( $row['product_id'] );
            $data[] = [
                'id'          => $row['product_id'],
                'name'        => $product ? $product->get_name() : __( 'Unknown Product', 'skydonation' ),
                'amount'      => floatval( $row['total_amount'] ),
                'count'       => intval( $row['order_count'] ),
            ];
        }

        return $data;
    }

    /**
     * Get donations by country
     */
    public static function get_donations_by_country( $limit = 10 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                pm_country.meta_value as country_code,
                COUNT(*) as donation_count,
                SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_amount
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm_country ON p.ID = pm_country.post_id AND pm_country.meta_key = '_billing_country'
            INNER JOIN {$wpdb->postmeta} AS pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm_country.meta_value != ''
            GROUP BY pm_country.meta_value
            ORDER BY total_amount DESC
            LIMIT %d
        ", $limit ), ARRAY_A );

        $countries = WC()->countries->get_countries();
        $data = [];

        foreach ( $results as $row ) {
            $country_code = strtoupper( $row['country_code'] );
            $data[] = [
                'code'   => $country_code,
                'name'   => isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : $country_code,
                'amount' => floatval( $row['total_amount'] ),
                'count'  => intval( $row['donation_count'] ),
            ];
        }

        return $data;
    }

    /**
     * Get donation amount distribution
     */
    public static function get_donation_distribution() {
        global $wpdb;

        $currency_code = get_option('woocommerce_currency');
        $currency = html_entity_decode( get_woocommerce_currency_symbol( $currency_code ) );

        $ranges = [
            [ 'min' => 0, 'max' => 10, 'label' => $currency . '0-10' ],
            [ 'min' => 10, 'max' => 25, 'label' => $currency . '10-25' ],
            [ 'min' => 25, 'max' => 50, 'label' => $currency . '25-50' ],
            [ 'min' => 50, 'max' => 100, 'label' => $currency . '50-100' ],
            [ 'min' => 100, 'max' => 250, 'label' => $currency . '100-250' ],
            [ 'min' => 250, 'max' => 500, 'label' => $currency . '250-500' ],
            [ 'min' => 500, 'max' => 999999, 'label' => $currency . '500+' ],
        ];

        $data = [];
        foreach ( $ranges as $range ) {
            $count = $wpdb->get_var( $wpdb->prepare( "
                SELECT COUNT(*)
                FROM {$wpdb->posts} AS p
                INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
                WHERE p.post_type = 'shop_order'
                AND p.post_status = 'wc-completed'
                AND pm.meta_key = '_order_total'
                AND CAST(pm.meta_value AS DECIMAL(10,2)) >= %f
                AND CAST(pm.meta_value AS DECIMAL(10,2)) < %f
            ", $range['min'], $range['max'] ) );

            $data[] = [
                'label' => $range['label'],
                'count' => intval( $count ),
            ];
        }

        return $data;
    }

    /**
     * Get top donors
     */
    public static function get_top_donors( $limit = 10 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                pm_email.meta_value as email,
                pm_first.meta_value as first_name,
                pm_last.meta_value as last_name,
                COUNT(*) as donation_count,
                SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_amount
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm_email ON p.ID = pm_email.post_id AND pm_email.meta_key = '_billing_email'
            INNER JOIN {$wpdb->postmeta} AS pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
            LEFT JOIN {$wpdb->postmeta} AS pm_first ON p.ID = pm_first.post_id AND pm_first.meta_key = '_billing_first_name'
            LEFT JOIN {$wpdb->postmeta} AS pm_last ON p.ID = pm_last.post_id AND pm_last.meta_key = '_billing_last_name'
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm_email.meta_value != ''
            GROUP BY pm_email.meta_value
            ORDER BY total_amount DESC
            LIMIT %d
        ", $limit ), ARRAY_A );

        $data = [];
        foreach ( $results as $row ) {
            $data[] = [
                'name'   => trim( $row['first_name'] . ' ' . $row['last_name'] ) ?: __( 'Anonymous', 'skydonation' ),
                'email'  => $row['email'],
                'amount' => floatval( $row['total_amount'] ),
                'count'  => intval( $row['donation_count'] ),
            ];
        }

        return $data;
    }

    /**
     * Get recent donations
     */
    public static function get_recent_donations( $limit = 10 ) {
        $orders = wc_get_orders( [
            'status' => 'completed',
            'limit'  => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ] );

        $data = [];
        foreach ( $orders as $order ) {
            $is_anonymous = $order->get_meta( '_anonymous_donation', true );

            $data[] = [
                'id'        => $order->get_id(),
                'name'      => $is_anonymous === '1'
                    ? __( 'Anonymous', 'skydonation' )
                    : $order->get_billing_first_name() . ' ' . substr( $order->get_billing_last_name(), 0, 1 ) . '.',
                'amount'    => $order->get_total(),
                'currency'  => $order->get_currency(),
                'date'      => $order->get_date_created()->format( 'M j, Y' ),
                'time_ago'  => human_time_diff( strtotime( $order->get_date_created() ), time() ),
            ];
        }

        return $data;
    }

    /**
     * Get donations by month for the year
     */
    public static function get_monthly_donations( $year = null ) {
        global $wpdb;

        if ( ! $year ) {
            $year = date( 'Y' );
        }

        $results = $wpdb->get_results( $wpdb->prepare( "
            SELECT
                MONTH(p.post_date) as month,
                COUNT(*) as donation_count,
                SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as donation_total
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_order_total'
            AND YEAR(p.post_date) = %d
            GROUP BY MONTH(p.post_date)
            ORDER BY month ASC
        ", $year ), ARRAY_A );

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];

        $results_by_month = [];
        foreach ( $results as $row ) {
            $results_by_month[ intval( $row['month'] ) ] = $row;
        }

        $data = [];
        foreach ( $months as $num => $label ) {
            $data[] = [
                'month'  => $num,
                'label'  => $label,
                'count'  => isset( $results_by_month[ $num ] ) ? intval( $results_by_month[ $num ]['donation_count'] ) : 0,
                'amount' => isset( $results_by_month[ $num ] ) ? floatval( $results_by_month[ $num ]['donation_total'] ) : 0,
            ];
        }

        return $data;
    }

    /**
     * Get comparison stats (this period vs last period)
     */
    public static function get_comparison_stats( $days = 30 ) {
        $current_total = self::get_total_donations( $days );
        $current_count = self::get_donation_count( $days );
        $current_donors = self::get_donors_count( $days );

        // Get previous period stats
        global $wpdb;
        $start_previous = date( 'Y-m-d', strtotime( "-" . ( $days * 2 ) . " days" ) );
        $end_previous = date( 'Y-m-d', strtotime( "-{$days} days" ) );

        $previous_total = $wpdb->get_var( $wpdb->prepare( "
            SELECT SUM(CAST(pm.meta_value AS DECIMAL(10,2)))
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            AND pm.meta_key = '_order_total'
            AND p.post_date >= %s
            AND p.post_date < %s
        ", $start_previous, $end_previous ) );
        $previous_total = $previous_total ? floatval( $previous_total ) : 0;

        $previous_count = $wpdb->get_var( $wpdb->prepare( "
            SELECT COUNT(*)
            FROM {$wpdb->posts}
            WHERE post_type = 'shop_order'
            AND post_status = 'wc-completed'
            AND post_date >= %s
            AND post_date < %s
        ", $start_previous, $end_previous ) );
        $previous_count = $previous_count ? intval( $previous_count ) : 0;

        return [
            'total' => [
                'current'  => $current_total,
                'previous' => $previous_total,
                'change'   => $previous_total > 0 ? round( ( ( $current_total - $previous_total ) / $previous_total ) * 100, 1 ) : 0,
            ],
            'count' => [
                'current'  => $current_count,
                'previous' => $previous_count,
                'change'   => $previous_count > 0 ? round( ( ( $current_count - $previous_count ) / $previous_count ) * 100, 1 ) : 0,
            ],
            'donors' => [
                'current'  => $current_donors,
            ],
            'average' => [
                'current' => $current_count > 0 ? round( $current_total / $current_count, 2 ) : 0,
            ],
        ];
    }
}
