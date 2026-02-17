<?php
/*
 * File: /config/db.php
 *
 * This file contains the database connection settings and the script to connect to the MySQL database.
 * It'''s included in other files where database access is needed.
 */

// Database credentials
$db_host = 'localhost';
$db_user = 'root'; // Default XAMPP username
$db_pass = '';     // Default XAMPP password
$db_name = 'roadmaps'; // The name of our database

// Create a new mysqli object to connect to the database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check if the connection was successful
if ($conn->connect_error) {
    // If the connection fails, stop the script and display an error message.
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Set character set to utf8mb4 for full Unicode support.
$conn->set_charset("utf8mb4");

?>
