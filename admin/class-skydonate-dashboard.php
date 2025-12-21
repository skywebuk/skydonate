<?php
/**
 * Dashboard Statistics Class
 *
 * Provides data for the donation dashboard charts and metrics
 * HPOS-compatible queries for WooCommerce 8.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skydonate_Dashboard {

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        add_action( 'wp_ajax_skydonate_get_analytics', array( __CLASS__, 'ajax_get_analytics' ) );
    }

    /**
     * AJAX handler for analytics date range - returns all data for charts
     */
    public static function ajax_get_analytics() {
        // Verify nonce
        if ( ! check_ajax_referer( 'skydonate_analytics_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'skydonate' ) ) );
        }

        // Check capability
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Unauthorized access', 'skydonate' ) ) );
        }

        $days = isset( $_POST['days'] ) ? absint( $_POST['days'] ) : 30;

        // Limit to allowed values
        if ( ! in_array( $days, array( 30, 60, 90, 365 ), true ) ) {
            $days = 30;
        }

        $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( get_option( 'woocommerce_currency' ) ) );

        // Get all data based on date range
        $comparison = self::get_comparison_stats( $days );
        $trends_data = self::get_donations_by_date( $days );
        $campaigns = self::get_donations_by_campaign( 6, $days );
        $countries = self::get_donations_by_country( 8, $days );
        $distribution = self::get_donation_distribution( $days );
        $top_donors = self::get_top_donors( 5, $days );
        $recent_donations = self::get_recent_donations( 5, $days );

        wp_send_json_success( array(
            'comparison'       => $comparison,
            'currency_symbol'  => $currency_symbol,
            'days'             => $days,
            'trends'           => array(
                'labels'  => array_column( $trends_data, 'label' ),
                'amounts' => array_column( $trends_data, 'amount' ),
                'counts'  => array_column( $trends_data, 'count' ),
            ),
            'campaigns'        => array(
                'labels'  => array_column( $campaigns, 'name' ),
                'amounts' => array_column( $campaigns, 'amount' ),
                'data'    => $campaigns,
            ),
            'countries'        => array(
                'labels'  => array_column( $countries, 'name' ),
                'amounts' => array_column( $countries, 'amount' ),
            ),
            'distribution'     => array(
                'labels' => array_column( $distribution, 'label' ),
                'counts' => array_column( $distribution, 'count' ),
            ),
            'top_donors'       => $top_donors,
            'recent_donations' => $recent_donations,
        ) );
    }

    /**
     * Check if HPOS is enabled
     */
    private static function is_hpos_enabled() {
        return class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
            && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    /**
     * Get order tables info for HPOS compatibility
     */
    private static function get_order_tables() {
        global $wpdb;

        if ( self::is_hpos_enabled() ) {
            return [
                'orders'      => $wpdb->prefix . 'wc_orders',
                'orders_meta' => $wpdb->prefix . 'wc_orders_meta',
                'id_column'   => 'id',
                'date_column' => 'date_created_gmt',
                'status_column' => 'status',
            ];
        }

        return [
            'orders'      => $wpdb->posts,
            'orders_meta' => $wpdb->postmeta,
            'id_column'   => 'ID',
            'date_column' => 'post_date',
            'status_column' => 'post_status',
            'type_filter' => "post_type = 'shop_order' AND",
        ];
    }

    /**
     * Get total donations amount using order total meta.
     */
    public static function get_total_donations( $days = 0 ) {
        $args = [
            'status' => 'completed',
            'type'   => 'shop_order',
            'limit'  => -1,
        ];

        if ( $days > 0 ) {
            $args['date_created'] = '>=' . date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $orders = wc_get_orders( $args );
        $total = 0;

        foreach ( $orders as $order ) {
            $total += floatval( $order->get_total() );
        }

        return $total;
    }

    /**
     * Get total number of donations
     */
    public static function get_donation_count( $days = 0 ) {
        $args = [
            'status' => 'completed',
            'type'   => 'shop_order',
            'limit'  => -1,
            'return' => 'ids',
        ];

        if ( $days > 0 ) {
            $args['date_created'] = '>=' . date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $orders = wc_get_orders( $args );
        return count( $orders );
    }

    /**
     * Get unique donors count
     */
    public static function get_donors_count( $days = 0 ) {
        $args = [
            'status' => 'completed',
            'type'   => 'shop_order',
            'limit'  => -1,
        ];

        if ( $days > 0 ) {
            $args['date_created'] = '>=' . date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $orders = wc_get_orders( $args );
        $emails = [];

        foreach ( $orders as $order ) {
            $email = $order->get_billing_email();
            if ( ! empty( $email ) ) {
                $emails[ $email ] = true;
            }
        }

        return count( $emails );
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

        $tables = self::get_order_tables();
        $is_hpos = self::is_hpos_enabled();
        $type_filter = $is_hpos ? '' : "AND o.post_type = 'shop_order'";
        $status_value = 'wc-completed';

        if ( $is_hpos ) {
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT DATE(o.date_created_gmt) as donation_date,
                       COUNT(*) as donation_count,
                       SUM(o.total_amount) as donation_total
                FROM {$tables['orders']} AS o
                WHERE o.status = %s
                AND o.date_created_gmt >= %s
                GROUP BY DATE(o.date_created_gmt)
                ORDER BY donation_date ASC
            ", $status_value, date( 'Y-m-d', strtotime( "-{$days} days" ) ) ), ARRAY_A );
        } else {
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT DATE(o.post_date) as donation_date,
                       COUNT(*) as donation_count,
                       SUM(CAST(pm.meta_value AS DECIMAL(10,2))) as donation_total
                FROM {$wpdb->posts} AS o
                INNER JOIN {$wpdb->postmeta} AS pm ON o.ID = pm.post_id
                WHERE o.post_type = 'shop_order'
                AND o.post_status = %s
                AND pm.meta_key = '_order_total'
                AND o.post_date >= %s
                GROUP BY DATE(o.post_date)
                ORDER BY donation_date ASC
            ", $status_value, date( 'Y-m-d', strtotime( "-{$days} days" ) ) ), ARRAY_A );
        }

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
     * Get donations by campaign/product using _skydonate_product_id order meta.
     */
    public static function get_donations_by_campaign( $limit = 10, $days = 0 ) {
        global $wpdb;

        $tables = self::get_order_tables();
        $is_hpos = self::is_hpos_enabled();

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND o.{$tables['date_column']} >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

        if ( $is_hpos ) {
            $meta_table = $wpdb->prefix . 'wc_orders_meta';
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT
                    pm.meta_value as product_id,
                    SUM(o.total_amount) as total_amount,
                    COUNT(DISTINCT o.id) as order_count
                FROM {$tables['orders']} AS o
                INNER JOIN {$meta_table} AS pm ON o.id = pm.order_id AND pm.meta_key = '_skydonate_product_id'
                WHERE o.status = 'wc-completed'
                {$date_filter}
                GROUP BY pm.meta_value
                ORDER BY total_amount DESC
                LIMIT %d
            ", $limit ), ARRAY_A );
        } else {
            $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT
                    pm.meta_value as product_id,
                    SUM(CAST(pm_total.meta_value AS DECIMAL(10,2))) as total_amount,
                    COUNT(DISTINCT o.ID) as order_count
                FROM {$wpdb->posts} AS o
                INNER JOIN {$wpdb->postmeta} AS pm ON o.ID = pm.post_id AND pm.meta_key = '_skydonate_product_id'
                INNER JOIN {$wpdb->postmeta} AS pm_total ON o.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                WHERE o.post_type = 'shop_order'
                AND o.post_status = 'wc-completed'
                {$date_filter}
                GROUP BY pm.meta_value
                ORDER BY total_amount DESC
                LIMIT %d
            ", $limit ), ARRAY_A );
        }

        $data = [];
        foreach ( $results as $row ) {
            $product = wc_get_product( $row['product_id'] );
            $data[] = [
                'id'          => $row['product_id'],
                'name'        => $product ? $product->get_name() : __( 'Unknown Product', 'skydonate' ),
                'amount'      => floatval( $row['total_amount'] ),
                'count'       => intval( $row['order_count'] ),
            ];
        }

        return $data;
    }

    /**
     * Get donations by country
     */
    public static function get_donations_by_country( $limit = 10, $days = 0 ) {
        global $wpdb;

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND p.post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

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
            {$date_filter}
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
    public static function get_donation_distribution( $days = 0 ) {
        global $wpdb;

        $currency_code = get_option('woocommerce_currency');
        $currency = html_entity_decode( get_woocommerce_currency_symbol( $currency_code ) );

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND p.post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

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
                {$date_filter}
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
    public static function get_top_donors( $limit = 10, $days = 0 ) {
        global $wpdb;

        $date_filter = '';
        if ( $days > 0 ) {
            $date_filter = $wpdb->prepare( " AND p.post_date >= %s", date( 'Y-m-d', strtotime( "-{$days} days" ) ) );
        }

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
            {$date_filter}
            GROUP BY pm_email.meta_value
            ORDER BY total_amount DESC
            LIMIT %d
        ", $limit ), ARRAY_A );

        $data = [];
        foreach ( $results as $row ) {
            $data[] = [
                'name'   => trim( $row['first_name'] . ' ' . $row['last_name'] ) ?: __( 'Anonymous', 'skydonate' ),
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
    public static function get_recent_donations( $limit = 10, $days = 0 ) {
        $args = [
            'status'  => 'completed',
            'type'    => 'shop_order',
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
        ];

        if ( $days > 0 ) {
            $args['date_created'] = '>=' . date( 'Y-m-d', strtotime( "-{$days} days" ) );
        }

        $orders = wc_get_orders( $args );

        $data = [];
        foreach ( $orders as $order ) {
            $is_anonymous = $order->get_meta( '_anonymous_donation', true );

            $data[] = [
                'id'        => $order->get_id(),
                'name'      => $is_anonymous === '1'
                    ? __( 'Anonymous', 'skydonate' )
                    : $order->get_billing_first_name() . ' ' . substr( $order->get_billing_last_name(), 0, 1 ) . '.',
                'amount'    => $order->get_total(),
                'currency'  => html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) ),
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

        // Get previous period stats using HPOS-compatible wc_get_orders
        $start_previous = date( 'Y-m-d', strtotime( "-" . ( $days * 2 ) . " days" ) );
        $end_previous = date( 'Y-m-d', strtotime( "-{$days} days" ) );

        $previous_orders = wc_get_orders( [
            'status'       => 'completed',
            'type'         => 'shop_order',
            'date_created' => $start_previous . '...' . $end_previous,
            'limit'        => -1,
        ] );

        $previous_total = 0;
        foreach ( $previous_orders as $order ) {
            $previous_total += floatval( $order->get_total() );
        }

        $previous_count = count( $previous_orders );

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

// Initialize AJAX handlers
Skydonate_Dashboard::init();
