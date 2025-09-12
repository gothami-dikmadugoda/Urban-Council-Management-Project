-- Create departments table if it doesn't exist
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Clear existing departments to prevent duplicates
TRUNCATE TABLE departments;

-- Insert default departments
INSERT INTO departments (name, description) VALUES
('Health', 'Handles health-related complaints and issues'),
('Engineering', 'Manages infrastructure and engineering complaints'),
('IT', 'Handles technology and system-related issues'),
('Reception', 'Manages general inquiries and complaints');

-- First, let's delete any existing admin users
DELETE FROM users WHERE email IN ('admin@system.com', 'admin@urbancouncil.com', 'admin@urban.com');

-- Now create a new admin user with a properly hashed password
INSERT INTO users (
    first_name,
    last_name,
    email,
    password,
    phone,
    address,
    role,
    department_id,
    job_role,
    status
) VALUES (
    'Admin',
    'User',
    'admin@urban.com',
    -- This is a bcrypt hash for password: admin123
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    '0123456789',
    'Urban Council HQ',
    'admin',
    3,
    'it_staff',
    'active'
); 