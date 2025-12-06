<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Addons {

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
        // Conditionally load and register each widget
        if (sky_widget_status_check('zakat_calculator')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-zakat-calculator-addons.php');
            $widgets_manager->register(new \SkyWeb_Donation_Zakat_Calculator_Addons());
        }
        if (sky_widget_status_check('zakat_calculator_classic')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-zakat-calculator-classic.php');
            $widgets_manager->register(new \SkyWeb_Donation_Zakat_Calculator_Classic());
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-zakat-preview.php');
            $widgets_manager->register(new \SkyWeb_Donation_Zakat_Preview());
        }
    
       if (sky_widget_status_check('metal_values')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-metal-values-addons.php');
            $widgets_manager->register(new \SkyWeb_Donation_Metal_Values_Addons());
        }
    
        
        if (sky_widget_status_check('recent_order')) {
            $recent_donation_layout = skyweb_donation_layout_option('recent_donation_layout');
            if (!is_array($recent_donation_layout)) {
                $recent_donation_layout = ['layout1'];
            }
            if (is_array($recent_donation_layout)) {
                if (in_array('layout1', $recent_donation_layout)) {
                    require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-recent-order-addon.php');
                    $widgets_manager->register(new \SkyWeb_Donation_Recent_Order_Addon());
                }
                if (in_array('layout2', $recent_donation_layout)) {
                    require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-recent-order-addon-2.php');
                    $widgets_manager->register(new \SkyWeb_Donation_Recent_Order_Addon_2());
                }
            }
        }
        
        if (sky_widget_status_check('donation_progress')) {
            $progress_bar_layout = skyweb_donation_layout_option('progress_bar_layout');
            if (!is_array($progress_bar_layout)) {
                $progress_bar_layout = ['layout1'];
            }
            if (is_array($progress_bar_layout)) {
                if (in_array('layout1', $progress_bar_layout)) {
                    require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-progress-addon.php');
                    $widgets_manager->register(new \SkyWeb_Donation_Progress_Addon());
                } 
                if (in_array('layout2', $progress_bar_layout)) {
                    require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-progress-addon-2.php');
                    $widgets_manager->register(new \SkyWeb_Donation_Progress_Addon_2());
                }
            }
        }
        if (sky_widget_status_check('donation_form')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-form-addon.php');
            $widgets_manager->register(new \SkyWeb_Donation_Form_Addon());
        }
        

         if (sky_widget_status_check('donation_card')) {
            $addons_card_layout = skyweb_donation_layout_option('addons_card_layout');

            if (!is_array($addons_card_layout)) {
                $addons_card_layout = ['layout1'];
            }
            
            if (in_array('layout1', $addons_card_layout)) {
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-card-addon.php');
              $widgets_manager->register(new \SkyWeb_Donation_Card_Addon());
            }
            if (in_array('layout2', $addons_card_layout)) {
                require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-card-addon-2.php');
                $widgets_manager->register(new \SkyWeb_Donation_Card_Addon_2());
            }
        }        
        
    
        if (sky_widget_status_check('impact_slider')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-impact-slider.php');
            $widgets_manager->register(new \SkyWeb_Donation_Impact_Slider());
        }
        
        if (sky_widget_status_check('qurbani_status')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-qurbani-status.php');
            $widgets_manager->register(new \SkyWeb_Donation_Qurbani_Status());
        }
        
        if (sky_widget_status_check('extra_donation')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-extra-donation.php');
            $widgets_manager->register(new \SkyWeb_Extra_Donation());
        }
        
        if (sky_widget_status_check('quick_donation')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-quick-donate.php');
            $widgets_manager->register(new \SkyWeb_Quick_Donation());
        }
        
        if (sky_widget_status_check('gift_aid_toggle') && get_option('enable_gift_aid', 0) == 1) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-gift-aid-toggle.php');
            $widgets_manager->register(new \SkyWeb_Gift_Aid_Toggle());
        }
    
        if (sky_widget_status_check('donation_button')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-button.php');
            $widgets_manager->register(new \SkyWeb_Donation_Button());
        }

    
        if (sky_widget_status_check('icon_slider')) {
            require_once(plugin_dir_path(__FILE__) . 'addons/class-skyweb-donation-icon-list.php');
            $widgets_manager->register(new \SkyWeb_Donation_Icon_List());
        }
		
    }   


    public function register_elementor_controls() {
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    public function register_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'skyweb_donation',
            [
                'title' => __('SkyWeb Donation', 'skydonate'),
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
new SkyWeb_Donation_Addons();

// Register deactivation hook
register_deactivation_hook(SKYWEB_DONATION_SYSTEM_FILE, ['SkyWeb_Donation_Addons', 'deactivate']);