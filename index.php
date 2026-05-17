<?php
// ১. সেশন শুরু করা (লগইন চেক করার জন্য)
session_start();

// ২. যদি ইউজার লগইন করা না থাকে
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// ৩. ডাটাবেস কানেকশন
$conn = mysqli_connect("localhost", "root", "", "smart_grid");

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

// ৪. ম্যানুয়াল ওভাররাইডিং লজিক (বাটন ক্লিক করলে স্ট্যাটাস পরিবর্তন)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_to_update = intval($_GET['id']);
    $action = $_GET['action'];
    $new_status = ($action == 'on') ? 'ON' : 'OFF';
    
    // ডাটাবেসে আপডেট কোয়েরি
    $update_query = "UPDATE zones SET status = '$new_status' WHERE zone_id = $id_to_update";
    
    if(mysqli_query($conn, $update_query)) {
        // সাকসেস হলে পেজ রিফ্রেশ করে অ্যাকশন প্যারামিটার মুছে ফেলা
        header("Location: index.php");
        exit();
    }
}

// ৫. স্ট্যাটাস কার্ডের জন্য ডাটা
$stats_query = "SELECT SUM(current_load) as total_l, COUNT(*) as total_z FROM zones";
$stats_res = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_res);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Grid Management System</title>
    <!-- Font Awesome আইকন -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    
    <!-- জাভাস্ক্রিপ্ট দিয়ে ৫ সেকেন্ড পর পর অটো রিফ্রেশ নিশ্চিত করা -->
    <script>
        setTimeout(function(){
           window.location.reload();
        }, 5000); // ৫০০০ মিলিসেকেন্ড = ৫ সেকেন্ড
    </script>

    <style>
        /* বাটন এবং আইডির জন্য স্টাইল */
        .btn-action { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; color: white; display: inline-block; transition: 0.3s; text-transform: uppercase; }
        .btn-on { background-color: #2ecc71; border: 1px solid #27ae60; }
        .btn-off { background-color: #e74c3c; border: 1px solid #c0392b; }
        .btn-action:hover { opacity: 0.8; transform: translateY(-1px); }
        .id-badge { color: #8e8e8e; font-weight: bold; font-family: monospace; }
        .priority-badge { background: #f1c40f; color: #000; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-bolt"></i>
            <span>SmartGrid</span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="load_history.php" class="nav-item"><i class="fas fa-history"></i> Load History</a>
            <a href="maintenance.php" class="nav-item"><i class="fas fa-tools"></i> Maintenance</a>
            <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <header class="main-header">
            <div class="header-info">
                <h1>Dashboard Overview</h1>
                <p>Live Updates: <span style="color: #2ecc71;">Every 5 Seconds</span></p>
            </div>
            <div class="user-profile">
                <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['user']); ?></strong></span>
                <img src="https://ui-avatars.com/api/?name=<?php echo $_SESSION['user']; ?>&background=2ecc71&color=fff" alt="User">
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid-stats">
            <div class="card">
                <h3>Total Load</h3>
                <p class="stat-value"><?php echo $stats['total_l'] ?? 0; ?> <span>MW</span></p>
            </div>
            <div class="card">
                <h3>Total Zones</h3>
                <p class="stat-value"><?php echo $stats['total_z'] ?? 0; ?></p>
            </div>
            <div class="card alert">
                <h3>System Status</h3>
                <p class="status-live"><span class="dot"></span> Monitoring Active</p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="table-container" style="margin-bottom: 25px; padding: 20px;">
            <h3 style="margin-bottom: 15px;">Real-time Load Analysis (MW)</h3>
            <canvas id="loadChart" style="max-height: 250px; width: 100%;"></canvas>
        </div>

        <!-- Zone Table -->
        <div class="table-container">
            <h3>Live Zone Control Panel</h3>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zone Name</th>
                        <th>Max Capacity</th>
                        <th>Load (MW)</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $labels = [];
                    $data_values = [];
                    
                    $query = "SELECT * FROM zones";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $labels[] = $row['zone_name'];
                            $data_values[] = $row['current_load'];

                            $is_on = (strtoupper($row['status']) == 'ON');
                            $status_class = $is_on ? 'on' : 'off';
                            $priority_text = ($row['is_priority']) ? 'High' : 'Standard';

                            $load = (int)$row['current_load'];
                            $capacity = (int)$row['max_capacity'];
                            $percent = ($capacity > 0) ? ($load / $capacity) * 100 : 0;

                            $load_color = "#2ecc71"; 
                            if ($percent > 90) $load_color = "#e74c3c"; 
                            else if ($percent > 70) $load_color = "#f1c40f"; 
                            ?>

                            <tr>
                                <td class="id-badge">#<?php echo $row['zone_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['zone_name']); ?></strong></td>
                                

                            <td class="capacity-text">
                              <?php echo $row['max_capacity']; ?> MW
                                </td>
                                <td style="color: <?php echo $load_color; ?>; font-weight: bold;">
                                    <?php echo $load; ?> MW
                                </td>
                                <td>
                                    <?php if($row['is_priority']): ?>
                                        <span class="priority-badge">HIGH</span>
                                    <?php else: ?>
                                        <span style="color: #95a5a6;">STD</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- অন/অফ বাটন লজিক -->
                                    <?php if ($is_on): ?>
                                        <a href="index.php?action=off&id=<?php echo $row['zone_id']; ?>" 
                                           class="btn-action btn-off" onclick="return confirm('পাওয়ার বন্ধ করতে চান?')">
                                           Cut Power
                                        </a>
                                    <?php else: ?>
                                        <a href="index.php?action=on&id=<?php echo $row['zone_id']; ?>" 
                                           class="btn-action btn-on">
                                           Restore
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('loadChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Current Load (MW)',
                    data: <?php echo json_encode($data_values); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    borderColor: '#2ecc71',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>