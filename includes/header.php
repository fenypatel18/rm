<?php
/*
 * File: /includes/header.php
 *
 * This is the header file for the website.
 * It includes the HTML head, navigation bar, and starts the body of the page.
 * The navigation bar is dynamic and shows different links based on the user'''s role and login status.
 */

// We need to access session variables, so we start the session if it'''s not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// A simple helper function to check if a user is logged in.
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// A helper function to check the role of the logged-in user.
function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roadmappr - Your Learning Companion</title>
    <!-- Link to the compiled Tailwind CSS file -->
    <link href="/assets/css/output.css" rel="stylesheet">
    <!-- A little bit of custom CSS for flair -->
    <style>
        /* Simple transition for a smoother hover effect on nav links */
        .nav-link {
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <nav class="bg-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <a href="/index.php" class="text-2xl font-bold text-blue-500 hover:text-blue-600 nav-link">Roadmappr</a>
                </div>
                <div class="flex items-center">
                    <!-- Main navigation links -->
                    <a href="/index.php" class="text-gray-600 hover:text-blue-500 px-4 nav-link">Home</a>
                    
                    <?php if (isLoggedIn()): ?>
                        <!-- Links for logged-in users -->
                        <?php if (getUserRole() == 'admin'): ?>
                            <a href="/admin/dashboard.php" class="text-gray-600 hover:text-blue-500 px-4 nav-link">Admin</a>
                        <?php elseif (getUserRole() == 'instructor'): ?>
                            <a href="/instructor/dashboard.php" class="text-gray-600 hover:text-blue-500 px-4 nav-link">Instructor</a>
                        <?php else: ?>
                            <a href="/user/dashboard.php" class="text-gray-600 hover:text-blue-500 px-4 nav-link">Dashboard</a>
                        <?php endif; ?>
                        <a href="/logout.php" class="bg-red-500 text-white rounded-full py-2 px-6 ml-4 hover:bg-red-600 transition-colors duration-300">Logout</a>
                    <?php else: ?>
                        <!-- Links for guest users -->
                        <a href="/login.php" class="text-gray-600 hover:text-blue-500 px-4 nav-link">Login</a>
                        <a href="/register.php" class="bg-blue-500 text-white rounded-full py-2 px-6 ml-4 hover:bg-blue-600 transition-colors duration-300">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
