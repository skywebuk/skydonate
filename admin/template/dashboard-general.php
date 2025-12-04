<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $SKDS, $SKDS_notice;

$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';
$tabs       = apply_filters( 'skyweb_general_settings_tabs', [] );
?>

<?php
	if ( $SKDS_notice ) {
        echo '<header>';
		$SKDS->plugin_admin_notice(
			esc_html__( 'Settings has been updated!', 'skyweb-invoice' ),
			'success'
		);
        echo '</header>';
        $SKDS_notice = false;
	}
?>

<div class="wrap">

	<?php do_action( 'skyweb_donation_settings_nav_before' ); ?>

	<nav class="nav-tab-wrapper">
		<?php if ( ! empty( $tabs ) ) : ?>
			<?php foreach ( $tabs as $tab_key => $tab ) : 
				$active_class = ( $active_tab === $tab_key ) ? 'nav-tab-active' : '';
				?>
				<a 
					id="<?php echo esc_attr( $tab_key ); ?>" 
					href="<?php echo esc_url( admin_url( 'admin.php?page=skydonation&tab=' . esc_attr( $tab_key ) ) ); ?>" 
					class="nav-tab <?php echo esc_attr( $active_class ); ?>"
				>
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</nav>

	<div class="tab-content">
		<?php
		do_action( 'skyweb_donation_settings_message' );
		do_action( 'skyweb_donation_settings_form_before' );

		$templates = [
			'general'        => '/template/general/general.php',
			'extra-donation' => '/template/general/extra-donation.php',
			'advanced'       => '/template/general/advanced.php',
			'currency'       => '/template/general/currency.php',
			'colors'         => '/template/general/colors.php',
		];

		if ( isset( $templates[ $active_tab ] ) ) {
			$SKDS->load_plugin_template(
				SKYWEB_DONATION_SYSTEM_ADMIN_PATH . $templates[ $active_tab ]
			);
		}else{
			$SKDS->load_plugin_template( SKYWEB_DONATION_SYSTEM_ADMIN_PATH . '/template/general/settings.php' );
		}

		do_action( 'skyweb_donation_settings_form_after' );
		?>
	</div>

</div>
