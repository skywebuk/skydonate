<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Form extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_form';
    }

    public function get_title() {
        return __('Donation Form', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {                                                                                                                           
        return ['donation-form-one'];                                                                                                                                
    }
    
    public function get_script_depends() {                                                                                                                           
        return ['donation-form'];                                                                                                                                
    }

    
    protected function register_controls() {
        // Section for donation form settings
        $this->start_controls_section(
            'donation_form_section',
            [
                'label' => __('Donation Form', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Enable filters control
        $this->add_control(
            'enable_filters',
            [
                'label' => __('Enable Filters', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        // Product Title Filter as a select dropdown
        $this->add_control(
            'filter_product_title',
            [
                'label' => __('Product Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => false, // Single product selection
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Ensure this returns product IDs
                'default' => '',
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // ===== Button Text =====
        $this->add_control(
            'placeholder_text',
            [
                'label' => __('Placeholder Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Custom Amount', 'skydonate'),
                'placeholder' => __('Enter Placeholder Text', 'skydonate'),
            ]
        );

        $this->end_controls_section();

        $this->Donation_Form_Button_Control();
        
        $this->start_controls_section(
            'donate_tabs_button_style_section',
            [
                'label' => __( 'Tabs', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'tab_wrapper_heading',
            [
                'label' => __( 'Tab Wrapper', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        // Container layout (flex gap)
        $this->add_responsive_control(
            'type_switch_gap',
            [
                'label' => __( 'Gap', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 64,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-switch' => '--switch-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Padding
        $this->add_responsive_control(
            'type_switch_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-switch' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'type_switch_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-switch' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Background
        $this->add_control(
            'type_switch_bg',
            [
                'label' => __( 'Background', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-switch' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'type_switch_bg',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-type-switch',
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'type_switch_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-switch',
            ]
        );

        // Border Radius
        $this->add_responsive_control(
            'type_switch_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-switch' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'type_switch_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-switch',
            ]
        );

        $this->add_control(
            'tab_item_wrapper_heading',
            [
                'label' => __( 'Tab Items', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::HEADING,
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
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn',
            ]
        );
        
        $this->add_responsive_control(
            'donate_tabs_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .donation-type-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .donation-type-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn:hover',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn:hover',
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
                    '{{WRAPPER}} .donation-form .donation-type-btn' => 'transition-duration: {{SIZE}}s',
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
                    '{{WRAPPER}} .donation-form .donation-type-btn.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_tabs_button_active_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn.active',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_tabs_button_active_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn.active',
            ]
        );
        
        $this->add_control(
            'donate_tabs_button_active_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-type-btn.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_tabs_button_active_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-type-btn.active',
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
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'donation_form_title_typography',
                'selector' => '{{WRAPPER}} .donation-form .donation-box-title',
            ]
        );
        
        $this->add_responsive_control(
            'donation_title_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-box-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .donation-box-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
        
        $this->end_controls_section(); // End Donation Title Section
        $this->start_controls_section(
            'donate_selection_button_style_section',
            [
                'label' => __( 'Donate Button', 'skydonate' ),
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
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn',
            ]
        );
        
        $this->add_responsive_control(
            'donate_selection_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn:hover',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_hover_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn:hover',
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
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn' => 'transition-duration: {{SIZE}}s',
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
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn.active' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_selection_button_active_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn.active',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_selection_button_active_border',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn.active',
            ]
        );
        
        $this->add_control(
            'donate_selection_button_active_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .donation-buttons .donation-btn.active' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_selection_button_active_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .donation-buttons .donation-btn.active',
            ]
        );
        
        $this->end_controls_tab(); // End Active Style Tab
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section(); // End Donate Button Style Section

        // Quick Button Style Section
        $this->start_controls_section(
            'select_input_style_section',
            [
                'label' => __( 'Select Input Style', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'select_input_typography',
                'selector' => '{{WRAPPER}} .donation-amount-groups .donation-custom .custom-amount-input,{{WRAPPER}} .donation-amount-groups .donation-custom .currency-symbol',
            ]
        );
        
        $this->add_control(
            'select_input_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-amount-groups .donation-custom .custom-amount-input,{{WRAPPER}} .donation-amount-groups .donation-custom .currency-symbol' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'select_input_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-amount-groups .donation-custom' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'select_input_border',
                'selector' => '{{WRAPPER}} .donation-amount-groups .donation-custom',
            ]
        );
        
        $this->add_control(
            'select_input_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-amount-groups .donation-custom' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'select_input_box_shadow',
                'selector' => '{{WRAPPER}} .donation-amount-groups .donation-custom',
            ]
        );


        $this->add_responsive_control(
            'select_input_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-amount-groups .donation-custom' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section(); // End Style Section

        $this->start_controls_section(
            'name_plaque_style_section',
            [
                'label' => __( 'Name on Plaque', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Text color
        $this->add_control(
            'name_plaque_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Placeholder color
        $this->add_control(
            'name_plaque_placeholder_color',
            [
                'label' => __( 'Placeholder Color', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Background color
        $this->add_control(
            'name_plaque_bg_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'name_plaque_typography',
                'selector' => '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]',
            ]
        );

        // Height
        $this->add_responsive_control(
            'name_plaque_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                    ],
                    'em' => [
                        'min' => 1,
                        'max' => 10,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Padding
        $this->add_responsive_control(
            'name_plaque_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'name_plaque_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'name_plaque_border',
                'selector' => '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]',
            ]
        );

        // Border Radius
        $this->add_responsive_control(
            'name_plaque_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type'  => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'name_plaque_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .name-on-plaque input[name="cart_custom_text"]',
            ]
        );

        $this->end_controls_section();

        
        $this->start_controls_section(
            'donate_button_style_section',
            [
                'label' => __( 'Submit Button', 'skydonate' ),
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
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button',
            ]
        );
        
        $this->add_control(
            'donate_button_text_color',
            [
                'label' => __( 'Text Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_border',
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button',
            ]
        );
        
        $this->add_control(
            'donate_button_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button',
            ]
        );
        
        $this->add_responsive_control(
            'donate_button_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'donate_button_width',
            [
                'label' => __( 'Width', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'donate_button_height',
            [
                'label' => __( 'Height', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'height: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}} .donation-form .form-submit-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'donate_button_hover_background',
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'donate_button_hover_border',
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'donate_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .donation-form .form-submit-button:hover',
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
                    '{{WRAPPER}} .donation-form .form-submit-button' => 'transition-duration: {{SIZE}}s',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Hover Style Tab
        $this->end_controls_tabs(); // End Donate Button Style Tabs
        $this->end_controls_section(); // End Donate Button Style Section
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $id = 0;
        // Determine product ID
        if ($settings['enable_filters'] === 'yes' && !empty($settings['filter_product_title'])) {
            $id = $settings['filter_product_title'];
        } elseif (is_product()) {
            $id = get_queried_object_id();
        }

        if (empty($id)) {
            echo '<div class="woocommerce-info">' . esc_html__('Donation ID not found.', 'skydonate') . '</div>';
            return;
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
            'id'             => $id,
            'placeholder'    => !empty($settings['placeholder_text']) ? esc_attr($settings['placeholder_text']) : __('Custom Amount', 'skydonate'),
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

        // Output shortcode
        echo do_shortcode($shortcode);
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
