-- Seed data for TransitOps
USE transitops;

INSERT IGNORE INTO roles (name, display_name) VALUES
('admin','Administrator'),
('fleet_manager','Fleet Manager'),
('operations','Operations'),
('driver','Driver'),
('viewer','Viewer');

INSERT IGNORE INTO permissions (name) VALUES
('dashboard'),('vehicles'),('drivers'),('trips'),('maintenance'),('fuel'),('expenses'),('reports'),('analytics'),('notifications'),('users'),('settings');

INSERT IGNORE INTO vehicle_types (name) VALUES ('Truck'),('Van'),('Bus'),('Courier');
INSERT IGNORE INTO regions (name) VALUES ('North Zone'),('South Zone'),('East Zone'),('West Zone');

INSERT IGNORE INTO users (full_name,email,password_hash,role,status) VALUES
('System Administrator','admin@transitops.com','$2y$10$R2zno5xrOMP.pvAV5U/JMeR0Iqo4ABoEWyrkDTVxatoGJexbzHi46','admin','Active');

INSERT IGNORE INTO settings (key_name, value_text) VALUES
('company_name','TransitOps'),
('company_email','hello@transitops.com'),
('company_phone','+91 9876543210');
