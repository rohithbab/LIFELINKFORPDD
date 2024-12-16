<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeLink - Connecting Lives Through Organ Donation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .nav-links a:not(.btn) {
            position: relative;
            text-decoration: none;
            color: var(--dark-gray);
            transition: color 0.3s ease;
        }

        .nav-links a:not(.btn)::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            transition: width 0.3s ease;
        }

        .nav-links a:not(.btn):hover::after {
            width: 100%;
        }

        .nav-links a:not(.btn):hover {
            color: var(--primary-blue);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <span class="logo-life">LifeLink</span>
            </a>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#testimonials">Testimonials</a>
                <a href="#community">Join Community</a>
                <a href="pages/hospital_hub.php" class="btn" style="
                    background: var(--primary-blue);
                    color: var(--white);
                    transition: all 0.3s ease;
                    margin-left: 1rem;
                    border: 2px solid var(--primary-blue);
                    padding: 0.5rem 1rem;
                    font-size: 0.9rem;
                " onmouseover="
                    this.style.background='transparent';
                    this.style.color='var(--primary-blue)';
                " onmouseout="
                    this.style.background='var(--primary-blue)';
                    this.style.color='var(--white)';
                ">Hospital Hub</a>
                <a href="pages/admin_login.php" class="btn" style="
                    background: var(--primary-green);
                    color: var(--white);
                    transition: all 0.3s ease;
                    margin-left: 0.5rem;
                    border: 2px solid var(--primary-green);
                    padding: 0.5rem 1rem;
                    font-size: 0.9rem;
                " onmouseover="
                    this.style.background='transparent';
                    this.style.color='var(--primary-green)';
                " onmouseout="
                    this.style.background='var(--primary-green)';
                    this.style.color='var(--white)';
                ">Admin Login</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="section hero" style="
        background: linear-gradient(rgba(33, 150, 243, 0.9), rgba(76, 175, 80, 0.9)),
        url('assets/images/common/hero-bg.jpg') center/cover;
        height: 100vh;
        display: flex;
        align-items: center;
        color: var(--white);
        margin-top: -5rem;
    ">
        <div class="container text-center">
            <h1 style="font-size: 3.5rem; margin-bottom: 1.5rem;">
                Connecting Lives Through<br>Organ Donation
            </h1>
            <p style="font-size: 1.25rem; margin-bottom: 2rem;">
                Join our mission to save lives by bridging the gap between donors and recipients
            </p>
            <div class="hero-buttons" style="display: flex; gap: 1rem; justify-content: center;">
                <a href="pages/donor_registration.php" 
                   class="btn btn-primary donor-btn" 
                   style="
                        background: var(--primary-blue);
                        transition: all 0.3s ease;
                        color: var(--white);
                        padding: 0.4rem 0.8rem;
                        font-size: 0.8rem;
                   " 
                   onmouseover="
                        this.style.background='var(--primary-green)';
                        document.querySelector('.recipient-btn').style.background='transparent';
                   " 
                   onmouseout="
                        this.style.background='var(--primary-blue)';
                        document.querySelector('.recipient-btn').style.background='transparent';
                   ">Register as Donor</a>
                <a href="pages/recipient_registration.php" 
                   class="btn btn-outline recipient-btn" 
                   style="
                        border: 2px solid var(--white);
                        color: var(--white);
                        transition: all 0.3s ease;
                        background: transparent;
                        padding: 0.4rem 0.8rem;
                        font-size: 0.8rem;
                   " 
                   onmouseover="
                        this.style.background='var(--primary-green)';
                        document.querySelector('.donor-btn').style.background='transparent';
                        document.querySelector('.donor-btn').style.border='2px solid var(--white)';
                   " 
                   onmouseout="
                        this.style.background='transparent';
                        document.querySelector('.donor-btn').style.background='var(--primary-blue)';
                   ">Register as Recipient</a>
            </div>
            <div style="margin-top: 1.5rem; color: var(--white);">
                <p style="font-size: 1.1rem;">Already registered? 
                    <a href="pages/donor_login.php" style="
                        color: var(--white);
                        text-decoration: none;
                        margin: 0 0.5rem;
                        padding: 0.3rem 1rem;
                        border-radius: 20px;
                        background: linear-gradient(45deg, rgba(76, 175, 80, 0.3), rgba(33, 150, 243, 0.3));
                        transition: all 0.3s ease;
                        font-weight: 500;
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        display: inline-block;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    " onmouseover="
                        this.style.background='linear-gradient(45deg, rgba(76, 175, 80, 0.6), rgba(33, 150, 243, 0.6))';
                        this.style.transform='translateY(-2px)';
                        this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)';
                    " onmouseout="
                        this.style.background='linear-gradient(45deg, rgba(76, 175, 80, 0.3), rgba(33, 150, 243, 0.3))';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.1)';
                    ">Donor</a> or 
                    <a href="pages/recipient_login.php" style="
                        color: var(--white);
                        text-decoration: none;
                        padding: 0.3rem 1rem;
                        border-radius: 20px;
                        background: linear-gradient(45deg, rgba(33, 150, 243, 0.3), rgba(76, 175, 80, 0.3));
                        transition: all 0.3s ease;
                        font-weight: 500;
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        display: inline-block;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    " onmouseover="
                        this.style.background='linear-gradient(45deg, rgba(33, 150, 243, 0.6), rgba(76, 175, 80, 0.6))';
                        this.style.transform='translateY(-2px)';
                        this.style.boxShadow='0 4px 15px rgba(0, 0, 0, 0.2)';
                    " onmouseout="
                        this.style.background='linear-gradient(45deg, rgba(33, 150, 243, 0.3), rgba(76, 175, 80, 0.3))';
                        this.style.transform='translateY(0)';
                        this.style.boxShadow='0 2px 10px rgba(0, 0, 0, 0.1)';
                    ">Recipient</a>
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div>
                    <h3>LifeLink</h3>
                    <p>Connecting lives through organ donation</p>
                </div>
                <div>
                    <h3>Quick Links</h3>
                    <ul style="list-style: none;">
                        <li><a href="#about" style="color: var(--white); text-decoration: none;">About</a></li>
                        <li><a href="#features" style="color: var(--white); text-decoration: none;">Features</a></li>
                        <li><a href="#how-it-works" style="color: var(--white); text-decoration: none;">How It Works</a></li>
                        <li><a href="#contact" style="color: var(--white); text-decoration: none;">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Contact</h3>
                    <p>Email: info@lifelink.com</p>
                    <p>Phone: (123) 456-7890</p>
                </div>
                <div>
                    <h3>Follow Us</h3>
                    <div style="display: flex; gap: 1rem;">
                        <a href="#" style="color: var(--white);"><i class="fab fa-facebook"></i></a>
                        <a href="#" style="color: var(--white);"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: var(--white);"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: var(--white);"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <p>&copy; 2024 LifeLink. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
