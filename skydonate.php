<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. It also includes all dependencies, registers activation and
 * deactivation functions, and defines the main plugin initializer.
 *
 * @link              https://skywebdesign.co.uk/
 * @since             1.2.0
 * @package           Skyweb_Donation_System
 *
 * @wordpress-plugin
 * Plugin Name:       SkyDonate
 * Plugin URI:        https://skywebdesign.co.uk/
 * Description:       A secure, user-friendly donation system built to simplify and manage charitable contributions. Designed for speed, clarity, and full transparency.
 * Version:           1.2.0
 * Author:            Sky Web Design
 * Author URI:        https://skywebdesign.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       skydonate
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 */

//
// --------------------------------------------------------------
// COPYRIGHT NOTICE
// --------------------------------------------------------------
//
/*
© 2025 Sky Web Design. All rights reserved.
This plugin is proprietary and confidential.
It may not be copied, modified, distributed, or resold without written permission.
Unauthorized use will result in legal action.

Registered with CopyrightDepot.com | Ref No: 94904  
https://copyrightdepot.com/AfficheCopyrightsArchives.php?lang=EN&idcopy=94904

Registered with Safe Creative | Registration Code: 2505171770984  
https://www.safecreative.org/work/2505171770984-sky-web-donation-system
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || ( is_multisite() && in_array( 'woocommerce/woocommerce.php', array_flip( get_site_option( 'active_sitewide_plugins' ) ) ) ) ) {

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

function define_skyweb_donation_system() {
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_VERSION', '1.0.65' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_FILE', __FILE__ );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_BASE', plugin_basename( SKYWEB_DONATION_SYSTEM_FILE));
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_DIR_PATH', plugin_dir_path( SKYWEB_DONATION_SYSTEM_FILE) );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_INCLUDES_PATH', SKYWEB_DONATION_SYSTEM_DIR_PATH.'includes' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_ADMIN_PATH', SKYWEB_DONATION_SYSTEM_DIR_PATH.'admin' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_PUBLIC_PATH', SKYWEB_DONATION_SYSTEM_DIR_PATH.'public' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_URL', plugin_dir_url( SKYWEB_DONATION_SYSTEM_FILE ) );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_PUBLIC_ASSETS', SKYWEB_DONATION_SYSTEM_URL.'public' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_ADMIN_ASSETS', SKYWEB_DONATION_SYSTEM_URL.'admin/' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_ASSETS', SKYWEB_DONATION_SYSTEM_URL.'public' );
		define_skyweb_donation_system_constants( 'SKYWEB_DONATION_SYSTEM_OPTION_URL', admin_url('admin-post.php'));
}

function define_skyweb_donation_system_constants( $key, $value ) {
		if ( ! defined( $key ) ) {
			define( $key, $value );
		}
}

register_activation_hook( __FILE__, 'activate_skyweb_donation_system' );
register_deactivation_hook( __FILE__, 'deactivate_skyweb_donation_system' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-skyweb-donation-system.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_skyweb_donation_system() {
	define_skyweb_donation_system();
	$plugin = new Skyweb_Donation_System();
	$plugin->run();
	$GLOBALS['SKDS'] = $plugin;
	$GLOBALS['SKDS_notice'] = false;
}

 add_action( 'plugins_loaded', 'run_skyweb_donation_system',99 );
 
} else {
    add_action('admin_notices', 'skyweb_donation_system_plugin_required_notice');
    function skyweb_donation_system_plugin_required_notice(){
        global $current_screen;
        if($current_screen->parent_base == 'plugins'){
                echo '<div class="error"><p>Sky Web Donation System '.__(' requires WooCommerce to be activated in order to work. Please install and activate <a href="'.admin_url('plugin-install.php?tab=search&type=term&s=WooCommerce').'" target="_blank">WooCommerce</a> first.', 'skyweb-donation-system').'</p></div>';
        }
    }
    $plugin = plugin_basename(__FILE__);
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    if(is_plugin_active($plugin)){
          deactivate_plugins( $plugin);
    }
    if ( isset( $_GET['activate'] ) ){
		 unset( $_GET['activate'] );
	}
}