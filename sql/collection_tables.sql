-- Table for collection requests
CREATE TABLE IF NOT EXISTS collection_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    area VARCHAR(100) NOT NULL,
    collection_date DATE NOT NULL,
    collection_time TIME NOT NULL,
    waste_type ENUM('household', 'garden', 'construction', 'hazardous', 'recyclable') NOT NULL,
    waste_volume ENUM('small', 'medium', 'large') NOT NULL,
    special_instructions TEXT,
    status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for collection notes
CREATE TABLE IF NOT EXISTS collection_notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    staff_id INT NOT NULL,
    note TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES collection_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_collection_requests_user ON collection_requests(user_id);
CREATE INDEX idx_collection_requests_status ON collection_requests(status);
CREATE INDEX idx_collection_requests_date ON collection_requests(collection_date);
CREATE INDEX idx_collection_notes_request ON collection_notes(request_id); 