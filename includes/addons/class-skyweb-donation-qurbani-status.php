<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SkyWeb_Donation_Qurbani_Status extends \Elementor\Widget_Base {


    public function get_name() {
        return 'qurbani_status_table';
    }

    public function get_title() {
        return __( 'Qurbani Status Table', 'skydonate' );
    }

    public function get_icon() {
        return 'eicon-table';
    }

    public function get_categories() {
        return [ 'skyweb_donation' ];
    }

    public function get_keywords() {
        return [ 'table', 'status', 'qurbani', 'dropdown' ];
    }


        protected function register_controls() {
            // Content Section
            $this->start_controls_section(
                'content_section',
                [
                    'label' => __( 'Table Content', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'stack_on_mobile',
                [
                    'label' => __( 'Stack on Mobile', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'skydonate' ),
                    'label_off' => __( 'No', 'skydonate' ),
                    'return_value' => 'yes',
                    'default' => 'no',
                    'description' => __( 'Display table as stacked cards on mobile devices', 'skydonate' ),
                ]
            );

            $this->add_control(
                'mobile_compact_mode',
                [
                    'label' => __( 'Mobile Compact Mode', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'skydonate' ),
                    'label_off' => __( 'No', 'skydonate' ),
                    'return_value' => 'yes',
                    'default' => 'yes',
                    'description' => __( 'Optimize table layout for mobile devices', 'skydonate' ),
                    'condition' => [
                        'stack_on_mobile!' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'column_1_header',
                [
                    'label' => __( 'Column 1 Header', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Name', 'skydonate' ),
                    'placeholder' => __( 'Enter column header', 'skydonate' ),
                ]
            );

            $this->add_control(
                'column_1_mobile_header',
                [
                    'label' => __( 'Column 1 Mobile Header (Optional)', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => '',
                    'placeholder' => __( 'Shorter text for mobile', 'skydonate' ),
                    'description' => __( 'Use shorter text for mobile devices', 'skydonate' ),
                    'condition' => [
                        'mobile_compact_mode' => 'yes',
                        'stack_on_mobile!' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'column_2_header',
                [
                    'label' => __( 'Column 2 Header', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Qurbani Status', 'skydonate' ),
                    'placeholder' => __( 'Enter column header', 'skydonate' ),
                ]
            );

            $this->add_control(
                'column_3_header',
                [
                    'label' => __( 'Column 3 Header', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Distribution Status', 'skydonate' ),
                    'placeholder' => __( 'Enter column header', 'skydonate' ),
                ]
            );

            // Popup Message
            $this->add_control(
                'popup_message_heading',
                [
                    'label' => __( 'Popup Message', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'enable_popup',
                [
                    'label' => __( 'Show Popup on Frontend Click', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'skydonate' ),
                    'label_off' => __( 'No', 'skydonate' ),
                    'return_value' => 'yes',
                    'default' => 'yes',
                ]
            );

            $this->add_control(
                'popup_title',
                [
                    'label' => __( 'Popup Title', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Status Information', 'skydonate' ),
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'popup_scheduled_message',
                [
                    'label' => __( 'Scheduled Status Message', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => __( 'This Qurbani is scheduled and will be completed soon. You will be notified once the sacrifice is performed.', 'skydonate' ),
                    'rows' => 4,
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'popup_completed_message',
                [
                    'label' => __( 'Completed Status Message', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => __( 'This Qurbani has been completed successfully. The meat has been distributed to the beneficiaries. May Allah accept your sacrifice.', 'skydonate' ),
                    'rows' => 4,
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'popup_not_available_message',
                [
                    'label' => __( 'Not Available Status Message', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => __( 'This option is not available in this location. Please check other available options or contact us for more information.', 'skydonate' ),
                    'rows' => 4,
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            // New Custom Status Popup Message
            $this->add_control(
                'popup_custom_message',
                [
                    'label' => __( 'Custom Status Message', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXTAREA,
                    'default' => __( 'This is a custom status. Please contact us for more information about this specific case.', 'skydonate' ),
                    'rows' => 4,
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            // Table Rows Repeater
            $repeater = new \Elementor\Repeater();

            $repeater->add_control(
                'row_name',
                [
                    'label' => __( 'Name', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Person Name', 'skydonate' ),
                    'label_block' => true,
                ]
            );

            $repeater->add_control(
                'column_2_status',
                [
                    'label' => __( 'Small Animal Status', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'scheduled',
                    'options' => [
                        'scheduled' => __( 'Scheduled', 'skydonate' ),
                        'completed' => __( 'Completed', 'skydonate' ),
                        'not_available' => __( 'Not Available', 'skydonate' ),
                        'custom' => __( 'Custom', 'skydonate' ),
                    ],
                ]
            );

            // Custom text for column 2
            $repeater->add_control(
                'column_2_custom_text',
                [
                    'label' => __( 'Custom Text for Small Animal', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Custom Status', 'skydonate' ),
                    'placeholder' => __( 'Enter custom status text', 'skydonate' ),
                    'label_block' => true,
                    'condition' => [
                        'column_2_status' => 'custom',
                    ],
                ]
            );

            $repeater->add_control(
                'column_3_status',
                [
                    'label' => __( 'Large Animal Status', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'scheduled',
                    'options' => [
                        'scheduled' => __( 'Scheduled', 'skydonate' ),
                        'completed' => __( 'Completed', 'skydonate' ),
                        'not_available' => __( 'Not Available', 'skydonate' ),
                        'custom' => __( 'Custom', 'skydonate' ),
                    ],
                ]
            );

            // Custom text for column 3
            $repeater->add_control(
                'column_3_custom_text',
                [
                    'label' => __( 'Custom Text for Large Animal', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => __( 'Custom Status', 'skydonate' ),
                    'placeholder' => __( 'Enter custom status text', 'skydonate' ),
                    'label_block' => true,
                    'condition' => [
                        'column_3_status' => 'custom',
                    ],
                ]
            );

            $this->add_control(
                'table_rows',
                [
                    'label' => __( 'Table Rows', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::REPEATER,
                    'fields' => $repeater->get_controls(),
                    'default' => [
                        [
                            'row_name' => __( 'Ahmad Hassan', 'skydonate' ),
                            'column_2_status' => 'completed',
                            'column_3_status' => 'scheduled',
                        ],
                        [
                            'row_name' => __( 'Fatima Ali', 'skydonate' ),
                            'column_2_status' => 'scheduled',
                            'column_3_status' => 'scheduled',
                        ],
                    ],
                    'title_field' => '{{{ row_name }}}',
                ]
            );

            $this->end_controls_section();

            // Table Style Section
            $this->start_controls_section(
                'table_style_section',
                [
                    'label' => __( 'Table Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_responsive_control(
                'table_alignment',
                [
                    'label' => __( 'Table Alignment', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => __( 'Left', 'skydonate' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => __( 'Center', 'skydonate' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => __( 'Right', 'skydonate' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'default' => 'center',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-wrapper' => 'text-align: {{VALUE}};',
                        '{{WRAPPER}} .qurbani-status-table' => 'margin-left: {{VALUE}} === "center" ? "auto" : "0"; margin-right: {{VALUE}} === "center" ? "auto" : "0";',
                    ],
                ]
            );

            $this->add_responsive_control(
                'table_width',
                [
                    'label' => __( 'Table Width', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'size_units' => [ '%', 'px' ],
                    'range' => [
                        '%' => [
                            'min' => 10,
                            'max' => 100,
                        ],
                        'px' => [
                            'min' => 100,
                            'max' => 1200,
                        ],
                    ],
                    'default' => [
                        'unit' => '%',
                        'size' => 100,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-status-table' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'table_background',
                [
                    'label' => __( 'Table Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-status-table' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'table_border',
                    'label' => __( 'Table Border', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .qurbani-status-table',
                ]
            );

            $this->add_control(
                'table_border_radius',
                [
                    'label' => __( 'Border Radius', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-status-table' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'table_box_shadow',
                    'label' => __( 'Box Shadow', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .qurbani-status-table',
                ]
            );

            $this->add_control(
                'cell_border_heading',
                [
                    'label' => __( 'Cell Borders', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'cell_border_style',
                [
                    'label' => __( 'Cell Border Style', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'solid',
                    'options' => [
                        'none' => __( 'None', 'skydonate' ),
                        'solid' => __( 'Solid', 'skydonate' ),
                        'dashed' => __( 'Dashed', 'skydonate' ),
                        'dotted' => __( 'Dotted', 'skydonate' ),
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body td, {{WRAPPER}} .qurbani-table-header th' => 'border-style: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'cell_border_width',
                [
                    'label' => __( 'Cell Border Width', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 10,
                        ],
                    ],
                    'default' => [
                        'size' => 1,
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body td, {{WRAPPER}} .qurbani-table-header th' => 'border-width: {{SIZE}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_control(
                'cell_border_color',
                [
                    'label' => __( 'Cell Border Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#dee2e6',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body td, {{WRAPPER}} .qurbani-table-header th' => 'border-color: {{VALUE}};',
                    ],
                ]
            );

            $this->end_controls_section();

            // Header Style Section
            $this->start_controls_section(
                'header_style_section',
                [
                    'label' => __( 'Header Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'header_background',
                [
                    'label' => __( 'Header Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#2c3e50',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-header' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'header_text_color',
                [
                    'label' => __( 'Header Text Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-header th' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'header_typography',
                    'label' => __( 'Header Typography', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .qurbani-table-header th',
                ]
            );

            $this->add_responsive_control(
                'header_padding',
                [
                    'label' => __( 'Header Padding', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'default' => [
                        'top' => '15',
                        'right' => '20',
                        'bottom' => '15',
                        'left' => '20',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-header th' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_section();

            // Row Style Section
            $this->start_controls_section(
                'row_style_section',
                [
                    'label' => __( 'Row Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'row_background_odd',
                [
                    'label' => __( 'Odd Row Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f8f9fa',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body tr:nth-child(odd)' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'row_background_even',
                [
                    'label' => __( 'Even Row Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body tr:nth-child(even)' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'row_text_color',
                [
                    'label' => __( 'Row Text Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#495057',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body td' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'row_typography',
                    'label' => __( 'Row Typography', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .qurbani-table-body td',
                ]
            );

            $this->add_responsive_control(
                'row_padding',
                [
                    'label' => __( 'Cell Padding', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'default' => [
                        'top' => '12',
                        'right' => '20',
                        'bottom' => '12',
                        'left' => '20',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-table-body td' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->end_controls_section();

            // Dropdown Style Section
            $this->start_controls_section(
                'dropdown_style_section',
                [
                    'label' => __( 'Status Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                ]
            );

            $this->add_control(
                'show_status_icon',
                [
                    'label' => __( 'Show Status Icons', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'skydonate' ),
                    'label_off' => __( 'No', 'skydonate' ),
                    'return_value' => 'yes',
                    'default' => 'no',
                ]
            );

            $this->add_control(
                'show_info_icon',
                [
                    'label' => __( 'Show Clickable Info Icons', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => __( 'Yes', 'skydonate' ),
                    'label_off' => __( 'No', 'skydonate' ),
                    'return_value' => 'yes',
                    'default' => 'yes',
                    'description' => __( 'Show info icon to indicate status is clickable', 'skydonate' ),
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'dropdown_completed_bg',
                [
                    'label' => __( 'Completed Background Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#28a745',
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown.status-completed, {{WRAPPER}} .status-display.status-completed' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'dropdown_scheduled_bg',
                [
                    'label' => __( 'Scheduled Background Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffc107',
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown.status-scheduled, {{WRAPPER}} .status-display.status-scheduled' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'dropdown_not_available_bg',
                [
                    'label' => __( 'Not Available Background Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#6c757d',
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown.status-not_available, {{WRAPPER}} .status-display.status-not_available' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            // New Custom Status Background Color
            $this->add_control(
                'dropdown_custom_bg',
                [
                    'label' => __( 'Custom Status Background Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#9b59b6',
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown.status-custom, {{WRAPPER}} .status-display.status-custom' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'dropdown_text_color',
                [
                    'label' => __( 'Status Text Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown, {{WRAPPER}} .status-display' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'dropdown_border_radius',
                [
                    'label' => __( 'Status Border Radius', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'top' => '4',
                        'right' => '4',
                        'bottom' => '4',
                        'left' => '4',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown, {{WRAPPER}} .status-display' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'dropdown_padding',
                [
                    'label' => __( 'Status Padding', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em' ],
                    'default' => [
                        'top' => '8',
                        'right' => '16',
                        'bottom' => '8',
                        'left' => '16',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .status-dropdown, {{WRAPPER}} .status-display' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Typography::get_type(),
                [
                    'name' => 'dropdown_typography',
                    'label' => __( 'Status Typography', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .status-dropdown, {{WRAPPER}} .status-display',
                ]
            );

            $this->end_controls_section();

            // Mobile Stack Style Section
            $this->start_controls_section(
                'mobile_stack_style_section',
                [
                    'label' => __( 'Mobile Stack Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'stack_on_mobile' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'mobile_card_background',
                [
                    'label' => __( 'Card Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body tr' => 'background-color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_card_header_bg',
                [
                    'label' => __( 'Card Header Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#87CEEB',
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:first-child' => 'background-color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_card_header_color',
                [
                    'label' => __( 'Card Header Text Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#000000',
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:first-child' => 'color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Border::get_type(),
                [
                    'name' => 'mobile_card_border',
                    'label' => __( 'Card Border', 'skydonate' ),
                    'selector' => '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body tr',
                    'fields_options' => [
                        'border' => [
                            'default' => 'solid',
                        ],
                        'width' => [
                            'default' => [
                                'top' => '1',
                                'right' => '1',
                                'bottom' => '1',
                                'left' => '1',
                                'isLinked' => true,
                            ],
                        ],
                        'color' => [
                            'default' => '#e5e5e5',
                        ],
                    ],
                ]
            );

            $this->add_control(
                'mobile_row_separator_heading',
                [
                    'label' => __( 'Row Separator', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::HEADING,
                    'separator' => 'before',
                ]
            );

            $this->add_control(
                'mobile_row_border_width',
                [
                    'label' => __( 'Row Border Width', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 10,
                        ],
                    ],
                    'default' => [
                        'size' => 1,
                    ],
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:not(:last-child)' => 'border-bottom-width: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_row_border_style',
                [
                    'label' => __( 'Row Border Style', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => 'solid',
                    'options' => [
                        'none' => __( 'None', 'skydonate' ),
                        'solid' => __( 'Solid', 'skydonate' ),
                        'dashed' => __( 'Dashed', 'skydonate' ),
                        'dotted' => __( 'Dotted', 'skydonate' ),
                    ],
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:not(:last-child)' => 'border-bottom-style: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_row_border_color',
                [
                    'label' => __( 'Row Border Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#f0f0f0',
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:not(:last-child)' => 'border-bottom-color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_label_color',
                [
                    'label' => __( 'Label Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#333333',
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:not(:first-child):before' => 'color: {{VALUE}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_row_padding',
                [
                    'label' => __( 'Row Padding', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px' ],
                    'default' => [
                        'top' => '20',
                        'right' => '25',
                        'bottom' => '20',
                        'left' => '25',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:not(:first-child)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_card_spacing',
                [
                    'label' => __( 'Card Spacing', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'min' => 0,
                            'max' => 50,
                        ],
                    ],
                    'default' => [
                        'size' => 20,
                    ],
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body tr' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
                    ],
                ]
            );

            $this->add_control(
                'mobile_card_border_radius',
                [
                    'label' => __( 'Card Border Radius', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'default' => [
                        'top' => '12',
                        'right' => '12',
                        'bottom' => '12',
                        'left' => '12',
                        'unit' => 'px',
                    ],
                    'selectors' => [
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body tr' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
                        '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body td:first-child' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} 0 0 !important;',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'mobile_card_box_shadow',
                    'label' => __( 'Card Shadow', 'skydonate' ),
                    'selector' => '(mobile){{WRAPPER}} .stack-on-mobile .qurbani-table-body tr',
                ]
            );

            $this->end_controls_section();

            // Popup Style Section
            $this->start_controls_section(
                'popup_style_section',
                [
                    'label' => __( 'Popup Style', 'skydonate' ),
                    'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                    'condition' => [
                        'enable_popup' => 'yes',
                    ],
                ]
            );

            $this->add_control(
                'popup_background',
                [
                    'label' => __( 'Popup Background', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#ffffff',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-popup-content' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'popup_title_color',
                [
                    'label' => __( 'Title Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#2c3e50',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-popup-title' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'popup_text_color',
                [
                    'label' => __( 'Message Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => '#495057',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-popup-message' => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'popup_overlay_color',
                [
                    'label' => __( 'Overlay Color', 'skydonate' ),
                    'type' => \Elementor\Controls_Manager::COLOR,
                    'default' => 'rgba(0,0,0,0.7)',
                    'selectors' => [
                        '{{WRAPPER}} .qurbani-popup-overlay' => 'background-color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                \Elementor\Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'popup_box_shadow',
                    'label' => __( 'Box Shadow', 'skydonate' ),
                    'selector' => '{{WRAPPER}} .qurbani-popup-content',
                ]
            );

            $this->end_controls_section();
        }

    

        protected function get_status_display_text( $status, $custom_text = '' ) {
            if ( $status === 'custom' && !empty( $custom_text ) ) {
                return $custom_text;
            } elseif ( $status === 'completed' ) {
                return __( 'Completed', 'skydonate' );
            } elseif ( $status === 'not_available' ) {
                return __( 'Not Available', 'skydonate' );
            } elseif ( $status === 'custom' ) {
                return __( 'Custom Status', 'skydonate' );
            } else {
                return __( 'Scheduled', 'skydonate' );
            }
        }

        protected function get_status_icon( $status ) {
            if ( $status === 'completed' ) {
                return '✓';
            } elseif ( $status === 'not_available' ) {
                return '✗';
            } elseif ( $status === 'custom' ) {
                return '★';
            } else {
                return '⏱';
            }
        }

        protected function render() {
            $settings = $this->get_settings_for_display();
            $widget_id = $this->get_id();
            $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();
            ?>
            <style>
                /* Base table styles */
                .elementor-widget-qurbani_status_table .qurbani-table-wrapper {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                }
                
                .elementor-widget-qurbani_status_table .qurbani-status-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0 auto;
                }
                
                .elementor-widget-qurbani_status_table .qurbani-table-header th:first-child,
                .elementor-widget-qurbani_status_table .qurbani-table-body td:first-child {
                    text-align: left;
                }
                
                .elementor-widget-qurbani_status_table .qurbani-table-body tr {
                    transition: background-color 0.3s ease;
                }
                
                /* Status styles */
                .elementor-widget-qurbani_status_table .status-dropdown,
                .elementor-widget-qurbani_status_table .status-display {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    border: none;
                    font-weight: 600;
                    text-align: center;
                    min-width: 120px;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    cursor: pointer;
                    position: relative;
                }
                
                /* Info icon styles */
                .elementor-widget-qurbani_status_table .status-info-icon {
                    width: 16px;
                    height: 16px;
                    opacity: 0.7;
                    transition: all 0.3s ease;
                    flex-shrink: 0;
                }
                
                .elementor-widget-qurbani_status_table .status-display:hover .status-info-icon {
                    opacity: 1;
                    transform: scale(1.1);
                }
                
                .elementor-widget-qurbani_status_table .status-dropdown {
                    -webkit-appearance: none;
                    -moz-appearance: none;
                    appearance: none;
                    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                    background-repeat: no-repeat;
                    background-position: right 10px center;
                    background-size: 15px;
                    padding-right: 35px !important;
                }
                
                .elementor-widget-qurbani_status_table .status-dropdown:hover,
                .elementor-widget-qurbani_status_table .status-display:hover {
                    opacity: 0.9;
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }
                
                .elementor-widget-qurbani_status_table .status-icon {
                    margin-right: 5px;
                    font-weight: bold;
                    font-size: 16px;
                }
                
                /* Mobile header */
                .elementor-widget-qurbani_status_table .mobile-header {
                    display: none;
                }
                
                /* Stack on mobile styles */
                @media (max-width: 767px) {
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-status-table {
                        display: block;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-header {
                        display: none;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body {
                        display: block;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body tr {
                        display: block;
                        margin-bottom: 20px;
                        overflow: hidden;
                        background: #fff;
                        border: 1px solid #e5e5e5;
                        border-radius: 12px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body td {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        text-align: left !important;
                        padding: 20px 25px !important;
                        border: none;
                        position: relative;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body td:not(:last-child) {
                        border-bottom: 1px solid #f0f0f0;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body td:not(:first-child):before {
                        content: attr(data-label);
                        font-weight: 500;
                        color: #333;
                        font-size: 16px;
                        margin-right: auto;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .qurbani-table-body td:first-child {
                        background-color: #87CEEB;
                        color: #000;
                        font-weight: 600;
                        font-size: 18px;
                        padding: 25px !important;
                        border-radius: 12px 12px 0 0;
                        display: block;
                        text-align: left;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .status-dropdown,
                    .elementor-widget-qurbani_status_table .stack-on-mobile .status-display {
                        float: none;
                        margin: 0;
                        min-width: 140px;
                        text-align: center;
                        padding: 10px 20px !important;
                        font-size: 14px;
                        font-weight: 500;
                        letter-spacing: 0.5px;
                        display: inline-flex !important;
                        align-items: center;
                        justify-content: center;
                        gap: 6px;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .status-info-icon {
                        width: 14px;
                        height: 14px;
                    }
                    
                    /* Adjust status colors for better visibility */
                    .elementor-widget-qurbani_status_table .stack-on-mobile .status-scheduled {
                        background-color: #ff9999 !important;
                        color: #fff !important;
                    }
                    
                    .elementor-widget-qurbani_status_table .stack-on-mobile .status-not_available {
                        background-color: #6c757d !important;
                        color: #fff !important;
                    }
                    
                    /* Compact mode (non-stacked) */
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .qurbani-table-wrapper {
                        padding: 0 10px;
                    }
                    
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .desktop-header.has-mobile {
                        display: none;
                    }
                    
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .mobile-header {
                        display: inline;
                    }
                    
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .qurbani-status-table {
                        font-size: 14px;
                    }
                    
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .status-dropdown,
                    .elementor-widget-qurbani_status_table:not(.stack-on-mobile) .status-display {
                        min-width: auto;
                        padding: 6px 12px !important;
                        font-size: 13px;
                    }
                }
                
                /* Popup styles */
                .qurbani-popup-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    z-index: 999;
                    animation: fadeIn 0.3s ease;
                }
                
                .qurbani-popup-content {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    padding: 30px;
                    border-radius: 8px;
                    max-width: 500px;
                    width: 90%;
                    text-align: center;
                    animation: slideIn 0.3s ease;
                }
                
                .qurbani-popup-close {
                    position: absolute;
                    top: 10px;
                    right: 10px;
                    width: 30px;
                    height: 30px;
                    cursor: pointer;
                    background: none;
                    border: none;
                    font-size: 24px;
                    color: #999;
                    transition: color 0.3s ease;
                }
                
                .qurbani-popup-close:hover {
                    color: #333;
                }
                
                .qurbani-popup-title {
                    font-size: 24px;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .qurbani-popup-message {
                    font-size: 16px;
                    line-height: 1.6;
                }
                
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes slideIn {
                    from { 
                        opacity: 0;
                        transform: translate(-50%, -60%);
                    }
                    to { 
                        opacity: 1;
                        transform: translate(-50%, -50%);
                    }
                }
            </style>
            
            <div class="qurbani-table-container <?php echo $settings['stack_on_mobile'] === 'yes' ? 'stack-on-mobile' : ''; ?>">
                <div class="qurbani-table-wrapper">
                    <table class="qurbani-status-table">
                        <thead class="qurbani-table-header">
                            <tr>
                                <th>
                                    <?php if ( $settings['mobile_compact_mode'] === 'yes' && !empty( $settings['column_1_mobile_header'] ) && $settings['stack_on_mobile'] !== 'yes' ) : ?>
                                        <span class="desktop-header has-mobile"><?php echo esc_html( $settings['column_1_header'] ); ?></span>
                                        <span class="mobile-header"><?php echo esc_html( $settings['column_1_mobile_header'] ); ?></span>
                                    <?php else : ?>
                                        <?php echo esc_html( $settings['column_1_header'] ); ?>
                                    <?php endif; ?>
                                </th>
                                <th><?php echo esc_html( $settings['column_2_header'] ); ?></th>
                                <th><?php echo esc_html( $settings['column_3_header'] ); ?></th>
                            </tr>
                        </thead>
                        <tbody class="qurbani-table-body">
                            <?php foreach ( $settings['table_rows'] as $index => $row ) : ?>
                                <tr>
                                    <td><?php echo esc_html( $row['row_name'] ); ?></td>
                                    <td data-label="<?php echo esc_attr( $settings['column_2_header'] ); ?>">
                                        <?php if ( $is_editor ) : ?>
                                            <select class="status-dropdown status-<?php echo esc_attr( $row['column_2_status'] ); ?>" 
                                                    data-row="<?php echo $index; ?>" 
                                                    data-column="column_2"
                                                    aria-label="<?php echo esc_attr( sprintf( __( 'Status for %s', 'skydonate' ), $row['row_name'] ) ); ?>">
                                                <option value="scheduled" <?php selected( $row['column_2_status'], 'scheduled' ); ?>>
                                                    <?php _e( 'Scheduled', 'skydonate' ); ?>
                                                </option>
                                                <option value="completed" <?php selected( $row['column_2_status'], 'completed' ); ?>>
                                                    <?php _e( 'Completed', 'skydonate' ); ?>
                                                </option>
                                                <option value="not_available" <?php selected( $row['column_2_status'], 'not_available' ); ?>>
                                                    <?php _e( 'Not Available', 'skydonate' ); ?>
                                                </option>
                                                <option value="custom" <?php selected( $row['column_2_status'], 'custom' ); ?>>
                                                    <?php echo esc_html( $row['column_2_custom_text'] ?? __( 'Custom', 'skydonate' ) ); ?>
                                                </option>
                                            </select>
                                        <?php else : ?>
                                            <div class="status-display status-<?php echo esc_attr( $row['column_2_status'] ); ?>" 
                                                 <?php if ( $settings['enable_popup'] === 'yes' ) : ?>
                                                 onclick="showQurbaniPopup_<?php echo $widget_id; ?>('<?php echo esc_js( $row['column_2_status'] ); ?>')"
                                                 role="button"
                                                 tabindex="0"
                                                 aria-label="<?php echo esc_attr( sprintf( __( 'Status: %s. Click for details', 'skydonate' ), $this->get_status_display_text( $row['column_2_status'], $row['column_2_custom_text'] ?? '' ) ) ); ?>"
                                                 <?php endif; ?>>
                                                <?php if ( $settings['show_status_icon'] === 'yes' ) : ?>
                                                    <span class="status-icon"><?php echo $this->get_status_icon( $row['column_2_status'] ); ?></span>
                                                <?php endif; ?>
                                                <?php echo $this->get_status_display_text( $row['column_2_status'], $row['column_2_custom_text'] ?? '' ); ?>
                                                <?php if ( $settings['enable_popup'] === 'yes' && $settings['show_info_icon'] === 'yes' ) : ?>
                                                    <svg class="status-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="<?php echo esc_attr( $settings['column_3_header'] ); ?>">
                                        <?php if ( $is_editor ) : ?>
                                            <select class="status-dropdown status-<?php echo esc_attr( $row['column_3_status'] ); ?>" 
                                                    data-row="<?php echo $index; ?>" 
                                                    data-column="column_3"
                                                    aria-label="<?php echo esc_attr( sprintf( __( 'Distribution status for %s', 'skydonate' ), $row['row_name'] ) ); ?>">
                                                <option value="scheduled" <?php selected( $row['column_3_status'], 'scheduled' ); ?>>
                                                    <?php _e( 'Scheduled', 'skydonate' ); ?>
                                                </option>
                                                <option value="completed" <?php selected( $row['column_3_status'], 'completed' ); ?>>
                                                    <?php _e( 'Completed', 'skydonate' ); ?>
                                                </option>
                                                <option value="not_available" <?php selected( $row['column_3_status'], 'not_available' ); ?>>
                                                    <?php _e( 'Not Available', 'skydonate' ); ?>
                                                </option>
                                                <option value="custom" <?php selected( $row['column_3_status'], 'custom' ); ?>>
                                                    <?php echo esc_html( $row['column_3_custom_text'] ?? __( 'Custom', 'skydonate' ) ); ?>
                                                </option>
                                            </select>
                                        <?php else : ?>
                                            <div class="status-display status-<?php echo esc_attr( $row['column_3_status'] ); ?>"
                                                 <?php if ( $settings['enable_popup'] === 'yes' ) : ?>
                                                 onclick="showQurbaniPopup_<?php echo $widget_id; ?>('<?php echo esc_js( $row['column_3_status'] ); ?>')"
                                                 role="button"
                                                 tabindex="0"
                                                 aria-label="<?php echo esc_attr( sprintf( __( 'Distribution status: %s. Click for details', 'skydonate' ), $this->get_status_display_text( $row['column_3_status'], $row['column_3_custom_text'] ?? '' ) ) ); ?>"
                                                 <?php endif; ?>>
                                                <?php if ( $settings['show_status_icon'] === 'yes' ) : ?>
                                                    <span class="status-icon"><?php echo $this->get_status_icon( $row['column_3_status'] ); ?></span>
                                                <?php endif; ?>
                                                <?php echo $this->get_status_display_text( $row['column_3_status'], $row['column_3_custom_text'] ?? '' ); ?>
                                                <?php if ( $settings['enable_popup'] === 'yes' && $settings['show_info_icon'] === 'yes' ) : ?>
                                                    <svg class="status-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                                    </svg>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ( !$is_editor && $settings['enable_popup'] === 'yes' ) : ?>
            <div class="qurbani-popup-overlay" id="qurbani-popup-<?php echo $widget_id; ?>" onclick="hideQurbaniPopup_<?php echo $widget_id; ?>(event)">
                <div class="qurbani-popup-content">
                    <button class="qurbani-popup-close" onclick="hideQurbaniPopup_<?php echo $widget_id; ?>()" aria-label="<?php esc_attr_e( 'Close popup', 'skydonate' ); ?>">&times;</button>
                    <h3 class="qurbani-popup-title"><?php echo esc_html( $settings['popup_title'] ); ?></h3>
                    <p class="qurbani-popup-message" id="qurbani-popup-message-<?php echo $widget_id; ?>"></p>
                </div>
            </div>
            
            <script>
            function showQurbaniPopup_<?php echo $widget_id; ?>(status) {
                var message = '';
                var messages = {
                    completed: <?php echo json_encode( $settings['popup_completed_message'] ); ?>,
                    scheduled: <?php echo json_encode( $settings['popup_scheduled_message'] ); ?>,
                    not_available: <?php echo json_encode( $settings['popup_not_available_message'] ); ?>,
                    custom: <?php echo json_encode( $settings['popup_custom_message'] ); ?>
                };
                
                message = messages[status] || messages.scheduled;
                
                document.getElementById('qurbani-popup-message-<?php echo $widget_id; ?>').textContent = message;
                document.getElementById('qurbani-popup-<?php echo $widget_id; ?>').style.display = 'block';
            }
            
            function hideQurbaniPopup_<?php echo $widget_id; ?>(event) {
                if (!event || event.target.classList.contains('qurbani-popup-overlay') || event.target.classList.contains('qurbani-popup-close')) {
                    document.getElementById('qurbani-popup-<?php echo $widget_id; ?>').style.display = 'none';
                }
            }
            
            // Add keyboard support
            document.addEventListener('DOMContentLoaded', function() {
                var statusDisplays = document.querySelectorAll('.elementor-element-<?php echo $widget_id; ?> .status-display[role="button"]');
                statusDisplays.forEach(function(display) {
                    display.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            display.click();
                        }
                    });
                });
            });
            </script>
            <?php endif; ?>
            
            <?php if ( $is_editor ) : ?>
            <script>
            jQuery(document).ready(function($) {
                $('.elementor-element-<?php echo $widget_id; ?> .status-dropdown').on('change', function() {
                    var $this = $(this);
                    var newStatus = $this.val();
                    
                    $this.removeClass('status-scheduled status-completed status-not_available status-custom');
                    $this.addClass('status-' + newStatus);
                    
                    $this.css('transform', 'scale(1.05)');
                    setTimeout(function() {
                        $this.css('transform', 'scale(1)');
                    }, 200);
                });
            });
            </script>
            <?php endif; ?>
            <?php
        }

        protected function content_template() {
            ?>
            <#
            view.addInlineEditingAttributes( 'column_1_header', 'none' );
            view.addInlineEditingAttributes( 'column_2_header', 'none' );
            view.addInlineEditingAttributes( 'column_3_header', 'none' );
            
            function getStatusDisplayText(status, customText) {
                if (status === 'custom' && customText) {
                    return customText;
                } else if (status === 'completed') {
                    return 'Completed';
                } else if (status === 'not_available') {
                    return 'Not Available';
                } else if (status === 'custom') {
                    return 'Custom Status';
                } else {
                    return 'Scheduled';
                }
            }
            
            function getStatusIcon(status) {
                if (status === 'completed') {
                    return '✓';
                } else if (status === 'not_available') {
                    return '✗';
                } else if (status === 'custom') {
                    return '★';
                } else {
                    return '⏱';
                }
            }
            #>
            
            <div class="qurbani-table-container {{ settings.stack_on_mobile === 'yes' ? 'stack-on-mobile' : '' }}">
                <div class="qurbani-table-wrapper">
                    <table class="qurbani-status-table">
                        <thead class="qurbani-table-header">
                            <tr>
                                <th {{{ view.getRenderAttributeString( 'column_1_header' ) }}}>
                                    <# if ( settings.mobile_compact_mode === 'yes' && settings.column_1_mobile_header && settings.stack_on_mobile !== 'yes' ) { #>
                                        <span class="desktop-header has-mobile">{{{ settings.column_1_header }}}</span>
                                        <span class="mobile-header">{{{ settings.column_1_mobile_header }}}</span>
                                    <# } else { #>
                                        {{{ settings.column_1_header }}}
                                    <# } #>
                                </th>
                                <th {{{ view.getRenderAttributeString( 'column_2_header' ) }}}>{{{ settings.column_2_header }}}</th>
                                <th {{{ view.getRenderAttributeString( 'column_3_header' ) }}}>{{{ settings.column_3_header }}}</th>
                            </tr>
                        </thead>
                        <tbody class="qurbani-table-body">
                            <# _.each( settings.table_rows, function( row, index ) { #>
                                <tr>
                                    <td>{{{ row.row_name }}}</td>
                                    <td data-label="{{{ settings.column_2_header }}}">
                                        <select class="status-dropdown status-{{{ row.column_2_status }}}" data-row="{{{ index }}}" data-column="2">
                                            <option value="scheduled" <# if ( row.column_2_status === 'scheduled' ) { #>selected<# } #>>Scheduled</option>
                                            <option value="completed" <# if ( row.column_2_status === 'completed' ) { #>selected<# } #>>Completed</option>
                                            <option value="not_available" <# if ( row.column_2_status === 'not_available' ) { #>selected<# } #>>Not Available</option>
                                            <option value="custom" <# if ( row.column_2_status === 'custom' ) { #>selected<# } #>>{{{ row.column_2_custom_text || 'Custom' }}}</option>
                                        </select>
                                    </td>
                                    <td data-label="{{{ settings.column_3_header }}}">
                                        <select class="status-dropdown status-{{{ row.column_3_status }}}" data-row="{{{ index }}}" data-column="3">
                                            <option value="scheduled" <# if ( row.column_3_status === 'scheduled' ) { #>selected<# } #>>Scheduled</option>
                                            <option value="completed" <# if ( row.column_3_status === 'completed' ) { #>selected<# } #>>Completed</option>
                                            <option value="not_available" <# if ( row.column_3_status === 'not_available' ) { #>selected<# } #>>Not Available</option>
                                            <option value="custom" <# if ( row.column_3_status === 'custom' ) { #>selected<# } #>>{{{ row.column_3_custom_text || 'Custom' }}}</option>
                                        </select>
                                    </td>
                                </tr>
                            <# } ); #>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
        }
    
    
    
}