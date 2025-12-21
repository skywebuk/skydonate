<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Skydonate_Notification {
    public function __construct() {
        add_action('wp_head', [$this, 'output_notifications_js']); 
        add_action('customize_register', [$this, 'register_customizer_settings']);
        add_action('wp_head', [$this, 'customizer_style']);
    }

    // Prepare and pass notifications to JavaScript
    public function output_notifications_js() {
        global $wp;
        $post_id = get_the_ID();
        $timing = [];
        $selected_items = [];
        $button_text = get_theme_mod('skydonate_notification_button_text', __('Donate', 'skydonate'));

        // Retrieve the enable_notification meta value
        $enable_notification = get_post_meta($post_id, '_skydonate_enable_notification', true);

        // Check if notifications are enabled
        if ($enable_notification == 'yes') {
            $selected_items = get_post_meta($post_id, '_skydonate_select_donation', true);
            $location_visibility = get_post_meta($post_id, '_skydonate_location_visibility', true);
            $title_visibility = get_post_meta($post_id, '_skydonate_title_visibility', true);
            $emoji_visibility    = get_post_meta($post_id, '_skydonate_emoji', true);
            $timestamp           = get_post_meta($post_id, '_skydonate_timestamp', true);
            $limit               = get_post_meta($post_id, '_skydonate_limit', true);
            $start_date_option   = get_post_meta($post_id, '_skydonate_start_date', false);
            $timing['start_time'] = get_post_meta($post_id, '_skydonate_start_time', true);
            $timing['visible_time'] = get_post_meta($post_id, '_skydonate_visible_time', true);
            $timing['gap_time'] = get_post_meta($post_id, '_skydonate_gap_time', true);
        }else {
            $selected_items = get_option('notification_select_donations', []);
            $emoji_visibility = get_option('enable_emoji_notifications') ? 'yes' : 'no';
            $location_visibility = get_option('enable_location_visibility') ? 'yes' : 'no';
            $title_visibility = get_option('enable_title_visibility') ? 'yes' : 'no';
            $timestamp = get_option('enable_timestamp_display') ? 'yes' : 'no';
            $limit = get_option('notification_limit', 10);
            $start_date_option = get_option('start_date_range');
            $timing['start_time'] = get_option('notifi_start_time', '5000');
            $timing['visible_time'] = get_option('notifi_visible_time', '10000');
            $timing['gap_time'] = get_option('notifi_gap_time', '5000');
            
        }
        
        $supporter_name = get_option('supporter_name_display_style', '');
        $show_urls = get_option('show_element_urls', '');
        $hide_urls = get_option('hide_element_urls', '');
        
        
        if (empty($selected_items)) return;

        // Determine the start date based on the selected option
        switch ($start_date_option) {
            case '3': // Last 3 Days
                $start_date = date('Y-m-d', strtotime('-3 days'));
                break;
            case '7': // Last 7 Days
                $start_date = date('Y-m-d', strtotime('-7 days'));
                break;
            case '14': // Last 14 Days
                $start_date = date('Y-m-d', strtotime('-14 days'));
                break;
            case '0': // Show All
            default:
                $start_date = null; // No date restriction
                break;
        }
        

        $orders = Skydonate_Functions::get_orders_ids_by_product_id($selected_items, ['wc-completed'], $limit, $start_date);
        
        // WooCommerce country codes and names
        $countries = WC()->countries->get_countries();
        $count = 0;
        
        // My code was these
        
        
        if (!empty($show_urls) || !empty($hide_urls)) {
            // Get the show and hide URL settings, ensuring they're arrays
            $show_urls = !empty($show_urls) ? explode("\n", $show_urls) : [];
            $hide_urls = !empty($hide_urls) ? explode("\n", $hide_urls) : [];
            
            // Sanitize URLs and remove trailing slashes
            $sanitize_urls = function($urls) {
                return array_filter(array_map(function($url) {
                    return rtrim(trim($url), '/');
                }, $urls));
            };
        
            $show_urls = $sanitize_urls($show_urls);
            $hide_urls = $sanitize_urls($hide_urls);
            
            // Get current page URL
            global $wp;
            $current_url = rtrim(home_url(add_query_arg([], $wp->request)), '/');
            
            // Function to match URLs with wildcard support
            $matches_with_wildcard = function($pattern, $url) {
                // Escape special regex characters in the URL except for the *
                $pattern = preg_quote($pattern, '/');
                // Replace the * with a regex equivalent that matches any string (including empty string)
                $pattern = str_replace('\*', '.*', $pattern);
                // Add the anchors to ensure it's an exact match
                $pattern = '/^' . $pattern . '$/';
                
                // Check if the current URL matches the pattern
                return preg_match($pattern, $url);
            };
            
            // Handle show URLs logic
            if (!empty($show_urls)) {
                $show_match = false;
                foreach ($show_urls as $show_url) {
                    if ($matches_with_wildcard($show_url, $current_url)) {
                        $show_match = true;
                        break; // Stop when a match is found
                    }
                }
                if (!$show_match) {
                    return; // Stop execution if no match is found in the show list
                }
            }
            
            // Handle hide URLs logic
            if (!empty($hide_urls)) {
                foreach ($hide_urls as $hide_url) {
                    if ($matches_with_wildcard($hide_url, $current_url)) {
                        return; // Stop execution if the current URL matches any in the hide list
                    }
                }
            }
        }
        

        $notifications = [];
        foreach ($orders as $order_id) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                foreach ( $order->get_items() as $item_id => $item ) {
                    $is_anonymous = get_post_meta($order->get_id(), '_anonymous_donation', true);
                    $product = $item->get_product();
                    if (!$product || !in_array($product->get_id(), $selected_items)) {
                        continue;
                    }
                    // Start building notification output
                    $output = '<div class="skydonate-notification">';
                    $output .= '<button class="close"><i class="fa-solid fa-xmark"></i></button>';
                    $output .= '<a href="' . esc_url($product->get_permalink()) . '" class="donate-button">' . esc_html($button_text) . '</a>';
                    $output .= '<div class="name">';
                    // Choose supporter name display format
                    if ($is_anonymous === '1') {
                        $output .= __('Anonymous', 'skydonate');
                    }elseif ($supporter_name === 'full_name') {
                        $output .= esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                    } elseif ($supporter_name === 'first_name_l') {
                        $output .= esc_html($order->get_billing_first_name() . ' ' . substr($order->get_billing_last_name(), 0, 1) . '.');
                    } elseif ($supporter_name === 'first_name') {
                        $output .= esc_html($order->get_billing_first_name());
                    } else {
                        $output .= __('Anonymous', 'skydonate');
                    }
                    // Display donation amount
                    $output .= ' ' . __('donated', 'skydonate') . ' ';
                    $output .= '<span>' . esc_html(get_woocommerce_currency_symbol($order->get_currency()) . number_format((float)$item->get_total(), 0)) . '</span>';
                    if($emoji_visibility == 'yes'){
                        // Add a random emoji
                        $emojis = [ '🎉', '💖', '👏', '🌟', '❤️', '🎊', '🫶', '🙏', '💸', '💰', '😊', '🌈', '💐', '🥰', '🎁', '🙌', '🤗', '🕊️', '💞', '💎', '🔥', '🎈', '🌺', '💫', '🎶', '🤝', '🏆', '💝', '✨', '🏅' ];
                        $random_emoji = $emojis[array_rand($emojis)];    
                        $output .= ' ' . $random_emoji;
                    }
                    $output .= '</div>';
                    if($title_visibility == 'yes'){
                        $output .= '<div class="name"><small>'.$product->get_title().'</small></div>';
                    }
                    if ($location_visibility == 'yes') {
                        // Get country code and retrieve full country name
                        $billing_country_code = strtoupper(sanitize_text_field($order->get_billing_country()));
                        $billing_city = sanitize_text_field($order->get_billing_city());
                        
                        // Ensure countries array is well-defined and fetch country name
                        $billing_country_name = isset($countries[$billing_country_code]) 
                            ? preg_replace('/\s*\(.*?\)$/', '', $countries[$billing_country_code]) // Remove (Country Code)
                            : __('Unknown Country', 'skydonate');
                    
                        $output .= '<div class="location"><span class="flag-icon flag-icon-' . esc_attr(strtolower($billing_country_code)) . '"></span> ';
                        $output .= esc_html($billing_city) . ', ' . esc_html($billing_country_name) . '</div>';
                    }                
                    if($timestamp == 'yes'){
                        $output .= '<div class="time">' . esc_html(human_time_diff(strtotime($order->get_date_created()), time())) . ' ' . __('ago', 'skydonate') . '</div>';
                    }
                    $output .= '</div>';
                    $notifications[] = $output;
                    $count++;
                }
            }
        }
        
        if ($notifications) {
            // Shuffle the notifications array to display them randomly
            echo '<script>
            var notifications = ' . json_encode($notifications) . ';
            var limit = ' . $limit . ';
            var start_time = ' . $timing['start_time'] . ';
            var visible_time = ' . $timing['visible_time'] . ';
            var gap_time = ' . $timing['gap_time'] . ';
            jQuery(document).ready(function($) {
                if (typeof notifications !== "undefined" && notifications.length > 0) {
                    let index = 0;
                    let count = 0; // Track the number of notifications shown
        
                    function showNotification() {
                        // Stop if the limit is reached
                        if (count >= limit) return;
        
                        const html = notifications[index];
                        $("body").append(html);
        
                        // Close button handler
                        $(document).on("click", ".skydonate-notification .close", function() {
                            $(this).closest(".skydonate-notification").fadeOut(300, function() {
                                $(this).remove();
                            });
                        });
        
                        // Set a timeout for fading out the notification
                        setTimeout(function() {
                            $(".skydonate-notification").fadeOut(300, function() {
                                $(this).remove();
                                // Introduce a 10-second delay before showing the next notification
                                setTimeout(function() {
                                    count++; // Increment notification counter
                                    index = (index + 1) % notifications.length; // Loop back to the first notification
                                    showNotification();
                                }, visible_time); // 10 seconds rest after fade-out
                            });
                        }, gap_time); // Display each notification for 10 seconds
                    }
        
                    // Start showing notifications after 10 seconds
                    setTimeout(function() {
                        showNotification();
                    }, start_time); // 10 seconds delay before starting notifications
                }
            });
            </script>';
        }
        
    }

    /**
     * Get customizer settings configuration
     * Centralized config makes it easy to add/modify settings
     */
    private function get_customizer_config() {
        return [
            // Color settings
            'accent_color' => [
                'type'     => 'color',
                'label'    => __('Accent Color', 'skydonate'),
                'default'  => '#2797ff',
            ],
            'title_color' => [
                'type'     => 'color',
                'label'    => __('Title Color', 'skydonate'),
                'default'  => '#2797ff',
            ],
            'text_color' => [
                'type'     => 'color',
                'label'    => __('Text Color', 'skydonate'),
                'default'  => '#212830',
            ],
            'bg_color' => [
                'type'     => 'color',
                'label'    => __('Background Color', 'skydonate'),
                'default'  => '#ffffff',
            ],
            'border_color' => [
                'type'     => 'color',
                'label'    => __('Border Color', 'skydonate'),
                'default'  => '#ffffff',
            ],
            // Number settings
            'border_size' => [
                'type'        => 'number',
                'label'       => __('Border Size (px)', 'skydonate'),
                'default'     => 1,
                'input_attrs' => [ 'min' => 0, 'max' => 20, 'step' => 1 ],
            ],
            'border_radius' => [
                'type'        => 'number',
                'label'       => __('Border Radius (px)', 'skydonate'),
                'default'     => 5,
                'input_attrs' => [ 'min' => 0, 'max' => 50, 'step' => 1 ],
            ],
            'box_width' => [
                'type'        => 'number',
                'label'       => __('Box Width (px)', 'skydonate'),
                'default'     => 360,
                'input_attrs' => [ 'min' => 100, 'max' => 1920, 'step' => 10 ],
                'description' => __('Enter the width of the notification box in pixels (default: 360px).', 'skydonate'),
            ],
            'title_font_size' => [
                'type'        => 'number',
                'label'       => __('Title Font Size (px)', 'skydonate'),
                'default'     => 16,
                'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
            ],
            'text_font_size' => [
                'type'        => 'number',
                'label'       => __('Text Font Size (px)', 'skydonate'),
                'default'     => 13,
                'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
            ],
            'button_font_size' => [
                'type'        => 'number',
                'label'       => __('Button Font Size (px)', 'skydonate'),
                'default'     => 16,
                'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
            ],
            // Checkbox
            'show_shadow' => [
                'type'    => 'checkbox',
                'label'   => __('Show Shadow', 'skydonate'),
                'default' => false,
            ],
            // Text
            'button_text' => [
                'type'    => 'text',
                'label'   => __('Button Text', 'skydonate'),
                'default' => __('Donate', 'skydonate'),
            ],
            // Select
            'position' => [
                'type'    => 'select',
                'label'   => __('Mobile Position', 'skydonate'),
                'default' => 'top',
                'choices' => [
                    'top'    => __('Top', 'skydonate'),
                    'bottom' => __('Bottom', 'skydonate'),
                ],
            ],
        ];
    }

    public function register_customizer_settings($wp_customize) {
        $section_id = 'skydonate_notification_section';
        $prefix = 'skydonate_notification_';

        // Add Section
        $wp_customize->add_section($section_id, [
            'title'       => __('Skydonate Notification', 'skydonate'),
            'description' => __('Customize the Skydonate Notifications style.', 'skydonate'),
            'priority'    => 30,
        ]);

        // Register all settings from config
        foreach ( $this->get_customizer_config() as $key => $config ) {
            $setting_id = $prefix . $key;
            $type = $config['type'];

            // Determine sanitize callback based on type
            $sanitize = match($type) {
                'color'    => 'sanitize_hex_color',
                'number'   => 'absint',
                'checkbox' => 'wp_validate_boolean',
                'text'     => 'sanitize_text_field',
                'select'   => [ $this, 'sanitize_position_option' ],
                default    => 'sanitize_text_field',
            };

            // Register setting
            $wp_customize->add_setting($setting_id, [
                'default'           => $config['default'],
                'sanitize_callback' => $sanitize,
            ]);

            // Build control args
            $control_args = [
                'label'    => $config['label'],
                'section'  => $section_id,
                'settings' => $setting_id,
            ];

            // Add type-specific args
            if ( isset($config['input_attrs']) ) {
                $control_args['input_attrs'] = $config['input_attrs'];
            }
            if ( isset($config['description']) ) {
                $control_args['description'] = $config['description'];
            }
            if ( isset($config['choices']) ) {
                $control_args['choices'] = $config['choices'];
            }
            if ( $type !== 'color' ) {
                $control_args['type'] = $type;
            }

            // Register control (color uses special class)
            if ( $type === 'color' ) {
                $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, $setting_id, $control_args));
            } else {
                $wp_customize->add_control($setting_id, $control_args);
            }
        }
    }

    // Sanitize position option
    public function sanitize_position_option($value) {
        $valid = ['top', 'bottom'];
        return in_array($value, $valid, true) ? $value : 'top';
    }


    /**
     * Get a customizer value with default from config
     * Handles empty string values by returning the default instead
     */
    private function get_setting($key) {
        $config = $this->get_customizer_config();
        if (!isset($config[$key])) {
            return '';
        }
        $default = $config[$key]['default'];
        $value = get_theme_mod('skydonate_notification_' . $key, $default);

        // If value is empty string (but not false for checkboxes), use default
        // This handles the case where WordPress saves empty values to the database
        if ($value === '' || $value === null) {
            return $default;
        }

        return $value;
    }

    public function customizer_style() {
        // Retrieve theme mod values using centralized config defaults
        $title_color   = esc_attr($this->get_setting('title_color'));
        $accent_color  = esc_attr($this->get_setting('accent_color'));
        $text_color    = esc_attr($this->get_setting('text_color'));
        $bg_color      = esc_attr($this->get_setting('bg_color'));
        $border_color  = esc_attr($this->get_setting('border_color'));
        $border_radius = absint($this->get_setting('border_radius'));
        $border_size   = absint($this->get_setting('border_size'));
        $title_size    = absint($this->get_setting('title_font_size'));
        $button_size   = absint($this->get_setting('button_font_size'));
        $text_size     = absint($this->get_setting('text_font_size'));
        $box_width     = absint($this->get_setting('box_width'));
        $shadow        = filter_var($this->get_setting('show_shadow'), FILTER_VALIDATE_BOOLEAN);
        $position      = $this->get_setting('position');
    
        // Inline styles
        ?>
        <style>
            .skydonate-notification {
                <?php if (!empty($border_radius)): ?>
                    border-radius: <?php echo $border_radius; ?>px;
                <?php endif; ?>

                <?php if (!empty($bg_color)): ?>
                    background: <?php echo $bg_color; ?>;
                <?php endif; ?>

                <?php if (!empty($text_color)): ?>
                    color: <?php echo $text_color; ?>;
                <?php endif; ?>

                <?php if (!empty($border_size) && !empty($border_color)): ?>
                    border: <?php echo $border_size; ?>px solid <?php echo $border_color; ?>;
                <?php endif; ?>

                <?php if ($shadow): ?>
                    box-shadow: rgba(0, 0, 0, 0.2) 0px 2px 10px;
                <?php else: ?>
                    box-shadow: none;
                <?php endif; ?>

                <?php if (!empty($box_width)): ?>
                    width: <?php echo $box_width; ?>px;
                <?php endif; ?>
            }

            .skydonate-notification .name {
                <?php if (!empty($accent_color)): ?>
                    color: <?php echo $accent_color; ?>;
                <?php endif; ?>

                <?php if ($title_size > 0): ?>
                    font-size: <?php echo $title_size; ?>px;
                <?php endif; ?>

                line-height: 1.2em;
            }

            .skydonate-notification .name small {
                <?php if (!empty($title_color)): ?>
                    color: <?php echo $title_color; ?>;
                <?php endif; ?>
            }

            .skydonate-notification .donate-button {
                <?php if (!empty($accent_color)): ?>
                    border: 1px solid <?php echo $accent_color; ?>;
                    color: <?php echo $accent_color; ?>;
                <?php endif; ?>

                <?php if ($button_size > 0): ?>
                    font-size: <?php echo $button_size; ?>px;
                <?php endif; ?>
            }

            .skydonate-notification .donate-button:hover {
                <?php if (!empty($accent_color)): ?>
                    background-color: <?php echo $accent_color; ?>;
                    color: #ffffff; /* Optional for better readability on hover */
                <?php endif; ?>
            }

            .skydonate-notification strong {
                <?php if (!empty($text_color)): ?>
                    color: <?php echo $text_color; ?>;
                <?php endif; ?>
            }

            .skydonate-notification .time,
            .skydonate-notification .location {
                <?php if ($text_size > 0): ?>
                    font-size: <?php echo $text_size; ?>px;
                <?php endif; ?>
            }

            <?php if (!empty($position) && $position == 'top'): ?>
                @media screen and (max-width: 768px) {
                    .skydonate-notification {
                        bottom: auto;
                        top: 20px;
                    }
                }
            <?php endif; ?>
        </style>

        <?php
    }
    
}

new Skydonate_Notification();