<?php
/**
 * DEPRECATED: This class is deprecated and will be removed in a future version.
 * Use the new Skyweb_License_Manager class instead.
 *
 * @link       https://skywebdesign.co.uk/
 * @since      1.0.0
 * @deprecated 2.0.0
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @deprecated Use Skyweb_License_Manager instead
 */
class Skyweb_Authenticator_Base {

    protected $server_url;

    public function __construct() {
        $this->server_url = 'https://skydonate.com';
    }

    protected function send_request($endpoint, $headers = [], $timeout = 10) {
        $url = trailingslashit($this->server_url) . ltrim($endpoint, '/');

        $response = wp_remote_get($url, [
            'headers'     => $headers,
            'timeout'     => $timeout,
            'redirection' => 5,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}
