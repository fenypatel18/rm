<?php
/*
 * File: /index.php
 *
 * This is the main homepage of the website.
 * It displays a hero section and lists all the available roadmaps for users to browse.
 * It also includes the header and footer.
 */

// Start the session to manage user login state.
session_start();

// Include the database connection file.
require_once 'config/db.php';

// Include the header file.
include 'includes/header.php';

// Fetch all roadmaps from the database to display on the homepage.
$sql = "SELECT * FROM roadmaps";
$result = $conn->query($sql);

?>

<div class="container mx-auto mt-10">
    <!-- Hero Section -->
    <section class="text-center bg-blue-500 text-white p-12 rounded-lg">
        <h1 class="text-5xl font-bold mb-4">Welcome to Roadmappr!</h1>
        <p class="text-xl">Your ultimate guide to structured learning paths.</p>
    </section>

    <!-- Roadmaps Section -->
    <section class="mt-12">
        <h2 class="text-3xl font-bold mb-6 text-center">Explore Our Roadmaps</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            // Check if there are any roadmaps to display.
            if ($result->num_rows > 0) {
                // Loop through each roadmap and display it as a card.
                while($row = $result->fetch_assoc()) {
            ?>
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                    <a href="roadmap.php?id=<?php echo $row['id']; ?>" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full transition-colors duration-300">View Roadmap</a>
                </div>
            </div>
            <?php
                }
            } else {
                // If no roadmaps are found, display a message.
                echo "<p class='text-center text-gray-500 col-span-full'>No roadmaps available at the moment. Please check back later.</p>";
            }
            ?>
        </div>
    </section>
</div>

<?php
// Include the footer file to complete the page structure.
include 'includes/footer.php';
?>
