<?php


if (!class_exists('Skydonate_Shortcode')) {

    class Skydonate_Shortcode {

        public function __construct() {
            add_shortcode('skydonate_form', [$this, 'render_shortcode']);
        }

        /**
         * Shortcode render callback
         */
        public function render_shortcode($atts) {

            $atts = shortcode_atts([
                'id'          => '',
                'fundraising_id' => '',
                'placeholder' => __('Custom Amount', 'skydonate'),
                'button_text' => __('Donate and Support', 'skydonate'),
                'before_icon' => (skydonate_get_layout('addons_donation_form') == 'layout-2' ? '<i class="fas fa-lock"></i>' : '<i class="fas fa-credit-card"></i>' ),
                'after_icon'  => '<i class="fas fa-arrow-right"></i>',
            ], $atts, 'skydonate_form');

            $id = intval($atts['id']);

            // Fallback to current product
            if (empty($id) && is_product()) {
                $id = get_the_ID();
            }
            
            if (empty($id)) {
                return '<div class="woocommerce-info">' . esc_html__('Donation ID not found.', 'skydonate') . '</div>';
            }
            
            ob_start();
            $this->render($id, $atts, skydonate_get_layout('addons_donation_form'));
            return ob_get_clean();
        }


        /**
         * Render donation form wrapper
         */
        protected function render($id, $atts, $card_layout) {
            $close_project  = get_post_meta($id, '_close_project', true);
            $zakat_applicable = get_post_meta($id, '_zakat_applicable', true);
            $closed_message = get_post_meta($id, '_project_closed_message', true);
            $closed_title   = get_post_meta($id, '_project_closed_title', true);

            echo '<div class="donation-form-wrapper" data-fundraising-id="' . esc_attr($atts['fundraising_id']) . '">';
            if ($close_project !== 'yes') {
                if(skydonate_get_layout('addons_donation_form') == 'layout-2'){
                    $this->layout_two($id,$atts);
                }elseif(skydonate_get_layout('addons_donation_form') == 'layout-3'){
                    $this->layout_three($id,$atts);
                }else {
                    $this->layout_one($id,$atts);
                }
            } else {
                echo '<div class="donation-closed">';
                    echo '<div class="clossing-img"><img src="' . esc_url(SKYDONATE_PUBLIC_ASSETS . '/img/give.png') . '" alt="Project Closed"/></div>';
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
         * Render donation card content - Layout One
         * Uses remote stub for protected rendering
         */
        protected function layout_one($id,$atts) {
            skydonate_remote_stubs()->render_layout_one($id, $atts);
        }

        /**
         * Render donation card content - Layout Two
         * Uses remote stub for protected rendering
         */
        protected function layout_two($id, $atts) {
            skydonate_remote_stubs()->render_layout_two($id, $atts);
        }


        /**
         * Render donation card content - Layout Three
         * Uses remote stub for protected rendering
         */
        protected function layout_three($id, $atts) {
            skydonate_remote_stubs()->render_layout_three($id, $atts);
        }

        /**
         * Render name on plaque field
         * Uses remote stub for protected rendering
         */
        protected function name_on_plaque($id) {
            skydonate_remote_stubs()->render_name_on_plaque($id);
        }


    }

    // Initialize the class
    new Skydonate_Shortcode();
}
