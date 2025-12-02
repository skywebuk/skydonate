<?php 
    if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>

<form class="skydonation-general-form-setup" method="post" action="">
    <input type="hidden" name="setup_action" value="save_sky_donation_settings">
    <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="setup_enable_sky_donations_module">Enable Sky Donations Module</label></th>
            <td>
                <input type="checkbox" name="setup_enable_sky_donations_module" id="setup_enable_sky_donations_module" value="1" <?php checked(get_option('setup_enable_sky_donations_module'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>Enable or disable the Sky Donations module. Check this box to activate the module.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_enable_custom_login_form">Enable Custom Login Form</label></th>
            <td>
                <input type="checkbox" name="setup_enable_custom_login_form" id="setup_enable_custom_login_form" value="1" <?php checked(get_option('setup_enable_custom_login_form'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>Enable or disable a custom login form provided by the Sky Donations plugin.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_checkout_custom_field_style">Checkout Custom Field Style</label></th>
            <td>
                <input type="checkbox" name="setup_checkout_custom_field_style" id="setup_checkout_custom_field_style" value="1" <?php checked(get_option('setup_checkout_custom_field_style'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>Check the styling option for custom fields during the checkout process.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_recent_donation_list_with_country">Recent Donation List with Country Name and Flag</label></th>
            <td>
                <input type="checkbox" name="setup_recent_donation_list_with_country" id="setup_recent_donation_list_with_country" value="1" <?php checked(get_option('setup_recent_donation_list_with_country'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>If enabled, you will add the functionality to include the Recent Donation List with country name and flag.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_auto_complete_processing">Auto Complete Processing</label></th>
            <td>
                <input type="checkbox" name="setup_auto_complete_processing" id="setup_auto_complete_processing" value="1" <?php checked(get_option('setup_auto_complete_processing'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>If enabled, automatically complete donation payments on the Thank You page.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_enable_donation_goal">Enable Donation Goal</label></th>
            <td>
                <input type="checkbox" name="setup_enable_donation_goal" id="setup_enable_donation_goal" value="1" <?php checked(get_option('setup_enable_donation_goal'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>If enabled, an option for a Donation Goal will be available for the donation appeals.</small>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="setup_enable_title_prefix">Enable Title Prefix</label></th>
            <td>
                <input type="checkbox" name="setup_enable_title_prefix" id="setup_enable_title_prefix" value="1" <?php checked(get_option('setup_enable_title_prefix'), 1); ?> <?php echo esc_attr(LDIS); ?>>
                <small>Enable or disable a Title Prefix for donation entries.</small>
            </td>
        </tr>

    </table>
    <br>
    <p class="submit">
        <button type="submit" class="skydonation-button" <?php echo esc_attr(LDIS); ?>><?php _e( 'Save Settings', 'skydonation' ); ?></button>
    </p>
</form>