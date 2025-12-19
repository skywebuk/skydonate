<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Recent_Order_2 extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_recent_orders_2';
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
        return ['recent-donation-two', 'donation-button'];                                                                                                                                
    }
    
    public function get_script_depends() {                                                                                                                           
        return ['recent-donation-two'];                                                                                                                                
    }

    protected function register_controls() {
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

        // Product Title Filter as a select dropdown
        $this->add_control(
            'filter_product_title',
            [
                'label' => __('Product Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true, // Allow multiple product selection
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Make sure this returns product IDs
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

        // No Donations Message
        $this->add_control(
            'no_donations_message',
            [
                'label' => __('Not Found Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No recent donations for this project.', 'skydonate'),
                'placeholder' => __('Enter the message to display when no donations are found', 'skydonate'),
            ]
        );

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
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        // ---- Image Size Control ----
        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'      => 'recent_icon_image_size',
                'default'   => 'large',
                'separator' => 'none',
                'exclude'   => [
                    'full',
                    'custom',
                    'large',
                    'shop_catalog',
                    'shop_single',
                    'shop_thumbnail',
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

        // ====================================
        // RESPONSIVE COLUMN COUNT CONTROL
        // ====================================
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

        
        $this->end_controls_section();

        $this->start_controls_section(
            'see_all_donation_section',
            [
                'label' => __( 'See All Options', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // All Button Controls
        $this->add_control(
            'all_donate_button_text',
            [
                'label'       => __( 'All Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'See All', 'skydonate' ),
                'placeholder' => __( 'Enter button text', 'skydonate' ),
            ]
        );

        $this->add_control(
            'all_donate_button_icon',
            [
                'label'   => __( 'All Button Icon', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-list',   // your default icon class
                    'library' => 'fa-solid',       // icon library
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


        // Style Section for Button
        $this->start_controls_section(
            'button_style',
            [
                'label' => __('Button Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'button_style_mode',
            [
                'label' => __('Style Mode', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'flat' => __('Flat', 'skydonate'),
                    'border' => __('Border', 'skydonate'),
                    'link' => __('Link', 'skydonate'),
                ],
                'default' => 'flat',
            ]
        );
        
        $this->add_control(
            'predefined_color',
            [
                'label' => __('Predefined Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'gray' => __('Gray', 'skydonate'),
                    'primary' => __('Primary', 'skydonate'),
                    'alternative' => __('Alternative', 'skydonate'),
                    'black' => __('Black', 'skydonate'),
                    'white' => __('White', 'skydonate'),
                    'custom' => __('Custom', 'skydonate'),
                ],
                'default' => 'primary',
            ]
        );

        $this->start_controls_tabs('button_style_tabs',
        [
            'condition' => [
                'predefined_color' => [ 'custom' ],
            ],
        ]);

        // Normal Tab
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'skydonate'),
            ]
        );

        // Button Text Color (Normal)
        $this->add_control(
            'button_color_normal',
            [
                'label' => __('Text Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .button-custom .primary-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Button Background Color (Normal)
        $this->add_control(
            'button_background_normal',
            [
                'label' => __('Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .button-custom .primary-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "button_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .button-custom .primary-button",
        ]);

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Hover', 'skydonate'),
            ]
        );

        // Button Text Color (Hover)
        $this->add_control(
            'button_color_hover',
            [
                'label' => __('Text Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .button-custom .primary-button:hover, {{WRAPPER}} .button-custom .primary-button.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "button_hover_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .button-custom .primary-button:hover, {{WRAPPER}} .button-custom .primary-button.active",
        ]);

        // Button Background Color (Hover)
        $this->add_control(
            'button_background_hover',
            [
                'label' => __('Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .button-custom .primary-button:hover, {{WRAPPER}} .button-custom .primary-button.active' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_control(
            'button_size',
            [
                'label' => __('Predefined Size', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'small' => __('Small', 'skydonate'),
                    'medium' => __('Medium', 'skydonate'),
                    'large' => __('Large', 'skydonate'),
                    'extra_large' => __('Extra Large', 'skydonate'),
                ],
                'default' => 'medium',
                'condition' => [
                    'button_style_mode!' => 'link',
                ],
            ]
        );

        $this->add_control(
            'button_shape',
            [
                'label' => __('Shape', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'rectangle' => __('Rectangle', 'skydonate'),
                    'round' => __('Round', 'skydonate'),
                    'rounded' => __('Rounded', 'skydonate'),
                ],
                'default' => 'rectangle',
                'condition' => [
                    'button_style_mode!' => 'link',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .primary-button',
            ]
        );

        $this->end_controls_section(); // End Button Style Section
    }
    
    protected function render() {
        $data = array();
        $settings = $this->get_settings_for_display();
        $enable_filters = $settings['enable_filters'] === 'yes';
        $filter_product_title = (array) $settings['filter_product_title'];
        if (is_product() && !$enable_filters) {
            $filter_product_title = array(get_queried_object_id());
        }
        $order_count = '';
        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $post_limit = $settings['post_limit'] ?: 8;
        $see_all_limit = $settings['see_all_limit'] ?: 20;
        $no_donations_message = esc_html($settings['no_donations_message']);
        $all_button_text = $settings['all_donate_button_text'] ?? __('See All', 'skydonate');
        $all_button_icon = Skydonate_Icon_Manager::render_icon($settings['all_donate_button_icon'], ['aria-hidden' => 'true']) ?? null;

        // Check if on a fundraising page
        if ( is_singular( 'fundraising' ) && ! $enable_filters ) {
            $page_id = get_queried_object_id();
            if ( function_exists( 'skydonate_get_fundraising_order_ids' ) ) {
                $base_product_id = get_post_meta( $page_id, SkyDonate_Fundraising_CPT::META_BASE_PRODUCT_ID, true );
                $order_ids = skydonate_get_fundraising_order_ids( $page_id, $post_limit );
                $product_ids = $base_product_id ? array( $base_product_id ) : array();

                // Get list icon
                $list_icon = '';
                if (!empty($settings['recent_icon_media_type'])) {
                    if (
                        $settings['recent_icon_media_type'] === 'icon' &&
                        !empty($settings['recent_icon_icon']['value'])
                    ) {
                        $list_icon = Skydonate_Icon_Manager::render_icon(
                            $settings['recent_icon_icon'],
                            ['aria-hidden' => 'true']
                        );
                    } elseif (
                        $settings['recent_icon_media_type'] === 'image' &&
                        !empty($settings['recent_icon_image']['url'])
                    ) {
                        $list_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html(
                            $settings,
                            'recent_icon_image_size',
                            'recent_icon_image'
                        );
                    }
                }

                $this->add_render_attribute( 'wrapper_attributes', 'class', ['recent-donation-wrapper'] );
                if(isset($settings['button_style_mode'])){
                    $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_style_mode'] );
                }
                if(isset($settings['predefined_color'])){
                    $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['predefined_color'] );
                }
                if(isset($settings['button_size'])){
                    $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_size'] );
                }
                if(isset($settings['button_shape'])){
                    $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_shape'] );
                }

                $col_desktop = !empty($settings['column_count']) ? $settings['column_count'] : 'one';
                $col_tablet  = !empty($settings['column_count_tablet']) ? $settings['column_count_tablet'] : $col_desktop;
                $col_mobile  = !empty($settings['column_count_mobile']) ? $settings['column_count_mobile'] : $col_tablet;

                $this->add_render_attribute(
                    'wrapper_attributes',
                    'class',
                    [
                        'columns-desktop-' . $col_desktop,
                        'columns-tablet-'  . $col_tablet,
                        'columns-mobile-'  . $col_mobile,
                    ]
                );

                echo '<div '.$this->get_render_attribute_string( "wrapper_attributes" ).'>';
                if ( ! empty( $order_ids ) ) {
                    echo '<div class="sky-default-donations">';
                    echo '<div class="sky-recent-donations-list">';
                    echo '<ul class="sky-donations-orders">';
                    Skydonate_Functions::render_recent_donations_item_layout_two( $order_ids, $product_ids, $list_icon );
                    echo '</ul>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo "<div class='woocommerce-info'>$no_donations_message</div>";
                }
                echo '</div>';
                return;
            }
        }

        if ((!$filter_product_title && !$filter_category && !$filter_tag)) {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
            return;
        }

        // Collect product IDs based on filters
        $product_ids = array_merge(
            $filter_product_title, 
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies($filter_category, 'product_cat'),
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies($filter_tag, 'product_tag')
        );

        $list_icon = '';
        if (!empty($settings['recent_icon_media_type'])) {
            if (
                $settings['recent_icon_media_type'] === 'icon' &&
                !empty($settings['recent_icon_icon']['value'])
            ) {
                $list_icon = Skydonate_Icon_Manager::render_icon(
                    $settings['recent_icon_icon'],
                    ['aria-hidden' => 'true']
                );
            } elseif (
                $settings['recent_icon_media_type'] === 'image' &&
                !empty($settings['recent_icon_image']['url'])
            ) {
                $list_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html(
                    $settings,
                    'recent_icon_image_size',
                    'recent_icon_image'
                );
            }
        }


        $product_ids = array_unique( $product_ids );
        $product_ids = array_values( $product_ids );
        $data['product_ids'] = $product_ids;
        $data['see_all_limit'] = $see_all_limit;
        $data['post_limit'] = $post_limit;
        $data['list_icon'] = $list_icon;

        $this->add_render_attribute( 'wrapper_attributes', 'class', ['recent-donation-wrapper'] );
        if(isset($settings['button_style_mode'])){
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_style_mode'] );
        }
        if(isset($settings['predefined_color'])){
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['predefined_color'] );
        }
        if(isset($settings['button_size'])){
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_size'] );
        }
        if(isset($settings['button_shape'])){
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-'.$settings['button_shape'] );
        }

        $this->add_render_attribute( 'wrapper_attributes', 'data-settings', wp_json_encode($data) );
        
        $col_desktop = !empty($settings['column_count']) ? $settings['column_count'] : 'one';
        $col_tablet  = !empty($settings['column_count_tablet']) ? $settings['column_count_tablet'] : $col_desktop;
        $col_mobile  = !empty($settings['column_count_mobile']) ? $settings['column_count_mobile'] : $col_tablet;

        $this->add_render_attribute(
            'wrapper_attributes',
            'class',
            [
                'columns-desktop-' . $col_desktop,
                'columns-tablet-'  . $col_tablet,
                'columns-mobile-'  . $col_mobile,
            ]
        );


        echo '<div '.$this->get_render_attribute_string( "wrapper_attributes" ).'>';
            if ($product_ids) {
                echo '<div class="sky-default-donations" >';
                    $order_ids = Skydonate_Functions::get_orders_ids_by_product_id($product_ids, ['wc-completed'], $post_limit);
                    if (!empty($order_ids)) {
                        echo '<div class="sky-recent-donations-list">';
                            echo '<ul class="sky-donations-orders">';
                                Skydonate_Functions::render_recent_donations_item_layout_two($order_ids, $product_ids, $list_icon);
                            echo '</ul>';
                        echo '</div>';
                    } else {
                        echo "<div class='woocommerce-info'>$no_donations_message</div>";
                    }
                echo '</div>';
                echo '<div class="sky-modal-actions">';
                echo '<button type="button" class="button primary-button see-all-button">'.$all_button_text . $all_button_icon.'</button>';
                echo '</div>';
                echo '<div class="sky-modal">';
                    echo '<div class="sky-modal_overlay"></div>';
                    echo '<div class="sky-modal_body">';
                        echo '<div class="sky-modal_header">';
                            echo '<div class="sky-modal_header-top">';
                                echo '<h3 class="sky-modal_title">' . __('Donations', 'skydonate') . '</h3>';
                                echo '<button class="sky-modal_close"><i class="far fa-times"></i></button>';
                            echo '</div>';
                        echo '</div>';
                        echo '<div class="sky-modal_content">';
                            echo '<div class="sky-modal_tab-contents">';
                                echo '<div class="sky-modal_tab sky-modal_tab-all">';
                                    echo '<div class="sky-recent-donations-list">';
                                        echo '<ul class="sky-donations-orders"></ul>';
                                        echo '<div class="items-loader">';
                                            echo Skydonate_Functions::loader_icon();
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }else {
                echo "<div class='woocommerce-info'>$no_donations_message</div>";
            }
        echo '</div>';
    }
}
?>