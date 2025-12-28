<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WC_Custom_Donation_Settings')) {

    class WC_Custom_Donation_Settings {

        public function __construct() {
            add_action('admin_notices', array($this, 'remove_wc_notices'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
            add_action('wp_loaded', array($this, 'conditionally_add_title_prefix_class'));
        }
        
        public function remove_wc_notices() {
            if (get_current_screen()->id === 'toplevel_page_wc-custom-donation-settings') {
                remove_all_actions('admin_notices');
            }
        }

        public function enqueue_admin_scripts() {
            if (get_current_screen()->id === 'toplevel_page_wc-custom-donation-settings') {
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');
            }
        }


        public function conditionally_add_title_prefix_class() {
            if (sky_status_check('enable_title_prefix')) {
                require_once plugin_dir_path(__FILE__) . 'class-skydonate-wc-title-prefix.php';
                new WC_Title_Prefix();
            }
        }
    }

    if (is_admin()) {
        new WC_Custom_Donation_Settings();
    }
}
?>
