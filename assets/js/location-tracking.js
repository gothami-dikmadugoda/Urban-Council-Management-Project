let map;
let marker;
let locationUpdateInterval;
let isTracking = false;

// Initialize map
function initMap() {
    try {
        // Default center (Sri Lanka)
        const defaultCenter = [6.9271, 79.8612];
        
        const mapElement = document.getElementById('staffMap');
        if (!mapElement) {
            console.error('Map element not found');
            return;
        }
        
        map = L.map('staffMap').setView(defaultCenter, 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        console.log('[Location Tracking] Map initialized successfully');
    } catch (error) {
        console.error('[Location Tracking] Error initializing map:', error);
    }
}

// Start location tracking
function startLocationTracking() {
    if (isTracking) {
        return; // Already tracking
    }

    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');

    if (!statusIcon || !statusText) {
        console.error('[Location Tracking] Status elements not found');
        return;
    }

    if ("geolocation" in navigator) {
        isTracking = true;
        statusIcon.classList.remove('status-inactive');
        statusIcon.classList.add('status-active');
        statusText.textContent = 'Location tracking active';
        console.log('[Location Tracking] Starting location tracking...');

        // Update location every 30 seconds
        function updateLocation() {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const { latitude, longitude } = position.coords;

                    // Update marker position
                    if (!marker && map) {
                        marker = L.marker([latitude, longitude]).addTo(map);
                    } else if (marker) {
                        marker.setLatLng([latitude, longitude]);
                    }

                    // Center map on current position if map exists
                    if (map) {
                        map.setView([latitude, longitude], map.getZoom());
                    }

                    // Send location to server
                    fetch('/urban2/api/update_location.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            latitude: latitude,
                            longitude: longitude,
                            timestamp: new Date().toISOString()
                        }),
                        credentials: 'include' // Include session cookies
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (!data.success) {
                            console.error('[Location Tracking] Error updating location:', data.message);
                            stopLocationTracking('Error updating location');
                        } else {
                            console.log('[Location Tracking] Location updated successfully');
                            updateLocationHistory(latitude, longitude);
                        }
                    })
                    .catch(error => {
                        console.error('[Location Tracking] Error:', error);
                        stopLocationTracking('Error updating location');
                    });
                },
                function(error) {
                    console.error('[Location Tracking] Geolocation error:', error);
                    stopLocationTracking('Error getting location');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        // Initial update
        updateLocation();
        
        // Set interval for updates
        locationUpdateInterval = setInterval(updateLocation, 30000);
    } else {
        stopLocationTracking('Geolocation not supported');
    }
}

// Stop location tracking
function stopLocationTracking(reason = 'Location tracking stopped') {
    if (locationUpdateInterval) {
        clearInterval(locationUpdateInterval);
        locationUpdateInterval = null;
    }
    
    const statusIcon = document.getElementById('statusIcon');
    const statusText = document.getElementById('statusText');

    if (statusIcon && statusText) {
        statusIcon.classList.remove('status-active');
        statusIcon.classList.add('status-inactive');
        statusText.textContent = reason;
    }
    
    isTracking = false;
    console.log('[Location Tracking] Stopped:', reason);
}

// Update location history table
function updateLocationHistory(latitude, longitude) {
    const tbody = document.getElementById('locationHistory');
    if (!tbody) {
        return;
    }

    const row = document.createElement('tr');
    const time = new Date().toLocaleTimeString();
    row.innerHTML = `
        <td>${time}</td>
        <td>${latitude.toFixed(6)}, ${longitude.toFixed(6)}</td>
        <td><span class="badge bg-success">Active</span></td>
    `;
    
    // Insert at the beginning of the table
    if (tbody.firstChild) {
        tbody.insertBefore(row, tbody.firstChild);
    } else {
        tbody.appendChild(row);
    }
    
    // Keep only last 10 entries
    while (tbody.children.length > 10) {
        tbody.removeChild(tbody.lastChild);
    }
}

// Initialize when document is loaded
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Initialize map
        initMap();
        
        // Start location tracking
        startLocationTracking();
        
        // Handle page visibility changes
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopLocationTracking('Location tracking paused');
            } else {
                startLocationTracking();
            }
        });
    } catch (error) {
        console.error('[Location Tracking] Initialization error:', error);
    }
}); 