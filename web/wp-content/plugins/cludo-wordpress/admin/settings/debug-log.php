<?php
/**
 * Display debug log
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if($this->settings->get('advanced', 'debug_log')) {
	$section = new CludoSettingsSection( 'debug_log', $this );

	$section->addField( 'debug_log_display', function () {
		if ( $this->settings->get( 'advanced', 'debug_log' ) ) {
			$debug_log_data = get_option( CLUDO_WP_PLUGIN_NAME . '_debug_log', [] );

			$log_text = '';

			ksort($debug_log_data);

			foreach ($debug_log_data as $timestamp => $entry){
				$log_text .= '['.$timestamp.'] ' . print_r($entry, true) . "\n";
			}

			return [
				'title'   => __( 'Request log', CLUDO_WP_TEXTDOMAIN ),
				'type'    => 'html',
				'desc'    => __( 'This is a list of all requests that have failed while the "enable debug log" feature was active.', CLUDO_WP_TEXTDOMAIN ),
				'default' => '<pre class="cludo__debug-log">' . $log_text . '</pre>',
			];
		}
	} );

	$section->addSection();
}