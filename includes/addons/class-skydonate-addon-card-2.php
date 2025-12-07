<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Card_2 extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_card_addon_2';
    }

    public function get_title() {
        return __('Donation Cards 2', 'skydonate');
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

        $repeater->add_control(
            'card_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'solid',
                ],
            ]
        );

        // Add the filter_product_title select control inside the repeater
        $repeater->add_control(
            'filter_product_title',
            [
                'label' => __('Donation Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Ensure it returns [ID => Title]
                'default' => '',
                'multiple' => false,
                'label_block' => true,
            ]
        );

        // Add the repeater control to the widget
        $this->add_control(
            'product_list',
            [
                'label' => __( 'Donation List', 'your-plugin' ),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ filter_product_title }}}', // Display selected product title as the title of the repeater item
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
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Ensure correct format
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
        ]);
        
        $this->end_controls_section();



        $this->start_controls_section(
            'settings_option_content_section',
            [
                'label' => __('Settings', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

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
        

        // Donation details (Raised, Donations, Target) toggle
        $this->add_control(
            'show_card_icon',
            [
                'label' => __('Show Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Secure Donation icon
        $this->add_control(
            'card_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'solid',
                ],
                'condition' => [
                    'show_card_icon' => 'yes',
                ],
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



        $this->start_controls_section(
            'title_replace_section',
            [
                'label' => __('Title Replace Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'show_title' => 'yes',
                ],
            ]
        );

        
        $this->add_control(
            'replace_title',
            [
                'label' => __('Replace Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'no',
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
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'learn_button_content_section',
            [
                'label' => __('Learn Button', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Learn button toggle
        $this->add_control(
            'enable_learn_button',
            [
                'label' => __('Show Learn Button', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Learn button text
        $this->add_control(
            'learn_button_text',
            [
                'label' => __('Learn Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Learn More', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
                'condition' => [
                    'enable_learn_button' => 'yes',
                ],
            ]
        );
        
        // Learn button icon
        $this->add_control(
            'learn_button_icon',
            [
                'label' => __('Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
                'condition' => [
                    'enable_learn_button' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            'quick_button_section',
            [
                'label' => __('Quick Button', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Quick Button toggle
        $this->add_control(
            'enable_quick_button',
            [
                'label' => __('Quick Button', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Quick Button text
        $this->add_control(
            'quick_button_text',
            [
                'label' => __('Quick Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Quick Action', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
                'condition' => [
                    'enable_quick_button' => 'yes',
                ],
            ]
        );
        
        // Quick Button icon
        $this->add_control(
            'quick_button_icon',
            [
                'label' => __('Quick Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-bolt',
                    'library' => 'solid',
                ],
                'condition' => [
                    'enable_quick_button' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        $this->start_controls_section(
            'modal_content_section',
            [
                'label' => __('Modal Content', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
        $this->end_controls_section();
        
        $this->Donation_Form_Button_Control();

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

        // Donation Icon style
        $this->start_controls_section(
            'donation_card_icon_section',
            [
                'label' => __( 'Donation Icon', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_card_icon' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('donation_card_icon_tab');
        
        // Normal Style Tab
        $this->start_controls_tab('donation_card_icon_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_icon_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .layout2 .donation-card .card-icon svg *' => 'fill: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'donation_icon_bg_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        // Add Width Control
        $this->add_responsive_control(
            'donation_card_icon_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        // Add Height Control
        $this->add_responsive_control(
            'donation_card_icon_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Add Icon Size Control
        $this->add_responsive_control(
            'donation_card_icon_size',
            [
                'label' => __( 'Icon Size', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 30,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon img' => 'width: {{SIZE}}{{UNIT}};max-width: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .layout2 .donation-card .card-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};max-width: {{SIZE}}{{UNIT}}; max-height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'donation_icon_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'donation_icon_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'donation_card_icon_transition',
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
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donation_card_icon_border',
                'label' => __( 'Border', 'skydonate' ),
                'selector' => '{{WRAPPER}} .layout2 .donation-card .card-icon',
            ]
        );
        
        $this->add_responsive_control(
            'donation_card_icon_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donation_card_icon_box_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .layout2 .donation-card .card-icon',
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donation_icon_hover_tab', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_icon_hover_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon:hover span' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'donation_box_icon_hover_bg_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card .card-icon:hover span' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        // Box Hover Style Tab
        $this->start_controls_tab('donation_box_icon_hover_tab', [
            'label' => __( 'Box Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donation_box_hover_icon_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card:hover .card-icon span' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'donation_box_hover_icon_bg_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .layout2 .donation-card:hover .card-icon span' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Box Hover Style Tab
        
        $this->end_controls_tabs(); // End Donation Title Style Tabs
        $this->end_controls_section(); // End Donation Icon Section
        
        // Donation Progress
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

        // Donation Title style
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
        
        $this->end_controls_tabs();
        $this->end_controls_section(); // End Donation Title Section
        
        // Donation Short Description
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
        
        $this->end_controls_tabs();
        $this->end_controls_section(); // End Donation Short Description Section

        // Learn More style
        $this->start_controls_section(
            'more_button_style_section',
            [
                'label' => __( 'Learn More', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_learn_button' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('more_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('more_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'more_button_typography',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_control(
            'more_button_text_color',
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
                'name' => 'more_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'more_button_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_control(
            'more_button_border_radius',
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
                'name' => 'more_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button',
            ]
        );
        
        $this->add_responsive_control(
            'more_button_padding',
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
            'more_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .donate-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
			'more_button_icon_heading',
			[
				'label' => esc_html__( 'Icon Style', 'skydonate' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        
        $this->add_control(
            'more_button_icon_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donate-button .icon' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'more_button_icon_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donate-button .icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control("donate_icon_size", [
            "label" => __("Size", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .donate-button .icon" =>
                    "font-size: {{SIZE}}px",
            ],
        ]);
        $this->add_control("donate_icon_width", [
            "label" => __("Width", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .donate-button .icon" =>
                    "width: {{SIZE}}px",
            ],
        ]);
        $this->add_control("donate_icon_height", [
            "label" => __("Height", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .donate-button .icon" =>
                    "height: {{SIZE}}px",
            ],
        ]);
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('more_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'more_button_hover_text_color',
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
                'name' => 'more_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'more_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_control(
            'more_button_hover_border_radius',
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
                'name' => 'more_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .donate-button:hover',
            ]
        );
        
        $this->add_control(
            'more_button_hover_transition',
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
        
        $this->end_controls_tabs(); // End Learn More Style Tabs
        $this->end_controls_section(); // End Learn More Style Section


        // Quick Button style
        $this->start_controls_section(
            'quick_button_style_section',
            [
                'label' => __( 'Quick Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'enable_quick_button' => 'yes',
                ],
            ]
        );
        
        $this->start_controls_tabs('quick_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('quick_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quick_button_typography',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button',
            ]
        );
        
        $this->add_control(
            'quick_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'quick_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quick_button_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button',
            ]
        );
        
        $this->add_control(
            'quick_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quick_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button',
            ]
        );
        
        $this->add_responsive_control(
            'quick_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'quick_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
			'quick_button_icon_heading',
			[
				'label' => esc_html__( 'Icon Style', 'skydonate' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        
        $this->add_control(
            'quick_button_icon_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button .icon' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'quick_button_icon_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quick-button .icon' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control("quick_donate_icon_size", [
            "label" => __("Size", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .quick-button .icon" =>
                    "font-size: {{SIZE}}px",
            ],
        ]);
        $this->add_control("quick_donate_icon_width", [
            "label" => __("Width", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .quick-button .icon" =>
                    "width: {{SIZE}}px",
            ],
        ]);
        $this->add_control("quick_donate_icon_height", [
            "label" => __("Height", "skydonate"),
            "type" => \Elementor\Controls_Manager::SLIDER,
            "range" => ["px" => ["max" => 50, "step" => 1]],
            "selectors" => [
                "{{WRAPPER}} .quick-button .icon" =>
                    "height: {{SIZE}}px",
            ],
        ]);
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('quick_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'quick_button_hover_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'quick_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quick_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button:hover',
            ]
        );
        
        $this->add_control(
            'quick_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-card .donation-content .quick-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quick_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-card .donation-content .quick-button:hover',
            ]
        );
        
        $this->add_control(
            'quick_button_hover_transition',
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
                    '{{WRAPPER}} .donation-card .donation-content .quick-button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        
        $this->end_controls_tabs(); // End Quick Button Style Tabs
        $this->end_controls_section(); // End Quick Button Style Section

    }
    
    protected function render() {
        $settings               = $this->get_settings_for_display();
        $enable_filters         = ($settings['enable_filters'] === 'yes');
        $enable_title_filter    = ($settings['enable_title_filter'] === 'yes');
        $filter_items           = $settings['product_list'];
        $filter_category        = $settings['filter_category'];
        $filter_tag             = $settings['filter_tag'];
        $post_limit             = $settings['post_limit'];
        $no_donations_message   = esc_html($settings['no_donations_message']);
        $grid_layout            = $settings['grid_layout'];

        $show_image             = ($settings['show_image'] === 'yes');
        $show_card_icon         = ($settings['show_card_icon'] === 'yes');
        $show_title             = ($settings['show_title'] === 'yes');
        $show_short_desc        = ($settings['show_short_description'] === 'yes');
        $word_limit             = !empty($settings['short_description_word_limit']) ? $settings['short_description_word_limit'] : 15;
        $enable_learn_button    = ($settings['enable_learn_button'] === 'yes');
        $enable_quick_button    = ($settings['enable_quick_button'] === 'yes');
        $show_progress_bar      = ($settings['show_progress_bar'] === 'yes');
        $show_donation_details  = ($settings['show_donation_details'] === 'yes');
        $show_play_button       = (!empty($settings['show_play_button']) && $settings['show_play_button'] === 'yes');

        $learn_button_text      = !empty($settings['learn_button_text']) ? $settings['learn_button_text'] : __('Learn More', 'skydonate');
        $learn_button_icon      = !empty($settings['learn_button_icon']) ? $settings['learn_button_icon'] : null;
        $quick_button_text      = !empty($settings['quick_button_text']) ? $settings['quick_button_text'] : __('Quick Donate', 'skydonate');
        $quick_button_icon      = !empty($settings['quick_button_icon']) ? $settings['quick_button_icon'] : null;

        $show_secure_donation   = (!empty($settings['show_secure_donation']) && $settings['show_secure_donation'] === 'yes');
        $secure_donation_text   = !empty($settings['secure_donation_text']) ? $settings['secure_donation_text'] : __('Secure Donation', 'skydonate');
        $secure_donation_icon   = !empty($settings['secure_donation_icon']) ? $settings['secure_donation_icon'] : null;

        $replace_title          = ($settings['replace_title'] === 'yes');
        $selected_words         = $settings['selected_words'];
        $new_words              = $settings['new_words'];

        $exclude_products       = $settings['exclude_products'];
        $card_icon              = !empty($settings['card_icon']) ? $settings['card_icon'] : null;

        // Convert Repeater items to array of product IDs
        if (is_array($filter_items)) {
            $filter_items = array_column($filter_items, 'filter_product_title');
            $filter_items = array_unique($filter_items);
        } else {
            $filter_items = [];
        }

        // Build grid classes
        $this->add_render_attribute('wrapper_attributes', 'class', ['donation-cards-wrapper', 'layout2', 'row', 'row-cols-1', 'g-3', 'g-lg-4']);
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
            'post_type'      => 'product',
            'orderby'        => $settings['order_by'],
            'order'          => $settings['order'],
            'meta_query'     => [],
            'posts_per_page' => $post_limit,
            'limit'          => $post_limit,
            'tax_query'      => ['relation' => 'AND']
        ];
    
        // If user selected product IDs via the Repeater
        if ($enable_title_filter && !empty($filter_items) && is_array($filter_items) && count(array_filter($filter_items)) > 0) {
            $args['post__in'] = $filter_items;
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

        $products = new \WP_Query($args);
        $count = 0; // Track index for repeater icons

        if ($products->have_posts()) {
            echo '<div '.$this->get_render_attribute_string("wrapper_attributes").'>';
                while ($products->have_posts()) {
                    $products->the_post();
                    echo '<div class="col">';
                        $product    = wc_get_product(get_the_ID());
                        $product_id = get_the_ID();

                        // Custom donation fields
                        $target_sales    = floatval(get_post_meta($product_id, '_target_sales_goal', true));
                        $offline_donation = floatval(get_post_meta($product_id, '_offline_donation', true));
                        
                        // Use improved method for total_donation_sales
                        $total_raised = $this->total_donation_sales($product_id) + $offline_donation;
                        
                        $progress_percentage = ($target_sales > 0) ? ($total_raised / $target_sales) * 100 : 0;
                        $video_url           = get_post_meta($product_id, '_woodmart_product_video', true);
                        
                        $title_tag = $settings['title_tag'];

                        echo '<div class="donation-card" data-id="donation-'.$product_id.'">';
                            // If show_image
                            if ($show_image && has_post_thumbnail()) {
                                echo '<div class="donation-image">';
                                    echo '<a href="' . esc_url(get_permalink()) . '">';
                                        the_post_thumbnail('large');
                                    echo '</a>';
                                    if(!empty($video_url) && $show_play_button){
                                        echo '<a href="'.esc_url($video_url).'" data-lity class="play-button">';
                                            echo '<i></i>';
                                            echo '<span>'.__('Watch video', 'skydonate').'</span>';
                                        echo '</a>';
                                    }
                                echo '</div>';
                            }

                            echo '<div class="donation-content">';
                                // Show icon
                                if($show_card_icon){
                                    echo '<div class="icon-wrapper">';
                                    if ($enable_filters && isset($settings['product_list'][$count]['card_icon'])) {
                                        $custom_icon = $settings['product_list'][$count]['card_icon'];
                                        echo '<div class="card-icon"><span>';
                                        // If it's an SVG
                                        if (!empty($custom_icon['library']) && $custom_icon['library'] === 'svg') {
                                            if (!empty($custom_icon['value']['url'])) {
                                                $svg_path = get_attached_file($custom_icon['value']['id']);
                                                if (file_exists($svg_path) && mime_content_type($svg_path) === 'image/svg+xml') {
                                                    echo file_get_contents($svg_path);
                                                } else {
                                                    // Fallback if not found
                                                    echo '<img src="' . esc_url($custom_icon['value']['url']) . '" alt="Card Icon">';
                                                }
                                            }
                                        } elseif (!empty($custom_icon['value'])) {
                                            // Font icon
                                            echo '<i class="' . esc_attr($custom_icon['value']) . '"></i>';
                                        }
                                        echo '</span></div>';
                                    } elseif (!empty($card_icon)) {
                                        // Fallback to global card_icon
                                        echo '<div class="card-icon"><span>';
                                        \Elementor\Icons_Manager::render_icon($card_icon, ['aria-hidden' => 'true']);
                                        echo '</span></div>';
                                    }
                                    echo '</div>';
                                }

                                // Donation details
                                if($show_donation_details){
                                    echo '<div class="donation-details">';
                                        echo '<span class="raised-amount">'
                                             . sprintf(
                                                 __('Raised <span class="amount" data-number="%s" >%s<span class="number">0</span></span>', 'skydonate'),
                                                 $total_raised,
                                                 Skydonate_Functions::Get_Currency_Symbol()
                                             )
                                             . '</span>';
                                        echo '<span class="target-amount">'
                                             . sprintf(
                                                 __('Target <span class="amount" data-number="%s">%s<span class="number">0</span></span>', 'skydonate'),
                                                 $target_sales,
                                                 Skydonate_Functions::Get_Currency_Symbol()
                                             )
                                             . '</span>';
                                    echo '</div>';
                                }

                                // Progress bar
                                if($show_progress_bar){
                                    echo '<div class="donation-progress">';
                                        echo '<div class="progress-bar" data-percent="'.esc_attr($progress_percentage).'"></div>';
                                    echo '</div>';
                                    echo '<div class="percentage"><span class="count">'.esc_html($progress_percentage).'</span>%</div>';
                                }

                                // Title
                                if($show_title && get_the_title()){
                                    if ($replace_title && !empty($selected_words) && !empty($new_words)) {
                                        $title = str_replace($selected_words, $new_words, get_the_title());
                                    } elseif ($replace_title && !empty($new_words)) {
                                        $title = esc_html($new_words . ' ' . get_the_title());
                                    } else {
                                        $title = get_the_title();
                                    }
                                    echo '<' . esc_attr($title_tag) . ' class="donation-title"><a href="' . esc_url(get_permalink()) . '">'
                                         . esc_html($title) . '</a></' . esc_attr($title_tag) . '>';
                                }

                                // Short Description
                                if($show_short_desc){
                                    if(has_excerpt()){
                                        echo '<div class="donation-short-description">'
                                             . wp_trim_words(get_the_excerpt(), $word_limit, '.') . '</div>';
                                    } else {
                                        echo '<div class="donation-short-description">'
                                             . wp_trim_words(get_the_content(), $word_limit, '.') . '</div>';
                                    }
                                }

                                // Action buttons
                                echo '<div class="row g-2 g-lg-3">';
                                    if($enable_learn_button){
                                        echo '<div class="col">';
                                            echo '<a href="' . esc_url(get_permalink()) . '" class="donation-button donate-button">';
                                                echo esc_html($learn_button_text);
                                                if(!empty($learn_button_icon)){
                                                    echo '<span class="icon">';
                                                    \Elementor\Icons_Manager::render_icon($learn_button_icon, [ 'aria-hidden' => 'true' ]);
                                                    echo '</span>';
                                                }
                                            echo '</a>';
                                        echo '</div>';
                                    }
                                    if($enable_quick_button){
                                        echo '<div class="col">';
                                            echo '<button type="button" class="donation-button white-button quick-button">';
                                                echo esc_html($quick_button_text);
                                                if(!empty($quick_button_icon)){
                                                    echo '<span class="icon">';
                                                    \Elementor\Icons_Manager::render_icon($quick_button_icon, [ 'aria-hidden' => 'true' ]);
                                                    echo '</span>';
                                                }
                                            echo '</button>';
                                        echo '</div>';
                                    }
                                echo '</div>'; // row
                            echo '</div>'; // .donation-content

                            if($enable_quick_button){
                                echo '<div class="quick-modal">';
                                echo '<div class="quick-modal-overlay"></div>';
                                    echo '<div class="quick-modal-body">';
                                        echo '<span class="quick-modal-close"></span>'; 
                                        echo '<div class="quick-modal-content">';
                                            if($show_secure_donation){
                                                echo '<div class="secure-donation">';
                                                    if(!empty($secure_donation_icon)){
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
                                        echo '</div>';
                                    echo '</div>';
                                echo '</div>';
                            }
                        echo '</div>'; // .donation-card
                    echo '</div>'; // .col

                    $count++;
                }
            echo '</div>'; // .donation-cards-wrapper
            wp_reset_postdata();
        } else {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
        }
    }
    
    /**
     * SQL-based approach to summing donation totals for a product,
     * instead of loading thousands of WC_Order objects.
     */
    public function total_donation_sales($product_id) {
        global $wpdb;

        $sql = "
            SELECT SUM( om2.meta_value )
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1
                ON (oi.order_item_id = om1.order_item_id)
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2
                ON (oi.order_item_id = om2.order_item_id)
            INNER JOIN {$wpdb->posts} AS p
                ON (oi.order_id = p.ID)
            WHERE p.post_status = 'wc-completed'
              AND oi.order_item_type = 'line_item'
              AND om1.meta_key = '_product_id'
              AND om1.meta_value = %d
              AND om2.meta_key = '_line_total'
        ";
        // Include other statuses if desired:
        // p.post_status IN ('wc-completed','wc-processing')

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
