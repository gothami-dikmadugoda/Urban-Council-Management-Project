<?php
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Announcement.php';
require_once __DIR__ . '/../config/database.php';

class LayoutController {
    private $db;
    private $settings;
    private $announcement;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->settings = new Settings($this->db);
        $this->announcement = new Announcement($this->db);
    }

    public function getSiteSettings() {
        return $this->settings->getSettings();
    }

    public function isMaintenanceMode() {
        return $this->settings->isMaintenanceMode();
    }

    public function renderHeader() {
        $siteSettings = $this->getSiteSettings();
        require_once __DIR__ . '/../views/header.php';
    }

    public function renderFooter() {
        $siteSettings = $this->getSiteSettings();
        require_once __DIR__ . '/../views/footer.php';
    }

    public function getAnnouncements($category = null) {
        try {
            $announcements = $this->announcement->getActiveAnnouncements($category);
            return $announcements;
        } catch (Exception $e) {
            error_log('Error fetching announcements: ' . $e->getMessage());
            return [];
        }
    }
} 