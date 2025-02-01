-- Create hospital_donor_approvals table
CREATE TABLE IF NOT EXISTS hospital_donor_approvals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hospital_id INT NOT NULL,
    donor_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_date TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (donor_id) REFERENCES donor(donor_id),
    INDEX idx_status (status),
    INDEX idx_hospital (hospital_id),
    INDEX idx_donor (donor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
