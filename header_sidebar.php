<?php
include 'db.php'; 

session_start(); // Ensure session is active

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Fetch the user's details from the database
    $query = "SELECT first_name, last_name, email, profile_avatar FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $first_name = $row['first_name']; 
        $last_name = $row['last_name']; 
        $email = $row['email']; 

        // Correctly format the BLOB image for display
        $profile_avatar = (!empty($row['profile_avatar']) && strlen($row['profile_avatar']) > 10) 
            ? 'data:image/jpeg;base64,' . base64_encode($row['profile_avatar']) 
            : 'default_avatar.png';
    } else {
        $first_name = 'Guest';
        $last_name = '';
        $email = 'guest@example.com';
        $profile_avatar = 'default_avatar.png';
    }
} else {
    $first_name = 'Guest';
    $last_name = '';
    $email = 'guest@example.com';
    $profile_avatar = 'default_avatar.png';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css?family=Poster+Gothic" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">

     <style>
        .profile-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%; /* Makes the image circular */
    object-fit: cover; /* Ensures the image fits within the circle */
    border: 2px solid #ccc; /* Optional border */
}

#logoutModal button {
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

#logoutModal button:hover {
    opacity: 0.9;
}
     </style>
</head>
<body>

<header>
    <div class="lofg">
        <img src="logobright.png" alt="Profile Picture">
    </div>
</header>

<aside>
<div class="logout">

<div class="profile_top" style="display: flex;
 align-items:center;
   gap: 10px;
   
   margin-right: 10px;
   padding-bottom: 10px;">
    <!-- Profile Avatar -->
    <img class="logoimg profile-avatar" width="50" height="50" src="<?php echo htmlspecialchars($profile_avatar); ?>" alt="Profile Picture">
    
    <!-- User Name and Email -->
    <div class="user-nav">
        <a class="lo"  href="javascript:void(0);" onclick="openLogoutModal()""
         style="
         margin: 5px 0;
        text-decoration: none;
         color:rgb(206, 196, 245);
         font-size: 18px;
         padding-block: 5px;
         ">
            <?php echo htmlspecialchars("$first_name $last_name"); ?>
        </a>


    
        <p style="margin: 5px 0; font-size: 14px; color: #666;">
            <?php echo htmlspecialchars($email); ?>
        </p>
    </div>
</div>
    </div>
    
<nav>
<ul>
  <div class="manage">
 <div class="mes">
    <li <?php if ($currentPage == 'dashboard') echo 'class="active"'; ?>>
        <i class="fas fa-tachometer-alt"></i> <a href="dashboard.php">Dashboard</a>
    </li>
    <li <?php if ($currentPage == 'admin') echo 'class="active"'; ?>>
        <i class="fas fa-users"></i> <a href="manage_account_admin.php">People</a>
    </li>
    <li <?php if ($currentPage == 'manage_applications') echo 'class="active"'; ?>>
        <i class="fas fa-file-alt"></i><a href="manage_applications.php">Applicants</a>
    </li>
    <li <?php if ($currentPage == 'manage_scholarships') echo 'class="active"'; ?>>
        <i class="fas fa-graduation-cap"></i><a href="manage_scholarships.php">Scholarships</a>
    </li>
    <li <?php if ($currentPage == 'inquiries') echo 'class="active"'; ?>>
        <i class="fas fa-envelope"></i>  <a href="inquiries.php">Inquiries</a>
    </li>
    <li <?php if ($currentPage == 'settings') echo 'class="active"'; ?>>
        <i class="fas fa-cog"></i>  <a href="settings.php">Settings</a>
    </li>
    <li <?php if ($currentPage == 'profile') echo 'class="active"'; ?>>
        <i class="fas fa-user"></i>  <a href="profile.php">Profile</a>
    </li>

 </div>
</div>
</ul>
</nav>
</aside>
<!-- Logout Confirmation Modal -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background-color: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; text-align: center; position: relative;">
        <!-- Profile Avatar -->
         <p
         style="
         background: linear-gradient(to right,rgb(207, 225, 243),rgb(211, 211, 211));
         color: #6fa4ce;
         font-size: 18px;
         font-weight: bold;
         padding: 10px"> Logout</p>
        <img class="profile-avatar" width="50" height="50" src="<?php echo htmlspecialchars($profile_avatar); ?>" alt="Profile Picture" style="margin-bottom: 10px;">

        <!-- User Email -->
        <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
            <?php echo htmlspecialchars($email); ?>
        </p>

        <!-- Confirmation Message -->
        <p style="font-size: 16px; font-weight: bold; margin-bottom: 20px;">
            Are you sure you want to logout?
        </p>

        <!-- Buttons -->
        <div style="display: flex; justify-content: space-between; gap: 10px;">
        <button onclick="closeLogoutModal()"
         style="flex: 1;
          padding: 10px; 
           background: linear-gradient(to right, #499bed, #499bed);
            color: white; border: none; border-radius: 5px; cursor: pointer;">
                Cancel
            </button>
             <button onclick="logoutUser()" style="flex: 1; padding: 10px;  
            background: linear-gradient(to right, #f6aa48, #fccc3a);
            color: white; border: none; border-radius: 5px; cursor: pointer;">
                Logout
            </button>
            
        </div>

        <!-- Loading Message -->
        <div id="loadingMessage" style="display: none; margin-top: 20px; font-size: 14px; color: #666;">
            <i class="fas fa-spinner fa-spin"></i> Logging out...
        </div>
    </div>
</div>
<script>
    function toggleSidebar() {
        document.getElementById("sidebar").classList.toggle("collapsed");
    }
</script>
<script>
    function openLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    function logoutUser() {
        // Show the loading message
        document.getElementById('loadingMessage').style.display = 'block';

        // Simulate a delay for logging out
        setTimeout(() => {
            window.location.href = 'logout.php'; // Redirect to the logout page
        }, 2000); // 2-second delay
    }
</script>

</body>
</html>
