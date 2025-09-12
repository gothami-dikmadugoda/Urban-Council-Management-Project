<?php
// Include necessary files
require_once '../config/database.php';
require_once '../includes/functions.php';

// Handle visitor actions (add, edit, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add':
            addVisitor($conn);
            break;
        case 'edit':
            editVisitor($conn);
            break;
        case 'delete':
            deleteVisitor($conn);
            break;
        default:
            set_message('Invalid action', 'danger');
            header('Location: ../visitors.php');
            exit();
    }
} else {
    // Invalid request method
    set_message('Invalid request', 'danger');
    header('Location: ../visitors.php');
    exit();
}

// Add a new visitor
function addVisitor($conn) {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $id_card_number = $conn->real_escape_string($_POST['id_card_number'] ?? '');
    $id_verified = isset($_POST['id_verified']) ? 1 : 0;
    $purpose = $conn->real_escape_string($_POST['purpose'] ?? '');
    $checkin_time = $conn->real_escape_string($_POST['checkin_time']);
    $checkout_time = !empty($_POST['checkout_time']) ? "'".$conn->real_escape_string($_POST['checkout_time'])."'" : "NULL";
    
    // Format datetime for MySQL
    $checkin_time = date('Y-m-d H:i:s', strtotime($checkin_time));
    if ($checkout_time !== "NULL") {
        $checkout_time = date('Y-m-d H:i:s', strtotime(trim($checkout_time, "'")));
        $checkout_time = "'".$checkout_time."'";
    }
    
    // Insert visitor into database
    $sql = "INSERT INTO visitors (name, email, phone, id_card_number, id_verified, purpose, checkin_time, checkout_time) 
            VALUES ('$name', '$email', '$phone', '$id_card_number', '$id_verified', '$purpose', '$checkin_time', $checkout_time)";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Visitor added successfully', 'success');
    } else {
        set_message('Error adding visitor: ' . $conn->error, 'danger');
    }
    
    header('Location: ../visitors.php');
    exit();
}

// Edit an existing visitor
function editVisitor($conn) {
    // Get form data
    $visitor_id = $conn->real_escape_string($_POST['visitor_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $phone = $conn->real_escape_string($_POST['phone'] ?? '');
    $id_card_number = $conn->real_escape_string($_POST['id_card_number'] ?? '');
    $id_verified = isset($_POST['id_verified']) ? 1 : 0;
    $purpose = $conn->real_escape_string($_POST['purpose'] ?? '');
    $checkin_time = $conn->real_escape_string($_POST['checkin_time']);
    $checkout_time = !empty($_POST['checkout_time']) ? "'".$conn->real_escape_string($_POST['checkout_time'])."'" : "NULL";
    
    // Format datetime for MySQL
    $checkin_time = date('Y-m-d H:i:s', strtotime($checkin_time));
    if ($checkout_time !== "NULL") {
        $checkout_time = date('Y-m-d H:i:s', strtotime(trim($checkout_time, "'")));
        $checkout_time = "'".$checkout_time."'";
    }
    
    // Update visitor in database
    $sql = "UPDATE visitors SET 
            name = '$name', 
            email = '$email', 
            phone = '$phone', 
            id_card_number = '$id_card_number', 
            id_verified = '$id_verified', 
            purpose = '$purpose', 
            checkin_time = '$checkin_time', 
            checkout_time = $checkout_time 
            WHERE visitor_id = '$visitor_id'";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Visitor updated successfully', 'success');
    } else {
        set_message('Error updating visitor: ' . $conn->error, 'danger');
    }
    
    header('Location: ../visitors.php');
    exit();
}

// Delete a visitor
function deleteVisitor($conn) {

    $visitor_id = $conn->real_escape_string($_POST['visitor_id']);

echo $visitor_id;


    
    // First check if visitor has appointments
    $sql = "SELECT * FROM appointments WHERE visitor_id = '$visitor_id'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        // Delete appointments first (cascade should handle this, but just to be safe)
        $sql = "DELETE FROM appointments WHERE visitor_id = '$visitor_id'";
        $conn->query($sql);
    }
    
    // Now delete the visitor
    $sql = "DELETE FROM visitors WHERE visitor_id = '$visitor_id'";
    
    if ($conn->query($sql) === TRUE) {
        set_message('Visitor deleted successfully', 'success');
    } else {
        set_message('Error deleting visitor: ' . $conn->error, 'danger');
    }
    
header('Location: ../visitors.php');
    exit();
}
?>