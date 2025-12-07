<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Elementor_Addons {

    public function __construct() {
        add_action('elementor/init', [$this, 'register_elementor_controls']);
        add_action('elementor/elements/categories_registered', [$this, 'register_widget_categories']);


        // Hook the API data update function to the daily cron event
        add_action('daily_api_data_update', [$this, 'fetch_and_store_api_data']);
        // Schedule the fetch operation to run daily
        if (!wp_next_scheduled('daily_api_data_update')) {
            wp_schedule_event(time(), 'daily', 'daily_api_data_update');
        }
    }


    public static function Get_Title($post_type = 'post') {
        // query for your post type
        $post_type_query = new WP_Query(array(
            'post_type' => $post_type,
            'posts_per_page' => -1
        ));
        // we need the array of posts
        $posts_array = $post_type_query->posts;
        // create a list with needed information
        // the key equals the ID, the value is the post_title
        $post_title = wp_list_pluck($posts_array, 'post_title', 'ID');

        return $post_title;
    }

    public function add_zakat_fund_to_cart() {
        $product_id = intval($_POST['product_id']);
        $zakat_due = floatval($_POST['zakat_amount']);
        $zakat_fund_name = sanitize_text_field($_POST['zakat_fund_name']);
        $zakat_fund_type = sanitize_text_field($_POST['zakat_fund_type']);

        $cart_items = array(
            'zakat_due' => $zakat_due,
            'zakat_fund_name' => $zakat_fund_name,
            'zakat_fund_type' => $zakat_fund_type
        );

        WC()->cart->add_to_cart( $product_id, 1, 0, array(), $cart_items );

        wp_send_json(array(
            'success' => true
        ));
    }

    public function register_widgets($widgets_manager) {
        if (skydonate_is_widget_enabled('zakat_calculator_classic')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-zakat-calculator-classic.php');
            $widgets_manager->register(new \Skydonate_Zakat_Calculator_Classic());
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-zakat-preview.php');
            $widgets_manager->register(new \Skydonate_Zakat_Preview());
        }

       if (skydonate_is_widget_enabled('metal_values')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-metal-values.php');
            $widgets_manager->register(new \Skydonate_Metal_Values());
        }

        

        if (skydonate_is_widget_enabled('recent_order')) {
            if(skydonate_get_layout('recent_donation') == 'layout-2'){
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-recent-order-2.php');
                $widgets_manager->register(new \Skydonate_Recent_Order_2());
            } else {
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-recent-order.php');
                $widgets_manager->register(new \Skydonate_Recent_Order());

            }
        }

        if (skydonate_is_widget_enabled('donation_progress')) {
            if(skydonate_get_layout('progress_bar') == 'layout-2'){
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-progress-2.php');
                $widgets_manager->register(new \Skydonate_Progress_2());
            } else {
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-progress.php');
                $widgets_manager->register(new \Skydonate_Progress());
            }
        }
        if (skydonate_is_widget_enabled('donation_form')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-form.php');
            $widgets_manager->register(new \Skydonate_Form());
        }


         if (skydonate_is_widget_enabled('donation_card')) {
            if(skydonate_get_layout('addons_card') == 'layout-2'){
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-card-2.php');
                $widgets_manager->register(new \Skydonate_Card_2());
            } else {
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-card.php');
                $widgets_manager->register(new \Skydonate_Card());
            }
        }


        if (skydonate_is_widget_enabled('impact_slider')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-impact-slider.php');
            $widgets_manager->register(new \Skydonate_Impact_Slider());
        }

        if (skydonate_is_widget_enabled('qurbani_status')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-qurbani-status.php');
            $widgets_manager->register(new \Skydonate_Qurbani_Status());
        }

        if (skydonate_is_widget_enabled('extra_donation')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-extra-donation.php');
            $widgets_manager->register(new \Skydonate_Extra_Donation_Widget());
        }

        if (skydonate_is_widget_enabled('quick_donation')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-quick-donate.php');
            $widgets_manager->register(new \Skydonate_Quick_Donation());
        }

        if (skydonate_is_feature_enabled( 'enhanced_gift_aid' ) && sky_status_check( 'enable_gift_aid' ) && skydonate_is_widget_enabled('gift_aid_toggle') ) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-gift-aid-toggle.php');
            $widgets_manager->register(new \Skydonate_Gift_Aid_Toggle());
        }

        if (skydonate_is_widget_enabled('donation_button')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-button.php');
            $widgets_manager->register(new \Skydonate_Button());
        }

        if (skydonate_is_widget_enabled('icon_slider')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skydonate-addon-icon-list.php');
            $widgets_manager->register(new \Skydonate_Icon_List());
        }

    }


    public function register_elementor_controls() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'skydonate',
            [
                'title' => __('SkyDonate', 'skydonate'),
                'icon' => 'fa fa-donate',
            ],
            1
        );
    }


    // Function to fetch price data from the API
    private function fetch_price($metal_code, $currency, $headers) {
        $url = "https://www.goldapi.io/api/$metal_code/$currency";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        // Decode the response
        $data = json_decode($response, true);

        // Check for API errors (assuming the API returns an 'error' key in case of failure)
        if (isset($data['error'])) {
            return ['error' => $data['error']]; // Return the error message if present
        }

        return $data;
    }

    // Store results in WordPress options
    private function store_api_data($results) {
        update_option('metal_price', array(
            'data' => $results,
            'updated_at' => current_time('mysql'),
        ));
    }

    // Function to fetch and store API data
    public function fetch_and_store_api_data() {
        // Retrieve and sanitize the API key
        $apiKey = sanitize_text_field(get_option('zakat_calc_api'));

        // Exit early if the API key is not set
        if (empty($apiKey)) {
            return; // You could add an admin notice or log an error here if needed
        }

        $headers = [
            "x-access-token: $apiKey",
            "Content-Type: application/json"
        ];

        $currencies = ["USD", "GBP", "EUR"];
        $metals = ["XAU" => "Gold", "XAG" => "Silver"];
        $results = [];

        // Fetch prices
        foreach ($metals as $metal_code => $metal_name) {
            $results[$metal_name] = [];
            foreach ($currencies as $currency) {
                $data = $this->fetch_price($metal_code, $currency, $headers);

                // Check if there's an error in the fetched data, skip updating if there's an error
                if (isset($data['error'])) {
                    return; // Stop the function if any error is encountered
                }

                if (isset($data['price_gram_24k'])) {
                    $results[$metal_name][$currency] = esc_html($data['price_gram_24k']);
                } else {
                    $results[$metal_name][$currency] = 0;
                }
            }
        }
        // Store the results if there are no errors
        $this->store_api_data($results);
    }

    // Clear scheduled event on plugin deactivation
    public static function deactivate() {
        wp_clear_scheduled_hook('daily_api_data_update');
    }

}

// Initialize the class
new Skydonate_Elementor_Addons();

// Backwards compatibility alias
class_alias( 'Skydonate_Elementor_Addons', 'SkyWeb_Donation_Addons' );

// Register deactivation hook
register_deactivation_hook(SKYDONATE_FILE, ['Skydonate_Elementor_Addons', 'deactivate']);
