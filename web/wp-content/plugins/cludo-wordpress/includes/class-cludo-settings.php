<?php

/**
 * Class to keep track of all user settings for the plugin.
 */
class CludoSettings {
	/** @var false|array Holds the entire settings array.  */
	public $settings;

	public function __construct(){
		$this->settings = get_option( CLUDO_WP_PLUGIN_NAME, [] );
	}

	/**
	 * Registers the settings with WordPress.
	 *
	 * @return void
	 */
	public function register(){
		$args = [
			'sanitize_callback' => [$this, 'onSettingsUpdated']
		];

		register_setting( CLUDO_WP_PLUGIN_NAME, CLUDO_WP_PLUGIN_NAME, $args );
	}

	/**
	 * Runs every time settings are updated.
	 *
	 * @param $settings
	 * @return mixed
	 */
	public function onSettingsUpdated( $settings ){
		apply_filters( 'cludo_on_settings_update', $settings );

		return $settings;
	}

	/**
	 * Retrieve the value of a given setting.
	 *
	 * @param string $section
	 * @param string $setting
	 *
	 * @return array|string
	 */
	public function get( string $section = '', string $setting = '', string $default = '' ) {
		$settings = $this->settings;

		if ( ! empty( $settings[ $section ][ $setting ] ) ) {
			$settings = $settings[ $section ][ $setting ];
		} else {
			$settings = $default;
		}

		return apply_filters( 'cludo_settings_get', $settings, $section, $setting );
	}
}