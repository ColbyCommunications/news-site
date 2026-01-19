<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}

$section = new CludoSettingsSection('content_indexing', $this);

/**
 * Add language mapping fields.
 */
$section->addFields(function(){
	$fields = [];

	$languages = cludo_get_site_languages();

	if(count($languages) <= 1){
		return false;
	}

	$fields['_language_info'] = [
		'type' => 'title',
		'title' => __( 'Language mapping', CLUDO_WP_TEXTDOMAIN ),
		'desc' => __( 'It looks like you have content in multiple languages. Choose the content language each crawler will index.', CLUDO_WP_TEXTDOMAIN ),
		'no_table_wrap' => true,
	];

	$options = [
		'' => 'All languages'
	];

	foreach ($languages as $language){
		$options[$language['locale']] = $language['name'];
	}

	foreach($this->api->getIndexes() as $crawler){
		$crawler_setting_name = cludo_get_crawler_language_setting_name($crawler['id']);
		$fields[$crawler_setting_name] = [
			'title' => $crawler['name'],
			'type' => 'select',
			'options' => $options,
			'default' => ''
		];
	}

	return $fields;
});

/**
 * Add content mapping fields.
 */
$section->addFields(function($section){
	$fields = [];

	$fields['_content_type_info'] = [
		'type' => 'title',
		'title' => __( 'Content mapping', CLUDO_WP_TEXTDOMAIN ),
		'desc' => __( 'Pick which post types each crawler will index.', CLUDO_WP_TEXTDOMAIN ),
		'no_table_wrap' => true,
	];

    ob_start();
	include plugin_dir_path( dirname( __FILE__ ) ) . 'partials/cludo-content-mapping-table.php';
    $content_type_table = ob_get_clean();

	$fields['content_type_table'] = [
		'type' => 'html',
		'default' => $content_type_table,
		'no_table_wrap' => true
	];

	return $fields;
});

$section->addSection(true);