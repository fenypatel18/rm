<?php
/*
 * File: /user/update_progress.php
 *
 * This script handles AJAX requests to update a user'''s progress on a roadmap.
 * It expects a JSON POST request containing the step ID, roadmap ID, and completion status.
 * It responds with a JSON status message.
 */

session_start();

// Set the content type of the response to JSON.
header('Content-Type: application/json');

// --- Security and Input Validation ---

// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated.']);
    exit();
}

// Check if the request method is POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

// Get the raw POST data and decode the JSON.
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if the required data is present.
if (!isset($data['step_id']) || !isset($data['roadmap_id']) || !isset($data['completed'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    exit();
}

// --- Database Interaction ---

require_once '../config/db.php';

$user_id = (int)$_SESSION['user_id'];
$step_id = (int)$data['step_id'];
$roadmap_id = (int)$data['roadmap_id'];
$completed = (int)$data['completed']; // Cast to integer (1 or 0)

// We use an INSERT ... ON DUPLICATE KEY UPDATE query for simplicity.
// This will either insert a new progress record or update an existing one.
// For this to work, we need a unique key on (user_id, roadmap_id, step_id).
// Let'''s add that to the database SQL. For now, we will check existence first.

// Check if a record already exists.
$sql_check = "SELECT id FROM user_progress WHERE user_id = ? AND roadmap_id = ? AND step_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $user_id, $roadmap_id, $step_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // If it exists, UPDATE it.
    $sql_update = "UPDATE user_progress SET completed = ? WHERE user_id = ? AND roadmap_id = ? AND step_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iiii", $completed, $user_id, $roadmap_id, $step_id);
    if ($stmt_update->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Progress updated.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update progress.']);
    }
    $stmt_update->close();
} else {
    // If it doesn'''t exist, INSERT a new record.
    $sql_insert = "INSERT INTO user_progress (user_id, roadmap_id, step_id, completed) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiii", $user_id, $roadmap_id, $step_id, $completed);
    if ($stmt_insert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Progress saved.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save progress.']);
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();

?>
