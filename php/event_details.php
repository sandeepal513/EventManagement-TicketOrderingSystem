<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticketing_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error)
  die("Connection failed: " . $conn->connect_error);

// Get Event ID from URL
$event_id = $_GET['event_id'] ?? 1;

// Fetch Event Details (with category and organizer)
$sql = "
SELECT e.*, c.category_name, u.full_name AS organizer_name, u.email AS organizer_email
FROM events e
LEFT JOIN categories c ON e.category_id = c.category_id
LEFT JOIN users u ON e.organizer_id = u.user_id
WHERE e.event_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

// Fetch Ticket Types for this Event
$tickets_sql = "SELECT * FROM ticket_types WHERE event_id = ? ORDER BY price ASC";
$stmt_tickets = $conn->prepare($tickets_sql);
$stmt_tickets->bind_param("i", $event_id);
$stmt_tickets->execute();
$tickets = $stmt_tickets->get_result();

if (!$event) {
  echo "<h2>Event not found!</h2>";
  exit;
}

// Calculate days until event
$event_datetime = new DateTime($event['event_date'] . ' ' . $event['event_time']);
$now = new DateTime();
$days_until = $now->diff($event_datetime)->days;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($event['event_name']) ?> | MyTicket</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      overflow: hidden;
      font-family: 'Montserrat', sans-serif;
      color: #2d3748;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    #page {
      position: fixed;
      top: 100vh;
      left: 0;
      right: 0;
      min-height: 100vh;
      background: #f7fafc;
      animation: slideUpPage 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      overflow-y: auto;
    }

    @keyframes slideUpPage {
      from { 
        transform: translateY(0);
        opacity: 0;
      }
      to { 
        transform: translateY(-100vh);
        opacity: 1;
      }
    }

    #page.done {
      position: static;
      animation: none;
    }

    html.done, body.done {
      overflow: auto;
    }

    /* Hero Section */
    .hero-section {
      position: relative;
      height: 500px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      overflow: hidden;
      animation: fadeIn 0.8s 0.2s forwards;
      opacity: 0;
    }

    @keyframes fadeIn {
      to { opacity: 1; }
    }

    .hero-image {
      position: absolute;
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.3;
    }

    .hero-overlay {
      position: absolute;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.9));
    }

    .hero-content {
      position: relative;
      max-width: 1200px;
      margin: 0 auto;
      padding: 80px 30px 40px;
      color: white;
      z-index: 2;
    }

    .event-badge {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      padding: 8px 20px;
      border-radius: 30px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .hero-title {
      font-size: 48px;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.2);
      line-height: 1.2;
    }

    .hero-meta {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      margin-top: 25px;
    }

    .meta-item {
      display: flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 12px 20px;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .meta-item i {
      font-size: 20px;
    }

    .countdown-timer {
      position: absolute;
      bottom: 30px;
      right: 30px;
      background: rgba(255, 255, 255, 0.95);
      color: #764ba2;
      padding: 20px 30px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .countdown-timer .days {
      font-size: 42px;
      font-weight: 800;
      line-height: 1;
    }

    .countdown-timer .label {
      font-size: 14px;
      font-weight: 600;
      margin-top: 5px;
      opacity: 0.8;
    }

    /* Main Container */
    .container {
      max-width: 1200px;
      margin: -60px auto 40px;
      padding: 0 30px;
      position: relative;
      z-index: 10;
    }

    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 30px;
      margin-top: 30px;
    }

    .main-content {
      background: white;
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }

    .sidebar {
      position: sticky;
      top: 30px;
      height: fit-content;
    }

    /* Section Styles */
    .section {
      margin-bottom: 50px;
    }

    .section-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 20px;
      color: #1a202c;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .section-title i {
      color: #667eea;
      font-size: 24px;
    }

    .event-description {
      font-size: 16px;
      line-height: 1.8;
      color: #4a5568;
      margin-bottom: 20px;
    }

    /* Info Cards */
    .info-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 40px;
    }

    .info-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
    }

    .info-card-title {
      font-size: 14px;
      opacity: 0.9;
      margin-bottom: 8px;
      font-weight: 500;
    }

    .info-card-value {
      font-size: 20px;
      font-weight: 700;
    }

    /* Map Section */
    .map-container {
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      height: 400px;
      margin-top: 20px;
    }

    .map-container iframe {
      width: 100%;
      height: 100%;
      border: none;
    }

    /* Ticket Section */
    .ticket-card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      margin-bottom: 20px;
    }

    .ticket-grid {
      display: grid;
      gap: 20px;
      margin-top: 25px;
    }

    .ticket-item {
      background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
      border: 2px solid #e2e8f0;
      border-radius: 15px;
      padding: 25px;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .ticket-item:hover {
      border-color: #667eea;
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
      transform: translateY(-3px);
    }

    .ticket-item.selected {
      border-color: #667eea;
      background: linear-gradient(135deg, #e9efff 0%, #f0e9ff 100%);
    }

    .ticket-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .ticket-name {
      font-size: 20px;
      font-weight: 700;
      color: #1a202c;
    }

    .ticket-price {
      font-size: 28px;
      font-weight: 800;
      color: #667eea;
    }

    .ticket-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
      padding-top: 15px;
      border-top: 1px solid #cbd5e0;
    }

    .ticket-available {
      font-size: 14px;
      color: #718096;
    }

    .ticket-available.low-stock {
      color: #e53e3e;
      font-weight: 600;
    }

    .quantity-selector {
      display: flex;
      align-items: center;
      gap: 15px;
      background: white;
      padding: 8px 15px;
      border-radius: 30px;
      border: 2px solid #e2e8f0;
    }

    .quantity-btn {
      width: 32px;
      height: 32px;
      border: none;
      background: #667eea;
      color: white;
      border-radius: 50%;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .quantity-btn:hover {
      background: #764ba2;
      transform: scale(1.1);
    }

    .quantity-input {
      width: 50px;
      text-align: center;
      border: none;
      font-size: 16px;
      font-weight: 700;
      color: #1a202c;
    }

    /* Cart Summary */
    .cart-summary {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      position: sticky;
      top: 30px;
    }

    .summary-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 25px;
      color: #1a202c;
    }

    .summary-item {
      display: flex;
      justify-content: space-between;
      padding: 15px 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .summary-total {
      display: flex;
      justify-content: space-between;
      padding: 20px 0;
      font-size: 24px;
      font-weight: 800;
      color: #1a202c;
      margin-top: 10px;
    }

    .checkout-btn {
      width: 100%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 18px;
      border-radius: 12px;
      font-size: 18px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 20px;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .checkout-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
    }

    .checkout-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    /* Social Share */
    .social-share {
      display: flex;
      gap: 15px;
      margin-top: 20px;
    }

    .social-btn {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-decoration: none;
      transition: all 0.3s ease;
      font-size: 20px;
    }

    .social-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .social-btn.facebook { background: #1877f2; }
    .social-btn.twitter { background: #000; }
    .social-btn.linkedin { background: #0077b5; }
    .social-btn.whatsapp { background: #25d366; }

    /* Carousel */
    .carousel-section {
      background: white;
      border-radius: 20px;
      padding: 40px;
      margin-top: 40px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    }

    .carousel-container {
      position: relative;
      margin-top: 30px;
    }

    .carousel {
      display: flex;
      gap: 20px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 10px 0;
      scrollbar-width: none;
    }

    .carousel::-webkit-scrollbar {
      display: none;
    }

    .carousel-item {
      flex: 0 0 250px;
      background: white;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      text-decoration: none;
      color: inherit;
    }

    .carousel-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
    }

    .carousel-item img {
      width: 100%;
      height: 180px;
      object-fit: cover;
    }

    .carousel-item p {
      padding: 20px;
      font-weight: 600;
      color: #1a202c;
    }

    .carousel-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 50px;
      height: 50px;
      border: none;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 50%;
      cursor: pointer;
      font-size: 20px;
      z-index: 10;
      transition: all 0.3s ease;
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .carousel-btn:hover {
      transform: translateY(-50%) scale(1.1);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .carousel-btn.prev { left: -25px; }
    .carousel-btn.next { right: -25px; }

    /* Fade In on Scroll Animations */
    .fade-in-section {
      opacity: 0;
      transform: translateY(30px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    .fade-in-section.is-visible {
      opacity: 1;
      transform: translateY(0);
    }

    .fade-in-left {
      opacity: 0;
      transform: translateX(-30px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    .fade-in-left.is-visible {
      opacity: 1;
      transform: translateX(0);
    }

    .fade-in-right {
      opacity: 0;
      transform: translateX(30px);
      transition: opacity 0.8s ease-out, transform 0.8s ease-out;
    }

    .fade-in-right.is-visible {
      opacity: 1;
      transform: translateX(0);
    }

    .stagger-animation > * {
      opacity: 0;
      transform: translateY(20px);
      transition: opacity 0.6s ease-out, transform 0.6s ease-out;
    }

    .stagger-animation.is-visible > * {
      opacity: 1;
      transform: translateY(0);
    }

    .stagger-animation.is-visible > *:nth-child(1) { transition-delay: 0.1s; }
    .stagger-animation.is-visible > *:nth-child(2) { transition-delay: 0.2s; }
    .stagger-animation.is-visible > *:nth-child(3) { transition-delay: 0.3s; }
    .stagger-animation.is-visible > *:nth-child(4) { transition-delay: 0.4s; }
    .stagger-animation.is-visible > *:nth-child(5) { transition-delay: 0.5s; }
    .stagger-animation.is-visible > *:nth-child(6) { transition-delay: 0.6s; }

    /* Responsive */
    @media (max-width: 968px) {
      .content-grid {
        grid-template-columns: 1fr;
      }

      .sidebar {
        position: static;
      }

      .hero-title {
        font-size: 36px;
      }

      .countdown-timer {
        position: static;
        margin-top: 20px;
      }
    }

    @media (max-width: 640px) {
      .hero-content {
        padding: 60px 20px 30px;
      }

      .hero-title {
        font-size: 28px;
      }

      .main-content {
        padding: 25px;
      }

      .carousel-btn {
        width: 40px;
        height: 40px;
        font-size: 16px;
      }
    }
  </style>
</head>

<body>
  <div id="page">
    <!-- Hero Section -->
    <div class="hero-section">
      <img src="<?= htmlspecialchars($event['image_url'] ?? '../Resources/images/default.jpg') ?>" 
           alt="<?= htmlspecialchars($event['event_name']) ?>" 
           class="hero-image">
      <div class="hero-overlay"></div>
      
      <div class="hero-content">
        <div class="event-badge">
          <i class="fas fa-star"></i> <?= htmlspecialchars($event['category_name']) ?>
        </div>
        
        <h1 class="hero-title"><?= htmlspecialchars($event['event_name']) ?></h1>
        
        <div class="hero-meta">
          <div class="meta-item">
            <i class="fas fa-calendar-alt"></i>
            <span><?= date('l, F j, Y', strtotime($event['event_date'])) ?></span>
          </div>
          <div class="meta-item">
            <i class="fas fa-clock"></i>
            <span><?= date('g:i A', strtotime($event['event_time'])) ?></span>
          </div>
          <div class="meta-item">
            <i class="fas fa-map-marker-alt"></i>
            <span><?= htmlspecialchars($event['venue']) ?></span>
          </div>
        </div>
      </div>

      <?php if ($days_until > 0): ?>
      <div class="countdown-timer">
        <div class="days"><?= $days_until ?></div>
        <div class="label">DAYS TO GO</div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Main Container -->
    <div class="container">
      <div class="content-grid">
        <!-- Main Content -->
        <div class="main-content">
          <!-- Event Info Cards -->
          <div class="info-cards stagger-animation fade-in-section">
            <div class="info-card">
              <div class="info-card-title">Organized by</div>
              <div class="info-card-value"><?= htmlspecialchars($event['organizer_name']) ?></div>
            </div>
            <div class="info-card">
              <div class="info-card-title">Status</div>
              <div class="info-card-value"><?= ucfirst($event['status']) ?></div>
            </div>
            <div class="info-card">
              <div class="info-card-title">Event Type</div>
              <div class="info-card-value"><?= htmlspecialchars($event['category_name']) ?></div>
            </div>
          </div>

          <!-- About Event -->
          <div class="section fade-in-section">
            <h2 class="section-title">
              <i class="fas fa-info-circle"></i>
              About This Event
            </h2>
            <p class="event-description"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
          </div>

          <!-- Venue & Location -->
          <?php if (!empty($event['location_map_link'])): ?>
          <div class="section fade-in-section">
            <h2 class="section-title">
              <i class="fas fa-map-marked-alt"></i>
              Venue & Location
            </h2>
            <p class="event-description">
              <strong><?= htmlspecialchars($event['venue']) ?></strong><br>
              Find your way to the venue easily with the map below.
            </p>
            <div class="map-container">
              <iframe src="<?= htmlspecialchars($event['location_map_link']) ?>" 
                      allowfullscreen 
                      loading="lazy"></iframe>
            </div>
          </div>
          <?php endif; ?>

          <!-- Share Event -->
          <div class="section fade-in-section">
            <h2 class="section-title">
              <i class="fas fa-share-alt"></i>
              Share This Event
            </h2>
            <div class="social-share">
              <a href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                 target="_blank" 
                 class="social-btn facebook">
                <i class="fab fa-facebook-f"></i>
              </a>
              <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($event['event_name']) ?>" 
                 target="_blank" 
                 class="social-btn twitter">
                <i class="fab fa-x-twitter"></i>
              </a>
              <a href="https://www.linkedin.com/shareArticle?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($event['event_name']) ?>" 
                 target="_blank" 
                 class="social-btn linkedin">
                <i class="fab fa-linkedin-in"></i>
              </a>
              <a href="https://wa.me/?text=<?= urlencode($event['event_name'] . ' - ' . $_SERVER['REQUEST_URI']) ?>" 
                 target="_blank" 
                 class="social-btn whatsapp">
                <i class="fab fa-whatsapp"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar fade-in-right">
          <div class="cart-summary">
            <h3 class="summary-title">Select Tickets</h3>
            <form action="add_to_cart.php" method="POST" id="ticketForm">
              <input type="hidden" name="event_id" value="<?= $event_id ?>">
              
              <div class="ticket-grid stagger-animation">
                <?php if ($tickets->num_rows > 0): ?>
                  <?php while ($ticket = $tickets->fetch_assoc()): ?>
                    <div class="ticket-item" data-ticket-id="<?= $ticket['ticket_type_id'] ?>">
                      <div class="ticket-header">
                        <div class="ticket-name"><?= htmlspecialchars($ticket['ticket_name']) ?></div>
                        <div class="ticket-price">£<?= number_format($ticket['price'], 2) ?></div>
                      </div>
                      <div class="ticket-info">
                        <span class="ticket-available <?= $ticket['available_quantity'] < 10 ? 'low-stock' : '' ?>">
                          <?= $ticket['available_quantity'] ?> available
                        </span>
                        <div class="quantity-selector">
                          <button type="button" class="quantity-btn minus">−</button>
                          <input type="number" 
                                 class="quantity-input" 
                                 name="quantity[<?= $ticket['ticket_type_id'] ?>]" 
                                 value="0" 
                                 min="0" 
                                 max="<?= $ticket['available_quantity'] ?>"
                                 data-price="<?= $ticket['price'] ?>"
                                 readonly>
                          <button type="button" class="quantity-btn plus">+</button>
                        </div>
                      </div>
                    </div>
                  <?php endwhile; ?>
                <?php else: ?>
                  <p>No tickets available for this event.</p>
                <?php endif; ?>
              </div>

              <div class="summary-total" id="totalSection" style="display:none;">
                <span>Total:</span>
                <span id="totalAmount">£0.00</span>
              </div>

              <button type="submit" class="checkout-btn" id="checkoutBtn" disabled>
                <i class="fas fa-shopping-cart"></i> Add to Cart
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Related Events Carousel -->
      <div class="carousel-section fade-in-section">
        <h2 class="section-title">
          <i class="fas fa-fire"></i>
          Other Events You May Like
        </h2>
        <div class="carousel-container">
          <div class="carousel">
            <?php
            $related_sql = "SELECT event_id, event_name, image_url FROM events WHERE event_id != ? AND status = 'upcoming' LIMIT 8";
            $stmt_related = $conn->prepare($related_sql);
            $stmt_related->bind_param("i", $event_id);
            $stmt_related->execute();
            $related_result = $stmt_related->get_result();

            if ($related_result->num_rows > 0):
              while ($row = $related_result->fetch_assoc()):
            ?>
                <a href="event_details.php?event_id=<?= $row['event_id'] ?>" class="carousel-item">
                  <img src="<?= htmlspecialchars($row['image_url'] ?? '../Resources/images/default.jpg') ?>" 
                       alt="<?= htmlspecialchars($row['event_name']) ?>">
                  <p><?= htmlspecialchars($row['event_name']) ?></p>
                </a>
            <?php
              endwhile;
            else:
              echo "<p>    No other events  found.</p>";
            endif;
            ?>
          </div>
          <button class="carousel-btn prev">&#10094;</button>
          <button class="carousel-btn next">&#10095;</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Page animation completion
    setTimeout(() => {
      document.getElementById('page').classList.add('done');
      document.documentElement.classList.add('done');
      document.body.classList.add('done');
      
      // Initialize scroll animations after page loads
      initScrollAnimations();
    }, 900);

    // Fade in on scroll functionality
    function initScrollAnimations() {
      const faders = document.querySelectorAll('.fade-in-section, .fade-in-left, .fade-in-right, .stagger-animation');
      
      const appearOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
      };

      const appearOnScroll = new IntersectionObserver(function(entries, appearOnScroll) {
        entries.forEach(entry => {
          if (!entry.isIntersecting) {
            return;
          } else {
            entry.target.classList.add('is-visible');
            appearOnScroll.unobserve(entry.target);
          }
        });
      }, appearOptions);

      faders.forEach(fader => {
        appearOnScroll.observe(fader);
      });
    }

    // Ticket quantity management
    document.querySelectorAll('.ticket-item').forEach(item => {
      const minusBtn = item.querySelector('.minus');
      const plusBtn = item.querySelector('.plus');
      const input = item.querySelector('.quantity-input');
      const max = parseInt(input.getAttribute('max'));

      minusBtn.addEventListener('click', () => {
        let val = parseInt(input.value);
        if (val > 0) {
          input.value = val - 1;
          updateTicketSelection(item, input.value > 0);
          updateTotal();
        }
      });

      plusBtn.addEventListener('click', () => {
        let val = parseInt(input.value);
        if (val < max) {
          input.value = val + 1;
          updateTicketSelection(item, true);
          updateTotal();
        }
      });
    });

    function updateTicketSelection(item, selected) {
      if (selected) {
        item.classList.add('selected');
      } else {
        item.classList.remove('selected');
      }
    }

    function updateTotal() {
      let total = 0;
      let hasTickets = false;

      document.querySelectorAll('.quantity-input').forEach(input => {
        const quantity = parseInt(input.value);
        const price = parseFloat(input.getAttribute('data-price'));
        if (quantity > 0) {
          total += quantity * price;
          hasTickets = true;
        }
      });

      const totalSection = document.getElementById('totalSection');
      const totalAmount = document.getElementById('totalAmount');
      const checkoutBtn = document.getElementById('checkoutBtn');

      if (hasTickets) {
        totalSection.style.display = 'flex';
        totalAmount.textContent = '£' + total.toFixed(2);
        checkoutBtn.disabled = false;
      } else {
        totalSection.style.display = 'none';
        checkoutBtn.disabled = true;
      }
    }

    // Carousel functionality
    const carousel = document.querySelector('.carousel');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');

    if (carousel && prevBtn && nextBtn) {
      nextBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: 270, behavior: 'smooth' });
      });

      prevBtn.addEventListener('click', () => {
        carousel.scrollBy({ left: -270, behavior: 'smooth' });
      });
    }

    // Form validation
    document.getElementById('ticketForm')?.addEventListener('submit', function(e) {
      let hasSelection = false;
      document.querySelectorAll('.quantity-input').forEach(input => {
        if (parseInt(input.value) > 0) {
          hasSelection = true;
        }
      });

      if (!hasSelection) {
        e.preventDefault();
        alert('Please select at least one ticket before adding to cart.');
      }
    });
  </script>
</body>
</html>

<?php $conn->close(); ?>