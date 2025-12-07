<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Active tab
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

// Define tabs
$tabs = array(
	'general'         => array(
		'label' => __( 'General', 'skydonate' ),
	),
	'gift-aid-export' => array(
		'label' => __( 'Gift Aid Export', 'skydonate' ),
	),
);
?>

	<nav class="nav-tab-wrapper">
		<?php foreach ( $tabs as $tab_key => $tab ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=skydonation-gift-aid&tab=' . $tab_key ) ); ?>"
			   class="nav-tab link <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
				<?php echo esc_html( $tab['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="tab-content">
		<?php
		switch ( $active_tab ) {
			case 'gift-aid-export':
				include SKYDONATE_ADMIN_PATH . '/template/gift-aid/tab-export.php';
				break;

			case 'general':
			default:
				include SKYDONATE_ADMIN_PATH . '/template/gift-aid/tab-general.php';
				break;
		}
		?>
	</div>