<?php
session_start();
require_once 'controllers/LayoutController.php';
require_once 'controllers/CitizenController.php';
require_once 'controllers/CollectionController.php';

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

<!-- Hero Section -->
<div class="hero-section-pro position-relative">
    <img src="assets/images/citybanner.jpg" alt="City Banner" class="hero-bg-img">
    <div class="container hero-content-pro text-center">
        <h1 class="display-3 fw-bold mb-3">Welcome to <?php echo htmlspecialchars($siteSettings['site_name']); ?></h1>
        <p class="lead mb-4"><?php echo htmlspecialchars($siteSettings['site_description']); ?></p>
        <a href="#services" class="btn btn-primary btn-lg hero-cta">View Our Services</a>
    </div>
</div>

<!-- Slider Section -->
<div class="slider-section">
    <div id="mainSlider" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#mainSlider" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="slider-card">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-6">
                            <img src="assets/images/slider/slide1.jpg" alt="Garbage Collection" class="img-fluid w-100">
                        </div>
                        <div class="col-md-6">
                            <div class="slider-content">
                                <h2>Efficient Waste Management</h2>
                                <p>We provide comprehensive waste management solutions for a cleaner environment.</p>
                                <div class="slider-buttons">
                                    <a href="#services" class="btn btn-primary me-3">Learn More</a>
                                    <a href="#services" class="btn btn-outline">Our Services</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="slider-card">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-6">
                            <img src="assets/images/slider/slide2.jpg alt="Recycling" class="img-fluid w-100">
                        </div>
                        <div class="col-md-6">
                            <div class="slider-content">
                                <h2>Recycling Initiatives</h2>
                                <p>Join our recycling programs and help create a sustainable future.</p>
                                <div class="slider-buttons">
                                    <a href="#services" class="btn btn-primary me-3">Join Now</a>
                                    <a href="#services" class="btn btn-outline">Our Services</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="carousel-item">
                <div class="slider-card">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-6">
                            <img src="assets/images/slider/slide3.jpg" alt="Community" class="img-fluid w-100">
                        </div>
                        <div class="col-md-6">
                            <div class="slider-content">
                                <h2>Community Engagement</h2>
                                <p>Working together with the community for a cleaner and healthier environment.</p>
                                <div class="slider-buttons">
                                    <a href="#services" class="btn btn-primary me-3">Contact Us</a>
                                    <a href="#services" class="btn btn-outline">Our Services</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mainSlider" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mainSlider" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</div>

<div class="index-content">
<!-- Calendar Section -->
<div class="calendar-section-pro mb-5">
    <div class="container">
        <div class="row justify-content-between align-items-center">
            <div class="col-md-5">
                <div class="calendar-card-pro">
                    <img src="assets/images/cityhall.jpg" alt="City Hall" class="img-fluid rounded shadow" style="width: 100%; height: 400px; object-fit: cover;">
                </div>
            </div>
            <div class="col-md-6">
                <div class="garbage-schedule-widget-pro">
                    <div class="text-center mb-4">
                        <span style="font-size: 3em; margin-bottom: 15px; display: block;">📅</span>
                        <h3>Garbage Collection Schedule / කසළ එකතු කිරීමේ කාලසටහන</h3>
                    </div>
                    <div id="garbage-calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add some spacing -->
<div class="mb-4"></div>

    <!-- Our Services Section -->
    <section id="services" class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Our Services / අපගේ සේවා</h2>
        <div class="row">
                <!-- Garbage Collection -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">🗑️</div>
                        <h5 class="service-title-pro">Garbage Collection <br><span class="service-si-pro">කසළ එකතු කිරීම</span></h5>
                        <p class="service-desc-pro">Daily waste collection services to ensure clean surroundings.</p>
                    </div>
                </div>

                <!-- Recycling -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">♻️</div>
                        <h5 class="service-title-pro">Recycling <br><span class="service-si-pro">ප්‍රතිචක්‍රීකරණය</span></h5>
                        <p class="service-desc-pro">Sustainable waste management through eco-friendly recycling.</p>
                    </div>
                </div>

                <!-- Special Waste Collection -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">🚛</div>
                        <h5 class="service-title-pro">Special Collection <br><span class="service-si-pro">විශේෂ එකතු කිරීම</span></h5>
                        <p class="service-desc-pro">Request special pickups for bulky or hazardous items.</p>
                    </div>
                </div>

                <!-- Complaint Handling -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">📝</div>
                        <h5 class="service-title-pro">Complaint Management <br><span class="service-si-pro">පැමිණිලි කළමනාකරණය</span></h5>
                        <p class="service-desc-pro">Easily report and track municipal service complaints online.</p>
                    </div>
                </div>

                <!-- Dansal Registration -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">🍱</div>
                        <h5 class="service-title-pro">Dansal Registration <br><span class="service-si-pro">දන්සල් ලියාපදිංචි</span></h5>
                        <p class="service-desc-pro">Register food donation events during Vesak and Poson festivals.</p>
                    </div>
                </div>

                <!-- Property & Land Services -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">🏠</div>
                        <h5 class="service-title-pro">Property Services <br><span class="service-si-pro">දේපළ සේවා</span></h5>
                        <p class="service-desc-pro">Manage land tax payments, assessments, and transfers.</p>
                    </div>
                </div>

                <!-- Event Approvals -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">📅</div>
                        <h5 class="service-title-pro">Event Approvals <br><span class="service-si-pro">සිදුවීම් අනුමත කිරීම</span></h5>
                        <p class="service-desc-pro">Apply for public area or event venue approvals.</p>
                    </div>
                </div>

                <!-- Water and Drainage -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">💧</div>
                        <h5 class="service-title-pro">Water & Drainage <br><span class="service-si-pro">ජල හා නාය නාලා සේවා</span></h5>
                        <p class="service-desc-pro">Request water supply connections and drainage support.</p>
                    </div>
                </div>

                <!-- Public Notices -->
                <div class="col-md-4 mb-4">
                    <div class="service-card-pro text-center">
                        <div class="service-icon-pro">📢</div>
                        <h5 class="service-title-pro">Public Notices <br><span class="service-si-pro">සභා නිවේදන</span></h5>
                        <p class="service-desc-pro">Stay informed with official council news and announcements.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add FullCalendar CSS and JS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.min.js'></script>

    <style>
/* Scope all styles to index-content to prevent conflicts */
.index-content {
    /* Color Variables - specific to index page */
    --index-white: #ffffff;
    --index-light-gray: #cecece;
    --index-brown: #543729;
    --index-green: #2baf2b;
    --index-blue: #00acee;
    --index-orange: #ef5734;
    --index-yellow: #ffcc2f;
    --index-shadow: rgba(0, 0, 0, 0.1);
    --index-text-color: #543729;
    --index-border-light: rgba(0, 0, 0, 0.05);
    --index-header-gradient: linear-gradient(135deg, #543729 0%, #2b5c2b 100%);
    --index-card-hover: rgba(43, 175, 43, 0.1);
}

/* Hero Section Styles */
.index-content .hero-section {
    position: relative;
    background: url('/urban2/assets/images/city-banner.jpg') no-repeat center center;
    background-size: cover;
    padding: 6rem 0;
    color: var(--index-white);
    margin-bottom: 0;
}

.index-content .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(84, 55, 41, 0.9) 0%, rgba(43, 175, 43, 0.85) 100%);
    z-index: 1;
}

/* Hero Section */
.index-content .hero-section h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: var(--index-white);
    line-height: 1.2;
}

.index-content .hero-section .lead {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    line-height: 1.6;
}

/* Service Cards */
.index-content #services {
    padding: 3rem 0;
}

.index-content .service-card {
    background: var(--index-white);
    border-radius: 10px;
    border: 1px solid var(--index-border-light);
    padding: 2rem;
    height: 100%;
    margin-bottom: 2rem;
    transition: all 0.3s ease;
    text-align: center;
    box-shadow: 0 4px 6px var(--index-shadow);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.index-content .service-card i {
    font-size: 2.5rem;
    margin-bottom: 1.5rem;
    color: var(--index-brown);
    transition: all 0.3s ease;
    background: var(--index-card-hover);
    width: 80px;
    height: 80px;
    line-height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.index-content .service-card h3 {
    color: var(--index-brown);
    font-size: 1.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
    line-height: 1.3;
}

.index-content .service-card p {
    color: var(--index-text-color);
    font-size: 1.1rem;
    margin-bottom: 0;
    line-height: 1.5;
}

/* Contact Section */
.index-content #contact {
    padding: 3rem 0;
}

.index-content .contact-info, .index-content .contact-form {
    background: var(--index-white);
    border-radius: 10px;
    border: 1px solid var(--index-border-light);
    padding: 2rem;
    height: 100%;
    box-shadow: 0 4px 6px var(--index-shadow);
}

.index-content .contact-info h3, .index-content .contact-form h3 {
    color: var(--index-brown);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--index-green);
    text-align: left;
}

.index-content .contact-info p {
    margin-bottom: 1rem;
    color: var(--index-text-color);
    font-size: 1.1rem;
            display: flex;
            align-items: center;
    line-height: 1.5;
}

.index-content .contact-info i {
    margin-right: 1rem;
    color: var(--index-brown);
    width: 24px;
    font-size: 1.4rem;
    text-align: center;
}

.index-content .contact-form .form-control {
    background: var(--index-white);
    border: 1px solid var(--index-border-light);
    padding: 0.8rem 1rem;
    margin-bottom: 1rem;
    border-radius: 5px;
    font-size: 1.1rem;
    line-height: 1.5;
}

/* Section Headers */
.index-content section h2 {
    color: var(--index-brown);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 3rem;
            text-align: center;
    position: relative;
    padding-bottom: 1rem;
    line-height: 1.3;
}

.index-content section h2:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--index-green);
    border-radius: 2px;
}

/* Garbage Schedule Widget */
.index-content .garbage-schedule-widget {
    background: var(--index-white);
    border: 1px solid var(--index-border-light);
    border-radius: 10px;
    padding: 2rem;
    height: 100%;
    box-shadow: 0 4px 6px var(--index-shadow);
}

.index-content .garbage-schedule-widget h3 {
    color: var(--index-brown);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--index-green);
    text-align: center;
    line-height: 1.3;
}

.index-content #garbage-calendar {
    background: var(--index-white);
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px var(--index-shadow);
}

/* Buttons */
.index-content .btn {
    padding: 0.8rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 5px;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.index-content .btn-primary {
    background: var(--index-green);
    border: none;
    color: var(--index-white);
}

.index-content .btn-primary:hover {
    background: var(--index-blue);
    transform: translateY(-2px);
}

/* Row Gutters */
.index-content .row {
    margin-left: -1rem;
    margin-right: -1rem;
}

.index-content .row > [class*='col-'] {
    padding-left: 1rem;
    padding-right: 1rem;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .index-content .hero-section {
        padding: 2rem 0;
    }
    
    .index-content .hero-section h1 {
        font-size: 2rem;
    }
    
    .index-content section h2 {
        font-size: 1.8rem;
        margin-bottom: 2rem;
    }
    
    .index-content .service-card, .index-content .contact-info, .index-content .contact-form {
        margin-bottom: 1.5rem;
    }
    
    .index-content .garbage-schedule-widget {
        margin-top: 2rem;
    }
}

/* Animation Classes */
.index-content .fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: var(--index-light-gray);
}

::-webkit-scrollbar-thumb {
    background: var(--index-green);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--index-blue);
}

/* Focus Styles */
:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(43, 175, 43, 0.3);
}

/* Loading State */
.index-content .loading {
    position: relative;
    overflow: hidden;
}

.index-content .loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Slider Section */
.index-content .slider-section {
    padding: 3rem 0;
    background: #f5f7fa;
}

.index-content .slider-card {
    background: var(--index-white);
    border-radius: 10px;
    border: 1px solid var(--index-border-light);
    padding: 2rem;
    margin: 0.5rem;
    box-shadow: 0 4px 6px var(--index-shadow);
    overflow: hidden;
}

.index-content .slider-content {
    padding: 2rem;
}

.index-content .slider-content h2 {
    color: var(--index-brown);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.3;
}

.index-content .slider-content p {
    color: var(--index-text-color);
    font-size: 1.2rem;
    margin-bottom: 2rem;
    line-height: 1.6;
}

.index-content .carousel-inner {
    border-radius: 10px;
    overflow: hidden;
}

.index-content .carousel-item img {
    border-radius: 8px;
    object-fit: cover;
    height: 400px;
    width: 100%;
}

.index-content .carousel-indicators {
    bottom: -3rem;
}

.index-content .carousel-indicators button {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: var(--index-light-gray);
    border: none;
    margin: 0 6px;
}

.index-content .carousel-indicators button.active {
    background-color: var(--index-green);
}

.index-content .carousel-control-prev,
.index-content .carousel-control-next {
    width: 50px;
    height: 50px;
    background: var(--index-white);
    border-radius: 50%;
    top: 50%;
    transform: translateY(-50%);
    opacity: 1;
    box-shadow: 0 2px 4px var(--index-shadow);
    transition: all 0.3s ease;
}

.index-content .carousel-control-prev {
    left: -25px;
}

.index-content .carousel-control-next {
    right: -25px;
}

.index-content .carousel-control-prev:hover,
.index-content .carousel-control-next:hover {
    background: var(--index-green);
}

.index-content .carousel-control-prev-icon,
.index-content .carousel-control-next-icon {
    width: 24px;
    height: 24px;
    background-size: 100%;
    filter: none;
}

.index-content .carousel-control-prev-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23543729' viewBox='0 0 16 16'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e");
}

.index-content .carousel-control-next-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23543729' viewBox='0 0 16 16'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

.index-content .carousel-control-prev:hover .carousel-control-prev-icon,
.index-content .carousel-control-next:hover .carousel-control-next-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 16 16'%3e%3cpath d='M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z'/%3e%3c/svg%3e");
}

.index-content .carousel-control-next:hover .carousel-control-next-icon {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='%23ffffff' viewBox='0 0 16 16'%3e%3cpath d='M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
}

@media (max-width: 768px) {
    .index-content .slider-card {
        padding: 1rem;
    }

    .index-content .slider-content {
        padding: 1rem;
    }

    .index-content .slider-content h2 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .index-content .slider-content p {
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }

    .index-content .carousel-item img {
        height: 250px;
    }

    .index-content .carousel-control-prev,
    .index-content .carousel-control-next {
        display: none;
    }
}

.hero-section-pro {
    position: relative;
    min-height: 420px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    margin-bottom: 0;
}
.hero-bg-img {
    width: 100%;
    height: 420px;
    object-fit: cover;
    filter: brightness(0.7);
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 1;
}
.hero-overlay-pro {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(120deg, rgba(26,35,126,0.85) 0%, rgba(0,51,20,0.85) 100%);
    z-index: 2;
}
.hero-content-pro {
    position: relative;
    z-index: 3;
    color: #fff;
    padding: 4rem 1rem 3rem 1rem;
}
.hero-content-pro h1 {
    font-size: 2.8rem;
    font-weight: 800;
    letter-spacing: 1px;
    margin-bottom: 1.2rem;
}
.hero-content-pro .lead {
    font-size: 1.3rem;
    margin-bottom: 2rem;
    opacity: 0.95;
}
.hero-cta {
    background: linear-gradient(90deg, #536dfe, #ab47bc);
    border: none;
    border-radius: 30px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #fff;
    box-shadow: 0 4px 16px rgba(83,109,254,0.18);
    transition: all 0.3s cubic-bezier(.4,2,.3,1);
    padding: 0.9rem 2.5rem;
}
.hero-cta:hover {
    background: linear-gradient(90deg, #ab47bc, #536dfe);
    color: #fff;
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 8px 32px rgba(171,71,188,0.22);
    filter: brightness(1.08);
}

/* Slider overlay */
.slider-section .slider-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(120deg, rgba(26,35,126,0.18) 0%, rgba(0,51,20,0.18) 100%);
    z-index: 2;
    pointer-events: none;
}
.slider-section .slider-content {
    position: relative;
    z-index: 3;
}

/* Calendar Card */
.calendar-card-pro {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(26,35,126,0.10);
    padding: 1.5rem;
    margin-bottom: 2rem;
}
.garbage-schedule-widget-pro {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(26,35,126,0.10);
    padding: 2rem 1.5rem;
}
.garbage-schedule-widget-pro h3 {
    color: #003314;
    font-weight: 700;
    font-size: 1.3rem;
}

/* Service Cards */
.service-card-pro {
    background: linear-gradient(135deg, #003314 60%, #145a32 100%);
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
.service-card-pro:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 16px 48px rgba(106,27,154,0.22);
    border-color: #ab47bc;
}
.service-icon-pro {
    font-size: 2.8rem;
    margin-bottom: 1.2rem;
    color: #ab47bc;
    filter: drop-shadow(0 2px 8px rgba(171,71,188,0.12));
}
.service-title-pro {
    color: #fff;
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.7rem;
    letter-spacing: 0.5px;
}
.service-si-pro {
    color: #ab47bc;
    font-size: 1rem;
    font-weight: 600;
}
.service-desc-pro {
    color: #b0b8c1;
    font-size: 1.05rem;
    margin-bottom: 0;
    margin-top: 0.5rem;
}
@media (max-width: 768px) {
    .hero-section-pro, .hero-bg-img {
        min-height: 220px;
        height: 220px;
    }
    .hero-content-pro h1 {
        font-size: 1.5rem;
    }
    .service-card-pro {
        padding: 1.2rem 0.7rem 1.2rem 0.7rem;
        min-height: 220px;
    }
    .service-icon-pro {
        font-size: 2rem;
    }
    .service-title-pro {
        font-size: 1.05rem;
    }
    .service-si-pro {
        font-size: 0.9rem;
    }
    .service-desc-pro {
        font-size: 0.95rem;
    }
}

.calendar-section-pro {
    padding: 3rem 0 2rem 0;
    background: #f5f7fa;
}
.calendar-section-pro .row {
    align-items: stretch !important;
    min-height: 420px;
}
.calendar-card-pro,
.garbage-schedule-widget-pro {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.calendar-card-pro img {
    height: 100%;
    min-height: 320px;
    max-height: 420px;
    object-fit: cover;
}
@media (max-width: 991.98px) {
    .calendar-section-pro .row {
        flex-direction: column;
        min-height: unset;
    }
    .calendar-card-pro,
    .garbage-schedule-widget-pro {
        height: auto;
        min-height: unset;
        margin-bottom: 1.5rem;
    }
    .calendar-card-pro img {
        min-height: 180px;
        max-height: 220px;
    }
}
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('garbage-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'si', // Sinhala locale
            timeZone: 'Asia/Colombo', // Set timezone to Sri Lanka
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            slotMinTime: '06:00:00', // Start time for day/week view
            slotMaxTime: '20:00:00', // End time for day/week view
            allDaySlot: false, // Disable all-day slot
            slotDuration: '01:00:00', // 1-hour slots
            events: '/urban2/api/garbage_schedules.php',
            eventClick: function(info) {
                // Show schedule details in a modal
                var schedule = info.event;
                var modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
                document.getElementById('scheduleArea').textContent = schedule.extendedProps.area;
                document.getElementById('scheduleDate').textContent = schedule.start.toLocaleDateString('si-LK');
                document.getElementById('scheduleTime').textContent = schedule.extendedProps.time;
                document.getElementById('scheduleType').textContent = schedule.extendedProps.waste_type === 'perishable' ? 'Perishable / පාංශු' : 'Non-Perishable / අපාංශු';
                modal.show();
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: true,
                hour12: true
            },
            views: {
                timeGridWeek: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }
                },
                timeGridDay: {
                    titleFormat: { year: 'numeric', month: 'long', day: 'numeric' },
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }
                }
            }
        });
        calendar.render();
    });
</script>

<!-- Schedule Details Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Details / කාලසටහන විස්තර</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Area / ප්‍රදේශය:</strong>
                    <span id="scheduleArea"></span>
                </div>
                <div class="mb-3">
                    <strong>Date / දිනය:</strong>
                    <span id="scheduleDate"></span>
                </div>
                <div class="mb-3">
                    <strong>Time / වේලාව:</strong>
                    <span id="scheduleTime"></span>
                </div>
                <div class="mb-3">
                    <strong>Waste Type / කසළ වර්ගය:</strong>
                    <span id="scheduleType"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Render footer
$layoutController->renderFooter();
?>

<!-- Add the announcement button -->
<?php include 'views/announcement_button.php'; ?>

</body>
</html>
