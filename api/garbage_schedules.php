<?php
require_once __DIR__ . '/../controllers/AdminController.php';

header('Content-Type: application/json');

try {
    $adminController = new AdminController();
    $result = $adminController->getGarbageSchedules();

    if ($result['success']) {
        $events = array_map(function($schedule) {
            // Convert time to 24-hour format for proper display
            $time = date('H:i:s', strtotime($schedule['schedule_time']));
            
            return array(
                'id' => $schedule['id'],
                'title' => $schedule['area'],
                'start' => $schedule['schedule_date'] . 'T' . $time,
                'end' => $schedule['schedule_date'] . 'T' . $time,
                'className' => 'waste-' . $schedule['waste_type'],
                'display' => 'block',
                'extendedProps' => array(
                    'area' => $schedule['area'],
                    'time' => date('h:i A', strtotime($schedule['schedule_time'])),
                    'waste_type' => $schedule['waste_type']
                )
            );
        }, $result['data']);

        echo json_encode($events);
    } else {
        http_response_code(500);
        echo json_encode(array('error' => 'Failed to fetch garbage schedules'));
    }
} catch (Exception $e) {
    error_log("Error in garbage_schedules.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array('error' => 'Internal server error'));
}
?> 
 