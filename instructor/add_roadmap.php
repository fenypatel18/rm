<?php
/*
 * File: /instructor/add_roadmap.php
 *
 * This page provides a form for instructors to add a new roadmap.
 * It handles form submission, validates the data, and inserts the new roadmap into the database.
 */

session_start();

// --- Authentication & Authorization Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

$title = "";
$description = "";
$category = "";
$error_message = "";
$success_message = "";

// --- Form Submission Handling ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data.
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $instructor_id = $_SESSION['user_id'];

    // --- Basic Validation ---
    if (empty($title) || empty($description) || empty($category)) {
        $error_message = "All fields are required.";
    } else {
        // Prepare and execute the insert statement.
        $sql = "INSERT INTO roadmaps (title, description, category, created_by) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // "sssi" means the parameters are: String, String, String, Integer
        $stmt->bind_param("sssi", $title, $description, $category, $instructor_id);

        if ($stmt->execute()) {
            $success_message = "Roadmap added successfully! You can now <a href='manage_steps.php?id={".($stmt->insert_id)."}' class='text-blue-500 hover:underline'>add steps</a> to it.";
            // Clear the form fields after successful submission
            $title = $description = $category = "";
        } else {
            $error_message = "Error adding roadmap. Please try again.";
        }
        $stmt->close();
    }
    $conn->close();
}

// We need to include the header after potential redirects and session checks.
include '../includes/header.php';
?>

<div class="container mx-auto mt-10 max-w-2xl">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6">Add a New Roadmap</h1>
        
        <?php
        // Display feedback messages
        if (!empty($success_message)) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6'>{$success_message}</div>";
        }
        if (!empty($error_message)) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6'>{$error_message}</div>";
        }
        ?>

        <form action="add_roadmap.php" method="POST">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 font-bold mb-2">Roadmap Title</label>
                <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required value="<?php echo htmlspecialchars($title); ?>">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Description</label>
                <textarea name="description" id="description" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="mb-6">
                <label for="category" class="block text-gray-700 font-bold mb-2">Category</label>
                <input type="text" name="category" id="category" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required placeholder="e.g., Frontend, Backend, DevOps" value="<?php echo htmlspecialchars($category); ?>">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded w-full">
                    Add Roadmap
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
