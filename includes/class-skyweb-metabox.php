<?php
class Skyweb_Donation_Metabox {

    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_metabox']);
        add_action('save_post', [$this, 'save_metabox_data']);
    }

    /**
     * Add Metabox
     */
    public function add_metabox() {
        add_meta_box(
            'skyweb_notification_options', // Metabox ID
            'Notification Options',        // Title
            [$this, 'render_metabox'],     // Callback
            ['post', 'page', 'product'],                        // Post type
        );
    }

    /**
     * Render Metabox
     *
     * @param WP_Post $post
     */
    public function render_metabox($post) {
        // Retrieve saved values
        $meta_keys = [
            'enable_notification',
            'select_donation',
            'emoji',
            'location_visibility',
            'title_visibility',
            'timestamp',
            'limit',
            'start_date',
            'start_time',
            'visible_time',
            'gap_time'
        ];
        $meta_values = [];
        foreach ($meta_keys as $key) {
            $meta_values[$key] = get_post_meta($post->ID, '_skyweb_' . $key, true);
        }
    
        // Get dynamic product options
        $products = Skyweb_Donation_Functions::Get_Title('product', 'ids');
        if (empty($products)) {
            $products = ['' => 'No products available'];
        }
    
        // Security nonce
        wp_nonce_field('skyweb_notification_options_nonce_action', 'skyweb_notification_options_nonce');
    
        ?>
        <div class="responsive-table">
            <table class="table">
                <thead>
                    <tr>
                        <th  colspan="5">
                            <label>
                                <input type="checkbox" name="skyweb_enable_notification" value="1" <?php checked($meta_values['enable_notification'], 'yes'); ?> />
                                Enable Notification
                            </label>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <label for="skyweb_select_donation">Select Donations For Notification:</label><br>
                            <!-- Multi-select dropdown for products -->
                            <select name="skyweb_select_donation[]" id="skyweb_select_donation" class="select_type_items" multiple="multiple" style="width: 100%;">
                                <?php foreach ($products as $id => $title): ?>
                                    <option value="<?php echo esc_attr($id); ?>" <?php echo in_array($id, (array)$meta_values['select_donation']) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($title); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="skyweb_emoji" value="1" <?php checked($meta_values['emoji'], 'yes'); ?> />
                                Emoji
                            </label>
                        </td>
                        <td colspan="1">
                            <label>
                                <input type="checkbox" name="skyweb_location_visibility" value="1" <?php checked($meta_values['location_visibility'], 'yes'); ?> />
                                Location Visibility
                            </label>
                        </td>
                        <td colspan="1">
                            <label>
                                <input type="checkbox" name="skyweb_title_visibility" value="1" <?php checked($meta_values['title_visibility'], 'yes'); ?> />
                                Title Visibility
                            </label>
                        </td>
                        <td colspan="2">
                            <label>
                                <input type="checkbox" name="skyweb_timestamp" value="1" <?php checked($meta_values['timestamp'], 'yes'); ?> />
                                Timestamp
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="skyweb_limit">Limit:</label><br>
                            <input type="number" name="skyweb_limit" id="skyweb_limit" class="form-control" value="<?php echo esc_attr($meta_values['limit'] ?: 10); ?>" />
                        </td>
                        <td>
                            <label for="skyweb_start_date">Start Date:</label><br>
                            <select name="skyweb_start_date" id="skyweb_start_date" class="form-control">
                                <option value="3" <?php selected($meta_values['start_date'], '3'); ?>><?php _e('Last 3 Days', 'skyweb'); ?></option>
                                <option value="7" <?php selected($meta_values['start_date'], '7'); ?>><?php _e('Last 7 Days', 'skyweb'); ?></option>
                                <option value="14" <?php selected($meta_values['start_date'], '14'); ?>><?php _e('Last 2 Weeks', 'skyweb'); ?></option>
                                <option value="0" <?php selected($meta_values['start_date'], '0'); ?>><?php _e('Show All', 'skyweb'); ?></option>
                            </select>
                        </td>
                        <td>
                            <label for="skyweb_start_time">Start Time (in seconds):</label><br>
                            <input type="number" name="skyweb_start_time" id="skyweb_start_time" class="form-control"
                                   value="<?php echo esc_attr($meta_values['start_time'] ?: 10000); ?>" />
                        </td>
                        <td>
                            <label for="skyweb_visible_time">Visible Time (in seconds):</label><br>
                            <input type="number" name="skyweb_visible_time" id="skyweb_visible_time" class="form-control"
                                   value="<?php echo esc_attr($meta_values['visible_time'] ?: 10000); ?>" />
                        </td>
                        <td>
                            <label for="skyweb_gap_time">Gap Time (in seconds):</label><br>
                            <input type="number" name="skyweb_gap_time" id="skyweb_gap_time" class="form-control"
                                   value="<?php echo esc_attr($meta_values['gap_time'] ?: 10000); ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Save Metabox Data
     *
     * @param int $post_id
     */
    public function save_metabox_data($post_id) {
        // Verify nonce
        if (!isset($_POST['skyweb_notification_options_nonce']) || !wp_verify_nonce($_POST['skyweb_notification_options_nonce'], 'skyweb_notification_options_nonce_action')) {
            return;
        }

        // Check autosave or permissions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || !current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields
        $fields = [
            'enable_notification',
            'select_donation',
            'emoji',
            'location_visibility',
            'title_visibility',
            'timestamp',
            'limit',
            'start_date',
            'start_time',
            'visible_time',
            'gap_time'
        ];

        foreach ($fields as $field) {
            $key = '_skyweb_' . $field;

            // Handle select_donation (multiple values, should be saved as an array)
            if ($field === 'select_donation') {
                // If there are selected donations, sanitize the values as an array
                $value = isset($_POST['skyweb_' . $field]) ? array_map('sanitize_text_field', $_POST['skyweb_' . $field]) : [];
            } else {
                // For other fields, sanitize single values
                $value = isset($_POST['skyweb_' . $field]) ? sanitize_text_field($_POST['skyweb_' . $field]) : '';
            }

            // Handle checkboxes (saving 'yes' or 'no')
            if (in_array($field, ['enable_notification', 'emoji', 'location_visibility', 'title_visibility', 'timestamp'])) {
                $value = $value === '1' ? 'yes' : 'no';
            }

            // Handle numeric fields (limit, start_time, visible_time, gap_time)
            if (in_array($field, ['limit', 'start_time', 'visible_time', 'gap_time'])) {
                $value = absint($value);
            }

            // Save the meta value
            update_post_meta($post_id, $key, $value);
        }
    }

}

// Initialize the Metabox
new Skyweb_Donation_Metabox();
