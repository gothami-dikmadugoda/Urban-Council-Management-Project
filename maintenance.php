<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Settings.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);
$siteSettings = $settings->getSettings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - <?php echo $siteSettings['site_name']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4361ee, #3f37c9);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .maintenance-content {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        .maintenance-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .contact-info {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="maintenance-content">
        <div class="maintenance-icon">🔧</div>
        <h1>System Maintenance</h1>
        <p>We're currently performing scheduled maintenance to improve our services.</p>
        <p>Please check back later.</p>
        
        <div class="contact-info">
            <p>For urgent inquiries, please contact us:</p>
            <p>Email: <?php echo $siteSettings['contact_email']; ?></p>
            <p>Phone: <?php echo $siteSettings['contact_phone']; ?></p>
        </div>
    </div>
</body>
</html> 