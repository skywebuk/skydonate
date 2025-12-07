<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Skydonate_Impact_Slider extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skydonate_impact_slider';
    }

    public function get_title() {
        return __('Impact Slider', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-carousel';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['donation-impact-slider','donation-card', 'donation-button']; // Enqueue your custom styles
    }

    public function get_script_depends() {
        return ['donation-impact-slider']; // Enqueue your custom scripts
    }

    protected function register_controls() {
        // Section for slider options
        $this->start_controls_section(
            'filter_section',
            [
                'label' => __('Slider Options', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'main_title',
            [
                'label' => __('Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('How many families can you support?', 'skydonate'),
            ]
        );

        $this->add_control(
            'target_donation',
            [
                'label' => __('Target Donation', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'), // Assumes this function returns an array of [ID => Title]
                'default' => '',
                'multiple' => false,
                'label_block' => true,
            ]
        );

        // Repeater for slider items
        $repeater = new \Elementor\Repeater();


        $repeater->add_control(
            'slider_item_icon',
            [
                'label' => __('Tooltip Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-heart',
                    'library' => 'solid',
                ],
            ]
        );

        
        $repeater->add_control(
            'slider_item_title',
            [
                'label' => __('Tooltip Content', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Support 3 families', 'skydonate'),
                'placeholder' => __('Enter tooltip content', 'skydonate')
            ]
        );

        $repeater->add_control(
            'slider_item_amount',
            [
                'label' => __('Amount', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'step' => 1,
                'default' => 100,
            ]
        );

        $repeater->add_control(
            'slider_item_active',
            [
                'label' => __('Active Tooltip', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'skydonate'),
                'label_off' => __('No', 'skydonate'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        // Add the repeater control to the widget
        $this->add_control(
            'slider_items',
            [
                'label' => __('Slider Items', 'skydonate'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'title_field' => '{{{ slider_item_title }}}',
            ]
        );


        $this->add_control(
            'list_style',
            [
                'label' => __( 'List Style', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'inline' => __( 'Inline', 'skydonate' ),
                    'list' => __( 'List', 'skydonate' ),
                ],
            ]
        );


        // Donate and Support button text
        $this->add_control(
            'donate_button_text',
            [
                'label' => __('Button Text', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donate and Support', 'skydonate'),
                'placeholder' => __('Enter button text', 'skydonate'),
                'separator' => 'before'
            ]
        );
        
        // Donate and Support button icon
        $this->add_control(
            'donate_button_icon',
            [
                'label' => __('Button Icon', 'skydonate'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-donate',
                    'library' => 'solid',
                ],
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
                'separator' => 'before'
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

        // Style Section for Title
        $this->start_controls_section(
            'title_style',
            [
                'label' => __('Title Style', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Title Color Control
        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .main-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Title Typography Control
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .main-title',
            ]
        );
        // Title Alignment Control
        $this->add_control(
            'title_alignment',
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
                    'justify' => [
                        'title' => __('Justify', 'skydonate'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .main-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        // Title Margin Control
        $this->add_responsive_control(
            'title_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .main-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title Padding Control
        $this->add_responsive_control(
            'title_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .main-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Title Text Shadow Control
        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_text_shadow',
                'label' => __('Text Shadow', 'skydonate'),
                'selector' => '{{WRAPPER}} .main-title',
            ]
        );

        $this->end_controls_section();






        // Style Section for Slider Item
        $this->start_controls_section(
            'slider_item_style',
            [
                'label' => __('Slide Contents', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        // Button Width
        $this->add_responsive_control(
            'slider_width',
            [
                'label' => __('Width', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'], // Added vw here
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 3000,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}  .donation-impact-slider' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        // Tabs for Slide Content Style: Normal and Hover states
        $this->start_controls_tabs('slide_content_style_tabs');

        // Dots Tab
        $this->start_controls_tab(
            'slide_content_dots_tab',
            [
                'label' => __('Dots', 'skydonate'),
            ]
        );


        $this->add_control(
            'dot_color',
            [
                'label' => __('Dot Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-impact-slider .slider-item .dot' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        
        $this->add_responsive_control(
            'dots_size',
            [
                'label' => __('Dots Size', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'], // Added vw here
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 3000,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-impact-slider .slider-item .dot' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab(); // End Dots Tab

        // Tooltip Tab
        $this->start_controls_tab(
            'slide_content_tooltip_tab',
            [
                'label' => __('Tooltip', 'skydonate'),
            ]
        );


        // Title Color
        $this->add_control(
            'tolltip_color',
            [
                'label' => __('Tip Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .toptip' => 'color: {{VALUE}};',
                ],
            ]
        );


        // Title Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tolltip_typography',
                'label' => __('Tip Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .toptip',
            ]
        );
        $this->add_control(
            'tolltip_border_color',
            [
                'label' => __('Tip Border Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .toptip,{{WRAPPER}} .toptip:after' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'tooltip_icon_size',
            [
                'label' => __('Icon Size', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'vw'], // Includes vw as a unit
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 3000,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                    'vw' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-impact-slider .slider-item .toptip .icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .donation-impact-slider .slider-item .toptip .icon' => 'font-size: {{SIZE}}{{UNIT}};'
                ],
            ]
        );
        
        // Title Color
        $this->add_control(
            'tooltip_icon_color',
            [
                'label' => __('Icon Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',  // Optional default color
                'selectors' => [
                    '{{WRAPPER}} .toptip i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .toptip svg' => 'fill: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_tab(); // End Tooltip Tab



        // Price Tab
        $this->start_controls_tab(
            'slide_content_price_tab',
            [
                'label' => __('Price', 'skydonate'),
            ]
        );

        // Price Color
        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .price-amount' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Price Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'label' => __('Price Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .price-amount',
            ]
        );

        
        $this->end_controls_tab(); // End Price Tab
        $this->end_controls_tabs(); // End Slide Content Style Tabs
        $this->end_controls_section();


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
                    '{{WRAPPER}} .donation-button-wrapper' => 'text-align: {{VALUE}};',
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

    }


    protected function render() {
        $settings = $this->get_settings_for_display();
        $widget_id = $this->get_id();
        $list_style = $settings['list_style'];
        $heading_text = $settings['main_title'];
        echo '<h2 class="main-title">' . esc_html($heading_text) . '</h2>';
    
        $secure_donation_text = !empty($settings['secure_donation_text']) ? $settings['secure_donation_text'] : __('Secure Donation', 'skydonate');
        $secure_donation_icon = !empty($settings['secure_donation_icon']) ? $settings['secure_donation_icon'] : null;
        $show_secure_donation = !empty($settings['show_secure_donation']) && $settings['show_secure_donation'] === 'yes';
    
        $donate_button_text = !empty($settings['donate_button_text']) ? $settings['donate_button_text'] : __('Donate and Support', 'skydonate');
        $donate_button_icon = !empty($settings['donate_button_icon']) ? $settings['donate_button_icon'] : null;

        $this->add_render_attribute( 'wrapper_attributes', 'class', ['donation-button-wrapper mt-4'] );
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

        if (!empty($settings['target_donation'])) {
            if (!empty($settings['slider_items'])) {
                echo '<div class="donation-impact-slider ' . esc_attr($list_style) . '">';
                
                // Flag to identify if an active item has been found
                $active_found = false;
                
                foreach ($settings['slider_items'] as $index => $item) {
                    // Check if this is the active item
                    $is_active = !$active_found && !empty($item['slider_item_active']) && $item['slider_item_active'] === 'yes';
                    
                    if ($is_active) {
                        $active_found = true; // Mark as active item found
                    }
                    
                    $unique = 'slider_item_id_' . $index . '_' . uniqid();
                    ?>
                    <div class="slider-item">
                        <input type="radio" id="<?php echo esc_attr($unique); ?>" <?php echo $is_active ? 'checked' : ''; ?> name="slider_item_<?php echo esc_attr($widget_id); ?>" value="<?php echo esc_attr(!empty($item['slider_item_amount']) ? $item['slider_item_amount'] : '0'); ?>" />
                        <!-- Display title if available -->
                        <label for="<?php echo esc_attr($unique); ?>">
                            <?php if (!empty($item['slider_item_title']) || !empty($item['slider_item_icon'])): ?>
                                <div class="toptip">
                                <?php
                                    if (!empty($item['slider_item_icon'])) {
                                        echo '<span class="icon">';
                                        \Elementor\Icons_Manager::render_icon($item['slider_item_icon'], ['aria-hidden' => 'true']);
                                        echo '</span>';
                                    }
                                ?>
                                <?php echo wp_kses_post($item['slider_item_title']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="dot"></div>
                            <!-- Display amount, with a default of 0 if it's empty -->
                            <div class="price-amount">
                                <?php echo esc_html(Skydonate_Functions::Get_Currency_Symbol() . (!empty($item['slider_item_amount']) ? $item['slider_item_amount'] : '0')); ?>
                            </div>
                        </label>
                    </div>
                    <?php
                }
                
                echo '</div>'; // End .donation-impact-slider
                
                if (!empty($donate_button_text) || !empty($donate_button_icon)) {
                    echo '<div '.$this->get_render_attribute_string( "wrapper_attributes" ).'>';
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
                    echo '</div>';
                }

        
                echo '<div class="quick-modal">';
                echo '<div class="quick-modal-overlay"></div>';
                echo '<div class="quick-modal-body">';
                echo '<span class="quick-modal-close"></span>'; // Close button
                echo '<div class="quick-modal-content">';
                if ($show_secure_donation) {
                    echo '<div class="secure-donation">';
                    if (!empty($secure_donation_icon)) {
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
                    'id'             => $settings['target_donation'],
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
            }
        } else {
            echo "<div class='woocommerce-info'>" . esc_html__('Do not select any target donation.', 'skydonate') . "</div>";
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