<?php
/**
 * SkyDonate Admin Class
 *
 * @package    SkyDonate
 * @subpackage SkyDonate/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Skydonate_Admin {

    private $plugin_name;
    private $version;

    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Enqueue CSS
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'skydonate-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', [], $this->version );
    }

    /**
     * Enqueue JS
     */
    public function enqueue_scripts() {

        // Color Picker (only on specific tab)
        if ( isset($_GET['tab'], $_GET['page']) && $_GET['tab'] === 'colors' && $_GET['page'] === 'skydonate-general' ) {
            wp_enqueue_style( 'wp-color-picker' );

            wp_enqueue_script(
                'skydonate-color-picker',
                plugin_dir_url(__FILE__) . 'js/color-picker.js',
                [ 'wp-color-picker' ],
                '1.0.0',
                true
            );
        }

        // Chart.js for Analytics Dashboard (main page)
        if ( isset($_GET['page']) && $_GET['page'] === 'skydonate' ) {
            wp_enqueue_script(
                'chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                [],
                '4.4.1',
                true
            );
        }

        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'select2' );

        wp_enqueue_script(
            'skydonate-settings',
            plugin_dir_url( __FILE__ ) . 'js/skydonate-settings.js',
            [ 'jquery' ],
            $this->version,
            true
        );

        wp_localize_script( 'skydonate-settings', 'skydonate_setting', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'skydonate_settings_nonce' ),
        ]);

        wp_enqueue_script(
            'skydonate-admin',
            plugin_dir_url( __FILE__ ) . 'js/skydonate-admin.js',
            [ 'jquery' ],
            $this->version,
            true
        );
    }

    /**
     * Admin Menu
     */
    public function add_admin_menu() {
        $parent_slug = 'skydonate';
        $is_valid = skydonate_license()->is_valid();

        // If license is inactive, main menu goes to License page
        if ( ! $is_valid ) {
            add_menu_page(
                esc_html__( 'SkyDonate', 'skydonate' ),
                esc_html__( 'SkyDonate', 'skydonate' ),
                'manage_options',
                'skydonate',
                [ $this, 'license_page_content' ],
                'dashicons-skydonate',
                20
            );
        } else {
            add_menu_page(
                esc_html__( 'SkyDonate', 'skydonate' ),
                esc_html__( 'SkyDonate', 'skydonate' ),
                'manage_options',
                'skydonate',
                [ $this, 'analytics_page_content' ],
                'dashicons-skydonate',
                20
            );

            do_action( 'skydonate_menus', $parent_slug );
        }
    }

    /**
     * Dashboard Tabs
     */
    public function admin_dashboard_menu_tabs( $current_page ) {
        $sub_menus = apply_filters( 'skydonate_menu_array', [] );

        if ( ! empty( $sub_menus ) ) { ?>
            <ul class="skydonate-navigation-menu">

                <?php foreach ( $sub_menus as $sub_menu ) :
                    // Skip if validation is set and returns false
                    if ( isset( $sub_menu['validation'] ) && ! $sub_menu['validation'] ) {
                        continue;
                    }

                    $page_slug = ! empty( $sub_menu['page_slug'] ) ? $sub_menu['page_slug'] : 'skydonate';
                    $class     = isset( $sub_menu['class'] ) ? esc_attr( $sub_menu['class'] ) : '';
                ?>
                    <li <?php echo $class ? 'class="' . $class . '"' : ''; ?>>
                        <a href="<?php echo admin_url( 'admin.php?page=' . $page_slug ); ?>"
                        class="nav-link <?php echo ( $current_page == $page_slug ) ? 'active' : ''; ?>">
                            <?php echo esc_html( $sub_menu['page_title'] ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>

            </ul>
        <?php }
    }

    /**
     * Submenu Array
     */
    public function skydonate_menu_array( $menus ) {

        return [
            [
                'page_title' => esc_html__( 'Analytics', 'skydonate' ),
                'menu_title' => esc_html__( 'Analytics', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate',
                'callback'   => 'analytics_page_content',
            ],
            [
                'page_title' => esc_html__( 'General', 'skydonate' ),
                'menu_title' => esc_html__( 'General', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-general',
                'callback'   => 'general_page_content',
            ],
            [
                'page_title' => esc_html__( 'Donation Fees', 'skydonate' ),
                'menu_title' => esc_html__( 'Donation Fees', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-donation-fees',
                'callback'   => 'donation_fees_page_content',
                'validation'  => skydonate_is_feature_enabled('donation_fees'),
            ],
            [
                'page_title' => esc_html__( 'Gift Aid', 'skydonate' ),
                'menu_title' => esc_html__( 'Gift Aid', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-gift-aid',
                'callback'   => 'gift_aid_page_content',
                'validation'  => skydonate_is_feature_enabled('enhanced_gift_aid'),
            ],
            [
                'page_title' => esc_html__( 'Widgets', 'skydonate' ),
                'menu_title' => esc_html__( 'Widgets', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-widgets',
                'callback'   => 'widgets_page_content',
            ],
            [
                'page_title' => esc_html__( 'Address Autocomplete', 'skydonate' ),
                'menu_title' => esc_html__( 'Address Autocomplete', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-address-autoload',
                'callback'   => 'address_autoload_page_content',
            ],
            [
                'page_title' => esc_html__( 'Notification', 'skydonate' ),
                'menu_title' => esc_html__( 'Notification', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-notification',
                'callback'   => 'notification_page_content',
                'validation'  => skydonate_is_feature_enabled('notification'),
            ],
            [
                'page_title' => esc_html__( 'API', 'skydonate' ),
                'menu_title' => esc_html__( 'API', 'skydonate' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonate-api',
                'callback'   => 'api_page_content',
            ],
        ];
    }

    /**
     * Register submenu pages
     */
    public function skydonate_menus( $parent_slug ) {
        $sub_menus = apply_filters( 'skydonate_menu_array', [] );

        if ( ! empty( $sub_menus ) ) {

            foreach ( $sub_menus as $sub_menu ) {
                // Skip if validation is set and returns false
                if ( isset( $sub_menu['validation'] ) && ! $sub_menu['validation'] ) {
                    continue;
                }

                $page_slug = ! empty( $sub_menu['page_slug'] ) ? $sub_menu['page_slug'] : $parent_slug;

                add_submenu_page(
                    $parent_slug,
                    $sub_menu['page_title'],
                    $sub_menu['menu_title'],
                    $sub_menu['capability'],
                    $page_slug,
                    [ $this, $sub_menu['callback'] ]
                );
            }
        }
    }

    /**
     * General Settings Tabs
     */
    public function skydonate_general_settings_tabs() {
        return [
            'general' => [
                'label' => __( 'General', 'skydonate' ),
                'icon'  => 'info-circle',
            ],
            'extra-donation' => [
                'label' => __( 'Extra Donation', 'skydonate' ),
                'icon'  => 'info-circle',
            ],
            'advanced' => [
                'label' => __( 'Advanced', 'skydonate' ),
                'icon'  => 'info-circle',
            ],
            'currency' => [
                'label' => __( 'Currency', 'skydonate' ),
                'icon'  => 'money-bill-wave',
            ],
            'colors' => [
                'label' => __( 'Colors', 'skydonate' ),
                'icon'  => 'info-circle',
            ],
        ];
    }

    public function register_elementor_widgets() {
        register_setting( 'skydonate_widgets_group', 'skydonate_widgets' );
    }

    /**
     * Page templates loader
     */
    public function analytics_page_content() { $this->display_page_content('analytics'); }
    public function api_page_content() { $this->display_page_content('api'); }
    public function general_page_content() { $this->display_page_content('general'); }
    public function donation_fees_page_content() { $this->display_page_content('donation-fees'); }
    public function gift_aid_page_content() { $this->display_page_content('gift-aid'); }
    public function widgets_page_content() { $this->display_page_content('widgets'); }
    public function address_autoload_page_content() { $this->display_page_content('address-autoload'); }
    public function notification_page_content() {
        if(!skydonate_is_feature_enabled('notification')){
            return false;
        }
        $this->display_page_content('notification');
    }
    public function license_page_content() { $this->display_page_content('license'); }

    /**
     * Universal Template Loader
     */
    private function display_page_content( $template ) {
        $is_valid = skydonate_license()->is_valid();

        // If license is inactive, only show license page (no nav, no wrapper styling)
        if ( ! $is_valid && $template === 'license' ) {
            echo '<div class="skydonate-page-wrapper license-template license-inactive">';
                echo '<div class="skydonate-content-wrapper">';
                    include_once SKYDONATE_ADMIN_PATH . '/template/page-license.php';
                echo '</div>';
            echo '</div>';
            return;
        }

        echo '<div class="skydonate-page-wrapper ' . esc_attr( $template ) . '-template">';
            echo '<div class="skydonate-navigation-wrapper">';
                include_once SKYDONATE_ADMIN_PATH . '/template/navigation.php';
            echo '</div>';
            echo '<div class="skydonate-content-wrapper">';
                include_once SKYDONATE_ADMIN_PATH . "/template/page-{$template}.php";
            echo '</div>';
        echo '</div>';
    }
}

// Backwards compatibility alias
class_alias( 'Skydonate_Admin', 'Skyweb_Donation_System_Admin' );
