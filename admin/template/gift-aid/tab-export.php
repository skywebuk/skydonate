<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="skydonate-settings-panel">
    <div class="skydonate-gift-aid-export-form">
        <table class="form-table">

            <!-- Export All Declarations -->
            <tr>
                <th scope="row">
                    <label><?php esc_html_e( 'Export All Declarations', 'skydonate' ); ?></label>
                </th>
                <td>
                    <p>
                        <?php esc_html_e( 'Export all Gift Aid records where donors have given consent. This may include a large amount of data.', 'skydonate' ); ?>
                    </p>
                    <form class="gift-aid-full-export" method="post" action="">
                        <input type="hidden" name="action" value="skydonate_gift_aid_export">
                        <?php wp_nonce_field( 'skydonate_gift_aid_export_action', 'skydonate_gift_aid_export_nonce' ); ?>
                        <button type="submit" class="skydonation-button">
                            <?php esc_html_e( 'Export CSV', 'skydonate' ); ?>
                            <span class="dashicons dashicons-download"></span>
                        </button>
                    </form>
                </td>
            </tr>

            <!-- Export by Date Range -->
            <tr>
                <th scope="row">
                    <label><?php esc_html_e( 'Export by Date Range', 'skydonate' ); ?></label>
                </th>
                <td>
                    <form class="gift-aid-date-export" method="post" action="">
                        <input type="hidden" name="action" value="skydonate_gift_aid_export">
                        <?php wp_nonce_field( 'skydonate_gift_aid_export_action', 'skydonate_gift_aid_export_nonce' ); ?>
                        <p>
                            <?php esc_html_e( 'Export Gift Aid declarations within a specific date range for reporting or auditing.', 'skydonate' ); ?>
                        </p>
                        <div class="gift-aid-date-group">
                            <div class="gift-aid-date-field">
                                <label for="start_date"><strong><?php esc_html_e( 'Start Date:', 'skydonate' ); ?></strong></label>
                                <input 
                                    type="date" 
                                    id="start_date" 
                                    name="start_date" 
                                    value="<?php echo esc_attr( date( 'Y-m-d', strtotime('-3 months') ) ); ?>">
                            </div>

                            <div class="gift-aid-date-field">
                                <label for="end_date"><strong><?php esc_html_e( 'End Date:', 'skydonate' ); ?></strong></label>
                                <input 
                                    type="date" 
                                    id="end_date" 
                                    name="end_date" 
                                    value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                            </div>
                        </div>
                        <p>
                            <button type="submit" class="skydonation-button">
                                <?php esc_html_e( 'Export CSV', 'skydonate' ); ?>
                                <span class="dashicons dashicons-download"></span>
                            </button>
                        </p>
                    </form>
                </td>
            </tr>

        </table>
    </div>
</div>
