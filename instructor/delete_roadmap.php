<?php
/*
 * File: /instructor/delete_roadmap.php
 *
 * This script handles the deletion of a roadmap.
 * It verifies ownership and removes the roadmap and all associated data (steps, progress) from the database.
 */

session_start();

// --- Authentication & Authorization Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    // If not an instructor, send a forbidden status and exit.
    http_response_code(403);
    echo "Access Forbidden.";
    exit();
}

require_once '../config/db.php';

$roadmap_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$instructor_id = $_SESSION['user_id'];

if ($roadmap_id > 0) {
    // --- Verify Ownership before Deleting ---
    $sql_verify = "SELECT id FROM roadmaps WHERE id = ? AND created_by = ?";
    $stmt_verify = $conn->prepare($sql_verify);
    $stmt_verify->bind_param("ii", $roadmap_id, $instructor_id);
    $stmt_verify->execute();
    $result_verify = $stmt_verify->get_result();

    if ($result_verify->num_rows === 1) {
        // --- Deletion Logic ---
        // The database is set up with ON DELETE CASCADE for foreign key constraints.
        // This means deleting a roadmap will automatically delete associated steps and user progress.
        // This is efficient and ensures data integrity.
        
        $sql_delete = "DELETE FROM roadmaps WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $roadmap_id);

        if ($stmt_delete->execute()) {
            // On successful deletion, redirect back to the dashboard.
            $_SESSION['delete_success'] = "Roadmap deleted successfully.";
        } else {
            // If deletion fails for some reason.
            $_SESSION['delete_error'] = "Error deleting roadmap. Please try again.";
        }
        $stmt_delete->close();
    } else {
        // If the user tries to delete a roadmap that isn't theirs.
        $_SESSION['delete_error'] = "You are not authorized to delete this roadmap.";
    }
    $stmt_verify->close();
} else {
    // If the ID is invalid.
    $_SESSION['delete_error'] = "Invalid roadmap ID.";
}

$conn->close();

// Redirect back to the instructor dashboard.
header("Location: dashboard.php");
exit();

?>
