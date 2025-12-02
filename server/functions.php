<?php
/**
 * SkyDonate Remote Functions
 *
 * This file is loaded from the license server when a valid license is active.
 * It contains premium features and functionality that are only available to licensed users.
 *
 * @package    Skyweb_Donation_System
 * @version    1.0.0
 *
 * IMPORTANT: This file should be uploaded to your license server at:
 * https://skydonate.com/remote/functions.php
 *
 * The file will be loaded by the plugin when:
 * 1. License is valid
 * 2. allow_remote_functions capability is enabled
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Premium Analytics Functions
 */
class SkyDonate_Premium_Analytics {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_skydonate_get_advanced_analytics', array($this, 'get_advanced_analytics'));
        add_action('wp_ajax_skydonate_export_donations', array($this, 'export_donations'));
        add_action('wp_ajax_skydonate_get_donor_insights', array($this, 'get_donor_insights'));
    }

    /**
     * Get advanced analytics data
     */
    public function get_advanced_analytics() {
        check_ajax_referer('skydonate_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $period = isset($_POST['period']) ? sanitize_text_field($_POST['period']) : '30';
        $data = $this->calculate_advanced_metrics($period);

        wp_send_json_success($data);
    }

    /**
     * Calculate advanced donation metrics
     */
    private function calculate_advanced_metrics($days = 30) {
        global $wpdb;

        $date_from = date('Y-m-d', strtotime("-{$days} days"));

        // Get donation products
        $donation_products = $this->get_donation_product_ids();

        if (empty($donation_products)) {
            return $this->get_empty_metrics();
        }

        $product_ids = implode(',', array_map('intval', $donation_products));

        // Total donations in period
        $total_donations = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(order_item_meta.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
        ", $date_from));

        // Order count
        $order_count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT order_items.order_id)
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
        ", $date_from));

        // Unique donors
        $unique_donors = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT post_meta.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            INNER JOIN {$wpdb->postmeta} AS post_meta
                ON posts.ID = post_meta.post_id AND post_meta.meta_key = '_billing_email'
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
        ", $date_from));

        // Daily breakdown
        $daily_data = $wpdb->get_results($wpdb->prepare("
            SELECT DATE(posts.post_date) as date,
                   SUM(order_item_meta.meta_value) as total,
                   COUNT(DISTINCT order_items.order_id) as count
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
            GROUP BY DATE(posts.post_date)
            ORDER BY date ASC
        ", $date_from), ARRAY_A);

        // Top projects
        $top_projects = $wpdb->get_results($wpdb->prepare("
            SELECT product_meta.meta_value as product_id,
                   SUM(order_item_meta.meta_value) as total,
                   COUNT(DISTINCT order_items.order_id) as count
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
            GROUP BY product_meta.meta_value
            ORDER BY total DESC
            LIMIT 5
        ", $date_from), ARRAY_A);

        // Add product names
        foreach ($top_projects as &$project) {
            $project['name'] = get_the_title($project['product_id']);
        }

        // Recurring vs one-time breakdown
        $recurring_total = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(order_item_meta.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS subscription_meta
                ON order_items.order_item_id = subscription_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
                AND subscription_meta.meta_key = '_subscription_period'
        ", $date_from));

        $one_time_total = floatval($total_donations) - floatval($recurring_total);

        return array(
            'total_amount' => floatval($total_donations),
            'order_count' => intval($order_count),
            'unique_donors' => intval($unique_donors),
            'average_donation' => $order_count > 0 ? floatval($total_donations) / intval($order_count) : 0,
            'daily_data' => $daily_data,
            'top_projects' => $top_projects,
            'recurring_total' => floatval($recurring_total),
            'one_time_total' => $one_time_total,
            'period' => $days,
            'currency' => get_woocommerce_currency_symbol(),
        );
    }

    /**
     * Get empty metrics structure
     */
    private function get_empty_metrics() {
        return array(
            'total_amount' => 0,
            'order_count' => 0,
            'unique_donors' => 0,
            'average_donation' => 0,
            'daily_data' => array(),
            'top_projects' => array(),
            'recurring_total' => 0,
            'one_time_total' => 0,
            'currency' => get_woocommerce_currency_symbol(),
        );
    }

    /**
     * Get donation product IDs
     */
    private function get_donation_product_ids() {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_sky_donation_product',
                    'value' => 'yes',
                    'compare' => '=',
                ),
            ),
        );

        $query = new WP_Query($args);
        return $query->posts;
    }

    /**
     * Export donations to CSV
     */
    public function export_donations() {
        check_ajax_referer('skydonate_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $period = isset($_POST['period']) ? intval($_POST['period']) : 30;
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';

        // Generate export data
        $data = $this->get_export_data($period);

        if ($format === 'csv') {
            $csv = $this->generate_csv($data);
            wp_send_json_success(array(
                'content' => $csv,
                'filename' => 'skydonate-donations-' . date('Y-m-d') . '.csv',
            ));
        }

        wp_send_json_error(array('message' => 'Invalid format'));
    }

    /**
     * Get export data
     */
    private function get_export_data($days) {
        global $wpdb;

        $date_from = date('Y-m-d', strtotime("-{$days} days"));
        $donation_products = $this->get_donation_product_ids();

        if (empty($donation_products)) {
            return array();
        }

        $product_ids = implode(',', array_map('intval', $donation_products));

        return $wpdb->get_results($wpdb->prepare("
            SELECT
                posts.ID as order_id,
                posts.post_date as date,
                billing_email.meta_value as email,
                billing_name.meta_value as name,
                order_item_meta.meta_value as amount,
                order_items.order_item_name as project
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            LEFT JOIN {$wpdb->postmeta} AS billing_email
                ON posts.ID = billing_email.post_id AND billing_email.meta_key = '_billing_email'
            LEFT JOIN {$wpdb->postmeta} AS billing_name
                ON posts.ID = billing_name.post_id AND billing_name.meta_key = '_billing_first_name'
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND posts.post_date >= %s
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
            ORDER BY posts.post_date DESC
        ", $date_from), ARRAY_A);
    }

    /**
     * Generate CSV from data
     */
    private function generate_csv($data) {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');

        // Headers
        fputcsv($output, array('Order ID', 'Date', 'Donor Email', 'Donor Name', 'Amount', 'Project'));

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, array(
                $row['order_id'],
                $row['date'],
                $row['email'],
                $row['name'],
                $row['amount'],
                $row['project'],
            ));
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Get donor insights
     */
    public function get_donor_insights() {
        check_ajax_referer('skydonate_analytics_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        global $wpdb;

        $donation_products = $this->get_donation_product_ids();

        if (empty($donation_products)) {
            wp_send_json_success(array('donors' => array()));
        }

        $product_ids = implode(',', array_map('intval', $donation_products));

        // Top donors
        $top_donors = $wpdb->get_results("
            SELECT
                billing_email.meta_value as email,
                CONCAT(billing_fname.meta_value, ' ', billing_lname.meta_value) as name,
                SUM(order_item_meta.meta_value) as total_donated,
                COUNT(DISTINCT order_items.order_id) as donation_count,
                MAX(posts.post_date) as last_donation
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            LEFT JOIN {$wpdb->postmeta} AS billing_email
                ON posts.ID = billing_email.post_id AND billing_email.meta_key = '_billing_email'
            LEFT JOIN {$wpdb->postmeta} AS billing_fname
                ON posts.ID = billing_fname.post_id AND billing_fname.meta_key = '_billing_first_name'
            LEFT JOIN {$wpdb->postmeta} AS billing_lname
                ON posts.ID = billing_lname.post_id AND billing_lname.meta_key = '_billing_last_name'
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value IN ({$product_ids})
            GROUP BY billing_email.meta_value
            ORDER BY total_donated DESC
            LIMIT 20
        ", ARRAY_A);

        wp_send_json_success(array(
            'donors' => $top_donors,
            'currency' => get_woocommerce_currency_symbol(),
        ));
    }
}

/**
 * Premium Donation Features
 */
class SkyDonate_Premium_Features {

    public function __construct() {
        // AI-powered donation suggestions
        add_filter('skydonate_suggested_amounts', array($this, 'ai_suggested_amounts'), 10, 2);

        // Advanced goal tracking
        add_action('woocommerce_order_status_completed', array($this, 'update_goal_progress'));

        // Performance optimizations
        add_action('init', array($this, 'setup_caching'));
    }

    /**
     * AI-powered donation amount suggestions based on user behavior
     */
    public function ai_suggested_amounts($amounts, $product_id) {
        if (!skydonate_license()->has_feature('ai')) {
            return $amounts;
        }

        // Get average donation for this product
        global $wpdb;

        $avg = $wpdb->get_var($wpdb->prepare("
            SELECT AVG(order_item_meta.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta
                ON order_items.order_item_id = order_item_meta.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS product_meta
                ON order_items.order_item_id = product_meta.order_item_id
            INNER JOIN {$wpdb->posts} AS posts
                ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
                AND posts.post_status IN ('wc-completed', 'wc-processing')
                AND order_item_meta.meta_key = '_line_total'
                AND product_meta.meta_key = '_product_id'
                AND product_meta.meta_value = %d
            LIMIT 100
        ", $product_id));

        if ($avg > 0) {
            // Generate smart suggestions around the average
            $avg = round($avg);
            $amounts = array(
                round($avg * 0.5),
                $avg,
                round($avg * 1.5),
                round($avg * 2),
            );
        }

        return $amounts;
    }

    /**
     * Update goal progress on order completion
     */
    public function update_goal_progress($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;

        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $amount = $item->get_total();

            // Update goal progress in transient for fast access
            $current = get_transient('skydonate_goal_progress_' . $product_id);
            if ($current === false) {
                $current = 0;
            }
            $current += floatval($amount);
            set_transient('skydonate_goal_progress_' . $product_id, $current, DAY_IN_SECONDS);
        }
    }

    /**
     * Setup performance caching
     */
    public function setup_caching() {
        if (!skydonate_license()->has_feature('performance_boost')) {
            return;
        }

        // Add object caching for frequently accessed data
        add_filter('skydonate_get_donation_stats', array($this, 'cache_donation_stats'), 10, 2);
    }

    /**
     * Cache donation statistics
     */
    public function cache_donation_stats($stats, $product_id) {
        $cache_key = 'skydonate_stats_' . $product_id;
        $cached = wp_cache_get($cache_key, 'skydonate');

        if ($cached !== false) {
            return $cached;
        }

        wp_cache_set($cache_key, $stats, 'skydonate', HOUR_IN_SECONDS);
        return $stats;
    }
}

/**
 * Image Optimization Feature
 */
class SkyDonate_Image_Optimizer {

    public function __construct() {
        if (!skydonate_license()->has_feature('img_optimize')) {
            return;
        }

        add_filter('wp_get_attachment_image_src', array($this, 'optimize_image'), 10, 4);
    }

    /**
     * Optimize images on the fly
     */
    public function optimize_image($image, $attachment_id, $size, $icon) {
        if (!$image || !is_array($image)) {
            return $image;
        }

        // Add lazy loading attribute
        add_filter('wp_get_attachment_image_attributes', function($attr) {
            $attr['loading'] = 'lazy';
            return $attr;
        });

        return $image;
    }
}

/**
 * Remove branding feature
 */
class SkyDonate_No_Branding {

    public function __construct() {
        if (!skydonate_license()->has_feature('no_branding')) {
            return;
        }

        // Remove SkyDonate branding
        add_filter('skydonate_show_branding', '__return_false');
        add_filter('skydonate_footer_text', '__return_empty_string');
    }
}

// Initialize premium features
add_action('plugins_loaded', function() {
    if (function_exists('skydonate_license') && skydonate_license()->is_license_valid()) {
        new SkyDonate_Premium_Analytics();
        new SkyDonate_Premium_Features();
        new SkyDonate_Image_Optimizer();
        new SkyDonate_No_Branding();
    }
}, 20);

/**
 * Helper function to check premium feature
 */
function skydonate_has_premium_feature($feature) {
    if (!function_exists('skydonate_license')) {
        return false;
    }
    return skydonate_license()->has_feature($feature);
}

/**
 * Helper function to get analytics instance
 */
function skydonate_analytics() {
    return SkyDonate_Premium_Analytics::get_instance();
}
