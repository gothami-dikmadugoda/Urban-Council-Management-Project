<?php
// Include necessary files
require_once 'config/database.php';
require_once 'includes/functions.php';

// Set page title
$page_title = 'Reception Dashboard';

// Get latest visitors
//$latest_visitors = get_latest_visitors();

// Include header
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Welcome to Reception Dashboard</h4>
                            <p class="text-muted">
                                Manage visitors, appointments, and check daily statistics all in one place.
                            </p>
                            <div class="mt-4">
                                <a href="visitors.php" class="btn btn-primary me-2">
                                    <i class="fas fa-user-plus me-2"></i>Register Visitor
                                </a>
                                <a href="appointments.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <img src="img/main.png" alt="Reception Desk" class="img-fluid rounded">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
        
       
    

    <div class="row">
        <!-- Photo Collage -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h5 class="mb-3">Reception Services</h5>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <img src="img/img1.jpg" class="card-img-top" alt="Reception Area">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Welcome Area</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <img src="img/img2.jpg" class="card-img-top" alt="Visitor Check-in">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Visitor Registration</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <img src="img/img3.jpg" class="card-img-top" alt="Visitor Management">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Visitor Management</h6>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card h-100">
                                <img src="img/img4.jpg" class="card-img-top" alt="Calendar">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Appointment Scheduling</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Additional JavaScript for charts
$extra_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    fetch("api/get_appointment_data.php")
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById("appointmentsPieChart").getContext("2d");
            new Chart(ctx, {
                type: "pie",
                data: {
                    labels: ["Today", "This Month", "This Year"],
                    datasets: [{
                        label: "Appointments",
                        data: [data.today, data.month, data.year],
                        backgroundColor: ["#4CAF50", "#FFC107", "#2196F3"],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: "bottom"
                        },
                        title: {
                            display: true,
                            text: "Appointment Summary"
                        }
                    }
                }
            });
        });
});
</script>';

// Include footer
include 'includes/footer.php';
?>
