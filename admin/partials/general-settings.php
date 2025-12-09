<?php 
if (!defined('ABSPATH')) {

    exit(); // Exit if accessed directly.
}

global $SKDS, $SKDS_notice;


$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

$tabs = apply_filters('skyweb_general_settings_tabs', array());


?>
<div class="skydonation-page-wrapper template">
	<div class="skydonation-navigation-wrapper">
		<?php
		include_once SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/dashboard-tabs.php';
		?>
	</div>
</div>

<header>
	<?php
	if( $SKDS_notice ) {
		$error_text = esc_html__( 'settings has been updated. !', 'skyweb-invoice' );
		$SKDS->plugin_admin_notice( $error_text, 'success' );
	}
	?>
</header>

<div class="wrap">
    <?php do_action('skyweb_donation_settings_nav_before'); ?>
    <nav class="nav-tab-wrapper">
        <?php
            if (is_array($tabs) && !empty($tabs)) {
                foreach ($tabs as $tab_key => $tab) {
                    $tab_classes = 'link ';
                    if (!empty($active_tab) && $active_tab === $tab_key) {
                        $tab_classes .= 'nav-tab-active';
                    }
                    ?>
                    <a id="<?php echo esc_attr($tab_key); ?>" href="<?php echo esc_url(admin_url('admin.php?page=skydonation') . '&tab=' . esc_attr($tab_key)); ?>" class="nav-tab <?php echo $tab_classes; ?>"><?php echo esc_html($tab['label']); ?></a>
                    <?php
                }
            }
        ?>
    </nav>

    <div class="tab-content">
        <?php 
		do_action('skyweb_donation_settings_message');
        do_action('skyweb_donation_settings_form_before');
        // if submenu is directly clicked.
        if ($active_tab == '' || $active_tab == 'general') {
            $active_tab = 'general';
        }

        if($active_tab == 'general'){
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/dashboard-general.php';
            $SKDS->load_plugin_template($file_path);
        }elseif($active_tab == 'extra-donation'){
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/dashboard-extra-donation.php';
            $SKDS->load_plugin_template($file_path);
        }elseif($active_tab == 'advanced'){
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/dashboard-advanced.php';
            $SKDS->load_plugin_template($file_path);
        }elseif($active_tab == 'currency'){
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/dashboard-currency.php';
            $SKDS->load_plugin_template($file_path);
        }elseif($active_tab == 'colors'){
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/dashboard-colors.php';
            $SKDS->load_plugin_template($file_path);
        }else {
            $file_path = SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/partials/general-settings-form.php';
            $SKDS->load_plugin_template($file_path);
        }

        do_action('skyweb_donation_settings_form_after');
        ?>
    </div>

</div>