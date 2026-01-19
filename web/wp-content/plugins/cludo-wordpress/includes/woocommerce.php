<?php

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cludo_wc_on_post_update_did_status_change(bool $status_changed, array $post_after, array $post_before){
	if('product' !== $post_after['post_type']){
		return $status_changed;
	}

	if($status_changed){
		return $status_changed;
	}

	if(array_key_exists('_visibility', $post_after) && array_key_exists('current_visibility', $post_after)){
		$post_was_hidden_before = $post_after['current_visibility'] === 'hidden';
		$post_is_hidden_now = $post_after['_visibility'] === 'hidden';

		if($post_was_hidden_before !== $post_is_hidden_now){
			$status_changed = true;
		}
	}

	return $status_changed;
}

function cludo_wc_on_post_update_post_before_is_public(bool $is_public, array $post_before, array $post_after){
	if('product' !== $post_before['post_type']){
		return $is_public;
	}

	if(!$is_public){
		return $is_public;
	}

	if(array_key_exists('current_visibility', $post_after)){
		if($post_after['current_visibility'] === 'hidden'){
			$is_public = false;
		}
	}

	return $is_public;
}

function cludo_wc_on_post_update_post_after_is_public(bool $is_public, array $post_after){
	if('product' !== $post_after['post_type']){
		return $is_public;
	}

	if(!$is_public){
		return $is_public;
	}

	if(array_key_exists('_visibility', $post_after)){
		if($post_after['_visibility'] === 'hidden'){
			$is_public = false;
		}
	}

	return $is_public;
}

add_action('woocommerce_init', function(){
	add_filter('cludo_on_post_update_post_status_changed', 'cludo_wc_on_post_update_did_status_change', 10, 3);
	add_filter('cludo_on_post_update_post_before_is_public', 'cludo_wc_on_post_update_post_before_is_public', 10, 3);
	add_filter('cludo_on_post_update_post_after_is_public', 'cludo_wc_on_post_update_post_after_is_public', 10, 2);
});