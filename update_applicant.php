<?php
require 'db.php'; // Ensure connection to database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $applicant_id = $_POST['applicant_id'];
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? '';
    $last_name = $_POST['last_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $place_of_birth = $_POST['place_of_birth'];
    $mobile_number = $_POST['mobile_number'];
    $street_barangay = $_POST['street_barangay'];
    $town_city_municipality = $_POST['town_city_municipality'];
    $province = $_POST['province'];
    $zip_code = $_POST['zip_code'];
    $email_address = $_POST['email_address'];
    $school_attended = $_POST['school_attended'] ?? 'N/A';
    $school_id_number = $_POST['school_id_number'] ?? 'N/A';
    $grade_level = $_POST['grade_level'] ?? 'N/A';
    $course = $_POST['course'] ?? 'N/A';
    $type_of_disability = $_POST['type_of_disability'] ?? 'N/A';

    // Handle profile picture
    $profile_picture = null;
    if (!empty($_FILES["profile_picture"]["tmp_name"])) {
        // A new file is uploaded
        $file_tmp = $_FILES["profile_picture"]["tmp_name"];
        $profile_picture = file_get_contents($file_tmp); // Read the file as binary data
    } else {
        // Use the existing profile picture from the hidden input
        $profile_picture = !empty($_POST['current_profile_picture']) ? base64_decode($_POST['current_profile_picture']) : null;
    }

    // Prepare SQL update query
    $sql = "UPDATE applicants SET 
                first_name = ?, middle_name = ?, last_name = ?, 
                age = ?, gender = ?, birthdate = ?, place_of_birth = ?, 
                mobile_number = ?, street_barangay = ?, town_city_municipality = ?, 
                province = ?, zip_code = ?, email_address = ?, 
                school_attended = ?, school_id_number = ?, grade_level = ?, 
                course = ?, type_of_disability = ?, profile_picture = ? 
            WHERE applicant_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssisssssssssssssbsi",
        $first_name, $middle_name, $last_name, 
        $age, $gender, $birthdate, $place_of_birth, 
        $mobile_number, $street_barangay, $town_city_municipality, 
        $province, $zip_code, $email_address, 
        $school_attended, $school_id_number, $grade_level, 
        $course, $type_of_disability, $profile_picture, $applicant_id
    );

    if ($stmt->execute()) {
        echo "Applicant information updated successfully.";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>