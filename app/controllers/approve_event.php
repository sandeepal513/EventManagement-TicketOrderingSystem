<?php
    include_once __DIR__ . '/../../config/constants.php';
    include_once ROOT . '/config/connection.php';
    session_start();

    // 1. Admin Role Check: Ensure user is an admin
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Set a message and redirect to login
        $_SESSION['error'] = "❌ You do not have permission to access this page.";
        // Redirect to login page or a general-purpose home page
        header("Location: " . BASE_URL . "app/views/auth/login_view.php"); 
        exit;
    }

    // Handle Approve/Reject
    if (isset($_GET['action'], $_GET['id'])) {
        $id = intval($_GET['id']);
        $action = $_GET['action'];
        $status = ($action === 'approve') ? 'approved' : 'rejected';

        $sql = "UPDATE events SET status='$status' WHERE event_id=$id";
        if ($conn->query($sql)) {
            header("Location: " . BASE_URL . "app/views/admin/admin_approve_events.php"); // reload page after action
            exit;
        } else {
            echo "Error updating status: " . $conn->error;
        }
    }

    // Initialize search filters
    $search_category = isset($_GET['search_category']) ? $_GET['search_category'] : '';
    $search_date = isset($_GET['search_date']) ? $_GET['search_date'] : '';


    $where_clauses = [];
    if ($search_category) {
        $where_clauses[] = "e.category_id = '$search_category'";
    }
    if ($search_date) {
        $where_clauses[] = "e.event_date = '$search_date'";
    }

    $where_sql = '';
    if (!empty($where_clauses)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
    }

    // Fetch events based on the selected filters
    $result = $conn->query("
        SELECT e.*, u.full_name AS organizer, c.category_name
        FROM events e
        LEFT JOIN users u ON e.organizer_id = u.user_id
        LEFT JOIN categories c ON e.category_id = c.category_id
        $where_sql
        ORDER BY e.event_date DESC
    ");
?>