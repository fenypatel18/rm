<?php
/*
 * File: /user/dashboard.php
 *
 * This is the dashboard for regular users.
 * It greets the user and displays a list of roadmaps they have made progress on.
 */

session_start();

// --- Authentication Check ---
// If the user is not logged in or is not a regular user, redirect them.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';
include '../includes/header.php';

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// --- Fetch Enrolled Roadmaps ---
// Select roadmaps where the user has completed at least one step.
$sql = "SELECT DISTINCT r.id, r.title, r.description 
        FROM roadmaps r
        JOIN user_progress up ON r.id = up.roadmap_id
        WHERE up.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<div class="container mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-6">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>

    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Your Enrolled Roadmaps</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="border p-6 rounded-lg hover:shadow-md transition-shadow">
                        <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                        <a href="/roadmap.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:underline">Continue Learning &rarr;</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500">You haven't started any roadmaps yet. <a href="/index.php" class="text-blue-500 hover:underline">Explore some now</a>!</p>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>
