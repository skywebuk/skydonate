<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Progress_Addon_2 extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_progress_2';
    }

    public function get_title() {
        return __('Donation Progress 2', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-counter';
    }

    public function get_categories() {
        return ['skyweb_donation'];
    }

    public function get_style_depends() {
        return ['donation-progress'];
    }
    
    public function get_script_depends() {
        return ['donation-progress'];
    }

    protected function register_controls() {
        // Section for progress bar settings
        $this->start_controls_section(
            'progress_bar_section',
            [
                'label' => __('Progress Bar Settings', 'skydonate'),
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
        
        // Product Title Filter
        $this->add_control(
            'filter_product_title',
            [
                'label' => __('Product Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skyweb_Donation_Functions::Get_Title('product', 'ids'),
                'default' => '',
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );
        
        // Category Filter
        $this->add_control(
            'filter_category',
            [
                'label' => __('Category', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skyweb_Donation_Functions::Get_Taxonomies('product_cat'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // Tag Filter
        $this->add_control(
            'filter_tag',
            [
                'label' => __('Tag', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skyweb_Donation_Functions::Get_Taxonomies('product_tag'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        // Progress style selector
        $this->add_control(
            'progress_style',
            [
                'label' => __('Progress Style', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'line' => __('Line', 'skydonate'),
                    'circle' => __('Circle', 'skydonate'),
                ],
                'default' => 'line',
                'label_block' => true,
            ]
        );

        $this->add_control(
            'offline_donation',
            [
                'label'        => __( 'Offline Donation', 'skydonate' ),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'skydonate' ),
                'label_off'    => __( 'No', 'skydonate' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        // Progress duration
        $this->add_control(
            'progress_duration',
            [
                'label' => __('Progress Duration', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1500, // ms
                'min' => 1,
                'step' => 1,
                'max' => 9999,
                'description' => __('Set the progress duration in milliseconds.', 'skydonate'),
            ]
        );

        // No Donations Message
        $this->add_control(
            'no_donations_message',
            [
                'label' => __('No Donations Message', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No donations received yet.', 'skydonate'),
            ]
        );

        // Raised Label
        $this->add_control(
            'raised_label',
            [
                'label' => __('Raised Label', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Raised', 'skydonate'),
            ]
        );
        
        // Target Label
        $this->add_control(
            'target_label',
            [
                'label' => __('Target Label', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Target', 'skydonate'),
            ]
        );
        
        // Donations Label
        $this->add_control(
            'donations_label',
            [
                'label' => __('Donations Label', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Donations', 'skydonate'),
            ]
        );

        // Override Target Goal
        $this->add_control(
            'override_target_goal',
            [
                'label' => __('Override Target Goal', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1,
                'default' => '',
                'description' => __('If not empty, this overrides the product goal when filters are enabled.', 'skydonate'),
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );


        // 🔹 Start Date Control
        $this->add_control(
            'donation_start_date',
            [
                'label' => __('Donation Start Date', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'enableTime' => false, // date only
                ],
                'default' => '',
                'description' => __('Show donations starting from this date. Leave empty for no limit.', 'skydonate'),
            ]
        );

        // 🔹 End Date Control
        $this->add_control(
            'donation_end_date',
            [
                'label' => __('Donation End Date', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DATE_TIME,
                'picker_options' => [
                    'enableTime' => false, // date only
                ],
                'default' => '',
                'description' => __('Show donations up to this date. Leave empty for no limit.', 'skydonate'),
            ]
        );

        $this->end_controls_section();

        /*
         * The style sections below remain unchanged. They define how the widget
         * looks but don't affect the logic for calculating the target goal.
         */
        // Style: Heading
        $this->start_controls_section(
            'progress_heading_style',
            [
                'label' => __('Heading', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'progress_heading_color',
            [
                'label' => __('Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .raised-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'progress_heading_typo',
                'selector' => '{{WRAPPER}} .donation-progress.layout2 .raised-title',
            ]
        );

        $this->add_responsive_control(
            'progress_heading_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .raised-title' =>
                        'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();

        // Style: Content
        $this->start_controls_section(
            'content_style',
            [
                'label' => __('Content', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_text_color',
            [
                'label' => __('Text Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .target-title, {{WRAPPER}} .donation-progress.layout2 .target-title *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography',
                'label' => __('Typography', 'skydonate'),
                'selector' => '{{WRAPPER}} .donation-progress.layout2 .target-title',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => __('Padding', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .target-title' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .target-title' =>
                        'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style: Progress Bar
        $this->start_controls_section(
            'progress_bar_style',
            [
                'label' => __('Progress Bar', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'progress_bar_color',
            [
                'label' => __('Progress Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .progress-bar-background .progress-bar' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar .circle' => 'stroke: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'progress_bar_bg_color',
            [
                'label' => __('Bar Background Color', 'skydonate'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress .progress-bar-background' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar .circle-bg' => 'stroke: {{VALUE}}',
                ],
            ]
        );

        $this->add_control(
            'float_position',
            [
                'label' => __('Position', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'justify-content:space-between;align-items:center;',
                'options' => [
                    'flex-direction:column-reverse;' => __('Top', 'skydonate'),
                    'justify-content:space-between;align-items:center;' => __('Right', 'skydonate'),
                    'flex-direction:column;' => __('Bottom', 'skydonate'),
                    'flex-direction:row-reverse;align-items:center;justify-content:flex-end;' => __('Left', 'skydonate'),
                ],
                'label_block' => true,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .progress-bar-row' => '{{VALUE}}',
                ],
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_circle_size',
            [
                'label' => __('Size', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 999,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar, {{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar svg' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_circle_bg_stroke_width',
            [
                'label' => __('Background Stroke Width', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar svg .circle-bg' => 'stroke-width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'progress_circle_stroke_width',
            [
                'label' => __('Stroke Width', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar svg .circle' => 'stroke-width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'progress_bar_height',
            [
                'label' => __('Height', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2 .progress-bar-background .progress-bar' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'progress_style' => 'line',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_bar_radius',
            [
                'label' => __('Border Radius', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress .progress-bar-background' =>
                        'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'progress_style' => 'line',
                ],
            ]
        );

        $this->add_responsive_control(
            'progress_circle_margin',
            [
                'label' => __('Margin', 'skydonate'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar' =>
                        'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ]
            ]
        );

		$this->add_control(
			'percentage_heading',
			[
				'label' => esc_html__( 'Percentage', 'skydonate' ),
				'type' => \Elementor\Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'percentage_typo',
                'selector' => '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar .percent',
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );
        
        $this->add_control(
            'percentage_color',
            [
                'label' => __( 'Color', 'skydonate' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .donation-progress.layout2.circle .circle-progress-bar .percent' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'progress_style' => 'circle',
                ],
            ]
        );

        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        $enable_filters = $settings['enable_filters'] === 'yes';
        $filter_product_title = (array) $settings['filter_product_title'];
        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $override_target_goal = !empty($settings['override_target_goal']) ? floatval($settings['override_target_goal']) : 0;
        $no_donations_message = esc_html($settings['no_donations_message']);


        $start_date = !empty($settings['donation_start_date']) ? $settings['donation_start_date'] : null;
        $end_date   = !empty($settings['donation_end_date']) ? $settings['donation_end_date'] : null;

        // If on a product page and filters are disabled, default to that product
        if (is_product() && !$enable_filters) {
            $filter_product_title = [get_queried_object_id()];
        }
        
        // If no products, categories, or tags selected, show fallback message
        if (!$filter_product_title && !$filter_category && !$filter_tag) {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
            return;
        }
        
        // Get combined product IDs
        $product_ids = array_merge(
            $filter_product_title,
            Skyweb_Donation_Functions::get_product_ids_by_multiple_taxonomies($filter_category, 'product_cat'),
            Skyweb_Donation_Functions::get_product_ids_by_multiple_taxonomies($filter_tag, 'product_tag')
        );
        $product_ids = array_unique($product_ids);
        
        $total_raised = 0;
        $target_sales_sum = 0;
        $donation_count = 0;

        // If override is set AND filters are enabled, use override_target_goal
        if ($enable_filters && $override_target_goal > 0) {
            $target_sales_sum = $override_target_goal;
            foreach ($product_ids as $product_id) {
                $offline_donation = floatval(get_post_meta($product_id, '_offline_donation', true));
                $product_sales      = $this->get_total_donation_sales_amount_by_product_id( $product_id, $start_date, $end_date );
                $total_raised += $product_sales['amount'];
                if ( $settings['offline_donation'] === 'yes' ){
                    $total_raised += $offline_donation;
                }
                $donation_count += $product_sales['count'];
            }
        } else {
            // Use each product's _target_sales_goal
            foreach ($product_ids as $product_id) {
                $target_sales = floatval(get_post_meta($product_id, '_target_sales_goal', true));
                $offline_donation = floatval(get_post_meta($product_id, '_offline_donation', true));
                if ($target_sales > 0) {
                    $target_sales_sum += $target_sales;
                }
                $product_sales = $this->get_total_donation_sales_amount_by_product_id( $product_id, $start_date, $end_date );
                $total_raised += $product_sales['amount'];
                if ( $settings['offline_donation'] === 'yes' ){
                    $total_raised += $offline_donation;
                }
                $donation_count += $product_sales['count'];
            }
        }

        if ($target_sales_sum <= 0) {
            echo '<div class="woocommerce-info">' . esc_html__('Donation goal target not found.', 'skydonate') . '</div>';
            return;
        }

        // Add elementor wrapper classes
        $this->add_render_attribute('wrapper_attributes', 'class', 'donation-progress layout2');

        // Build data settings for front-end
        $bar_settings = [
            'raised'  => $total_raised,
            'target'  => $target_sales_sum,
            'duration'=> $settings['progress_duration'],
            'symbol'  => Skyweb_Donation_Functions::Get_Currency_Symbol(),
        ];
        $this->add_render_attribute('wrapper_attributes', 'data-settings', wp_json_encode($bar_settings));
        
        // If style == 'line'
        if ($settings['progress_style'] === 'line') {
            $this->add_render_attribute('wrapper_attributes', 'class', 'line');
            echo '<div ' . $this->get_render_attribute_string('wrapper_attributes') . '>';
                // Title
                echo '<h3 class="raised-title">';
                echo sprintf(
                    __('<span class="raised">%s</span> %s', 'skydonate'),
                    wc_price(0),
                    $settings['raised_label']
                );
                echo '</h3>';

                // Subtitle with target, count, percent
                echo '<div class="target-title">';
                echo sprintf(
                    __('<span class="goal">%s</span> %s <small class="fa-solid fa-circle"></small> <span class="count">%d</span> %s <span class="percent">%d%%</span>', 'skydonate'),
                    Skyweb_Donation_Functions::Get_Currency_Symbol() . Skyweb_Donation_Functions::format_large_number($target_sales_sum),
                    $settings['target_label'],
                    $donation_count,
                    $settings['donations_label'],
                    0 // initial %
                );
                echo '</div>';

                echo '<div class="progress-bar-background"><div class="progress-bar"></div></div>';
            echo '</div>';
        
        // If style == 'circle'
        } else {
            $this->add_render_attribute('wrapper_attributes', 'class', ['circle']);
            echo '<div ' . $this->get_render_attribute_string('wrapper_attributes') . '>';

                // Container
                echo '<div class="progress-bar-row">';
                    // Left side
                    echo '<div class="left-content">';
                        echo '<h3 class="raised-title">';
                        echo sprintf(
                            __('<span class="raised">%s</span> %s', 'skydonate'),
                            wc_price(0),
                            $settings['raised_label']
                        );
                        echo '</h3>';

                        echo '<div class="target-title">';
                        echo sprintf(
                            __('<span class="goal">%s</span> %s <small class="fa-solid fa-circle"></small> <span class="count">%d</span> %s', 'skydonate'),
                            Skyweb_Donation_Functions::Get_Currency_Symbol() . Skyweb_Donation_Functions::format_large_number($target_sales_sum),
                            $settings['target_label'],
                            $donation_count,
                            $settings['donations_label']
                        );
                        echo '</div>';
                    echo '</div>';

                    // Circle
                    echo '<div class="circle-progress-bar">';
                        echo '<svg viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">';
                        echo '<circle class="circle-bg" cx="60" cy="60" r="50"></circle>';
                        // stroke-dashoffset for the circle is set via JS
                        echo '<circle class="circle" cx="60" cy="60" r="50" style="stroke-dashoffset: 314px;"></circle>';
                        echo '</svg>';
                        // Display 0% initially
                        echo '<span class="percent">0</span>';
                    echo '</div>';
                echo '</div>'; // row
            echo '</div>';
        }
    }

    
    public function get_total_donation_sales_amount_by_product_id($product_id, $start_date = null, $end_date = null) {
        if (!is_numeric($product_id) || $product_id <= 0) {
            return ['amount' => 0, 'count' => 0]; // Invalid product ID
        }

        global $wpdb;

        // ---- Build date conditions ----
        $date_conditions = '';
        $date_params = [];

        if (!empty($start_date)) {
            $date_conditions .= " AND p.post_date_gmt >= %s";
            $date_params[] = $start_date;
        }
        if (!empty($end_date)) {
            $date_conditions .= " AND p.post_date_gmt <= %s";
            $date_params[] = $end_date;
        }

        // ---- 1) Count distinct orders ----
        $count_sql = "
            SELECT COUNT(DISTINCT oi.order_id)
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1
                ON oi.order_item_id = om1.order_item_id
            INNER JOIN {$wpdb->posts} AS p
                ON oi.order_id = p.ID
            WHERE p.post_status = 'wc-completed'
                AND oi.order_item_type = 'line_item'
                AND om1.meta_key = '_product_id'
                AND om1.meta_value = %d
                {$date_conditions}
        ";
        $count_params = array_merge([$product_id], $date_params);
        $count_result = $wpdb->get_var($wpdb->prepare($count_sql, $count_params));
        $count_result = $count_result ? (int) $count_result : 0;

        // ---- 2) Sum of line totals with currency conversion ----
        $sum_sql = "
            SELECT oi.order_id, om2.meta_value AS line_total, 
                (SELECT meta_value 
                    FROM {$wpdb->prefix}postmeta 
                    WHERE post_id = oi.order_id 
                    AND meta_key = '_order_currency' 
                    LIMIT 1) AS order_currency
            FROM {$wpdb->prefix}woocommerce_order_items AS oi
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om1
                ON oi.order_item_id = om1.order_item_id
            INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS om2
                ON oi.order_item_id = om2.order_item_id
            INNER JOIN {$wpdb->posts} AS p
                ON oi.order_id = p.ID
            WHERE p.post_status = 'wc-completed'
                AND oi.order_item_type = 'line_item'
                AND om1.meta_key = '_product_id'
                AND om1.meta_value = %d
                AND om2.meta_key = '_line_total'
                {$date_conditions}
        ";
        $sum_params = array_merge([$product_id], $date_params);
        $sum_results = $wpdb->get_results($wpdb->prepare($sum_sql, $sum_params));

        $total_gbp = 0;

        // Process each result and apply currency conversion
        if (!empty($sum_results)) {
            foreach ($sum_results as $row) {
                $currency = !empty($row->order_currency) ? $row->order_currency : get_option('woocommerce_currency');
                $amount = floatval($row->line_total); // Convert line total to float

                // If the currency is not GBP, convert it to GBP using the currency rate
                if (strtoupper($currency) !== get_option('woocommerce_currency')) {
                    $rate = Skyweb_Currency_Changer::get_rate(get_option('woocommerce_currency'), $currency);
                    if ($rate && $rate > 0) {
                        $amount = $amount / $rate; // Convert the amount to GBP
                    } else {
                        // Optional: Handle the case where the conversion rate is not available
                        $amount = 0; // Default to 0 if conversion rate is not found
                    }
                }

                // Add the converted amount to the total GBP
                $total_gbp += $amount;
            }
        }

        // Return the results as an associative array
        return [
            'amount' => round($total_gbp, 2), // Total sales amount in GBP
            'count'  => $count_result,        // Number of orders
        ];
    }



}
