-- Add assigned_admin_id column to urban_renewals table
ALTER TABLE urban_renewals
ADD COLUMN assigned_admin_id INT(11) UNSIGNED NULL
COMMENT '分配的企業管理者ID'
AFTER chairman_phone;

-- Add index
ALTER TABLE urban_renewals
ADD INDEX idx_assigned_admin_id (assigned_admin_id);

-- Add foreign key constraint
ALTER TABLE urban_renewals
ADD CONSTRAINT fk_urban_renewals_assigned_admin
FOREIGN KEY (assigned_admin_id)
REFERENCES users(id)
ON DELETE SET NULL
ON UPDATE CASCADE;
