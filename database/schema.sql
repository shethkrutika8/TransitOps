-- 1. Roles table
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` TEXT NULL, -- JSON formatted list of permission keys
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `role_id` INT NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Active', -- Active, Suspended
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Vehicles table
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `registration_number` VARCHAR(30) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL, -- Truck, Van, Trailer, Car, etc.
  `manufacturer` VARCHAR(100) NOT NULL,
  `year` INT NOT NULL,
  `capacity` DECIMAL(10, 2) NOT NULL, -- Max Load Capacity in kg
  `odometer` INT NOT NULL DEFAULT 0,
  `purchase_cost` DECIMAL(12, 2) NOT NULL,
  `purchase_date` DATE NOT NULL,
  `insurance_expiry` DATE NOT NULL,
  `rc_expiry` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Available', -- Available, On Trip, In Shop, Retired
  `image_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. Drivers table
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `employee_id` VARCHAR(30) NOT NULL UNIQUE,
  `license_number` VARCHAR(50) NOT NULL,
  `license_category` VARCHAR(30) NOT NULL,
  `license_expiry` DATE NOT NULL,
  `mobile` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `address` TEXT NULL,
  `safety_score` DECIMAL(5, 2) DEFAULT 100.00, -- Safety score out of 100
  `experience` INT NOT NULL, -- Experience in years
  `emergency_contact` VARCHAR(100) NULL,
  `status` VARCHAR(20) DEFAULT 'Available', -- Available, On Trip, Off Duty, Suspended
  `photo_path` VARCHAR(255) NULL,
  `license_path` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Trips table
CREATE TABLE IF NOT EXISTS `trips` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `trip_number` VARCHAR(50) NOT NULL UNIQUE, -- E.g. TRP-YYYYMMDD-XXXX
  `source` VARCHAR(150) NOT NULL,
  `destination` VARCHAR(150) NOT NULL,
  `vehicle_id` INT NOT NULL,
  `driver_id` INT NOT NULL,
  `cargo_type` VARCHAR(100) NOT NULL,
  `cargo_weight` DECIMAL(10, 2) NOT NULL,
  `planned_distance` DECIMAL(8, 2) NOT NULL,
  `start_date` DATETIME NOT NULL,
  `expected_end_date` DATETIME NOT NULL,
  `revenue` DECIMAL(12, 2) NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Draft', -- Draft, Dispatched, Completed, Cancelled
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Trip Logs table
CREATE TABLE IF NOT EXISTS `trip_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `trip_id` INT NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `notes` TEXT NULL,
  `updated_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Maintenance table
CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` INT NOT NULL,
  `type` VARCHAR(50) NOT NULL, -- Routine, Repair, Inspection, Accident, etc.
  `description` TEXT NOT NULL,
  `date` DATE NOT NULL,
  `cost` DECIMAL(10, 2) NOT NULL,
  `vendor` VARCHAR(100) NOT NULL,
  `next_service_km` INT NOT NULL,
  `next_service_date` DATE NOT NULL,
  `status` VARCHAR(20) DEFAULT 'Pending', -- Pending, In Progress, Completed
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 8. Fuel Logs table
CREATE TABLE IF NOT EXISTS `fuel_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `quantity` DECIMAL(8, 2) NOT NULL, -- in Litres
  `cost` DECIMAL(10, 2) NOT NULL,
  `station` VARCHAR(100) NOT NULL,
  `odometer` INT NOT NULL,
  `mileage` DECIMAL(8, 2) NOT NULL DEFAULT 0.00, -- km/L
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Expenses table
CREATE TABLE IF NOT EXISTS `expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `vehicle_id` INT NOT NULL,
  `type` VARCHAR(30) NOT NULL, -- Toll, Parking, Repairs, Insurance, Tax, Other
  `amount` DECIMAL(10, 2) NOT NULL,
  `date` DATE NOT NULL,
  `remarks` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` VARCHAR(20) DEFAULT 'info', -- info, warning, danger, success
  `is_read` TINYINT DEFAULT 0,
  `related_id` INT NULL,
  `related_type` VARCHAR(50) NULL, -- Vehicle, Driver, Maintenance, etc.
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. Documents table
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `entity_type` VARCHAR(30) NOT NULL, -- Vehicle, Driver
  `entity_id` INT NOT NULL,
  `doc_type` VARCHAR(50) NOT NULL, -- Insurance, RC, License, etc.
  `file_path` VARCHAR(255) NOT NULL,
  `expiry_date` DATE NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 12. Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 13. Audit Logs table
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NULL,
  `entity_id` INT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Indexes for performance
CREATE INDEX idx_vehicles_status ON vehicles(status);
CREATE INDEX idx_drivers_status ON drivers(status);
CREATE INDEX idx_trips_status ON trips(status);
CREATE INDEX idx_maintenance_status ON maintenance(status);
CREATE INDEX idx_fuel_logs_vehicle ON fuel_logs(vehicle_id);
CREATE INDEX idx_expenses_vehicle ON expenses(vehicle_id);
CREATE INDEX idx_notifications_unread ON notifications(is_read);
