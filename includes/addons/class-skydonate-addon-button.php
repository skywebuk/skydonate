<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Button extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_button';
    }

    public function get_title() {
        return __('Donation Button', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-button';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['donation-card', 'donation-button'];
    }

    public function get_script_depends() {
        return ['donation-button'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'button_options_section',
            [
                'label' => __('Button Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

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
            'target_donation',
            [
                'label' => __('Target Donation', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Ensure this returns product IDs
                'default' => [],
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'donate_button_text',
            [
                'label' => __('Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate and Support', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
            ]
        );
        
        $this->add_control(
            'donate_button_icon',
            [
                'label' => __('Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-hand-holding-heart',
                    'library' => 'solid',
                ],
            ]
        );
        
        $this->add_control(
            'show_secure_donation',
            [
                'label' => __('Show Secure Badge', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
                'separator' => 'before'
            ]
        );
        
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
        
        $this->add_control(
            'secure_donation_icon',
            [
                'label' => __('Secure Donation Icon', 'skydonate'),
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

        $this->Donation_Form_Button_Control();
                
        // Style Section for Button
        $this->start_controls_section(
            'button_style',
            [
                'label' => __('Button Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Enable/Disable Filter
        $this->add_control(
            'full_width',
            [
                'label' => __('Full Width', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        // Button Alignment
        $this->add_control(
            'wrapper_alignment',
            [
                'label' => __('Alignment', 'skydonate'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'skydonate'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'skydonate'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'skydonate'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
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
            'condition' => [
                'button_style_mode' => 'border',
            ],
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
                    '{{WRAPPER}} .button-custom .primary-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "button_hover_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .button-custom .primary-button:hover",
            'condition' => [
                'button_style_mode' => 'border',
            ],
        ]);

        // Button Background Color (Hover)
        $this->add_control(
            'button_background_hover',
            [
                'label' => __('Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .button-custom .primary-button:hover' => 'background-color: {{VALUE}};',
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

        $this->add_control(
            'icon_position',
            [
                'label' => __('Icon Position', 'skydonate'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'skydonate'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'right' => [
                        'title' => __('Right', 'skydonate'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'separator' => 'before'
            ]
        );
        $this->end_controls_section(); // End Button Style Section

        $this->start_controls_section(
            'donate_tabs_button_style_section',
            [
                'label' => __( 'Tabs Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'donate_tabs_buttons_background',
            [
                'label' => __( 'Area Background', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .donation-tabs' => 'background-color: {{VALUE}};',
                    '.donation-form-layout2 {{WRAPPER}} .donation-tabs .button.active' => 'color: {{VALUE}};'
                ],
            ]
        );
        
        $this->start_controls_tabs('donate_tabs_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('donate_tabs_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donate_tabs_button_typography',
                'selector' => '{{WRAPPER}} .donation-tabs .button',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-tabs .button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_border',
                'selector' => '{{WRAPPER}} .donation-tabs .button',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-tabs .button',
            ]
        );
        
        $this->add_responsive_control(
            'donate_tabs_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donate_tabs_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donate_tabs_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donate_tabs_button_hover_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-tabs .button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-tabs .button:hover',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-tabs .button:hover',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_hover_transition',
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
                    '{{WRAPPER}} .donation-tabs .button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        $this->end_controls_tab(); // End Hover Style Tab
        
        $this->start_controls_tab('donate_tabs_button_active', [
            'label' => __( 'Active', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donate_tabs_button_active_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_active_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-tabs .button.active',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_active_border',
                'selector' => '{{WRAPPER}} .donation-tabs .button.active',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_active_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-tabs .button.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_active_box_shadow',
                'selector' => '{{WRAPPER}} .donation-tabs .button.active',
            ]
        );
        
        $this->end_controls_tab(); // End Active Style Tab
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section(); // End Donate Button Style Section


        $this->start_controls_section(
            'donation_form_title_section',
            [
                'label' => __( 'Donation Title', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->start_controls_tabs('donation_form_title_style_tab');
        
        // Normal Style Tab
        $this->start_controls_tab('donation_form_title_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donation_form_title_typography',
                'selector' => '{{WRAPPER}} .box-title',
            ]
        );
        
        $this->add_control(
            'donation_title_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .box-title' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .box-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .box-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'donation_form_title_transition',
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
                    '{{WRAPPER}} .box-title' => 'transition-duration: {{SIZE}}s',
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
                    '{{WRAPPER}} .box-title:hover' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .box-title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Box Hover Style Tab
        $this->end_controls_tabs(); // End Donation Title Style Tabs
        $this->end_controls_section(); // End Donation Title Section
        
        
        $this->start_controls_section(
            'donate_selection_button_style_section',
            [
                'label' => __( 'Selection Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->start_controls_tabs('donate_selection_button_style_tabs');
        
        // Normal Style Tab
        $this->start_controls_tab('donate_selection_button_normal', [
            'label' => __( 'Normal', 'skydonate' ),
        ]);
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donate_selection_button_typography',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button .amount' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_border',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_box_shadow',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button',
            ]
        );
        
        $this->add_responsive_control(
            'donate_selection_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donate_selection_button_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'donate_selection_button_height',
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
                    '{{WRAPPER}} .custom-options .custom-option-button' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        
        $this->end_controls_tab(); // End Normal Style Tab
        
        // Hover Style Tab
        $this->start_controls_tab('donate_selection_button_hover', [
            'label' => __( 'Hover', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donate_selection_button_hover_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button:hover .amount' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_hover_border',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button:hover',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button:hover',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_hover_transition',
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
                    '{{WRAPPER}} .custom-options .custom-option-button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        $this->end_controls_tab(); // End Hover Style Tab
        
        $this->start_controls_tab('donate_selection_button_active', [
            'label' => __( 'Active', 'skydonate' ),
        ]);
        
        $this->add_control(
            'donate_selection_button_active_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button.selected .amount' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_active_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button.selected',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_active_border',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button.selected',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_active_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .custom-options .custom-option-button.selected' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_active_box_shadow',
                'selector' => '{{WRAPPER}} .custom-options .custom-option-button.selected',
            ]
        );
        
        $this->end_controls_tab(); // End Active Style Tab
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section(); // End Donate Button Style Section

        // Quick Button Style Section
        $this->start_controls_section(
            'select_input_style_section',
            [
                'label' => __( 'Input Area', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_control(
            'input_area_background',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'input_area_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_area_border',
                'selector' => '.donation-form-layout2 {{WRAPPER}} .custom-amount-box',
            ]
        );
        

        $this->add_responsive_control(
            'input_area_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_area_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

		$this->add_control(
			'input_area_title_heading',
			[
				'label' => esc_html__( 'Title', 'skydonate' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_area_title_typography',
                'selector' => '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .total-label',
            ]
        );

        $this->add_control(
            'input_area_title_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .total-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_area_title_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .total-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_area_title_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .total-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
		$this->add_control(
			'main_input_heading',
			[
				'label' => esc_html__( 'Input Field', 'skydonate' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);


        $this->add_control(
            'main_input_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount, .donation-form-layout2 {{WRAPPER}} .custom-amount-box .currency-symbol' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'main_input_background',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'main_input_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'main_input_border',
                'selector' => '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount',
            ]
        );
        

        $this->add_responsive_control(
            'main_input_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'main_input_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '.donation-form-layout2 {{WRAPPER}} .custom-amount-box .selected_amount' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section(); // End Style Section


        $this->start_controls_section(
            'donate_button_style_section',
            [
                'label' => __( 'Donate Button', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
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
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_control(
            'donate_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_border',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_control(
            'donate_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_box_shadow',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button',
            ]
        );
        
        $this->add_responsive_control(
            'donate_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .single_add_to_cart_button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .single_add_to_cart_button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_hover_border',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
            ]
        );
        
        $this->add_control(
            'donate_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .single_add_to_cart_button:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .single_add_to_cart_button:hover',
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
                    '{{WRAPPER}} .single_add_to_cart_button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section();
        

    }

    protected function render() {
        $settings = $this->get_settings_for_display();


        $enable_filters = $settings['enable_filters'] === 'yes';
        $target_donation = (array) $settings['target_donation'];
        if (is_product() && !$enable_filters) {
            $target_donation = array(get_queried_object_id());
        }
        
        $secure_donation_text = $settings['secure_donation_text'] ?? __('Secure Donation', 'skydonate');
        $secure_donation_icon = $settings['secure_donation_icon'] ?? null;
        $show_secure_donation = !empty($settings['show_secure_donation']) && $settings['show_secure_donation'] === 'yes';
        $donate_button_text = $settings['donate_button_text'] ?? __('Donate and Support', 'skydonate');
        $donate_button_icon = $settings['donate_button_icon'] ?? null;
        $no_donations_message = esc_html($settings['no_donations_message']);

        $this->add_render_attribute( 'wrapper_attributes', 'class', ['donation-button-wrapper'] );
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
        if(isset($settings['full_width']) && $settings['full_width'] == 'yes'){
            $this->add_render_attribute( 'wrapper_attributes', 'class', 'button-full' );
        }

        if (empty($target_donation[0])) {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
            return;
        }else {
            echo '<div '.$this->get_render_attribute_string( "wrapper_attributes" ).'>';
            if ($donate_button_text) {
                echo '<a href="/" class="modal-open-button primary-button">';
                if ($donate_button_icon && $settings['icon_position'] == 'left') {
                    echo ' <span class="donation-button-icon pe-2">';
                    \Elementor\Icons_Manager::render_icon($donate_button_icon, ['aria-hidden' => 'true']);
                    echo '</span>';
                }
                echo esc_html($donate_button_text);
                if ($donate_button_icon && $settings['icon_position'] == 'right') {
                    echo ' <span class="donation-button-icon ps-2">';
                    \Elementor\Icons_Manager::render_icon($donate_button_icon, ['aria-hidden' => 'true']);
                    echo '</span>';
                }
                echo '</a>';
            }
            echo '<div class="quick-modal">';
            echo '<div class="quick-modal-overlay"></div>';
            echo '<div class="quick-modal-body">';
            echo '<span class="quick-modal-close"></span>';
            echo '<div class="quick-modal-content">';
            if ($show_secure_donation) {
                echo '<div class="secure-donation">';
                if ($secure_donation_icon) {
                    echo '<span class="secure-donation-icon">';
                    \Elementor\Icons_Manager::render_icon($secure_donation_icon, ['aria-hidden' => 'true']);
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
                    'id'             => esc_attr($target_donation[0]),
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
            echo '</div>'; // End .quick-modal-content
            echo '</div>'; // End .quick-modal-body
            echo '</div>'; // End .quick-modal
            echo '</div>'; // End .donation-button-wrapper
        }
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
