<?php
class PcfmAPI {
	private $api_key = null;
	private $api_base_url = null;
	public function __construct( $api_host, $api_key) {
		$this->api_key = $api_key;
		$this->api_base_url = $api_host . '/api/v1/';
	}
	public function make_api_call($url, $method, $params = array()) {
		$opts = [ 
				"http" => [ 
						"method" => $method,
						"header" => array (
								"Authorization: Bearer " . $this->api_key 
						) 
				] 
		];
		
		if ($method == 'GET') {
			
		}
		
		if ($method == 'POST') {
			$opts ['http'] ['header'] [] = 'Content-type: application/json;charset=utf-8';
			$opts ['http'] ['content'] = json_encode ( $params );
		}
		
		$context = stream_context_create ( $opts );
		return @file_get_contents ( $this->api_base_url . $url, false, $context );
	}
	public function list_marketo_preference_centers() {		
		return json_decode ( $this->make_api_call ( 'marketo-email-preference-centers', 'GET' ) );
	}
	public function get_marketo_preference_center($id) {
		return json_decode ( $this->make_api_call ( 'marketo-email-preference-centers/' . $id, 'GET' ) );
	}
	
	public function render_marketo_preference_center_template( $id, $email, $add_params = array() ) {
		$add_params['email'] = $email;
		return json_decode ( $this->make_api_call ( 'marketo-email-preference-centers/' . $id . '/renderTemplate', 'POST', $add_params ) );
	}
	
	public function update_marketo_preference_center_preferences( $id, $email, $key_values ){
		return  json_decode( $this->make_api_call ( 'marketo-email-preference-centers/' . $id . '/updatePreferences', 'POST', array('email' => $email, 'values' => $key_values) ) );
	}
}

?>