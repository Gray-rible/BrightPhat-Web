<?php
$currentPage = 'manage_scholarships';
include 'header_sidebar.php';

// Start the session only if it's not active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
} 

include 'db.php'; // Replace with your actual database connection file

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the logged-in user's ID
$loggedInUserId = $_SESSION['user_id'];

// Fetch scholarships and their categories from the database

$sql = "SELECT 
            s.scholarship_id,
            s.scholarship_name, 
            c.category_name, 
            s.Scholarship_general_description_s1, 
            s.scholarship_selection_criteria, 
            CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) AS admin_name,
            st.status_name, -- Fetch the status name
            s.slots_available,
            s.image_data, -- Keep image_data for gallery mode
            s.logo, -- Add logo for table mode
            s.user_id
        FROM scholarships s
        LEFT JOIN scholarship_categories c ON s.scholarship_category_id = c.scholarship_category_id
        LEFT JOIN users u ON s.user_id = u.user_id
        LEFT JOIN status st ON s.status_id = st.status_id";
        

$result = mysqli_query($conn, $sql);

// Check if the query was successful
if (!$result) {
    die("SQL Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarships Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        main {
            margin-left: 320px;
            padding: 20px;
        }
        h1 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .scholarship-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 250px;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .scholarship-card.managed {
            background: linear-gradient(to bottom, rgb(255, 255, 255), rgb(186, 255, 202)); /* Light to green gradient */
            border: solid green 3px;
            color: white;
        }
        .scholarship-card.managed h3 {
            color: black;
        }
        .scholarship-card.managed p {
            color: rgb(133, 133, 133);
        }
        .scholarship-card.managed .status {
            color: rgb(36, 122, 40);
        }
        .scholarship-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .scholarship-card h3 {
            font-size: 18px;
            margin: 10px 0;
            color: #333;
        }
        .scholarship-card p {
            margin: 5px 0;
            color: #555;
        }
        .scholarship-card .status {
            font-weight: bold;
            color: #28a745;
        }
        .scholarship-card .status.inactive {
            color: #dc3545;
        }
        .scholarship-card .buttons {
            margin-top: 10px;
        }
        .scholarship-card .buttons a {
            display: inline-block;
            margin: 5px 5px 0 0;
            padding: 5px 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .scholarship-card .buttons a.edit {
            background-color: #28a745;
        }
        .scholarship-card .buttons a:hover {
            opacity: 0.9;
        }
        .add-card {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f4f4f4;
            border: 2px dashed #007bff;
            border-radius: 8px;
            width: 250px;
            height: 400px;
            text-align: center;
            cursor: pointer;
        }
        .add-card a {
            text-decoration: none;
            color: #007bff;
            font-size: 18px;
            font-weight: bold;
        }
        .add-card:hover {
            background: #e9ecef;
        }
        table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    .th {
        background: linear-gradient(to right,rgb(33, 50, 68),rgb(5, 14, 22));
        color: white;
        font-weight: bold;
        border: none;
    }

    tr:hover {
        background-color: #f1f1f1;
        transition: background-color 0.3s ease;
    }

    td img {
        vertical-align: middle;
    }

    td a {
        text-decoration: none;
        color: #007bff;
        font-size: 16px;
        margin-right: 10px;
    }

    td a:hover {
        color: #0056b3;
    }

    td a.view-icon {
        color: #007bff;
    }

    td a.edit-icon {
        color: #28a745;
    }
    tr.table-contents {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    tr.table-contents:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    </style>
</head>
<body>
    <main>
        <h1>Scholarships Management</h1>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;margin-top: -20px">
    <button id="toggleViewMode" style="display: flex; align-items: center; gap: 10px; padding: 10px 15px; background-color: rgb(255, 255, 255); color: black; border: none; border-radius: 5px; cursor: pointer;">
        <i id="viewIcon" class="fas fa-eye"></i> <!-- Static view icon -->
        <span id="viewModeText">View Mode</span>
        <i id="currentModeIcon" class="fas fa-th"></i> <!-- Changing icon -->
    </button>
</div>


<table id="scholarshipTable" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr class="th">
            <th style="border: 1px solid #ddd; padding: 8px;">ID</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Scholarship Name</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Category</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Managed By</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Slots Available</th>
            <th style="border: 1px solid #ddd; padding: 8px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // Populate the table rows
        mysqli_data_seek($result, 0); // Reset the result pointer
        while ($row = mysqli_fetch_assoc($result)) {
            $imageSrc = !empty($row['image_data']) 
                ? "data:image/jpeg;base64," . base64_encode($row['image_data']) 
                : 'default-image.jpg'; // Default image if none is provided

            echo "<tr class='table-contents'>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['scholarship_id']) . "</td>";
           
            $logoSrc = !empty($row['logo']) 
                ? "data:image/jpeg;base64," . base64_encode($row['logo']) 
                : 'default-logo.jpg'; // Default logo if none is provided
            
            echo "<td style='border: 1px solid #ddd; padding: 8px;'><img src='$logoSrc' alt='Scholarship Logo' style='width:50px; height: 50px; object-fit: cover; border-radius: 50px;'> " . htmlspecialchars($row['scholarship_name']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['category_name'] ?? 'Uncategorized') . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['admin_name']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['status_name']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($row['slots_available']) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>
        <a href='view_scholarship.php?scholarship_id=" . $row['scholarship_id'] . "' class='view-icon'>
            <i class='fas fa-eye'></i> <!-- View Icon -->
        </a>";
if ($row['user_id'] == $loggedInUserId) {
    echo "<a href='edit_scholarship.php?scholarship_id=" . $row['scholarship_id'] . "' class='edit-icon'>
            <i class='fas fa-edit'></i> <!-- Edit Icon -->
        </a>";
}
echo "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
        <div class="gallery" style="display: none;">
    <?php
    mysqli_data_seek($result, 0); // Reset the result pointer
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $imageSrc = !empty($row['image_data']) 
                ? "data:image/jpeg;base64," . base64_encode($row['image_data']) 
                : 'default-image.jpg'; // Default image if none is provided

            $cardClass = $row['user_id'] == $loggedInUserId ? 'scholarship-card managed' : 'scholarship-card';

            echo "<div class='$cardClass' id='scholarship-{$row['scholarship_id']}'>";
            echo "<img src='$imageSrc' alt='Scholarship Image'>";
            echo "<h3>" . htmlspecialchars($row['scholarship_name']) . "</h3>";
            echo "<p>Category: " . htmlspecialchars($row['category_name'] ?? 'Uncategorized') . "</p>";
            echo "<p>Managed By: " . htmlspecialchars($row['admin_name']) . "</p>";
            echo "<p class='status'>" . htmlspecialchars($row['status_name']) . "</p>";
            echo "<p>Slots Available: " . htmlspecialchars($row['slots_available']) . "</p>";
            echo "<div class='buttons'>";
            echo "<a href='view_scholarship.php?scholarship_id=" . $row['scholarship_id'] . "' class='view'>View</a>";
            if ($row['user_id'] == $loggedInUserId) {
                echo "<a href='edit_scholarship.php?scholarship_id=" . $row['scholarship_id'] . "' class='edit'>Edit</a>";
            }
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>No scholarships found</p>";
    }
    ?>
            <!-- Add Scholarship Card -->
            <div class="add-card">
                <a href="add_scps.php">+ Add Scholarship</a>
            </div>
        </div>
    </main>
</body>
<script>
    const toggleViewModeButton = document.getElementById('toggleViewMode');
    const currentModeIcon = document.getElementById('currentModeIcon');
    const gallery = document.querySelector('.gallery');
    const scholarshipTable = document.getElementById('scholarshipTable');

    toggleViewModeButton.addEventListener('click', () => {
    if (gallery.style.display === 'none') {
        // Switch to card view
        gallery.style.display = 'flex';
        scholarshipTable.style.display = 'none';
        currentModeIcon.className = 'fas fa-th'; // Icon for card view
    } else {
        // Switch to table view
        gallery.style.display = 'none';
        scholarshipTable.style.display = 'table';
        currentModeIcon.className = 'fas fa-table'; // Icon for table view
    }
});

</script>
</html>