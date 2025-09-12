<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/models/Booking.php';

class BookingController {
    private $bookingModel;

    public function __construct() {
        $this->bookingModel = new Booking();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create_booking':
                    $this->handleCreateBooking();
                    break;
            }
        }
    }

    private function handleCreateBooking() {
        try {
            // Check if user is authenticated
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }

            // Add user_id to POST data
            $_POST['user_id'] = $_SESSION['user_id'];

            // Process the booking
            $result = $this->bookingModel->createBooking($_POST);

            if ($result['status'] === 'success') {
                $_SESSION['message'] = 'Booking submitted successfully! Your Booking ID is: ' . $result['booking_id'] . 
                                     '. A receptionist will review your booking request shortly.';
                $_SESSION['message_type'] = 'success';
                $_SESSION['booking_success'] = true;
                header('Location: /A-11/views/citizen/schedule-booking.php');
                exit();
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            $_SESSION['message'] = 'Error: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
            header('Location: /A-11/views/citizen/schedule-booking.php');
            exit();
        }
    }

    public function getAvailableFacilities() {
        return $this->bookingModel->getAvailableFacilities();
    }

    public function createBooking($data) {
        try {
            // Check if the area exists and is available
            $area = $this->bookingModel->getAreaById($data['area_id']);
            if (!$area) {
                throw new Exception("Selected area does not exist");
            }
            if ($area['status'] !== 'available') {
                throw new Exception("Selected area is not available for booking");
            }

            // Check if the time slot is available
            if (!$this->bookingModel->isTimeSlotAvailable($data)) {
                throw new Exception("Selected time slot is not available");
            }

            // Create the booking
            $result = $this->bookingModel->createBooking($data);
            
            if ($result['status'] === 'success') {
                return $result;
            } else {
                throw new Exception($result['message'] ?? "Failed to create booking");
            }
        } catch (Exception $e) {
            error_log("Error in createBooking: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function getUpcomingBookings($user_id) {
        return $this->bookingModel->getUpcomingBookings($user_id);
    }

    public function getPendingBookings() {
        return $this->bookingModel->getPendingBookings();
    }

    public function updateBookingStatus($booking_id, $status, $message = '') {
        try {
            // Update booking status
            $result = $this->bookingModel->updateBookingStatus($booking_id, $status);
            
            if ($result) {
                // Notify user about the status update
                $this->notifyUser($booking_id, $status, $message);
                return [
                    'status' => 'success',
                    'message' => 'Booking status updated successfully'
                ];
            } else {
                throw new Exception('Failed to update booking status');
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateBookingData($data) {
        $required_fields = ['user_id', 'facility_id', 'booking_date', 'start_time', 'end_time', 'purpose'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Validate date format
        if (!strtotime($data['booking_date'])) {
            throw new Exception("Invalid booking date format");
        }

        // Validate time format
        if (!strtotime($data['start_time']) || !strtotime($data['end_time'])) {
            throw new Exception("Invalid time format");
        }

        // Check if booking date is not in the past
        $booking_date = new DateTime($data['booking_date']);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        if ($booking_date < $today) {
            throw new Exception("Booking date cannot be in the past");
        }

        // Check if end time is after start time
        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            throw new Exception("End time must be after start time");
        }
    }

    private function notifyReceptionist($booking_id) {
        // Get booking details
        $booking = $this->bookingModel->getBookingById($booking_id);
        
        if ($booking) {
            // Create notification for receptionist
            $notification = [
                'type' => 'new_booking',
                'title' => 'New Booking Request',
                'message' => "New booking request for {$booking['facility_name']} on " . 
                            date('F j, Y', strtotime($booking['booking_date'])) . 
                            " from " . date('h:i A', strtotime($booking['start_time'])) . 
                            " to " . date('h:i A', strtotime($booking['end_time'])),
                'booking_id' => $booking_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Save notification to database
            $this->bookingModel->createNotification($notification);
        }
    }

    private function notifyUser($booking_id, $status, $message = '') {
        // Get booking details
        $booking = $this->bookingModel->getBookingById($booking_id);
        
        if ($booking) {
            $status_text = ucfirst($status);
            $notification = [
                'type' => 'booking_status',
                'title' => "Booking {$status_text}",
                'message' => "Your booking for {$booking['facility_name']} on " . 
                            date('F j, Y', strtotime($booking['booking_date'])) . 
                            " has been {$status_text}" . 
                            ($message ? ". Message: {$message}" : ""),
                'booking_id' => $booking_id,
                'user_id' => $booking['user_id'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            // Save notification to database
            $this->bookingModel->createNotification($notification);
        }
    }

    public function getAvailableAreas() {
        return $this->bookingModel->getAvailableAreas();
    }

    public function getReceptionist() {
        return $this->bookingModel->getReceptionist();
    }

    public function calculateTotalAmount($area_id, $duration_hours) {
        return $this->bookingModel->calculateTotalAmount($area_id, $duration_hours);
    }
}

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new BookingController();
} 