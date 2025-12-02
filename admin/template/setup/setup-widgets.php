<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
// Default widget widgets
$default_widgets = [
    'zakat_calculator' => 'yes',
    'zakat_calculator_classic' => 'yes',
    'metal_values' => 'yes',
    'recent_order' => 'yes',
    'donation_progress' => 'yes',
    'donation_form' => 'yes',
    'donation_card' => 'yes',
    'impact_slider' => 'yes',
    'qurbani_status' => 'yes',
    'donation_button' => 'yes',
    'icon_slider' => 'yes',
];

// Get saved options or use defaults
$widgets = get_option('skydonation_widgets_setup', []);
$widgets = wp_parse_args($widgets, $default_widgets);
?>
<div class="skydonation-dashboard-content">
    <form class="skydonation-setup-widget-form" method="post" action="">
        <div class="skydonation-checkboxs skydonation-widgets">
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Zakat Calculator', 'skydonation' ); ?></span>
                <input id="zakat_calculator" name="setup_widgets[zakat_calculator]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['zakat_calculator'] ) && $widgets['zakat_calculator'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Zakat Calculator Classic', 'skydonation' ); ?></span>
                <input id="zakat_calculator_classic" name="setup_widgets[zakat_calculator_classic]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['zakat_calculator_classic'] ) && $widgets['zakat_calculator_classic'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Metal Values', 'skydonation' ); ?></span>
                <input id="metal_values" name="setup_widgets[metal_values]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['metal_values'] ) && $widgets['metal_values'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Recent Donation', 'skydonation' ); ?></span>
                <input id="recent_order" name="setup_widgets[recent_order]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['recent_order'] ) && $widgets['recent_order'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Donation Progress', 'skydonation' ); ?></span>
                <input id="donation_progress" name="setup_widgets[donation_progress]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['donation_progress'] ) && $widgets['donation_progress'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Donation Form', 'skydonation' ); ?></span>
                <input id="donation_form" name="setup_widgets[donation_form]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['donation_form'] ) && $widgets['donation_form'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Donation Card', 'skydonation' ); ?></span>
                <input id="donation_card" name="setup_widgets[donation_card]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['donation_card'] ) && $widgets['donation_card'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Impact Slider', 'skydonation' ); ?></span>
                <input id="impact_slider" name="setup_widgets[impact_slider]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['impact_slider'] ) && $widgets['impact_slider'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Qurbani Status', 'skydonation' ); ?></span>
                <input id="qurbani_status" name="setup_widgets[qurbani_status]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['qurbani_status'] ) && $widgets['qurbani_status'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Donation Button', 'skydonation' ); ?></span>
                <input id="donation_button" name="setup_widgets[donation_button]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['donation_button'] ) && $widgets['donation_button'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
            <label class="skydonation-switcher">
                <span class="switch-label"><?php _e( 'Icon Slider', 'skydonation' ); ?></span>
                <input id="icon_slider" name="setup_widgets[icon_slider]" type="checkbox" <?php echo esc_attr(LDIS); ?> 
                    <?php checked( isset( $widgets['icon_slider'] ) && $widgets['icon_slider'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonation' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonation' ); ?></span>
                </span>
            </label>
        </div>
        <div class="skydonation-footer">
            <button type="submit" class="skydonation-button" <?php echo esc_attr(LDIS); ?> ><?php _e( 'Save Settings', 'skydonation' ); ?></button>
        </div>
    </form>
</div>