<?php
session_start();

include_once './../config/connection.php';

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if request is POST and has required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['toggle_type']) || !isset($data['value'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$toggle_type = $data['toggle_type'];
$value = $data['value'] ? 1 : 0;

// Validate toggle type
$valid_toggles = ['email_alerts', 'sms_alerts'];
if (!in_array($toggle_type, $valid_toggles)) {
    echo json_encode(['success' => false, 'message' => 'Invalid toggle type']);
    exit();
}

// Update database
$sql = "UPDATE users SET $toggle_type = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $value, $user_id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    echo json_encode([
        'success' => true, 
        'message' => 'Notification preference updated',
        'toggle_type' => $toggle_type,
        'value' => $value
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update preference']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>