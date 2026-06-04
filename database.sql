-- REMS Database Schema
-- Run this SQL to set up the database

CREATE DATABASE IF NOT EXISTS rems_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rems_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties table
CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    property_name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip VARCHAR(20) NOT NULL,
    property_type ENUM('apartment', 'house', 'commercial', 'condo') NOT NULL,
    total_units INT DEFAULT 0,
    occupied_units INT DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    owner_id INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Units table
CREATE TABLE units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    unit_number VARCHAR(20) NOT NULL,
    type ENUM('studio', '1br', '2br', '3br', 'commercial') NOT NULL,
    sqft INT NOT NULL,
    rent DECIMAL(10,2) NOT NULL,
    status ENUM('occupied', 'vacant', 'maintenance') DEFAULT 'vacant',
    features TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Tenants table
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    property_id INT NOT NULL,
    unit_id INT NOT NULL,
    lease_start DATE NOT NULL,
    lease_end DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Leases table
CREATE TABLE leases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    unit_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    monthly_rent DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'expired', 'pending', 'terminated') DEFAULT 'pending',
    document VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    unit_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    type ENUM('rent', 'deposit', 'fee', 'other') NOT NULL,
    method ENUM('cash', 'check', 'card', 'bank_transfer') NOT NULL,
    status ENUM('paid', 'pending', 'failed', 'refunded') DEFAULT 'pending',
    reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- Maintenance requests table
CREATE TABLE maintenance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    unit_id INT NOT NULL,
    tenant_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'completed', 'cancelled') DEFAULT 'open',
    assigned_to VARCHAR(100) DEFAULT NULL,
    estimated_cost DECIMAL(10,2) DEFAULT NULL,
    actual_cost DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@rems.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Default password is 'password'

-- Insert sample properties
INSERT INTO properties (name, address, city, state, zip, type, total_units, occupied_units, image, status) VALUES
('Sunrise Apartments', '123 Main Street', 'Los Angeles', 'CA', '90001', 'apartment', 24, 21, 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=400', 'active'),
('Oak Hill Residences', '456 Oak Avenue', 'San Francisco', 'CA', '94102', 'condo', 12, 10, 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400', 'active'),
('Downtown Commercial Center', '789 Business Blvd', 'San Diego', 'CA', '92101', 'commercial', 8, 6, 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=400', 'active'),
('Palm Gardens', '321 Palm Drive', 'Miami', 'FL', '33101', 'apartment', 36, 32, 'https://images.unsplash.com/photo-1567496898669-ee935f5f647a?w=400', 'active'),
('Mountain View Houses', '555 Highland Road', 'Denver', 'CO', '80201', 'house', 6, 5, 'https://images.unsplash.com/photo-1564013799919-ab6000fcffc6?w=400', 'active');

-- Insert sample units
INSERT INTO units (property_id, unit_number, type, sqft, rent, status, features) VALUES
(1, '101', 'studio', 450, 1200.00, 'occupied', 'Parking,Laundry'),
(1, '102', '1br', 650, 1500.00, 'vacant', 'Parking,Balcony,Laundry'),
(1, '201', '2br', 850, 1900.00, 'occupied', 'Parking,Balcony,Gym'),
(2, 'A1', '1br', 700, 2200.00, 'occupied', 'Pool,Gym,Concierge'),
(2, 'B2', '2br', 950, 2800.00, 'vacant', 'Pool,Gym,Balcony'),
(3, 'C1', 'commercial', 1500, 4500.00, 'occupied', 'Parking,Security'),
(4, '301', '3br', 1200, 2500.00, 'occupied', 'Pool,Parking,Garden'),
(5, 'H1', '3br', 1800, 3200.00, 'occupied', 'Garage,Garden,Fireplace');

-- Insert sample tenants
INSERT INTO tenants (name, email, phone, property_id, unit_id, lease_start, lease_end, status) VALUES
('John Smith', 'john.smith@email.com', '(555) 123-4567', 1, 1, '2024-01-15', '2025-01-14', 'active'),
('Sarah Johnson', 'sarah.j@email.com', '(555) 234-5678', 1, 3, '2024-03-01', '2025-02-28', 'active'),
('Michael Brown', 'm.brown@email.com', '(555) 345-6789', 2, 4, '2023-06-01', '2024-05-31', 'active'),
('Emily Davis', 'emily.d@email.com', '(555) 456-7890', 3, 6, '2024-02-01', '2026-01-31', 'active'),
('Robert Wilson', 'r.wilson@email.com', '(555) 567-8901', 4, 7, '2023-11-01', '2024-10-31', 'active'),
('Lisa Anderson', 'lisa.a@email.com', '(555) 678-9012', 5, 8, '2024-04-01', '2025-03-31', 'active');

-- Insert sample leases
INSERT INTO leases (tenant_id, property_id, unit_id, start_date, end_date, monthly_rent, security_deposit, status) VALUES
(1, 1, 1, '2024-01-15', '2025-01-14', 1200.00, 2400.00, 'active'),
(2, 1, 3, '2024-03-01', '2025-02-28', 1900.00, 3800.00, 'active'),
(3, 2, 4, '2023-06-01', '2024-05-31', 2200.00, 4400.00, 'expired'),
(4, 3, 6, '2024-02-01', '2026-01-31', 4500.00, 9000.00, 'active'),
(5, 4, 7, '2023-11-01', '2024-10-31', 2500.00, 5000.00, 'active'),
(6, 5, 8, '2024-04-01', '2025-03-31', 3200.00, 6400.00, 'active');

-- Insert sample payments
INSERT INTO payments (tenant_id, property_id, unit_id, amount, payment_date, type, method, status, reference) VALUES
(1, 1, 1, 1200.00, '2024-05-01', 'rent', 'bank_transfer', 'paid', 'TXN001'),
(2, 1, 3, 1900.00, '2024-05-01', 'rent', 'card', 'paid', 'TXN002'),
(3, 2, 4, 2200.00, '2024-05-03', 'rent', 'check', 'pending', NULL),
(4, 3, 6, 4500.00, '2024-05-01', 'rent', 'bank_transfer', 'paid', 'TXN003'),
(5, 4, 7, 2500.00, '2024-05-02', 'rent', 'card', 'paid', 'TXN004'),
(6, 5, 8, 3200.00, '2024-05-05', 'rent', 'bank_transfer', 'pending', NULL),
(1, 1, 1, 100.00, '2024-05-10', 'fee', 'cash', 'paid', NULL);

-- Insert sample maintenance requests
INSERT INTO maintenance_requests (property_id, unit_id, tenant_id, title, description, priority, status, estimated_cost) VALUES
(1, 1, 1, 'Leaking Faucet', 'Kitchen faucet has been dripping constantly for the past 2 days.', 'medium', 'open', 150.00),
(2, 4, 3, 'AC Not Working', 'Air conditioning unit stopped working. Temperature is very high.', 'high', 'in_progress', 500.00),
(4, 7, 5, 'Broken Window Lock', 'Window lock in bedroom is broken and window won\'t close properly.', 'low', 'completed', NULL),
(5, 8, 6, 'Water Heater Issue', 'No hot water coming from taps. Urgent repair needed.', 'urgent', 'open', 800.00),
(3, 6, 4, 'Electrical Outlet Not Working', 'One of the electrical outlets in the main office area is not functioning.', 'medium', 'in_progress', 200.00);
