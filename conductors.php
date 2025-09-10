<?php
/**
 * GV Florida Fleet Management System
 * Conductors Management Module
 */

$pageTitle = 'Conductors Management';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_conductor':
            $data = [
                'employee_id' => sanitize($_POST['employee_id']),
                'full_name' => sanitize($_POST['full_name']),
                'date_of_birth' => $_POST['date_of_birth'],
                'contact_phone' => sanitize($_POST['contact_phone']),
                'contact_email' => sanitize($_POST['contact_email']),
                'address' => sanitize($_POST['address']),
                'emergency_contact' => sanitize($_POST['emergency_contact']),
                'emergency_phone' => sanitize($_POST['emergency_phone']),
                'hire_date' => $_POST['hire_date'],
                'shift_schedule' => sanitize($_POST['shift_schedule']),
                'status' => sanitize($_POST['status']),
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->insert('conductors', $data);
            jsonResponse(['conductor_id' => $result], $result !== false, $result ? 'Conductor added successfully' : 'Failed to add conductor');
            break;
            
        case 'update_conductor':
            $conductorId = intval($_POST['conductor_id']);
            $data = [
                'employee_id' => sanitize($_POST['employee_id']),
                'full_name' => sanitize($_POST['full_name']),
                'date_of_birth' => $_POST['date_of_birth'],
                'contact_phone' => sanitize($_POST['contact_phone']),
                'contact_email' => sanitize($_POST['contact_email']),
                'address' => sanitize($_POST['address']),
                'emergency_contact' => sanitize($_POST['emergency_contact']),
                'emergency_phone' => sanitize($_POST['emergency_phone']),
                'hire_date' => $_POST['hire_date'],
                'shift_schedule' => sanitize($_POST['shift_schedule']),
                'status' => sanitize($_POST['status']),
                'notes' => sanitize($_POST['notes'] ?? '')
            ];
            
            $result = $db->update('conductors', $data, 'conductor_id = ?', [$conductorId]);
            jsonResponse([], $result !== false, $result ? 'Conductor updated successfully' : 'Failed to update conductor');
            break;
            
        case 'delete_conductor':
            $conductorId = intval($_POST['conductor_id']);
            
            // Check if conductor has active assignments
            $activeAssignments = $db->count('assignments', "conductor_id = ? AND status = 'Active'", [$conductorId]);
            if ($activeAssignments > 0) {
                jsonResponse([], false, 'Cannot delete conductor with active assignments');
                break;
            }
            
            $result = $db->delete('conductors', 'conductor_id = ?', [$conductorId]);
            jsonResponse([], $result !== false, $result ? 'Conductor deleted successfully' : 'Failed to delete conductor');
            break;
    }
    exit;
}

// Get all conductors with statistics
$conductors = $db->fetchAll("
    SELECT c.*, 
           COUNT(a.assignment_id) as total_assignments,
           (SELECT COUNT(*) FROM assignments a2 WHERE a2.conductor_id = c.conductor_id AND a2.status = 'Active') as active_assignments,
           (SELECT COUNT(*) FROM trips t WHERE t.conductor_id = c.conductor_id AND t.trip_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) as recent_trips,
           (SELECT COUNT(*) FROM violations v WHERE v.employee_type = 'Conductor' AND v.employee_id = c.employee_id AND v.status = 'Open') as open_violations
    FROM conductors c
    LEFT JOIN assignments a ON c.conductor_id = a.conductor_id
    GROUP BY c.conductor_id
    ORDER BY c.full_name
");

// Get conductor statistics
$conductorStats = [
    'total' => count($conductors),
    'active' => count(array_filter($conductors, fn($c) => $c['status'] === 'Active')),
    'on_leave' => count(array_filter($conductors, fn($c) => $c['status'] === 'On Leave')),
    'suspended' => count(array_filter($conductors, fn($c) => $c['status'] === 'Suspended')),
    'shift_distribution' => []
];

// Calculate shift distribution
$shifts = ['Morning', 'Afternoon', 'Evening', 'Night', 'Rotating'];
foreach ($shifts as $shift) {
    $conductorStats['shift_distribution'][$shift] = count(array_filter($conductors, fn($c) => $c['shift_schedule'] === $shift));
}
?>

<!-- Main Content -->
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="text-gradient mb-1">Conductors Management</h1>
                        <p class="text-muted mb-0">Manage conductor information, shift schedules, and assignments</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary me-2" onclick="exportTable('excel', 'conductors')">
                            <i class="fas fa-download me-2"></i>Export Excel
                        </button>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#conductorModal">
                            <i class="fas fa-plus me-2"></i>Add New Conductor
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conductor Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3 class="mb-1"><?= $conductorStats['total'] ?></h3>
                        <p class="text-muted mb-0">Total Conductors</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3 class="mb-1"><?= $conductorStats['active'] ?></h3>
                        <p class="text-muted mb-0">Active</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-calendar-times fa-2x text-warning mb-2"></i>
                        <h3 class="mb-1"><?= $conductorStats['on_leave'] ?></h3>
                        <p class="text-muted mb-0">On Leave</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-ban fa-2x text-danger mb-2"></i>
                        <h3 class="mb-1"><?= $conductorStats['suspended'] ?></h3>
                        <p class="text-muted mb-0">Suspended</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Distribution -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Shift Distribution
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($conductorStats['shift_distribution'] as $shift => $count): ?>
                                <div class="col-md-2-4">
                                    <div class="text-center p-3">
                                        <div class="h4 mb-1"><?= $count ?></div>
                                        <div class="text-muted"><?= $shift ?></div>
                                        <div class="progress mt-2" style="height: 4px;">
                                            <div class="progress-bar" style="width: <?= $conductorStats['total'] > 0 ? ($count / $conductorStats['total']) * 100 : 0 ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conductors Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Conductor Directory
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover data-table" id="conductorsTable">
                        <thead>
                            <tr>
                                <th>Conductor</th>
                                <th>Employee ID</th>
                                <th>Contact</th>
                                <th>Shift Schedule</th>
                                <th>Service Period</th>
                                <th>Status</th>
                                <th>Assignments</th>
                                <th>Violations</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($conductors as $conductor): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://placehold.co/40x40?text=<?= urlencode(substr($conductor['full_name'], 0, 2)) ?>" 
                                                 alt="Conductor profile picture placeholder" 
                                                 class="rounded-circle me-2">
                                            <div>
                                                <strong><?= $conductor['full_name'] ?></strong><br>
                                                <small class="text-muted">
                                                    Age: <?= $conductor['date_of_birth'] ? date('Y') - date('Y', strtotime($conductor['date_of_birth'])) : 'N/A' ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $conductor['employee_id'] ?></span>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone me-1"></i><?= $conductor['contact_phone'] ?><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i><?= $conductor['contact_email'] ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                        $shiftColors = [
                                            'Morning' => 'success',
                                            'Afternoon' => 'warning',
                                            'Evening' => 'info',
                                            'Night' => 'dark',
                                            'Rotating' => 'primary'
                                        ];
                                        $badgeColor = $shiftColors[$conductor['shift_schedule']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $badgeColor ?>"><?= $conductor['shift_schedule'] ?></span>
                                    </td>
                                    <td>
                                        <strong><?= date('M Y', strtotime($conductor['hire_date'])) ?></strong><br>
                                        <small class="text-muted">
                                            <?= floor((time() - strtotime($conductor['hire_date'])) / (365.25 * 24 * 3600)) ?> years
                                        </small>
                                    </td>
                                    <td><?= getStatusBadge($conductor['status']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $conductor['active_assignments'] ?> Active</span><br>
                                        <small class="text-muted"><?= $conductor['total_assignments'] ?> Total</small>
                                    </td>
                                    <td>
                                        <?php if ($conductor['open_violations'] > 0): ?>
                                            <span class="badge bg-danger"><?= $conductor['open_violations'] ?> Open</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Clean</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?= $conductor['recent_trips'] ?> trips (30d)</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewConductor(<?= $conductor['conductor_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-success" 
                                                    onclick="editConductor(<?= $conductor['conductor_id'] ?>)"
                                                    data-bs-toggle="tooltip" 
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php if (isAdmin()): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="deleteConductor(<?= $conductor['conductor_id'] ?>, '<?= $conductor['full_name'] ?>')"
                                                        data-bs-toggle="tooltip" 
                                                        title="Delete"
                                                        <?= $conductor['active_assignments'] > 0 ? 'disabled' : '' ?>>
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

<!-- Conductor Modal -->
<div class="modal fade" id="conductorModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="conductorForm" class="ajax-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="conductorModalTitle">Add New Conductor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="conductorAction" value="add_conductor">
                    <input type="hidden" name="conductor_id" id="conductorId">
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
                                <label for="shiftSchedule" class="form-label">Shift Schedule *</label>
                                <select class="form-select" name="shift_schedule" id="shiftSchedule" required>
                                    <option value="">Select Shift</option>
                                    <option value="Morning">Morning</option>
                                    <option value="Afternoon">Afternoon</option>
                                    <option value="Evening">Evening</option>
                                    <option value="Night">Night</option>
                                    <option value="Rotating">Rotating</option>
                                </select>
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
                    <button type="submit" class="btn btn-primary" id="conductorSubmitBtn">
                        <i class="fas fa-save me-2"></i>Save Conductor
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
    $('#conductorsTable').DataTable({
        order: [[0, 'asc']],
        columnDefs: [
            { orderable: false, targets: [-1] },
            { searchable: false, targets: [-1] }
        ]
    });
    
    // Set default hire date to today
    document.getElementById('hireDate').value = new Date().toISOString().split('T')[0];
}

function editConductor(conductorId) {
    // Fetch conductor data and populate form
    $.ajax({
        url: 'api/get_conductor.php',
        type: 'GET',
        data: { conductor_id: conductorId },
        success: function(response) {
            if (response.success) {
                const conductor = response.data;
                
                $('#conductorModalTitle').text('Edit Conductor');
                $('#conductorAction').val('update_conductor');
                $('#conductorId').val(conductor.conductor_id);
                $('#employeeId').val(conductor.employee_id);
                $('#fullName').val(conductor.full_name);
                $('#dateOfBirth').val(conductor.date_of_birth);
                $('#contactPhone').val(conductor.contact_phone);
                $('#contactEmail').val(conductor.contact_email);
                $('#address').val(conductor.address);
                $('#emergencyContact').val(conductor.emergency_contact);
                $('#emergencyPhone').val(conductor.emergency_phone);
                $('#hireDate').val(conductor.hire_date);
                $('#shiftSchedule').val(conductor.shift_schedule);
                $('#status').val(conductor.status);
                $('#notes').val(conductor.notes);
                
                $('#conductorSubmitBtn').html('<i class="fas fa-save me-2"></i>Update Conductor');
                $('#conductorModal').modal('show');
            }
        }
    });
}

function viewConductor(conductorId) {
    // This would show conductor details in a modal or navigate to detail page
    Swal.fire({
        title: 'Conductor Details',
        text: 'This feature will show detailed conductor information including assignment history, trip records, and violation history.',
        icon: 'info'
    });
}

function deleteConductor(conductorId, conductorName) {
    Swal.fire({
        title: 'Delete Conductor',
        text: `Are you sure you want to delete conductor ${conductorName}? This action cannot be undone.`,
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
                    action: 'delete_conductor',
                    conductor_id: conductorId,
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
$('#conductorModal').on('hidden.bs.modal', function() {
    $('#conductorForm')[0].reset();
    $('#conductorModalTitle').text('Add New Conductor');
    $('#conductorAction').val('add_conductor');
    $('#conductorId').val('');
    $('#conductorSubmitBtn').html('<i class="fas fa-save me-2"></i>Save Conductor');
    document.getElementById('hireDate').value = new Date().toISOString().split('T')[0];
});
</script>

<style>
.col-md-2-4 {
    flex: 0 0 auto;
    width: 20%;
}

@media (max-width: 767.98px) {
    .col-md-2-4 {
        width: 100%;
        margin-bottom: 1rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>