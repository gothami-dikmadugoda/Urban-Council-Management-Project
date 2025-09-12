<?php
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

<!-- Services Hero Section -->
<div class="departments-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4">Our Services / අපගේ සේවා</h1>
                <p class="lead">Discover the range of services we provide to our community</p>
            </div>
        </div>
    </div>
</div>

<!-- Services Content -->
<div class="container py-5">
    <div class="row g-4">
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">🗑️</div>
                <h3>Garbage Collection<br><span class="service-si">කසළ එකතු කිරීම</span></h3>
                <p>Daily waste collection services to ensure clean surroundings.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">♻️</div>
                <h3>Recycling<br><span class="service-si">ප්‍රතිචක්‍රීකරණය</span></h3>
                <p>Sustainable waste management through eco-friendly recycling.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">🚛</div>
                <h3>Special Collection<br><span class="service-si">විශේෂ එකතු කිරීම</span></h3>
                <p>Request special pickups for bulky or hazardous items.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">📝</div>
                <h3>Complaint Management<br><span class="service-si">පැමිණිලි කළමනාකරණය</span></h3>
                <p>Easily report and track municipal service complaints online.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">🍱</div>
                <h3>Dansal Registration<br><span class="service-si">දන්සල් ලියාපදිංචි</span></h3>
                <p>Register food donation events during Vesak and Poson festivals.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">🏠</div>
                <h3>Property Services<br><span class="service-si">දේපළ සේවා</span></h3>
                <p>Manage land tax payments, assessments, and transfers.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">📅</div>
                <h3>Event Approvals<br><span class="service-si">සිදුවීම් අනුමත කිරීම</span></h3>
                <p>Apply for public area or event venue approvals.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">💧</div>
                <h3>Water & Drainage<br><span class="service-si">ජල හා නාය නාලා සේවා</span></h3>
                <p>Request water supply connections and drainage support.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-4">
            <div class="service-card-modern text-center">
                <div class="service-icon-modern">📢</div>
                <h3>Public Notices<br><span class="service-si">සභා නිවේදන</span></h3>
                <p>Stay informed with official council news and announcements.</p>
            </div>
        </div>
    </div>
</div>

<style>
.service-card-modern {
    background: linear-gradient(135deg, #1a237e 60%, #004225 100%);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(26,35,126,0.18);
    border: 2px solid #6a1b9a;
    color: #fff;
    padding: 2.5rem 1.5rem 2rem 1.5rem;
    margin-bottom: 18px;
    transition: all 0.3s cubic-bezier(.4,2,.3,1);
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 320px;
    position: relative;
}
.service-card-modern:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 16px 48px rgba(106,27,154,0.22);
    border-color: #ab47bc;
}
.service-icon-modern {
    font-size: 2.8rem;
    margin-bottom: 1.2rem;
    color: #ab47bc;
    filter: drop-shadow(0 2px 8px rgba(171,71,188,0.12));
}
.service-card-modern h3 {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.7rem;
    letter-spacing: 0.5px;
}
.service-si {
    color: #ab47bc;
    font-size: 1rem;
    font-weight: 600;
}
.service-card-modern p {
    color: #b0b8c1;
    font-size: 1.05rem;
    margin-bottom: 0;
    margin-top: 0.5rem;
}
@media (max-width: 768px) {
    .service-card-modern {
        padding: 1.2rem 0.7rem 1.2rem 0.7rem;
        min-height: 220px;
    }
    .service-icon-modern {
        font-size: 2rem;
    }
    .service-card-modern h3 {
        font-size: 1.05rem;
    }
    .service-si {
        font-size: 0.9rem;
    }
    .service-card-modern p {
        font-size: 0.95rem;
    }
}
</style>

<?php
$layoutController->renderFooter();
?> 