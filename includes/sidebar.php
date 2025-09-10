<?php
/**
 * GV Florida Fleet Management System
 * Sidebar Navigation Component
 */

// Get current page for active navigation
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Navigation menu items
$menuItems = [
    'dashboard' => [
        'icon' => 'fas fa-tachometer-alt',
        'title' => 'Dashboard',
        'url' => 'dashboard.php',
        'access' => ['Admin', 'Staff']
    ],
    'buses' => [
        'icon' => 'fas fa-bus',
        'title' => 'Fleet Management',
        'url' => 'buses.php',
        'access' => ['Admin', 'Staff']
    ],
    'drivers' => [
        'icon' => 'fas fa-user-tie',
        'title' => 'Drivers',
        'url' => 'drivers.php',
        'access' => ['Admin', 'Staff']
    ],
    'conductors' => [
        'icon' => 'fas fa-users',
        'title' => 'Conductors',
        'url' => 'conductors.php',
        'access' => ['Admin', 'Staff']
    ],
    'assignments' => [
        'icon' => 'fas fa-clipboard-list',
        'title' => 'Assignments',
        'url' => 'assignments.php',
        'access' => ['Admin', 'Staff']
    ],
    'trips' => [
        'icon' => 'fas fa-route',
        'title' => 'Trip Records',
        'url' => 'trips.php',
        'access' => ['Admin', 'Staff']
    ],
    'violations' => [
        'icon' => 'fas fa-exclamation-triangle',
        'title' => 'Violations',
        'url' => 'violations.php',
        'access' => ['Admin', 'Staff']
    ],
    'maintenance' => [
        'icon' => 'fas fa-tools',
        'title' => 'Maintenance',
        'url' => 'maintenance.php',
        'access' => ['Admin', 'Staff']
    ],
    'users' => [
        'icon' => 'fas fa-user-cog',
        'title' => 'User Management',
        'url' => 'users.php',
        'access' => ['Admin']
    ],
    'reports' => [
        'icon' => 'fas fa-chart-bar',
        'title' => 'Reports',
        'url' => 'reports.php',
        'access' => ['Admin', 'Staff']
    ]
];

// Check if user has access to menu item
function hasAccess($accessLevels) {
    return in_array($_SESSION['role'], $accessLevels);
}
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="https://placehold.co/80x80?text=GV+Logo" alt="GV Florida Transport Logo" class="img-fluid mb-2" style="max-width: 60px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3);">
        <h5><?= COMPANY_NAME ?></h5>
        <small>Fleet Management Portal</small>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Main Navigation -->
            <li class="nav-section">
                <i class="fas fa-home me-2"></i>Main Navigation
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>" 
                   href="<?= $menuItems['dashboard']['url'] ?>">
                    <i class="<?= $menuItems['dashboard']['icon'] ?>"></i>
                    <?= $menuItems['dashboard']['title'] ?>
                </a>
            </li>
            
            <!-- Fleet Management -->
            <li class="nav-section">
                <i class="fas fa-truck me-2"></i>Fleet Management
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'buses' ? 'active' : '' ?>" 
                   href="<?= $menuItems['buses']['url'] ?>">
                    <i class="<?= $menuItems['buses']['icon'] ?>"></i>
                    <?= $menuItems['buses']['title'] ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'maintenance' ? 'active' : '' ?>" 
                   href="<?= $menuItems['maintenance']['url'] ?>">
                    <i class="<?= $menuItems['maintenance']['icon'] ?>"></i>
                    <?= $menuItems['maintenance']['title'] ?>
                </a>
            </li>
            
            <!-- Personnel Management -->
            <li class="nav-section">
                <i class="fas fa-users me-2"></i>Personnel
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'drivers' ? 'active' : '' ?>" 
                   href="<?= $menuItems['drivers']['url'] ?>">
                    <i class="<?= $menuItems['drivers']['icon'] ?>"></i>
                    <?= $menuItems['drivers']['title'] ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'conductors' ? 'active' : '' ?>" 
                   href="<?= $menuItems['conductors']['url'] ?>">
                    <i class="<?= $menuItems['conductors']['icon'] ?>"></i>
                    <?= $menuItems['conductors']['title'] ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'assignments' ? 'active' : '' ?>" 
                   href="<?= $menuItems['assignments']['url'] ?>">
                    <i class="<?= $menuItems['assignments']['icon'] ?>"></i>
                    <?= $menuItems['assignments']['title'] ?>
                </a>
            </li>
            
            <!-- Operations -->
            <li class="nav-section">
                <i class="fas fa-cogs me-2"></i>Operations
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'trips' ? 'active' : '' ?>" 
                   href="<?= $menuItems['trips']['url'] ?>">
                    <i class="<?= $menuItems['trips']['icon'] ?>"></i>
                    <?= $menuItems['trips']['title'] ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'violations' ? 'active' : '' ?>" 
                   href="<?= $menuItems['violations']['url'] ?>">
                    <i class="<?= $menuItems['violations']['icon'] ?>"></i>
                    <?= $menuItems['violations']['title'] ?>
                    <?php
                    // Show violations count badge
                    $violationsCount = $db->count('violations', "status = 'Open'");
                    if ($violationsCount > 0):
                    ?>
                        <span class="badge bg-danger ms-auto"><?= $violationsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            
            <!-- Reports & Analytics -->
            <li class="nav-section">
                <i class="fas fa-chart-line me-2"></i>Analytics
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>" 
                   href="<?= $menuItems['reports']['url'] ?>">
                    <i class="<?= $menuItems['reports']['icon'] ?>"></i>
                    <?= $menuItems['reports']['title'] ?>
                </a>
            </li>
            
            <!-- Administration (Admin Only) -->
            <?php if (isAdmin()): ?>
            <li class="nav-section">
                <i class="fas fa-shield-alt me-2"></i>Administration
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" 
                   href="<?= $menuItems['users']['url'] ?>">
                    <i class="<?= $menuItems['users']['icon'] ?>"></i>
                    <?= $menuItems['users']['title'] ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#systemSettingsModal">
                    <i class="fas fa-cog"></i>
                    System Settings
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#backupModal">
                    <i class="fas fa-database"></i>
                    Backup & Restore
                </a>
            </li>
            <?php endif; ?>
            
            <!-- Quick Actions -->
            <li class="nav-section">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="openQuickAdd('trip')">
                    <i class="fas fa-plus-circle"></i>
                    Add New Trip
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="openQuickAdd('violation')">
                    <i class="fas fa-exclamation-circle"></i>
                    Report Violation
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="showFleetStatus()">
                    <i class="fas fa-eye"></i>
                    Fleet Status
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- Sidebar Footer -->
    <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 1rem; background: rgba(0,0,0,0.1); border-top: 1px solid rgba(255,255,255,0.1);">
        <div class="text-center">
            <small class="text-muted">
                <i class="fas fa-user me-1"></i>
                Welcome, <?= $_SESSION['full_name'] ?>
            </small>
            <br>
            <small class="text-muted">
                Version <?= APP_VERSION ?> | 
                <span id="currentDateTime"></span>
            </small>
        </div>
    </div>
</div>

<!-- Quick Actions Modals -->
<div class="modal fade" id="quickActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="quickActionsContent">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
// Update current date/time
function updateDateTime() {
    const now = new Date();
    const options = { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: true
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleTimeString('en-US', options);
}

// Update time every minute
setInterval(updateDateTime, 60000);
updateDateTime();

// Quick Actions Functions
function openQuickAdd(type) {
    const modal = new bootstrap.Modal(document.getElementById('quickActionsModal'));
    const content = document.getElementById('quickActionsContent');
    
    switch(type) {
        case 'trip':
            content.innerHTML = `
                <form id="quickTripForm">
                    <div class="mb-3">
                        <label class="form-label">Bus</label>
                        <select class="form-select" required>
                            <option value="">Select Bus</option>
                            <!-- Options will be loaded via AJAX -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Route</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Trip</button>
                </form>
            `;
            break;
            
        case 'violation':
            content.innerHTML = `
                <form id="quickViolationForm">
                    <div class="mb-3">
                        <label class="form-label">Employee Type</label>
                        <select class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="Driver">Driver</option>
                            <option value="Conductor">Conductor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Violation Type</label>
                        <select class="form-select" required>
                            <option value="">Select Violation</option>
                            <option value="Overspeeding">Overspeeding</option>
                            <option value="Reckless Driving">Reckless Driving</option>
                            <option value="Late Departure">Late Departure</option>
                            <option value="Traffic Violation">Traffic Violation</option>
                            <option value="Misconduct">Misconduct</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-warning">Report Violation</button>
                </form>
            `;
            break;
    }
    
    modal.show();
}

function showFleetStatus() {
    // This would typically load real-time fleet status
    alert('Fleet Status: 4 Active, 1 In Maintenance, 0 On Trip');
}
</script>