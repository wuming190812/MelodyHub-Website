<?php
include "db.php";
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
class functional extends database
{
    public function __construct()
    {
        $this->connection();
    }
    public function sql($query)
    {
        $result=$this->conn->query($query);
        return $result;
    }
    public function select($symbol,$table_name,$column)
    {
        $result=$this->conn->query("select $symbol from ".$table_name." ".$column);
        return $result;
    }
    public function insert($table_name,$column){
        $result=$this->conn->query("insert into ".$table_name." ".$column);
        return $result;
    }
    public function update($table_name,$column,$getID){
        $result=$this->conn->query("update $table_name set ".$column." ".$getID);
        return $result;
    }
    public function delete($table_name,$getID){
        $result=$this->conn->query("delete from ".$table_name." ".$getID);
        return $result;
    }
}
?>