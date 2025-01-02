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
    public function select ($sql){
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }

    //executes rowcount query
    public function row_count ($sql){
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute();
        $total_row = $stmt->rowCount();
        return $total_row;
    }
}

