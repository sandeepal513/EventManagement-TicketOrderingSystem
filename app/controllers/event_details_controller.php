<?php

  include_once __DIR__ . '/../../config/constants.php';
  include_once ROOT . '/config/connection.php';

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