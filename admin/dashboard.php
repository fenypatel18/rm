<?php
/*
 * File: /admin/dashboard.php
 *
 * This is the main dashboard for administrators.
 * It provides an overview of all users and all roadmaps on the platform,
 * with links to manage them.
 */

session_start();

// --- Authentication & Authorization Check ---
// Redirect if not logged in or not an admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';
include '../includes/header.php';

$admin_name = $_SESSION['user_name'];

// --- Fetch All Users ---
$sql_users = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
$result_users = $conn->query($sql_users);

// --- Fetch All Roadmaps ---
$sql_roadmaps = "SELECT r.id, r.title, r.category, u.name as author_name 
                 FROM roadmaps r 
                 JOIN users u ON r.created_by = u.id 
                 ORDER BY r.created_at DESC";
$result_roadmaps = $conn->query($sql_roadmaps);

?>

<div class="container mx-auto mt-10">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
    <p class="text-xl mb-8">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</p>

    <!-- Users Management Section -->
    <div class="bg-white p-8 rounded-lg shadow-lg mb-8">
        <h2 class="text-2xl font-bold mb-6">Manage Users</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-6 text-left">Name</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Role</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-6"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                        <td class="py-3 px-6 text-center">
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-blue-500 hover:underline">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Roadmaps Management Section -->
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6">Manage Roadmaps</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="py-3 px-6 text-left">Title</th>
                        <th class="py-3 px-6 text-left">Author</th>
                        <th class="py-3 px-6 text-left">Category</th>
                        <th class="py-3 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php while ($roadmap = $result_roadmaps->fetch_assoc()): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-6"><?php echo htmlspecialchars($roadmap['title']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($roadmap['author_name']); ?></td>
                        <td class="py-3 px-6"><?php echo htmlspecialchars($roadmap['category']); ?></td>
                        <td class="py-3 px-6 text-center">
                            <a href="../roadmap.php?id=<?php echo $roadmap['id']; ?>" class="text-green-500 hover:underline" target="_blank">View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
