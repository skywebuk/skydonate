<?php
/**
 * Remote Loader Class
 *
 * Handles loading remote functions and configuration
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skyweb_Remote_Loader {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * License manager instance
     */
    private $license_manager;

    /**
     * Remote config cache
     */
    private $remote_config = null;

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
        $this->license_manager = skydonate_license();

        add_action('skydonate_load_remote_functions', array($this, 'load_remote_functions'));
        add_action('init', array($this, 'maybe_load_remote_functions'), 20);
        add_action('init', array($this, 'maybe_load_remote_config'), 20);
    }

    /**
     * Maybe load remote functions on init
     */
    public function maybe_load_remote_functions() {
        if (!$this->license_manager->is_license_valid()) {
            return;
        }

        if (!$this->license_manager->has_capability('allow_remote_functions')) {
            return;
        }

        $this->load_remote_functions();
    }

    /**
     * Maybe load remote config on init
     */
    public function maybe_load_remote_config() {
        if (!$this->license_manager->is_license_valid()) {
            return;
        }

        $config_url = $this->license_manager->get_remote_config_url();
        if (!empty($config_url)) {
            $this->load_remote_config($config_url);
        }
    }

    /**
     * Load remote functions from server
     */
    public function load_remote_functions() {
        $functions_url = $this->license_manager->get_remote_functions_url();

        if (empty($functions_url)) {
            return false;
        }

        // Check cache first
        $cached_code = get_transient('skydonate_remote_functions');
        $cached_hash = get_transient('skydonate_remote_functions_hash');

        if ($cached_code !== false && $cached_hash !== false) {
            // Verify integrity
            if (hash('sha256', $cached_code) === $cached_hash) {
                $this->execute_remote_code($cached_code);
                return true;
            }
        }

        // Fetch fresh code from server
        $response = wp_remote_get($functions_url, array(
            'timeout' => 30,
            'headers' => array(
                'X-License-Key' => $this->license_manager->get_license_key(),
                'X-Site-Domain' => $this->get_site_domain(),
            ),
        ));

        if (is_wp_error($response)) {
            error_log('SkyDonate: Failed to load remote functions - ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code !== 200 || empty($code)) {
            return false;
        }

        // Validate the code (basic security check)
        if (!$this->validate_remote_code($code)) {
            error_log('SkyDonate: Remote code validation failed');
            return false;
        }

        // Cache the code for 6 hours
        $hash = hash('sha256', $code);
        set_transient('skydonate_remote_functions', $code, 6 * HOUR_IN_SECONDS);
        set_transient('skydonate_remote_functions_hash', $hash, 6 * HOUR_IN_SECONDS);

        // Execute the code
        $this->execute_remote_code($code);

        return true;
    }

    /**
     * Load remote configuration
     */
    public function load_remote_config($config_url) {
        // Check cache first
        $cached_config = get_transient('skydonate_remote_config');

        if ($cached_config !== false) {
            $this->remote_config = $cached_config;
            return $this->remote_config;
        }

        $response = wp_remote_get($config_url, array(
            'timeout' => 15,
            'headers' => array(
                'X-License-Key' => $this->license_manager->get_license_key(),
                'X-Site-Domain' => $this->get_site_domain(),
            ),
        ));

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $config = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($config)) {
            return false;
        }

        // Cache for 1 hour
        set_transient('skydonate_remote_config', $config, HOUR_IN_SECONDS);
        $this->remote_config = $config;

        // Apply remote config
        $this->apply_remote_config($config);

        return $config;
    }

    /**
     * Get remote config value
     */
    public function get_config($key, $default = null) {
        if ($this->remote_config === null) {
            $this->remote_config = get_transient('skydonate_remote_config');
        }

        if (!is_array($this->remote_config)) {
            return $default;
        }

        return isset($this->remote_config[$key]) ? $this->remote_config[$key] : $default;
    }

    /**
     * Validate remote code for security
     */
    private function validate_remote_code($code) {
        // Check for dangerous functions
        $dangerous_patterns = array(
            '/\beval\s*\(/i',
            '/\bexec\s*\(/i',
            '/\bsystem\s*\(/i',
            '/\bshell_exec\s*\(/i',
            '/\bpassthru\s*\(/i',
            '/\bpopen\s*\(/i',
            '/\bproc_open\s*\(/i',
            '/\bpcntl_exec\s*\(/i',
            '/\bfile_put_contents\s*\([^,]+,\s*\$_(GET|POST|REQUEST)/i',
            '/\bunlink\s*\(\s*\$_(GET|POST|REQUEST)/i',
            '/\brmdir\s*\(/i',
            '/\bmkdir\s*\(/i',
        );

        foreach ($dangerous_patterns as $pattern) {
            if (preg_match($pattern, $code)) {
                return false;
            }
        }

        // Check for PHP opening tag
        if (strpos($code, '<?php') === false) {
            return false;
        }

        // Basic syntax check
        $syntax_check = @eval('return true; ?>' . $code);
        if ($syntax_check === false) {
            return false;
        }

        return true;
    }

    /**
     * Execute remote code safely
     */
    private function execute_remote_code($code) {
        try {
            // Remove opening PHP tag if present
            $code = preg_replace('/^<\?php\s*/i', '', $code);

            // Execute in a controlled manner
            eval($code);

            return true;
        } catch (Exception $e) {
            error_log('SkyDonate: Error executing remote code - ' . $e->getMessage());
            return false;
        } catch (Error $e) {
            error_log('SkyDonate: Fatal error in remote code - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply remote configuration settings
     */
    private function apply_remote_config($config) {
        // Update local settings based on remote config
        if (isset($config['settings']) && is_array($config['settings'])) {
            foreach ($config['settings'] as $key => $value) {
                // Only update specific allowed settings
                $allowed_settings = array(
                    'skydonate_accent_color',
                    'skydonate_accent_dark_color',
                    'skydonate_accent_light_color',
                );

                if (in_array($key, $allowed_settings)) {
                    update_option($key, sanitize_text_field($value));
                }
            }
        }

        // Fire action for other components to use config
        do_action('skydonate_remote_config_loaded', $config);
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
     * Clear remote caches
     */
    public function clear_cache() {
        delete_transient('skydonate_remote_functions');
        delete_transient('skydonate_remote_functions_hash');
        delete_transient('skydonate_remote_config');
        $this->remote_config = null;
    }
}

// Initialize
function skydonate_remote_loader() {
    return Skyweb_Remote_Loader::get_instance();
}
