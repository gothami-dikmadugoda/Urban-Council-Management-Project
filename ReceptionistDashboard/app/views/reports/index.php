<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Appointments</h6>
                    <h2 class="mb-0"><?php echo $appointmentStats['total']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h6 class="card-title text-muted">Total Visitors</h6>
                    <h2 class="mb-0"><?php echo $visitorStats['total']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h6 class="card-title text-muted">Unique Visitors</h6>
                    <h2 class="mb-0"><?php echo $visitorStats['unique_visitors']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow">
                <div class="card-body">
                    <h6 class="card-title text-muted">Today's Visitors</h6>
                    <h2 class="mb-0"><?php echo $visitorStats['today']; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Appointment Status -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Appointment Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="appointmentStatusChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Visitors -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Daily Visitors</h5>
                        <input type="date" class="form-control form-control-sm w-auto" id="dailyDate" value="<?php echo $selectedDate; ?>">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Purpose</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($dailyVisitors->num_rows > 0): ?>
                                    <?php while($row = $dailyVisitors->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['purpose'] ?: '-'); ?></td>
                                            <td><?php echo format_date($row['checkin_time']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">No visitors for this date</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Visitors -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Monthly Visitors</h5>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm w-auto" id="monthSelect">
                                <?php for($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $selectedMonth ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <select class="form-select form-select-sm w-auto" id="yearSelect">
                                <?php for($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $i == $selectedYear ? 'selected' : ''; ?>>
                                        <?php echo $i; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Purpose</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($monthlyVisitors->num_rows > 0): ?>
                                    <?php while($row = $monthlyVisitors->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?: '-'); ?></td>
                                            <td><?php echo htmlspecialchars($row['purpose'] ?: '-'); ?></td>
                                            <td><?php echo format_date($row['checkin_time']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No visitors for this month</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Appointment Status Chart
    const ctx = document.getElementById('appointmentStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
            datasets: [{
                data: [
                    <?php echo $appointmentStats['pending']; ?>,
                    <?php echo $appointmentStats['confirmed']; ?>,
                    <?php echo $appointmentStats['cancelled']; ?>,
                    <?php echo $appointmentStats['completed']; ?>
                ],
                backgroundColor: ['#FFC107', '#28A745', '#DC3545', '#17A2B8']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Date picker event
    document.getElementById('dailyDate').addEventListener('change', function() {
        window.location.href = `/reports?date=${this.value}`;
    });

    // Month and year select events
    document.getElementById('monthSelect').addEventListener('change', function() {
        const year = document.getElementById('yearSelect').value;
        window.location.href = `/reports?year=${year}&month=${this.value}`;
    });

    document.getElementById('yearSelect').addEventListener('change', function() {
        const month = document.getElementById('monthSelect').value;
        window.location.href = `/reports?year=${this.value}&month=${month}`;
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?> 