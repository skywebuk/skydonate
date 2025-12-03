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
class Skyweb_Donation_System {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Skyweb_Donation_System_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SKYWEB_DONATION_SYSTEM_VERSION' ) ) {
			$this->version = SKYWEB_DONATION_SYSTEM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'skyweb-donation-system';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->verification();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Skyweb_Donation_System_Loader. Orchestrates the hooks of the plugin.
	 * - Skyweb_Donation_System_i18n. Defines internationalization functionality.
	 * - Skyweb_Donation_System_Admin. Defines all hooks for the admin area.
	 * - Skyweb_Donation_System_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {


		$this->include_file('includes/class-skyweb-donation-snippet-functions.php');

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		$this->include_file('includes/class-skyweb-donation-system-loader.php');
		$this->include_file('includes/class-skyweb-donation-system-authenticate.php');
		
		

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		$this->include_file('includes/class-skyweb-donation-system-i18n.php');

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		$this->include_file( 'admin/class-skyweb-donation-system-admin.php');
		$this->include_file('admin/class-skyweb-donation-system-settings.php');
		$this->include_file('admin/class-skyweb-donation-system-update.php');
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		$this->include_file('public/class-skyweb-donation-system-public.php');
		
		$this->include_file('includes/functions.php');
		$this->include_file('includes/class-skyweb-functions.php');
		$this->include_file('includes/class-wc-custom-donation-settings.php');
		$this->include_file('includes/class-skyweb-metabox.php');
		$this->include_file('includes/class-skyweb-donation-shortcode.php');
		$this->include_file('includes/class-wc-donation-fees.php');
		$this->include_file('includes/class-skyweb-extra-donation.php');


		if(get_option('skyweb_currency_changer_enabled', 0) == 1){
			$this->include_file('includes/class-skyweb-currency.php');
			new Skyweb_Currency_Changer();
		}
		


		if(get_option('setup_enable_notification') === '1' && !empty(get_option('wc_notification_item_select', []))){
			$this->include_file('includes/class-skyweb-notification.php');
		}
		if (is_admin()) {
			if (!class_exists('WC_Custom_Donation_Settings')) {
				new WC_Custom_Donation_Settings();
			}
			if (!class_exists('WC_Donation_Fees')) {
				new WC_Donation_Fees();
			}
		}

		if (sky_status_check('enable_sky_donations_module')) {
			$this->include_file('includes/class-wc-custom-donation-options.php');
			$this->include_file('includes/class-wc-field-visibility.php');

			$this->conditionally_initialize_class('WC_Custom_Donation_Options');
			$this->conditionally_initialize_class('WC_Field_Visibility');
		}

		$this->include_file('includes/class-skyweb-donation-gift-aid.php');

		if (get_option('address_autoload_status', 0) == 1) {
			$this->include_file('includes/class-skyweb-donation-address-autoload.php');
		}

		if (sky_status_check('recent_donation_list_with_country')) {
			$this->include_file('includes/class-wc-recent-donations.php');
			$this->conditionally_initialize_class('WC_Recent_Donations');
		}
		if (sky_status_check('auto_complete_processing')) {
			$this->include_file('includes/class-wc-auto-complete-processing.php');
			$this->conditionally_initialize_class('WC_Auto_Complete_Processing');
		}
		if (sky_status_check('enable_donation_goal')) {
			$this->include_file('includes/class-wc-donation-goal.php');
			$this->conditionally_initialize_class('WC_Donation_Goal');
		}
		if (sky_status_check('enable_title_prefix')) {
			$this->include_file('includes/class-wc-title-prefix.php');
			$this->conditionally_initialize_class('WC_Title_Prefix');
		}
		if ( class_exists( 'Elementor\Plugin' ) ){
			$this->include_file('includes/class-skyweb-donation-addons.php');
			$this->include_file('includes/class-skyweb-icon-manager.php');
		}
		
		$this->loader = new Skyweb_Donation_System_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Skyweb_Donation_System_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Skyweb_Donation_System_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Skyweb_Donation_System_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu');
		$this->loader->add_action( 'admin_dashboard_menu_tabs', $plugin_admin, 'admin_dashboard_menu_tabs' );
		$this->loader->add_action( 'admin_init', $plugin_admin,'register_elementor_widgets' );
		$this->loader->add_action( 'skyweb_donation_system_menus', $plugin_admin,'skyweb_donation_system_menus' );
		$this->loader->add_action( 'skyweb_donation_system_menu_array', $plugin_admin,'skyweb_donation_system_menu_array' );
		$this->loader->add_filter( 'skyweb_general_settings_tabs', $plugin_admin,'skyweb_general_settings_tabs' );
		$this->loader->add_filter( 'skyweb_general_settings_fields', $plugin_admin,'skyweb_general_settings_fields' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'skyweb_save_settings_fields' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'validation_check' );
		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Skyweb_Donation_System_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter('body_class', $plugin_public, 'add_checkout_custom_style_class');
		//if(sky_status_check('enable_custom_login_form')){}
			$this->loader->add_filter( 'woocommerce_locate_template',$plugin_public, 'sky_donation_woocommerce_myaccount_login_template', 10, 3 );
		if(sky_status_check('enable_custom_login_form')){
               $this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'sky_donation_woocommerce_myaccount_custom_login_template', 10, 3 );
         }
		$this->loader->add_filter( 'woocommerce_hidden_order_itemmeta',$plugin_public, 'custom_woocommerce_hidden_order_itemmeta' );
		$this->loader->add_filter( 'woocommerce_order_item_get_formatted_meta_data', $plugin_public, 'custom_hide_order_item_meta', 10, 2 );
		$this->loader->add_filter( 'woocommerce_order_item_display_meta_key', $plugin_public, 'custom_hide_order_item_meta_in_emails', 10, 3 );
		$this->loader->add_action( 'woocommerce_after_order_itemmeta', $plugin_public,'display_custom_order_item_meta', 10, 3 );
		$this->loader->add_action( 'woocommerce_saved_order_items', $plugin_public, 'save_custom_order_item_meta', 10, 2 );
		$this->loader->add_action('wp_loaded',  $plugin_public, 'remove_woocommerce_widget_shopping_cart_total', 10, 2 );
	}
	public function load_plugin_template( $content_path ) {
		if ( file_exists( $content_path ) ) {
			include $content_path;
		} else {
			/* translators: %s: file path */
			$tmr_notice = sprintf( esc_html__( 'Unable to locate file at location "%s". Some features may not work properly in this plugin. Please contact us!', 'skyweb-donation-system' ), $content_path );
			$this->plugin_admin_notice( $tmr_notice, 'error' );
		}
	}
	public static function plugin_admin_notice( $tmr_message, $type = 'error' ) {

		$tmr_classes = 'notice ';

		switch ( $type ) {
			
			case 'update':
				$tmr_classes .= 'updated is-dismissible';
				break;

			case 'update-nag':
				$tmr_classes .= 'update-nag is-dismissible';
				break;

			case 'success':
				$tmr_classes .= 'notice-success is-dismissible';
				break;

			default:
				$tmr_classes .= 'notice-error is-dismissible';
		}

		$tmr_notice  = '<div class="' . esc_attr( $tmr_classes ) . 'errorr">';
		$tmr_notice .= '<p>' . esc_html( $tmr_message ) . '</p>';
		$tmr_notice .= '</div>';

		echo wp_kses_post( $tmr_notice );
	}
	
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Skyweb_Donation_System_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	private function include_file($file) {
		$filepath = SKYWEB_DONATION_SYSTEM_DIR_PATH. $file;
		if (file_exists($filepath)) {
			require_once $filepath;
		}
	}
	private function conditionally_initialize_class($class) {
		if (class_exists($class)) {
			new $class();
		}
	}
	public function verification(){
		
	}

}
