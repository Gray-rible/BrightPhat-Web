<?php
include 'db.php'; // Replace with your actual database connection file

$currentPage = 'manage_scholarships';
include 'header_sidebar.php';

// Check if a session is already active before starting one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

// Validate the scholarship_id
if (!isset($_GET['scholarship_id']) || empty($_GET['scholarship_id'])) {
    die("Scholarship ID is missing.");
}

$scholarshipId = intval($_GET['scholarship_id']);

// Fetch scholarship details

$sql = "SELECT 
            s.scholarship_name, 
            s.logo, -- Include the logo column
            s.Scholarship_general_description_s1, 
            s.Scholarship_selection_criteria, 
            s.status_id, 
            st.status_name, 
            s.image_data, 
            s.user_id, 
            s.slots_available, 
            s.Scholarship_education_details_s2, 
            s.Scholarship_financial_assistance_details_s3, 
            s.Scholarship_maintaing_s4, 
            s.Scholarship_effects_for_others_s5, 
            s.forfeiture_of_benefit, 
            s.note_for_submission, 
            CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) AS admin_name,
            c.category_name
        FROM scholarships s
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN scholarship_categories c ON s.scholarship_category_id = c.scholarship_category_id
        LEFT JOIN status st ON s.status_id = st.status_id
        WHERE s.scholarship_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Scholarship not found.");
}

$scholarship = $result->fetch_assoc();

$scholarship = $result->fetch_assoc();

// Fetch applicants for the current scholarship
$sqlApplicantsList = "SELECT 
                        asr.record_id,
                        a.first_name,
                        a.middle_name,
                        a.last_name,
                        asr.submission_date,
                        asr.status
                      FROM applicant_scholarship_records asr
                      JOIN applicants a ON asr.applicant_id = a.applicant_id
                      WHERE asr.scholarship_id = ?";
$stmt = $conn->prepare($sqlApplicantsList);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$applicantsListResult = $stmt->get_result();
$applicantsList = [];
while ($row = $applicantsListResult->fetch_assoc()) {
    $applicantsList[] = $row;
}

// Fetch requirements
$sqlRequirements = "SELECT r.requirement_name, sr.label 
                    FROM scholarship_requirements sr
                    JOIN requirements r ON sr.requirement_id = r.requirement_id
                    WHERE sr.scholarship_id = ?";
$stmt = $conn->prepare($sqlRequirements);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$requirementsResult = $stmt->get_result();
$requirements = [];
while ($row = $requirementsResult->fetch_assoc()) {
    $requirements[] = $row;
}

// Fetch applicant statistics (approved and pending)
$sqlApplicants = "SELECT 
                    (SELECT COUNT(*) FROM applicant_scholarship_records WHERE scholarship_id = ? AND status = 'approved') AS approved_count,
                    (SELECT COUNT(*) FROM applicant_scholarship_records WHERE scholarship_id = ? AND status = 'pending') AS pending_count";
$stmt = $conn->prepare($sqlApplicants);
$stmt->bind_param('ii', $scholarshipId, $scholarshipId);
$stmt->execute();
$applicantStats = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$result = $stmt->get_result();

// Fetch posts for the current scholarship
$sqlPosts = "SELECT 
                sp.post_id, 
                sp.description, 
                sp.date_posted, 
                sp.likes_count, 
                s.scholarship_name, 
                s.logo 
            FROM scholarship_posts sp
            JOIN scholarships s ON sp.scholarship_id = s.scholarship_id
            WHERE sp.scholarship_id = ?
            ORDER BY sp.date_posted DESC";
$stmt = $conn->prepare($sqlPosts);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$postsResult = $stmt->get_result();
$posts = [];
while ($row = $postsResult->fetch_assoc()) {
    $posts[] = $row;
}

// Fetch the count of completed applicants
$sqlCompletedApplicants = "SELECT COUNT(*) AS completed_count 
                           FROM applicant_scholarship_records 
                           WHERE scholarship_id = ? AND status = 'completed'";
$stmt = $conn->prepare($sqlCompletedApplicants);
$stmt->bind_param('i', $scholarshipId);
$stmt->execute();
$completedApplicantsResult = $stmt->get_result();
$completedApplicants = $completedApplicantsResult->fetch_assoc()['completed_count'];
if ($result->num_rows === 0) {
    die("Scholarship not found.");
}

$scholarship = $result->fetch_assoc();

if (!$scholarship) {
    die("Error: Scholarship details could not be retrieved.");
}
if (!$scholarship['status_name']) {
    echo "Debug: Status name is missing or null.";
}

if (!$scholarship['admin_name']) {
    echo "Debug: Admin name is missing or null.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Scholarship</title>
    <link rel="stylesheet" href="style_view_scholarship.css">

 
</head>
<body>
    

<body>
   <main>
    <div class="container">
    <div class="imageco"> 
                    <img src="<?php echo !empty($scholarship['image_data']) ? 'data:image/jpeg;base64,' . base64_encode($scholarship['image_data']) : 'default-image.jpg'; ?>" alt="Scholarship Image">
                </div>

<!-- display them next to each other horizontally-->
<div class="scholarship-header" style="display: flex; align-items: center; gap: 20px; margin-top: 20px; padding-bottom: 10px; border-bottom: 1px solid #ccc;">
    <!-- Logo -->
    <div>
        <img src="<?php echo !empty($scholarship['logo']) ? 'data:image/jpeg;base64,' . base64_encode($scholarship['logo']) : 'default_logo_page.jpg'; ?>" 
             alt="Scholarship Logo" 
             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 2px solid #ccc;">
    </div>

    <!-- Scholarship Name -->
    <div>
        <h1><?php echo htmlspecialchars($scholarship['scholarship_name']); ?></h1>
        <p>Category: <?php echo htmlspecialchars($scholarship['category_name']); ?></p>
        
        <!-- display next to each other horizontally and add icon-->
        <div class="scholarship-details" style="display: flex; align-items: center; gap: 20px; margin-top: -5px;">
    <!-- Slots -->
    <div style="display: flex; align-items: center; gap: 10px;">
    <i class="fas fa-users" style="color: #007bff;"></i>
    <p style="color: rgb(88, 88, 88);">
        Slots: <?php echo $scholarship['slots_available'] . ' / ' . $completedApplicants; ?>
    </p>
</div>

    <!-- Status -->
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-info-circle" style="color: #28a745;"></i>
        <p style=" color:rgb(88, 88, 88)">Status: <?php echo htmlspecialchars($scholarship['status_name']); ?></p>
    </div>
    </div>
        </div>
</div>
     <div>
        <!-- add 4 navigation buttons with icon horizontally (announcement, Scholarship Details, ReQuirements , contacts) display  center. clicking the annoncement will show the div for announcenent and hides the remaining 3 buttons container.. same goes to details,requirements, contacts-->
        <!-- Navigation Buttons -->
<div class="nav-container">
<button class="nav-button active" onclick="showSection('announcement')"><i class="fas fa-bullhorn"></i> Updates</button>
  <button class="nav-button" onclick="showSection('details')"><i class="fas fa-info-circle"></i> Scholarship Details</button>
    <button class="nav-button" onclick="showSection('requirements')"><i class="fas fa-check-circle"></i> Requirements</button>
    <button class="nav-button" onclick="showSection('applicants')"><i class="fas fa-users"></i> Applicants</button>
    <button class="nav-button" onclick="showSection('contacts')"><i class="fas fa-address-book"></i> Contacts</button>
</div>

<!-- Sections -->
<div id="announcement" class="section active">
<h3 style="margin-top: -10px; color: rgb(255, 123, 0); font-size: 22px; display: flex; justify-content: space-between; align-items: center; gap: 10px; width: 98%; background: linear-gradient(to right, rgba(215, 196, 253, 0.91), rgba(255, 191, 52, 0.66)); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
    <span style="display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-bullhorn" style="color: rgb(255, 136, 0);"></i> Announcements
    </span>
    <button onclick="openModal()" style="background: linear-gradient(135deg, rgb(46, 42, 77), rgb(23, 23, 31)); color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
        New Post
    </button>
</h3>
<!-- Loading and Success Messages -->
<div id="loadingMessage" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1100;">
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); text-align: center;">
        <div class="spinner" style="margin-bottom: 10px;"></div>
        <p style="font-size: 18px; color: #333;">Uploading your post...</p>
    </div>
</div>

<div id="successMessage" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1100;">
    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); text-align: center;">
        <p style="font-size: 18px; color: #28a745;">Uploaded successfully!</p>
    </div>
</div>

<div id="newPostModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 20px; width: 90%; max-width: 500px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2); position: relative;">
        <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
            <img src="<?php echo !empty($scholarship['logo']) ? 'data:image/jpeg;base64,' . base64_encode($scholarship['logo']) : 'default_logo_page.jpg'; ?>" 
                 alt="Scholarship Logo" 
                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;">
            <?php echo htmlspecialchars($scholarship['scholarship_name']); ?>
        </h2>
       <!-- New Post Form -->
<form id="newPostForm" method="POST" action="post_scholarship.php" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px; width: 100%; max-width: 500px; margin: auto;">
    <!-- Hidden Field for Scholarship ID -->
    <input type="hidden" name="scholarship_id" value="<?php echo htmlspecialchars($_GET['scholarship_id']); ?>">

    <!-- Description Field -->
    <textarea name="description" placeholder="Write a description..." required style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc; resize: none; height: 100px;"></textarea>

    <!-- File Upload Field -->
    <label for="images" style="cursor: pointer; background: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-align: center;">
        Upload Images
    </label>
    <input type="file" name="images[]" id="images" multiple accept="image/*" style="display: none;">

    <!-- Preview Container -->
    <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; background-color:rgb(231, 231, 231); min-height: 100px;">
        
    </div>

    <!-- Submit Button -->
    <button type="submit" style="background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
        Post
    </button>
</form>
    </div>
</div>


<?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>


            <div class="post" style="border: 1px solid #ccc; border-radius: 10px; padding: 15px; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">
                <!-- Post Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="<?php echo !empty($post['logo']) ? 'data:image/jpeg;base64,' . base64_encode($post['logo']) : 'default_logo_page.jpg'; ?>" 
                 alt="Scholarship Logo" 
                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;">
            <div>
                <h4 style="margin: 0; font-size: 16px;"><?php echo htmlspecialchars($post['scholarship_name']); ?></h4>
                <p style="margin: 0; font-size: 12px; color: #888;"><?php echo date('F j, Y, g:i a', strtotime($post['date_posted'])); ?></p>
            </div>
        </div>

        <!-- Three Dots Icon -->
        <div class="dropdown" style="position: relative;">
            <button onclick="toggleDropdown(<?php echo $post['post_id']; ?>)" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-ellipsis-v" style="font-size: 20px; color: #888;"></i>
            </button>
            <div id="dropdown-<?php echo $post['post_id']; ?>" class="dropdown-menu" style="display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); z-index: 1000;">
                <button onclick="deletePost(<?php echo $post['post_id']; ?>)" style="display: flex; align-items: center; gap: 10px; padding: 10px; width: 100%; background: none; border: none; cursor: pointer;">
                    <i class="fas fa-trash" style="color: #dc3545;"></i> Delete Post
                </button>
                <button onclick="editPost(<?php echo $post['post_id']; ?>)" style="display: flex; align-items: center; gap: 10px; padding: 10px; width: 100%; background: none; border: none; cursor: pointer;">
                    <i class="fas fa-edit" style="color: #007bff;"></i> Edit Post
                </button>
                <button onclick="togglePrivacy(<?php echo $post['post_id']; ?>)" style="display: flex; align-items: center; gap: 10px; padding: 10px; width: 100%; background: none; border: none; cursor: pointer;">
                    <i class="fas fa-eye-slash" style="color: #ffc107;"></i> Privacy (Hide/Public)
                </button>
            </div>
        </div>
        </div>
                <!-- Post Description -->
                <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>

                <!-- Post Images -->
<div style="display: flex; overflow-x: auto; white-space: nowrap; gap: 10px; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ccc;">
    <?php
    $sqlImages = "SELECT image_data FROM scholarship_post_images WHERE post_id = ?";
    $stmt = $conn->prepare($sqlImages);
    $stmt->bind_param('i', $post['post_id']);
    $stmt->execute();
    $imagesResult = $stmt->get_result();
    while ($image = $imagesResult->fetch_assoc()):
    ?>
        <div style="flex-shrink: 0; width: 700px; height: 400px; overflow: hidden; position: relative;  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($image['image_data']); ?>" 
                 alt="Post Image" 
                 style="width: auto; height: 100%; position: absolute; top: 0; left: 0; cursor: pointer; "
                 onclick="openImageModal(this.src)">
        </div>
    <?php endwhile; ?>
</div>

<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; z-index: 1000;">
   <!-- Close Button -->
<span onclick="closeImageModal()" 
      style="position: absolute; top: 20px; right: 20px; color:rgb(255, 255, 255);; font-size: 40px; font-weight: bold; cursor: pointer;  background: linear-gradient(to right, rgb(207, 183, 255), rgba(255, 191, 52, 0.66)); padding: 10px 20px; border-radius: 50%; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);">
    &times;
</span>
    <img id="modalImage" src="" alt="Full Image" style="max-width: 90%; max-height: 90%; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);">
</div>

<script>
    function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageSrc;
    modal.style.display = 'flex';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}
</script>

<div style="  margin-top: -10px;">            <!-- Post Actions -->
                <div style="display: flex; align-items: center; gap: 20px;padding-top: 10px;">
                    <!-- Like Button -->
                    <button onclick="likePost(<?php echo $post['post_id']; ?>)" style="background: none; border: none; color:rgb(48, 43, 75); cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-thumbs-up"></i> Like (<span id="likes-<?php echo $post['post_id']; ?>"><?php echo $post['likes_count']; ?></span>)
                    </button>

                    <!-- Comment Button -->
                    <button onclick="toggleComments(<?php echo $post['post_id']; ?>)" style="background: none; border: none; color:rgb(50, 30, 85); cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                </div>

<!-- Comments Section -->
 <div class="comment_container">
<div id="comments-<?php echo $post['post_id']; ?>" style="display: none; margin-top: 10px;">
<form onsubmit="addComment(event, <?php echo $post['post_id']; ?>)" style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px; padding-top: 20px; margin-inline: 30px;">
    <select name="commentator" id="commentator-<?php echo $post['post_id']; ?>" required style="padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <option value="user">Comment as: <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($_SESSION['middle_name'] ?? '') . ' ' . htmlspecialchars($_SESSION['last_name'] ?? ''); ?></option>
        <?php elseif (isset($_SESSION['applicant_id'])): ?>
            <option value="applicant">Comment as: <?php echo htmlspecialchars($_SESSION['applicant_first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($_SESSION['applicant_middle_name'] ?? '') . ' ' . htmlspecialchars($_SESSION['applicant_last_name'] ?? ''); ?></option>
        <?php endif; ?>
        <option value="scholarship">Comment as: <?php echo htmlspecialchars($scholarship['scholarship_name']); ?></option>
    </select>
    <div style=" background: linear-gradient(to right, rgb(207, 183, 255), rgba(255, 191, 52, 0.66));  ; border-radius: 5px; display: flex; align-items: center; gap: 10px; padding: 5px; flex: 1;">
    <input type="text" name="comment" placeholder="Write a comment..." required style="flex: 1; padding: 10px; border-radius: 25px; border: 1px solid #ccc;">
    <button type="submit" style="background: none; border: none; cursor: pointer;">
        <i class="fas fa-paper-plane" style="font-size: 20px; color: white;background-color: rgb(34, 34, 61); padding: 8px; border-radius: 5px;"></i>
    </button>

    </div>
   
</form>
    <div id="comment-list-<?php echo $post['post_id']; ?>" class="comments-section">
        <?php
        $sqlComments = "SELECT 
                            c.comment_text, 
                            c.date_commented, 
                            c.commentator, 
                            s.scholarship_name, 
                            s.logo AS scholarship_logo,
                            u.first_name AS user_first_name, 
                            u.middle_name AS user_middle_name, 
                            u.last_name AS user_last_name, 
                            u.profile_avatar AS user_avatar,
                            a.first_name AS applicant_first_name, 
                            a.middle_name AS applicant_middle_name, 
                            a.last_name AS applicant_last_name, 
                            a.profile_picture AS applicant_picture
                        FROM scholarship_post_comments c
                        LEFT JOIN scholarships s ON c.scholarship_id = s.scholarship_id
                        LEFT JOIN users u ON c.user_id = u.user_id
                        LEFT JOIN applicants a ON c.applicant_id = a.applicant_id
                        WHERE c.post_id = ?
                        ORDER BY c.date_commented ASC";
        $stmt = $conn->prepare($sqlComments);
        $stmt->bind_param('i', $post['post_id']);
        $stmt->execute();
        $commentsResult = $stmt->get_result();

        while ($comment = $commentsResult->fetch_assoc()):
            $commentatorName = '';
            $commentatorImage = '';

            if ($comment['commentator'] === 'scholarship') {
                $commentatorName = $comment['scholarship_name'];
                $commentatorImage = !empty($comment['scholarship_logo']) ? 'data:image/jpeg;base64,' . base64_encode($comment['scholarship_logo']) : 'default_logo_page.jpg';
            } elseif ($comment['commentator'] === 'user') {
                $commentatorName = $comment['user_first_name'] . ' ' . $comment['user_middle_name'] . ' ' . $comment['user_last_name'];
                $commentatorImage = !empty($comment['user_avatar']) ? 'data:image/jpeg;base64,' . base64_encode($comment['user_avatar']) : 'default_user_avatar.jpg';
            } elseif ($comment['commentator'] === 'applicant') {
                $commentatorName = $comment['applicant_first_name'] . ' ' . $comment['applicant_middle_name'] . ' ' . $comment['applicant_last_name'];
                $commentatorImage = !empty($comment['applicant_picture']) ? 'data:image/jpeg;base64,' . base64_encode($comment['applicant_picture']) : 'default_applicant_picture.jpg';
            }
        ?>
            <div style="margin-bottom: 10px; background-color: white;padding: 10px;  border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); display: flex; gap: 10px;">
                <img src="<?php echo $commentatorImage; ?>" 
                     alt="Commentator Image" 
                     style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid #ccc;">
                <div>
                    <p style="margin: 0; font-weight: bold;"><?php echo htmlspecialchars($commentatorName); ?></p>
                    <p style="margin: 0;"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                    <p style="margin: 0; font-size: 12px; color: grey;"><?php echo date('F j, Y, g:i a', strtotime($comment['date_commented'])); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</div>
        </div>
        </div>



        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts found.</p>
    <?php endif; ?>
    </div>
    </div>
   


<div id="details" class="section">
    <h3 style="margin-top: -10px; color: rgb(255, 123, 0); font-size: 22px; display: flex; align-items: center; gap: 10px; width: 98%; background: linear-gradient(to right, rgba(215, 196, 253, 0.91), rgba(255, 191, 52, 0.66)); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fas fa-info-circle" style="color: rgb(255, 136, 0);"></i> Scholarship Details
    </h3>

    <!-- Description -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-file-alt" style="color: rgb(255, 136, 0);"></i> Description:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_general_description_s1'])); ?></p>
    </div>

    <!-- Selection Criteria -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-check-circle" style="color: rgb(255, 136, 0);"></i> Selection Criteria:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_selection_criteria'])); ?></p>
    </div>

    <!-- Education Details -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-graduation-cap" style="color: rgb(255, 136, 0);"></i> Education Details:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_education_details_s2'])); ?></p>
    </div>

    <!-- Financial Assistance -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-hand-holding-usd" style="color: rgb(255, 136, 0);"></i> Financial Assistance:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_financial_assistance_details_s3'])); ?></p>
    </div>

    <!-- Maintaining Requirements -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-tasks" style="color: rgb(255, 136, 0);"></i> Maintaining Requirements:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_maintaing_s4'])); ?></p>
    </div>

    <!-- Effects for Other Scholarship Grants/Assistance -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-exchange-alt" style="color: rgb(255, 136, 0);"></i> Effects for Other Scholarship Grants/Assistance:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['Scholarship_effects_for_others_s5'])); ?></p>
    </div>

    <!-- Forfeiture of Benefit -->
    <div style="margin-bottom: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-ban" style="color: rgb(255, 136, 0);"></i> Forfeiture of Benefit:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['forfeiture_of_benefit'])); ?></p>
    </div>
</div>

<div id="requirements" class="section">
    <h3 style="margin-top: -10px; color: rgb(255, 123, 0); font-size: 22px; display: flex; align-items: center; gap: 10px; width: 98%; background: linear-gradient(to right, rgba(215, 196, 253, 0.91), rgba(255, 191, 52, 0.66)); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fas fa-list-alt" style="color: rgb(255, 136, 0);"></i> Requirements
    </h3>

    <ul style="list-style: none; padding: 0; margin: 0;">
        <?php foreach ($requirements as $requirement): ?>
            <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                <i class="fas fa-check-circle" style="color: #28a745;"></i>
                <span>
                    <strong><?php echo htmlspecialchars($requirement['requirement_name']); ?></strong>: <?php echo htmlspecialchars($requirement['label']); ?>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="column" id="two" style="margin-top: 20px;">
        <h3 style="color: rgb(255, 123, 0); font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-sticky-note" style="color: rgb(255, 136, 0);"></i> Note for Submission:
        </h3>
        <p style="margin-left: 30px;"><?php echo nl2br(htmlspecialchars($scholarship['note_for_submission'])); ?></p>
    </div>
</div>

<div id="applicants" class="section">
    <h3 style="margin-top: -10px; color: rgb(255, 123, 0); font-size: 22px; display: flex; align-items: center; gap: 10px; width: 98%; background: linear-gradient(to right, rgba(215, 196, 253, 0.91), rgba(255, 191, 52, 0.66)); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fas fa-users" style="color: rgb(255, 136, 0);"></i> Applicants
    </h3>
    <p>Approved Applicants: <?php echo $applicantStats['approved_count']; ?></p>
    <p>Pending Applicants: <?php echo $applicantStats['pending_count']; ?></p>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="background-color: #f9f9f9; border-bottom: 1px solid #ccc;">
                <th style="padding: 10px; text-align: left;">Full Name</th>
                <th style="padding: 10px; text-align: left;">Date of Submission</th>
                <th style="padding: 10px; text-align: left;">Status</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($applicantsList)): ?>
                <?php foreach ($applicantsList as $applicant): ?>
                    <tr style="border-bottom: 1px solid #ccc;">
                        <td style="padding: 10px;">
                            <?php echo htmlspecialchars($applicant['first_name'] . ' ' . $applicant['middle_name'] . ' ' . $applicant['last_name']); ?>
                        </td>
                        <td style="padding: 10px;">
                            <?php echo htmlspecialchars($applicant['submission_date']); ?>
                        </td>
                        <td style="padding: 10px;">
                            <?php echo htmlspecialchars($applicant['status']); ?>
                        </td>
                        <td style="padding: 10px;">
    <a href="view_requirement.php?record_id=<?php echo $applicant['record_id']; ?>" style="color: #007bff; text-decoration: none;">
        <i class="fas fa-eye"></i> View
    </a>
</td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" style="padding: 10px; text-align: center;">No applicants found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="contacts" class="section">
    <h3 style="margin-top: -10px;  color: rgb(255, 123, 0); font-size: 22px; display: flex; align-items: center; gap: 10px; width: 98%; background: linear-gradient(to right, rgba(215, 196, 253, 0.91), rgba(255, 191, 52, 0.66)); padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <i class="fas fa-phone-alt" style="color: rgb(230, 140, 7);"></i> Contacts
    </h3>

    <h3 style="color: rgb(33, 25, 49); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-user-tie"></i> Program Coordinator:
    </h3>
    <p style="margin-left: 30px;"><?php echo htmlspecialchars($scholarship['admin_name']); ?></p>

    <h3 style="color: rgb(33, 25, 49); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-map-marker-alt"></i> Office Address:
    </h3>
    <p style="margin-left: 30px;">[Add address here]</p>

    <h3 style="color: rgb(33, 25, 49); display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-clock"></i> Office Hours:
    </h3>
    <p style="margin-left: 30px;">[Add office hours here]</p>
</div>
    </div>
                    
 
    </div>
    </main>
    <script>
   function showSection(sectionId) {
    // Hide all sections
    document.querySelectorAll('.section').forEach(section => {
        section.classList.remove('active');
    });

    // Show the selected section
    document.getElementById(sectionId).classList.add('active');

    // Remove 'active' class from all buttons
    document.querySelectorAll('.nav-button').forEach(button => {
        button.classList.remove('active');
    });

    // Add 'active' class to the clicked button
    document.querySelector(`.nav-button[onclick="showSection('${sectionId}')"]`).classList.add('active');
}
</script>
<script>
    function openModal() {
        document.getElementById('newPostModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('newPostModal').style.display = 'none';
    }
</script>

<script>
    function openModal() {
        document.getElementById('newPostModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('newPostModal').style.display = 'none';
    }

    function previewImages(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = ''; // Clear previous previews

    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.borderRadius = '5px';
            img.style.objectFit = 'cover';
            img.style.border = '1px solid #ccc';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
}

</script>
<script>
    const form = document.querySelector('form[action="post_scholarship.php"]');

    form.addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    // Show the loading message
    const loadingMessage = document.getElementById('loadingMessage');
    loadingMessage.style.display = 'flex';

    // Prepare form data
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (response.ok) {
            console.log('Form submitted successfully.');

            // Hide the loading message
            loadingMessage.style.display = 'none';

            // Show the success message
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'flex';

            // Redirect after a short delay
            const scholarshipId = formData.get('scholarship_id');
            setTimeout(() => {
                window.location.href = `view_scholarship.php?scholarship_id=${scholarshipId}`;
            }, 2000); // 2-second delay for better UX
        } else {
            throw new Error('Failed to upload post.');
        }
    })
    .catch(error => {
        console.error('Error during fetch:', error);

        // Hide the loading message
        loadingMessage.style.display = 'none';

        // Show an error message to the user
        alert('An error occurred while uploading your post. Please try again.');
    });
});s
    
</script>
<script>
    function likePost(postId) {
        fetch(`like_post.php?post_id=${postId}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likesCount = document.getElementById(`likes-${postId}`);
                    likesCount.textContent = data.likes_count;
                } else {
                    alert('You can only like a post once.');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function toggleComments(postId) {
        const commentsSection = document.getElementById(`comments-${postId}`);
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
    }

    function addComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const commentText = form.comment.value;
    const commentatorValue = form.commentator.value;

    // Parse the commentator value to extract type and ID
    const [commentatorType, commentatorId] = commentatorValue.split('_');

    fetch('add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}&commentator_type=${commentatorType}&commentator_id=${commentatorId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentList = document.getElementById(`comment-list-${postId}`);
            const newComment = document.createElement('div');
            newComment.style.marginBottom = '10px';
            newComment.style.padding = '10px';
            newComment.style.background = '#f9f9f9';
            newComment.style.borderRadius = '5px';
            newComment.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
            newComment.innerHTML = `
                <p style="margin: 0; font-weight: bold;">${data.commentator_name}</p>
                <p style="margin: 0;">${data.comment_text}</p>
                <p style="margin: 0; font-size: 12px; color: #888;">${data.date_commented}</p>
            `;
            commentList.appendChild(newComment);
            form.reset();
        } else {
            alert('Failed to add comment.');
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
<script>
  function likePost(postId) {
    fetch('like_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likesCount = document.getElementById(`likes-${postId}`);
            likesCount.textContent = data.likes_count;

            const likeButton = document.querySelector(`button[onclick="likePost(${postId})"]`);
            if (data.liked) {
                likeButton.innerHTML = `<i class="fas fa-thumbs-up"></i> Unlike (${data.likes_count})`;
            } else {
                likeButton.innerHTML = `<i class="fas fa-thumbs-up"></i> Like (${data.likes_count})`;
            }
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}

function toggleComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    const isVisible = commentsSection.style.display === 'block';

    // Toggle visibility
    commentsSection.style.display = isVisible ? 'none' : 'block';

    // If comments are being shown, fetch them dynamically
    if (!isVisible) {
        fetch(`fetch_comments.php?post_id=${postId}`)
            .then(response => response.json())
            .then(data => {
                const commentList = document.getElementById(`comment-list-${postId}`);
                commentList.innerHTML = ''; // Clear existing comments

                if (data.comments.length > 0) {
                    data.comments.forEach(comment => {
                        const commentDiv = document.createElement('div');
                        commentDiv.style.marginBottom = '10px';
                        commentDiv.style.padding = '10px';
                        commentDiv.style.background = '#f9f9f9';
                        commentDiv.style.borderRadius = '5px';
                        commentDiv.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
                        commentDiv.innerHTML = `
                            <p style="margin: 0; font-weight: bold;">${comment.commentator_name}</p>
                            <p style="margin: 0;">${comment.comment_text}</p>
                            <p style="margin: 0; font-size: 12px; color: #888;">${comment.date_commented}</p>
                        `;
                        commentList.appendChild(commentDiv);
                    });
                } else {
                    commentList.innerHTML = '<p style="color: #888;">No comments yet. Be the first to comment!</p>';
                }
            })
            .catch(error => console.error('Error fetching comments:', error));
    }
}

function addComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const commentText = form.comment.value;
    const commentator = form.commentator.value;

    fetch('add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&comment=${encodeURIComponent(commentText)}&commentator=${commentator}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const commentList = document.getElementById(`comment-list-${postId}`);
            const newComment = document.createElement('div');
            newComment.style.marginBottom = '10px';
            newComment.style.padding = '10px';
            newComment.style.background = '#f9f9f9';
            newComment.style.borderRadius = '5px';
            newComment.style.boxShadow = '0 1px 3px rgba(0, 0, 0, 0.1)';
            newComment.innerHTML = `
                <p style="margin: 0; font-weight: bold;">${data.commentator_name}</p>
                <p style="margin: 0;">${data.comment_text}</p>
                <p style="margin: 0; font-size: 12px; color: #888;">${data.date_commented}</p>
            `;
            commentList.appendChild(newComment);
            form.reset();
        } else {
            alert('Failed to add comment.');
        }
    })
    .catch(error => console.error('Error:', error));
}
function handleCommentatorChange(selectElement, scholarshipId) {
    const postId = selectElement.id.split('-')[1];
    const scholarshipInput = document.getElementById(`scholarship-id-${postId}`);

    if (selectElement.value === 'scholarship') {
        scholarshipInput.value = scholarshipId;
    } else {
        scholarshipInput.value = '';
    }
}

function toggleDropdown(postId) {
    const dropdown = document.getElementById(`dropdown-${postId}`);
    const isVisible = dropdown.style.display === 'block';
    dropdown.style.display = isVisible ? 'none' : 'block';

    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu').forEach(menu => {
        if (menu.id !== `dropdown-${postId}`) {
            menu.style.display = 'none';
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function (event) {
    const isDropdown = event.target.closest('.dropdown');
    if (!isDropdown) {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.style.display = 'none';
        });
    }
});
</script>

<script>
function deletePost(postId) {
    if (confirm('Are you sure you want to delete this post?')) {
        fetch(`delete_post.php?post_id=${postId}`, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Post deleted successfully.');
                    location.reload(); // Reload the page to reflect changes
                } else {
                    alert('Failed to delete post.');
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

function editPost(postId) {
    // Redirect to the edit post page
    window.location.href = `edit_post.php?post_id=${postId}`;
}

function togglePrivacy(postId) {
    fetch(`toggle_privacy.php?post_id=${postId}`, { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Post privacy updated to: ${data.privacy_status}`);
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Failed to update post privacy.');
            }
        })
        .catch(error => console.error('Error:', error));
}



</script>

<script>
    document.getElementById('images').addEventListener('change', function (event) {
    const files = event.target.files;
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = ''; // Clear previous previews

    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '80px';
            img.style.height = '80px';
            img.style.borderRadius = '5px';
            img.style.objectFit = 'cover';
            img.style.border = '1px solid #ccc';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>
</body>
</html>
