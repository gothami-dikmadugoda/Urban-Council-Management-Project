<?php
require_once __DIR__ . '/../models/GarbageSchedule.php';
require_once __DIR__ . '/../config/database.php';

class GarbageScheduleController {
    private $db;
    private $schedule;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->schedule = new GarbageSchedule($this->db);
    }

    public function getAllSchedules() {
        $query = "SELECT * FROM garbage_schedules ORDER BY schedule_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getScheduleById($id) {
        $query = "SELECT * FROM garbage_schedules WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSchedule($data) {
        // Validate input
        if(empty($data['area']) || empty($data['schedule_date']) || empty($data['schedule_time'])) {
            return array(
                "success" => false,
                "message" => "සියලු ක්ෂේත්ර පුරවන්න / Please fill all fields"
            );
        }

        // Set schedule properties
        $this->schedule->area = $data['area'];
        $this->schedule->schedule_date = $data['schedule_date'];
        $this->schedule->schedule_time = $data['schedule_time'];
        $this->schedule->status = $data['status'] ?? 'active';
        $this->schedule->created_by = $_SESSION['user_id'];

        // Create schedule
        if($this->schedule->create()) {
            return array(
                "success" => true,
                "message" => "සාර්ථකව කුණු රැස් කිරීමේ කාලසටහන එකතු කරන ලදී / Garbage schedule added successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "කුණු රැස් කිරීමේ කාලසටහන එකතු කිරීමේදී දෝෂයක් ඇති විය / Error adding garbage schedule"
        );
    }

    public function updateSchedule($id, $data) {
        // Validate input
        if(empty($data['area']) || empty($data['schedule_date']) || empty($data['schedule_time'])) {
            return array(
                "success" => false,
                "message" => "සියලු ක්ෂේත්ර පුරවන්න / Please fill all fields"
            );
        }

        // Set schedule properties
        $this->schedule->id = $id;
        $this->schedule->area = $data['area'];
        $this->schedule->schedule_date = $data['schedule_date'];
        $this->schedule->schedule_time = $data['schedule_time'];
        $this->schedule->status = $data['status'] ?? 'active';

        // Update schedule
        if($this->schedule->update()) {
            return array(
                "success" => true,
                "message" => "සාර්ථකව කුණු රැස් කිරීමේ කාලසටහන යාවත්කාලීන කරන ලදී / Garbage schedule updated successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "යාවත්කාලීන කිරීමේදී දෝෂයක් ඇති විය / Error updating garbage schedule"
        );
    }

    public function deleteSchedule($id) {
        $this->schedule->id = $id;
        if($this->schedule->delete()) {
            return array(
                "success" => true,
                "message" => "සාර්ථකව කුණු රැස් කිරීමේ කාලසටහන මකා දමන ලදී / Garbage schedule deleted successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "මකා දැමීමේදී දෝෂයක් ඇති විය / Error deleting garbage schedule"
        );
    }

    public function getSchedulesByArea($area) {
        $query = "SELECT * FROM garbage_schedules WHERE area = ? ORDER BY schedule_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $area);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingSchedules() {
        $query = "SELECT * FROM garbage_schedules WHERE schedule_date >= CURDATE() AND status = 'active' ORDER BY schedule_date ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?> 