<?php
require_once __DIR__ . '/../models/StaffLocation.php';

class LocationController {
    private $staffLocation;

    public function __construct() {
        $this->staffLocation = new StaffLocation();
    }

    // Update staff location
    public function updateLocation($data) {
        if (!isset($_SESSION['user_id']) || !isset($data['latitude']) || !isset($data['longitude'])) {
            return [
                'success' => false,
                'message' => 'Invalid request data'
            ];
        }

        $result = $this->staffLocation->updateLocation(
            $_SESSION['user_id'],
            $data['latitude'],
            $data['longitude']
        );

        return [
            'success' => $result,
            'message' => $result ? 'Location updated successfully' : 'Failed to update location'
        ];
    }

    // Get all active staff locations for map
    public function getActiveLocations() {
        try {
            $locations = $this->staffLocation->getActiveStaffLocations();
            return [
                'success' => true,
                'data' => $locations
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching locations'
            ];
        }
    }

    // Get location history for a specific staff member
    public function getStaffHistory($user_id, $start_date = null, $end_date = null) {
        try {
            $history = $this->staffLocation->getStaffLocationHistory($user_id, $start_date, $end_date);
            return [
                'success' => true,
                'data' => $history
            ];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching location history'
            ];
        }
    }

    // Validate if user is authorized to view locations
    public function validateAccess() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            header('Location: /urban2/login.php');
            exit();
        }

        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['department'] !== 'health') {
            header('Location: /urban2/access_denied.php');
            exit();
        }
    }
} 