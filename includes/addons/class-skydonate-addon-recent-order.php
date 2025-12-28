<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Recent_Order extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_recent_orders';
    }

    public function get_title() {
        return __('Recent Donation', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-woocommerce';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['recent-donation', 'donation-button'];
    }

    public function get_script_depends() {
        return ['recent-donation'];
    }

    protected function register_controls() {
        $is_layout_two = (skydonate_get_layout('recent_donation') == 'layout-2');

        // Section for Layout Options (only show relevant controls based on global layout)
        $this->start_controls_section(
            'layout_section',
            [
                'label' => __('Layout Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Show current layout info
        $this->add_control(
            'layout_info',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => sprintf(
                    '<div style="padding: 10px; background: #f0f0f0; border-radius: 4px;">%s: <strong>%s</strong></div>',
                    __('Current Layout', 'skydonate'),
                    $is_layout_two ? __('Layout 2', 'skydonate') : __('Layout 1', 'skydonate')
                ),
                'content_classes' => 'elementor-panel-alert',
            ]
        );

        // Layout 2 specific controls
        if ($is_layout_two) {
            // ---- Media Type Selector ----
            $this->add_control(
                'recent_icon_media_type',
                [
                    'label' => __('Media Type', 'skydonate'),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'label_block' => false,
                    'options' => [
                        'icon' => [
                            'title' => __('Icon', 'skydonate'),
                            'icon' => 'eicon-star',
                        ],
                        'image' => [
                            'title' => __('Image', 'skydonate'),
                            'icon' => 'eicon-image',
                        ],
                    ],
                    'default' => 'image',
                ]
            );

            // ---- Image Control ----
            $this->add_control(
                'recent_icon_image',
                [
                    'label' => __('Image', 'skydonate'),
                    'type' => \Elementor\Controls_Manager::MEDIA,
                    'default' => [
                        'url' => SKYDONATE_PUBLIC_ASSETS . '/img/give-hand.svg',
                    ],
                    'condition' => [
                        'recent_icon_media_type' => 'image',
                    ],
                ]
            );

            // ---- Icon Control ----
            $this->add_control(
                'recent_icon_icon',
                [
                    'label' => __('Icon', 'skydonate'),
                    'type' => \Elementor\Controls_Manager::ICONS,
                    'label_block' => true,
                    'default' => [
                        'value'   => 'fas fa-hand-holding-heart',
                        'library' => 'solid',
                    ],
                    'condition' => [
                        'recent_icon_media_type' => 'icon',
                    ],
                ]
            );

            // Responsive Column Count
            $this->add_responsive_control(
                'column_count',
                [
                    'label'   => __( 'Columns', 'skydonate' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => [
                        'one' => __( '1 Column', 'skydonate' ),
                        'two' => __( '2 Columns', 'skydonate' ),
                        'three' => __( '3 Columns', 'skydonate' ),
                    ],
                    'default' => 'one',
                    'devices' => [ 'desktop', 'tablet', 'mobile' ],
                    'desktop_default' => 'one',
                    'tablet_default'  => 'one',
                    'mobile_default'  => 'one',
                ]
            );
        }

        $this->end_controls_section();

        // Section for filtering options
        $this->start_controls_section(
            'filter_section',
            [
                'label' => __('Filter Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Enable/Disable Filter
        $this->add_control(
            'enable_filters',
            [
                'label' => __('Enable Filters', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        // Product Title Filter
        $this->add_control(
            'filter_product_title',
            [
                'label' => __('Product Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // Category Filter
        $this->add_control(
            'filter_category',
            [
                'label' => __('Category', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Taxonomies('product_cat'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // Tag Filter
        $this->add_control(
            'filter_tag',
            [
                'label' => __('Tag', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Taxonomies('product_tag'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control("post_limit", [
            "label" => __("Limit", "skydonate"),
            "type" => \Elementor\Controls_Manager::NUMBER,
            "default" => 8,
            "separator" => "before",
        ]);

        $this->end_controls_section();

        // See All Options
        $this->start_controls_section(
            'see_all_donation_section',
            [
                'label' => __( 'See All Options', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'all_donate_button_text',
            [
                'label'       => __( 'Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'See All', 'skydonate' ),
            ]
        );

        $this->add_control(
            'all_donate_button_icon',
            [
                'label'   => __( 'Button Icon', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-list',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'see_all_limit',
            [
                'label'   => __( 'Limit', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
            ]
        );

        $this->end_controls_section();

        // See Top Options
        $this->start_controls_section(
            'see_top_donation_section',
            [
                'label' => __( 'See Top Options', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'top_donate_button_text',
            [
                'label'       => __( 'Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'See Top', 'skydonate' ),
            ]
        );

        $this->add_control(
            'top_donate_button_icon',
            [
                'label'   => __( 'Top Button Icon', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'see_top_limit',
            [
                'label'   => __( 'Limit', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
            ]
        );

        $this->end_controls_section();

        // Modal Button Options
        $this->start_controls_section(
            'modal_section',
            [
                'label' => __( 'Modal Button', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'modal_button_text',
            [
                'label'       => __( 'Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'Donate & Support', 'skydonate' ),
            ]
        );

        $this->add_control(
            'modal_button_icon',
            [
                'label' => __( 'Button Icon', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::ICONS,
            ]
        );

        $this->add_control(
            'modal_button_link',
            [
                'label'       => __( 'Button Link', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => __( 'https://example.com', 'skydonate' ),
                'default'     => [
                    'url'         => '',
                    'is_external' => false,
                    'nofollow'    => false,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $is_layout_two = (skydonate_get_layout('recent_donation') == 'layout-2');
        $layout = $is_layout_two ? 'layout-2' : 'layout-1';

        $enable_filters = $settings['enable_filters'] === 'yes';
        $filter_product_title = (array) $settings['filter_product_title'];

        if (is_product() && !$enable_filters) {
            $filter_product_title = array(get_queried_object_id());
        }

        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $post_limit = $settings['post_limit'] ?: 8;
        $see_top_limit = $settings['see_top_limit'] ?: 20;
        $see_all_limit = $settings['see_all_limit'] ?: 20;

        $all_button_text = $settings['all_donate_button_text'] ?? __('See All', 'skydonate');
        $top_button_text = $settings['top_donate_button_text'] ?? __('See Top', 'skydonate');
        $modal_button_text = $settings['modal_button_text'] ?? __('Donate & Support', 'skydonate');
        $all_button_icon = Skydonate_Icon_Manager::render_icon($settings['all_donate_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $top_button_icon = Skydonate_Icon_Manager::render_icon($settings['top_donate_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $modal_button_icon = Skydonate_Icon_Manager::render_icon($settings['modal_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $modal_button_link = $this->get_settings_for_display('modal_button_link');

        // Get list icon for Layout 2
        $list_icon = '';
        if ($is_layout_two && !empty($settings['recent_icon_media_type'])) {
            if ($settings['recent_icon_media_type'] === 'icon' && !empty($settings['recent_icon_icon']['value'])) {
                $list_icon = Skydonate_Icon_Manager::render_icon($settings['recent_icon_icon'], ['aria-hidden' => 'true']);
            } elseif ($settings['recent_icon_media_type'] === 'image' && !empty($settings['recent_icon_image']['url'])) {
                $list_icon = '<img src="' . esc_url($settings['recent_icon_image']['url']) . '" alt="">';
            }
        }

        // Check if on a fundraising page
        if ( is_singular( 'fundraising' ) && ! $enable_filters ) {
            $page_id = get_queried_object_id();
            if ( function_exists( 'skydonate_get_fundraising_order_ids' ) ) {
                $base_product_id = get_post_meta( $page_id, SkyDonate_Fundraising_CPT::META_BASE_PRODUCT_ID, true );
                $order_ids = skydonate_get_fundraising_order_ids( $page_id, $post_limit );
                $product_ids = $base_product_id ? array( $base_product_id ) : array();

                $this->render_widget($order_ids, $product_ids, $settings, $layout, $list_icon);
                return;
            }
        }

        if ((!$filter_product_title && !$filter_category && !$filter_tag)) {
            return;
        }

        // Collect product IDs based on filters
        $product_ids = array_merge(
            !empty( $filter_product_title ) ? $filter_product_title : [],
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies( $filter_category, 'product_cat' ),
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies( $filter_tag, 'product_tag' )
        );

        $product_ids = array_unique( $product_ids );
        $product_ids = array_values( $product_ids );

        if (empty($product_ids)) {
            return;
        }

        // Get cached order IDs
        $order_ids = Skydonate_Functions::get_cached_orders_ids_by_product_ids($product_ids, $post_limit);

        $this->render_widget($order_ids, $product_ids, $settings, $layout, $list_icon);
    }

    /**
     * Render the widget output
     */
    private function render_widget($order_ids, $product_ids, $settings, $layout, $list_icon = '') {
        if (empty($order_ids)) {
            return;
        }

        $is_layout_two = ($layout === 'layout-2');
        $post_limit = $settings['post_limit'] ?: 8;
        $see_top_limit = $settings['see_top_limit'] ?: 20;
        $see_all_limit = $settings['see_all_limit'] ?: 20;

        $all_button_text = $settings['all_donate_button_text'] ?? __('See All', 'skydonate');
        $top_button_text = $settings['top_donate_button_text'] ?? __('See Top', 'skydonate');
        $modal_button_text = $settings['modal_button_text'] ?? __('Donate & Support', 'skydonate');
        $all_button_icon = Skydonate_Icon_Manager::render_icon($settings['all_donate_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $top_button_icon = Skydonate_Icon_Manager::render_icon($settings['top_donate_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $modal_button_icon = Skydonate_Icon_Manager::render_icon($settings['modal_button_icon'], ['aria-hidden' => 'true']) ?? '';
        $modal_button_link = $settings['modal_button_link'] ?? [];

        // Build data for JavaScript
        $data = [
            'product_ids'   => $product_ids,
            'see_all_limit' => $see_all_limit,
            'see_top_limit' => $see_top_limit,
            'post_limit'    => $post_limit,
            'layout'        => $layout,
            'list_icon'     => $list_icon,
        ];

        // Build wrapper classes
        $wrapper_classes = ['recent-donation-wrapper', $layout];

        if ($is_layout_two) {
            $col_desktop = !empty($settings['column_count']) ? $settings['column_count'] : 'one';
            $col_tablet  = !empty($settings['column_count_tablet']) ? $settings['column_count_tablet'] : $col_desktop;
            $col_mobile  = !empty($settings['column_count_mobile']) ? $settings['column_count_mobile'] : $col_tablet;

            $wrapper_classes[] = 'columns-desktop-' . $col_desktop;
            $wrapper_classes[] = 'columns-tablet-' . $col_tablet;
            $wrapper_classes[] = 'columns-mobile-' . $col_mobile;
        }

        $this->add_render_attribute('wrapper', 'class', $wrapper_classes);
        $this->add_render_attribute('wrapper', 'data-settings', wp_json_encode($data));
        ?>
        <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
            <div class="sky-slide-donations">
                <div class="sky-recent-donations-list">
                    <ul class="sky-donations-orders">
                        <?php
                        if ($is_layout_two) {
                            Skydonate_Functions::render_recent_donations_item_layout_two($order_ids, $product_ids, $list_icon, true);
                        } else {
                            Skydonate_Functions::render_recent_donations_item_layout_one($order_ids, $product_ids, true);
                        }
                        ?>
                    </ul>
                </div>
            </div>

            <div class="sky-modal-actions">
                <button type="button" class="button primary-button see-all-button"><?php echo esc_html($all_button_text) . $all_button_icon; ?></button>
                <button type="button" class="button primary-button see-top-button"><?php echo esc_html($top_button_text) . $top_button_icon; ?></button>
            </div>

            <div class="sky-modal">
                <div class="sky-modal_overlay"></div>
                <div class="sky-modal_body">
                    <div class="sky-modal_header">
                        <div class="sky-modal_header-top">
                            <h3 class="sky-modal_title"><?php esc_html_e('Donations', 'skydonate'); ?></h3>
                            <button class="sky-modal_close"><i class="far fa-times"></i></button>
                        </div>
                        <div class="sky-modal_tabs">
                            <button type="button" class="button all-button"><?php echo esc_html($all_button_text) . $all_button_icon; ?></button>
                            <button type="button" class="button top-button"><?php echo esc_html($top_button_text) . $top_button_icon; ?></button>
                        </div>
                    </div>
                    <div class="sky-modal_content">
                        <div class="sky-modal_tab-contents">
                            <div class="sky-modal_tab sky-modal_tab-all">
                                <div class="sky-recent-donations-list">
                                    <ul class="sky-donations-orders"></ul>
                                    <div class="items-loader"><?php echo Skydonate_Functions::loader_icon(); ?></div>
                                </div>
                            </div>
                            <div class="sky-modal_tab sky-modal_tab-top">
                                <div class="sky-recent-donations-list">
                                    <ul class="sky-donations-orders"></ul>
                                    <div class="items-loader"><?php echo Skydonate_Functions::loader_icon(); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sky-modal_footer">
                        <?php
                        $button_url = !empty($modal_button_link['url']) ? $modal_button_link['url'] : '#';
                        ?>
                        <a href="<?php echo esc_url($button_url); ?>" class="button btn-full">
                            <?php echo esc_html($modal_button_text) . $modal_button_icon; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
