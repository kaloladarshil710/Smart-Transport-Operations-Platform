-- TransitOps | Smart Transport Operations Platform
-- MySQL 8.0+ | Single, directly importable phpMyAdmin schema and demo dataset

DROP DATABASE IF EXISTS `transitops`;
CREATE DATABASE `transitops` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `transitops`;
SET NAMES utf8mb4;

CREATE TABLE roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, slug VARCHAR(80) NOT NULL,
  description VARCHAR(255) NULL, status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_roles_name (name), UNIQUE KEY uq_roles_slug (slug), KEY idx_roles_status (status)
) ENGINE=InnoDB;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL,
  email VARCHAR(190) NOT NULL, password VARCHAR(255) NOT NULL, phone VARCHAR(25) NULL, avatar VARCHAR(255) NULL,
  last_login DATETIME NULL, remember_token VARCHAR(100) NULL, is_active TINYINT(1) NOT NULL DEFAULT 1,
  status ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active', created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_users_email (email), KEY idx_users_email (email), KEY idx_users_status (status),
  CONSTRAINT chk_users_active CHECK (is_active IN (0,1))
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, `key` VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL, status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active', created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_permissions_name (name), UNIQUE KEY uq_permissions_key (`key`), KEY idx_permissions_status (status)
) ENGINE=InnoDB;

CREATE TABLE user_roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, role_id BIGINT UNSIGNED NOT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_user_roles (user_id,role_id), KEY idx_ur_role (role_id),
  CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, role_id BIGINT UNSIGNED NOT NULL, permission_id BIGINT UNSIGNED NOT NULL,
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_role_permissions (role_id,permission_id), KEY idx_rp_permission (permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_rp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE vehicles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, registration_number VARCHAR(40) NOT NULL, vehicle_name VARCHAR(120) NOT NULL,
  model VARCHAR(120) NOT NULL, manufacturer VARCHAR(120) NOT NULL, vehicle_type VARCHAR(60) NOT NULL, fuel_type VARCHAR(40) NOT NULL,
  load_capacity DECIMAL(12,2) NOT NULL, engine_number VARCHAR(80) NULL, chassis_number VARCHAR(80) NULL, purchase_date DATE NULL,
  purchase_cost DECIMAL(14,2) NOT NULL DEFAULT 0, insurance_number VARCHAR(80) NULL, insurance_expiry DATE NULL, fitness_expiry DATE NULL,
  pollution_expiry DATE NULL, odometer DECIMAL(12,2) NOT NULL DEFAULT 0, current_region VARCHAR(100) NULL,
  status ENUM('Available','On Trip','In Shop','Retired') NOT NULL DEFAULT 'Available', vehicle_photo VARCHAR(255) NULL, remarks TEXT NULL,
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_vehicles_registration (registration_number), UNIQUE KEY uq_vehicles_engine (engine_number), UNIQUE KEY uq_vehicles_chassis (chassis_number),
  KEY idx_vehicle_registration (registration_number), KEY idx_vehicle_status (status),
  CONSTRAINT chk_vehicle_capacity CHECK (load_capacity > 0), CONSTRAINT chk_vehicle_cost CHECK (purchase_cost >= 0), CONSTRAINT chk_vehicle_odometer CHECK (odometer >= 0),
  CONSTRAINT fk_vehicles_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_vehicles_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE vehicle_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, vehicle_id BIGINT UNSIGNED NOT NULL, document_type ENUM('RC','Insurance','Fitness','Pollution','Permit','Other') NOT NULL,
  document_name VARCHAR(150) NOT NULL, document_file VARCHAR(255) NOT NULL, expiry_date DATE NULL, status ENUM('Active','Expired','Archived') NOT NULL DEFAULT 'Active',
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_vd_vehicle (vehicle_id), KEY idx_vd_expiry (expiry_date),
  CONSTRAINT fk_vd_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE drivers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, driver_name VARCHAR(160) NOT NULL, license_number VARCHAR(60) NOT NULL, license_category VARCHAR(30) NOT NULL,
  license_issue_date DATE NULL, license_expiry_date DATE NOT NULL, experience_years DECIMAL(4,1) NOT NULL DEFAULT 0, joining_date DATE NULL,
  blood_group VARCHAR(8) NULL, phone VARCHAR(25) NOT NULL, email VARCHAR(190) NULL, address TEXT NULL, emergency_contact VARCHAR(25) NOT NULL,
  safety_score DECIMAL(5,2) NOT NULL DEFAULT 100, status ENUM('Available','On Trip','Off Duty','Suspended') NOT NULL DEFAULT 'Available', photo VARCHAR(255) NULL,
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_drivers_license (license_number), UNIQUE KEY uq_drivers_email (email), KEY idx_driver_license (license_number), KEY idx_driver_status (status),
  CONSTRAINT chk_driver_experience CHECK (experience_years >= 0), CONSTRAINT chk_driver_safety CHECK (safety_score BETWEEN 0 AND 100),
  CONSTRAINT fk_drivers_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_drivers_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE driver_documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, driver_id BIGINT UNSIGNED NOT NULL,
  document_type ENUM('License Copy','Aadhaar','PAN','Medical Certificate','Other') NOT NULL, document_name VARCHAR(150) NOT NULL,
  document_file VARCHAR(255) NOT NULL, expiry_date DATE NULL, status ENUM('Active','Expired','Archived') NOT NULL DEFAULT 'Active',
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_dd_driver (driver_id), KEY idx_dd_expiry (expiry_date),
  CONSTRAINT fk_dd_driver FOREIGN KEY (driver_id) REFERENCES drivers(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE trips (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, trip_number VARCHAR(40) NOT NULL, vehicle_id BIGINT UNSIGNED NOT NULL, driver_id BIGINT UNSIGNED NOT NULL,
  source VARCHAR(160) NOT NULL, destination VARCHAR(160) NOT NULL, cargo_weight DECIMAL(12,2) NOT NULL, distance_km DECIMAL(12,2) NOT NULL,
  revenue DECIMAL(14,2) NOT NULL DEFAULT 0, dispatch_date DATETIME NULL, arrival_date DATETIME NULL, start_odometer DECIMAL(12,2) NULL,
  end_odometer DECIMAL(12,2) NULL, fuel_used DECIMAL(12,2) NULL, average_mileage DECIMAL(12,2) NULL,
  status ENUM('Draft','Dispatched','Completed','Cancelled') NOT NULL DEFAULT 'Draft', cancellation_reason VARCHAR(255) NULL,
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_trips_number (trip_number), KEY idx_trip_number (trip_number), KEY idx_trip_vehicle (vehicle_id), KEY idx_trip_driver (driver_id), KEY idx_trip_status (status),
  CONSTRAINT chk_trip_cargo CHECK (cargo_weight > 0), CONSTRAINT chk_trip_distance CHECK (distance_km > 0), CONSTRAINT chk_trip_revenue CHECK (revenue >= 0),
  CONSTRAINT chk_trip_fuel CHECK (fuel_used IS NULL OR fuel_used > 0), CONSTRAINT chk_trip_odo CHECK (end_odometer IS NULL OR start_odometer IS NULL OR end_odometer >= start_odometer),
  CONSTRAINT fk_trips_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_trips_driver FOREIGN KEY (driver_id) REFERENCES drivers(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE trip_history (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, trip_id BIGINT UNSIGNED NOT NULL, previous_status ENUM('Draft','Dispatched','Completed','Cancelled') NULL,
  new_status ENUM('Draft','Dispatched','Completed','Cancelled') NOT NULL, notes VARCHAR(500) NULL, changed_by BIGINT UNSIGNED NULL,
  status ENUM('Active','Archived') NOT NULL DEFAULT 'Active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_th_trip (trip_id), KEY idx_th_status (new_status),
  CONSTRAINT fk_th_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_th_user FOREIGN KEY (changed_by) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE maintenance_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, vehicle_id BIGINT UNSIGNED NOT NULL, maintenance_type VARCHAR(100) NOT NULL, description TEXT NULL,
  mechanic VARCHAR(120) NULL, vendor VARCHAR(120) NULL, cost DECIMAL(14,2) NOT NULL DEFAULT 0, start_date DATE NOT NULL, end_date DATE NULL,
  status ENUM('Scheduled','In Progress','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled', created_by BIGINT UNSIGNED NULL,
  updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_ml_vehicle (vehicle_id), KEY idx_ml_status (status), KEY idx_ml_start (start_date), CONSTRAINT chk_ml_cost CHECK (cost >= 0),
  CONSTRAINT fk_ml_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE fuel_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, vehicle_id BIGINT UNSIGNED NOT NULL, trip_id BIGINT UNSIGNED NULL, fuel_station VARCHAR(160) NOT NULL,
  liters DECIMAL(12,2) NOT NULL, cost DECIMAL(14,2) NOT NULL, rate_per_liter DECIMAL(12,2) NOT NULL, fuel_date DATE NOT NULL,
  status ENUM('Active','Voided') NOT NULL DEFAULT 'Active', created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_fl_vehicle (vehicle_id), KEY idx_fl_trip (trip_id), KEY idx_fl_date (fuel_date), CONSTRAINT chk_fl_liters CHECK (liters > 0),
  CONSTRAINT chk_fl_cost CHECK (cost >= 0), CONSTRAINT chk_fl_rate CHECK (rate_per_liter >= 0),
  CONSTRAINT fk_fl_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_fl_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE expenses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, vehicle_id BIGINT UNSIGNED NULL, trip_id BIGINT UNSIGNED NULL,
  expense_type ENUM('Fuel','Maintenance','Toll','Parking','Insurance','Repair','Fine','Miscellaneous') NOT NULL, expense_amount DECIMAL(14,2) NOT NULL,
  expense_date DATE NOT NULL, remarks TEXT NULL, status ENUM('Pending','Approved','Rejected','Paid') NOT NULL DEFAULT 'Pending',
  created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_exp_vehicle (vehicle_id), KEY idx_exp_trip (trip_id), KEY idx_exp_type (expense_type), KEY idx_exp_date (expense_date),
  CONSTRAINT chk_exp_amount CHECK (expense_amount >= 0), CONSTRAINT fk_exp_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_exp_trip FOREIGN KEY (trip_id) REFERENCES trips(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NOT NULL, title VARCHAR(180) NOT NULL, message TEXT NOT NULL,
  type ENUM('Info','Success','Warning','Danger') NOT NULL DEFAULT 'Info', is_read TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('Active','Archived') NOT NULL DEFAULT 'Active', created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_not_user_read (user_id,is_read), CONSTRAINT chk_not_read CHECK (is_read IN (0,1)),
  CONSTRAINT fk_not_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(100) NOT NULL, setting_value TEXT NULL, setting_group VARCHAR(80) NOT NULL DEFAULT 'general',
  status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active', created_by BIGINT UNSIGNED NULL, updated_by BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  UNIQUE KEY uq_settings_key (setting_key), KEY idx_settings_group (setting_group)
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, action VARCHAR(120) NOT NULL, module VARCHAR(80) NOT NULL,
  description TEXT NULL, ip_address VARCHAR(45) NULL, browser VARCHAR(255) NULL, status ENUM('Success','Failure','Info') NOT NULL DEFAULT 'Info',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_al_user (user_id), KEY idx_al_module (module), KEY idx_al_created (created_at),
  CONSTRAINT fk_al_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE login_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id BIGINT UNSIGNED NULL, ip_address VARCHAR(45) NULL, browser VARCHAR(255) NULL,
  login_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, logout_time DATETIME NULL, status ENUM('Success','Failed','Logged Out') NOT NULL DEFAULT 'Success',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_ll_user (user_id), KEY idx_ll_login (login_time), CONSTRAINT fk_ll_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE sessions (
  id VARCHAR(128) PRIMARY KEY, user_id BIGINT UNSIGNED NULL, ip_address VARCHAR(45) NULL, user_agent VARCHAR(255) NULL,
  payload MEDIUMTEXT NULL, last_activity DATETIME NOT NULL, status ENUM('Active','Expired','Revoked') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, deleted_at TIMESTAMP NULL,
  KEY idx_sessions_user (user_id), KEY idx_sessions_activity (last_activity),
  CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

DELIMITER $$
CREATE TRIGGER trg_trips_after_insert AFTER INSERT ON trips FOR EACH ROW BEGIN
  INSERT INTO trip_history (trip_id,previous_status,new_status,notes,changed_by) VALUES (NEW.id,NULL,NEW.status,'Trip created',NEW.created_by);
  INSERT INTO activity_logs (user_id,action,module,description) VALUES (NEW.created_by,'Created','Trips',CONCAT('Trip ',NEW.trip_number,' created'));
END$$
CREATE TRIGGER trg_trips_after_update AFTER UPDATE ON trips FOR EACH ROW BEGIN
  IF OLD.status <> NEW.status THEN
    INSERT INTO trip_history (trip_id,previous_status,new_status,notes,changed_by) VALUES (NEW.id,OLD.status,NEW.status,'Trip status changed',NEW.updated_by);
    INSERT INTO activity_logs (user_id,action,module,description) VALUES (NEW.updated_by,'Status changed','Trips',CONCAT('Trip ',NEW.trip_number,': ',OLD.status,' to ',NEW.status));
  END IF;
END$$
CREATE TRIGGER trg_maintenance_after_insert AFTER INSERT ON maintenance_logs FOR EACH ROW BEGIN
  IF NEW.status IN ('Scheduled','In Progress') THEN UPDATE vehicles SET status='In Shop' WHERE id=NEW.vehicle_id AND status <> 'Retired'; END IF;
  INSERT INTO activity_logs (user_id,action,module,description) VALUES (NEW.created_by,'Created','Maintenance',CONCAT('Maintenance log #',NEW.id,' created'));
END$$
CREATE TRIGGER trg_maintenance_after_update AFTER UPDATE ON maintenance_logs FOR EACH ROW BEGIN
  IF NEW.status='Completed' AND OLD.status <> 'Completed' THEN
    UPDATE vehicles SET status='Available' WHERE id=NEW.vehicle_id AND status='In Shop' AND NOT EXISTS (SELECT 1 FROM maintenance_logs WHERE vehicle_id=NEW.vehicle_id AND status IN ('Scheduled','In Progress') AND deleted_at IS NULL);
  END IF;
END$$

CREATE PROCEDURE sp_create_trip(IN p_number VARCHAR(40),IN p_vehicle BIGINT,IN p_driver BIGINT,IN p_source VARCHAR(160),IN p_destination VARCHAR(160),IN p_cargo DECIMAL(12,2),IN p_distance DECIMAL(12,2),IN p_revenue DECIMAL(14,2),IN p_user BIGINT)
BEGIN
 DECLARE v_capacity DECIMAL(12,2); DECLARE v_vstatus VARCHAR(20); DECLARE v_dstatus VARCHAR(20); DECLARE v_expiry DATE;
 SELECT load_capacity,status INTO v_capacity,v_vstatus FROM vehicles WHERE id=p_vehicle AND deleted_at IS NULL FOR UPDATE;
 SELECT status,license_expiry_date INTO v_dstatus,v_expiry FROM drivers WHERE id=p_driver AND deleted_at IS NULL FOR UPDATE;
 IF v_capacity IS NULL OR v_vstatus <> 'Available' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Vehicle is not available'; END IF;
 IF v_dstatus IS NULL OR v_dstatus <> 'Available' OR v_expiry < CURDATE() THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Driver is not available or license has expired'; END IF;
 IF p_cargo > v_capacity THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cargo exceeds vehicle capacity'; END IF;
 INSERT INTO trips(trip_number,vehicle_id,driver_id,source,destination,cargo_weight,distance_km,revenue,created_by,updated_by) VALUES(p_number,p_vehicle,p_driver,p_source,p_destination,p_cargo,p_distance,p_revenue,p_user,p_user);
END$$
CREATE PROCEDURE sp_dispatch_trip(IN p_trip BIGINT,IN p_user BIGINT)
BEGIN
 DECLARE v_vehicle BIGINT; DECLARE v_driver BIGINT; SELECT vehicle_id,driver_id INTO v_vehicle,v_driver FROM trips WHERE id=p_trip AND status='Draft' FOR UPDATE;
 IF v_vehicle IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Only draft trips can be dispatched'; END IF;
 UPDATE vehicles SET status='On Trip',updated_by=p_user WHERE id=v_vehicle AND status='Available';
 IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Vehicle is unavailable'; END IF;
 UPDATE drivers SET status='On Trip',updated_by=p_user WHERE id=v_driver AND status='Available' AND license_expiry_date>=CURDATE();
 IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Driver is unavailable'; END IF;
 UPDATE trips SET status='Dispatched',dispatch_date=NOW(),updated_by=p_user WHERE id=p_trip;
END$$
CREATE PROCEDURE sp_complete_trip(IN p_trip BIGINT,IN p_end_odo DECIMAL(12,2),IN p_fuel DECIMAL(12,2),IN p_user BIGINT)
BEGIN
 DECLARE v_vehicle BIGINT; DECLARE v_driver BIGINT; DECLARE v_start DECIMAL(12,2); SELECT vehicle_id,driver_id,start_odometer INTO v_vehicle,v_driver,v_start FROM trips WHERE id=p_trip AND status='Dispatched' FOR UPDATE;
 IF v_vehicle IS NULL OR p_end_odo < COALESCE(v_start,0) OR p_fuel <= 0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Invalid completion data'; END IF;
 UPDATE trips SET status='Completed',arrival_date=NOW(),end_odometer=p_end_odo,fuel_used=p_fuel,average_mileage=(p_end_odo-COALESCE(v_start,0))/p_fuel,updated_by=p_user WHERE id=p_trip;
 UPDATE vehicles SET status='Available',odometer=p_end_odo,updated_by=p_user WHERE id=v_vehicle; UPDATE drivers SET status='Available',updated_by=p_user WHERE id=v_driver;
END$$
CREATE PROCEDURE sp_cancel_trip(IN p_trip BIGINT,IN p_reason VARCHAR(255),IN p_user BIGINT)
BEGIN
 DECLARE v_vehicle BIGINT; DECLARE v_driver BIGINT; SELECT vehicle_id,driver_id INTO v_vehicle,v_driver FROM trips WHERE id=p_trip AND status IN ('Draft','Dispatched') FOR UPDATE;
 IF v_vehicle IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Trip cannot be cancelled'; END IF;
 UPDATE trips SET status='Cancelled',cancellation_reason=p_reason,updated_by=p_user WHERE id=p_trip; UPDATE vehicles SET status='Available',updated_by=p_user WHERE id=v_vehicle AND status='On Trip'; UPDATE drivers SET status='Available',updated_by=p_user WHERE id=v_driver AND status='On Trip';
END$$
CREATE PROCEDURE sp_add_maintenance(IN p_vehicle BIGINT,IN p_type VARCHAR(100),IN p_description TEXT,IN p_cost DECIMAL(14,2),IN p_date DATE,IN p_user BIGINT)
BEGIN
 IF NOT EXISTS(SELECT 1 FROM vehicles WHERE id=p_vehicle AND status='Available' AND deleted_at IS NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Only available vehicles can enter maintenance'; END IF;
 INSERT INTO maintenance_logs(vehicle_id,maintenance_type,description,cost,start_date,status,created_by,updated_by) VALUES(p_vehicle,p_type,p_description,p_cost,p_date,'In Progress',p_user,p_user);
END$$
CREATE PROCEDURE sp_complete_maintenance(IN p_log BIGINT,IN p_end DATE,IN p_user BIGINT)
BEGIN UPDATE maintenance_logs SET status='Completed',end_date=p_end,updated_by=p_user WHERE id=p_log AND status IN ('Scheduled','In Progress'); IF ROW_COUNT()=0 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Maintenance record cannot be completed'; END IF; END$$
CREATE PROCEDURE sp_calculate_fuel_efficiency(IN p_vehicle BIGINT) BEGIN SELECT vehicle_id,ROUND(SUM(CASE WHEN end_odometer>start_odometer THEN end_odometer-start_odometer ELSE 0 END)/NULLIF(SUM(fuel_used),0),2) AS km_per_liter FROM trips WHERE vehicle_id=p_vehicle AND status='Completed' AND deleted_at IS NULL GROUP BY vehicle_id; END$$
CREATE PROCEDURE sp_calculate_roi(IN p_vehicle BIGINT) BEGIN SELECT v.id,v.registration_number,ROUND(((COALESCE(SUM(t.revenue),0)-COALESCE((SELECT SUM(e.expense_amount) FROM expenses e WHERE e.vehicle_id=v.id AND e.deleted_at IS NULL),0))/NULLIF(v.purchase_cost,0))*100,2) AS roi_percent FROM vehicles v LEFT JOIN trips t ON t.vehicle_id=v.id AND t.status='Completed' AND t.deleted_at IS NULL WHERE v.id=p_vehicle GROUP BY v.id; END$$
DELIMITER ;

CREATE VIEW vw_vehicle_summary AS SELECT v.id,v.registration_number,v.vehicle_name,v.vehicle_type,v.load_capacity,v.status,COUNT(t.id) total_trips,COALESCE(SUM(t.revenue),0) trip_revenue FROM vehicles v LEFT JOIN trips t ON t.vehicle_id=v.id AND t.deleted_at IS NULL WHERE v.deleted_at IS NULL GROUP BY v.id;
CREATE VIEW vw_driver_summary AS SELECT d.id,d.driver_name,d.license_number,d.license_expiry_date,d.safety_score,d.status,COUNT(t.id) total_trips FROM drivers d LEFT JOIN trips t ON t.driver_id=d.id AND t.deleted_at IS NULL WHERE d.deleted_at IS NULL GROUP BY d.id;
CREATE VIEW vw_trip_summary AS SELECT t.*,v.registration_number,d.driver_name FROM trips t JOIN vehicles v ON v.id=t.vehicle_id JOIN drivers d ON d.id=t.driver_id WHERE t.deleted_at IS NULL;
CREATE VIEW vw_operational_cost AS SELECT v.id vehicle_id,v.registration_number,COALESCE((SELECT SUM(cost) FROM fuel_logs f WHERE f.vehicle_id=v.id AND f.deleted_at IS NULL),0) fuel_cost,COALESCE((SELECT SUM(cost) FROM maintenance_logs m WHERE m.vehicle_id=v.id AND m.deleted_at IS NULL),0) maintenance_cost,COALESCE((SELECT SUM(expense_amount) FROM expenses e WHERE e.vehicle_id=v.id AND e.deleted_at IS NULL),0) expense_cost FROM vehicles v WHERE v.deleted_at IS NULL;
CREATE VIEW vw_fleet_utilization AS SELECT COUNT(*) total_vehicles,SUM(status='On Trip') active_vehicles,ROUND(100*SUM(status='On Trip')/NULLIF(COUNT(*),0),2) utilization_percent FROM vehicles WHERE deleted_at IS NULL;
CREATE VIEW vw_dashboard AS SELECT (SELECT COUNT(*) FROM vehicles WHERE status='Available' AND deleted_at IS NULL) available_vehicles,(SELECT COUNT(*) FROM vehicles WHERE status='On Trip' AND deleted_at IS NULL) active_vehicles,(SELECT COUNT(*) FROM trips WHERE status='Dispatched' AND deleted_at IS NULL) running_trips,(SELECT COUNT(*) FROM drivers WHERE status='Available' AND deleted_at IS NULL) available_drivers,(SELECT COALESCE(SUM(revenue),0) FROM trips WHERE status='Completed' AND deleted_at IS NULL) total_revenue,(SELECT COALESCE(SUM(fuel_cost+maintenance_cost+expense_cost),0) FROM vw_operational_cost) operational_cost;

INSERT INTO roles(name,slug,description) VALUES ('Administrator','administrator','Full platform control'),('Fleet Manager','fleet-manager','Fleet and maintenance management'),('Dispatcher','dispatcher','Trip dispatch management'),('Safety Officer','safety-officer','Driver and compliance management'),('Financial Analyst','financial-analyst','Finance and analytics access');
INSERT INTO users(first_name,last_name,email,password,phone,status) VALUES ('System','Administrator','admin@transitops.com','$2y$10$UwrUXJ5wMAyHxrLUct4pjOQRH2Ihbgp4CxOzUyxaCQndXVzn251fe','9000000001','Active'),('Raven','Manager','manager@transitops.com','$2y$10$UwrUXJ5wMAyHxrLUct4pjOQRH2Ihbgp4CxOzUyxaCQndXVzn251fe','9000000002','Active'),('Dev','Dispatcher','dispatcher@transitops.com','$2y$10$UwrUXJ5wMAyHxrLUct4pjOQRH2Ihbgp4CxOzUyxaCQndXVzn251fe','9000000003','Active'),('Sam','Safety','safety@transitops.com','$2y$10$UwrUXJ5wMAyHxrLUct4pjOQRH2Ihbgp4CxOzUyxaCQndXVzn251fe','9000000004','Active'),('Fin','Analyst','finance@transitops.com','$2y$10$UwrUXJ5wMAyHxrLUct4pjOQRH2Ihbgp4CxOzUyxaCQndXVzn251fe','9000000005','Active');
INSERT INTO permissions(name,`key`) VALUES ('Dashboard','dashboard.view'),('Vehicle','vehicles.manage'),('Driver','drivers.manage'),('Trip','trips.manage'),('Maintenance','maintenance.manage'),('Fuel','fuel.manage'),('Expense','expenses.manage'),('Analytics','analytics.view'),('Reports','reports.view'),('Settings','settings.manage'),('Notifications','notifications.view'),('Users','users.manage');
INSERT INTO user_roles(user_id,role_id) VALUES (1,1),(2,2),(3,3),(4,4),(5,5);
INSERT INTO role_permissions(role_id,permission_id) SELECT 1,id FROM permissions;
INSERT INTO role_permissions(role_id,permission_id) SELECT 2,id FROM permissions WHERE `key` IN ('dashboard.view','vehicles.manage','maintenance.manage','reports.view','notifications.view');
INSERT INTO role_permissions(role_id,permission_id) SELECT 3,id FROM permissions WHERE `key` IN ('dashboard.view','trips.manage','vehicles.manage','drivers.manage','notifications.view');
INSERT INTO role_permissions(role_id,permission_id) SELECT 4,id FROM permissions WHERE `key` IN ('dashboard.view','drivers.manage','trips.manage','reports.view','notifications.view');
INSERT INTO role_permissions(role_id,permission_id) SELECT 5,id FROM permissions WHERE `key` IN ('dashboard.view','fuel.manage','expenses.manage','analytics.view','reports.view','notifications.view');
INSERT INTO settings(setting_key,setting_value,setting_group) VALUES ('company_name','TransitOps Logistics','company'),('company_logo','','company'),('currency','INR','regional'),('timezone','Asia/Kolkata','regional'),('distance_unit','km','regional'),('fuel_unit','litres','regional');

DELIMITER $$
CREATE PROCEDURE sp_seed_demo_data()
BEGIN
 DECLARE i INT DEFAULT 1;
 WHILE i<=25 DO INSERT INTO vehicles(registration_number,vehicle_name,model,manufacturer,vehicle_type,fuel_type,load_capacity,purchase_date,purchase_cost,odometer,current_region,status,created_by,updated_by) VALUES(CONCAT('GJ-01-TO-',LPAD(i,4,'0')),CONCAT('Transit Vehicle ',i),CONCAT('Model ',i),'Transit Motors',IF(i%3=0,'Truck','Van'),IF(i%2=0,'Diesel','CNG'),IF(i%3=0,5000,1000),DATE_SUB(CURDATE(),INTERVAL (800+i) DAY),500000+i*15000,20000+i*1700,'Ahmedabad',CASE WHEN i=24 THEN 'In Shop' WHEN i=25 THEN 'Retired' WHEN i BETWEEN 20 AND 23 THEN 'On Trip' ELSE 'Available' END,1,1); SET i=i+1; END WHILE;
 SET i=1; WHILE i<=30 DO INSERT INTO drivers(driver_name,license_number,license_category,license_issue_date,license_expiry_date,experience_years,joining_date,phone,email,emergency_contact,safety_score,status,created_by,updated_by) VALUES(CONCAT('Driver ',i),CONCAT('GJDL',LPAD(i,8,'0')),IF(i%2=0,'HMV','LMV'),DATE_SUB(CURDATE(),INTERVAL (2000+i) DAY),DATE_ADD(CURDATE(),INTERVAL (300+i) DAY),i%12,DATE_SUB(CURDATE(),INTERVAL (900+i) DAY),CONCAT('98',LPAD(i,8,'0')),CONCAT('driver',i,'@transitops.test'),CONCAT('99',LPAD(i,8,'0')),70+(i%31),IF(i BETWEEN 24 AND 26,'Off Duty',IF(i=30,'Suspended',IF(i BETWEEN 20 AND 23,'On Trip','Available'))),1,1); SET i=i+1; END WHILE;
 SET i=1; WHILE i<=80 DO INSERT INTO trips(trip_number,vehicle_id,driver_id,source,destination,cargo_weight,distance_km,revenue,dispatch_date,arrival_date,start_odometer,end_odometer,fuel_used,average_mileage,status,created_by,updated_by) VALUES(CONCAT('TRP-',LPAD(i,5,'0')),((i-1)%19)+1,((i-1)%19)+1,'Ahmedabad',IF(i%2=0,'Surat','Vadodara'),500+(i%5)*50,100+(i%8)*25,8000+i*150,DATE_SUB(NOW(),INTERVAL (i+2) DAY),IF(i%4=0,NULL,DATE_SUB(NOW(),INTERVAL i DAY)),30000+i*100,IF(i%4=0,NULL,30200+i*100),IF(i%4=0,NULL,20+(i%10)),IF(i%4=0,NULL,10),CASE WHEN i%4=0 THEN 'Dispatched' WHEN i%9=0 THEN 'Cancelled' ELSE 'Completed' END,1,1); SET i=i+1; END WHILE;
 SET i=1; WHILE i<=50 DO INSERT INTO fuel_logs(vehicle_id,trip_id,fuel_station,liters,cost,rate_per_liter,fuel_date,created_by,updated_by) VALUES(((i-1)%25)+1,((i-1)%80)+1,'Transit Fuel Station',20+(i%30),(20+(i%30))*95,95,DATE_SUB(CURDATE(),INTERVAL i DAY),1,1); SET i=i+1; END WHILE;
 SET i=1; WHILE i<=40 DO INSERT INTO maintenance_logs(vehicle_id,maintenance_type,description,mechanic,vendor,cost,start_date,end_date,status,created_by,updated_by) VALUES(((i-1)%23)+1,IF(i%2=0,'Oil Change','Tyre Service'),'Scheduled service','Mechanic A','Transit Workshop',1500+i*100,DATE_SUB(CURDATE(),INTERVAL (i+20) DAY),DATE_SUB(CURDATE(),INTERVAL (i+19) DAY),'Completed',1,1); SET i=i+1; END WHILE;
 SET i=1; WHILE i<=60 DO INSERT INTO expenses(vehicle_id,trip_id,expense_type,expense_amount,expense_date,remarks,status,created_by,updated_by) VALUES(((i-1)%25)+1,((i-1)%80)+1,ELT((i%8)+1,'Fuel','Maintenance','Toll','Parking','Insurance','Repair','Fine','Miscellaneous'),500+i*75,DATE_SUB(CURDATE(),INTERVAL i DAY),'Demo operating expense','Paid',1,1); SET i=i+1; END WHILE;
 INSERT INTO notifications(user_id,title,message,type) VALUES (1,'TransitOps ready','Demo data has been loaded.','Success'),(2,'Maintenance review','Review upcoming vehicle maintenance.','Warning'),(3,'Trips live','Dispatched trips require monitoring.','Info'),(4,'Compliance check','Review driver license expiry dates.','Warning'),(5,'Finance report','Monthly operational costs are available.','Info');
END$$
DELIMITER ;
CALL sp_seed_demo_data();
DROP PROCEDURE sp_seed_demo_data;
