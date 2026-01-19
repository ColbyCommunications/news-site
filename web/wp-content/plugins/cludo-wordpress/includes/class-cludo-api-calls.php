<?php

/**
 * Class containing the various calls we can make to the Cludo API.
 *
 * Contains no WordPress-specific code and only deals with URLs and crawler IDs.
 */
class CludoApiCalls extends CludoApiBase {
	/**
	 * Converts an array of URLs into the format required by the API.
	 *
	 * @param $urls
	 * @return array|array[]
	 */
	private static function urlsToDeleteJsonBody($urls){
		if(is_string($urls)){
			$urls = [$urls];
		}

		$body = [];

		foreach($urls as $url){
			// There's a bug in the Cludo API where it will only delete a page from the crawler index
			// if it has no trailing slash - even if it is indexed with one.
			// Let's just delete both versions of the URL if we find one.

			$url_no_slash = rtrim($url, '/');

			if($url !== $url_no_slash){
				$body[] = [$url => 'PageContent', $url_no_slash => 'PageContent'];
			}
			else {
				$body[] = [$url => 'PageContent'];
			}
		}

		return $body;
	}

	/**
	 * Returns all crawlers.
	 *
	 * @return array
	 */
	public function getCrawlers() : array {
		$response = $this->request('/crawlers');

		if(!is_array($response)){
			return [];
		}
		else {
			return $response;
		}
	}

	/**
	 * Pushes an array of URLs to a crawler.
	 *
	 * @param $urls
	 * @param $crawlerId
	 * @return bool
	 */
	public function pushUrlsToCrawler($urls, $crawlerId){
		$body = $urls;

		$crawlers = $this->getCrawlers();
		$crawlername = '';

		foreach ($crawlers as $crawler){
			if($crawler->id == $crawlerId){
				$crawlername = $crawler->name;
			}
		}

		cludo_log(["Pushing ".count($urls)." to crawler ".$crawlername.":", $body]);

		$response = $this->request('/content/'.$crawlerId.'/pushurls', $body, 'POST');

		return ($response !== false && $response !== -1);
	}

	/**
	 * Deletes an array of URLs from a crawler.
	 *
	 * @param $urls
	 * @param $crawlerId
	 * @return bool
	 */
	public function deleteUrlsFromCrawler($urls, $crawlerId){
		$body = self::urlsToDeleteJsonBody($urls);

		$crawlers = $this->getCrawlers();
		$crawlername = '';

		foreach ($crawlers as $crawler){
			if($crawler->id == $crawlerId){
				$crawlername = $crawler->name;
			}
		}

		cludo_log(["Deleting ".count($urls)." from crawler ".$crawlername.":", $body]);

		$response = $this->request('/content/'.$crawlerId.'/delete', $body, 'POST');

		return ($response !== false && $response !== -1);
	}
}