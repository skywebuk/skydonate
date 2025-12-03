<?php


if (!class_exists('Skyweb_Donation_Shortcode')) {

    class Skyweb_Donation_Shortcode {

        public function __construct() {
            add_shortcode('skyweb_donation_form', [$this, 'render_shortcode']);
        }

        /**
         * Shortcode render callback
         */
        public function render_shortcode($atts) {
            $layout = skyweb_donation_layout_option('addons_donation_form_layout');
            if (!is_array($layout)) {
                $layout = ['layout1'];
            }

            $atts = shortcode_atts([
                'id'          => '',
                'placeholder' => __('Custom Amount', 'skydonate'),
                'button_text' => __('Donate and Support', 'skydonate'),
                'before_icon' => (in_array('layout2',  $layout) ? '<i class="fas fa-lock"></i>' : '<i class="fas fa-credit-card"></i>' ),
                'after_icon'  => '<i class="fas fa-arrow-right"></i>',
            ], $atts, 'skyweb_donation_form');

            $id = intval($atts['id']);

            // Fallback to current product
            if (empty($id) && is_product()) {
                $id = get_the_ID();
            }
            
            if (empty($id)) {
                return '<div class="woocommerce-info">' . esc_html__('Donation ID not found.', 'skydonate') . '</div>';
            }
            
            ob_start();
            $this->render($id, $atts, $layout);
            return ob_get_clean();
        }


        /**
         * Render donation form wrapper
         */
        protected function render($id, $atts, $layout) {
            $close_project  = get_post_meta($id, '_close_project', true);
            $zakat_applicable = get_post_meta($id, '_zakat_applicable', true);
            $closed_message = get_post_meta($id, '_project_closed_message', true);
            $closed_title   = get_post_meta($id, '_project_closed_title', true);
            echo '<div class="donation-form-wrapper">';
            if ($close_project !== 'yes') {
                if(in_array('layout2',  $layout)){
                    $this->layout_two($id,$atts);
                }elseif(in_array('layout3',  $layout)){
                    $this->layout_three($id,$atts);
                }else {
                    $this->layout_one($id,$atts);
                }
            } else {
                echo '<div class="donation-closed">';
                    echo '<div class="clossing-img"><img src="' . esc_url(SKYWEB_DONATION_SYSTEM_PUBLIC_ASSETS . '/img/give.png') . '" alt="Project Closed"/></div>';
                    if (!empty($closed_title)) {
                        echo '<h4>' . esc_html($closed_title) . '</h4>';
                    }
                    if (!empty($closed_message)) {
                        echo '<p>' . esc_html($closed_message) . '</p>';
                    }
                echo '</div>';
            }

            if ( $zakat_applicable === 'yes' ) {
                echo '<div class="zakat-applicable">
                        <i class="fa-sharp fa-solid fa-circle-info"></i>
                        ' . __('This project is Zakat applicable.', 'skydonate') . '
                    </div>';
            }


            echo '</div>'; // .donation-form-wrapper
        }

        /**
         * Render donation card content
         */
        protected function layout_one($id,$atts) {
            $btn_label = false;
            $today     = date('Y-m-d');
            $tomorrow  = date('Y-m-d', strtotime('+1 day'));
            $one_year_later = date('Y-m-d', strtotime('+1 year'));
            $heart_switcher = get_option('donation_monthly_heart_icon');
            $start_date = get_post_meta($id, '_start_date', true);
            $enable_start_date = get_post_meta($id, '_enable_start_date', true);
            $end_date = get_post_meta($id, '_end_date', true);
            $enable_end_date = get_post_meta($id, '_enable_end_date', true);
            if ($start_date && $today <= $start_date) {
                $start_date = $today;
            }
            if ($end_date && $end_date <= $today) {
                $end_date = '';
            }
            if(!empty($start_date)){
                $start_date = strtotime($start_date);
                $start_date = date('d-m-Y', $start_date);
            }
            if(!empty($end_date)){
                $end_date = strtotime($end_date);
                $end_date = date('d-m-Y', $end_date);
            }

            $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
            $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
            $layout             = get_post_meta($id, '_skyweb_selected_layout', true) ?: 'layout_one';
            $layout             = ($layout == 'layout_one') ? 'grid-layout' : 'list-layout';
            $custom_options     = get_post_meta($id, '_custom_options', true);
            $default_option     = get_post_meta($id, '_default_option', true);
            $box_title          = get_post_meta($id, '_box_title', true);
            $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

            $deafult_amount = '';

            echo '<form class="donation-form ' . esc_attr($layout) . '" data-product="' . esc_attr($id) . '">';

            // ----- Donation Frequency Buttons -----
            $frequencies = [
                'once'    => __('Once', 'skydonate'),
                'daily'   => __('Daily', 'skydonate'),
                'weekly'  => __('Weekly', 'skydonate'),
                'monthly' => __('Monthly', 'skydonate'),
                'yearly'  => __('Yearly', 'skydonate'),
            ];



            if (count($button_visibility) >= 2) {
                echo '<div class="donation-type-switch buttons-' . esc_attr(min(count($button_visibility), 3)) . '">';
                foreach ($frequencies as $key => $label) {
                    $meta_key = "show_{$key}";
                    if (!in_array($meta_key, $button_visibility, true)) continue;
                    $active_class = ($key === $donation_frequency) ? ' active' : '';
                    $heart = ($key === 'monthly' && $heart_switcher == 1) ? '<span class="heart-icon"><i class="fas fa-heart"></i></span>' : '';
                    echo '<button type="button" class="donation-type-btn' . esc_attr($active_class) . ' ' . esc_attr($key) . '" data-type="' . esc_attr($key) . '">' . esc_html($label) . $heart . '</button>';
                }
                echo '</div>';
            } else {
                echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
            }

            // ----- Box Title -----
            if (!empty($box_title)) {
                $arrow_icon = '';
                if ($box_arrow_hide !== 'yes') {
                    $arrow_icon = '<svg class="arrow-up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21.048 12.8102L19.478 6.57021C19.4283 6.37774 19.3053 6.21238 19.1352 6.10957C18.9651 6.00676 18.7614 5.97468 18.568 6.02021L12.328 7.60021C12.1357 7.6559 11.9734 7.7857 11.8768 7.96105C11.7802 8.13639 11.7573 8.34293 11.813 8.53521C11.8687 8.72749 11.9985 8.88977 12.1738 8.98635C12.3492 9.08293 12.5557 9.1059 12.748 9.05021L17.168 7.94021C15.748 10.5902 11.518 17.1302 3.57797 19.0202C3.39912 19.0624 3.24198 19.1687 3.13634 19.319C3.0307 19.4694 2.98393 19.6533 3.0049 19.8358C3.02587 20.0183 3.11312 20.1868 3.25009 20.3093C3.38706 20.4318 3.56423 20.4997 3.74797 20.5002H3.91797C12.538 18.4502 16.918 11.5502 18.458 8.66021L19.598 13.1902C19.6398 13.3524 19.7341 13.4962 19.8661 13.5993C19.9981 13.7023 20.1605 13.7589 20.328 13.7602H20.508C20.6082 13.7374 20.7026 13.694 20.7852 13.6327C20.8677 13.5715 20.9366 13.4936 20.9874 13.4043C21.0382 13.3149 21.0698 13.2159 21.0803 13.1136C21.0907 13.0113 21.0797 12.908 21.048 12.8102Z" fill="currentColor"/></svg>';
                }
                echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
            }
            

            // ----- Donation Amounts -----
            echo '<div class="donation-amount-groups">';
            foreach ($frequencies as $key => $label) {
                if($layout == 'list-layout'){
                    $btn_label = true;
                }else{
                    $btn_label = false;
                }
                $meta_key = "show_{$key}";
                if (!in_array($meta_key, $button_visibility, true)) continue;

                $group_active = ($key === $donation_frequency) ? ' active' : '';
                echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
                echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 3)) . '">';
                $i = 1;

                foreach ($custom_options as $option) {
                    $amount_key = ($key === 'once') ? 'price' : $key;
                    $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
                    if(!empty($_COOKIE['skyweb_selected_currency']) && $_COOKIE['skyweb_selected_currency'] !== get_option('woocommerce_currency')){
                        $new_amount = Skyweb_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skyweb_selected_currency'], $amount);
                    }else {
                        $new_amount = $amount;
                    }
                    $is_active = ($default_option == $i) ? ' active' : '';
                    if (($key === $donation_frequency) && ($default_option == $i)) {
                        $deafult_amount = $new_amount;
                    }

                    echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
                    echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
                    if ($btn_label) {
                        echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
                    }elseif($key === 'daily'){
                        echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
                    }
                    echo '</button>';
                    $i++;
                }
                echo '</div></div>';
            }

            // ----- Custom Input -----
            echo '<div class="donation-custom">';
            if (class_exists('Skyweb_Currency_Changer')) {
                echo Skyweb_Currency_Changer::currency_changer();
            }
            echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($deafult_amount) . '" placeholder="0.00">';
            if(!empty($atts['placeholder'])){
                echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
            }
            echo '</div>';

            // ----- Date Pickers for Daily -----
            if (in_array('show_daily', $button_visibility) && $enable_start_date == 1 || $enable_end_date == 1) {
                echo '<div class="donation-daily-dates-group">';
                echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
                echo '<div class="donation-dates">';
                if ($enable_start_date == 1) {
                    echo '<div class="donation-date-field"><label>' . esc_html__('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                if($enable_end_date == 1){
                    echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                echo '</div></div>';
            }

            // ----- Name on Plaque (Text Box) -----
            $this->name_on_plaque($id);

            echo '</div>'; // .donation-amount-groups
            

            Skyweb_Donation_Functions::skydonate_submit_button($atts);

            echo '</form>';
        }
        
        /**
         * Render donation card content
         */
        protected function layout_two($id, $atts) {
            $btn_label = false;
            $today     = date('Y-m-d');
            $tomorrow  = date('Y-m-d', strtotime('+1 day'));
            $one_year_later = date('Y-m-d', strtotime('+1 year'));
            $heart_switcher = get_option('donation_monthly_heart_icon');
            $start_date = get_post_meta($id, '_start_date', true);
            $enable_start_date = get_post_meta($id, '_enable_start_date', true);
            $end_date = get_post_meta($id, '_end_date', true);
            $enable_end_date = get_post_meta($id, '_enable_end_date', true);
            if ($start_date && $today <= $start_date) {
                $start_date = $today;
            }
            if ($end_date && $end_date <= $today) {
                $end_date = '';
            }
            if(!empty($start_date)){
                $start_date = strtotime($start_date);
                $start_date = date('d-m-Y', $start_date);
            }
            if(!empty($end_date)){
                $end_date = strtotime($end_date);
                $end_date = date('d-m-Y', $end_date);
            }

            $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
            $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
            $layout             = get_post_meta($id, '_skyweb_selected_layout', true) ?: 'layout_one';
            $layout             = ($layout == 'layout_one') ? 'grid-layout' : 'list-layout';
            $custom_options     = get_post_meta($id, '_custom_options', true);
            $default_option     = get_post_meta($id, '_default_option', true);
            $box_title          = get_post_meta($id, '_box_title', true);
            $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

            $deafult_amount = '';

            echo '<form class="donation-form ' . esc_attr($layout) . '" data-product="' . esc_attr($id) . '">';

            // ----- Donation Frequency Buttons -----
            $frequencies = [
                'once'    => __('Once', 'skydonate'),
                'daily'   => __('Daily', 'skydonate'),
                'weekly'  => __('Weekly', 'skydonate'),
                'monthly' => __('Monthly', 'skydonate'),
                'yearly'  => __('Yearly', 'skydonate'),
            ];



            // Box title
            if (!empty($box_title)) {
                $arrow_icon = '';
                if($box_arrow_hide !== 'yes'){
                    $arrow_icon = '<span class="arrow-icon"></span>';
                }
                echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
            }


            if (count($button_visibility) >= 2) {
                echo '<div class="donation-type-switch buttons-' . esc_attr(min(count($button_visibility), 3)) . '">';
                foreach ($frequencies as $key => $label) {
                    $meta_key = "show_{$key}";
                    if (!in_array($meta_key, $button_visibility, true)) continue;
                    $active_class = ($key === $donation_frequency) ? ' active' : '';
                    $heart = ($key === 'monthly' && $heart_switcher == 1) ? '<span class="heart-icon"><i class="fas fa-heart"></i></span>' : '';
                    echo '<button type="button" class="donation-type-btn' . esc_attr($active_class) . ' ' . esc_attr($key) . '" data-type="' . esc_attr($key) . '">' . esc_html($label) . $heart . '</button>';
                }
                echo '</div>';
            } else {
                echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
            }

            // ----- Donation Amounts -----
            echo '<div class="donation-amount-groups">';
            foreach ($frequencies as $key => $label) {
                if($layout == 'list-layout'){
                    $btn_label = true;
                }else{
                    $btn_label = false;
                }
                $meta_key = "show_{$key}";
                if (!in_array($meta_key, $button_visibility, true)) continue;

                $group_active = ($key === $donation_frequency) ? ' active' : '';
                echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
                echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 3)) . '">';
                $i = 1;
                foreach ($custom_options as $option) {
                    $amount_key = ($key === 'once') ? 'price' : $key;
                    $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
                    if(!empty($_COOKIE['skyweb_selected_currency']) && $_COOKIE['skyweb_selected_currency'] !== get_option('woocommerce_currency')){
                        $new_amount = Skyweb_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skyweb_selected_currency'], $amount);
                    }else {
                        $new_amount = $amount;
                    }
                    $is_active = ($default_option == $i) ? ' active' : '';
                    if (($key === $donation_frequency) && ($default_option == $i)) {
                        $deafult_amount = $new_amount;
                    }
                    echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
                    echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
                    if ($btn_label) {
                        echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
                    }elseif($key === 'daily'){
                        echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
                    }
                    echo '</button>';
                    $i++;
                }

                echo '</div></div>';
            }

            // ----- Custom Input -----
            echo '<div class="donation-custom">';
            if (class_exists('Skyweb_Currency_Changer')) {
                echo Skyweb_Currency_Changer::currency_changer();
            }
            echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($deafult_amount) . '" placeholder="0.00">';
            if(!empty($atts['placeholder'])){
                echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
            }
            echo '</div>';

            // ----- Date Pickers for Daily -----
            if (in_array('show_daily', $button_visibility) && $enable_start_date == 1 || $enable_end_date == 1) {
                echo '<div class="donation-daily-dates-group">';
                echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
                echo '<div class="donation-dates">';
                if ($enable_start_date == 1) {
                    echo '<div class="donation-date-field"><label>' . esc_html__('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                if($enable_end_date == 1){
                    echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                echo '</div></div>';
            }

            // ----- Name on Plaque (Text Box) -----
            $this->name_on_plaque($id);

            echo '</div>'; // .donation-amount-groups

            Skyweb_Donation_Functions::skydonate_submit_button($atts);

            echo '</form>';
        }


        protected function layout_three($id, $atts) {
            $btn_label = false;
            $today     = date('Y-m-d');
            $tomorrow  = date('Y-m-d', strtotime('+1 day'));
            $one_year_later = date('Y-m-d', strtotime('+1 year'));
            $heart_switcher = get_option('donation_monthly_heart_icon');
            $start_date = get_post_meta($id, '_start_date', true);
            $enable_start_date = get_post_meta($id, '_enable_start_date', true);
            $end_date = get_post_meta($id, '_end_date', true);
            $enable_end_date = get_post_meta($id, '_enable_end_date', true);
            if ($start_date && $today <= $start_date) {
                $start_date = $today;
            }
            if ($end_date && $end_date <= $today) {
                $end_date = '';
            }
            if(!empty($start_date)){
                $start_date = strtotime($start_date);
                $start_date = date('d-m-Y', $start_date);
            }
            if(!empty($end_date)){
                $end_date = strtotime($end_date);
                $end_date = date('d-m-Y', $end_date);
            }

            $donation_frequency = get_post_meta($id, '_donation_frequency', true) ?: 'once';
            $button_visibility  = (array) get_post_meta($id, '_button_visibility', true) ?: ['show_once'];
            $layout             = get_post_meta($id, '_skyweb_selected_layout', true) ?: 'layout_one';
            $layout             = ($layout == 'layout_one') ? 'grid-layout' : 'list-layout';
            $custom_options     = get_post_meta($id, '_custom_options', true) ?: [];
            $default_option     = get_post_meta($id, '_default_option', true);
            $box_title          = get_post_meta($id, '_box_title', true);
            $box_arrow_hide     = get_post_meta($id, '_box_arrow_hide', true);

            $default_amount = '';

            $frequencies = [
                'once'    => __('One-off', 'skydonate'),
                'daily'   => __('Daily', 'skydonate'),
                'weekly'  => __('Weekly', 'skydonate'),
                'monthly' => __('Monthly', 'skydonate'),
                'yearly'  => __('Yearly', 'skydonate'),
            ];

            echo '<form class="donation-form ' . esc_attr($layout) . '" data-product="' . esc_attr($id) . '">';

            // ----- Donation Type Toggle -----
            if (count($button_visibility) >= 2) {
                echo '<div class="donation-type-switch">';
                echo '<button type="button" class="one-off ' . ($donation_frequency === 'once' ? ' active' : '') . '">' . __('One-off', 'skydonate') . '</button>';
                echo '<button type="button" class="recurring ' . ($donation_frequency !== 'once' ? ' active' : '') . '">' . __('Recurring', 'skydonate') . '</button>';
                echo '</div>';
            } else {
                echo '<input type="hidden" class="donation-type-btn active" data-type="' . esc_attr($donation_frequency) . '"/>';
            }

            // ----- Recurring Frequency Selector -----
            echo '<div class="period-select-options" style="' . ($donation_frequency === 'once' ? 'display:none;' : 'display:block;') . '">';
            echo '<label>' . __('Select recurring frequency', 'skydonate') . ' *</label>';
            echo '<select class="select-option" name="recurring_frequency">';
            foreach ($frequencies as $key => $label) {
                if ($key === 'once') continue; // skip One-off in dropdown
                if (!in_array("show_{$key}", $button_visibility, true)) continue;
                $selected = ($key === $donation_frequency) ? ' selected' : '';
                echo '<option value="' . esc_attr($key) . '"' . $selected . '>' . esc_html($label) . '</option>';
            }
            echo '</select></div>';

            // ----- Box Title -----
            if (!empty($box_title)) {
                $arrow_icon = '';
                if ($box_arrow_hide !== 'yes') {
                    $arrow_icon = '<svg class="arrow-up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M21.048 12.8102L19.478 6.57021C19.4283 6.37774 19.3053 6.21238 19.1352 6.10957C18.9651 6.00676 18.7614 5.97468 18.568 6.02021L12.328 7.60021C12.1357 7.6559 11.9734 7.7857 11.8768 7.96105C11.7802 8.13639 11.7573 8.34293 11.813 8.53521C11.8687 8.72749 11.9985 8.88977 12.1738 8.98635C12.3492 9.08293 12.5557 9.1059 12.748 9.05021L17.168 7.94021C15.748 10.5902 11.518 17.1302 3.57797 19.0202C3.39912 19.0624 3.24198 19.1687 3.13634 19.319C3.0307 19.4694 2.98393 19.6533 3.0049 19.8358C3.02587 20.0183 3.11312 20.1868 3.25009 20.3093C3.38706 20.4318 3.56423 20.4997 3.74797 20.5002H3.91797C12.538 18.4502 16.918 11.5502 18.458 8.66021L19.598 13.1902C19.6398 13.3524 19.7341 13.4962 19.8661 13.5993C19.9981 13.7023 20.1605 13.7589 20.328 13.7602H20.508C20.6082 13.7374 20.7026 13.694 20.7852 13.6327C20.8677 13.5715 20.9366 13.4936 20.9874 13.4043C21.0382 13.3149 21.0698 13.2159 21.0803 13.1136C21.0907 13.0113 21.0797 12.908 21.048 12.8102Z" fill="currentColor"/></svg>';
                }
                echo '<div class="donation-box-title">' . esc_html($box_title) . $arrow_icon . '</div>';
            }

            // ----- Donation Amount Buttons -----
            echo '<div class="donation-amount-groups">';
            foreach ($frequencies as $key => $label) {
                if($layout == 'list-layout'){
                    $btn_label = true;
                }else{
                    $btn_label = false;
                }
                $meta_key = "show_{$key}";
                if (!in_array($meta_key, $button_visibility, true)) continue;

                $group_active = ($key === $donation_frequency) ? ' active' : '';
                echo '<div class="donation-amount-group' . esc_attr($group_active) . '" data-group="' . esc_attr($key) . '">';
                echo '<div class="donation-buttons buttons-' . esc_attr(min(count($custom_options), 4)) . '">';
                $i = 1;

                foreach ($custom_options as $option) {
                    $amount_key = ($key === 'once') ? 'price' : $key;
                    $amount = !empty($option[$amount_key]) ? $option[$amount_key] : 0;
                    if(!empty($_COOKIE['skyweb_selected_currency']) && $_COOKIE['skyweb_selected_currency'] !== get_option('woocommerce_currency')){
                        $new_amount = Skyweb_Currency_Changer::convert_currency(get_option('woocommerce_currency'), $_COOKIE['skyweb_selected_currency'], $amount);
                    }else {
                        $new_amount = $amount;
                    }
                    $is_active = ($default_option == $i) ? ' active' : '';
                    if (($key === $donation_frequency) && ($default_option == $i)) {
                        $default_amount = $new_amount;
                    }
                    echo '<button type="button" class="donation-btn ' . esc_attr($is_active) . '" data-amount="' . esc_attr($new_amount) . '" data-original="' . esc_attr($amount) . '" data-type="' . esc_attr($key) . '">';
                    echo '<span class="price"><span class="currency-symbol">' . esc_html(get_woocommerce_currency_symbol()) . '</span><span class="btn-amount">' . esc_html($new_amount) . '</span></span>';
                    if ($btn_label) {
                        echo '<span class="btn-label">' . esc_html($option['label']) . '</span>';
                    } elseif ($key === 'daily') {
                        echo '<span class="daily-label">' . __('Daily','skydonate') . '</span>';
                    }
                    echo '</button>';
                    $i++;
                }

                echo '</div></div>';
            }

            // ----- Custom Amount Input -----
            echo '<div class="donation-custom">';
            if (class_exists('Skyweb_Currency_Changer')) {
                echo Skyweb_Currency_Changer::currency_changer();
            }
            echo '<input type="number" class="custom-amount-input" min="1" step="0.01" name="selected_amount" inputmode="numeric" value="' . esc_attr($default_amount) . '" placeholder="' . (!empty($atts['placeholder']) ? esc_attr($atts['placeholder']) : esc_attr(__('0.00','skydonate'))) . '">';
            if(!empty($atts['placeholder'])){
                echo '<span class="custom-placeholder">' . esc_html($atts['placeholder']) . '</span>';
            }
            echo '</div>';

            // ----- Daily Date Picker -----
            if (in_array('show_daily', $button_visibility) && $enable_start_date == 1 || $enable_end_date == 1) {
                echo '<div class="donation-daily-dates-group" style="' . ($donation_frequency === 'daily' ? 'display:block;' : 'display:none;') . '">';
                echo '<div class="date-title">' . __('Please set the start and end dates before donating.', 'skydonate') . '</div>';
                echo '<div class="donation-dates">';
                if ($enable_start_date == 1) {
                    echo '<div class="donation-date-field"><label>' . __('Start Date', 'skydonate') . '</label><input type="text" name="start_date" placeholder="'.$today.'" class="donation-start-date" value="' . esc_attr($start_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                if($enable_end_date == 1){
                    echo '<div class="donation-date-field"><label>' . __('End Date', 'skydonate') . '</label><input type="text" name="end_date" placeholder="'.$one_year_later.'" class="donation-end-date" value="' . esc_attr($end_date) . '" min="' . esc_attr($today) . '"></div>';
                }
                echo '</div></div>';
            }

            // ----- Name on Plaque (Text Box) -----
            $this->name_on_plaque($id);

            echo '</div>'; // .donation-amount-groups

            Skyweb_Donation_Functions::skydonate_submit_button($atts);

            echo '</form>';
        }

        protected function name_on_plaque($id) {
            $field_visibility_enabled  = get_post_meta($id, '_field_visibility_enabled', true);
            $field_visibility_value    = (float) get_post_meta($id, '_field_visibility_value', true);
            $field_label               = get_post_meta($id, '_field_label', true);
            $field_placeholder         = get_post_meta($id, '_field_placeholder', true);
            $field_label_visibility    = get_post_meta($id, '_field_label_visibility', true);

            if ($field_visibility_enabled === 'yes') {
                echo '<div class="name-on-plaque" data-visible="' . esc_attr($field_visibility_value) . '" style="display:none;">';
                if ($field_label_visibility === 'yes' && !empty($field_label)) {
                    echo '<label for="custom_text">' . esc_html($field_label) . '</label>';
                }
                echo '<input type="text" name="cart_custom_text" class="short" placeholder="' . esc_attr($field_placeholder) . '">';
                echo '</div>';
            }
        }
        

    }

    // Initialize the class
    new Skyweb_Donation_Shortcode();
}
