<?php

class Database {
    private static $instance = null;
    private $mysqli;

    private $host = "wheatley.cs.up.ac.za";
    private $db = "";
    private $user = "u24825532";
    private $password = "";

    private function __construct() {
        $this -> mysqli = new mysqli($this -> host, $this -> user, $this -> pass, $this -> db);

        if ($this -> mysqli -> connect_error) {
            die("Database connection failed: " . $this -> mysqli -> connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    public function connect() {
        return $this -> mysqli;
    }
}
?>