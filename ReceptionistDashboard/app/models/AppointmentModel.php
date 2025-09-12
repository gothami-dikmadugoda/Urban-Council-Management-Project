<?php

require_once __DIR__ . '/Model.php';

class AppointmentModel extends Model {
    public function getAllAppointments() {
        $sql = "SELECT a.*, v.name as visitor_name 
                FROM appointments a
                JOIN visitors v ON a.visitor_id = v.visitor_id
                ORDER BY a.appointment_date DESC";
        return $this->query($sql);
    }
    
    public function getAppointmentById($id) {
        $sql = "SELECT * FROM appointments WHERE appointment_id = '$id'";
        return $this->query($sql);
    }
    
    public function createAppointment($data) {
        $sql = "INSERT INTO appointments (visitor_id, appointment_date, purpose, status, notes, duration) 
                VALUES ('{$data['visitor_id']}', '{$data['appointment_date']}', '{$data['purpose']}', 
                        '{$data['status']}', '{$data['notes']}', '{$data['duration']}')";
        return $this->query($sql);
    }
    
    public function updateAppointment($id, $data) {
        $sql = "UPDATE appointments 
                SET visitor_id = '{$data['visitor_id']}', 
                    appointment_date = '{$data['appointment_date']}', 
                    purpose = '{$data['purpose']}', 
                    status = '{$data['status']}', 
                    notes = '{$data['notes']}', 
                    duration = '{$data['duration']}' 
                WHERE appointment_id = '$id'";
        return $this->query($sql);
    }
    
    public function deleteAppointment($id) {
        $sql = "DELETE FROM appointments WHERE appointment_id = '$id'";
        return $this->query($sql);
    }
} 