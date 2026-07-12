-- TransitOps database schema
-- MySQL 8+, InnoDB, UTF8MB4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS transitops;
CREATE DATABASE transitops CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE transitops;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  display_name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'viewer',
  status VARCHAR(20) NOT NULL DEFAULT 'Active',
  last_login TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_role (role),
  INDEX idx_users_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_roles (
  user_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicle_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE regions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  registration_number VARCHAR(50) NOT NULL UNIQUE,
  make VARCHAR(100) NOT NULL,
  model VARCHAR(100) NOT NULL,
  year INT NOT NULL,
  vehicle_type_id INT UNSIGNED NOT NULL,
  capacity_kg INT NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Active',
  region_id INT UNSIGNED NOT NULL,
  assigned_driver_id INT UNSIGNED NULL,
  fuel_efficiency DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  purchase_cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicles_type FOREIGN KEY (vehicle_type_id) REFERENCES vehicle_types(id),
  CONSTRAINT fk_vehicles_region FOREIGN KEY (region_id) REFERENCES regions(id),
  INDEX idx_vehicles_status (status),
  INDEX idx_vehicles_region (region_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE vehicle_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  document_type VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicle_documents_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE drivers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  license_number VARCHAR(50) NOT NULL UNIQUE,
  license_expiry DATE NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Available',
  region_id INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_drivers_region FOREIGN KEY (region_id) REFERENCES regions(id),
  INDEX idx_drivers_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE driver_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  driver_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  document_type VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_driver_documents_driver FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE trips (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_number VARCHAR(50) NOT NULL UNIQUE,
  vehicle_id INT UNSIGNED NOT NULL,
  driver_id INT UNSIGNED NOT NULL,
  cargo_weight_kg INT NOT NULL,
  origin VARCHAR(150) NOT NULL,
  destination VARCHAR(150) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Scheduled',
  revenue DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_trips_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id),
  CONSTRAINT fk_trips_driver FOREIGN KEY (driver_id) REFERENCES drivers(id),
  INDEX idx_trips_status (status),
  INDEX idx_trips_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE trip_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  trip_id INT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL,
  note VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_trip_history_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE maintenance_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  description VARCHAR(255) NOT NULL,
  cost DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  status VARCHAR(30) NOT NULL DEFAULT 'Pending',
  scheduled_date DATE NOT NULL,
  completed_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_maintenance_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  INDEX idx_maintenance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE fuel_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT UNSIGNED NOT NULL,
  quantity_liters DECIMAL(8,2) NOT NULL,
  cost DECIMAL(12,2) NOT NULL,
  logged_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fuel_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  expense_type VARCHAR(50) NOT NULL,
  description VARCHAR(255) NULL,
  expense_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_expenses_date (expense_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(100) NOT NULL UNIQUE,
  value_text TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE activity_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(150) NOT NULL,
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE login_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  success TINYINT(1) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_login_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  data TEXT NULL,
  expires_at INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at TIMESTAMP NOT NULL,
  used TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE email_templates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  subject VARCHAR(255) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE VIEW vw_dashboard_summary AS
SELECT
  (SELECT COUNT(*) FROM vehicles WHERE status = 'Active') AS active_vehicles,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'Active') AS available_vehicles,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'In Shop') AS vehicles_in_maintenance,
  (SELECT COUNT(*) FROM vehicles WHERE status = 'Retired') AS retired_vehicles,
  (SELECT COUNT(*) FROM drivers WHERE status = 'Available') AS drivers_available,
  (SELECT COUNT(*) FROM drivers WHERE status = 'On Trip') AS drivers_on_trip,
  (SELECT COUNT(*) FROM drivers WHERE status = 'Suspended') AS drivers_suspended,
  (SELECT COUNT(*) FROM trips WHERE status = 'In Progress') AS trips_running,
  (SELECT COUNT(*) FROM trips WHERE status = 'Completed') AS trips_completed,
  (SELECT COUNT(*) FROM trips WHERE status = 'Cancelled') AS trips_cancelled;

DELIMITER $$
CREATE TRIGGER before_insert_vehicle
BEFORE INSERT ON vehicles
FOR EACH ROW
BEGIN
  IF NEW.registration_number = '' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Registration number is required';
  END IF;
END$$

CREATE TRIGGER before_insert_driver
BEFORE INSERT ON drivers
FOR EACH ROW
BEGIN
  IF NEW.license_number = '' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'License number is required';
  END IF;
END$$
DELIMITER ;

INSERT INTO roles (name, display_name) VALUES
('admin','Administrator'),
('fleet_manager','Fleet Manager'),
('operations','Operations'),
('driver','Driver'),
('viewer','Viewer');

INSERT INTO permissions (name) VALUES
('dashboard'),('vehicles'),('drivers'),('trips'),('maintenance'),('fuel'),('expenses'),('reports'),('analytics'),('notifications'),('users'),('settings');

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('dashboard','vehicles','drivers','trips','maintenance','fuel','expenses','reports','analytics','notifications') WHERE r.name='admin';
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('dashboard','vehicles','drivers','trips','maintenance','fuel','expenses','reports','analytics','notifications') WHERE r.name='fleet_manager';
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('dashboard','vehicles','drivers','trips','maintenance','fuel','expenses','reports','analytics','notifications') WHERE r.name='operations';
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('dashboard') WHERE r.name='driver';
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM roles r JOIN permissions p ON p.name IN ('dashboard','reports') WHERE r.name='viewer';

INSERT INTO vehicle_types (name) VALUES ('Truck'),('Van'),('Bus'),('Courier');
INSERT INTO regions (name) VALUES ('North Zone'),('South Zone'),('East Zone'),('West Zone');

INSERT INTO users (full_name,email,password_hash,role,status) VALUES
('System Administrator','admin@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','admin','Active');

INSERT INTO settings (key_name, value_text) VALUES
('company_name','TransitOps'),
('company_email','hello@transitops.com'),
('company_phone','+91 9876543210');

INSERT INTO email_templates (name, subject, body) VALUES
('welcome','Welcome to TransitOps','Hello {name}, welcome to TransitOps.');

-- Enterprise extensions. These retain the original application-facing columns
-- while adding the operational, audit and reporting model required by TransitOps.
ALTER TABLE roles ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active', ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN first_name VARCHAR(75) NULL, ADD COLUMN last_name VARCHAR(75) NULL, ADD COLUMN phone VARCHAR(25) NULL, ADD COLUMN avatar_path VARCHAR(255) NULL, ADD COLUMN address TEXT NULL, ADD COLUMN remember_token CHAR(64) NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL;
ALTER TABLE regions ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active', ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE vehicle_types ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active', ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE vehicles ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN vehicle_name VARCHAR(150) NULL, ADD COLUMN manufacturer VARCHAR(100) NULL, ADD COLUMN fuel_type VARCHAR(30) NOT NULL DEFAULT 'Diesel', ADD COLUMN engine_number VARCHAR(100) NULL UNIQUE, ADD COLUMN chassis_number VARCHAR(100) NULL UNIQUE, ADD COLUMN odometer_km DECIMAL(12,2) NOT NULL DEFAULT 0, ADD COLUMN purchase_date DATE NULL, ADD COLUMN insurance_number VARCHAR(100) NULL, ADD COLUMN insurance_expiry DATE NULL, ADD COLUMN fitness_expiry DATE NULL, ADD COLUMN permit_expiry DATE NULL, ADD COLUMN pollution_expiry DATE NULL, ADD COLUMN photo_path VARCHAR(255) NULL, ADD COLUMN remarks TEXT NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_vehicle_capacity CHECK (capacity_kg > 0), ADD CONSTRAINT chk_vehicle_cost CHECK (purchase_cost >= 0);
ALTER TABLE drivers ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN license_category VARCHAR(50) NULL, ADD COLUMN license_issue_date DATE NULL, ADD COLUMN joining_date DATE NULL, ADD COLUMN experience_years DECIMAL(4,1) NOT NULL DEFAULT 0, ADD COLUMN address TEXT NULL, ADD COLUMN blood_group VARCHAR(8) NULL, ADD COLUMN emergency_contact VARCHAR(25) NULL, ADD COLUMN safety_score DECIMAL(5,2) NOT NULL DEFAULT 100, ADD COLUMN photo_path VARCHAR(255) NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_driver_safety CHECK (safety_score BETWEEN 0 AND 100);
ALTER TABLE trips ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN planned_distance_km DECIMAL(10,2) NOT NULL DEFAULT 0, ADD COLUMN actual_distance_km DECIMAL(10,2) NULL, ADD COLUMN dispatch_at DATETIME NULL, ADD COLUMN arrival_at DATETIME NULL, ADD COLUMN start_odometer DECIMAL(12,2) NULL, ADD COLUMN end_odometer DECIMAL(12,2) NULL, ADD COLUMN fuel_used_liters DECIMAL(10,2) NULL, ADD COLUMN mileage_kmpl DECIMAL(8,2) NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_trip_cargo CHECK (cargo_weight_kg > 0), ADD CONSTRAINT chk_trip_revenue CHECK (revenue >= 0);
ALTER TABLE maintenance_logs ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN maintenance_type VARCHAR(80) NOT NULL DEFAULT 'General', ADD COLUMN vendor_name VARCHAR(150) NULL, ADD COLUMN mechanic_name VARCHAR(150) NULL, ADD COLUMN invoice_path VARCHAR(255) NULL, ADD COLUMN start_date DATE NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_maintenance_cost CHECK (cost >= 0);
ALTER TABLE fuel_logs ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN trip_id INT UNSIGNED NULL, ADD COLUMN fuel_station_id INT UNSIGNED NULL, ADD COLUMN fuel_type VARCHAR(30) NOT NULL DEFAULT 'Diesel', ADD COLUMN price_per_liter DECIMAL(10,2) NOT NULL DEFAULT 0, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_fuel_quantity CHECK (quantity_liters > 0), ADD CONSTRAINT chk_fuel_cost CHECK (cost >= 0);
ALTER TABLE expenses ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN vehicle_id INT UNSIGNED NULL, ADD COLUMN trip_id INT UNSIGNED NULL, ADD COLUMN expense_category_id INT UNSIGNED NULL, ADD COLUMN deleted_at TIMESTAMP NULL, ADD COLUMN created_by INT UNSIGNED NULL, ADD COLUMN updated_by INT UNSIGNED NULL, ADD CONSTRAINT chk_expense_amount CHECK (amount >= 0);
ALTER TABLE notifications ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'Normal', ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, ADD COLUMN deleted_at TIMESTAMP NULL;
ALTER TABLE settings ADD COLUMN uuid CHAR(36) NULL UNIQUE AFTER id, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active', ADD COLUMN deleted_at TIMESTAMP NULL;

CREATE TABLE expense_categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, name VARCHAR(100) NOT NULL UNIQUE, description VARCHAR(255) NULL, status VARCHAR(20) NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL, created_by INT UNSIGNED NULL, updated_by INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE fuel_stations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL, address VARCHAR(255) NULL, city VARCHAR(100) NULL, contact_phone VARCHAR(25) NULL, status VARCHAR(20) NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL, created_by INT UNSIGNED NULL, updated_by INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE company (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, name VARCHAR(150) NOT NULL, email VARCHAR(150) NULL, phone VARCHAR(25) NULL, address TEXT NULL, logo_path VARCHAR(255) NULL, currency_code CHAR(3) NOT NULL DEFAULT 'INR', timezone_name VARCHAR(64) NOT NULL DEFAULT 'Asia/Kolkata', distance_unit VARCHAR(10) NOT NULL DEFAULT 'km', fuel_unit VARCHAR(10) NOT NULL DEFAULT 'litre', status VARCHAR(20) NOT NULL DEFAULT 'Active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL, created_by INT UNSIGNED NULL, updated_by INT UNSIGNED NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, user_id INT UNSIGNED NULL, module_name VARCHAR(80) NOT NULL, record_uuid CHAR(36) NULL, action VARCHAR(50) NOT NULL, before_value JSON NULL, after_value JSON NULL, ip_address VARCHAR(45) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_audit_module (module_name, created_at), CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE system_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, level_name VARCHAR(20) NOT NULL, message TEXT NOT NULL, context_json JSON NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_system_level (level_name, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE dashboard_cache (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, cache_key VARCHAR(120) NOT NULL UNIQUE, payload JSON NOT NULL, expires_at DATETIME NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_cache_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE report_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, uuid CHAR(36) NOT NULL UNIQUE, user_id INT UNSIGNED NULL, report_type VARCHAR(80) NOT NULL, filters_json JSON NULL, file_path VARCHAR(255) NULL, generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, CONSTRAINT fk_report_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE fuel_logs ADD CONSTRAINT fk_fuel_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON UPDATE CASCADE ON DELETE RESTRICT, ADD CONSTRAINT fk_fuel_station FOREIGN KEY (fuel_station_id) REFERENCES fuel_stations(id) ON UPDATE CASCADE ON DELETE RESTRICT;
ALTER TABLE expenses ADD CONSTRAINT fk_expense_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT, ADD CONSTRAINT fk_expense_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON UPDATE CASCADE ON DELETE RESTRICT, ADD CONSTRAINT fk_expense_category FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON UPDATE CASCADE ON DELETE RESTRICT;

-- Assign immutable identifiers after all existing and new rows are present.
UPDATE roles SET uuid = UUID() WHERE uuid IS NULL; UPDATE users SET uuid = UUID() WHERE uuid IS NULL; UPDATE regions SET uuid = UUID() WHERE uuid IS NULL; UPDATE vehicle_types SET uuid = UUID() WHERE uuid IS NULL;
UPDATE vehicles SET uuid = UUID() WHERE uuid IS NULL; UPDATE drivers SET uuid = UUID() WHERE uuid IS NULL; UPDATE trips SET uuid = UUID() WHERE uuid IS NULL; UPDATE maintenance_logs SET uuid = UUID() WHERE uuid IS NULL; UPDATE fuel_logs SET uuid = UUID() WHERE uuid IS NULL; UPDATE expenses SET uuid = UUID() WHERE uuid IS NULL; UPDATE notifications SET uuid = UUID() WHERE uuid IS NULL; UPDATE settings SET uuid = UUID() WHERE uuid IS NULL;

INSERT INTO roles (uuid, name, display_name) VALUES (UUID(),'dispatcher','Dispatcher'),(UUID(),'safety_officer','Safety Officer'),(UUID(),'financial_analyst','Financial Analyst') ON DUPLICATE KEY UPDATE display_name=VALUES(display_name);
INSERT INTO permissions (name) VALUES ('roles'),('profile'),('activity_logs'),('backup'),('documents'),('company'),('email'),('security'),('vehicles.create'),('vehicles.update'),('trips.dispatch'),('trips.complete'),('reports.export'),('maintenance.complete'),('fuel.create'),('expenses.create'),('drivers.create'),('users.manage') ON DUPLICATE KEY UPDATE name=VALUES(name);
INSERT INTO expense_categories (uuid,name,description) VALUES (UUID(),'Toll','Road tolls'),(UUID(),'Parking','Parking charges'),(UUID(),'Meals','Driver meals'),(UUID(),'Repair','Unplanned repair'),(UUID(),'Insurance','Insurance premium');
INSERT INTO fuel_stations (uuid,name,city,contact_phone) VALUES (UUID(),'Northway Fuel Hub','Ahmedabad','+91 9876543210'),(UUID(),'Highway Energy Point','Vadodara','+91 9876543211'),(UUID(),'Westline Fuels','Surat','+91 9876543212');
INSERT INTO company (uuid,name,email,phone,address) VALUES (UUID(),'TransitOps Logistics','hello@transitops.com','+91 9876543210','Ahmedabad, Gujarat, India');
INSERT INTO users (uuid,first_name,last_name,full_name,email,password_hash,role,status,phone) VALUES
(UUID(),'Fleet','Manager','Fleet Manager','manager@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','fleet_manager','Active','+91 9000000001'),
(UUID(),'Dispatch','Operator','Dispatch Operator','dispatcher@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','dispatcher','Active','+91 9000000002'),
(UUID(),'Safety','Officer','Safety Officer','safety@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','safety_officer','Active','+91 9000000003'),
(UUID(),'Financial','Analyst','Financial Analyst','finance@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','financial_analyst','Active','+91 9000000004');

DELIMITER $$
CREATE TRIGGER trg_trip_before_dispatch BEFORE UPDATE ON trips FOR EACH ROW BEGIN
 IF NEW.status = 'Dispatched' AND OLD.status <> 'Dispatched' THEN
  IF (SELECT status FROM vehicles WHERE id=NEW.vehicle_id) <> 'Available' OR (SELECT status FROM drivers WHERE id=NEW.driver_id) <> 'Available' OR (SELECT license_expiry FROM drivers WHERE id=NEW.driver_id) < CURDATE() THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Vehicle or driver is unavailable for dispatch'; END IF;
  IF NEW.cargo_weight_kg > (SELECT capacity_kg FROM vehicles WHERE id=NEW.vehicle_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cargo weight exceeds vehicle capacity'; END IF;
 END IF;
END$$
CREATE TRIGGER trg_trip_after_status AFTER UPDATE ON trips FOR EACH ROW BEGIN
 IF NEW.status='Dispatched' AND OLD.status<>'Dispatched' THEN UPDATE vehicles SET status='On Trip' WHERE id=NEW.vehicle_id; UPDATE drivers SET status='On Trip' WHERE id=NEW.driver_id; INSERT INTO trip_history (trip_id,status,note) VALUES (NEW.id,'Dispatched','Trip dispatched'); END IF;
 IF NEW.status='Completed' AND OLD.status<>'Completed' THEN UPDATE vehicles SET status='Available' WHERE id=NEW.vehicle_id; UPDATE drivers SET status='Available' WHERE id=NEW.driver_id; INSERT INTO trip_history (trip_id,status,note) VALUES (NEW.id,'Completed','Trip completed'); END IF;
 IF NEW.status='Cancelled' AND OLD.status<>'Cancelled' THEN UPDATE vehicles SET status='Available' WHERE id=NEW.vehicle_id AND status='On Trip'; UPDATE drivers SET status='Available' WHERE id=NEW.driver_id AND status='On Trip'; INSERT INTO trip_history (trip_id,status,note) VALUES (NEW.id,'Cancelled','Trip cancelled'); END IF;
END$$
CREATE TRIGGER trg_maintenance_status AFTER UPDATE ON maintenance_logs FOR EACH ROW BEGIN
 IF NEW.status='In Progress' AND OLD.status<>'In Progress' THEN UPDATE vehicles SET status='In Shop' WHERE id=NEW.vehicle_id; END IF;
 IF NEW.status='Completed' AND OLD.status<>'Completed' THEN UPDATE vehicles SET status='Available' WHERE id=NEW.vehicle_id AND status='In Shop'; END IF;
END$$
CREATE PROCEDURE sp_dispatch_trip(IN p_trip_id INT UNSIGNED) BEGIN UPDATE trips SET status='Dispatched',dispatch_at=COALESCE(dispatch_at,NOW()) WHERE id=p_trip_id AND status='Draft'; END$$
CREATE PROCEDURE sp_complete_trip(IN p_trip_id INT UNSIGNED, IN p_end_odometer DECIMAL(12,2)) BEGIN UPDATE trips SET status='Completed',arrival_at=NOW(),end_odometer=p_end_odometer,actual_distance_km=CASE WHEN start_odometer IS NULL THEN actual_distance_km ELSE p_end_odometer-start_odometer END WHERE id=p_trip_id AND status='Dispatched'; END$$
CREATE PROCEDURE sp_cancel_trip(IN p_trip_id INT UNSIGNED) BEGIN UPDATE trips SET status='Cancelled' WHERE id=p_trip_id AND status IN ('Draft','Dispatched'); END$$
DELIMITER ;

-- Deterministic sample data for a ready-to-demo fresh installation.
DELIMITER $$
CREATE PROCEDURE sp_seed_demo_data()
BEGIN
 DECLARE i INT DEFAULT 1;
 IF (SELECT COUNT(*) FROM vehicles) = 0 THEN
  WHILE i <= 25 DO
   INSERT INTO vehicles (uuid,registration_number,vehicle_name,make,model,year,vehicle_type_id,capacity_kg,status,region_id,fuel_efficiency,purchase_cost,odometer_km)
   VALUES (UUID(),CONCAT('GJ-01-TO-',LPAD(i,4,'0')),CONCAT('Fleet Unit ',i),IF(MOD(i,2)=0,'Tata','Ashok Leyland'),CONCAT('Model ',MOD(i,5)+1),2020+MOD(i,5),MOD(i-1,4)+1,1500+(MOD(i,5)*500),'Available',MOD(i-1,4)+1,11.5,1200000+(i*25000),15000+(i*1200));
   SET i=i+1;
  END WHILE;
 END IF;
 SET i=1;
 IF (SELECT COUNT(*) FROM drivers) = 0 THEN
  WHILE i <= 30 DO
   INSERT INTO drivers (uuid,full_name,email,phone,license_number,license_expiry,status,region_id,license_category,joining_date,safety_score)
   VALUES (UUID(),CONCAT('Driver ',LPAD(i,2,'0')),CONCAT('driver',i,'@transitops.demo'),CONCAT('+91 80000',LPAD(i,5,'0')),CONCAT('GJDL',LPAD(i,8,'0')),DATE_ADD(CURDATE(),INTERVAL 2 YEAR),'Available',MOD(i-1,4)+1,'HMV',DATE_SUB(CURDATE(),INTERVAL (i+1) YEAR),75+MOD(i,25));
   SET i=i+1;
  END WHILE;
 END IF;
 SET i=1;
 IF (SELECT COUNT(*) FROM trips) = 0 THEN
  WHILE i <= 80 DO
   INSERT INTO trips (uuid,trip_number,vehicle_id,driver_id,cargo_weight_kg,origin,destination,start_date,end_date,status,revenue,planned_distance_km,actual_distance_km)
   VALUES (UUID(),CONCAT('TRP-',DATE_FORMAT(CURDATE(),'%Y'),'-',LPAD(i,4,'0')),MOD(i-1,25)+1,MOD(i-1,30)+1,500+MOD(i,8)*100,ELT(MOD(i-1,4)+1,'Ahmedabad','Vadodara','Surat','Rajkot'),ELT(MOD(i,4)+1,'Mumbai','Pune','Udaipur','Indore'),DATE_SUB(CURDATE(),INTERVAL (81-i) DAY),CASE WHEN MOD(i,8)<6 THEN DATE_SUB(CURDATE(),INTERVAL (80-i) DAY) ELSE NULL END,CASE WHEN MOD(i,8)<6 THEN 'Completed' WHEN MOD(i,8)=6 THEN 'Draft' ELSE 'Cancelled' END,18000+MOD(i,10)*1250,180+MOD(i,6)*35,180+MOD(i,6)*35);
   SET i=i+1;
  END WHILE;
 END IF;
 SET i=1;
 IF (SELECT COUNT(*) FROM fuel_logs) = 0 THEN
  WHILE i <= 50 DO
   INSERT INTO fuel_logs (uuid,vehicle_id,trip_id,fuel_station_id,fuel_type,quantity_liters,price_per_liter,cost,logged_date)
   VALUES (UUID(),MOD(i-1,25)+1,MOD(i-1,80)+1,MOD(i-1,3)+1,'Diesel',45+MOD(i,20),91.50,(45+MOD(i,20))*91.50,DATE_SUB(CURDATE(),INTERVAL i DAY));
   SET i=i+1;
  END WHILE;
 END IF;
 SET i=1;
 IF (SELECT COUNT(*) FROM maintenance_logs) = 0 THEN
  WHILE i <= 40 DO
   INSERT INTO maintenance_logs (uuid,vehicle_id,maintenance_type,vendor_name,description,cost,status,scheduled_date,completed_date)
   VALUES (UUID(),MOD(i-1,25)+1,ELT(MOD(i-1,4)+1,'Preventive','Tyres','Oil Service','Repair'),'Transit Service Centre',CONCAT('Scheduled maintenance #',i),2500+MOD(i,8)*850,'Completed',DATE_SUB(CURDATE(),INTERVAL (i+10) DAY),DATE_SUB(CURDATE(),INTERVAL i DAY));
   SET i=i+1;
  END WHILE;
 END IF;
 SET i=1;
 IF (SELECT COUNT(*) FROM expenses) = 0 THEN
  WHILE i <= 60 DO
   INSERT INTO expenses (uuid,vehicle_id,trip_id,expense_category_id,title,amount,expense_type,description,expense_date)
   VALUES (UUID(),MOD(i-1,25)+1,MOD(i-1,80)+1,MOD(i-1,5)+1,CONCAT('Operational expense ',i),500+MOD(i,12)*175,ELT(MOD(i-1,5)+1,'Toll','Parking','Meals','Repair','Insurance'),'Demo operational expense',DATE_SUB(CURDATE(),INTERVAL i DAY));
   SET i=i+1;
  END WHILE;
 END IF;
 INSERT INTO notifications (uuid,user_id,title,message,priority) SELECT UUID(),id,'Welcome to TransitOps','Your enterprise transport workspace is ready.','Normal' FROM users WHERE NOT EXISTS (SELECT 1 FROM notifications);
END$$
DELIMITER ;
CALL sp_seed_demo_data();

-- Authentication audit fields used by the hardened PHP session and login layer.
ALTER TABLE login_logs ADD COLUMN browser VARCHAR(255) NULL AFTER ip_address, ADD COLUMN login_at DATETIME NULL AFTER success, ADD COLUMN logout_at DATETIME NULL AFTER login_at, ADD INDEX idx_login_ip_time (ip_address, created_at);

-- Driver-management profile, document and compliance metadata.
ALTER TABLE drivers ADD COLUMN employee_id VARCHAR(50) NULL UNIQUE AFTER uuid, ADD COLUMN date_of_birth DATE NULL, ADD COLUMN gender VARCHAR(20) NULL, ADD COLUMN city VARCHAR(100) NULL, ADD COLUMN state_name VARCHAR(100) NULL, ADD COLUMN country_name VARCHAR(100) NOT NULL DEFAULT 'India', ADD COLUMN postal_code VARCHAR(20) NULL, ADD COLUMN emergency_contact_name VARCHAR(150) NULL, ADD COLUMN medical_certificate_expiry DATE NULL, ADD COLUMN police_verification_date DATE NULL, ADD COLUMN aadhaar_number VARCHAR(20) NULL UNIQUE, ADD COLUMN pan_number VARCHAR(20) NULL UNIQUE, ADD COLUMN remarks TEXT NULL, ADD INDEX idx_driver_license_expiry (license_expiry), ADD INDEX idx_driver_status_safety (status, safety_score);
ALTER TABLE driver_documents ADD COLUMN expiry_date DATE NULL, ADD COLUMN uploaded_by INT UNSIGNED NULL, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Active', ADD CONSTRAINT fk_driver_document_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE maintenance_logs ADD COLUMN maintenance_code VARCHAR(50) NULL UNIQUE AFTER uuid, ADD COLUMN priority VARCHAR(20) NOT NULL DEFAULT 'Medium', ADD COLUMN estimated_cost DECIMAL(12,2) NULL, ADD COLUMN expected_completion_date DATE NULL, ADD COLUMN remarks TEXT NULL, ADD COLUMN photo_path VARCHAR(255) NULL, ADD INDEX idx_maintenance_schedule (status, scheduled_date), ADD INDEX idx_maintenance_priority (priority, status);
ALTER TABLE fuel_logs ADD COLUMN fuel_code VARCHAR(50) NULL UNIQUE AFTER uuid, ADD COLUMN odometer_reading DECIMAL(12,2) NULL, ADD COLUMN filled_by VARCHAR(150) NULL, ADD COLUMN payment_method VARCHAR(30) NULL, ADD COLUMN bill_number VARCHAR(100) NULL, ADD COLUMN receipt_path VARCHAR(255) NULL, ADD COLUMN remarks TEXT NULL, ADD INDEX idx_fuel_vehicle_date (vehicle_id, logged_date);
ALTER TABLE expenses ADD COLUMN expense_code VARCHAR(50) NULL UNIQUE AFTER uuid, ADD COLUMN vendor_name VARCHAR(150) NULL, ADD COLUMN invoice_number VARCHAR(100) NULL, ADD COLUMN invoice_path VARCHAR(255) NULL, ADD COLUMN remarks TEXT NULL, ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'Recorded', ADD INDEX idx_expense_vehicle_date (vehicle_id, expense_date);

CREATE OR REPLACE VIEW vw_dashboard AS SELECT (SELECT COUNT(*) FROM vehicles WHERE status='Available' AND deleted_at IS NULL) active_vehicles,(SELECT COUNT(*) FROM drivers WHERE status='Available' AND deleted_at IS NULL) drivers_available,(SELECT COUNT(*) FROM trips WHERE status='Dispatched' AND deleted_at IS NULL) trips_running,(SELECT COUNT(*) FROM trips WHERE status='Completed' AND deleted_at IS NULL) trips_completed,(SELECT COUNT(*) FROM maintenance_logs WHERE status IN ('Pending','In Progress') AND deleted_at IS NULL) vehicles_in_maintenance;
CREATE OR REPLACE VIEW vw_vehicle_summary AS SELECT v.id,v.uuid,v.registration_number,COALESCE(v.vehicle_name,CONCAT(v.make,' ',v.model)) vehicle_name,v.status,r.name region,COUNT(t.id) trip_count FROM vehicles v JOIN regions r ON r.id=v.region_id LEFT JOIN trips t ON t.vehicle_id=v.id AND t.deleted_at IS NULL WHERE v.deleted_at IS NULL GROUP BY v.id;
CREATE OR REPLACE VIEW vw_driver_summary AS SELECT d.id,d.uuid,d.full_name,d.license_number,d.license_expiry,d.status,r.name region,COUNT(t.id) trip_count FROM drivers d JOIN regions r ON r.id=d.region_id LEFT JOIN trips t ON t.driver_id=d.id AND t.deleted_at IS NULL WHERE d.deleted_at IS NULL GROUP BY d.id;
CREATE OR REPLACE VIEW vw_trip_summary AS SELECT t.*,v.registration_number,d.full_name driver_name FROM trips t JOIN vehicles v ON v.id=t.vehicle_id JOIN drivers d ON d.id=t.driver_id WHERE t.deleted_at IS NULL;
CREATE OR REPLACE VIEW vw_fuel_summary AS SELECT vehicle_id,SUM(quantity_liters) liters,SUM(cost) total_cost FROM fuel_logs WHERE deleted_at IS NULL GROUP BY vehicle_id;
CREATE OR REPLACE VIEW vw_expense_summary AS SELECT expense_type,SUM(amount) total_amount FROM expenses WHERE deleted_at IS NULL GROUP BY expense_type;
CREATE OR REPLACE VIEW vw_profit AS SELECT COALESCE((SELECT SUM(revenue) FROM trips WHERE status='Completed'),0)-COALESCE((SELECT SUM(cost) FROM fuel_logs WHERE deleted_at IS NULL),0)-COALESCE((SELECT SUM(amount) FROM expenses WHERE deleted_at IS NULL),0)-COALESCE((SELECT SUM(cost) FROM maintenance_logs WHERE deleted_at IS NULL),0) net_profit;
CREATE OR REPLACE VIEW vw_roi AS SELECT COALESCE((SELECT SUM(revenue) FROM trips WHERE status='Completed'),0) revenue,COALESCE((SELECT SUM(purchase_cost) FROM vehicles WHERE deleted_at IS NULL),0) asset_cost;
CREATE OR REPLACE VIEW vw_maintenance AS SELECT m.*,v.registration_number FROM maintenance_logs m JOIN vehicles v ON v.id=m.vehicle_id WHERE m.deleted_at IS NULL;

-- Status values used by the operational triggers and the existing PHP dashboard.
ALTER TABLE vehicles MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Available';
ALTER TABLE trips MODIFY status VARCHAR(30) NOT NULL DEFAULT 'Draft';
CREATE OR REPLACE VIEW vw_dashboard_summary AS SELECT
 (SELECT COUNT(*) FROM vehicles WHERE status IN ('Available','On Trip') AND deleted_at IS NULL) active_vehicles,
 (SELECT COUNT(*) FROM vehicles WHERE status='Available' AND deleted_at IS NULL) available_vehicles,
 (SELECT COUNT(*) FROM vehicles WHERE status='In Shop' AND deleted_at IS NULL) vehicles_in_maintenance,
 (SELECT COUNT(*) FROM vehicles WHERE status='Retired' AND deleted_at IS NULL) retired_vehicles,
 (SELECT COUNT(*) FROM drivers WHERE status='Available' AND deleted_at IS NULL) drivers_available,
 (SELECT COUNT(*) FROM drivers WHERE status='On Trip' AND deleted_at IS NULL) drivers_on_trip,
 (SELECT COUNT(*) FROM drivers WHERE status='Suspended' AND deleted_at IS NULL) drivers_suspended,
 (SELECT COUNT(*) FROM trips WHERE status='Dispatched' AND deleted_at IS NULL) trips_running,
 (SELECT COUNT(*) FROM trips WHERE status='Completed' AND deleted_at IS NULL) trips_completed,
 (SELECT COUNT(*) FROM trips WHERE status='Cancelled' AND deleted_at IS NULL) trips_cancelled;

DELIMITER $$
CREATE TRIGGER trg_fuel_audit AFTER INSERT ON fuel_logs FOR EACH ROW BEGIN
 INSERT INTO activity_logs (user_id,action,description) VALUES (NEW.created_by,'Fuel recorded',CONCAT('Fuel log #',NEW.id,' recorded for vehicle #',NEW.vehicle_id));
END$$
CREATE TRIGGER trg_expense_audit AFTER INSERT ON expenses FOR EACH ROW BEGIN
 INSERT INTO activity_logs (user_id,action,description) VALUES (NEW.created_by,'Expense recorded',CONCAT('Expense #',NEW.id,' recorded: ',NEW.title));
END$$
CREATE PROCEDURE sp_create_vehicle(IN p_registration VARCHAR(50),IN p_make VARCHAR(100),IN p_model VARCHAR(100),IN p_year INT,IN p_type INT UNSIGNED,IN p_capacity INT,IN p_region INT UNSIGNED)
BEGIN INSERT INTO vehicles (uuid,registration_number,make,model,year,vehicle_type_id,capacity_kg,region_id,status) VALUES (UUID(),p_registration,p_make,p_model,p_year,p_type,p_capacity,p_region,'Available'); END$$
CREATE PROCEDURE sp_create_driver(IN p_name VARCHAR(150),IN p_email VARCHAR(150),IN p_phone VARCHAR(20),IN p_license VARCHAR(50),IN p_expiry DATE,IN p_region INT UNSIGNED)
BEGIN INSERT INTO drivers (uuid,full_name,email,phone,license_number,license_expiry,region_id,status) VALUES (UUID(),p_name,p_email,p_phone,p_license,p_expiry,p_region,'Available'); END$$
CREATE PROCEDURE sp_create_trip(IN p_trip_number VARCHAR(50),IN p_vehicle INT UNSIGNED,IN p_driver INT UNSIGNED,IN p_cargo INT,IN p_origin VARCHAR(150),IN p_destination VARCHAR(150),IN p_start DATE,IN p_revenue DECIMAL(12,2))
BEGIN INSERT INTO trips (uuid,trip_number,vehicle_id,driver_id,cargo_weight_kg,origin,destination,start_date,revenue,status) VALUES (UUID(),p_trip_number,p_vehicle,p_driver,p_cargo,p_origin,p_destination,p_start,p_revenue,'Draft'); END$$
CREATE PROCEDURE sp_create_maintenance(IN p_vehicle INT UNSIGNED,IN p_description VARCHAR(255),IN p_cost DECIMAL(12,2),IN p_date DATE)
BEGIN INSERT INTO maintenance_logs (uuid,vehicle_id,description,cost,scheduled_date,status) VALUES (UUID(),p_vehicle,p_description,p_cost,p_date,'Pending'); END$$
CREATE PROCEDURE sp_complete_maintenance(IN p_maintenance INT UNSIGNED)
BEGIN UPDATE maintenance_logs SET status='Completed',completed_date=CURDATE() WHERE id=p_maintenance AND status IN ('Pending','In Progress'); END$$
CREATE PROCEDURE sp_add_fuel(IN p_vehicle INT UNSIGNED,IN p_liters DECIMAL(8,2),IN p_cost DECIMAL(12,2),IN p_date DATE)
BEGIN INSERT INTO fuel_logs (uuid,vehicle_id,quantity_liters,cost,price_per_liter,logged_date) VALUES (UUID(),p_vehicle,p_liters,p_cost,p_cost/p_liters,p_date); END$$
CREATE PROCEDURE sp_add_expense(IN p_title VARCHAR(150),IN p_amount DECIMAL(12,2),IN p_type VARCHAR(50),IN p_date DATE)
BEGIN INSERT INTO expenses (uuid,title,amount,expense_type,expense_date) VALUES (UUID(),p_title,p_amount,p_type,p_date); END$$
CREATE PROCEDURE sp_generate_dashboard()
BEGIN SELECT * FROM vw_dashboard; END$$
CREATE PROCEDURE sp_calculate_fleet_utilization()
BEGIN SELECT COUNT(*) total_vehicles,SUM(status='On Trip') vehicles_on_trip,ROUND(SUM(status='On Trip')*100/NULLIF(COUNT(*),0),2) utilization_percent FROM vehicles WHERE deleted_at IS NULL; END$$
CREATE PROCEDURE sp_calculate_roi()
BEGIN SELECT revenue,asset_cost,ROUND((revenue-asset_cost)*100/NULLIF(asset_cost,0),2) roi_percent FROM vw_roi; END$$
CREATE PROCEDURE sp_calculate_fuel_efficiency()
BEGIN SELECT v.id,v.registration_number,ROUND(SUM(t.actual_distance_km)/NULLIF(SUM(f.quantity_liters),0),2) km_per_litre FROM vehicles v LEFT JOIN trips t ON t.vehicle_id=v.id LEFT JOIN fuel_logs f ON f.vehicle_id=v.id GROUP BY v.id; END$$
DELIMITER ;
