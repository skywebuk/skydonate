<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class SkyWeb_Gift_Aid_Toggle extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_gift_aid_toggle';
    }

    public function get_title() {
        return __( 'Gift Aid Toggle', 'skydonate' );
    }

    public function get_icon() {
        return 'eicon-checkbox';
    }

    public function get_categories() {
        return [ 'skyweb_donation' ];
    }

    public function get_script_depends() {
        return ['gift-aid-toggle'];
    }

    protected function register_controls() {

        $this->start_controls_section(
            'gift_aid_section',
            [
                'label' => __( 'Gift Aid Toggle', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Toggle label text
        $this->add_control(
            'label_text',
            [
                'label'       => __( 'Toggle Label Text', 'skydonate' ),
                'type'        => \Elementor\Controls_Manager::TEXT,
                'default'     => __( 'Boost your £10 gift to £12.50 with Gift Aid', 'skydonate' ),
                'placeholder' => __( 'Enter label text', 'skydonate' ),
            ]
        );

        // Gift Aid note
        $this->add_control(
            'note_text',
            [
                'label'   => __( 'Gift Aid Note', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __( 'Applies to this donation and any made in the future and in the past four years. I am a UK taxpayer and I understand that if I pay less Income and/or Capital Gains Tax than the amount of Gift Aid claimed on all my donations in the relevant tax year, it is my responsibility to pay any difference. <a href="https://www.gov.uk/donating-to-charity/gift-aid" target="_blank">Read more about Gift Aid</a>.', 'skydonate' ),
            ]
        );

        // Image upload
        $this->add_control(
            'gift_aid_image',
            [
                'label'   => __( 'Gift Aid Image', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => SKYWEB_DONATION_SYSTEM_PUBLIC_ASSETS . '/img/gift-aid-uk-logo.svg',
                ],
            ]
        );

        $this->end_controls_section();

        // Checkbox Style Controls
        $this->start_controls_section(
            'checkbox_style_section',
            [
                'label' => __( 'Checkbox Label', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'checkbox_typography',
                'selector' => '{{WRAPPER}} .skyweb-gift-aid-toggle .checkbox',
            ]
        );

        // Text Color
        $this->add_control(
            'checkbox_text_color',
            [
                'label'     => __( 'Text Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .checkbox' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'checkbox_margin',
            [
                'label'      => __( 'Margin', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .checkbox' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // Gift Aid Image Style Controls
        $this->start_controls_section(
            'gift_aid_image_style_section',
            [
                'label' => __( 'Gift Aid Image', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Image Width
        $this->add_responsive_control(
            'gift_aid_image_width',
            [
                'label'      => __( 'Width', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 500 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-image img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Image Height
        $this->add_responsive_control(
            'gift_aid_image_height',
            [
                'label'      => __( 'Height', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 500 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-image img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Float
        $this->add_control(
            'gift_aid_image_float',
            [
                'label'   => __( 'Float', 'skydonate' ),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'none'       => __( 'None', 'skydonate' ),
                    'left'   => __( 'Left', 'skydonate' ),
                    'right'  => __( 'Right', 'skydonate' ),
                ],
                'default'   => 'right',
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-image' => 'float: {{VALUE}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'gift_aid_image_margin',
            [
                'label'      => __( 'Margin', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-image' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Padding
        $this->add_responsive_control(
            'gift_aid_image_padding',
            [
                'label'      => __( 'Padding', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Gift Aid Note Style Controls
        $this->start_controls_section(
            'gift_aid_note_style_section',
            [
                'label' => __( 'Gift Aid Note', 'skydonate' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'gift_aid_note_typography',
                'selector' => '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note',
            ]
        );

        // Text Color
        $this->add_control(
            'gift_aid_note_text_color',
            [
                'label'     => __( 'Text Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Link Color
        $this->add_control(
            'gift_aid_note_link_color',
            [
                'label'     => __( 'Link Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note a' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Link Hover Color
        $this->add_control(
            'gift_aid_note_link_hover_color',
            [
                'label'     => __( 'Link Hover Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );


        // Width
        $this->add_responsive_control(
            'gift_aid_note_width',
            [
                'label'      => __( 'Width', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 1200 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Background Color
        $this->add_control(
            'gift_aid_note_bg_color',
            [
                'label'     => __( 'Background Color', 'skydonate' ),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Margin
        $this->add_responsive_control(
            'gift_aid_note_margin',
            [
                'label'      => __( 'Margin', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Padding
        $this->add_responsive_control(
            'gift_aid_note_padding',
            [
                'label'      => __( 'Padding', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name'     => 'gift_aid_note_border',
                'selector' => '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note',
            ]
        );

        // Border Radius
        $this->add_responsive_control(
            'gift_aid_note_border_radius',
            [
                'label'      => __( 'Border Radius', 'skydonate' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em' ],
                'selectors'  => [
                    '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Box Shadow
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'gift_aid_note_box_shadow',
                'selector' => '{{WRAPPER}} .skyweb-gift-aid-toggle .gift-aid-note',
            ]
        );

        $this->end_controls_section();


    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $label    = $settings['label_text'] ?? '';
        $note     = $settings['note_text'] ?? '';
        $image    = $settings['gift_aid_image']['url'] ?? '';
        // Get user preference
        $gift_aid_checked = 'checked';
        if ($user_id = get_current_user_id()) {
            $gift_aid_checked = get_user_meta($user_id, 'gift_aid_status', true) === 'no' ? '' : 'checked';
        }
        ?>
        <div class="skyweb-gift-aid-toggle">
            <?php if ($image): ?>
                <div class="gift-aid-image">
                    <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($label); ?>" />
                </div>
            <?php endif; ?>
            <?php if ($label): ?>
                <label class="checkbox sky-smart-switch">
                    <input type="checkbox" class="input-checkbox extra-donation-checkbox" name="gift_aid_it" id="gift_aid_it" data-value="<?php echo $gift_aid_checked; ?>" <?php echo $gift_aid_checked; ?>>
                    <span class="switch"></span>
                    <?php echo wp_kses_post($label); ?>
                </label>
            <?php endif; ?>
            <?php if ($note): ?>
                <p class="gift-aid-note"><?php echo wp_kses_post($note); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }



}
