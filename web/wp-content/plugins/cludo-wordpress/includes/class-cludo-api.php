<?php

/**
 * The main API class we will be interacting with.
 *
 * All functions in here bridge WordPress with the platform-agnostic API.
 */
class CludoApi extends CludoApiCalls {
	/**
	 * Gets all available crawlers and returns them as an array of indexes.
	 *
	 * @return array
	 */
	public function getIndexes(){
		$crawlers = $this->getCrawlers();

		$output = [];

		foreach ($crawlers as $crawler){
			$urls = [];

			if(!empty($crawler->crawlUrls)){
				foreach($crawler->crawlUrls as $url){
					$urls[] = $url->url;
				}
			}

			$output[] = [
				'id' => $crawler->id,
				'name' => $crawler->name,
				'language' => $crawler->language,
				'urls' => $urls
			];
		}

		return $output;
	}

	/**
	 * Pushes a single post to crawlers indexing its post type and language (if any).
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public function pushPostToCrawlers(int $post_id){
		return $this->pushPostsToCrawlers([$post_id]);
	}

	/**
	 * Given an array of post IDs, this function will attempt to distribute and
	 * push them to the crawlers indexing them.
	 *
	 * @param array $post_ids
	 * @return bool
	 */
	public function pushPostsToCrawlers(array $post_ids){
		$post_ids = apply_filters('cludo_push_posts_ids', $post_ids);

		$post_ids_by_type = cludo_arrange_post_ids_by_post_type($post_ids);

		$push_failed = false;

		foreach ($post_ids_by_type as $post_type => $post_type_ids){
			$crawlers = cludo_get_crawler_ids_for_post_type($post_type);

			foreach($crawlers as $crawler_id){
				$ids_for_crawler = apply_filters('cludo_get_posts_ids_for_crawler', $post_type_ids, $crawler_id);

				if(count($ids_for_crawler) > 0){
					$urls = cludo_get_urls_from_post_ids($ids_for_crawler);
					$urls = apply_filters('cludo_push_posts_urls', $urls, $post_type);

					$success = $this->pushUrlsToCrawler($urls, $crawler_id);

					if($success === false){
						cludo_log("Failed pushing posts to crawler $crawler_id");
						$push_failed = true;
					}
				}
			}
		}

		return !$push_failed;
	}

	/**
	 * Deletes a single post ID from the crawlers indexing it (if any).
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public function deletePostFromCrawlers(int $post_id){
		return $this->deletePostsFromCrawlers([$post_id]);
	}

	/**
	 * Given an array of post IDs, this function will attempt to distribute and
	 * delete them from the crawlers indexing them.
	 *
	 * @param array $post_ids
	 * @return bool
	 */
	public function deletePostsFromCrawlers(array $post_ids){
		$post_ids = apply_filters('cludo_delete_posts_ids', $post_ids);

		$post_ids_by_type = cludo_arrange_post_ids_by_post_type($post_ids);

		$delete_failed = false;

		foreach ($post_ids_by_type as $post_type => $post_type_ids){
			$crawlers = cludo_get_crawler_ids_for_post_type($post_type);

			foreach($crawlers as $crawler_id){
				$ids_for_crawler = apply_filters('cludo_get_posts_ids_for_crawler', $post_type_ids, $crawler_id);

				if(count($ids_for_crawler) > 0) {
					$urls = cludo_get_urls_from_post_ids( $ids_for_crawler );
					$urls = apply_filters( 'cludo_delete_posts_urls', $urls, $post_type );

					$success = $this->deleteUrlsFromCrawler( $urls, $crawler_id );

					if($success === false){
						cludo_log("Failed deleting posts from crawler $crawler_id");
						$delete_failed = true;
					}
				}
			}
		}

		return !$delete_failed;
	}
}