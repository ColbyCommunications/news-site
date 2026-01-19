<?php
/**
 * This file contains all the functions and filters required for compatibility with Polylang and WPML.
 *
 * As we don't want to hardcode any multilang stuff into the main plugin, the preferred method is to add
 * generic filters throughout the rest of the plugin which we can hook into here.
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns true if WPML is installed and active.
 *
 * We need to test for this since Polylang has support for the same hooks as WPML,
 * but most of the WPML-specific functionality isn't needed by Polylang.
 *
 * @return bool
 */
function cludo_multilang_wpml_is_active (){
	return defined( 'ICL_SITEPRESS_VERSION' );
}

/**
 * Returns the language code for a given post ID.
 *
 * @param $post_id
 * @return false|mixed|string
 */
function cludo_multilang_get_language_for_post( $post_id ) {
	$lang_code = '';

	if ( function_exists( 'pll_get_post_language' ) ) {
		$lang_code = pll_get_post_language( $post_id );
	}

	if( cludo_multilang_wpml_is_active() ){
		$wpml_lang = apply_filters( 'wpml_post_language_details', NULL, $post_id );

		if(!empty($wpml_lang) && is_array($wpml_lang)){
			$lang_code = $wpml_lang['language_code'];
		}
	}

	if(empty($lang_code)){
		return false;
	}
	else {
		return $lang_code;
	}
}

/**
 * Tests whether a post ID is in the language specified.
 *
 * @param $post_id
 * @param $language
 * @return bool
 */
function cludo_multilang_post_exists_in_language( $post_id, $language ){
	if(cludo_multilang_wpml_is_active()){
		$post_type = get_post_type($post_id);

		$wpml_object_id = apply_filters( 'wpml_object_id', $post_id, $post_type, false, $language );

		if(empty($wpml_object_id)){
			return false;
		}
	}

	return $language == cludo_multilang_get_language_for_post($post_id);
}

/**
 * Hooks into the push/delete API functions and removes post IDs not in the crawler's language.
 *
 * @param array $post_ids
 * @param int $crawler_id
 * @return array
 */
function cludo_multilang_filter_crawler_post_ids_for_language( array $post_ids, int $crawler_id ) {
	$crawler_lang = cludo_get_language_for_crawler_id( $crawler_id );
	$crawler_post_ids = [];

	if(empty($crawler_lang)){
		return $post_ids;
	}

	foreach ($post_ids as $key => $post_id){
		if(cludo_multilang_post_exists_in_language( $post_id, $crawler_lang )){
			$crawler_post_ids[] = $post_id;
		}
	}

	return $crawler_post_ids;
}

add_filter( 'cludo_get_posts_ids_for_crawler', 'cludo_multilang_filter_crawler_post_ids_for_language', 10, 2 );


/**
 * Required for WPML support.
 *
 * Runs whenever a new post is created, and holds the posts push queue until the WPML language data has been stored.
 *
 * @param $should_run_queue
 * @param $post_queue
 * @return false|mixed
 */
function cludo_multilang_wpml_on_new_post_wait_for_translation_before_push($should_run_queue, $post_queue){
	if(!cludo_multilang_wpml_is_active()){
		return $should_run_queue;
	}

	foreach ($post_queue as $post_id){
		$wpml_post_language_details = apply_filters( 'wpml_post_language_details', NULL, $post_id );

		if(!is_array($wpml_post_language_details) || empty($wpml_post_language_details['language_code'])){
			return false;
		}
	}

	return $should_run_queue;
}

add_filter( 'cludo_on_save_post_should_run_push_queue', 'cludo_multilang_wpml_on_new_post_wait_for_translation_before_push', 10, 2 );

/**
 * Required for WPML support.
 *
 * Pushes a newly translated post to the Cludo API.
 *
 * @param $new_post_id
 * @param $data
 * @param $job
 * @return void
 */
function cludo_multilang_wpml_translation_completed($new_post_id, $data, $job){
	if(!cludo_multilang_wpml_is_active()){
		return;
	}

	$wpml_post_language_details = apply_filters( 'wpml_post_language_details', NULL, $new_post_id );

	if(is_array($wpml_post_language_details) && !empty($wpml_post_language_details['language_code'])){
		$api = new CludoApi();
		$api->pushPostToCrawlers($new_post_id);
	}
}

add_action( 'wpml_pro_translation_completed', 'cludo_multilang_wpml_translation_completed', 20, 3 );