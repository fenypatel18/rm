<?php
/*
 * File: /instructor/manage_steps.php
 *
 * This page allows instructors to manage the steps of a specific roadmap.
 * They can add new steps, edit existing steps, and delete them.
 */

session_start();

// --- Authentication & Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'instructor') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

$roadmap_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$instructor_id = $_SESSION['user_id'];

// --- Verify Ownership and Fetch Roadmap ---
$sql_roadmap = "SELECT title FROM roadmaps WHERE id = ? AND created_by = ?";
$stmt_roadmap = $conn->prepare($sql_roadmap);
$stmt_roadmap->bind_param("ii", $roadmap_id, $instructor_id);
$stmt_roadmap->execute();
$result_roadmap = $stmt_roadmap->get_result();

if ($result_roadmap->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}
$roadmap = $result_roadmap->fetch_assoc();

// --- Fetch Existing Steps ---
$sql_steps = "SELECT * FROM roadmap_steps WHERE roadmap_id = ? ORDER BY step_order ASC";
$stmt_steps = $conn->prepare($sql_steps);
$stmt_steps->bind_param("i", $roadmap_id);
$stmt_steps->execute();
$result_steps = $stmt_steps->get_result();

include '../includes/header.php';
?>

<div class="container mx-auto mt-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Manage Steps</h1>
            <p class="text-xl text-gray-600">for "<?php echo htmlspecialchars($roadmap['title']); ?>"</p>
        </div>
        <a href="dashboard.php" class="text-gray-600 hover:underline">&larr; Back to Dashboard</a>
    </div>

    <!-- Add Step Form -->
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h2 class="text-2xl font-bold mb-4">Add New Step</h2>
        <form action="add_step.php" method="POST">
            <input type="hidden" name="roadmap_id" value="<?php echo $roadmap_id; ?>">
            <div class="mb-4">
                <label for="step_title" class="block text-gray-700 font-bold mb-2">Step Title</label>
                <input type="text" name="step_title" id="step_title" class="shadow border rounded w-full py-2 px-3" required>
            </div>
            <div class="mb-4">
                <label for="step_description" class="block text-gray-700 font-bold mb-2">Step Description (Optional)</label>
                <textarea name="step_description" id="step_description" rows="3" class="shadow border rounded w-full py-2 px-3"></textarea>
            </div>
            <div class="mb-4">
                <label for="step_order" class="block text-gray-700 font-bold mb-2">Order</label>
                <input type="number" name="step_order" id="step_order" class="shadow border rounded w-24 py-2 px-3" value="<?php echo $result_steps->num_rows + 1; ?>" required>
            </div>
            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                + Add Step
            </button>
        </form>
    </div>

    <!-- Existing Steps -->
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Existing Steps</h2>
        <?php if ($result_steps->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while ($step = $result_steps->fetch_assoc()): ?>
                <div class="border rounded-lg p-4 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($step['step_order']); ?>. <?php echo htmlspecialchars($step['step_title']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($step['step_description']); ?></p>
                    </div>
                    <div>
                        <!-- Edit and Delete links would go here -->
                        <!-- For simplicity, we'''ll add a delete link directly -->
                        <a href="delete_step.php?id=<?php echo $step['id']; ?>&roadmap_id=<?php echo $roadmap_id; ?>" onclick="return confirm('Are you sure?');" class="text-red-500 hover:underline">Delete</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No steps have been added yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt_roadmap->close();
$stmt_steps->close();
$conn->close();
include '../includes/footer.php';
?>
