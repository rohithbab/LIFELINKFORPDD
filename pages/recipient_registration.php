<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipient Registration - LifeLink</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .form-section {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .form-section h3 {
            color: var(--primary-blue);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-green);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"],
        .form-group input[type="tel"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input[type="file"] {
            padding: 0.5rem 0;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-blue);
            outline: none;
        }
        .checkbox-group {
            margin: 1rem 0;
        }
        .checkbox-group label {
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #555;
        }
        .checkbox-group input[type="checkbox"] {
            margin-top: 0.2rem;
        }
        .submit-btn {
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: opacity 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }
        .submit-btn:hover {
            opacity: 0.9;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../index.php" class="logo">
                <span class="logo-life">LifeLink</span>
            </a>
            <div class="nav-links">
                <a href="../index.php" class="btn" style="
                    background: var(--primary-blue);
                    color: var(--white);
                    transition: all 0.3s ease;
                    border: 2px solid var(--primary-blue);
                " onmouseover="
                    this.style.background='transparent';
                    this.style.color='var(--primary-blue)';
                " onmouseout="
                    this.style.background='var(--primary-blue)';
                    this.style.color='var(--white)';
                "><i class="fas fa-home"></i> Back to Home</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: 2rem;">
        <h2 class="text-center" style="margin-bottom: 2rem; color: var(--primary-blue);">Recipient Registration</h2>
        
        <form action="../backend/php/recipient_registration_process.php" method="POST" enctype="multipart/form-data" id="recipientForm">
            <!-- Personal Details Section -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Personal Details</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="fullName">Full Name *</label>
                        <input type="text" id="fullName" name="fullName" required>
                    </div>
                    <div class="form-group">
                        <label for="dob">Date of Birth *</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label for="gender">Gender *</label>
                        <select id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                    </div>
                </div>
            </div>

            <!-- Medical Information Section -->
            <div class="form-section">
                <h3><i class="fas fa-heartbeat"></i> Medical Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="medicalCondition">Medical Condition *</label>
                        <textarea id="medicalCondition" name="medicalCondition" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="bloodType">Blood Type *</label>
                        <select id="bloodType" name="bloodType" required>
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="organRequired">Organ Required *</label>
                        <select id="organRequired" name="organRequired" required>
                            <option value="">Select Organ</option>
                            <option value="kidney">Kidney</option>
                            <option value="liver">Liver</option>
                            <option value="heart">Heart</option>
                            <option value="lungs">Lungs</option>
                            <option value="pancreas">Pancreas</option>
                            <option value="corneas">Corneas</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="organReason">Reason for Organ Requirement *</label>
                        <textarea id="organReason" name="organReason" rows="3" required placeholder="Please explain your medical condition and why you need this organ"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="medicalRecords">Medical Records Upload *</label>
                        <input type="file" id="medicalRecords" name="medicalRecords" accept=".pdf,.doc,.docx" required>
                        <small class="error-message">Supported formats: PDF, DOC, DOCX (Max size: 5MB)</small>
                    </div>
                </div>
            </div>

            <!-- ID Verification Section -->
            <div class="form-section">
                <h3><i class="fas fa-id-card"></i> ID Verification</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="idType">ID Proof Type *</label>
                        <select id="idType" name="idType" required>
                            <option value="">Select ID Type</option>
                            <option value="passport">Passport</option>
                            <option value="national_id">National ID</option>
                            <option value="aadhar">Aadhar Card</option>
                            <option value="drivers_license">Driver's License</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="idProof">ID Proof Upload *</label>
                        <input type="file" id="idProof" name="idProof" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="error-message">Supported formats: PDF, JPG, JPEG, PNG (Max size: 2MB)</small>
                    </div>
                </div>
            </div>

            <!-- Authentication Section -->
            <div class="form-section">
                <h3><i class="fas fa-lock"></i> Authentication Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                        <small class="error-message">Minimum 8 characters, must include numbers and special characters</small>
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password *</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                    </div>
                </div>
            </div>

            <!-- Policies and Acknowledgement Section -->
            <div class="form-section">
                <h3><i class="fas fa-file-contract"></i> Policies and Acknowledgement</h3>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="policyAgreement" required>
                        I have read and agree to the organ donation policies, including the understanding that organ donations are based on medical necessity and ethical standards.
                    </label>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="medicalRecordsConsent" required>
                        I consent to share my medical records with relevant medical authorities for verification and matching purposes.
                    </label>
                </div>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="termsAgreement" required>
                        I agree to the terms and conditions of organ donation, which includes ethical standards and the right to withdraw from the waiting list at any time.
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Register as Recipient</button>
        </form>
    </div>

    <script>
        // Form validation
        document.getElementById('recipientForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Password validation
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }

            // Password strength check
            const passwordRegex = /^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/;
            if (!passwordRegex.test(password)) {
                e.preventDefault();
                alert('Password must be at least 8 characters long and contain numbers and special characters!');
                return;
            }

            // File size validation
            const medicalRecords = document.getElementById('medicalRecords').files[0];
            const idProof = document.getElementById('idProof').files[0];

            if (medicalRecords && medicalRecords.size > 5 * 1024 * 1024) {
                e.preventDefault();
                alert('Medical records file size must be less than 5MB!');
                return;
            }

            if (idProof && idProof.size > 2 * 1024 * 1024) {
                e.preventDefault();
                alert('ID proof file size must be less than 2MB!');
                return;
            }
        });
    </script>
</body>
</html>