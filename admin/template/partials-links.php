<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
$tabs = apply_filters('skydonation_settings_general_link_tabs', array());
?>
<ul class="general-links">
    <?php
   
    foreach ( $tabs as $tab_key => $tab ) :
        $active_class = ( $active_tab === $tab_key ) ? 'active' : '';
        ?>
        <li>
            <a href="<?php echo esc_url(admin_url('admin.php?page=skydonation-general') . '&tab=' . esc_attr($tab_key)); ?>" class="nav-link <?php echo $active_class; ?>">
                <!-- <span class="nav-icon"><i class="fas fa-<?php //echo esc_attr( $data['icon'] ); ?>"></i></span> -->
                <?php echo esc_html( $tab['label'] ); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
