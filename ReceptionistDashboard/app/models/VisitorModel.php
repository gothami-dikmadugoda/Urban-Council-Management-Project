<?php

require_once __DIR__ . '/Model.php';

class VisitorModel extends Model {
    public function getAllVisitors() {
        $sql = "SELECT * FROM visitors ORDER BY checkin_time DESC";
        return $this->query($sql);
    }
    
    public function getVisitorById($id) {
        $sql = "SELECT * FROM visitors WHERE visitor_id = '$id'";
        return $this->query($sql);
    }
    
    public function createVisitor($data) {
        $sql = "INSERT INTO visitors (name, email, phone, purpose, checkin_time) 
                VALUES ('{$data['name']}', '{$data['email']}', '{$data['phone']}', 
                        '{$data['purpose']}', NOW())";
        return $this->query($sql);
    }
    
    public function updateVisitor($id, $data) {
        $sql = "UPDATE visitors 
                SET name = '{$data['name']}', 
                    email = '{$data['email']}', 
                    phone = '{$data['phone']}', 
                    purpose = '{$data['purpose']}'
                WHERE visitor_id = '$id'";
        return $this->query($sql);
    }
    
    public function deleteVisitor($id) {
        $sql = "DELETE FROM visitors WHERE visitor_id = '$id'";
        return $this->query($sql);
    }
    
    public function getLatestVisitors($limit = 5) {
        $sql = "SELECT * FROM visitors ORDER BY checkin_time DESC LIMIT $limit";
        return $this->query($sql);
    }
} 