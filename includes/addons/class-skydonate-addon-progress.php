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
        $no_donations_message = esc_html($settings['no_donations_message']);

        // Check if we're on a fundraising page
        if ( is_singular( 'fundraising' ) && ! $enable_filters ) {
            $page_id = get_queried_object_id();

            // Get fundraising data using fundraising plugin functions
            if ( function_exists( 'skydonate_get_total_raised' ) && function_exists( 'skydonate_get_target_amount' ) ) {
                $total_raised = skydonate_get_total_raised( $page_id );
                $target_sales_sum = skydonate_get_target_amount( $page_id );
                $currency = function_exists( 'skydonate_get_page_currency' ) ? skydonate_get_page_currency( $page_id ) : get_woocommerce_currency();
                $currency_symbol = get_woocommerce_currency_symbol( $currency );
            } else {
                // Fallback if fundraising plugin functions not available
                $total_raised = 0;
                $target_sales_sum = 0;
                $currency_symbol = Skydonate_Functions::Get_Currency_Symbol();
            }

            if ( empty($target_sales_sum) ) {
                echo '<div class="woocommerce-info">' . esc_html__( 'Donation goal target not found.', 'skydonate' ) . '</div>';
                return;
            }

            $this->render_progress_bar( $total_raised, $target_sales_sum, $settings, $currency_symbol );
            return;
        }

        $filter_product_title = (array) $settings['filter_product_title'];
        $filter_category = $settings['filter_category'];
        $filter_tag = $settings['filter_tag'];
        $override_target_goal = !empty($settings['override_target_goal']) ? floatval($settings['override_target_goal']) : 0;

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
                $total_sales      = floatval( get_post_meta( $product_id, '_total_sales_amount', true ) );
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
                $total_sales = floatval( get_post_meta( $product_id, '_total_sales_amount', true ) );
                $total_raised += $total_sales;
                if ( $settings['offline_donation'] === 'yes' ) {
                    $total_raised += $offline_donation;
                }
            }
        }

        if (empty($target_sales_sum)) {
            echo '<div class="woocommerce-info">' . esc_html__('Donation goal target not found.', 'skydonate') . '</div>';
            return;
        }

        $this->render_progress_bar( $total_raised, $target_sales_sum, $settings, Skydonate_Functions::Get_Currency_Symbol() );
    }

    /**
     * Render the progress bar HTML
     *
     * @param float  $total_raised    Total amount raised.
     * @param float  $target          Target goal amount.
     * @param array  $settings        Widget settings.
     * @param string $currency_symbol Currency symbol.
     */
    protected function render_progress_bar( $total_raised, $target, $settings, $currency_symbol ) {
        $this->add_render_attribute('wrapper_attributes', 'class', 'donation-progress');

        $bar_settings = [
            'raised' => $total_raised,
            'target' => $target,
            'duration' => $settings['progress_duration'],
            'symbol' => $currency_symbol,
        ];

        $this->add_render_attribute('wrapper_attributes', 'data-settings', wp_json_encode($bar_settings));

        echo '<div ' . $this->get_render_attribute_string('wrapper_attributes') . '>';
            echo '<div class="progress-info">' . sprintf(
                __('<span class="raised">%s</span> of <span class="goal">%s</span> goal', 'skydonate'),
                esc_html( $currency_symbol . '0' ),
                esc_html( $currency_symbol . number_format( $target ) )
            ) . '</div>';
            echo '<div class="progress-bar-background">';
                echo '<div class="progress-bar">';
                echo '<span class="percent">0%</span>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
    }

}
