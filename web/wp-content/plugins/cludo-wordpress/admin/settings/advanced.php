<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

$section = new CludoSettingsSection('advanced', $this);

$section->addField('debug_log', [
	'title' => __( 'Debug log', CLUDO_WP_TEXTDOMAIN ),
	'type'  => 'checkbox',
	'desc'  => __( 'Activate to save request errors and display them under debug log tab.', CLUDO_WP_TEXTDOMAIN ),
]);

$section->addSection();