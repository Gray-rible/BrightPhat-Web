<?php
include 'db.php';

$post_id = intval($_GET['post_id'] ?? 0);

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID.']);
    exit();
}


$sql = "SELECT 
            c.comment_text, 
            c.date_commented, 
            c.commentator, 
            s.scholarship_name, 
            s.logo AS scholarship_logo, 
            u.first_name AS user_first_name, 
            u.middle_name AS user_middle_name, 
            u.last_name AS user_last_name, 
            u.avatar AS user_avatar, 
            a.first_name AS applicant_first_name, 
            a.middle_name AS applicant_middle_name, 
            a.last_name AS applicant_last_name, 
            a.picture AS applicant_picture
        FROM scholarship_post_comments c
        LEFT JOIN scholarships s ON c.scholarship_id = s.scholarship_id
        LEFT JOIN users u ON c.user_id = u.user_id
        LEFT JOIN applicants a ON c.applicant_id = a.applicant_id
        WHERE c.post_id = ?
        ORDER BY c.date_commented ASC";

$comments = [];
while ($row = $result->fetch_assoc()) {
    $commentator_name = '';
    $commentator_image = '';

    if ($row['commentator'] === 'scholarship') {
        $commentator_name = $row['scholarship_name'];
        $commentator_image = !empty($row['scholarship_logo']) ? 'data:image/jpeg;base64,' . base64_encode($row['scholarship_logo']) : 'default_logo_page.jpg';
    } elseif ($row['commentator'] === 'user') {
        $commentator_name = $row['user_first_name'] . ' ' . $row['user_middle_name'] . ' ' . $row['user_last_name'];
        $commentator_image = !empty($row['user_avatar']) ? 'data:image/jpeg;base64,' . base64_encode($row['user_avatar']) : 'default_user_avatar.jpg';
    } elseif ($row['commentator'] === 'applicant') {
        $commentator_name = $row['applicant_first_name'] . ' ' . $row['applicant_middle_name'] . ' ' . $row['applicant_last_name'];
        $commentator_image = !empty($row['applicant_picture']) ? 'data:image/jpeg;base64,' . base64_encode($row['applicant_picture']) : 'default_applicant_picture.jpg';
    }

    $comments[] = [
        'comment_text' => htmlspecialchars($row['comment_text']),
        'date_commented' => date('F j, Y, g:i a', strtotime($row['date_commented'])),
        'commentator_name' => htmlspecialchars($commentator_name),
        'commentator_image' => $commentator_image,
    ];
}

echo json_encode(['success' => true, 'comments' => $comments]);
?>