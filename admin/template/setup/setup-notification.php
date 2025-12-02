<?php 
    if (!defined('ABSPATH')) exit; // Exit if accessed directly

?>

<form class="skydonation-notification-form-setup" method="post" action="">
    <input type="hidden" name="action" value="save_sky_donation_settings">
    <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="setup_enable_notification">Enable Notification</label></th>
            <td>
                <input type="checkbox" name="setup_enable_notification" id="setup_enable_notification" value="1" <?php checked(get_option('setup_enable_notification'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>Enable or disable the Donations Notification. Check this box to activate the notification.</small>
            </td>
        </tr>
    </table>
    <br>
    <p class="submit">
        <button type="submit" class="skydonation-button" <?php echo esc_attr(LDIS); ?>><?php _e( 'Save Settings', 'skydonation' ); ?></button>
    </p>
</form>