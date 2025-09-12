<?php
// Make sure this is at the very top, before any output
// Start session if not already started
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Format date to readable format
 */
function format_date($date_string) {
    $date = new DateTime($date_string);
    return $date->format('M j, Y g:i A');
}

/**
 * Format duration in hours and minutes
 */
function format_duration($hours) {
    $hours_whole = floor($hours);
    $minutes = ($hours - $hours_whole) * 60;
    
    if ($hours_whole > 0 && $minutes > 0) {
        return $hours_whole . ' hour' . ($hours_whole > 1 ? 's' : '') . ' ' . $minutes . ' min';
    } else if ($hours_whole > 0) {
        return $hours_whole . ' hour' . ($hours_whole > 1 ? 's' : '');
    } else {
        return $minutes . ' min';
    }
}

/**
 * Get appointment status badge HTML
 */
function get_status_badge($status) {
    $class = '';
    switch ($status) {
        case 'pending':
            $class = 'bg-warning text-dark';
            break;
        case 'confirmed':
            $class = 'bg-primary';
            break;
        case 'cancelled':
            $class = 'bg-danger';
            break;
        case 'completed':
            $class = 'bg-success';
            break;
        default:
            $class = 'bg-secondary';
    }
    
    return '<span class="badge ' . $class . '">' . ucfirst($status) . '</span>';
}

/**
 * Set a flash message
 */
function set_message($message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Check if there's a flash message
 */
function has_flash_message() {
    return isset($_SESSION['message']) && !empty($_SESSION['message']);
}

/**
 * Display flash message
 */
function display_flash_message() {
    if (has_flash_message()) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Clear the flash message
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Get current page name
 */
function get_current_page() {
    $path = $_SERVER['PHP_SELF'];
    $file = basename($path);
    return $file;
}

/**
 * Check if the current page matches a given page
 */
function is_current_page($page) {
    return get_current_page() === $page;
}

/**
 * Generate proper ARIA attributes for navigation
 */
function nav_aria_current($page) {
    return is_current_page($page) ? ' aria-current="page"' : '';
}

/**
 * Generate active class for navigation
 */
function nav_active_class($page) {
    return is_current_page($page) ? ' active' : '';
}

/**
 * Get count of visitors currently checked in
 */
function get_checked_in_count() {
    global $conn, $use_postgres;
    
    $sql = "SELECT COUNT(*) as count FROM visitors WHERE checkin_time IS NOT NULL AND checkout_time IS NULL";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['count'] : 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0;
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        return 0;
    }
}

/**
 * Get count of today's appointments
 */
function get_todays_appointments_count() {
    global $conn, $use_postgres;
    
    $today = date('Y-m-d');
    $sql = "SELECT COUNT(*) as count FROM appointments WHERE DATE(appointment_date) = '$today'";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['count'] : 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0;
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        return 0;
    }
}

/**
 * Get today's appointments count (alias for status.php)
 */
function get_today_appointments_count($conn) {
    return get_todays_appointments_count();
}

/**
 * Get today's visitors count 
 */
function get_today_visitors_count($conn) {
    global $use_postgres;
    
    $sql = "SELECT COUNT(*) as count FROM visitors WHERE DATE(checkin_time) = CURDATE()";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['count'] : 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0;
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        return 0;
    }
}

/**
 * Get appointment status breakdown
 */
function get_status_breakdown($conn) {
    global $use_postgres;
    
    $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    $breakdown = array_fill_keys($statuses, 0);
    
    $sql = "SELECT status, COUNT(*) as count FROM appointments 
            WHERE DATE(appointment_date) = CURDATE()
            GROUP BY status";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if (in_array($row['status'], $statuses)) {
                    $breakdown[$row['status']] = $row['count'];
                }
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                if (in_array($row['status'], $statuses)) {
                    $breakdown[$row['status']] = $row['count'];
                }
            }
        }
    }
    
    return $breakdown;
}

/**
 * Get count of pending appointments
 */
function get_pending_appointments_count() {
    global $conn, $use_postgres;
    
    $sql = "SELECT COUNT(*) as count FROM appointments WHERE status = 'pending'";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['count'] : 0;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return 0;
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        if ($result && $row = $result->fetch_assoc()) {
            return $row['count'];
        }
        return 0;
    }
}

/**
 * Format a timestamp for the screen reader
 */
function format_time_for_sr($timestamp) {
    if (empty($timestamp)) return 'Not specified';
    
    $date = new DateTime($timestamp);
    return $date->format('l, F j, Y \a\t g:i A');
}

/**
 * Create accessible CSV data
 */
function generate_csv_data($data, $headers) {
    $output = fopen('php://temp', 'r+');
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    rewind($output);
    $csv_data = stream_get_contents($output);
    fclose($output);
    
    return $csv_data;
}

/**
 * Create accessible pagination controls
 */
function create_pagination($current_page, $total_pages, $url_pattern) {
    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination">';
    
    // Previous button
    echo '<li class="page-item' . ($current_page <= 1 ? ' disabled' : '') . '">';
    if ($current_page <= 1) {
        echo '<span class="page-link" aria-disabled="true">Previous</span>';
    } else {
        echo '<a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '" aria-label="Previous page">Previous</a>';
    }
    echo '</li>';
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        echo '<li class="page-item' . ($i == $current_page ? ' active' : '') . '">';
        if ($i == $current_page) {
            echo '<span class="page-link" aria-current="page">' . $i . '</span>';
        } else {
            echo '<a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a>';
        }
        echo '</li>';
    }
    
    // Next button
    echo '<li class="page-item' . ($current_page >= $total_pages ? ' disabled' : '') . '">';
    if ($current_page >= $total_pages) {
        echo '<span class="page-link" aria-disabled="true">Next</span>';
    } else {
        echo '<a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '" aria-label="Next page">Next</a>';
    }
    echo '</li>';
    
    echo '</ul>';
    echo '</nav>';
}

/**
 * Format a phone number for better accessibility
 */
function format_phone_for_accessibility($phone) {
    if (empty($phone)) return '';
    
    // Strip all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Format based on length
    if (strlen($phone) == 10) {
        return '(' . substr($phone, 0, 3) . ') ' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    } else if (strlen($phone) == 11 && substr($phone, 0, 1) == '1') {
        return '+1 (' . substr($phone, 1, 3) . ') ' . substr($phone, 4, 3) . '-' . substr($phone, 7);
    }
    
    // Return as is if it doesn't match expected formats
    return $phone;
}

/**
 * Ensure email is accessible with mailto link
 */
function format_email_link($email) {
    if (empty($email)) return 'Not provided';
    
    return '<a href="mailto:' . $email . '" aria-label="Send email to ' . $email . '">' . $email . '</a>';
}

/**
 * Check if SendGrid is available
 */
function is_sendgrid_available() {
    return defined('SENDGRID_API_KEY') && !empty(SENDGRID_API_KEY) && file_exists('../vendor/autoload.php');
}

/**
 * Logging function
 */
function log_activity($action, $details = '') {
    $log_file = '../logs/activity.log';
    $log_dir = dirname($log_file);
    
    // Ensure log directory exists
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    // Format log entry
    $log_entry = date('Y-m-d H:i:s') . ' - ' . $action . ' - ' . $details . PHP_EOL;
    
    // Write to log file
    error_log($log_entry, 3, $log_file);
}

/**
 * Get the latest visitors
 */
function get_latest_visitors() {
    global $conn, $use_postgres;
    
    $sql = "SELECT * FROM visitors ORDER BY checkin_time DESC LIMIT 5";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    } else {
        // MySQL query
        $result = $conn->query($sql);
        return $result; // Return the mysqli_result directly
    }
}

/**
 * Get upcoming appointments with consistent data structure
 */
function get_upcoming_appointments() {
    global $conn, $use_postgres;
    
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT 
                a.appointment_id,
                a.appointment_date,
                a.purpose,
                a.status,
                v.name as visitor_name,
                v.email as visitor_email
            FROM appointments a
            LEFT JOIN visitors v ON a.visitor_id = v.visitor_id
            WHERE a.appointment_date > ? 
            ORDER BY a.appointment_date ASC 
            LIMIT 5";
    
    if ($use_postgres) {
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$now]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $results = [];
        }
    } else {
        // MySQL query
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $now);
        $stmt->execute();
        $result = $stmt->get_result();
        $results = [];
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }
    
    // Ensure consistent structure
    return array_map(function($appt) {
        return [
            'id' => $appt['id'] ?? null,
            'appointment_date' => $appt['appointment_date'] ?? null,
            'purpose' => $appt['purpose'] ?? 'No purpose specified',
            'status' => $appt['status'] ?? 'pending',
            'visitor_name' => $appt['visitor_name'] ?? 'Unknown Visitor',
            'visitor_email' => $appt['visitor_email'] ?? null
        ];
    }, $results);
}
?>