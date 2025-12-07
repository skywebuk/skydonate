<?php
if (!defined('ABSPATH')) exit;


use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;

class Skydonate_Quick_Donation extends Widget_Base {

    public function get_name() {
        return 'skyweb_quick_donation';
    }

    public function get_title() {
        return __('Quick Donation', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-cart';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['quick-donation'];
    }

    public function get_script_depends() {
        return ['quick-donation'];
    }
    
    public function get_keywords() {
        return ['donation', 'quick donation', 'charity', 'fund', 'WooCommerce', 'skydonate'];
    }

    protected function register_controls() {
        // FREQUENCY SECTION
        $this->start_controls_section(
            'frequency_section',
            [
                'label' => __( 'Frequency Dropdown', 'quick-donate-woo' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $rep_freq = new \Elementor\Repeater();

        $rep_freq->add_control(
            'frequency_option',
            [
                'label'   => __( 'Frequency Option', 'quick-donate-woo' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'monthly',
                'options' => [
                    'Once'   => __( 'Once', 'quick-donate-woo' ),
                    'Daily'  => __( 'Daily', 'quick-donate-woo' ),
                    'Weekly' => __( 'Weekly', 'quick-donate-woo' ),
                    'Monthly'=> __( 'Monthly', 'quick-donate-woo' ),
                    'Yearly' => __( 'Yearly', 'quick-donate-woo' ),
                ],
            ]
        );

        // Default frequency items
        $this->add_control(
            'frequency_options',
            [
                'label'       => __( 'Frequency Items', 'quick-donate-woo' ),
                'type'        => \Elementor\Controls_Manager::REPEATER,
                'fields'      => $rep_freq->get_controls(),
                'default'     => [
                    [ 'frequency_option' => 'Once' ],
                    [ 'frequency_option' => 'Daily' ],
                    [ 'frequency_option' => 'Weekly' ],
                    [ 'frequency_option' => 'Monthly' ],
                    [ 'frequency_option' => 'Yearly' ],
                ],
                'title_field' => '{{{ frequency_option.charAt(0).toUpperCase() + frequency_option.slice(1) }}}',
            ]
        );

        // Default frequency
        $this->add_control(
            'default_frequency',
            [
                'label'   => __( 'Default Frequency', 'quick-donate-woo' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'default' => 'Monthly',
                'options' => [
                    'Once'   => __( 'Once', 'quick-donate-woo' ),
                    'Weekly' => __( 'Weekly', 'quick-donate-woo' ),
                    'Monthly'=> __( 'Monthly', 'quick-donate-woo' ),
                    'Yearly' => __( 'Yearly', 'quick-donate-woo' ),
                ],
            ]
        );

        $this->end_controls_section();


        // AMOUNT SECTION
        $this->start_controls_section(
            'amount_section',
            [
                'label' => __( 'Amount Dropdown', 'quick-donate-woo' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );
        $rep_amount = new Repeater();
        $rep_amount->add_control(
            'amount_option',
            [
                'label'   => __( 'Amount', 'quick-donate-woo' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '10',
            ]
        );
        $this->add_control(
            'amount_options',
            [
                'label'       => __( 'Amount Items', 'quick-donate-woo' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $rep_amount->get_controls(),
                'default'     => [
                    [ 'amount_option' => '10' ],
                    [ 'amount_option' => '20' ],
                    [ 'amount_option' => '50' ],
                    [ 'amount_option' => 'Custom' ],
                ],
                'title_field' => '{{{ amount_option }}}',
            ]
        );
        $this->add_control(
            'default_amount',
            [
                'label'   => __( 'Default Amount', 'quick-donate-woo' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
        $this->end_controls_section();

        // FUND SECTION
        $this->start_controls_section(
            'fund_category_section',
            [
                'label' => __( 'Fund Category Dropdown', 'quick-donate-woo' ),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $rep_fund = new Repeater();
        $rep_fund->add_control(
            'fund_option',
            [
                'label'   => __( 'Fund Category', 'quick-donate-woo' ),
                'type'    => Controls_Manager::TEXT,
                'default' => 'General Fund',
            ]
        );
        $rep_fund->add_control(
            'fund_product_id',
            [
                'label'   => __( 'Donation', 'quick-donate-woo' ),
                'type'    => Controls_Manager::SELECT,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'),
                'default' => 0,
            ]
        );
        $this->add_control(
            'fund_options',
            [
                'label'       => __( 'Fund Items', 'quick-donate-woo' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $rep_fund->get_controls(),
                'default'     => [
                    [ 'fund_option' => 'General Fund', 'fund_product_id' => 0 ],
                    [ 'fund_option' => 'Special Fund', 'fund_product_id' => 0 ],
                ],
                'title_field' => '{{{ fund_option }}}',
            ]
        );
        $this->add_control(
            'default_fund',
            [
                'label'   => __( 'Default Fund', 'quick-donate-woo' ),
                'type'    => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
        $this->end_controls_section();

        // BUTTON SECTION
        $this->start_controls_section(
            'basket_button_section',
            [
                'label' => __( 'Button Settings', 'quick-donate-woo' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Left Icon
        $this->add_control(
            'button_left_icon',
            [
                'label'   => __( 'Left Icon', 'quick-donate-woo' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
            ]
        );

        // Button Text
        $this->add_control(
            'button_text',
            [
                'label'   => __( 'Button Text', 'quick-donate-woo' ),
                'type'    => \Elementor\Controls_Manager::TEXT,
                'default' => __( 'Donate and Support', 'quick-donate-woo' ),
            ]
        );

        // Right Icon
        $this->add_control(
            'button_right_icon',
            [
                'label'   => __( 'Right Icon', 'quick-donate-woo' ),
                'type'    => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value'   => 'fas fa-arrow-right',
                    'library' => 'solid',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'fields_style_section',
            [
                'label' => __( 'Fields Style', 'quick-donate-woo' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'fields_typography',
                'selector' => '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control, {{WRAPPER}} .skydonate-quick-donation-widget .currency',
            ]
        );
        $this->add_control(
            'fields_text_color',
            [
                'label'     => __( 'Text Colour', 'quick-donate-woo' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control, {{WRAPPER}} .skydonate-quick-donation-widget .currency' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'fields_background_color',
            [
                'label'     => __( 'Background Colour', 'quick-donate-woo' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'fields_border',
                'selector' => '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control',
            ]
        );
        $this->add_responsive_control(
            'fields_border_radius',
            [
                'label'      => __( 'Border Radius', 'quick-donate-woo' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'fields_height',
            [
                'label'      => __( 'Height', 'quick-donate-woo' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'default'    => [
                    'size' => 55,
                    'unit' => 'px',
                ],
                'selectors'  => [
                    '{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .qd-control,{{WRAPPER}} .skydonate-quick-donation-widget .qd-field .form-submit-button' => 'height: {{SIZE}}{{UNIT}};'
                ],
            ]
        );
        $this->end_controls_section();

        // BUTTON STYLE SECTION
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __( 'Button Style', 'quick-donate-woo' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Button typography
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .form-submit-button',
            ]
        );

        // Button text color
        $this->add_control(
            'button_text_color',
            [
                'label'     => __( 'Text Colour', 'quick-donate-woo' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .form-submit-button' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Button background
        $this->add_control(
            'button_bg_color',
            [
                'label'     => __( 'Background Colour', 'quick-donate-woo' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .form-submit-button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Button padding
        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => __( 'Padding', 'quick-donate-woo' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .form-submit-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Button border
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'button_border',
                'selector' => '{{WRAPPER}} .form-submit-button',
            ]
        );

        // Button border radius
        $this->add_responsive_control(
            'button_border_radius',
            [
                'label'      => __( 'Border Radius', 'quick-donate-woo' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .form-submit-button' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        $atts = array();
        $settings = $this->get_settings_for_display();
        $currency_symbol = get_woocommerce_currency_symbol();

        $button_left_icon  = ! empty( $settings['button_left_icon'] ) 
            ? Skydonate_Icon_Manager::render_icon( $settings['button_left_icon'], ['aria-hidden' => 'true'] ) 
            : '';

        $button_text = $settings['button_text'] ?? 'Donate and Support';

        $button_right_icon = ! empty( $settings['button_right_icon'] ) 
            ? Skydonate_Icon_Manager::render_icon( $settings['button_right_icon'], ['aria-hidden' => 'true'] ) 
            : '';
            

        echo '<div class="skydonate-quick-donation-widget">';

        // ======================
        // FREQUENCY DROPDOWN
        // ======================
        $frequency_options = $settings['frequency_options'] ?? [];
        $default_frequency = $settings['default_frequency'] ?: 'Monthly';

        echo '<div class="qd-field qd-frequency-wrap">';
        echo '<select class="qd-frequency qd-control">';
        foreach ( $frequency_options as $freq ) {
            $selected = ( $freq['frequency_option'] === $default_frequency ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $freq['frequency_option'] ) . '" ' . $selected . '>';
            echo esc_html( $freq['frequency_option'] );
            echo '</option>';
        }
        echo '</select>';
        echo '</div>';

        // ======================
        // AMOUNT DROPDOWN
        // ======================
        $amount_options = $settings['amount_options'] ?? [];
        $default_amount = $settings['default_amount'] ?: '';

        echo '<div class="qd-field qd-amount-wrap">';
        echo '<select class="qd-amount qd-control">';
        foreach ( $amount_options as $amount ) {
            $selected = ( $amount['amount_option'] === $default_amount ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $amount['amount_option'] ) . '" ' . $selected . '>';
            if($amount['amount_option'] != 'Custom'){
                echo '<span class="currency-symbol">' . esc_html( $currency_symbol ) . '</span>';
            }
            echo esc_html( $amount['amount_option'] );
            echo '</option>';
        }
        echo '</select>';

        // Custom amount input (hidden by default, shown with JS)
        echo '<div class="qd-custom-amount" style="display:none;">';
        echo '<span class="currency currency-symbol">' . esc_html( $currency_symbol ) . '</span>';
        echo '<input type="number" placeholder="'. __('Custom Amount','skydonate') .'" class="qd-custom-amount-input qd-control" min="1" step="1" />';
        echo '</div>';

        echo '</div>';

        // ======================
        // FUND DROPDOWN
        // ======================
        $fund_options = $settings['fund_options'] ?? [];
        $default_fund = $settings['default_fund'] ?: '';

        echo '<div class="qd-field qd-fund-wrap">';
        echo '<select class="qd-control qd-fund">';
        foreach ( $fund_options as $fund ) {
            $selected = ( $fund['fund_option'] === $default_fund ) ? 'selected' : '';
            echo '<option value="' . esc_attr( $fund['fund_option'] ) . '" ' . $selected . ' data-product-id="' . esc_attr( $fund['fund_product_id'] ) . '">';
            echo esc_html( $fund['fund_option'] );
            echo '</option>';
        }
        echo '</select>';
        echo '</div>';

        // ======================
        // BUTTON
        // ======================
        echo '<div class="qd-field qd-button-wrap">';
        $atts["before_icon"] = $button_left_icon;
        $atts["button_text"] = $button_text;
        $atts["after_icon"] = $button_right_icon;
        Skydonate_Functions::skydonate_submit_button($atts);
        echo '</div>';


        echo '</div>'; // .skydonate-quick-donation-widget
    }


}
