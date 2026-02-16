<?php
/*
 * File: /instructor/add_step.php
 *
 * This script handles the submission of the 'Add New Step' form from manage_steps.php.
 * It validates the input and inserts a new step into the database.
 */

session_start();

// --- Authentication & Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

// --- Form Submission and Validation ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roadmap_id = isset($_POST['roadmap_id']) ? (int)$_POST['roadmap_id'] : 0;
    $step_title = trim($_POST['step_title']);
    $step_description = trim($_POST['step_description']);
    $step_order = isset($_POST['step_order']) ? (int)$_POST['step_order'] : 0;
    $instructor_id = $_SESSION['user_id'];

    // Verify ownership of the roadmap before adding a step to it.
    $sql_verify = "SELECT id FROM roadmaps WHERE id = ? AND created_by = ?";
    $stmt_verify = $conn->prepare($sql_verify);
    $stmt_verify->bind_param("ii", $roadmap_id, $instructor_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 1 && !empty($step_title) && $step_order > 0) {
        // If ownership is verified and data is valid, insert the new step.
        $sql_insert = "INSERT INTO roadmap_steps (roadmap_id, step_title, step_description, step_order) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issi", $roadmap_id, $step_title, $step_description, $step_order);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_verify->close();
    $conn->close();
}

// --- Redirect back to the manage steps page ---
// This keeps the user on the same page to see their changes.
header("Location: manage_steps.php?id=" . $roadmap_id);
exit();

?>
