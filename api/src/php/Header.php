<?php
include 'ErrorMsg.php';

class Header {
	
	public $status = "";
	public $error = null;

	public function __construct() {

	}

	public function setHeader($errorCode) {

		if ($errorCode == null) {
			$this->status = "OK";
		}
		
		else {
			
			$errorMsg = new ErrorMsg();
			$errorMsg->setError($errorCode);

			$this->status = "FAILED";
			$this->error = $errorMsg;	
		}
	}
}

?>