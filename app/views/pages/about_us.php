<?php
        include_once __DIR__ . '/../../../config/constants.php';
        session_start();
        include_once ROOT . '/app/views/layouts/navbar.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EventSphere</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/about_us.css">
</head>
<body>
    <section class="hero-section">
        <div class="hero-content" data-aos="fade-up">
            <h1>About EventSphere</h1>
            <p>Connecting People Through Events</p>
        </div>
    </section>

    <section class="story-section section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Our Story</h2>
            <div class="story-content" data-aos="fade-up" data-aos-delay="100">
                <p>EventSphere was born from a simple idea: finding and booking events should be as exciting as attending them. What started as a small passion project in 2018 has grown into a thriving platform that connects thousands of people with unforgettable experiences every day.</p>
                <p>Our mission is to make event discovery simple, fun, and accessible to everyone. Whether you're looking for a local concert, a tech conference, or a community gathering, we're here to help you find your next amazing experience.</p>
                <p>Today, we're proud to serve over 500 organizers and have helped sell more than 10,000 tickets this month alone. But beyond the numbers, what drives us is the joy of bringing people together and creating memories that last a lifetime.</p>
            </div>
        </div>
    </section>

    <section class="team-section section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Meet Our Team</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">The passionate people behind EventSphere</p>
            <div class="team-grid">
                <div class="team-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="team-photo">AJ</div>
                    <h3 class="team-name">Alex Johnson</h3>
                    <p class="team-role">Founder & CEO</p>
                </div>
                <div class="team-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="team-photo">SC</div>
                    <h3 class="team-name">Sarah Chen</h3>
                    <p class="team-role">Head of Product</p>
                </div>
                <div class="team-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="team-photo">MP</div>
                    <h3 class="team-name">Marcus Patel</h3>
                    <p class="team-role">Lead Developer</p>
                </div>
                <div class="team-card" data-aos="fade-up" data-aos-delay="500">
                    <div class="team-photo">EW</div>
                    <h3 class="team-name">Emily Williams</h3>
                    <p class="team-role">Community Manager</p>
                </div>
            </div>
        </div>
    </section>

    <section class="vision-mission-section section">
        <div class="container">
            <h2 class="section-title" data-aos="fade-up">Vision & Mission</h2>
            <div class="vision-mission-grid">
                <div class="vm-card" data-aos="fade-right" data-aos-delay="200">
                    <div class="vm-icon">🎯</div>
                    <h3 class="vm-title">Our Vision</h3>
                    <p class="vm-text">To become the world's most trusted platform for event discovery, where every person can easily find experiences that inspire, connect, and transform their lives.</p>
                </div>
                <div class="vm-card" data-aos="fade-left" data-aos-delay="300">
                    <div class="vm-icon">🚀</div>
                    <h3 class="vm-title">Our Mission</h3>
                    <p class="vm-text">To simplify event booking and empower organizers with the tools they need to create unforgettable experiences, while fostering a vibrant community of event-goers worldwide.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="cta-section">
        <div class="cta-content" data-aos="zoom-in">
            <h2 class="cta-title">Join Our Journey</h2>
            <p class="cta-text">Ready to discover amazing events or host your own? Let's make it happen together.</p>
            <a href="#contact" class="cta-button"><span>Get In Touch</span></a>
        </div>
    </section>

    <footer>
        <div class="footer-links">
            <a href="#home">Home</a>
            <a href="#events">Events</a>
            <a href="#categories">Categories</a>
            <a href="#contact">Contact Us</a>
            <a href="#about">About Us</a>
        </div>
        <div class="social-links">
            <a href="#facebook">Facebook</a>
            <a href="#twitter">Twitter</a>
            <a href="#instagram">Instagram</a>
        </div>
        <p class="copyright">© 2025 EventSphere. All rights reserved.</p>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    </script>
</body>
</html>