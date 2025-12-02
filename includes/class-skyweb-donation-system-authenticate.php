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

require_once SKYWEB_DONATION_SYSTEM_INCLUDES_PATH . '/class-skyweb-donation-system-authenticate-base.php';

/**
 * @deprecated Use Skyweb_License_Manager instead
 */
class Skyweb_Donation_System_Authenticate extends Skyweb_Authenticator_Base {

    /**
     * @deprecated Use skydonate_license() instead
     */
    public static function authenticateUser($username, $password) {
        // This method is no longer used in the new license system
        return false;
    }

    /**
     * @deprecated Use skydonate_license()->activate_license() instead
     */
    public static function authenticate_license($license_key) {
        if (function_exists('skydonate_license')) {
            $result = skydonate_license()->activate_license($license_key);
            if ($result['success']) {
                return $license_key;
            }
        }
        return false;
    }

    /**
     * @deprecated Use skydonate_license()->is_license_valid() instead
     */
    public static function setup_update_status($license_key) {
        if (function_exists('skydonate_license')) {
            return skydonate_license()->is_license_valid();
        }
        return false;
    }
}
