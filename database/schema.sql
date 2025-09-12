-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS urban_council_db;
USE urban_council_db;

-- Create departments table first (no foreign keys)
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default departments
INSERT INTO departments (name, description) VALUES
('Health', 'Handles health-related complaints and issues'),
('Engineering', 'Manages infrastructure and engineering complaints'),
('IT', 'Handles technology and system-related issues'),
('Reception', 'Manages general inquiries and complaints');

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary Key: Unique ID for each user',
    first_name VARCHAR(50) NOT NULL COMMENT 'User first name',
    last_name VARCHAR(50) NOT NULL COMMENT 'User last name',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'User email address (used for login)',
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password for authentication',
    phone VARCHAR(20) NOT NULL COMMENT 'Contact number of the user',
    address TEXT NOT NULL COMMENT 'Residential or organizational address',
    role ENUM('admin', 'staff', 'citizen', 'private_company') NOT NULL DEFAULT 'citizen' COMMENT 'Role of the user in the system',
    department ENUM('health', 'engineering', 'it', 'reception') NULL COMMENT 'Applicable department if user is staff',
    job_role ENUM('garbage_manager', 'garbage_collector', 'field_visitor', 'moh_officer', 'complaint_manager', 'it_staff', 'receptionist') NULL COMMENT 'Job role for staff users',
    profile_picture VARCHAR(255) NULL COMMENT 'File path or URL to user profile picture',
    remember_token VARCHAR(100) NULL COMMENT 'Token for remember-me functionality (login)',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active' COMMENT 'Account status of the user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of user account creation',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Timestamp of last update to user record',
    profile_image VARCHAR(255) NULL COMMENT 'Alternate field for profile picture (optional)',
    PRIMARY KEY (id)
) COMMENT='Table to store all users including admins, staff, citizens, and private companies';

-- Delete existing admin user if exists
DELETE FROM users WHERE email = 'admin@urbancouncil.com';

-- Create new admin user
INSERT INTO users (
    first_name,
    last_name,
    email,
    password,
    phone,
    address,
    role,
    status,
    created_at,
    updated_at
) VALUES (
    'Admin',
    'User',
    'admin@urbancouncil.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is a hashed version of 'password'
    '0112345678',
    'Urban Council Office, Main Street',
    'admin',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);

-- Create complaints table (depends on departments and users)
CREATE TABLE IF NOT EXISTS complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved', 'closed') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    department_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create complaint_comments table (depends on complaints and users)
CREATE TABLE IF NOT EXISTS complaint_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create complaint_attachments table (depends on complaints and users)
CREATE TABLE IF NOT EXISTS complaint_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id),
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
); 

CREATE TABLE announcements (
    announcement_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Primary Key',
    title VARCHAR(255) NOT NULL COMMENT 'Announcement title',
    content TEXT NOT NULL COMMENT 'Detailed announcement content',
    posted_by INT NOT NULL COMMENT 'User ID of IT Team member who posted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when announcement was posted',
    expiry_datetime DATETIME COMMENT 'Date and time when the announcement expires',
    CONSTRAINT fk_posted_by FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE CASCADE
) COMMENT='Table to store announcements published by IT Team';

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_type ENUM('tax', 'service_charge', 'fine', 'other') NOT NULL,
    payment_method ENUM('bank_transfer', 'cash', 'online_payment') NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded', 'under_review') DEFAULT 'pending',
    bank_name VARCHAR(100) NULL,
    branch VARCHAR(100) NULL,
    deposit_date DATE NULL,
    reference_number VARCHAR(50) NULL,
    bank_slip_image LONGBLOB NULL,
    bank_slip_upload_date TIMESTAMP NULL,
    description TEXT NOT NULL,
    assigned_to INT(11) NOT NULL,
    verified_by INT(11) NULL,
    verification_date TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payment_id (payment_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_type (payment_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
    announcement_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT COMMENT 'Primary Key',
    title VARCHAR(255) NOT NULL COMMENT 'Announcement title',
    content TEXT NOT NULL COMMENT 'Detailed announcement content',
    posted_by INT NOT NULL COMMENT 'User ID of IT Team member who posted',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when announcement was posted',
    expiry_datetime DATETIME COMMENT 'Date and time when the announcement expires',
    CONSTRAINT fk_posted_by FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE
) COMMENT='Table to store announcements published by IT Team';

// ... existing code ...

CREATE TABLE IF NOT EXISTS payment_replies (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    payment_id INT(11) NOT NULL,
    staff_id INT(11) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment_id (payment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

// ... existing code ...

CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id VARCHAR(20) NOT NULL UNIQUE,
    user_id INT(11) NOT NULL,
    area_id INT(11) NOT NULL,
    start_datetime DATETIME NOT NULL,
    duration_hours INT NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
    assigned_to INT(11) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (area_id) REFERENCES public_areas(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_start_datetime (start_datetime),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create booking_reminders table
CREATE TABLE IF NOT EXISTS booking_reminders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    booking_id INT(11) NOT NULL,
    reminder_type ENUM('24h', '1h', '15min') NOT NULL,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_reminder_type (reminder_type),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS public_areas (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT NOT NULL,
    hourly_rate DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'maintenance', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;