<?php
/*
 * File: /instructor/delete_step.php
 *
 * This script handles the deletion of a single step from a roadmap.
 * It verifies ownership before deleting.
 */

session_start();

// --- Authentication & Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

$step_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$roadmap_id = isset($_GET['roadmap_id']) ? (int)$_GET['roadmap_id'] : 0;
$instructor_id = $_SESSION['user_id'];

// --- Deletion Logic ---
if ($step_id > 0 && $roadmap_id > 0) {
    // We need to join the roadmaps table to ensure the instructor owns the roadmap this step belongs to.
    $sql = "DELETE rs FROM roadmap_steps rs 
            JOIN roadmaps r ON rs.roadmap_id = r.id 
            WHERE rs.id = ? AND r.created_by = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $step_id, $instructor_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// --- Redirect back to the manage steps page ---
header("Location: manage_steps.php?id=" . $roadmap_id);
exit();

?>
