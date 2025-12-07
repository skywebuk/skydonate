<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Metal_Values extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_metal_values';
    }

    public function get_title() {
        return __('Metal Values', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-info-box';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    protected function _register_controls() {

        // Content section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Add select control for metal type
        $this->add_control(
            'metal_value_option',
            [
                'label' => __('Metal Value Option', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'Gold' => __('Gold', 'skydonate'),
                    'Silver' => __('Silver', 'skydonate'),
                ],
                'default' => 'Gold',
            ]
        );

        // Add control for gold grams
        $this->add_control(
            'gold_grams',
            [
                'label' => __('Gold Amount in Grams', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 87.48,
                'condition' => [
                    'metal_value_option' => 'Gold',
                ],
            ]
        );

        // Add control for silver grams
        $this->add_control(
            'silver_grams',
            [
                'label' => __('Silver Amount in Grams', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 612.36,
                'condition' => [
                    'metal_value_option' => 'Silver',
                ],
            ]
        );

        // Add control for description content
        $this->add_control(
            'description_content',
            [
                'label' => __('Description Content', 'skydonate'),
                'description' => __('Use placeholders in your description content: 
                    {amount_in_grams} - This will be replaced with the amount of metal in grams based on the selected option (Gold or Silver).
                    {metal_price} - This will be replaced with the price per gram of the selected metal.', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('This is equal to {amount_in_grams} grams at {metal_price} per gram', 'skydonate'),
            ]
        );


        $this->end_controls_section();

        // Area Style
        $this->start_controls_section(
            'area_style_section',
            [
                'label' => __('Area Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Area text-align
        $this->add_control(
            'area_text_align',
            [
                'label' => __('Text Align', 'skydonate'),
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
                    '{{WRAPPER}} .metal-value' => 'text-align: {{VALUE}};',
                ],
            ]
        );


        // Area margin
        $this->add_responsive_control(
            'area_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-value' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Area padding
        $this->add_responsive_control(
            'area_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-value' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Area border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'area_border',
                'label' => __('Border', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-value',
            ]
        );

        // Area border radius
        $this->add_responsive_control(
            'area_border_radius',
            [
                'label' => __('Border Radius', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-value' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Area box shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'area_box_shadow',
                'label' => __('Box Shadow', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-value',
            ]
        );

        // Area background
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'area_background',
                'label' => __('Background', 'skydonate'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .metal-value',
            ]
        );

        $this->end_controls_section();

        // Title Style
        $this->start_controls_section(
            'title_style_section',
            [
                'label' => __('Title Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Title typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-title',
            ]
        );

        // Title color
        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .metal-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Title margin
        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title padding
        $this->add_responsive_control(
            'title_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'title_border',
                'label' => __('Border', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-title',
            ]
        );

        // Title background
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'title_background',
                'label' => __('Background', 'skydonate'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .metal-title',
            ]
        );

        $this->end_controls_section();

        // Description Style
        $this->start_controls_section(
            'description_style_section',
            [
                'label' => __('Description Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Description typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'label' => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-description',
            ]
        );

        // Description color
        $this->add_control(
            'description_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .metal-description' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Description margin
        $this->add_responsive_control(
            'description_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-description' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Description padding
        $this->add_responsive_control(
            'description_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .metal-description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Description border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'description_border',
                'label' => __('Border', 'skydonate'),
                'selector' => '{{WRAPPER}} .metal-description',
            ]
        );

        // Description background
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'description_background',
                'label' => __('Background', 'skydonate'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .metal-description',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
    $settings = $this->get_settings_for_display();
    $metal_value_option = $settings['metal_value_option'];
    $description_content = $settings['description_content'];

    // Get the selected amount in grams
    $amount_in_grams = ($metal_value_option == 'Gold') ? $settings['gold_grams'] : $settings['silver_grams'];

    // Get metal price data from options
    $price_data = get_option('metal_price');
    

    if (empty($price_data)) {
        // Default values for gold and silver
        $default_data = array(
            'Gold' => array(
                'GBP' => 79.1534
            ),
            'Silver' => array(
                'GBP' => 0.8922,
            )
        );
        $price_data = array('data' => $default_data, 'updated_at' => '2024-08-14 05:21:08');
    }

    // Ensure that the price is a valid number
    $metal_price = isset($price_data['data'][$metal_value_option]['GBP']) && is_numeric($price_data['data'][$metal_value_option]['GBP'])
        ? floatval($price_data['data'][$metal_value_option]['GBP'])
        : 0; // Set a default value if not valid

    // Format the metal price and total value
    $formatted_metal_price = number_format($metal_price, 2);
    $total_value = number_format($metal_price * $amount_in_grams, 2);

    // Define a CSS class based on the currency symbol
    $currency_symbol = '£';
    $currency_class = strtolower($currency_symbol);

    // Replace placeholders in the description content with HTML for the currency and metal price
    $description_content = str_replace(
        ['{amount_in_grams}', '{metal_price}'],
        [
            $amount_in_grams,
            '<span class="currency">' . esc_html($currency_symbol) . '</span><span class="value">' . esc_html($formatted_metal_price) . '</span>'
        ],
        $description_content
    );

    ?>
    <div class="metal-value <?php echo esc_attr(strtolower($metal_value_option)); ?> currency-<?php echo esc_attr($currency_class); ?>">
        <h3 class="metal-title">
            <?php echo esc_html($metal_value_option); ?>: 
            <span class="currency"><?php echo esc_html($currency_symbol); ?></span>
            <span class="value"><?php echo esc_html($total_value); ?></span>
        </h3>
        <p class="metal-description"><?php echo $description_content; ?></p>
    </div>
    <?php
}

    
    
}
