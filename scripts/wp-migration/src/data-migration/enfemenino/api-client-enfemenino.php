<?php 

namespace Scripts\GetDatas\Aufeminin;

use Scripts\Common\ApiClient;

class ApiClientAufeminin extends ApiClient
{

	public function prepare_success_response($response_body)
	{
		if( isset($response_body['_embedded']) &&
			isset($response_body['page_count'])
		) {
			return [$response_body['_embedded'], $response_body['page_count']];
		}

		return [$response_body, 1];
	}

	public function prepare_error_response()
	{
		return [[], 0];
	}

}