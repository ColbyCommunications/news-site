<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://rommel.dk
 * @since      1.0.0
 *
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/includes
 */

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
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/includes
 */
class Cludo_Wordpress {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cludo_Wordpress_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * Stores a reference to the background processor.
	 *
	 * @since    1.0.0
	 * @var      CludoTaskQueue
	 */
	public CludoTaskQueue $task_queue;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CLUDO_WP_VERSION' ) ) {
			$this->version = CLUDO_WP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'cludo-wordpress';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cludo_Wordpress_Loader. Orchestrates the hooks of the plugin.
	 * - Cludo_Wordpress_i18n. Defines internationalization functionality.
	 * - Cludo_Wordpress_Admin. Defines all hooks for the admin area.
	 * - Cludo_Wordpress_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		// Plugin guts:
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-wordpress-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-wordpress-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-settings-field.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-settings-section.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-api-base.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-api-calls.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-api.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cludo-wordpress-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cludo-wordpress-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cludo-content-hooks.php';

		$this->loader = new Cludo_Wordpress_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Cludo_Wordpress_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cludo_Wordpress_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin  = new Cludo_Wordpress_Admin( $this->get_plugin_name(), $this->get_version() );
		$content_hooks = new CludoContentHooks($this);

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'init');
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_settings_page', 200);

		$this->loader->add_action( 'cludo_on_settings_update', $this, 'delete_transients' );

		$content_hooks->addHooks($this->loader);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Cludo_Wordpress_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	public function delete_transients(){
		delete_transient('cludo_indexable_post_types');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
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
	 * @return    Cludo_Wordpress_Loader    Orchestrates the hooks of the plugin.
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
