<?php
/**
 * Helper functions.
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper function to validate if current page displayed is settings page.
 *
 * @return bool
 */
function cludo_is_settings_page() {
	$current_screen = get_current_screen();

	if(empty($current_screen)){
		return false;
	}

	if ( 'settings_page_' . CLUDO_WP_PLUGIN_NAME == $current_screen->base ) {
		return true;
	}

	return false;
}

/**
 * Returns the "API Connected" status on the settings page.
 *
 * @param $api
 * @return void
 */
function cludo_api_connected_message($api = NULL) {
	if(!$api){
		$api = new CludoApiBase();
	}

	$status = $api->active;
	$symbol = $status ? '✓' : '✗';

	$text = __( 'Enter API key to connect to Cludo API', CLUDO_WP_TEXTDOMAIN );

	if ( $api->invalidKey ) {
		$text = __( 'Customer ID or API key invalid', CLUDO_WP_TEXTDOMAIN );
	}
	if ( $api->active ) {
		$text = __( 'Cludo API connected', CLUDO_WP_TEXTDOMAIN );
	}

	printf( "<p class='cludo__connection %s'>%s %s</p>", ( $status ? 'active' : '' ), $symbol, $text );
}

/**
 * Returns an array of all the post types we can possibly index.
 *
 * @return mixed|string[]|WP_Post_Type[]
 */
function cludo_get_indexable_post_types() {
	if($transient = get_transient('cludo_indexable_post_types')){
		return $transient;
	}

	$ptypes = get_post_types( [
		'public'              => true,
		'exclude_from_search' => false,
	] );

	if(array_key_exists('attachment', $ptypes)){
		unset($ptypes['attachment']);
	}

	set_transient('cludo_indexable_post_types', $ptypes, 60);

	return $ptypes;
}

/**
 * Returns the option name for whether or not a post type is indexed by a specific crawler.
 *
 * @param $post_type
 * @param $crawler_id
 * @return string
 */
function cludo_get_crawler_index_setting_name( $post_type, $crawler_id ) {
	return sprintf( "is_post_type_%s_indexed_by_crawler_id_%s", $post_type, $crawler_id );
}

/**
 * Returns the option name for getting the language of a crawler.
 *
 * @param $post_type
 * @param $crawler_id
 * @return string
 */
function cludo_get_crawler_language_setting_name( $crawler_id ) {
	return sprintf( "language_for_crawler_%s", $crawler_id );
}

/**
 * Returns the site language as a two-letter language code.
 *
 * @return array|string|string[]|null
 */
function cludo_get_site_language_iso(){
	$current_lang = get_locale();

	$pattern = "/^([a-z]+).*/";

	return preg_replace($pattern, "$1", $current_lang);
}

/**
 * Returns an array of the different languages that are active on the current site.
 *
 * @return array
 */

function cludo_get_site_languages() {
	$languages = [];
	$languages_iso = [];

	$current_lang = cludo_get_site_language_iso();

	if(!empty($current_lang)){
		$languages_iso = [$current_lang];
	}

	// First test for Polylang:
	if ( function_exists( 'pll_languages_list' ) ) {
		$pl_list = pll_languages_list( [ 'hide_empty' => true, 'fields' => 'slug' ] );

		$languages_iso = array_merge( $languages_iso, $pl_list );
	}

	// Then test for WPML
	$wpml_list = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );

	if ( is_array( $wpml_list ) ) {
		foreach ( $wpml_list as $wpml_lang ) {
			$lang_iso = $wpml_lang['language_code'];
			$languages_iso[] = $lang_iso;
		}
	}

	$languages_iso = apply_filters( 'cludo_get_site_languages', $languages_iso );

	foreach ( $languages_iso as $lang_code ) {
		$languages[ $lang_code ] = [
			'locale' => $lang_code,
			'name'   => locale_get_display_language( $lang_code, $current_lang )
		];
	}

	return $languages;
}

/**
 * Returns true if a post type is indexed by a specific crawler.
 *
 * @param $post_type
 * @param $crawler_id
 * @return array|string
 */
function cludo_is_post_type_indexed_by_crawler_id($post_type, $crawler_id){
	$settings = new CludoSettings();
	return $settings->get('content_indexing', cludo_get_crawler_index_setting_name($post_type, $crawler_id));
}

/**
 * Returns the language code of a crawler, if it is set.
 *
 * @param $crawler_id
 * @return array|false|string
 */
function cludo_get_language_for_crawler_id($crawler_id){
	$settings = new CludoSettings();

	$lang_for_crawler = $settings->get('content_indexing', cludo_get_crawler_language_setting_name($crawler_id));

	if(empty($lang_for_crawler)){
		return false;
	}
	else {
		return $lang_for_crawler;
	}
}

/**
 * Given a specific post type, returns the crawler IDs associated with it.
 *
 * @param $post_type
 * @return array
 */
function cludo_get_crawler_ids_for_post_type($post_type){
	$api = new CludoApi();

	$indexes = $api->getIndexes();

	$crawler_ids = [];

	foreach ($indexes as $crawler){
		$is_indexed = cludo_is_post_type_indexed_by_crawler_id($post_type, $crawler['id']);
		if($is_indexed){
			$crawler_ids[] = $crawler['id'];
		}
	}

	return $crawler_ids;
}

/**
 * Arranges a one-dimensional array of post IDs into a two-dimensional array, organized by post type.
 *
 * @param array $post_ids
 * @return array
 */
function cludo_arrange_post_ids_by_post_type(array $post_ids) {
	$posts = [];
	foreach($post_ids as $post_id){
		$post_type = get_post_type($post_id);
		$posts[$post_type][] = $post_id;
	}

	return $posts;
}

/**
 * Given an array of post IDs, returns the permalinks for each of them.
 *
 * @param array $post_ids
 * @return array
 */
function cludo_get_urls_from_post_ids(array $post_ids){
	$urls = [];
	foreach($post_ids as $post_id){
		$urls[] = cludo_get_permalink($post_id);
	}

	return $urls;
}

/**
 * Formats a unix timestamp in our preferred format.
 *
 * @param $timestamp
 * @return false|string
 */
function cludo_format_timestamp($timestamp){
	return wp_date( __(  'j F Y H:i:s', CLUDO_WP_TEXTDOMAIN), $timestamp );
}

/**
 * Gets the permalink for a post ID. Makes it possible to hook into.
 *
 * @param $post_id
 * @return mixed|null
 */
function cludo_get_permalink($post_id){
	return apply_filters( 'cludo_get_permalink', get_permalink($post_id), $post_id );
}