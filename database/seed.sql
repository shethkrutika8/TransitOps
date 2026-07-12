USE `transitops`;

-- Seed Roles
INSERT INTO `roles` (`id`, `name`, `permissions`) VALUES
(1, 'Super Admin', '["all"]'),
(2, 'Fleet Manager', '["dashboard_view", "vehicles_manage", "drivers_manage", "maintenance_manage", "fuel_manage", "expenses_manage", "reports_view"]'),
(3, 'Dispatcher', '["dashboard_view", "trips_manage", "vehicles_view", "drivers_view"]'),
(4, 'Safety Officer', '["dashboard_view", "drivers_manage", "maintenance_manage", "notifications_view"]'),
(5, 'Finance Manager', '["dashboard_view", "fuel_manage", "expenses_manage", "reports_manage"]');

-- Seed Users (Password is 'admin123' for all)
-- Hash generated for 'admin123': $2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB
INSERT INTO `users` (`id`, `email`, `password`, `name`, `role_id`, `status`) VALUES
(1, 'admin@transitops.com', '$2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB', 'John Admin', 1, 'Active'),
(2, 'manager@transitops.com', '$2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB', 'Alice Manager', 2, 'Active'),
(3, 'dispatcher@transitops.com', '$2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB', 'Bob Dispatcher', 3, 'Active'),
(4, 'safety@transitops.com', '$2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB', 'Charlie Safety', 4, 'Active'),
(5, 'finance@transitops.com', '$2y$10$sP3gV45G04bM2Lq1Q8R3eeL4Q6nF5m6Xv/X7Y5tBv.9kL9mK6rWvB', 'David Finance', 5, 'Active');

-- Seed Settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'TransitOps'),
('company_name', 'TransitOps Logistics Ltd.'),
('currency', 'USD'),
('timezone', 'UTC'),
('license_warning_days', '30'),
('insurance_warning_days', '30');

-- Seed Vehicles (Vibrant demo fleet)
INSERT INTO `vehicles` (`id`, `registration_number`, `name`, `model`, `type`, `manufacturer`, `year`, `capacity`, `odometer`, `purchase_cost`, `purchase_date`, `insurance_expiry`, `rc_expiry`, `status`) VALUES
(1, 'TX-1029-A', 'Freightliner Cascadia', 'Cascadia 126', 'Truck', 'Freightliner', 2022, 18000.00, 45200, 145000.00, '2022-04-15', '2026-08-15', '2027-04-15', 'Available'),
(2, 'CA-8832-B', 'Volvo FH16', 'FH16 Globetrotter', 'Truck', 'Volvo', 2021, 20000.00, 68900, 160000.00, '2021-09-10', '2026-09-10', '2026-09-10', 'On Trip'),
(3, 'NY-4402-C', 'Ford Transit Cargo', 'Transit 350', 'Van', 'Ford', 2023, 3500.00, 12400, 48000.00, '2023-01-20', '2026-07-20', '2028-01-20', 'In Shop'),
(4, 'FL-5021-D', 'Peterbilt 579', '579 Ultralight', 'Truck', 'Peterbilt', 2020, 19000.00, 120500, 138000.00, '2020-06-05', '2026-06-05', '2025-06-05', 'Available'), -- Expired insurance for notification testing
(5, 'IL-7762-E', 'Mercedes-Benz Sprinter', 'Sprinter 2500', 'Van', 'Mercedes-Benz', 2022, 4000.00, 31000, 52000.00, '2022-11-12', '2026-11-12', '2027-11-12', 'Retired');

-- Seed Drivers (Demo driving force)
INSERT INTO `drivers` (`id`, `name`, `employee_id`, `license_number`, `license_category`, `license_expiry`, `mobile`, `email`, `address`, `safety_score`, `experience`, `emergency_contact`, `status`) VALUES
(1, 'Michael Smith', 'EMP-001', 'DL-9988221', 'Heavy Commercial', '2028-05-15', '+1-555-0199', 'michael.s@transitops.com', '123 Main St, Dallas, TX', 96.50, 8, 'Jane Smith (+1-555-0198)', 'Available'),
(2, 'Robert Johnson', 'EMP-002', 'DL-4433110', 'Heavy Commercial', '2027-11-20', '+1-555-0288', 'robert.j@transitops.com', '456 Oak Ave, Houston, TX', 92.00, 12, 'Mary Johnson (+1-555-0287)', 'On Trip'),
(3, 'William Davis', 'EMP-003', 'DL-2233445', 'Commercial', '2026-06-10', '+1-555-0377', 'william.d@transitops.com', '789 Pine Rd, Austin, TX', 78.00, 5, 'Sarah Davis (+1-555-0376)', 'Available'), -- Expired license for notifications
(4, 'James Wilson', 'EMP-004', 'DL-5544332', 'Commercial', '2029-01-30', '+1-555-0466', 'james.w@transitops.com', '321 Elm St, San Antonio, TX', 98.00, 10, 'Linda Wilson (+1-555-0465)', 'Suspended'),
(5, 'Patricia Taylor', 'EMP-005', 'DL-7788990', 'Light Commercial', '2027-08-14', '+1-555-0555', 'patricia.t@transitops.com', '654 Maple Dr, Dallas, TX', 94.00, 4, 'John Taylor (+1-555-0554)', 'Available');

-- Seed Trips
INSERT INTO `trips` (`id`, `trip_number`, `source`, `destination`, `vehicle_id`, `driver_id`, `cargo_type`, `cargo_weight`, `planned_distance`, `start_date`, `expected_end_date`, `revenue`, `status`) VALUES
(1, 'TRP-20260710-0001', 'Dallas Warehouse', 'Houston Distribution Center', 1, 1, 'Electronics', 8500.00, 240.00, '2026-07-10 08:00:00', '2026-07-10 14:00:00', 1200.00, 'Completed'),
(2, 'TRP-20260712-0002', 'Houston Port', 'Austin Fulfillment Hub', 2, 2, 'Industrial Equipment', 15000.00, 160.00, '2026-07-12 07:00:00', '2026-07-12 11:30:00', 1850.00, 'Dispatched'),
(3, 'TRP-20260713-0003', 'Fort Worth Logistics', 'Dallas Warehouse', 1, 5, 'Automotive Parts', 5000.00, 35.00, '2026-07-13 09:00:00', '2026-07-13 11:00:00', 450.00, 'Draft');

-- Seed Trip Logs
INSERT INTO `trip_logs` (`trip_id`, `status`, `notes`, `updated_by`) VALUES
(1, 'Draft', 'Trip created in draft mode.', 3),
(1, 'Dispatched', 'Vehicle dispatched and left source warehouse.', 3),
(1, 'Completed', 'Trip completed successfully. Cargo delivered.', 3),
(2, 'Draft', 'Trip draft prepared.', 3),
(2, 'Dispatched', 'Dispatched by Bob.', 3);

-- Seed Maintenance Logs
INSERT INTO `maintenance` (`id`, `vehicle_id`, `type`, `description`, `date`, `cost`, `vendor`, `next_service_km`, `next_service_date`, `status`) VALUES
(1, '3', 'Repair', 'Engine misfire diagnosis, spark plugs replacement, and fuel system cleaning.', '2026-07-11', 450.00, 'Apex Auto Shop', 20000, '2026-10-11', 'In Progress'),
(2, '1', 'Routine', 'Routine 45,000 km engine oil, oil filter, air filter change and tire rotation.', '2026-06-15', 280.00, 'Quick Lube Pro', 50000, '2026-09-15', 'Completed');

-- Seed Fuel Logs
INSERT INTO `fuel_logs` (`vehicle_id`, `date`, `quantity`, `cost`, `station`, `odometer`, `mileage`) VALUES
(1, '2026-06-01', 120.00, 480.00, 'Texaco Star', 44000, 0.00),
(1, '2026-06-10', 115.00, 465.00, 'Shell Express', 44690, 6.00), -- 690 km / 115 L = 6.00 km/L
(1, '2026-07-02', 130.00, 520.00, 'Chevron Depot', 45200, 3.92), -- 510 km / 130 L = 3.92 km/L
(2, '2026-07-05', 210.00, 840.00, 'Pilots Travel Center', 68900, 0.00);

-- Seed Expenses
INSERT INTO `expenses` (`vehicle_id`, `type`, `amount`, `date`, `remarks`) VALUES
(1, 'Toll', 75.00, '2026-07-10', 'Highway 45 Toll charges for Trip TRP-20260710-0001'),
(1, 'Repairs', 280.00, '2026-06-15', 'Oils and filter replacement'),
(3, 'Repairs', 450.00, '2026-07-11', 'Apex Auto Shop diagnostic invoice'),
(2, 'Parking', 40.00, '2026-07-12', 'Overnight secure parking fee at Houston Port'),
(4, 'Insurance', 1200.00, '2026-05-01', 'Annual insurance renewal partial payment');

-- Seed Notifications
INSERT INTO `notifications` (`title`, `message`, `type`, `is_read`, `related_id`, `related_type`) VALUES
('Driver License Expiry Alert', 'Driver William Davis (EMP-003) has an expired commercial license (Expired: 2026-06-10).', 'danger', 0, 3, 'Driver'),
('Vehicle Insurance Expiration Alert', 'Vehicle FL-5021-D has an expired insurance certificate (Expired: 2026-06-05).', 'danger', 0, 4, 'Vehicle'),
('Vehicle Maintenance Scheduled', 'Vehicle NY-4402-C is currently in shop for engine diagnostic repair.', 'warning', 0, 3, 'Maintenance');
