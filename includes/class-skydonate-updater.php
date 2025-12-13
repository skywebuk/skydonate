<?php
/**
 * SkyDonate Plugin Updater
 *
 * Handles plugin updates via the license server
 *
 * @package SkyDonate
 * @version 2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_Updater {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * License client instance
     */
    private $license;

    /**
     * Plugin slug
     */
    private $plugin_slug = 'skydonate';

    /**
     * Plugin basename
     */
    private $plugin_basename;

    /**
     * Plugin version
     */
    private $plugin_version;

    /**
     * Update cache key
     */
    private $cache_key = 'skydonate_update_info';

    /**
     * Cache duration (6 hours)
     */
    private $cache_duration = 21600;

    /**
     * Get singleton instance
     */
    public static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Set plugin info
        $this->plugin_basename = defined( 'SKYDONATE_PLUGIN_BASE' ) ? SKYDONATE_PLUGIN_BASE : 'skydonate/skydonate.php';
        $this->plugin_version = defined( 'SKYDONATE_VERSION' ) ? SKYDONATE_VERSION : '1.0.0';

        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Check for updates
        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );

        // Provide update info
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );

        // After update cleanup
        add_action( 'upgrader_process_complete', array( $this, 'after_update' ), 10, 2 );

        // Add update message in plugins list
        add_action( 'in_plugin_update_message-' . $this->plugin_basename, array( $this, 'update_message' ), 10, 2 );

        // Check authentication before download
        add_filter( 'upgrader_pre_download', array( $this, 'check_download_permission' ), 10, 3 );
    }

    /**
     * Get license client instance
     */
    private function get_license() {
        if ( $this->license === null ) {
            if ( function_exists( 'skydonate_license' ) ) {
                $this->license = skydonate_license();
            }
        }
        return $this->license;
    }

    /**
     * Check if auto-updates are allowed
     */
    public function can_auto_update() {
        $license = $this->get_license();

        if ( ! $license ) {
            return false;
        }

        // Must have active license with allow_auto_updates capability
        if ( ! $license->is_active() ) {
            return false;
        }

        return $license->has_capability( 'allow_auto_updates' );
    }

    /**
     * Check for plugin updates
     *
     * @param object $transient Update transient data
     * @return object Modified transient data
     */
    public function check_for_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        // Check if auto-updates are allowed
        if ( ! $this->can_auto_update() ) {
            return $transient;
        }

        // Get update info (cached)
        $update_info = $this->get_update_info();

        if ( ! $update_info || empty( $update_info['update_available'] ) ) {
            return $transient;
        }

        // Compare versions
        $new_version = $update_info['version'] ?? '';
        if ( empty( $new_version ) || version_compare( $this->plugin_version, $new_version, '>=' ) ) {
            return $transient;
        }

        // Build update response object
        $response = new stdClass();
        $response->id = $this->plugin_slug;
        $response->slug = $this->plugin_slug;
        $response->plugin = $this->plugin_basename;
        $response->new_version = $new_version;
        $response->url = $update_info['homepage'] ?? 'https://skydonate.com';
        $response->package = $this->get_download_url();
        $response->icons = $update_info['icons'] ?? array();
        $response->banners = $update_info['banners'] ?? array();
        $response->banners_rtl = array();
        $response->tested = $update_info['tested'] ?? '';
        $response->requires_php = $update_info['requires_php'] ?? '7.2';
        $response->requires = $update_info['requires'] ?? '5.0';

        // Add compatibility info
        if ( isset( $update_info['upgrade_notice'] ) ) {
            $response->upgrade_notice = $update_info['upgrade_notice'];
        }

        $transient->response[ $this->plugin_basename ] = $response;

        return $transient;
    }

    /**
     * Provide plugin info for the update details popup
     *
     * @param mixed  $result  Default result
     * @param string $action  API action
     * @param object $args    Request arguments
     * @return mixed Plugin info object or original result
     */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || $args->slug !== $this->plugin_slug ) {
            return $result;
        }

        // Get update info
        $update_info = $this->get_update_info( true );

        if ( ! $update_info ) {
            return $result;
        }

        // Build plugin info object
        $info = new stdClass();
        $info->name = $update_info['name'] ?? 'SkyDonate';
        $info->slug = $this->plugin_slug;
        $info->version = $update_info['version'] ?? $this->plugin_version;
        $info->author = $update_info['author'] ?? '<a href="https://skywebdesign.co.uk">Sky Web Design</a>';
        $info->homepage = $update_info['homepage'] ?? 'https://skydonate.com';
        $info->requires = $update_info['requires'] ?? '5.0';
        $info->tested = $update_info['tested'] ?? '';
        $info->requires_php = $update_info['requires_php'] ?? '7.2';
        $info->downloaded = $update_info['downloaded'] ?? 0;
        $info->last_updated = $update_info['last_updated'] ?? '';
        $info->sections = array(
            'description' => $update_info['description'] ?? '',
            'installation' => $update_info['installation'] ?? '',
            'changelog' => $update_info['changelog'] ?? '',
        );
        $info->download_link = $this->get_download_url();
        $info->icons = $update_info['icons'] ?? array();
        $info->banners = $update_info['banners'] ?? array();

        return $info;
    }

    /**
     * Get update info from license server
     *
     * @param bool $force Force refresh
     * @return array|null Update info or null
     */
    private function get_update_info( $force = false ) {
        // Check cache first
        if ( ! $force ) {
            $cached = get_transient( $this->cache_key );
            if ( $cached !== false ) {
                return $cached;
            }
        }

        $license = $this->get_license();
        if ( ! $license ) {
            return null;
        }

        // Get update info from license server
        $result = $license->check_update();

        if ( empty( $result ) || ! isset( $result['success'] ) ) {
            return null;
        }

        // Cache the result
        set_transient( $this->cache_key, $result, $this->cache_duration );

        return $result;
    }

    /**
     * Get download URL for updates
     *
     * @return string|false Download URL or false
     */
    private function get_download_url() {
        $license = $this->get_license();
        if ( ! $license ) {
            return false;
        }

        return $license->get_download_url();
    }

    /**
     * Check download permission before allowing update
     *
     * @param mixed  $reply   Default reply
     * @param string $package Download URL
     * @param object $upgrader Upgrader instance
     * @return mixed Reply or WP_Error
     */
    public function check_download_permission( $reply, $package, $upgrader ) {
        // Only check for our plugin
        if ( strpos( $package, 'sky_license_download' ) === false ) {
            return $reply;
        }

        // Verify license is valid
        $license = $this->get_license();
        if ( ! $license || ! $license->is_active() ) {
            return new WP_Error(
                'skydonate_license_required',
                __( 'A valid SkyDonate license is required to download updates.', 'skydonate' )
            );
        }

        // Check auto-update capability
        if ( ! $license->has_capability( 'allow_auto_updates' ) ) {
            return new WP_Error(
                'skydonate_updates_disabled',
                __( 'Your license does not include automatic updates. Please upgrade your license.', 'skydonate' )
            );
        }

        return $reply;
    }

    /**
     * Display update message in plugins list
     *
     * @param array  $plugin_data Plugin data
     * @param object $response    Update response
     */
    public function update_message( $plugin_data, $response ) {
        $license = $this->get_license();

        // Show message if license is not valid
        if ( ! $license || ! $license->is_active() ) {
            echo '<br /><strong>' . esc_html__( 'A valid license is required to update this plugin.', 'skydonate' ) . '</strong> ';
            printf(
                '<a href="%s">%s</a>',
                esc_url( admin_url( 'admin.php?page=skydonate-license' ) ),
                esc_html__( 'Activate license', 'skydonate' )
            );
            return;
        }

        // Show message if auto-updates not allowed
        if ( ! $license->has_capability( 'allow_auto_updates' ) ) {
            echo '<br /><strong>' . esc_html__( 'Your license does not include automatic updates.', 'skydonate' ) . '</strong> ';
            echo '<a href="https://skydonate.com/upgrade" target="_blank" rel="noopener">';
            echo esc_html__( 'Upgrade your license', 'skydonate' );
            echo '</a>';
            return;
        }

        // Show upgrade notice if available
        if ( isset( $response->upgrade_notice ) && ! empty( $response->upgrade_notice ) ) {
            echo '<br /><strong>' . esc_html__( 'Upgrade Notice:', 'skydonate' ) . '</strong> ';
            echo wp_kses_post( $response->upgrade_notice );
        }
    }

    /**
     * After update cleanup
     *
     * @param object $upgrader   Upgrader instance
     * @param array  $hook_extra Extra arguments
     */
    public function after_update( $upgrader, $hook_extra ) {
        if (
            isset( $hook_extra['action'] ) && $hook_extra['action'] === 'update' &&
            isset( $hook_extra['type'] ) && $hook_extra['type'] === 'plugin' &&
            isset( $hook_extra['plugins'] ) && is_array( $hook_extra['plugins'] )
        ) {
            foreach ( $hook_extra['plugins'] as $plugin ) {
                if ( $plugin === $this->plugin_basename ) {
                    // Clear update cache
                    delete_transient( $this->cache_key );

                    // Refresh license data
                    $license = $this->get_license();
                    if ( $license ) {
                        $license->clear_cache();
                    }

                    // Fire action for other plugins/themes
                    do_action( 'skydonate_plugin_updated' );

                    break;
                }
            }
        }
    }

    /**
     * Clear update cache
     */
    public function clear_cache() {
        delete_transient( $this->cache_key );
    }

    /**
     * Force check for updates
     *
     * @return array|null Update info
     */
    public function force_check() {
        $this->clear_cache();
        return $this->get_update_info( true );
    }

    /**
     * Get current plugin version
     *
     * @return string Plugin version
     */
    public function get_version() {
        return $this->plugin_version;
    }

    /**
     * Check if update is available
     *
     * @return bool True if update available
     */
    public function is_update_available() {
        if ( ! $this->can_auto_update() ) {
            return false;
        }

        $update_info = $this->get_update_info();
        if ( ! $update_info || empty( $update_info['update_available'] ) ) {
            return false;
        }

        $new_version = $update_info['version'] ?? '';
        return ! empty( $new_version ) && version_compare( $this->plugin_version, $new_version, '<' );
    }

    /**
     * Get available version
     *
     * @return string|null Available version or null
     */
    public function get_available_version() {
        $update_info = $this->get_update_info();
        return $update_info['version'] ?? null;
    }

    /**
     * Get changelog
     *
     * @return string|null Changelog or null
     */
    public function get_changelog() {
        $update_info = $this->get_update_info();
        return $update_info['changelog'] ?? null;
    }
}

/**
 * Get updater instance
 *
 * @return SkyDonate_Updater
 */
function skydonate_updater() {
    return SkyDonate_Updater::instance();
}
