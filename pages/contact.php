<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Contact Us | Event Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
  integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
  crossorigin="anonymous">
  <link rel="stylesheet" href="contact.css" /> 
  <link rel="stylesheet" href="contact.css?v=<?php echo time(); ?>" />
</head>
<body style="background-color: rgba(208, 225, 244, 1);">

<section class="contact-section">
  <h2>Contact Us</h2>
  <p class="intro-text">Have a question or need help? Get in touch with us — we’re happy to assist!</p>

  <div class="contact-flex">
    <!-- Contact Form -->
    <div class="contact-form">
  <form action="sendmail.php" method="POST">
        <div class="input-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Enter your name" value="<?= $_SESSION['form_data']['name'] ?? '' ?>"  />
          <?php if (isset($_SESSION['errors']['name'])): ?>
            <span class="error"><?= $_SESSION['errors']['name'] ?></span>
          <?php endif; ?>
        </div>

        <div class="input-group">
          <label>Email Address</label>
          <input type="email" name="email" placeholder="Enter your email" value="<?= $_SESSION['form_data']['email'] ?? '' ?>"  />
          <?php if (isset($_SESSION['errors']['email'])): ?>
            <span class="error"><?= $_SESSION['errors']['email'] ?></span>
          <?php endif; ?>
        </div>

        <div class="input-group">
          <label>Phone Number</label>
          <input type="text" name="phone" placeholder="Optional" value="<?= $_SESSION['form_data']['phone'] ?? '' ?>" />
          <?php if (isset($_SESSION['errors']['phone'])): ?>
            <span class="error"><?= $_SESSION['errors']['phone'] ?></span>
          <?php endif; ?>
        </div>

        <div class="input-group">
          <label>Address</label>
          <textarea name="address" rows="2" placeholder="Type your Address here..." ><?= $_SESSION['form_data']['address'] ?? '' ?></textarea>
          <?php if (isset($_SESSION['errors']['address'])): ?>
            <span class="error"><?= $_SESSION['errors']['address'] ?></span>
          <?php endif; ?>
        </div>

        <div class="input-group">
          <label>Message</label>
          <textarea name="message" rows="3" placeholder="Type your message here..." ><?= $_SESSION['form_data']['message'] ?? '' ?></textarea>
          <?php if (isset($_SESSION['errors']['message'])): ?>
            <span class="error"><?= $_SESSION['errors']['message'] ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" name="send">Send Message</button>
      </form>
    </div>

    <!-- Contact Info -->
    <div class="contact-info">
      <h3>Our Contact Information</h3>
      <p><strong>Address:</strong><br /> 123 Main Street, Colombo, Sri Lanka</p>
      <p><strong>Phone:</strong><br /> +94 71 234 5678</p>
      <p><strong>Email:</strong><br /> event@gamail.com</p>
      <p><strong>Working Hours:</strong><br /> Mon–Fri: 9:00 AM – 6:00 PM</p>

      <div class="social-links">
        <h5>Follow Us</h5>
        <a href="https://web.facebook.com/?_rdc=1&_rdr">Facebook</a> |
        <a href="https://www.instagram.com/accounts/login/">Instagram</a> |
        <a href="https://x.com/i/flow/login?lang=en-id">Twitter</a>
      </div>
    </div>
  </div>
</section>

<!-- Google Map -->
<div class="map-container">
  <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.8354670607213!2d79.8608226!3d6.918985999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae2591ff1f845a7%3A0x6b82585f9d78e720!2sColombo!5e0!3m2!1sen!2slk!4v1700000000000!5m2!1sen!2slk"
    width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</div>

<section class="faq-section" id="faq">
  <h2>Frequently Asked Questions</h2>
  <p class="intro-text">Before reaching out, check if your question is answered here. If not, we're just a message away!</p>
  
  <div class="accordion" id="faqAccordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="headingOne">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
          How do I book tickets for an event?
        </button>
      </h2>
      <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Browse our <a href="event.php">Events page</a>, select your event, choose tickets, and complete payment via our secure gateway. You'll receive a confirmation email instantly. Need help? Use our quick booking widget!
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="headingTwo">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          What is your refund policy?
        </button>
      </h2>
      <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Refunds are available up to 48 hours before the event start time, minus a 10% processing fee. Contact us with your ticket ID for processing. No refunds after the event.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="headingThree">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
          Can I change my booking details?
        </button>
      </h2>
      <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          Yes, log into your <a href="dashboard.php">dashboard</a> to edit name/email up to 24 hours before the event. For other changes, email us at support@eventtickets.lk.
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="headingFour">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
          What payment methods do you accept?
        </button>
      </h2>
      <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
        <div class="accordion-body">
          We accept Visa, MasterCard All transactions are encrypted and secure.
        </div>
      </div>
    </div>

    </div>

  <div class="faq-cta">
    <p>Still have questions? <a href="#contact-form">Get in touch</a> — we'll respond within 24 hours.</p>
  </div>
</section>




<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
crossorigin="anonymous"></script>


<!--Start of Tawk.to Script-->
<script type="text/javascript">
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/68f5b156a200291956076fb1/1j7vrpf0j';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
  })();
</script>

<script>
  var messageText = "<?= $_SESSION['status'] ?? '' ?>";

  if (messageText != ''){
    Swal.fire({
      title: "Success!", 
      text: messageText,
      icon: "success",
    });
    
    <?php 
  
    unset($_SESSION['status']); 
    unset($_SESSION['form_data']); 
    ?> 
  }


  <?php
  if (isset($_SESSION['errors'])) {
   
    echo 'window.onload = function() { Swal.fire({ title: "Error!", text: "Please fix the errors below.", icon: "error" }); };';
    
    unset($_SESSION['errors']); 
  }
  ?>
</script>
</body>
</html>

