<?php
if (!defined('ABSPATH')) exit;

class SkyWeb_Extra_Donation extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_extra_donation';
    }

    public function get_title() {
        return __('Extra Donation', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-checkbox';
    }

    public function get_categories() {
        return ['skyweb_donation'];
    }

    public function get_script_depends() {
        return ['extra-donation'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'donation_title',
            [
                'label' => __('Donation Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
            ]
        );

        $this->end_controls_section();
        
        // ----------------------------
        // Switch Style Section with Tabs
        // ----------------------------
        $this->start_controls_section(
            'switch_style_section',
            [
                'label' => __('Switch', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Tabs: Normal / Active
        $this->start_controls_tabs('switch_style_tabs');

        // --- Normal Tab ---
        $this->start_controls_tab(
            'switch_normal_tab',
            [
                'label' => __('Normal', 'skydonate'),
            ]
        );

        $this->add_control(
            'switch_normal_bg',
            [
                'label' => __('Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sky-smart-switch .switch' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'switch_normal_knob',
            [
                'label' => __('Knob Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sky-smart-switch .switch::after' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // --- Active Tab ---
        $this->start_controls_tab(
            'switch_active_tab',
            [
                'label' => __('Active', 'skydonate'),
            ]
        );

        $this->add_control(
            'switch_active_bg',
            [
                'label' => __('Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sky-smart-switch input:checked + .switch' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'switch_active_knob',
            [
                'label' => __('Knob Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sky-smart-switch input:checked + .switch::after' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();


        // ----------------------------
        // Label Text Style Section
        // ----------------------------
        $this->start_controls_section(
            'label_style_section',
            [
                'label' => __('Label Text', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'label' => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .sky-smart-switch',
            ]
        );

        // Color
        $this->add_control(
            'label_color',
            [
                'label' => __('Text Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sky-smart-switch' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->end_controls_section();

    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $title = esc_html($settings['donation_title']);

        echo '<div class="skyweb-extra-donation-widget">';

        if( !empty($title) ){
            echo '<h3>' . $title . '</h3>';
        }

        // Display donation items if Skyweb_Extra_Donation exists
        if (class_exists('Skyweb_Extra_Donation_Settings')) {
            $donation_class = new Skyweb_Extra_Donation_Settings();
            echo $donation_class->render_donation_shortcode();
        }

        echo '</div>';
    }

}
