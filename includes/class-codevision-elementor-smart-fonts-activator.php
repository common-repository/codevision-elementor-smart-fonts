<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.codevision.io
 * @since      1.0.0
 *
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Codevision_Elementor_Smart_Fonts
 * @subpackage Codevision_Elementor_Smart_Fonts/includes
 * @author     codevision <info@codevision.io>
 */
class Codevision_Elementor_Smart_Fonts_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$manager = esf_get_license_manager();
		$manager
            ->init()
            ->activate();
	}

}
