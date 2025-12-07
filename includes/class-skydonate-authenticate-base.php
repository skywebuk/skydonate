<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://skywebdesign.co.uk/
 * @since      1.0.0
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/includes
 * @author     Sky Web Design <shafiq6171@gmail.com>
 */

class Skyweb_Authenticator_Base {

    protected $server_url;

    public function __construct() {
        $this->server_url = 'https://skywebdesign.uk';
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