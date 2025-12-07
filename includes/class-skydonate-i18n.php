<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://skywebdesign.co.uk/
 * @since      1.0.0
 *
 * @package    SkyDonate
 * @subpackage SkyDonate/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    SkyDonate
 * @subpackage SkyDonate/includes
 * @author     Sky Web Design <shafiq6171@gmail.com>
 */
class Skydonate_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'skydonate',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}

// Backwards compatibility alias
class_alias( 'Skydonate_i18n', 'Skyweb_Donation_System_i18n' );
