<?php

$settings_fields = apply_filters( 'skyweb_general_settings_fields', [] );
$active_tab      = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';

if ( ! empty( $settings_fields ) && isset( $settings_fields[ $active_tab ] ) ) {
    $field = $settings_fields[ $active_tab ];
    ?>

    <div class="skydonate-settings-panel">
        <form class="skydonation-<?php echo esc_attr( $active_tab ); ?>-form">
            <?php
                wp_nonce_field( 'setting_submit_nonce', 'setting_submit_fields' );
                if ( ! empty( $field ) ) {
                    echo '<table class="form-table"><tbody>';
                    SkyDonation_Functions::render_components( $field );
                    echo '</tbody></table>';
                }
            ?>
        </form>
    </div>

    <?php
}
?>
