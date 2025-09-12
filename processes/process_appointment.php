<?php
// Include necessary files
require_once '../config/database.php';
require_once '../includes/functions.php';

// Handle appointment actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    global $conn; // Make connection available
    
    switch ($action) {
        case 'add':
            addAppointment($conn);
            break;
        case 'edit':
            editAppointment($conn);
            break;
        case 'delete':
            deleteAppointment($conn);
            break;
        default:
            set_message('Invalid action', 'danger');
            header('Location: ../appointments.php');
            exit();
    }
} else {
    // Invalid request method
    set_message('Invalid request', 'danger');
    header('Location: ../appointments.php');
    exit();
}

// Add a new appointment
function addAppointment($conn) {
    // Get form data
    $visitor_id = $conn->real_escape_string($_POST['visitor_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $purpose = $conn->real_escape_string($_POST['purpose'] ?? '');
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');
    $duration = floatval($_POST['duration']);
    
    // Format datetime for MySQL
    $appointment_date = date('Y-m-d H:i:s', strtotime($appointment_date));
    
    // Insert appointment into database
    $sql = "INSERT INTO appointments (visitor_id, appointment_date, purpose, status, notes, duration) 
            VALUES ('$visitor_id', '$appointment_date', '$purpose', '$status', '$notes', '$duration')";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Appointment added successfully', 'success');
    } else {
        set_message('Error adding appointment: ' . $conn->error, 'danger');
    }
    
    // Redirect based on referer
    $referer = $_SERVER['HTTP_REFERER'] ?? '../appointments.php';
    if (strpos($referer, 'calendar.php') !== false) {
        header('Location: ../calendar.php');
    } else {
        header('Location: ../appointments.php');
    }
    exit();
}

// Edit an existing appointment
function editAppointment($conn) {
    // Get form data
    $appointment_id = $conn->real_escape_string($_POST['appointment_id']);
    $visitor_id = $conn->real_escape_string($_POST['visitor_id']);
    $appointment_date = $conn->real_escape_string($_POST['appointment_date']);
    $purpose = $conn->real_escape_string($_POST['purpose'] ?? '');
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');
    $duration = floatval($_POST['duration']);
    
    // Format datetime for MySQL
    $appointment_date = date('Y-m-d H:i:s', strtotime($appointment_date));
    
    // Update appointment in database
    $sql = "UPDATE appointments SET 
            visitor_id = '$visitor_id', 
            appointment_date = '$appointment_date', 
            purpose = '$purpose', 
            status = '$status', 
            notes = '$notes', 
            duration = '$duration' 
            WHERE appointment_id = '$appointment_id'";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Appointment updated successfully', 'success');
    } else {
        set_message('Error updating appointment: ' . $conn->error, 'danger');
    }
    
    header('Location: ../appointments.php');
    exit();
}

// Delete an appointment
function deleteAppointment($conn) {
    $appointment_id = $conn->real_escape_string($_POST['appointment_id']);
    
    // Delete the appointment
    $sql = "DELETE FROM appointments WHERE appointment_id = '$appointment_id'";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Appointment deleted successfully', 'success');
    } else {
        set_message('Error deleting appointment: ' . $conn->error, 'danger');
    }
    
    header('Location: ../appointments.php');
    exit();
}
?>