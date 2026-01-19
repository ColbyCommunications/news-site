<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

$section = new CludoSettingsSection('api', $this);

$section->addField('customer_id', [
	'title'    => __( 'Customer ID', CLUDO_WP_TEXTDOMAIN ),
	'type'     => 'text',
	'required' => true,
	'desc'     => __( 'Enter the Customer ID from the Search API tab in Cludo Dashboard.', CLUDO_WP_TEXTDOMAIN ),
]);

$section->addField('api_key', [
	'title'    => __( 'API key', CLUDO_WP_TEXTDOMAIN ),
	'type'     => 'text',
	'required' => true,
	'desc'     => __( 'Enter the API key from the Search API tab in Cludo Dashboard.', CLUDO_WP_TEXTDOMAIN ),
]);

$section->addField('api_endpoint', [
	'title'    => __( 'API host name', CLUDO_WP_TEXTDOMAIN ),
	'type'     => 'text',
	'required' => false,
	'desc'     => __( 'Advanced. Leave blank to use default.', CLUDO_WP_TEXTDOMAIN ),
]);

$section->addSection();