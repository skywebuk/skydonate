<?php
/**
 * Authenticator Base Class
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
 * The authenticator base class.
 *
 * This is used to define authentication functionality
 * for license validation.
 *
 * @since      1.0.0
 * @package    SkyDonate
 * @subpackage SkyDonate/includes
 * @author     Sky Web Design <shafiq6171@gmail.com>
 */
class Skydonate_Authenticator_Base {

    protected $server_url;

    public function __construct() {
        $this->server_url = 'https://skywebdesign.uk';
    }

    protected function send_request( $endpoint, $headers = [], $timeout = 10 ) {
        $url = trailingslashit( $this->server_url ) . ltrim( $endpoint, '/' );

        $response = wp_remote_get( $url, [
            'headers'     => $headers,
            'timeout'     => $timeout,
            'redirection' => 5,
        ] );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        return json_decode( $body, true );
    }
}

// Backwards compatibility alias
class_alias( 'Skydonate_Authenticator_Base', 'Skyweb_Authenticator_Base' );
