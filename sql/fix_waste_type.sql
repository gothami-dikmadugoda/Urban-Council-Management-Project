-- First, backup the existing data
CREATE TABLE collection_requests_backup AS SELECT * FROM collection_requests;

-- Modify the waste_type column to use proper ENUM
ALTER TABLE collection_requests 
MODIFY COLUMN waste_type ENUM('household', 'garden', 'construction', 'hazardous', 'recyclable') NOT NULL;

-- Update any existing records with invalid waste_type values to 'household' (default)
UPDATE collection_requests 
SET waste_type = 'household' 
WHERE waste_type NOT IN ('household', 'garden', 'construction', 'hazardous', 'recyclable'); 