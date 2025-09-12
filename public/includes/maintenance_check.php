<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Settings.php';

// Skip maintenance check for admin pages
if (strpos($_SERVER['PHP_SELF'], '/views/admin/') === false) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $settings = new Settings($db);
        
        if ($settings->isMaintenanceMode()) {
            // If maintenance mode is enabled and user is not admin
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                header('Location: /urban2/maintenance.php');
                exit;
            }
        }
    } catch (Exception $e) {
        error_log("Error checking maintenance mode: " . $e->getMessage());
    }
} 