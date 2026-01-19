<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://rommel.dk
 * @since      1.0.0
 *
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cludo_Wordpress
 * @subpackage Cludo_Wordpress/admin
 */
class Cludo_Wordpress_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	public CludoSettings $settings;
	public CludoApi $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since      1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function init(){
		$this->settings = new CludoSettings();
		$this->settings->register();

		if(cludo_is_settings_page()){
			$this->api = new CludoApi();

			$this->load_settings_sections();
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( !cludo_is_settings_page() ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/css/cludo-wordpress-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( !cludo_is_settings_page() ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/js/cludo-wordpress-admin.js', array( 'jquery' ), $this->version, true );
	}

	public function load_settings_sections(){
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/general.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/content-indexing.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/account.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/advanced.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/debug-log.php';
	}

	/**
	 * Option page callback.
	 *
	 * @since   1.0.0
	 */
	public function settings_page_callback() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$this->init();

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/cludo-admin-display.php';
	}

	/**
	 * Adds menu to the admin page.
	 *
	 * @since   1.0.0
	 */
	public function add_settings_page() {
		add_submenu_page(
			'options-general.php',
			__( 'Settings', CLUDO_WP_TEXTDOMAIN ),
			__( 'Cludo Search', CLUDO_WP_TEXTDOMAIN ),
			'administrator',
			CLUDO_WP_PLUGIN_NAME,
			array($this, 'settings_page_callback')
		);
	}

	/**
	 * Returns an array with all settings for the plugin.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function get_settings_sections() {
		$settings = [];

		return apply_filters( 'cludo_settings_sections', $settings );
	}

	/**
	 * Returns readable titles for the settings sections.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function settings_tab_titles() {
		return [
			'general' => __( 'General', CLUDO_WP_TEXTDOMAIN ),
			'api' => __( 'API Setup', CLUDO_WP_TEXTDOMAIN ),
			'content_indexing' => __( 'Content Indexing', CLUDO_WP_TEXTDOMAIN ),
			'help' => __( 'Help', CLUDO_WP_TEXTDOMAIN ),
			'debug_log' => __( 'Debug Log', CLUDO_WP_TEXTDOMAIN ),
			'advanced' => __( 'Advanced', CLUDO_WP_TEXTDOMAIN ),
		];
	}
}
