<?php
class Database {
    private $host;
    private $user;
    private $pass;
    private $data;
    public $conn;

    function __construct() {
        $this->connection();
    }

    function connection() {
        $this->host = "localhost";
        $this->user = "root";
        $this->pass = ""; // Update if you set a password
        $this->data = "melodyhub_db";

        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->data); // Specify port
        return $this->conn;
        // Check connection
        // if ($this->conn->connect_error) {
        //     die("Connection failed: " . $this->conn->connect_error . " (Host: " . $this->host . " Port: 3307)");
        // } else {
        //     echo "Connected successfully!";
        // }
    }
}
?>