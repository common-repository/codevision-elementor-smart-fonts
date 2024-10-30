<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.codevision.io
 * @since      1.0.0
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 * @author     codevision <info@codevision.io>
 */
class Codevision_Elementor_Smart_Fonts_i18n {


    public function __construct() {
        add_filter( 'plugin_locale', [ $this, 'handle_locales' ], 1, 2 );
        add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain'] );
    }

    /**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			ESF_SLUG,
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

		$poly_lang = new \Codevision\Language\ACFPolyLang( ESF_SLUG, [
			'esf-settings',
			'esf-license'
		], [
			'license_url'     => '"https://www.codevision.io/elementor-smart-fonts/?utm_source=plugin-installation&utm_medium=license-notice&utm_campaign=missing-license-key"',
			'license_target'  => '"_BLANK"',
			'agency_price'    => '65,95€',
			'single_price'    => '12,95€',
			'additional_info' => __( 'With both types your receive six month of personal support and 12 month free updates.', 'codevision-elementor-smart-fonts' )
		] );

		$poly_lang->init();

	}

	/**
	 *
	 * Translations for the setting page of this plugin
	 *
	 */
	protected function settings() {
		$i18n = [
			__( 'Use Own TrueType Font (TTF)', 'codevision-elementor-smart-fonts' ),
			__( 'Installed Fonts', 'codevision-elementor-smart-fonts' ),
			__( 'Here you can choose to upload a ttf file you want to use as webfont. We will generate the stylesheet for you. Below this field you will then find the corresponding css-class you have use to display your text in the specified font. The Font will also appear within the elementor font selection &laquo;Smart Fonts&raquo;.<br/>Attention: Always save the form after adding a new Font. You can add as many fonts as you please, but only one at a time. After saving the form the uploaded font will be converted and then removed from the wordpress media folder.', 'codevision-elementor-smart-fonts' ),
			__( 'Yes', 'codevision-elementor-smart-fonts' ),
			__( 'No', 'codevision-elementor-smart-fonts' ),
            __( 'Enable Font Preload', 'codevision-elementor-smart-fonts' ),
            __( 'Refresh Font Cache', 'codevision-elementor-smart-fonts' ),
            __( 'Here you can refresh the path information in the generated CSS Files in case you changed your base url or moved your website to another hoster.', 'codevision-elementor-smart-fonts' ),
            __( 'This option adds the preload directive for all custom fonts to the head part of your website. This will greatly reduce the loading time of your website. It is recommended to leave this option enabled.', 'codevision-elementor-smart-fonts' ),
		];
	}

	protected function license() {
		$i18n = [
			__( 'Notice', 'codevision-elementor-smart-fonts' ),
			__( 'License Key', 'codevision-elementor-smart-fonts' ),
			__( 'License Information', 'codevision-elementor-smart-fonts' ),
			__( 'Please enter your personal license key you received by email. If you do not own a license key, or your 30 day trial has been expired you can order your personal license key <a href={license_url} target={license_target}>here</a>. The single Site License is only <strong>{single_price}</strong>. If you plan to use the plugin on more than one site, please consider buying our <strong>agency</strong> license fore unlimited site usage for only <strong>{agency_price}</strong>. {additional_info}', 'codevision-elementor-smart-fonts' ),

			__( 'Could not verify the License Key. Please buy a License Key <a href=%s>here</a>. As long as no valid License Key is found, the Plugin will not work correctly.', 'codevision-elementor-smart-fonts' ),
			__( 'You are using Smart Fonts for Elementor without a valid License Key. You have <strong>%%d</strong> days left for your trial. Please consider buying <a target="_blank" href=%s>here</a>.', 'codevision-elementor-smart-fonts' ),
			__( 'You are using Smart Fonts for Elementor without a valid License Key. You have only <strong>one</strong> day left for your trial. Please consider buying <a target="_blank" href=%s>here</a>.', 'codevision-elementor-smart-fonts' ),

			__( 'You have a valid License Key, thank you for your support', 'codevision-elementor-smart-fonts' ),
			__( 'Your Product', 'codevision-elementor-smart-fonts' ),
			__( 'Last Payment', 'codevision-elementor-smart-fonts' ),
			__( 'Next Payment', 'codevision-elementor-smart-fonts' ),
			__( 'Paid until', 'codevision-elementor-smart-fonts' ),
			__( 'Trial Start', 'codevision-elementor-smart-fonts' ),
			__( 'Trial End', 'codevision-elementor-smart-fonts' ),
		];
	}

    protected function main() {
        $i18n = [
            __( 'Smart Fonts for Elementor', 'codevision-elementor-smart-fonts'),
            __( 'https://www.codevision.io/elementor-smart-fonts/', 'codevision-elementor-smart-fonts'),
            __( 'Adding your beloved TrueType Fonts to your website was never easier before. Comes also with full Elementor Styling Support!', 'codevision-elementor-smart-fonts'),
        ];
    }

    /**
     * Set the global english speaking domain to en_EN instead of using
     * en_UK, en_US and so on
     *
     * @param $locale
     * @param $text_domain
     *
     * @return string
     */
    function handle_locales( $locale, $text_domain ) {

        if ( $text_domain !== ESF_SLUG ) {
            return $locale;
        }

        if ( strpos( $locale, 'de_' ) !== false ) {
            return 'de_DE';
        }


        if ( strpos( $locale, 'en_' ) === false ) {
            return $locale;
        }

        return 'en_EN';
    }


}
