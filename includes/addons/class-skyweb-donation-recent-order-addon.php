<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Recent_Order_Addon extends \Elementor\Widget_Base {

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
        return ['skyweb_donation'];
    }        
    
    public function get_style_depends() {                                                                                                                           
        return ['recent-donation', 'donation-button'];                                                                                                                                
    }
    
    public function get_script_depends() {                                                                                                                           
        return ['recent-donation'];                                                                                                                                
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
                'options' => Skyweb_Donation_Functions::Get_Title('product', 'ids'), // Make sure this returns product IDs
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
                'options' => Skyweb_Donation_Functions::Get_Taxonomies('product_cat'),
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
                'options' => Skyweb_Donation_Functions::Get_Taxonomies('product_tag'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control("post_limit", [
            "label" => __("Limit", "skyweb"),
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
                'label'   => __( 'Limit', 'skyweb' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'see_top_donation_section',
            [
                'label' => __( 'See Top Options', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Top Button Controls
        $this->add_control(
            'top_donate_button_text',
            [
                'label'       => __( 'Top Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'See Top', 'skydonate' ),
                'placeholder' => __( 'Enter button text', 'skydonate' ),
            ]
        );

      
        $this->add_control(
            'top_donate_button_icon',
            [
                'label'   => __( 'Top Button Icon', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-star', // ⭐ default icon
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'see_top_limit',
            [
                'label'   => __( 'Limit', 'skyweb' ),
                'type'    => \Elementor\Controls_Manager::NUMBER,
                'default' => 20,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'modal_section',
            [
                'label' => __( 'Modal Button', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Modal Button Text
        $this->add_control(
            'modal_button_text',
            [
                'label'       => __( 'Modal Button Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'Donate & Support', 'skydonate' ),
                'placeholder' => __( 'Enter modal button text', 'skydonate' ),
            ]
        );

        // Modal Button Icon
        $this->add_control(
            'modal_button_icon',
            [
                'label' => __( 'Modal Button Icon', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::ICONS,
            ]
        );

        // Modal Button Link
        $this->add_control(
            'modal_button_link',
            [
                'label'       => __( 'Modal Button Link', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => __( 'https://example.com', 'skydonate' ),
                'default'     => [
                    'url'         => '',
                    'is_external' => false,
                    'nofollow'    => false,
                ],
                'show_label' => true,
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
        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $post_limit = $settings['post_limit'] ?: 8;
        $see_top_limit = $settings['see_top_limit'] ?: 8;
        $see_all_limit = $settings['see_all_limit'] ?: 8;
        $no_donations_message = esc_html($settings['no_donations_message']);
        $all_button_text = $settings['all_donate_button_text'] ?? __('See All', 'skydonate');
        $top_button_text = $settings['top_donate_button_text'] ?? __('See Top', 'skydonate');
        $modal_button_text = $settings['modal_button_text'] ?? __('Donate & Support', 'skydonate');
        $all_button_icon = Skyweb_Addons_Icon_manager::render_icon($settings['all_donate_button_icon'], ['aria-hidden' => 'true']) ?? null;
        $top_button_icon = Skyweb_Addons_Icon_manager::render_icon($settings['top_donate_button_icon'], ['aria-hidden' => 'true']) ?? null;
        $modal_button_icon = Skyweb_Addons_Icon_manager::render_icon($settings['modal_button_icon'], ['aria-hidden' => 'true']) ?? null;
        $modal_button_link = $this->get_settings_for_display('modal_button_link');
        if ((!$filter_product_title && !$filter_category && !$filter_tag)) {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
            return;
        }
        
        // Collect product IDs based on filters
        $product_ids = array_merge(
            !empty( $filter_product_title ) ? $filter_product_title : [],
            Skyweb_Donation_Functions::get_product_ids_by_multiple_taxonomies( $filter_category, 'product_cat' ),
            Skyweb_Donation_Functions::get_product_ids_by_multiple_taxonomies( $filter_tag, 'product_tag' )
        );

        // Remove duplicates and reindex
        $product_ids = array_unique( $product_ids );
        $product_ids = array_values( $product_ids );

        // Store data
        $data['product_ids']   = $product_ids;
        $data['see_all_limit'] = $see_all_limit;
        $data['see_top_limit'] = $see_top_limit;
        $data['post_limit'] = $post_limit;


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

        echo '<div '.$this->get_render_attribute_string( "wrapper_attributes" ).'>';
            if ($product_ids) {
                echo '<div class="sky-slide-donations">';
                    $order_ids = Skyweb_Donation_Functions::get_orders_ids_by_product_id($product_ids, ['wc-completed'], $post_limit);
                    if (!empty($order_ids)) {
                        echo '<div class="sky-recent-donations-list">';
                            echo '<ul class="sky-donations-orders">';
                            Skyweb_Donation_Functions::render_recent_donations_item_layout_one($order_ids, $product_ids, true);
                            echo '</ul>';
                        echo '</div>';
                    } else {
                        echo "<div class='woocommerce-info'>$no_donations_message</div>";
                    }
                echo '</div>';
                echo '<div class="sky-modal-actions">';
                echo '<button type="button" class="button primary-button see-all-button">'.$all_button_text . $all_button_icon.'</button>';
                echo '<button type="button" class="button primary-button see-top-button">'.$top_button_text . $top_button_icon.'</button>';
                echo '</div>';
                echo '<div class="sky-modal">';
                    echo '<div class="sky-modal_overlay"></div>';
                    echo '<div class="sky-modal_body">';
                        echo '<div class="sky-modal_header">';
                            echo '<div class="sky-modal_header-top">';
                                echo '<h3 class="sky-modal_title">' . __('Donations', 'skydonate') . '</h3>';
                                echo '<button class="sky-modal_close"><i class="far fa-times"></i></button>';
                            echo '</div>';
                            echo '<div class="sky-modal_tabs">';
                                echo '<button type="button" class="button all-button">'
                                        . $all_button_text . $all_button_icon .
                                    '</button>';
                                echo '<button type="button" class="button top-button">'
                                        . $top_button_text . $top_button_icon .
                                    '</button>';
                            echo '</div>';
                        echo '</div>';
                        echo '<div class="sky-modal_content">';
                            echo '<div class="sky-modal_tab-contents">';
                                echo '<div class="sky-modal_tab sky-modal_tab-all">';
                                    echo '<div class="sky-recent-donations-list">';
                                        echo '<ul class="sky-donations-orders"></ul>';
                                        echo '<div class="items-loader">';
                                            echo Skyweb_Donation_Functions::loader_icon();
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                                echo '<div class="sky-modal_tab sky-modal_tab-top">';
                                    echo '<div class="sky-recent-donations-list">';
                                        echo '<ul class="sky-donations-orders"></ul>';
                                        echo '<div class="items-loader">';
                                            echo Skyweb_Donation_Functions::loader_icon();
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</div>';
                        echo '</div>';
                        echo '<div class="sky-modal_footer">';
                            echo '<a href="' . esc_url($modal_button_link['url']) . '" class="button btn-full">';
                                echo esc_html($modal_button_text);
                                echo $modal_button_icon;
                            echo '</a>';
                        echo '</div>';
                    echo '</div>';
                echo '</div>';
            }else {
                echo "<div class='woocommerce-info'>$no_donations_message</div>";
            }
        echo '</div>';
    }

    public function recent_donation_all_list($product_ids, $limit, $hidden_class = false, $loader = false){
        return false;
    }
}
?>