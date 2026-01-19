<?php
/**
 * Holds all the post/content modifications hooks.
 *
 * Also implements a push/delete queue containing the IDs we want to push upon modification.
 *
 * We need this queue in two scenarios:
 * 1. When batch deleting posts, we want to delete all of these at once instead of sending multiple API requests.
 * 2. When updating a post, we need to compare the values in before_insert_post_data() before we actually push
 *    the post later on in on_save_post().
 *
 * @since      1.0.0
 */
class CludoContentHooks {
	private Cludo_Wordpress $main;
	private $queue_option_prefix = 'posts_queue';

	public function __construct(Cludo_Wordpress $parent) {
		$this->main = $parent;
	}

	/**
	 * Adds all the actions contained in the class to our loader.
	 *
	 * @param Cludo_Wordpress_Loader $loader
	 * @return void
	 */
	public function addHooks(Cludo_Wordpress_Loader $loader){
		// Posts being created, updated or deleted:
		$loader->add_action('wp_insert_post_data', $this, 'before_insert_post_data', 10, 4);
		$loader->add_action('wp_trash_post', $this, 'on_post_deleted');
		$loader->add_action('save_post', $this, 'on_save_post', 200, 3);
	}

	/**
	 * Adds a post to the posts push queue.
	 *
	 * @param $post_id
	 * @return void
	 */
	private function add_post_to_push_queue($post_id){
		$post_push_queue = get_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_push', []);

		if(!in_array($post_id, $post_push_queue) && $post_id > 1){
			$post_push_queue[] = $post_id;
		}

		update_option( CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_push', $post_push_queue );
	}

	/**
	 * Adds a post to the posts delete queue.
	 *
	 * @param $post_id
	 * @return void
	 */
	private function add_post_to_delete_queue($post_id){
		$post_delete_queue = get_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete', []);

		if(!in_array($post_id, $post_delete_queue) && $post_id > 1){
			$post_delete_queue[] = $post_id;
		}

		update_option( CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete', $post_delete_queue );
	}

	/**
	 * Pushes the posts in the queue to the Cludo API.
	 *
	 * @return void
	 */
	private function run_push_queue(){
		$post_push_queue = get_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_push', []);

		if(false === apply_filters( 'cludo_on_save_post_should_run_push_queue', true, $post_push_queue )){
			return;
		}

		if(count($post_push_queue) > 0){
			$api = new CludoApi();

			$api->pushPostsToCrawlers($post_push_queue);
		}

		// Clear push queue.
		update_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_push', []);
	}

	/**
	 * Pushes the posts in the queue to the Cludo API.
	 *
	 * @return void
	 */
	private function run_delete_queue(){
		$post_delete_queue = get_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete', []);

		if(false === apply_filters( 'cludo_on_save_post_should_run_delete_queue', true, $post_delete_queue )){
			return;
		}

		if(count($post_delete_queue) > 0){
			$api = new CludoApi();

			$api->deletePostsFromCrawlers($post_delete_queue);
		}

		// Clear delete queue.
		update_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete', []);
	}

	/**
	 * Checks if the user has manually modified a page permalink.
	 * If yes, deletes the old one and pushes the new one.
	 * 
	 * Unused since 1.0.3
	 * 
	 * @param array $post_before
	 * @param array $post_after
	 * @return void
	 */
	private function handle_maybe_permalink_change(array $post_before, array $post_after) {
		if($post_before['post_name'] !== $post_after['post_name']){
			$api = new CludoApi();

			$api->deletePostFromCrawlers($post_before['ID']);

			$this->add_post_to_push_queue($post_before['ID']);
		}
	}

	/**
	 * Runs at the very end of the post creation/modification process.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 * @param bool $update
	 * @return void
	 */
	public function on_save_post(int $post_id, WP_Post $post, bool $update){
		$this->run_push_queue();
	}

	/**
	 * Runs whenever the user deletes posts in the backend.
	 * Checks for multiple posts and batches them together as a single API request.
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function on_post_deleted(int $post_id){
		/*
		 * If a user is deleting multiple posts, we want to batch these together instead of sending them as
		 * individual API requests.
		 *
		 * WordPress will set the posts to delete in $_GET['post'] and trigger on_post_deleted for each one of these.
		 *
		 * Let's store the amount of batch posts that are being deleted as an option and only run our code on the
		 * last iteration.
		 */

		// First, check if we're deleting multiple posts or not.
		if ( isset( $_GET['post'] ) && is_array( $_GET['post'] ) ) {
			$counter_default = [
				'count' => count($_GET['post']),
				'i' => 0
			];

			// Store iteration count and total amount of iterations as an option.
			$counter = get_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete_counter', $counter_default);
			$counter['i'] += 1;
			update_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete_counter', $counter);

			// If we're not on the last iteration, bail out to prevent duplicate API requests.
			// Otherwise, delete our option and continue execution.
			if($counter['i'] < $counter['count']){
				return;
			}
			else {
				delete_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete_counter');
			}

			// Add each post to our delete queue.
			foreach ( $_GET['post'] as $pid ) {
				$this->add_post_to_delete_queue($pid);
			}
		} else {
			$this->add_post_to_delete_queue($post_id);

			// Delete the option just in case there's a stray entry in the database.
			delete_option(CLUDO_WP_PLUGIN_NAME . '_' . $this->queue_option_prefix . '_delete_counter');
		}

		$this->run_delete_queue();
	}


	/**
	 * Runs at the very start of the post modification process.
	 * At this point in the process, we can still compare new and old values.
	 *
	 * Handles every possible post modification scenario that should trigger an API call.
	 *
	 * @param $data
	 * @param $post_attr
	 * @param $unsanitized_post_attr
	 * @param $is_existing_post_being_updated
	 * @return mixed
	 */
	public function before_insert_post_data($data, $post_attr, $unsanitized_post_attr, $is_existing_post_being_updated){
		$post_id = $post_attr['ID'];
		$indexed_post_types = cludo_get_indexable_post_types();

		// Bail out if we aren't indexing this post type.
		if(!in_array($post_attr['post_type'], $indexed_post_types)){
			return $data;
		}

		// Post is being created. Add to push queue if it's public.
		if(!$is_existing_post_being_updated){
			if('publish' === $post_attr['post_status'] && empty($post_attr['post_password'] && apply_filters('cludo_is_post_public', true, $post_attr))){
				$this->add_post_to_push_queue($post_id);
			}
			return $data;
		}

		// Get the current post values so we can compare them.
		$post_before = get_post($post_id, ARRAY_A);

		// Helper booleans.
		$post_status_changed = apply_filters('cludo_on_post_update_post_status_changed', $post_attr['post_status'] !== $post_before['post_status'], $post_attr, $post_before);
		$post_has_password = !empty($post_attr['post_password']);
		$post_password_changed = $post_attr['post_password'] !== $post_before['post_password'];
		$post_before_is_public = apply_filters('cludo_on_post_update_post_before_is_public', 'publish' === $post_before['post_status'], $post_before, $post_attr);
		$post_after_is_public = apply_filters('cludo_on_post_update_post_after_is_public', 'publish' === $post_attr['post_status'], $post_attr);

		// Post is being trashed. Do nothing as we handle this in on_post_deleted() above.
		if('trash' === $post_attr['post_status']){
			return $data;
		}

		// Post got password protected. Delete from indexed URLs.
		if($post_has_password && $post_password_changed){
			$this->add_post_to_delete_queue($post_id);
			$this->run_delete_queue();
		}

		// Post has password. We already deleted it above, so bail out.
		if($post_has_password){
			return $data;
		}

		// -----------------------------------------------------------------------------------------------
		// The code run from here on out is guaranteed to run on a non-password-protected page.
		// -----------------------------------------------------------------------------------------------

		// Post is going public. Add to indexed URLs.
		if(($post_password_changed || $post_status_changed) && $post_after_is_public){
			$this->add_post_to_push_queue($post_id);

			return $data;
		}

		// Post is being made private. Delete from indexed URLs.
		if($post_before_is_public && !$post_after_is_public){
			$this->add_post_to_delete_queue($post_id);
			$this->run_delete_queue();

			return $data;
		}

		// Post is already public but is being updated. Check for possible permalink changes.
		if(!$post_status_changed && 'publish' === $post_attr['post_status']){
			
			// In the case of an existing post being updated, we want to 
			// trigger a push anyways, since the content might have changed.
			$this->add_post_to_push_queue($post_id);

			// Uncomment the below line to revert to the old behavior which
			// only pushed when the permalink changed.
			// $this->handle_maybe_permalink_change($post_before, $post_attr);

			return $data;
		}

		return $data;
	}
}