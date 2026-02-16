<?php
/*
 * File: /admin/edit_user.php
 *
 * This page allows an admin to edit a user'''s role.
 */

session_start();

// --- Authentication & Authorization ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /login.php");
    exit();
}

require_once '../config/db.php';

$user_id_to_edit = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success_message = "";
$error_message = "";

// --- Fetch user data ---
$sql_user = "SELECT id, name, email, role FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id_to_edit);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows !== 1) {
    // If user not found, redirect
    header("Location: dashboard.php");
    exit();
}

$user = $result_user->fetch_assoc();

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_role = trim($_POST['role']);

    // Validate the role
    if (in_array($new_role, ['user', 'instructor', 'admin'])) {
        $sql_update = "UPDATE users SET role = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_role, $user_id_to_edit);

        if ($stmt_update->execute()) {
            $success_message = "User role updated successfully!";
            $user['role'] = $new_role; // Update the role in the current user data
        } else {
            $error_message = "Failed to update user role.";
        }
        $stmt_update->close();
    } else {
        $error_message = "Invalid role selected.";
    }
}

$stmt_user->close();
$conn->close();

include '../includes/header.php';
?>

<div class="container mx-auto mt-10 max-w-lg">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6">Edit User Role</h1>

        <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form action="edit_user.php?id=<?php echo $user_id_to_edit; ?>" method="POST">
            <div class="mb-4">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="mb-6">
                <label for="role" class="block text-gray-700 font-bold mb-2">Role</label>
                <select name="role" id="role" class="shadow border rounded w-full py-2 px-3">
                    <option value="user" <?php echo ($user['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="instructor" <?php echo ($user['role'] === 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                    <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Update Role
                </button>
                <a href="dashboard.php" class="text-gray-600 hover:underline">&larr; Back to Dashboard</a>
            </div>
        </form>
    </div>
</div>

<?php
include '../includes/footer.php';
?>
