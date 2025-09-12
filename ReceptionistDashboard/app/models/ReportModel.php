<?php

require_once __DIR__ . '/Model.php';

class ReportModel extends Model {
    public function getDailyVisitors($date) {
        $sql = "SELECT * FROM visitors WHERE DATE(checkin_time) = '$date' ORDER BY checkin_time DESC";
        return $this->query($sql);
    }
    
    public function getMonthlyVisitors($year, $month) {
        $sql = "SELECT * FROM visitors 
                WHERE YEAR(checkin_time) = '$year' AND MONTH(checkin_time) = '$month' 
                ORDER BY checkin_time DESC";
        return $this->query($sql);
    }
    
    public function getAppointmentStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
                FROM appointments";
        return $this->query($sql);
    }
    
    public function getVisitorStats() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(DISTINCT email) as unique_visitors,
                    COUNT(CASE WHEN DATE(checkin_time) = CURDATE() THEN 1 END) as today
                FROM visitors";
        return $this->query($sql);
    }
} 