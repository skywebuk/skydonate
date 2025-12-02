<?php if (!defined('ABSPATH')) exit; ?>

<div class="custom-login-container">
    <img src="https://skywebdesign.co.uk/wp-content/uploads/2024/12/skywebdesign.co_.uk_black-06.svg" alt="Logo" class="logo">
    <form class="custom-login-form" id="setup-login-form" action="" method="post">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <input type="hidden" name="custom_login_nonce" value="<?php echo wp_create_nonce('custom_login_action'); ?>">
            <button type="submit" class="skydonation-button">Login</button>
        </div>
    </form>
</div>