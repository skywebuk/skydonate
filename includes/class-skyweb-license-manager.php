<?php
/**
 * License Manager Class
 *
 * Handles license validation, caching, and feature gating
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skyweb_License_Manager {

    /**
     * License server URL
     */
    const LICENSE_SERVER = 'https://skydonate.com/';

    /**
     * License key option name
     */
    const LICENSE_KEY_OPTION = 'skydonate_license_key';

    /**
     * License data option name
     */
    const LICENSE_DATA_OPTION = 'skydonate_license_data';

    /**
     * License status option name
     */
    const LICENSE_STATUS_OPTION = 'skydonate_license_status';

    /**
     * Cache duration in seconds (24 hours)
     */
    const CACHE_DURATION = 86400;

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * License data cache
     */
    private $license_data = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_init', array($this, 'schedule_license_check'));
        add_action('skydonate_daily_license_check', array($this, 'validate_license_cron'));
        add_action('wp_ajax_skydonate_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_skydonate_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_skydonate_check_license', array($this, 'ajax_check_license'));
    }

    /**
     * Schedule daily license check
     */
    public function schedule_license_check() {
        if (!wp_next_scheduled('skydonate_daily_license_check')) {
            wp_schedule_event(time(), 'daily', 'skydonate_daily_license_check');
        }
    }

    /**
     * Cron job for license validation
     */
    public function validate_license_cron() {
        $license_key = $this->get_license_key();
        if (!empty($license_key)) {
            $this->validate_license($license_key, true);
        }
    }

    /**
     * Get the stored license key
     */
    public function get_license_key() {
        return get_option(self::LICENSE_KEY_OPTION, '');
    }

    /**
     * Get license data (cached or fresh)
     */
    public function get_license_data($force_refresh = false) {
        if ($this->license_data !== null && !$force_refresh) {
            return $this->license_data;
        }

        $cached_data = get_option(self::LICENSE_DATA_OPTION, array());
        $cache_time = isset($cached_data['cache_time']) ? $cached_data['cache_time'] : 0;

        // Check if cache is still valid
        if (!$force_refresh && !empty($cached_data) && (time() - $cache_time) < self::CACHE_DURATION) {
            $this->license_data = $cached_data;
            return $this->license_data;
        }

        // Refresh from server
        $license_key = $this->get_license_key();
        if (!empty($license_key)) {
            $this->validate_license($license_key, true);
            return get_option(self::LICENSE_DATA_OPTION, array());
        }

        return array();
    }

    /**
     * Check if license is valid
     */
    public function is_license_valid() {
        $status = get_option(self::LICENSE_STATUS_OPTION, 'invalid');
        return $status === 'valid';
    }

    /**
     * Get license status
     */
    public function get_license_status() {
        return get_option(self::LICENSE_STATUS_OPTION, 'invalid');
    }

    /**
     * Check if a specific feature is enabled
     */
    public function has_feature($feature) {
        if (!$this->is_license_valid()) {
            return false;
        }

        $license_data = $this->get_license_data();
        return isset($license_data['features'][$feature]) && $license_data['features'][$feature] === true;
    }

    /**
     * Check if a capability is enabled
     */
    public function has_capability($capability) {
        if (!$this->is_license_valid()) {
            return false;
        }

        $license_data = $this->get_license_data();
        return isset($license_data['capabilities'][$capability]) && $license_data['capabilities'][$capability] === true;
    }

    /**
     * Get all features
     */
    public function get_features() {
        $license_data = $this->get_license_data();
        return isset($license_data['features']) ? $license_data['features'] : array();
    }

    /**
     * Get all capabilities
     */
    public function get_capabilities() {
        $license_data = $this->get_license_data();
        return isset($license_data['capabilities']) ? $license_data['capabilities'] : array();
    }

    /**
     * Get license expiry date
     */
    public function get_expiry_date() {
        $license_data = $this->get_license_data();
        return isset($license_data['expires']) ? $license_data['expires'] : '';
    }

    /**
     * Get remote functions URL
     */
    public function get_remote_functions_url() {
        $license_data = $this->get_license_data();
        return isset($license_data['remote_functions_url']) ? $license_data['remote_functions_url'] : '';
    }

    /**
     * Get remote config URL
     */
    public function get_remote_config_url() {
        $license_data = $this->get_license_data();
        return isset($license_data['remote_config_url']) ? $license_data['remote_config_url'] : '';
    }

    /**
     * Validate license with server
     */
    public function validate_license($license_key, $update_cache = true) {
        $domain = $this->get_site_domain();

        // Allow localhost if developing
        if ($this->is_localhost() && !$this->has_capability('allow_localhost')) {
            // Still try to validate - server will determine if localhost is allowed
        }

        $response = $this->api_request('sky_license_validate', array(
            'license' => $license_key,
            'domain' => $domain,
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'status' => 'error',
                'message' => $response->get_error_message(),
            );
        }

        error_log(print_r($response, true));

        // Check HTTP status code
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code !== 200) {
            return array(
                'success' => false,
                'status' => 'error',
                'message' => sprintf(__('License server returned error (HTTP %d). Please try again later.', 'skydonate'), $status_code),
            );
        }

        $response_body = wp_remote_retrieve_body($response);
        $body = json_decode($response_body, true);

        // Check if JSON decode failed
        if ($body === null) {
            // Include snippet of response for debugging
            $snippet = substr(strip_tags($response_body), 0, 100);
            return array(
                'success' => false,
                'status' => 'error',
                'message' => sprintf(__('Invalid response from license server: %s', 'skydonate'), $snippet ?: __('Empty response', 'skydonate')),
            );
        }

        // Ensure message field exists for error responses
        if (!isset($body['success']) || $body['success'] !== true) {
            if (!isset($body['message'])) {
                $body['message'] = __('License validation failed. Please check your license key.', 'skydonate');
            }
        }

        if ($update_cache && isset($body['success']) && $body['success'] === true) {
            $body['cache_time'] = time();
            update_option(self::LICENSE_DATA_OPTION, $body);
            update_option(self::LICENSE_STATUS_OPTION, 'valid');
            update_option(self::LICENSE_KEY_OPTION, $license_key);
        } elseif ($update_cache) {
            update_option(self::LICENSE_STATUS_OPTION, isset($body['status']) ? $body['status'] : 'invalid');
        }

        return $body;
    }

    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $result = $this->validate_license($license_key, true);

        if (isset($result['success']) && $result['success'] === true) {
            // Load remote functions if allowed
            if ($this->has_capability('allow_remote_functions')) {
                do_action('skydonate_load_remote_functions');
            }
        }

        return $result;
    }

    /**
     * Deactivate license
     */
    public function deactivate_license() {
        delete_option(self::LICENSE_KEY_OPTION);
        delete_option(self::LICENSE_DATA_OPTION);
        update_option(self::LICENSE_STATUS_OPTION, 'invalid');

        // Clear scheduled events
        wp_clear_scheduled_hook('skydonate_daily_license_check');

        return array(
            'success' => true,
            'message' => __('License deactivated successfully.', 'skydonate'),
        );
    }

    /**
     * AJAX handler for license activation
     */
    public function ajax_activate_license() {
        check_ajax_referer('skydonate_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'skydonate')));
        }

        $license_key = isset($_POST['license_key']) ? sanitize_text_field($_POST['license_key']) : '';

        if (empty($license_key)) {
            wp_send_json_error(array('message' => __('Please enter a license key.', 'skydonate')));
        }

        // Validate license key format
        if (!$this->is_valid_license_format($license_key)) {
            wp_send_json_error(array('message' => __('Invalid license key format. Expected: SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX', 'skydonate')));
        }

        $result = $this->activate_license($license_key);

        if (isset($result['success']) && $result['success'] === true) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    /**
     * AJAX handler for license deactivation
     */
    public function ajax_deactivate_license() {
        check_ajax_referer('skydonate_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'skydonate')));
        }

        $result = $this->deactivate_license();
        wp_send_json_success($result);
    }

    /**
     * AJAX handler for checking license status
     */
    public function ajax_check_license() {
        check_ajax_referer('skydonate_license_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied.', 'skydonate')));
        }

        $license_key = $this->get_license_key();

        if (empty($license_key)) {
            wp_send_json_error(array('message' => __('No license key found.', 'skydonate')));
        }

        $result = $this->validate_license($license_key, true);
        wp_send_json_success($result);
    }

    /**
     * Make API request to license server
     */
    private function api_request($action, $data) {
        $url = add_query_arg($action, '1', self::LICENSE_SERVER);

        $response = wp_remote_post($url, array(
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($data),
        ));

        return $response;
    }

    /**
     * Get site domain
     */
    private function get_site_domain() {
        $site_url = get_site_url();
        $parsed = wp_parse_url($site_url);
        return isset($parsed['host']) ? $parsed['host'] : '';
    }

    /**
     * Check if running on localhost
     */
    private function is_localhost() {
        $domain = $this->get_site_domain();
        $localhost_patterns = array('localhost', '127.0.0.1', '::1', '.local', '.test', '.dev');

        foreach ($localhost_patterns as $pattern) {
            if (strpos($domain, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate license key format
     */
    private function is_valid_license_format($license_key) {
        // Format: SKY-XXXXXXXX-XXXXXXXX-XXXXXXXX-XXXXXXXX
        $pattern = '/^SKY-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}-[A-Z0-9]{8}$/i';
        return preg_match($pattern, $license_key);
    }

    /**
     * Get days until expiry
     */
    public function get_days_until_expiry() {
        $expiry = $this->get_expiry_date();
        if (empty($expiry)) {
            return null;
        }

        $expiry_time = strtotime($expiry);
        $current_time = current_time('timestamp');
        $diff = $expiry_time - $current_time;

        return max(0, floor($diff / DAY_IN_SECONDS));
    }

    /**
     * Check if license is about to expire (within 30 days)
     */
    public function is_expiring_soon() {
        $days = $this->get_days_until_expiry();
        return $days !== null && $days <= 30;
    }
}

// Initialize the license manager
function skydonate_license() {
    return Skyweb_License_Manager::get_instance();
}
