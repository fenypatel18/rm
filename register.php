<?php
/*
 * File: /register.php
 *
 * This file provides the user registration form.
 * It handles form submission, validates the input, hashes the password,
 * and inserts the new user into the database.
 */

// Start the session.
session_start();

// If the user is already logged in, redirect them to their dashboard.
if (isset($_SESSION['user_id'])) {
    header("Location: /user/dashboard.php");
    exit();
}

// Include the database connection file.
require_once 'config/db.php';

// Define variables to hold form data and error messages.
$name = "";
$email = "";
$password = "";
$error_message = "";
$success_message = "";

// Check if the form has been submitted.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and retrieve form data.
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // --- Basic Form Validation ---
    if (empty($name) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Check if the email already exists in the database.
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_message = "An account with this email already exists.";
        } else {
            // Hash the password for security before storing it.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert the new user into the database with the default 'user' role.
            $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
            
            // Execute the statement and check for success.
            if ($insert_stmt->execute()) {
                $success_message = "Registration successful! You can now <a href='login.php' class='text-blue-500 hover:underline'>log in</a>.";
            } else {
                $error_message = "Error during registration. Please try again.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
    $conn->close();
}

// Include the header file.
include 'includes/header.php';
?>

<div class="container mx-auto mt-10 max-w-lg">
    <div class="bg-white p-8 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold mb-6 text-center">Create Your Account</h1>
        
        <?php
        // Display success or error messages if they exist.
        if (!empty($success_message)) {
            echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6' role='alert'>{$success_message}</div>";
        } elseif (!empty($error_message)) {
            echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6' role='alert'>{$error_message}</div>";
        }
        ?>
        
        <form action="register.php" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Full Name</label>
                <input type="text" name="name" id="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($name); ?>">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address</label>
                <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required value="<?php echo htmlspecialchars($email); ?>">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" required>
                <p class="text-gray-600 text-xs italic">Password must be at least 6 characters long.</p>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Register
                </button>
            </div>
            <p class="text-center text-gray-500 text-sm mt-6">
                Already have an account? <a href="login.php" class="text-blue-500 hover:underline">Log in here</a>.
            </p>
        </form>
    </div>
</div>

<?php
// Include the footer file.
include 'includes/footer.php';
?>
