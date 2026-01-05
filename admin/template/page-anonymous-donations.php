<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Default values
$default_display_name = 'Anonymous';
$default_description = 'Allow donors to make their donations anonymously. Their name will be hidden from public donation lists and displays.';
?>

<div class="skydonate-settings-panel">
    <form class="skydonate-anonymous-donations-form" method="post" action="">
        <input type="hidden" name="action" value="save_anonymous_donations_settings">
        <?php wp_nonce_field('save_anonymous_donations_settings', 'anonymous_donations_nonce'); ?>

        <table class="form-table">
            <!-- Enable Anonymous Donations -->
            <tr>
                <th scope="row">
                    <label for="enable_anonymous_donations"><?php esc_html_e('Enable Anonymous Donations', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input
                            type="checkbox"
                            name="enable_anonymous_donations"
                            id="enable_anonymous_donations"
                            value="1"
                            <?php checked( get_option('enable_anonymous_donations', 0), 1 ); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Enable this option to allow donors to make anonymous donations.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Anonymous Display Name -->
            <tr>
                <th scope="row">
                    <label for="anonymous_display_name"><?php esc_html_e('Anonymous Display Name', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="anonymous_display_name" id="anonymous_display_name" value="<?php echo esc_attr(get_option('anonymous_display_name', $default_display_name)); ?>" class="regular-text">
                    <p><small><?php esc_html_e('The name displayed for anonymous donors (e.g., "Anonymous", "A Kind Donor", "Hidden Donor").', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Anonymous Donations Description -->
            <tr>
                <th scope="row">
                    <label for="anonymous_donations_description"><?php esc_html_e('Description', 'skydonate'); ?></label>
                </th>
                <td>
                    <textarea name="anonymous_donations_description" id="anonymous_donations_description" rows="4" cols="50"><?php echo esc_textarea(get_option('anonymous_donations_description', $default_description)); ?></textarea>
                    <p><small><?php esc_html_e('Description text shown to donors explaining the anonymous donation option.', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Checkbox Label -->
            <tr>
                <th scope="row">
                    <label for="anonymous_checkbox_label"><?php esc_html_e('Checkbox Label', 'skydonate'); ?></label>
                </th>
                <td>
                    <input type="text" name="anonymous_checkbox_label" id="anonymous_checkbox_label" value="<?php echo esc_attr(get_option('anonymous_checkbox_label', 'Make my donation anonymous')); ?>" class="regular-text">
                    <p><small><?php esc_html_e('Label text for the anonymous donation checkbox on the donation form.', 'skydonate'); ?></small></p>
                </td>
            </tr>

            <!-- Checkbox Default Status -->
            <tr>
                <th scope="row">
                    <label for="anonymous_checkbox_default"><?php esc_html_e('Default Checkbox Status', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input
                            type="checkbox"
                            name="anonymous_checkbox_default"
                            id="anonymous_checkbox_default"
                            value="1"
                            <?php checked( get_option('anonymous_checkbox_default', 0), 1 ); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Check to have the anonymous checkbox checked by default on the donation form.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Hide from Donation Lists -->
            <tr>
                <th scope="row">
                    <label for="anonymous_hide_from_lists"><?php esc_html_e('Hide from Donation Lists', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input
                            type="checkbox"
                            name="anonymous_hide_from_lists"
                            id="anonymous_hide_from_lists"
                            value="1"
                            <?php checked( get_option('anonymous_hide_from_lists', 0), 1 ); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Completely hide anonymous donations from public donation lists instead of showing the anonymous display name.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

            <!-- Hide Donation Amount -->
            <tr>
                <th scope="row">
                    <label for="anonymous_hide_amount"><?php esc_html_e('Hide Donation Amount', 'skydonate'); ?></label>
                </th>
                <td>
                    <label class="checkbox-switch">
                        <input
                            type="checkbox"
                            name="anonymous_hide_amount"
                            id="anonymous_hide_amount"
                            value="1"
                            <?php checked( get_option('anonymous_hide_amount', 0), 1 ); ?>
                        >
                        <span class="switch"></span>
                        <small><?php esc_html_e('Also hide the donation amount for anonymous donors in public displays.', 'skydonate'); ?></small>
                    </label>
                </td>
            </tr>

        </table>
        <br/>
        <p>
            <button type="submit" class="skydonation-button"><?php esc_html_e('Save Settings', 'skydonate'); ?></button>
        </p>
    </form>
</div>
