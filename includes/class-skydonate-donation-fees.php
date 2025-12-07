<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Donation_Fees {

    public function __construct() {
        add_shortcode('optional_fee_checkbox', array($this, 'optional_fee_checkbox_shortcode'));
        add_action('wp_footer', array($this, 'enqueue_optional_fee_script_inline'),99);
        add_action( 'woocommerce_checkout_update_order_review', [ $this, 'handle_checkbox_submission' ],99 );
		add_action( 'woocommerce_cart_calculate_fees', [ $this, 'add_optional_fee' ], 1 );
        add_filter('woocommerce_paypal_args', array($this, 'add_optional_fee_to_paypal'), 10, 1);
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_optional_fee'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'display_optional_fee_in_admin'), 10, 1);
    }

    public function optional_fee_checkbox_shortcode() {
        if (!sky_status_check('enable_donation_fees')) {
            return false; // Return nothing if the functionality is disabled
        }
        if ( ! WC()->cart ) {
            return false;
        }
        $cart_items = WC()->cart->get_cart();
        foreach ( $cart_items as $cart_item ) {
            if ( isset( $cart_item['cover_fees'] ) && $cart_item['cover_fees'] === 'no' ) {
                return false;
            }
            break;
        }

        $checkbox_label = get_option('checkbox_label', __('Cover transaction fees', 'woocommerce'));
        $additional_text = get_option('additional_text', 'Would you like to cover the transaction costs so that we receive 100% of your gift? ❤️');
        $tooltip_text = get_option('fees_tooltip_text', 'By including <span id="fee_amount">[Amount auto calculated]</span> in transaction costs, you cover our processing and platform fees.');
        $checkbox_status = get_option('fees_checkbox_default_status') == '1' ? 'checked' : '';

        ob_start();
        ?>
        <div id="optional_fee_field">
            <label for="optional_fee" class="wootooltip sky-smart-switch checkbox">
                <input type="checkbox" name="optional_fee" id="optional_fee" class="input-checkbox" onclick="toggleText()" <?php echo esc_attr($checkbox_status); ?>>
                <span class="switch"></span>
                <?php echo esc_html($checkbox_label); ?>
            </label>
            
            <span class="tooltip-container" onclick="toggleTooltip()">
                <div class="ctooltip"> <span class="tooltip-icon">?</span><span class="ctooltiptext"><?php echo wp_kses_post($tooltip_text); ?></span></div>
                <span class="tooltip-text"><?php echo wp_kses_post($tooltip_text); ?></span>
            </span>
            
            <div id="additionalText">
                <?php echo esc_html($additional_text); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_optional_fee_script_inline() {
        if (is_checkout()) {
            ?>
            <script>
                (function($) {
                    $(document).ready(function() {
                        function updateFeeAmount() {
                            let feePercentage = <?php echo get_option('donation_fee_percentage', 1.7); ?>;
                            let cartTotal = parseFloat('<?php echo WC()->cart->cart_contents_total + WC()->cart->get_taxes_total(); ?>');
                           
                            let feeAmount = (feePercentage / 100) * cartTotal;
                            $("#fee_amount").text(wc_price(feeAmount));
                        }
    
                        function toggleAdditionalText() {
                            if ($("input[name=optional_fee]").is(':checked')) {
                                $("#additionalText").hide();
                            } else {
                                $("#additionalText").show();
                            }
                        }
    
                        $("input[name=optional_fee]").on("change", function() {
                            updateFeeAmount();
                            toggleAdditionalText(); // Initial state check
                            $(document.body).trigger("update_checkout");
                        });

                        updateFeeAmount();
    
                        function wc_price(amount) {
                            return new Intl.NumberFormat('en-US', { style: 'currency', currency: '<?php echo get_woocommerce_currency(); ?>' }).format(amount);
                        }
    
                        // Initial fee amount calculation
                        toggleAdditionalText(); // Initial state check
                    });
                })(jQuery);
            </script>
            <?php
        }
    }

  

    public function add_optional_fee_to_paypal($paypal_args) {
        if (WC()->session->get('optional_fee') === 'yes') {
            $fee = WC()->session->get('optional_fee_amount');
            $paypal_args['amount'] += $fee;
            $paypal_args['item_name_1'] .= ' + ' . __('Donation Transaction Fee', 'woocommerce');
        }
        return $paypal_args;
    }
    
    public function save_optional_fee($order_id) {
        if (WC()->session->get('optional_fee') === 'yes') {
            $fee = WC()->session->get('optional_fee_amount');
            update_post_meta($order_id, '_optional_fee', 'yes');
            update_post_meta($order_id, '_optional_fee_amount', $fee);
        }
    }

    public function display_optional_fee_in_admin($order) {
        $has_optional_fee = get_post_meta($order->get_id(), '_optional_fee', true);
        if ($has_optional_fee === 'yes') {
            $fee_amount = get_post_meta($order->get_id(), '_optional_fee_amount', true);
            echo '<p><strong>' . __('Optional Fee', 'woocommerce') . ':</strong> ' . wc_price($fee_amount) . '</p>';
        }
    }
    

	// 2. Process checkbox value and store in session
	public function handle_checkbox_submission( $post_data ) {
		parse_str( $post_data, $parsed );
		if ( isset( $parsed['optional_fee'] ) && $parsed['optional_fee'] === 'on' ) {
			WC()->session->set( 'optional_fee', 'yes' );
		} else {
			WC()->session->set( 'optional_fee', 'no' );
		}
	}

	// 3. Apply optional fee
	public function add_optional_fee( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( WC()->session->get( 'optional_fee' ) === 'yes' ) {
			$percentage = (float) get_option( 'donation_fee_percentage', 1.7 ); // Default 1.7%
			$cart_total = $cart->cart_contents_total + $cart->get_taxes_total();
			$fee = ( $percentage / 100 ) * $cart_total;

			$cart->add_fee( __( 'Donation Transaction Fee', 'woocommerce' ), $fee, false );
			WC()->session->set( 'optional_fee_amount', $fee );
		} else {
			WC()->session->set( 'optional_fee_amount', 0 );
		}
	}
}

new WC_Donation_Fees();
?>