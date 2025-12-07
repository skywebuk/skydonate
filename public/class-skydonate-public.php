<?php
/**
 * SkyDonate Public Class
 *
 * @package    SkyDonate
 * @subpackage SkyDonate/public
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skydonate_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
	    
        wp_register_style(
            'zakat-calculator',
            SKYDONATE_ASSETS . '/addons/css/zakat-calculator.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'zakat-calculator-classic',
            SKYDONATE_ASSETS . '/addons/css/zakat-calculator-classic.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'zakat-calculator-preview',
            SKYDONATE_ASSETS . '/addons/css/zakat-calculator-preview.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'donation-button',
            SKYDONATE_ASSETS . '/addons/css/donation-button.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'quick-donation',
            SKYDONATE_ASSETS . '/addons/css/quick-donation.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'lity-lightbox',
            SKYDONATE_PUBLIC_ASSETS . '/css/lity-min.css',
            [],
            SKYDONATE_VERSION
        );
        wp_register_style(
            'donation-icon-list',
            SKYDONATE_ASSETS . '/addons/css/donation-icon-list.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'donation-impact-slider',
            SKYDONATE_ASSETS . '/addons/css/donation-impact-slider.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'recent-donation',
            SKYDONATE_ASSETS . '/addons/css/recent-donation.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'recent-donation-two',
            SKYDONATE_ASSETS . '/addons/css/recent-donation-two.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'donation-progress',
            SKYDONATE_ASSETS . '/addons/css/donation-progress.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'skydonate-swiper',
            SKYDONATE_ASSETS . '/css/swiper.min.css',
            [],
            '5.4.5'
        );
        wp_register_style(
            'swiper-override',
            SKYDONATE_PUBLIC_ASSETS . '/css/swiper-override.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        wp_register_style(
            'donation-card',
            SKYDONATE_ASSETS . '/addons/css/donation-card.css',
            [],
            SKYDONATE_VERSION  // Replace with the version number or leave as is
        );
        
        
            $addons_form_layout = skydonate_layout_option('addons_donation_form_layout');

            if (!is_array($addons_form_layout)) {
                $addons_form_layout = ['layout1'];
            }
            
            if (in_array('layout3',  $addons_form_layout)) {
                wp_enqueue_style(
                    'donation-form-three',
                    SKYDONATE_ASSETS . '/addons/css/donation-form-three.css',
                    [],
                    SKYDONATE_VERSION  // Replace with the version number or leave as is
                );
                // JavaScript Assets
                wp_enqueue_script(
                    'donation-form',
                SKYDONATE_ASSETS . '/addons/js/donation-form-three.js',
                    ['jquery'],
                    SKYDONATE_VERSION,  // Replace with the version number or leave as is
                    true
                );
            } elseif (in_array('layout2', $addons_form_layout)) {
                wp_enqueue_style(
                    'donation-form-two',
                    SKYDONATE_ASSETS . '/addons/css/donation-form-two.css',
                    [],
                    SKYDONATE_VERSION  // Replace with the version number or leave as is
                );
                // JavaScript Assets
                wp_enqueue_script(
                    'donation-form',
                SKYDONATE_ASSETS . '/addons/js/donation-form.js',
                    ['jquery'],
                    SKYDONATE_VERSION,  // Replace with the version number or leave as is
                    true
                );
            }else {
                wp_enqueue_style(
                    'donation-form-one',
                    SKYDONATE_ASSETS . '/addons/css/donation-form-one.css',
                    [],
                    SKYDONATE_VERSION  // Replace with the version number or leave as is
                );
                // JavaScript Assets
                wp_enqueue_script(
                    'donation-form',
                SKYDONATE_ASSETS . '/addons/js/donation-form.js',
                    ['jquery'],
                    SKYDONATE_VERSION,  // Replace with the version number or leave as is
                    true
                );
            }

        
        // JavaScript Assets
        wp_register_script(
            'zakat-calculator',
           SKYDONATE_ASSETS . '/addons/js/zakat-calculator.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        
        wp_localize_script('zakat-calculator', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce'),
            'cart_url' => wc_get_cart_url(),
        ]);

        wp_localize_script('donation-form', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce'),
        ]);


        wp_register_script(
            'zakat-calculator-classic',
            SKYDONATE_ASSETS . '/addons/js/zakat-calculator-classic.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );

        wp_localize_script('zakat-calculator-classic', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce'),
            'cart_url' => wc_get_cart_url(),
        ]);

        wp_register_script(
            'zakat-calculator-preview',
            SKYDONATE_ASSETS . '/addons/js/zakat-calculator-preview.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );

        wp_localize_script('zakat-calculator-preview', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce'),
            'cart_url' => wc_get_cart_url(),
        ]);

        // JavaScript Assets
        wp_register_script(
            'recent-donation',
            SKYDONATE_ASSETS . '/addons/js/recent-donation.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_localize_script('recent-donation', 'skydonate_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce')
        ]);

        wp_register_script(
            'recent-donation-two',
            SKYDONATE_ASSETS . '/addons/js/recent-donation-two.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_localize_script('recent-donation-two', 'skydonate_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce')
        ]);

        wp_register_script(
            'quick-donation',
            SKYDONATE_ASSETS . '/addons/js/quick-donation.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );

        wp_localize_script('quick-donation', 'skydonate_extra_donation_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_nonce')
        ]);

        wp_register_script(
            'gift-aid-toggle',
            SKYDONATE_ASSETS . '/addons/js/gift-aid-toggle.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );

        wp_register_script(
            'lity-lightbox',
            SKYDONATE_PUBLIC_ASSETS . '/js/lity-min.js',
            ['jquery'],
            SKYDONATE_VERSION,
            true
        );
        wp_register_script(
            'donation-progress',
            SKYDONATE_ASSETS . '/addons/js/donation-progress.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_register_script(
            'donation-card',
            SKYDONATE_ASSETS . '/addons/js/donation-card.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_register_script(
            'donation-impact-slider',
            SKYDONATE_ASSETS . '/addons/js/donation-impact-slider.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );

        wp_register_script(
            'donation-button',
            SKYDONATE_ASSETS . '/addons/js/donation-button.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_register_script(
            'donation-icon-list',
            SKYDONATE_ASSETS . '/addons/js/donation-icon-list.js',
            ['jquery'],
            SKYDONATE_VERSION,  // Replace with the version number or leave as is
            true
        );
        wp_register_script(
            'skydonate-swiper',
            SKYDONATE_ASSETS . '/js/swiper.min.js',
            ['jquery'],
            '5.4.5',
            true
        );
    
        // Localize the script with `ajaxurl`
        wp_localize_script('donation-card', 'skydonateDonation', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
        
        // --- Dequeue Elementor Font Awesome CSS ---
        wp_dequeue_style( 'elementor-icons-shared-0' );
        wp_dequeue_style( 'elementor-icons-fa-solid' );
        wp_deregister_style( 'elementor-icons-shared-0' );
        wp_deregister_style( 'elementor-icons-fa-solid' );

        // --- Load Font Awesome 7.1.0 ---
        wp_enqueue_style(
            'fontawesome-all',
            'https://site-assets.fontawesome.com/releases/v7.1.0/css/all.css',
            array(),
            null
        );

		wp_enqueue_style(
			'additional-fees-styles', 
			SKYDONATE_PUBLIC_ASSETS . '/css/additional-fees-styles.css', 
			array(), 
			SKYDONATE_VERSION
		);

        if(skydonate_is_feature_enabled('checkout_custom_field_style') && sky_status_check('checkout_custom_field_style')){
            wp_enqueue_style(
                'checkout-custom-style', 
                SKYDONATE_PUBLIC_ASSETS . '/css/checkout-custom-style.css', 
                array(), 
                SKYDONATE_VERSION
            );
        }
        
		wp_enqueue_style(
			'bootstrap', 
			SKYDONATE_PUBLIC_ASSETS. '/css/bootstrap-min.css', 
			array(), 
			SKYDONATE_VERSION
		);
		if (is_account_page() || is_checkout()) {
			wp_enqueue_style(
				'wc-registration-style', 
				SKYDONATE_PUBLIC_ASSETS . '/css/wc-registration.css', 
				array(), 
				SKYDONATE_VERSION
			);
		}
		
		// Enqueue main frontend stylesheet
		wp_enqueue_style(
			'frontend-global',
			SKYDONATE_PUBLIC_ASSETS . '/css/frontend-global.css',
			array(),
			SKYDONATE_VERSION
		);

		// Get saved colors with defaults
		$accent_color       = get_option( 'skydonation_accent_color', '#3442ad' );
		$accent_dark_color  = get_option( 'skydonation_accent_dark_color', '#282699' );
		$accent_light_color = get_option( 'skydonation_accent_light_color', '#ebecf7' );

		// Prepare CSS variables
		$custom_css = sprintf(
			'body {
				--accent-color: %1$s;
				--accent-dark-color: %2$s;
				--accent-light-color: %3$s;
			}',
			esc_attr( $accent_color ),
			esc_attr( $accent_dark_color ),
			esc_attr( $accent_light_color )
		);

		// Add dynamic inline CSS
		wp_add_inline_style( 'frontend-global', $custom_css );

	
		if (sky_status_check('enable_donation_goal')) {
			wp_enqueue_style(
				'donation-goal', 
				SKYDONATE_PUBLIC_ASSETS . '/css/donation-goal.css', 
				array(), 
				SKYDONATE_VERSION
			);
		}
	
		if (sky_status_check('recent_donation_list_with_country')) {
			wp_enqueue_style(
				'recent-donations', 
				SKYDONATE_PUBLIC_ASSETS . '/css/recent-donations.css', 
				array(), 
				SKYDONATE_VERSION
			);
			wp_enqueue_style(
				'flag-icons', 
				'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css'
			);
		}
	}

	public function enqueue_scripts() {
		if (is_account_page() || is_checkout()) {
			wp_enqueue_script(
				'wc-registration-script', 
				SKYDONATE_PUBLIC_ASSETS. '/js/wc-registration.js', 
				array('jquery'), 
				SKYDONATE_VERSION, 
				true
			);
		}

		wp_enqueue_script(
			'sweetalert2',
			'https://cdn.jsdelivr.net/npm/sweetalert2@11',
			array('jquery'),
			null,
			true
		);
		
		wp_enqueue_script(
			'wc-single-script', 
			SKYDONATE_PUBLIC_ASSETS . '/js/wc-single.js', 
			array('jquery'), 
			SKYDONATE_VERSION, 
			true
		);

		wp_enqueue_script(
			'sky-frontend-global', 
			SKYDONATE_PUBLIC_ASSETS . '/js/frontend-global.js', 
			array('jquery'), 
			SKYDONATE_VERSION, 
			true
		);
        
        wp_enqueue_script('jquery-ui-datepicker');


		if ( is_account_page() ) {
			wp_enqueue_script(
				'account-page',
				SKYDONATE_PUBLIC_ASSETS . '/js/account-page.js',
				array('jquery'),
				SKYDONATE_VERSION,
				true
			);
			// Pass AJAX URL and nonce to JS
			wp_localize_script( 'account-page', 'account_page_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'save_account_data' ),
			));
			// Enqueue CSS for Account Page styling
			wp_enqueue_style(
				'account-page-style',
				SKYDONATE_PUBLIC_ASSETS . '/css/account-page.css',
				array(),
				SKYDONATE_VERSION
			);

		}
	}

	public function add_checkout_custom_style_class($classes) {
		if (skydonate_is_feature_enabled('checkout_custom_field_style') && sky_status_check('checkout_custom_field_style')) {
			$classes[] = 'checkout-custom-style';
		}

		$layout_option_style = skydonate_layout_option('addons_donation_form_layout');
		$layout_option = $layout_option_style?$layout_option_style:['layout1'];

		if (!is_array($layout_option)) {
			$layout_option = ['layout1'];
		}
		if (in_array('layout2', $layout_option, true)) {
			$classes[] = 'donation-form-layout2';
		}
		if (in_array('layout3', $layout_option, true)) {
			$classes[] = 'donation-form-layout3';
		}
	
		return $classes;
	}

	public function sky_donation_woocommerce_myaccount_login_template($template, $template_name, $template_path){
		$_template = $template;
        if (! $template_path) {
            $template_path = wc()->template_url;
        }
        $plugin_path  = plugin_dir_path(dirname(__FILE__)) . 'templates/';
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
              )
        );
        if (!$template && file_exists($plugin_path . $template_name)) {
            $template = $plugin_path . $template_name;
        }
        if (! $template) {
            $template = $_template;
        }
        return $template;
	}

	public function sky_donation_woocommerce_myaccount_custom_login_template( $template, $template_name, $template_path ) {
        $plugin_path = plugin_dir_path(dirname(__FILE__)) . 'templates/';
        if ( 'myaccount/form-login.php' === $template_name || 'global/form-login.php' === $template_name || 'checkout/form-login.php' === $template_name && file_exists( $plugin_path . $template_name ) ) {
            return $plugin_path . $template_name;
        }
        return $template;
    }

	public function custom_woocommerce_hidden_order_itemmeta( $meta_keys ) {
		$meta_keys[] = 'Amount';
		$meta_keys[] = 'Selected Amount';
		$meta_keys[] = 'Name on Plaque/ Banner';
		return $meta_keys;
	}

	public function custom_hide_order_item_meta( $formatted_meta, $item ) {
		foreach ( $formatted_meta as $key => $meta ) {
			if ( in_array( $meta->key, array( 'Amount', 'Selected Amount' ) ) ) {
				unset( $formatted_meta[ $key ] );
			}
		}
		return $formatted_meta;
	}

	public function custom_hide_order_item_meta_in_emails( $display_key, $meta, $item ) {
		if ( in_array( $meta->key, array( 'Amount', 'Selected Amount' ) ) ) {
			return '';
		}
		return $display_key;
	}

	public function display_custom_order_item_meta( $item_id, $item, $product ) {
		if ( $meta = wc_get_order_item_meta( $item_id, 'Name on Plaque/ Banner' ) ) {
			echo '<p><strong>' . __( 'Name on Plaque/ Banner', 'skydonate' ) . ':</strong><input type="text" name="custom_order_item_meta[' . $item_id . ']" value="' . esc_attr( $meta ) . '" /></p>';
		}
	}

	public function save_custom_order_item_meta( $order_id, $items ) {
		if ( isset( $_POST['custom_order_item_meta'] ) ) {
			foreach ( $_POST['custom_order_item_meta'] as $item_id => $meta_value ) {
				wc_update_order_item_meta( $item_id, 'Name on Plaque/ Banner', sanitize_text_field( $meta_value ) );
			}
		}
	}

	public function remove_woocommerce_widget_shopping_cart_total(){
		remove_action('woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10);
	}
}

// Backwards compatibility alias
class_alias( 'Skydonate_Public', 'Skyweb_Donation_System_Public' );
