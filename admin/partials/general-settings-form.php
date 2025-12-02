<?php
if (!defined('ABSPATH')) {
    exit;
}

$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

$settings_fields = apply_filters('skyweb_general_settings_fields', array());
$field 			= isset($settings_fields[$active_tab])?$settings_fields[$active_tab]:'';
?>
<div class="skyweb-settings-panel">
    <!--  template file for admin settings. -->
    <form action="" method="POST">
        <div class="section">
            <?php
            wp_nonce_field('setting_submit_nonce', 'setting_submit_fields');
            skyweb_donation_fields_html($field);
            ?>
        </div>
    </form>
</div>