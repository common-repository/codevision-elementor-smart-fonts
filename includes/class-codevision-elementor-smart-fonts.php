<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.codevision.io
 * @since      1.0.0
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 */

use Codevision\Licensing\Manager;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 * @author     codevision <info@codevision.io>
 */
class Codevision_Elementor_Smart_Fonts {



	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Codevision_Elementor_Smart_Fonts_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The License Manager
	 *
	 * @var Manager
	 */
	private $license_manager;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 *
	 * @param Manager $license_manager
	 */
	public function __construct( $license_manager ) {

		if ( defined( 'ESF_PLUGIN_VERSION' ) ) {
			$this->version = ESF_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = ESF_SLUG;
		$this->license_manager = $license_manager;

		$this->load_dependencies();
		$this->define_admin_hooks();
        $this->define_public_hooks();

        $this->loader->run();

        add_filter( 'http_request_timeout', function( $timeout, $url ) {

            if ( strpos( $url, 'license.codevision.io' ) === false ) {
                return $timeout;
            }

            return 10;
        },10,2 );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Codevision_Elementor_Smart_Fonts_Loader. Orchestrates the hooks of the plugin.
	 * - Codevision_Elementor_Smart_Fonts_i18n. Defines internationalization functionality.
	 * - Codevision_Elementor_Smart_Fonts_Admin. Defines all hooks for the admin area.
	 * - Codevision_Elementor_Smart_Fonts_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-codevision-elementor-smart-fonts-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-codevision-elementor-smart-fonts-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-codevision-elementor-smart-fonts-public.php';

		$this->loader = new Codevision_Elementor_Smart_Fonts_Loader();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Codevision_Elementor_Smart_Fonts_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'acf/prepare_field/key=field_5bc05efaf3213', $plugin_admin, 'render_user_font_info' );

		$this->loader->add_filter( 'acf/save_post', $plugin_admin, 'handle_user_font' );
		$this->loader->add_filter( 'upload_mimes', $plugin_admin, 'allow_upload_types' );
        $this->loader->add_filter( 'wp_check_filetype_and_ext', $plugin_admin, 'fix_wp_check_filetype_and_ext', 10, 4 );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_page' );

		$this->loader->add_action( 'wp_ajax_esf-user-font-removal', $plugin_admin, 'ajax_user_font_removal' );
        $this->loader->add_action( 'wp_ajax_esf-user-font-refresh', $plugin_admin, 'ajax_user_font_refresh' );

		add_filter('cv_widget_id', function(){
		    return 'cv_elementor_smart_fonts';
        });

        add_filter('cv_widget_name', function(){
            return 'Smart Fonts for Elementor';
        });

        add_filter('cv_widget_url', function(){

            $base_url = 'https://www.codevision.io/en/';
            $base_param = '?utm_source=elementor-smart-fonts&utm_medium=wp-dashboard&utm_content=' . urlencode(site_url());

            if ( $this->license_manager->get_trial_period() > 0 ) {
                $base_url .= 'plugin-trial/';
                $base_url .= $base_param . '&utm_campaign=trial-version-active&trial_left=' . $this->license_manager->get_trial_period();
            } else if ( $this->license_manager->is_valid() ) {
                $base_url .= 'plugin-purchased/';
                $base_url .= $base_param . '&utm_campaign=paid-version-active';
            } else {
                $base_url .= 'plugin-expired/';
                $base_url .= $base_param . '&utm_campaign=trial-version-expired&when=' . $this->license_manager->get_trial_period();
            }

            return $base_url;

        });
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		if ( ! $this->license_manager->is_valid() ) {
			return;
		}

		$plugin_public = new Codevision_Elementor_Smart_Fonts_Public( $this->get_plugin_name(), $this->get_version() );


		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_head', $plugin_public, 'preload_fonts' );

		$this->loader->add_filter( 'elementor/fonts/additional_fonts', $plugin_public, 'elementor_add_fonts' );
		$this->loader->add_filter( 'elementor/fonts/groups', $plugin_public, 'elementor_add_font_group' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Codevision_Elementor_Smart_Fonts_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
