<?php
require_once dirname(__DIR__) . '/config/database.php';

class Booking {
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
        } catch(Exception $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function getAvailableAreas() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    id, 
                    name, 
                    description, 
                    capacity, 
                    hourly_rate,
                    status
                FROM public_areas 
                WHERE status = 'available'
                ORDER BY name
            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Debug output
            error_log("Available areas query results: " . print_r($results, true));
            
            return $results;
        } catch (PDOException $e) {
            error_log("Error getting areas: " . $e->getMessage());
            return [];
        }
    }

    public function createBooking($data) {
        try {
            // Generate unique booking ID
            $booking_id = 'BK' . strtoupper(uniqid());
            
            $stmt = $this->db->prepare("
                INSERT INTO bookings (
                    booking_id, user_id, area_id, start_datetime, 
                    duration_hours, description, status, assigned_to, total_amount
                ) VALUES (
                    :booking_id, :user_id, :area_id, :start_datetime,
                    :duration_hours, :description, 'pending', :assigned_to, :total_amount
                )
            ");

            $params = [
                'booking_id' => $booking_id,
                'user_id' => $data['user_id'],
                'area_id' => $data['area_id'],
                'start_datetime' => $data['start_datetime'],
                'duration_hours' => $data['duration_hours'],
                'description' => $data['description'] ?? null,
                'assigned_to' => $data['assigned_to'],
                'total_amount' => $data['total_amount']
            ];

            $result = $stmt->execute($params);

            if ($result) {
                $id = $this->db->lastInsertId();
                $this->createBookingReminders($id);
                return [
                    'status' => 'success',
                    'booking_id' => $booking_id,
                    'id' => $id
                ];
            }
            return [
                'status' => 'error',
                'message' => 'Failed to create booking'
            ];
        } catch (PDOException $e) {
            error_log("Error in createBooking: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Database error occurred'
            ];
        }
    }

    public function isTimeSlotAvailable($data) {
        try {
            // Calculate end datetime based on start time and duration
            $start_datetime = $data['start_datetime'];
            $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime . ' + ' . $data['duration_hours'] . ' hours'));

            // Debug log the input parameters
            error_log("Checking availability for: Area ID: {$data['area_id']}, Start: {$start_datetime}, End: {$end_datetime}");

            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM bookings
                WHERE area_id = :area_id
                AND status NOT IN ('cancelled', 'rejected')
                AND (
                    -- New booking starts during an existing booking
                    (:start_datetime BETWEEN start_datetime AND DATE_ADD(start_datetime, INTERVAL duration_hours HOUR))
                    OR
                    -- New booking ends during an existing booking
                    (:end_datetime BETWEEN start_datetime AND DATE_ADD(start_datetime, INTERVAL duration_hours HOUR))
                    OR
                    -- New booking completely encompasses an existing booking
                    (start_datetime BETWEEN :start_datetime AND :end_datetime)
                )
            ");

            $stmt->execute([
                'area_id' => $data['area_id'],
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Debug log the result
            error_log("Availability check result: " . ($result['count'] == 0 ? 'Available' : 'Not Available') . " (Found {$result['count']} conflicting bookings)");
            
            return $result['count'] == 0;
        } catch (PDOException $e) {
            error_log("Error checking time slot availability: " . $e->getMessage());
            return false;
        }
    }

    public function getUpcomingBookings($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    b.*,
                    pa.name as area_name,
                    pa.location as area_location,
                    pa.hourly_rate
                FROM bookings b
                JOIN public_areas pa ON b.area_id = pa.id
                WHERE b.user_id = :user_id
                AND b.start_datetime >= NOW()
                AND b.status != 'cancelled'
                ORDER BY b.start_datetime ASC
            ");
            $stmt->execute(['user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching upcoming bookings: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingBookings() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    b.*,
                    pa.name as area_name,
                    pa.location as area_location,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM bookings b
                JOIN public_areas pa ON b.area_id = pa.id
                JOIN users u ON b.user_id = u.id
                WHERE b.status = 'pending'
                ORDER BY b.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching pending bookings: " . $e->getMessage());
            return [];
        }
    }

    public function getBookingById($booking_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    b.*,
                    pa.name as area_name,
                    pa.location as area_location
                FROM bookings b
                JOIN public_areas pa ON b.area_id = pa.id
                WHERE b.id = :booking_id
            ");
            $stmt->execute(['booking_id' => $booking_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error fetching booking: " . $e->getMessage());
            return null;
        }
    }

    public function updateBookingStatus($booking_id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE bookings 
                SET status = :status
                WHERE id = :booking_id
            ");
            return $stmt->execute([
                'booking_id' => $booking_id,
                'status' => $status
            ]);
        } catch(PDOException $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }

    private function createBookingReminders($booking_id) {
        try {
            $reminder_types = ['24h', '1h', '15min'];
            $stmt = $this->db->prepare("
                INSERT INTO booking_reminders (booking_id, reminder_type)
                VALUES (:booking_id, :reminder_type)
            ");

            foreach ($reminder_types as $type) {
                $stmt->execute([
                    'booking_id' => $booking_id,
                    'reminder_type' => $type
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error creating booking reminders: " . $e->getMessage());
        }
    }

    public function getReceptionist() {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE role = 'staff' 
                AND job_role = 'receptionist' 
                AND status = 'active' 
                LIMIT 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['id'] : null;
        } catch (PDOException $e) {
            error_log("Error getting receptionist: " . $e->getMessage());
            return null;
        }
    }

    public function calculateTotalAmount($area_id, $duration_hours) {
        try {
            $stmt = $this->db->prepare("
                SELECT hourly_rate 
                FROM public_areas 
                WHERE id = :area_id
            ");
            $stmt->execute(['area_id' => $area_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result['hourly_rate'] * $duration_hours;
            }
            return 0;
        } catch (PDOException $e) {
            error_log("Error calculating total amount: " . $e->getMessage());
            return 0;
        }
    }

    public function getAreaById($area_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM public_areas 
                WHERE id = :area_id
            ");
            $stmt->execute(['area_id' => $area_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting area by ID: " . $e->getMessage());
            return null;
        }
    }
} 