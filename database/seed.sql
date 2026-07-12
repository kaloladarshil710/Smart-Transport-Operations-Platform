-- Seed data for TransitOps
USE transitops;

INSERT INTO roles (name, display_name) VALUES
('admin','Administrator'),
('fleet_manager','Fleet Manager'),
('operations','Operations'),
('driver','Driver'),
('viewer','Viewer');

INSERT INTO permissions (name) VALUES
('dashboard'),('vehicles'),('drivers'),('trips'),('maintenance'),('fuel'),('expenses'),('reports'),('analytics'),('notifications'),('users'),('settings');

INSERT INTO vehicle_types (name) VALUES ('Truck'),('Van'),('Bus'),('Courier');
INSERT INTO regions (name) VALUES ('North Zone'),('South Zone'),('East Zone'),('West Zone');

INSERT INTO users (full_name,email,password_hash,role,status) VALUES
('System Administrator','admin@transitops.com','$2y$10$1ANfy60nPt0ndsmYQzKSYu5F2m2B2liYPWXQFyxam3tdQOCAyzp2e','admin','Active');

INSERT INTO settings (key_name, value_text) VALUES
('company_name','TransitOps'),
('company_email','hello@transitops.com'),
('company_phone','+91 9876543210');
