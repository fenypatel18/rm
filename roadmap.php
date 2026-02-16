<?php
/*
 * File: /roadmap.php
 *
 * This page displays the details of a single roadmap, including its steps.
 * It requires a roadmap ID from the URL query string (e.g., roadmap.php?id=1).
 * For logged-in users, it shows progress checkboxes.
 */

session_start();
require_once 'config/db.php';
include 'includes/header.php';

// --- Check for Roadmap ID ---
// If no ID is provided in the URL, redirect to the homepage.
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$roadmap_id = (int)$_GET['id'];
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

// --- Fetch Roadmap Details ---
$sql_roadmap = "SELECT r.title, r.description, u.name AS author FROM roadmaps r JOIN users u ON r.created_by = u.id WHERE r.id = ?";
$stmt_roadmap = $conn->prepare($sql_roadmap);
$stmt_roadmap->bind_param("i", $roadmap_id);
$stmt_roadmap->execute();
$result_roadmap = $stmt_roadmap->get_result();

if ($result_roadmap->num_rows === 0) {
    // If no roadmap is found with the given ID, show an error.
    echo "<div class='container mx-auto mt-10'><p class='text-red-500 text-center'>Roadmap not found!</p></div>";
    include 'includes/footer.php';
    exit();
}

$roadmap = $result_roadmap->fetch_assoc();

// --- Fetch Roadmap Steps ---
$sql_steps = "SELECT id, step_title, step_description FROM roadmap_steps WHERE roadmap_id = ? ORDER BY step_order ASC";
$stmt_steps = $conn->prepare($sql_steps);
$stmt_steps->bind_param("i", $roadmap_id);
$stmt_steps->execute();
$result_steps = $stmt_steps->get_result();

// --- Fetch User Progress (if logged in) ---
$user_progress = [];
if ($user_id) {
    $sql_progress = "SELECT step_id FROM user_progress WHERE user_id = ? AND roadmap_id = ? AND completed = 1";
    $stmt_progress = $conn->prepare($sql_progress);
    $stmt_progress->bind_param("ii", $user_id, $roadmap_id);
    $stmt_progress->execute();
    $result_progress = $stmt_progress->get_result();
    while ($row = $result_progress->fetch_assoc()) {
        $user_progress[$row['step_id']] = true;
    }
}

?>

<div class="container mx-auto mt-10">
    <!-- Roadmap Header -->
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h1 class="text-4xl font-bold mb-2"><?php echo htmlspecialchars($roadmap['title']); ?></h1>
        <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($roadmap['description']); ?></p>
        <p class="text-gray-500 text-sm">Created by: <?php echo htmlspecialchars($roadmap['author']); ?></p>
    </div>

    <!-- Roadmap Steps -->
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold mb-6">Learning Path</h2>
        
        <?php if ($result_steps->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while($step = $result_steps->fetch_assoc()): ?>
                    <?php $step_id = $step['id']; ?>
                    <div class="flex items-start p-4 border rounded-lg hover:bg-gray-50 transition">
                        <!-- Checkbox for logged-in users -->
                        <?php if ($user_id): ?>
                            <input type="checkbox" 
                                class="h-6 w-6 rounded border-gray-300 text-blue-600 focus:ring-blue-500 mt-1 mr-4 progress-checkbox"
                                data-step-id="<?php echo $step_id; ?>"
                                data-roadmap-id="<?php echo $roadmap_id; ?>"
                                <?php echo isset($user_progress[$step_id]) ? 'checked' : ''; ?> >
                        <?php endif; ?>
                        
                        <!-- Step Details -->
                        <div>
                            <h3 class="font-bold text-xl text-gray-800"><?php echo htmlspecialchars($step['step_title']); ?></h3>
                            <?php if (!empty($step['step_description'])): ?>
                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($step['step_description']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No steps have been added to this roadmap yet.</p>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for handling progress updates -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.progress-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const stepId = this.dataset.stepId;
            const roadmapId = this.dataset.roadmapId;
            const completed = this.checked ? 1 : 0;
            
            // Use Fetch API to send data to the server without a page reload
            fetch('/user/update_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    step_id: stepId, 
                    roadmap_id: roadmapId, 
                    completed: completed 
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status !== 'success') {
                    console.error('Failed to update progress.');
                    // Optional: You could uncheck the box to show the update failed.
                    this.checked = !completed; 
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.checked = !completed;
            });
        });
    });
});
</script>

<?php
$stmt_roadmap->close();
$stmt_steps->close();
if (isset($stmt_progress)) {
    $stmt_progress->close();
}
$conn->close();
include 'includes/footer.php';
?>
