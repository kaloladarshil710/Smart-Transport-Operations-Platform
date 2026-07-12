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
('System Administrator','admin@transitops.com','$2y$10$1ANfy60nPt0ndsmYQzKSYu5F2m2B2liYPWXQFyxam3tdQOCAyzp2e','admin','Active');

INSERT INTO settings (key_name, value_text) VALUES
('company_name','TransitOps'),
('company_email','hello@transitops.com'),
('company_phone','+91 9876543210');

INSERT INTO email_templates (name, subject, body) VALUES
('welcome','Welcome to TransitOps','Hello {name}, welcome to TransitOps.');
