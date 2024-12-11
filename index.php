<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeLink - Connecting Lives Through Organ Donation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">Life<span style="color: var(--light-green);">Link</span></a>
            <div class="nav-links">
                <a href="#about">About</a>
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#contact">Contact</a>
                <a href="pages/login.php" class="btn btn-outline">Login</a>
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
                <a href="pages/donor-registration.php" class="btn btn-primary">Register as Donor</a>
                <a href="pages/recipient-registration.php" class="btn btn-outline" style="border-color: var(--white); color: var(--white);">Register as Recipient</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="section">
        <div class="container">
            <h2 class="text-center mb-3">Why Choose LifeLink?</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div class="card">
                    <i class="fas fa-heart" style="color: var(--primary-green); font-size: 2.5rem; margin-bottom: 1rem;"></i>
                    <h3>Save Lives</h3>
                    <p>Make a difference by donating organs to those in need</p>
                </div>
                <div class="card">
                    <i class="fas fa-hospital" style="color: var(--primary-blue); font-size: 2.5rem; margin-bottom: 1rem;"></i>
                    <h3>Hospital Network</h3>
                    <p>Connect with verified hospitals and medical professionals</p>
                </div>
                <div class="card">
                    <i class="fas fa-shield-alt" style="color: var(--primary-green); font-size: 2.5rem; margin-bottom: 1rem;"></i>
                    <h3>Secure Platform</h3>
                    <p>Your data is protected with the highest security standards</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section" style="background-color: var(--light-blue);">
        <div class="container">
            <h2 class="text-center mb-3">How It Works</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                <div class="card">
                    <div class="step-number" style="
                        background: var(--primary-blue);
                        color: white;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1rem;
                    ">1</div>
                    <h3>Register</h3>
                    <p>Create your account as a donor or recipient</p>
                </div>
                <div class="card">
                    <div class="step-number" style="
                        background: var(--primary-blue);
                        color: white;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1rem;
                    ">2</div>
                    <h3>Connect</h3>
                    <p>Match with compatible donors or recipients</p>
                </div>
                <div class="card">
                    <div class="step-number" style="
                        background: var(--primary-blue);
                        color: white;
                        width: 40px;
                        height: 40px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-bottom: 1rem;
                    ">3</div>
                    <h3>Save Lives</h3>
                    <p>Complete the donation process with verified hospitals</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section">
        <div class="container text-center">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                <div>
                    <h2 style="color: var(--primary-green);">1000+</h2>
                    <p>Registered Donors</p>
                </div>
                <div>
                    <h2 style="color: var(--primary-blue);">500+</h2>
                    <p>Successful Matches</p>
                </div>
                <div>
                    <h2 style="color: var(--primary-green);">50+</h2>
                    <p>Partner Hospitals</p>
                </div>
                <div>
                    <h2 style="color: var(--primary-blue);">100%</h2>
                    <p>Secure Platform</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section" style="background-color: var(--light-green);">
        <div class="container">
            <h2 class="text-center mb-3">Contact Us</h2>
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <form>
                    <div style="margin-bottom: 1rem;">
                        <label for="name">Name</label>
                        <input type="text" id="name" style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray); border-radius: 5px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="email">Email</label>
                        <input type="email" id="email" style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray); border-radius: 5px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="message">Message</label>
                        <textarea id="message" rows="4" style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray); border-radius: 5px;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
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
                        <a href="#" style="color: var(--white);"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3" style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                <p>&copy; 2024 LifeLink. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
