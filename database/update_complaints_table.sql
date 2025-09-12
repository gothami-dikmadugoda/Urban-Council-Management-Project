-- Add image column if it doesn't exist
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS image LONGBLOB;

-- Modify priority column to include all options
ALTER TABLE complaints MODIFY COLUMN priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium';

-- Modify status column to include all options
ALTER TABLE complaints MODIFY COLUMN status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending';

-- Modify timestamp columns to be NOT NULL
ALTER TABLE complaints MODIFY COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE complaints MODIFY COLUMN updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
ALTER TABLE complaints MODIFY COLUMN resolved_date TIMESTAMP NULL DEFAULT NULL;

-- Add assigned_to column if it doesn't exist
ALTER TABLE complaints ADD COLUMN IF NOT EXISTS assigned_to INT(11);

-- Add foreign key for assigned_to if it doesn't exist
ALTER TABLE complaints ADD CONSTRAINT fk_complaints_assigned_to 
    FOREIGN KEY (assigned_to) REFERENCES users(id);

-- Add indexes if they don't exist
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_department_id (department_id);
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_user_id (user_id);
ALTER TABLE complaints ADD INDEX IF NOT EXISTS idx_assigned_to (assigned_to);

-- Update complaint_notes table
ALTER TABLE complaint_notes 
    MODIFY COLUMN id INT(11) NOT NULL AUTO_INCREMENT,
    MODIFY COLUMN complaint_id INT(11) NOT NULL,
    MODIFY COLUMN user_id INT(11) NOT NULL,
    MODIFY COLUMN note TEXT NOT NULL,
    MODIFY COLUMN created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ADD PRIMARY KEY IF NOT EXISTS (id),
    ADD INDEX IF NOT EXISTS idx_complaint_id (complaint_id),
    ADD INDEX IF NOT EXISTS idx_user_id (user_id),
    ADD CONSTRAINT IF NOT EXISTS fk_complaint_notes_complaint 
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    ADD CONSTRAINT IF NOT EXISTS fk_complaint_notes_user 
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- First, check if the constraint exists and drop it if it does
SET @constraint_name = 'fk_complaints_department';
SET @sql = CONCAT('ALTER TABLE complaints DROP FOREIGN KEY IF EXISTS ', @constraint_name);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Then add the foreign key constraint
ALTER TABLE complaints 
    ADD CONSTRAINT fk_complaints_department 
    FOREIGN KEY (department_id) REFERENCES departments(id);

-- Ensure all indexes exist
ALTER TABLE complaints 
    ADD INDEX IF NOT EXISTS idx_department_id (department_id),
    ADD INDEX IF NOT EXISTS idx_user_id (user_id),
    ADD INDEX IF NOT EXISTS idx_assigned_to (assigned_to); 