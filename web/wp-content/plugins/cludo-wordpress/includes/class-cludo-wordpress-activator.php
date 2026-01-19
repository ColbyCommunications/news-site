<?php

/**
 * Fired during plugin activation
 *
 * @link       https://rommel.dk
 * @since      1.0.0
 *
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/includes
 */
class Cludo_Wordpress_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$settings = get_option( CLUDO_WP_PLUGIN_NAME, false );
		if ( ! $settings ) {
			// Set default options for plugin.
			do_action( CLUDO_WP_PLUGIN_NAME . '_activation_settings', $settings, CLUDO_WP_PLUGIN_NAME );
		}
		// Allow functions to be run after activation is completed.
		do_action( CLUDO_WP_PLUGIN_NAME . '_activation_completed', $settings, CLUDO_WP_PLUGIN_NAME );
	}

}
