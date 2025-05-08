<?php
include 'db.php';
$currentPage = 'manage_applications';
include 'header_sidebar.php';

// Check if a session is already active before starting one
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to access this page.");
}

if (isset($_GET['record_id'])) {
    $record_id = $_GET['record_id'];

    // Fetch applicant details along with the scholarship_id and status from applicant_scholarship_records
    $queryApplicant = "SELECT asr.scholarship_id, asr.status, a.* 
                       FROM applicant_scholarship_records asr
                       INNER JOIN applicants a ON asr.applicant_id = a.applicant_id
                       WHERE asr.record_id = ?";
    $stmtApplicant = $conn->prepare($queryApplicant);
    $stmtApplicant->bind_param("i", $record_id);
    $stmtApplicant->execute();
    $resultApplicant = $stmtApplicant->get_result();
    $applicant = $resultApplicant->fetch_assoc(); // Fetch single applicant row

    // Fetch scholarship name using scholarship_id
    $queryScholarship = "SELECT scholarship_name FROM scholarships WHERE scholarship_id = ?";
    $stmtScholarship = $conn->prepare($queryScholarship);
    $stmtScholarship->bind_param("i", $applicant['scholarship_id']);
    $stmtScholarship->execute();
    $resultScholarship = $stmtScholarship->get_result();
    $scholarship = $resultScholarship->fetch_assoc();
    $scholarshipName = $scholarship['scholarship_name'] ?? 'Unknown Scholarship';

    // Fetch notes from application_notes
    $notes = [];
    $queryNotes = "SELECT note, status FROM application_notes WHERE record_id = ?";
    $stmtNotes = $conn->prepare($queryNotes);
    $stmtNotes->bind_param("i", $record_id);
    $stmtNotes->execute();
    $resultNotes = $stmtNotes->get_result();
    while ($row = $resultNotes->fetch_assoc()) {
        $notes[] = $row;
    }

    // Fetch submitted requirements
    $queryRequirements = "SELECT ar.applicant_requirements_id, ar.uploaded_file_path, sr.requirement_id, r.requirement_name 
                          FROM applicant_requirements ar
                          INNER JOIN scholarship_requirements sr ON ar.scholarship_requirements_id = sr.scholarship_requirement_id
                          INNER JOIN requirements r ON sr.requirement_id = r.requirement_id
                          WHERE ar.record_id = ?";
    $stmtRequirements = $conn->prepare($queryRequirements);
    $stmtRequirements->bind_param("i", $record_id);
    $stmtRequirements->execute();
    $result = $stmtRequirements->get_result(); // Store result for looping
} else {
    echo "<p>Invalid request.</p>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Requirements</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    <style>
       main{
        margin-left: 320px;
        margin-top: 20px;
       }
        .applicant-details {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .details-section {
            display: inline-block;
            width: 25%; /* Adjust width to fit two sections per row */
            vertical-align: top;
            margin-bottom: 10px;
        }
    
        .details-section img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            display: block;
            margin: 0 auto 10px;
        }
        .details-section h3 {
            margin-top: 5px;
            font-size: 18px;
            color: #333;
          
        }
        .details-section p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
            margin-top: 15px;
           
        }
        .details-section input ,select {
            font-size: 14px;
            color: #555;
            background: none;
            border: none;
            outline: none;
            width: 50%;
            padding: 5px;
            margin-inline: 30px;
            background-color:rgb(228, 228, 228);
            border: 3px solid rgb(226, 226, 226);
        }
        .details-section input:hover,
        .details-section select:hover,
        .details-section select:focus,
        .details-section input:focus {
            border: 3px solid #007bff;
            background-color:hsl(0, 0.00%, 100.00%);
        }
        .avatar-container {
            text-align: center;
        }
        .avatar-container img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ccc;
            margin-bottom: 10px;
        }
        .avatar-container input[type="file"] {
            display: none;
        }
        .avatar-container label {
            display: inline-block;
            padding: 5px 10px;
                  
    background: linear-gradient(135deg, rgb(46, 42, 77), rgb(23, 23, 31));
  
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .requirements-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }
        .requirement-card {
            width: 250px;
            height: 350px;
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .requirement-card img {
            max-width: 220px;
            height: 180px;
            object-fit: contain;
            border-radius: 5px;
        }
        .requirement-card button {
            margin-top: 10px;
            padding: 10px 15px;
           
    background: linear-gradient(135deg, rgb(46, 42, 77), rgb(23, 23, 31));
  
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .requirement-card a {
            text-decoration: none;
            color:rgb(249, 255, 172);
            
            background-color:rgb(17, 16, 41);
            padding: 10px 30px;
            border-radius: 25px;
        }
        @media (max-width: 768px) {
            .details-section {
                width: 100%; /* Stack sections on smaller screens */
            }
        }
        .status{
            width: 100%;
            margin-top: 20px;
            padding: 20px;
            margin-bottom: 0px;
            border: 1px solid #ccc;
            padding-bottom: 30px;
            background :rgb(255, 255, 255);
            color: white;
            border-radius: 10px;
            border: 3px solid rgb(209, 209, 209);
        }
        
        .status h3{=
            font-size: 20px;
            color: rgb(255, 255, 255);
            padding: 10px 20px;
            text-align: center;
            background-color: rgb(44, 92, 50);

        }
        .statusform{
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
            width: 50%;
            margin: 0 auto;
            height: 400px;
            border: none;
            border-radius: 10px;
        }
        .status select {
            font-size: 14px;
            color: #555;
            background: none;
            border: none;
            outline: none;
            width: 50%;
            padding: 5px;
            margin-inline: 30px;
            background-color:rgb(228, 228, 228);
            border: 3px solid rgb(226, 226, 226);
        }
        .status select:hover {
    border: 3px solid #007bff;
    background-color: hsl(0, 0.00%, 100.00%);
}
button.status-modal {
    
    background: linear-gradient(135deg, rgb(46, 42, 77), rgb(23, 23, 31));
  
    color: rgb(255, 255, 255);
  
    border-radius: 15px;
    padding: 12px 30px;
    font-size: 14px;
    cursor: pointer;
}

button.status-modal:hover {
    background-color: #0056b3;
    color: white;
}
    </style>
</head>
<body>
    <main>
<!-- Flex Container for Horizontal Alignment -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">

    <!-- Scholarship Name Section -->
    <div style="font-size: 18px; font-weight: bold; color: #333;">
        APPLYING TO: <?php echo htmlspecialchars($scholarshipName); ?>
    </div>

    <!-- Open Status Button -->
    <div>
        <button class="status-modal" onclick="openStatusModal()" >
            Update Status
        </button>
    </div>

    <!-- Cancel or X Button -->
    <div>
        <a href="manage_applications.php" style="text-decoration: none; color: white;">
            <button style="padding: 10px 20px; background-color: red; color: white; border: none; border-radius: 5px; cursor: pointer;">
                &times; Cancel
            </button>
        </a>
    </div>

</div>

<!-- Add this inside the <body> tag -->
<div id="successMessage" style="display: none; position: fixed; top: 20px; right: 20px; background-color: #28a745; color: white; padding: 10px 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);">
    Updated successfully!
</div>

    <!-- Applicant Details Section -->
<!-- Update the form to include an onsubmit event -->
<form method="POST"  action="update_applicant.php" onsubmit="showSuccessMessage(event)" enctype="multipart/form-data">
           <!-- Hidden input for applicant_id -->
    <input type="hidden" name="applicant_id" value="<?php echo htmlspecialchars($applicant['applicant_id']); ?>">

<div class="applicant-details">


         <!-- Extra Section -->
         <div class="details-section avatar-container">
                <h3>Profile Picture</h3>
               <!-- Hidden input to store the current profile picture -->
    <input type="hidden" id="currentProfilePicture" name="current_profile_picture" value="<?php echo !empty($applicant['profile_picture']) ? base64_encode($applicant['profile_picture']) : ''; ?>">

<!-- Preview the current or newly selected profile picture -->
<img id="profilePreview" src="<?php echo !empty($applicant['profile_picture']) ? 'data:image/jpeg;base64,' . base64_encode($applicant['profile_picture']) : 'default_avatar.png'; ?>" alt="Profile Picture">

<!-- File input for selecting a new profile picture -->
<input type="file" id="profilePicture" name="profile_picture" accept="image/*" onchange="previewImage(event)">
<label for="profilePicture">Change Avatar</label>


                <p><strong>First Name:</strong> </br>
    <input type="text" name="first_name" value="<?php echo htmlspecialchars($applicant['first_name']); ?>">
</p>

<p><strong>Middle Name:</strong> </br>
    <input type="text" name="middle_name" value="<?php echo htmlspecialchars($applicant['middle_name'] ?? ''); ?>">
</p>

<p><strong>Last Name:</strong> </br>
    <input type="text" name="last_name" value="<?php echo htmlspecialchars($applicant['last_name']); ?>">
</p>

            </div>

            <!-- Section 1 -->
            <div class="details-section">
                <h3>Personal Information</h3>

                <p><strong>Age:</strong> </br>
        <input type="number" name="age" value="<?php echo htmlspecialchars($applicant['age']); ?>">
    </p>

    <p><strong>Gender:</strong> </br>
        <select name="gender">
            <option value="male" <?php echo $applicant['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
            <option value="female" <?php echo $applicant['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
            <option value="other" <?php echo $applicant['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
        </select>
    </p>

                <p><strong>Birthdate:</strong>  </br>
                    <input type="date" name="birthdate" value="<?php echo htmlspecialchars($applicant['birthdate']); ?>">
                </p>
                <p><strong>Place of Birth:</strong>  </br>
                    <input type="text" name="place_of_birth" value="<?php echo htmlspecialchars($applicant['place_of_birth']); ?>">
                </p>
                <p><strong>Mobile Number:</strong>  </br>
                    <input type="text" name="mobile_number" value="<?php echo htmlspecialchars($applicant['mobile_number']); ?>">
                </p>
                 
            </div>

            <!-- Section 2 -->
            <div class="details-section">
                <h3>Address</h3>
                <p><strong>Street/Barangay:</strong>  </br>
                    <input type="text" name="street_barangay" value="<?php echo htmlspecialchars($applicant['street_barangay']); ?>">
                </p>
                <p><strong>Town/City/Municipality:</strong>  </br>
                    <input type="text" name="town_city_municipality" value="<?php echo htmlspecialchars($applicant['town_city_municipality']); ?>">
                </p>
                <p><strong>Province:</strong>  </br>
                    <input type="text" name="province" value="<?php echo htmlspecialchars($applicant['province']); ?>">
                </p>
                <p><strong>Zip Code:</strong>  </br>
                    <input type="text" name="zip_code" value="<?php echo htmlspecialchars($applicant['zip_code']); ?>">
                </p>
                <p><strong>Email Address:</strong>  </br>
                    <input type="email" name="email_address" value="<?php echo htmlspecialchars($applicant['email_address']); ?>">
                </p>
            </div>

            <!-- Section 3 -->
            <div class="details-section">
                <h3>Educational Information</h3>
                <p><strong>School Attended:</strong>  </br>
                    <input type="text" name="school_attended" value="<?php echo htmlspecialchars($applicant['school_attended'] ?? 'N/A'); ?>">
                </p>
                <p><strong>School ID Number:</strong>  </br>
                    <input type="text" name="school_id_number" value="<?php echo htmlspecialchars($applicant['school_id_number'] ?? 'N/A'); ?>">
                </p>
                <p><strong>Grade Level:</strong>  </br>
                    <input type="text" name="grade_level" value="<?php echo htmlspecialchars($applicant['grade_level'] ?? 'N/A'); ?>">
                </p>
                <p><strong>Course:</strong>  </br>
                    <input type="text" name="course" value="<?php echo htmlspecialchars($applicant['course'] ?? 'N/A'); ?>">
                </p>
                <p><strong>Type of Disability:</strong>  </br>
                    <input type="text" name="type_of_disability" value="<?php echo htmlspecialchars($applicant['type_of_disability'] ?? 'N/A'); ?>">
                </p>
                
        <button type="submit" style="margin-top: 20px; padding: 10px 20px;      
    background: linear-gradient(135deg, rgb(46, 42, 77), rgb(23, 23, 31));
   color: white; border: none; border-radius: 5px; cursor: pointer;">Save Changes</button>
   
            </div>
        </div> </form>

    <!-- Requirements Section -->
    <h2>Submitted Requirements</h2>
    <div class="requirements-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $requirement_name = htmlspecialchars($row['requirement_name']);
                $file_id = $row['applicant_requirements_id'];
                $image_url = !empty($row['uploaded_file_path']) ? "view_image.php?file_id=" . urlencode($file_id) : "default_req.png";
            ?>
            <div class="requirement-card">
                <p><strong><?php echo $requirement_name; ?></strong></p>
                <img src="<?php echo $image_url; ?>" alt="Requirement Image">
                <button onclick="openImageModal('<?php echo $image_url; ?>')">View</button>
                <?php if (!empty($row['uploaded_file_path'])): ?>
                    <p><a href="download_file.php?file_id=<?php echo urlencode($file_id); ?>">
                    <i class="fas fa-download"></i> Download File</a></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</div>
<!-- Image Modal -->
<div id="imageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; z-index: 1000;">
    <img id="modalImage" src="" alt="Requirement Image" >
    <button onclick="closeImageModal()" style="position: absolute; top: 10px; right: 10px; padding: 15px 20px; background-color: red; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 24px;">
        &times;
    </button>
</div>
<!-- Modal Structure -->
<div id="statusIssuesModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background-color: white; padding: 20px; border-radius: 10px; width: 80%; max-width: 800px; position: relative;">
        <!-- Close Button -->
        <button onclick="closeStatusModal()" style="position: absolute; top: 10px; right: 10px; padding: 5px 10px; background-color: red; color: white; border: none; border-radius: 5px; cursor: pointer;">
    <i class="fas fa-times"></i>
</button>

        <!-- Parent Container for Status Form and Application Issues -->
        <div style="display: flex; justify-content: space-between; gap: 20px; background-color: rgb(199, 199, 199); padding: 20px; border-radius: 10px; border: 3px solid rgb(87, 87, 87);">
            <!-- Status Update Section -->
            <div class="status" style="width: 45%;">
                <form class="statusform" id="statusForm">
                    <h3>Update Status</h3>
                    <!-- Hidden input for record_id -->
                    <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($record_id); ?>">

                    <p><strong>Status:</strong></p>
                    <select id="statusDropdown" name="status" onchange="toggleNotesAndIssues(event)">
                        <option value="pending" <?php echo $applicant['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $applicant['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo $applicant['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="needs_revision" <?php echo $applicant['status'] === 'needs_revision' ? 'selected' : ''; ?>>Needs Revision</option>
                    </select>

                    <!-- Notes Form (Hidden by Default) -->
                    <div id="notesForm" style="display: none; margin-top: 20px;">
                        <h2 style="color: rgb(38, 65, 26);">Application Notes</h2>
                        <textarea name="note" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;" placeholder="Enter your notes here..."></textarea>
                    </div>

                    <button type="submit" style="margin-top: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">Update Status</button>
                </form>
            </div>

            <!-- Issue Container -->
            <div id="issueContainer" style="display: <?php echo $applicant['status'] === 'needs_revision' ? 'block' : 'none'; ?>; width: 45%; padding: 20px; border: 1px solid #ccc; background-color: rgb(255, 224, 224); border-radius: 10px; border: solid 3px rgb(255, 141, 141);">
                <h3 style="color: rgb(255, 0, 0); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle" style="color: rgb(255, 0, 0);"></i> Application Issues
                </h3>

                <?php if (!empty($notes)): ?>
                    <ul style="list-style-type: none; padding: 0;">
                        <?php foreach ($notes as $note): ?>
                            <li style="margin-bottom: 10px; padding: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: #fff;">
                                <p><strong>Status:</strong> <?php echo htmlspecialchars($note['status']); ?></p>
                                <p><?php echo htmlspecialchars($note['note']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No issues found for this record.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<script>
    // Show or hide the notes form based on the selected status
    function toggleNotesAndIssues(event) {
    const notesForm = document.getElementById('notesForm');
    const issueContainer = document.getElementById('issueContainer');

    if (event.target.value === 'needs_revision') {
        notesForm.style.display = 'block';
        issueContainer.style.display = 'block';
    } else {
        notesForm.style.display = 'none';
        issueContainer.style.display = 'none';
    }
}
    // Handle the status form submission via AJAX
    document.getElementById('statusForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        const form = event.target;
        const formData = new FormData(form); // Collect form data

        // Send the form data via AJAX
        fetch('update_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse the JSON response
        .then(data => {
            if (data.status === 'success') {
                alert('Status updated successfully!');
                // Optionally, reload the page or update the UI dynamically
            } else {
                alert('Error updating status: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status.');
        });
    });
</script>


    <!-- JavaScript to Open and Close Modal -->
    <script>
function openImageModal(imageUrl) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    modalImage.src = imageUrl;
    modal.style.display = 'flex';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}
    </script>

<!-- JavaScript for Image Preview -->
<script>
    document.getElementById('updateForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        const form = event.target;
        const formData = new FormData(form); // Collect form data

        // Send the form data via AJAX
        fetch('update_applicant.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Parse the JSON response
        .then(data => {
            if (data.status === 'success') {
                // Show the success message
                const successMessage = document.getElementById('successMessage');
                successMessage.style.display = 'block';

                // Hide the success message after 3 seconds
                setTimeout(() => {
                    successMessage.style.display = 'none';
                }, 3000);
            } else {
                alert('Error updating record: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the record.');
        });
    });

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const output = document.getElementById('profilePreview');
            output.src = reader.result;

            // Clear the hidden input since a new file is selected
            document.getElementById('currentProfilePicture').value = '';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
    <!-- Add this JavaScript at the end of the file -->
<script>
    function showSuccessMessage(event) {
        event.preventDefault(); // Prevent the form from submitting immediately
        const successMessage = document.getElementById('successMessage');
        successMessage.style.display = 'block'; // Show the success message

        // Hide the success message after 3 seconds
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 3000);

        // Optionally, submit the form after showing the message
        setTimeout(() => {
            event.target.submit(); // Submit the form after the message disappears
        }, 3000);
    }
</script>
<script>
    function goBack() {
        window.history.back(); // Navigate to the previous page
    }
</script>
<script>
function openStatusModal() {
    const modal = document.getElementById('statusIssuesModal');
    modal.style.display = 'flex';
}

function closeStatusModal() {
    const modal = document.getElementById('statusIssuesModal');
    modal.style.display = 'none';
}
</script>
</main>
</body>
</html>