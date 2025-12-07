<?php 
    if (!defined('ABSPATH')) exit; // Exit if accessed directly

    // Get the previously saved option
    $selected_ids = get_option('notification_select_donations', []);
    

    // Fetch product IDs and titles
    $products = Skydonate_Functions::Get_Title('product', 'ids'); // Adjust according to your data structure

    // Ensure $selected_ids is an array
    if (!is_array($selected_ids)) {
        $selected_ids = [];
    }
?>
<div class="skydonate-settings-panel">
    <form class="skydonation-notification-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="notification_select_donations">Select Donations</label></th>
                <td>
                    <select name="notification_select_donations" class="select_type_items" id="notification_select_donations" multiple="multiple" style="width: 100%; height: 150px;">
                        <?php
                            foreach ($products as $id => $name) {
                                $selected = in_array($id, $selected_ids) ? 'selected' : '';
                                echo '<option value="' . esc_attr($id) . '" ' . $selected . '>' . esc_html($name) . '</option>';
                            }
                        ?>
                    </select>
                    <p><small>Select one or more donation items for notifications. Hold down the Ctrl (Windows) or Command (Mac) button to select multiple items.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="supporter_name_display_style">Supporter's Name</label></th>
                <td>
                    <select name="supporter_name_display_style" id="supporter_name_display_style">
                        <option value="full_name" <?php selected(get_option('supporter_name_display_style'), 'full_name'); ?>>Full Name</option>
                        <option value="first_last_initial" <?php selected(get_option('supporter_name_display_style'), 'first_last_initial'); ?>>First Name, Last Initial</option>
                        <option value="first_name" <?php selected(get_option('supporter_name_display_style'), 'first_name'); ?>>First Name</option>
                        <option value="hide" <?php selected(get_option('supporter_name_display_style'), 'hide'); ?>>Hide</option>
                    </select>
                    <p><small>Choose how the supporter's name will be displayed during checkout. Options include full name, first name with last initial, first name only, or hiding the name entirely.</small></p>
                </td>
            </tr>
            <tr>
                <tr>
                    <th scope="row"><label for="enable_emoji_notifications">Enable Emojis</label></th>
                    <td>
                        <label class="checkbox-switch">
                            <input 
                                type="checkbox" 
                                name="enable_emoji_notifications" 
                                id="enable_emoji_notifications" 
                                value="1" 
                                <?php checked(get_option('enable_emoji_notifications'), 1); ?>
                            >
                            <span class="switch"></span>
                            <small>Check this box to add fun, random emojis to your notification messages. Uncheck it to disable emojis.</small>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="enable_location_visibility">Location Visibility</label></th>
                    <td>
                        <label class="checkbox-switch">
                            <input 
                                type="checkbox" 
                                name="enable_location_visibility" 
                                id="enable_location_visibility" 
                                value="1" 
                                <?php checked(get_option('enable_location_visibility'), 1); ?> 
                            
                            >
                            <span class="switch"></span>
                            <small>Check this box to show the location in notification messages. Uncheck to hide the location.</small>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="enable_title_visibility">Title Visibility</label></th>
                    <td>
                        <label class="checkbox-switch">
                            <input 
                                type="checkbox" 
                                name="enable_title_visibility" 
                                id="enable_title_visibility" 
                                value="1" 
                                <?php checked(get_option('enable_title_visibility'), 1); ?> 
                            
                            >
                            <span class="switch"></span>
                            <small>Check this box to show the title in notification messages. Uncheck to hide the title.</small>
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="enable_timestamp_display">Timestamp</label></th>
                    <td>
                        <label class="checkbox-switch">
                            <input 
                                type="checkbox" 
                                name="enable_timestamp_display" 
                                id="enable_timestamp_display" 
                                value="1" 
                                <?php checked(get_option('enable_timestamp_display'), 1); ?> 
                            
                            >
                            <span class="switch"></span>
                            <small>Check this box to show time as "1 minute ago" or "3 hours ago" in notification messages. Uncheck to hide.</small>
                        </label>
                    </td>
                </tr>

            <tr>
                <th scope="row"><label for="notification_limit">Limit</label></th>
                <td>
                    <input type="number" name="notification_limit" id="notification_limit" value="<?php echo esc_attr(get_option('notification_limit', 5)); ?>" min="0">
                    <p><small>Enter the maximum number of notifications that can be shown at once. Set to 0 for no limit.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="start_date_range">Start Date</label></th>
                <td>
                    <select name="start_date_range" id="start_date_range">
                        <option value="3" <?php selected(get_option('start_date_range'), '3'); ?>>Last 3 Days</option>
                        <option value="7" <?php selected(get_option('start_date_range'), '7'); ?>>Last 7 Days</option>
                        <option value="14" <?php selected(get_option('start_date_range'), '14'); ?>>Last 2 Weeks</option>
                        <option value="0" <?php selected(get_option('start_date_range'), '0'); ?>>Show All</option>
                    </select>
                    <p><small>Choose the start date range for sending notifications. Donations made within the selected period will be included.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="notifi_start_time">Start Time (in seconds)</label></th>
                <td>
                    <input type="number" name="notifi_start_time" id="notifi_start_time" value="<?php echo esc_attr(get_option('notifi_start_time', 10000)); ?>" min="0">
                    <p><small>Enter the start time for notifications in seconds. Default is 10000 seconds.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="notifi_visible_time">Visible Time (in seconds)</label></th>
                <td>
                    <input type="number" name="notifi_visible_time" id="notifi_visible_time" value="<?php echo esc_attr(get_option('notifi_visible_time', 10000)); ?>" min="0">
                    <p><small>Enter the duration for which the notification will be visible in seconds. Default is 10000 seconds.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="notifi_gap_time">Gap Time (in seconds)</label></th>
                <td>
                    <input type="number" name="notifi_gap_time" id="notifi_gap_time" value="<?php echo esc_attr(get_option('notifi_gap_time', 15000)); ?>" min="0">
                    <p><small>Enter the gap time between notifications in seconds. Default is 15000 seconds.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="show_element_urls">Only show element at these URLs</label></th>
                <td>
                    <textarea name="show_element_urls" id="show_element_urls" rows="4" cols="50" 
                        placeholder="https://example.com/
https://example.com/*
https://example.com/about/*"
                    ><?php echo esc_textarea(get_option('show_element_urls')); ?></textarea>
                    <p><small>Enter URLs (one per line) where the element should be shown.</small></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="hide_element_urls">Never show element at these URLs</label></th>
                <td>
                    <textarea name="hide_element_urls" id="hide_element_urls" rows="4" cols="50" 
                        placeholder="https://example.com/
https://example.com/*
https://example.com/about/*" ><?php echo esc_textarea(get_option('hide_element_urls')); ?></textarea>
                    <p><small>Enter URLs (one per line) where the element should be hidden.</small></p>
                </td>
            </tr>
        </table>
        <br>
        <p class="submit">
            <button type="submit" class="skydonation-button"><?php _e( 'Save Settings', 'skydonate' ); ?></button>
        </p>
    </form>
</div>