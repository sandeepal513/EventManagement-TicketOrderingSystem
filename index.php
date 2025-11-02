<?php
    include_once './config/constants.php';
    include_once ROOT . '/app/views/layouts/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSphere - Find and Book Events</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/home.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
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

    <script src="<?php echo BASE_URL; ?>public/assets/css/home.css"></script>
</body>
</html>