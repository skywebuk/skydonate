<?php
/**
 * Fired during plugin update - newly updated
 *
 * @link       http://wpgenie.org
 * @since      1.0.0
 *
 * @package    Skyeb_Donation_System_Update
 * @subpackage Skyeb_Donation_System_Update/includes
 */
class Skyeb_Donation_System_Update {
    private $update_url = 'https://skywebdesign.uk/wp-json/resource/v1/autoupdater/skyweb-donation-system';
    private $plugin_slug = 'skyweb-donation-system';
    private $plugin_file = 'skyweb-donation-system/skyweb-donation-system.php';

    public function __construct() {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_plugin_update']);
        add_filter('plugins_api', [$this, 'plugin_api_call'], 10, 3);
    }

    public function check_for_plugin_update($transient) {
        if (empty($transient->checked)) return $transient;

        $plugin_data = $this->fetch_remote_plugin_data();
        if (!$plugin_data) return $transient;

        $current_version = $transient->checked[$this->plugin_file] ?? '0.0.0';

        if (version_compare($plugin_data['version'], $current_version, '>')) {
            $update              = new stdClass();
            $update->slug        = $this->plugin_slug;
            $update->plugin      = $this->plugin_file;
            $update->new_version = $plugin_data['version'];
            $update->url         = $plugin_data['author_profile'];
            $update->package     = $plugin_data['download_url'];
            $update->tested      = $plugin_data['tested'] ?? '';
            $update->requires    = $plugin_data['requires'] ?? '';
            $update->requires_php= $plugin_data['requires_php'] ?? '';

            $transient->response[$this->plugin_file] = $update;
        }

        return $transient;
    }

    public function plugin_api_call($def, $action, $args) {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) return $def;

        $plugin_data = $this->fetch_remote_plugin_data();
        if (!$plugin_data) return $def;

        $info              = new stdClass();
        $info->name        = $plugin_data['name'];
        $info->slug        = $plugin_data['slug'];
        $info->version     = $plugin_data['version'];
        $info->author      = '<a href="' . esc_url($plugin_data['author_profile']) . '">' . esc_html($plugin_data['author']) . '</a>';
        $info->homepage    = esc_url($plugin_data['author_profile']);
        $info->download_link = esc_url($plugin_data['download_url']);
        $info->requires    = $plugin_data['requires'];
        $info->tested      = $plugin_data['tested'];
        $info->requires_php= $plugin_data['requires_php'];
        $info->sections    = $plugin_data['sections'];
        $info->banners     = $plugin_data['banners'];

        return $info;
    }

    private function fetch_remote_plugin_data() {
        $response = wp_remote_get($this->update_url, [
            'headers' => [
                'x_api_key'    => esc_attr(get_option('license_key')),
                'x_client_url' => home_url(),
            ]
        ]);

        if (is_wp_error($response)) return false;
        if (wp_remote_retrieve_response_code($response) !== 200) return false;

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data[0] ?? false;
    }
}
new Skyeb_Donation_System_Update();
