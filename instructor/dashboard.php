<?php
/*
 * File: /instructor/dashboard.php
 *
 * This is the dashboard for instructors.
 * It greets the instructor and displays a list of roadmaps they have created.
 * It also provides links to add, edit, or delete their roadmaps.
 */

session_start();

// --- Authentication & Authorization Check ---
// Redirect if not logged in or not an instructor.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';
include '../includes/header.php';

$instructor_id = $_SESSION['user_id'];
$instructor_name = $_SESSION['user_name'];

// --- Fetch Roadmaps Created by This Instructor ---
$sql = "SELECT id, title, description, category FROM roadmaps WHERE created_by = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container mx-auto mt-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Instructor Dashboard</h1>
        <a href="add_roadmap.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">+ Add New Roadmap</a>
    </div>
    <p class="text-xl mb-8">Welcome, <?php echo htmlspecialchars($instructor_name); ?>!</p>

    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Your Created Roadmaps</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="py-3 px-6 text-left">Title</th>
                            <th class="py-3 px-6 text-left">Category</th>
                            <th class="py-3 px-6 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-6 font-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="py-3 px-6"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="py-3 px-6 text-center">
                                    <a href="edit_roadmap.php?id=<?php echo $row['id']; ?>" class="bg-blue-500 text-white py-1 px-3 rounded hover:bg-blue-600">Edit</a>
                                    <a href="manage_steps.php?id=<?php echo $row['id']; ?>" class="bg-yellow-500 text-white py-1 px-3 rounded hover:bg-yellow-600 ml-2">Manage Steps</a>
                                    <a href="delete_roadmap.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this roadmap?');" class="bg-red-500 text-white py-1 px-3 rounded hover:bg-red-600 ml-2">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You have not created any roadmaps yet. <a href="add_roadmap.php" class="text-blue-500 hover:underline">Create one now</a>!</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>
