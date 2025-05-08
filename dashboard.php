<?php
$currentPage = 'dashboard';
include 'header_sidebar.php';
include 'db.php'; // Ensure the database connection is included

// Fetch total applications from the database
$query = "SELECT COUNT(*) AS total FROM applicant_scholarship_records	";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    $totalApplications = $row['total'];
} else {
    $totalApplications = 0; // Default to 0 if the query fails
}

// Example data for the charts (replace with dynamic data from your database)
$approvedApplications = 80; // Replace with dynamic data
$pendingApplications = 40;  // Replace with dynamic data
$rejectedApplications = 20; // Replace with dynamic data
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
           
            margin: 0;
            padding: 0;
        }

        main {
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
        }

        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .stat-card {
           padding-block: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
         
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-title {
            font-weight: bold;
            font-size: 1.1em;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
           
        }

        .stat-icon {
            font-size: 2.5em;
            color: rgb(64, 112, 72);
            margin-bottom: 10px;
        }

        .chart-container {
            
            max-width: 400px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .stat-card {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <main>
        <div id="dashboard" class="page active-page">
            <h2>Dashboard</h2>
            <div class="dashboard-stats">
                <div class="stat-card" style="background-color:rgba(251, 255, 0, 0.17); ">
                    <div class="stat-icon"  ><i class="fas fa-file-alt" style="color:rgb(194, 181, 0); "></i></div>
                    <div class="stat-title">Total Applications</div>
                    <div class="stat-value" id="total-applications" style="color:rgb(179, 167, 0); ">120</div>
                </div>
                <div class="stat-card" style="background-color:rgba(197, 248, 197, 0.66); ">
                    <div class="stat-icon" ><i class="fas fa-check-circle" style="color:rgb(23, 107, 48); "></i></div>
                    <div class="stat-title">Approved Applications</div>
                    <div class="stat-value" id="approved-applications" style="color:rgb(23, 107, 48); "><?php echo $approvedApplications; ?></div>
                </div>
                <div class="stat-card" style="background-color:rgba(228, 197, 248, 0.63); ">
                    <div class="stat-icon" ><i class="fas fa-clock" style="color:rgb(70, 9, 94); "></i></div>
                    <div class="stat-title">Pending Applications</div>
                    <div class="stat-value" id="pending-applications" style="color:rgb(70, 9, 94); "><?php echo $pendingApplications; ?></div>
                </div>
            </div>
<!-- Flex Container for Charts -->
<div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; margin-top: -35px;">
  
            <!-- Bar Chart -->
            <div class="chart-container">
                <h3 style="text-align: center;">Applications Overview</h3>
                <canvas id="applicationsBarChart"></canvas>
            </div>

            <!-- Pie Chart -->
            <div class="chart-container">
                <h3 style="text-align: center;">Application Status Distribution</h3>
                <canvas id="applicationsPieChart"></canvas>
            </div>
        </div></div>
    </main>

    <script>
        // Bar Chart
        const barCtx = document.getElementById('applicationsBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Total', 'Approved', 'Pending', 'Rejected'],
                datasets: [{
                    label: 'Applications',
                    data: [<?php echo $totalApplications; ?>, <?php echo $approvedApplications; ?>, <?php echo $pendingApplications; ?>, <?php echo $rejectedApplications; ?>],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545'],
                    borderColor: ['#0056b3', '#1e7e34', '#d39e00', '#c82333'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Pie Chart
        const pieCtx = document.getElementById('applicationsPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    data: [<?php echo $approvedApplications; ?>, <?php echo $pendingApplications; ?>, <?php echo $rejectedApplications; ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>
