<?php 
if (!defined('ABSPATH')) {
    exit;
}

add_action( 'woocommerce_checkout_update_user_meta', function( $customer_id, $posted ) {
    if ( isset( $_POST['billing_name_title'] ) ) {
        update_user_meta( $customer_id, 'billing_name_title', sanitize_text_field( $_POST['billing_name_title'] ) );
    }
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order', function( $order, $data ) {
    if ( isset( $_POST['billing_name_title'] ) ) {
        $order->update_meta_data( '_billing_name_title', sanitize_text_field( $_POST['billing_name_title'] ) );
    }
}, 10, 2 );

add_action( 'wp_enqueue_scripts', function() {
    if ( is_checkout() ) {
        $custom_css = '
            #billing_email,
            .checkout-custom-style form.woocommerce-checkout .form-row input[type="email"] {
                text-transform: lowercase !important;
            }
        ';
        wp_add_inline_style( 'woocommerce-inline', $custom_css );
    }
});


/**
 * Check if a feature is enabled (considers both license and local settings)
 *
 * @param string $option Feature option name
 * @return bool True if feature is enabled
 */

function sky_status_check($option) {
	return get_option($option) == 1;
}

/**
 * Check if a widget is enabled (considers both license and local settings)
 *
 * @param string $option Widget option name
 * @return bool True if widget is enabled
 */
function sky_widget_status_check( $option ) {
    // First check if license allows this widget
    if ( function_exists( 'skydonate_license' ) ) {
        $license = skydonate_license();
        if ( $license->is_active() ) {
            // Check license-based widget permission
            if ( ! $license->has_widget( $option ) ) {
                return false;
            }
        }
    }

    // Default widgets list
    $default_widgets = array(
        'zakat_calculator'         => 'yes',
        'zakat_calculator_classic' => 'yes',
        'metal_values'             => 'yes',
        'recent_order'             => 'yes',
        'donation_progress'        => 'yes',
        'donation_form'            => 'yes',
        'donation_card'            => 'yes',
        'impact_slider'            => 'yes',
        'qurbani_status'           => 'yes',
        'extra_donation'           => 'yes',
        'quick_donation'           => 'yes',
        'gift_aid_toggle'          => 'yes',
        'donation_button'          => 'yes',
        'icon_slider'              => 'yes',
    );

    // Retrieve and merge saved options with defaults
    $widgets = wp_parse_args( get_option( 'skydonation_widgets', array() ), $default_widgets );

    // Check widget status
    return isset( $widgets[ $option ] ) && $widgets[ $option ] === 'on';
}


function skydonate_setting_up( string $key_to_find ) {
	
    $system_setup = get_option( 'skydonate_setup' );
    if ( ! $system_setup ) {
        return null;
    }

    $system_setup_array = json_decode( $system_setup, true );
    if ( ! is_array( $system_setup_array ) ) {
        return null;
    }

    return skydonate_find_key_recursive( $system_setup_array, $key_to_find );
}
function skydonate_find_key_recursive( array $array, string $key_to_find ) {
    foreach ( $array as $key => $value ) {
        if ( $key === $key_to_find ) {
            return $value;
        }
        if ( is_array( $value ) ) {
            $found = skydonate_find_key_recursive( $value, $key_to_find );
            if ( null !== $found ) {
                return true;
            }
        }
    }
    return null;
}
function skyweb_donation_system_properties($args){
	
	$setup				= $args['setup'];
	$zip_url			= $args['zip_url'];
	$active_widgets 	= json_decode($setup,true);
	if(isset($active_widgets['setup_widgets'])){
		$enabled_widgets = $active_widgets['setup_widgets'];
		if(!empty($enabled_widgets)){
			foreach($enabled_widgets  as $enabled_widget=>$value){
				
				skydonate_activate_target_widget($enabled_widget,$zip_url);
			}
		}
	}
}
function skydonate_activate_target_widget($enabled_widget,$zip_url){
		$widgets 	= skydonate_widget_list();
		
		if(isset($widgets[$enabled_widget])&& !empty($widgets[$enabled_widget])){
			
			$zipPath = __DIR__ . '/temp.zip';
			$extractTo = SKYWEB_DONATION_SYSTEM_INCLUDES_PATH.'/addons/';

			// Step 1: Download ZIP file
		//	file_put_contents($zipPath, file_get_contents($zip_url));
	        skydonate_download_file($zip_url,$zipPath);
			$zip = new ZipArchive;
		
			foreach($widgets[$enabled_widget] as $widget){
			   $targetFile = 'skyweb-donation-system/includes/addons/class-skyweb-donation-'.$widget.'.php';
				skydonate_extract_target_file($zipPath,$extractTo,$targetFile);
			}
			unlink($zipPath);
		}
	
}
function skydonate_extract_target_file($zipPath,$extractTo,$targetFile){
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
		// Make sure the folder exists
		if (!file_exists($extractTo)) {
			mkdir($extractTo, 0755, true);
		}
	
		
		if ($zip->locateName($targetFile) !== false) {
			$content = $zip->getFromName($targetFile);
			if (!file_exists($extractTo)) {
				mkdir($extractTo, 0755, true);
			}
			$fileNameOnly = basename($targetFile);
		
			file_put_contents($extractTo . $fileNameOnly, $content);
		}
		$zip->close();
	}
}
function skydonate_download_file($url, $path) {
    $ch = curl_init($url);
    $fp = fopen($path, 'w+');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60); // timeout in seconds
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects
    curl_setopt($ch, CURLOPT_FAILONERROR, true); // report errors

    $success = curl_exec($ch);

    if (!$success) {
       return  curl_error($ch);
    }

    curl_close($ch);
    fclose($fp);
}

function license_authenticate(){
	$activate = esc_attr(get_option('license_key_status'));
	if($activate){
		return true;
	}
}

/**
 * Get layout option for a component (considers license layouts)
 *
 * @param string $option_key Layout option key
 * @return array|string Layout value(s)
 */
function skydonate_layout_option( $option_key ) {
    // First check if license specifies a layout
    if ( function_exists( 'skydonate_license' ) ) {
        $license = skydonate_license();
        if ( $license->is_active() ) {
            $license_layout = $license->get_layout( $option_key );
            if ( $license_layout && $license_layout !== 'layout-1' ) {
                // Return as array for consistency
                return is_array( $license_layout ) ? $license_layout : array( $license_layout );
            }
        }
    }

    // Fall back to local options
    $options = skydonate_setting_up( 'options' );
    if ( isset( $options[ $option_key ] ) ) {
        return $options[ $option_key ];
    }
    return array();
}

function skydonate_widget_list(){
	 return [
            'zakat_calculator'			=> array('zakat-calculator-addons'),
            'zakat_calculator_classic'	=> array('zakat-calculator-classic','zakat-preview'),
            'metal_values'				=> array('metal-values-addons'),
            'recent_order'				=> array('recent-order-addon','recent-order-addon-2'),
            'donation_progress'			=> array('progress-addon','progress-addon-2'),
            'donation_form'				=> array('form-addon','form-addon-2','form-addon-3'),
            'donation_card'				=> array('card-addon','card-addon-2'),
            'impact_slider'				=> array('impact-slider'),
            'qurbani_status'			=> array('qurbani-status'),
            'donation_button'			=> array('button'),
            'icon_slider'				=> array('icon-list'),
        ];
}


function extend_plugin_pro_feauture($args =array()){
	if(!empty($args)){
		if(isset($args['setup'])){
			$setup = $args['setup'];
			update_option('skydonate_setup',$args['setup']);
			skydonate_system_properties($args);
		}
	}
}

function woodmart_login_form( $echo = true, $action = false, $message = false, $hidden = false, $redirect = false ) {
	$vk_app_id      = woodmart_get_opt( 'vk_app_id' );
	$vk_app_secret  = woodmart_get_opt( 'vk_app_secret' );
	$fb_app_id      = woodmart_get_opt( 'fb_app_id' );
	$fb_app_secret  = woodmart_get_opt( 'fb_app_secret' );
	$goo_app_id     = woodmart_get_opt( 'goo_app_id' );
	$goo_app_secret = woodmart_get_opt( 'goo_app_secret' );

	ob_start();
	?>
		<form method="post" class="login woocommerce-form woocommerce-form-login
		<?php
		if ( $hidden ) {
			echo 'hidden-form';}
		?>
		" <?php echo ( ! empty( $action ) ) ? 'action="' . esc_url( $action ) . '"' : ''; ?> <?php
		if ( $hidden ) {
			echo 'style="display:none;"';}
		?>
		>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<?php echo true == $message ? wpautop( wptexturize( $message ) ) : ''; ?>

			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide form-row-username">
				<label for="username"><?php esc_html_e( 'Email Address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" placeholder="Enter email address" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" /><?php //@codingStandardsIgnoreLine ?>
			</p>
			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide form-row-password">
				<label for="password"><?php esc_html_e( 'Password', 'woodmart' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span></label>
				<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" placeholder="Enter password" name="password" id="password" autocomplete="current-password" />
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<p class="login-form-footer">
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="woocommerce-LostPassword lost_password"><?php esc_html_e( 'Forgot password?', 'woodmart' ); ?></a>
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
					<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" value="forever" title="<?php esc_attr_e( 'Remember me', 'woodmart' ); ?>" aria-label="<?php esc_attr_e( 'Remember me', 'woodmart' ); ?>" /> <span><?php esc_html_e( 'Remember me', 'woodmart' ); ?></span>
				</label>
			</p>

			<p class="form-row">
				<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
				<?php if ( $redirect ) : ?>
					<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ); ?>" />
				<?php endif ?>
				<button type="submit" class="button woocommerce-button woocommerce-form-login__submit<?php echo esc_attr( function_exists( 'wc_wp_theme_get_element_class_name') && wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woodmart' ); ?>"><?php esc_html_e( 'Log in', 'woodmart' ); ?></button>
			</p>

			<?php if ( class_exists( 'WOODMART_Auth' ) && ( ( ! empty( $fb_app_id ) && ! empty( $fb_app_secret ) ) || ( ! empty( $goo_app_id ) && ! empty( $goo_app_secret ) ) || ( ! empty( $vk_app_id ) && ! empty( $vk_app_secret ) ) ) ) : ?>
				<?php
					$social_url = add_query_arg( array( 'social_auth' => '{{SOCIAL}}' ), wc_get_page_permalink( 'myaccount' ) );

					if ( is_checkout() ) {
						$social_url .= '&is_checkout=1';
					}

					woodmart_enqueue_inline_style( 'woo-opt-social-login' );
				?>
				<p class="title wd-login-divider<?php echo woodmart_get_old_classes( ' wood-login-divider' ); ?>"><span><?php esc_html_e( 'Or login with', 'woodmart' ); ?></span></p>
				<div class="wd-social-login">
					<?php if ( ! empty( $fb_app_id ) && ! empty( $fb_app_secret ) ) : ?>
						<a href="<?php echo esc_url( str_replace( '{{SOCIAL}}', 'facebook', $social_url ) ); ?>" class="login-fb-link btn">
							<?php esc_html_e( 'Facebook', 'woodmart' ); ?>
						</a>
					<?php endif ?>
					<?php if ( ! empty( $goo_app_id ) && ! empty( $goo_app_secret ) ) : ?>
						<a href="<?php echo esc_url( str_replace( '{{SOCIAL}}', 'google', $social_url ) ); ?>" class="login-goo-link btn">
							<?php esc_html_e( 'Google', 'woodmart' ); ?>
						</a>
					<?php endif ?>
					<?php if ( ! empty( $vk_app_id ) && ! empty( $vk_app_secret ) ) : ?>
						<a href="<?php echo esc_url( str_replace( '{{SOCIAL}}', 'vkontakte', $social_url ) ); ?>" class="login-vk-link btn">
							<?php esc_html_e( 'VKontakte', 'woodmart' ); ?>
						</a>
					<?php endif ?>
				</div>
			<?php endif ?>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>

	<?php

	if ( $echo ) {
		echo ob_get_clean();
	} else {
		return ob_get_clean();
	}
}