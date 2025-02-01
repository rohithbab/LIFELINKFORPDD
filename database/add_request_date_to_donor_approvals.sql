-- Add request_date column to hospital_donor_approvals table
ALTER TABLE hospital_donor_approvals 
ADD COLUMN request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status;
