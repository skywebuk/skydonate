<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Zakat_Preview extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'skyweb_donation_zakat_preview';
    }

    public function get_title() {
        return __('Classic Zakat Preview', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-image-box';
    }

    public function get_categories() {
        return ['skyweb_donation'];
    }

    public function get_style_depends() {
        return ['zakat-calculator-preview', 'donation-button'];
    }

    public function get_script_depends() {
        return ['zakat-calculator-preview'];
    }

    protected function _register_controls() {
        
        // Preview Options Section
        $this->start_controls_section(
            'zakat_preview_options',
            [
                'label' => __('Preview Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'target_id',
            [
                'label' => __('Target ID', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Use this ID at classic zakat preview addons', 'skydonate'),
                'default' => uniqid(),
            ]
        ); 
        
        
        $this->add_control(
            'target_product_id',
            [
                'label' => __('Target Donation', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => Skyweb_Donation_Functions::Get_Title('product', 'ids'), // Ensure this returns product IDs
            ]
        );

        $this->end_controls_section();

        // Preview Header Section
        $this->start_controls_section(
            'zakat_preview_header',
            [
                'label' => __('Preview Header', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'zaka_preview_background',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .header_image',
            ]
        );

        $this->add_responsive_control(
            'zaka_preview_height',
            [
                'label' => __('Banner Height', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 1000,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 200,
                ],
                'selectors' => [
                    '{{WRAPPER}} .header_image' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'update_info',
            [
                'label'       => __('Update Info', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Nisab values are updated regularly', 'skydonate'),
                'placeholder' => esc_attr__('Enter the update info', 'skydonate'),
            ]
        );

        $this->end_controls_section();

        // Preview Body Section
        $this->start_controls_section(
            'zakat_preview_body',
            [
                'label' => __('Preview Body', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'zakat_preview_body_summary',
            [
                'label'       => __('Summary Title', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Your Zakat Summary', 'skydonate'),
            ]
        );

        $this->add_control(
            'zakat_preview_body_assets',
            [
                'label'       => __('Total Assets Title', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Total Assets', 'skydonate'),
            ]
        );

        $this->add_control(
            'zakat_preview_body_liabilities',
            [
                'label'       => __('Liabilities Title', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Less Total Liabilities', 'skydonate'),
            ]
        );

        $this->add_control(
            'zakat_preview_body_zakatable',
            [
                'label'       => __('Zakatable Total Title', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Equals Total Zakatable', 'skydonate'),
            ]
        );

        $this->add_control(
            'zakat_preview_body_to_pay',
            [
                'label'       => __('Zakat to Pay Title', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Zakat to Pay', 'skydonate'),
            ]
        );

        $this->end_controls_section();

        // Preview Footer Section
        $this->start_controls_section(
            'zakat_preview_footer',
            [
                'label' => __('Button', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'footer_button_text',
            [
                'label'       => __('Button Text', 'skydonate'),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => esc_html__('Pay Your Zakat', 'skydonate'),
            ]
        );

        $this->add_control(
            'footer_button_icon',
            [
                'label' => __('Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
            ]
        );

        $this->end_controls_section();

        // Style Section for General Settings
        $this->start_controls_section(
            'general_style_section',
            [
                'label' => __('General', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'general_padding',
            [
                'label'      => __('Spacing', 'skydonate'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_left'  => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .zakat_preview .preview_header .header_right' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .zakat_preview .preview_body'                 => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .zakat_preview .preview_footer'               => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name'     => 'general_background',
                'types'    => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .zakat_preview',
            ]
        );

        $this->add_control(
            'general_border_color',
            [
                'label'     => __('Border Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_left'  => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .zakat_preview .preview_header .header_right' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .zakat_preview .preview_header .header_info'  => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .zakat_preview .preview_footer'               => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'header_style_section',
            [
                'label' => __('Header', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'header_title_heading',
            [
                'label' => esc_html__('Title', 'skydonate'),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'header_title_color',
            [
                'label'     => __('Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'header_title_typo',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat_preview .preview_header .header_title',
            ]
        );
        
        $this->add_responsive_control(
            'header_title_margin',
            [
                'label'      => __('Margin', 'skydonate'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'header_info_heading',
            [
                'label' => esc_html__('Amount', 'skydonate'),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'header_info_color',
            [
                'label'     => __('Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_amount, {{WRAPPER}} .zakat_preview .preview_header .header_weight' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'header_info_typo',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat_preview .preview_header .header_amount, {{WRAPPER}} .zakat_preview .preview_header .header_weight',
            ]
        );
        
        $this->end_controls_section();
        

        $this->start_controls_section(
            'info_style_section',
            [
                'label' => __('Info', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'info_padding',
            [
                'label'      => __('Padding', 'skydonate'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'info_typo',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat_preview .preview_header .header_info',
            ]
        );

        $this->add_control(
            'info_color',
            [
                'label'     => __('Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_header .header_info' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
        

        $this->start_controls_section(
            'body_style_section',
            [
                'label' => __('Body', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'body_title_heading',
            [
                'label' => esc_html__('Title', 'skydonate'),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'body_title_color',
            [
                'label'     => __('Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_body .summary_title' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'body_title_typo',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat_preview .preview_body .summary_title',
            ]
        );
        
        $this->add_responsive_control(
            'body_title_margin',
            [
                'label'      => __('Margin', 'skydonate'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .zakat_preview .preview_body .summary_title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'body_list_heading',
            [
                'label' => esc_html__('List', 'skydonate'),
                'type'  => \Elementor\Controls_Manager::HEADING,
            ]
        );
        
        $this->add_control(
            'body_info_color',
            [
                'label'     => __('Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_body .calc_list' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'body_info_typo',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat_preview .preview_body .calc_list',
            ]
        );
        
        $this->add_responsive_control(
            'body_info_margin',
            [
                'label'      => __('Margin', 'skydonate'),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors'  => [
                    '{{WRAPPER}} .zakat_preview .preview_body .calc_list li:not(:last-child)' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'list_amount_color',
            [
                'label'     => __('Amount Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat_preview .preview_body .calc_list .num'  => 'color: {{VALUE}};',
                ],
            ]
        );

        
        $this->end_controls_section();


        // Style Section for Button
        $this->start_controls_section(
            'button_style',
            [
                'label' => __('Button', 'skydonate'),
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
                'default' => 'yes',
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
                    '{{WRAPPER}} .preview_footer' => 'text-align: {{VALUE}};',
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
                    '{{WRAPPER}} .preview_footer .primary-button' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .preview_footer .primary-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "button_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .preview_footer .primary-button",
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
                    '{{WRAPPER}} .preview_footer .primary-button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(\Elementor\Group_Control_Border::get_type(), [
            "name" => "button_hover_border",
            "label" => __("Border", "skydonate"),
            "selector" => "{{WRAPPER}} .preview_footer .primary-button:hover",
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
                    '{{WRAPPER}} .preview_footer .primary-button:hover' => 'background-color: {{VALUE}};',
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


    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $price_data = get_option('metal_price');
        $target_id = $settings['target_id'];
    
        // Extracting metal prices in GBP
        
        $gold_price   = (!empty($price_data['data']['Gold']['GBP']) && $price_data['data']['Gold']['GBP'] != '0') 
                ? (float) $price_data['data']['Gold']['GBP'] 
                : 90.2062;

        $silver_price = (!empty($price_data['data']['Silver']['GBP']) && $price_data['data']['Silver']['GBP'] != '0') 
                ? (float) $price_data['data']['Silver']['GBP'] 
                : 1.1039;

        
        // Calculating Nisab values
        $gold_nisab = 87.48 * $gold_price;
        $silver_nisab = 612.36 * $silver_price;

        $donate_button_icon = $settings['footer_button_icon'];


        $this->add_render_attribute( 'wrapper_attributes', 'class', ['zakat_preview'] );

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

        if(!empty($target_id)){
            $this->add_render_attribute( 'wrapper_attributes', 'id', $target_id );
        }

        ?>
        <div <?php echo $this->get_render_attribute_string( "wrapper_attributes" ); ?> >
            <div class="preview_header">
                <div class="header_image"></div>
                <div class="header_left">
                    <h5 class="header_title">Gold Nisab</h5>
                    <div class="header_amount">£<?php echo number_format($gold_nisab, 2); ?></div>
                    <div class="header_weight">(87.48g)</div>
                </div>
                <div class="header_right">
                    <h5 class="header_title">Silver Nisab</h5>
                    <div class="header_amount">£<?php echo number_format($silver_nisab, 2); ?></div>
                    <div class="header_weight">(612.36g)</div>
                </div>
                <div class="header_info"><?php echo esc_html($settings['update_info']); ?></div>
            </div>
            <div class="preview_body">
                <h2 class="summary_title"><?php echo esc_html($settings['zakat_preview_body_summary']); ?></h2>
                <ul class="calc_list">
                    <li class="assets"><span class="name"><?php echo esc_html($settings['zakat_preview_body_assets']); ?>: </span><span class="num">£<span>0.00</span></span></li>
                    <li class="liabilities"><span class="name"><?php echo esc_html($settings['zakat_preview_body_liabilities']); ?>: </span><span class="num">£<span>0.00</span></span></li>
                    <li class="zakatable"><span class="name"><?php echo esc_html($settings['zakat_preview_body_zakatable']); ?>: </span><span class="num">£<span>0.00</span></span></li>
                    <li class="to_pay"><span class="name"><?php echo esc_html($settings['zakat_preview_body_to_pay']); ?>: </span><span class="num">£<span>0.00</span></span></li>
                </ul>
            </div>
            <div class="preview_footer">
                <input type="hidden" class="product_id" value="<?php echo $settings['target_product_id']; ?>">
                <input type="hidden" class="zakat_input">
                <button class="primary-button" type="button">
                    <?php 
                        if ($donate_button_icon && $settings['icon_position'] == 'left') {
                            echo ' <span class="donation-button-icon pe-2">';
                            \Elementor\Icons_Manager::render_icon($donate_button_icon, ['aria-hidden' => 'true']);
                            echo '</span>';
                        }
                    ?>
                    <?php echo esc_html($settings['footer_button_text']); ?>
                    <?php 
                        if ($donate_button_icon && $settings['icon_position'] == 'right') {
                            echo ' <span class="donation-button-icon ps-2">';
                            \Elementor\Icons_Manager::render_icon($donate_button_icon, ['aria-hidden' => 'true']);
                            echo '</span>';
                        }
                    ?>
                </button>
            </div>
        </div>
        <?php
    }
    
}