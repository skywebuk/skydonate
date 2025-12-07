<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
// Default widget widgets
$default_widgets = [
    'zakat_calculator' => 'on',
    'zakat_calculator_classic' => 'on',
    'metal_values' => 'on',
    'recent_order' => 'on',
    'donation_progress' => 'on',
    'donation_form' => 'on',
    'donation_card' => 'on',
    'impact_slider' => 'on',
    'qurbani_status' => 'on',
    'extra_donation' => 'on',
    'quick_donation' => 'on',
    'gift_aid_toggle' => 'on',
    'donation_button' => 'on',
    'icon_slider' => 'on',
];

// Get saved options or use defaults
$widgets = get_option('skydonate_widgets', []);
$widgets = wp_parse_args($widgets, $default_widgets);
?>
<div class="skydonate-settings-panel">
    <form class="skydonate-widget-form" method="post" action="">
        <div class="skydonate-checkboxs skydonate-widgets">
            <?php if(sky_widget_status_check('zakat_calculator')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Zakat Calculator', 'skydonate' ); ?></span>
                <input id="zakat_calculator" name="widgets[zakat_calculator]" type="checkbox" 
                    <?php checked( isset( $widgets['zakat_calculator'] ) && $widgets['zakat_calculator'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('zakat_calculator_classic')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Zakat Calculator Classic', 'skydonate' ); ?></span>
                <input id="zakat_calculator_classic" name="widgets[zakat_calculator_classic]" type="checkbox" 
                    <?php checked( isset( $widgets['zakat_calculator_classic'] ) && $widgets['zakat_calculator_classic'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('metal_values')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Metal Values', 'skydonate' ); ?></span>
                <input id="metal_values" name="widgets[metal_values]" type="checkbox" 
                    <?php checked( isset( $widgets['metal_values'] ) && $widgets['metal_values'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('recent_order')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Recent Donation', 'skydonate' ); ?></span>
                <input id="recent_order" name="widgets[recent_order]" type="checkbox" 
                    <?php checked( isset( $widgets['recent_order'] ) && $widgets['recent_order'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('donation_progress')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Donation Progress', 'skydonate' ); ?></span>
                <input id="donation_progress" name="widgets[donation_progress]" type="checkbox" 
                    <?php checked( isset( $widgets['donation_progress'] ) && $widgets['donation_progress'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('donation_form')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Donation Form', 'skydonate' ); ?></span>
                <input id="donation_form" name="widgets[donation_form]" type="checkbox" 
                    <?php checked( isset( $widgets['donation_form'] ) && $widgets['donation_form'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('donation_card')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Donation Card', 'skydonate' ); ?></span>
                <input id="donation_card" name="widgets[donation_card]" type="checkbox" 
                    <?php checked( isset( $widgets['donation_card'] ) && $widgets['donation_card'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('impact_slider')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Impact Slider', 'skydonate' ); ?></span>
                <input id="impact_slider" name="widgets[impact_slider]" type="checkbox" 
                    <?php checked( isset( $widgets['impact_slider'] ) && $widgets['impact_slider'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('qurbani_status')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Qurbani Status', 'skydonate' ); ?></span>
                <input id="qurbani_status" name="widgets[qurbani_status]" type="checkbox" 
                    <?php checked( isset( $widgets['qurbani_status'] ) && $widgets['qurbani_status'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('extra_donation')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Extra Donation', 'skydonate' ); ?></span>
                <input id="extra_donation" name="widgets[extra_donation]" type="checkbox" 
                    <?php checked( isset( $widgets['extra_donation'] ) && $widgets['extra_donation'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('quick_donation')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Quick Donation', 'skydonate' ); ?></span>
                <input id="quick_donation" name="widgets[quick_donation]" type="checkbox" 
                    <?php checked( isset( $widgets['quick_donation'] ) && $widgets['quick_donation'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('gift_aid_toggle')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Gift Aid Toggle', 'skydonate' ); ?></span>
                <input id="gift_aid_toggle" name="widgets[gift_aid_toggle]" type="checkbox" 
                    <?php checked( isset( $widgets['gift_aid_toggle'] ) && $widgets['gift_aid_toggle'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('donation_button')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Donation Button', 'skydonate' ); ?></span>
                <input id="donation_button" name="widgets[donation_button]" type="checkbox" 
                    <?php checked( isset( $widgets['donation_button'] ) && $widgets['donation_button'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
            <?php if(sky_widget_status_check('icon_slider')): ?>
            <label class="skydonate-switcher">
                <span class="switch-label"><?php _e( 'Icon Slider', 'skydonate' ); ?></span>
                <input id="icon_slider" name="widgets[icon_slider]" type="checkbox" 
                    <?php checked( isset( $widgets['icon_slider'] ) && $widgets['icon_slider'] === 'on' ); ?>>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <?php endif; ?>
        </div>
        <div class="skydonate-footer">
            <label class="skydonate-switcher">
                <input id="toggleAll" type="checkbox" />
                <span class="switch-label enable"><?php _e( 'Enable All', 'skydonate' ); ?></span>
                <span class="switch-label disable"><?php _e( 'Disable All', 'skydonate' ); ?></span>
                <span class="switch-toggle">
                    <span class="switch-text on"><?php _e( 'On', 'skydonate' ); ?></span>
                    <span class="switch-text off"><?php _e( 'Off', 'skydonate' ); ?></span>
                </span>
            </label>
            <button type="submit" class="skydonation-button" ><?php _e( 'Save Settings', 'skydonate' ); ?></button>
        </div>
    </form>
</div>