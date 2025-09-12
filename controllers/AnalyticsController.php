<?php
require_once __DIR__ . '/../config/database.php';

class AnalyticsController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getDashboardData() {
        return [
            'historical' => $this->getHistoricalData(),
            'categories' => $this->getCategoryDistribution(),
            'status' => $this->getStatusDistribution()
        ];
    }

    public function getReportsData() {
        $data = [
            'total_complaints' => $this->getTotalComplaints(),
            'resolved_complaints' => $this->getResolvedComplaints(),
            'pending_complaints' => $this->getPendingComplaints(),
            'avg_resolution_time' => $this->getAverageResolutionTime(),
            'complaint_trends' => $this->getComplaintTrends(),
            'department_performance' => $this->getDepartmentPerformance(),
            'detailed_data' => $this->getDetailedReportData()
        ];
        return $data;
    }

    public function getFilteredReports($reportType, $dateRange, $department) {
        $dateFilter = $this->getDateFilter($dateRange);
        $departmentFilter = $department ? "AND d.name = :department" : "";
        
        switch ($reportType) {
            case 'complaints':
                return $this->getFilteredComplaintsReport($dateFilter, $departmentFilter, $department);
            case 'staff':
                return $this->getFilteredStaffReport($dateFilter, $departmentFilter, $department);
            case 'revenue':
                return $this->getFilteredRevenueReport($dateFilter, $departmentFilter, $department);
            case 'maintenance':
                return $this->getFilteredMaintenanceReport($dateFilter, $departmentFilter, $department);
            default:
                return $this->getFilteredComplaintsReport($dateFilter, $departmentFilter, $department);
        }
    }

    private function getDateFilter($dateRange) {
        switch ($dateRange) {
            case 'today':
                return "DATE(c.created_at) = CURDATE()";
            case 'week':
                return "c.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            case 'month':
                return "c.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'year':
                return "c.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
            default:
                return "c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }

    private function getFilteredComplaintsReport($dateFilter, $departmentFilter, $department) {
        // Get summary data first
        $summarySQL = "SELECT 
                    COUNT(c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    AVG(DATEDIFF(c.resolved_date, c.created_at)) as avg_resolution_time
                FROM complaints c
                JOIN departments d ON c.department_id = d.id
                WHERE $dateFilter $departmentFilter";

        $stmt = $this->db->prepare($summarySQL);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get category-wise breakdown
        $categorySQL = "SELECT 
                    c.category,
                    COUNT(c.id) as total,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM complaints c
                JOIN departments d ON c.department_id = d.id
                WHERE $dateFilter $departmentFilter
                GROUP BY c.category
                ORDER BY c.category";

        $stmt = $this->db->prepare($categorySQL);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pending = $row['total'] - $row['resolved'];
            $resolution_rate = $row['total'] > 0 
                ? round(($row['resolved'] / $row['total']) * 100, 1) 
                : 0;
            
            $categories[] = [
                'name' => $row['category'],
                'total' => $row['total'],
                'resolved' => $row['resolved'],
                'pending' => $pending,
                'resolution_rate' => $resolution_rate
            ];
        }

        // Return combined data
        return [
            'total_complaints' => $summary['total_complaints'],
            'resolved_complaints' => $summary['resolved'],
            'pending_complaints' => $summary['pending'],
            'avg_resolution_time' => round($summary['avg_resolution_time'] ?? 0),
            'categories' => $categories,
            'complaint_trends' => $this->getComplaintTrendsForPeriod($dateFilter, $departmentFilter, $department),
            'department_performance' => $this->getDepartmentPerformanceForPeriod($dateFilter, $departmentFilter, $department)
        ];
    }

    private function getFilteredStaffReport($dateFilter, $departmentFilter, $department) {
        // Replace the table alias in date filter
        $dateFilter = str_replace('c.', 'c.', $dateFilter); // keep c. for complaints table
        
        $sql = "SELECT 
                    DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                    u.department as department,
                    COUNT(DISTINCT u.id) as total_staff,
                    COUNT(DISTINCT c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    AVG(CASE WHEN c.status = 'resolved' THEN DATEDIFF(c.resolved_date, c.created_at) ELSE NULL END) as avg_resolution_time
                FROM users u
                LEFT JOIN complaints c ON c.assigned_to = u.id AND $dateFilter
                WHERE u.role = 'staff' " . str_replace('d.name', 'u.department', $departmentFilter) . "
                GROUP BY COALESCE(DATE_FORMAT(c.created_at, '%Y-%m-%d'), DATE_FORMAT(NOW(), '%Y-%m-%d')), u.department
                ORDER BY date DESC, department";

        $stmt = $this->db->prepare($sql);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resolution_rate = $row['total_complaints'] > 0 
                ? round(($row['resolved'] / $row['total_complaints']) * 100) 
                : 0;
            
            $data[] = [
                'date' => $row['date'],
                'department' => $row['department'],
                'total_staff' => $row['total_staff'],
                'total_complaints' => $row['total_complaints'] ?? 0,
                'resolved' => $row['resolved'] ?? 0,
                'pending' => $row['pending'] ?? 0,
                'resolution_rate' => $resolution_rate,
                'avg_resolution_time' => round($row['avg_resolution_time'] ?? 0)
            ];
        }

        // Get complaint trends data
        $trends = $this->getStaffComplaintTrends($dateFilter, $departmentFilter, $department);
        
        // Get department performance data
        $deptPerformance = $this->getStaffDepartmentPerformance($dateFilter, $departmentFilter, $department);

        // Calculate summary statistics
        $summary = [
            'total_staff' => $data ? max(array_column($data, 'total_staff')) : 0,
            'total_complaints' => array_sum(array_column($data, 'total_complaints')),
            'resolved_complaints' => array_sum(array_column($data, 'resolved')),
            'pending_complaints' => array_sum(array_column($data, 'pending')),
            'avg_resolution_time' => $data ? round(array_sum(array_column($data, 'avg_resolution_time')) / count($data)) : 0,
            'complaint_trends' => $trends,
            'department_performance' => $deptPerformance,
            'detailed_data' => $data
        ];

        return $summary;
    }

    private function getStaffComplaintTrends($dateFilter, $departmentFilter, $department) {
        $sql = "SELECT 
                    DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                    COUNT(DISTINCT c.id) as total,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM users u
                LEFT JOIN complaints c ON c.assigned_to = u.id AND $dateFilter
                WHERE u.role = 'staff' " . str_replace('d.name', 'u.department', $departmentFilter) . "
                GROUP BY DATE_FORMAT(c.created_at, '%Y-%m-%d')
                ORDER BY date ASC";

        $stmt = $this->db->prepare($sql);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getStaffDepartmentPerformance($dateFilter, $departmentFilter, $department) {
        $sql = "SELECT 
                    u.department as department,
                    COUNT(DISTINCT u.id) as total_staff,
                    COUNT(DISTINCT c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints,
                    AVG(CASE WHEN c.status = 'resolved' THEN DATEDIFF(c.resolved_date, c.created_at) ELSE NULL END) as avg_resolution_time
                FROM users u
                LEFT JOIN complaints c ON c.assigned_to = u.id AND $dateFilter
                WHERE u.role = 'staff' " . str_replace('d.name', 'u.department', $departmentFilter) . "
                GROUP BY u.department
                ORDER BY u.department";

        $stmt = $this->db->prepare($sql);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        
        $performance = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resolution_rate = $row['total_complaints'] > 0 
                ? round(($row['resolved_complaints'] / $row['total_complaints']) * 100) 
                : 0;
            
            $performance[] = [
                'department' => $row['department'],
                'total_staff' => $row['total_staff'],
                'total_complaints' => $row['total_complaints'],
                'resolution_rate' => $resolution_rate,
                'avg_resolution_time' => round($row['avg_resolution_time'] ?? 0)
            ];
        }
        return $performance;
    }

    private function getComplaintTrendsForPeriod($dateFilter, $departmentFilter, $department) {
        $sql = "SELECT 
                    DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM complaints c
                JOIN departments d ON c.department_id = d.id
                WHERE $dateFilter $departmentFilter
                GROUP BY DATE_FORMAT(c.created_at, '%Y-%m-%d')
                ORDER BY date";

        $stmt = $this->db->prepare($sql);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDepartmentPerformanceForPeriod($dateFilter, $departmentFilter, $department) {
        $sql = "SELECT 
                    d.name as department,
                    COUNT(c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints
                FROM departments d
                LEFT JOIN complaints c ON c.department_id = d.id AND $dateFilter
                WHERE 1=1 $departmentFilter
                GROUP BY d.id, d.name
                ORDER BY d.name";

        $stmt = $this->db->prepare($sql);
        if ($department) {
            $stmt->bindParam(':department', $department);
        }
        $stmt->execute();
        
        $performance = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resolution_rate = $row['total_complaints'] > 0 
                ? round(($row['resolved_complaints'] / $row['total_complaints']) * 100) 
                : 0;
            $performance[] = [
                'department' => $row['department'],
                'resolution_rate' => $resolution_rate
            ];
        }
        return $performance;
    }

    private function getTotalComplaints() {
        $sql = "SELECT COUNT(*) as total FROM complaints";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    private function getResolvedComplaints() {
        $sql = "SELECT COUNT(*) as total FROM complaints WHERE status = 'resolved'";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    private function getPendingComplaints() {
        $sql = "SELECT COUNT(*) as total FROM complaints WHERE status = 'pending'";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    private function getAverageResolutionTime() {
        $sql = "SELECT AVG(DATEDIFF(resolved_date, created_at)) as avg_time 
                FROM complaints 
                WHERE status = 'resolved'";
        $stmt = $this->db->query($sql);
        return round($stmt->fetch(PDO::FETCH_ASSOC)['avg_time'] ?? 0);
    }

    private function getComplaintTrends() {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') as date,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
                FROM complaints
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                ORDER BY date";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getDepartmentPerformance() {
        $sql = "SELECT 
                    d.name as department,
                    COUNT(c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints
                FROM departments d
                LEFT JOIN complaints c ON c.department_id = d.id
                GROUP BY d.id, d.name
                ORDER BY d.name";
        $stmt = $this->db->query($sql);
        $performance = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resolution_rate = $row['total_complaints'] > 0 
                ? round(($row['resolved_complaints'] / $row['total_complaints']) * 100) 
                : 0;
            $performance[] = [
                'department' => $row['department'],
                'resolution_rate' => $resolution_rate
            ];
        }
        return $performance;
    }

    private function getDetailedReportData() {
        $sql = "SELECT 
                    DATE_FORMAT(c.created_at, '%Y-%m-%d') as date,
                    d.name as department,
                    COUNT(c.id) as total_complaints,
                    SUM(CASE WHEN c.status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN c.status = 'pending' THEN 1 ELSE 0 END) as pending,
                    AVG(DATEDIFF(c.resolved_date, c.created_at)) as avg_resolution_time
                FROM complaints c
                JOIN departments d ON c.department_id = d.id
                WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE_FORMAT(c.created_at, '%Y-%m-%d'), d.id, d.name
                ORDER BY date DESC, department";
        $stmt = $this->db->query($sql);
        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resolution_rate = $row['total_complaints'] > 0 
                ? round(($row['resolved'] / $row['total_complaints']) * 100) 
                : 0;
            $data[] = [
                'date' => $row['date'],
                'department' => $row['department'],
                'total_complaints' => $row['total_complaints'],
                'resolved' => $row['resolved'],
                'pending' => $row['pending'],
                'resolution_rate' => $resolution_rate,
                'avg_resolution_time' => round($row['avg_resolution_time'] ?? 0)
            ];
        }
        return $data;
    }

    public function getHistoricalData($period = 'weekly') {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') as time_period,
                    COUNT(*) as total_complaints,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_complaints,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_complaints,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_complaints,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_complaints,
                    AVG(DATEDIFF(COALESCE(resolved_date, NOW()), created_at)) as avg_resolution_time
                FROM complaints 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 ";

        switch ($period) {
            case 'daily':
                $sql .= "DAY) GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H') ORDER BY time_period DESC LIMIT 24";
                break;
            case 'weekly':
                $sql .= "WEEK) GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d') ORDER BY time_period DESC LIMIT 7";
                break;
            case 'monthly':
                $sql .= "MONTH) GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d') ORDER BY time_period DESC LIMIT 30";
                break;
            default:
                $sql .= "WEEK) GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d') ORDER BY time_period DESC LIMIT 7";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($row) {
            return [
                'time_period' => $row['time_period'],
                'total_complaints' => (int)$row['total_complaints'],
                'resolved_complaints' => (int)$row['resolved_complaints'],
                'pending_complaints' => (int)$row['pending_complaints'],
                'in_progress_complaints' => (int)$row['in_progress_complaints'],
                'closed_complaints' => (int)$row['closed_complaints'],
                'avg_resolution_time' => round($row['avg_resolution_time'] ?? 0),
                'resolution_rate' => $row['total_complaints'] > 0 
                    ? round(($row['resolved_complaints'] / $row['total_complaints']) * 100) 
                    : 0
            ];
        }, $data);
    }

    private function calculatePrediction($data) {
        if (count($data) < 2) {
            return 0;
        }

        // Simple moving average for prediction
        $total = 0;
        $count = 0;
        foreach ($data as $item) {
            $total += $item['total_complaints'];
            $count++;
        }
        return round($total / $count);
    }

    private function getNextPeriod($lastPeriod, $period) {
        $date = new DateTime($lastPeriod);
        switch ($period) {
            case 'daily':
                $date->modify('+1 hour');
                return $date->format('Y-m-d H:00');
            case 'weekly':
                $date->modify('+1 day');
                return $date->format('Y-m-d');
            case 'monthly':
                $date->modify('+1 month');
                return $date->format('Y-m');
            default:
                $date->modify('+1 day');
                return $date->format('Y-m-d');
        }
    }

    private function getCategoryDistribution() {
        $sql = "SELECT 
                    category,
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
                    AVG(DATEDIFF(COALESCE(resolved_date, NOW()), created_at)) as avg_resolution_time
                FROM complaints
                GROUP BY category
                ORDER BY total DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getStatusDistribution() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    AVG(DATEDIFF(COALESCE(resolved_date, NOW()), created_at)) as avg_time_in_status
                FROM complaints
                GROUP BY status";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function handleAjaxRequest() {
        if (!isset($_GET['action'])) {
            return json_encode(['error' => 'No action specified']);
        }

        switch ($_GET['action']) {
            case 'getHistoricalData':
                $period = $_GET['period'] ?? 'weekly';
                return json_encode($this->getHistoricalData($period));
            case 'getCategoryDistribution':
                return json_encode($this->getCategoryDistribution());
            case 'getStatusDistribution':
                return json_encode($this->getStatusDistribution());
            default:
                return json_encode(['error' => 'Invalid action']);
        }
    }
}

if (isset($_GET['action'])) {
    $controller = new AnalyticsController();
    echo $controller->handleAjaxRequest();
    exit;
} 