<?php
if (!defined('ABSPATH')) exit;

$register_text_replacements = get_option('register_text_replacements');
$init_woocommerce_customizations = get_option('init_woocommerce_customizations');
$init_order_postcode_search = get_option('init_order_postcode_search');
$init_menu_label_changes = get_option('init_menu_label_changes');
$init_stripe_order_meta_modifications = get_option('init_stripe_order_meta_modifications');
$init_guest_checkout_for_existing_customers = get_option('init_guest_checkout_for_existing_customers');
$add_checkout_password_note = get_option('add_checkout_password_note');
$init_project_column_for_orders = get_option('init_project_column_for_orders');
$init_email_product_name_replacement = get_option('init_email_product_name_replacement');
$customize_woocommerce_login_message = get_option('customize_woocommerce_login_message');
$init_checkout_email_typo_correction = get_option('init_checkout_email_typo_correction');
$init_guest_checkout_data_saver = get_option('init_guest_checkout_data_saver');

// Remove specific meta data from Stripe Order Meta
function modify_stripe_order_meta_data($meta_data, $order) {
    // Output the meta data for debugging
    foreach ($meta_data as $key => $value) {
        // Check if the key matches the pattern 'product_{product_id}'
        if (preg_match('/^product_\d+$/', $key)) {
            unset($meta_data[$key]);
        }
    }

    // Remove other specific meta data
    unset($meta_data['partner']);
    unset($meta_data['product']);
    unset($meta_data['user_id']);

    // Add the replacement meta data
    $meta_data['description'] = 'Donation to Global Helping Hands';

    return $meta_data;
}

/**
 * Initialize payment gateway hooks on init to avoid early translation loading
 * This fixes WordPress 6.7+ translation timing requirements
 */
function skydonate_init_payment_gateway_hooks() {
    add_filter('wc_stripe_order_meta_data', 'modify_stripe_order_meta_data', 10, 2);

    add_filter('wc_ppcp_get_order_item', function($item, $order_item){
        // Set the item name to 'Donation to Global Helping Hands'
        $item->setName('Donation to Global Helping Hands');
        return $item;
    }, 10, 2);
}
add_action( 'init', 'skydonate_init_payment_gateway_hooks', 1 );

if ($register_text_replacements == 1) {
    function skydonate_start_buffer() {
        ob_start();
    }
    function skydonate_end_buffer() {
        $html = ob_get_clean();
        $html = str_replace('Search for posts', 'Search for Appeals', $html);
        $html = str_replace('Browse products', 'Browse projects', $html);
        $html = str_replace('No order has been made yet.', 'No donations has been made yet.', $html);
        $html = str_replace('Zip / Postal Code', 'Postcode', $html);
        $html = str_replace('Shopping cart', 'Donation Basket', $html);
        $html = str_replace('If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'If you have donated with us before, please enter your details below. If you are a new donor, please proceed to the Billing section.', $html);
        $html = str_replace('Returning customer?', 'Returning donor?', $html);
        $html = str_replace('Thank you. Your order has been received', 'Thank you. Your donation has been received', $html);
        $html = str_replace('Order number', 'Donation ID', $html);
        $html = str_replace('Order details', 'Donation details', $html);
        $html = str_replace('Order', 'My Donation', $html);
        $html = str_replace('Hello,', 'Salaam,', $html);
        $html = str_replace('Hello', 'Salaam,', $html);
        $html = str_replace('Orders', 'Donations History', $html);
        $html = str_replace('Subscription', 'Recurring Donation', $html);
        $html = str_replace('Dashboard', 'Donor Dashboard', $html);
        $html = str_replace('Donor Donor Dashboard', 'Donor Dashboard', $html);
        $html = str_replace('recent orders', 'recent donations', $html);
        $html = str_replace('Customer', 'Donor', $html);
        $html = str_replace('Donation number:', 'Donation ID:', $html);
        $html = str_replace('shipping and billing addresses', 'billing addresse', $html);
        $html = str_replace('Product short description', 'Donation short description', $html);
        echo $html;
    }
    add_action('wp_head', 'skydonate_start_buffer');
    add_action('wp_footer', 'skydonate_end_buffer');

    function skydonate_order_button_text() {
        return 'PROCESS PAYMENT';
    }
    add_filter('woocommerce_order_button_text', 'skydonate_order_button_text');

    function skydonate_single_add_to_cart_text() {
        return __('Donate and Support', 'woocommerce');
    }
    add_filter('woocommerce_product_single_add_to_cart_text', 'skydonate_single_add_to_cart_text');

    function skydonate_archive_add_to_cart_text() {
        return __('Donate and Support', 'woocommerce');
    }
    add_filter('woocommerce_product_add_to_cart_text', 'skydonate_archive_add_to_cart_text');

    function skydonate_translate_product($translated, $text, $domain) {
        if ($domain === 'woocommerce') {
            if ($translated === 'Product') $translated = 'Project';
            if ($translated === 'Products') $translated = 'Projects';
        }
        return $translated;
    }
    add_filter('gettext', 'skydonate_translate_product', 20, 3);
    add_filter('ngettext', 'skydonate_translate_product', 20, 3);

    function skydonate_frontend_js() { ?>
        <script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                const updateCartMessages = () => {
                    const emptyCartMessage = document.querySelector('.woocommerce-mini-cart__empty-message');
                    if (emptyCartMessage && emptyCartMessage.textContent.trim() === "No products in the basket.") {
                        emptyCartMessage.textContent = "Browse and explore our projects";
                    }
                };
                const replaceText = (node) => {
                    if (node.nodeType === Node.TEXT_NODE) {
                        node.nodeValue = node.nodeValue.replace(/\bshop\b/gi, 'projects');
                    } else {
                        node.childNodes.forEach(replaceText);
                    }
                };
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'childList' || mutation.type === 'subtree') {
                            updateCartMessages();
                            mutation.addedNodes.forEach((node) => replaceText(node));
                        }
                    });
                });
                observer.observe(document.body, { childList: true, subtree: true });
                updateCartMessages();
                replaceText(document.body);
            });
        </script><?php
    }
    add_action('wp_footer', 'skydonate_frontend_js', 100);

    function skydonate_admin_order_text($translated, $text, $domain) {
        if (is_admin() && current_user_can('manage_woocommerce')) {
            $screen_id = isset($_GET['page']) ? $_GET['page'] : '';
            if (strpos($screen_id, 'wc') !== false || (isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order')) {
                $words = array(
                    'Order' => 'Donation',
                    'Order #' => 'Donation',
                    'order' => 'donation',
                    'Product updated' => 'Project updated.',
                    'View Product' => 'View Project',
                    'Orders' => 'Donations',
                    'orders' => 'donations',
                    'View products' => 'Browse projects',
                    'net sales this month' => 'Total donations this month',
                    'top seller this month' => 'Top appeal this month',
                    '(sold' => '(number of donations',
                    'Buy Now' => 'Checkout'
                );
                $translated = str_ireplace(array_keys($words), $words, $translated);
            }
        }
        return $translated;
    }
    add_filter('gettext', 'skydonate_admin_order_text', 20, 3);
    add_filter('ngettext', 'skydonate_admin_order_text', 20, 3);

    function skydonate_set_display_name($user_id) {
        $data = get_userdata($user_id);
        $display_name = !empty($data->first_name) ? $data->first_name : 'Donor';
        wp_update_user(['ID' => $user_id, 'display_name' => $display_name]);
    }
    add_action('user_register', 'skydonate_set_display_name');

    function skydonate_email_order_donation($translated, $text, $domain) {
        if ($domain === 'woocommerce' && strpos($translated, 'Order #') !== false) {
            $translated = str_replace('Order #', 'Donation #', $translated);
        }
        return $translated;
    }
    add_filter('gettext', 'skydonate_email_order_donation', 999, 3);
    add_filter('ngettext', 'skydonate_email_order_donation', 999, 3);

    function skydonate_replace_order_donation($translated, $text, $domain) {
        $map = array(
            'Order' => 'Donation',
            'Orders' => 'Donations',
            'order' => 'donation',
            'orders' => 'donations'
        );
        if (isset($map[$text])) return $map[$text];
        return $translated;
    }
    add_filter('gettext', 'skydonate_replace_order_donation', 20, 3);
    add_filter('ngettext', function($translation, $single, $plural, $number, $domain) {
        $map = array(
            'Order' => ['Donation', 'Donations'],
            'order' => ['donation', 'donations']
        );
        if (isset($map[$single])) {
            $translation = ($number == 1) ? $map[$single][0] : $map[$single][1];
        }
        return $translation;
    }, 20, 5);

    add_filter('woocommerce_email_order_title', function($title) {
        return str_replace('Order', 'Donation', $title);
    });
}

if ($init_woocommerce_customizations == 1) {

    add_filter('woocommerce_get_order_item_totals', 'skydonate_remove_cart_subtotal', 10, 2);
    function skydonate_remove_cart_subtotal($totals, $order) {
        if (isset($totals['cart_subtotal'])) {
            unset($totals['cart_subtotal']);
        }
        return $totals;
    }

    add_filter('woocommerce_cart_calculate_fees', 'skydonate_hide_recurring_totals', 10, 1);
    function skydonate_hide_recurring_totals($cart) {
        if (!empty($cart->recurring_cart_key)) {
            remove_action('woocommerce_cart_totals_after_order_total', ['WC_Subscriptions_Cart', 'display_recurring_totals'], 10);
            remove_action('woocommerce_review_order_after_order_total', ['WC_Subscriptions_Cart', 'display_recurring_totals'], 10);
        }
    }

    add_filter('woocommerce_checkout_fields', 'skydonate_remove_checkout_username');
    function skydonate_remove_checkout_username($fields) {
        unset($fields['account']['account_username']);
        return $fields;
    }

    add_filter('woocommerce_new_customer_data', 'skydonate_set_email_as_username');
    function skydonate_set_email_as_username($new_customer_data) {
        if (isset($new_customer_data['user_email'])) {
            $new_customer_data['user_login'] = $new_customer_data['user_email'];
        }
        return $new_customer_data;
    }

    add_filter('woocommerce_registration_errors', 'skydonate_clean_registration_errors', 10, 3);
    function skydonate_clean_registration_errors($errors, $sanitized_user_login, $user_email) {
        if (isset($_POST['username'])) {
            unset($_POST['username']);
        }
        return $errors;
    }

    add_filter('woocommerce_default_address_fields', 'skydonate_remove_address_username');
    function skydonate_remove_address_username($address_fields) {
        unset($address_fields['username']);
        return $address_fields;
    }

    add_action('woocommerce_register_form_start', 'skydonate_hide_register_username_field');
    function skydonate_hide_register_username_field() {
        ?>
        <style>
            #reg_username_field {
                display: none;
            }
        </style>
        <?php
    }

    add_filter('woocommerce_product_data_tabs', 'skydonate_remove_product_tabs');
    function skydonate_remove_product_tabs($tabs) {
        unset($tabs['inventory']);
        unset($tabs['shipping']);
        unset($tabs['linked_product']);
        return $tabs;
    }

    add_action('admin_head', 'skydonate_hide_product_panels');
    function skydonate_hide_product_panels() {
        ?>
        <style>
            #inventory_product_data,
            #shipping_product_data,
            #linked_product_data {
                display: none;
            }
        </style>
        <?php
    }

    add_action('admin_head', 'skydonate_hide_product_date');
    function skydonate_hide_product_date() {
        echo '<style>.postbox-header { display: none; }</style>';
    }

    add_action('add_meta_boxes', 'skydonate_remove_product_descriptions', 99);
    function skydonate_remove_product_descriptions() {
        remove_post_type_support('product', 'editor');
    }

    remove_action('woocommerce_order_details_after_order_table', 'woocommerce_order_again_button');
}

if ($init_order_postcode_search == 1) {
    add_action('restrict_manage_posts', 'skydonate_add_order_postcode_search');
    function skydonate_add_order_postcode_search() {
        global $typenow;
        if ($typenow === 'shop_order') { ?>
            <input type="text" name="postcode" id="postcode" placeholder="<?php _e('Search by Postcode', 'your-text-domain'); ?>" value="<?php echo isset($_GET['postcode']) ? esc_attr($_GET['postcode']) : ''; ?>" />
        <?php }
    }
    add_action('pre_get_posts', 'skydonate_filter_orders_by_postcode');
    function skydonate_filter_orders_by_postcode($query) {
        global $pagenow, $typenow;
        if ($pagenow === 'edit.php' && $typenow === 'shop_order' && !empty($_GET['postcode'])) {
            $postcode = sanitize_text_field($_GET['postcode']);
            $query->set('meta_query', [[
                'key'     => '_billing_postcode',
                'value'   => $postcode,
                'compare' => 'LIKE'
            ]]);
        }
    }
}

if ($init_menu_label_changes == 1) {
    add_filter('woocommerce_register_post_type_product', 'skydonate_change_product_labels');
    function skydonate_change_product_labels($args) {
        $args['labels']['name'] = 'Donation Forms';
        $args['labels']['singular_name'] = 'Donation Form';
        $args['labels']['menu_name'] = 'Donation Forms';
        $args['labels']['all_items'] = 'Donation Forms';
        $args['labels']['add_new'] = 'Add Donation Form';
        $args['labels']['add_new_item'] = 'Add New Donation Form';
        $args['labels']['edit_item'] = 'Edit Donation Form';
        $args['labels']['new_item'] = 'New Donation Form';
        $args['labels']['view_item'] = 'View Donation Form';
        $args['labels']['search_items'] = 'Search Donation Forms';
        $args['labels']['not_found'] = 'No Donation Forms found';
        $args['labels']['not_found_in_trash'] = 'No Donation Forms found in Trash';
        $args['labels']['parent_item_colon'] = 'Parent Donation Form:';
        return $args;
    }
    function skydonate_change_menu_items($menu) {
        if (empty($menu)) return $menu;
        foreach ($menu as $key => $value) {
            if ($menu[$key][0] === 'WooCommerce') {
                $menu[$key][0] = 'Donations';
            }
            if ($menu[$key][0] === 'Orders') {
                $menu[$key][0] = 'Donations';
            }
            if ($menu[$key][0] === 'WooCommerce Status') {
                $menu[$key][0] = 'Donation status';
            }
            if ($menu[$key][0] === 'Order') {
                $menu[$key][0] = 'Donation';
            }
            if ($menu[$key][0] === 'dontion_id') {
                $menu[$key][0] = 'order_id';
            }
        }
        return $menu;
    }
    add_filter('custom_menu_order', 'skydonate_change_menu_items');
    add_filter('menu_order', 'skydonate_change_menu_items');
    function skydonate_change_submenu_labels() {
        global $menu, $submenu;
        foreach ($menu as $key => $value) {
            if ($menu[$key][0] === 'WooCommerce') {
                $menu[$key][0] = 'Donations';
            }
        }
        if (isset($submenu['woocommerce'])) {
            foreach ($submenu['woocommerce'] as $sub_key => $sub_value) {
                if ($submenu['woocommerce'][$sub_key][0] === 'Orders') {
                    $submenu['woocommerce'][$sub_key][0] = 'Donations';
                }
            }
        }
    }
    add_action('admin_menu', 'skydonate_change_submenu_labels');
}

if ($init_stripe_order_meta_modifications == 1) {
    function skydonate_allow_order_payment_without_login($allcaps, $caps, $args) {
        if (isset($caps[0]) && $caps[0] === 'pay_for_order') {
            $order_id = isset($args[2]) ? $args[2] : null;
            $order    = wc_get_order($order_id);
            if (!$order_id) {
                $allcaps['pay_for_order'] = true;
                return $allcaps;
            }
            if ($order) {
                $user    = $order->get_user();
                $user_id = $user ? $user->ID : 0;
                if ($user_id === $order->get_user_id() || !$order->get_user_id()) {
                    $allcaps['pay_for_order'] = true;
                }
            }
        }
        return $allcaps;
    }
    add_filter('user_has_cap', 'skydonate_allow_order_payment_without_login', 10, 3);
    function skydonate_checkout_existing_customer_login($data) {
        $email = isset($data['billing_email']) ? $data['billing_email'] : '';
        if (!is_user_logged_in() && $email && email_exists($email)) {
            $user = get_user_by('email', $email);
            if ($user) {
                $user_id = $user->ID;
                wc_set_customer_auth_cookie($user_id);
                if (!session_id()) {
                    session_start();
                }
                $_SESSION['skydonate_flag'] = "133";
                $_SESSION['skydonate_user'] = $user_id;
            }
        }
        return $data;
    }
    add_filter('woocommerce_checkout_posted_data', 'skydonate_checkout_existing_customer_login', 10, 1);
    function skydonate_clear_user_session_on_new_order($order_id) {
        if (!empty($_SESSION['skydonate_flag']) && $_SESSION['skydonate_flag'] == 133) {
            nocache_headers();
            wp_clear_auth_cookie();
            if (!empty($_SESSION['skydonate_user'])) {
                $session = WP_Session_Tokens::get_instance($_SESSION['skydonate_user']);
                $session->destroy_all();
            }
            $_SESSION['skydonate_flag'] = '';
            $_SESSION['skydonate_user'] = '';
        }
    }
    add_action('woocommerce_new_order', 'skydonate_clear_user_session_on_new_order');
}

if ($init_guest_checkout_for_existing_customers == 1) {
    function skydonate_url_product_attribute_option_shortcode($atts) {
        $atts = shortcode_atts(array(
            'attribute' => ''
        ), $atts, 'url_product_attribute_option');

        $query_param_key = $atts['attribute'];

        if (!empty($_GET[$query_param_key])) {
            $attribute_value           = sanitize_text_field($_GET[$query_param_key]);
            $formatted_attribute_value = str_replace('-', ' ', $attribute_value);
            return $formatted_attribute_value;
        }

        return 'None';
    }
    add_shortcode('url_product_attribute_option', 'skydonate_url_product_attribute_option_shortcode');
    function skydonate_enqueue_variation_description_scripts() {
        if (function_exists('is_product') && is_product()) {
            wp_enqueue_script(
                'skydonate-ajax-variation-description',
                get_template_directory_uri() . '/js/ajax-variation-description.js',
                array('jquery'),
                null,
                true
            );
            wp_localize_script('skydonate-ajax-variation-description', 'skydonate_ajax_var_desc', array(
                'ajax_url' => admin_url('admin-ajax.php'),
            ));
        }
    }
    add_action('wp_enqueue_scripts', 'skydonate_enqueue_variation_description_scripts');
    function skydonate_variation_description_display_shortcode() {
        ob_start(); ?>
        <div id="variation_description_display"></div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.variations_form').on('found_variation', function(event, variation) {
                if (variation) {
                    $.post(skydonate_ajax_var_desc.ajax_url, {
                        action: 'skydonate_fetch_variation_description',
                        variation_id: variation.variation_id
                    }, function(response) {
                        $('#variation_description_display').html(response);
                    });
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
    add_shortcode('variation_description_display', 'skydonate_variation_description_display_shortcode');
    function skydonate_fetch_variation_description() {
        $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;

        if ($variation_id) {
            $variation = new WC_Product_Variation($variation_id);
            if ($variation && $variation->exists()) {
                $description = $variation->get_description();
                echo $description ? $description : 'No description available for this variation';
            } else {
                echo 'Variation not found';
            }
        } else {
            echo 'No variation ID provided';
        }

        wp_die();
    }
    add_action('wp_ajax_skydonate_fetch_variation_description', 'skydonate_fetch_variation_description');
    add_action('wp_ajax_nopriv_skydonate_fetch_variation_description', 'skydonate_fetch_variation_description');
    function skydonate_replace_loop_add_to_cart_button() {
        remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
        add_action('woocommerce_after_shop_loop_item', 'skydonate_custom_view_product_button', 10);
    }
    add_action('wp', 'skydonate_replace_loop_add_to_cart_button');
    function skydonate_custom_view_product_button() {
        global $product;
        $link = get_permalink($product->get_id());
        echo '<a href="' . esc_url($link) . '" class="button view_product_button">Donate and Support</a>';
    }
    function skydonate_replace_add_to_cart_with_donate() {
        if (is_shop() || is_product_category()) { ?>
            <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                let buttons = document.querySelectorAll('.add_to_cart_button');
                buttons.forEach(function(button) {
                    let product = button.closest('.product');
                    if (product) {
                        let productLink = product.querySelector('a').href;
                        let newButton   = document.createElement('a');
                        newButton.href  = productLink;
                        newButton.textContent = 'DONATE NOW';
                        newButton.className   = 'button view_product_button';
                        button.replaceWith(newButton);
                    }
                });
            });
            </script>
        <?php }
    }
    add_action('wp_footer', 'skydonate_replace_add_to_cart_with_donate', 100);
}

if ($add_checkout_password_note == 1) {
    add_action('wp_footer', 'skydonate_add_password_note_script');
    function skydonate_add_password_note_script() {
        if (is_checkout()) { ?>
            <script type="text/javascript">
                jQuery(document).ready(function($){
                    function insertPasswordNote() {
                        var passwordField = $('input#account_password');
                        if (passwordField.length && !passwordField.next('.password-note').length) {
                            passwordField.after('<p class="password-note">This password is to access the donor dashboard and manage your donations</p>');
                        }
                    }
                    insertPasswordNote();
                    $(document.body).on('updated_checkout', function() {
                        insertPasswordNote();
                    });
                });
            </script>
        <?php }
    }
}

if ($init_project_column_for_orders == 1) {
    function skydonate_account_orders_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $name) {
            $new_columns[$key] = $name;
            if ($key === 'order-status') {
                $new_columns['order-products'] = __('Project', 'woocommerce');
            }
        }
        return $new_columns;
    }
    add_filter('woocommerce_account_orders_columns', 'skydonate_account_orders_columns');
    function skydonate_account_orders_project_column($order) {
        $date_created = $order->get_date_created();
        $date_for_attribute = $date_created ? $date_created->date_i18n('Y-m-d H:i:s') : '';
        foreach ($order->get_items() as $item) {
            $product_id = $item->get_product_id();
            $name = $item->get_name();
            $link = $product_id ? get_permalink($product_id) : '#';
            echo '<div class="product order-data-row" data-orderdate="' . esc_attr($date_for_attribute) . '">';
                echo '<a href="' . esc_url($link) . '">' . esc_html($name) . '</a>';
                echo '<span class="days-since"></span>';
            echo '</div>';
        }
    }
    add_action('woocommerce_my_account_my_orders_column_order-products', 'skydonate_account_orders_project_column');
    function skydonate_admin_orders_columns($columns) {
        $columns['product_names'] = __('Project', 'woocommerce');
        return $columns;
    }
    add_filter('manage_woocommerce_page_wc-orders_columns', 'skydonate_admin_orders_columns', 20);

    function skydonate_admin_orders_custom_column($column, $order_id) {
        if ($column === 'product_names') {
            $order = wc_get_order($order_id);
            $items = $order->get_items();
            $product_names = array();
            foreach ($items as $item) {
                $product_names[] = $item->get_name();
            }
            echo implode(', ', $product_names);
        }
    }
    add_action('manage_woocommerce_page_wc-orders_custom_column', 'skydonate_admin_orders_custom_column', 10, 2);
    function skydonate_enqueue_order_date_script() {
        if (is_account_page()) { ?>
            <script>
            jQuery(document).ready(function($) {
                $('.order-data-row').each(function() {
                    var rawDate = $(this).data('orderdate');
                    if (!rawDate) return;
                    var orderTime = new Date(rawDate).getTime();
                    var now = new Date().getTime();
                    var diff = now - orderTime;
                    var daysSince = Math.floor(diff / (1000 * 60 * 60 * 24));
                    $(this).find('.days-since').text(' (' + daysSince + ' days ago)');
                });
            });
            </script>
        <?php }
    }
    add_action('wp_footer', 'skydonate_enqueue_order_date_script');
}

if ($init_email_product_name_replacement == 1) {
    add_filter('woocommerce_email_subject_customer_note', 'skydonate_replace_product_name_in_email', 10, 2);
    function skydonate_replace_product_name_in_email($subject, $order) {
        if (strpos($subject, '{product_name}') !== false) {
            $product_names = [];
            foreach ($order->get_items() as $item) {
                $product_names[] = $item->get_name();
            }
            $subject = str_replace('{product_name}', implode(', ', $product_names), $subject);
        }
        return $subject;
    }
}

if ($customize_woocommerce_login_message == 1) {
    add_filter('gettext', 'skydonate_customize_woocommerce_login_message', 20, 3);
    function skydonate_customize_woocommerce_login_message($translated_text, $text, $domain) {
        if ($domain === 'woocommerce' && $text === 'Please log in to your account to view this order.') {
            $translated_text = 'Your donation was successful. It looks like you have an account with us. To view your donation details, please sign in to your account.';
        }
        return $translated_text;
    }
}

if($init_checkout_email_typo_correction == 1){
    function validate_email_with_typo_correction($email) {
        $email = strtolower(trim($email));
        $domain_corrections = [
            'gmial.com'=>'gmail.com','gmai.com'=>'gmail.com','gmil.com'=>'gmail.com','gmal.com'=>'gmail.com',
            'gmali.com'=>'gmail.com','gmail.co'=>'gmail.com','gmail.con'=>'gmail.com','gmail.cim'=>'gmail.com',
            'gmail.cpm'=>'gmail.com','gmail.xom'=>'gmail.com','gmail.vom'=>'gmail.com','gmail.comm'=>'gmail.com',
            'gmaill.com'=>'gmail.com','gmailcom'=>'gmail.com','yahooo.com'=>'yahoo.com','yaho.com'=>'yahoo.com',
            'yahoo.co'=>'yahoo.com','yahoo.con'=>'yahoo.com','yahoo.cim'=>'yahoo.com','yahoo.cpm'=>'yahoo.com',
            'yahoo.xom'=>'yahoo.com','yahoo.vom'=>'yahoo.com','yahoo.comm'=>'yahoo.com','yahoocom'=>'yahoo.com',
            'hotmial.com'=>'hotmail.com','hotmai.com'=>'hotmail.com','hotmil.com'=>'hotmail.com','hotmal.com'=>'hotmail.com',
            'hotmail.co'=>'hotmail.com','hotmail.con'=>'hotmail.com','hotmail.cim'=>'hotmail.com','hotmail.cpm'=>'hotmail.com',
            'hotmail.xom'=>'hotmail.com','hotmail.vom'=>'hotmail.com','hotmail.comm'=>'hotmail.com','hotmaill.com'=>'hotmail.com',
            'outlok.com'=>'outlook.com','outlook.co'=>'outlook.com','outlook.con'=>'outlook.com','outlook.cim'=>'outlook.com',
            'outlook.cpm'=>'outlook.com','outlook.xom'=>'outlook.com','outlook.vom'=>'outlook.com','outlook.comm'=>'outlook.com',
            'outloook.com'=>'outlook.com','iclod.com'=>'icloud.com','icloud.co'=>'icloud.com','icloud.con'=>'icloud.com',
            'icloud.cim'=>'icloud.com','icloud.cpm'=>'icloud.com','icloud.xom'=>'icloud.com','icloud.vom'=>'icloud.com',
            'icloud.comm'=>'icloud.com','iclooud.com'=>'icloud.com'
        ];
        $tld_corrections = [
            'con'=>'com','cim'=>'com','cpm'=>'com','xom'=>'com','vom'=>'com','comm'=>'com','co'=>'com',
            'cm'=>'com','om'=>'com','orgg'=>'org','ogr'=>'org','og'=>'org','or'=>'org','nett'=>'net',
            'nte'=>'net','ent'=>'net','ne'=>'net','nt'=>'net'
        ];
        $result = ['is_valid'=>false,'original'=>$email,'suggestion'=>'','errors'=>[]];
        if(!is_email($email)){
            if(strpos($email,'@')===false) $result['errors'][]='Email address must contain an @ symbol.';
            elseif(strpos($email,'.')===false) $result['errors'][]='Email address must contain a domain extension (e.g., .com).';
            else $result['errors'][]='Please enter a valid email address format.';
            $parts=explode('@',$email);
            if(count($parts)==2&&!empty($parts[1])){
                $domain=$parts[1];
                $corrected_domain=correct_email_domain($domain,$domain_corrections,$tld_corrections);
                if($corrected_domain!==$domain) $result['suggestion']=$parts[0].'@'.$corrected_domain;
            }
            return $result;
        }
        $parts=explode('@',$email);
        if(count($parts)!=2){$result['errors'][]='Invalid email format.';return $result;}
        $local_part=$parts[0];$domain=$parts[1];
        $corrected_domain=correct_email_domain($domain,$domain_corrections,$tld_corrections);
        if($corrected_domain!==$domain){$result['suggestion']=$local_part.'@'.$corrected_domain;$result['errors'][]='Email address appears to contain a typo.';return $result;}
        $result['is_valid']=true;return $result;
    }

    function correct_email_domain($domain,$domain_corrections,$tld_corrections){
        if(isset($domain_corrections[$domain])) return $domain_corrections[$domain];
        $domain_parts=explode('.',$domain);
        if(count($domain_parts)>=2){
            $tld=end($domain_parts);
            if(isset($tld_corrections[$tld])){$domain_parts[count($domain_parts)-1]=$tld_corrections[$tld];return implode('.',$domain_parts);}
        }
        return $domain;
    }

    add_action('wp_footer','add_checkout_email_validation_script');
    function add_checkout_email_validation_script(){
        if(!is_checkout()) return;
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            var domainCorrections={'gmial.com':'gmail.com','gmai.com':'gmail.com','gmil.com':'gmail.com','gmail.con':'gmail.com','gmail.cim':'gmail.com','gmail.comm':'gmail.com','yahooo.com':'yahoo.com','yahoo.con':'yahoo.com','hotmial.com':'hotmail.com','hotmail.con':'hotmail.com','outlok.com':'outlook.com','outlook.con':'outlook.com','iclod.com':'icloud.com','icloud.con':'icloud.com'};
            var tldCorrections={'con':'com','cim':'com','cpm':'com','comm':'com','co':'com'};
            function validateEmailTypos(email){
                email=email.toLowerCase().trim();
                if(!email||email.indexOf('@')===-1) return null;
                var parts=email.split('@');if(parts.length!==2) return null;
                var domain=parts[1],correctedDomain=domain;
                if(domainCorrections[domain]) correctedDomain=domainCorrections[domain];
                else {var domainParts=domain.split('.');if(domainParts.length>=2){var tld=domainParts[domainParts.length-1];if(tldCorrections[tld]){domainParts[domainParts.length-1]=tldCorrections[tld];correctedDomain=domainParts.join('.');}}}
                if(correctedDomain!==domain) return parts[0]+'@'+correctedDomain;return null;
            }
            $('#billing_email').after('<div id="email-suggestion" style="display:none;"></div>');
            window.applyEmailSuggestion=function(suggestedEmail){$('#billing_email').val(suggestedEmail).trigger('change');$('#email-suggestion').hide();};
            $('#billing_email').on('blur',function(){var email=$(this).val();var suggestion=validateEmailTypos(email);if(suggestion){$('#email-suggestion').html('Did you mean <strong>'+suggestion+'</strong>? <a href="javascript:void(0);" onclick="applyEmailSuggestion(\''+suggestion+'\');" style="color:#721c24;text-decoration:underline;font-weight:bold;">Use this email</a>').show();}else{$('#email-suggestion').hide();}});
            $('#billing_email').on('keydown',function(){$('#email-suggestion').hide();});
        });
        </script>
        <style>
        #email-suggestion{font-size:13px;padding:8px 10px;background-color:#fff8e1;border:1px solid #ffeb3b;color:#856404;border-radius:3px;margin:5px 0 0 0 !important;line-height:1.4;display:block;box-sizing:border-box;}
        #email-suggestion:before{content:'\26A0';margin-right:5px;}
        #email-suggestion a{color:#856404 !important;text-decoration:underline !important;font-weight:bold;cursor:pointer;}
        #email-suggestion a:hover{color:#533f03 !important;text-decoration:none !important;}
        </style>
        <?php
    }

    add_filter('woocommerce_registration_errors','validate_registration_email_field',10,3);
    function validate_registration_email_field($errors,$username,$email){
        if(!empty($email)&&!is_email($email)) $errors->add('email_invalid','Please enter a valid email address.');
        return $errors;
    }

}

if($init_guest_checkout_data_saver == 1){
    add_action('woocommerce_checkout_order_processed', 'skydonate_save_guest_checkout_data', 10, 3);
    function skydonate_save_guest_checkout_data($order_id, $posted_data, $order){
        if(is_user_logged_in()) return;
        if(isset($posted_data['save_checkout_data']) && $posted_data['save_checkout_data'] != '1') return;
        $expiry = time() + (90 * DAY_IN_SECONDS);
        wc_setcookie('guest_billing_first_name',$order->get_billing_first_name(),$expiry);
        wc_setcookie('guest_billing_last_name',$order->get_billing_last_name(),$expiry);
        wc_setcookie('guest_billing_email',$order->get_billing_email(),$expiry);
        wc_setcookie('guest_billing_phone',$order->get_billing_phone(),$expiry);
        wc_setcookie('guest_billing_address_1',$order->get_billing_address_1(),$expiry);
        wc_setcookie('guest_billing_city',$order->get_billing_city(),$expiry);
        wc_setcookie('guest_billing_postcode',$order->get_billing_postcode(),$expiry);
        wc_setcookie('guest_billing_country',$order->get_billing_country(),$expiry);
        wc_setcookie('guest_billing_state',$order->get_billing_state(),$expiry);
        wc_setcookie('guest_data_saved','yes',$expiry);
    }
    add_action('wp_footer','skydonate_guest_checkout_ajax_save');
    function skydonate_guest_checkout_ajax_save(){
        if(!is_checkout()||is_user_logged_in()) return; ?>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            var saveTimer,savedData={};
            function saveCheckoutData(){
                var newData={
                    action:'skydonate_save_guest_checkout_ajax',
                    nonce:'<?php echo wp_create_nonce("skydonate_guest_checkout_nonce"); ?>',
                    billing_first_name:$('#billing_first_name').val(),
                    billing_last_name:$('#billing_last_name').val(),
                    billing_email:$('#billing_email').val(),
                    billing_phone:$('#billing_phone').val(),
                    billing_address_1:$('#billing_address_1').val(),
                    billing_city:$('#billing_city').val(),
                    billing_postcode:$('#billing_postcode').val(),
                    billing_country:$('#billing_country').val(),
                    billing_state:$('#billing_state').val()
                };
                if(JSON.stringify(newData)!==JSON.stringify(savedData)){savedData=newData;$.post('<?php echo admin_url("admin-ajax.php"); ?>',newData);}
            }
            $('#billing_first_name,#billing_last_name,#billing_email,#billing_phone,#billing_address_1,#billing_city,#billing_postcode').on('blur change',function(){clearTimeout(saveTimer);saveTimer=setTimeout(saveCheckoutData,500);});
            $('#billing_country,#billing_state').on('change',function(){setTimeout(saveCheckoutData,500);});
        });
        </script>
        <?php
    }
    add_action('wp_ajax_skydonate_save_guest_checkout_ajax','skydonate_handle_guest_checkout_ajax');
    add_action('wp_ajax_nopriv_skydonate_save_guest_checkout_ajax','skydonate_handle_guest_checkout_ajax');
    function skydonate_handle_guest_checkout_ajax(){
        if(!wp_verify_nonce($_POST['nonce'],'skydonate_guest_checkout_nonce')) wp_die('Security check failed');
        $expiry = time() + (90 * DAY_IN_SECONDS);
        if(!empty($_POST['billing_email'])){
            wc_setcookie('guest_billing_first_name',sanitize_text_field($_POST['billing_first_name']),$expiry);
            wc_setcookie('guest_billing_last_name',sanitize_text_field($_POST['billing_last_name']),$expiry);
            wc_setcookie('guest_billing_email',sanitize_email($_POST['billing_email']),$expiry);
            wc_setcookie('guest_billing_phone',sanitize_text_field($_POST['billing_phone']),$expiry);
            wc_setcookie('guest_billing_address_1',sanitize_text_field($_POST['billing_address_1']),$expiry);
            wc_setcookie('guest_billing_city',sanitize_text_field($_POST['billing_city']),$expiry);
            wc_setcookie('guest_billing_postcode',sanitize_text_field($_POST['billing_postcode']),$expiry);
            wc_setcookie('guest_billing_country',sanitize_text_field($_POST['billing_country']),$expiry);
            wc_setcookie('guest_billing_state',sanitize_text_field($_POST['billing_state']),$expiry);
            wc_setcookie('guest_data_saved','yes',$expiry);
        }
        wp_send_json_success('Data saved');
    }
    add_filter('woocommerce_checkout_get_value','skydonate_prefill_guest_checkout_fields',10,2);
    function skydonate_prefill_guest_checkout_fields($value,$input){
        if(is_user_logged_in()||!empty($value)) return $value;
        $cookie_map=[
            'billing_first_name'=>'guest_billing_first_name',
            'billing_last_name'=>'guest_billing_last_name',
            'billing_email'=>'guest_billing_email',
            'billing_phone'=>'guest_billing_phone',
            'billing_address_1'=>'guest_billing_address_1',
            'billing_city'=>'guest_billing_city',
            'billing_postcode'=>'guest_billing_postcode',
            'billing_country'=>'guest_billing_country',
            'billing_state'=>'guest_billing_state'
        ];
        if(isset($cookie_map[$input]) && isset($_COOKIE[$cookie_map[$input]])) return sanitize_text_field($_COOKIE[$cookie_map[$input]]);
        return $value;
    }
    add_action('woocommerce_review_order_before_submit','skydonate_add_guest_data_save_checkbox');
    function skydonate_add_guest_data_save_checkbox(){
        if(is_user_logged_in()) return; ?>
        <p class="form-row save-guest-data" style="margin: 15px 0;">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="save_checkout_data" id="save_checkout_data" value="1" checked />
                <span><?php esc_html_e('Remember me for next time (90 days)','woocommerce'); ?></span>
            </label>
        </p>
        <?php
    }
    add_action('woocommerce_before_checkout_form','skydonate_show_guest_data_notice');
    function skydonate_show_guest_data_notice(){
        if(!is_user_logged_in() && isset($_COOKIE['guest_data_saved']) && $_COOKIE['guest_data_saved']==='yes'){
            // Handle clear guest data request with nonce verification
            if(isset($_GET['clear_guest_data']) && $_GET['clear_guest_data']==='1'){
                if(isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'skydonate_clear_guest_data')){
                    skydonate_clear_guest_checkout_cookies();
                    wp_safe_redirect(wc_get_checkout_url());
                    exit;
                }
            }
            $clear_url = wp_nonce_url(add_query_arg('clear_guest_data', '1', wc_get_checkout_url()), 'skydonate_clear_guest_data');
            ?>
            <div class="woocommerce-info">
                <span><?php esc_html_e('Welcome back! We\'ve filled in your details from your last order.','woocommerce'); ?></span>
                <a href="<?php echo esc_url($clear_url); ?>" class="clear-saved-data" style="float: right;"><?php esc_html_e('Clear my information','woocommerce'); ?></a>
            </div>
            <?php
        }
    }
    function skydonate_clear_guest_checkout_cookies(){
        $guest_cookies=['guest_billing_first_name','guest_billing_last_name','guest_billing_email','guest_billing_phone','guest_billing_address_1','guest_billing_city','guest_billing_postcode','guest_billing_country','guest_billing_state','guest_data_saved'];
        foreach($guest_cookies as $cookie){wc_setcookie($cookie,'',time()-HOUR_IN_SECONDS);}
    }
    add_action('init','skydonate_litespeed_cookie_configuration');
    function skydonate_litespeed_cookie_configuration(){
        if(!defined('LSCWP_V')) return;
        add_filter('litespeed_vary_cookies',function($cookies){$cookies[]='guest_data_saved';$cookies[]='guest_billing_email';return $cookies;});
    }
    add_action('wp_head','skydonate_guest_checkout_custom_css');
    function skydonate_guest_checkout_custom_css(){
        if(!is_checkout()) return; ?>
        <style>
        .save-guest-data{background:#f7f7f7;padding:12px;border-radius:4px;margin:20px 0 10px;}
        .clear-saved-data{text-decoration:underline;font-size:14px;}
        .clear-saved-data:hover{text-decoration:none;}
        </style>
        <?php
    }
}