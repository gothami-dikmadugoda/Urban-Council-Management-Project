-- Add department_id column to complaints table if it doesn't exist
ALTER TABLE complaints
ADD COLUMN IF NOT EXISTS department_id INT,
ADD FOREIGN KEY (department_id) REFERENCES departments(id);

-- Update existing complaints with default department (Health - ID 1)
UPDATE complaints 
SET department_id = 1 
WHERE department_id IS NULL; 