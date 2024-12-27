<?php

class Database {
    //set database connection parameters
    private $dsn = "odbc:MDDB4";
    private $username = "monetdb";
    private $password = "monetdb";

    //sets up the database connection
    private function connect(){
        try{
            $pdo = new PDO($this->dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }catch (Exception $e) {die($e->getMessage());}
    }

    //executes selection query
    protected function select ($sql){
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    //executes rowcount query
    protected function row_count ($sql){
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        $total_row = $stmt->rowCount();
        return $total_row;
    }

/*
    function __destruct(){
        if ($this->stmt !== null) {$this->stmt = null;}
        if ($this->pdo !== null) {$this->pdo = null;}
    }

*/

}

