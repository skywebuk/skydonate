<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<ul class="general-links">
    <?php
    $tabs = [
        'general' => [
            'label' => __( 'General', 'skydonation' ),
            'url'   => admin_url( 'admin.php?page=skydonation-setup&setup=general' )
        ],
        'gift-aid' => [
            'label' => __( 'Gift Aid', 'skydonation' ),
            'url'   => admin_url( 'admin.php?page=skydonation-setup&setup=gift-aid' )
        ],
        'widgets' => [
            'label' => __( 'Widgets', 'skydonation' ),
            'url'   => admin_url( 'admin.php?page=skydonation-setup&setup=widgets' )
        ],
        'notification' => [
            'label' => __( 'Notification', 'skydonation' ),
            'url'   => admin_url( 'admin.php?page=skydonation-setup&setup=notification' )
        ],
        'options' => [
            'label' => __( 'Options', 'skydonation' ),
            'url'   => admin_url( 'admin.php?page=skydonation-setup&setup=options' )
        ],
    ];

    // Get current setup parameter
    $current_setup = isset( $_GET['setup'] ) ? sanitize_text_field( $_GET['setup'] ) : 'general';

    foreach ( $tabs as $setup => $data ) :
        $active_class = ( $current_setup === $setup ) ? 'active' : '';
        ?>
        <li>
            <a href="<?php echo esc_url( $data['url'] ); ?>" class="nav-link <?php echo esc_attr( $active_class ); ?>">
                <?php echo esc_html( $data['label'] ); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
