<?php
/**
 * SkyDonate License Admin Handler
 *
 * Manages license activation/deactivation and admin page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Admin {

    /**
     * Initialize
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_license_actions' ) );
        add_action( 'wp_ajax_skydonate_validate_license', array( $this, 'ajax_validate_license' ) );
        add_action( 'wp_ajax_skydonate_activate_license', array( $this, 'ajax_activate_license' ) );
        add_action( 'wp_ajax_skydonate_deactivate_license', array( $this, 'ajax_deactivate_license' ) );
        add_action( 'wp_ajax_skydonate_refresh_license', array( $this, 'ajax_refresh_license' ) );
    }

    /**
     * Handle license form submissions
     */
    public function handle_license_actions() {
        if ( ! isset( $_POST['skydonate_license_action'] ) ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['skydonate_license_nonce'] ?? '', 'skydonate_license_action' ) ) {
            return;
        }

        $action = sanitize_text_field( $_POST['skydonate_license_action'] );
        $license_key = sanitize_text_field( $_POST['skydonate_license_key'] ?? '' );

        $client = skydonate_license_client();
        $redirect_url = admin_url( 'admin.php?page=skydonation' );
        $message_type = '';
        $message = '';

        switch ( $action ) {
            case 'activate':
                if ( ! empty( $license_key ) ) {
                    $result = $client->activate_license( $license_key );
                    if ( ! empty( $result['success'] ) && $result['status'] === 'valid' ) {
                        $message_type = 'activated';
                        $message = 'success';
                    } else {
                        $message_type = 'error';
                        $message = urlencode( $result['message'] ?? __( 'Failed to activate license.', 'skydonate' ) );
                    }
                } else {
                    $message_type = 'error';
                    $message = urlencode( __( 'Please enter a license key.', 'skydonate' ) );
                }
                break;

            case 'deactivate':
                $saved_key = get_option( 'skydonate_license_key', '' );
                if ( ! empty( $saved_key ) ) {
                    $client->deactivate_license( $saved_key );
                    $message_type = 'deactivated';
                    $message = 'success';
                }
                break;

            case 'refresh':
                $saved_key = get_option( 'skydonate_license_key', '' );
                if ( ! empty( $saved_key ) ) {
                    $client->clear_cache();
                    $client->validate_license( $saved_key, true );
                    $message_type = 'refreshed';
                    $message = 'success';
                }
                $redirect_url = admin_url( 'admin.php?page=skydonation-license' );
                break;
        }

        // Redirect with message
        if ( $message_type ) {
            $redirect_url = add_query_arg( array(
                'skydonate_message' => $message_type,
                'skydonate_status'  => $message,
            ), $redirect_url );
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * AJAX: Validate license
     */
    public function ajax_validate_license() {
        check_ajax_referer( 'skydonate_license_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $license_key = sanitize_text_field( $_POST['license_key'] ?? '' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => 'License key is required' ) );
        }

        $client = skydonate_license_client();
        $result = $client->validate_license( $license_key, true );

        wp_send_json( $result );
    }

    /**
     * AJAX: Activate license
     */
    public function ajax_activate_license() {
        check_ajax_referer( 'skydonate_license_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $license_key = sanitize_text_field( $_POST['license_key'] ?? '' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => 'License key is required' ) );
        }

        $client = skydonate_license_client();
        $result = $client->activate_license( $license_key );

        wp_send_json( $result );
    }

    /**
     * AJAX: Deactivate license
     */
    public function ajax_deactivate_license() {
        check_ajax_referer( 'skydonate_license_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => 'No license to deactivate' ) );
        }

        $client = skydonate_license_client();
        $result = $client->deactivate_license( $license_key );

        wp_send_json( $result );
    }

    /**
     * AJAX: Refresh license
     */
    public function ajax_refresh_license() {
        check_ajax_referer( 'skydonate_license_action', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            wp_send_json_error( array( 'message' => 'No license found' ) );
        }

        $client = skydonate_license_client();
        $client->clear_cache();
        $result = $client->validate_license( $license_key, true );

        wp_send_json( $result );
    }

    /**
     * Get license info for display
     */
    public static function get_license_info() {
        $license_key = get_option( 'skydonate_license_key', '' );

        if ( empty( $license_key ) ) {
            return array(
                'has_license' => false,
                'status'      => 'inactive',
                'key'         => '',
                'masked_key'  => '',
                'data'        => null,
            );
        }

        $client = skydonate_license_client();
        $data = $client->validate_license( $license_key );

        // Mask the license key for display
        $masked_key = substr( $license_key, 0, 4 ) . str_repeat( '*', max( 0, strlen( $license_key ) - 8 ) ) . substr( $license_key, -4 );

        return array(
            'has_license' => true,
            'status'      => $data['status'] ?? 'unknown',
            'key'         => $license_key,
            'masked_key'  => $masked_key,
            'data'        => $data,
        );
    }

    /**
     * Get status badge HTML
     */
    public static function get_status_badge( $status ) {
        $badges = array(
            'valid'    => '<span class="license-badge license-badge--valid">Active</span>',
            'expired'  => '<span class="license-badge license-badge--expired">Expired</span>',
            'inactive' => '<span class="license-badge license-badge--inactive">Inactive</span>',
            'invalid'  => '<span class="license-badge license-badge--invalid">Invalid</span>',
            'error'    => '<span class="license-badge license-badge--error">Error</span>',
            'unknown'  => '<span class="license-badge license-badge--unknown">Unknown</span>',
        );

        return $badges[ $status ] ?? $badges['unknown'];
    }

    /**
     * Format feature name for display
     */
    public static function format_feature_name( $key ) {
        $key = str_replace( '_', ' ', $key );
        return ucwords( $key );
    }
}

// Initialize
new SkyDonate_License_Admin();
