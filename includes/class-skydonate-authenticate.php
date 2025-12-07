<?php
/**
 * Authenticate Class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://skywebdesign.co.uk/
 * @since      1.0.0
 *
 * @package    SkyDonate
 * @subpackage SkyDonate/includes
 */

/**
 * The authenticator class.
 *
 * This is used to define authentication functionality
 * for license validation.
 *
 * @since      1.0.0
 * @package    SkyDonate
 * @subpackage SkyDonate/includes
 * @author     Sky Web Design <shafiq6171@gmail.com>
 */
include_once SKYWEB_DONATION_SYSTEM_INCLUDES_PATH . '/class-skydonate-authenticate-base.php';

class Skydonate_Authenticate extends Skydonate_Authenticator_Base {

    public static function authenticateUser( $username, $password ) {
        $instance = new self();
        $response = $instance->send_request(
            '/wp-json/resource/v1/authenticate',
            [
                'x-username' => $username,
                'x-password' => $password,
            ]
        );

        if ( $response && isset( $response[0]['user'] ) && ! empty( $response[0]['user'] ) ) {
            return true;
        }

        return false;
    }

    public static function authenticate_license( $license_key ) {
        $instance = new self();
        $response = $instance->send_request(
            '/wp-json/resource/v1/customer/validator',
            [
                'x-api-key'    => $license_key,
                'x-client-url' => site_url(),
            ]
        );

        if ( $response ) {
            if ( isset( $response['data']['status'] ) && $response['data']['status'] == 400 ) {
                extend_plugin_pro_feauture();
                return false;
            }

            if ( isset( $response[0]['api_key'] ) ) {
                $zip_url = isset( $response[0]['zip_url'] ) ? $response[0]['zip_url'] : '';
                $setup   = isset( $response[0]['setup'] ) ? $response[0]['setup'] : '';
                $args    = array(
                    'zip_url' => $zip_url,
                    'setup'   => $setup
                );

                extend_plugin_pro_feauture( $args );
                return $response[0]['api_key'];
            }

            return true;
        }
        return false;
    }

    public static function setup_update_status( $license_key ) {
        $instance = new self();
        $response = $instance->send_request(
            '/wp-json/resource/v1/update-status',
            [
                'x-api-key'    => $license_key,
                'x-client-url' => site_url(),
            ]
        );

        if ( $response ) {
            if ( isset( $response[0]['status'] ) && $response[0]['status'] == 1 && isset( $response[0]['update_status'] ) ) {
                $update_status = isset( $response[0]['update_status'] ) ? $response[0]['update_status'] : '';
                $status        = isset( $response[0]['status'] ) ? $response[0]['status'] : '';
                if ( $status ) {
                    if ( $update_status ) {
                        if ( ! get_option( 'license_update_status' ) || get_option( 'license_update_status' ) != $update_status ) {
                            self::authenticate_license( $license_key );
                        }
                        update_option( 'license_update_status', $update_status );
                    }
                    return $update_status;
                }
            }
        }
        return false;
    }
}

// Backwards compatibility alias
class_alias( 'Skydonate_Authenticate', 'Skyweb_Donation_System_Authenticate' );
