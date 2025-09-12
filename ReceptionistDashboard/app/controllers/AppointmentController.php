<?php

require_once __DIR__ . '/Controller.php';

class AppointmentController extends Controller {
    private $appointmentModel;
    
    public function __construct() {
        $this->appointmentModel = $this->model('AppointmentModel');
    }
    
    public function index() {
        $appointments = $this->appointmentModel->getAllAppointments();
        $edit_mode = false;
        $appointment = null;
        
        if (isset($_GET['edit'])) {
            $result = $this->appointmentModel->getAppointmentById($_GET['edit']);
            if ($result && $result->num_rows > 0) {
                $appointment = $result->fetch_assoc();
                $edit_mode = true;
            }
        }
        
        $this->view('appointments/index', [
            'appointments' => $appointments,
            'edit_mode' => $edit_mode,
            'appointment' => $appointment
        ]);
    }
    
    public function process() {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $this->appointmentModel->createAppointment($_POST);
        } elseif ($action == 'edit') {
            $this->appointmentModel->updateAppointment($_POST['appointment_id'], $_POST);
        } elseif ($action == 'delete') {
            $this->appointmentModel->deleteAppointment($_POST['appointment_id']);
        }
        
        $this->redirect('/appointments');
    }
}