<?php
require 'db.php'; // Ensure connection to database

header('Content-Type: application/json'); // Set the response type to JSON

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    exit();
}

if (empty($_POST['record_id']) || empty($_POST['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Record ID or status is missing.']);
    exit();
}

$record_id = intval($_POST['record_id']);
$status = $_POST['status'];
$note = $_POST['note'] ?? null;

// Update the status in the applicant_scholarship_records table
$sqlUpdateStatus = "UPDATE applicant_scholarship_records SET status = ? WHERE record_id = ?";
$stmtUpdateStatus = $conn->prepare($sqlUpdateStatus);
$stmtUpdateStatus->bind_param("si", $status, $record_id);

if (!$stmtUpdateStatus->execute()) {
    echo json_encode(['status' => 'error', 'message' => $stmtUpdateStatus->error]);
    exit();
}

// If the status is "needs_revision" and a note is provided, insert the note into the application_notes table
if ($status === 'needs_revision' && !empty($note)) {
    $sqlInsertNote = "INSERT INTO application_notes (record_id, note, status) VALUES (?, ?, 'pending')";
    $stmtInsertNote = $conn->prepare($sqlInsertNote);
    $stmtInsertNote->bind_param("is", $record_id, $note);

    if (!$stmtInsertNote->execute()) {
        echo json_encode(['status' => 'error', 'message' => $stmtInsertNote->error]);
        exit();
    }
}

echo json_encode(['status' => 'success']);
$stmtUpdateStatus->close();
if (isset($stmtInsertNote)) {
    $stmtInsertNote->close();
}
$conn->close();
?>