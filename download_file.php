<?php
include 'db.php';

if (isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    $query = "SELECT uploaded_file_path FROM applicant_requirements WHERE applicant_requirements_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename='requirement_file.jpg'"); // Change extension based on file type
        echo $row['uploaded_file_path']; // Output binary file data
    } else {
        echo "File not found.";
    }
}
?>
