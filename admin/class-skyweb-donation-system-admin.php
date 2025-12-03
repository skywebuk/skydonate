<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://skywebdesign.co.uk/
 * @since      1.0.0
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Skyweb_Donation_System
 * @subpackage Skyweb_Donation_System/admin
 * @author     Sky Web Design <shafiq6171@gmail.com>
 */
class Skyweb_Donation_System_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Skyweb_Donation_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Skyweb_Donation_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		
		//wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], $this->version );
		wp_enqueue_style( 'skydonation-login-style', 'https://skywebdesign.uk/auth/custom-login-style.css', [], $this->version );
		wp_enqueue_style( 'html-fields', plugin_dir_url( __FILE__ ) . 'css/html-fields.css', [], $this->version );
		wp_enqueue_style( 'skydonation-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', [], $this->version );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/skyweb-donation-system-admin3.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Skyweb_Donation_System_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Skyweb_Donation_System_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// Replace 'toplevel_page_skydonation' with your actual admin menu slug if different
		if ( isset($_GET['tab'], $_GET['page']) && $_GET['tab'] === 'colors' && $_GET['page'] === 'skydonation' ) {
			// Enqueue WP color picker CSS and JS
			wp_enqueue_style('wp-color-picker');
			wp_enqueue_script(
				'skydonation-color-picker',
				plugin_dir_url(__FILE__) . 'js/color-picker.js', // your JS file
				['wp-color-picker'],
				'1.0.0', // or $this->version if inside a class
				true
			);
		}

		// Enqueue Chart.js for dashboard
		$current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
		if ($current_page === 'skydonation-dashboard' || $current_page === 'skydonation') {
			wp_enqueue_script(
				'chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
				[],
				'4.4.0',
				true
			);
		}

		wp_enqueue_style('select2');
		wp_enqueue_script('select2');
		wp_enqueue_script( 'skydonation-admin-script', plugin_dir_url( __FILE__ ) . 'js/admin-script.js', [ 'jquery' ], $this->version, true );
		wp_localize_script( 'skydonation-admin-script', 'skydonation_setting', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'skydonation_settings_nonce' ),
		]);

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/skyweb-donation-system-admin.js', array( 'jquery' ), $this->version, true );

	}
	public function add_admin_menu(){
			$parent_slug = 'skydonation-licenses';
			$callback = 'licenses_page_content';
			if(license_authenticate()){
				$callback = 'general_page_content';
				
				$parent_slug = 'skydonation';
			}
			add_menu_page(
				esc_html__( 'SkyDonate', 'skydonation' ),
				esc_html__( 'SkyDonate', 'skydonation' ),
				'manage_options',
				$parent_slug,
				[ $this, $callback],
				'dashicons-skydonation ',
				20
			);
			if(license_authenticate()){
				do_action('skyweb_donation_system_menus',$parent_slug);
			}
	}
	public function admin_dashboard_menu_tabs($current_page){
		$sub_menus = array();
		if(license_authenticate()){
			$sub_menus = apply_filters('skyweb_donation_system_menu_array', array());
		}
		if(!empty($sub_menus)){
		?>
		<ul class="skydonation-navigation-menu">
			<?php
			foreach ($sub_menus as $sub_menu) {
				$page_slug 	= (isset($sub_menu['page_slug']) && !empty($sub_menu['page_slug']))?$sub_menu['page_slug']:'skydonation';
				$class_attr = isset($sub_menu['class']) ? ' class="' . esc_attr($sub_menu['class']) . '"' : '';
				// Skip if 'valid' is set and the option is not enabled
				if (isset($sub_menu['valid']) && skyweb_donation_setting_up($sub_menu['valid']) != 1) {
					continue;
				}
			?>
			<li<?php echo $class_attr; ?>>
				<a href="<?php echo esc_url(admin_url('admin.php?page='.$page_slug)); ?>"  class="nav-link <?php echo ($current_page == $page_slug ? 'active' : ''); ?>">
					<?php echo esc_html($sub_menu['page_title']); ?>
					<?php if($page_slug == 'skydonation-setup'): ?>
					<svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" x="0" y="0" viewBox="0 0 401.998 401.998" style="enable-background:new 0 0 512 512" xml:space="preserve"><g><path d="M357.45 190.721c-5.331-5.33-11.8-7.993-19.417-7.993h-9.131v-54.821c0-35.022-12.559-65.093-37.685-90.218C266.093 12.563 236.025 0 200.998 0c-35.026 0-65.1 12.563-90.222 37.688-25.126 25.126-37.685 55.196-37.685 90.219v54.821h-9.135c-7.611 0-14.084 2.663-19.414 7.993-5.33 5.326-7.994 11.799-7.994 19.417V374.59c0 7.611 2.665 14.086 7.994 19.417 5.33 5.325 11.803 7.991 19.414 7.991H338.04c7.617 0 14.085-2.663 19.417-7.991 5.325-5.331 7.994-11.806 7.994-19.417V210.135c.004-7.612-2.669-14.084-8.001-19.414zm-83.363-7.993H127.909v-54.821c0-20.175 7.139-37.402 21.414-51.675 14.277-14.275 31.501-21.411 51.678-21.411 20.179 0 37.399 7.135 51.677 21.411 14.271 14.272 21.409 31.5 21.409 51.675v54.821z" fill="currentColor" opacity="1" data-original="currentColor"></path></g></svg>
					<?php endif;?>
				</a>
			</li>
		<?php } ?>
		</ul>

		<?php 
		}
	}
	public function skyweb_donation_system_menu_array($menus){
		$sub_menus = array(
			array(
				'page_title' => esc_html__('Dashboard', 'skydonation'),
				'menu_title' => esc_html__('Dashboard', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => '',
				'callback'   => 'dashboard_page_content',
			),
			array(
				'page_title' => esc_html__('General', 'skydonation'),
				'menu_title' => esc_html__('General', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-general',
				'callback'   => 'general_page_content',
			),
			array(
				'page_title' => esc_html__('Donation Fees', 'skydonation'),
				'menu_title' => esc_html__('Donation Fees', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-donation-fees',
				'callback'   => 'donation_fees_page_content',
				'valid'      => 'setup_enable_donation_fees',
			),
			array(
				'page_title' => esc_html__('Gift Aid', 'skydonation'),
				'menu_title' => esc_html__('Gift Aid', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-gift-aid',
				'callback'   => 'gift_aid_page_content',
			),
			array(
				'page_title' => esc_html__('Widgets', 'skydonation'),
				'menu_title' => esc_html__('Widgets', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-widgets',
				'callback'   => 'widgets_page_content',
			),
			array(
				'page_title' => esc_html__('Address Autocomplete', 'skydonation'),
				'menu_title' => esc_html__('Address Autocomplete', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-address-autoload',
				'callback'   => 'address_autoload_page_content'
			),
			array(
				'page_title' => esc_html__('Notification', 'skydonation'),
				'menu_title' => esc_html__('Notification', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-notification',
				'callback'   => 'notification_page_content',
				'valid'      => 'setup_enable_notification',
			),
			array(
				'page_title' => esc_html__('Licenses', 'skydonation'),
				'menu_title' => esc_html__('Licenses', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-licenses',
				'callback'   => 'licenses_page_content',
			),
			array(
				'page_title' => esc_html__('API', 'skydonation'),
				'menu_title' => esc_html__('API', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-api',
				'callback'   => 'api_page_content',
			),
			/*array(
				'page_title' => esc_html__('Setup', 'skydonation'),
				'menu_title' => esc_html__('Setup', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-setup',
				'callback'   => 'setup_page_content',
				'class'			=>'setup-button'
			),*/
		);
		return $sub_menus;
	}
	/**
	 * Registers submenus for the Skyweb Donation System admin panel.
	 *
	 * @param string $parent_slug The slug name for the parent menu.
	 */
	public function skyweb_donation_system_menus($parent_slug) {
		
		// Allow filtering of menu array externally
		$sub_menus = apply_filters('skyweb_donation_system_menu_array', array());
		
		if (!empty($sub_menus)) {
			foreach ($sub_menus as $sub_menu) {
				$page_slug = (isset($sub_menu['page_slug']) && !empty($sub_menu['page_slug']))?$sub_menu['page_slug']:$parent_slug;
				// Skip if 'valid' is set and the option is not enabled
				if (isset($sub_menu['valid']) && skyweb_donation_setting_up($sub_menu['valid']) != 1) {
					continue;
				}

				// Add submenu
				add_submenu_page(
					$parent_slug,
					$sub_menu['page_title'],
					$sub_menu['menu_title'],
					$sub_menu['capability'],
					$page_slug,
					[$this, $sub_menu['callback']]
				);
			}
		}
	}

	
	
    public function general_page_content() {
		include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/partials/general-settings.php';
    }

    public function dashboard_page_content() {
        $this->display_page_content('main');
    }
	public function skyweb_general_settings_tabs(){
		$tabs = [
			'general' => [
				'label' => __( 'General', 'skyweb-donation-system' ),
				'icon'  => 'info-circle',
			],
			'extra-donation' => [
				'label' => __( 'Extra Donation', 'skyweb-donation-system' ),
				'icon'  => 'info-circle',
			],
			'advanced' => [
				'label' => __( 'Advanced', 'skyweb-donation-system' ),
				'icon'  => 'info-circle',
			],
			'currency' => [
				'label' => __( 'Currency', 'skyweb-donation-system' ),
				'icon'  => 'money-bill-wave',
			],
			'colors' => [
				'label' => __( 'Colors', 'skyweb-donation-system' ),
				'icon'  => 'info-circle',
			],
			/*
			'address-autocomplete' => [
				'label' => __( 'Address Autocomplete', 'skyweb-donation-system' ),
				'icon'  => 'map-marker-alt',
			],
			'magic-login' => [
				'label' => __( 'Magic Login Pro', 'skyweb-donation-system' ),
				'icon'  => 'magic',
			]*/
		];
        return $tabs;
	}
	public function skyweb_general_settings_fields(){
		global $SKYIN;
		$settings_fields = array();
		 return $settings_fields;		

	}
	public function skyweb_save_settings_fields(){
		global $SKDS_notice;
		$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        if (isset($_POST['setting_submit_fields']) && wp_verify_nonce($_POST['setting_submit_fields'], 'setting_submit_nonce')) {
            $settings_fields = apply_filters('skyweb_general_settings_fields', array());
            foreach ($settings_fields as $key => $fields) {
                if ($key === $active_tab) {
                    foreach ($fields as $field) {
                        $field_name = $field['id'];
                        if (isset($_POST[$field_name])) {
                            if (is_array($_POST[$field_name])) {
                                $posted_value = map_deep($_POST[$field_name], 'sanitize_text_field');
                                update_option($field_name, $posted_value);
                            } else {
                                if($field['type'] =='wysiwyg'){
									$posted_value = wp_unslash($_POST[$field_name]);
									update_option($field_name, $posted_value);
								}else{
									$posted_value = sanitize_text_field(wp_unslash($_POST[$field_name]));
									update_option($field_name, $posted_value);
								}
							}
							
							$SKDS_notice = true;
                        } else {
                            delete_option($field_name);
                        }
                    }
                }
            }
			
            do_action('save_settings_fields', $_POST);
        }
	}
	
	public function register_elementor_widgets() {
        register_setting( 'skydonation_widgets_group', 'skydonation_widgets' );
    }

	public function api_page_content() {
        $this->display_page_content('api');
    }

    public function setup_page_content() {
        $this->display_page_content('setup');
    }
    
    public function donation_fees_page_content() {
        $this->display_page_content( 'donation-fees' );
    }

	public function gift_aid_page_content() {
		$this->display_page_content( 'gift-aid' );
	}
	
    public function widgets_page_content() {
        $this->display_page_content( 'widgets' );
    }

    public function address_autoload_page_content() {
        $this->display_page_content( 'address-autoload' );
    }

    public function notification_page_content() {
        $this->display_page_content( 'notification' );
    }

    public function licenses_page_content() {
        $this->display_page_content( 'licenses' );
    }

    private function display_page_content( $template ) {
        echo '<div class="skydonation-page-wrapper ' . esc_attr( $template ) . '-template">';
            echo '<div class="skydonation-content-wrapper">';
                include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . "/template/dashboard-{$template}.php";
            echo '</div>';
        echo '</div>';
    }
	public function validation_check(){
		// Use new license manager
		return skydonate_license()->is_license_valid();
	}

}

/**
 * License AJAX Handlers
 */
add_action('wp_ajax_skydonate_activate_license', 'skydonate_ajax_activate_license');
function skydonate_ajax_activate_license() {
    if (!check_ajax_referer('skydonate_license_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skydonate')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'skydonate')));
    }

    $license_key = sanitize_text_field($_POST['license_key'] ?? '');

    if (empty($license_key)) {
        wp_send_json_error(array('message' => __('Please enter a valid license key.', 'skydonate')));
    }

    $result = skydonate_license()->activate_license($license_key);

    if ($result['success']) {
        wp_send_json_success(array('message' => $result['message']));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}

add_action('wp_ajax_skydonate_deactivate_license', 'skydonate_ajax_deactivate_license');
function skydonate_ajax_deactivate_license() {
    if (!check_ajax_referer('skydonate_license_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skydonate')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'skydonate')));
    }

    $result = skydonate_license()->deactivate_license();

    if ($result['success']) {
        wp_send_json_success(array('message' => $result['message']));
    } else {
        wp_send_json_error(array('message' => $result['message']));
    }
}

add_action('wp_ajax_skydonate_check_license', 'skydonate_ajax_check_license');
function skydonate_ajax_check_license() {
    if (!check_ajax_referer('skydonate_license_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skydonate')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'skydonate')));
    }

    // Force revalidation
    skydonate_license()->clear_cache();
    $is_valid = skydonate_license()->validate_license();

    if ($is_valid) {
        wp_send_json_success(array('message' => __('License is valid and active.', 'skydonate')));
    } else {
        wp_send_json_error(array('message' => __('License validation failed.', 'skydonate')));
    }
}

add_action('wp_ajax_skydonate_get_dashboard_stats', 'skydonate_ajax_get_dashboard_stats');
function skydonate_ajax_get_dashboard_stats() {
    if (!check_ajax_referer('skydonate_dashboard_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skydonate')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'skydonate')));
    }

    $period = intval($_POST['period'] ?? 30);
    $stats = skydonate_get_dashboard_stats($period);

    wp_send_json_success($stats);
}

add_action('wp_ajax_skydonate_export_donations', 'skydonate_ajax_export_donations');
function skydonate_ajax_export_donations() {
    if (!check_ajax_referer('skydonate_analytics_nonce', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed.', 'skydonate')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'skydonate')));
    }

    // Check if pro feature is available
    if (!skydonate_license()->has_feature('pro_widgets')) {
        wp_send_json_error(array('message' => __('This feature requires a Pro license.', 'skydonate')));
    }

    $period = intval($_POST['period'] ?? 30);
    $format = sanitize_text_field($_POST['format'] ?? 'csv');

    $end_date = current_time('mysql');
    $start_date = date('Y-m-d H:i:s', strtotime("-{$period} days", current_time('timestamp')));

    $orders = wc_get_orders(array(
        'status' => array('completed', 'processing'),
        'date_created' => $start_date . '...' . $end_date,
        'limit' => -1,
    ));

    $csv_lines = array();
    $csv_lines[] = 'Order ID,Date,Donor Name,Email,Project,Amount,Type';

    foreach ($orders as $order) {
        $items = $order->get_items();
        foreach ($items as $item) {
            $subscription_data = $item->get_meta('_subscription_period');
            $type = !empty($subscription_data) ? 'Recurring' : 'One-time';

            $csv_lines[] = sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"',
                $order->get_id(),
                $order->get_date_created()->format('Y-m-d H:i:s'),
                $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                $order->get_billing_email(),
                $item->get_name(),
                $order->get_total(),
                $type
            );
        }
    }

    $content = implode("\n", $csv_lines);
    $filename = 'skydonate-donations-' . date('Y-m-d') . '.csv';

    wp_send_json_success(array(
        'content' => $content,
        'filename' => $filename
    ));
}