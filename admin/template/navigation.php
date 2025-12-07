<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_page = (isset( $_GET['page'] ) && !empty( $_GET['page'] )) ? $_GET['page'] : 'skydonate';
do_action('admin_dashboard_menu_tabs',$current_page);
?>