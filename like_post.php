<?php
include 'db.php'; // Database connection
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) && !isset($_SESSION['applicant_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like a post.']);
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;
$applicant_id = $_SESSION['applicant_id'] ?? null;
$post_id = intval($_POST['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit();
}

// Check if the user/applicant has already liked the post
$sqlCheckLike = "SELECT * FROM scholarship_post_likes WHERE post_id = ? AND (user_id = ? OR applicant_id = ?)";
$stmt = $conn->prepare($sqlCheckLike);
$stmt->bind_param('iii', $post_id, $user_id, $applicant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike the post
    $sqlUnlike = "DELETE FROM scholarship_post_likes WHERE post_id = ? AND (user_id = ? OR applicant_id = ?)";
    $stmt = $conn->prepare($sqlUnlike);
    $stmt->bind_param('iii', $post_id, $user_id, $applicant_id);
    $stmt->execute();

    // Decrease the likes count
    $sqlUpdateLikes = "UPDATE scholarship_posts SET likes_count = likes_count - 1 WHERE post_id = ?";
    $stmt = $conn->prepare($sqlUpdateLikes);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    // Return the updated likes count
    $sqlGetLikes = "SELECT likes_count FROM scholarship_posts WHERE post_id = ?";
    $stmt = $conn->prepare($sqlGetLikes);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $likes_count = $result->fetch_assoc()['likes_count'];

    echo json_encode(['success' => true, 'likes_count' => $likes_count, 'liked' => false]);
} else {
    // Like the post
    $sqlInsertLike = "INSERT INTO scholarship_post_likes (post_id, user_id, applicant_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sqlInsertLike);
    $stmt->bind_param('iii', $post_id, $user_id, $applicant_id);
    $stmt->execute();

    // Increase the likes count
    $sqlUpdateLikes = "UPDATE scholarship_posts SET likes_count = likes_count + 1 WHERE post_id = ?";
    $stmt = $conn->prepare($sqlUpdateLikes);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();

    // Return the updated likes count
    $sqlGetLikes = "SELECT likes_count FROM scholarship_posts WHERE post_id = ?";
    $stmt = $conn->prepare($sqlGetLikes);
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $likes_count = $result->fetch_assoc()['likes_count'];

    echo json_encode(['success' => true, 'likes_count' => $likes_count, 'liked' => true]);
}
?>