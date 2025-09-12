<?php

require_once __DIR__ . '/Controller.php';

class VisitorController extends Controller {
    private $visitorModel;
    
    public function __construct() {
        $this->visitorModel = $this->model('VisitorModel');
    }
    
    public function index() {
        $visitors = $this->visitorModel->getAllVisitors();
        $edit_mode = false;
        $visitor = null;
        
        if (isset($_GET['edit'])) {
            $result = $this->visitorModel->getVisitorById($_GET['edit']);
            if ($result && $result->num_rows > 0) {
                $visitor = $result->fetch_assoc();
                $edit_mode = true;
            }
        }
        
        $this->view('visitors/index', [
            'visitors' => $visitors,
            'edit_mode' => $edit_mode,
            'visitor' => $visitor
        ]);
    }
    
    public function process() {
        $action = $_POST['action'];
        
        if ($action == 'add') {
            $this->visitorModel->createVisitor($_POST);
        } elseif ($action == 'edit') {
            $this->visitorModel->updateVisitor($_POST['visitor_id'], $_POST);
        } elseif ($action == 'delete') {
            $this->visitorModel->deleteVisitor($_POST['visitor_id']);
        }
        
        $this->redirect('/visitors');
    }
} 