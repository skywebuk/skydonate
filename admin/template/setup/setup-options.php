<?php 
    if (!defined('ABSPATH')) exit;

    // Get options for different layouts
    $selected_card_layouts = get_option('select_card_layouts', []);
    $addons_card_layout = get_option('addons_card_layout', []);
    $addons_donation_form_layout = get_option('addons_donation_form_layout');
    $recent_donation_layout = get_option('recent_donation_layout', []);
    $progress_bar_layout = get_option('progress_bar_layout', []);
    if (!is_array($addons_card_layout)) {
        $addons_card_layout = ['layout1'];
    }
?>

<form class="skydonation-setup-options-form" method="post" action="">
    <input type="hidden" name="action" value="save_sky_donation_settings">
    <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
    <table class="form-table">
        <!-- Donation Details Card Layout -->
        <tr>
            <th scope="row"><label for="select_card_layouts">Donation Details Card Layout</label></th>
            <td>
                <?php
                // Ensure $selected_card_layouts is an array
                if (!is_array($selected_card_layouts)) {
                    $selected_card_layouts = !empty($selected_card_layouts) ? explode(',', $selected_card_layouts) : [];
                }
                ?>
                <select name="select_card_layouts" class="select_items" id="select_card_layouts" <?php echo esc_attr(LDIS); ?> multiple="multiple" style="width: 200px; height: 150px;">
                    <option value="layout1" <?php echo in_array('layout1', $selected_card_layouts) ? 'selected' : ''; ?>>Layout 1</option>
                    <option value="layout2" <?php echo in_array('layout2', $selected_card_layouts) ? 'selected' : ''; ?>>Layout 2</option>
                </select>
                <p><small><?php _e('Select one or more donation card layout select options.', 'skydonation'); ?></small></p>
            </td>
        </tr>

        <!-- Addons Card Layout -->
        <tr>
            <th scope="row"><label for="addons_card_layout">Cards Layout (Addons)</label></th>
            <td>
                <select name="addons_card_layout" class="select_items" multiple="multiple" id="addons_card_layout" style="width: 200px;" <?php echo esc_attr(LDIS); ?>>
                    <option value="layout1" <?php echo in_array('layout1', (array)$addons_card_layout) ? 'selected' : ''; ?>>Layout 1</option>
                    <option value="layout2" <?php echo in_array('layout2', (array)$addons_card_layout) ? 'selected' : ''; ?>>Layout 2</option>
                </select>
                <p><small>Select one addons card layout style.</small></p>
            </td>
        </tr>

        <!-- Addons Donation Form Layout -->
        <tr>
            <th scope="row"><label for="addons_donation_form_layout">Donation Form Layout</label></th>
            <td>
                <select name="addons_donation_form_layout" class="select_items" id="addons_donation_form_layout" style="width: 200px;" <?php echo esc_attr(LDIS); ?>>
                    <option value="layout1" <?php echo in_array('layout1', (array)$addons_donation_form_layout) ? 'selected' : ''; ?>>Layout 1</option>
                    <option value="layout2" <?php echo in_array('layout2', (array)$addons_donation_form_layout) ? 'selected' : ''; ?>>Layout 2</option>
                    <option value="layout3" <?php echo in_array('layout3', (array)$addons_donation_form_layout) ? 'selected' : ''; ?>>Layout 3</option>
                </select>
                <p><small>Select one addons donation form layout style.</small></p>
            </td>
        </tr>

        <!-- Recent Donation Layout -->
        <tr>
            <th scope="row"><label for="recent_donation_layout">Recent Donation Layout (Addons)</label></th>
            <td>
                <select name="recent_donation_layout" class="select_items" multiple="multiple" id="recent_donation_layout" style="width: 200px;" <?php echo esc_attr(LDIS); ?>>
                    <option value="layout1" <?php echo in_array('layout1', (array)$recent_donation_layout) ? 'selected' : ''; ?>>Layout 1</option>
                    <option value="layout2" <?php echo in_array('layout2', (array)$recent_donation_layout) ? 'selected' : ''; ?>>Layout 2</option>
                </select>
                <p><small>Select one recent donation layout style.</small></p>
            </td>
        </tr>

        <!-- Progress Bar Layout -->
        <tr>
            <th scope="row"><label for="progress_bar_layout">Progress Bar Layout (Addons)</label></th>
            <td>
                <select name="progress_bar_layout" class="select_items" multiple="multiple" id="progress_bar_layout" style="width: 200px;" <?php echo esc_attr(LDIS); ?>>
                    <option value="layout1" <?php echo in_array('layout1', (array)$progress_bar_layout) ? 'selected' : ''; ?>>Layout 1</option>
                    <option value="layout2" <?php echo in_array('layout2', (array)$progress_bar_layout) ? 'selected' : ''; ?>>Layout 2</option>
                </select>
                <p><small>Select one progress bar layout style.</small></p>
            </td>
        </tr>

    </table>
    <br>
    <p class="submit">
        <button type="submit" class="skydonation-button" <?php echo esc_attr(LDIS); ?>><?php _e( 'Save Settings', 'skydonation' ); ?></button>
    </p>
</form>
