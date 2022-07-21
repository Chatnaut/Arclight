<?php

class DbManager
{
	//Database configuration
	private $dbhost = 'localhost';
	private $dbport = '27017';
	private $conn;

	function __construct(){
        //Connecting to MongoDB
        try {
			//Establish database connection
            $this->conn = new MongoDB\Driver\Manager('mongodb://'.$this->dbhost.':'.$this->dbport);
        }catch(Exception $e) {
            echo $e->getMessage();
			echo nl2br("n");
        }
    }
	
	function getConnection() {
		return $this->conn;
	}
}






?>