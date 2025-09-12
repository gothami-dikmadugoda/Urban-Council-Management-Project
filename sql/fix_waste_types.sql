-- First, create a backup of the collection_requests table
CREATE TABLE collection_requests_backup AS SELECT * FROM collection_requests;

-- Update any invalid waste types to 'household' (as a default)
UPDATE collection_requests 
SET waste_type = 'household' 
WHERE waste_type NOT IN ('household', 'garden', 'construction', 'hazardous', 'recyclable');

-- Alter the table to enforce the ENUM constraint
ALTER TABLE collection_requests 
MODIFY COLUMN waste_type ENUM('household', 'garden', 'construction', 'hazardous', 'recyclable') NOT NULL; 