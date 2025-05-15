<?php

class Database {
    private static $instance = null;
    private $mysqli;

    private $host = "wheatley.cs.up.ac.za";
    private $db = "u24825532_u24580482";
    private $user = "u24825532";
    private $password = "KEDLNSSD2QCRO4D5SLZVXJYUJ46TE3DB";

    private function __construct() {
        $this -> mysqli = new mysqli($this -> host, $this -> user, $this -> pass, $this -> db);

        if ($this -> mysqli -> connect_error) {
            die("Database connection failed: " . $this -> mysqli -> connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function connect() {
        return $this -> mysqli;
    }
}
?>