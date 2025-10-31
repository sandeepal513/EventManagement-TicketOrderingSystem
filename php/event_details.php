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
SELECT e.*, c.category_name, u.full_name AS organizer_name 
FROM events e
LEFT JOIN categories c ON e.category_id = c.category_id
LEFT JOIN users u ON e.organizer_id = u.user_id
WHERE e.event_id = $event_id
";
$result = $conn->query($sql);
$event = $result->fetch_assoc();

// Fetch Ticket Types for this Event
$tickets_sql = "SELECT * FROM ticket_types WHERE event_id = $event_id";
$tickets = $conn->query($tickets_sql);

if (!$event) {
  echo "<h2>Event not found!</h2>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($event['event_name']) ?> | MyTicket</title>

  <link rel="stylesheet" href="../css/style.css">

  <style>

    /* -------------------------------------------------
     1. LOCK THE VIEWPORT – no scroll, no jump
     ------------------------------------------------- */
  html, body {
    margin:0; padding:0; height:100%; overflow:hidden;
  }

  /* -------------------------------------------------
     2. PAGE WRAPPER – the element that slides up
     ------------------------------------------------- */
  #page {
    position:fixed;            /* out of normal flow */
    top:100vh;                 /* start one viewport below */
    left:0; right:0;
    min-height:100vh;          /* at least a full screen */
    background:#fafafa;        /* same as your body bg */
    animation:slideUpPage 0.85s cubic-bezier(.2,.6,.4,1) forwards;
  }

  @keyframes slideUpPage {
    from { transform:translateY(0);   opacity:0; }
    to   { transform:translateY(-100vh); opacity:1; }
  }

  /* -------------------------------------------------
     3. RE-ENABLE SCROLL after the animation finishes
     ------------------------------------------------- */
  #page.done {
    position:static;
    animation:none;
    overflow-y:auto;           /* normal scrolling returns */
  }
  html.done, body.done { overflow:auto; }

  /* -------------------------------------------------
     4. OPTIONAL: fade-in the header a little later
     ------------------------------------------------- */
  header { animation:fadeIn 0.6s 0.3s forwards; opacity:0; }
  @keyframes fadeIn { to { opacity:1; } }







  </style>
</head>

<body>
 <div id="page">
  <div class="container">

    <div class="event-details">
      <div class="event-image">
        <img src="<?= htmlspecialchars($event['image_url'] ?? '../Resources/images/default.jpg') ?>"
          alt="<?= htmlspecialchars($event['event_name']) ?>">
      </div>
      <div class="info">
        <h1 class="event_name"><span class="db-value"><?= htmlspecialchars($event['event_name']) ?></span></h1>
        <p><strong>Category:</strong><span class="db-value">
            <?= htmlspecialchars($event['category_name']) ?></span></p>
        <p><strong>Organizer:</strong><span class="db-value">
          <?= htmlspecialchars($event['organizer_name']) ?></span></p>
        <p><strong>Date:</strong> <span class="db-value"><?= htmlspecialchars($event['event_date']) ?> </span>|
            <strong>Time:</strong> <span class="db-value"> <?= htmlspecialchars($event['event_time']) ?></span></p>
        <p><strong>Venue:</strong><span class="db-value"> <?= htmlspecialchars($event['venue']) ?></span></p>
        <p><strong>Status:</strong><span class="db-value"><?= ucfirst($event['status']) ?></span></p>
        <p><span class="db-value"><?= nl2br(htmlspecialchars($event['description'])) ?></span></p>



        <!-- Optional Agenda or Performer Section -->
        <h3>Agenda / Performer Info</h3>
        <p>Stay tuned for an exciting lineup of speakers and performers!</p>

        <!-- Google Map Embed -->
        <?php if (!empty($event['location_map_link'])): ?>
          <div class="map">
            <iframe src="<?= htmlspecialchars($event['location_map_link']) ?>" allowfullscreen></iframe>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Ticket Section -->
    <h2> Available Tickets</h2>
    <?php if ($tickets->num_rows > 0): ?>
      <form action="add_to_cart.php" method="POST">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <table class="price-table">
          <tr>
            <th>Type</th>
            <th>Price</th>
            <th>Available</th>
            <th>Quantity</th>
          </tr>
          <?php while ($ticket = $tickets->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($ticket['ticket_name']) ?></td>
              <td>£<?= number_format($ticket['price'], 2) ?></td>
              <td><?= $ticket['available_quantity'] ?></td>
              <td>
                <input type="number" name="quantity[<?= $ticket['ticket_type_id'] ?>]" min="0"
                  max="<?= $ticket['available_quantity'] ?>" value="0">
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
        <br>
        <button type="submit" class="add_cart_btn">Add to Cart</button>
      </form>
    <?php else: ?>
      <p>No tickets available for this event.</p>
    <?php endif; ?>

    <script>
      // Add click functionality
      document.querySelector('.add_cart_btn').addEventListener('click', function () {
        this.classList.add('clicked');
      });
    </script>



    <!-- Social Sharing -->
    <h3>Share this Event</h3>
    <div class="social-share">
      <a href="https://facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
        target="_blank"><img src="../Resources/images/facebook.png" alt="facebook_logo"></a>
      <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
        target="_blank"><img src="../Resources/images/x.png" alt="x_logo"></a>
      <a href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"
        target="_blank"><img src="../Resources/images/linkedin.png" alt="linkedin_logo"></a>
    </div>

    <!-- Event Carousel -->
    <h2>🔥 Other Events You May Like</h2>
    <div class="carousel-container">
      <div class="carousel">
        <?php
        // Fetch 5 other events (exclude the current one)
        $related_sql = "SELECT event_id, event_name, image_url FROM events WHERE event_id != $event_id LIMIT 5";
        $related_result = $conn->query($related_sql);

        if ($related_result->num_rows > 0):
          while ($row = $related_result->fetch_assoc()):
            ?>
            <div class="carousel-item">
              <a href="event_details.php?event_id=<?= $row['event_id'] ?>">
                <img src="<?= htmlspecialchars($row['image_url'] ?? '../Resources/images/default.jpg') ?>"
                  alt="<?= htmlspecialchars($row['event_name']) ?>">
                <p><?= htmlspecialchars($row['event_name']) ?></p>
              </a>
            </div>
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
  <>/div> <!-- End of #page -->
  <script>
    const carousel = document.querySelector('.carousel');
    const prevBtn = document.querySelector('.carousel-btn.prev');
    const nextBtn = document.querySelector('.carousel-btn.next');

    let scrollAmount = 0;
    const scrollStep = 220; // pixels per click

    nextBtn.addEventListener('click', () => {
      carousel.scrollBy({ left: scrollStep, behavior: 'smooth' });
    });

    prevBtn.addEventListener('click', () => {
      carousel.scrollBy({ left: -scrollStep, behavior: 'smooth' });
    });
  </script>

</body>

</html>

<?php $conn->close(); ?>