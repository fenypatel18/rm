<?php
/*
 * File: /instructor/edit_roadmap.php
 *
 * This page allows an instructor to edit the details of a roadmap they have created.
 * It fetches the roadmap data, pre-fills a form, and handles the update submission.
 */

session_start();

// --- Authentication & Authorization Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

$roadmap_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$instructor_id = $_SESSION['user_id'];
$error_message = "";
$success_message = "";

// --- Verify Ownership and Fetch Data ---
$sql = "SELECT * FROM roadmaps WHERE id = ? AND created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $roadmap_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    // If roadmap doesn't exist or doesn't belong to the instructor, redirect.
    header("Location: dashboard.php");
    exit();
}

$roadmap = $result->fetch_assoc();

// --- Form Submission Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data.
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);

    // --- Basic Validation ---
    if (empty($title) || empty($description) || empty($category)) {
        $error_message = "All fields are required.";
    } else {
        // Prepare and execute the update statement.
        $update_sql = "UPDATE roadmaps SET title = ?, description = ?, category = ? WHERE id = ? AND created_by = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssii", $title, $description, $category, $roadmap_id, $instructor_id);

        if ($update_stmt->execute()) {
            $success_message = "Roadmap updated successfully!";
            // Refresh the roadmap data to show the new values in the form
            $roadmap['title'] = $title;
            $roadmap['description'] = $description;
            $roadmap['category'] = $category;
        } else {
            $error_message = "Error updating roadmap. Please try again.";
        }
        $update_stmt->close();
    }
}

$stmt->close();
$conn->close();

include '../includes/header.php';
?>

<div class="container mx-auto mt-10 max-w-2xl">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6">Edit Roadmap</h1>
        
        <?php
        if (!empty($success_message)) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6'>{$success_message}</div>";
        }
        if (!empty($error_message)) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6'>{$error_message}</div>";
        }
        ?>

        <form action="edit_roadmap.php?id=<?php echo $roadmap_id; ?>" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Roadmap Title</label>
                <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required value="<?php echo htmlspecialchars($roadmap['title']); ?>">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required><?php echo htmlspecialchars($roadmap['description']); ?></textarea>
            </div>
            <div class="mb-6">
                <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                <input type="text" name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required value="<?php echo htmlspecialchars($roadmap['category']); ?>">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded w-full">
                    Save Changes
                </button>
            </div>
        </form>
        <div class="mt-6 text-center">
            <a href="dashboard.php" class="text-gray-600 hover:underline">&larr; Back to Dashboard</a>
        </div>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
