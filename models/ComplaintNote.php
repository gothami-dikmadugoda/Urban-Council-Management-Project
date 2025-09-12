<?php
require_once dirname(__DIR__) . '/config/database.php';

class ComplaintNote {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO complaint_notes (
                complaint_id, user_id, note, attachment_path, 
                created_at
            ) VALUES (?, ?, ?, ?, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['complaint_id'],
                $data['user_id'],
                $data['note'],
                $data['attachment_path'] ?? null
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating complaint note: " . $e->getMessage());
            return false;
        }
    }

    public function getByComplaintId($complaintId) {
        try {
            $sql = "SELECT cn.*, u.name as user_name, u.role as user_role 
                    FROM complaint_notes cn 
                    LEFT JOIN users u ON cn.user_id = u.id 
                    WHERE cn.complaint_id = ? 
                    ORDER BY cn.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$complaintId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching complaint notes: " . $e->getMessage());
            return [];
        }
    }

    public function handleAttachment($file) {
        // Validate file type
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Validate file size (10MB max)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return false;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = dirname(__DIR__) . '/uploads/notes/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('note_') . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return 'uploads/notes/' . $filename;
        }

        return false;
    }
}
?> 