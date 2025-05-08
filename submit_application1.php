<?php
include 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: You must be logged in to submit an application.");
}

// Check form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate applicant and scholarship IDs
    if (!isset($_POST['applicant_id']) || !isset($_POST['scholarship_id'])) {
        die("Error: Missing applicant_id or scholarship_id.");
    }

    $applicant_id = intval($_POST['applicant_id']);
    $scholarship_id = intval($_POST['scholarship_id']);

    // Debugging Output
    echo "Debug: Applicant ID: $applicant_id, Scholarship ID: $scholarship_id<br>";

    // Insert into applicant_scholarship_records
    $stmt = $conn->prepare("
        INSERT INTO applicant_scholarship_records (applicant_id, scholarship_id, status, submission_date)
        VALUES (?, ?, 'pending', NOW())
    ");
    $stmt->bind_param('ii', $applicant_id, $scholarship_id);

    if (!$stmt->execute()) {
        die("Error inserting applicant record: " . $stmt->error);
    }

    $record_id = $stmt->insert_id;

    // Handle file uploads for scholarship requirements
    foreach ($_FILES as $key => $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $scholarship_requirement_id = intval(str_replace('requirement_', '', $key));
            $fileTmpName = $file['tmp_name'];
            $fileName = basename($file['name']);
            $fileType = $file['type'];
            $fileData = file_get_contents($fileTmpName);

            // Debugging: Print file details
            echo "Processing file: $fileName | Type: $fileType | Size: " . filesize($fileTmpName) . " bytes<br>";

            // Ensure correct column name (uploaded_file_path)
            $stmt = $conn->prepare("
                INSERT INTO applicant_requirements (record_id, applicant_id, scholarship_requirements_id, uploaded_file_path, status, submission_date)
                VALUES (?, ?, ?, ?, 'submitted', NOW())
            ");
            $stmt->bind_param('iiib', $record_id, $applicant_id, $scholarship_requirement_id, $fileData);

            if (!$stmt->execute()) {
                die("Error inserting file data: " . $stmt->error);
            }
        } else {
            echo "File upload error for $key: Code " . $file['error'] . "<br>";
        }
    }

    // Redirect with confirmation
    echo "<script>alert('Application submitted successfully!'); window.location.href = 'manage_applications.php';</script>";
}
?>
