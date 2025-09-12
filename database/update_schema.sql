-- Add resolved_date column to complaints table if it doesn't exist
ALTER TABLE complaints
ADD COLUMN IF NOT EXISTS resolved_date TIMESTAMP NULL;

-- Update existing resolved complaints with resolved_date
UPDATE complaints 
SET resolved_date = updated_at 
WHERE status = 'resolved' AND resolved_date IS NULL; 