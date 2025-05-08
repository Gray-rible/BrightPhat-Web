<?php
include 'db.php'; // Ensure database connection
$startTime = microtime(true); // Start measuring execution time

// Check if a user is logged in
session_start(); // Ensure session is started if not already started in header_sidebar.php
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to create a post.");
}

$user_id = $_SESSION['user_id'];
$scholarship_id = $_POST['scholarship_id'] ?? null;
$description = $_POST['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate scholarship ID
    if (!$scholarship_id) {
        die("Invalid scholarship selection.");
    }

    // Insert new post
    $insertPost = "INSERT INTO scholarship_posts (scholarship_id, description) VALUES (?, ?)";
    $stmt = $conn->prepare($insertPost);
    $stmt->bind_param("is", $scholarship_id, $description);

    if (!$stmt->execute()) {
        die("Error posting update: " . $stmt->error);
    }

    $post_id = $stmt->insert_id; // Get new post ID

    // Handle multiple image uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $imageData = file_get_contents($tmp_name);

                // Insert image into the database
                $insertImage = "INSERT INTO scholarship_post_images (post_id, image_data) VALUES (?, ?)";
                $stmtImage = $conn->prepare($insertImage);
                $stmtImage->bind_param("ib", $post_id, $null); // Use $null as a placeholder
                $stmtImage->send_long_data(1, $imageData); // Send the long blob data
                if (!$stmtImage->execute()) {
                    die("Error inserting image: " . $stmtImage->error);
                }
            }
        }
    }

    // Redirect back to view_scholarship.php with the scholarship_id
    header("Location: view_scholarship.php?scholarship_id=" . $scholarship_id);

    // Measure execution time
    $endTime = microtime(true);
    $executionTime = $endTime - $startTime;
    error_log("Post submission execution time: " . $executionTime . " seconds");

    exit(); // Ensure no further code is executed after the redirect
}
?>