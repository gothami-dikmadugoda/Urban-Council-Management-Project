<?php

require_once __DIR__ . '/Controller.php';

class ReportController extends Controller {
    private $reportModel;
    
    public function __construct() {
        $this->reportModel = $this->model('ReportModel');
    }
    
    public function index() {
        $appointmentStats = $this->reportModel->getAppointmentStats()->fetch_assoc();
        $visitorStats = $this->reportModel->getVisitorStats()->fetch_assoc();
        
        $date = $_GET['date'] ?? date('Y-m-d');
        $dailyVisitors = $this->reportModel->getDailyVisitors($date);
        
        $year = $_GET['year'] ?? date('Y');
        $month = $_GET['month'] ?? date('m');
        $monthlyVisitors = $this->reportModel->getMonthlyVisitors($year, $month);
        
        $this->view('reports/index', [
            'appointmentStats' => $appointmentStats,
            'visitorStats' => $visitorStats,
            'dailyVisitors' => $dailyVisitors,
            'monthlyVisitors' => $monthlyVisitors,
            'selectedDate' => $date,
            'selectedYear' => $year,
            'selectedMonth' => $month
        ]);
    }
} 