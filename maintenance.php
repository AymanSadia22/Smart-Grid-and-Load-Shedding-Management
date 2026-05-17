<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "smart_grid");

if (!$conn) {
    die("Database Connection failed: " . mysqli_connect_error());
}

$msg = "";
// ডাটা ইনসার্ট করার লজিক (তোমার কলাম অনুযায়ী)
if (isset($_POST['add_maintenance'])) {
    $zone_id = intval($_POST['zone_id']);
    $issue = mysqli_real_escape_string($conn, $_POST['task']); // ফর্মের task ইনপুটকে issue কলামে রাখা হচ্ছে
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // তোমার কলামের নাম অনুযায়ী SQL কোড
    $insert_query = "INSERT INTO maintenance_logs (zone_id, issue, status) 
                     VALUES ($zone_id, '$issue', '$status')";
    
    if (mysqli_query($conn, $insert_query)) {
        $msg = "<p class='alert-success'>Maintenance record added successfully!</p>";
    } else {
        $msg = "<p class='alert-error'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Smart Grid</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        :root { --primary-blue: #2980b9; --dark-blue: #1a5276; }
        .form-container { background: #fff; padding: 25px; border-radius: 10px; border-top: 4px solid var(--primary-blue); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark-blue); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #d5dbdb; border-radius: 6px; }
        .btn-submit { background: var(--primary-blue); color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .status-pill { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; color: white; }
        .status-pending { background: #f39c12; }
        .status-completed { background: var(--primary-blue); }
        .alert-success { background: #d4efdf; color: #1e8449; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header" style="color: #fff;"><i class="fas fa-bolt"></i> <span>SmartGrid</span></div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="load_history.php" class="nav-item"><i class="fas fa-history"></i> Load History</a>
            <a href="maintenance.php" class="nav-item active"><i class="fas fa-tools"></i> Maintenance</a>
            <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="main-header" style="padding-bottom: 15px; margin-bottom: 25px; border-bottom: 2px solid var(--primary-blue);">
            <div class="header-info">
                <h1 style="color: var(--dark-blue);">Maintenance Management</h1>
                <p>Track issues and repairs across zones</p>
            </div>
        </header>

        <?php echo $msg; ?>

        <div class="form-container">
            <h3 style="color: var(--primary-blue);"><i class="fas fa-plus-circle"></i> Log New Issue</h3>
            <form action="maintenance.php" method="POST" style="margin-top: 20px;">
                <div class="form-group">
                    <label>Zone Name</label>
                    <select name="zone_id" required>
                        <option value="">-- Select a Zone --</option>
                        <?php
                        $z_query = "SELECT zone_id, zone_name FROM zones";
                        $z_res = mysqli_query($conn, $z_query);
                        while($z = mysqli_fetch_assoc($z_res)) {
                            echo "<option value='".$z['zone_id']."'>".$z['zone_name']."</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Issue Description</label>
                    <textarea name="task" rows="3" placeholder="Explain the problem..." required></textarea>
                </div>
                <div class="form-group" style="margin-top: 15px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="Pending">Pending</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" name="add_maintenance" class="btn-submit">Save Log</button>
                </div>
            </form>
        </div>

        <div class="table-container">
            <h3 style="margin-bottom: 15px; color: var(--dark-blue);">Maintenance History</h3>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Created At</th>
                        <th>Zone Name</th>
                        <th>Issue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // তোমার ডাটাবেসের সঠিক কলাম 'log_id' এবং 'issue' ব্যবহার করা হয়েছে
                    $log_query = "SELECT m.*, z.zone_name FROM maintenance_logs m 
                                  JOIN zones z ON m.zone_id = z.zone_id 
                                  ORDER BY m.log_id DESC";
                    $log_res = mysqli_query($conn, $log_query);

                    if (mysqli_num_rows($log_res) > 0) {
                        while($log = mysqli_fetch_assoc($log_res)) {
                            $st_class = (strtolower($log['status']) == 'completed') ? 'status-completed' : 'status-pending';
                            ?>
                            <tr>
                                <td><?php echo $log['created_at']; ?></td>
                                <td><strong><?php echo htmlspecialchars($log['zone_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($log['issue']); ?></td>
                                <td><span class="status-pill <?php echo $st_class; ?>"><?php echo $log['status']; ?></span></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding: 20px;'>No logs found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>