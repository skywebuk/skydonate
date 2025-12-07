<?php
if (!defined('ABSPATH')) {
    exit;
}

class Skydonate_Progress extends \Elementor\Widget_Base {

    public function get_name() {
        return 'skyweb_donation_progress';
    }

    public function get_title() {
        return __('Donation Progress', 'skydonate');
    }

    public function get_icon() {
        return 'eicon-counter';
    }

    public function get_categories() {
        return ['skydonate'];
    }

    public function get_style_depends() {
        return ['donation-progress'];
    }
    
    public function get_script_depends() {
        return ['donation-progress'];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'progress_bar_section',
            [
                'label' => __('Progress Bar Settings', 'skydonate'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'enable_filters',
            [
                'label' => __('Enable Filters', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'no',
            ]
        );

        $this->add_control(
            'filter_product_title',
            [
                'label' => __('Product Title', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Title('product', 'ids'),
                'default' => '',
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_category',
            [
                'label' => __('Category', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Taxonomies('product_cat'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'filter_tag',
            [
                'label' => __('Tag', 'skydonate'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => Skydonate_Functions::Get_Taxonomies('product_tag'),
                'default' => [],
                'label_block' => true,
                'condition' => [
                    'enable_filters' => 'yes',
                ],
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


        $this->add_control(
            'progress_duration',
            [
                'label' => __('Progress Duration', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1500,
                'min' => 1,
                'step' => 1,
                'max' => 9999,
                'description' => __('Set the progress duration time in milliseconds.', 'skydonate'),
            ]
        );

        // New override setting
        $this->add_control(
            'override_target_goal',
            [
                'label' => __('Override Target Goal', 'skydonate'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1,
                'default' => '',
                'description' => __('If not empty, this overrides the product target goal when filters are enabled.', 'skydonate'),
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

        $this->add_control(
            'no_donations_message',
            [
                'label' => __('No Donations Message', 'skydonate'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('No donations received yet.', 'skydonate'),
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

        if (is_product() && !$enable_filters) {
            $filter_product_title = [get_queried_object_id()];
        }

        // If no filters and no product, show message
        if ((!$filter_product_title && !$filter_category && !$filter_tag)) {
            echo "<div class='woocommerce-info'>$no_donations_message</div>";
            return;
        }

        $product_ids = array_merge(
            $filter_product_title,
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies($filter_category, 'product_cat'),
            Skydonate_Functions::get_product_ids_by_multiple_taxonomies($filter_tag, 'product_tag')
        );
        $product_ids = array_unique($product_ids);
        $total_raised = 0;
        $target_sales_sum = 0;

        // If override is set and filters are enabled, override target
        if ( $enable_filters && $override_target_goal > 0 ) {
            $target_sales_sum = $override_target_goal;
            foreach ( $product_ids as $product_id ) {
                $offline_donation = floatval( get_post_meta( $product_id, '_offline_donation', true ) );
                $total_sales      = $this->get_total_donation_sales_amount_by_product_id( $product_id, $start_date, $end_date );
                $total_raised += $total_sales;
                if ( $settings['offline_donation'] === 'yes' ) {
                    $total_raised += $offline_donation;
                }
            }
        } else {
            foreach ( $product_ids as $product_id ) {
                $target_sales    = floatval( get_post_meta( $product_id, '_target_sales_goal', true ) );
                $offline_donation = floatval( get_post_meta( $product_id, '_offline_donation', true ) );
                if ( $target_sales > 0 ) {
                    $target_sales_sum += $target_sales;
                }
                $total_sales = $this->get_total_donation_sales_amount_by_product_id( $product_id, $start_date, $end_date );
                $total_raised += $total_sales;
                if ( $settings['offline_donation'] === 'yes' ) {
                    $total_raised += $offline_donation;
                }
            }
        }

        if ($target_sales_sum <= 0) {
            echo '<div class="woocommerce-info">' . esc_html__('Donation goal target not found.', 'skydonate') . '</div>';
            return;
        }

        $this->add_render_attribute('wrapper_attributes', 'class', 'donation-progress');

        $bar_settings = [
            'raised' => $total_raised,
            'target' => $target_sales_sum,
            'duration' => $settings['progress_duration'],
            'symbol' => Skydonate_Functions::Get_Currency_Symbol(),
        ];

        $this->add_render_attribute('wrapper_attributes', 'data-settings', wp_json_encode($bar_settings));

        echo '<div ' . $this->get_render_attribute_string('wrapper_attributes') . '>';
            echo '<div class="progress-info">' . sprintf(
                __('<span class="raised">%s</span> of <span class="goal">%s</span> goal', 'skydonate'),
                wc_price(0),
                Skydonate_Functions::Get_Currency_Symbol() . number_format($target_sales_sum)
            ) . '</div>';
            echo '<div class="progress-bar-background">';
                echo '<div class="progress-bar">';
                echo '<span class="percent">0%</span>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }

    public function get_total_donation_sales_amount_by_product_id($product_id, $start_date = null, $end_date = null) {
        global $wpdb;

        // Base SQL to also fetch currency
        $sql = "
            SELECT 
                p.ID AS order_id,
                CAST(om2.meta_value AS DECIMAL(10,2)) AS line_total,
                (SELECT meta_value 
                FROM {$wpdb->prefix}postmeta 
                WHERE post_id = p.ID 
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
        ";

        $params = [ $product_id ];

        // Optional date filters
        if ( !empty($start_date) ) {
            $sql .= " AND p.post_date >= %s";
            $params[] = $start_date;
        }

        if ( !empty($end_date) ) {
            $sql .= " AND p.post_date <= %s";
            $params[] = $end_date;
        }

        // Get results (multiple rows)
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));


        $total_gbp = 0;

        if ( !empty($results) ) {
            foreach ( $results as $row ) {
                $currency = !empty($row->order_currency) ? $row->order_currency : get_option('woocommerce_currency');
                $amount = floatval($row->line_total);

                // If not GBP, convert to GBP
                if ( strtoupper($currency) !== get_option('woocommerce_currency') ) {
                    $rate = Skydonate_Currency_Changer::get_rate(get_option('woocommerce_currency'), $currency);
                    if ( $rate && $rate > 0 ) {
                        $amount = $amount / $rate; // convert to GBP
                    }
                }
                $total_gbp += round($amount);
            }
        }

        return round($total_gbp, 2);
    }

}
