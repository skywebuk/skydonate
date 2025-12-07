<?php 
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="skydonate-settings-panel">
    <form method="post" action="" class="skydonation-colors-form">
        <table class="form-table">

            <!-- Accent Color -->
            <tr>
                <th scope="row">
                    <label for="skydonation_accent_color"><?php esc_html_e( 'Accent Color', 'skydonate' ); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="skydonation_accent_color" 
                        name="skydonation_accent_color" 
                        class="skydonation-color-field" 
                        value="<?php echo esc_attr( get_option( 'skydonation_accent_color', '#3442ad' ) ); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Set the main accent color used throughout the site (buttons, links, and highlights).', 'skydonate' ); ?>
                    </p>
                </td>
            </tr>

            <!-- Accent Dark Color -->
            <tr>
                <th scope="row">
                    <label for="skydonation_accent_dark_color"><?php esc_html_e( 'Accent Dark Color', 'skydonate' ); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="skydonation_accent_dark_color" 
                        name="skydonation_accent_dark_color" 
                        class="skydonation-color-field" 
                        value="<?php echo esc_attr( get_option( 'skydonation_accent_dark_color', '#282699' ) ); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Set the darker shade of the accent color (used for hover or contrast elements).', 'skydonate' ); ?>
                    </p>
                </td>
            </tr>

            <!-- Accent Light Color -->
            <tr>
                <th scope="row">
                    <label for="skydonation_accent_light_color"><?php esc_html_e( 'Accent Light Color', 'skydonate' ); ?></label>
                </th>
                <td>
                    <input 
                        type="text" 
                        id="skydonation_accent_light_color" 
                        name="skydonation_accent_light_color" 
                        class="skydonation-color-field" 
                        value="<?php echo esc_attr( get_option( 'skydonation_accent_light_color', '#ebecf7' ) ); ?>"
                    >
                    <p class="description">
                        <?php esc_html_e( 'Set the lighter shade of the accent color (used for backgrounds, highlights, or subtle elements).', 'skydonate' ); ?>
                    </p>
                </td>
            </tr>

        </table>
        
        <br>
        <p>
            <button type="submit" class="skydonation-button">
                <?php _e('Save Settings', 'skydonate'); ?>
            </button>
        </p>
    </form>
</div>
