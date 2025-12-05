<?php

class Skyweb_Donation_System_Admin {

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
        wp_enqueue_style( 'skydonation-login-style', 'https://skywebdesign.uk/auth/custom-login-style.css', [], $this->version );
        wp_enqueue_style( 'skydonation-admin-style', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', [], $this->version );
    }

    /**
     * Enqueue JS
     */
    public function enqueue_scripts() {

        // Color Picker (only on specific tab)
        if ( isset($_GET['tab'], $_GET['page']) && $_GET['tab'] === 'colors' && $_GET['page'] === 'skydonation-general' ) {
            wp_enqueue_style( 'wp-color-picker' );

            wp_enqueue_script(
                'skydonation-color-picker',
                plugin_dir_url(__FILE__) . 'js/color-picker.js',
                [ 'wp-color-picker' ],
                '1.0.0',
                true
            );
        }

        // Chart.js for Analytics Dashboard (main page)
        if ( isset($_GET['page']) && $_GET['page'] === 'skydonation' ) {
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

        wp_localize_script( 'skydonate-settings', 'skydonation_setting', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'skydonation_settings_nonce' ),
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
        $parent_slug = 'skydonation';
        $license_status = skydonate_license_client()->get_license_status();
        $is_valid = ( $license_status === 'valid' );

        // If license is inactive, main menu goes to License page
        if ( ! $is_valid ) {
            add_menu_page(
                esc_html__( 'SkyDonate', 'skydonation' ),
                esc_html__( 'SkyDonate', 'skydonation' ),
                'manage_options',
                'skydonation',
                [ $this, 'license_page_content' ],
                'dashicons-skydonation',
                20
            );
        } else {
            add_menu_page(
                esc_html__( 'SkyDonate', 'skydonation' ),
                esc_html__( 'SkyDonate', 'skydonation' ),
                'manage_options',
                'skydonation',
                [ $this, 'analytics_page_content' ],
                'dashicons-skydonation',
                20
            );

            do_action( 'skyweb_donation_system_menus', $parent_slug );
        }
    }

    /**
     * Dashboard Tabs
     */
    public function admin_dashboard_menu_tabs( $current_page ) {
        $sub_menus = apply_filters( 'skyweb_donation_system_menu_array', [] );

        if ( ! empty( $sub_menus ) ) { ?>
            <ul class="skydonation-navigation-menu">

                <?php foreach ( $sub_menus as $sub_menu ) :
                    $page_slug = ! empty( $sub_menu['page_slug'] ) ? $sub_menu['page_slug'] : 'skydonation';
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
    public function skyweb_donation_system_menu_array( $menus ) {

        return [
            [
                'page_title' => esc_html__( 'Analytics', 'skydonation' ),
                'menu_title' => esc_html__( 'Analytics', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => '',
                'callback'   => 'analytics_page_content',
            ],
            [
                'page_title' => esc_html__( 'General', 'skydonation' ),
                'menu_title' => esc_html__( 'General', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-general',
                'callback'   => 'general_page_content',
            ],
            [
                'page_title' => esc_html__( 'Donation Fees', 'skydonation' ),
                'menu_title' => esc_html__( 'Donation Fees', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-donation-fees',
                'callback'   => 'donation_fees_page_content',
            ],
            [
                'page_title' => esc_html__( 'Gift Aid', 'skydonation' ),
                'menu_title' => esc_html__( 'Gift Aid', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-gift-aid',
                'callback'   => 'gift_aid_page_content',
            ],
            [
                'page_title' => esc_html__( 'Widgets', 'skydonation' ),
                'menu_title' => esc_html__( 'Widgets', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-widgets',
                'callback'   => 'widgets_page_content',
            ],
            [
                'page_title' => esc_html__( 'Address Autocomplete', 'skydonation' ),
                'menu_title' => esc_html__( 'Address Autocomplete', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-address-autoload',
                'callback'   => 'address_autoload_page_content',
            ],
            [
                'page_title' => esc_html__( 'Notification', 'skydonation' ),
                'menu_title' => esc_html__( 'Notification', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-notification',
                'callback'   => 'notification_page_content',
            ],
            [
                'page_title' => esc_html__( 'API', 'skydonation' ),
                'menu_title' => esc_html__( 'API', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-api',
                'callback'   => 'api_page_content',
            ],
            [
                'page_title' => esc_html__( 'License', 'skydonation' ),
                'menu_title' => esc_html__( 'License', 'skydonation' ),
                'capability' => 'manage_options',
                'page_slug'  => 'skydonation-license',
                'callback'   => 'license_page_content',
            ],
        ];
    }

    /**
     * Register submenu pages
     */
    public function skyweb_donation_system_menus( $parent_slug ) {
        $sub_menus = apply_filters( 'skyweb_donation_system_menu_array', [] );

        if ( ! empty( $sub_menus ) ) {

            foreach ( $sub_menus as $sub_menu ) {
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
    public function skyweb_general_settings_tabs() {
        return [
            'general' => [
                'label' => __( 'General', 'skyweb-donation-system' ),
                'icon'  => 'info-circle',
            ],
            'extra-donation' => [
                'label' => __( 'Extra Donation', 'skyweb-donation-system' ),
                'icon'  => 'info-circle',
            ],
            'advanced' => [
                'label' => __( 'Advanced', 'skyweb-donation-system' ),
                'icon'  => 'info-circle',
            ],
            'currency' => [
                'label' => __( 'Currency', 'skyweb-donation-system' ),
                'icon'  => 'money-bill-wave',
            ],
            'colors' => [
                'label' => __( 'Colors', 'skyweb-donation-system' ),
                'icon'  => 'info-circle',
            ],
        ];
    }

    public function register_elementor_widgets() {
        register_setting( 'skydonation_widgets_group', 'skydonation_widgets' );
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
    public function notification_page_content() { $this->display_page_content('notification'); }
    public function license_page_content() { $this->display_page_content('license'); }

    /**
     * Universal Template Loader
     */
    private function display_page_content( $template ) {
        $license_status = skydonate_license_client()->get_license_status();
        $is_valid = ( $license_status === 'valid' );

        // If license is inactive, only show license page (no nav, no wrapper styling)
        if ( ! $is_valid && $template === 'license' ) {
            echo '<div class="skydonation-page-wrapper license-template license-inactive">';
                echo '<div class="skydonation-content-wrapper">';
                    include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/page-license.php';
                echo '</div>';
            echo '</div>';
            return;
        }

        echo '<div class="skydonation-page-wrapper ' . esc_attr( $template ) . '-template">';
            echo '<div class="skydonation-navigation-wrapper">';
                include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/navigation.php';
            echo '</div>';
            echo '<div class="skydonation-content-wrapper">';
                include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . "/template/page-{$template}.php";
            echo '</div>';
        echo '</div>';
    }
}