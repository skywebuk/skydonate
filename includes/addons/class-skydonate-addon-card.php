<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Card extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_card_addon';
    }

    public function get_title() {
        return __('Donation Cards', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-products-archive';
    }

    public function get_categories() {
        return ['skydonate'];
    }        
    
    public function get_style_depends() {
        return ['donation-card', 'lity-lightbox'];
    }

    public function get_script_depends() {
        return ['donation-card', 'lity-lightbox'];
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

        $this->add_control(
            'enable_title_filter',
            [
                'label' => __('Filter By Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // Define the repeater instance
        $repeater = new \Elementor\Repeater();

        // Add the filter_product_title select control inside the repeater
        $repeater->add_control(
            'filter_product_title',
            [
                'label' => __('Donation Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'),
                'default' => '',
                'multiple' => false,
                'label_block' => true,
            ]
        );

        // Add the repeater control to the widget
        $this->add_control(
            'product_list',
            [
                'label' => __('Donation List', 'skydonate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ filter_product_title }}}',
                'condition' => [
                    'enable_filters' => 'yes',
                    'enable_title_filter' => 'yes',
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
                    'enable_title_filter!' => 'yes',
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
                    'enable_title_filter!' => 'yes',
                ],
            ]
        );

        // Exclude Products
        $this->add_control(
            'exclude_products',
            [
                'label' => __('Exclude Donation', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'),
                'default' => '',
                'multiple' => true,
                'label_block' => true,
                'condition' => [
                    'enable_title_filter!' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'order_by',
            [
                'label' => __( 'Order By', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __( 'Date', 'skydonate' ),
                    'title' => __( 'Title', 'skydonate' ),
                    'id' => __( 'ID', 'skydonate' ),
                    'rand' => __( 'Random', 'skydonate' ),
                    'menu_order' => __( 'Menu Order', 'skydonate' ),
                    'author' => __( 'Author', 'skydonate' ),
                    'post__in' => __( 'Post In', 'skydonate' ),
                    'raised_amount' => __( 'Raised Amount', 'skydonate' ),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => __( 'Order', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => __( 'Ascending', 'skydonate' ),
                    'DESC' => __( 'Descending', 'skydonate' ),
                ],
            ]
        );

        $this->add_control("post_limit", [
            "label" => __("Limit", "skydonate"),
            "type" => \Elementor\Controls_Manager::NUMBER,
            "default" => 5,
            "separator" => "after",
        ]);

        $this->add_control(
            'grid_layout',
            [
                'label' => __('Grid Layout', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => __('1 Column', 'skydonate'),
                    '2' => __('2 Columns', 'skydonate'),
                    '3' => __('3 Columns', 'skydonate'),
                    '4' => __('4 Columns', 'skydonate'),
                    '5' => __('5 Columns', 'skydonate'),
                ],
                'default' => '3',
            ]
        );
        
        // Image toggle
        $this->add_control(
            'show_image',
            [
                'label' => __('Show Image', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Title toggle
        $this->add_control(
            'show_title',
            [
                'label' => __('Show Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        $this->add_control(
            'title_tag',
            [
                'label' => __('Title Tag', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'h3',
                'options' => [
                    'h1' => __('H1', 'skydonate'),
                    'h2' => __('H2', 'skydonate'),
                    'h3' => __('H3', 'skydonate'),
                    'h4' => __('H4', 'skydonate'),
                    'h5' => __('H5', 'skydonate'),
                    'h6' => __('H6', 'skydonate'),
                    'div' => __('DIV', 'skydonate'),
                    'span' => __('SPAN', 'skydonate'),
                    'p' => __('P', 'skydonate'),
                ],
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_short_description',
            [
                'label' => __('Show Short Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'short_description_word_limit',
            [
                'label' => __('Short Description Word Limit', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 15,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'condition' => [
                    'show_short_description' => 'yes',
                ],
            ]
        );

        // Learn More button toggle
        $this->add_control(
            'show_learn_more',
            [
                'label' => __('Show Learn More Button', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Learn More button text
        $this->add_control(
            'learn_more_text',
            [
                'label' => __('Learn More Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Learn More', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
                'condition' => [
                    'show_learn_more' => 'yes',
                ],
            ]
        );
        
        // Learn More button icon
        $this->add_control(
            'learn_more_icon',
            [
                'label' => __('Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
                'condition' => [
                    'show_learn_more' => 'yes',
                ],
            ]
        );
    
        // Quick Donate button toggle
        $this->add_control(
            'show_quick_donate',
            [
                'label' => __('Show Quick Donate Button', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Quick Donate button text
        $this->add_control(
            'quick_donate_text',
            [
                'label' => __('Quick Donate Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Quick Donate', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
                'condition' => [
                    'show_quick_donate' => 'yes',
                ],
            ]
        );
        
        // Quick Donate button icon
        $this->add_control(
            'quick_donate_icon',
            [
                'label' => __('Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-donate',
                    'library' => 'solid',
                ],
                'condition' => [
                    'show_quick_donate' => 'yes',
                ],
            ]
        );

        // Progress Bar toggle
        $this->add_control(
            'show_progress_bar',
            [
                'label' => __('Show Progress Bar', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
    
        // Donation details (Raised, Donations, Target) toggle
        $this->add_control(
            'show_donation_details',
            [
                'label' => __('Show Donation Details', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // No Donations Message
        $this->add_control(
            'no_donations_message',
            [
                'label' => __('Not Found Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No project found.', 'skydonate'),
                'placeholder' => __('Enter the message to display when no project are found', 'skydonate'),
            ]
        );
        
        // Secure Donation toggle
        $this->add_control(
            'show_secure_donation',
            [
                'label' => __('Show Secure Donation Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Secure Donation text
        $this->add_control(
            'secure_donation_text',
            [
                'label' => __('Secure Donation Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Secure Donation', 'skydonate'),
                'placeholder' => __('Enter secure donation text', 'skydonate'),
                'condition' => [
                    'show_secure_donation' => 'yes',
                ],
            ]
        );
        
        // Secure Donation icon
        $this->add_control(
            'secure_donation_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-lock',
                    'library' => 'solid',
                ],
                'condition' => [
                    'show_secure_donation' => 'yes',
                ],
            ]
        );

        // Add control for replacing title
        $this->add_control(
            'replace_title',
            [
                'label' => __('Replace Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        // Selected Words text field
        $this->add_control(
            'selected_words',
            [
                'label' => __('Selected Words', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Enter words to replace', 'skydonate'),
                'condition' => [
                    'replace_title' => 'yes',
                ],
            ]
        );

        // New Words text field
        $this->add_control(
            'new_words',
            [
                'label' => __('New Words', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'placeholder' => __('Enter new words', 'skydonate'),
                'condition' => [
                    'replace_title' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_play_button',
            [
                'label' => __('Video Play Button', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();


        $this->Donation_Form_Button_Control();
        /**
         * Style sections for the donation card, titles, short descriptions, etc.
         * Below are exactly as in your existing code. None of the style logic changed.
         */

        // 1) Donation Card Style Section
        $this->start_controls_section("donation_card_style_section", [
            "label" => __("Donation Card Style", "skydonate"),
            "tab" => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        
        $this->start_controls_tabs("donation_card_style_tab");
        $this->start_controls_tab("donation_card_normal", [
            "label" => __("Normal", "skydonate"),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donation_card_typography',
                'selector' => '{{WRAPPER}} .donation-card',
            ]
        );
        
        $this->add_control(
            'donation_card_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Background Control
        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            "name" => "donation_card_background",
            "label" => __("Background", "skydonate"),
            "types" => ["classic", "gradient"],
            "selector" => "{{WRAPPER}} .donation-card",
        ]);
        
        // Border Control
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "donation_card_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .donation-card",
        ]);
        
        // Border Radius Control
        $this->add_responsive_control("donation_card_border_radius", [
            "label" => esc_html__("Border Radius", "skydonate"),
            "type" => \Elementor\Controls_Manager::DIMENSIONS,
            "selectors" => [
                "{{WRAPPER}} .donation-card" =>
                    "border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;",
            ],
        ]);
        
        // Box Shadow Control
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), [
            "name" => "donation_card_box_shadow",
            "label" => __("Box Shadow", "skydonate"),
            "selector" => "{{WRAPPER}} .donation-card",
        ]);
        
        // Margin Control
        $this->add_responsive_control("donation_card_margin", [
            "label" => __("Margin", "skydonate"),
            "type" => \Elementor\Controls_Manager::DIMENSIONS,
            "size_units" => ["px", "%", "em"],
            "selectors" => [
                "{{WRAPPER}} .donation-card" =>
                    "margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
            ],
            "separator" => "before",
        ]);
        
        // Padding Control
        $this->add_responsive_control("donation_card_padding", [
            "label" => __("Padding", "skydonate"),
            "type" => \Elementor\Controls_Manager::DIMENSIONS,
            "size_units" => ["px", "%", "em"],
            "selectors" => [
                "{{WRAPPER}} .donation-card" =>
                    "padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};",
            ],
            "separator" => "before",
        ]);
        
        // Text Alignment Control
        $this->add_responsive_control("donation_card_text_align", [
            "label" => __("Alignment", "skydonate"),
            "type" => \Elementor\Controls_Manager::CHOOSE,
            "options" => [
                "left" => [
                    "title" => __("Left", "skydonate"),
                    "icon" => "eicon-text-align-left",
                ],
                "center" => [
                    "title" => __("Center", "skydonate"),
                    "icon" => "eicon-text-align-center",
                ],
                "right" => [
                    "title" => __("Right", "skydonate"),
                    "icon" => "eicon-text-align-right",
                ],
                "justify" => [
                    "title" => __("Justified", "skydonate"),
                    "icon" => "eicon-text-align-justify",
                ],
            ],
            "selectors" => [
                "{{WRAPPER}} .donation-card" => "text-align: {{VALUE}};",
            ],
            "separator" => "before",
        ]);
        
        // Transform Control
        $this->add_control("donation_card_transform", [
            "label" => __("Transform", "skydonate"),
            "type" => \Elementor\Controls_Manager::TEXT,
            "selectors" => [
                "{{WRAPPER}} .donation-card" => "transform: {{VALUE}}",
            ],
        ]);
        
        // Transition Duration Control
        $this->add_control("donation_card_transition", [
            "label" => __("Transition Duration", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 3, "step" => 0.1]],
            "selectors" => [
                "{{WRAPPER}} .donation-card" =>
                    "transition-duration: {{SIZE}}s",
            ],
        ]);
        
        $this->end_controls_tab();
        
        // Hover Style tab Start
        $this->start_controls_tab("donation_card_hover", [
            "label" => __("Hover", "skydonate"),
        ]);
        
        $this->add_group_control(\Elementor\Group_Control_Background::get_type(), [
            "name" => "donation_card_hover_background",
            "label" => __("Background", "skydonate"),
            "types" => ["classic", "gradient"],
            "selector" => "{{WRAPPER}} .donation-card:hover",
        ]);
        
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "donation_card_border_hover",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .donation-card:hover",
        ]);
        
        $this->add_responsive_control("donation_card_hover_border_radius", [
            "label" => esc_html__("Border Radius", "skydonate"),
            "type" => \Elementor\Controls_Manager::DIMENSIONS,
            "selectors" => [
                "{{WRAPPER}} .donation-card:hover" =>
                    "border-radius: {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;",
            ],
        ]);
        
        $this->add_group_control(\Elementor\Group_Control_Box_Shadow::get_type(), [
            "name" => "donation_card_hover_shadow",
            "label" => __("Box Shadow", "skydonate"),
            "selector" => "{{WRAPPER}} .donation-card:hover",
        ]);
        
        $this->add_control("donation_card_hover_transform", [
            "label" => __("Transform", "skydonate"),
            "type" => \Elementor\Controls_Manager::TEXT,
            "selectors" => [
                "{{WRAPPER}} .donation-card:hover" => "transform: {{VALUE}}",
            ],
        ]);
        
        $this->end_controls_tab(); // Hover Style tab end
        $this->end_controls_tabs(); // Donation Card Style tabs end
        $this->end_controls_section(); // Donation Card section style end

        // 2) Donation Card Title Style
        $this->start_controls_section(
            'donation_card_title_section',
            [
                'label' => __( 'Donation Title', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('donation_card_title_style_tab');
        
        // Normal Style Tab
        $this->start_controls_tab('donation_card_title_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donation_card_title_typography',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donation-title',
            ]
        );
        
        $this->add_control(
            'donation_title_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donation_title_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'donation_title_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'donation_card_title_transition',
            [
                'label' => __( 'Transition Duration', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-title' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donation_title_hover_tab', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_title_hover_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-title:hover a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        // Box Hover Style Tab
        $this->start_controls_tab('donation_box_title_hover_tab', [
            'label' => __( 'Box Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_box_hover_title_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card:hover .donation-content .donation-title a' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Box Hover Style Tab
        
        $this->end_controls_tabs(); // End Donation Title Style Tabs
        $this->end_controls_section(); // End Donation Title Section

        // 3) Donation Short Description Style
        $this->start_controls_section(
            'donation_short_description_section',
            [
                'label' => __( 'Donation Short Description', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_short_description' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('donation_short_description_style_tab');
        
        // Normal Style Tab
        $this->start_controls_tab('donation_short_description_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donation_short_description_typography',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donation-short-description',
            ]
        );
        
        $this->add_control(
            'donation_short_description_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-short-description' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donation_short_description_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-short-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'donation_short_description_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-short-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'donation_short_description_transition',
            [
                'label' => __( 'Transition Duration', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-short-description' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donation_short_description_hover_tab', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_short_description_hover_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donation-short-description:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        // Box Hover Style Tab
        $this->start_controls_tab('donation_box_short_description_hover_tab', [
            'label' => __( 'Box Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_box_hover_short_description_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card:hover .donation-content .donation-short-description' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Box Hover Style Tab
        
        $this->end_controls_tabs(); // End Donation Short Description Style Tabs
        $this->end_controls_section(); // End Donation Short Description Section

        // 4) Donate Button Style
        $this->start_controls_section(
            'donate_button_style_section',
            [
                'label' => __( 'Donate Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_learn_more' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('donate_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('donate_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donate_button_typography',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_control(
            'donate_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_control(
            'donate_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_responsive_control(
            'donate_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donate_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donate_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donate_button_hover_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_control(
            'donate_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_control(
            'donate_button_hover_transition',
            [
                'label' => __( 'Transition Duration', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section(); // End Donate Button Style Section

        // 5) Quick Donate Button Style
        $this->start_controls_section(
            'quick_button_section',
            [
                'label' => __( 'Quick Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_quick_donate' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('quick_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('quick_button_normal_tab', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quick_button_typography',
                'selector' => '{{WRAPPER}} .quick-button',
            ]
        );
        
        $this->add_control(
            'quick_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'quick_button_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quick_button_border',
                'selector' => '{{WRAPPER}} .quick-button',
            ]
        );
        
        $this->add_control(
            'quick_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .quick-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quick_button_box_shadow',
                'selector' => '{{WRAPPER}} .quick-button',
            ]
        );
        
        $this->add_responsive_control(
            'quick_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .quick-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'quick_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .quick-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('quick_button_hover_tab', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'quick_button_hover_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'quick_button_hover_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quick_button_hover_border',
                'selector' => '{{WRAPPER}} .quick-button:hover',
            ]
        );
        
        $this->add_control(
            'quick_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .quick-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quick_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .quick-button:hover',
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        $this->end_controls_tabs(); // End Quick Button Style Tabs
        $this->end_controls_section(); // End Quick Button Section

        // 6) Donation Progress Style
        $this->start_controls_section(
            'donation_progress_section',
            [
                'label' => __( 'Donation Progress', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_progress_bar' => 'yes',
                ],
            ]
        );
        
        // Progress Bar Container
        $this->add_control(
            'donation_progress_background',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donation_progress_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress' => 'height: {{SIZE}}px;',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donation_progress_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donation_progress_box_shadow',
                'selector' => '{{WRAPPER}} .donation-progress',
            ]
        );
        
        // Progress Bar Inside
        $this->add_control(
            'progress_bar_background',
            [
                'label' => __( 'Progress Bar Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress .progress-bar' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        
        // Submit Button
        $this->start_controls_section(
            'submit_button_style_section',
            [
                'label' => __( 'Submit Button', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->start_controls_tabs('submit_button_style_tabs');
        
        /*-----------------------
        | Normal State
        ------------------------*/
        $this->start_controls_tab('submit_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'submit_button_typography',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_control(
            'submit_button_text_color',
            [
                'label'     => __( 'Text Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'submit_button_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'submit_button_border',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_control(
            'submit_button_border_radius',
            [
                'label'      => __( 'Border Radius', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'submit_button_box_shadow',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_responsive_control(
            'submit_button_padding',
            [
                'label'      => __( 'Padding', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'submit_button_margin',
            [
                'label'      => __( 'Margin', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'submit_button_width',
            [
                'label'      => __( 'Width', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 500 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                    'em' => [ 'min' => 0, 'max' => 50 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        // Alignment
        $this->add_responsive_control(
            'submit_button_align',
            [
                'label'   => __( 'Alignment', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Left', 'skydonate' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'skydonate' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end' => [
                        'title' => __( 'Right', 'skydonate' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'justify-content: {{VALUE}};',
                ],
            ]
        );
        
        // Left Icon
        $this->add_control(
            'submit_button_left_icon_heading',
            [
                'label'     => esc_html__( 'Left Icon', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'submit_button_left_icon_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button .icon.left',
            ]
        );
        
        // Right Icon
        $this->add_control(
            'submit_button_right_icon_heading',
            [
                'label'     => esc_html__( 'Right Icon', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'submit_button_right_icon_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button .icon.right',
            ]
        );
        
        $this->end_controls_tab();
        
        /*-----------------------
        | Hover State
        ------------------------*/
        $this->start_controls_tab('submit_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'submit_button_hover_text_color',
            [
                'label'     => __( 'Text Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'submit_button_hover_background',
                'types'    => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'submit_button_hover_border',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
            ]
        );
        
        $this->add_control(
            'submit_button_hover_border_radius',
            [
                'label'      => __( 'Border Radius', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .single_add_to_cart_button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'submit_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
            ]
        );
        
        $this->add_control(
            'submit_button_hover_transition',
            [
                'label'     => __( 'Transition Duration', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::SLIDER,
                'range'     => [
                    'px' => [ 'max' => 3, 'step' => 0.1 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'transition-duration: {{SIZE}}s;',
                ],
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();

    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $enable_filters = $settings['enable_filters'] === 'yes';
        $filter_product_title = $settings['product_list'];
        $exclude_products = $settings['exclude_products'];

        // Convert Repeater items to an array of product IDs
        if (is_array($filter_product_title)) {
            $filter_product_title = array_column($filter_product_title, 'filter_product_title');
            $filter_product_title = array_unique($filter_product_title);
        } else {
            $filter_product_title = [];
        }

        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $post_limit = ($settings['post_limit']);
        $no_donations_message = esc_html($settings['no_donations_message']);
        $grid_layout = $settings['grid_layout'];

        // Toggles
        $show_image = $settings['show_image'] === 'yes';
        $show_title = $settings['show_title'] === 'yes';
        $show_short_description = $settings['show_short_description'] === 'yes';
        $word_limit = !empty($settings['short_description_word_limit']) ? $settings['short_description_word_limit'] : 15;
        $show_learn_more = $settings['show_learn_more'] === 'yes';
        $show_quick_donate = $settings['show_quick_donate'] === 'yes';
        $show_progress_bar = $settings['show_progress_bar'] === 'yes';
        $show_donation_details = $settings['show_donation_details'] === 'yes';

        // Button text/icons
        $learn_more_text = !empty($settings['learn_more_text']) ? $settings['learn_more_text'] : __('Learn More', 'skydonate');
        $learn_more_icon = !empty($settings['learn_more_icon']) ? $settings['learn_more_icon'] : null;
        $quick_donate_text = !empty($settings['quick_donate_text']) ? $settings['quick_donate_text'] : __('Quick Donate', 'skydonate');
        $quick_donate_icon = !empty($settings['quick_donate_icon']) ? $settings['quick_donate_icon'] : null;

        $secure_donation_text = !empty($settings['secure_donation_text']) ? $settings['secure_donation_text'] : __('Secure Donation', 'skydonate');
        $secure_donation_icon = !empty($settings['secure_donation_icon']) ? $settings['secure_donation_icon'] : null;
        $show_secure_donation = !empty($settings['show_secure_donation']) && $settings['show_secure_donation'] === 'yes';

        $show_play_button = !empty($settings['show_play_button']) && $settings['show_play_button'] === 'yes';

        $replace_title = $settings['replace_title'] === 'yes';
        $selected_words = $settings['selected_words'];
        $new_words = $settings['new_words'];

        $enable_title_filter = $settings['enable_title_filter'] === 'yes';

        // Wrapper classes for grid layout
        $this->add_render_attribute('wrapper_attributes', 'class', ['donation-cards-wrapper', 'row', 'row-cols-1', 'g-3', 'g-lg-4']);
        switch ($grid_layout) {
            case '1':
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-1');
                break;
            case '2':
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-2');
                break;
            case '3':
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-2 row-cols-lg-3');
                break;
            case '4':
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-2 row-cols-lg-3 row-cols-xl-4');
                break;
            case '5':
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 row-cols-xxl-5');
                break;
            default:
                $this->add_render_attribute('wrapper_attributes', 'class', 'row-cols-sm-2 row-cols-lg-3');
                break;
        }
    
        // Build WP_Query args
        $args = [
            'post_type' => 'product',
            'orderby'   => $settings['order_by'],
            'order'     => $settings['order'],
            'meta_query'=> [],
            'posts_per_page' => $post_limit,
            'limit'          => $post_limit,
            'tax_query' => [
                'relation' => 'AND',
            ]
        ];

        // Handle raised_amount ordering
        if ($settings['order_by'] === 'raised_amount') {
            $args['meta_key'] = '_total_sales_amount';
            $args['orderby'] = 'meta_value_num';
        }

        // Filter by product title if enabled
        if ($enable_title_filter && !empty($filter_product_title) && is_array($filter_product_title) && count(array_filter($filter_product_title)) > 0) {
            $args['post__in'] = $filter_product_title;
        }

        // Exclude products if no title filter
        if (!$enable_title_filter && !empty($exclude_products) && is_array($exclude_products) && count(array_filter($exclude_products)) > 0) {
            $args['post__not_in'] = $exclude_products;
        }

        // Filter by category
        if (!empty($filter_category)) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $filter_category,
            ];
        }

        // Filter by tag
        if (!empty($filter_tag)) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_tag',
                'field'    => 'slug',
                'terms'    => $filter_tag,
            ];
        }

        // Query products
        $products = new WP_Query($args);
        if ($products->have_posts()) {
            echo '<div '.$this->get_render_attribute_string("wrapper_attributes").'>';
                while ($products->have_posts()) {
                    echo '<div class="col">';
                    $products->the_post();
                    $product = wc_get_product(get_the_ID());
                    $product_id = get_the_ID();

                    // Get custom donation fields
                    $target_sales = floatval(get_post_meta($product_id, '_target_sales_goal', true));
                    $offline_donation = floatval(get_post_meta($product_id, '_offline_donation', true));

                    // Use improved method below
                    $total_raised = $this->total_donation_sales($product_id) + $offline_donation;

                    // WooCommerce "total sales" is the number of items sold
                    $donations_number = $product->get_total_sales();

                    $progress_percentage = ($target_sales > 0) ? ($total_raised / $target_sales) * 100 : 0;
                    $video_url = get_post_meta($product_id, '_woodmart_product_video', true);

                    // Title tag from widget settings
                    $title_tag = $settings['title_tag'];

                    echo '<div class="donation-card" data-id="donation-'.$product_id.'">';
                        // Donation Image
                        if ($show_image && has_post_thumbnail()) {
                            echo '<div class="donation-image">';
                                echo '<a href="' . esc_url(get_permalink()) . '">';
                                    the_post_thumbnail('large');
                                echo '</a>';
                                if (!empty($video_url) && $show_play_button) {
                                    echo '<a href="'.esc_url($video_url).'" data-lity class="play-button">';
                                        echo '<i></i>';
                                        echo '<span>'.__('Watch video', 'skydonate').'</span>';
                                    echo '</a>';
                                }
                            echo '</div>';
                        }

                        echo '<div class="donation-content">';
                            // Donation Title
                            if ($show_title && get_the_title()) {
                                if ($replace_title && !empty($selected_words) && !empty($new_words)) {
                                    // Replace specific words
                                    $title = str_replace($selected_words, $new_words, get_the_title());
                                } elseif ($replace_title && !empty($new_words)) {
                                    $title = esc_html($new_words . ' ' . get_the_title());
                                } else {
                                    $title = get_the_title();
                                }
                                // Output heading
                                echo '<' . esc_attr($title_tag) . ' class="donation-title">';
                                    echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html($title) . '</a>';
                                echo '</' . esc_attr($title_tag) . '>';
                            }
                            
                            // Short Description
                            if ($show_short_description) {
                                if (has_excerpt()) {
                                    echo '<div class="donation-short-description">' . wp_trim_words(get_the_excerpt(), $word_limit, '.') . '</div>';
                                } else {
                                    echo '<div class="donation-short-description">' . wp_trim_words(get_the_content(), $word_limit, '.') . '</div>';
                                }
                            }
                            
                            // Action Buttons (Quick Donate, Learn More)
                            echo '<div class="row g-2 g-lg-3">';
                                if ($show_quick_donate) {
                                    echo '<div class="col">';
                                        echo '<button type="button" class="donation-button white-button quick-button">';
                                            echo esc_html($quick_donate_text);
                                            if (!empty($quick_donate_icon)) {
                                                echo '<span class="icon">';
                                                \Elementor\Icons_Manager::render_icon($quick_donate_icon, [ 'aria-hidden' => 'true' ]);
                                                echo '</span>';
                                            }
                                        echo '</button>';
                                    echo '</div>';
                                }

                                if ($show_learn_more) {
                                    echo '<div class="col">';
                                        echo '<a href="' . esc_url(get_permalink()) . '" class="donation-button donate-button">';
                                            echo esc_html($learn_more_text);
                                            if (!empty($learn_more_icon['value'])) {
                                                echo '<span class="icon">';
                                                \Elementor\Icons_Manager::render_icon($learn_more_icon, [ 'aria-hidden' => 'true' ]);
                                                echo '</span>';
                                            }
                                        echo '</a>';
                                    echo '</div>';
                                }
                            echo '</div>';

                            // Progress Bar
                            if ($show_progress_bar) {
                                echo '<div class="donation-progress">';
                                    echo '<div class="progress-bar" data-percent="'.esc_attr($progress_percentage).'"></div>';
                                echo '</div>';
                            }

                            // Donation Details (Raised, Donations, Target)
                            if ($show_donation_details) {
                                echo '<div class="donation-details">';
                                    echo '<span class="raised-amount">';
                                    echo sprintf(
                                        __('Raised <span class="amount" data-number="%s" >%s<span class="number">0</span></span>', 'skydonate'),
                                        $total_raised,
                                        Skydonate_Functions::Get_Currency_Symbol()
                                    );
                                    echo '</span>';

                                    echo '<span class="donations-number">';
                                    echo sprintf(
                                        __('Donations <span class="amount">%s</span>', 'skydonate'),
                                        $donations_number
                                    );
                                    echo '</span>';

                                    echo '<span class="target-amount">';
                                    echo sprintf(
                                        __('Target <span class="amount" data-number="%s">%s<span class="number">0</span></span>', 'skydonate'),
                                        $target_sales,
                                        Skydonate_Functions::Get_Currency_Symbol()
                                    );
                                    echo '</span>';
                                echo '</div>';
                            }
                        echo '</div>'; // .donation-content

                        // Quick Donate Modal
                        if ($show_quick_donate) {
                            echo '<div class="quick-modal">';
                            echo '<div class="quick-modal-overlay"></div>';
                            echo '<div class="quick-modal-body">';
                                    echo '<span class="quick-modal-close"></span>'; // Close button
                                    echo '<div class="quick-modal-content">';
                                        if ($show_secure_donation) {
                                            echo '<div class="secure-donation">';
                                                if (!empty($secure_donation_icon)) {
                                                    echo '<span class="secure-donation-icon">';
                                                    \Elementor\Icons_Manager::render_icon($secure_donation_icon, [ 'aria-hidden' => 'true' ]);
                                                    echo '</span>';
                                                }
                                                echo esc_html($secure_donation_text);
                                            echo '</div>';
                                        }
                                        
                // Before icon
                $before_icon = '';
                if (isset($settings['icon_before_media_type'])) {
                    if ($settings['icon_before_media_type'] === 'icon' && !empty($settings['icon_before_icon']['value'])) {
                        $before_icon = Skydonate_Icon_Manager::render_icon($settings['icon_before_icon'], ['aria-hidden' => 'true']);
                    } elseif ($settings['icon_before_media_type'] === 'image' && !empty($settings['icon_before_image']['url'])) {
                        $before_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html($settings, 'icon_before_image_size', 'icon_before_image');
                    }
                }

                // After icon
                $after_icon = '';
                if (isset($settings['icon_after_media_type'])) {
                    if ($settings['icon_after_media_type'] === 'icon' && !empty($settings['icon_after_icon']['value'])) {
                        $after_icon = Skydonate_Icon_Manager::render_icon($settings['icon_after_icon'], ['aria-hidden' => 'true']);
                    } elseif ($settings['icon_after_media_type'] === 'image' && !empty($settings['icon_after_image']['url'])) {
                        $after_icon = \Elementor\Group_Control_Image_Size::get_attachment_image_html($settings, 'icon_after_image_size', 'icon_after_image');
                    }
                }

                // Build shortcode attributes dynamically
                $atts = [
                    'id'             => $product_id,
                    'button_text'    => !empty($settings['donation_button_text']) ? esc_attr($settings['donation_button_text']) : __('Donate and Support', 'skydonate'),
                    'before_icon'    => !empty($before_icon) ? esc_attr($before_icon) : '',
                    'after_icon'     => !empty($after_icon) ? esc_attr($after_icon) : '',
                ];

                // Build shortcode string
                $shortcode = '[skydonate_form';
                foreach ($atts as $key => $value) {
                    if ($value !== '') {
                        $shortcode .= ' ' . $key . '="' . $value . '"';
                    }
                }
                $shortcode .= ']';
                echo do_shortcode($shortcode);
                                    echo '</div>'; // .quick-modal-content
                                echo '</div>'; // .quick-modal-body
                            echo '</div>'; // .quick-modal
                        }

                    echo '</div>'; // .donation-card
                    echo '</div>'; // .col
                }
            echo '</div>'; // .donation-cards-wrapper
            wp_reset_postdata();
        } else {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
        }
    }
    
    /**
     * Direct SQL approach to sum only the line totals for $product_id
     * in wc-completed orders (instead of loading each WC_Order).
     * 
     * If you also want to include wc-processing orders,
     * change p.post_status in the query to:
     * p.post_status IN ('wc-completed','wc-processing')
     */
    public function total_donation_sales($product_id) {
        global $wpdb;

        $sql = "
            SELECT SUM( om2.meta_value )
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1
                ON oi.order_item_id = om1.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2
                ON oi.order_item_id = om2.order_item_id
            INNER JOIN {$wpdb->posts} AS p
                ON oi.order_id = p.ID
            WHERE p.post_status = 'wc-completed'
              AND oi.order_item_type = 'line_item'
              AND om1.meta_key = '_product_id'
              AND om1.meta_value = %d
              AND om2.meta_key = '_line_total'
        ";

        $sum_result = $wpdb->get_var($wpdb->prepare($sql, $product_id));
        return $sum_result ? floatval($sum_result) : 0;
    }

    protected function Donation_Form_Button_Control(){
        // ===========================
        // Section: Donation Button
        // ===========================
        $this->start_controls_section(
            'donation_form_button',
            [
                'label' => __('Submit Button', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // ===== Button Text =====
        $this->add_control(
            'donation_button_text',
            [
                'label' => __('Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate and Support', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
            ]
        );


        // ===========================
        // Tabs: Button Icons
        // ===========================
        $this->start_controls_tabs('donation_button_icon_tabs');

        // ===== Icon Before Tab =====
        $this->start_controls_tab(
            'donation_button_icon_before_tab',
            [
                'label' => __('Icon Before', 'skydonate'),
            ]
        );

        // ---- Media Type Selector ----
        $this->add_control(
            'icon_before_media_type',
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
                'default' => 'icon',
            ]
        );

        // ---- Image Control ----
        $this->add_control(
            'icon_before_image',
            [
                'label' => __('Image', 'skydonate'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
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
                'name' => 'icon_before_image_size',
                'default' => 'large',
                'separator' => 'none',
                'exclude' => [
                    'full',
                    'custom',
                    'large',
                    'shop_catalog',
                    'shop_single',
                    'shop_thumbnail',
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
            ]
        );

        // ---- Icon Control ----
        $this->add_control(
            'icon_before_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'condition' => [
                    'icon_before_media_type' => 'icon',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_size',
            [
                'label' => __( 'Icon Size', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px'  => [ 'min' => 0, 'max' => 200 ],
                    'em'  => [ 'min' => 0, 'max' => 10 ],
                    'rem' => [ 'min' => 0, 'max' => 10 ],
                ],
                'condition' => [
                    'icon_before_media_type' => 'icon',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'font-size: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_before_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.left' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();


        // ===== Icon After Tab =====
        $this->start_controls_tab(
            'donation_button_icon_after_tab',
            [
                'label' => __('Icon After', 'skydonate'),
            ]
        );

        // ---- Media Type Selector ----
        $this->add_control(
            'icon_after_media_type',
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
                'default' => 'icon',
            ]
        );

        // ---- Image Control ----
        $this->add_control(
            'icon_after_image',
            [
                'label' => __('Image', 'skydonate'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
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
                'name' => 'icon_after_image_size',
                'default' => 'large',
                'separator' => 'none',
                'exclude' => [
                    'full',
                    'custom',
                    'large',
                    'shop_catalog',
                    'shop_single',
                    'shop_thumbnail',
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
            ]
        );

        // ---- Icon Control ----
        $this->add_control(
            'icon_after_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
                'condition' => [
                    'icon_after_media_type' => 'icon',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 100 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'image',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_size',
            [
                'label' => __( 'Icon Size', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range' => [
                    'px'  => [ 'min' => 0, 'max' => 200 ],
                    'em'  => [ 'min' => 0, 'max' => 10 ],
                    'rem' => [ 'min' => 0, 'max' => 10 ],
                ],
                'condition' => [
                    'icon_after_media_type' => 'icon',
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'font-size: {{SIZE}}{{UNIT}};width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right svg' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_after_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form-wrapper .donation-form .form-submit-button .icon.right' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();


    }
}
