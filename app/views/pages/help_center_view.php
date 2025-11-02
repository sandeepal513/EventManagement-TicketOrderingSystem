<?php 
    include_once __DIR__ . '/../../../config/constants.php';
    include_once ROOT . '/config/connection.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Event Ticketing Help Center - Find answers to common questions about purchasing tickets, refunds, event entry, and account issues.">
    <title>Help Center - Event Ticketing System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/help_center.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/footer.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/css/navbar.css">
</head>
<body>

    <?php include_once ROOT . '/app/views/layouts/navbar.php'; ?>

    <section class="hero">
        <div class="container">
            <h1>How Can We Help You?</h1>
            <p>Search our help center or browse categories below</p>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for answers...">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </section>

    <main class="main-content">
        <div class="container">
            <div class="quick-links">
                <div class="quick-link-card" onclick="scrollToCategory('purchasing')">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Purchasing Tickets</h3>
                </div>
                <div class="quick-link-card" onclick="scrollToCategory('refunds')">
                    <i class="fas fa-undo"></i>
                    <h3>Refunds</h3>
                </div>
                <div class="quick-link-card" onclick="scrollToCategory('entry')">
                    <i class="fas fa-door-open"></i>
                    <h3>Event Entry</h3>
                </div>
                <div class="quick-link-card" onclick="scrollToCategory('account')">
                    <i class="fas fa-user"></i>
                    <h3>Account Issues</h3>
                </div>
            </div>

            <section class="faq-section">
                <h2>Frequently Asked Questions</h2>

                <div class="faq-category" id="purchasing">
                    <h3 class="category-title">
                        <i class="fas fa-shopping-cart"></i>
                        Purchasing Tickets
                    </h3>
                    
                    <div class="faq-item">
                        <div class="faq-question">
                            How do I purchase tickets?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            To purchase tickets, browse our events page, select your desired event, choose the number of tickets and seating preference, then proceed to checkout. You'll need to create an account or log in to complete your purchase. Follow the payment instructions and you'll receive a confirmation email with your tickets.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            What payment methods are accepted?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            We accept all major credit cards (Visa, MasterCard, American Express), debit cards, PayPal, and digital wallets like Apple Pay and Google Pay. All transactions are secured with SSL encryption for your safety.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Can I buy tickets for multiple events at once?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! You can add tickets from multiple events to your cart and complete the purchase in a single transaction. Simply browse events and add your desired tickets before proceeding to checkout.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Is there a purchase limit per transaction?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Most events have a limit of 8-10 tickets per transaction to ensure fair access. The specific limit will be displayed on the event page. For group bookings exceeding this limit, please contact our sales team.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Do I need an account to purchase tickets?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, an account is required to purchase tickets. This allows you to access your order history, manage your tickets, and receive important event updates. Registration is quick and free!
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Are there any booking fees?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            A small service fee is added to each ticket to cover payment processing and platform maintenance. The total amount including all fees will be clearly displayed before you complete your purchase.
                        </div>
                    </div>
                </div>

                <div class="faq-category" id="refunds">
                    <h3 class="category-title">
                        <i class="fas fa-undo"></i>
                        Refunds & Cancellations
                    </h3>

                    <div class="faq-item">
                        <div class="faq-question">
                            What is the refund policy?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Refund policies vary by event. Generally, tickets can be refunded up to 48 hours before the event start time. Service fees are non-refundable. Check the specific event page for detailed refund terms or contact support for assistance.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I request a refund?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Log into your account, go to "My Orders," select the order you wish to refund, and click "Request Refund." Follow the prompts to submit your request. You'll receive an email confirmation once processed.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            How long do refunds take to process?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Once approved, refunds are typically processed within 5-7 business days. The time for funds to appear in your account depends on your payment provider and may take an additional 3-5 business days.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            What if an event is cancelled or postponed?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            If an event is cancelled, you'll receive a full refund automatically within 7-10 business days. For postponed events, your tickets remain valid for the new date. If you can't attend the rescheduled date, you can request a refund.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Are tickets transferable to another event?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Tickets are generally not transferable between different events. However, you may be able to transfer tickets to another person for the same event. Check the event's terms or contact support for specific transfer options.
                        </div>
                    </div>
                </div>

                <div class="faq-category" id="entry">
                    <h3 class="category-title">
                        <i class="fas fa-door-open"></i>
                        Event Entry
                    </h3>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I access my tickets?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Your tickets are available in your account under "My Tickets" and will also be sent to your email. You can display them on your mobile device or print them. Each ticket has a unique QR code for entry.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Can I use digital tickets?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes! Digital tickets are fully accepted at all our events. Simply show the QR code on your mobile device at the entrance. We recommend downloading your tickets before arriving at the venue in case of connectivity issues.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            What should I bring to the event?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Bring your ticket (digital or printed) and a valid photo ID. Some events may have specific requirements - check your confirmation email and the event page for details about prohibited items or special entry requirements.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            What if I lose my ticket?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Don't worry! Log into your account to redownload your tickets, or check your email for the confirmation. If you're unable to access either, contact our support team with your order number for assistance.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Can I enter the event late?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Late entry policies vary by event. Most events allow entry after the start time, but some performances or shows may have restrictions on late seating. Check your event details or contact the venue directly.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Are there age restrictions for events?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Age restrictions vary by event. This information is clearly displayed on each event page. Common restrictions include 18+, 21+, or all ages with parental supervision required for minors. Valid ID may be required for verification.
                        </div>
                    </div>
                </div>

                <div class="faq-category" id="account">
                    <h3 class="category-title">
                        <i class="fas fa-user"></i>
                        Account Issues
                    </h3>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I create an account?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Click "Sign Up" in the top right corner, enter your email, create a password, and provide basic information. You'll receive a verification email - click the link to activate your account and start purchasing tickets!
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            I forgot my password, what should I do?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Click "Forgot Password" on the login page, enter your registered email, and we'll send you a password reset link. Follow the instructions in the email to create a new password. If you don't receive the email, check your spam folder.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I update my account information?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Log into your account and go to "Account Settings" or "Profile." Here you can update your name, email, phone number, password, and notification preferences. Remember to save your changes!
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Can I delete my account?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Yes, you can request account deletion through your account settings or by contacting support. Note that you must cancel or use all active tickets before deletion. This action is permanent and cannot be undone.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Why can't I log in?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Common issues include incorrect password, unverified email, or account suspension. Try resetting your password first. If the problem persists, contact support with your registered email for assistance.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I change my email address?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Go to Account Settings and update your email address. You'll need to verify the new email through a confirmation link we'll send. Your old email will remain active until verification is complete.
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h3 class="category-title">
                        <i class="fas fa-tools"></i>
                        Technical Issues
                    </h3>

                    <div class="faq-item">
                        <div class="faq-question">
                            The website isn't loading properly
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Try clearing your browser cache and cookies, or use a different browser. Ensure your internet connection is stable. If the problem continues, try accessing the site from a different device or contact technical support.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            Payment failed but money was deducted
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Sometimes a temporary hold is placed on your account even if the transaction fails. This usually releases within 3-5 business days. Check your account for the order confirmation. If charged without receiving tickets, contact support immediately with transaction details.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            I didn't receive my confirmation email
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Check your spam/junk folder first. If not there, verify the email address in your account is correct. You can also log into your account to view your orders. Contact support if you need the confirmation resent.
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            How do I contact technical support?
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            Visit our Contact Us page to reach our support team. You can email us at support@tickethub.com or call our support line at 1-800-TICKETS. Our team is available Monday-Friday 9AM-8PM EST and weekends 10AM-6PM EST.
                        </div>
                    </div>
                </div>
            </section>

            <section class="help-cta">
                <h2>Still Need Help?</h2>
                <p>Can't find the answer you're looking for? Our support team is here to help!</p>
                <a href="contact.php" class="cta-button">
                    <i class="fas fa-envelope"></i> Contact Support
                </a>
                
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <strong>Email</strong>
                        <span>support@tickethub.com</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-phone"></i>
                        <strong>Phone</strong>
                        <span>1-800-TICKETS</span>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-clock"></i>
                        <strong>Response Time</strong>
                        <span>Within 24 hours</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include_once ROOT . '/app/views/layouts/footer.php'; ?>

</body>
</html>
