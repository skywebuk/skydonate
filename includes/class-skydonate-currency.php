<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'Skydonate_Currency_Changer' ) ) :

class Skydonate_Currency_Changer {
    
    private $api_url;

    public function __construct() {
        // Get saved CurrencyAPI key from settings
        $api_key = get_option( 'currencyapi_key' );

        // If no key is saved, optionally handle fallback
        if ( empty( $api_key ) ) {
            $api_key = ''; // You can leave this blank or use a test key for local use
        }
        
        // Build the API URL dynamically
        $this->api_url = 'https://api.currencyapi.com/v3/latest?apikey=' . trim( $api_key );
   
        // Enqueue JS and CSS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // AJAX handlers
        add_action('wp_ajax_skydonate_change_currency', [$this, 'ajax_change_currency']);
        add_action('wp_ajax_nopriv_skydonate_change_currency', [$this, 'ajax_change_currency']);

        add_action('wp_ajax_skydonate_update_amount_on_currency', [$this, 'skydonate_update_amount_on_currency']);
        add_action('wp_ajax_nopriv_skydonate_update_amount_on_currency', [$this, 'skydonate_update_amount_on_currency']);

        // WooCommerce currency filter
        add_filter('woocommerce_currency', [$this, 'set_woocommerce_currency']);

        // Early cookie setup
        add_action('init', [$this, 'maybe_set_currency_cookie']);
        
        // Schedule daily update
        add_action('wp', [$this, 'schedule_currency_update']);
        add_action('skydonate_update_currency_rates', [$this, 'update_currency_rates']);
    }

    /**
     * Schedule the daily event
     */
    public function schedule_currency_update() {
        if ( ! wp_next_scheduled( 'skydonate_update_currency_rates' ) ) {
            wp_schedule_event( time(), 'daily', 'skydonate_update_currency_rates' );
        }
    }

    /**
     * Fetch and save exchange rates
     */
    public function update_currency_rates() {
        $response = wp_remote_get( $this->api_url, [ 'timeout' => 20 ] );

        if ( is_wp_error( $response ) ) {
            return;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            $formatted = [];
            // Simplify the structure: code => value
            foreach ( $data['data'] as $code => $rate_info ) {
                $formatted[ $code ] = floatval( $rate_info['value'] );
            }
            update_option( 'skydonate_currency_rates', [
                'timestamp' => time(),
                'rates'     => $formatted,
            ] );
        }
    }

    /**
     * Retrieve the full saved rates array
     */
    public static function get_saved_rates() {
        $data = get_option( 'skydonate_currency_rates', [] );
        return $data['rates'] ?? [];
    }

    /**
     * Get single currency rate by code (e.g. 'BDT')
     */
    public static function get_rate( $from = 'GBP', $to = 'USD' ) {
        $rates = self::get_saved_rates();
        $from  = strtoupper( $from );
        $to    = strtoupper( $to );

        if ( empty( $rates[ $from ] ) || empty( $rates[ $to ] ) ) {
            return null;
        }

        // Prevent division by zero
        if ( floatval( $rates[ $from ] ) === 0.0 ) {
            return null;
        }

        // Convert via USD base
        $value = $rates[ $to ] / $rates[ $from ];
        return round( $value, 4 );
    }

    public static function convert_currency( $baseCurrency, $targetCurrency, $amount ) {
        $rates = self::get_saved_rates(); // Retrieve saved currency rates
        $baseCurrency   = strtoupper( $baseCurrency );
        $targetCurrency = strtoupper( $targetCurrency );

        // Check if rates exist
        if ( empty( $rates[ $baseCurrency ] ) || empty( $rates[ $targetCurrency ] ) ) {
            // Return original amount if rate missing
            return $amount;
        }

        // Prevent division by zero
        if ( floatval( $rates[ $baseCurrency ] ) === 0.0 ) {
            return $amount;
        }

        // Conversion via USD base (or your saved rates logic)
        $rate = $rates[ $targetCurrency ] / $rates[ $baseCurrency ];
        $convertedAmount = $amount * $rate;

        // Round to nearest integer (like JS Math.round)
        return round( $convertedAmount, 2 );
    }



    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'skydonate-currency',
            SKYDONATE_ASSETS . '/css/currency.css',
            [],
            SKYDONATE_VERSION
        );

        wp_enqueue_script(
            'skydonate-currency',
            SKYDONATE_ASSETS . '/js/currency.js',
            ['jquery'],
            SKYDONATE_VERSION,
            true
        );

        wp_localize_script('skydonate-currency', 'skydonate_currency_changer_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('skydonate_currency_changer_nonce'),
            'rates'    => Skydonate_Currency_Changer::get_saved_rates(),
            'woocommerce_currency' => get_option('woocommerce_currency')
        ]);
    }

    /**
     * Safely set cookie early (runs before output)
     */
    public function maybe_set_currency_cookie() {
        // Skip if cookie already exists
        if (isset($_COOKIE['skydonate_selected_currency'])) {
            return;
        }

        $geo_enabled = get_option('skydonate_geo_currency_enabled', 0);
        $geo_mode = get_option('skydonate_geo_currency_mode', 'all'); // 'all' or 'selected'
        $geo_default_all = get_option('skydonate_geo_default_all', 0); // Show all currencies by default

        // If default-all is enabled, just use all currencies
        if ($geo_default_all) {
            $selected_currencies = array_keys(get_woocommerce_currencies());
            $currency_to_set = get_woocommerce_currency(); // Default WooCommerce currency
        } else {
            $selected_currencies = (array) get_option('skydonate_selected_currency', []);
            $currency_to_set = get_woocommerce_currency();

            // Geolocation detection
            if ($geo_enabled) {
                $country_code = Skydonate_Functions::get_user_country_name('code');
                $detected_currency = Skydonate_Functions::get_currency_by_country_code($country_code);

                if ($geo_mode === 'all' && !empty($detected_currency)) {
                    $currency_to_set = $detected_currency;
                } elseif ($geo_mode === 'selected' && !empty($detected_currency) && in_array($detected_currency, $selected_currencies, true)) {
                    $currency_to_set = $detected_currency;
                }
            }

            // Ensure default currency is always in the list
            if (!in_array($currency_to_set, $selected_currencies, true)) {
                $selected_currencies[] = $currency_to_set;
            }
        }

        // Set cookie with SameSite attribute for CSRF protection
        if (!headers_sent()) {
            $cookie_options = array(
                'expires'  => time() + MONTH_IN_SECONDS,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            );
            setcookie( 'skydonate_selected_currency', $currency_to_set, $cookie_options );
        }
        $_COOKIE['skydonate_selected_currency'] = $currency_to_set;
    }

    /**
     * Frontend currency changer HTML
     */
    public static function currency_changer() {
        $post_id = get_the_ID();
        $donation_currency_override = get_post_meta($post_id, '_donation_currency_override', true);
        $selected_currencies = (array) get_option('skydonate_selected_currency', []);
        $default_currency = get_woocommerce_currency();

        // Include all currencies if default-all is enabled
        $geo_default_all = get_option('skydonate_geo_default_all', 0);
        if ($geo_default_all) {
            $selected_currencies = array_keys(get_woocommerce_currencies());
        }

        // Ensure default currency is always available
        if (!in_array($default_currency, $selected_currencies, true)) {
            $selected_currencies[] = $default_currency;
        }

        // Detect user country → map to currency
        $geo_enabled = get_option('skydonate_geo_currency_enabled', 0);
        $geo_mode = get_option('skydonate_geo_currency_mode', 'all');
        $country_code = Skydonate_Functions::get_user_country_name('code');
        $detected_currency = Skydonate_Functions::get_currency_by_country_code($country_code);
        

        if (!empty($_COOKIE['skydonate_selected_currency'])) {
            $current_currency = sanitize_text_field($_COOKIE['skydonate_selected_currency']);
        } elseif ($geo_enabled) {
            if ($geo_mode === 'all' && !empty($detected_currency)) {
                $current_currency = $detected_currency;
            } elseif ($geo_mode === 'selected' && in_array($detected_currency, $selected_currencies, true)) {
                $current_currency = $detected_currency;
            } else {
                $current_currency = $default_currency;
            }
            $_COOKIE['skydonate_selected_currency'] = $current_currency;
        } else {
            $current_currency = $default_currency;
        }

        // Disable switcher if donation override
        $disable_switcher = ($donation_currency_override === 'yes');
        if ($disable_switcher) {
            $current_currency = $default_currency;
        }
        

        $all_currencies = get_woocommerce_currencies();

        

        // --- Build markup ---
        $html  = '<div class="skydonate-currency-wrapper '.($disable_switcher ? 'switcher-off' : 'switcher-on').'">';
        $html .= '<div class="currency-symbol"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol($current_currency)) . '</span></div>';
        if(!$disable_switcher):
        $html .= '<select class="skydonate-currency-select" style="position:absolute;">';
        foreach ($selected_currencies as $currency) {
            $symbol = get_woocommerce_currency_symbol($currency);
            $name   = $all_currencies[$currency] ?? $currency;
            $html .= '<option value="' . esc_attr($currency) . '" data-symbol="' . esc_attr($symbol) . '" ' . selected($current_currency, $currency, false) . '>'
                    . esc_html($currency . ' (' . $symbol . ')')
                    . '</option>';
        }
        $html .= '</select>';
        endif;
        $html .= '</div>';
        return $html;
    }

    /**
     * AJAX handler for currency change
     */
    public function ajax_change_currency() {
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'skydonate_currency_changer_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        if (empty($_POST['currency'])) {
            wp_send_json_error('No currency provided');
        }

        $currency = sanitize_text_field($_POST['currency']);

        // Save in cookie (1 month - consistent with initial cookie) with SameSite attribute
        if (!headers_sent()) {
            $cookie_options = array(
                'expires'  => time() + MONTH_IN_SECONDS,
                'path'     => COOKIEPATH ?: '/',
                'domain'   => COOKIE_DOMAIN,
                'secure'   => is_ssl(),
                'httponly' => true,
                'samesite' => 'Lax',
            );
            setcookie( 'skydonate_selected_currency', $currency, $cookie_options );
        }
        $_COOKIE['skydonate_selected_currency'] = $currency;

        wp_send_json_success(['currency' => $currency]);
    }

    public function skydonate_update_amount_on_currency() {
        // Verify nonce
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'skydonate_currency_changer_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Validate required fields
        if (empty($_POST['currency']) || empty($_POST['amount'])) {
            wp_send_json_error(['message' => 'Currency or amount missing']);
        }

        $currency = sanitize_text_field($_POST['currency']);
        $amount   = floatval($_POST['amount']); // ensure it's numeric

        // Get rate using WooCommerce base currency instead of hardcoded GBP
        $base_currency = get_option('woocommerce_currency', 'GBP');
        $rate = Skydonate_Currency_Changer::get_rate($base_currency, $currency);

        if (!$rate || !is_numeric($rate)) {
            wp_send_json_error(['message' => 'Invalid or missing exchange rate']);
        }

        // Calculate converted amount
        $converted_amount = $amount * $rate;

        // Return response
        wp_send_json_success([
            'original_amount'  => $amount,
            'converted_amount' => round($converted_amount, 2),
            'currency'         => $currency,
            'rate'             => $rate,
        ]);
    }


    /**
     * WooCommerce currency override filter
     */
    public function set_woocommerce_currency($currency) {
        global $post;

        if (isset($post) && get_post_meta($post->ID, '_donation_currency_override', true) === 'yes') {
            return $currency;
        }

        if (!empty($_COOKIE['skydonate_selected_currency'])) {
            return sanitize_text_field($_COOKIE['skydonate_selected_currency']);
        }

        $geo_enabled = get_option('skydonate_geo_currency_enabled', 0);
        $geo_mode = get_option('skydonate_geo_currency_mode', 'all');
        $geo_default_all = get_option('skydonate_geo_default_all', 0);

        if ($geo_enabled) {
            $country_code = Skydonate_Functions::get_user_country_name('code');
            $detected_currency = Skydonate_Functions::get_currency_by_country_code($country_code);
            $selected_currencies = (array) get_option('skydonate_selected_currency', []);

            if ($geo_mode === 'all' && !empty($detected_currency)) {
                return $detected_currency;
            } elseif ($geo_mode === 'selected' && in_array($detected_currency, $selected_currencies, true)) {
                return $detected_currency;
            }
        }

        return $currency;
    }
    /**
     * Cleanup scheduled events on plugin deactivation
     * Call this method from the main plugin file's deactivation hook
     */
    public static function deactivate() {
        wp_clear_scheduled_hook( 'skydonate_update_currency_rates' );
    }
}

endif;