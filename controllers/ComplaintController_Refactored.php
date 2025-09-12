<?php
require_once __DIR__ . '/../models/Complaint.php';
require_once __DIR__ . '/../config/database.php';

class ComplaintController {
    private $db;
    private $complaint;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->complaint = new Complaint($this->db);
    }

    public function getAssignedComplaints($staffId) {
        return $this->complaint->getAssignedTo($staffId);
    }

    public function getComplaintById($id) {
        return $this->complaint->getById($id);
    }

    public function updateComplaintStatus($id, $status, $notes = null) {
        return $this->complaint->updateStatus($id, $status);
    }

    public function addComplaintNote($id, $note) {
        return $this->complaint->addNote($id, $_SESSION['user_id'], $note);
    }

    public function getComplaintNotes($id) {
        return $this->complaint->getNotes($id);
    }

    public function createComplaint($data) {
        return $this->complaint->create($data);
    }

    public function getComplaintsByUserId($userId) {
        return $this->complaint->getByUserId($userId);
    }
}
?>
