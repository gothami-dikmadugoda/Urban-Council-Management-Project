<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Receptionist Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/styles.css" rel="stylesheet">
    <!-- Accessibility CSS -->
    <link href="css/accessibility.css" rel="stylesheet">
</head>
<body>
    <!-- Skip to content link for keyboard users -->
    <a href="#main-content" class="skip-to-content">Skip to main content</a>
    
    <!-- Main navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-building" aria-hidden="true"></i>
                <span class="ms-2">Reception Dashboard</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link<?php echo nav_active_class('index.php'); ?>" href="index.php"<?php echo nav_aria_current('index.php'); ?>>
                            <i class="bi bi-speedometer2 me-1" aria-hidden="true"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo nav_active_class('visitors.php'); ?>" href="visitors.php"<?php echo nav_aria_current('visitors.php'); ?>>
                            <i class="bi bi-people me-1" aria-hidden="true"></i> Visitors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo nav_active_class('appointments.php'); ?>" href="appointments.php"<?php echo nav_aria_current('appointments.php'); ?>>
                            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i> Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo nav_active_class('status.php'); ?>" href="status.php"<?php echo nav_aria_current('status.php'); ?>>
                            <i class="bi bi-reception-4 me-1" aria-hidden="true"></i> Status
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php echo nav_active_class('reports.php'); ?>" href="reports.php"<?php echo nav_aria_current('reports.php'); ?>>
                            <i class="bi bi-file-earmark-bar-graph me-1" aria-hidden="true"></i> Reports
                        </a>
                    </li>
                </ul>
                
                <!-- Settings & Accessibility controls -->
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-light btn-sm me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#accessibilityOffcanvas" aria-controls="accessibilityOffcanvas">
                        <i class="bi bi-person-gear me-1" aria-hidden="true"></i> Accessibility
                    </button>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Accessibility Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="accessibilityOffcanvas" aria-labelledby="accessibilityOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="accessibilityOffcanvasLabel">Accessibility Settings</h5>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="mb-3">
                <label for="quickFontSize" class="form-label">Font Size</label>
                <select class="form-select" id="quickFontSize" aria-label="Adjust font size">
                    <option value="normal">Normal</option>
                    <option value="large">Large</option>
                    <option value="x-large">Extra Large</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="quickContrastMode" class="form-label">Contrast Mode</label>
                <select class="form-select" id="quickContrastMode" aria-label="Adjust contrast mode">
                    <option value="normal">Normal</option>
                    <option value="high-contrast">High Contrast</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="quickAnimationSetting" class="form-label">Animations</label>
                <select class="form-select" id="quickAnimationSetting" aria-label="Adjust animation settings">
                    <option value="enabled">Enabled</option>
                    <option value="reduced">Reduced</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="screenReaderOptimized" aria-label="Optimize for screen readers">
                <label class="form-check-label" for="screenReaderOptimized">Screen Reader Optimized</label>
            </div>
            
            <button type="button" id="saveQuickAccessibilitySettings" class="btn btn-primary">
                Save Settings
            </button>
            
            <hr>
            
            <h6>Keyboard Shortcuts</h6>
            <ul class="list-unstyled">
                <li><kbd>Alt</kbd> + <kbd>1</kbd> to <kbd>7</kbd>: Navigate to main menu items</li>
                <li><kbd>Alt</kbd> + <kbd>S</kbd>: Search</li>
                <li><kbd>Alt</kbd> + <kbd>H</kbd>: Home</li>
                <li><kbd>Alt</kbd> + <kbd>A</kbd>: Accessibility menu</li>
            </ul>
        </div>
    </div>
    
    <!-- Main content -->
    <div class="container mt-4">
        <!-- Flash messages -->
        <?php display_flash_message(); ?>
        
        <!-- Screen reader announcement area -->
        <div aria-live="polite" id="sr-announcements" class="sr-only"></div>
        
        <!-- Page content starts here - main content will be included separately -->
