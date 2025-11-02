<?php
  include_once __DIR__. '/../../../config/constants.php';

  include_once ROOT . '/app/controllers/event_details_controller.php';

  $image_path = BASE_URL . 'public/upload/event_imgs/';
  $default_image = $image_path . 'default_event_image.png'; // You can create a default image here
  $image_url = $event['image_url'] ? $image_path . $event['image_url'] : $default_image;
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
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/event_details.css">
</head>

<body>
  <div id="page">
    <!-- Hero Section -->
    <div class="hero-section">
      <img src="<?= htmlspecialchars($image_url) ?>" 
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

                      <?php 
                          if ($ticket['ticket_type'] == 'GA')  {
                            $ticket_name = 'General Admission';
                          } elseif ($ticket['ticket_type'] == 'VIP') {
                            $ticket_name = 'VIP';
                          } else {
                            $ticket_name = $ticket['ticket_name'];
                          }
                        ?>
                        <div class="ticket-name"><?= htmlspecialchars($ticket_name) ?></div>
                        <div class="ticket-price">LKR <?= number_format($ticket['price'], 2) ?></div>
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
                <span id="totalAmount">LKR0.00</span>
              </div>

              <button type="submit" class="checkout-btn" id="checkoutBtn" disabled>
                <i class="fas fa-shopping-cart"></i>  Book Now
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
            $related_sql = "SELECT event_id, event_name, image_url FROM events WHERE event_id != ? AND status = 'approved' LIMIT 8";
            $stmt_related = $conn->prepare($related_sql);
            $stmt_related->bind_param("i", $event_id);
            $stmt_related->execute();
            $related_result = $stmt_related->get_result();

            if ($related_result->num_rows > 0):
              while ($row = $related_result->fetch_assoc()):
            ?>
                <a href="<?php echo BASE_URL; ?>app/views/pages/event_details_view.php?event_id=<?= $row['event_id'] ?>" class="carousel-item">
                  <img src="<?= htmlspecialchars($row['image_url'] ?? BASE_URL . 'public/assets/images/default.png') ?>" 
                       alt="<?= htmlspecialchars($row['event_name']) ?>">
                  <p><?= htmlspecialchars($row['event_name']) ?></p>
                </a>
            <?php
              endwhile;
            else:
              echo "<p>No other events found.</p>";
            endif;
            ?>
          </div>
          <button class="carousel-btn prev">&#10094;</button>
          <button class="carousel-btn next">&#10095;</button>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo BASE_URL; ?>public/assets/js/event_details.js"></script>
</body>
</html>

<?php $conn->close(); ?>