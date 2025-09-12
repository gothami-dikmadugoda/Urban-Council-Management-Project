<?php

class Model {
    protected $db;
    
    public function __construct() {
        global $conn;
        $this->db = $conn;
    }
    
    protected function query($sql) {
        return $this->db->query($sql);
    }
} 