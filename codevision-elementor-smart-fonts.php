<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.codevision.io
 * @since             1.0.0
 * @package           Codevision_Elementor_Smart_Fonts
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Fonts for Elementor
 * Plugin URI:        https://www.codevision.io/elementor-smart-fonts
 * Description:       Adding your beloved TrueType Fonts to your website was never easier before. Comes also with full Elementor Styling Support!
 * Version:           2.1.5
 * Elementor tested up to: 3.6.6
 * Elementor Pro tested up to: 3.7.1
 * Author:            codevision
 * Author URI:        https://www.codevision.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       codevision-elementor-smart-fonts
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

include_once __DIR__ . '/vendor/autoload.php';

define( 'ESF_PLUGIN_VERSION', '2.1.5' );
define( 'ESF_ROOT', __DIR__ );
define( 'ESF_SLUG', 'codevision-elementor-smart-fonts' );
define( 'ESF_NAMESPACE', 'esf-' );
define( 'ESF_CACHE_NAME', 'esf-cache');

define( 'ESF_MIN_PHP', '7.4' );
define( 'ESF_MIN_WP', '5.9.0' );

function activate_codevision_elementor_smart_fonts() {

    Codevision_Elementor_Smart_Fonts_Activator::activate();
}

function deactivate_codevision_elementor_smart_fonts() {

    Codevision_Elementor_Smart_Fonts_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_codevision_elementor_smart_fonts' );


function esf_get_license_manager() {

    $trial_link           = '"https://www.codevision.io/elementor-smart-fonts/?utm_source=plugin-installation&utm_medium=license-notice&utm_campaign=trial-version-active"';
    $missing_license_link = '"https://www.codevision.io/elementor-smart-fonts/?utm_source=plugin-installation&utm_medium=license-warning&utm_campaign=trial-version-expired"';

    $parts        = [
        WP_CONTENT_DIR,
        ESF_CACHE_NAME,
    ];

    $cache_folder = implode( '/', $parts );
    $log_folder   = __DIR__ . '/log';
    $licenseServerUrl = 'https://license.codevision.io';

    $env = new \Codevision\Environment();

    $env->setPluginName( 'Smart Fonts for Elementor' )
        ->setVersion( ESF_PLUGIN_VERSION )
        ->setBaseFile( __FILE__ )
        ->setBasePath( __DIR__ )
        ->setSlug( ESF_SLUG )
        ->setBaseUrl( plugin_dir_url( __FILE__ ) )
        ->setVendorUrl( plugin_dir_url( __FILE__ ) . 'vendor' );

    $manager = \Codevision\Licensing\Manager::getInstanceWithCompatiblityCheck( $env );

    $manager->setApiServer( $licenseServerUrl )
            ->setAuthKey( '92E3C052-CE75-4318-96E0-0030CD5C758C' )
            ->setNamespace( ESF_NAMESPACE )
            ->setLicenseInfoField( 'field_5bd1dbb456d02' )
            ->setLicenseKeyName( 'esf_license' )
            ->setLicenseViewSlug( 'acf-options-esf-license' )
            ->setMessages( [
                'headline'                     => 'Smart Fonts for Elementor',
                'missing_license_admin_notice' => [
                    'Could not verify the License Key. Please buy a License Key <a href=%s>here</a>. As long as no valid License Key is found, the Plugin will not work correctly.',
                    $missing_license_link,
                ],
                'trial_period_admin_notice'    => [
                    'You are using Smart Fonts for Elementor without a valid License Key. You have <strong>%%d</strong> days left for your trial. Please consider buying <a target="_blank" href=%s>here</a>.',
                    $trial_link,
                ],
                'trial'                        => [
                    'You are using Smart Fonts for Elementor without a valid License Key. You have <strong>%%d</strong> days left for your trial. Please consider buying <a target="_blank" href=%s>here</a>.',
                    $trial_link,
                ],
                'trial_one_day_left'           => [
                    'You are using Smart Fonts for Elementor without a valid License Key. You have only <strong>one</strong> day left for your trial. Please consider buying <a target="_blank" href=%s>here</a>.',
                    $trial_link,
                ],
            ] )
            ->getCompatibilityCheck()
            ->setSuccessFunction( function() use ( $manager ) {

                register_deactivation_hook( __FILE__, 'deactivate_codevision_elementor_smart_fonts' );

                $init = new Codevision_Elementor_Smart_Fonts( $manager );
                $init->run();

            } )
            ->setTitle( 'Smart Fonts for Elementor Compatibility Check' )
            ->setEscalationScore( 20 )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'License server secure connection test [ %s ] ', $licenseServerUrl ),
                sprintf( 'We cannot make a secure connection to our license server @ %s! Please check your web-firewall, security plugin(s) and hosting settings!', $licenseServerUrl ),
                function() use ( $licenseServerUrl ) {

                    $response = wp_remote_get( $licenseServerUrl );

                    return ! ( $response instanceof \WP_Error );
                }, 20 ) )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'PHP Version Check [ Required at least %s] ', ESF_MIN_PHP ),
                sprintf( 'Your PHP Version %s is too old, please upgrade to at least version %s to use this plugin!', PHP_VERSION, ESF_MIN_PHP ),
                function() {

                    return version_compare( PHP_VERSION, ESF_MIN_PHP, '>=' );
                },
                20 ) )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'Wordpress Version Check [ Required at least %s ]', ESF_MIN_WP ),
                sprintf( 'Your Wordpress version is too old for this plugin. At least, Wordpress version %s is required!', ESF_MIN_WP ),
                function() {

                    global $wp_version;

                    return version_compare( $wp_version, ESF_MIN_WP, '>=' );
                },
                20 ) )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'PHP Extensions ZIP' ), sprintf( 'To use this plugin the PHP Extension ZIP has to be enabled!' ), function() {

                return extension_loaded( 'zip' );
            }, 20 ) )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'Testing Writing permissions to log folder [ %s ]', $log_folder ),
                sprintf( 'Could not write to log folder %s , logging will be disabled!', $log_folder ),
                function() use ( $log_folder ) {

                    if ( ! is_dir( $log_folder ) && ! @mkdir( $log_folder ) ) {
                        return false;
                    }

                    $test_file = $log_folder . '/.test';

                    if ( ! @touch( $test_file ) ) {
                        return false;
                    }

                    if ( ! @unlink( $test_file ) ) {
                        return false;
                    }

                    return true;
                },
                10 ) )
            ->add( new \Codevision\Compatibility\SimpleCheck( sprintf( 'Testing Writing permissions to font cache folder [ %s ]', $cache_folder ),
                sprintf( 'Could not write to cache folder, please create the folder \'%s\' with read, write and delete permissions!', $cache_folder ),
                function() use ( $cache_folder ) {

                    if ( ! is_dir( $cache_folder ) && ! @mkdir( $cache_folder ) ) {
                        return false;
                    }

                    $test_file = $cache_folder . '/.test';

                    if ( ! @touch( $test_file ) ) {
                        return false;
                    }

                    if ( ! @unlink( $test_file ) ) {
                        return false;
                    }

                    return true;
                },
                10 ) );


    return $manager;
}

new Codevision_Elementor_Smart_Fonts_i18n();

add_action( 'acf/init', function() {
    $manager = esf_get_license_manager();
    $manager->init();
});
