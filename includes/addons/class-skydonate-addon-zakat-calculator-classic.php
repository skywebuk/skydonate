<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Zakat_Calculator_Classic extends \Elementor\Widget_Base {
    public function get_name() {
        return 'skyweb_donation_zakat_calculator_classic';
    }
    public function get_title() {
        return __('Classic Zakat Calculator', 'skydonate');
    }
    public function get_icon() {
        return 'eicon-table';
    }
    public function get_categories() {
        return ['skydonate'];
    }                                                                                                                                                          
    public function get_style_depends() {                                                                                                                           
        return [                                                                                                                                                    
            'zakat-calculator-classic'                                                                                                                                  
        ];
    }
    public function get_script_depends() {                                                                                                                          
        return [                                                                                                                                                    
            'zakat-calculator-classic'                                                                                                                            
        ];                                                                                                                                                          
    }
    protected function _register_controls() {
        // Editable content controls
        $this->start_controls_section(
            'calculator_header_section',
            [
                'label' => __('Header', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'preview_id',
            [
                'label' => __('Preview ID', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('67a4b78d095c6', 'skydonate'),
                'description' => __('Enter the preview ID from the Zakat Preview addon', 'skydonate'),
            ]
        );
        

        $this->add_control(
            'input_title_nisab',
            [
                'label' => __('Nisab Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Nisab to use', 'skydonate'),
                'separator' => 'before'
            ]
        );

            $this->add_control(
                'input_description_nisab',
                [
                    'label' => __('Nisab Description', 'skydonate'),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => __('The nisab determines the minimum level of wealth before zakat is due. You can choose to use either the prescribed weight of gold or silver for the nisab.', 'skydonate'),
                ]
            );

            $this->add_control(
                'info_icon',
                [
                    'label' => __('Info Icon', 'skydonate'),
                    'type' => \Elementor\Controls_Manager::ICONS,
                    'default' => [
                        'value' => 'fa-solid fa-info',
                        'library' => 'solid',
                    ],
                ]
            );

        $this->end_controls_section();

        $this->start_controls_section(
            'assets_list_section',
            [
                'label' => __('Assets List', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
                        'assets_input_title' => __('Cash at Home / Bank', 'skydonate'),
                        'assets_input_description' => __('Zakat is paid on a year\'s worth of savings. This usually means entering the minimum balance in your account since you calculated your Zakat a year ago. Include all bank accounts, cryptocurrency, PayPal balances, and cash. Bank interest is haram and should not be included.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Gold & Silver', 'skydonate'),
                        'assets_input_description' => __('Most scholars are of the opinion that zakat should be paid on all gold and silver jewellery, whether it is worn or not, and even if it is owned by a man (who isn\'t permitted to wear gold jewellery).', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Property (Other Than Home)', 'skydonate'),
                        'assets_input_description' => __('Any property other than your home must be considered for zakat. If you are in the business of buying and then selling properties when they appreciate in value, then zakat is due on the current resale value of these properties. However, If you are in the business of letting properties (rather than buying and selling them), then zakat is due on savings made from this rental income only.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Pensions', 'skydonate'),
                        'assets_input_description' => __('If the payments are deducted from the salary at source, so that the money never comes into the possession of the contributor, no zakat is due on the payments. Zakat will only become payable when money from the fund is paid out and received by the contributor.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Shares', 'skydonate'),
                        'assets_input_description' => __('If you don\'t intend to sell the shares, then zakat is due on dividends you earn from them. However, if you are buying and selling shares, then zakat is due on their current market value.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Other Investments', 'skydonate'),
                        'assets_input_description' => __('The return on cash investments / bonds tends to be \'fixed-interest\' and as such no zakat is due on this haram income. But zakat must still be paid on the initial sum invested.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Money Owed To You', 'skydonate'),
                        'assets_input_description' => __('If you are owed any debts, and you believe the debt can be recovered on demand, add it here. However, if there is any doubt about when and if you will recover the debt, do not include it here — but include it in the year you actually receive it, and pay zakat for all previous years that the debt was outstanding. If you have already received some of the debt, then zakat is due on this received amount.', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'assets_input_title' => __('Business Value', 'skydonate'),
                        'assets_input_description' => __('Add the total value of: cash in tills and at bank + stock for sale (current sale value) + raw materials (value at cost).', 'skydonate'),
                        'assets_input_placeholder' => __('', 'skydonate'),
                    ],
                ],
            ]
        );
        
        $this->end_controls_section();
        
        $this->start_controls_section(
            'liabilities_list_section',
            [
                'label' => __('Liabilities List', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Liabilities repeater
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
                        'liabilities_input_title' => __('Unpaid Debts', 'skydonate'),
                        'liabilities_input_description' => __('Any unpaid rent, house payments, utility bills or money that you owe etc., that are due or overdue should be excluded.', 'skydonate'),
                        'liabilities_input_placeholder' => __('', 'skydonate'),
                    ],
                    [
                        'liabilities_input_title' => __('Business Debts', 'skydonate'),
                        'liabilities_input_description' => __('Any unpaid rent, property payments, invoices, staff salaries or money that you owe etc., that are due or overdue should be excluded.', 'skydonate'),
                        'liabilities_input_placeholder' => __('', 'skydonate'),
                    ],
                ],
            ]
        );
        
        $this->end_controls_section();

        $this->start_controls_section(
            'general_style_section',
            [
                'label' => __('General Style', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_responsive_control(
            'general_padding',
            [
                'label' => __('Spacing', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-assets' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-liabilities' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-header' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'general_background',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .classic-zakat-calculator',
            ]
        );
        
        $this->add_control(
            'general_border_color',
            [
                'label' => __('Border Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-assets' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-liabilities' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-header' => 'border-color: {{VALUE}};',
                    '{{WRAPPER}} .classic-zakat-calculator' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'general_accent_color',
            [
                'label' => __('Accent Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator' => ' --classic-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'label_style_section',
            [
                'label' => __('Label Style', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Label Color
        $this->add_control(
            'label_style_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-label' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Typography Control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'label_typography',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-label',
            ]
        );
        
        $this->end_controls_section();
        

        $this->start_controls_section(
            'input_style_section',
            [
                'label' => __('Input Style', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Label Color
        $this->add_control(
            'input_style_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .zakat-radio-group .zakat-radio, 
                    {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group input[type="number"], 
                    {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Typography Control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'input_typography',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .zakat-radio-group .zakat-radio, 
                              {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group input[type="number"], 
                              {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol',
            ]
        );
        
        // Input Height
        $this->add_responsive_control(
            'input_height',
            [
                'label' => __('Height', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 1000,
                        'step' => 10,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 60,
                ],
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group input[type="number"], 
                    {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        // Border Color
        $this->add_control(
            'input_border_color',
            [
                'label' => __('Border Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group input[type="number"], {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-info,
                    {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol' => 'border-color: {{VALUE}};',
                ],
            ]
        );
        
        // Currency Background Color
        $this->add_control(
            'currency_background_color',
            [
                'label' => __('Currency Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol, {{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-info' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control('input_radius', [
            'label' => esc_html__('Border Radius', 'skydonate'),
            'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'], // Optional: Allow percentage values too
            'selectors' => [
                '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group input[type="number"]' =>
                    'border-radius: 0 {{RIGHT}}px {{BOTTOM}}px 0;',
                '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .input-group .currency-symbol' =>
                    'border-radius: {{TOP}}px 0 0 {{LEFT}}px;',
            ],
        ]);
        
        $this->end_controls_section();
        
        $this->start_controls_section(
            'description_style_section',
            [
                'label' => __('Description Style', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Label Color
        $this->add_control(
            'description_style_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-info' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        // Typography Control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'description_typography',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-info',
            ]
        );
        
        
        $this->add_responsive_control(
            'description_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-form-group .zakat-info' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        
        $this->end_controls_section();


        $this->start_controls_section(
            'footer_style_section',
            [
                'label' => __('Footer', 'skydonate'),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_responsive_control(
            'footer_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer .zakat-total' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'footer_background',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer .zakat-total',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'footer_typography',
                'label'    => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer .zakat-total',
            ]
        );
        
        $this->add_control(
            'footer_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer .zakat-total' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'list_amount_color',
            [
                'label'     => __('Amount Color', 'skydonate'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .classic-zakat-calculator .zakat-calculator-footer .zakat-total .total-amount'  => 'color: {{VALUE}};',
                ],
            ]
        );

        
        $this->end_controls_section();
        
        
    }


    protected function render() {
        $settings = $this->get_settings_for_display();
        $preview_id = isset($settings['preview_id']) ? $settings['preview_id'] : null;
        $price_data = get_option('metal_price');
        
        // Ensure API key is sanitized
        $apiKey = sanitize_text_field(get_option('zakat_calc_api'));

        if (empty($apiKey) && current_user_can('administrator')) {
            $admin_url = admin_url('admin.php?page=wc-custom-donation-settings');
            echo '<div class="woocommerce-info">Please set up your API to get actual values. <a href="' . esc_url($admin_url) . '">Setup API Key</a></div>';
        }

        // Set default values if no price data is found
        if (!is_array($price_data) || $price_data['data']['Gold']['USD'] == '0') {
            $price_data = [
                'data' => [
                    'Gold' => ['USD' => '120.8948', 'GBP' => '90.2062', 'EUR' => '103.3055'],
                    'Silver' => ['USD' => '1.481', 'GBP' => '1.1039', 'EUR' => '1.2656']
                ],
                'updated_at' => '2025-09-28 12:45:59'
            ];
        }



        // Ensure $price_data is an array before merging
        if (!empty($preview_id) && is_array($price_data)) {
            $price_data = array_merge(['preview_id' => $preview_id], $price_data);
        }

        // Ensure $price_data is JSON-encoded properly
        $price_data = wp_json_encode($price_data);

        // Convert to lowercase
        $price_data = strtolower($price_data);

        ?>
        <div class="classic-zakat-calculator" data-settings='<?php echo esc_attr($price_data); ?>'>
            <div class="zakat-calculator-header">
                <div class="zakat-form-group">
                    <label class="zakat-label">
                        <?php echo esc_html($settings['input_title_nisab']); ?>
                    </label>
                    <div class="zakat-content">
                        <div class="zakat-radio-group">
                            <label class="zakat-radio">
                                <input type="radio" name="metal" value="silver" checked>
                                <span class="circle"></span>
                                <?php _e('Silver', 'skydonate'); ?>
                            </label>
                            <label class="zakat-radio">
                                <input type="radio" name="metal" value="gold">
                                <span class="circle"></span>
                                <?php _e('Gold', 'skydonate'); ?>
                            </label>
                        </div>
                        <div class="zakat-info">
                            <?php echo esc_html($settings['input_description_nisab']); ?>
                        </div>
                    </div>
                    <div class="zakat-toggle">
                        <button class="zakat-toggle-button" type="button">
                            <?php \Elementor\Icons_Manager::render_icon($settings['info_icon'], ['aria-hidden' => 'true']); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="zakat-calculator-assets">
                <?php
                    if (!empty($settings['assets_list'])):
                        $i = 1;
                        foreach ($settings['assets_list'] as $asset):
                            ?>
                            <div class="zakat-form-group">
                                <label for="<?php echo esc_attr($asset['assets_input_title']); ?>" class="zakat-label mt-lg-3">
                                    <?php echo esc_html($asset['assets_input_title']); ?>
                                </label>
                                <div class="zakat-content">
                                    <div class="input-group">
                                        <span class="currency-symbol"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                        <input type="number" id="<?php echo esc_attr($asset['assets_input_title']); ?>" name="assets-<?php echo $i; ?>" class="zakat-input loop-input" placeholder="<?php echo esc_attr($asset['assets_input_placeholder']); ?>">
                                    </div>
                                    <div class="zakat-info">
                                        <?php echo esc_html($asset['assets_input_description']); ?>
                                    </div>
                                </div>
                                <div class="zakat-toggle mt-2 pt-lg-2">
                                    <button class="zakat-toggle-button" type="button">
                                        <?php \Elementor\Icons_Manager::render_icon($settings['info_icon'], ['aria-hidden' => 'true']); ?>
                                    </button>
                                </div>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                    endif;
                ?>
            </div>
            <div class="zakat-calculator-liabilities">
                <?php
                    if (!empty($settings['liabilities_list'])):
                        $i = 1;
                        foreach ($settings['liabilities_list'] as $asset):
                            ?>
                            <div class="zakat-form-group">
                                <label for="<?php echo esc_attr($asset['liabilities_input_title']); ?>" class="zakat-label mt-lg-3">
                                    <?php echo esc_html($asset['liabilities_input_title']); ?>
                                </label>
                                <div class="zakat-content">
                                    <div class="input-group">
                                        <span class="currency-symbol"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                        <input type="number" id="<?php echo esc_attr($asset['liabilities_input_title']); ?>" name="liabilities-<?php echo $i; ?>" class="zakat-input loop-input" placeholder="<?php echo esc_attr($asset['liabilities_input_placeholder']); ?>">
                                    </div>
                                    <div class="zakat-info">
                                        <?php echo esc_html($asset['liabilities_input_description']); ?>
                                    </div>
                                </div>
                                <div class="zakat-toggle mt-2 pt-lg-2">
                                    <button class="zakat-toggle-button" type="button">
                                        <?php \Elementor\Icons_Manager::render_icon($settings['info_icon'], ['aria-hidden' => 'true']); ?>
                                    </button>
                                </div>
                            </div>
                            <?php
                            $i++;
                        endforeach;
                    endif;
                ?>
            </div>
            <div class="zakat-calculator-footer">
                <h3 class="zakat-total mb-0">Zakat Total <span class="mx-4">=</span><span class="total-amount"><?php echo get_woocommerce_currency_symbol(); ?><span class="total">0.00</span></span></h3>
            </div>
        </div>
        <?php
    }
}