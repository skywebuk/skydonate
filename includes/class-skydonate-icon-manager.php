<?php
use Elementor\Icons_Manager; // Correct use statement
use Elementor\Utils; // Correct use statement

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Icon Manager Addons
 */
class Skydonate_Icon_Manager {

    public static function elementor_version_check( $operator = '<', $version = '2.6.0' ) {
        return defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, $version, $operator );
    }

    private static function render_svg_icon( $value ) {
        if ( ! isset( $value['id'] ) ) {
            return '';
        }

        return self::elementor_version_check( '>=', '3.5.0' ) ? 
            \Elementor\Core\Files\File_Types\Svg::get_inline_svg( $value['id'] ) : 
            \Elementor\Core\Files\Assets\Svg\Svg_Handler::get_inline_svg( $value['id'] );
    }    

    private static function render_icon_html( $icon, $attributes = [], $tag = 'i' ) {
        $icon_types = Icons_Manager::get_icon_manager_tabs();
        if ( isset( $icon_types[ $icon['library'] ]['render_callback'] ) && is_callable( $icon_types[ $icon['library'] ]['render_callback'] ) ) {
            return call_user_func_array( $icon_types[ $icon['library'] ]['render_callback'], [ $icon, $attributes, $tag ] );
        }

        if ( empty( $attributes['class'] ) ) {
            $attributes['class'] = $icon['value'];
        } else {
            if ( is_array( $attributes['class'] ) ) {
                $attributes['class'][] = $icon['value'];
            } else {
                $attributes['class'] .= ' ' . $icon['value'];
            }
        }
        return '<' . $tag . ' ' . Utils::render_html_attributes( $attributes ) . '></' . $tag . '>';
    }

    public static function render_icon( $icon, $attributes = [], $tag = 'i' ) {
        if ( empty( $icon['library'] ) ) {
            return false;
        }

        // Handle SVG Icons
        if ( 'svg' === $icon['library'] ) {
            return self::render_svg_icon( $icon['value'] );
        }

        // Handle other icons
        return self::render_icon_html( $icon, $attributes, $tag );
    }
}
