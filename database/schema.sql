-- GV Florida Fleet Management System
-- Database Schema
-- MySQL Database: fleetdb

CREATE DATABASE IF NOT EXISTS fleetdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fleetdb;

-- Users table for authentication and user management
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Staff') DEFAULT 'Staff',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Buses table for fleet vehicle management
CREATE TABLE buses (
    bus_id INT AUTO_INCREMENT PRIMARY KEY,
    bus_number VARCHAR(20) UNIQUE NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    model VARCHAR(100) NOT NULL,
    year YEAR NOT NULL,
    color VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    status ENUM('Active', 'In Maintenance', 'Retired') DEFAULT 'Active',
    last_maintenance DATE,
    next_maintenance DATE,
    fuel_type ENUM('Diesel', 'Gasoline', 'Electric', 'Hybrid') DEFAULT 'Diesel',
    mileage DECIMAL(10,2) DEFAULT 0,
    purchase_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Drivers table for driver information and licensing
CREATE TABLE drivers (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    license_expiry DATE NOT NULL,
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    hire_date DATE NOT NULL,
    experience_years INT DEFAULT 0,
    status ENUM('Active', 'On Leave', 'Suspended', 'Terminated') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Conductors table for conductor staff management
CREATE TABLE conductors (
    conductor_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    date_of_birth DATE,
    contact_phone VARCHAR(20),
    contact_email VARCHAR(100),
    address TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone VARCHAR(20),
    hire_date DATE NOT NULL,
    shift_schedule ENUM('Morning', 'Afternoon', 'Evening', 'Night', 'Rotating') DEFAULT 'Morning',
    status ENUM('Active', 'On Leave', 'Suspended', 'Terminated') DEFAULT 'Active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Assignments table for bus-driver-conductor assignments
CREATE TABLE assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    driver_id INT NOT NULL,
    conductor_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    shift_type ENUM('Morning', 'Afternoon', 'Evening', 'Night') NOT NULL,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (conductor_id) REFERENCES conductors(conductor_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Trips table for trip recording and tracking
CREATE TABLE trips (
    trip_id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    driver_id INT NOT NULL,
    conductor_id INT NOT NULL,
    route VARCHAR(255) NOT NULL,
    trip_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME,
    start_location VARCHAR(255) NOT NULL,
    end_location VARCHAR(255) NOT NULL,
    distance_km DECIMAL(8,2),
    fuel_consumed DECIMAL(8,2),
    passenger_count INT DEFAULT 0,
    fare_collected DECIMAL(10,2) DEFAULT 0,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    remarks TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(driver_id) ON DELETE CASCADE,
    FOREIGN KEY (conductor_id) REFERENCES conductors(conductor_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Violations table for violation tracking and management
CREATE TABLE violations (
    violation_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_type ENUM('Driver', 'Conductor') NOT NULL,
    employee_id VARCHAR(20) NOT NULL,
    bus_id INT,
    violation_type ENUM('Overspeeding', 'Reckless Driving', 'Late Departure', 'Traffic Violation', 'Misconduct', 'Attendance Issue', 'Customer Complaint', 'Equipment Misuse', 'Other') NOT NULL,
    violation_date DATE NOT NULL,
    violation_time TIME,
    location VARCHAR(255),
    description TEXT NOT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    penalty_amount DECIMAL(10,2) DEFAULT 0,
    action_taken TEXT,
    status ENUM('Open', 'Under Investigation', 'Resolved', 'Dismissed') DEFAULT 'Open',
    resolved_date DATE NULL,
    resolved_by INT NULL,
    reported_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE SET NULL,
    FOREIGN KEY (resolved_by) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Maintenance Records table
CREATE TABLE maintenance_records (
    maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    maintenance_type ENUM('Routine', 'Emergency', 'Preventive', 'Repair') DEFAULT 'Routine',
    maintenance_date DATE NOT NULL,
    description TEXT NOT NULL,
    cost DECIMAL(10,2) DEFAULT 0,
    service_provider VARCHAR(255),
    next_service_date DATE,
    status ENUM('Scheduled', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    performed_by VARCHAR(255),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Create indexes for better performance
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_employee_id ON users(employee_id);
CREATE INDEX idx_buses_status ON buses(status);
CREATE INDEX idx_buses_plate_number ON buses(plate_number);
CREATE INDEX idx_drivers_license ON drivers(license_number);
CREATE INDEX idx_drivers_status ON drivers(status);
CREATE INDEX idx_conductors_status ON conductors(status);
CREATE INDEX idx_assignments_bus_id ON assignments(bus_id);
CREATE INDEX idx_assignments_status ON assignments(status);
CREATE INDEX idx_trips_date ON trips(trip_date);
CREATE INDEX idx_trips_bus_id ON trips(bus_id);
CREATE INDEX idx_violations_date ON violations(violation_date);
CREATE INDEX idx_violations_employee ON violations(employee_type, employee_id);
CREATE INDEX idx_violations_status ON violations(status);
CREATE INDEX idx_maintenance_bus_id ON maintenance_records(bus_id);
CREATE INDEX idx_maintenance_date ON maintenance_records(maintenance_date);

-- Insert default admin user
INSERT INTO users (employee_id, first_name, last_name, username, password, role, status) 
VALUES ('ADMIN001', 'System', 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Active');
-- Default password: password

-- Insert sample data for testing
INSERT INTO users (employee_id, first_name, middle_name, last_name, username, password, role) VALUES
('EMP001', 'Maria', 'Santos', 'Rodriguez', 'maria.rodriguez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff'),
('EMP002', 'John', 'Michael', 'Smith', 'john.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff'),
('EMP003', 'Ana', 'Isabel', 'Garcia', 'ana.garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

INSERT INTO buses (bus_number, plate_number, model, year, color, capacity, status, fuel_type) VALUES
('GVF001', 'ABC-1234', 'Mercedes-Benz Sprinter', 2022, 'White', 25, 'Active', 'Diesel'),
('GVF002', 'ABC-1235', 'Ford Transit', 2021, 'Blue', 20, 'Active', 'Diesel'),
('GVF003', 'ABC-1236', 'Iveco Daily', 2020, 'White', 30, 'In Maintenance', 'Diesel'),
('GVF004', 'ABC-1237', 'Mercedes-Benz Sprinter', 2023, 'Red', 25, 'Active', 'Diesel'),
('GVF005', 'ABC-1238', 'Ford Transit', 2019, 'White', 20, 'Active', 'Diesel');

INSERT INTO drivers (employee_id, full_name, license_number, license_expiry, contact_phone, hire_date, experience_years) VALUES
('DRV001', 'Carlos Eduardo Martinez', 'DL123456789', '2025-12-31', '555-0101', '2020-01-15', 8),
('DRV002', 'Roberto Luis Fernandez', 'DL123456790', '2026-06-30', '555-0102', '2019-03-20', 12),
('DRV003', 'Miguel Angel Torres', 'DL123456791', '2025-09-15', '555-0103', '2021-07-10', 5),
('DRV004', 'Francisco Javier Morales', 'DL123456792', '2026-01-20', '555-0104', '2018-11-05', 15),
('DRV005', 'Diego Antonio Ruiz', 'DL123456793', '2025-04-30', '555-0105', '2022-02-14', 3);

INSERT INTO conductors (employee_id, full_name, contact_phone, hire_date, shift_schedule) VALUES
('CON001', 'Elena Sofia Valdez', '555-0201', '2020-02-01', 'Morning'),
('CON002', 'Carmen Patricia Lopez', '555-0202', '2019-05-15', 'Afternoon'),
('CON003', 'Lucia Fernanda Castro', '555-0203', '2021-08-20', 'Morning'),
('CON004', 'Isabel Maria Santos', '555-0204', '2020-10-10', 'Evening'),
('CON005', 'Rosa Elena Jimenez', '555-0205', '2022-01-30', 'Rotating');

INSERT INTO assignments (bus_id, driver_id, conductor_id, start_date, shift_type, created_by) VALUES
(1, 1, 1, '2024-01-01', 'Morning', 1),
(2, 2, 2, '2024-01-01', 'Afternoon', 1),
(4, 3, 3, '2024-01-15', 'Morning', 1),
(5, 4, 4, '2024-01-20', 'Evening', 1);

INSERT INTO violations (employee_type, employee_id, bus_id, violation_type, violation_date, description, severity, penalty_amount, reported_by) VALUES
('Driver', 'DRV001', 1, 'Overspeeding', '2024-01-15', 'Exceeded speed limit by 15 km/h on Highway 95', 'Medium', 150.00, 1),
('Conductor', 'CON002', 2, 'Customer Complaint', '2024-01-20', 'Passenger complained about rude behavior during fare collection', 'Low', 50.00, 1),
('Driver', 'DRV003', 4, 'Late Departure', '2024-01-25', 'Departed 20 minutes late from main terminal', 'Low', 25.00, 1);