CREATE TABLE IF NOT EXISTS organ_matches (
    match_id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    recipient_id INT NOT NULL,
    organ_type VARCHAR(50) NOT NULL,
    hospital_id INT NOT NULL,
    match_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    completion_date TIMESTAMP NULL,
    notes TEXT,
    FOREIGN KEY (donor_id) REFERENCES donors(id),
    FOREIGN KEY (recipient_id) REFERENCES recipients(id),
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id)
);
