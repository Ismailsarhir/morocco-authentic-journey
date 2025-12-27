<?php

namespace Scripts\Shared;

use mysqli;

class OldDb{
	private $host;
	private $user;
	private $password;
	private $db_name;
	private static $_instance;

	public function __construct($host, $user, $password, $db_name) {
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->db_name = $db_name;

		self::$_instance = new mysqli($this->host, $this->user, $this->password, $this->db_name);
		if (mysqli_connect_errno()) {
			print_flush("Failed to connect to MySQL: " . mysqli_connect_error());
			exit();
		}
		self::$_instance->set_charset("utf8");
	}

	public static function get_instance($host, $user, $password, $db_name){
		if (is_null(self::$_instance)) {
			new self($host, $user, $password, $db_name);
		}
		return self::$_instance;
	}
}

