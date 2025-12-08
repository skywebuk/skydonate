<?php
/**
 * SkyDonate Remote Functions
 *
 * This file contains functions that are loaded lazily, only after
 * license verification. These functions handle downloading and
 * activating remote widgets from the license server.
 *
 * IMPORTANT: This file should NOT be loaded directly. It is loaded
 * through the SkyDonate_Remote_Functions_Handler after verifying
 * the license key and domain.
 *
 * @package SkyDonate
 * @since 1.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Prevent direct loading without going through the handler
if ( ! did_action( 'wp_ajax_skydonate_activate_remote_widget' ) && ! defined( 'SKYDONATE_LOADING_REMOTE_FUNCTIONS' ) ) {
    // Allow loading if explicitly permitted
    if ( ! apply_filters( 'skydonate_allow_direct_remote_functions', false ) ) {
        return;
    }
}

/**
 * Process system properties and activate widgets
 *
 * @param array $args Configuration array containing 'setup' and 'zip_url'
 * @return void
 */
function skydonate_system_properties( $args ) {
    if ( ! isset( $args['setup'] ) || ! isset( $args['zip_url'] ) ) {
        return;
    }

    $setup          = $args['setup'];
    $zip_url        = $args['zip_url'];
    $active_widgets = json_decode( $setup, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        error_log( '[SkyDonate] Invalid JSON in setup configuration' );
        return;
    }

    if ( isset( $active_widgets['setup_widgets'] ) ) {
        $enabled_widgets = $active_widgets['setup_widgets'];

        if ( ! empty( $enabled_widgets ) && is_array( $enabled_widgets ) ) {
            foreach ( $enabled_widgets as $enabled_widget => $value ) {
                skydonate_activate_target_widget( $enabled_widget, $zip_url );
            }
        }
    }
}

/**
 * Activate a specific widget by downloading from remote URL
 *
 * @param string $enabled_widget Widget identifier
 * @param string $zip_url URL to the ZIP file containing widget files
 * @return bool True on success, false on failure
 */
function skydonate_activate_target_widget( $enabled_widget, $zip_url ) {
    $widgets = skydonate_widget_list();

    if ( ! isset( $widgets[ $enabled_widget ] ) || empty( $widgets[ $enabled_widget ] ) ) {
        return false;
    }

    // Validate ZIP URL
    if ( ! filter_var( $zip_url, FILTER_VALIDATE_URL ) ) {
        error_log( '[SkyDonate] Invalid ZIP URL provided' );
        return false;
    }

    $zip_path   = SKYDONATE_INCLUDES_PATH . '/temp.zip';
    $extract_to = SKYDONATE_INCLUDES_PATH . '/addons/';

    // Download ZIP file
    $download_result = skydonate_download_file( $zip_url, $zip_path );

    if ( $download_result !== true ) {
        error_log( '[SkyDonate] Download failed: ' . $download_result );
        return false;
    }

    // Ensure addons directory exists
    if ( ! file_exists( $extract_to ) ) {
        wp_mkdir_p( $extract_to );
    }

    $success = true;

    foreach ( $widgets[ $enabled_widget ] as $widget ) {
        $target_file = 'skydonate/includes/addons/class-skydonate-addon-' . $widget . '.php';

        if ( ! skydonate_extract_target_file( $zip_path, $extract_to, $target_file ) ) {
            $success = false;
        }
    }

    // Clean up temporary file
    if ( file_exists( $zip_path ) ) {
        wp_delete_file( $zip_path );
    }

    return $success;
}

/**
 * Extract a specific file from a ZIP archive
 *
 * @param string $zip_path Path to the ZIP file
 * @param string $extract_to Destination directory
 * @param string $target_file File to extract from the ZIP
 * @return bool True on success, false on failure
 */
function skydonate_extract_target_file( $zip_path, $extract_to, $target_file ) {
    if ( ! class_exists( 'ZipArchive' ) ) {
        error_log( '[SkyDonate] ZipArchive class not available' );
        return false;
    }

    $zip = new ZipArchive();

    if ( $zip->open( $zip_path ) !== true ) {
        error_log( '[SkyDonate] Failed to open ZIP file: ' . $zip_path );
        return false;
    }

    // Ensure extract directory exists
    if ( ! file_exists( $extract_to ) ) {
        wp_mkdir_p( $extract_to );
    }

    if ( $zip->locateName( $target_file ) === false ) {
        $zip->close();
        error_log( '[SkyDonate] Target file not found in ZIP: ' . $target_file );
        return false;
    }

    $content = $zip->getFromName( $target_file );

    if ( $content === false ) {
        $zip->close();
        error_log( '[SkyDonate] Failed to read file from ZIP: ' . $target_file );
        return false;
    }

    $file_name_only = basename( $target_file );
    $destination    = $extract_to . $file_name_only;

    // Use WordPress filesystem for writing
    global $wp_filesystem;

    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ( $wp_filesystem ) {
        $result = $wp_filesystem->put_contents( $destination, $content, FS_CHMOD_FILE );
    } else {
        // Fallback to file_put_contents
        $result = file_put_contents( $destination, $content );
    }

    $zip->close();

    if ( $result === false ) {
        error_log( '[SkyDonate] Failed to write extracted file: ' . $destination );
        return false;
    }

    return true;
}

/**
 * Download a file from URL to local path
 *
 * @param string $url URL to download from
 * @param string $path Local path to save to
 * @return bool|string True on success, error message on failure
 */
function skydonate_download_file( $url, $path ) {
    // Validate URL
    if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
        return __( 'Invalid URL provided', 'skydonate' );
    }

    // Use WordPress HTTP API first (preferred method)
    $response = wp_remote_get( $url, array(
        'timeout'     => 60,
        'sslverify'   => true,
        'redirection' => 5,
    ) );

    if ( is_wp_error( $response ) ) {
        // Fallback to cURL if WordPress HTTP API fails
        return skydonate_download_file_curl( $url, $path );
    }

    $status_code = wp_remote_retrieve_response_code( $response );

    if ( $status_code !== 200 ) {
        return sprintf( __( 'Download failed with status code: %d', 'skydonate' ), $status_code );
    }

    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        return __( 'Downloaded file is empty', 'skydonate' );
    }

    // Use WordPress filesystem for writing
    global $wp_filesystem;

    if ( empty( $wp_filesystem ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ( $wp_filesystem ) {
        $result = $wp_filesystem->put_contents( $path, $body, FS_CHMOD_FILE );
    } else {
        $result = file_put_contents( $path, $body );
    }

    if ( $result === false ) {
        return __( 'Failed to write downloaded file', 'skydonate' );
    }

    return true;
}

/**
 * Download a file using cURL (fallback method)
 *
 * @param string $url URL to download from
 * @param string $path Local path to save to
 * @return bool|string True on success, error message on failure
 */
function skydonate_download_file_curl( $url, $path ) {
    if ( ! function_exists( 'curl_init' ) ) {
        return __( 'cURL is not available', 'skydonate' );
    }

    $ch = curl_init( $url );
    $fp = fopen( $path, 'w+' );

    if ( $fp === false ) {
        return __( 'Failed to open file for writing', 'skydonate' );
    }

    curl_setopt( $ch, CURLOPT_FILE, $fp );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_FAILONERROR, true );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );

    $success = curl_exec( $ch );
    $error   = curl_error( $ch );

    curl_close( $ch );
    fclose( $fp );

    if ( ! $success ) {
        // Clean up failed download
        if ( file_exists( $path ) ) {
            wp_delete_file( $path );
        }
        return $error ?: __( 'Download failed', 'skydonate' );
    }

    return true;
}

/**
 * Get list of available widgets
 *
 * @return array Widget configuration array
 */
function skydonate_widget_list() {
    return array(
        'zakat_calculator'        => array( 'zakat-calculator-addons' ),
        'zakat_calculator_classic' => array( 'zakat-calculator-classic', 'zakat-preview' ),
        'metal_values'            => array( 'metal-values-addons' ),
        'recent_order'            => array( 'recent-order-addon', 'recent-order-addon-2' ),
        'donation_progress'       => array( 'progress-addon', 'progress-addon-2' ),
        'donation_form'           => array( 'form-addon', 'form-addon-2', 'form-addon-3' ),
        'donation_card'           => array( 'card-addon', 'card-addon-2' ),
        'impact_slider'           => array( 'impact-slider' ),
        'qurbani_status'          => array( 'qurbani-status' ),
        'donation_button'         => array( 'button' ),
        'icon_slider'             => array( 'icon-list' ),
    );
}
