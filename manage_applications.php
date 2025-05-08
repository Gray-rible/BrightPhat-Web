<?php
$currentPage = 'manage_applications';
include 'header_sidebar.php';
include 'db.php';




// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Get the search name from the GET request
$searchName = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';

// Fetch all scholarships for the filter section
$scholarshipQuery = "SELECT scholarship_id, scholarship_name FROM scholarships";
$scholarshipResult = $conn->query($scholarshipQuery);

// Get the selected scholarship IDs from the filter (if any)
$selectedScholarshipIds = isset($_GET['scholarship_ids']) ? $_GET['scholarship_ids'] : [];

// Base query to fetch the required data

$sql = "
    SELECT 
        asr.record_id, 
        CONCAT(a.first_name, ' ', COALESCE(a.middle_name, ''), ' ', a.last_name) AS full_name, 
        s.scholarship_name, 
        asr.status, 
        asr.submission_date
    FROM 
        applicant_scholarship_records asr
    LEFT JOIN 
        applicants a ON asr.applicant_id = a.applicant_id
    INNER JOIN 
        scholarships s ON asr.scholarship_id = s.scholarship_id
";

// Add scholarship filter condition
if (!empty($selectedScholarshipIds)) {
    $selectedScholarshipIds = array_map('intval', $selectedScholarshipIds); // Sanitize input
    $sql .= " WHERE asr.scholarship_id IN (" . implode(',', $selectedScholarshipIds) . ")";
}

// Add search condition
if (!empty($searchName)) {
    $searchName = $conn->real_escape_string($searchName); // Prevent SQL injection
    $sql .= (strpos($sql, 'WHERE') !== false ? ' AND' : ' WHERE') . 
            " LOWER(CONCAT(a.first_name, ' ', COALESCE(a.middle_name, ''), ' ', a.last_name)) LIKE LOWER('%$searchName%')";
}

// Execute query
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Applications</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  
   <link rel="stylesheet" href="styleformanageapp.css">
   
</head>

<body>
    <main>
        <div class="content-container">
            <!-- Scholarship Applications Table -->
            <div class="applications-table">
                <h2 class="listof">List of Applications</h2>
                <table>
        <thead>
            <tr>
                <th>no.</th>
                <th>Applicant Name</th>
                <th>Scholarship Name</th>
                <th>Status</th>
                <th>Submission Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Loop through the result and display rows in the table
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['record_id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['scholarship_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['submission_date']) . "</td>";
               
                echo "<td><a href='view_requirement.php?record_id=" . $row['record_id'] . "' class='btn btn-primary'><i class='fas fa-folder-open'></i></a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
            </div>

            <!-- Filter Section -->
            <div class="filter-container">
    <h3>Find Applicant</h3>
    <form method="GET" action="">
        <!-- Search Bar -->
        <div class="search-bar">
            <input type="text" name="search_name" placeholder="Search Name" value="<?php echo isset($_GET['search_name']) ? htmlspecialchars($_GET['search_name']) : ''; ?>">
            <button type="submit" class="search-button">Search</button>
        </div>
          <br>
        <h3>Filter by Scholarships</h3>
        <?php while ($scholarship = $scholarshipResult->fetch_assoc()): ?>
            <div>
                <input type="checkbox" name="scholarship_ids[]" value="<?php echo $scholarship['scholarship_id']; ?>" 
                    <?php echo in_array($scholarship['scholarship_id'], $selectedScholarshipIds) ? 'checked' : ''; ?>>
                <label><?php echo htmlspecialchars($scholarship['scholarship_name']); ?></label>
            </div>
        <?php endwhile; ?>
        <a href="manage_applications.php" class="reset-button">Reset</a><br>
        <button class="addapplicant" onclick="event.preventDefault(); window.location.href='apply1.php';">+</button>
    </form>
</div>
         
    </main>

    <script>
        // Automatically submit the form when a checkbox is clicked
        document.querySelectorAll('input[name="scholarship_ids[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                checkbox.closest('form').submit();
            });
        });
    </script>
</body>

</html>

<?php
// Close the connection
$conn->close();
?>*/