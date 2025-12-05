<?php
/**
 * SkyDonate License Server API
 *
 * Add this code to your skydonate.com WordPress theme's functions.php
 * or create a simple plugin with it.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * License Server Handler
 */
class SkyDonate_License_Server {

    /**
     * Initialize
     */
    public function __construct() {
        add_action( 'init', array( $this, 'handle_license_requests' ), 1 );
    }

    /**
     * Handle incoming license requests
     */
    public function handle_license_requests() {
        // Validate license
        if ( isset( $_GET['sky_license_validate'] ) ) {
            $this->handle_validate();
        }

        // Activate license
        if ( isset( $_GET['sky_license_activate'] ) ) {
            $this->handle_activate();
        }

        // Deactivate license
        if ( isset( $_GET['sky_license_deactivate'] ) ) {
            $this->handle_deactivate();
        }
    }

    /**
     * Get request data
     */
    private function get_request_data() {
        $json = file_get_contents( 'php://input' );
        $data = json_decode( $json, true );

        if ( ! $data ) {
            // Try POST data
            $data = array(
                'license' => sanitize_text_field( $_POST['license'] ?? '' ),
                'domain'  => sanitize_text_field( $_POST['domain'] ?? '' ),
            );
        }

        return $data;
    }

    /**
     * Send JSON response
     */
    private function send_response( $data ) {
        header( 'Content-Type: application/json' );
        echo wp_json_encode( $data );
        exit;
    }

    /**
     * Validate license
     */
    private function handle_validate() {
        $data = $this->get_request_data();
        $license_key = $data['license'] ?? '';
        $domain = $data['domain'] ?? '';

        if ( empty( $license_key ) ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'License key is required.',
            ) );
        }

        // Look up license in database
        $license = $this->get_license( $license_key );

        if ( ! $license ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'Invalid license key.',
            ) );
        }

        // Check if expired
        if ( ! empty( $license['expires'] ) && strtotime( $license['expires'] ) < time() ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'expired',
                'message' => 'License has expired.',
            ) );
        }

        // Check domain activation
        $activated_domains = $license['domains'] ?? array();
        if ( ! empty( $activated_domains ) && ! in_array( $domain, $activated_domains, true ) ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'inactive',
                'message' => 'License is not activated for this domain.',
            ) );
        }

        // License is valid
        $this->send_response( array(
            'success'      => true,
            'status'       => 'valid',
            'features'     => $license['features'] ?? $this->get_default_features(),
            'widgets'      => $license['widgets'] ?? $this->get_default_widgets(),
            'layouts'      => $license['layouts'] ?? $this->get_default_layouts(),
            'capabilities' => $license['capabilities'] ?? $this->get_default_capabilities(),
            'expires'      => $license['expires'] ?? '',
            'message'      => 'License is valid.',
        ) );
    }

    /**
     * Activate license
     */
    private function handle_activate() {
        $data = $this->get_request_data();
        $license_key = $data['license'] ?? '';
        $domain = $data['domain'] ?? '';

        if ( empty( $license_key ) ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'License key is required.',
            ) );
        }

        if ( empty( $domain ) ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'error',
                'message' => 'Domain is required.',
            ) );
        }

        // Look up license
        $license = $this->get_license( $license_key );

        if ( ! $license ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'Invalid license key.',
            ) );
        }

        // Check if expired
        if ( ! empty( $license['expires'] ) && strtotime( $license['expires'] ) < time() ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'expired',
                'message' => 'License has expired.',
            ) );
        }

        // Check activation limit
        $activated_domains = $license['domains'] ?? array();
        $max_activations = $license['max_activations'] ?? 1;

        if ( ! in_array( $domain, $activated_domains, true ) ) {
            if ( count( $activated_domains ) >= $max_activations ) {
                $this->send_response( array(
                    'success' => false,
                    'status'  => 'limit_reached',
                    'message' => 'Activation limit reached. Please deactivate another site first.',
                ) );
            }

            // Add domain to activated list
            $activated_domains[] = $domain;
            $this->update_license_domains( $license_key, $activated_domains );
        }

        // Return success
        $this->send_response( array(
            'success'      => true,
            'status'       => 'valid',
            'features'     => $license['features'] ?? $this->get_default_features(),
            'widgets'      => $license['widgets'] ?? $this->get_default_widgets(),
            'layouts'      => $license['layouts'] ?? $this->get_default_layouts(),
            'capabilities' => $license['capabilities'] ?? $this->get_default_capabilities(),
            'expires'      => $license['expires'] ?? '',
            'message'      => 'License activated successfully.',
        ) );
    }

    /**
     * Deactivate license
     */
    private function handle_deactivate() {
        $data = $this->get_request_data();
        $license_key = $data['license'] ?? '';
        $domain = $data['domain'] ?? '';

        if ( empty( $license_key ) || empty( $domain ) ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'error',
                'message' => 'License key and domain are required.',
            ) );
        }

        // Look up license
        $license = $this->get_license( $license_key );

        if ( ! $license ) {
            $this->send_response( array(
                'success' => false,
                'status'  => 'invalid',
                'message' => 'Invalid license key.',
            ) );
        }

        // Remove domain from activated list
        $activated_domains = $license['domains'] ?? array();
        $activated_domains = array_filter( $activated_domains, function( $d ) use ( $domain ) {
            return $d !== $domain;
        } );

        $this->update_license_domains( $license_key, array_values( $activated_domains ) );

        $this->send_response( array(
            'success' => true,
            'status'  => 'deactivated',
            'message' => 'License deactivated successfully.',
        ) );
    }

    /**
     * Get license from database
     */
    private function get_license( $license_key ) {
        // Get all licenses from options
        $licenses = get_option( 'skydonate_licenses', array() );

        return $licenses[ $license_key ] ?? null;
    }

    /**
     * Update license domains
     */
    private function update_license_domains( $license_key, $domains ) {
        $licenses = get_option( 'skydonate_licenses', array() );

        if ( isset( $licenses[ $license_key ] ) ) {
            $licenses[ $license_key ]['domains'] = $domains;
            update_option( 'skydonate_licenses', $licenses );
        }
    }

    /**
     * Default features (all enabled)
     */
    private function get_default_features() {
        return array(
            'recurring_donations'   => true,
            'donation_goals'        => true,
            'donor_wall'            => true,
            'email_notifications'   => true,
            'export_donations'      => true,
            'custom_amounts'        => true,
            'anonymous_donations'   => true,
            'dedication_messages'   => true,
            'multi_currency'        => true,
            'tax_receipts'          => true,
        );
    }

    /**
     * Default widgets
     */
    private function get_default_widgets() {
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
    private function get_default_layouts() {
        return array(
            'donation_form'     => 'layout-3',
            'donation_progress' => 'layout-2',
            'recent_donors'     => 'layout-1',
        );
    }

    /**
     * Default capabilities
     */
    private function get_default_capabilities() {
        return array(
            'max_campaigns'            => -1,
            'max_donation_forms'       => -1,
            'priority_support'         => true,
            'white_label'              => true,
            'api_access'               => true,
            'allow_remote_functions'   => false,
        );
    }
}

// Initialize
new SkyDonate_License_Server();


/**
 * Admin page to manage licenses (add to your admin menu)
 */
function skydonate_license_admin_menu() {
    add_menu_page(
        'License Manager',
        'Licenses',
        'manage_options',
        'skydonate-licenses',
        'skydonate_license_admin_page',
        'dashicons-admin-network',
        30
    );
}
add_action( 'admin_menu', 'skydonate_license_admin_menu' );

/**
 * License admin page
 */
function skydonate_license_admin_page() {
    // Handle form submission
    if ( isset( $_POST['add_license'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'add_license' ) ) {
        $license_key = sanitize_text_field( $_POST['license_key'] );
        $expires = sanitize_text_field( $_POST['expires'] );
        $max_activations = intval( $_POST['max_activations'] );

        if ( ! empty( $license_key ) ) {
            $licenses = get_option( 'skydonate_licenses', array() );
            $licenses[ $license_key ] = array(
                'created'         => current_time( 'mysql' ),
                'expires'         => $expires,
                'max_activations' => $max_activations > 0 ? $max_activations : 1,
                'domains'         => array(),
                'features'        => null, // Use defaults
                'widgets'         => null,
                'layouts'         => null,
                'capabilities'    => null,
            );
            update_option( 'skydonate_licenses', $licenses );
            echo '<div class="notice notice-success"><p>License added successfully!</p></div>';
        }
    }

    // Handle delete
    if ( isset( $_GET['delete_license'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'delete_license' ) ) {
        $license_key = sanitize_text_field( $_GET['delete_license'] );
        $licenses = get_option( 'skydonate_licenses', array() );
        unset( $licenses[ $license_key ] );
        update_option( 'skydonate_licenses', $licenses );
        echo '<div class="notice notice-info"><p>License deleted.</p></div>';
    }

    $licenses = get_option( 'skydonate_licenses', array() );
    ?>
    <div class="wrap">
        <h1>SkyDonate License Manager</h1>

        <h2>Add New License</h2>
        <form method="post">
            <?php wp_nonce_field( 'add_license' ); ?>
            <table class="form-table">
                <tr>
                    <th>License Key</th>
                    <td>
                        <input type="text" name="license_key" class="regular-text"
                               value="SKY-<?php echo strtoupper( wp_generate_password( 8, false ) ); ?>-<?php echo strtoupper( wp_generate_password( 8, false ) ); ?>-<?php echo strtoupper( wp_generate_password( 8, false ) ); ?>-<?php echo strtoupper( wp_generate_password( 8, false ) ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th>Expires</th>
                    <td>
                        <input type="date" name="expires" value="<?php echo date( 'Y-m-d', strtotime( '+1 year' ) ); ?>" />
                    </td>
                </tr>
                <tr>
                    <th>Max Activations</th>
                    <td>
                        <input type="number" name="max_activations" value="1" min="1" />
                    </td>
                </tr>
            </table>
            <p><button type="submit" name="add_license" class="button button-primary">Add License</button></p>
        </form>

        <h2>Existing Licenses</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>License Key</th>
                    <th>Expires</th>
                    <th>Activations</th>
                    <th>Domains</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $licenses ) ) : ?>
                    <tr><td colspan="5">No licenses found.</td></tr>
                <?php else : ?>
                    <?php foreach ( $licenses as $key => $license ) : ?>
                        <tr>
                            <td><code><?php echo esc_html( $key ); ?></code></td>
                            <td><?php echo esc_html( $license['expires'] ?? 'Never' ); ?></td>
                            <td><?php echo count( $license['domains'] ?? array() ); ?> / <?php echo esc_html( $license['max_activations'] ?? 1 ); ?></td>
                            <td><?php echo esc_html( implode( ', ', $license['domains'] ?? array() ) ?: '-' ); ?></td>
                            <td>
                                <a href="<?php echo wp_nonce_url( add_query_arg( 'delete_license', $key ), 'delete_license' ); ?>"
                                   onclick="return confirm('Delete this license?');" class="button button-small">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
