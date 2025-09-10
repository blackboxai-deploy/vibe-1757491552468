<?php
/**
 * GV Florida Fleet Management System
 * Drivers Management Module
 */

$pageTitle = 'Drivers Management';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_driver':
            $data = [
                'employee_id' => sanitize($_POST['employee_id']),
                'full_name' => sanitize($_POST['full_name']),
                'date_of_birth' => $_POST['date_of_birth'],
                'license_number' => sanitize($_POST['license_number']),
                'license_expiry' => $_POST['license_expiry'],
                'contact_phone' => sanitize($_POST['contact_phone']),
                'contact_email' => sanitize($_POST['contact_email']),
                'address' => sanitize($_POST['address']),
                'emergency_contact' => sanitize($_POST['emergency_contact']),
                'emergency_phone' => sanitize($_POST['emergency_phone']),
                'hire_date' => $_POST['hire_date'],
                'experience_years' => intval($_POST['experience_years']),
                'status' => sanitize($_POST['status']),
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->insert('drivers', $data);
            jsonResponse(['driver_id' => $result], $result !== false, $result ? 'Driver added successfully' : 'Failed to add driver');
            break;
            
        case 'update_driver':
            $driverId = intval($_POST['driver_id']);
            $data = [
                'employee_id' => sanitize($_POST['employee_id']),
                'full_name' => sanitize($_POST['full_name']),
                'date_of_birth' => $_POST['date_of_birth'],
                'license_number' => sanitize($_POST['license_number']),
                'license_expiry' => $_POST['license_expiry'],
                'contact_phone' => sanitize($_POST['contact_phone']),
                'contact_email' => sanitize($_POST['contact_email']),
                'address' => sanitize($_POST['address']),
                'emergency_contact' => sanitize($_POST['emergency_contact']),
                'emergency_phone' => sanitize($_POST['emergency_phone']),
                'hire_date' => $_POST['hire_date'],
                'experience_years' => intval($_POST['experience_years']),
                'status' => sanitize($_POST['status']),
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->update('drivers', $data, 'driver_id = ?', [$driverId]);
            jsonResponse([], $result !== false, $result ? 'Driver updated successfully' : 'Failed to update driver');
            break;
            
        case 'delete_driver':
            $driverId = intval($_POST['driver_id']);
            
            // Check if driver has active assignments
            $activeAssignments = $db->count('assignments', "driver_id = ? AND status = 'Active'", [$driverId]);
            if ($activeAssignments > 0) {
                jsonResponse([], false, 'Cannot delete driver with active assignments');
                break;
            }
            
            $result = $db->delete('drivers', 'driver_id = ?', [$driverId]);
            jsonResponse([], $result !== false, $result ? 'Driver deleted successfully' : 'Failed to delete driver');
            break;
    }
    exit;
}

// Get all drivers with statistics
$drivers = $db->fetchAll("
    SELECT d.*, 
           COUNT(a.assignment_id) as total_assignments,
           (SELECT COUNT(*) FROM assignments a2 WHERE a2.driver_id = d.driver_id AND a2.status = 'Active') as active_assignments,
           (SELECT COUNT(*) FROM trips t WHERE t.driver_id = d.driver_id AND t.trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as recent_trips,
           (SELECT COUNT(*) FROM violations v WHERE v.employee_type = 'Driver' AND v.employee_id = d.employee_id AND v.status = 'Open') as open_violations
    FROM drivers d
    LEFT JOIN assignments a ON d.driver_id = a.driver_id
    GROUP BY d.driver_id
    ORDER BY d.full_name
");

// Get driver statistics
$driverStats = [
    'total' => count($drivers),
    'active' => count(array_filter($drivers, fn($d) => $d['status'] === 'Active')),
    'on_leave' => count(array_filter($drivers, fn($d) => $d['status'] === 'On Leave')),
    'suspended' => count(array_filter($drivers, fn($d) => $d['status'] === 'Suspended')),
    'license_expiring' => count(array_filter($drivers, fn($d) => strtotime($d['license_expiry']) <= strtotime('+30 days')))
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
                        <h1 class="text-gradient mb-1">Drivers Management</h1>
                        <p class="text-muted mb-0">Manage driver information, licenses, and assignments</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary me-2" onclick="exportTable('excel', 'drivers')">
                            <i class="fas fa-download me-2"></i>Export Excel
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#driverModal">
                            <i class="fas fa-plus me-2"></i>Add New Driver
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Statistics -->
        <div class="row mb-4">
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-user-tie fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?= $driverStats['total'] ?></h3>
                        <p class="text-muted mb-0">Total Drivers</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?= $driverStats['active'] ?></h3>
                        <p class="text-muted mb-0">Active</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-times fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1"><?= $driverStats['on_leave'] ?></h3>
                        <p class="text-muted mb-0">On Leave</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                        <h3 class="mb-1"><?= $driverStats['suspended'] ?></h3>
                        <p class="text-muted mb-0">Suspended</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-2-4 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-id-card fa-2x text-info mb-2"></i>
                        <h3 class="mb-1"><?= $driverStats['license_expiring'] ?></h3>
                        <p class="text-muted mb-0">License Expiring</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Expiration Alerts -->
        <?php
        $expiringLicenses = array_filter($drivers, fn($d) => strtotime($d['license_expiry']) <= strtotime('+30 days'));
        if (!empty($expiringLicenses)):
        ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>License Expiration Alerts
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Employee ID</th>
                                        <th>License Number</th>
                                        <th>Expiry Date</th>
                                        <th>Days Left</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($expiringLicenses as $driver): ?>
                                        <?php 
                                        $daysLeft = floor((strtotime($driver['license_expiry']) - time()) / 86400);
                                        $rowClass = $daysLeft <= 0 ? 'table-danger' : ($daysLeft <= 7 ? 'table-warning' : 'table-info');
                                        ?>
                                        <tr class="<?= $rowClass ?>">
                                            <td><strong><?= $driver['full_name'] ?></strong></td>
                                            <td><?= $driver['employee_id'] ?></td>
                                            <td><?= $driver['license_number'] ?></td>
                                            <td><?= formatDate($driver['license_expiry']) ?></td>
                                            <td>
                                                <?php if ($daysLeft <= 0): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php else: ?>
                                                    <?= $daysLeft ?> days
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="renewLicense(<?= $driver['driver_id'] ?>)">
                                                    Renew License
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

        <!-- Drivers Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Driver Directory
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table" id="driversTable">
                        <thead>
                            <tr>
                                <th>Driver</th>
                                <th>Employee ID</th>
                                <th>License Info</th>
                                <th>Contact</th>
                                <th>Experience</th>
                                <th>Status</th>
                                <th>Assignments</th>
                                <th>Violations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/40x40?text=<?= urlencode(substr($driver['full_name'], 0, 2)) ?>" 
                                                 alt="Driver profile picture placeholder" 
                                                 class="rounded-circle me-2">
                                            <div>
                                                <strong><?= $driver['full_name'] ?></strong><br>
                                                <small class="text-muted">
                                                    Age: <?= date('Y') - date('Y', strtotime($driver['date_of_birth'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $driver['employee_id'] ?></span>
                                    </td>
                                    <td>
                                        <strong><?= $driver['license_number'] ?></strong><br>
                                        <small class="<?= strtotime($driver['license_expiry']) <= strtotime('+30 days') ? 'text-danger' : 'text-muted' ?>">
                                            Exp: <?= formatDate($driver['license_expiry']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone me-1"></i><?= $driver['contact_phone'] ?><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i><?= $driver['contact_email'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $driver['experience_years'] ?> years</span><br>
                                        <small class="text-muted">
                                            Since: <?= date('M Y', strtotime($driver['hire_date'])) ?>
                                        </small>
                                    </td>
                                    <td><?= getStatusBadge($driver['status']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $driver['active_assignments'] ?> Active</span><br>
                                        <small class="text-muted"><?= $driver['total_assignments'] ?> Total</small>
                                    </td>
                                    <td>
                                        <?php if ($driver['open_violations'] > 0): ?>
                                            <span class="badge bg-danger"><?= $driver['open_violations'] ?> Open</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Clean</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= $driver['recent_trips'] ?> trips (30d)</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewDriver(<?= $driver['driver_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" 
                                                    onclick="editDriver(<?= $driver['driver_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (isAdmin()): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteDriver(<?= $driver['driver_id'] ?>, '<?= $driver['full_name'] ?>')"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete"
                                                        <?= $driver['active_assignments'] > 0 ? 'disabled' : '' ?>>
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

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="driverForm" class="ajax-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="driverModalTitle">Add New Driver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="driverAction" value="add_driver">
                    <input type="hidden" name="driver_id" id="driverId">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    
                    <!-- Personal Information -->
                    <h6 class="border-bottom pb-2 mb-3">Personal Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="employeeId" class="form-label">Employee ID *</label>
                                <input type="text" class="form-control" name="employee_id" id="employeeId" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="fullName" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" id="fullName" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dateOfBirth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" id="dateOfBirth">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="hireDate" class="form-label">Hire Date *</label>
                                <input type="date" class="form-control" name="hire_date" id="hireDate" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="experienceYears" class="form-label">Experience (Years)</label>
                                <input type="number" class="form-control" name="experience_years" id="experienceYears" min="0" max="50">
                            </div>
                        </div>
                    </div>
                    
                    <!-- License Information -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">License Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="licenseNumber" class="form-label">License Number *</label>
                                <input type="text" class="form-control" name="license_number" id="licenseNumber" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="licenseExpiry" class="form-label">License Expiry *</label>
                                <input type="date" class="form-control" name="license_expiry" id="licenseExpiry" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Contact Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contactPhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="contact_phone" id="contactPhone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="contact_email" id="contactEmail">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                    </div>
                    
                    <!-- Emergency Contact -->
                    <h6 class="border-bottom pb-2 mb-3 mt-4">Emergency Contact</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergencyContact" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" name="emergency_contact" id="emergencyContact">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emergencyPhone" class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" name="emergency_phone" id="emergencyPhone">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status and Notes -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="">Select Status</option>
                                    <option value="Active">Active</option>
                                    <option value="On Leave">On Leave</option>
                                    <option value="Suspended">Suspended</option>
                                    <option value="Terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" id="notes" rows="2" placeholder="Additional information..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="driverSubmitBtn">
                        <i class="fas fa-save me-2"></i>Save Driver
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
    $('#driversTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [-1] },
            { searchable: false, targets: [-1] }
        ]
    });
    
    // Set default hire date to today
    document.getElementById('hireDate').value = new Date().toISOString().split('T')[0];
}

function editDriver(driverId) {
    // Fetch driver data and populate form
    $.ajax({
        url: 'api/get_driver.php',
        type: 'GET',
        data: { driver_id: driverId },
        success: function(response) {
            if (response.success) {
                const driver = response.data;
                
                $('#driverModalTitle').text('Edit Driver');
                $('#driverAction').val('update_driver');
                $('#driverId').val(driver.driver_id);
                $('#employeeId').val(driver.employee_id);
                $('#fullName').val(driver.full_name);
                $('#dateOfBirth').val(driver.date_of_birth);
                $('#licenseNumber').val(driver.license_number);
                $('#licenseExpiry').val(driver.license_expiry);
                $('#contactPhone').val(driver.contact_phone);
                $('#contactEmail').val(driver.contact_email);
                $('#address').val(driver.address);
                $('#emergencyContact').val(driver.emergency_contact);
                $('#emergencyPhone').val(driver.emergency_phone);
                $('#hireDate').val(driver.hire_date);
                $('#experienceYears').val(driver.experience_years);
                $('#status').val(driver.status);
                $('#notes').val(driver.notes);
                
                $('#driverSubmitBtn').html('<i class="fas fa-save me-2"></i>Update Driver');
                $('#driverModal').modal('show');
            }
        }
    });
}

function viewDriver(driverId) {
    // This would show driver details in a modal or navigate to detail page
    Swal.fire({
        title: 'Driver Details',
        text: 'This feature will show detailed driver information including assignment history, trip records, and violation history.',
        icon: 'info'
    });
}

function deleteDriver(driverId, driverName) {
    Swal.fire({
        title: 'Delete Driver',
        text: `Are you sure you want to delete driver ${driverName}? This action cannot be undone.`,
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
                    action: 'delete_driver',
                    driver_id: driverId,
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

function renewLicense(driverId) {
    Swal.fire({
        title: 'Renew License',
        html: '<input type="date" id="newExpiryDate" class="form-control" min="' + new Date().toISOString().split('T')[0] + '">',
        showCancelButton: true,
        confirmButtonText: 'Update Expiry',
        preConfirm: () => {
            const newDate = document.getElementById('newExpiryDate').value;
            if (!newDate) {
                Swal.showValidationMessage('Please select a new expiry date');
                return false;
            }
            return newDate;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/update_license.php',
                type: 'POST',
                data: {
                    driver_id: driverId,
                    license_expiry: result.value,
                    csrf_token: '<?= $_SESSION['csrf_token'] ?>'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Updated!', 'License expiry date updated successfully', 'success');
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
$('#driverModal').on('hidden.bs.modal', function() {
    $('#driverForm')[0].reset();
    $('#driverModalTitle').text('Add New Driver');
    $('#driverAction').val('add_driver');
    $('#driverId').val('');
    $('#driverSubmitBtn').html('<i class="fas fa-save me-2"></i>Save Driver');
    document.getElementById('hireDate').value = new Date().toISOString().split('T')[0];
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