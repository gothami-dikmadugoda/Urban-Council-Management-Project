<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Settings.php';

$database = new Database();
$db = $database->getConnection();
$settings = new Settings($db);
$siteSettings = $settings->getSettings();
?>
    <footer class="site-footer">
        <div class="footer-top-pattern"></div>
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="footer-section">
                        <h5 class="footer-heading">URBAN COUNCIL<br>MANAGEMENT SYSTEM</h5>
                        <p class="footer-description">A comprehensive system for managing urban council complaints and services</p>
                        <div class="footer-social-links">
                            <a href="#" class="social-link"><i class='bx bxl-facebook'></i></a>
                            <a href="#" class="social-link"><i class='bx bxl-twitter'></i></a>
                            <a href="#" class="social-link"><i class='bx bxl-instagram'></i></a>
                            <a href="#" class="social-link"><i class='bx bxl-linkedin'></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="footer-section">
                        <h5 class="footer-heading">CONTACT US / අප අමතන්න</h5>
                        <div class="contact-links">
                            <a href="mailto:<?php echo $siteSettings['contact_email']; ?>" class="footer-contact-item">
                                <div class="contact-icon">
                                    <i class='bx bxs-envelope'></i>
                                </div>
                                <span><?php echo $siteSettings['contact_email']; ?></span>
                            </a>
                            <a href="tel:<?php echo $siteSettings['contact_phone']; ?>" class="footer-contact-item">
                                <div class="contact-icon">
                                    <i class='bx bxs-phone'></i>
                                </div>
                                <span><?php echo $siteSettings['contact_phone']; ?></span>
                            </a>
                            <div class="footer-contact-item">
                                <div class="contact-icon">
                                    <i class='bx bxs-map'></i>
                                </div>
                                <span><?php echo $siteSettings['address']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="footer-section">
                        <h5 class="footer-heading">QUICK LINKS / ක්ෂණික සබැඳි</h5>
                        <div class="row">
                            <div class="col-6">
                                <ul class="footer-links">
                                    <li><a href="/urban2">Home / මුල් පිටුව</a></li>
                                    <li><a href="/urban2/complaints.php">Complaints / පැමිණිලි</a></li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="footer-links">
                                    <li><a href="/urban2/services.php">Services / සේවා</a></li>
                                    <li><a href="/urban2/contact.php">Contact / සම්බන්ධ වන්න</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center py-3">
                    <div class="col-md-6">
                        <p class="copyright">&copy; <?php echo date('Y'); ?> <?php echo $siteSettings['site_name']; ?>. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="footer-bottom-links">
                            <a href="/urban2/privacy.php">Privacy Policy / රහස්යතා ප්රතිපත්තිය</a>
                            <a href="/urban2/terms.php">Terms of Service / සේවා කොන්දේසි</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <style>
        /* Color Variables */
        :root {
            --footer-bg: #A1D99B;
            --footer-text: #2C4A1D;
            --footer-link: #2C4A1D;
            --footer-hover: #1B2F12;
            --footer-icon-bg: #FFFFFF;
            --footer-divider: rgba(44, 74, 29, 0.1);
            --footer-hover-bg: rgba(44, 74, 29, 0.1);
        }

        /* SERVICES SECTION */
        #services {
            background: linear-gradient(to right, #f4f7f6, #e9ecef);
            padding: 60px 0;
        }

        #services .service-card {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            transition: all 0.3s ease-in-out;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        #services .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }

        #services .service-card img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }

        #services .service-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        #services .service-card p {
            font-size: 0.95rem;
            color: #555;
        }

        #services .card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
        }

        #services .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        #services .card-body {
            padding: 30px;
        }

        #services i {
            font-size: 3rem;
            margin-bottom: 15px;
            display: block;
        }

        #services h5.card-title {
            font-size: 1.25rem;
            margin-bottom: 15px;
            color: #333;
            font-weight: 600;
        }

        #services p.card-text {
            font-size: 0.95rem;
            color: #666;
        }

        /* Footer Styles */
        .site-footer, footer, .footer {
            width: 100vw; /* Full viewport width */
            margin-left: calc(50% - 50vw); /* Center the full-width background if inside a container */
            right: 0;
            left: 0;
            background: linear-gradient(90deg, #a5d6a7 0%, #81c784 100%);
            color: #222;
            padding: 2.5rem 0 1.2rem 0;
            border-top: 1px solid #b2dfdb;
            box-shadow: 0 -2px 12px rgba(44,62,80,0.06);
        }

        .footer-top-pattern {
            height: 4px;
            background: linear-gradient(90deg, 
                rgba(44, 74, 29, 0.1) 0%,
                rgba(44, 74, 29, 0.2) 50%,
                rgba(44, 74, 29, 0.1) 100%
            );
        }

        .footer-section {
            padding: 1rem;
            height: 100%;
        }

        .footer-heading {
            color: var(--footer-text);
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--footer-text);
            opacity: 0.7;
        }

        .footer-description {
            color: var(--footer-text);
            line-height: 1.8;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .footer-social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--footer-icon-bg);
            color: var(--footer-text);
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 1px solid var(--footer-text);
        }

        .social-link:hover {
            background: var(--footer-text);
            color: var(--footer-bg);
            transform: translateY(-3px);
        }

        .contact-links {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--footer-text);
            text-decoration: none;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 6px;
        }

        .contact-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: var(--footer-icon-bg);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .footer-contact-item:hover {
            background: var(--footer-hover-bg);
        }

        .footer-contact-item:hover .contact-icon {
            background: var(--footer-text);
            color: var(--footer-bg);
        }

        .footer-contact-item i {
            font-size: 1.25rem;
            color: var(--footer-text);
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .footer-links a {
            color: var(--footer-text);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 6px;
            opacity: 0.9;
        }

        .footer-links a:hover {
            background: var(--footer-hover-bg);
            opacity: 1;
            padding-left: 1rem;
        }

        .footer-bottom {
            background: rgba(44, 74, 29, 0.05);
            border-top: 1px solid var(--footer-divider);
        }

        .copyright {
            color: var(--footer-text);
            margin-bottom: 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .footer-bottom-links {
            display: flex;
            gap: 2rem;
            justify-content: flex-end;
        }

        .footer-bottom-links a {
            color: var(--footer-text);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            position: relative;
            opacity: 0.9;
        }

        .footer-bottom-links a::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--footer-text);
            transition: width 0.3s ease;
            opacity: 0.7;
        }

        .footer-bottom-links a:hover {
            opacity: 1;
        }

        .footer-bottom-links a:hover::after {
            width: 100%;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            footer {
                margin-left: 0;
                width: 100%;
            }

            /* Services responsive styles */
            #services .service-card {
                padding: 20px;
            }

            #services .service-card h3 {
                font-size: 1.1rem;
            }

            #services .service-card img {
                width: 60px;
                height: 60px;
            }

            #services .card-body {
                padding: 20px;
            }

            #services h5.card-title {
                font-size: 1.1rem;
            }

            #services i {
                font-size: 2.5rem;
            }

            /* Footer responsive styles */
            .footer {
                margin-top: 2rem;
            }

            .footer-section {
                padding: 0.5rem;
                text-align: center;
            }

            .footer-heading::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .footer-social-links {
                justify-content: center;
            }

            .contact-links {
                align-items: center;
            }

            .footer-contact-item {
                width: 100%;
                justify-content: center;
            }

            .footer-links {
                align-items: center;
            }

            .footer-bottom-links {
                justify-content: center;
                margin-top: 1rem;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .copyright {
                text-align: center;
                margin-bottom: 1rem;
            }

            .row > [class*='col-6'] {
                margin-bottom: 1rem;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 