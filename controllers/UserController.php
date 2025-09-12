<?php
require_once dirname(__DIR__) . '/config/paths.php';
require_once MODELS_PATH . '/User.php';
require_once dirname(__DIR__) . '/config/database.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            $this->user = new User($this->db);
        } catch(Exception $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function validateUserAccess() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /urban2/login.php');
            exit;
        }
        // Log user access validation
        error_log("User access validated for user ID: " . $_SESSION['user_id']);
    }

    public function register($data) {
        // Validate input
        if(empty($data['first_name']) || empty($data['last_name']) || empty($data['email']) || 
           empty($data['password']) || empty($data['phone']) || empty($data['address'])) {
            return array(
                "success" => false,
                "message" => "සියලු ක්ෂේත්ර පුරවන්න / Please fill all fields"
            );
        }

        // Check if email already exists
        if($this->emailExists($data['email'])) {
            return array(
                "success" => false,
                "message" => "මෙම ඊමේල් ලිපිනය දැනටමත් භාවිතා කර ඇත / This email is already registered"
            );
        }

        // Set user properties
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
        $this->user->phone = $data['phone'];
        $this->user->address = $data['address'];
        $this->user->role = isset($data['role']) ? $data['role'] : 'citizen';
        $this->user->department = isset($data['department']) ? $data['department'] : null;
        $this->user->job_role = isset($data['job_role']) ? $data['job_role'] : null;
        $this->user->status = 'active';

        // Create user
        if($this->user->create()) {
            return array(
                "success" => true,
                "message" => "පරිශීලකයා සාර්ථකව ලියාපදිංචි විය / User registered successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "ලියාපදිංචි වීමේදී දෝෂයක් ඇති විය / Error registering user"
        );
    }

    public function login($email, $password) {
        if($this->user->login($email, $password)) {
            // Start session and store user data
            $_SESSION['user_id'] = $this->user->id;
            $_SESSION['user_role'] = $this->user->role;
            $_SESSION['user_name'] = $this->user->first_name . ' ' . $this->user->last_name;
            $_SESSION['department'] = $this->user->department;
            $_SESSION['job_role'] = $this->user->job_role;

            return array(
                "success" => true,
                "message" => "සාර්ථකව පිවිසුණා / Login successful",
                "redirect" => $this->getRedirectUrl($this->user->role,$this->user->department)
            );
        }

        return array(
            "success" => false,
            "message" => "වලංගු නොවන ඊමේල් හෝ මුරපදය / Invalid email or password"
        );
    }

    public function logout() {
        session_start();
        session_destroy();
        return array(
            "success" => true,
            "message" => "සාර්ථකව පිටවී ඇත / Logged out successfully"
        );
    }

    private function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = ? LIMIT 0,1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    private function getRedirectUrl($role,$department) {
        // Check if there's a stored redirect URL for IT staff
        if ($role === 'staff' && isset($_SESSION['department']) && $_SESSION['department'] === 'it' && 
            isset($_SESSION['job_role']) && $_SESSION['job_role'] === 'it_staff' && 
            isset($_SESSION['redirect_after_login'])) {
            $redirect = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            return $redirect;
        }

        // Default redirects based on role
        switch ($role) {
            case 'admin':
                return '/urban2/views/admin/dashboard.php';
            case 'staff':
                return '/urban2/views/admin/staff_dashboard.php';
            case 'reception':
                return '/urban2/ReceptionistDashboard';
            case 'citizen':
            case 'private_company':
                return '/urban2/views/citizen/dashboard.php';
            default:
                return '/urban2/login.php';
        }
    }

    public function getUsers($role = null) {
        $query = "SELECT * FROM users";
        if($role) {
            $query .= " WHERE role = ?";
        }
        $stmt = $this->db->prepare($query);
        if($role) {
            $stmt->bindParam(1, $role);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, first_name, last_name, email, phone, address, role, 
                       department, job_role, profile_picture, status
                FROM users 
                WHERE id = ? AND status = 'active'
            ");
            
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            return $user;
        } catch(PDOException $e) {
            error_log("Error getting user: " . $e->getMessage());
            throw new Exception('Failed to get user information');
        }
    }

    public function updateUser($userId, $data) {
        try {
            $this->db->beginTransaction();

            $updateFields = [];
            $params = [];

            // Handle basic fields
            if (isset($data['first_name'])) {
                $updateFields[] = "first_name = ?";
                $params[] = $data['first_name'];
            }
            if (isset($data['last_name'])) {
                $updateFields[] = "last_name = ?";
                $params[] = $data['last_name'];
            }
            if (isset($data['email'])) {
                $updateFields[] = "email = ?";
                $params[] = $data['email'];
            }
            if (isset($data['phone'])) {
                $updateFields[] = "phone = ?";
                $params[] = $data['phone'];
            }
            if (isset($data['address'])) {
                $updateFields[] = "address = ?";
                $params[] = $data['address'];
            }

            // Handle password update
            if (isset($data['password'])) {
                $updateFields[] = "password = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            // Handle profile picture upload
            if (isset($data['profile_picture']) && is_array($data['profile_picture'])) {
                $uploadDir = dirname(__DIR__) . '/uploads/profile_pictures/';
                
                // Create upload directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    if (!mkdir($uploadDir, 0777, true)) {
                        throw new Exception('Failed to create upload directory');
                    }
                }

                // Generate unique filename
                $fileName = uniqid() . '_' . basename($data['profile_picture']['name']);
                $targetPath = $uploadDir . $fileName;

                // Move uploaded file
                if (move_uploaded_file($data['profile_picture']['tmp_name'], $targetPath)) {
                    $updateFields[] = "profile_picture = ?";
                    $params[] = '/urban2/uploads/profile_pictures/' . $fileName;
                } else {
                    throw new Exception('Failed to upload profile picture');
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No fields to update');
            }

            // Add user ID to params
            $params[] = $userId;

            // Construct and execute update query
            $query = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Profile updated successfully'
            ];

        } catch(Exception $e) {
            $this->db->rollBack();
            error_log("Error updating user: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteUser($id) {
        $this->user->id = $id;
        if($this->user->delete()) {
            return array(
                "success" => true,
                "message" => "පරිශීලකයා සාර්ථකව මකා දමන ලදී / User deleted successfully"
            );
        }

        return array(
            "success" => false,
            "message" => "මකා දැමීමේදී දෝෂයක් ඇති විය / Error deleting user"
        );
    }
}
?> 