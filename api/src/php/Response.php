<?php
include 'Header.php';

class Response {

	public $header = null;
	public $payload = null;
	private $errorMsg = null;
 
	public function __construct() {
		
		$this->initResponse();
	}

	public function initResponse() {
		
		$this->header = new Header();
	}

	public function addPayload($payload) {

		$this->payload = $payload;
	}

	public function addHeader($error) {

		$this->header->setHeader($error);
		
	}
}

?>