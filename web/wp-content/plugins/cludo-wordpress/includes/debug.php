<?php
/**
 * Helper functions for debugging.
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cludo_log( $message, string $clue = '', array $params = [] ) {
	error_log( print_r( $message, true ) );

	if ( cludo_is_debug_enabled() ) {

		// Get log entries.
		$_debug_log = get_option( CLUDO_WP_PLUGIN_NAME . '_debug_log', [] );

		// Clean up old log entries.
		$current_time = floor(microtime(true) * 1000);
		foreach ( $_debug_log as $item => $item_data ) {
			if ( $current_time - $item >= ( apply_filters( 'cludo_debug_log_days', 7 ) * 24 * 60 * 60 ) ) {
				unset( $_debug_log[ $item ] );
			}
		}

		// Add new log entry.
		if ( ! empty( $clue ) ) {
			$debug_log[ $current_time ] = 'Error start (' . date( 'd/m/y H:m:s' ) . ') ----';
			$debug_log[ $current_time + 1 ] = 'Endpoint: ' . $clue;
		}
		if ( ! empty( $params ) ) {
			$debug_log[ $current_time + 2 ] = 'Request params: ' . print_r( $params, true );
		}

		$debug_log[ $current_time + 3 ] = print_r( $message, true );

		if ( ! empty( $clue ) ) {
			$debug_log[ $current_time + 4 ] = 'Error end ----';
		}

		$debug_log                      = $debug_log + $_debug_log;
		update_option( CLUDO_WP_PLUGIN_NAME . '_debug_log', $debug_log );
	}
}

function cludo_is_debug_enabled() {
	$settings = new CludoSettings();

	return ! empty( apply_filters( 'cludo_api_debug_mode', $settings->get( 'advanced', 'debug_log' ) ) );
}