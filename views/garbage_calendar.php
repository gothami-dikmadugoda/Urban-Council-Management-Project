<?php
require_once __DIR__ . '/../controllers/AdminController.php';

$adminController = new AdminController();
$schedules = $adminController->getGarbageSchedules();

// Debug log
error_log("Schedules response: " . print_r($schedules, true));

$schedules = isset($schedules['success']) && $schedules['success'] ? $schedules['data'] : [];

// Group schedules by date for better organization
$groupedSchedules = [];
foreach ($schedules as $schedule) {
    $date = date('Y-m-d', strtotime($schedule['schedule_date']));
    if (!isset($groupedSchedules[$date])) {
        $groupedSchedules[$date] = [];
    }
    $groupedSchedules[$date][] = $schedule;
}

// Sort dates
ksort($groupedSchedules);

// Debug log
error_log("Grouped schedules: " . print_r($groupedSchedules, true));
?>
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="fas fa-calendar-alt me-2"></i>
            Garbage Collection Schedule / කසළ එකතු කිරීමේ කාලසටහන
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($schedules)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No garbage collection schedules available at the moment. / මෙම මොහොතේ කසළ එකතු කිරීමේ කාලසටහනක් නොමැත.
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-12">
                    <?php foreach ($groupedSchedules as $date => $daySchedules): ?>
                        <div class="schedule-card mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-calendar-day me-2"></i>
                                        <?php echo date('l, F j, Y', strtotime($date)); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($daySchedules as $schedule): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="schedule-item p-3 border rounded">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-clock me-2"></i>
                                                            <?php echo date('h:i A', strtotime($schedule['schedule_time'])); ?>
                                                        </h6>
                                                        <span class="waste-type waste-<?php echo $schedule['waste_type']; ?>">
                                                            <?php echo ucfirst(str_replace('_', ' ', $schedule['waste_type'])); ?>
                                                        </span>
                                                    </div>
                                                    <p class="mb-2">
                                                        <i class="fas fa-map-marker-alt me-2"></i>
                                                        <?php echo htmlspecialchars($schedule['area']); ?>
                                                    </p>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-info-circle me-1"></i>
                                                        Please prepare your waste for collection
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.schedule-card {
    transition: transform 0.2s;
}
.schedule-card:hover {
    transform: translateY(-2px);
}
.schedule-item {
    background: #f8f9fa;
    transition: all 0.3s ease;
}
.schedule-item:hover {
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.waste-type {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}
.waste-perishable {
    background: #e2e3e5;
    color: #383d41;
}
.waste-non_perishable {
    background: #cce5ff;
    color: #004085;
}
</style> 