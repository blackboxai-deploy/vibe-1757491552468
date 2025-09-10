<?php
/**
 * GV Florida Fleet Management System
 * Header Component
 */

require_once __DIR__ . '/../config/config.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' - ' : '' ?><?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --sidebar-width: 260px;
            --header-height: 70px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            height: var(--header-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header .navbar {
            padding: 0.5rem 1rem;
            height: 100%;
        }
        
        .header .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.2rem;
            text-decoration: none;
        }
        
        .header .navbar-brand:hover {
            color: #ecf0f1 !important;
        }
        
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.2rem;
            margin-right: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            color: #ecf0f1;
            transform: scale(1.1);
        }
        
        .user-dropdown .dropdown-toggle {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            border-radius: 25px;
            padding: 0.5rem 1rem;
            text-decoration: none;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .user-dropdown .dropdown-toggle::after {
            margin-left: 0.5rem;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            height: calc(100vh - var(--header-height));
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
            z-index: 1020;
            transition: transform 0.3s ease;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-header h5 {
            margin: 0;
            font-weight: 600;
            color: #ecf0f1;
        }
        
        .sidebar-header small {
            color: #bdc3c7;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .nav-link {
            color: #bdc3c7 !important;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-radius: 0;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
            padding-left: 2rem;
        }
        
        .nav-link.active {
            background: linear-gradient(90deg, var(--accent-color), rgba(52, 152, 219, 0.8));
            color: white !important;
            border-left: 4px solid #ffffff;
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 0.75rem;
            text-align: center;
        }
        
        .nav-section {
            padding: 0.5rem 1.5rem;
            color: #95a5a6;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s ease;
            padding: 2rem;
        }
        
        .main-content.sidebar-collapsed {
            margin-left: 0;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
            padding: 1rem 1.5rem;
        }
        
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.05));
            z-index: 1;
        }
        
        .stat-card > * {
            position: relative;
            z-index: 2;
        }
        
        .stat-card.primary { background: linear-gradient(135deg, #3498db, #2980b9); }
        .stat-card.success { background: linear-gradient(135deg, #27ae60, #229954); }
        .stat-card.warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .stat-card.danger { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .stat-card.info { background: linear-gradient(135deg, #17a2b8, #138496); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
        }
        
        /* Button Styles */
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        /* Table Styles */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            font-weight: 600;
            padding: 1rem 0.75rem;
        }
        
        .table tbody td {
            border-color: #f1f3f4;
            padding: 0.875rem 0.75rem;
        }
        
        /* Modal Styles */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 1px solid #dee2e6;
            border-radius: 12px 12px 0 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .header .navbar-brand {
                font-size: 1rem;
            }
        }
        
        /* Loading States */
        .loading {
            position: relative;
            pointer-events: none;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Utility Classes */
        .text-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
        }
    </style>
    
    <?php if (isset($additionalStyles)): ?>
        <?= $additionalStyles ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <nav class="navbar navbar-expand-lg h-100">
            <button class="sidebar-toggle" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-bus me-2"></i>
                <?= APP_NAME ?>
            </a>
            
            <div class="ms-auto">
                <div class="dropdown user-dropdown">
                    <a class="dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= $_SESSION['full_name'] ?>
                        <span class="badge bg-primary ms-2"><?= $_SESSION['role'] ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Account Options</h6></li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i>Profile Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i>Preferences
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>