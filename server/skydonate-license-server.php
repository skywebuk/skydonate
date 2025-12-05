<?php
/**
 * Plugin Name: SkyDonate License Server
 * Description: License management server for SkyDonate plugin
 * Version: 1.0.0
 * Author: Sky Web Design
 *
 * INSTALLATION:
 * 1. Upload this file to wp-content/plugins/ on your license server (skydonate.com)
 * 2. Activate the plugin
 * 3. Go to "Licenses" menu to create and manage license keys
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SkyDonate_License_Server {

    private $option_name = 'skydonate_licenses';

    public function __construct() {
        add_action( 'init', array( $this, 'handle_api_requests' ), 1 );
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
    }

    /**
     * Handle incoming API requests
     */
    public function handle_api_requests() {
        if ( isset( $_GET['sky_license_validate'] ) ) {
            $this->api_validate();
        }
        if ( isset( $_GET['sky_license_activate'] ) ) {
            $this->api_activate();
        }
        if ( isset( $_GET['sky_license_deactivate'] ) ) {
            $this->api_deactivate();
        }
    }

    /**
     * Get request data from JSON body
     */
    private function get_request_data() {
        $json = file_get_contents( 'php://input' );
        $data = json_decode( $json, true );

        if ( ! $data ) {
            $data = array(
                'license' => sanitize_text_field( $_POST['license'] ?? '' ),
                'domain'  => sanitize_text_field( $_POST['domain'] ?? '' ),
            );
        }

        return $data;
    }

    /**
     * Send JSON response and exit
     */
    private function send_json( $data ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        echo json_encode( $data, JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Get license from database
     */
    private function get_license( $key ) {
        $licenses = get_option( $this->option_name, array() );
        return $licenses[ $key ] ?? null;
    }

    /**
     * Update license in database
     */
    private function update_license( $key, $data ) {
        $licenses = get_option( $this->option_name, array() );
        $licenses[ $key ] = $data;
        update_option( $this->option_name, $licenses );
    }

    /**
     * API: Validate license
     */
    private function api_validate() {
        $req = $this->get_request_data();
        $key = $req['license'] ?? '';
        $domain = $req['domain'] ?? '';

        if ( empty( $key ) ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'error',
                'message' => 'License key required',
            ) );
        }

        $license = $this->get_license( $key );

        if ( ! $license ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'Invalid license key',
            ) );
        }

        // Check expiration
        if ( ! empty( $license['expires'] ) && strtotime( $license['expires'] ) < time() ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'expired',
                'message' => 'License has expired',
            ) );
        }

        // Check if domain is activated
        $domains = $license['domains'] ?? array();
        if ( ! empty( $domains ) && ! in_array( $domain, $domains, true ) ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'inactive',
                'message' => 'License not activated for this domain',
            ) );
        }

        $this->send_json( array(
            'success'      => true,
            'status'       => 'valid',
            'message'      => 'License is valid',
            'expires'      => $license['expires'] ?? '',
            'features'     => $license['features'] ?? $this->default_features(),
            'widgets'      => $license['widgets'] ?? $this->default_widgets(),
            'layouts'      => $license['layouts'] ?? $this->default_layouts(),
            'capabilities' => $license['capabilities'] ?? $this->default_capabilities(),
        ) );
    }

    /**
     * API: Activate license
     */
    private function api_activate() {
        $req = $this->get_request_data();
        $key = $req['license'] ?? '';
        $domain = $req['domain'] ?? '';

        if ( empty( $key ) || empty( $domain ) ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'error',
                'message' => 'License key and domain required',
            ) );
        }

        $license = $this->get_license( $key );

        if ( ! $license ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'Invalid license key',
            ) );
        }

        // Check expiration
        if ( ! empty( $license['expires'] ) && strtotime( $license['expires'] ) < time() ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'expired',
                'message' => 'License has expired',
            ) );
        }

        // Check activation limit
        $domains = $license['domains'] ?? array();
        $max = $license['max_sites'] ?? 1;

        if ( ! in_array( $domain, $domains, true ) ) {
            if ( count( $domains ) >= $max ) {
                $this->send_json( array(
                    'success' => false,
                    'status'  => 'limit_reached',
                    'message' => 'Activation limit reached (' . $max . ' sites)',
                ) );
            }

            // Add domain
            $domains[] = $domain;
            $license['domains'] = $domains;
            $this->update_license( $key, $license );
        }

        $this->send_json( array(
            'success'      => true,
            'status'       => 'valid',
            'message'      => 'License activated',
            'expires'      => $license['expires'] ?? '',
            'features'     => $license['features'] ?? $this->default_features(),
            'widgets'      => $license['widgets'] ?? $this->default_widgets(),
            'layouts'      => $license['layouts'] ?? $this->default_layouts(),
            'capabilities' => $license['capabilities'] ?? $this->default_capabilities(),
        ) );
    }

    /**
     * API: Deactivate license
     */
    private function api_deactivate() {
        $req = $this->get_request_data();
        $key = $req['license'] ?? '';
        $domain = $req['domain'] ?? '';

        if ( empty( $key ) || empty( $domain ) ) {
            $this->send_json( array(
                'success' => false,
                'status'  => 'error',
                'message' => 'License key and domain required',
            ) );
        }

        $license = $this->get_license( $key );

        if ( $license ) {
            $domains = $license['domains'] ?? array();
            $license['domains'] = array_values( array_filter( $domains, function( $d ) use ( $domain ) {
                return $d !== $domain;
            } ) );
            $this->update_license( $key, $license );
        }

        $this->send_json( array(
            'success' => true,
            'status'  => 'deactivated',
            'message' => 'License deactivated',
        ) );
    }

    /**
     * Default features
     */
    private function default_features() {
        return array(
            'recurring_donations' => true,
            'donation_goals'      => true,
            'donor_wall'          => true,
            'email_notifications' => true,
            'export_donations'    => true,
            'custom_amounts'      => true,
            'anonymous_donations' => true,
            'dedication_messages' => true,
            'multi_currency'      => true,
            'tax_receipts'        => true,
        );
    }

    /**
     * Default widgets
     */
    private function default_widgets() {
        return array(
            'donation_form'     => true,
            'donation_progress' => true,
            'recent_donors'     => true,
            'campaign_list'     => true,
            'donor_leaderboard' => true,
        );
    }

    /**
     * Default layouts
     */
    private function default_layouts() {
        return array(
            'donation_form'     => 'layout-3',
            'donation_progress' => 'layout-2',
            'recent_donors'     => 'layout-1',
        );
    }

    /**
     * Default capabilities
     */
    private function default_capabilities() {
        return array(
            'max_campaigns'      => -1,
            'max_donation_forms' => -1,
            'priority_support'   => true,
            'white_label'        => true,
            'api_access'         => true,
        );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'License Manager',
            'Licenses',
            'manage_options',
            'skydonate-licenses',
            array( $this, 'admin_page' ),
            'dashicons-admin-network',
            30
        );
    }

    /**
     * Admin page
     */
    public function admin_page() {
        // Handle form submissions
        if ( isset( $_POST['action'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'skydonate_license' ) ) {
            $this->handle_admin_action( $_POST['action'] );
        }

        $licenses = get_option( $this->option_name, array() );
        ?>
        <div class="wrap">
            <h1>SkyDonate License Manager</h1>

            <h2>Add New License</h2>
            <form method="post" style="background:#fff;padding:20px;border:1px solid #ccd0d4;max-width:600px;">
                <?php wp_nonce_field( 'skydonate_license' ); ?>
                <input type="hidden" name="action" value="add">
                <table class="form-table">
                    <tr>
                        <th>License Key</th>
                        <td>
                            <input type="text" name="license_key" class="regular-text"
                                   value="<?php echo 'SKY-' . strtoupper( wp_generate_password( 8, false ) ) . '-' . strtoupper( wp_generate_password( 8, false ) ) . '-' . strtoupper( wp_generate_password( 8, false ) ) . '-' . strtoupper( wp_generate_password( 8, false ) ); ?>"
                                   required>
                        </td>
                    </tr>
                    <tr>
                        <th>Expires</th>
                        <td>
                            <input type="datetime-local" name="expires"
                                   value="<?php echo date( 'Y-m-d\TH:i', strtotime( '+1 year' ) ); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>Max Sites</th>
                        <td>
                            <input type="number" name="max_sites" value="1" min="1" style="width:80px;">
                        </td>
                    </tr>
                </table>
                <p><button type="submit" class="button button-primary">Add License</button></p>
            </form>

            <h2 style="margin-top:30px;">Existing Licenses</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>License Key</th>
                        <th>Expires</th>
                        <th>Sites</th>
                        <th>Activated Domains</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $licenses ) ) : ?>
                        <tr><td colspan="5">No licenses found.</td></tr>
                    <?php else : ?>
                        <?php foreach ( $licenses as $key => $data ) : ?>
                            <tr>
                                <td><code style="font-size:12px;"><?php echo esc_html( $key ); ?></code></td>
                                <td><?php echo esc_html( $data['expires'] ?? 'Never' ); ?></td>
                                <td><?php echo count( $data['domains'] ?? array() ); ?> / <?php echo esc_html( $data['max_sites'] ?? 1 ); ?></td>
                                <td><?php echo esc_html( implode( ', ', $data['domains'] ?? array() ) ?: '-' ); ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field( 'skydonate_license' ); ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="license_key" value="<?php echo esc_attr( $key ); ?>">
                                        <button type="submit" class="button button-small" onclick="return confirm('Delete this license?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Handle admin actions
     */
    private function handle_admin_action( $action ) {
        $licenses = get_option( $this->option_name, array() );

        if ( $action === 'add' ) {
            $key = sanitize_text_field( $_POST['license_key'] ?? '' );
            if ( $key ) {
                $licenses[ $key ] = array(
                    'created'   => current_time( 'mysql' ),
                    'expires'   => sanitize_text_field( $_POST['expires'] ?? '' ),
                    'max_sites' => intval( $_POST['max_sites'] ?? 1 ),
                    'domains'   => array(),
                );
                update_option( $this->option_name, $licenses );
                echo '<div class="notice notice-success"><p>License added!</p></div>';
            }
        }

        if ( $action === 'delete' ) {
            $key = sanitize_text_field( $_POST['license_key'] ?? '' );
            unset( $licenses[ $key ] );
            update_option( $this->option_name, $licenses );
            echo '<div class="notice notice-info"><p>License deleted.</p></div>';
        }
    }
}

new SkyDonate_License_Server();
