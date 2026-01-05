<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="skydonate-settings-panel">

    <div class="skydonate-tool-section">
        <h3><?php _e( 'Recalculate Donation Statistics', 'skydonate' ); ?></h3>
        <p>
            <small><?php _e( 'Recalculate "Raised Amount" and "Donation Count" for all donation products. This runs automatically daily, but you can trigger it manually here.', 'skydonate' ); ?></small>
        </p>
        <p>
            <button type="button" id="skydonate-recalculate-stats" class="skydonation-button">
                <?php _e( 'Recalculate Donation Stats', 'skydonate' ); ?>
            </button>
        </p>
    </div>

</div>
