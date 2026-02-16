<?php
/*
 * File: /login.php
 *
 * This file provides the user login form.
 * It handles form submission, verifies credentials against the database,
 * and starts a session upon successful login, redirecting the user based on their role.
 */

// Start the session.
session_start();

// If the user is already logged in, redirect them to their dashboard.
if (isset($_SESSION['user_id'])) {
    // Simple switch to redirect based on role.
    $role = $_SESSION['role'];
    switch ($role) {
        case 'admin':
            header("Location: /admin/dashboard.php");
            break;
        case 'instructor':
            header("Location: /instructor/dashboard.php");
            break;
        default:
            header("Location: /user/dashboard.php");
            break;
    }
    exit();
}

// Include the database connection file.
require_once 'config/db.php';

// Define variables for form data and error messages.
$email = "";
$error_message = "";

// Check if the form is submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and retrieve form data.
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Basic validation.
    if (empty($email) || empty($password)) {
        $error_message = "Both email and password are required.";
    } else {
        // Fetch the user from the database based on the email provided.
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            // If a user is found, verify the password.
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Password is correct, so we start the session.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on the user's role.
                switch ($user['role']) {
                    case 'admin':
                        header("Location: /admin/dashboard.php");
                        break;
                    case 'instructor':
                        header("Location: /instructor/dashboard.php");
                        break;
                    default:
                        header("Location: /user/dashboard.php");
                        break;
                }
                exit(); // Important to stop the script after a redirect.
            } else {
                // If the password does not match.
                $error_message = "Invalid email or password.";
            }
        } else {
            // If no user is found with that email.
            $error_message = "Invalid email or password.";
        }
        $stmt->close();
    }
    $conn->close();
}

// Include the header file.
include 'includes/header.php';
?>

<div class="container mx-auto mt-10 max-w-md">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center">Login to Your Account</h1>
        
        <?php
        // Display error message if it exists.
        if (!empty($error_message)) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6' role='alert'>{$error_message}</div>";
        }
        ?>
        
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address</label>
                <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Login
                </button>
            </div>
            <p class="text-center text-gray-500 text-sm mt-6">
                Don't have an account? <a href="register.php" class="text-blue-500 hover:underline">Register here</a>.
            </p>
        </form>
    </div>
</div>

<?php
// Include the footer file.
include 'includes/footer.php';
?>
