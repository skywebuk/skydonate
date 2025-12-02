<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Zakat_Calculator_Addons extends \Elementor\Widget_Base {
    public function get_name() {
        return 'skyweb_donation_zakat_calculator';
    }
    public function get_title() {
        return __('Zakat Calculator', 'skydonate');
    }
    public function get_icon() {
        return 'eicon-table';
    }
    public function get_categories() {
        return ['skyweb_donation'];
    }                                                                                                                                                          
    public function get_style_depends() {                                                                                                                           
        return [                                                                                                                                                    
            'zakat-calculator'                                                                                                                                  
        ];                                                                                                                                                          
    }                                                                                                                                                                                                                                                                                                                              
    public function get_script_depends() {                                                                                                                          
        return [                                                                                                                                                    
            'zakat-calculator'                                                                                                                            
        ];                                                                                                                                                          
    }
    protected function _register_controls() {
        // Editable content controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'show_currency_select',
            [
                'label' => __('Show Currency Select', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'skydonate'),
                'label_off' => __('Hide', 'skydonate'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        $this->add_control(
            'assets_title',
            [
                'label' => __('Assets Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Assets', 'skydonate'),
            ]
        );
        $this->add_control(
            'assets_description',
            [
                'label' => __('Assets Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Assets include cash, property, jewellery, and anything else of value. If a field is not applicable, leave it blank.', 'skydonate'),
            ]
        );

        // Assets repeater
        $assets_repeater = new \Elementor\Repeater();

        $assets_repeater->add_control(
            'assets_input_title',
            [
                'label' => __('Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );


        $assets_repeater->add_control(
            'assets_input_description',
            [
                'label' => __('Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
            ]
        );

        $assets_repeater->add_control(
            'assets_input_placeholder',
            [
                'label' => __('Placeholder', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );
        
        $this->add_control(
            'assets_list',
            [
                'label' => __('Assets List', 'skydonate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $assets_repeater->get_controls(),
                'title_field' => '{{{ assets_input_title }}}',
                'default' => [
                    [
                        'assets_input_title' => __('Money in the bank and home', 'skydonate'),
                        'assets_input_description' => __('Zakat is paid on a year\'s worth of savings. This usually means entering the minimum balance in your account since you calculated your Zakat a year ago. Include all bank accounts, cryptocurrency, PayPal balances, and cash. Bank interest is haram and should not be included.', 'skydonate'),
                        'assets_input_placeholder' => __('Money in the bank and home', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Jewellery (gold, silver)', 'skydonate'),
                        'assets_input_description' => __('The majority of scholars believe that zakat should be paid on all gold and silver jewellery, whether worn or not.', 'skydonate'),
                        'assets_input_placeholder' => __('Jewellery (gold, silver)', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Property', 'skydonate'),
                        'assets_input_description' => __('For zakat purposes, any property other than your home must be included. Zakat is due on the current resale value of properties if you are in the business of buying and then selling them when their value increases. If you are in the business of renting out properties, however, zakat is only due on the savings made from the rental income.', 'skydonate'),
                        'assets_input_placeholder' => __('Property', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Shares', 'skydonate'),
                        'assets_input_description' => __('If you don\'t intend to sell the shares, then zakat is due on dividends you earn. However, if you are buying and selling shares, zakat is due on their current market value.', 'skydonate'),
                        'assets_input_placeholder' => __('Shares', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Money Owed to You', 'skydonate'),
                        'assets_input_description' => __('If you don\'t plan to sell your stock, zakat is due on any dividends you receive. When buying and selling shares, however, zakat is due on the current market value.', 'skydonate'),
                        'assets_input_placeholder' => __('Money Owed to You', 'skydonate'),
                    ],
                ],
            ]
        );



        // Liabilities
        $this->add_control(
            'liabilities_title',
            [
                'label' => __('Liabilities Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Liabilities', 'skydonate'),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'liabilities_description',
            [
                'label' => __('Liabilities Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Liabilities are sums of money owed to others.', 'skydonate'),
            ]
        );


        // Assets repeater
        $liabilities_repeater = new \Elementor\Repeater();

        $liabilities_repeater->add_control(
            'liabilities_input_title',
            [
                'label' => __('Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );


        $liabilities_repeater->add_control(
            'liabilities_input_description',
            [
                'label' => __('Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
            ]
        );

        $liabilities_repeater->add_control(
            'liabilities_input_placeholder',
            [
                'label' => __('Placeholder', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );
        
        $this->add_control(
            'liabilities_list',
            [
                'label' => __('Liabilities List', 'skydonate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $liabilities_repeater->get_controls(),
                'title_field' => '{{{ liabilities_input_title }}}',
                'default' => [
                    [
                        'liabilities_input_title' => __('Personal Debt', 'skydonate'),
                        'liabilities_input_description' => __('Any rent, house payments, utility bills, or money owed to you that is due or overdue should be entered here.', 'skydonate'),
                        'liabilities_input_placeholder' => __('Personal Debt', 'skydonate'),
                    ],
                    [
                        'liabilities_input_title' => __('Business Debt', 'skydonate'),
                        'liabilities_input_description' => __('Any unpaid rent, property payments, invoices, staff salaries, or money you owe, for example, should be entered here.', 'skydonate'),
                        'liabilities_input_placeholder' => __('Business Debt', 'skydonate'),
                    ],
                ],
            ]
        );

        $this->add_control(
            'input_title_nisab',
            [
                'label' => __('Nisab Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Base Nisab on value of', 'skydonate'),
                'separator' => 'before'
            ]
        );

        $this->add_control(
            'input_description_nisab',
            [
                'label' => __('Nisab Description', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Nisab Description', 'skydonate'),
            ]
        );

        $this->add_control(
            'calculate_button_text',
            [
                'label' => __('Calculate Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Calculate Your Zakat', 'skydonate'),
            ]
        );
        $this->add_control(
            'donate_now_button_text',
            [
                'label' => __('Donate Now Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate Now', 'skydonate'),
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'product_title',
            [
                'label' => esc_html__('Select Donation Form', 'skyweb'),
                'description' => esc_html__('Select a donation form for donate now button', 'skyweb'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'multiple' => true,
                'options' => SkyWeb_Donation_Addons::Get_Title('product'),
            ]
        );
        
        $this->end_controls_section();
    

        $this->section_title_style();

        $this->description_style();

        $this->input_lavel_style();
        
        $this->input_field_style();

        $this->button_style();
    
        $this->preview_section_style();
    }


    private function preview_section_style() {
        // Preview Section
        $this->start_controls_section(
            'preview_section_style',
            [
                'label' => __( 'Preview Section', 'skyweb' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
    
        // Text Color Control
        $this->add_control(
            'preview_color',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-preview' => 'color: {{VALUE}};',
                ],
            ]
        );
    
        // Background Control
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'preview_background',
                'label' => __( 'Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-preview',
            ]
        );
    
        // Text Align Control
        $this->add_control(
            'preview_text_align',
            [
                'label' => __( 'Text Align', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'skyweb' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __( 'Center', 'skyweb' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'skyweb' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-preview' => 'text-align: {{VALUE}};',
                ],
                'default' => 'center',
            ]
        );
    
        // Margin Control
        $this->add_responsive_control(
            'preview_margin',
            [
                'label' => __( 'Margin', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-preview' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
    
        // Padding Control
        $this->add_responsive_control(
            'preview_padding',
            [
                'label' => __( 'Padding', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-preview' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );
    
        // Border Control
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'preview_border',
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-preview',
            ]
        );
    
        // Border Radius Control
        $this->add_responsive_control(
            'preview_radius',
            [
                'label' => __( 'Border Radius', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-preview' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
    
        // Box Shadow Control
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'preview_box_shadow',
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-preview',
            ]
        );
    
        $this->end_controls_section();
    }
    
    
    private function button_style() {
        // Button Section
        $this->start_controls_section(
            'button_section_style',
            [
                'label' => __( 'Button', 'skyweb' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
    
        // Button Style Tabs
        $this->start_controls_tabs( 'tabs_button_style' );
    
        // Typography Control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button',
            ]
        );

        // Normal State
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => __( 'Normal', 'skyweb' ),
            ]
        );
    
        // Text Color Control
        $this->add_control(
            'button_text_color',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'color: {{VALUE}};',
                ],
            ]
        );
    
        // Background Control
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background_color',
                'label' => __( 'Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button',
            ]
        );
    
        // Margin Control
        $this->add_responsive_control(
            'button_margin',
            [
                'label' => __( 'Margin', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );
    
        // Padding Control
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __( 'Padding', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );   
    
        // Border Control
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button',
            ]
        );
    
        // Border Radius Control
        $this->add_responsive_control(
            'button_radius',
            [
                'label' => __( 'Border Radius', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
    
        // Box Shadow Control
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button',
            ]
        );

    
        // Height Control
        $this->add_responsive_control(
            'button_height',
            [
                'label' => __( 'Height', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
    
        // Width Control
        $this->add_responsive_control(
            'button_width',
            [
                'label' => __( 'Width', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
    
        // Floating Control
        $this->add_responsive_control(
            'button_floting',
            [
                'label' => __( 'Button Floating', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __( 'Left', 'skyweb' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'none' => [
                        'title' => __( 'None', 'skyweb' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __( 'Right', 'skyweb' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"], {{WRAPPER}} button' => 'float: {{VALUE}};',
                ],
                'default' => 'none',
                'separator' =>'before',
            ]
        );
    
        $this->end_controls_tab();
    
        // Hover State
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => __( 'Hover', 'skyweb' ),
            ]
        );
    
        // Hover Text Color Control
        $this->add_control(
            'button_hover_color',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"]:hover, {{WRAPPER}} button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
    
        // Hover Background Control
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_hover_background',
                'label' => __( 'Hover Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} input[type="submit"]:hover, {{WRAPPER}} button:hover',
            ]
        );
    
        // Hover Border Color Control
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __( 'Border Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type="submit"]:hover, {{WRAPPER}} button:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );
    
        // Hover Box Shadow Control
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_hover_box_shadow',
                'selector' => '{{WRAPPER}} input[type="submit"]:hover, {{WRAPPER}} button:hover',
            ]
        );
    
        $this->end_controls_tab();
    
        $this->end_controls_tabs();
    
        $this->end_controls_section();
    }
    
    
    private function input_field_style(){
        $this->start_controls_section(
            'input_style_section',
            [
                'label' => __( 'Input Field', 'skyweb' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        
        $this->start_controls_tabs( 'tabs_input_style' );
        
        $this->start_controls_tab(
            'tab_input_normal',
            [
                'label' => __( 'Normal', 'skyweb' ),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea',
            ]
        );
        
        $this->add_control(
            'input_text_color',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea, {{WRAPPER}} .zakat-calculator ::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'input_background_color',
                'label' => __( 'Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea'
            ]
        );
        
        $this->add_responsive_control(
            'input_margin',
            [
                'label' => __( 'Margin', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );
        
        $this->add_responsive_control(
            'input_padding',
            [
                'label' => __( 'Padding', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_border',
                'selector' => '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea',
            ]
        );
        
        $this->add_responsive_control(
            'input_radius',
            [
                'label' => __( 'Border Radius', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' =>'before',
            ]
        );
        
        $this->add_responsive_control(
            'input_box_height',
            [
                'label' => __( 'Height', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea' => 'min-height: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'input_box_width',
            [
                'label' => __( 'Width', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_box_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator select, {{WRAPPER}} .zakat-calculator input[type="text"], {{WRAPPER}} .zakat-calculator input[type="email"], {{WRAPPER}} .zakat-calculator input[type="url"], {{WRAPPER}} .zakat-calculator input[type="password"], {{WRAPPER}} .zakat-calculator input[type="search"], {{WRAPPER}} .zakat-calculator input[type="number"], {{WRAPPER}} .zakat-calculator input[type="tel"], {{WRAPPER}} .zakat-calculator input[type="date"], {{WRAPPER}} .zakat-calculator input[type="month"], {{WRAPPER}} .zakat-calculator input[type="week"], {{WRAPPER}} .zakat-calculator input[type="time"], {{WRAPPER}} .zakat-calculator input[type="datetime"], {{WRAPPER}} .zakat-calculator input[type="datetime-local"], {{WRAPPER}} .zakat-calculator input[type="color"], {{WRAPPER}} .zakat-calculator textarea',
                'separator' => 'before',
            ]
        );
        

        $this->end_controls_tab();


        // Focus Tab
        $this->start_controls_tab(
            'tab_input_focus',
            [
                'label' => __( 'Focus', 'skyweb' ),
            ]
        );

        $this->add_control(
            'input_focus_text_color',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select:focus, {{WRAPPER}} .zakat-calculator input[type="text"]:focus, {{WRAPPER}} .zakat-calculator input[type="email"]:focus, {{WRAPPER}} .zakat-calculator input[type="url"]:focus, {{WRAPPER}} .zakat-calculator input[type="password"]:focus, {{WRAPPER}} .zakat-calculator input[type="search"]:focus, {{WRAPPER}} .zakat-calculator input[type="number"]:focus, {{WRAPPER}} .zakat-calculator input[type="tel"]:focus, {{WRAPPER}} .zakat-calculator input[type="date"]:focus, {{WRAPPER}} .zakat-calculator input[type="month"]:focus, {{WRAPPER}} .zakat-calculator input[type="week"]:focus, {{WRAPPER}} .zakat-calculator input[type="time"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:focus, {{WRAPPER}} .zakat-calculator input[type="color"]:focus, {{WRAPPER}} .zakat-calculator textarea:focus' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'input_focus_background_color',
                'label' => __( 'Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zakat-calculator select:focus, {{WRAPPER}} .zakat-calculator input[type="text"]:focus, {{WRAPPER}} .zakat-calculator input[type="email"]:focus, {{WRAPPER}} .zakat-calculator input[type="url"]:focus, {{WRAPPER}} .zakat-calculator input[type="password"]:focus, {{WRAPPER}} .zakat-calculator input[type="search"]:focus, {{WRAPPER}} .zakat-calculator input[type="number"]:focus, {{WRAPPER}} .zakat-calculator input[type="tel"]:focus, {{WRAPPER}} .zakat-calculator input[type="date"]:focus, {{WRAPPER}} .zakat-calculator input[type="month"]:focus, {{WRAPPER}} .zakat-calculator input[type="week"]:focus, {{WRAPPER}} .zakat-calculator input[type="time"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:focus, {{WRAPPER}} .zakat-calculator input[type="color"]:focus, {{WRAPPER}} .zakat-calculator textarea:focus'
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_focus_border',
                'label' => __( 'Border', 'skyweb' ),
                'selector' => '{{WRAPPER}} .zakat-calculator select:focus, {{WRAPPER}} .zakat-calculator input[type="text"]:focus, {{WRAPPER}} .zakat-calculator input[type="email"]:focus, {{WRAPPER}} .zakat-calculator input[type="url"]:focus, {{WRAPPER}} .zakat-calculator input[type="password"]:focus, {{WRAPPER}} .zakat-calculator input[type="search"]:focus, {{WRAPPER}} .zakat-calculator input[type="number"]:focus, {{WRAPPER}} .zakat-calculator input[type="tel"]:focus, {{WRAPPER}} .zakat-calculator input[type="date"]:focus, {{WRAPPER}} .zakat-calculator input[type="month"]:focus, {{WRAPPER}} .zakat-calculator input[type="week"]:focus, {{WRAPPER}} .zakat-calculator input[type="time"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:focus, {{WRAPPER}} .zakat-calculator input[type="color"]:focus, {{WRAPPER}} .zakat-calculator textarea:focus',
            ]
        );

        $this->add_responsive_control(
            'input_focus_radius',
            [
                'label' => __( 'Border Radius', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select:focus, {{WRAPPER}} .zakat-calculator input[type="text"]:focus, {{WRAPPER}} .zakat-calculator input[type="email"]:focus, {{WRAPPER}} .zakat-calculator input[type="url"]:focus, {{WRAPPER}} .zakat-calculator input[type="password"]:focus, {{WRAPPER}} .zakat-calculator input[type="search"]:focus, {{WRAPPER}} .zakat-calculator input[type="number"]:focus, {{WRAPPER}} .zakat-calculator input[type="tel"]:focus, {{WRAPPER}} .zakat-calculator input[type="date"]:focus, {{WRAPPER}} .zakat-calculator input[type="month"]:focus, {{WRAPPER}} .zakat-calculator input[type="week"]:focus, {{WRAPPER}} .zakat-calculator input[type="time"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:focus, {{WRAPPER}} .zakat-calculator input[type="color"]:focus, {{WRAPPER}} .zakat-calculator textarea:focus' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_focus_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator select:focus, {{WRAPPER}} .zakat-calculator input[type="text"]:focus, {{WRAPPER}} .zakat-calculator input[type="email"]:focus, {{WRAPPER}} .zakat-calculator input[type="url"]:focus, {{WRAPPER}} .zakat-calculator input[type="password"]:focus, {{WRAPPER}} .zakat-calculator input[type="search"]:focus, {{WRAPPER}} .zakat-calculator input[type="number"]:focus, {{WRAPPER}} .zakat-calculator input[type="tel"]:focus, {{WRAPPER}} .zakat-calculator input[type="date"]:focus, {{WRAPPER}} .zakat-calculator input[type="month"]:focus, {{WRAPPER}} .zakat-calculator input[type="week"]:focus, {{WRAPPER}} .zakat-calculator input[type="time"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime"]:focus, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:focus, {{WRAPPER}} .zakat-calculator input[type="color"]:focus, {{WRAPPER}} .zakat-calculator textarea:focus',
            ]
        );
        

        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'tab_input_hover',
            [
                'label' => __( 'Hover', 'skyweb' ),
            ]
        );
        
        $this->add_control(
            'input_text_color_hover',
            [
                'label' => __( 'Text Color', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select:hover, {{WRAPPER}} .zakat-calculator input[type="text"]:hover, {{WRAPPER}} .zakat-calculator input[type="email"]:hover, {{WRAPPER}} .zakat-calculator input[type="url"]:hover, {{WRAPPER}} .zakat-calculator input[type="password"]:hover, {{WRAPPER}} .zakat-calculator input[type="search"]:hover, {{WRAPPER}} .zakat-calculator input[type="number"]:hover, {{WRAPPER}} .zakat-calculator input[type="tel"]:hover, {{WRAPPER}} .zakat-calculator input[type="date"]:hover, {{WRAPPER}} .zakat-calculator input[type="month"]:hover, {{WRAPPER}} .zakat-calculator input[type="week"]:hover, {{WRAPPER}} .zakat-calculator input[type="time"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:hover, {{WRAPPER}} .zakat-calculator input[type="color"]:hover, {{WRAPPER}} .zakat-calculator textarea:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'input_background_color_hover',
                'label' => __( 'Background', 'skyweb' ),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .zakat-calculator select:hover, {{WRAPPER}} .zakat-calculator input[type="text"]:hover, {{WRAPPER}} .zakat-calculator input[type="email"]:hover, {{WRAPPER}} .zakat-calculator input[type="url"]:hover, {{WRAPPER}} .zakat-calculator input[type="password"]:hover, {{WRAPPER}} .zakat-calculator input[type="search"]:hover, {{WRAPPER}} .zakat-calculator input[type="number"]:hover, {{WRAPPER}} .zakat-calculator input[type="tel"]:hover, {{WRAPPER}} .zakat-calculator input[type="date"]:hover, {{WRAPPER}} .zakat-calculator input[type="month"]:hover, {{WRAPPER}} .zakat-calculator input[type="week"]:hover, {{WRAPPER}} .zakat-calculator input[type="time"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:hover, {{WRAPPER}} .zakat-calculator input[type="color"]:hover, {{WRAPPER}} .zakat-calculator textarea:hover',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_border_hover',
                'selector' => '{{WRAPPER}} .zakat-calculator select:hover, {{WRAPPER}} .zakat-calculator input[type="text"]:hover, {{WRAPPER}} .zakat-calculator input[type="email"]:hover, {{WRAPPER}} .zakat-calculator input[type="url"]:hover, {{WRAPPER}} .zakat-calculator input[type="password"]:hover, {{WRAPPER}} .zakat-calculator input[type="search"]:hover, {{WRAPPER}} .zakat-calculator input[type="number"]:hover, {{WRAPPER}} .zakat-calculator input[type="tel"]:hover, {{WRAPPER}} .zakat-calculator input[type="date"]:hover, {{WRAPPER}} .zakat-calculator input[type="month"]:hover, {{WRAPPER}} .zakat-calculator input[type="week"]:hover, {{WRAPPER}} .zakat-calculator input[type="time"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:hover, {{WRAPPER}} .zakat-calculator input[type="color"]:hover, {{WRAPPER}} .zakat-calculator textarea:hover',
            ]
        );
        
        $this->add_responsive_control(
            'input_radius_hover',
            [
                'label' => __( 'Border Radius', 'skyweb' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator select:hover, {{WRAPPER}} .zakat-calculator input[type="text"]:hover, {{WRAPPER}} .zakat-calculator input[type="email"]:hover, {{WRAPPER}} .zakat-calculator input[type="url"]:hover, {{WRAPPER}} .zakat-calculator input[type="password"]:hover, {{WRAPPER}} .zakat-calculator input[type="search"]:hover, {{WRAPPER}} .zakat-calculator input[type="number"]:hover, {{WRAPPER}} .zakat-calculator input[type="tel"]:hover, {{WRAPPER}} .zakat-calculator input[type="date"]:hover, {{WRAPPER}} .zakat-calculator input[type="month"]:hover, {{WRAPPER}} .zakat-calculator input[type="week"]:hover, {{WRAPPER}} .zakat-calculator input[type="time"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:hover, {{WRAPPER}} .zakat-calculator input[type="color"]:hover, {{WRAPPER}} .zakat-calculator textarea:hover' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_hover_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator select:hover, {{WRAPPER}} .zakat-calculator input[type="text"]:hover, {{WRAPPER}} .zakat-calculator input[type="email"]:hover, {{WRAPPER}} .zakat-calculator input[type="url"]:hover, {{WRAPPER}} .zakat-calculator input[type="password"]:hover, {{WRAPPER}} .zakat-calculator input[type="search"]:hover, {{WRAPPER}} .zakat-calculator input[type="number"]:hover, {{WRAPPER}} .zakat-calculator input[type="tel"]:hover, {{WRAPPER}} .zakat-calculator input[type="date"]:hover, {{WRAPPER}} .zakat-calculator input[type="month"]:hover, {{WRAPPER}} .zakat-calculator input[type="week"]:hover, {{WRAPPER}} .zakat-calculator input[type="time"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime"]:hover, {{WRAPPER}} .zakat-calculator input[type="datetime-local"]:hover, {{WRAPPER}} .zakat-calculator input[type="color"]:hover, {{WRAPPER}} .zakat-calculator textarea:hover',
            ]
        );
        
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->end_controls_section();
        
    }

    private function input_lavel_style(){
        // Input Label Style tab section
        $this->start_controls_section(
            'input_label_style',
            [
                'label' => __( 'Input Label', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_label_typography',
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-label',
            ]
        );

        $this->add_control(
            'input_label_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_label_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'input_label_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'input_label_border',
                'label' => __( 'Border', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-label',
            ]
        );

        $this->add_responsive_control(
            'input_label_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_label_box_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-label',
            ]
        );

        $this->add_control(
            'input_label_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-label' => 'background-color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

    }

    private function description_style(){
        // Descriptions Style tab section
        $this->start_controls_section(
            'descriptions_style',
            [
                'label' => __( 'Descriptions', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'descriptions_typography',
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-description',
            ]
        );

        $this->add_control(
            'descriptions_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'descriptions_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'descriptions_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'descriptions_border',
                'label' => __( 'Border', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-description',
            ]
        );

        $this->add_responsive_control(
            'descriptions_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-description' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'descriptions_box_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-description',
            ]
        );

        $this->add_control(
            'descriptions_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-description' => 'background-color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

    }

    private function section_title_style(){
        // Section Title Style tab section
        $this->start_controls_section(
            'section_title_style',
            [
                'label' => __( 'Section Title', 'skydonate' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'section_title_typography',
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-section-title',
            ]
        );

        $this->add_control(
            'section_title_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-section-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'section_title_margin',
            [
                'label' => __( 'Margin', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-section-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'section_title_padding',
            [
                'label' => __( 'Padding', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-section-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'section_title_border',
                'label' => __( 'Border', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-section-title',
            ]
        );

        $this->add_responsive_control(
            'section_title_border_radius',
            [
                'label' => __( 'Border Radius', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em' ],
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-section-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'section_title_box_shadow',
                'label' => __( 'Box Shadow', 'skydonate' ),
                'selector' => '{{WRAPPER}} .zakat-calculator .zakat-section-title',
            ]
        );

        $this->add_control(
            'section_title_background_color',
            [
                'label' => __( 'Background Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-calculator .zakat-section-title' => 'background-color: {{VALUE}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

    }


    protected function render() {
        $settings = $this->get_settings_for_display();
        $price_data = get_option('metal_price');
        $apiKey = sanitize_text_field(get_option('zakat_calc_api'));
        $product_id = !empty($settings['product_title']) ? $settings['product_title'] : 123;

    
        if (empty($apiKey) && current_user_can('administrator')) {
            // Generate the admin URL for the settings page
            $admin_url = admin_url('admin.php?page=wc-custom-donation-settings');
            // Notify admin to set up API with a dynamic URL
            echo '<div class="woocommerce-info">Please set up your API to get actual values. <a href="' . esc_url($admin_url) . '">Setup API Key</a></div>';
        }
    
        if (empty($price_data)) {
            // Set default values if no price data is found
            $price_data = '{"data":{"Gold":{"USD":"79.37","GBP":"61.81","EUR":"72.14"},"Silver":{"USD":"0.90","GBP":"0.70","EUR":"0.82"}},"updated_at":"2024-08-14 07:27:02"}';
        } else {
            // Ensure $price_data is encoded as JSON
            $price_data = wp_json_encode($price_data);
        }
        $price_data = strtolower($price_data);
        ?>
        <div class="zakat-calculator" data-settings='<?php echo esc_attr($price_data); ?>' data-product="<?php echo esc_attr($product_id); ?>">
            <div class="zakat-options">
                <?php if ('yes' === $settings['show_currency_select']) : ?>
                    <div class="zakat-form-group">
                        <select class="zakat-currency-select zakat-input" id="zakat-currency-select" name="currency">
                            <option value="GBP">GBP</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>
                <?php endif; ?>
    
                <?php if (!empty($settings['assets_title'])): ?>
                    <h3 class="zakat-section-title">
                        <?php echo esc_html($settings['assets_title']); ?>
                    </h3>
                <?php endif; ?>
    
                <?php if (!empty($settings['assets_description'])): ?>
                    <p class="zakat-description">
                        <?php echo esc_html($settings['assets_description']); ?>
                    </p>
                <?php endif; ?>
    
                <form id="zakat-calculator-form" class="zakat-form">
                    <?php
                    if (!empty($settings['assets_list'])):
                        $i = 1;
                        foreach ($settings['assets_list'] as $asset):
                            ?>
                            <div class="zakat-form-group">
                                <label for="<?php echo esc_attr($asset['assets_input_title']); ?>" class="zakat-label">
                                    <?php echo esc_html($asset['assets_input_title']); ?>
                                </label>
                                <div class="relative">
                                    <span class="currency-name">£</span>
                                    <input type="number" id="<?php echo esc_attr($asset['assets_input_title']); ?>" name="assets-<?php echo $i; ?>" class="zakat-input loop-input" placeholder="<?php echo esc_attr($asset['assets_input_placeholder']); ?>">
                                </div>
                                <?php if (!empty($asset['assets_input_description'])): ?>
                                    <p class="zakat-description">
                                        <?php echo esc_html($asset['assets_input_description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                    endif;
                    ?>
    
                    <?php
                    if (!empty($settings['liabilities_title'])):
                        ?>
                        <h3 class="zakat-section-title">
                            <?php echo esc_html($settings['liabilities_title']); ?>
                        </h3>
                        <?php
                    endif;
                    ?>
    
                    <?php
                    if (!empty($settings['liabilities_description'])):
                        ?>
                        <p class="zakat-description">
                            <?php echo esc_html($settings['liabilities_description']); ?>
                        </p>
                        <?php
                    endif;
                    ?>
    
                    <?php
                    if (!empty($settings['liabilities_list'])):
                        $i = 1;
                        foreach ($settings['liabilities_list'] as $liability):
                            ?>
                            <div class="zakat-form-group">
                                <label for="<?php echo esc_attr($liability['liabilities_input_title']); ?>" class="zakat-label">
                                    <?php echo esc_html($liability['liabilities_input_title']); ?>
                                </label>
                                <div class="relative">
                                    <span class="currency-name">£</span>
                                    <input type="number" id="<?php echo esc_attr($liability['liabilities_input_title']); ?>" name="liabilities-<?php echo $i; ?>" class="zakat-input loop-input" placeholder="<?php echo esc_attr($liability['liabilities_input_placeholder']); ?>">
                                </div>
                                <?php if (!empty($liability['liabilities_input_description'])): ?>
                                    <p class="zakat-description">
                                        <?php echo esc_html($liability['liabilities_input_description']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                    endif;
                    ?>
    
                    <div class="zakat-form-group">
                        <label for="zakat_metal_select" class="zakat-label">
                            <?php echo esc_html($settings['input_title_nisab']); ?>
                        </label>
                        <select class="zakat-metal-select zakat-input" id="zakat_metal_select" name="metal">
                            <option value="XAG"><?php _e('Silver','skydonate'); ?></option>
                            <option value="XAU"><?php _e('Gold','skydonate'); ?></option>
                        </select>
                        <?php if (!empty($settings['input_description_nisab'])): ?>
                            <p class="zakat-description">
                                <?php echo esc_html($settings['input_description_nisab']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
    
                    <?php if (!empty($settings['calculate_button_text'])): ?>
                        <button type="submit" class="zakat-submit-button">
                            <?php echo esc_html($settings['calculate_button_text']); ?>
                            <span class="spinner"></span>
                        </button>
                    <?php endif; ?>
                </form>
            </div>
            <div class="zakat-preview">
                <h4 class="zakat-preview-title"><?php _e('Your total zakat due is','skydonate'); ?></h4>
                <h2 class="zakat-deu-amount"></h2>
                <?php if (!empty($settings['donate_now_button_text'])): ?>
                    <!-- Donate Now Button -->
                    <div class="donate-button">
                        <button type="button" class="donate-now-button">
                            <?php echo wp_kses_post($settings['donate_now_button_text']); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}