<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Donation_Goal {

    public function __construct() {
        if (sky_status_check('enable_donation_goal')) {
            add_shortcode('product_sales_progress_bar', [$this, 'product_sales_progress_bar_shortcode']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue_donation_goal_styles']);
        }
    }

    public function enqueue_donation_goal_styles() {
        wp_enqueue_style('donation-goal', SKYDONATE_PUBLIC_ASSETS. '/css/donation-goal.css');
    }

    public function add_target_sales_meta_box() {
        add_meta_box(
            'target_sales_meta_box',       // Unique ID
            'Donation Goal Target',        // Box title
            [$this, 'target_sales_meta_box_html'],  // Content callback
            'product',                     // Post type
            'side'                         // Context
        );
    }

    public function target_sales_meta_box_html($post) {
        $target_sales = get_post_meta($post->ID, '_target_sales_goal', true);
        $offline_donation = get_post_meta($post->ID, '_offline_donation', true);
        ?>
        <label for="target_sales_goal">Donation Goal Target</label>
        <input type="number" id="target_sales_goal" name="target_sales_goal" value="<?php echo esc_attr($target_sales); ?>" style="width:100%;">
        
        <label for="offline_donation" style="margin-top:10px;display:block;">Offline Donation</label>
        <input type="number" id="offline_donation" name="offline_donation" value="<?php echo esc_attr($offline_donation); ?>" style="width:100%;">
        <?php
    }

    public function save_target_sales_meta_box_data($post_id) {
        if (array_key_exists('target_sales_goal', $_POST)) {
            update_post_meta(
                $post_id,
                '_target_sales_goal',
                sanitize_text_field($_POST['target_sales_goal'])
            );
        }
        if (array_key_exists('offline_donation', $_POST)) {
            update_post_meta(
                $post_id,
                '_offline_donation',
                sanitize_text_field($_POST['offline_donation'])
            );
        }
    }

    public function get_product_total_sales_revenue($product_id) {
        global $wpdb;

        $total_sales = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(oim2.meta_value)
            FROM {$wpdb->prefix}woocommerce_order_items oi
            JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
            JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim2 ON oi.order_item_id = oim2.order_item_id
            JOIN {$wpdb->prefix}posts p ON oi.order_id = p.ID
            WHERE oim.meta_key = '_product_id'
            AND oim.meta_value = %d
            AND oim2.meta_key = '_line_total'
            AND p.post_status = 'wc-completed'
        ", $product_id));

        return $total_sales ? $total_sales : 0;
    }

    public function product_sales_progress_bar_shortcode($atts) {
        global $post;

        if (is_product()) {
            $product_id = $post->ID;
        } else {
            $atts = shortcode_atts(
                [
                    'id' => '', // Product ID
                ],
                $atts,
                'product_sales_progress_bar'
            );
            $product_id = $atts['id'];
        }

        if (!$product_id) {
            return ''; // No product ID provided
        }

        $target_sales = get_post_meta($product_id, '_target_sales_goal', true);
        $offline_donation = get_post_meta($product_id, '_offline_donation', true);

        if (empty($target_sales)) {
            return ''; // No donation goal is set, hide the section
        }

        if (empty($offline_donation)) {
            $offline_donation = 0;
        }

        $total_sales = $this->get_product_total_sales_revenue($product_id);
        $total_raised = $total_sales + $offline_donation;
        $percentage = ($target_sales > 0) ? ($total_raised / $target_sales) * 100 : 0;
        $percentage = min(number_format($percentage, 0), 100); // Cap at 100%

        ob_start();
        ?>
        <span class="goal_left"> £<span id="raised-amount">0</span> </span> <span class="goal_right"> of £<?php echo number_format($target_sales, 0); ?> goal </span>

        <div class="progress-container">    
            <div class="indicator" id="progress-indicator" style="width: 0;">
                <div class="percentage">
                    <?php echo $percentage; ?>%
                </div>
            </div>
        </div>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                animateCounter(<?php echo $total_raised; ?>);
                animateProgressBar(<?php echo $percentage; ?>);
            });

            function animateCounter(finalAmount) {
                let currentAmount = 0;
                const increment = finalAmount / 100;
                const interval = setInterval(() => {
                    currentAmount += increment;
                    document.getElementById('raised-amount').innerText = number_format(currentAmount);
                    if (currentAmount >= finalAmount) {
                        clearInterval(interval);
                        document.getElementById('raised-amount').innerText = number_format(finalAmount);
                    }
                }, 10); // Adjust the speed of the animation
            }

            function animateProgressBar(finalPercentage) {
                let currentPercentage = 0;
                const increment = finalPercentage / 100;
                const interval = setInterval(() => {
                    currentPercentage += increment;
                    document.getElementById('progress-indicator').style.width = currentPercentage + '%';
                    if (currentPercentage >= finalPercentage) {
                        clearInterval(interval);
                        document.getElementById('progress-indicator').style.width = finalPercentage + '%';
                    }
                }, 10); // Adjust the speed of the animation
            }

            function number_format(number) {
                return Number(number.toFixed(0)).toLocaleString('en-UK');
            }
        </script>
        <?php
        return ob_get_clean();
    }
}

new WC_Donation_Goal();
?>
