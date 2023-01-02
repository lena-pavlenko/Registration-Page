<?php
class Db
{
    public $connect;
    
    public function __construct()
    {
        try{
            $this->connect = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        } catch (Exception $e) {
            $this->connect = null;
        }
    }
}