<?php 
if (!defined('ABSPATH')) {
    exit;
}
if(!function_exists('skyweb_donation_fields_html')){
	function skyweb_donation_fields_html($components = array(),$post_id=""){
		include SKYWEB_DONATION_SYSTEM_ADMIN_PATH.'/partials/html-fields.php';
	}
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


function sky_status_check($option) {
    if (sky_setup_check($option) ) {
        return get_option($option) == 1;
    }
    return false;
}
function sky_setup_check($option) {
	$option = 'setup_'.$option;
	return skyweb_donation_setting_up($option) == 1;
}
function sky_widget_status_check($option) {
	// if(skyweb_donation_setting_up($option) == 1){
	// }
   
	$default_widgets = [
		'zakat_calculator' => 'yes',
		'zakat_calculator_classic' => 'yes',
		'metal_values' => 'yes',
		'recent_order' => 'yes',
		'donation_progress' => 'yes',
		'donation_form' => 'yes',
		'donation_card' => 'yes',
		'impact_slider' => 'yes',
		'qurbani_status' => 'yes',
		'extra_donation' => 'yes',
		'donation_button' => 'yes',
		'icon_slider' => 'yes',
	];

	// Retrieve and merge saved options with defaults
	$widgets = wp_parse_args(get_option('skydonation_widgets', []), $default_widgets);
	// Check widget status
	return isset($widgets[$option]) && $widgets[$option] === 'on';
}


function skyweb_donation_setting_up( string $key_to_find ) {
	
    $system_setup = get_option( 'skyweb_donation_system_setup' );
    if ( ! $system_setup ) {
        return null;
    }

    $system_setup_array = json_decode( $system_setup, true );
    if ( ! is_array( $system_setup_array ) ) {
        return null;
    }

    return skyweb_donation_find_key_recursive( $system_setup_array, $key_to_find );
}
function skyweb_donation_find_key_recursive( array $array, string $key_to_find ) {
    foreach ( $array as $key => $value ) {
        if ( $key === $key_to_find ) {
            return $value;
        }
        if ( is_array( $value ) ) {
            $found = skyweb_donation_find_key_recursive( $value, $key_to_find );
            if ( null !== $found ) {
                return $found;
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
				
				skyweb_donation_activate_target_widget($enabled_widget,$zip_url);
			}
		}
	}
}
function skyweb_donation_activate_target_widget($enabled_widget,$zip_url){
		$widgets 	= skyweb_donation_widget_list();
		
		if(isset($widgets[$enabled_widget])&& !empty($widgets[$enabled_widget])){
			
			$zipPath = __DIR__ . '/temp.zip';
			$extractTo = SKYWEB_DONATION_SYSTEM_INCLUDES_PATH.'/addons/';

			// Step 1: Download ZIP file
		//	file_put_contents($zipPath, file_get_contents($zip_url));
	        skyweb_donation_downloadFile($zip_url,$zipPath);
			$zip = new ZipArchive;
		
			foreach($widgets[$enabled_widget] as $widget){
			   $targetFile = 'skyweb-donation-system/includes/addons/class-skyweb-donation-'.$widget.'.php';
				skyweb_donation_extract_target_file($zipPath,$extractTo,$targetFile);
			}
			unlink($zipPath);
		}
	
}
function skyweb_donation_extract_target_file($zipPath,$extractTo,$targetFile){
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
function skyweb_donation_downloadFile($url, $path) {
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

/**
 * Get the license manager instance
 *
 * @return Skyweb_License_Manager
 */
function skydonate_license() {
    return Skyweb_License_Manager::get_instance();
}

/**
 * Check if license is authenticated (compatible with old system)
 *
 * @return bool
 */
function license_authenticate() {
    return skydonate_license()->is_license_valid();
}

/**
 * Get dashboard statistics
 *
 * @param int $days Number of days for the period
 * @return array
 */
function skydonate_get_dashboard_stats($days = 30) {
    global $wpdb;

    $stats = array(
        'total_amount' => 0,
        'unique_donors' => 0,
        'order_count' => 0,
        'average_donation' => 0,
        'total_change' => 0,
        'donors_change' => 0,
        'one_time_total' => 0,
        'recurring_total' => 0,
        'top_projects' => array(),
        'recent_donations' => array(),
        'daily_data' => array(),
    );

    if (!class_exists('WooCommerce')) {
        return $stats;
    }

    // Date range
    $end_date = current_time('mysql');
    $start_date = date('Y-m-d H:i:s', strtotime("-{$days} days", current_time('timestamp')));
    $prev_start_date = date('Y-m-d H:i:s', strtotime("-" . ($days * 2) . " days", current_time('timestamp')));

    // Get donation products
    $donation_products = get_posts(array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_sky_donation_product',
                'value' => 'yes',
                'compare' => '='
            )
        ),
        'fields' => 'ids'
    ));

    if (empty($donation_products)) {
        // Fallback: Get all products if no dedicated donation products
        $donation_products = get_posts(array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ));
    }

    // Current period orders
    $orders = wc_get_orders(array(
        'status' => array('completed', 'processing'),
        'date_created' => $start_date . '...' . $end_date,
        'limit' => -1,
    ));

    // Previous period orders (for comparison)
    $prev_orders = wc_get_orders(array(
        'status' => array('completed', 'processing'),
        'date_created' => $prev_start_date . '...' . $start_date,
        'limit' => -1,
    ));

    $total = 0;
    $donors = array();
    $one_time = 0;
    $recurring = 0;
    $projects = array();
    $daily = array();

    // Initialize daily data
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days", current_time('timestamp')));
        $daily[$date] = array('date' => date('M j', strtotime($date)), 'total' => 0, 'count' => 0);
    }

    // Process current orders
    foreach ($orders as $order) {
        $order_total = $order->get_total();
        $total += $order_total;

        $billing_email = $order->get_billing_email();
        if ($billing_email) {
            $donors[$billing_email] = true;
        }

        $order_date = $order->get_date_created()->format('Y-m-d');
        if (isset($daily[$order_date])) {
            $daily[$order_date]['total'] += $order_total;
            $daily[$order_date]['count']++;
        }

        // Check if recurring (subscription)
        $is_recurring = false;
        foreach ($order->get_items() as $item) {
            $subscription_data = $item->get_meta('_subscription_period');
            if (!empty($subscription_data)) {
                $is_recurring = true;
            }

            // Track project data
            $product_id = $item->get_product_id();
            $product_name = $item->get_name();
            if (!isset($projects[$product_id])) {
                $projects[$product_id] = array(
                    'name' => $product_name,
                    'count' => 0,
                    'total' => 0
                );
            }
            $projects[$product_id]['count']++;
            $projects[$product_id]['total'] += $item->get_total();
        }

        if ($is_recurring) {
            $recurring += $order_total;
        } else {
            $one_time += $order_total;
        }
    }

    // Previous period totals
    $prev_total = 0;
    $prev_donors = array();
    foreach ($prev_orders as $order) {
        $prev_total += $order->get_total();
        $billing_email = $order->get_billing_email();
        if ($billing_email) {
            $prev_donors[$billing_email] = true;
        }
    }

    // Calculate changes
    $total_change = $prev_total > 0 ? round((($total - $prev_total) / $prev_total) * 100, 1) : 0;
    $prev_donor_count = count($prev_donors);
    $donor_count = count($donors);
    $donors_change = $prev_donor_count > 0 ? round((($donor_count - $prev_donor_count) / $prev_donor_count) * 100, 1) : 0;

    // Sort projects by total
    usort($projects, function($a, $b) {
        return $b['total'] - $a['total'];
    });

    // Get recent donations
    $recent_orders = wc_get_orders(array(
        'status' => array('completed', 'processing'),
        'limit' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    ));

    $recent_donations = array();
    foreach ($recent_orders as $order) {
        $items = $order->get_items();
        $first_item = reset($items);
        $recent_donations[] = array(
            'name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'project' => $first_item ? $first_item->get_name() : 'Donation',
            'amount' => $order->get_total(),
            'date' => $order->get_date_created()->format('Y-m-d H:i:s')
        );
    }

    $order_count = count($orders);

    return array(
        'total_amount' => $total,
        'unique_donors' => $donor_count,
        'order_count' => $order_count,
        'average_donation' => $order_count > 0 ? $total / $order_count : 0,
        'total_change' => $total_change,
        'donors_change' => $donors_change,
        'one_time_total' => $one_time,
        'recurring_total' => $recurring,
        'top_projects' => array_slice($projects, 0, 5),
        'recent_donations' => $recent_donations,
        'daily_data' => array_values($daily),
    );
}

function skyweb_donation_layout_option($option_key) {
	// if($option_key == 'addons_donation_form_layout'){
	// 	return ['layout2'];
	// }
	// if($option_key == 'recent_donation_layout'){
	// 	return ['layout1'];
	// }
    $options = skyweb_donation_setting_up('options');
    if (isset($options[$option_key])) {
        return $options[$option_key];
    }
    return array();
}

function skyweb_donation_widget_list(){
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
			update_option('skyweb_donation_system_setup',$args['setup']);
			skyweb_donation_system_properties($args);
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