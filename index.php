<?php
    include_once './config/constants.php';
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSphere - Find and Book Events</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <h1>EventSphere</h1>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active">Home</a></li>
            <li><a href="#">Events</a></li>
            <li class="dropdown">
                <a href="#">Categories ▼</a>
                <div class="dropdown-content">
                    <a href="#">Music</a>
                    <a href="#">Sports</a>
                    <a href="#">Tech</a>
                </div>
            </li>
        </ul>
        <div class="nav-right">
            <input type="text" class="search-bar" placeholder="Search events...">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                    include_once ROOT . '/config/connection.php';
                    include_once ROOT . '/app/models/user_model.php';
                    $user = getUserById($conn, $_SESSION['user_id']);
                    $profile_pic_src = !empty($user['profile_pic']) ? BASE_URL . 'public/upload/profile_pic/' . htmlspecialchars($user['profile_pic']) : BASE_URL . 'public/assets/images/sys_img/default_profile.png';
                ?>
                <a href="<?php echo BASE_URL; ?>app/controllers/profile_controller.php" class="nav-btn">
                    <img src="<?php echo $profile_pic_src; ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 5px;">
                </a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>app/views/auth/login_view.php" class="nav-btn">Login / Sign Up</a>
            <?php endif; ?>
            <a href="" class="nav-btn">Contact Us</a>
            <a href="" class="nav-btn">About Us</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Find and Book Events Near You</h1>
            <p>Discover unforgettable experiences with EventSphere</p>
            <a href="#" class="cta-btn">Explore Events</a>
        </div>
    </section>

    <!-- Search Bar + Category Filters -->
    <section class="search-filter">
        <div class="search-container">
            <input type="text" class="main-search" placeholder="Search for events...">
            <button class="search-btn">Search</button>
        </div>
        <div class="category-filters">
            <button class="filter-btn" data-category="all">All</button>
            <button class="filter-btn" data-category="music">Music</button>
            <button class="filter-btn" data-category="sports">Sports</button>
            <button class="filter-btn" data-category="tech">Tech</button>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section class="events">
        <h2>Trending Events</h2>
        <div class="event-grid">
            <div class="event-card">
                <img src="https://via.placeholder.com/300x200" alt="Event 1">
                <h3>Live Music Festival</h3>
                <p>Nov 15, 2025 | New York, NY</p>
                <a href="#" class="book-btn">Book Now</a>
            </div>
            <div class="event-card">
                <img src="https://via.placeholder.com/300x200" alt="Event 2">
                <h3>Tech Conference 2025</h3>
                <p>Dec 10, 2025 | San Francisco, CA</p>
                <a href="#" class="book-btn">Book Now</a>
            </div>
            <div class="event-card">
                <img src="https://via.placeholder.com/300x200" alt="Event 3">
                <h3>Marathon Run</h3>
                <p>Jan 20, 2026 | Boston, MA</p>
                <a href="#" class="book-btn">Book Now</a>
            </div>
            <div class="event-card">
                <img src="https://via.placeholder.com/300x200" alt="Event 4">
                <h3>Jazz Night</h3>
                <p>Feb 5, 2026 | Chicago, IL</p>
                <a href="#" class="book-btn">Book Now</a>
            </div>
        </div>
    </section>

    <!-- Testimonials and Statistics Section -->
    <section class="testimonials-stats">
        <h2>What People Are Saying</h2>
        <div class="stats">
            <div class="stat-item">
                <h3>10,000+</h3>
                <p>Tickets sold this month</p>
            </div>
            <div class="stat-item">
                <h3>500+</h3>
                <p>Trusted by organizers</p>
            </div>
        </div>
        <div class="testimonials">
            <div class="testimonial-card">
                <p>"EventSphere made booking my concert tickets so easy!"</p>
                <h4>– Sarah M.</h4>
            </div>
            <div class="testimonial-card">
                <p>"I found the perfect tech event in minutes. Highly recommend!"</p>
                <h4>– John D.</h4>
            </div>
            <div class="testimonial-card">
                <p>"The best platform for discovering local events!"</p>
                <h4>– Emily R.</h4>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-links">
            <a href="#">Home</a>
            <a href="#">Events</a>
            <a href="#">Categories</a>
            <a href="#">Contact Us</a>
            <a href="#">About Us</a>
        </div>
        <div class="social-icons">
            <a href="#">Facebook</a>
            <a href="#">Twitter</a>
            <a href="#">Instagram</a>
        </div>
        <p>&copy; 2025 EventSphere. All rights reserved.</p>
    </footer>

    <script src="<?php echo BASE_URL; ?>public/assets/css/home.css"></script>
</body>
</html>