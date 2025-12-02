<?php
/**
 * License Updater Class
 *
 * Handles WordPress auto-updates integration
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class Skyweb_License_Updater {

    /**
     * Plugin slug
     */
    private $plugin_slug;

    /**
     * Plugin basename
     */
    private $plugin_basename;

    /**
     * Current version
     */
    private $current_version;

    /**
     * License manager instance
     */
    private $license_manager;

    /**
     * Cached update data
     */
    private $update_cache = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->plugin_slug = 'skydonate';
        $this->plugin_basename = defined('SKYWEB_DONATION_SYSTEM_BASE') ? SKYWEB_DONATION_SYSTEM_BASE : 'skydonate/skydonate.php';
        $this->current_version = defined('SKYWEB_DONATION_SYSTEM_VERSION') ? SKYWEB_DONATION_SYSTEM_VERSION : '1.0.0';
        $this->license_manager = skydonate_license();

        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Check for updates
        add_filter('pre_set_site_transient_update_plugins', array($this, 'check_for_updates'));

        // Plugin info popup
        add_filter('plugins_api', array($this, 'plugin_info'), 20, 3);

        // After update complete
        add_action('upgrader_process_complete', array($this, 'after_update'), 10, 2);

        // Auto-update filter
        add_filter('auto_update_plugin', array($this, 'auto_update_plugin'), 10, 2);

        // Add update message
        add_action('in_plugin_update_message-' . $this->plugin_basename, array($this, 'update_message'), 10, 2);
    }

    /**
     * Check for plugin updates
     */
    public function check_for_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        // Don't check if license is not valid
        if (!$this->license_manager->is_license_valid()) {
            return $transient;
        }

        // Don't check if update notifications are disabled
        if ($this->license_manager->has_capability('disable_update_notifications')) {
            return $transient;
        }

        $remote_data = $this->get_remote_version();

        if ($remote_data && isset($remote_data['update_available']) && $remote_data['update_available'] === true) {
            $plugin_data = array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $remote_data['version'],
                'url' => isset($remote_data['info_url']) ? $remote_data['info_url'] : '',
                'package' => isset($remote_data['download_url']) ? $remote_data['download_url'] : '',
                'tested' => isset($remote_data['tested']) ? $remote_data['tested'] : '',
                'requires' => isset($remote_data['requires']) ? $remote_data['requires'] : '',
                'requires_php' => isset($remote_data['requires_php']) ? $remote_data['requires_php'] : '',
            );

            $transient->response[$this->plugin_basename] = (object) $plugin_data;
        } else {
            // No update, add to no_update list
            $transient->no_update[$this->plugin_basename] = (object) array(
                'slug' => $this->plugin_slug,
                'plugin' => $this->plugin_basename,
                'new_version' => $this->current_version,
            );
        }

        return $transient;
    }

    /**
     * Get plugin info for WordPress popup
     */
    public function plugin_info($result, $action, $args) {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if (!isset($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $remote_data = $this->get_remote_version();

        if (!$remote_data) {
            return $result;
        }

        $plugin_info = new stdClass();
        $plugin_info->name = 'SkyDonate';
        $plugin_info->slug = $this->plugin_slug;
        $plugin_info->version = isset($remote_data['version']) ? $remote_data['version'] : $this->current_version;
        $plugin_info->author = '<a href="https://skywebdesign.co.uk/">Sky Web Design</a>';
        $plugin_info->homepage = 'https://skywebdesign.co.uk/';
        $plugin_info->requires = isset($remote_data['requires']) ? $remote_data['requires'] : '5.0';
        $plugin_info->tested = isset($remote_data['tested']) ? $remote_data['tested'] : '';
        $plugin_info->requires_php = isset($remote_data['requires_php']) ? $remote_data['requires_php'] : '7.2';
        $plugin_info->downloaded = 0;
        $plugin_info->last_updated = date('Y-m-d H:i:s');
        $plugin_info->sections = array(
            'description' => __('A secure, user-friendly donation system built to simplify and manage charitable contributions.', 'skydonate'),
            'changelog' => isset($remote_data['changelog']) ? nl2br(esc_html($remote_data['changelog'])) : '',
        );
        $plugin_info->download_link = isset($remote_data['download_url']) ? $remote_data['download_url'] : '';

        return $plugin_info;
    }

    /**
     * Handle auto-update permission
     */
    public function auto_update_plugin($update, $item) {
        if (!isset($item->plugin) || $item->plugin !== $this->plugin_basename) {
            return $update;
        }

        // Check if auto-updates are allowed by license
        if ($this->license_manager->has_capability('allow_auto_updates')) {
            return true;
        }

        return $update;
    }

    /**
     * After update complete
     */
    public function after_update($upgrader, $options) {
        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }

        if (!isset($options['plugins']) || !is_array($options['plugins'])) {
            return;
        }

        if (in_array($this->plugin_basename, $options['plugins'])) {
            // Clear update cache
            delete_transient('skydonate_update_check');
            $this->update_cache = null;

            // Re-validate license after update
            $license_key = $this->license_manager->get_license_key();
            if (!empty($license_key)) {
                $this->license_manager->validate_license($license_key, true);
            }
        }
    }

    /**
     * Add message to update notice
     */
    public function update_message($plugin_data, $response) {
        if (!$this->license_manager->is_license_valid()) {
            echo '<br><span style="color: #dc3232;">';
            echo esc_html__('Please activate your license to receive updates.', 'skydonate');
            echo '</span>';
        } elseif ($this->license_manager->is_expiring_soon()) {
            $days = $this->license_manager->get_days_until_expiry();
            echo '<br><span style="color: #dba617;">';
            printf(
                esc_html__('Your license expires in %d days. Renew to continue receiving updates.', 'skydonate'),
                $days
            );
            echo '</span>';
        }
    }

    /**
     * Get remote version data
     */
    private function get_remote_version() {
        // Check cache first
        $cached = get_transient('skydonate_update_check');
        if ($cached !== false) {
            return $cached;
        }

        $license_key = $this->license_manager->get_license_key();
        if (empty($license_key)) {
            return false;
        }

        $domain = $this->get_site_domain();

        // Check for beta updates if allowed
        $include_beta = $this->license_manager->has_capability('allow_beta');

        $response = wp_remote_post(
            add_query_arg('sky_license_update', '1', Skyweb_License_Manager::LICENSE_SERVER),
            array(
                'timeout' => 30,
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode(array(
                    'license' => $license_key,
                    'domain' => $domain,
                    'version' => $this->current_version,
                    'include_beta' => $include_beta,
                )),
            )
        );

        if (is_wp_error($response)) {
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['success']) && $body['success'] === true) {
            // Cache for 12 hours
            set_transient('skydonate_update_check', $body, 12 * HOUR_IN_SECONDS);
            return $body;
        }

        return false;
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
     * Force update check
     */
    public function force_check() {
        delete_transient('skydonate_update_check');
        delete_site_transient('update_plugins');
        $this->update_cache = null;
    }
}

// Initialize updater
new Skyweb_License_Updater();
