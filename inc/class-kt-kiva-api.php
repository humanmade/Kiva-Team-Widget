<?php

class KT_Kiva_API {

	public function __construct() {
		
	}

	public function request( $method, $url, $args = array() ) {

		$url = 'http://api.kivaws.org/v1/' . $url;

		$url = add_query_arg( 'app_id', 'com.hmn.ktw', $url );

		if ( $method === 'GET' ) {
			$url = add_query_arg( $args, $url );
		}

		$request = wp_remote_request( $url, array(
			'method' => $method,
			'body' => $method === 'POST' ? $args : null

		));

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		return $response;
	}
}