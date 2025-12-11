<?php
class Skydonate_Metabox {

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
        if ( skydonate_is_feature_enabled('notification') ) {
            add_meta_box(
                'skydonate_notification_options',
                'Notification Options',
                [$this, 'render_metabox'],
                ['post', 'page', 'product']
            );
        }
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
            $meta_values[$key] = get_post_meta($post->ID, '_skydonate_' . $key, true);
        }
    
        // Get dynamic product options
        $products = Skydonate_Functions::Get_Title('product', 'ids');
    
        // Security nonce
        wp_nonce_field('skydonate_notification_options_nonce_action', 'skydonate_notification_options_nonce');
    
        ?>
        <div class="responsive-table">
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="5">
                            <label>
                                <input type="checkbox" name="skydonate_enable_notification" value="1" <?php checked($meta_values['enable_notification'], 'yes'); ?> />
                                Enable Notification
                            </label>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="5">
                            <label for="skydonate_select_donation">Select Donations For Notification:</label><br>
                            <?php if ( ! empty( $products ) ) : ?>
                                <select name="skydonate_select_donation[]" id="skydonate_select_donation" class="select_type_items" multiple="multiple" style="width: 100%;">
                                    <?php foreach ($products as $id => $title): ?>
                                        <option value="<?php echo esc_attr($id); ?>" <?php echo in_array($id, (array)$meta_values['select_donation']) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($title); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else : ?>
                                <p class="description"><?php esc_html_e('No products available', 'skydonate'); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>
                                <input type="checkbox" name="skydonate_emoji" value="1" <?php checked($meta_values['emoji'], 'yes'); ?> />
                                Emoji
                            </label>
                        </td>
                        <td colspan="1">
                            <label>
                                <input type="checkbox" name="skydonate_location_visibility" value="1" <?php checked($meta_values['location_visibility'], 'yes'); ?> />
                                Location Visibility
                            </label>
                        </td>
                        <td colspan="1">
                            <label>
                                <input type="checkbox" name="skydonate_title_visibility" value="1" <?php checked($meta_values['title_visibility'], 'yes'); ?> />
                                Title Visibility
                            </label>
                        </td>
                        <td colspan="2">
                            <label>
                                <input type="checkbox" name="skydonate_timestamp" value="1" <?php checked($meta_values['timestamp'], 'yes'); ?> />
                                Timestamp
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="skydonate_limit">Limit:</label><br>
                            <input type="number" name="skydonate_limit" id="skydonate_limit" class="form-control" value="<?php echo esc_attr($meta_values['limit'] ?: 10); ?>" />
                        </td>
                        <td>
                            <?php $start_date = $meta_values['start_date'] ?: '7'; ?>
                            <label for="skydonate_start_date">Start Date:</label><br>
                            <select name="skydonate_start_date" id="skydonate_start_date" class="form-control">
                                <option value="3" <?php selected($start_date, '3'); ?>><?php _e('Last 3 Days', 'skydonate'); ?></option>
                                <option value="7" <?php selected($start_date, '7'); ?>><?php _e('Last 7 Days', 'skydonate'); ?></option>
                                <option value="14" <?php selected($start_date, '14'); ?>><?php _e('Last 2 Weeks', 'skydonate'); ?></option>
                                <option value="0" <?php selected($start_date, '0'); ?>><?php _e('Show All', 'skydonate'); ?></option>
                            </select>
                        </td>
                        <td>
                            <label for="skydonate_start_time">Start Time (in seconds):</label><br>
                            <input type="number" name="skydonate_start_time" id="skydonate_start_time" class="form-control"
                                   value="<?php echo esc_attr($meta_values['start_time'] ?: 10000); ?>" />
                        </td>
                        <td>
                            <label for="skydonate_visible_time">Visible Time (in seconds):</label><br>
                            <input type="number" name="skydonate_visible_time" id="skydonate_visible_time" class="form-control"
                                   value="<?php echo esc_attr($meta_values['visible_time'] ?: 10000); ?>" />
                        </td>
                        <td>
                            <label for="skydonate_gap_time">Gap Time (in seconds):</label><br>
                            <input type="number" name="skydonate_gap_time" id="skydonate_gap_time" class="form-control"
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
        // Check if feature is enabled
        if ( ! skydonate_is_feature_enabled('notification') ) {
            return;
        }

        // Verify nonce
        if ( ! isset($_POST['skydonate_notification_options_nonce']) || ! wp_verify_nonce($_POST['skydonate_notification_options_nonce'], 'skydonate_notification_options_nonce_action') ) {
            return;
        }

        // Check autosave
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return;
        }

        // Check permissions
        if ( ! current_user_can('edit_post', $post_id) ) {
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

        $checkbox_fields = ['enable_notification', 'emoji', 'location_visibility', 'title_visibility', 'timestamp'];
        $numeric_fields  = ['limit', 'start_time', 'visible_time', 'gap_time'];

        foreach ($fields as $field) {
            $key       = '_skydonate_' . $field;
            $post_key  = 'skydonate_' . $field;

            // Handle select_donation (multiple values)
            if ($field === 'select_donation') {
                $value = isset($_POST[$post_key]) && is_array($_POST[$post_key]) 
                    ? array_map('sanitize_text_field', $_POST[$post_key]) 
                    : [];
            } else {
                $value = isset($_POST[$post_key]) ? sanitize_text_field($_POST[$post_key]) : '';
            }

            // Handle checkboxes
            if ( in_array($field, $checkbox_fields, true) ) {
                $value = ($value === '1') ? 'yes' : 'no';
            }

            // Handle numeric fields
            if ( in_array($field, $numeric_fields, true) ) {
                $value = absint($value);
            }

            update_post_meta($post_id, $key, $value);
        }
    }
}

// Class is initialized in Skydonate_System::init_woocommerce_integrations() on the 'init' hook
// to comply with WordPress 6.7+ translation timing requirements