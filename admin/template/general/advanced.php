<?php 
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<div class="skyweb-settings-panel">
    <form class="skydonation-advanced-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>

        <table class="form-table">

            <tr>
                <th scope="row">
                    <label for="donation_monthly_heart_icon">Monthly Button Heart Icon</label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" 
                            name="donation_monthly_heart_icon" 
                            id="donation_monthly_heart_icon" 
                            value="1" 
                            <?php checked(get_option('donation_monthly_heart_icon'), 1); ?>>
                        <span class="switch"></span>
                        <small>
                            Enable heart icon for the monthly donation button.
                        </small>
                    </label>
                </td>
            </tr>


            <tr>
                <th scope="row"><label for="register_text_replacements">Text & Label Replacements</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="register_text_replacements" id="register_text_replacements" value="1" <?php checked(get_option('register_text_replacements'), 1); ?>>
                        <span class="switch"></span>
                        <small>Replace default WooCommerce and WordPress text/labels across frontend, admin, and emails.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_woocommerce_customizations">WooCommerce UI Tweaks</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_woocommerce_customizations" id="init_woocommerce_customizations" value="1" <?php checked(get_option('init_woocommerce_customizations'), 1); ?>>
                        <span class="switch"></span>
                        <small>Apply styling, checkout field adjustments, and other WooCommerce UI improvements.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_order_postcode_search">Order Search by Postcode</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_order_postcode_search" id="init_order_postcode_search" value="1" <?php checked(get_option('init_order_postcode_search'), 1); ?>>
                        <span class="switch"></span>
                        <small>Enable custom order search functionality using customer postcodes.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_menu_label_changes">Admin Menu Label Changes</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_menu_label_changes" id="init_menu_label_changes" value="1" <?php checked(get_option('init_menu_label_changes'), 1); ?>>
                        <span class="switch"></span>
                        <small>Rename WooCommerce admin menu labels (e.g., “Products” → “Donation Forms”).</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_stripe_order_meta_modifications">Stripe Order Meta</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_stripe_order_meta_modifications" id="init_stripe_order_meta_modifications" value="1" <?php checked(get_option('init_stripe_order_meta_modifications'), 1); ?>>
                        <span class="switch"></span>
                        <small>Store and display extra Stripe order metadata for better reporting.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_guest_checkout_for_existing_customers">Guest Checkout for Existing Customers</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_guest_checkout_for_existing_customers" id="init_guest_checkout_for_existing_customers" value="1" <?php checked(get_option('init_guest_checkout_for_existing_customers'), 1); ?>>
                        <span class="switch"></span>
                        <small>Allow existing customers to complete checkout without logging in.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="add_checkout_password_note">Checkout Password Note</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="add_checkout_password_note" id="add_checkout_password_note" value="1" <?php checked(get_option('add_checkout_password_note'), 1); ?>>
                        <span class="switch"></span>
                        <small>Show a helpful note on the checkout page about password requirements.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_project_column_for_orders">Project Column for Orders</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_project_column_for_orders" id="init_project_column_for_orders" value="1" <?php checked(get_option('init_project_column_for_orders'), 1); ?>>
                        <span class="switch"></span>
                        <small>Add a custom “Project” column in WooCommerce orders list.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_email_product_name_replacement">Email Product Name Replacement</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_email_product_name_replacement" id="init_email_product_name_replacement" value="1" <?php checked(get_option('init_email_product_name_replacement'), 1); ?>>
                        <span class="switch"></span>
                        <small>Replace product names with custom donation titles in WooCommerce emails.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="customize_woocommerce_login_message">WooCommerce Login Message</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="customize_woocommerce_login_message" id="customize_woocommerce_login_message" value="1" <?php checked(get_option('customize_woocommerce_login_message'), 1); ?>>
                        <span class="switch"></span>
                        <small>Replace WooCommerce login notices with custom messages for donors.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_checkout_email_typo_correction">Checkout Email Typo Correction</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_checkout_email_typo_correction" id="init_checkout_email_typo_correction" value="1" <?php checked(get_option('init_checkout_email_typo_correction'), 1); ?>>
                        <span class="switch"></span>
                        <small>Automatically detect and suggest corrections for common email typos at checkout.</small>          
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><label for="init_guest_checkout_data_saver">Guest Checkout Data Saver</label></th>
                <td>
                    <label class="checkbox-switch">
                        <input type="checkbox" name="init_guest_checkout_data_saver" id="init_guest_checkout_data_saver" value="1" <?php checked(get_option('init_guest_checkout_data_saver'), 1); ?>>
                        <span class="switch"></span>
                        <small>Preserve guest checkout form data for smoother user experience.</small>          
                    </label>
                </td>
            </tr>

        </table>

        <br>
        <p>
            <button type="submit" class="skydonation-button"><?php _e('Save Settings', 'skydonation'); ?></button>
        </p>
    </form>
</div>
