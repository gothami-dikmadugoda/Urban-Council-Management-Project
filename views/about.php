<?php
session_start();
require_once '../config/database.php';
require_once '../models/Settings.php';
require_once '../controllers/LayoutController.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);
$siteSettings = $settings->getSettings();

$layoutController = new LayoutController();
$siteSettings = $layoutController->getSiteSettings();
$isMaintenanceMode = $layoutController->isMaintenanceMode();

if ($isMaintenanceMode && !isset($_SESSION['admin'])) {
    header('Location: ../maintenance.php');
    exit();
}

// Render header
$layoutController->renderHeader();

// Get the base URL
$baseUrl = '/urban2';
?>

<!-- About Hero Section -->
<div class="about-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4">About Us / අප ගැන</h1>
                <p class="lead">Building a Better Community Together / එකට වැඩ කරමු හොඳ ප්‍රජාවක් සඳහා</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container py-5">
    <!-- Welcome Section -->
    <section class="welcome-section mb-5">
        <div class="content-card">
            <h2>Welcome to Matara Urban Council</h2>
            <p class="lead">At the heart of every thriving community is a dedicated local government that listens, serves, and acts. The <em>Kandy Urban Council</em> is committed to enhancing the quality of life for our residents through efficient public service delivery, sustainable urban development, and active community engagement.</p>
        </div>
    </section>

    <!-- Who We Are Section -->
    <section class="who-we-are-section mb-5">
        <div class="content-card">
            <h2>Who We Are</h2>
            <p>The Matara Urban Council is the official local authority responsible for municipal administration within the matara area. Guided by principles of transparency, accountability, and inclusivity, we work tirelessly to ensure our city is clean, safe, and well-managed for all citizens and future generations.</p>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="vision-section mb-5">
        <div class="content-card text-center">
            <h2>Our Vision</h2>
            <blockquote class="vision-quote">
                <p>"To create a smart, sustainable, and people-centered urban environment that inspires growth, equity, and quality living for all."</p>
            </blockquote>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section mb-5">
        <div class="content-card">
            <h2>Our Mission</h2>
            <ul class="mission-list">
                <li>To deliver efficient and citizen-friendly municipal services</li>
                <li>To promote sustainable urban planning and infrastructure development</li>
                <li>To ensure environmental cleanliness and proper waste management</li>
                <li>To foster community well-being through participatory governance</li>
                <li>To leverage digital technology for smarter, more accessible services</li>
            </ul>
        </div>
    </section>

    <!-- What We Do Section -->
    <section class="services-section mb-5">
        <div class="content-card">
            <h2>What We Do</h2>
            <p>We are responsible for a wide range of civic services including:</p>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-trash-alt'></i>
                        <h4>Waste Management & Sanitation</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-heart'></i>
                        <h4>Public Health & Safety</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-building-house'></i>
                        <h4>Urban Infrastructure</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-tree'></i>
                        <h4>Environmental Conservation</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-message-rounded-dots'></i>
                        <h4>Complaint Handling</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-group'></i>
                        <h4>Community Development</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-calendar-event'></i>
                        <h4>Event Management</h4>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="service-item">
                        <i class='bx bxs-chat'></i>
                        <h4>Citizen Engagement</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Smart Governance Section -->
    <section class="smart-governance-section mb-5">
        <div class="content-card">
            <h2>Smart Governance</h2>
            <p>In our move towards digital transformation, the Urban Council is implementing a <em>Smart Urban Council Management System</em>. This online platform enables citizens to:</p>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class='bx bxs-check-circle'></i>
                        <span>Submit and track complaints</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class='bx bxs-check-circle'></i>
                        <span>View garbage collection schedules</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class='bx bxs-check-circle'></i>
                        <span>Interact with council staff</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="feature-item">
                        <i class='bx bxs-check-circle'></i>
                        <span>Access council services online</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Us Section -->
    <section class="join-us-section">
        <div class="content-card text-center">
            <h2>Join Us in Building a Better Community</h2>
            <p class="lead mb-4">We believe in collaborative growth. Whether you're a resident, business owner, student, or visitor, your voice matters. Get involved, stay informed, and help shape the future of Matara.</p>
            <a href="<?php echo $baseUrl; ?>/register.php" class="btn btn-primary btn-lg">Join Our Community</a>
        </div>
    </section>
</div>

<style>
:root {
    --primary-blue: #1a237e;
    --primary-green: #004225;
    --primary-purple: #6a1b9a;
    --accent-blue: #536dfe;
    --accent-green: #43a047;
    --accent-purple: #ab47bc;
    --card-bg: linear-gradient(135deg, #232b3e 60%, #1a2330 100%);
    --card-border: #6a1b9a;
    --card-shadow: 0 8px 32px rgba(26,35,126,0.18);
    --text-white: #fff;
    --text-muted: #b0b8c1;
}

.about-hero {
    background: linear-gradient(120deg, #1a237e 0%, #004225 100%);
    color: var(--text-white);
    padding: 100px 0;
    margin-bottom: 50px;
    position: relative;
    box-shadow: 0 8px 32px rgba(26,35,126,0.18);
}
.about-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(60deg, rgba(106,27,154,0.18) 0%, rgba(67,160,71,0.12) 100%);
    pointer-events: none;
}
.about-hero h1 {
    font-weight: 800;
    margin-bottom: 20px;
    color: var(--text-white);
    text-shadow: 2px 2px 8px rgba(26,35,126,0.18);
}

.content-card {
    background: var(--card-bg);
    padding: 40px;
    border-radius: 18px;
    box-shadow: var(--card-shadow);
    margin-bottom: 30px;
    color: var(--text-white);
    border: 2px solid var(--primary-purple);
    position: relative;
}
.content-card h2 {
    color: var(--accent-blue);
    font-size: 2.1rem;
    font-weight: 800;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 15px;
    letter-spacing: 0.5px;
}
.content-card h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 80px;
    height: 4px;
    background: var(--accent-purple);
    border-radius: 2px;
}
.vision-quote {
    font-size: 1.5rem;
    font-style: italic;
    color: var(--accent-purple);
    border-left: 5px solid var(--accent-purple);
    padding: 20px;
    background: rgba(171,71,188,0.08);
    border-radius: 10px;
}
.mission-list {
    list-style: none;
    padding: 0;
    color: var(--text-white);
}
.mission-list li {
    padding: 15px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    font-size: 1.08rem;
}
.mission-list li::before {
    content: '\e876';
    font-family: 'boxicons';
    margin-right: 15px;
    color: var(--accent-green);
    font-size: 1.2rem;
}
.service-item {
    background: var(--primary-blue);
    padding: 25px;
    border-radius: 14px;
    text-align: center;
    transition: all 0.3s cubic-bezier(.4,2,.3,1);
    height: 100%;
    box-shadow: 0 5px 18px rgba(26,35,126,0.18);
    color: var(--text-white);
    border: 2px solid var(--accent-purple);
    margin-bottom: 18px;
}
.service-item:hover {
    transform: translateY(-7px) scale(1.03);
    box-shadow: 0 12px 32px rgba(83,109,254,0.22);
    border-color: var(--accent-blue);
}
.service-item i {
    font-size: 2.5rem;
    color: var(--accent-purple);
    margin-bottom: 15px;
}
.service-item h4 {
    font-size: 1.1rem;
    color: var(--accent-blue);
    margin: 0;
    font-weight: 700;
}
.feature-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: rgba(83,109,254,0.08);
    border-radius: 10px;
    margin-bottom: 15px;
    color: var(--text-white);
    border-left: 4px solid var(--accent-purple);
}
.feature-item i {
    font-size: 1.5rem;
    color: var(--accent-green);
    margin-right: 15px;
}
.feature-item span {
    color: var(--text-white);
    font-weight: 500;
}
.btn-primary {
    background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
    border: none;
    padding: 15px 36px;
    border-radius: 30px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #fff;
    box-shadow: 0 4px 16px rgba(83,109,254,0.18);
    transition: all 0.3s cubic-bezier(.4,2,.3,1);
}
.btn-primary:hover {
    background: linear-gradient(90deg, var(--accent-purple), var(--accent-blue));
    color: #fff;
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 8px 32px rgba(171,71,188,0.22);
    filter: brightness(1.08);
}
@media (max-width: 768px) {
    .about-hero {
        padding: 60px 0;
    }
    .content-card {
        padding: 25px;
    }
    .vision-quote {
        font-size: 1.2rem;
    }
    .service-item {
        margin-bottom: 20px;
    }
}
</style>

<?php
// Include the footer
require_once 'footer.php';
?> 