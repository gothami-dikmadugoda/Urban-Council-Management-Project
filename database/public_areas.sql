-- Create public_areas table
CREATE TABLE IF NOT EXISTS public_areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    capacity INT,
    hourly_rate DECIMAL(10,2) NOT NULL,
    location VARCHAR(255),
    status ENUM('available', 'unavailable', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample public areas
INSERT INTO public_areas (name, description, capacity, hourly_rate, location, status) VALUES
('Community Hall', 'Large hall suitable for community events and gatherings', 200, 1000.00, 'Main Building, Ground Floor', 'available'),
('Conference Room', 'Professional meeting room with presentation facilities', 50, 500.00, 'Main Building, First Floor', 'available'),
('Sports Ground', 'Outdoor sports facility with basic amenities', 100, 800.00, 'Behind Main Building', 'available'),
('Auditorium', 'Fully equipped auditorium with stage and sound system', 300, 1500.00, 'Main Building, Ground Floor', 'available'),
('Garden Area', 'Beautiful outdoor garden space for events', 150, 600.00, 'East Wing', 'available'); 