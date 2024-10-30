<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.codevision.io
 * @since      1.0.0
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/public
 * @author     codevision <info@codevision.io>
 */
class Codevision_Elementor_Smart_Fonts_Public {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_name The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * The Renderer
     *
     * @var \Codevision\View\TwigRenderer
     */
    private $renderer;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->renderer = new \Codevision\View\TwigRenderer( ESF_SLUG, false, ESF_ROOT . '/templates' );
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {
            return;
        }

        foreach ( $fonts as $font ) {
            $file = $font[ 'font_folder' ] . 'style.css';
            $hash = $this->version;

            if ( file_exists( $file ) ) {
                $hash = hash_file( 'sha256', $file );
            }

            wp_enqueue_style( $font[ 'font_name' ], $font[ 'stylesheet' ], array(), $hash, 'all' );
        }

    }

    function elementor_add_font_group( $groups ) {

        $groups[ 'esf' ] = __( 'Smart Fonts', 'elementor-smart-fonts' );

        return $groups;
    }

    function elementor_add_fonts( $elementor_fonts ) {

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {
            return $elementor_fonts;
        }

        foreach ( $fonts as $font ) {
            $elementor_fonts[ $font[ 'font_name' ] ] = 'esf';
        }

        return $elementor_fonts;
    }

    function preload_fonts() {

        $preload = get_transient( 'esf_font_preload' );

        if ( strpos( $preload, '<link rel="preload"' ) === 0 ) {
            echo $preload;

            return;
        }

        $preload = get_field( 'esf_font_preload', 'options' );

        if ( $preload === null ) {
            update_field( 'esf_font_preload', 'options', true );
            $preload = true;
        }

        if ( ! $preload ) {
            return;
        }

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {
            return;
        }

        $styles = [];
        foreach ( $fonts as $font ) {
            $styles = array_merge( $styles, $this->render_preload_font( $font ) );
        }

        if ( ! $styles ) {
            return;
        }

        $preload = implode( '', $styles );

        set_transient( 'esf_font_preload', $preload, 24 * HOUR_IN_SECONDS );

        echo $preload;
    }

    private function get_font_from_css( $css_string, $format ) {

        $regex = "/src: url\((.+?)\) format\('$format'\);/";

        if ( preg_match( $regex, $css_string, $matches ) !== 1 ) {
            return false;
        }

        return $matches[ 1 ];
    }

    private function render_preload_font_bc( $font ) {

        if ( ! array_key_exists( 'font_folder', $font ) ) {
            return [];
        }

        $style = $font[ 'font_folder' ] . 'style.css';

        if ( ! file_exists( $style ) ) {
            return [];
        }

        $content = file_get_contents( $style );

        $font = $this->get_font_from_css( $content, 'woff2' );

        if ( $font === false ) {
            return [];
        }

        return [
            $this->renderer->view( 'preload', [ 'font_name' => $font, 'font_format' => 'woff2' ] ),
        ];
    }

    private function render_preload_font( $font ) {

        if ( ! array_key_exists( 'styles', $font ) ) {
            return $this->render_preload_font_bc( $font );
        }

        $preload = [];

        $allowed_preload = array_filter( $font[ 'styles' ],
            function( $type ) {

                return in_array( $type, [ 'woff', 'woff2' ] );

            },
            ARRAY_FILTER_USE_KEY );

        foreach ( $allowed_preload as $type => $content ) {

            $font = $this->get_font_from_css( $content, $type );

            if ( $font === false ) {
                continue;
            }

            $preload[] = $this->renderer->view( 'preload', [ 'font_name' => $font, 'font_format' => $type ] );
        }

        return $preload;
    }

}
