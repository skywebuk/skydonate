<?php if ( ! defined( 'ABSPATH' ) ) exit;

$current_page = (isset( $_GET['page'] ) && !empty( $_GET['page'] )) ? $_GET['page'] : 'skydonation';
do_action('admin_dashboard_menu_tabs',$current_page);
?>