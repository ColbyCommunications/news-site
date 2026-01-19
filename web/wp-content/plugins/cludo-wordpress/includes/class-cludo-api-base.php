<?php
/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base functions for connecting to the API.
 *
 * @var bool            $active         True if API connection and auth is successful.
 * @var bool            $invalidKey     True if API auth failed.
 * @var bool            $hasKey         True if the user has entered API credentials.
 * @var string          $endpoint       The URL for the Cludo API.
 * @var array           $baseHeaders    HTTP headers included in every request.
 * @var string          $customerId     API credential - also the username for Basic auth.
 * @var string          $apiKey         API credential - also the password for Basic auth.
 * @var CludoSettings   $settings       Reference to the settings saved by the user.
 */
class CludoApiBase {
	public bool $active = false;
	public bool $invalidKey = false;
	public bool $hasKey = false;

	private string $endpoint;
	private array $baseHeaders;
	private string $customerId;
	private string $apiKey;
	private CludoSettings $settings;

	public function getAuthHeader(){
		return ['Authorization' => 'Basic '.base64_encode($this->customerId . ':' . $this->apiKey)];
	}

	public function getBaseHeaders(){
		$headers = [
			'Content-Type' => 'application/json',
		];

		$headers = array_merge($headers, $this->getAuthHeader());

		return $headers;
	}

	public function getApiEndpoint(){
		$endpointUrl = $this->settings->get( 'api', 'api_endpoint' );

		if(empty($endpointUrl)) {
			$endpointUrl = 'https://api.cludo.com/';
		}

		return rtrim($endpointUrl, '/');
	}

	public function __construct(){
		$this->settings    = new CludoSettings();

		$this->customerId  = $this->settings->get( 'api', 'customer_id' );
		$this->apiKey      = $this->settings->get( 'api', 'api_key' );
		$this->endpoint    = $this->getApiEndpoint();
		$this->baseHeaders = $this->getBaseHeaders();

		if(!empty($this->customerId) && !empty($this->apiKey)){
			$this->hasKey = true;

			if($this->verifyAuth()){
				$this->active = true;
			}
			else {
				$this->invalidKey = true;
			}
		}
	}

	/**
	 * Checks whether or not we are successfully connected to the API.
	 *
	 * Stores this as a transient for the next 10 seconds so we don't spam.
	 *
	 * @return bool
	 */
	public function verifyAuth() : bool {
		if($transient = get_transient('cludo_api_auth_status')){
			if(array_key_exists("timestamp", $transient) && $transient['timestamp'] > time() - 1){
				return $transient['authenticated'];
			}
		}

		$response = $this->request('/crawlers', [], "GET", [], true);

		if($response === -1 || empty($response)){
			set_transient('cludo_api_auth_status', ['timestamp' => time(), 'authenticated' => false], 1);
			return false;
		}
		else {
			set_transient('cludo_api_auth_status', ['timestamp' => time(), 'authenticated' => true], 10);
			return true;
		}
	}

	/**
	 * Internal function. Stores a successful HTTP request as a short-lived transient.
	 *
	 * Prevents unneccessary duplicate calls to the API for the same data.
	 *
	 * @param $url
	 * @param $params
	 * @return array|false|int|mixed
	 */
	private function cachedRequest($url, $params){
		// Generate a unique ID for the request based on the URL and parameters.
		$request_id = md5($url.json_encode($params));
		$transient_name = 'cludo_api_request_cache_'.$request_id;

		// Found the request in cache. Return it.
		if( 'GET' === $params['method'] && $transient = get_transient($transient_name)){
			if(array_key_exists("timestamp", $transient) && $transient['timestamp'] > time() - 1){
				return $transient['response'];
			}
		}

		try {
			// Do request.
			$http          = new WP_Http();
			$response_req  = $http->request( $url, $params );
			$response_code = wp_remote_retrieve_response_code( $response_req );
			$response_body = json_decode( wp_remote_retrieve_body( $response_req ) );

			// Auth invalid.
			if( $response_code === 401 ){
				cludo_log( $response_req, $url, $params );
				$this->active = false;

				return -1;
			}

			// Check that we have a successful response.
			if ( strpos( $response_code, '20' ) === false ) {
				cludo_log( $response_req, $url, $params );

				return false;
			}

			// Successful request, store in transient and return
			$response = [
				'code' => $response_code,
				'body' => $response_body
			];

			if($params['method'] === 'GET'){
				set_transient($transient_name, [ "timestamp" => time(), "response" => $response ], 1);
			}

			return $response;
		} catch ( \Exception $e ) {
			cludo_log( $e->getMessage(), $url, $params );

			return false;
		}
	}

	/**
	 * Does a HTTP request to the API.
	 *
	 * @param string $path
	 * @param array $body
	 * @param string $method
	 * @param array $headers
	 * @param bool $force
	 * @return array|bool|int|mixed
	 */
	public function request( string $path, array $body = [], string $method = 'GET', array $headers = [], bool $force = false ){
		if(!$this->active && !$force){
			return [];
		}

		$url = $this->endpoint . '/api/v3/' . $this->customerId . '/' . ltrim($path, '/');

		$params = [
			'headers' => $this->baseHeaders,
			'method'  => strtoupper( $method ),
			'body'    => empty( $body ) ? null : json_encode( $body ),
		];
		if ( ! empty( $headers ) ) {
			$params['headers'] = array_merge( $params['headers'], $headers );
		}
		
		$response = $this->cachedRequest($url, $params);

		if(is_array($response)){
			return $response['body'] ?: true;
		}
		else {
			return $response;
		}
	}
}