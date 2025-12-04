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
				$class 		= isset($sub_menu['class'])?'class="'.$sub_menu['class'].'"':'';
				// Skip if 'valid' is set and the option is not enabled
				if (isset($sub_menu['valid']) && skyweb_donation_setting_up($sub_menu['valid']) != 1) {
					continue;
				}
			?>
			<li <?php echo $class;?>>
				<a href="<?php echo admin_url('admin.php?page='.$page_slug); ?>"  class="nav-link <?php echo ($current_page == $page_slug ? 'active' : ''); ?>">
					<?php echo $sub_menu['page_title']; ?>
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
				'page_title' => esc_html__('General', 'skydonation'),
				'menu_title' => esc_html__('General', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => '',
				'callback'   => 'general_page_content',
			),
			array(
				'page_title' => esc_html__('Donation Fees', 'skydonation'),
				'menu_title' => esc_html__('Donation Fees', 'skydonation'),
				'capability' => 'manage_options',
				'page_slug'  => 'skydonation-donation-fees',
				'callback'   => 'donation_fees_page_content',
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
                                if($field['type'] == 'wysiwyg'){
									// Sanitize WYSIWYG content with wp_kses_post to allow safe HTML
									$posted_value = wp_kses_post(wp_unslash($_POST[$field_name]));
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
            echo '<div class="skydonation-navigation-wrapper">';
                include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/dashboard-tabs.php';
            echo '</div>';
            echo '<div class="skydonation-content-wrapper">';
                include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . "/template/dashboard-{$template}.php";
            echo '</div>';
        echo '</div>';
    }
	public function validation_check(){
		 if (Skyweb_Donation_System_Authenticate::setup_update_status(get_option('license_key'))) {
			 return true;
		 }
	}

}