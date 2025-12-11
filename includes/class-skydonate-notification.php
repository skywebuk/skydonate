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

    public function register_customizer_settings($wp_customize) {
        // Add Section
        $wp_customize->add_section('skydonate_notification_section', [
            'title'       => __('Skydonate Notification', 'skydonate'),
            'description' => __('Customize the Skydonate Notifications style.', 'skydonate'),
            'priority'    => 30,
        ]);

        // Accent Color
        $wp_customize->add_setting('skydonate_notification_accent_color', [
            'default'           => '#2797ff',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'skydonate_notification_accent_color', [
            'label'    => __('Accent Color', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'settings' => 'skydonate_notification_accent_color',
        ]));


        $wp_customize->add_setting('skydonate_notification_title_color', [
            'default'           => '#2797ff',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'skydonate_notification_title_color', [
            'label'    => __('Title Color', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'settings' => 'skydonate_notification_title_color',
        ]));

        // Text Color
        $wp_customize->add_setting('skydonate_notification_text_color', [
            'default'           => '#212830',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'skydonate_notification_text_color', [
            'label'    => __('Text Color', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'settings' => 'skydonate_notification_text_color',
        ]));

        // Background Color
        $wp_customize->add_setting('skydonate_notification_bg_color', [
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'skydonate_notification_bg_color', [
            'label'    => __('Background Color', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'settings' => 'skydonate_notification_bg_color',
        ]));

        // Border Size
        $wp_customize->add_setting('skydonate_notification_border_size', [
            'default'           => 1,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_border_size', [
            'label'       => __('Border Size (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'settings'    => 'skydonate_notification_border_size',
            'input_attrs' => [ 'min' => 0, 'max' => 20, 'step' => 1 ],
        ]);

        // Border Radius
        $wp_customize->add_setting('skydonate_notification_border_radius', [
            'default'           => 5,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_border_radius', [
            'label'       => __('Border Radius (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'settings'    => 'skydonate_notification_border_radius',
            'input_attrs' => [ 'min' => 0, 'max' => 50, 'step' => 1 ],
        ]);

        // Border Color
        $wp_customize->add_setting('skydonate_notification_border_color', [
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);
        $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'skydonate_notification_border_color', [
            'label'    => __('Border Color', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'settings' => 'skydonate_notification_border_color',
        ]));

        // Show Shadow Checkbox
        $wp_customize->add_setting('skydonate_notification_show_shadow', [
            'default'           => false,
            'sanitize_callback' => 'wp_validate_boolean',
        ]);
        $wp_customize->add_control('skydonate_notification_show_shadow', [
            'label'    => __('Show Shadow', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'type'     => 'checkbox',
            'settings' => 'skydonate_notification_show_shadow',
        ]);

        // Button Text
        $wp_customize->add_setting('skydonate_notification_button_text', [
            'default'           => __('Donate', 'skydonate'),
            'sanitize_callback' => 'sanitize_text_field',
        ]);
        $wp_customize->add_control('skydonate_notification_button_text', [
            'label'    => __('Button Text', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'type'     => 'text',
            'settings' => 'skydonate_notification_button_text',
        ]);


        // Position Option (Select)
        $wp_customize->add_setting('skydonate_notification_position', [
            'default'           => 'top',
            'sanitize_callback' => [$this, 'sanitize_position_option'],
        ]);
        $wp_customize->add_control('skydonate_notification_position', [
            'label'    => __('Mobile Position', 'skydonate'),
            'section'  => 'skydonate_notification_section',
            'type'     => 'select',
            'choices'  => [
                'top'    => __('Top', 'skydonate'),
                'bottom' => __('Bottom', 'skydonate'),
            ],
            'settings' => 'skydonate_notification_position',
        ]);

        // Box Width Setting
        $wp_customize->add_setting('skydonate_notification_box_width', [
            'default'           => 360,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_box_width', [
            'label'       => __('Box Width (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'input_attrs' => [
                'min' => 100, // Minimum value
                'max' => 1920, // Maximum value
                'step' => 10, // Step value
            ],
            'description' => __('Enter the width of the notification box in pixels (default: 360px).', 'skydonate'),
        ]);

        // Title Font Size
        $wp_customize->add_setting('skydonate_notification_title_font_size', [
            'default'           => 16,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_title_font_size', [
            'label'       => __('Title Font Size (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'settings'    => 'skydonate_notification_title_font_size',
            'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
        ]);

        // Text Font Size
        $wp_customize->add_setting('skydonate_notification_text_font_size', [
            'default'           => 13,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_text_font_size', [
            'label'       => __('Text Font Size (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'settings'    => 'skydonate_notification_text_font_size',
            'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
        ]);

        // Button Font Size
        $wp_customize->add_setting('skydonate_notification_button_font_size', [
            'default'           => 16,
            'sanitize_callback' => 'absint',
        ]);
        $wp_customize->add_control('skydonate_notification_button_font_size', [
            'label'       => __('Button Font Size (px)', 'skydonate'),
            'section'     => 'skydonate_notification_section',
            'type'        => 'number',
            'settings'    => 'skydonate_notification_button_font_size',
            'input_attrs' => [ 'min' => 8, 'max' => 72, 'step' => 1 ],
        ]);



    }

    // Sanitize position option
    public function sanitize_position_option($value) {
        $valid = ['top', 'bottom'];
        return in_array($value, $valid, true) ? $value : 'top';
    }


    public function customizer_style() {
        // Retrieve theme mod values with default fallback

        $title_color = esc_attr(get_theme_mod('skydonate_notification_title_color', '#2797ff'));
        $accent_color = esc_attr(get_theme_mod('skydonate_notification_accent_color', '#2797ff'));
        $text_color = esc_attr(get_theme_mod('skydonate_notification_text_color', '#212830'));
        $bg_color = esc_attr(get_theme_mod('skydonate_notification_bg_color', '#ffffff'));
        $border_color = esc_attr(get_theme_mod('skydonate_notification_border_color', '#ffffff'));
        $border_radius = absint(get_theme_mod('skydonate_notification_border_radius', 5));
        $border_size = absint(get_theme_mod('skydonate_notification_border_size', 1));
        $title_size = absint(get_theme_mod('skydonate_notification_title_font_size', 16));
        $button_size = absint(get_theme_mod('skydonate_notification_button_font_size', 16));
        $text_size = absint(get_theme_mod('skydonate_notification_text_font_size', 13));
        $box_width = absint(get_theme_mod('skydonate_notification_box_width', 360));
        $shadow_raw = get_theme_mod('skydonate_notification_show_shadow', false);
        $shadow = filter_var( $shadow_raw, FILTER_VALIDATE_BOOLEAN );

        $position = get_theme_mod('skydonate_notification_position', 'top');
    
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