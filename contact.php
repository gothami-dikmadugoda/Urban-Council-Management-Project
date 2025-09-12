<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'controllers/LayoutController.php';

$layoutController = new LayoutController();
$siteSettings = $layoutController->getSiteSettings();
$isMaintenanceMode = $layoutController->isMaintenanceMode();

if ($isMaintenanceMode && !isset($_SESSION['admin'])) {
    header('Location: maintenance.php');
    exit();
}

// Render header
$layoutController->renderHeader();
?>

<!-- Contact Hero Section -->
<div class="departments-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4">Contact Us / අප හා සම්බන්ධ වන්න</h1>
                <p class="lead">We're here to help! Reach out to us with your questions or feedback.</p>
            </div>
        </div>
    </div>
</div>

<!-- Contact Info & Form -->
<div class="container py-5">
    <div class="row g-5 align-items-start">
        <div class="col-md-5">
            <div class="contact-info-modern mb-4">
                <h3 class="mb-4" style="color:#ab47bc;">Contact Details</h3>
                <div class="mb-3"><i class='bx bxs-envelope'></i> <a href="mailto:<?php echo $siteSettings['contact_email']; ?>"><?php echo $siteSettings['contact_email']; ?></a></div>
                <div class="mb-3"><i class='bx bxs-phone'></i> <a href="tel:<?php echo $siteSettings['contact_phone']; ?>"><?php echo $siteSettings['contact_phone']; ?></a></div>
                <div class="mb-3"><i class='bx bxs-map'></i> <?php echo $siteSettings['address'] ?? 'Urban Council Office, City, Country'; ?></div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="contact-form-modern p-4">
                <h3 class="mb-4" style="color:#536dfe;">Send Us a Message</h3>
                <form method="post" action="#" autocomplete="off">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required autocomplete="name">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.contact-info-modern {
    background: linear-gradient(135deg, #1a237e 60%, #004225 100%);
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(26,35,126,0.18);
    padding: 2.2rem 2rem 2rem 2rem;
    border: 2px solid #ab47bc;
    font-size: 1.08rem;
}
.contact-info-modern i {
    color: #ab47bc;
    margin-right: 10px;
    font-size: 1.2rem;
}
.contact-info-modern a {
    color: #fff;
    text-decoration: underline;
    transition: color 0.2s;
}
.contact-info-modern a:hover {
    color: #536dfe;
}
.contact-form-modern {
    background: linear-gradient(135deg, #232b3e 60%, #1a2330 100%);
    color: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(26,35,126,0.18);
    border: 2px solid #536dfe;
}
.contact-form-modern label {
    color: #ab47bc;
    font-weight: 600;
}
.contact-form-modern .form-control {
    background: #232b3e;
    color: #fff;
    border: 1.5px solid #6a1b9a;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.contact-form-modern .form-control:focus {
    border-color: #536dfe;
    box-shadow: 0 0 0 2px rgba(83,109,254,0.15);
    background: #232b3e;
    color: #fff;
}
.contact-form-modern .btn-primary {
    background: linear-gradient(90deg, #536dfe, #ab47bc);
    border: none;
    border-radius: 30px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #fff;
    box-shadow: 0 4px 16px rgba(83,109,254,0.18);
    transition: all 0.3s cubic-bezier(.4,2,.3,1);
}
.contact-form-modern .btn-primary:hover {
    background: linear-gradient(90deg, #ab47bc, #536dfe);
    color: #fff;
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 8px 32px rgba(171,71,188,0.22);
    filter: brightness(1.08);
}
@media (max-width: 768px) {
    .contact-info-modern, .contact-form-modern {
        padding: 1.2rem 0.7rem 1.2rem 0.7rem;
    }
}
</style>

<?php
$layoutController->renderFooter();
?>
