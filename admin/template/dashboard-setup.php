<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$current_setup = isset( $_GET['setup'] ) ? sanitize_text_field( $_GET['setup'] ) : 'general';

?>

<div class="skydonation-dashboard-content">
    <?php if ( ! defined( 'MLOG' ) || ! MLOG ): ?>
        <?php 
        // Include the login form template if the user is not logged in
        include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/login-form.php'; 
        ?>
    <?php else: ?>
        <?php 
        // Include the setup links template
        include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-link.php'; 
        ?>

        <?php
        // Define valid setups and their corresponding template paths
        $setup_templates = [
            'general'        => SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-general.php',
            'donation-fees'  => SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-donation-fees.php',
            'notification'   => SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-notification.php',
            'widgets'        => SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-widgets.php',
            'options'        => SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/setup/setup-options.php',
        ];

        // Validate and include the template
        $template_path = isset( $setup_templates[ $current_setup ] ) ? $setup_templates[ $current_setup ] : $setup_templates['general'];

        if ( file_exists( $template_path ) ) {
            include_once $template_path;
        } else {
            echo '<p>' . esc_html__( 'Template not found.', 'skydonation' ) . '</p>';
        }
        ?>
    <?php endif; ?>
</div>
