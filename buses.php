<?php
/**
 * GV Florida Fleet Management System
 * Buses Management Module
 */

$pageTitle = 'Fleet Management';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_bus':
            $data = [
                'bus_number' => sanitize($_POST['bus_number']),
                'plate_number' => sanitize($_POST['plate_number']),
                'model' => sanitize($_POST['model']),
                'year' => intval($_POST['year']),
                'color' => sanitize($_POST['color']),
                'capacity' => intval($_POST['capacity']),
                'status' => sanitize($_POST['status']),
                'fuel_type' => sanitize($_POST['fuel_type']),
                'purchase_date' => $_POST['purchase_date'],
                'next_maintenance' => $_POST['next_maintenance'],
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->insert('buses', $data);
            jsonResponse(['bus_id' => $result], $result !== false, $result ? 'Bus added successfully' : 'Failed to add bus');
            break;
            
        case 'update_bus':
            $busId = intval($_POST['bus_id']);
            $data = [
                'bus_number' => sanitize($_POST['bus_number']),
                'plate_number' => sanitize($_POST['plate_number']),
                'model' => sanitize($_POST['model']),
                'year' => intval($_POST['year']),
                'color' => sanitize($_POST['color']),
                'capacity' => intval($_POST['capacity']),
                'status' => sanitize($_POST['status']),
                'fuel_type' => sanitize($_POST['fuel_type']),
                'purchase_date' => $_POST['purchase_date'],
                'next_maintenance' => $_POST['next_maintenance'],
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->update('buses', $data, 'bus_id = ?', [$busId]);
            jsonResponse([], $result !== false, $result ? 'Bus updated successfully' : 'Failed to update bus');
            break;
            
        case 'delete_bus':
            $busId = intval($_POST['bus_id']);
            
            // Check if bus has active assignments
            $activeAssignments = $db->count('assignments', "bus_id = ? AND status = 'Active'", [$busId]);
            if ($activeAssignments > 0) {
                jsonResponse([], false, 'Cannot delete bus with active assignments');
                break;
            }
            
            $result = $db->delete('buses', 'bus_id = ?', [$busId]);
            jsonResponse([], $result !== false, $result ? 'Bus deleted successfully' : 'Failed to delete bus');
            break;
    }
    exit;
}

// Get all buses with statistics
$buses = $db->fetchAll("
    SELECT b.*, 
           COUNT(a.assignment_id) as total_assignments,
           (SELECT COUNT(*) FROM assignments a2 WHERE a2.bus_id = b.bus_id AND a2.status = 'Active') as active_assignments,
           (SELECT COUNT(*) FROM trips t WHERE t.bus_id = b.bus_id AND t.trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as recent_trips
    FROM buses b
    LEFT JOIN assignments a ON b.bus_id = a.bus_id
    GROUP BY b.bus_id
    ORDER BY b.bus_number
");

// Get fleet statistics
$fleetStats = [
    'total' => count($buses),
    'active' => count(array_filter($buses, fn($b) => $b['status'] === 'Active')),
    'maintenance' => count(array_filter($buses, fn($b) => $b['status'] === 'In Maintenance')),
    'retired' => count(array_filter($buses, fn($b) => $b['status'] === 'Retired')),
    'maintenance_due' => count(array_filter($buses, fn($b) => strtotime($b['next_maintenance']) <= strtotime('+7 days')))
];
?>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-gradient mb-1">Fleet Management</h1>
                        <p class="text-muted mb-0">Manage your bus fleet, maintenance schedules, and vehicle information</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary me-2" onclick="exportTable('excel', 'buses')">
                            <i class="fas fa-download me-2"></i>Export Excel
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#busModal">
                            <i class="fas fa-plus me-2"></i>Add New Bus
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fleet Statistics -->
        <div class="row mb-4">
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-bus fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?= $fleetStats['total'] ?></h3>
                        <p class="text-muted mb-0">Total Fleet</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?= $fleetStats['active'] ?></h3>
                        <p class="text-muted mb-0">Active</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-tools fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1"><?= $fleetStats['maintenance'] ?></h3>
                        <p class="text-muted mb-0">In Maintenance</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h3 class="mb-1"><?= $fleetStats['retired'] ?></h3>
                        <p class="text-muted mb-0">Retired</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt fa-2x text-info mb-2"></i>
                        <h3 class="mb-1"><?= $fleetStats['maintenance_due'] ?></h3>
                        <p class="text-muted mb-0">Maintenance Due</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buses Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Fleet Inventory
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table" id="busesTable">
                        <thead>
                            <tr>
                                <th>Bus #</th>
                                <th>Plate Number</th>
                                <th>Model</th>
                                <th>Year</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Maintenance</th>
                                <th>Assignments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($buses as $bus): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/50x30?text=<?= urlencode($bus['color']) ?>+Bus" 
                                                 alt="<?= $bus['color'] ?> bus representation" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 25px; object-fit: cover;">
                                            <strong><?= $bus['bus_number'] ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $bus['plate_number'] ?></span>
                                    </td>
                                    <td>
                                        <?= $bus['model'] ?><br>
                                        <small class="text-muted"><?= $bus['color'] ?></small>
                                    </td>
                                    <td><?= $bus['year'] ?></td>
                                    <td>
                                        <i class="fas fa-users me-1"></i>
                                        <?= $bus['capacity'] ?>
                                    </td>
                                    <td><?= getStatusBadge($bus['status']) ?></td>
                                    <td>
                                        <?php if ($bus['next_maintenance']): ?>
                                            <?php 
                                            $daysUntil = floor((strtotime($bus['next_maintenance']) - time()) / 86400);
                                            $badgeClass = $daysUntil <= 0 ? 'danger' : ($daysUntil <= 7 ? 'warning' : 'success');
                                            ?>
                                            <span class="badge bg-<?= $badgeClass ?>">
                                                <?= formatDate($bus['next_maintenance']) ?>
                                            </span>
                                            <?php if ($daysUntil <= 7): ?>
                                                <br><small class="text-<?= $badgeClass ?>">
                                                    <?= $daysUntil <= 0 ? 'Overdue' : $daysUntil . ' days' ?>
                                                </small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not scheduled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $bus['active_assignments'] ?> Active</span><br>
                                        <small class="text-muted"><?= $bus['total_assignments'] ?> Total</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewBus(<?= $bus['bus_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" 
                                                    onclick="editBus(<?= $bus['bus_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (isAdmin()): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteBus(<?= $bus['bus_id'] ?>, '<?= $bus['bus_number'] ?>')"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete"
                                                        <?= $bus['active_assignments'] > 0 ? 'disabled' : '' ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Bus Modal -->
<div class="modal fade" id="busModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="busForm" class="ajax-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="busModalTitle">Add New Bus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="busAction" value="add_bus">
                    <input type="hidden" name="bus_id" id="busId">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="busNumber" class="form-label">Bus Number *</label>
                                <input type="text" class="form-control" name="bus_number" id="busNumber" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="plateNumber" class="form-label">Plate Number *</label>
                                <input type="text" class="form-control" name="plate_number" id="plateNumber" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="model" class="form-label">Model *</label>
                                <input type="text" class="form-control" name="model" id="model" required placeholder="e.g., Mercedes-Benz Sprinter">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="year" class="form-label">Year *</label>
                                <input type="number" class="form-control" name="year" id="year" required min="1990" max="2030">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="color" class="form-label">Color *</label>
                                <select class="form-select" name="color" id="color" required>
                                    <option value="">Select Color</option>
                                    <option value="White">White</option>
                                    <option value="Blue">Blue</option>
                                    <option value="Red">Red</option>
                                    <option value="Green">Green</option>
                                    <option value="Yellow">Yellow</option>
                                    <option value="Silver">Silver</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="capacity" class="form-label">Capacity *</label>
                                <input type="number" class="form-control" name="capacity" id="capacity" required min="1" max="100">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="fuelType" class="form-label">Fuel Type *</label>
                                <select class="form-select" name="fuel_type" id="fuelType" required>
                                    <option value="">Select Fuel Type</option>
                                    <option value="Diesel">Diesel</option>
                                    <option value="Gasoline">Gasoline</option>
                                    <option value="Electric">Electric</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="In Maintenance">In Maintenance</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="purchaseDate" class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" id="purchaseDate">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nextMaintenance" class="form-label">Next Maintenance</label>
                                <input type="date" class="form-control" name="next_maintenance" id="nextMaintenance">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="notes" rows="3" placeholder="Additional information about the bus..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="busSubmitBtn">
                        <i class="fas fa-save me-2"></i>Save Bus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize page-specific scripts
function initPageScripts() {
    // Initialize DataTable with custom options
    $('#busesTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [-1] },
            { searchable: false, targets: [-1] }
        ]
    });
}

function editBus(busId) {
    // Fetch bus data and populate form
    $.ajax({
        url: 'api/get_bus.php',
        type: 'GET',
        data: { bus_id: busId },
        success: function(response) {
            if (response.success) {
                const bus = response.data;
                
                $('#busModalTitle').text('Edit Bus');
                $('#busAction').val('update_bus');
                $('#busId').val(bus.bus_id);
                $('#busNumber').val(bus.bus_number);
                $('#plateNumber').val(bus.plate_number);
                $('#model').val(bus.model);
                $('#year').val(bus.year);
                $('#color').val(bus.color);
                $('#capacity').val(bus.capacity);
                $('#fuelType').val(bus.fuel_type);
                $('#status').val(bus.status);
                $('#purchaseDate').val(bus.purchase_date);
                $('#nextMaintenance').val(bus.next_maintenance);
                $('#notes').val(bus.notes);
                
                $('#busSubmitBtn').html('<i class="fas fa-save me-2"></i>Update Bus');
                $('#busModal').modal('show');
            }
        }
    });
}

function viewBus(busId) {
    // This would show bus details in a modal or navigate to detail page
    Swal.fire({
        title: 'Bus Details',
        text: 'This feature will show detailed bus information including maintenance history, assignments, and trip records.',
        icon: 'info'
    });
}

function deleteBus(busId, busNumber) {
    Swal.fire({
        title: 'Delete Bus',
        text: `Are you sure you want to delete bus ${busNumber}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'delete_bus',
                    bus_id: busId,
                    csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Reset form when modal is closed
$('#busModal').on('hidden.bs.modal', function() {
    $('#busForm')[0].reset();
    $('#busModalTitle').text('Add New Bus');
    $('#busAction').val('add_bus');
    $('#busId').val('');
    $('#busSubmitBtn').html('<i class="fas fa-save me-2"></i>Save Bus');
});
</script>

<style>
.col-xl-2-4 {
    flex: 0 0 auto;
    width: 20%;
}

@media (max-width: 1199.98px) {
    .col-xl-2-4 {
        width: 50%;
    }
}

@media (max-width: 767.98px) {
    .col-xl-2-4 {
        width: 100%;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>