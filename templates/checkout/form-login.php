<?php
    /**
     * Checkout login form
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-login.php.
     *
     * @see https://woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 3.8.0
     */
    
    if ( ! defined( 'ABSPATH' ) ) {
    	exit; // Exit if accessed directly.
    }
    
    if ( is_user_logged_in() || 'no' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
    	return;
    }
    
    $account_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
    
    // WooCommerce version check for backward compatibility.
    if ( function_exists( 'WC' ) && version_compare( WC()->version, '3.5.0', '<' ) ) {
    	wc_print_notices();
    }
    
    woodmart_enqueue_inline_style( 'woo-mod-login-form' );
    woodmart_enqueue_inline_style( 'woo-page-login-register' );
    
    do_action( 'woocommerce_before_customer_login_form' );

	// Safely retrieve 'action' from the query parameters
	$action = isset( $_GET['action'] ) ? $_GET['action'] : '';

?>

<div class="sky-checkout-login">
    <div class="woocommerce-form-login-toggle">
    	<?php wc_print_notice( apply_filters( 'woocommerce_checkout_login_message', esc_html__( 'Returning customer?', 'woocommerce' ) ) . ' <a href="#" class="showskylogin">' . esc_html__( 'Click here to login', 'woocommerce' ) . '</a>', 'notice' ); ?>
	</div>

	<div class="sky-donation-login <?php echo !empty($action) ? 'show' : ''; ?>">
<?php
	if ( ! isset( $_GET['action'] ) || 'login' == $_GET['action'] ) :
		?>
			<div class="login-form-box">
				<h2 class="wd-login-title"><?php esc_html_e( 'Donor Dashboard', 'skydonate' ); ?></h2>
				<?php woodmart_login_form( true, add_query_arg( 'action', 'login', $account_link ) ); ?>
				<div class="text-center py-4">
					<a href="?action=magic_login" class="link__text">Use single sign-on via email for a quick login</a>
				</div>
				<hr>
				<div class="text-center pt-3">
					<p class="regular__text mb-0">Don't have an account? <a href="?action=register" class="link__text">Sign up</a></p>
				</div>
			</div>
		<?php
		elseif(! isset( $_GET['action'] ) || 'register' == $_GET['action']):
		?>
			<div class="login-form-box">
				<h2 class="wd-login-title"><?php esc_html_e( 'Sign Up', 'skydonate' ); ?></h2>
				<form method="post" action="<?php echo esc_url( add_query_arg( 'action', 'register', $account_link ) ); ?>" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
					<?php //do_action( 'woocommerce_register_form_start' ); ?>
					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_username"><?php esc_html_e( 'Username', 'skydonate' ); ?>&nbsp;<span class="required">*</span></label>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" placeholder="<?php esc_attr_e( 'Enter your username', 'skydonate' ); ?>" value="<?php echo ! empty( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" />
						</p>
					<?php endif; ?>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_email"><?php esc_html_e( 'Email Address', 'skydonate' ); ?>&nbsp;<span class="required">*</span></label>
						<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" placeholder="<?php esc_attr_e( 'Enter your email address', 'skydonate' ); ?>" value="<?php echo ! empty( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" />
					</p>
					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="reg_billing_phone">Phone Number <span class="required">*</span></label>
						<input type="text" class="input-text" placeholder="Enter phone number" name="billing_phone" id="reg_billing_phone" value="">
					</p>
					<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_password"><?php esc_html_e( 'Password', 'skydonate' ); ?>&nbsp;<span class="required">*</span></label>
							<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" placeholder="<?php esc_attr_e( 'Enter your password', 'skydonate' ); ?>" />
						</p>
					<?php else : ?>
						<p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'skydonate' ); ?></p>
					<?php endif; ?>
					<!-- Spam Trap -->
					<div style="<?php echo is_rtl() ? 'right' : 'left'; ?>: -999em; position: absolute;">
						<label for="trap"><?php esc_html_e( 'Anti-spam', 'skydonate' ); ?></label>
						<input type="text" name="email_2" id="trap" tabindex="-1" placeholder="<?php esc_attr_e( 'Leave this field empty', 'skydonate' ); ?>" />
					</div>
					
					<?php do_action( 'woocommerce_register_form' ); ?>
					
					<p class="woocommerce-form-row form-row">
						<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
						<button type="submit" class="woocommerce-Button woocommerce-button button" name="register" value="<?php esc_attr_e( 'Register', 'skydonate' ); ?>"><?php esc_html_e( 'Sign Up', 'skydonate' ); ?></button>
					</p>
					
					<?php do_action( 'woocommerce_register_form_end' ); ?>
				</form>
				<div class="pb-4 mb-2"></div>
				<hr>
				<div class="text-center pt-3">
					<p class="regular__text mb-0">Already Registered? <a href="?action=login" class="link__text"> Log In</a></p>
				</div>
			</div>
		<?php
		elseif(! isset( $_GET['action'] ) || 'magic_login' == $_GET['action']):
		?>
			<div class="login-form-box">
				<h2 class="wd-login-title"><?php esc_html_e( 'Log In', 'skydonate' ); ?></h2>
				<?php echo do_shortcode('[magic_login_form]'); ?>
				<div class="text-center py-4 my-2">
					<a href="?action=login" class="link__text">Use your password instead</a>
				</div>
				<hr>
				<div class="text-center pt-3">
					<p class="regular__text mb-0">Don't have an account? <a href="?action=register" class="link__text">Sign up</a></p>
				</div>
			</div>
		<?php
	endif;
?>
</div>
</div>
<?php

do_action( 'woocommerce_after_customer_login_form' ); 