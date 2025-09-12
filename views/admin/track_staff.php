<?php
session_start();
require_once __DIR__ . '/../../controllers/LocationController.php';

$locationController = new LocationController();
$locationController->validateAccess();

$locations = $locationController->getActiveLocations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Staff - Urban Council Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #FF69B4;
            --secondary-color: #9370DB;
            --success-color: #3CB371;
            --warning-color: #FFD700;
            --info-color: #4169E1;
            --dark-color: #202020;
            --light-color: #F1F1F1;
            --cream-color: #FFFDD0;
            --border-radius: 15px;
            --box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            --transition: all 0.3s ease;
            --gradient-primary: linear-gradient(135deg, #FF69B4, #9370DB);
            --gradient-success: linear-gradient(135deg, #3CB371, #4169E1);
            --gradient-warning: linear-gradient(135deg, #FFD700, #FFFDD0);
            --card-bg: #202020;
            --text-light: #F1F1F1;
            --text-gray: #7E909A;
        }

        body {
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #151921;
            color: var(--text-light);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: var(--transition);
        }

        /* Map Styles */
        #map {
            height: 600px;
            width: 100%;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Card Styles */
        .card {
            background: linear-gradient(145deg, #1a1f2b, #202632);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            color: var(--text-light);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Staff List Styles */
        .staff-list {
            max-height: 600px;
            overflow-y: auto;
            padding: 0.5rem;
            background: rgba(26, 31, 43, 0.5);
            border-radius: var(--border-radius);
        }

        .staff-list::-webkit-scrollbar {
            width: 6px;
        }

        .staff-list::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .staff-list::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 10px;
        }

        .staff-card {
            background: linear-gradient(145deg, #1e2330, #2a2f3d);
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem;
        }

        .staff-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.2);
            background: linear-gradient(145deg, #252a38, #2f3444);
            border: 1px solid var(--primary-color);
        }

        .staff-card h6 {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .staff-card .text-muted {
            color: #a0a8b8 !important;
        }

        .staff-image {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            box-shadow: 0 0 15px rgba(255, 105, 180, 0.3);
            padding: 2px;
            background: #fff;
        }

        /* Button Styles */
        .btn-group .btn {
            padding: 0.8rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: var(--border-radius);
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }

        .btn-group .btn:hover {
            background: var(--gradient-primary);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 105, 180, 0.3);
            border-color: transparent;
        }

        .btn-group .btn i {
            font-size: 1.2rem;
        }

        /* Location Info Styles */
        .location-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .location-info p {
            margin-bottom: 0.5rem;
            color: #a0a8b8;
            display: flex;
            align-items: center;
        }

        .location-info i {
            color: var(--primary-color);
            margin-right: 0.8rem;
            font-size: 1.2rem;
        }

        .card-header h5 {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-header h5::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 10px var(--primary-color);
        }

        .job-role {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: rgba(255, 105, 180, 0.1);
            border-radius: 20px;
            color: var(--primary-color);
            font-size: 0.85rem;
            margin-top: 0.2rem;
        }

        .timestamp {
            font-size: 0.85rem;
            color: #8b93a2;
            margin-top: 0.5rem;
        }

        /* Popup Styles */
        .popup-content {
            min-width: 250px;
            padding: 1rem;
            color: #333;
        }

        .popup-content h5 {
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            #map {
                height: 400px;
            }
            
            .staff-list {
                max-height: 400px;
            }
        }

        /* Page Title */
        h2 {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        /* Text Colors */
        .text-muted {
            color: var(--text-gray) !important;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Track Field Staff</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="refreshLocations()">
                        <i class='bx bx-refresh'></i> Refresh
                    </button>
                    <button class="btn btn-outline-primary" onclick="centerMap()">
                        <i class='bx bx-target-lock'></i> Center Map
                    </button>
                </div>
            </div>

            <div class="row">
                <!-- Map Section -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <div id="map"></div>
                        </div>
                    </div>
                </div>

                <!-- Staff List Section -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Active Field Staff</h5>
                        </div>
                        <div class="card-body staff-list" id="staffList">
                            <!-- Staff cards will be populated here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = {};
        const defaultCenter = [6.9271, 79.8612]; // Default center (Colombo)
        
        // Initialize map
        function initMap() {
            map = L.map('map', {
                maxZoom: 19,  // Maximum zoom for street details
                minZoom: 10   // Minimum zoom to keep context
            }).setView(defaultCenter, 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Add scale control
            L.control.scale().addTo(map);
        }

        // Update staff locations with address lookup
        async function updateLocations(locations) {
            const staffList = document.getElementById('staffList');
            staffList.innerHTML = '';

            for (const staff of locations) {
                // Get address information
                try {
                    const response = await fetch(
                        `https://nominatim.openstreetmap.org/reverse?lat=${staff.latitude}&lon=${staff.longitude}&format=json`
                    );
                    const addressData = await response.json();
                    const address = addressData.display_name;
                    const street = addressData.address.road || addressData.address.street || 'Unknown Road';
                    const suburb = addressData.address.suburb || addressData.address.neighbourhood || '';
                    const city = addressData.address.city || addressData.address.town || '';

                    // Update or create marker with detailed information
                    if (markers[staff.user_id]) {
                        markers[staff.user_id].setLatLng([staff.latitude, staff.longitude]);
                        markers[staff.user_id].setPopupContent(createPopupContent(staff, street, suburb, city));
                    } else {
                        const marker = L.marker([staff.latitude, staff.longitude])
                            .bindPopup(createPopupContent(staff, street, suburb, city))
                            .addTo(map);
                        markers[staff.user_id] = marker;
                    }

                    // Add staff card to list with location info
                    staffList.innerHTML += `
                        <div class="card staff-card" onclick="focusStaff(${staff.user_id}, ${staff.latitude}, ${staff.longitude})">
                            <div class="card-body p-0">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="${staff.profile_image ? '/urban2/assets/images/profiles/' + staff.profile_image : '/urban2/assets/images/OIP.jpeg'}" 
                                         class="staff-image me-3" 
                                         alt="${staff.first_name}'s photo"
                                         onerror="handleImageError(this)">
                                    <div>
                                        <h6 class="mb-1">${staff.first_name} ${staff.last_name}</h6>
                                        <span class="job-role">${staff.job_role}</span>
                                    </div>
                                </div>
                                <div class="location-info">
                                    <p class="mb-2"><i class='bx bx-map'></i> ${street}</p>
                                    <p class="mb-2"><i class='bx bx-buildings'></i> ${suburb}${city ? ', ' + city : ''}</p>
                                    <p class="timestamp">
                                        <i class='bx bx-time-five'></i>
                                        Last updated: ${new Date(staff.timestamp).toLocaleTimeString()}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                } catch (error) {
                    console.error('Error getting address:', error);
                    // Fallback to basic marker if address lookup fails
                    if (!markers[staff.user_id]) {
                        const marker = L.marker([staff.latitude, staff.longitude])
                            .bindPopup(createPopupContent(staff))
                            .addTo(map);
                        markers[staff.user_id] = marker;
                    }
                }
            }
        }

        // Helper function to create popup content
        function createPopupContent(staff, street = null, suburb = null, city = null) {
            let locationInfo = '';
            if (street) {
                locationInfo = `
                    <strong>Location:</strong><br>
                    ${street}<br>
                    ${suburb}${city ? ', ' + city : ''}<br>
                `;
            }
            
            return `
                <div class="popup-content">
                    <h5 class="mb-2">${staff.first_name} ${staff.last_name}</h5>
                    <p class="mb-2"><strong>Role:</strong> ${staff.job_role}</p>
                    ${locationInfo}
                    <p class="mb-0"><strong>Last updated:</strong><br>
                    ${new Date(staff.timestamp).toLocaleString()}</p>
                </div>
            `;
        }

        // Focus on specific staff member with zoom for street details
        function focusStaff(userId, lat, lng) {
            const marker = markers[userId];
            if (marker) {
                map.setView([lat, lng], 17); // Zoom level 17 shows street details
                marker.openPopup();
            }
        }

        // Refresh locations
        function refreshLocations() {
            fetch('/urban2/api/get_locations.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateLocations(data.data);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Center map on all markers
        function centerMap() {
            const bounds = Object.values(markers).map(marker => marker.getLatLng());
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            } else {
                map.setView(defaultCenter, 13);
            }
        }

        // Add a function to handle profile image errors
        function handleImageError(img) {
            img.onerror = null; // Prevent infinite loop
            img.src = '/urban2/assets/images/OIP.jpeg';
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initMap();
            refreshLocations();
            // Refresh locations every minute
            setInterval(refreshLocations, 60000);
        });
    </script>
</body>
</html> 