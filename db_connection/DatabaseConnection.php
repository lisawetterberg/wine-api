<?php 
class DatabaseConnection {

	const MYSQL_HOST = 'host';

  	const MYSQL_USERNAME = 'name';

  	const MYSQL_PASSWORD = 'password';

  	const MYSQL_DATABASE = 'db';

  	public $mysqli;

	public function __construct() {

		$this->connect();

	}

	private function connect() {

		$this->mysqli = new mysqli(
    		self::MYSQL_HOST,
    		self::MYSQL_USERNAME,
    		self::MYSQL_PASSWORD,
    		self::MYSQL_DATABASE
    		);
		
		mysqli_set_charset($this->mysqli,"utf8");
		
		return $this->mysqli->connect_errno;;

	}

	public function connectionIsValid() {
  		return ($this->mysqli->connect_errno === 0) ? true : false;
  	}

	public function getResult($query) {

		if($this->connectionIsValid() == TRUE) {
			$result = $this->mysqli->query($query);

			return $result;
		}
		else {
			return null;
		}
		
	}
}


?>