<?php
include 'Response.php';
include 'Wine.php';

class Main {

	private $response = null;
	private $error = null;
	private $payload = null;

	public function __construct() {
		$this->response = new Response();
	}
	
	public function getResponse() {
		
		if(isset($_GET['method'])) {
			if($_GET['method'] == 'get') {
				$this->payload = $this->getPayload();
				$this->handleGet();
			}
			else {
				$this->handleNotGet();
			}
		}
		else {
			$this->handleNoMethod();
		}

		if (isset($_GET['debug']) and $_GET['debug'] == 'true') {
			return json_encode($this->response, JSON_PRETTY_PRINT);
		}
		else {
			return json_encode($this->response);
		}	
	}

	private function handleNoMethod() {
			$this->error = 102;
			$this->payload = [];

			$this->response->addHeader($this->error);
			$this->response->addPayload($this->payload);
	}

	private function handleNotGet() {
			$this->error = 101;
			$this->payload = [];

			$this->response->addHeader($this->error);
			$this->response->addPayload($this->payload);
	}

	private function handleGet() {

		if ($this->payload !== null) {
			$this->response->addHeader($this->error);
			$this->response->addPayload($this->payload);
		}
		else {
			$this->error = 103;
			$this->payload = [];

			$this->response->addHeader($this->error);
			$this->response->addPayload($this->payload);
		}
	}

	private function getPayload() {
		$srch = new stdClass();
		$wine = new Wine();

		isset($_GET['names']) ? $srch->name = $_GET['names'] : $srch->name = '';
		isset($_GET['price_inc_tax']) ? $srch->price_inc_tax = $_GET['price_inc_tax'] : $srch->price_inc_tax = '';
		isset($_GET['volume_ml']) ? $srch->volume_ml = $_GET['volume_ml'] : $srch->volume_ml = '';
		isset($_GET['types']) ? $srch->type = $_GET['types'] : $srch->type = '';
		isset($_GET['styles']) ? $srch->style = $_GET['styles'] : $srch->style = '';
		isset($_GET['packagings']) ? $srch->packaging = $_GET['packagings'] : $srch->packaging = '';
		isset($_GET['vintage']) ? $srch->vintage = $_GET['vintage'] : $srch->vintage = '';
		isset($_GET['alcohols']) ? $srch->alcohol = $_GET['alcohols'] : $srch->alcohol = '';
		isset($_GET['organic']) ? $srch->organic = $_GET['organic'] : $srch->organic = '';
		isset($_GET['ethical']) ? $srch->ethical = $_GET['ethical'] : $srch->ethical = '';
		isset($_GET['kosher']) ? $srch->kosher = $_GET['kosher'] : $srch->kosher = '';
		isset($_GET['countries']) ? $srch->country = $_GET['countries'] : $srch->country = '';
		isset($_GET['regions']) ? $srch->region = $_GET['regions'] : $srch->region = '';
		isset($_GET['producers']) ? $srch->producer = $_GET['producers'] : $srch->producer = '';
		isset($_GET['suppliers']) ? $srch->supplier = $_GET['suppliers'] : $srch->supplier = '';
		isset($_GET['article_types']) ? $srch->article_type = $_GET['article_types'] : $srch->article_type = '';
		isset($_GET['search']) ? $srch->search = $_GET['search'] : $srch->search = '';
		isset($_GET['sort_in']) ? $srch->sort_in = $_GET['sort_in'] : $srch->sort_in = '';
		isset($_GET['order_by']) ? $srch->order_by = $_GET['order_by'] : $srch->order_by = '';
		isset($_GET['limit']) ? $srch->limit = $_GET['limit'] : $srch->limit = '';
		
		$data = $wine->find($srch);
		return $data;

	}
}

?>