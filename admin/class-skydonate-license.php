<?php
/**
 * SkyDonate License Admin
 *
 * Handles license page and AJAX submissions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'wp_ajax_skydonate_activate_license', array( $this, 'ajax_activate' ) );
        add_action( 'wp_ajax_skydonate_deactivate_license', array( $this, 'ajax_deactivate' ) );
        add_action( 'wp_ajax_skydonate_refresh_license', array( $this, 'ajax_refresh' ) );
    }

    /**
     * AJAX: Activate license
     */
    public function ajax_activate() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $key = sanitize_text_field( $_POST['license_key'] ?? '' );

        if ( empty( $key ) ) {
            wp_send_json_error( array( 'message' => 'Please enter a license key' ) );
        }

        $result = skydonate_license()->activate( $key );

        if ( ! empty( $result['success'] ) && $result['status'] === 'valid' ) {
            wp_send_json_success( array(
                'message' => 'License activated successfully!',
                'reload'  => true,
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? 'Activation failed',
            ) );
        }
    }

    /**
     * AJAX: Deactivate license
     */
    public function ajax_deactivate() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        skydonate_license()->deactivate();

        wp_send_json_success( array(
            'message' => 'License deactivated',
            'reload'  => true,
        ) );
    }

    /**
     * AJAX: Refresh license
     */
    public function ajax_refresh() {
        check_ajax_referer( 'skydonate_license_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $license = skydonate_license();
        $license->clear_cache();
        $result = $license->validate( null, true );

        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( array(
                'message' => 'License refreshed',
                'reload'  => true,
            ) );
        } else {
            wp_send_json_error( array(
                'message' => $result['message'] ?? 'Refresh failed',
            ) );
        }
    }

    /**
     * Get license info for template
     */
    public static function get_info() {
        $license = skydonate_license();
        $key = $license->get_key();
        $data = $license->get_data();
        $status = $data['status'] ?? 'inactive';

        // Mask key for display
        $masked = '';
        if ( $key ) {
            $masked = substr( $key, 0, 8 ) . str_repeat( '*', 20 ) . substr( $key, -4 );
        }

        return array(
            'key'          => $key,
            'masked_key'   => $masked,
            'status'       => $status,
            'is_valid'     => $status === 'valid',
            'data'         => $data,
            'features'     => $data['features'] ?? array(),
            'widgets'      => $data['widgets'] ?? array(),
            'layouts'      => $data['layouts'] ?? array(),
            'capabilities' => $data['capabilities'] ?? array(),
            'expires'      => $data['expires'] ?? '',
            'message'      => $data['message'] ?? '',
        );
    }

    /**
     * Get status badge HTML
     */
    public static function get_badge( $status ) {
        $badges = array(
            'valid'    => '<span class="skydonate-badge skydonate-badge--valid">Active</span>',
            'expired'  => '<span class="skydonate-badge skydonate-badge--expired">Expired</span>',
            'inactive' => '<span class="skydonate-badge skydonate-badge--inactive">Inactive</span>',
            'invalid'  => '<span class="skydonate-badge skydonate-badge--invalid">Invalid</span>',
            'error'    => '<span class="skydonate-badge skydonate-badge--error">Error</span>',
        );
        return $badges[ $status ] ?? $badges['inactive'];
    }

    /**
     * Format feature name
     */
    public static function format_name( $key ) {
        return ucwords( str_replace( '_', ' ', $key ) );
    }
}

// Initialize
new SkyDonate_License_Admin();
