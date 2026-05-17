<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$conn = mysqli_connect("localhost", "root", "", "smart_grid");

// সার্চ এবং ফিল্টার ভেরিয়েবল সেট করা
$search_zone = isset($_GET['search_zone']) ? mysqli_real_escape_string($conn, $_GET['search_zone']) : '';
$filter_action = isset($_GET['filter_action']) ? mysqli_real_escape_string($conn, $_GET['filter_action']) : '';

// কুয়েরি তৈরি করা
$query = "SELECT * FROM load_shedding_logs WHERE 1=1";

if ($search_zone != '') {
    $query .= " AND zone_id = '$search_zone'";
}
if ($filter_action != '') {
    $query .= " AND action = '$filter_action'";
}

$query .= " ORDER BY timestamp DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Load History - SmartGrid</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-section {
            background: #2a2a40;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .filter-section input, .filter-section select {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #3d3d5c;
            background: #1e1e2f;
            color: white;
        }
        .btn-search {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-reset {
            background: #e74c3c;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><i class="fas fa-bolt"></i> SmartGrid</div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="load_history.php" class="nav-item active"><i class="fas fa-history"></i> Load History</a>
            <a href="maintenance.php" class="nav-item"><i class="fas fa-tools"></i> Maintenance</a>
            <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="main-header">
            <h1>Load Shedding History</h1>
            <p>Search and track automated power actions</p>
        </header>

        <!-- Search and Filter Form -->
        <form method="GET" class="filter-section">
            <input type="number" name="search_zone" placeholder="Search by Zone ID..." value="<?php echo $search_zone; ?>">
            
            <select name="filter_action">
                <option value="">All Actions</option>
                <option value="AUTO_OFF_OVERLOAD" <?php if($filter_action == 'AUTO_OFF_OVERLOAD') echo 'selected'; ?>>Auto Off (Overload)</option>
                <option value="LOCAL_OVERLOAD" <?php if($filter_action == 'LOCAL_OVERLOAD') echo 'selected'; ?>>Local Overload</option>
                <option value="MANUAL_OFF" <?php if($filter_action == 'MANUAL_OFF') echo 'selected'; ?>>Manual Off</option>
            </select>

            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
            <?php if($search_zone != '' || $filter_action != ''): ?>
                <a href="load_history.php" class="btn-reset">Reset</a>
            <?php endif; ?>
        </form>

        <div class="table-container">
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Zone ID</th>
                        <th>Action</th>
                        <th>Load (MW)</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $badge_color = ($row['action'] == 'MANUAL_OFF') ? '#f1c40f' : '#e74c3c';
                            echo "<tr>
                                <td>#{$row['log_id']}</td>
                                <td><strong>Zone {$row['zone_id']}</strong></td>
                                <td><span class='badge' style='background: $badge_color'>{$row['action']}</span></td>
                                <td>{$row['load_at_time']} MW</td>
                                <td>{$row['timestamp']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center;'>No matching records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>