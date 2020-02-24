<?php 
class ErrorMsg {

	public $id = null;
	public $description = "";

	public function __construct() {
	
	}

	public function setError($error) {

		switch($error) {
			case 101: 
				$this->id = $error;
				$this->description = "Invalid method requested. Please refer to documentation for valid methods.";
				break;
			case 102:
				$this->id = $error;
				$this->description = "No method was requested. Please refer to documentation for valid methods.";
				break;
			case 103:
				$this->id = $error;
				$this->description = "Database error. Connection to database failed, try again. If the error remains, please conntact admin.";
				break;
		}

	}

}

?>