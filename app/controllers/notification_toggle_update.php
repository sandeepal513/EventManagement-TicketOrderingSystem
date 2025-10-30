<?php
session_start();

// Include constants for consistent path handling
include_once __DIR__ . '/../../config/constants.php';

// Set proper JSON headers
header('Content-Type: application/json');

include_once ROOT . '/config/connection.php';

// Check if user is logged in
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if request is POST and has required data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check for JSON parsing errors
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit();
}

if (!isset($data['toggle_type']) || !isset($data['value'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

$user_id = $_SESSION['user_id'];
$toggle_type = $data['toggle_type'];
$value = $data['value'] ? 1 : 0;

// Validate toggle type (whitelist approach for security)
$valid_toggles = ['email_alerts', 'sms_alerts'];
if (!in_array($toggle_type, $valid_toggles)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid toggle type']);
    exit();
}

// Check database connection
if (!$conn || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

// Update database using prepared statement to prevent SQL injection
// Using prepared statement with column name requires special handling
$sql = "UPDATE users SET `$toggle_type` = ? WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $value, $user_id);
$result = mysqli_stmt_execute($stmt);

if ($result) {
    // Check if any row was actually updated
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Notification preference updated successfully',
        'toggle_type' => $toggle_type,
        'value' => $value,
        'affected_rows' => $affected_rows
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to update preference: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>