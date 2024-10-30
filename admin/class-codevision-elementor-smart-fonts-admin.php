<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.codevision.io
 * @since      1.0.0
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/admin
 */

use \Codevision\View\TwigRenderer;
use \Codevision\ESF\Helper;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/admin
 * @author     codevision <info@codevision.io>
 */
class Codevision_Elementor_Smart_Fonts_Admin {

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
     * @var TwigRenderer
     */
    private $renderer;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version The version of this plugin.
     *
     * @since    1.0.0
     *
     */
    public function __construct( $plugin_name, $version ) {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->renderer = new TwigRenderer( ESF_SLUG, false, ESF_ROOT . '/templates' );
    }

    /**
     * Add Menu to Page
     */
    public function add_admin_page() {

        //$img_path = 'data:image/png;base64,' . base64_encode( file_get_contents( ESF_ROOT . '/admin/img/admin-elementor-smart-fonts.png' ) );
        $img_path = 'data:image/svg+xml;base64,' . base64_encode( file_get_contents( ESF_ROOT . '/admin/img/admin-elementor-smart-fonts.svg' ) );

        acf_add_options_page( array(
            'page_title' => __( 'Smart Fonts', 'codevision-elementor-smart-fonts' ),
            'menu_title' => __( 'Smart Fonts', 'codevision-elementor-smart-fonts' ),
            'menu_slug'  => ESF_SLUG,
            'capability' => 'manage_options',
            'icon_url'   => $img_path,
            'redirect'   => true,
        ) );

        acf_add_options_sub_page( array(
            'page_title'  => __( 'Settings', 'codevision-elementor-smart-fonts' ),
            'menu_title'  => __( 'Settings', 'codevision-elementor-smart-fonts' ),
            'parent_slug' => ESF_SLUG,
            'slug'        => 'acf-options-esf-settings',
            'icon_url'    => $img_path,
        ) );

        acf_add_options_sub_page( array(
            'page_title'  => __( 'License', 'codevision-elementor-smart-fonts' ),
            'menu_title'  => __( 'License', 'codevision-elementor-smart-fonts' ),
            'parent_slug' => ESF_SLUG,
            'icon_url'    => $img_path,
            'slug'        => 'acf-options-esf-license',
        ) );

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . "css/{$this->plugin_name}.min.css", array(), $this->version, 'all' );
        wp_enqueue_style( 'fontawesome', plugin_dir_url( __FILE__ ) . 'css/all.css', array(), $this->version, 'all' );
    }

    public function is_plugin_screen() {

        $current_screen = get_current_screen();

        // We cannot extract the current screen thus no decision can be made
        if ( ! $current_screen ) {
            return false;
        }

        // only if we are within our own plugin screen, the plugin name is contained within the id of the screen
        return strpos( $current_screen->id, 'esf-' ) !== false;
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        if ( ! $this->is_plugin_screen() ) {
            return;
        }

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . "js/{$this->plugin_name}.min.js", array( 'jquery' ), $this->version, false );

        wp_localize_script( $this->plugin_name,
            'esf_nonces',
            array(
                'esf_user_font_removal' => wp_create_nonce( "esf-user-font-removal" ),
                'esf_user_font_refresh' => wp_create_nonce( "esf-user-font-refresh" ),
            ) );
    }

    function allow_upload_types( $mime_types ) {

        $mime_types[ 'ttf' ] = 'application/x-font-ttf|application/octet-stream|font/ttf|font/sfnt|application/font-sfnt';

        return $mime_types;
    }

    function fix_wp_check_filetype_and_ext( $data, $file, $filename, $mimes ) {

        if ( ! empty( $data[ 'ext' ] ) && ! empty( $data[ 'type' ] ) ) {
            return $data;
        }

        $allowed_ext = [];

        $allowed_ext = $this->allow_upload_types( $allowed_ext );

        $filetype = wp_check_filetype( $filename, $mimes );

        if ( ! isset( $allowed_ext[ $filetype[ 'ext' ] ] ) ) {
            return $data;
        }

        return [
            'ext'             => $filetype[ 'ext' ],
            'type'            => $filetype[ 'type' ],
            'proper_filename' => $data[ 'proper_filename' ],
        ];
    }

    function handle_user_font() {

        $screen = get_current_screen();

        if ( strpos( $screen->id, "acf-options-esf-settings" ) === false ) {
            return;
        }

        if ( ! array_key_exists( 'field_5bc05e26f3212', $_POST[ 'acf' ] ) ) {
            return;
        }

        delete_transient( 'esf_font_preload' );

        $attachment_id = $_POST[ 'acf' ][ 'field_5bc05e26f3212' ] ?? false;

        if ( $attachment_id === false ) {
            return;
        }

        $attachment_id = intval( sanitize_text_field( $attachment_id ) );

        if ( ! $attachment_id ) {
            return;
        }

        $font_family = pathinfo( get_attached_file( $attachment_id ), PATHINFO_FILENAME );

        $font_family = sanitize_title( $font_family );

        // By now, set the font name to the already sanitized font family
        $font_name = $font_family;

        $font_folder = Helper::get_cache_folder( [ $font_family ] );
        $user_url    = Helper::get_cache_url( [ $font_family ] );

        // We have already downloaded this file, or folder already exists
        if ( is_dir( $font_folder ) ) {

            wp_delete_attachment( $attachment_id );

            return;
        }

        // Get the Zip File from the Webservice
        $zip_data = Helper::get_font_file( $attachment_id );

        // Do not proceed if we encountered an error
        if ( ! $zip_data ) {
            return;
        }

        // Create the font-folder
        mkdir( $font_folder, 0777, true );

        $zip_file = $font_folder . '/' . $font_family . '.zip';

        // Store the Zip File in the folder
        file_put_contents( $zip_file, $zip_data );

        // Extract the zip file into the new folder
        $zip = new ZipArchive;
        $res = $zip->open( $zip_file );

        // In case of error remove zip file
        if ( $res !== true ) {

            Helper::rmdir( $font_folder );

            return;
        }

        $zip->extractTo( $font_folder );
        $zip->close();

        // Remove zip File
        unlink( $zip_file );

        //Gather file info and create the style.css
        $styles = [];
        foreach ( glob( $font_folder . '*' ) as $font ) {

            // Skip the original ttf file
            if ( strpos( $font, '/original-' ) ) {
                unlink( $font );
                continue;
            }

            $font_format = strtolower( pathinfo( $font, PATHINFO_EXTENSION ) );

            $font_source = $user_url . basename( $font );

            switch ( $font_format ) {
                case 'eot':
                    $styles[ $font_format ] = $this->renderer->view( 'font-templates/eot', compact( 'font_source', 'font_family', 'font_format' ) );
                    break;
                case 'woff':
                case 'woff2':
                case 'svg':
                    $styles[ $font_format ] = $this->renderer->view( 'font-templates/woff-woff2-svg', compact( 'font_source', 'font_family', 'font_format' ) );
                    break;
                case 'ttf':
                    $styles[ $font_format ] = $this->renderer->view( 'font-templates/ttf', compact( 'font_source', 'font_family', 'font_format' ) );
            }

        }

        // If we did not find any font files, remove the font folder alltogether
        if ( ! $styles ) {

            Helper::rmdir( $font_folder );

            return;
        }
        $styles[ 'local' ] = $this->renderer->view( 'font-templates/local', compact( 'font_family', 'font_name' ) );
        $styles[ 'class' ] = $this->renderer->view( 'font-templates/style', compact( 'font_family', 'font_name' ) );

        uksort( $styles,
            function( $font_format_a, $font_format_b ) {

                $a = $this->get_font_weight( $font_format_a );
                $b = $this->get_font_weight( $font_format_b );

                return $a <=> $b;
            } );

        file_put_contents( $font_folder . 'style.css', implode( PHP_EOL, array_values( $styles ) ) );

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {
            $fonts = [];
        }

        $stylesheet = $user_url . 'style.css';

        $fonts[] = compact( 'font_name', 'font_family', 'font_folder', 'stylesheet', 'styles' );

        update_option( 'esf_user_fonts', $fonts );

        // Remove the attachment and unset the corresponding attachment id in the settings form
        wp_delete_attachment( $attachment_id );

        $_POST[ 'acf' ][ 'field_5bc05e26f3212' ] = '';
    }

    private function verify_ajax_args( $action ) {

        check_ajax_referer( $action, 'security' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json( [
                'error' => 'You are not allowed to refresh fonts',
            ] );

            exit;
        }

        $font_family = sanitize_text_field( $_REQUEST[ 'font' ] ?? false );

        if ( ! $font_family ) {
            wp_send_json( [
                'error' => 'Font Payload not found',
            ] );

            exit;
        }

        if ( strpos( $font_family, '/' ) !== false ) {
            wp_send_json( [
                'error' => 'Font Payload contains illegal characters',
            ] );

            exit;
        }

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {

            wp_send_json( [
                'error' => 'Font option not found',
            ] );

            exit;
        }

        $new_fonts = array_filter( $fonts,
            function( $font ) use ( $font_family ) {

                return $font[ 'font_family' ] !== $font_family;
            } );

        if ( sizeof( $new_fonts ) === sizeof( $fonts ) ) {

            wp_send_json( [
                'error' => 'Font family not found in font storage',
            ] );

            exit;
        }

        return $font_family;
    }

    function ajax_user_font_refresh() {

        $font_family = $this->verify_ajax_args( 'esf-user-font-refresh' );

        $font_folder = Helper::get_cache_folder( [ $font_family ] );

        $file = $font_folder . 'style.css';

        if ( ! file_exists( $file ) ) {
            wp_send_json( [
                'error' => 'CSS Style not found in folder!',
            ] );

            exit;
        }

        $regex = "/url\(([^(){}]+?)\)/";

        $css_string = file_get_contents( $file );

        if ( preg_match_all( $regex, $css_string, $matches ) === false ) {
            wp_send_json( [
                'error' => 'No URL directive found in css file!',
            ] );

            exit;
        }

        if ( sizeof( $matches ) !== 2 ) {
            wp_send_json( [
                'error' => 'No URL directive found in css file!',
            ] );

            exit;
        }

        $is_replace = false;

        foreach( $matches[1] as $url ) {
            $parts = explode(ESF_CACHE_NAME, $url);

            $part = end($parts);

            $new_url = Helper::get_cache_url() . ltrim($part, '/');

            if ( $new_url === $url ) {
                continue;
            }

            $css_string = str_replace( $url, $new_url, $css_string );
            $is_replace = true;
        }

        if ( ! $is_replace ) {
            wp_send_json( [
                'done' => true,
            ] );

            exit;
        }

        file_put_contents( $file, $css_string );

        $fonts = get_option( 'esf_user_fonts' );

        foreach( $fonts as & $font ) {
            $stylesheet = $font['stylesheet'];

            $parts = explode(ESF_CACHE_NAME, $stylesheet);

            $part = end($parts);

            $font['stylesheet'] = Helper::get_cache_url() . ltrim($part, '/');
        }

        update_option('esf_user_fonts', $fonts);

        wp_send_json( [
            'done' => true,
        ] );

        exit;
    }


    function ajax_user_font_removal() {

        $font_family = $this->verify_ajax_args( 'esf-user-font-removal' );

        $fonts = get_option( 'esf_user_fonts' );

        $new_fonts = array_filter( $fonts,
            function( $font ) use ( $font_family ) {

                return $font[ 'font_family' ] !== $font_family;
            } );

        update_option( 'esf_user_fonts', $new_fonts );

        $user_folder = Helper::get_cache_folder( [ $font_family ] );

        if ( ! is_dir( $user_folder ) ) {
            wp_send_json( [
                'error' => 'Font folder not found',
            ] );

            exit;
        }

        Helper::rmdir( $user_folder );

        wp_send_json( [
            'reload' => true,
        ] );

        exit;
    }

    function render_user_font_info( $field ) {

        $fonts = get_option( 'esf_user_fonts' );

        if ( ! $fonts ) {
            return $field;
        }

        $style = '<p class="esf-style" data-family="{font_family}">CSS-Class: <span style="font-size: 16px; color: #f05c56;">esf-{font_name}</span> <a href="{stylesheet}" target="_blank">{stylesheet}</a> <span class="esf-{font_name}" style="margin: 0 15px; display: inline-block;">The quick brown fox jumps over the lazy dog</span>{delete}</p>';

        $list = [];

        $engine = new \StringTemplate\Engine();

        foreach ( $fonts as $font ) {

            $font[ 'delete' ] = $this->renderer->view( 'button',
                [
                    'class' => 'esf-user-font-removal',
                    'title' => __( 'Remove Font', 'codevision-elementor-smart-fonts' ),
                    'data'  => [
                        'font' => $font[ 'font_family' ],
                    ],
                ] );

            $list[] = $engine->render( $style, $font );

            wp_enqueue_style( $font[ 'font_name' ], $font[ 'stylesheet' ], array(), $this->version, 'all' );
        }

        $field[ 'message' ] = implode( '', $list );

        return $field;
    }

    private function get_font_weight( $font_format ) {

        switch ( $font_format ) {
            case 'local':
                return 5;
            case 'eot':
                return 50;
            case 'woff':
                return 20;
            case 'woff2':
                return 10;
            case 'svg':
                return 30;
            case 'ttf':
                return 40;
            default:
                return 60;
        }
    }
}
