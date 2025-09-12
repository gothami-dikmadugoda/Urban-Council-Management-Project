CREATE TABLE IF NOT EXISTS categories (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    department_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY (department_id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Insert some sample categories
INSERT INTO categories (name, department_id) VALUES
('Garbage Collection', 1),
('Street Cleaning', 1),
('Drainage', 2),
('Road Maintenance', 2),
('Public Health', 3),
('Sanitation', 3),
('Building Permits', 4),
('Zoning', 4); 