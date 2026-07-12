-- Run once for existing TransitOps installations before enabling self-registration.
USE transitops;
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS employee_id VARCHAR(80) NULL,
  ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS address TEXT NULL,
  ADD COLUMN IF NOT EXISTS city VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS state VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS country VARCHAR(100) NULL,
  ADD COLUMN IF NOT EXISTS approval_status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Approved',
  ADD COLUMN IF NOT EXISTS approved_by INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS rejected_by INT UNSIGNED NULL,
  ADD COLUMN IF NOT EXISTS rejected_at TIMESTAMP NULL,
  ADD COLUMN IF NOT EXISTS rejection_reason TEXT NULL,
  ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1,
  ADD INDEX IF NOT EXISTS idx_users_approval (approval_status, created_at);
UPDATE users SET approval_status='Approved', is_active=1 WHERE approval_status IS NULL OR approval_status='Pending';
