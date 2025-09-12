ALTER TABLE users 
MODIFY COLUMN job_role ENUM(
    'garbage_manager', 
    'garbage_collector', 
    'field_visitor',
    'moh_officer', 
    'complaint_manager', 
    'it_staff', 
    'receptionist'
) NULL; 