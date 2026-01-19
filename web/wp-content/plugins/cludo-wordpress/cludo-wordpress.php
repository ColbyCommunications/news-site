<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://rommel.dk
 * @since             1.0.0
 * @package           Cludo_Wordpress
 *
 * @wordpress-plugin
 * Plugin Name:       Cludo for WordPress
 * Plugin URI:        https://rommel.dk
 * Description:       Connect your WordPress website with Cludo.
 * Version:           1.0.2
 * Author:            Rommel ApS
 * Author URI:        https://rommel.dk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cludo-wordpress
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Defining plugin constants.
 */
const CLUDO_WP_TEXTDOMAIN  = 'cludo-wordpress';
const CLUDO_WP_PLUGIN_NAME = 'cludo_wp';
const CLUDO_WP_VERSION     = '1.0.2';

/**
 * Making plugin name/description available for translation.
 */
__( 'Cludo for WordPress', CLUDO_WP_TEXTDOMAIN );
__( 'Connect your WordPress website with Cludo.', CLUDO_WP_TEXTDOMAIN );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-cludo-wordpress-activator.php
 */
function activate_cludo_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cludo-wordpress-activator.php';
	Cludo_Wordpress_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-cludo-wordpress-deactivator.php
 */
function deactivate_cludo_wordpress() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cludo-wordpress-deactivator.php';
	Cludo_Wordpress_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cludo_wordpress' );
register_deactivation_hook( __FILE__, 'deactivate_cludo_wordpress' );

/**
 * Require the guts of the plugin.
 */
require_once( __DIR__ . '/includes/debug.php');
require_once( __DIR__ . '/includes/helpers.php');
require_once( __DIR__ . '/includes/multilang.php');
require_once( __DIR__ . '/includes/woocommerce.php');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cludo-wordpress.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cludo_wordpress() {
	$plugin = new Cludo_Wordpress();
	$plugin->run();

}
run_cludo_wordpress();

/**
 * Plugin activation.
 */
function cludo_plugin_activated() {
	//wp_mail( 'support@rommel.dk', 'APSIS One for WooCommerce plugin activated', sprintf( 'The plugin APSIS One for WooCommerce %s has been activated at %s.', CLUDO_WP_VERSION, get_site_url() ) );
}

add_action( CLUDO_WP_PLUGIN_NAME . '_activation_completed', 'cludo_plugin_activated' );

/**
 * Add links to Plugin settings in the Plugin over
 *
 * @param array $actions
 *
 * @return array
 */
function cludo_plugin_action_links( $actions ): array {
	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . CLUDO_WP_PLUGIN_NAME ), __( 'Settings', CLUDO_WP_TEXTDOMAIN ) ) );

	return $actions;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'cludo_plugin_action_links' );
