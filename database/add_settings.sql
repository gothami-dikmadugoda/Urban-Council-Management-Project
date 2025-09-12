-- Create settings table if it doesn't exist
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    site_name VARCHAR(100) NOT NULL DEFAULT 'Urban Council Management System',
    site_description TEXT,
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    address TEXT,
    maintenance_mode BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings if not exists
INSERT INTO settings (id, site_name, site_description, contact_email, contact_phone, address)
VALUES (1, 'Urban Council Management System', 'A comprehensive system for managing urban council complaints and services', 'contact@urbancouncil.com', '+1234567890', '123 Council Street, City')
ON DUPLICATE KEY UPDATE id = id; 