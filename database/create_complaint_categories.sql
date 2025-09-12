-- Create complaint categories table
CREATE TABLE IF NOT EXISTS complaint_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
);

-- Insert sample categories for Health Department (assuming department_id = 1)
INSERT INTO complaint_categories (name, department_id, description) VALUES
('Hospital Sanitation', 1, 'Issues related to cleanliness and hygiene in hospitals'),
('Medical Waste Management', 1, 'Concerns about medical waste disposal'),
('Mosquito Control', 1, 'Issues related to mosquito breeding and control measures'),
('Food Safety', 1, 'Complaints about food safety in restaurants and establishments');

-- Insert sample categories for Engineering Department (assuming department_id = 2)
INSERT INTO complaint_categories (name, department_id, description) VALUES
('Road Damage', 2, 'Issues with road conditions, potholes, and repairs'),
('Street Light Issues', 2, 'Problems with street lighting and electrical infrastructure'),
('Sewage Blockage', 2, 'Drainage and sewage system problems'),
('Building Safety', 2, 'Concerns about building structural safety');

-- Insert sample categories for Waste Management Department (assuming department_id = 3)
INSERT INTO complaint_categories (name, department_id, description) VALUES
('Garbage Collection', 3, 'Issues with regular garbage collection'),
('Waste Segregation', 3, 'Problems with waste segregation compliance'),
('Public Bins', 3, 'Complaints about public waste bins'),
('Illegal Dumping', 3, 'Reports of illegal waste dumping'); 