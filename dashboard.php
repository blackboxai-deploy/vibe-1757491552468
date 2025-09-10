<?php
/**
 * GV Florida Fleet Management System
 * Main Dashboard
 */

$pageTitle = 'Dashboard';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Get dashboard statistics
$stats = [
    'total_buses' => $db->count('buses'),
    'active_buses' => $db->count('buses', "status = 'Active'"),
    'total_drivers' => $db->count('drivers'),
    'active_drivers' => $db->count('drivers', "status = 'Active'"),
    'total_conductors' => $db->count('conductors'),
    'active_conductors' => $db->count('conductors', "status = 'Active'"),
    'active_assignments' => $db->count('assignments', "status = 'Active'"),
    'open_violations' => $db->count('violations', "status = 'Open'"),
    'todays_trips' => $db->count('trips', "trip_date = CURDATE()"),
    'maintenance_due' => $db->count('buses', "next_maintenance <= CURDATE() + INTERVAL 7 DAY")
];

// Get recent activities
$recentTrips = $db->fetchAll("
    SELECT t.*, b.bus_number, d.full_name as driver_name, c.full_name as conductor_name
    FROM trips t
    JOIN buses b ON t.bus_id = b.bus_id
    JOIN drivers d ON t.driver_id = d.driver_id
    JOIN conductors c ON t.conductor_id = c.conductor_id
    ORDER BY t.created_at DESC
    LIMIT 5
");

$recentViolations = $db->fetchAll("
    SELECT v.*, b.bus_number
    FROM violations v
    LEFT JOIN buses b ON v.bus_id = b.bus_id
    WHERE v.status = 'Open'
    ORDER BY v.violation_date DESC, v.created_at DESC
    LIMIT 5
");

// Get chart data for violations by type
$violationsByType = $db->fetchAll("
    SELECT violation_type, COUNT(*) as count
    FROM violations
    WHERE violation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY violation_type
    ORDER BY count DESC
");

// Get chart data for trips per month
$tripsPerMonth = $db->fetchAll("
    SELECT DATE_FORMAT(trip_date, '%Y-%m') as month, COUNT(*) as count
    FROM trips
    WHERE trip_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(trip_date, '%Y-%m')
    ORDER BY month
");

// Get buses due for maintenance
$maintenanceDue = $db->fetchAll("
    SELECT bus_id, bus_number, model, last_maintenance, next_maintenance,
           DATEDIFF(next_maintenance, CURDATE()) as days_until_maintenance
    FROM buses
    WHERE next_maintenance <= CURDATE() + INTERVAL 14 DAY
    AND status = 'Active'
    ORDER BY next_maintenance ASC
    LIMIT 5
");
?>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-gradient mb-1">Fleet Dashboard</h1>
                        <p class="text-muted mb-0">Welcome back, <?= $_SESSION['full_name'] ?>! Here's your fleet overview.</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" onclick="window.location.reload()">
                            <i class="fas fa-sync-alt me-2"></i>Refresh
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#quickStatsModal">
                            <i class="fas fa-chart-line me-2"></i>Quick Stats
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h2 class="stat-number"><?= $stats['active_buses'] ?></h2>
                                <p class="stat-label">Active Buses</p>
                                <small class="text-light">of <?= $stats['total_buses'] ?> total buses</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-bus fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h2 class="stat-number"><?= $stats['active_drivers'] ?></h2>
                                <p class="stat-label">Active Drivers</p>
                                <small class="text-light">of <?= $stats['total_drivers'] ?> total drivers</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-tie fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card info">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h2 class="stat-number"><?= $stats['active_conductors'] ?></h2>
                                <p class="stat-label">Active Conductors</p>
                                <small class="text-light">of <?= $stats['total_conductors'] ?> total conductors</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-users fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card <?= $stats['open_violations'] > 0 ? 'danger' : 'warning' ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h2 class="stat-number"><?= $stats['open_violations'] ?></h2>
                                <p class="stat-label">Open Violations</p>
                                <small class="text-light">requiring attention</small>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle fa-3x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Stats Row -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-clipboard-list fa-2x text-primary mb-2"></i>
                        <h4 class="mb-1"><?= $stats['active_assignments'] ?></h4>
                        <p class="text-muted mb-0">Active Assignments</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-route fa-2x text-success mb-2"></i>
                        <h4 class="mb-1"><?= $stats['todays_trips'] ?></h4>
                        <p class="text-muted mb-0">Today's Trips</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                        <h4 class="mb-1"><?= $stats['maintenance_due'] ?></h4>
                        <p class="text-muted mb-0">Maintenance Due</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-day fa-2x text-info mb-2"></i>
                        <h4 class="mb-1"><?= date('d') ?></h4>
                        <p class="text-muted mb-0"><?= date('M Y') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Trips per Month
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tripsChart" height="100"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Violations by Type
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="violationsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Tables Row -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Trips
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentTrips)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Bus</th>
                                            <th>Route</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentTrips as $trip): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $trip['bus_number'] ?></strong><br>
                                                    <small class="text-muted"><?= $trip['driver_name'] ?></small>
                                                </td>
                                                <td><?= $trip['route'] ?></td>
                                                <td><?= formatDate($trip['trip_date']) ?></td>
                                                <td><?= getStatusBadge($trip['status']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-route fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No recent trips found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Open Violations
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentViolations)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Severity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentViolations as $violation): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= $violation['employee_id'] ?></strong><br>
                                                    <small class="text-muted"><?= $violation['employee_type'] ?></small>
                                                </td>
                                                <td><?= $violation['violation_type'] ?></td>
                                                <td><?= formatDate($violation['violation_date']) ?></td>
                                                <td><?= getSeverityBadge($violation['severity']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <p class="text-muted">No open violations</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Alerts -->
        <?php if (!empty($maintenanceDue)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tools me-2"></i>Maintenance Due Soon
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Bus</th>
                                        <th>Model</th>
                                        <th>Last Maintenance</th>
                                        <th>Due Date</th>
                                        <th>Days Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenanceDue as $bus): ?>
                                        <tr class="<?= $bus['days_until_maintenance'] <= 0 ? 'table-danger' : 'table-warning' ?>">
                                            <td><strong><?= $bus['bus_number'] ?></strong></td>
                                            <td><?= $bus['model'] ?></td>
                                            <td><?= formatDate($bus['last_maintenance']) ?></td>
                                            <td><?= formatDate($bus['next_maintenance']) ?></td>
                                            <td>
                                                <?php if ($bus['days_until_maintenance'] <= 0): ?>
                                                    <span class="badge bg-danger">Overdue</span>
                                                <?php else: ?>
                                                    <?= $bus['days_until_maintenance'] ?> days
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="scheduleMaintenance(<?= $bus['bus_id'] ?>)">
                                                    Schedule
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Trips per Month Chart
    const tripsCtx = document.getElementById('tripsChart').getContext('2d');
    new Chart(tripsCtx, {
        type: 'line',
        data: {
            labels: [<?= "'" . implode("','", array_column($tripsPerMonth, 'month')) . "'" ?>],
            datasets: [{
                label: 'Number of Trips',
                data: [<?= implode(',', array_column($tripsPerMonth, 'count')) ?>],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Violations by Type Chart
    const violationsCtx = document.getElementById('violationsChart').getContext('2d');
    new Chart(violationsCtx, {
        type: 'doughnut',
        data: {
            labels: [<?= "'" . implode("','", array_column($violationsByType, 'violation_type')) . "'" ?>],
            datasets: [{
                data: [<?= implode(',', array_column($violationsByType, 'count')) ?>],
                backgroundColor: [
                    '#e74c3c', '#f39c12', '#3498db', '#27ae60', '#9b59b6', '#34495e'
                ]
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
});

function scheduleMaintenance(busId) {
    // This would typically open a modal for scheduling maintenance
    Swal.fire({
        title: 'Schedule Maintenance',
        text: 'This feature will be implemented to schedule maintenance for the selected bus.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

// Auto-refresh dashboard every 5 minutes
setInterval(function() {
    window.location.reload();
}, 300000);
</script>

<?php require_once 'includes/footer.php'; ?>