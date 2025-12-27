<?php 

namespace Scripts\Common;

use \Scripts\Shared\Logger;

class ApiClient
{

	use Logger;
	
	protected $base_url;
	protected $args;

	function __construct($base_url, $args = [])
	{
		$defaults = [
			'timeout' => 20, 
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0'
		];

		$this->args = wp_parse_args($args, $defaults);
		$this->base_url = $base_url;
	}

	public function prepare_success_response($response_body) {
		if( isset($response_body['items']) &&
			isset($response_body['total_pages'])
		) {
			return [$response_body['items'], $response_body['total_pages']];
		}
		return [$response_body['items'], 1];
	}
	
	public function prepare_error_response() {
		return [[], 0];
	}

	final public function fetch($endpoint, $query_params = []) {
		$url = $this->base_url . $endpoint;
		
		// Add query parameters to URL
		if(!empty($query_params)) {
			$url .= '?' . http_build_query($query_params);
		}

		// Make a get HTTP request
		$response = wp_remote_get($url, $this->args);

		// Retrieve code response
		$response_code = wp_remote_retrieve_response_code($response);

		if (is_wp_error($response) || 200 !== $response_code) {
			$this->registerLog("API << {$url} >> failed, response code = {$response_code}", '_error');
			return $this->prepare_error_response();
		}

		$response_body = wp_remote_retrieve_body($response);
		$response_body = json_decode($response_body, true);
		return $this->prepare_success_response($response_body);
	}
}