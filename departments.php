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

<!-- Departments Hero Section -->
<div class="departments-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4">Our Departments / අපගේ දෙපාර්තමේන්තු</h1>
                <p class="lead">Explore the various departments that work together to serve our community</p>
            </div>
        </div>
    </div>
</div>

<!-- Departments Grid -->
<div class="container py-5">
    <div class="row g-4">
        <!-- Waste Management Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div class="department-content">
                    <h3>Waste Management / කසළ කළමනාකරණය</h3>
                    <p><strong>Main Responsibility:</strong><br>Handle garbage collection, recycling, and keeping public spaces clean.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Garbage Collection (daily/weekly schedules)</li>
                            <li>Recycling Programs (plastics, papers, metals)</li>
                            <li>Waste Disposal (landfills, composting)</li>
                            <li>Awareness Campaigns (citizen education on waste separation)</li>
                            <li>Monitoring illegal dumping and enforcing penalties</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Services Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="department-content">
                    <h3>Health Services / සෞඛ්‍ය සේවා</h3>
                    <p><strong>Main Responsibility:</strong><br>Maintain public health and offer essential health services to the community.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Public Health Programs (vaccination drives, medical camps)</li>
                            <li>Disease Prevention (monitoring outbreaks, spraying campaigns)</li>
                            <li>Health Education (awareness on hygiene, nutrition, diseases)</li>
                            <li>Sanitation Management (ensuring public restrooms and facilities are clean)</li>
                            <li>Emergency Response (in pandemics, floods, other disasters)</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engineering Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="department-content">
                    <h3>Engineering / ඉංජිනේරු</h3>
                    <p><strong>Main Responsibility:</strong><br>Develop and maintain public infrastructure and facilities.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Road Maintenance (repairs, upgrades, paving new roads)</li>
                            <li>Building Projects (community centers, libraries, sports complexes)</li>
                            <li>Infrastructure Planning (long-term city planning, zoning)</li>
                            <li>Public Lighting Management (installing and maintaining streetlights)</li>
                            <li>Bridge and Drainage System Maintenance (preventing floods)</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="department-content">
                    <h3>Revenue / ආදායම</h3>
                    <p><strong>Main Responsibility:</strong><br>Handle financial activities like tax collection, financial planning, and managing the council's budget.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Tax Collection: Collect taxes from citizens and businesses (property tax, service tax, etc.)</li>
                            <li>Financial Planning: Budget forecasting, financial reporting, and ensuring funds are allocated properly</li>
                            <li>Budget Management: Monitoring spending, approving departmental budgets, and adjusting financial plans as needed</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Planning Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="department-content">
                    <h3>Planning / සැලසුම්</h3>
                    <p><strong>Main Responsibility:</strong><br>Oversee urban development and manage land use to ensure organized city growth.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Urban Development: Approve new building projects, ensure proper city expansion</li>
                            <li>Land Use Planning: Create zoning regulations (residential, commercial, industrial zones)</li>
                            <li>Development Permits: Review and approve construction permits, building renovations, and land use changes</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legal Department -->
        <div class="col-md-6 col-lg-4">
            <div class="department-card">
                <div class="department-icon">
                    <i class="fas fa-gavel"></i>
                </div>
                <div class="department-content">
                    <h3>Legal / නීති</h3>
                    <p><strong>Main Responsibility:</strong><br>Provide legal advice and handle all legal matters of the municipal council.</p>
                    <div><strong>Key Activities:</strong>
                    <ul class="department-services">
                            <li>Legal Counsel: Advise the council on legal rights, obligations, and potential risks</li>
                            <li>Ordinance Management: Draft, review, and enforce local laws (ordinances)</li>
                            <li>Compliance Monitoring: Ensure that council activities comply with national and local laws, handle litigation</li>
                    </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-blue: #1a237e; /* dark blue */
    --primary-green: #004225; /* dark green */
    --primary-purple: #6a1b9a; /* purple */
    --card-bg: linear-gradient(135deg, #232b3e 60%, #1a2330 100%);
    --card-border: #6a1b9a;
    --card-shadow: 0 8px 32px rgba(26,35,126,0.18);
    --icon-bg: linear-gradient(135deg, #6a1b9a 0%, #004225 100%);
    --icon-shadow: 0 4px 16px rgba(106,27,154,0.18);
    --text-white: #fff;
    --text-muted: #b0b8c1;
    --accent-blue: #536dfe;
    --accent-green: #43a047;
    --accent-purple: #ab47bc;
}

.departments-hero {
    background: linear-gradient(120deg, #1a237e 0%, #004225 100%);
    color: var(--text-white);
    padding: 120px 0 80px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(26,35,126,0.18);
}

.departments-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(60deg, rgba(106,27,154,0.18) 0%, rgba(67,160,71,0.12) 100%);
    pointer-events: none;
}

.departments-hero h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 20px;
    color: var(--text-white);
    text-shadow: 2px 2px 8px rgba(26,35,126,0.18);
}

.departments-hero .lead {
    font-size: 1.2rem;
    color: var(--text-muted);
    line-height: 1.8;
    text-shadow: 1px 1px 2px rgba(26,35,126,0.10);
}

.department-card {
    background: var(--card-bg);
    border-radius: 20px;
    padding: 36px 28px 32px 28px;
    height: 100%;
    box-shadow: var(--card-shadow);
    border: 2px solid var(--card-border);
    transition: all 0.4s cubic-bezier(.4,2,.3,1);
    position: relative;
    overflow: hidden;
    color: var(--text-white);
    display: flex;
    flex-direction: column;
    align-items: center;
}

.department-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; width: 100%; height: 5px;
    background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple), var(--accent-green));
    opacity: 0.7;
    transition: opacity 0.4s;
}

.department-card:hover {
    transform: translateY(-10px) scale(1.03);
    box-shadow: 0 16px 48px rgba(106,27,154,0.22);
    border-color: var(--accent-purple);
}

.department-card:hover::before {
    opacity: 1;
}

.department-icon {
    width: 84px;
    height: 84px;
    background: var(--icon-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 28px;
    box-shadow: var(--icon-shadow);
    transition: all 0.4s cubic-bezier(.4,2,.3,1);
    border: 3px solid var(--accent-purple);
}

.department-icon i {
    font-size: 2.3rem;
    color: var(--text-white);
    transition: transform 0.4s;
}

.department-card:hover .department-icon {
    transform: scale(1.12) rotate(-8deg);
    box-shadow: 0 8px 32px rgba(171,71,188,0.22);
    border-color: var(--accent-blue);
}

.department-card:hover .department-icon i {
    transform: rotate(360deg) scale(1.08);
}

.department-content {
    text-align: center;
    width: 100%;
}

.department-content h3 {
    color: var(--accent-blue);
    font-size: 1.35rem;
    font-weight: 700;
    margin-bottom: 18px;
    letter-spacing: 0.5px;
}

.department-content p {
    color: var(--text-white);
    font-size: 1.08rem;
    line-height: 1.7;
    margin-bottom: 18px;
    font-weight: 500;
}

.department-content strong {
    color: var(--accent-purple);
    font-weight: 700;
}

.department-services {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
    color: var(--text-muted);
}

.department-services li {
    color: var(--text-muted);
    font-size: 1rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 0.7em;
    position: relative;
    padding-left: 1.5em;
}

.department-services li::before {
    content: '\2022';
    color: var(--accent-purple);
    font-size: 1.3em;
    position: absolute;
    left: 0;
    top: 0.1em;
}

@media (max-width: 768px) {
    .departments-hero {
        padding: 100px 0 60px;
    }
    .departments-hero h1 {
        font-size: 2.2rem;
    }
    .departments-hero .lead {
        font-size: 1.1rem;
    }
    .department-card {
        padding: 22px 10px 18px 10px;
    }
    .department-icon {
        width: 64px;
        height: 64px;
    }
    .department-icon i {
        font-size: 1.5rem;
    }
    .department-content h3 {
        font-size: 1.1rem;
    }
}

.fade-in {
    animation: fadeIn 0.6s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #232b3e;
}
::-webkit-scrollbar-thumb {
    background: var(--primary-purple);
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: var(--accent-blue);
}
:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(106,27,154,0.18);
}
</style>

<?php
// Render footer
$layoutController->renderFooter();
?> 